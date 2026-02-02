<?php
/**
 * Performance Configuration
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Configure caching, optimization, and performance settings.
 */

return [
    // =========================================================================
    // Page Caching
    // =========================================================================
    'page_cache' => [
        'enabled' => env('PAGE_CACHE_ENABLED', false),
        'driver' => env('CACHE_DRIVER', 'file'), // file, redis, memcached
        'ttl' => 3600, // 1 hour

        // Pages to cache (glob patterns)
        'include' => [
            '/',
            '/products',
            '/products/*',
            '/categories',
            '/categories/*',
        ],

        // Pages to never cache
        'exclude' => [
            '/cart*',
            '/checkout*',
            '/account*',
            '/admin*',
            '/api*',
            '/login',
            '/register',
        ],

        // Skip cache when
        'skip_when' => [
            'logged_in' => true, // Don't cache for logged in users
            'has_cart' => true, // Don't cache when cart has items
        ],
    ],

    // =========================================================================
    // Query Caching
    // =========================================================================
    'query_cache' => [
        'enabled' => env('QUERY_CACHE_ENABLED', true),
        'ttl' => 300, // 5 minutes

        // Cache these query types
        'cached_queries' => [
            'categories' => 3600, // 1 hour
            'products_list' => 300, // 5 minutes
            'settings' => 3600, // 1 hour
            'menu' => 3600, // 1 hour
        ],
    ],

    // =========================================================================
    // Asset Optimization
    // =========================================================================
    'assets' => [
        // Add version hash to bust cache
        'versioning' => true,

        // Minify HTML output
        'minify_html' => env('MINIFY_HTML', false),

        // Lazy load images
        'lazy_images' => true,

        // Preload critical assets
        'preload' => [
            '/assets/css/main.css',
            '/assets/js/main.js',
        ],

        // DNS prefetch
        'dns_prefetch' => [
            'fonts.googleapis.com',
            'fonts.gstatic.com',
            'cdn.jsdelivr.net',
        ],
    ],

    // =========================================================================
    // Image Optimization
    // =========================================================================
    'images' => [
        // Generate responsive sizes
        'responsive_sizes' => [320, 640, 768, 1024, 1280, 1536],

        // Convert to WebP
        'webp_conversion' => true,

        // JPEG quality
        'jpeg_quality' => 85,

        // WebP quality
        'webp_quality' => 80,

        // Max dimensions
        'max_width' => 2000,
        'max_height' => 2000,

        // Lazy loading placeholder
        'placeholder' => 'data:image/svg+xml,...', // Low-quality placeholder
    ],

    // =========================================================================
    // Database Optimization
    // =========================================================================
    'database' => [
        // Connection pooling
        'persistent' => false,

        // Query logging (disable in production)
        'log_queries' => env('LOG_QUERIES', false),

        // Slow query threshold (ms)
        'slow_query_threshold' => 100,

        // Max connections
        'max_connections' => 10,
    ],

    // =========================================================================
    // Compression
    // =========================================================================
    'compression' => [
        // Enable gzip compression
        'gzip' => true,
        'gzip_level' => 6,

        // Minimum size to compress (bytes)
        'min_size' => 1024,

        // Content types to compress
        'types' => [
            'text/html',
            'text/css',
            'text/javascript',
            'application/javascript',
            'application/json',
            'application/xml',
            'text/xml',
            'image/svg+xml',
        ],
    ],

    // =========================================================================
    // HTTP Caching Headers
    // =========================================================================
    'http_cache' => [
        // Static assets
        'static' => [
            'max_age' => 31536000, // 1 year
            'immutable' => true,
        ],

        // Dynamic pages
        'pages' => [
            'max_age' => 0,
            'must_revalidate' => true,
            'private' => true,
        ],

        // API responses
        'api' => [
            'max_age' => 60,
            'stale_while_revalidate' => 300,
        ],
    ],

    // =========================================================================
    // Monitoring
    // =========================================================================
    'monitoring' => [
        // Track page load times
        'timing' => env('TRACK_TIMING', false),

        // Track memory usage
        'memory' => env('TRACK_MEMORY', false),

        // Track database queries
        'queries' => env('TRACK_QUERIES', false),

        // Performance threshold alerts (ms)
        'alert_threshold' => 2000, // 2 seconds
    ],
];
