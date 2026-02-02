<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- SEO Meta Tags -->
    <title><?= e($meta_title ?? config('seo.defaults.title')) ?></title>
    <meta name="description" content="<?= e($meta_description ?? config('seo.defaults.description')) ?>">
    <meta name="keywords" content="<?= e($meta_keywords ?? config('seo.defaults.keywords')) ?>">
    <meta name="robots" content="<?= e($meta_robots ?? config('seo.defaults.robots')) ?>">
    <meta name="author" content="<?= e(config('seo.defaults.author')) ?>">
    <?php if (!empty($canonical)): ?>
    <link rel="canonical" href="<?= e($canonical) ?>">
    <?php endif; ?>

    <!-- Open Graph -->
    <meta property="og:type" content="<?= e($og_type ?? config('seo.opengraph.type')) ?>">
    <meta property="og:title" content="<?= e($meta_title ?? config('seo.defaults.title')) ?>">
    <meta property="og:description" content="<?= e($meta_description ?? config('seo.defaults.description')) ?>">
    <meta property="og:url" content="<?= e(url($_SERVER['REQUEST_URI'] ?? '')) ?>">
    <meta property="og:site_name" content="<?= e(config('seo.opengraph.site_name')) ?>">
    <meta property="og:locale" content="<?= e(config('seo.opengraph.locale')) ?>">
    <?php if (!empty($og_image)): ?>
    <meta property="og:image" content="<?= e($og_image) ?>">
    <meta property="og:image:width" content="<?= e(config('seo.opengraph.image_width')) ?>">
    <meta property="og:image:height" content="<?= e(config('seo.opengraph.image_height')) ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="<?= e(config('seo.twitter.card')) ?>">
    <meta name="twitter:site" content="<?= e(config('seo.twitter.site')) ?>">
    <meta name="twitter:title" content="<?= e($meta_title ?? config('seo.defaults.title')) ?>">
    <meta name="twitter:description" content="<?= e($meta_description ?? config('seo.defaults.description')) ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= asset('images/favicon.ico') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('images/apple-touch-icon.png') ?>">

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= asset('css/design-system.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">

    <?php if (isset($styles)): echo $styles; endif; ?>

    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?= e(config('app.name')) ?>",
        "url": "<?= e(config('app.url')) ?>",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?= e(url('/search?q={search_term_string}')) ?>",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <?php if (!empty($schema)): ?>
    <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>
</head>
<body>
    <!-- Skip to main content (accessibility) -->
    <a href="#main-content" class="sr-only">Skip to main content</a>

    <!-- Site Header -->
    <?php include APP_PATH . '/Views/components/header.php'; ?>

    <!-- Main Navigation -->
    <?php include APP_PATH . '/Views/components/navigation.php'; ?>

    <!-- Flash Messages -->
    <?php if ($flash_success = flash('success')): ?>
    <div class="container">
        <div class="alert alert-success mt-4"><?= e($flash_success) ?></div>
    </div>
    <?php endif; ?>
    <?php if ($flash_error = flash('error')): ?>
    <div class="container">
        <div class="alert alert-danger mt-4"><?= e($flash_error) ?></div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main id="main-content">
        <?= $content ?? '' ?>
    </main>

    <!-- Site Footer -->
    <?php include APP_PATH . '/Views/components/footer.php'; ?>

    <!-- Cart Drawer -->
    <?php include APP_PATH . '/Views/components/cart-drawer.php'; ?>

    <!-- Mobile Menu -->
    <?php include APP_PATH . '/Views/components/mobile-menu.php'; ?>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Core JavaScript -->
    <script>
        window.Pricetag = {
            baseUrl: '<?= url() ?>',
            csrfToken: '<?= csrfToken() ?>',
            currency: {
                symbol: '<?= config('payment.currency.symbol', 'R') ?>',
                decimals: <?= config('payment.currency.decimal_places', 2) ?>
            },
            cart: {
                count: <?= cartCount() ?>,
                items: []
            },
            wishlist: {
                count: <?= wishlistCount() ?>
            },
            isLoggedIn: <?= auth() ? 'true' : 'false' ?>
        };
    </script>
    <script src="<?= asset('js/app.js') ?>" defer></script>

    <?php if (isset($scripts)): echo $scripts; endif; ?>

    <?php if (config('seo.google_analytics')): ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e(config('seo.google_analytics')) ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= e(config('seo.google_analytics')) ?>');
    </script>
    <?php endif; ?>
</body>
</html>
