<?php
declare(strict_types=1);

/**
 * Admin SEO Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;

class SeoController extends Controller
{
    public function index(): void
    {
        $db = db();

        // Get SEO settings
        $settings = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'seo_%'")->fetchAll(\PDO::FETCH_KEY_PAIR);

        // Get products without SEO descriptions
        $productsWithoutSeo = $db->query("
            SELECT id, name, slug, short_description
            FROM products
            WHERE (short_description IS NULL OR short_description = '')
            AND status = 1
            LIMIT 10
        ")->fetchAll();

        // Get pages without meta descriptions
        $pagesWithoutMeta = $db->query("
            SELECT id, title, slug
            FROM pages
            WHERE (meta_description IS NULL OR meta_description = '')
            AND status = 'published'
            LIMIT 10
        ")->fetchAll();

        // Get sitemap statistics
        $sitemapStats = [
            'products' => (int) $db->query("SELECT COUNT(*) FROM products WHERE status = 1")->fetchColumn(),
            'categories' => (int) $db->query("SELECT COUNT(*) FROM categories WHERE is_active = 1")->fetchColumn(),
            'pages' => (int) $db->query("SELECT COUNT(*) FROM pages WHERE status = 'published'")->fetchColumn(),
        ];

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/seo/index', [
            'title' => 'SEO Settings',
            'settings' => [
                'site_title' => $settings['seo_site_title'] ?? config('app.name'),
                'site_description' => $settings['seo_site_description'] ?? '',
                'site_keywords' => $settings['seo_site_keywords'] ?? '',
                'twitter_handle' => $settings['seo_twitter_handle'] ?? '',
                'facebook_app_id' => $settings['seo_facebook_app_id'] ?? '',
                'google_verification' => $settings['seo_google_verification'] ?? '',
                'bing_verification' => $settings['seo_bing_verification'] ?? '',
                'google_analytics' => $settings['seo_google_analytics'] ?? '',
                'gtm_id' => $settings['seo_gtm_id'] ?? '',
                'facebook_pixel' => $settings['seo_facebook_pixel'] ?? '',
                'robots_allow' => $settings['seo_robots_allow'] ?? '/,/products/,/categories/',
                'robots_disallow' => $settings['seo_robots_disallow'] ?? '/admin/,/cart/,/checkout/,/account/',
            ],
            'productsWithoutSeo' => $productsWithoutSeo,
            'pagesWithoutMeta' => $pagesWithoutMeta,
            'sitemapStats' => $sitemapStats,
        ]);
    }

    public function update(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        // Define allowed SEO settings
        $seoSettings = [
            'seo_site_title', 'seo_site_description', 'seo_site_keywords',
            'seo_twitter_handle', 'seo_facebook_app_id',
            'seo_google_verification', 'seo_bing_verification',
            'seo_google_analytics', 'seo_gtm_id', 'seo_facebook_pixel',
            'seo_robots_allow', 'seo_robots_disallow',
        ];

        foreach ($_POST as $key => $value) {
            if ($key === 'csrf_token') {
                continue;
            }

            $settingKey = 'seo_' . $key;

            if (!in_array($settingKey, $seoSettings)) {
                continue;
            }

            $value = trim((string) $value);

            $existing = $db->query("SELECT id FROM settings WHERE setting_key = ?", [$settingKey])->fetch();

            if ($existing) {
                $db->query(
                    "UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?",
                    [$value, $settingKey]
                );
            } else {
                $db->query(
                    "INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())",
                    [$settingKey, $value]
                );
            }
        }

        flash('success', 'SEO settings updated successfully.');
        $this->redirect('/admin/seo');
    }

    public function generateSitemap(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();
        $baseUrl = config('app.url');

        // Build sitemap content
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Home page
        $xml .= $this->sitemapUrl($baseUrl, 'daily', '1.0');

        // Static pages
        $pages = $db->query("SELECT slug, updated_at FROM pages WHERE status = 'published'")->fetchAll();
        foreach ($pages as $page) {
            $xml .= $this->sitemapUrl("{$baseUrl}/page/{$page['slug']}", 'monthly', '0.5', $page['updated_at']);
        }

        // Categories
        $categories = $db->query("SELECT slug, updated_at FROM categories WHERE is_active = 1")->fetchAll();
        foreach ($categories as $cat) {
            $xml .= $this->sitemapUrl("{$baseUrl}/categories/{$cat['slug']}", 'daily', '0.8', $cat['updated_at']);
        }

        // Products
        $products = $db->query("SELECT slug, updated_at FROM products WHERE status = 1")->fetchAll();
        foreach ($products as $product) {
            $xml .= $this->sitemapUrl("{$baseUrl}/products/{$product['slug']}", 'weekly', '0.7', $product['updated_at']);
        }

        $xml .= '</urlset>';

        // Save to file
        $sitemapPath = PUBLIC_PATH . '/sitemap.xml';
        file_put_contents($sitemapPath, $xml);

        // Ping search engines
        $this->pingSearchEngines($baseUrl . '/sitemap.xml');

        flash('success', 'Sitemap generated and search engines notified.');
        $this->redirect('/admin/seo');
    }

    private function sitemapUrl(string $url, string $changefreq, string $priority, ?string $lastmod = null): string
    {
        $xml = '<url>';
        $xml .= '<loc>' . htmlspecialchars($url) . '</loc>';
        if ($lastmod) {
            $xml .= '<lastmod>' . date('Y-m-d', strtotime($lastmod)) . '</lastmod>';
        }
        $xml .= '<changefreq>' . $changefreq . '</changefreq>';
        $xml .= '<priority>' . $priority . '</priority>';
        $xml .= '</url>';
        return $xml;
    }

    private function pingSearchEngines(string $sitemapUrl): void
    {
        $pingUrls = [
            'https://www.google.com/ping?sitemap=' . urlencode($sitemapUrl),
            'https://www.bing.com/ping?sitemap=' . urlencode($sitemapUrl),
        ];

        foreach ($pingUrls as $pingUrl) {
            $ch = curl_init($pingUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_exec($ch);
            curl_close($ch);
        }
    }
}
