<?php
declare(strict_types=1);

/**
 * Admin Settings Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;

class SettingsController extends Controller
{
    public function index(): void
    {
        $db = db();

        // Get all settings
        $settings = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(\PDO::FETCH_KEY_PAIR);

        // Group settings by category
        $groupedSettings = [
            'general' => [
                'site_name' => $settings['site_name'] ?? config('app.name'),
                'site_tagline' => $settings['site_tagline'] ?? '',
                'site_description' => $settings['site_description'] ?? '',
                'contact_email' => $settings['contact_email'] ?? '',
                'contact_phone' => $settings['contact_phone'] ?? '',
                'contact_address' => $settings['contact_address'] ?? '',
            ],
            'store' => [
                'currency' => $settings['currency'] ?? 'ZAR',
                'currency_symbol' => $settings['currency_symbol'] ?? 'R',
                'tax_rate' => $settings['tax_rate'] ?? '15',
                'tax_included' => $settings['tax_included'] ?? '1',
                'low_stock_threshold' => $settings['low_stock_threshold'] ?? '10',
            ],
            'shipping' => [
                'shipping_default_rate' => $settings['shipping_default_rate'] ?? '75',
                'shipping_express_rate' => $settings['shipping_express_rate'] ?? '150',
                'shipping_free_threshold' => $settings['shipping_free_threshold'] ?? '500',
                'shipping_estimate_days' => $settings['shipping_estimate_days'] ?? '3-5',
            ],
            'payment' => [
                'yoco_enabled' => $settings['yoco_enabled'] ?? '1',
                'eft_enabled' => $settings['eft_enabled'] ?? '1',
                'eft_bank_name' => $settings['eft_bank_name'] ?? '',
                'eft_account_name' => $settings['eft_account_name'] ?? '',
                'eft_account_number' => $settings['eft_account_number'] ?? '',
                'eft_branch_code' => $settings['eft_branch_code'] ?? '',
            ],
            'email' => [
                'mail_from_name' => $settings['mail_from_name'] ?? config('app.name'),
                'mail_from_address' => $settings['mail_from_address'] ?? '',
                'order_notification_email' => $settings['order_notification_email'] ?? '',
            ],
            'social' => [
                'facebook_url' => $settings['facebook_url'] ?? '',
                'instagram_url' => $settings['instagram_url'] ?? '',
                'twitter_url' => $settings['twitter_url'] ?? '',
                'youtube_url' => $settings['youtube_url'] ?? '',
                'tiktok_url' => $settings['tiktok_url'] ?? '',
            ],
        ];

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/settings/index', [
            'title' => 'Settings',
            'settings' => $groupedSettings,
        ]);
    }

    public function update(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        // Define allowed settings
        $allowedSettings = [
            // General
            'site_name', 'site_tagline', 'site_description',
            'contact_email', 'contact_phone', 'contact_address',
            // Store
            'currency', 'currency_symbol', 'tax_rate', 'tax_included', 'low_stock_threshold',
            // Shipping
            'shipping_default_rate', 'shipping_express_rate', 'shipping_free_threshold', 'shipping_estimate_days',
            // Payment
            'yoco_enabled', 'eft_enabled', 'eft_bank_name', 'eft_account_name', 'eft_account_number', 'eft_branch_code',
            // Email
            'mail_from_name', 'mail_from_address', 'order_notification_email',
            // Social
            'facebook_url', 'instagram_url', 'twitter_url', 'youtube_url', 'tiktok_url',
        ];

        $updated = 0;

        foreach ($_POST as $key => $value) {
            if ($key === 'csrf_token' || !in_array($key, $allowedSettings)) {
                continue;
            }

            $value = trim((string) $value);

            // Check if setting exists
            $existing = $db->query("SELECT id FROM settings WHERE setting_key = ?", [$key])->fetch();

            if ($existing) {
                $db->query(
                    "UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?",
                    [$value, $key]
                );
            } else {
                $db->query(
                    "INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())",
                    [$key, $value]
                );
            }

            $updated++;
        }

        // Handle logo upload
        if (!empty($_FILES['site_logo']['name']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            $logoPath = $this->uploadLogo($_FILES['site_logo']);
            if ($logoPath) {
                $this->saveSetting($db, 'site_logo', $logoPath);
            }
        }

        // Handle favicon upload
        if (!empty($_FILES['site_favicon']['name']) && $_FILES['site_favicon']['error'] === UPLOAD_ERR_OK) {
            $faviconPath = $this->uploadFavicon($_FILES['site_favicon']);
            if ($faviconPath) {
                $this->saveSetting($db, 'site_favicon', $faviconPath);
            }
        }

        // Clear settings cache if using caching
        if (function_exists('apcu_delete')) {
            apcu_delete('site_settings');
        }

        flash('success', 'Settings updated successfully.');
        $this->redirect('/admin/settings');
    }

    private function saveSetting(\PDO $db, string $key, string $value): void
    {
        $existing = $db->query("SELECT id FROM settings WHERE setting_key = ?", [$key])->fetch();

        if ($existing) {
            $db->query(
                "UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?",
                [$value, $key]
            );
        } else {
            $db->query(
                "INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())",
                [$key, $value]
            );
        }
    }

    private function uploadLogo(array $file): ?string
    {
        $uploadDir = PUBLIC_PATH . '/uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'svg', 'webp'])) {
            return null;
        }

        $filename = 'logo.' . $ext;
        $path = $uploadDir . $filename;

        // Delete existing logos
        foreach (glob($uploadDir . 'logo.*') as $oldLogo) {
            unlink($oldLogo);
        }

        if (move_uploaded_file($file['tmp_name'], $path)) {
            return '/uploads/' . $filename;
        }

        return null;
    }

    private function uploadFavicon(array $file): ?string
    {
        $uploadDir = PUBLIC_PATH . '/uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['ico', 'png', 'svg'])) {
            return null;
        }

        $filename = 'favicon.' . $ext;
        $path = $uploadDir . $filename;

        // Delete existing favicons
        foreach (glob($uploadDir . 'favicon.*') as $oldFavicon) {
            unlink($oldFavicon);
        }

        if (move_uploaded_file($file['tmp_name'], $path)) {
            return '/uploads/' . $filename;
        }

        return null;
    }
}
