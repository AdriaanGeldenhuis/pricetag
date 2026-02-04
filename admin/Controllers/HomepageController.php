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
            'sectionTypes' => $this->getSectionTypes(),
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

        // Decode JSON config if stored as string
        if ($section && isset($section['config']) && is_string($section['config'])) {
            $section['config'] = json_decode($section['config'], true) ?: [];
        }

        $this->layout('admin');
        $this->view('pages/homepage/edit', [
            'page_title' => 'Edit Section',
            'active_page' => 'homepage',
            'section' => $section,
            'sectionTypes' => $this->getSectionTypes(),
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

    /**
     * Toggle section active status (AJAX)
     */
    public function toggle($id): void
    {
        $db = Database::getInstance();

        // Toggle status
        $stmt = $db->prepare("UPDATE home_sections SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);

        // Get new status
        $stmt = $db->prepare("SELECT is_active FROM home_sections WHERE id = ?");
        $stmt->execute([$id]);
        $section = $stmt->fetch();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'is_active' => (bool) $section['is_active'],
        ]);
    }

    /**
     * Reorder sections (AJAX)
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
        $stmt = $db->prepare("UPDATE home_sections SET sort_order = ? WHERE id = ?");

        foreach ($order as $position => $id) {
            $stmt->execute([(int) $position, (int) $id]);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    private function getSectionTypes(): array
    {
        return [
            'hero_slider' => [
                'name' => 'Hero Slider',
                'description' => 'Main hero banner slider at the top of the homepage',
            ],
            'categories' => [
                'name' => 'Categories Grid',
                'description' => 'Display category cards in a grid layout',
            ],
            'featured_products' => [
                'name' => 'Featured Products',
                'description' => 'Showcase featured products',
            ],
            'new_arrivals' => [
                'name' => 'New Arrivals',
                'description' => 'Display newest products',
            ],
            'best_sellers' => [
                'name' => 'Best Sellers',
                'description' => 'Show top selling products',
            ],
            'trust_badges' => [
                'name' => 'Trust Badges',
                'description' => 'Display trust indicators like free shipping, secure payment',
            ],
            'newsletter' => [
                'name' => 'Newsletter Signup',
                'description' => 'Email newsletter subscription form',
            ],
            'custom' => [
                'name' => 'Custom Section',
                'description' => 'Custom HTML content section',
            ],
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
