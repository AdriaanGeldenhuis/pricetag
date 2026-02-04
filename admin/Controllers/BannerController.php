<?php
/**
 * Admin Banner Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class BannerController extends Controller
{
    public function index(): void
    {
        $banners = [];

        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT * FROM banners ORDER BY location, sort_order ASC");
            $result = $stmt->fetchAll();
            if ($result) {
                $banners = $result;
            }
        } catch (\Throwable $e) {
            // Table may not exist or query failed
        }

        $this->layout('admin');
        $this->view('pages/banners/index', [
            'page_title' => 'Banners & Sliders',
            'active_page' => 'banners',
            'banners' => $banners,
        ]);
    }

    public function create(): void
    {
        $this->layout('admin');
        $this->view('pages/banners/form', [
            'page_title' => 'Add Banner',
            'active_page' => 'banners',
            'banner' => null,
            'locations' => $this->getBannerLocations(),
        ]);
    }

    public function store(): void
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("INSERT INTO banners (location, title, image, url, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['location'] ?? 'hero',
            $_POST['title'] ?? '',
            $_POST['image'] ?? '',
            $_POST['url'] ?? '',
            isset($_POST['is_active']) ? 1 : 0
        ]);

        flash('success', 'Banner created');
        $this->redirect('/admin/banners');
    }

    public function edit($id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $banner = $stmt->fetch();

        $this->layout('admin');
        $this->view('pages/banners/form', [
            'page_title' => 'Edit Banner',
            'active_page' => 'banners',
            'banner' => $banner,
            'locations' => $this->getBannerLocations(),
        ]);
    }

    public function update($id): void
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("UPDATE banners SET location=?, title=?, image=?, url=?, is_active=? WHERE id=?");
        $stmt->execute([
            $_POST['location'] ?? 'hero',
            $_POST['title'] ?? '',
            $_POST['image'] ?? '',
            $_POST['url'] ?? '',
            isset($_POST['is_active']) ? 1 : 0,
            $id
        ]);

        flash('success', 'Banner updated');
        $this->redirect('/admin/banners');
    }

    public function destroy($id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM banners WHERE id = ?");
        $stmt->execute([$id]);

        flash('success', 'Banner deleted');
        $this->redirect('/admin/banners');
    }

    /**
     * Toggle banner active status (AJAX)
     */
    public function toggle($id): void
    {
        $db = Database::getInstance();

        // Toggle status
        $stmt = $db->prepare("UPDATE banners SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);

        // Get new status
        $stmt = $db->prepare("SELECT is_active FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $banner = $stmt->fetch();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'is_active' => (bool) $banner['is_active'],
        ]);
    }

    /**
     * Reorder banners (AJAX)
     */
    public function reorder(): void
    {
        $order = $_POST['order'] ?? [];
        if (empty($order) || !is_array($order)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false]);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE banners SET sort_order = ? WHERE id = ?");

        foreach ($order as $position => $id) {
            $stmt->execute([(int) $position, (int) $id]);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    private function getBannerLocations(): array
    {
        return [
            'hero' => 'Hero Slider',
            'sidebar' => 'Sidebar',
            'homepage_top' => 'Homepage - Top',
            'homepage_middle' => 'Homepage - Middle',
            'homepage_bottom' => 'Homepage - Bottom',
            'category_top' => 'Category Page - Top',
            'product_sidebar' => 'Product Page - Sidebar',
        ];
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
