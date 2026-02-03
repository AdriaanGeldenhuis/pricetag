<?php
declare(strict_types=1);

/**
 * Admin Report Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Handles analytics and reporting functionality
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ReportController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();

        // Date range
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Overview stats
        $stats = $this->getOverviewStats($db, $startDate, $endDate);

        // Revenue chart data (daily)
        $revenueChart = $this->getRevenueChart($db, $startDate, $endDate);

        // Order status breakdown
        $stmt = $db->prepare("
            SELECT status, COUNT(*) as count, SUM(total) as total
            FROM orders
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY status
        ");
        $stmt->execute([$startDate, $endDate]);
        $ordersByStatus = $stmt->fetchAll();

        // Top products
        $topProducts = $this->getTopProducts($db, $startDate, $endDate, 10);

        // Top categories
        $topCategories = $this->getTopCategories($db, $startDate, $endDate, 10);

        // Recent orders
        $stmt = $db->prepare("
            SELECT o.*, CONCAT(o.billing_first_name, ' ', o.billing_last_name) as customer_name
            FROM orders o
            ORDER BY o.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        $recentOrders = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/reports/index', [
            'title' => 'Reports & Analytics',
            'stats' => $stats,
            'revenueChart' => $revenueChart,
            'ordersByStatus' => $ordersByStatus,
            'topProducts' => $topProducts,
            'topCategories' => $topCategories,
            'recentOrders' => $recentOrders,
            'dateRange' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ]);
    }

    public function sales(): void
    {
        $db = Database::getInstance();

        // Date range
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $groupBy = $_GET['group_by'] ?? 'day'; // day, week, month

        // Sales data
        $salesData = $this->getSalesData($db, $startDate, $endDate, $groupBy);

        // Payment method breakdown
        $stmt = $db->prepare("
            SELECT p.method, COUNT(*) as count, SUM(p.amount) as total
            FROM payments p
            JOIN orders o ON p.order_id = o.id
            WHERE p.status = 'completed'
            AND DATE(o.created_at) BETWEEN ? AND ?
            GROUP BY p.method
        ");
        $stmt->execute([$startDate, $endDate]);
        $paymentMethods = $stmt->fetchAll();

        // Coupon usage
        $stmt = $db->prepare("
            SELECT c.code, COUNT(cu.id) as usage_count, SUM(cu.discount_amount) as total_discount
            FROM coupon_usage cu
            JOIN coupons c ON cu.coupon_id = c.id
            JOIN orders o ON cu.order_id = o.id
            WHERE DATE(o.created_at) BETWEEN ? AND ?
            GROUP BY c.id
            ORDER BY usage_count DESC
            LIMIT 10
        ");
        $stmt->execute([$startDate, $endDate]);
        $couponUsage = $stmt->fetchAll();

        // Average order value over time
        $aovData = $this->getAOVData($db, $startDate, $endDate, $groupBy);

        // Shipping methods
        $stmt = $db->prepare("
            SELECT shipping_method, COUNT(*) as count, SUM(shipping_amount) as total
            FROM orders
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND shipping_method IS NOT NULL
            GROUP BY shipping_method
        ");
        $stmt->execute([$startDate, $endDate]);
        $shippingMethods = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/reports/sales', [
            'title' => 'Sales Report',
            'salesData' => $salesData,
            'paymentMethods' => $paymentMethods,
            'couponUsage' => $couponUsage,
            'aovData' => $aovData,
            'shippingMethods' => $shippingMethods,
            'dateRange' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'groupBy' => $groupBy,
        ]);
    }

    public function products(): void
    {
        $db = Database::getInstance();

        // Date range
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Top selling products
        $topSelling = $this->getTopProducts($db, $startDate, $endDate, 20);

        // Low stock products
        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name,
                   (SELECT path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
            FROM products p
            LEFT JOIN categories c ON p.id = c.id
            WHERE p.manage_stock = 1
            AND p.stock_quantity <= p.low_stock_threshold
            AND p.stock_quantity > 0
            ORDER BY p.stock_quantity ASC
            LIMIT 20
        ");
        $stmt->execute();
        $lowStock = $stmt->fetchAll();

        // Out of stock products
        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.id = c.id
            WHERE p.manage_stock = 1 AND p.stock_quantity = 0
            ORDER BY p.sold_count DESC
            LIMIT 20
        ");
        $stmt->execute();
        $outOfStock = $stmt->fetchAll();

        // Products by view count
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.sku, p.view_count, p.sold_count,
                   CASE WHEN p.view_count > 0 THEN (p.sold_count / p.view_count * 100) ELSE 0 END as conversion_rate
            FROM products p
            WHERE p.status = 'active'
            ORDER BY p.view_count DESC
            LIMIT 20
        ");
        $stmt->execute();
        $mostViewed = $stmt->fetchAll();

        // Product performance (views vs sales)
        $stmt = $db->prepare("
            SELECT
                p.id, p.name, p.sku, p.price, p.view_count, p.sold_count,
                COALESCE(SUM(oi.total), 0) as revenue
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND DATE(o.created_at) BETWEEN ? AND ?
            WHERE p.status = 'active'
            GROUP BY p.id
            ORDER BY revenue DESC
            LIMIT 50
        ");
        $stmt->execute([$startDate, $endDate]);
        $productPerformance = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/reports/products', [
            'title' => 'Product Report',
            'topSelling' => $topSelling,
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
            'mostViewed' => $mostViewed,
            'productPerformance' => $productPerformance,
            'dateRange' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ]);
    }

    public function customers(): void
    {
        $db = Database::getInstance();

        // Date range
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // New customers
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM users
            WHERE role = 'customer'
            AND DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $newCustomers = $stmt->fetchColumn();

        // Active customers (placed order in period)
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT user_id) as count
            FROM orders
            WHERE DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $activeCustomers = $stmt->fetchColumn();

        // Top customers
        $stmt = $db->prepare("
            SELECT
                u.id, u.email, u.first_name, u.last_name,
                COUNT(o.id) as order_count,
                SUM(o.total) as total_spent,
                MAX(o.created_at) as last_order
            FROM users u
            JOIN orders o ON u.id = o.user_id
            WHERE DATE(o.created_at) BETWEEN ? AND ?
            GROUP BY u.id
            ORDER BY total_spent DESC
            LIMIT 20
        ");
        $stmt->execute([$startDate, $endDate]);
        $topCustomers = $stmt->fetchAll();

        // Customer acquisition over time
        $stmt = $db->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM users
            WHERE role = 'customer'
            AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $stmt->execute([$startDate, $endDate]);
        $customerGrowth = $stmt->fetchAll();

        // Repeat vs new customers
        $stmt = $db->prepare("
            SELECT
                CASE WHEN order_count > 1 THEN 'Repeat' ELSE 'New' END as type,
                COUNT(*) as count
            FROM (
                SELECT user_id, COUNT(*) as order_count
                FROM orders
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY user_id
            ) as customer_orders
            GROUP BY type
        ");
        $stmt->execute([$startDate, $endDate]);
        $customerTypes = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/reports/customers', [
            'title' => 'Customer Report',
            'newCustomers' => $newCustomers,
            'activeCustomers' => $activeCustomers,
            'topCustomers' => $topCustomers,
            'customerGrowth' => $customerGrowth,
            'customerTypes' => $customerTypes,
            'dateRange' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ]);
    }

    /**
     * Export report data as CSV
     */
    public function export(): void
    {
        $type = $_GET['type'] ?? 'orders';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $db = Database::getInstance();

        switch ($type) {
            case 'orders':
                $stmt = $db->prepare("
                    SELECT
                        order_number, status, payment_status, total, discount_amount,
                        shipping_amount, tax_amount, customer_email, shipping_method,
                        created_at
                    FROM orders
                    WHERE DATE(created_at) BETWEEN ? AND ?
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$startDate, $endDate]);
                $data = $stmt->fetchAll();
                $filename = "orders_{$startDate}_to_{$endDate}.csv";
                break;

            case 'products':
                $stmt = $db->prepare("
                    SELECT
                        p.sku, p.name, p.price, p.stock_quantity, p.sold_count, p.view_count,
                        c.name as category
                    FROM products p
                    LEFT JOIN product_categories pc ON p.id = pc.product_id AND pc.is_primary = 1
                    LEFT JOIN categories c ON pc.category_id = c.id
                    ORDER BY p.sold_count DESC
                ");
                $stmt->execute();
                $data = $stmt->fetchAll();
                $filename = "products_{$startDate}_to_{$endDate}.csv";
                break;

            case 'customers':
                $stmt = $db->prepare("
                    SELECT
                        u.email, u.first_name, u.last_name, u.phone,
                        COUNT(o.id) as order_count,
                        COALESCE(SUM(o.total), 0) as total_spent,
                        u.created_at as registered
                    FROM users u
                    LEFT JOIN orders o ON u.id = o.user_id
                    WHERE u.role = 'customer'
                    GROUP BY u.id
                    ORDER BY total_spent DESC
                ");
                $stmt->execute();
                $data = $stmt->fetchAll();
                $filename = "customers_{$startDate}_to_{$endDate}.csv";
                break;

            default:
                flash('error', 'Invalid export type.');
                $this->redirect('/admin/reports');
                return;
        }

        // Output CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Header row
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }

        // Data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    private function getOverviewStats(\PDO $db, string $startDate, string $endDate): array
    {
        // Total revenue
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(total), 0)
            FROM orders
            WHERE payment_status = 'paid'
            AND DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $revenue = $stmt->fetchColumn();

        // Total orders
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM orders
            WHERE DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $orders = $stmt->fetchColumn();

        // Average order value
        $aov = $orders > 0 ? $revenue / $orders : 0;

        // Total customers
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT user_id)
            FROM orders
            WHERE DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $customers = $stmt->fetchColumn();

        // Items sold
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(oi.quantity), 0)
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE DATE(o.created_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $itemsSold = $stmt->fetchColumn();

        // Compare to previous period
        $daysDiff = (strtotime($endDate) - strtotime($startDate)) / 86400;
        $prevStartDate = date('Y-m-d', strtotime($startDate) - $daysDiff * 86400);
        $prevEndDate = date('Y-m-d', strtotime($startDate) - 86400);

        $stmt = $db->prepare("
            SELECT COALESCE(SUM(total), 0)
            FROM orders
            WHERE payment_status = 'paid'
            AND DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$prevStartDate, $prevEndDate]);
        $prevRevenue = $stmt->fetchColumn();

        $revenueGrowth = $prevRevenue > 0 ? (($revenue - $prevRevenue) / $prevRevenue) * 100 : 0;

        return [
            'revenue' => (float) $revenue,
            'orders' => (int) $orders,
            'aov' => (float) $aov,
            'customers' => (int) $customers,
            'items_sold' => (int) $itemsSold,
            'revenue_growth' => round($revenueGrowth, 1),
        ];
    }

    private function getRevenueChart(\PDO $db, string $startDate, string $endDate): array
    {
        $stmt = $db->prepare("
            SELECT
                DATE(created_at) as date,
                COUNT(*) as orders,
                SUM(total) as revenue
            FROM orders
            WHERE payment_status = 'paid'
            AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function getTopProducts(\PDO $db, string $startDate, string $endDate, int $limit): array
    {
        $stmt = $db->prepare("
            SELECT
                p.id, p.name, p.sku, p.price,
                SUM(oi.quantity) as quantity_sold,
                SUM(oi.total) as revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE DATE(o.created_at) BETWEEN ? AND ?
            AND o.payment_status = 'paid'
            GROUP BY p.id
            ORDER BY revenue DESC
            LIMIT {$limit}
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function getTopCategories(\PDO $db, string $startDate, string $endDate, int $limit): array
    {
        $stmt = $db->prepare("
            SELECT
                c.id, c.name,
                SUM(oi.quantity) as quantity_sold,
                SUM(oi.total) as revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            JOIN product_categories pc ON p.id = pc.product_id AND pc.is_primary = 1
            JOIN categories c ON pc.category_id = c.id
            WHERE DATE(o.created_at) BETWEEN ? AND ?
            AND o.payment_status = 'paid'
            GROUP BY c.id
            ORDER BY revenue DESC
            LIMIT {$limit}
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function getSalesData(\PDO $db, string $startDate, string $endDate, string $groupBy): array
    {
        $dateFormat = match ($groupBy) {
            'week' => "DATE_FORMAT(created_at, '%Y-%u')",
            'month' => "DATE_FORMAT(created_at, '%Y-%m')",
            default => 'DATE(created_at)',
        };

        $stmt = $db->prepare("
            SELECT
                {$dateFormat} as period,
                COUNT(*) as orders,
                SUM(total) as revenue,
                SUM(discount_amount) as discounts,
                SUM(shipping_amount) as shipping,
                SUM(tax_amount) as tax
            FROM orders
            WHERE payment_status = 'paid'
            AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY period
            ORDER BY period
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function getAOVData(\PDO $db, string $startDate, string $endDate, string $groupBy): array
    {
        $dateFormat = match ($groupBy) {
            'week' => "DATE_FORMAT(created_at, '%Y-%u')",
            'month' => "DATE_FORMAT(created_at, '%Y-%m')",
            default => 'DATE(created_at)',
        };

        $stmt = $db->prepare("
            SELECT
                {$dateFormat} as period,
                AVG(total) as aov
            FROM orders
            WHERE payment_status = 'paid'
            AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY period
            ORDER BY period
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        $viewPath = ADMIN_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View '$view' not found at '$viewPath'");
        }
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        if (isset($this->data['_layout'])) {
            $layoutPath = ADMIN_PATH . '/Views/layouts/' . $this->data['_layout'] . '.php';
            if (file_exists($layoutPath)) {
                include $layoutPath;
                return;
            }
        }
        echo $content;
    }

    protected function layout(string $layout): self
    {
        $this->data['_layout'] = $layout;
        return $this;
    }
}
