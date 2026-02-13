<?php
/**
 * Admin Settings Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class SettingsController extends Controller
{
    public function index(): void
    {
        $settings = $this->getSettings();

        $this->layout('admin');
        $this->view('pages/settings/index', [
            'page_title' => 'Settings',
            'active_page' => 'settings',
            'settings' => $settings,
        ]);
    }

    public function branding(): void
    {
        $settings = $this->getSettings();

        $this->layout('admin');
        $this->view('pages/settings/branding', [
            'page_title' => 'Logo & Branding',
            'active_page' => 'branding',
            'settings' => $settings,
        ]);
    }

    public function appearance(): void
    {
        $settings = $this->getSettings();

        $this->layout('admin');
        $this->view('pages/settings/appearance', [
            'page_title' => 'Appearance Settings',
            'active_page' => 'appearance',
            'settings' => $settings,
        ]);
    }

    public function updateAppearance(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        $settingsToUpdate = [
            // Announcement Bar
            'announcement_enabled' => !empty($_POST['announcement_enabled']) ? '1' : '0',
            'announcement_text' => $_POST['announcement_text'] ?? '',
            'announcement_link' => $_POST['announcement_link'] ?? '',
            'announcement_bg_color' => $_POST['announcement_bg_color'] ?? '#1e40af',
            'announcement_text_color' => $_POST['announcement_text_color'] ?? '#ffffff',
            // WhatsApp Widget
            'whatsapp_enabled' => !empty($_POST['whatsapp_enabled']) ? '1' : '0',
            'whatsapp_number' => $_POST['whatsapp_number'] ?? '',
            'whatsapp_message' => $_POST['whatsapp_message'] ?? '',
            'whatsapp_position' => $_POST['whatsapp_position'] ?? 'bottom-right',
            // Trust Badges
            'trust_badges_enabled' => !empty($_POST['trust_badges_enabled']) ? '1' : '0',
            'trust_badge_1_title' => $_POST['trust_badge_1_title'] ?? 'Free Delivery',
            'trust_badge_1_subtitle' => $_POST['trust_badge_1_subtitle'] ?? 'Orders over R500',
            'trust_badge_2_title' => $_POST['trust_badge_2_title'] ?? 'Secure Payment',
            'trust_badge_2_subtitle' => $_POST['trust_badge_2_subtitle'] ?? '100% Protected',
            'trust_badge_3_title' => $_POST['trust_badge_3_title'] ?? 'Quality Products',
            'trust_badge_3_subtitle' => $_POST['trust_badge_3_subtitle'] ?? 'Best brands only',
            'trust_badge_4_title' => $_POST['trust_badge_4_title'] ?? '24/7 Support',
            'trust_badge_4_subtitle' => $_POST['trust_badge_4_subtitle'] ?? 'Always here to help',
            // Footer
            'footer_description' => $_POST['footer_description'] ?? '',
            'footer_email' => $_POST['footer_email'] ?? '',
            'footer_phone' => $_POST['footer_phone'] ?? '',
            'footer_hours' => $_POST['footer_hours'] ?? '',
            'footer_copyright' => $_POST['footer_copyright'] ?? '',
            // Cookie Consent
            'cookie_consent_enabled' => !empty($_POST['cookie_consent_enabled']) ? '1' : '0',
            'cookie_consent_text' => $_POST['cookie_consent_text'] ?? '',
            'cookie_policy_link' => $_POST['cookie_policy_link'] ?? '/page/privacy',
            // Back to Top
            'back_to_top_enabled' => !empty($_POST['back_to_top_enabled']) ? '1' : '0',
        ];

        foreach ($settingsToUpdate as $key => $value) {
            $stmt = $db->prepare("
                INSERT INTO settings (`group`, `key`, `value`) VALUES ('appearance', ?, ?)
                ON DUPLICATE KEY UPDATE `value` = ?
            ");
            $stmt->execute([$key, $value, $value]);
        }

        flash('success', 'Appearance settings updated successfully');
        $this->redirect('/admin/appearance');
    }

    public function updateBranding(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        // Use document root for correct server path (works on shared hosting)
        $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $uploadDir = $docRoot . '/uploads/branding/';

        // Ensure upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $settingsToUpdate = [
            'primary_color' => $_POST['primary_color'] ?? '#2563eb',
            'secondary_color' => $_POST['secondary_color'] ?? '#1e40af',
            'accent_color' => $_POST['accent_color'] ?? '#f59e0b',
            'font_family' => $_POST['font_family'] ?? 'Inter',
            'logo_height' => $_POST['logo_height'] ?? '50',
        ];

        $uploadFailed = false;

        // Handle logo upload
        if (!empty($_FILES['logo']['name'])) {
            $logoPath = $this->uploadImage($_FILES['logo'], $uploadDir, 'logo');
            if ($logoPath) {
                $settingsToUpdate['logo'] = $logoPath;
            } else {
                $uploadFailed = true;
            }
        } elseif (!empty($_POST['remove_logo'])) {
            $settingsToUpdate['logo'] = '';
        }

        // Handle favicon upload
        if (!empty($_FILES['favicon']['name'])) {
            $faviconPath = $this->uploadImage($_FILES['favicon'], $uploadDir, 'favicon');
            if ($faviconPath) {
                $settingsToUpdate['favicon'] = $faviconPath;
            } else {
                $uploadFailed = true;
            }
        } elseif (!empty($_POST['remove_favicon'])) {
            $settingsToUpdate['favicon'] = '';
        }

        // Handle header background image
        if (!empty($_FILES['header_bg']['name'])) {
            $headerBgPath = $this->uploadImage($_FILES['header_bg'], $uploadDir, 'header_bg');
            if ($headerBgPath) {
                $settingsToUpdate['header_bg'] = $headerBgPath;
            } else {
                $uploadFailed = true;
            }
        } elseif (!empty($_POST['remove_header_bg'])) {
            $settingsToUpdate['header_bg'] = '';
        }

        // Only save settings that didn't fail
        foreach ($settingsToUpdate as $key => $value) {
            $stmt = $db->prepare("
                INSERT INTO settings (`group`, `key`, `value`) VALUES ('branding', ?, ?)
                ON DUPLICATE KEY UPDATE `value` = ?
            ");
            $stmt->execute([$key, $value, $value]);
        }

        if (!$uploadFailed) {
            flash('success', 'Branding settings updated successfully');
        }
        $this->redirect('/admin/branding');
    }

    private function uploadImage(array $file, string $uploadDir, string $prefix): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            ];
            flash('error', $errors[$file['error']] ?? 'Unknown upload error');
            return null;
        }

        if (!in_array($file['type'], $allowedTypes)) {
            flash('error', 'Invalid file type. Allowed: JPG, PNG, GIF, WebP, SVG, ICO');
            return null;
        }

        if ($file['size'] > $maxSize) {
            flash('error', 'File too large. Maximum size: 5MB');
            return null;
        }

        // Ensure directory exists and is writable
        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0755, true)) {
                flash('error', 'Failed to create upload directory');
                return null;
            }
        }

        if (!is_writable($uploadDir)) {
            flash('error', 'Upload directory is not writable');
            return null;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $prefix . '_' . time() . '.' . $extension;
        $destination = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            flash('error', 'Failed to move uploaded file');
            return null;
        }

        // Verify file exists after upload
        if (!file_exists($destination)) {
            flash('error', 'File upload verification failed');
            return null;
        }

        return '/uploads/branding/' . $filename;
    }

    public function update(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $section = $_POST['section'] ?? 'general';

        $settingsToUpdate = [];

        switch ($section) {
            case 'general':
                $settingsToUpdate = [
                    'site_name' => $_POST['site_name'] ?? '',
                    'site_tagline' => $_POST['site_tagline'] ?? '',
                    'site_email' => $_POST['site_email'] ?? '',
                    'site_phone' => $_POST['site_phone'] ?? '',
                    'site_address' => $_POST['site_address'] ?? '',
                ];
                break;

            case 'invoice':
                $settingsToUpdate = [
                    'invoice_company_name' => $_POST['invoice_company_name'] ?? '',
                    'invoice_address' => $_POST['invoice_address'] ?? '',
                    'invoice_email' => $_POST['invoice_email'] ?? '',
                    'invoice_phone' => $_POST['invoice_phone'] ?? '',
                    'invoice_website' => $_POST['invoice_website'] ?? '',
                    'invoice_vat_number' => $_POST['invoice_vat_number'] ?? '',
                    'invoice_reg_number' => $_POST['invoice_reg_number'] ?? '',
                    'invoice_bank_name' => $_POST['invoice_bank_name'] ?? '',
                    'invoice_bank_account' => $_POST['invoice_bank_account'] ?? '',
                    'invoice_bank_branch' => $_POST['invoice_bank_branch'] ?? '',
                    'invoice_bank_type' => $_POST['invoice_bank_type'] ?? '',
                    'invoice_payment_terms' => $_POST['invoice_payment_terms'] ?? 'due_on_receipt',
                    'invoice_notes' => $_POST['invoice_notes'] ?? '',
                    'invoice_footer_text' => $_POST['invoice_footer_text'] ?? '',
                ];
                break;

            case 'store':
                $settingsToUpdate = [
                    'currency_code' => $_POST['currency_code'] ?? 'ZAR',
                    'currency_symbol' => $_POST['currency_symbol'] ?? 'R',
                    'tax_rate' => $_POST['tax_rate'] ?? '15',
                    'tax_included' => !empty($_POST['tax_included']) ? '1' : '0',
                    'free_shipping_threshold' => $_POST['free_shipping_threshold'] ?? '0',
                    'default_shipping_cost' => $_POST['default_shipping_cost'] ?? '0',
                ];
                break;

            case 'payment':
                $settingsToUpdate = [
                    'yoco_public_key' => $_POST['yoco_public_key'] ?? '',
                    'yoco_secret_key' => $_POST['yoco_secret_key'] ?? '',
                    'yoco_test_mode' => !empty($_POST['yoco_test_mode']) ? '1' : '0',
                    'cod_enabled' => !empty($_POST['cod_enabled']) ? '1' : '0',
                    'eft_enabled' => !empty($_POST['eft_enabled']) ? '1' : '0',
                    'eft_bank_name' => $_POST['eft_bank_name'] ?? '',
                    'eft_account_name' => $_POST['eft_account_name'] ?? '',
                    'eft_account_number' => $_POST['eft_account_number'] ?? '',
                    'eft_branch_code' => $_POST['eft_branch_code'] ?? '',
                ];
                break;

            case 'social':
                $settingsToUpdate = [
                    'social_facebook' => $_POST['social_facebook'] ?? '',
                    'social_instagram' => $_POST['social_instagram'] ?? '',
                    'social_twitter' => $_POST['social_twitter'] ?? '',
                    'social_linkedin' => $_POST['social_linkedin'] ?? '',
                    'social_youtube' => $_POST['social_youtube'] ?? '',
                ];
                break;

            case 'seo':
                $settingsToUpdate = [
                    'meta_title' => $_POST['meta_title'] ?? '',
                    'meta_description' => $_POST['meta_description'] ?? '',
                    'google_analytics' => $_POST['google_analytics'] ?? '',
                    'google_tag_manager' => $_POST['google_tag_manager'] ?? '',
                    'facebook_pixel' => $_POST['facebook_pixel'] ?? '',
                ];
                break;

            case 'email':
                $settingsToUpdate = [
                    'smtp_host' => $_POST['smtp_host'] ?? '',
                    'smtp_port' => $_POST['smtp_port'] ?? '587',
                    'smtp_username' => $_POST['smtp_username'] ?? '',
                    'smtp_password' => $_POST['smtp_password'] ?? '',
                    'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls',
                    'mail_from_address' => $_POST['mail_from_address'] ?? '',
                    'mail_from_name' => $_POST['mail_from_name'] ?? '',
                ];
                break;
        }

        foreach ($settingsToUpdate as $key => $value) {
            $stmt = $db->prepare("
                INSERT INTO settings (`group`, `key`, `value`) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE `value` = ?
            ");
            $stmt->execute([$section, $key, $value, $value]);
        }

        flash('success', 'Settings updated successfully');
        $this->redirect('/admin/settings?section=' . $section);
    }

    private function getSettings(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT `key`, `value` FROM settings");
        $rows = $stmt->fetchAll();

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }

        return $settings;
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
