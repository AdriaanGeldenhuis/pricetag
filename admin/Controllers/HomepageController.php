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
    private $sectionTypes = [
        'hero' => [
            'name' => 'Hero Slider',
            'description' => 'Full-width hero slider with banners',
            'configurable' => false,
        ],
        'trust_badges' => [
            'name' => 'Trust Badges',
            'description' => 'Display trust indicators (free shipping, secure payment, etc.)',
            'configurable' => true,
        ],
        'featured_categories' => [
            'name' => 'Featured Categories',
            'description' => 'Display category grid',
            'configurable' => true,
        ],
        'trending_products' => [
            'name' => 'Trending Products',
            'description' => 'Best-selling or most viewed products',
            'configurable' => true,
        ],
        'banner' => [
            'name' => 'Promotional Banner',
            'description' => 'Mid-page promotional banner',
            'configurable' => false,
        ],
        'new_arrivals' => [
            'name' => 'New Arrivals',
            'description' => 'Recently added products',
            'configurable' => true,
        ],
        'testimonials' => [
            'name' => 'Testimonials',
            'description' => 'Customer reviews and testimonials',
            'configurable' => true,
        ],
        'newsletter' => [
            'name' => 'Newsletter Signup',
            'description' => 'Email subscription form',
            'configurable' => true,
        ],
        'brands' => [
            'name' => 'Brand Logos',
            'description' => 'Showcase partner or brand logos',
            'configurable' => true,
        ],
        'custom_html' => [
            'name' => 'Custom HTML',
            'description' => 'Custom HTML/CSS content block',
            'configurable' => true,
        ],
    ];

    private $sectionIcons = [
        'hero' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>',
        'trust_badges' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
        'featured_categories' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
        'trending_products' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>',
        'banner' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>',
        'new_arrivals' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
        'testimonials' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path></svg>',
        'newsletter' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
        'brands' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>',
        'custom_html' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
    ];

    public function index(): void
    {
        $sections = [];

        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT * FROM home_sections ORDER BY sort_order ASC");
            $sections = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($sections as &$section) {
                $section['config'] = json_decode($section['config'] ?? '{}', true) ?: [];
            }
        } catch (\Throwable $e) {
            // Table may not exist yet - that's OK
        }

        $this->layout('admin');
        $this->view('pages/homepage/index', [
            'page_title' => 'Homepage Builder',
            'active_page' => 'homepage',
            'sections' => $sections,
            'sectionTypes' => $this->sectionTypes,
            'sectionIcons' => $this->sectionIcons,
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
            'sectionIcons' => $this->sectionIcons,
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
