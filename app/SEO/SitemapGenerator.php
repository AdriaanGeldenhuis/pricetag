<?php
/**
 * Sitemap Generator
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Generates XML sitemaps for search engine indexing.
 */

declare(strict_types=1);

namespace App\SEO;

use App\Core\Database;
use PDO;

class SitemapGenerator
{
    private PDO $db;
    private string $baseUrl;
    private array $urls = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->baseUrl = rtrim(config('app.url', ''), '/');
    }

    /**
     * Generate full sitemap
     */
    public function generate(): string
    {
        $this->addStaticPages();
        $this->addCategories();
        $this->addProducts();
        $this->addCmsPages();

        return $this->buildXml();
    }

    /**
     * Add static pages
     */
    private function addStaticPages(): void
    {
        // Home page
        $this->addUrl('/', 'daily', '1.0');

        // Static pages
        $staticPages = [
            '/shop' => ['daily', '0.9'],
            '/categories' => ['weekly', '0.8'],
            '/contact' => ['monthly', '0.5'],
            '/about' => ['monthly', '0.5'],
        ];

        foreach ($staticPages as $path => $config) {
            $this->addUrl($path, $config[0], $config[1]);
        }
    }

    /**
     * Add categories
     */
    private function addCategories(): void
    {
        $stmt = $this->db->query("
            SELECT slug, updated_at
            FROM categories
            WHERE status = 'active'
            ORDER BY name
        ");

        while ($row = $stmt->fetch()) {
            $this->addUrl(
                '/category/' . $row['slug'],
                'daily',
                '0.8',
                $row['updated_at']
            );
        }
    }

    /**
     * Add products
     */
    private function addProducts(): void
    {
        $stmt = $this->db->query("
            SELECT slug, updated_at
            FROM products
            WHERE status = 'active'
            ORDER BY updated_at DESC
        ");

        while ($row = $stmt->fetch()) {
            $this->addUrl(
                '/product/' . $row['slug'],
                'weekly',
                '0.9',
                $row['updated_at']
            );
        }
    }

    /**
     * Add CMS pages
     */
    private function addCmsPages(): void
    {
        $stmt = $this->db->query("
            SELECT slug, updated_at
            FROM pages
            WHERE status = 'published'
            ORDER BY title
        ");

        while ($row = $stmt->fetch()) {
            $this->addUrl(
                '/' . $row['slug'],
                'weekly',
                '0.6',
                $row['updated_at']
            );
        }
    }

    /**
     * Add URL to sitemap
     */
    public function addUrl(
        string $path,
        string $changefreq = 'weekly',
        string $priority = '0.5',
        ?string $lastmod = null
    ): self {
        $this->urls[] = [
            'loc' => $this->baseUrl . $path,
            'lastmod' => $lastmod ? date('Y-m-d', strtotime($lastmod)) : date('Y-m-d'),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];

        return $this;
    }

    /**
     * Build XML sitemap
     */
    private function buildXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($this->urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            $xml .= "    <lastmod>" . $url['lastmod'] . "</lastmod>\n";
            $xml .= "    <changefreq>" . $url['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Save sitemap to file
     */
    public function save(string $path = null): bool
    {
        $path = $path ?? PUBLIC_PATH . '/sitemap.xml';
        $xml = $this->generate();

        return file_put_contents($path, $xml) !== false;
    }

    /**
     * Generate sitemap index for large sites
     */
    public function generateIndex(array $sitemaps): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($sitemaps as $sitemap) {
            $xml .= "  <sitemap>\n";
            $xml .= "    <loc>" . htmlspecialchars($this->baseUrl . '/' . $sitemap) . "</loc>\n";
            $xml .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    /**
     * Get URL count
     */
    public function getUrlCount(): int
    {
        return count($this->urls);
    }
}
