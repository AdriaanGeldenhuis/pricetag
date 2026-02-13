<?php
/**
 * Admin Order Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Order;

class OrderController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(o.order_number LIKE ? OR u.email LIKE ? OR o.billing_first_name LIKE ? OR o.billing_last_name LIKE ?)";
            $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
        }

        if ($status) {
            $where[] = "o.status = ?";
            $params[] = $status;
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $stmt = $db->prepare("SELECT COUNT(*) FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE $whereClause");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        // Get orders
        $offset = ($page - 1) * $perPage;
        $stmt = $db->prepare("
            SELECT o.*, u.email, u.first_name, u.last_name
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
            WHERE $whereClause
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/orders/index', [
            'page_title' => 'Orders',
            'active_page' => 'orders',
            'orders' => $orders,
            'search' => $search,
            'status' => $status,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function show(string $id): void
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT o.*, u.email, u.first_name, u.last_name, u.phone as user_phone
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
            WHERE o.id = ?
        ");
        $stmt->execute([(int) $id]);
        $order = $stmt->fetch();

        if (!$order) {
            flash('error', 'Order not found');
            $this->redirect('/admin/orders');
            return;
        }

        // Get order items
        $stmt = $db->prepare("
            SELECT oi.*, p.name as product_name, p.sku, p.slug
            FROM order_items oi
            LEFT JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([(int) $id]);
        $items = $stmt->fetchAll();

        // Get payments
        $stmt = $db->prepare("
            SELECT * FROM payments WHERE order_id = ? ORDER BY created_at DESC
        ");
        $stmt->execute([(int) $id]);
        $payments = $stmt->fetchAll();

        // Get order status history
        $stmt = $db->prepare("
            SELECT oh.*, u.first_name, u.last_name
            FROM order_status_history oh
            LEFT JOIN users u ON u.id = oh.created_by
            WHERE oh.order_id = ?
            ORDER BY oh.created_at DESC
        ");
        $stmt->execute([(int) $id]);
        $statusHistory = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/orders/show', [
            'page_title' => 'Order #' . $order['order_number'],
            'active_page' => 'orders',
            'order' => $order,
            'items' => $items,
            'payments' => $payments,
            'statusHistory' => $statusHistory,
        ]);
    }

    public function updateStatus(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $newStatus = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? '';

        $validStatuses = ['pending', 'processing', 'paid', 'shipped', 'delivered', 'cancelled', 'refunded'];
        if (!in_array($newStatus, $validStatuses)) {
            flash('error', 'Invalid status');
            $this->redirect('/admin/orders/' . $id);
            return;
        }

        // Get current status
        $stmt = $db->prepare("SELECT status FROM orders WHERE id = ?");
        $stmt->execute([(int) $id]);
        $oldStatus = $stmt->fetchColumn();

        if (!$oldStatus) {
            flash('error', 'Order not found');
            $this->redirect('/admin/orders');
            return;
        }

        // Update order status
        $stmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, (int) $id]);

        // Log status change
        $stmt = $db->prepare("
            INSERT INTO order_status_history (order_id, status, comment, created_by, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([(int) $id, $newStatus, $notes, auth()->id ?? null]);

        flash('success', 'Order status updated');
        $this->redirect('/admin/orders/' . $id);
    }

    public function addNote(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $note = trim($_POST['note'] ?? '');
        if (empty($note)) {
            flash('error', 'Note cannot be empty');
            $this->redirect('/admin/orders/' . $id);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO order_notes (order_id, note, created_by, is_customer_visible, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            (int) $id,
            $note,
            auth()->id ?? null,
            !empty($_POST['customer_visible']) ? 1 : 0
        ]);

        flash('success', 'Note added');
        $this->redirect('/admin/orders/' . $id);
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
