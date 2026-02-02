<?php
/**
 * Payment Configuration
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

return [
    'default' => 'yoco',

    'gateways' => [
        'yoco' => [
            'name' => 'Yoco',
            'enabled' => true,
            'sandbox' => env('YOCO_SANDBOX', true),
            'public_key' => env('YOCO_PUBLIC_KEY', ''),
            'secret_key' => env('YOCO_SECRET_KEY', ''),
            'webhook_secret' => env('YOCO_WEBHOOK_SECRET', ''),
            'endpoints' => [
                'sandbox' => 'https://payments.yoco.com/api/',
                'live' => 'https://payments.yoco.com/api/',
            ],
            'supported_currencies' => ['ZAR'],
            'supported_methods' => ['card'],
        ],
    ],

    'currency' => [
        'code' => 'ZAR',
        'symbol' => 'R',
        'decimal_places' => 2,
        'decimal_separator' => '.',
        'thousands_separator' => ' ',
        'symbol_position' => 'before',
    ],

    'tax' => [
        'enabled' => true,
        'rate' => 15.00, // South Africa VAT
        'inclusive' => true, // Prices include VAT
        'label' => 'VAT',
        'registration_number' => '',
    ],

    'shipping' => [
        'free_threshold' => 500.00, // Free shipping over R500
        'default_rate' => 75.00,
        'express_rate' => 150.00,
    ],

    'fraud' => [
        'enabled' => true,
        'max_order_amount' => 50000.00,
        'require_3ds' => true,
        'block_vpn' => false,
    ],

    'retry' => [
        'enabled' => true,
        'max_attempts' => 3,
        'delay' => 5, // seconds
    ],
];
