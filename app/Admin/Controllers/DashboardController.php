<?php
/**
 * Admin Dashboard Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->requireAdmin();
    }

    public function index(): void
    {
        $db = Database::getInstance();

        // Get dashboard stats
        $stats = $this->getDashboardStats($db);

        // Get recent orders
        $stmt = $db->query("
            SELECT o.*, u.first_name, u.last_name, u.email
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT 10
        ");
        $recentOrders = $stmt->fetchAll();

        // Get low stock products
        $stmt = $db->query("
            SELECT p.*, pi.image_path as image
            FROM products p
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE p.stock_quantity <= p.low_stock_threshold AND p.track_stock = 1
            ORDER BY p.stock_quantity ASC
            LIMIT 10
        ");
        $lowStockProducts = $stmt->fetchAll();

        // Get sales chart data (last 30 days)
        $salesData = $this->getSalesChartData($db, 30);

        // Get top products
        $stmt = $db->query("
            SELECT p.id, p.name, p.slug, SUM(oi.quantity) as total_sold, SUM(oi.price * oi.quantity) as revenue
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND o.status != 'cancelled'
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT 5
        ");
        $topProducts = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('admin/pages/dashboard', [
            'meta_title' => 'Dashboard | Admin',
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
            'salesData' => $salesData,
            'topProducts' => $topProducts,
        ]);
    }

    private function getDashboardStats($db): array
    {
        // Today's orders
        $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
        $todayOrders = (int) $stmt->fetchColumn();

        // Today's revenue
        $stmt = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'");
        $todayRevenue = (float) $stmt->fetchColumn();

        // This month's revenue
        $stmt = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW()) AND status != 'cancelled'");
        $monthRevenue = (float) $stmt->fetchColumn();

        // Last month's revenue for comparison
        $stmt = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND status != 'cancelled'");
        $lastMonthRevenue = (float) $stmt->fetchColumn();

        $revenueGrowth = $lastMonthRevenue > 0 ? (($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;

        // Pending orders
        $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
        $pendingOrders = (int) $stmt->fetchColumn();

        // Total customers
        $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
        $totalCustomers = (int) $stmt->fetchColumn();

        // New customers this month
        $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())");
        $newCustomers = (int) $stmt->fetchColumn();

        // Total products
        $stmt = $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
        $totalProducts = (int) $stmt->fetchColumn();

        // Low stock count
        $stmt = $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= low_stock_threshold AND track_stock = 1");
        $lowStockCount = (int) $stmt->fetchColumn();

        return [
            'today_orders' => $todayOrders,
            'today_revenue' => $todayRevenue,
            'month_revenue' => $monthRevenue,
            'revenue_growth' => $revenueGrowth,
            'pending_orders' => $pendingOrders,
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
        ];
    }

    private function getSalesChartData($db, int $days): array
    {
        $stmt = $db->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as orders, COALESCE(SUM(total), 0) as revenue
            FROM orders
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND status != 'cancelled'
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$days]);

        $results = $stmt->fetchAll();

        $labels = [];
        $orderData = [];
        $revenueData = [];

        // Fill in missing dates with 0
        $startDate = new \DateTime("-{$days} days");
        $endDate = new \DateTime();
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($startDate, $interval, $endDate);

        $dataByDate = [];
        foreach ($results as $row) {
            $dataByDate[$row['date']] = $row;
        }

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $orderData[] = (int) ($dataByDate[$dateStr]['orders'] ?? 0);
            $revenueData[] = (float) ($dataByDate[$dateStr]['revenue'] ?? 0);
        }

        return [
            'labels' => $labels,
            'orders' => $orderData,
            'revenue' => $revenueData,
        ];
    }
}
