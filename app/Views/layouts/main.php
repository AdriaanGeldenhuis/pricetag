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
    <?php $branding = getBranding(); ?>
    <?php if (!empty($branding['favicon'])): ?>
    <link rel="icon" href="<?= url($branding['favicon']) ?>">
    <?php else: ?>
    <link rel="icon" type="image/x-icon" href="<?= asset('images/favicon.ico') ?>">
    <?php endif; ?>
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('images/apple-touch-icon.png') ?>">

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= asset('css/design-system.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">

    <!-- Dynamic Brand Styles -->
    <style>
        :root {
            --color-primary: <?= e($branding['primary_color']) ?>;
            --color-primary-500: <?= e($branding['primary_color']) ?>;
            --color-primary-600: <?= e($branding['primary_color']) ?>;
            --color-primary-700: <?= e($branding['secondary_color']) ?>;
            --color-primary-800: <?= e($branding['secondary_color']) ?>;
            --color-primary-dark: <?= e($branding['secondary_color']) ?>;
            --color-secondary: <?= e($branding['secondary_color']) ?>;
            --color-secondary-500: <?= e($branding['secondary_color']) ?>;
            --color-accent: <?= e($branding['accent_color']) ?>;
            --color-accent-500: <?= e($branding['accent_color']) ?>;
            <?php if (!empty($branding['font_family']) && $branding['font_family'] !== 'Inter'): ?>
            --font-sans: '<?= e($branding['font_family']) ?>', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            <?php endif; ?>
        }
        <?php if (!empty($branding['font_family']) && $branding['font_family'] !== 'Inter'): ?>
        @import url('https://fonts.googleapis.com/css2?family=<?= e(str_replace(' ', '+', $branding['font_family'])) ?>:wght@400;500;600;700&display=swap');
        <?php endif; ?>
    </style>

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

    <?php $appearance = getAppearance(); ?>

    <!-- Announcement Bar -->
    <?php if (!empty($appearance['announcement_enabled']) && !empty($appearance['announcement_text'])): ?>
    <div class="announcement-bar" style="background-color: <?= e($appearance['announcement_bg_color']) ?>; color: <?= e($appearance['announcement_text_color']) ?>;">
        <div class="container">
            <?php if (!empty($appearance['announcement_link'])): ?>
            <a href="<?= e($appearance['announcement_link']) ?>" style="color: <?= e($appearance['announcement_text_color']) ?>;">
                <?= e($appearance['announcement_text']) ?>
            </a>
            <?php else: ?>
            <span><?= e($appearance['announcement_text']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

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

    <!-- AI Shopping Assistant -->
    <?php if (config('app.ai_assistant_enabled', true)): ?>
    <?php include APP_PATH . '/Views/components/ai-assistant.php'; ?>
    <?php endif; ?>

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

    <!-- WhatsApp Floating Button -->
    <?php if (!empty($appearance['whatsapp_enabled']) && !empty($appearance['whatsapp_number'])): ?>
    <a href="https://wa.me/<?= e($appearance['whatsapp_number']) ?>?text=<?= urlencode($appearance['whatsapp_message']) ?>"
       target="_blank"
       rel="noopener noreferrer"
       class="whatsapp-float <?= $appearance['whatsapp_position'] === 'bottom-left' ? 'left' : 'right' ?>"
       aria-label="Chat on WhatsApp">
        <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </a>
    <?php endif; ?>

    <!-- Cookie Consent -->
    <?php if (!empty($appearance['cookie_consent_enabled'])): ?>
    <div id="cookie-consent" class="cookie-consent" style="display: none;">
        <div class="cookie-consent-content">
            <p><?= e($appearance['cookie_consent_text']) ?>
                <a href="<?= e($appearance['cookie_policy_link']) ?>">Learn more</a>
            </p>
            <button type="button" class="btn btn-primary btn-sm" onclick="acceptCookies()">Accept</button>
        </div>
    </div>
    <script>
        if (!localStorage.getItem('cookieConsent')) {
            document.getElementById('cookie-consent').style.display = 'block';
        }
        function acceptCookies() {
            localStorage.setItem('cookieConsent', 'true');
            document.getElementById('cookie-consent').style.display = 'none';
        }
    </script>
    <?php endif; ?>

    <style>
    /* Announcement Bar */
    .announcement-bar {
        padding: 0.5rem 0;
        text-align: center;
        font-size: 0.875rem;
        font-weight: 500;
    }
    .announcement-bar a {
        text-decoration: none;
    }
    .announcement-bar a:hover {
        text-decoration: underline;
    }

    /* WhatsApp Float Button */
    .whatsapp-float {
        position: fixed;
        bottom: 20px;
        width: 56px;
        height: 56px;
        background: #25D366;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .whatsapp-float.right { right: 20px; }
    .whatsapp-float.left { left: 20px; }
    .whatsapp-float:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    }

    /* Cookie Consent */
    .cookie-consent {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #1a1a2e;
        color: white;
        padding: 1rem;
        z-index: 9999;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.15);
    }
    .cookie-consent-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .cookie-consent p {
        margin: 0;
        flex: 1;
    }
    .cookie-consent a {
        color: var(--color-primary-light, #60a5fa);
    }
    </style>
</body>
</html>
