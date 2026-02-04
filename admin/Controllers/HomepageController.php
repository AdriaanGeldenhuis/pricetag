<?php
/**
 * Admin Homepage Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Handles homepage section management with all configuration options.
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HomepageController extends Controller
{
    /**
     * Display all sections
     */
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

    /**
     * Store a new section
     */
    public function store(): void
    {
        // Validate CSRF
        if (!$this->validateCsrf()) {
            flash('error', 'Invalid security token. Please try again.');
            $this->redirect('/admin/homepage');
            return;
        }

        $db = Database::getInstance();

        // Get next sort order
        $stmt = $db->query("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM home_sections");
        $nextOrder = $stmt->fetch()['next_order'] ?? 1;

        $stmt = $db->prepare("
            INSERT INTO home_sections (type, title, subtitle, config, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
        ");

        $type = $_POST['type'] ?? 'custom';
        $title = $_POST['title'] ?? $this->getDefaultTitle($type);

        $stmt->execute([
            $type,
            $title,
            null,
            json_encode($this->getDefaultConfig($type)),
            $nextOrder,
        ]);

        flash('success', 'Section added successfully');
        $this->redirect('/admin/homepage/' . $db->lastInsertId() . '/edit');
    }

    /**
     * Show edit form
     */
    public function edit($id): void
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

        // Decode JSON config if stored as string
        if (isset($section['config']) && is_string($section['config'])) {
            $section['config'] = json_decode($section['config'], true) ?: [];
        }

        // Fetch categories for category selector
        $categories = $this->getCategories();

        $this->layout('admin');
        $this->view('pages/homepage/edit', [
            'page_title' => 'Edit Section',
            'active_page' => 'homepage',
            'section' => $section,
            'sectionTypes' => $this->getSectionTypes(),
            'categories' => $categories,
        ]);
    }

    /**
     * Update a section
     */
    public function update($id): void
    {
        // Validate CSRF
        if (!$this->validateCsrf()) {
            flash('error', 'Invalid security token. Please try again.');
            $this->redirect('/admin/homepage/' . $id . '/edit');
            return;
        }

        $db = Database::getInstance();

        // Get existing section to know the type
        $stmt = $db->prepare("SELECT type FROM home_sections WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch();

        if (!$existing) {
            flash('error', 'Section not found');
            $this->redirect('/admin/homepage');
            return;
        }

        // Build config based on section type
        $config = $this->buildConfigFromPost($existing['type']);

        $stmt = $db->prepare("
            UPDATE home_sections
            SET title = ?, subtitle = ?, config = ?, is_active = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['title'] ?? '',
            $_POST['subtitle'] ?? null,
            json_encode($config),
            isset($_POST['is_active']) ? 1 : 0,
            $id,
        ]);

        flash('success', 'Section updated successfully');
        $this->redirect('/admin/homepage');
    }

    /**
     * Delete a section
     */
    public function destroy($id): void
    {
        // Validate CSRF
        if (!$this->validateCsrf()) {
            flash('error', 'Invalid security token. Please try again.');
            $this->redirect('/admin/homepage');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM home_sections WHERE id = ?");
        $stmt->execute([$id]);

        flash('success', 'Section deleted successfully');
        $this->redirect('/admin/homepage');
    }

    /**
     * Toggle section active status (AJAX)
     */
    public function toggle($id): void
    {
        // Validate CSRF for AJAX
        if (!$this->validateCsrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            return;
        }

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
            'message' => $section['is_active'] ? 'Section activated' : 'Section deactivated',
        ]);
    }

    /**
     * Reorder sections (AJAX)
     */
    public function reorder(): void
    {
        // Validate CSRF for AJAX
        if (!$this->validateCsrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            return;
        }

        $order = $_POST['order'] ?? [];
        if (empty($order) || !is_array($order)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid order data']);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE home_sections SET sort_order = ? WHERE id = ?");

        foreach ($order as $position => $id) {
            $stmt->execute([(int) $position, (int) $id]);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Order updated']);
    }

    /**
     * Build config array from POST data based on section type
     */
    private function buildConfigFromPost(string $type): array
    {
        $config = [];

        switch ($type) {
            case 'trust_badges':
                $icons = $_POST['badge_icons'] ?? [];
                $titles = $_POST['badge_titles'] ?? [];
                $descriptions = $_POST['badge_descriptions'] ?? [];

                $badges = [];
                foreach ($icons as $i => $icon) {
                    if (!empty($titles[$i]) || !empty($descriptions[$i])) {
                        $badges[] = [
                            'icon' => $icon,
                            'title' => $titles[$i] ?? '',
                            'description' => $descriptions[$i] ?? '',
                        ];
                    }
                }
                $config['badges'] = $badges;
                break;

            case 'featured_categories':
            case 'categories':
                $config['columns'] = (int) ($_POST['columns'] ?? 6);
                $config['show_product_count'] = !empty($_POST['show_product_count']);
                $config['category_ids'] = array_map('intval', $_POST['category_ids'] ?? []);
                break;

            case 'featured_products':
            case 'trending_products':
            case 'new_arrivals':
            case 'best_sellers':
                $config['limit'] = (int) ($_POST['limit'] ?? 8);
                $config['columns'] = (int) ($_POST['columns'] ?? 4);
                break;

            case 'testimonials':
                $names = $_POST['testimonial_names'] ?? [];
                $titles = $_POST['testimonial_titles'] ?? [];
                $contents = $_POST['testimonial_contents'] ?? [];
                $ratings = $_POST['testimonial_ratings'] ?? [];

                $testimonials = [];
                foreach ($names as $i => $name) {
                    if (!empty($name) || !empty($contents[$i])) {
                        $testimonials[] = [
                            'name' => $name,
                            'title' => $titles[$i] ?? '',
                            'content' => $contents[$i] ?? '',
                            'rating' => (int) ($ratings[$i] ?? 5),
                        ];
                    }
                }
                $config['testimonials'] = $testimonials;
                break;

            case 'newsletter':
                $config['show_name_field'] = !empty($_POST['show_name_field']);
                $config['button_text'] = $_POST['button_text'] ?? 'Subscribe';
                $config['placeholder'] = $_POST['placeholder'] ?? 'Enter your email';
                break;

            case 'custom_html':
            case 'custom':
                $config['html'] = $_POST['custom_html'] ?? '';
                $config['css'] = $_POST['custom_css'] ?? '';
                break;

            case 'hero':
            case 'hero_slider':
            case 'banner':
                // These sections use banners from the banner manager
                $config['banner_location'] = $type === 'hero' || $type === 'hero_slider' ? 'hero' : 'homepage_middle';
                break;
        }

        return $config;
    }

    /**
     * Get default config for a section type
     */
    private function getDefaultConfig(string $type): array
    {
        switch ($type) {
            case 'trust_badges':
                return [
                    'badges' => [
                        ['icon' => 'truck', 'title' => 'Free Shipping', 'description' => 'On orders over R500'],
                        ['icon' => 'shield', 'title' => 'Secure Payment', 'description' => '100% secure checkout'],
                        ['icon' => 'refresh-cw', 'title' => 'Easy Returns', 'description' => '30-day return policy'],
                        ['icon' => 'headphones', 'title' => '24/7 Support', 'description' => 'Here to help'],
                    ],
                ];

            case 'featured_categories':
            case 'categories':
                return ['columns' => 6, 'show_product_count' => true, 'category_ids' => []];

            case 'featured_products':
            case 'trending_products':
            case 'new_arrivals':
            case 'best_sellers':
                return ['limit' => 8, 'columns' => 4];

            case 'testimonials':
                return ['testimonials' => []];

            case 'newsletter':
                return ['show_name_field' => false, 'button_text' => 'Subscribe', 'placeholder' => 'Enter your email'];

            case 'custom_html':
            case 'custom':
                return ['html' => '', 'css' => ''];

            default:
                return [];
        }
    }

    /**
     * Get default title for a section type
     */
    private function getDefaultTitle(string $type): string
    {
        $types = $this->getSectionTypes();
        return $types[$type]['name'] ?? ucfirst(str_replace('_', ' ', $type));
    }

    /**
     * Get all categories for the selector
     */
    private function getCategories(): array
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT id, name, slug FROM categories WHERE parent_id IS NULL ORDER BY name ASC");
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Available section types
     */
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
            'featured_categories' => [
                'name' => 'Featured Categories',
                'description' => 'Manually select categories to feature',
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
            'trending_products' => [
                'name' => 'Trending Products',
                'description' => 'Display trending/popular products',
            ],
            'trust_badges' => [
                'name' => 'Trust Badges',
                'description' => 'Display trust indicators like free shipping, secure payment',
            ],
            'testimonials' => [
                'name' => 'Testimonials',
                'description' => 'Customer reviews and testimonials',
            ],
            'newsletter' => [
                'name' => 'Newsletter Signup',
                'description' => 'Email newsletter subscription form',
            ],
            'banner' => [
                'name' => 'Banner Section',
                'description' => 'Display promotional banner from Banner Manager',
            ],
            'custom_html' => [
                'name' => 'Custom HTML',
                'description' => 'Custom HTML content section',
            ],
            'custom' => [
                'name' => 'Custom Section',
                'description' => 'Legacy custom content section',
            ],
        ];
    }

    /**
     * Validate CSRF token
     */
    private function validateCsrf(): bool
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return !empty($token) && isset($_SESSION['_token']) && hash_equals($_SESSION['_token'], $token);
    }

    /**
     * Render view with admin layout
     */
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

    /**
     * Set layout
     */
    protected function layout(string $layout): self
    {
        $this->data['_layout'] = $layout;
        return $this;
    }
}
