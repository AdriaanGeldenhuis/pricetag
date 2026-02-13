<?php
/**
 * Admin Coupon Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class CouponController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();

        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        $sql = "SELECT * FROM coupons WHERE 1=1";
        $params = [];

        if ($status === 'active') {
            $sql .= " AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW())";
        } elseif ($status === 'expired') {
            $sql .= " AND expires_at IS NOT NULL AND expires_at <= NOW()";
        } elseif ($status === 'inactive') {
            $sql .= " AND is_active = 0";
        }

        if ($search) {
            $sql .= " AND code LIKE ?";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $coupons = $stmt->fetchAll();

        // Get stats
        $stats = [
            'total' => count($coupons),
            'active' => 0,
            'used_today' => 0,
            'total_savings' => 0,
        ];

        foreach ($coupons as $coupon) {
            if ($coupon['is_active'] && (!$coupon['expires_at'] || strtotime($coupon['expires_at']) > time())) {
                $stats['active']++;
            }
        }

        // Get today's usage
        $stmt = $db->query("SELECT COUNT(*) as count, SUM(discount_amount) as total FROM coupon_usage WHERE DATE(created_at) = CURDATE()");
        $usage = $stmt->fetch();
        $stats['used_today'] = $usage['count'] ?? 0;
        $stats['total_savings'] = $usage['total'] ?? 0;

        $this->layout('admin');
        $this->view('pages/coupons/index', [
            'page_title' => 'Coupons',
            'active_page' => 'coupons',
            'coupons' => $coupons,
            'stats' => $stats,
            'filters' => ['status' => $status, 'search' => $search],
        ]);
    }

    public function create(): void
    {
        $this->layout('admin');
        $this->view('pages/coupons/form', [
            'page_title' => 'Create Coupon',
            'active_page' => 'coupons',
            'coupon' => null,
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        // Validate unique code
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $stmt = $db->prepare("SELECT id FROM coupons WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            flash('error', 'A coupon with this code already exists');
            $this->redirect('/admin/coupons/create');
            return;
        }

        $stmt = $db->prepare("
            INSERT INTO coupons (code, type, value, minimum_amount, maximum_discount, usage_limit, usage_per_user, starts_at, expires_at, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $code,
            $_POST['type'] ?? 'percentage',
            (float)($_POST['value'] ?? 0),
            !empty($_POST['minimum_amount']) ? (float)$_POST['minimum_amount'] : null,
            !empty($_POST['maximum_discount']) ? (float)$_POST['maximum_discount'] : null,
            !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null,
            !empty($_POST['usage_per_user']) ? (int)$_POST['usage_per_user'] : 1,
            !empty($_POST['starts_at']) ? $_POST['starts_at'] : null,
            !empty($_POST['expires_at']) ? $_POST['expires_at'] : null,
            !empty($_POST['is_active']) ? 1 : 0,
        ]);

        flash('success', 'Coupon created successfully');
        $this->redirect('/admin/coupons');
    }

    public function edit(int $id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            flash('error', 'Coupon not found');
            $this->redirect('/admin/coupons');
            return;
        }

        // Get usage history
        $stmt = $db->prepare("
            SELECT cu.*, u.email, u.first_name, u.last_name, o.order_number
            FROM coupon_usage cu
            LEFT JOIN users u ON cu.user_id = u.id
            LEFT JOIN orders o ON cu.order_id = o.id
            WHERE cu.coupon_id = ?
            ORDER BY cu.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$id]);
        $usage = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/coupons/form', [
            'page_title' => 'Edit Coupon',
            'active_page' => 'coupons',
            'coupon' => $coupon,
            'usage' => $usage,
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            flash('error', 'Coupon not found');
            $this->redirect('/admin/coupons');
            return;
        }

        // Check if code changed and is unique
        $code = strtoupper(trim($_POST['code'] ?? ''));
        if ($code !== $coupon['code']) {
            $stmt = $db->prepare("SELECT id FROM coupons WHERE code = ? AND id != ?");
            $stmt->execute([$code, $id]);
            if ($stmt->fetch()) {
                flash('error', 'A coupon with this code already exists');
                $this->redirect('/admin/coupons/' . $id . '/edit');
                return;
            }
        }

        $stmt = $db->prepare("
            UPDATE coupons SET
                code = ?, type = ?, value = ?, minimum_amount = ?, maximum_discount = ?,
                usage_limit = ?, usage_per_user = ?, starts_at = ?, expires_at = ?, is_active = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $code,
            $_POST['type'] ?? 'percentage',
            (float)($_POST['value'] ?? 0),
            !empty($_POST['minimum_amount']) ? (float)$_POST['minimum_amount'] : null,
            !empty($_POST['maximum_discount']) ? (float)$_POST['maximum_discount'] : null,
            !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null,
            !empty($_POST['usage_per_user']) ? (int)$_POST['usage_per_user'] : 1,
            !empty($_POST['starts_at']) ? $_POST['starts_at'] : null,
            !empty($_POST['expires_at']) ? $_POST['expires_at'] : null,
            !empty($_POST['is_active']) ? 1 : 0,
            $id,
        ]);

        flash('success', 'Coupon updated successfully');
        $this->redirect('/admin/coupons');
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->execute([$id]);

        flash('success', 'Coupon deleted successfully');
        $this->redirect('/admin/coupons');
    }

    public function toggle(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $db = Database::getInstance();

        $stmt = $db->prepare("UPDATE coupons SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);

        $stmt = $db->prepare("SELECT is_active FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        $coupon = $stmt->fetch();

        $this->json(['success' => true, 'is_active' => (bool)$coupon['is_active']]);
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
