<?php
/**
 * Admin Homepage Builder Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HomepageController extends Controller
{
    private array $sectionTypes = [
        'hero' => [
            'name' => 'Hero Slider',
            'icon' => 'image',
            'description' => 'Full-width hero slider with banners',
            'configurable' => false,
        ],
        'trust_badges' => [
            'name' => 'Trust Badges',
            'icon' => 'shield',
            'description' => 'Display trust indicators (free shipping, secure payment, etc.)',
            'configurable' => true,
        ],
        'featured_categories' => [
            'name' => 'Featured Categories',
            'icon' => 'grid',
            'description' => 'Display category grid',
            'configurable' => true,
        ],
        'trending_products' => [
            'name' => 'Trending Products',
            'icon' => 'trending-up',
            'description' => 'Best-selling or most viewed products',
            'configurable' => true,
        ],
        'banner' => [
            'name' => 'Promotional Banner',
            'icon' => 'image',
            'description' => 'Mid-page promotional banner',
            'configurable' => false,
        ],
        'new_arrivals' => [
            'name' => 'New Arrivals',
            'icon' => 'package',
            'description' => 'Recently added products',
            'configurable' => true,
        ],
        'testimonials' => [
            'name' => 'Testimonials',
            'icon' => 'message-circle',
            'description' => 'Customer reviews and testimonials',
            'configurable' => true,
        ],
        'newsletter' => [
            'name' => 'Newsletter Signup',
            'icon' => 'mail',
            'description' => 'Email subscription form',
            'configurable' => true,
        ],
        'brands' => [
            'name' => 'Brand Logos',
            'icon' => 'award',
            'description' => 'Showcase partner or brand logos',
            'configurable' => true,
        ],
        'custom_html' => [
            'name' => 'Custom HTML',
            'icon' => 'code',
            'description' => 'Custom HTML/CSS content block',
            'configurable' => true,
        ],
    ];

    public function index(): void
    {
        $db = Database::getInstance();

        $stmt = $db->query("SELECT * FROM home_sections ORDER BY sort_order ASC");
        $sections = $stmt->fetchAll();

        // Parse JSON config
        foreach ($sections as &$section) {
            $section['config'] = json_decode($section['config'] ?? '{}', true) ?: [];
        }

        $this->layout('admin');
        $this->view('pages/homepage/index', [
            'page_title' => 'Homepage Builder',
            'active_page' => 'homepage',
            'sections' => $sections,
            'sectionTypes' => $this->sectionTypes,
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $type = $_POST['type'] ?? '';

        if (!isset($this->sectionTypes[$type])) {
            flash('error', 'Invalid section type');
            $this->redirect('/admin/homepage');
            return;
        }

        // Get max sort order
        $stmt = $db->query("SELECT MAX(sort_order) as max_order FROM home_sections");
        $maxOrder = $stmt->fetch()['max_order'] ?? 0;

        $stmt = $db->prepare("
            INSERT INTO home_sections (type, title, subtitle, config, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
        ");

        $stmt->execute([
            $type,
            $this->sectionTypes[$type]['name'],
            '',
            '{}',
            $maxOrder + 1,
        ]);

        flash('success', 'Section added successfully');
        $this->redirect('/admin/homepage');
    }

    public function edit(int $id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM home_sections WHERE id = ?");
        $stmt->execute([$id]);
        $section = $stmt->fetch();

        if (!$section) {
            flash('error', 'Section not found');
            $this->redirect('/admin/homepage');
            return;
        }

        $section['config'] = json_decode($section['config'] ?? '{}', true) ?: [];

        // Get categories for featured_categories section
        $categories = [];
        if ($section['type'] === 'featured_categories') {
            $stmt = $db->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name");
            $categories = $stmt->fetchAll();
        }

        $this->layout('admin');
        $this->view('pages/homepage/edit', [
            'page_title' => 'Edit Section',
            'active_page' => 'homepage',
            'section' => $section,
            'sectionTypes' => $this->sectionTypes,
            'categories' => $categories,
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM home_sections WHERE id = ?");
        $stmt->execute([$id]);
        $section = $stmt->fetch();

        if (!$section) {
            flash('error', 'Section not found');
            $this->redirect('/admin/homepage');
            return;
        }

        // Build config based on section type
        $config = $this->buildConfig($section['type'], $_POST);

        $stmt = $db->prepare("
            UPDATE home_sections SET
                title = ?, subtitle = ?, config = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['title'] ?? '',
            $_POST['subtitle'] ?? '',
            json_encode($config),
            !empty($_POST['is_active']) ? 1 : 0,
            $id,
        ]);

        flash('success', 'Section updated successfully');
        $this->redirect('/admin/homepage');
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM home_sections WHERE id = ?");
        $stmt->execute([$id]);

        flash('success', 'Section deleted successfully');
        $this->redirect('/admin/homepage');
    }

    public function reorder(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $items = $data['items'] ?? [];

        if (empty($items)) {
            $this->json(['success' => false, 'message' => 'No items provided']);
            return;
        }

        $db = Database::getInstance();

        foreach ($items as $item) {
            $stmt = $db->prepare("UPDATE home_sections SET sort_order = ? WHERE id = ?");
            $stmt->execute([(int)$item['order'], (int)$item['id']]);
        }

        $this->json(['success' => true]);
    }

    public function toggle(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $db = Database::getInstance();

        $stmt = $db->prepare("UPDATE home_sections SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);

        $stmt = $db->prepare("SELECT is_active FROM home_sections WHERE id = ?");
        $stmt->execute([$id]);
        $section = $stmt->fetch();

        $this->json(['success' => true, 'is_active' => (bool)$section['is_active']]);
    }

    private function buildConfig(string $type, array $post): array
    {
        $config = [];

        switch ($type) {
            case 'trust_badges':
                $badges = [];
                if (!empty($post['badge_icons'])) {
                    foreach ($post['badge_icons'] as $i => $icon) {
                        if (!empty($icon) || !empty($post['badge_titles'][$i])) {
                            $badges[] = [
                                'icon' => $icon,
                                'title' => $post['badge_titles'][$i] ?? '',
                                'description' => $post['badge_descriptions'][$i] ?? '',
                            ];
                        }
                    }
                }
                $config['badges'] = $badges;
                break;

            case 'featured_categories':
                $config['columns'] = (int)($post['columns'] ?? 6);
                $config['category_ids'] = array_filter(array_map('intval', $post['category_ids'] ?? []));
                $config['show_product_count'] = !empty($post['show_product_count']);
                break;

            case 'trending_products':
            case 'new_arrivals':
                $config['limit'] = (int)($post['limit'] ?? 8);
                $config['columns'] = (int)($post['columns'] ?? 4);
                break;

            case 'testimonials':
                $testimonials = [];
                if (!empty($post['testimonial_names'])) {
                    foreach ($post['testimonial_names'] as $i => $name) {
                        if (!empty($name) || !empty($post['testimonial_contents'][$i])) {
                            $testimonials[] = [
                                'name' => $name,
                                'title' => $post['testimonial_titles'][$i] ?? '',
                                'content' => $post['testimonial_contents'][$i] ?? '',
                                'rating' => (int)($post['testimonial_ratings'][$i] ?? 5),
                            ];
                        }
                    }
                }
                $config['testimonials'] = $testimonials;
                break;

            case 'newsletter':
                $config['show_name_field'] = !empty($post['show_name_field']);
                $config['button_text'] = $post['button_text'] ?? 'Subscribe';
                $config['placeholder'] = $post['placeholder'] ?? 'Enter your email';
                break;

            case 'brands':
                $brands = [];
                if (!empty($post['brand_names'])) {
                    foreach ($post['brand_names'] as $i => $name) {
                        if (!empty($name)) {
                            $brands[] = [
                                'name' => $name,
                                'logo' => $post['brand_logos'][$i] ?? '',
                                'url' => $post['brand_urls'][$i] ?? '',
                            ];
                        }
                    }
                }
                $config['brands'] = $brands;
                break;

            case 'custom_html':
                $config['html'] = $post['custom_html'] ?? '';
                $config['css'] = $post['custom_css'] ?? '';
                break;
        }

        return $config;
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
