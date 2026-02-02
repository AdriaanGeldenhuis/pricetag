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
                INSERT INTO settings (`key`, `value`) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE `value` = ?
            ");
            $stmt->execute([$key, $value, $value]);
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
