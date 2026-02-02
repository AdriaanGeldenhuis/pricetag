<?php
/**
 * SEO Manager
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Centralized SEO management for meta tags, schema markup, and optimization.
 */

declare(strict_types=1);

namespace App\SEO;

class SeoManager
{
    private static ?self $instance = null;
    private array $meta = [];
    private array $schema = [];
    private array $openGraph = [];
    private array $twitterCards = [];
    private string $canonical = '';
    private array $breadcrumbs = [];

    private function __construct()
    {
        $this->initDefaults();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initDefaults(): void
    {
        $siteUrl = config('app.url', '');
        $siteName = config('app.name', 'Pricetag');

        $this->meta = [
            'title' => config('seo.default_title', $siteName),
            'description' => config('seo.default_description', ''),
            'keywords' => config('seo.default_keywords', ''),
            'robots' => 'index, follow',
            'author' => $siteName,
        ];

        $this->openGraph = [
            'type' => 'website',
            'site_name' => $siteName,
            'locale' => 'en_ZA',
        ];

        $this->twitterCards = [
            'card' => 'summary_large_image',
            'site' => config('seo.twitter_handle', ''),
        ];

        $this->canonical = $siteUrl . ($_SERVER['REQUEST_URI'] ?? '');
    }

    /**
     * Set page title
     */
    public function title(string $title, bool $appendSiteName = true): self
    {
        $siteName = config('app.name', 'Pricetag');
        $this->meta['title'] = $appendSiteName ? "$title | $siteName" : $title;
        $this->openGraph['title'] = $title;
        $this->twitterCards['title'] = $title;
        return $this;
    }

    /**
     * Set page description
     */
    public function description(string $description): self
    {
        $description = truncate(strip_tags($description), 160, '');
        $this->meta['description'] = $description;
        $this->openGraph['description'] = $description;
        $this->twitterCards['description'] = $description;
        return $this;
    }

    /**
     * Set page keywords
     */
    public function keywords(string|array $keywords): self
    {
        $this->meta['keywords'] = is_array($keywords) ? implode(', ', $keywords) : $keywords;
        return $this;
    }

    /**
     * Set canonical URL
     */
    public function canonical(string $url): self
    {
        $this->canonical = $url;
        $this->openGraph['url'] = $url;
        return $this;
    }

    /**
     * Set image for sharing
     */
    public function image(string $url, int $width = 1200, int $height = 630): self
    {
        $this->openGraph['image'] = $url;
        $this->openGraph['image:width'] = $width;
        $this->openGraph['image:height'] = $height;
        $this->twitterCards['image'] = $url;
        return $this;
    }

    /**
     * Set robots directive
     */
    public function robots(string $directive): self
    {
        $this->meta['robots'] = $directive;
        return $this;
    }

    /**
     * No index this page
     */
    public function noIndex(): self
    {
        $this->meta['robots'] = 'noindex, nofollow';
        return $this;
    }

    /**
     * Add breadcrumb
     */
    public function addBreadcrumb(string $name, string $url = ''): self
    {
        $this->breadcrumbs[] = [
            'name' => $name,
            'url' => $url,
        ];
        return $this;
    }

    /**
     * Set breadcrumbs
     */
    public function breadcrumbs(array $breadcrumbs): self
    {
        $this->breadcrumbs = $breadcrumbs;
        return $this;
    }

    /**
     * Add product schema
     */
    public function productSchema(array $product): self
    {
        $this->schema['product'] = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product['name'],
            'description' => $product['description'] ?? '',
            'image' => $product['image'] ?? '',
            'sku' => $product['sku'] ?? '',
            'brand' => [
                '@type' => 'Brand',
                'name' => $product['brand'] ?? config('app.name'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'ZAR',
                'price' => $product['price'],
                'availability' => ($product['stock_quantity'] ?? 0) > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url' => $product['url'] ?? '',
            ],
        ];

        if (!empty($product['rating'])) {
            $this->schema['product']['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $product['rating'],
                'reviewCount' => $product['review_count'] ?? 0,
            ];
        }

        return $this;
    }

    /**
     * Add FAQ schema
     */
    public function faqSchema(array $faqs): self
    {
        $items = [];
        foreach ($faqs as $faq) {
            $items[] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ];
        }

        $this->schema['faq'] = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $items,
        ];

        return $this;
    }

    /**
     * Add organization schema
     */
    public function organizationSchema(): self
    {
        $this->schema['organization'] = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => config('app.url'),
            'logo' => asset('images/logo.png'),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => config('seo.contact_phone', ''),
                'contactType' => 'customer service',
            ],
        ];

        return $this;
    }

    /**
     * Render all meta tags
     */
    public function render(): string
    {
        $output = [];

        // Basic meta tags
        $output[] = '<title>' . e($this->meta['title']) . '</title>';
        $output[] = '<meta name="description" content="' . e($this->meta['description']) . '">';
        $output[] = '<meta name="keywords" content="' . e($this->meta['keywords']) . '">';
        $output[] = '<meta name="robots" content="' . e($this->meta['robots']) . '">';
        $output[] = '<meta name="author" content="' . e($this->meta['author']) . '">';

        // Canonical
        if ($this->canonical) {
            $output[] = '<link rel="canonical" href="' . e($this->canonical) . '">';
        }

        // Open Graph
        foreach ($this->openGraph as $property => $content) {
            if ($content) {
                $output[] = '<meta property="og:' . e($property) . '" content="' . e($content) . '">';
            }
        }

        // Twitter Cards
        foreach ($this->twitterCards as $name => $content) {
            if ($content) {
                $output[] = '<meta name="twitter:' . e($name) . '" content="' . e($content) . '">';
            }
        }

        return implode("\n    ", $output);
    }

    /**
     * Render schema markup
     */
    public function renderSchema(): string
    {
        $output = [];

        // Breadcrumb schema
        if (!empty($this->breadcrumbs)) {
            $breadcrumbSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [],
            ];

            foreach ($this->breadcrumbs as $i => $crumb) {
                $breadcrumbSchema['itemListElement'][] = [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $crumb['name'],
                    'item' => $crumb['url'] ?: null,
                ];
            }

            $output[] = '<script type="application/ld+json">' .
                json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES) . '</script>';
        }

        // Other schema
        foreach ($this->schema as $schema) {
            $output[] = '<script type="application/ld+json">' .
                json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
        }

        return implode("\n", $output);
    }

    /**
     * Get current meta data
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Get breadcrumbs
     */
    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }
}
