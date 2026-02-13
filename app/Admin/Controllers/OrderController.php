<?php
declare(strict_types=1);

/**
 * Admin Order Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index(): void
    {
        $db = db();

        // Get filter parameters
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';

        // Build query
        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(o.order_number LIKE ? OR o.billing_email LIKE ? OR o.billing_first_name LIKE ? OR o.billing_last_name LIKE ?)";
            $params = array_merge($params, ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"]);
        }

        if ($status) {
            $where[] = 'o.status = ?';
            $params[] = $status;
        }

        if ($dateFrom) {
            $where[] = 'DATE(o.created_at) >= ?';
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $where[] = 'DATE(o.created_at) <= ?';
            $params[] = $dateTo;
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countSql = "SELECT COUNT(*) FROM orders o WHERE {$whereClause}";
        $total = (int) $db->query($countSql, $params)->fetchColumn();
        $totalPages = (int) ceil($total / $perPage);

        // Get orders
        $offset = ($page - 1) * $perPage;
        $sql = "
            SELECT o.*,
                   CONCAT(o.billing_first_name, ' ', o.billing_last_name) as customer_name,
                   u.email as user_email
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE {$whereClause}
            ORDER BY o.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        $orders = $db->query($sql, $params)->fetchAll();

        // Get status counts for filters
        $statusCounts = $db->query("
            SELECT status, COUNT(*) as count FROM orders GROUP BY status
        ")->fetchAll(\PDO::FETCH_KEY_PAIR);

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/orders/index', [
            'title' => 'Orders',
            'orders' => $orders,
            'statusCounts' => $statusCounts,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'count' => $total,
                'perPage' => $perPage,
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function show(int $id): void
    {
        $db = db();

        $order = $db->query("SELECT * FROM orders WHERE id = ?", [$id])->fetch();

        if (!$order) {
            flash('error', 'Order not found.');
            $this->redirect('/admin/orders');
            return;
        }

        // Get order items
        $items = $db->query("
            SELECT oi.*, p.slug as product_slug,
                   (SELECT image_url FROM product_images WHERE product_id = oi.product_id AND is_primary = 1 LIMIT 1) as image
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ", [$id])->fetchAll();

        // Get order notes
        $notes = $db->query("
            SELECT n.*, u.first_name, u.last_name
            FROM order_notes n
            LEFT JOIN users u ON n.user_id = u.id
            WHERE n.order_id = ?
            ORDER BY n.created_at DESC
        ", [$id])->fetchAll();

        // Get order timeline
        $timeline = $db->query("
            SELECT * FROM order_status_history
            WHERE order_id = ?
            ORDER BY created_at DESC
        ", [$id])->fetchAll();

        // Get customer info if registered
        $customer = null;
        if ($order['user_id']) {
            $customer = $db->query("
                SELECT u.*,
                       (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
                       (SELECT SUM(total) FROM orders WHERE user_id = u.id AND payment_status = 'paid') as total_spent
                FROM users u WHERE id = ?
            ", [$order['user_id']])->fetch();
        }

        // Get payments
        $payments = $db->query("
            SELECT * FROM payments WHERE order_id = ? ORDER BY created_at DESC
        ", [$id])->fetchAll();

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/orders/show', [
            'title' => 'Order #' . $order['order_number'],
            'order' => $order,
            'items' => $items,
            'notes' => $notes,
            'timeline' => $timeline,
            'customer' => $customer,
            'payments' => $payments,
        ]);
    }

    public function updateStatus(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        $order = $db->query("SELECT * FROM orders WHERE id = ?", [$id])->fetch();

        if (!$order) {
            if (isAjax()) {
                $this->json(['success' => false, 'message' => 'Order not found'], 404);
                return;
            }
            flash('error', 'Order not found.');
            $this->redirect('/admin/orders');
            return;
        }

        $newStatus = $_POST['status'] ?? '';
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

        if (!in_array($newStatus, $validStatuses)) {
            if (isAjax()) {
                $this->json(['success' => false, 'message' => 'Invalid status'], 400);
                return;
            }
            flash('error', 'Invalid status.');
            $this->redirect('/admin/orders/' . $id);
            return;
        }

        $oldStatus = $order['status'];

        // Update order status
        $db->query("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $id]);

        // Add to status history
        $db->query("
            INSERT INTO order_status_history (order_id, status, comment, created_by, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ", [
            $id,
            $newStatus,
            $_POST['note'] ?? null,
            $_SESSION['user_id'],
        ]);

        // Send notification email to customer
        if (!empty($_POST['notify_customer'])) {
            $this->sendStatusEmail($order, $newStatus);
        }

        // Add tracking info if shipped
        if ($newStatus === 'shipped' && !empty($_POST['tracking_number'])) {
            $db->query("
                UPDATE orders SET
                    tracking_number = ?,
                    tracking_carrier = ?,
                    shipped_at = NOW()
                WHERE id = ?
            ", [
                $_POST['tracking_number'],
                $_POST['tracking_carrier'] ?? null,
                $id,
            ]);
        }

        if (isAjax()) {
            $this->json(['success' => true, 'message' => 'Order status updated']);
            return;
        }

        flash('success', 'Order status updated to ' . ucfirst($newStatus));
        $this->redirect('/admin/orders/' . $id);
    }

    public function addNote(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        $order = $db->query("SELECT id FROM orders WHERE id = ?", [$id])->fetch();

        if (!$order) {
            $this->json(['success' => false, 'message' => 'Order not found'], 404);
            return;
        }

        $note = trim($_POST['note'] ?? '');

        if (empty($note)) {
            $this->json(['success' => false, 'message' => 'Note cannot be empty'], 400);
            return;
        }

        $isCustomerVisible = isset($_POST['customer_visible']) ? 1 : 0;

        $db->query("
            INSERT INTO order_notes (order_id, user_id, note, is_customer_visible, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ", [$id, $_SESSION['user_id'], $note, $isCustomerVisible]);

        $noteId = $db->lastInsertId();

        // Get the created note with user info
        $createdNote = $db->query("
            SELECT n.*, u.first_name, u.last_name
            FROM order_notes n
            LEFT JOIN users u ON n.user_id = u.id
            WHERE n.id = ?
        ", [$noteId])->fetch();

        if (isAjax()) {
            $this->json([
                'success' => true,
                'message' => 'Note added',
                'note' => [
                    'id' => $createdNote['id'],
                    'note' => $createdNote['note'],
                    'author' => $createdNote['first_name'] . ' ' . $createdNote['last_name'],
                    'is_customer_visible' => (bool) $createdNote['is_customer_visible'],
                    'created_at' => date('d M Y H:i', strtotime($createdNote['created_at'])),
                ],
            ]);
            return;
        }

        flash('success', 'Note added successfully.');
        $this->redirect('/admin/orders/' . $id);
    }

    private function sendStatusEmail(array $order, string $status): void
    {
        // In production, use proper email service
        $email = $order['billing_email'];
        $orderNumber = $order['order_number'];

        $subjects = [
            'processing' => "Your order #{$orderNumber} is being processed",
            'shipped' => "Your order #{$orderNumber} has been shipped",
            'delivered' => "Your order #{$orderNumber} has been delivered",
            'cancelled' => "Your order #{$orderNumber} has been cancelled",
            'refunded' => "Your order #{$orderNumber} has been refunded",
        ];

        $subject = $subjects[$status] ?? "Order #{$orderNumber} status update";

        // Log email for development
        logMessage('info', 'Order status email', [
            'to' => $email,
            'subject' => $subject,
            'status' => $status,
        ]);
    }
}
