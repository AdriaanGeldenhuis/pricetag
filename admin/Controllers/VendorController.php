<?php
/**
 * Admin Vendor Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class VendorController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();

        $status = $_GET['status'] ?? '';

        $sql = "SELECT v.*, COUNT(p.id) as product_count
                FROM vendors v
                LEFT JOIN products p ON v.id = p.vendor_id
                WHERE 1=1";
        $params = [];

        if ($status) {
            $sql .= " AND v.status = ?";
            $params[] = $status;
        }

        $sql .= " GROUP BY v.id ORDER BY v.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $vendors = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/vendors/index', [
            'page_title' => 'Vendors',
            'active_page' => 'vendors',
            'vendors' => $vendors,
            'filters' => ['status' => $status],
        ]);
    }

    public function create(): void
    {
        $this->layout('admin');
        $this->view('pages/vendors/form', [
            'page_title' => 'Add Vendor',
            'active_page' => 'vendors',
            'vendor' => null,
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        $name = trim($_POST['name'] ?? '');
        $slug = $this->generateSlug($name);

        $stmt = $db->prepare("
            INSERT INTO vendors (name, slug, description, api_endpoint, api_key, sync_enabled, commission_rate, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $name,
            $slug,
            $_POST['description'] ?? '',
            $_POST['api_endpoint'] ?? '',
            $_POST['api_key'] ?? '',
            !empty($_POST['sync_enabled']) ? 1 : 0,
            !empty($_POST['commission_rate']) ? (float)$_POST['commission_rate'] : null,
            $_POST['status'] ?? 'pending',
        ]);

        flash('success', 'Vendor created successfully');
        $this->redirect('/admin/vendors');
    }

    public function edit(int $id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM vendors WHERE id = ?");
        $stmt->execute([$id]);
        $vendor = $stmt->fetch();

        if (!$vendor) {
            flash('error', 'Vendor not found');
            $this->redirect('/admin/vendors');
            return;
        }

        $this->layout('admin');
        $this->view('pages/vendors/form', [
            'page_title' => 'Edit Vendor',
            'active_page' => 'vendors',
            'vendor' => $vendor,
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        $stmt = $db->prepare("
            UPDATE vendors SET
                name = ?, description = ?, api_endpoint = ?, api_key = ?,
                sync_enabled = ?, commission_rate = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['name'] ?? '',
            $_POST['description'] ?? '',
            $_POST['api_endpoint'] ?? '',
            $_POST['api_key'] ?? '',
            !empty($_POST['sync_enabled']) ? 1 : 0,
            !empty($_POST['commission_rate']) ? (float)$_POST['commission_rate'] : null,
            $_POST['status'] ?? 'pending',
            $id,
        ]);

        flash('success', 'Vendor updated successfully');
        $this->redirect('/admin/vendors');
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM vendors WHERE id = ?");
        $stmt->execute([$id]);

        flash('success', 'Vendor deleted successfully');
        $this->redirect('/admin/vendors');
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        $viewPath = ADMIN_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        if (isset($this->data['_layout'])) {
            include ADMIN_PATH . '/Views/layouts/' . $this->data['_layout'] . '.php';
            return;
        }
        echo $content;
    }

    protected function layout(string $layout): self
    {
        $this->data['_layout'] = $layout;
        return $this;
    }
}
