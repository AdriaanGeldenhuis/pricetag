<?php
/**
 * Admin Homepage Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HomepageController extends Controller
{
    public function index(): void
    {
        $sections = [];

        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT * FROM home_sections ORDER BY sort_order ASC");
            $result = $stmt->fetchAll();
            if ($result) {
                $sections = $result;
            }
        } catch (\Throwable $e) {
            // Table may not exist or query failed
        }

        $this->layout('admin');
        $this->view('pages/homepage/index', [
            'page_title' => 'Homepage Builder',
            'active_page' => 'homepage',
            'sections' => $sections,
        ]);
    }

    public function store(): void
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("INSERT INTO home_sections (type, title, sort_order, is_active) VALUES (?, ?, ?, 1)");
        $stmt->execute([
            $_POST['type'] ?? 'custom',
            $_POST['title'] ?? '',
            0
        ]);

        flash('success', 'Section added');
        $this->redirect('/admin/homepage');
    }

    public function edit($id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM home_sections WHERE id = ?");
        $stmt->execute([$id]);
        $section = $stmt->fetch();

        $this->layout('admin');
        $this->view('pages/homepage/edit', [
            'page_title' => 'Edit Section',
            'active_page' => 'homepage',
            'section' => $section,
        ]);
    }

    public function update($id): void
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("UPDATE home_sections SET title=?, is_active=? WHERE id=?");
        $stmt->execute([
            $_POST['title'] ?? '',
            isset($_POST['is_active']) ? 1 : 0,
            $id
        ]);

        flash('success', 'Section updated');
        $this->redirect('/admin/homepage');
    }

    public function destroy($id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM home_sections WHERE id = ?");
        $stmt->execute([$id]);

        flash('success', 'Section deleted');
        $this->redirect('/admin/homepage');
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
