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

        try {
            $stmt = $db->query("SELECT * FROM redirects ORDER BY created_at DESC");
            $redirects = $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Table might not exist - create it
            $db->exec("
                CREATE TABLE IF NOT EXISTS `redirects` (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `from_url` VARCHAR(500) NOT NULL,
                    `to_url` VARCHAR(500) NOT NULL,
                    `status_code` SMALLINT NOT NULL DEFAULT 301,
                    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                    `hit_count` INT UNSIGNED NOT NULL DEFAULT 0,
                    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `from_url` (`from_url`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            $redirects = [];
        }

        $this->layout('admin');
        $this->view('pages/redirects/index', [
            'page_title' => 'URL Redirects',
            'active_page' => 'redirects',
            'redirects' => $redirects,
        ]);
    }

    public function create(): void
    {
        $this->layout('admin');
        $this->view('pages/redirects/form', [
            'page_title' => 'Add Redirect',
            'active_page' => 'redirects',
            'redirect' => null,
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
            $this->redirect('/admin/redirects/create');
            return;
        }

        // Ensure from_url starts with /
        if (!str_starts_with($fromUrl, '/')) {
            $fromUrl = '/' . $fromUrl;
        }

        $stmt = $db->prepare("
            INSERT INTO redirects (from_url, to_url, status_code, is_active)
            VALUES (?, ?, ?, ?)
        ");

        $statusCode = (int)($_POST['status_code'] ?? 301);
        $isActive = !empty($_POST['is_active']) ? 1 : 0;
        $stmt->execute([$fromUrl, $toUrl, $statusCode, $isActive]);

        flash('success', 'Redirect created successfully');
        $this->redirect('/admin/redirects');
    }

    public function edit(int $id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM redirects WHERE id = ?");
        $stmt->execute([$id]);
        $redirect = $stmt->fetch();

        if (!$redirect) {
            flash('error', 'Redirect not found');
            $this->redirect('/admin/redirects');
            return;
        }

        $this->layout('admin');
        $this->view('pages/redirects/form', [
            'page_title' => 'Edit Redirect',
            'active_page' => 'redirects',
            'redirect' => $redirect,
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        $fromUrl = trim($_POST['from_url'] ?? '');
        $toUrl = trim($_POST['to_url'] ?? '');

        if (!$fromUrl || !$toUrl) {
            flash('error', 'Both URLs are required');
            $this->redirect('/admin/redirects/' . $id . '/edit');
            return;
        }

        // Ensure from_url starts with /
        if (!str_starts_with($fromUrl, '/')) {
            $fromUrl = '/' . $fromUrl;
        }

        $stmt = $db->prepare("
            UPDATE redirects SET from_url = ?, to_url = ?, status_code = ?, is_active = ?
            WHERE id = ?
        ");

        $statusCode = (int)($_POST['status_code'] ?? 301);
        $isActive = !empty($_POST['is_active']) ? 1 : 0;
        $stmt->execute([$fromUrl, $toUrl, $statusCode, $isActive, $id]);

        flash('success', 'Redirect updated successfully');
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
