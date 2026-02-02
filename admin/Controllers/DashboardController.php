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
    public function index(): void
    {
        $db = Database::getInstance();

        // Get stats for today and this month
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');

        // Orders today
        $stmt = $db->prepare("SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total FROM orders WHERE DATE(created_at) = ?");
        $stmt->execute([$today]);
        $ordersToday = $stmt->fetch();

        // Orders this month
        $stmt = $db->prepare("SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total FROM orders WHERE created_at >= ?");
        $stmt->execute([$monthStart]);
        $ordersMonth = $stmt->fetch();

        // Pending orders
        $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
        $pendingOrders = $stmt->fetchColumn();

        // Total products
        $stmt = $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
        $totalProducts = $stmt->fetchColumn();

        // Low stock products
        $stmt = $db->query("SELECT COUNT(*) FROM products WHERE manage_stock = 1 AND stock_quantity <= low_stock_threshold AND status = 'active'");
        $lowStockProducts = $stmt->fetchColumn();

        // Total users
        $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
        $totalUsers = $stmt->fetchColumn();

        // New users this month
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE created_at >= ? AND role = 'customer'");
        $stmt->execute([$monthStart]);
        $newUsersMonth = $stmt->fetchColumn();

        // Recent orders
        $stmt = $db->query("
            SELECT o.*, u.first_name, u.last_name
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
            ORDER BY o.created_at DESC
            LIMIT 10
        ");
        $recentOrders = $stmt->fetchAll();

        // Top products by sales
        $stmt = $db->query("
            SELECT p.name, p.sku, SUM(oi.quantity) as sold, SUM(oi.total) as revenue
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            JOIN orders o ON o.id = oi.order_id
            WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY oi.product_id
            ORDER BY sold DESC
            LIMIT 5
        ");
        $topProducts = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/dashboard', [
            'page_title' => 'Dashboard',
            'active_page' => 'dashboard',
            'stats' => [
                'orders_today' => $ordersToday,
                'orders_month' => $ordersMonth,
                'pending_orders' => $pendingOrders,
                'total_products' => $totalProducts,
                'low_stock' => $lowStockProducts,
                'total_users' => $totalUsers,
                'new_users_month' => $newUsersMonth,
            ],
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
        ]);
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
