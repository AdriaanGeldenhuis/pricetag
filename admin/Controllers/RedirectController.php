<?php
/**
 * Admin Redirect Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class RedirectController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();

        $stmt = $db->query("SELECT * FROM redirects ORDER BY created_at DESC");
        $redirects = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/redirects/index', [
            'page_title' => 'URL Redirects',
            'active_page' => 'redirects',
            'redirects' => $redirects,
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        $fromUrl = trim($_POST['from_url'] ?? '');
        $toUrl = trim($_POST['to_url'] ?? '');

        if (!$fromUrl || !$toUrl) {
            flash('error', 'Both URLs are required');
            $this->redirect('/admin/redirects');
            return;
        }

        // Ensure from_url starts with /
        if (!str_starts_with($fromUrl, '/')) {
            $fromUrl = '/' . $fromUrl;
        }

        $stmt = $db->prepare("
            INSERT INTO redirects (from_url, to_url, status_code, is_active)
            VALUES (?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE to_url = ?, status_code = ?, updated_at = NOW()
        ");

        $statusCode = (int)($_POST['status_code'] ?? 301);
        $stmt->execute([$fromUrl, $toUrl, $statusCode, $toUrl, $statusCode]);

        flash('success', 'Redirect saved successfully');
        $this->redirect('/admin/redirects');
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM redirects WHERE id = ?");
        $stmt->execute([$id]);

        flash('success', 'Redirect deleted successfully');
        $this->redirect('/admin/redirects');
    }

    public function toggle(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false]);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE redirects SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);

        $stmt = $db->prepare("SELECT is_active FROM redirects WHERE id = ?");
        $stmt->execute([$id]);
        $redirect = $stmt->fetch();

        $this->json(['success' => true, 'is_active' => (bool)$redirect['is_active']]);
    }

    private function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
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
