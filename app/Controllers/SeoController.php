<?php
/**
 * SEO Controller - Sitemap and Robots.txt
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class SeoController extends Controller
{
    public function sitemap(): void
    {
        $db = Database::getInstance();
        $baseUrl = config('app.url');

        header('Content-Type: application/xml; charset=utf-8');

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Home page
        $this->addUrl($baseUrl, 'daily', '1.0');

        // Static pages
        $stmt = $db->query("SELECT slug, updated_at FROM pages WHERE status = 'published'");
        while ($page = $stmt->fetch()) {
            $this->addUrl("$baseUrl/page/{$page['slug']}", 'monthly', '0.5', $page['updated_at']);
        }

        // Categories
        $stmt = $db->query("SELECT slug, updated_at FROM categories WHERE is_active = 1");
        while ($cat = $stmt->fetch()) {
            $this->addUrl("$baseUrl/categories/{$cat['slug']}", 'daily', '0.8', $cat['updated_at']);
        }

        // Products
        $stmt = $db->query("SELECT slug, updated_at FROM products WHERE status = 'active'");
        while ($product = $stmt->fetch()) {
            $this->addUrl("$baseUrl/products/{$product['slug']}", 'weekly', '0.7', $product['updated_at']);
        }

        echo '</urlset>';
        exit;
    }

    private function addUrl(string $url, string $changefreq, string $priority, ?string $lastmod = null): void
    {
        echo '<url>';
        echo '<loc>' . htmlspecialchars($url) . '</loc>';
        if ($lastmod) {
            echo '<lastmod>' . date('Y-m-d', strtotime($lastmod)) . '</lastmod>';
        }
        echo '<changefreq>' . $changefreq . '</changefreq>';
        echo '<priority>' . $priority . '</priority>';
        echo '</url>';
    }

    public function robots(): void
    {
        header('Content-Type: text/plain');

        $baseUrl = config('app.url');
        $config = config('seo.robots', []);

        echo "User-agent: *\n";

        // Allow rules
        foreach ($config['allow'] ?? ['/', '/products/', '/categories/'] as $path) {
            echo "Allow: $path\n";
        }

        echo "\n";

        // Disallow rules
        foreach ($config['disallow'] ?? ['/admin/', '/cart/', '/checkout/', '/account/'] as $path) {
            echo "Disallow: $path\n";
        }

        echo "\n";
        echo "Sitemap: $baseUrl/sitemap.xml\n";

        exit;
    }
}
