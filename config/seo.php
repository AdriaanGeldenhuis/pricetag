<?php
/**
 * SEO Configuration
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

return [
    'defaults' => [
        'title' => 'Pricetag - Premium Online Shopping in South Africa',
        'title_separator' => ' | ',
        'description' => 'Shop the best deals on electronics, fashion, home goods and more. Fast delivery across South Africa. Trusted by thousands of customers.',
        'keywords' => 'online shopping, south africa, electronics, fashion, deals, free delivery',
        'robots' => 'index, follow',
        'author' => 'Pricetag',
    ],

    'opengraph' => [
        'type' => 'website',
        'site_name' => 'Pricetag',
        'locale' => 'en_ZA',
        'image' => '/assets/images/og-default.jpg',
        'image_width' => 1200,
        'image_height' => 630,
    ],

    'twitter' => [
        'card' => 'summary_large_image',
        'site' => '@pricetag_za',
        'creator' => '@pricetag_za',
    ],

    'schema' => [
        'organization' => [
            '@type' => 'Organization',
            'name' => 'Pricetag',
            'url' => 'https://pricetag.co.za',
            'logo' => 'https://pricetag.co.za/assets/images/logo.png',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '',
                'contactType' => 'customer service',
                'availableLanguage' => 'English',
            ],
        ],
    ],

    'sitemap' => [
        'enabled' => true,
        'cache_duration' => 3600,
        'changefreq' => [
            'home' => 'daily',
            'category' => 'daily',
            'product' => 'weekly',
            'page' => 'monthly',
        ],
        'priority' => [
            'home' => 1.0,
            'category' => 0.8,
            'product' => 0.7,
            'page' => 0.5,
        ],
    ],

    'robots' => [
        'allow' => [
            '/',
            '/products/',
            '/categories/',
        ],
        'disallow' => [
            '/admin/',
            '/cart/',
            '/checkout/',
            '/account/',
            '/api/',
            '/storage/',
        ],
    ],

    'canonical' => [
        'trailing_slash' => false,
        'lowercase' => true,
    ],
];
