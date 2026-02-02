<?php
/**
 * Admin Customer Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;

class CustomerController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $search = $_GET['search'] ?? '';

        $where = ["role = 'customer'"];
        $params = [];

        if ($search) {
            $where[] = "(email LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR phone LIKE ?)";
            $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE $whereClause");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        // Get customers
        $offset = ($page - 1) * $perPage;
        $stmt = $db->prepare("
            SELECT u.*,
                   (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
                   (SELECT COALESCE(SUM(total), 0) FROM orders WHERE user_id = u.id AND payment_status = 'paid') as total_spent
            FROM users u
            WHERE $whereClause
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);
        $customers = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/customers/index', [
            'page_title' => 'Customers',
            'active_page' => 'customers',
            'customers' => $customers,
            'search' => $search,
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
            SELECT u.*,
                   (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
                   (SELECT COALESCE(SUM(total), 0) FROM orders WHERE user_id = u.id AND payment_status = 'paid') as total_spent
            FROM users u
            WHERE u.id = ?
        ");
        $stmt->execute([(int) $id]);
        $customer = $stmt->fetch();

        if (!$customer) {
            flash('error', 'Customer not found');
            $this->redirect('/admin/customers');
            return;
        }

        // Get orders
        $stmt = $db->prepare("
            SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 20
        ");
        $stmt->execute([(int) $id]);
        $orders = $stmt->fetchAll();

        // Get addresses
        $stmt = $db->prepare("
            SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC
        ");
        $stmt->execute([(int) $id]);
        $addresses = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/customers/show', [
            'page_title' => $customer['first_name'] . ' ' . $customer['last_name'],
            'active_page' => 'customers',
            'customer' => $customer,
            'orders' => $orders,
            'addresses' => $addresses,
        ]);
    }

    public function edit(string $id): void
    {
        $customer = User::find((int) $id);

        if (!$customer) {
            flash('error', 'Customer not found');
            $this->redirect('/admin/customers');
            return;
        }

        $this->layout('admin');
        $this->view('pages/customers/edit', [
            'page_title' => 'Edit Customer',
            'active_page' => 'customers',
            'customer' => $customer,
        ]);
    }

    public function update(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $customer = User::find((int) $id);

        if (!$customer) {
            flash('error', 'Customer not found');
            $this->redirect('/admin/customers');
            return;
        }

        $validation = $this->validate([
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please check your input');
            $this->redirect('/admin/customers/' . $id . '/edit');
            return;
        }

        // Check email uniqueness
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$_POST['email'], (int) $id]);
        if ($stmt->fetch()) {
            flash('error', 'Email already in use by another account');
            $this->redirect('/admin/customers/' . $id . '/edit');
            return;
        }

        $customer->first_name = $_POST['first_name'];
        $customer->last_name = $_POST['last_name'];
        $customer->email = $_POST['email'];
        $customer->phone = $_POST['phone'] ?? null;
        $customer->is_active = !empty($_POST['is_active']);

        if (!empty($_POST['password'])) {
            $customer->password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $customer->save();

        flash('success', 'Customer updated successfully');
        $this->redirect('/admin/customers/' . $id);
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
