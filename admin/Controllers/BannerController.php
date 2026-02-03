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
    private array $locations = [
        'hero' => 'Homepage Hero Slider',
        'homepage_top' => 'Homepage Top Banner',
        'homepage_middle' => 'Homepage Middle Banner',
        'homepage_bottom' => 'Homepage Bottom Banner',
        'category_top' => 'Category Page Top',
        'product_sidebar' => 'Product Page Sidebar',
        'cart_promo' => 'Cart Page Promo',
        'checkout_top' => 'Checkout Top Banner',
    ];

    public function index(): void
    {
        $banners = [];
        $grouped = [];
        $location = $_GET['location'] ?? '';

        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT * FROM banners ORDER BY location, sort_order ASC");
            $banners = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($banners as $banner) {
                $grouped[$banner['location']][] = $banner;
            }
        } catch (\Throwable $e) {
            // Table may not exist yet - that's OK
        }

        $this->layout('admin');
        $this->view('pages/banners/index', [
            'page_title' => 'Banners & Sliders',
            'active_page' => 'banners',
            'banners' => $banners,
            'grouped' => $grouped,
            'locations' => $this->locations,
            'currentLocation' => $location,
        ]);
    }

    public function create(): void
    {
        $this->layout('admin');
        $this->view('pages/banners/form', [
            'page_title' => 'Add Banner',
            'active_page' => 'banners',
            'banner' => null,
            'locations' => $this->locations,
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $uploadDir = PUBLIC_PATH . '/uploads/banners/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $imagePath = null;
        $mobileImagePath = null;

        // Handle main image upload
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = $this->uploadImage($_FILES['image'], $uploadDir, 'banner');
            if (!$imagePath) {
                $this->redirect('/admin/banners/create');
                return;
            }
        } else {
            flash('error', 'Banner image is required');
            $this->redirect('/admin/banners/create');
            return;
        }

        // Handle mobile image upload (optional)
        if (!empty($_FILES['mobile_image']['name']) && $_FILES['mobile_image']['error'] === UPLOAD_ERR_OK) {
            $mobileImagePath = $this->uploadImage($_FILES['mobile_image'], $uploadDir, 'banner_mobile');
        }

        $stmt = $db->prepare("
            INSERT INTO banners (location, title, subtitle, image, mobile_image, url, button_text, text_color, overlay_opacity, sort_order, starts_at, expires_at, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $_POST['location'] ?? 'hero',
            $_POST['title'] ?? '',
            $_POST['subtitle'] ?? '',
            $imagePath,
            $mobileImagePath,
            $_POST['url'] ?? '',
            $_POST['button_text'] ?? '',
            $_POST['text_color'] ?? '#ffffff',
            (int)($_POST['overlay_opacity'] ?? 0),
            (int)($_POST['sort_order'] ?? 0),
            !empty($_POST['starts_at']) ? $_POST['starts_at'] : null,
            !empty($_POST['expires_at']) ? $_POST['expires_at'] : null,
            !empty($_POST['is_active']) ? 1 : 0,
        ]);

        flash('success', 'Banner created successfully');
        $this->redirect('/admin/banners');
    }

    public function edit(int $id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $banner = $stmt->fetch();

        if (!$banner) {
            flash('error', 'Banner not found');
            $this->redirect('/admin/banners');
            return;
        }

        $this->layout('admin');
        $this->view('pages/banners/form', [
            'page_title' => 'Edit Banner',
            'active_page' => 'banners',
            'banner' => $banner,
            'locations' => $this->locations,
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $banner = $stmt->fetch();

        if (!$banner) {
            flash('error', 'Banner not found');
            $this->redirect('/admin/banners');
            return;
        }

        $uploadDir = PUBLIC_PATH . '/uploads/banners/';
        $imagePath = $banner['image'];
        $mobileImagePath = $banner['mobile_image'];

        // Handle main image upload
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImagePath = $this->uploadImage($_FILES['image'], $uploadDir, 'banner');
            if ($newImagePath) {
                // Delete old image
                if ($imagePath && file_exists(PUBLIC_PATH . $imagePath)) {
                    unlink(PUBLIC_PATH . $imagePath);
                }
                $imagePath = $newImagePath;
            }
        }

        // Handle mobile image upload
        if (!empty($_FILES['mobile_image']['name']) && $_FILES['mobile_image']['error'] === UPLOAD_ERR_OK) {
            $newMobileImagePath = $this->uploadImage($_FILES['mobile_image'], $uploadDir, 'banner_mobile');
            if ($newMobileImagePath) {
                // Delete old mobile image
                if ($mobileImagePath && file_exists(PUBLIC_PATH . $mobileImagePath)) {
                    unlink(PUBLIC_PATH . $mobileImagePath);
                }
                $mobileImagePath = $newMobileImagePath;
            }
        } elseif (!empty($_POST['remove_mobile_image'])) {
            if ($mobileImagePath && file_exists(PUBLIC_PATH . $mobileImagePath)) {
                unlink(PUBLIC_PATH . $mobileImagePath);
            }
            $mobileImagePath = null;
        }

        $stmt = $db->prepare("
            UPDATE banners SET
                location = ?, title = ?, subtitle = ?, image = ?, mobile_image = ?,
                url = ?, button_text = ?, text_color = ?, overlay_opacity = ?,
                sort_order = ?, starts_at = ?, expires_at = ?, is_active = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['location'] ?? 'hero',
            $_POST['title'] ?? '',
            $_POST['subtitle'] ?? '',
            $imagePath,
            $mobileImagePath,
            $_POST['url'] ?? '',
            $_POST['button_text'] ?? '',
            $_POST['text_color'] ?? '#ffffff',
            (int)($_POST['overlay_opacity'] ?? 0),
            (int)($_POST['sort_order'] ?? 0),
            !empty($_POST['starts_at']) ? $_POST['starts_at'] : null,
            !empty($_POST['expires_at']) ? $_POST['expires_at'] : null,
            !empty($_POST['is_active']) ? 1 : 0,
            $id,
        ]);

        flash('success', 'Banner updated successfully');
        $this->redirect('/admin/banners');
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT image, mobile_image FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $banner = $stmt->fetch();

        if ($banner) {
            // Delete images
            if ($banner['image'] && file_exists(PUBLIC_PATH . $banner['image'])) {
                unlink(PUBLIC_PATH . $banner['image']);
            }
            if ($banner['mobile_image'] && file_exists(PUBLIC_PATH . $banner['mobile_image'])) {
                unlink(PUBLIC_PATH . $banner['mobile_image']);
            }

            $stmt = $db->prepare("DELETE FROM banners WHERE id = ?");
            $stmt->execute([$id]);
        }

        flash('success', 'Banner deleted successfully');
        $this->redirect('/admin/banners');
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
            $stmt = $db->prepare("UPDATE banners SET sort_order = ? WHERE id = ?");
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

        $stmt = $db->prepare("UPDATE banners SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);

        $stmt = $db->prepare("SELECT is_active FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $banner = $stmt->fetch();

        $this->json(['success' => true, 'is_active' => (bool)$banner['is_active']]);
    }

    private function uploadImage(array $file, string $uploadDir, string $prefix): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($file['type'], $allowedTypes)) {
            flash('error', 'Invalid file type. Allowed: JPG, PNG, GIF, WebP');
            return null;
        }

        if ($file['size'] > $maxSize) {
            flash('error', 'File too large. Maximum size: 10MB');
            return null;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return '/uploads/banners/' . $filename;
        }

        flash('error', 'Failed to upload file');
        return null;
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
