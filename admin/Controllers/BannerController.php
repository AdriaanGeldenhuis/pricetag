<?php
/**
 * Admin Banner Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Handles banner CRUD operations with proper file upload handling.
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class BannerController extends Controller
{
    /**
     * Upload directory for banner images
     */
    private const UPLOAD_DIR = 'uploads/banners';

    /**
     * Allowed MIME types for images
     */
    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    /**
     * Maximum file size (10MB)
     */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /**
     * Display all banners
     */
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

    /**
     * Show create form
     */
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

    /**
     * Store new banner
     */
    public function store(): void
    {
        // Validate CSRF
        if (!$this->validateCsrf()) {
            flash('error', 'Invalid security token. Please try again.');
            $this->redirect('/admin/banners/create');
            return;
        }

        // Handle main image upload (required)
        $imagePath = $this->uploadImage($_FILES['image'] ?? null, 'banner');
        if (!$imagePath) {
            flash('error', 'Banner image is required. Please upload a valid image (JPG, PNG, WebP, GIF up to 10MB).');
            $this->redirect('/admin/banners/create');
            return;
        }

        // Handle mobile image upload (optional)
        $mobileImagePath = null;
        if (!empty($_FILES['mobile_image']['name'])) {
            $mobileImagePath = $this->uploadImage($_FILES['mobile_image'] ?? null, 'banner_mobile');
        }

        // Parse dates
        $startsAt = !empty($_POST['starts_at']) ? date('Y-m-d H:i:s', strtotime($_POST['starts_at'])) : null;
        $expiresAt = !empty($_POST['expires_at']) ? date('Y-m-d H:i:s', strtotime($_POST['expires_at'])) : null;

        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO banners (
                location, title, subtitle, image, mobile_image, url, button_text,
                text_color, overlay_opacity, sort_order, starts_at, expires_at, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $_POST['location'] ?? 'hero',
            $_POST['title'] ?? null,
            $_POST['subtitle'] ?? null,
            $imagePath,
            $mobileImagePath,
            $_POST['url'] ?? null,
            $_POST['button_text'] ?? null,
            $_POST['text_color'] ?? '#ffffff',
            (int) ($_POST['overlay_opacity'] ?? 0),
            (int) ($_POST['sort_order'] ?? 0),
            $startsAt,
            $expiresAt,
            isset($_POST['is_active']) ? 1 : 0,
        ]);

        flash('success', 'Banner created successfully');
        $this->redirect('/admin/banners');
    }

    /**
     * Show edit form
     */
    public function edit($id): void
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
            'locations' => $this->getBannerLocations(),
        ]);
    }

    /**
     * Update existing banner
     */
    public function update($id): void
    {
        // Validate CSRF
        if (!$this->validateCsrf()) {
            flash('error', 'Invalid security token. Please try again.');
            $this->redirect('/admin/banners/' . $id . '/edit');
            return;
        }

        $db = Database::getInstance();

        // Get existing banner
        $stmt = $db->prepare("SELECT * FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch();

        if (!$existing) {
            flash('error', 'Banner not found');
            $this->redirect('/admin/banners');
            return;
        }

        // Handle main image upload (keep existing if not changed)
        $imagePath = $existing['image'];
        if (!empty($_FILES['image']['name'])) {
            $newImagePath = $this->uploadImage($_FILES['image'] ?? null, 'banner');
            if ($newImagePath) {
                // Delete old image
                $this->deleteImage($existing['image']);
                $imagePath = $newImagePath;
            }
        }

        // Handle mobile image
        $mobileImagePath = $existing['mobile_image'];

        // Check if user wants to remove mobile image
        if (!empty($_POST['remove_mobile_image'])) {
            $this->deleteImage($existing['mobile_image']);
            $mobileImagePath = null;
        } elseif (!empty($_FILES['mobile_image']['name'])) {
            $newMobileImagePath = $this->uploadImage($_FILES['mobile_image'] ?? null, 'banner_mobile');
            if ($newMobileImagePath) {
                // Delete old mobile image
                $this->deleteImage($existing['mobile_image']);
                $mobileImagePath = $newMobileImagePath;
            }
        }

        // Parse dates
        $startsAt = !empty($_POST['starts_at']) ? date('Y-m-d H:i:s', strtotime($_POST['starts_at'])) : null;
        $expiresAt = !empty($_POST['expires_at']) ? date('Y-m-d H:i:s', strtotime($_POST['expires_at'])) : null;

        $stmt = $db->prepare("
            UPDATE banners SET
                location = ?, title = ?, subtitle = ?, image = ?, mobile_image = ?,
                url = ?, button_text = ?, text_color = ?, overlay_opacity = ?,
                sort_order = ?, starts_at = ?, expires_at = ?, is_active = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['location'] ?? 'hero',
            $_POST['title'] ?? null,
            $_POST['subtitle'] ?? null,
            $imagePath,
            $mobileImagePath,
            $_POST['url'] ?? null,
            $_POST['button_text'] ?? null,
            $_POST['text_color'] ?? '#ffffff',
            (int) ($_POST['overlay_opacity'] ?? 0),
            (int) ($_POST['sort_order'] ?? 0),
            $startsAt,
            $expiresAt,
            isset($_POST['is_active']) ? 1 : 0,
            $id,
        ]);

        flash('success', 'Banner updated successfully');
        $this->redirect('/admin/banners');
    }

    /**
     * Delete banner
     */
    public function destroy($id): void
    {
        // Validate CSRF
        if (!$this->validateCsrf()) {
            flash('error', 'Invalid security token. Please try again.');
            $this->redirect('/admin/banners');
            return;
        }

        $db = Database::getInstance();

        // Get banner to delete images
        $stmt = $db->prepare("SELECT image, mobile_image FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $banner = $stmt->fetch();

        if ($banner) {
            // Delete images
            $this->deleteImage($banner['image']);
            $this->deleteImage($banner['mobile_image']);

            // Delete from database
            $stmt = $db->prepare("DELETE FROM banners WHERE id = ?");
            $stmt->execute([$id]);

            flash('success', 'Banner deleted successfully');
        } else {
            flash('error', 'Banner not found');
        }

        $this->redirect('/admin/banners');
    }

    /**
     * Toggle banner active status (AJAX)
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
            'message' => $banner['is_active'] ? 'Banner activated' : 'Banner deactivated',
        ]);
    }

    /**
     * Reorder banners (AJAX)
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
        $stmt = $db->prepare("UPDATE banners SET sort_order = ? WHERE id = ?");

        foreach ($order as $position => $id) {
            $stmt->execute([(int) $position, (int) $id]);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Order updated']);
    }

    /**
     * Upload an image file
     */
    private function uploadImage(?array $file, string $prefix = 'banner'): ?string
    {
        if (!$file || empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Validate file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return null;
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset(self::ALLOWED_TYPES[$mimeType])) {
            return null;
        }

        $extension = self::ALLOWED_TYPES[$mimeType];

        // Generate unique filename
        $filename = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

        // Ensure upload directory exists
        $uploadPath = $this->getStoragePath() . '/' . self::UPLOAD_DIR;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fullPath = $uploadPath . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return null;
        }

        // Return relative path for database storage
        return '/' . self::UPLOAD_DIR . '/' . $filename;
    }

    /**
     * Delete an image file
     */
    private function deleteImage(?string $imagePath): bool
    {
        if (!$imagePath) {
            return false;
        }

        $fullPath = $this->getStoragePath() . $imagePath;

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    /**
     * Get storage path
     */
    private function getStoragePath(): string
    {
        // Check for STORAGE_PATH constant, fallback to public directory
        if (defined('STORAGE_PATH')) {
            return STORAGE_PATH;
        }

        return dirname(dirname(__DIR__)) . '/public';
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
     * Get available banner locations
     */
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
