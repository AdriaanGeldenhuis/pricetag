<!-- Home Page - Section-driven layout -->
<?php
/**
 * Homepage view - renders sections in order from the home_sections table.
 * Each section type maps to a partial in /Views/sections/.
 * Falls back to default order when no sections are configured in admin.
 *
 * Available data from HomeController:
 *   $sections, $heroBanners, $categories, $featuredCategories,
 *   $trendingProducts, $newArrivals, $bestSellers, $onSaleProducts,
 *   $flashSale, $testimonials, $promoBanners
 */

// Section type -> partial file mapping
$sectionMap = [
    'hero'               => 'hero_slider',
    'hero_slider'        => 'hero_slider',
    'category_cards'     => 'category_cards',
    'categories'         => 'featured_categories',
    'featured_categories'=> 'featured_categories',
    'new_arrivals'       => 'product_carousel',
    'best_sellers'       => 'product_carousel',
    'featured_products'  => 'product_carousel',
    'deals_countdown'    => 'deals_countdown',
    'hot_deals'          => 'deals_countdown',
    'trending_products'  => 'product_grid',
    'trust_badges'       => 'trust_badges',
    'promo_banner'       => 'promo_banner',
    'testimonials'       => 'testimonials',
    'newsletter'         => 'newsletter',
    'recently_viewed'    => 'recently_viewed',
];

// Section type -> data binding (sets variables before including the partial)
$sectionData = [
    'new_arrivals' => [
        'products'      => $newArrivals ?? [],
        'sectionTitle'  => 'New Arrivals',
        'sectionBadge'  => 'Just In',
        'badgeClass'    => 'badge-primary',
        'viewAllUrl'    => '/products?sort=newest',
        'carouselId'    => 'new-arrivals',
        'bgClass'       => 'bg-background-alt',
    ],
    'best_sellers' => [
        'products'      => $bestSellers ?? [],
        'sectionTitle'  => 'Best Sellers',
        'sectionBadge'  => 'Top Rated',
        'badgeClass'    => 'badge-success',
        'viewAllUrl'    => '/products?sort=popular',
        'carouselId'    => 'best-sellers',
        'bgClass'       => '',
    ],
    'featured_products' => [
        'products'      => $bestSellers ?? [],
        'sectionTitle'  => 'Featured Products',
        'sectionBadge'  => 'Staff Picks',
        'badgeClass'    => 'badge-primary',
        'viewAllUrl'    => '/products?featured=1',
        'carouselId'    => 'featured',
        'bgClass'       => '',
    ],
    'trending_products' => [
        'products'      => $trendingProducts ?? [],
        'sectionTitle'  => "What's Hot",
        'sectionBadge'  => 'Trending',
        'badgeClass'    => 'badge-warning',
        'viewAllUrl'    => '/products?sort=popular',
        'bgClass'       => 'bg-background-alt',
    ],
];

// Default section order (used when no sections in database)
$defaultSections = [
    ['type' => 'hero_slider',        'is_active' => 1],
    ['type' => 'category_cards',     'is_active' => 1],
    ['type' => 'trust_badges',       'is_active' => 1],
    ['type' => 'new_arrivals',       'is_active' => 1],
    ['type' => 'best_sellers',       'is_active' => 1],
    ['type' => 'deals_countdown',    'is_active' => 1],
    ['type' => 'trending_products',  'is_active' => 1],
    ['type' => 'promo_banner',       'is_active' => 1],
    ['type' => 'recently_viewed',    'is_active' => 1],
    ['type' => 'newsletter',         'is_active' => 1],
];

$activeSections = !empty($sections) ? $sections : $defaultSections;
?>

<?php foreach ($activeSections as $section):
    if (empty($section['is_active'])) continue;

    $type = $section['type'] ?? '';
    $partial = $sectionMap[$type] ?? null;

    if (!$partial) continue;

    $partialPath = APP_PATH . '/Views/sections/' . $partial . '.php';
    if (!file_exists($partialPath)) continue;

    // Apply section title override from admin config
    if (!empty($section['title'])) {
        $sectionTitle = $section['title'];
    }
    if (!empty($section['subtitle'])) {
        $sectionSubtitle = $section['subtitle'];
    }

    // Bind section-specific data
    if (isset($sectionData[$type])) {
        extract($sectionData[$type]);
    }

    // Decode config if available
    $sectionConfig = [];
    if (!empty($section['config'])) {
        $sectionConfig = is_string($section['config']) ? (json_decode($section['config'], true) ?: []) : $section['config'];
    }

    include $partialPath;

    // Reset section variables
    unset($sectionTitle, $sectionSubtitle, $sectionBadge, $badgeClass, $viewAllUrl, $carouselId, $bgClass, $products);
endforeach; ?>

<link rel="stylesheet" href="<?= asset('css/home.css') ?>">
<script src="<?= asset('js/home.js') ?>" defer></script>
