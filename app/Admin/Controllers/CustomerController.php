<?php
declare(strict_types=1);

/**
 * Admin Customer Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;

class CustomerController extends Controller
{
    public function index(): void
    {
        $db = db();

        // Get filter parameters
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'created_at';
        $order = $_GET['order'] ?? 'desc';

        // Validate sort column
        $allowedSorts = ['created_at', 'first_name', 'email', 'order_count', 'total_spent'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        // Build query
        $where = ["u.role = 'customer'"];
        $params = [];

        if ($search) {
            $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $params = array_merge($params, ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"]);
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countSql = "SELECT COUNT(*) FROM users u WHERE {$whereClause}";
        $total = (int) $db->query($countSql, $params)->fetchColumn();
        $totalPages = (int) ceil($total / $perPage);

        // Get customers
        $offset = ($page - 1) * $perPage;

        $orderByClause = match ($sort) {
            'order_count' => 'order_count',
            'total_spent' => 'total_spent',
            default => "u.{$sort}",
        };

        $sql = "
            SELECT u.*,
                   (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
                   (SELECT COALESCE(SUM(total), 0) FROM orders WHERE user_id = u.id AND payment_status = 'paid') as total_spent,
                   (SELECT MAX(created_at) FROM orders WHERE user_id = u.id) as last_order_at
            FROM users u
            WHERE {$whereClause}
            ORDER BY {$orderByClause} {$order}
            LIMIT {$perPage} OFFSET {$offset}
        ";
        $customers = $db->query($sql, $params)->fetchAll();

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/customers/index', [
            'title' => 'Customers',
            'customers' => $customers,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'count' => $total,
                'perPage' => $perPage,
            ],
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'order' => strtolower($order),
            ],
        ]);
    }

    public function show(int $id): void
    {
        $db = db();

        $customer = $db->query("
            SELECT u.*,
                   (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
                   (SELECT COALESCE(SUM(total), 0) FROM orders WHERE user_id = u.id AND payment_status = 'paid') as total_spent,
                   (SELECT AVG(total) FROM orders WHERE user_id = u.id AND payment_status = 'paid') as avg_order_value
            FROM users u
            WHERE u.id = ? AND u.role = 'customer'
        ", [$id])->fetch();

        if (!$customer) {
            flash('error', 'Customer not found.');
            $this->redirect('/admin/customers');
            return;
        }

        // Get recent orders
        $orders = $db->query("
            SELECT * FROM orders
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ", [$id])->fetchAll();

        // Get addresses
        $addresses = $db->query("
            SELECT * FROM user_addresses
            WHERE user_id = ?
            ORDER BY is_default DESC, created_at DESC
        ", [$id])->fetchAll();

        // Get wishlist
        $wishlist = $db->query("
            SELECT w.*, p.name, p.price, p.slug,
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
            FROM wishlist w
            JOIN products p ON w.product_id = p.id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
            LIMIT 10
        ", [$id])->fetchAll();

        // Get activity log
        $activity = $db->query("
            SELECT * FROM user_activity_log
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 20
        ", [$id])->fetchAll();

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/customers/show', [
            'title' => $customer['first_name'] . ' ' . $customer['last_name'],
            'customer' => $customer,
            'orders' => $orders,
            'addresses' => $addresses,
            'wishlist' => $wishlist,
            'activity' => $activity,
        ]);
    }

    public function edit(int $id): void
    {
        $db = db();

        $customer = $db->query("
            SELECT * FROM users WHERE id = ? AND role = 'customer'
        ", [$id])->fetch();

        if (!$customer) {
            flash('error', 'Customer not found.');
            $this->redirect('/admin/customers');
            return;
        }

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/customers/edit', [
            'title' => 'Edit Customer',
            'customer' => $customer,
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        $customer = $db->query("SELECT * FROM users WHERE id = ? AND role = 'customer'", [$id])->fetch();

        if (!$customer) {
            flash('error', 'Customer not found.');
            $this->redirect('/admin/customers');
            return;
        }

        $validation = $this->validate([
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please correct the errors below.');
            $_SESSION['form_errors'] = $validation['errors'];
            $this->redirect('/admin/customers/' . $id . '/edit');
            return;
        }

        // Check email uniqueness
        $existing = $db->query(
            "SELECT id FROM users WHERE email = ? AND id != ?",
            [$_POST['email'], $id]
        )->fetch();

        if ($existing) {
            flash('error', 'A user with this email already exists.');
            $this->redirect('/admin/customers/' . $id . '/edit');
            return;
        }

        // Update customer
        $sql = "
            UPDATE users SET
                first_name = ?,
                last_name = ?,
                email = ?,
                phone = ?,
                updated_at = NOW()
            WHERE id = ?
        ";

        $db->query($sql, [
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone'] ?? null,
            $id,
        ]);

        // Update password if provided
        if (!empty($_POST['password'])) {
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $db->query("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $id]);
        }

        // Handle account status
        if (isset($_POST['is_active'])) {
            $db->query("UPDATE users SET is_active = ? WHERE id = ?", [1, $id]);
        } else {
            $db->query("UPDATE users SET is_active = ? WHERE id = ?", [0, $id]);
        }

        flash('success', 'Customer updated successfully.');
        $this->redirect('/admin/customers/' . $id);
    }
}
