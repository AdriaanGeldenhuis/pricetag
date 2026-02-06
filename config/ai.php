<?php
/**
 * AI Configuration - Taggy
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Configure OpenAI integration and Taggy, the AI shopping assistant.
 */

return [
    // Enable/disable AI features
    'enabled' => env('AI_ENABLED', true),

    // OpenAI API configuration
    'openai' => [
        'api_key' => env('OPENAI_API_KEY', ''),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 500),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.7),
    ],

    // Chat widget configuration
    'widget_color' => env('AI_WIDGET_COLOR', '#4f46e5'),
    'widget_title' => env('AI_WIDGET_TITLE', 'Taggy'),
    'widget_position' => env('AI_WIDGET_POSITION', 'right'), // 'left' or 'right'

    // Rate limiting
    'rate_limit' => [
        'requests_per_minute' => (int) env('AI_RATE_LIMIT', 30),
        'window_seconds' => 60,
    ],

    // Fallback responses when API is unavailable
    'fallback' => [
        'greeting' => "Hey! I'm Taggy, your personal shopping sidekick at Pricetag. I can help you find products, track orders, or answer any questions about our store. What can I do for you?",
        'shipping' => "Here's the delivery info:\n\n• Standard delivery: 3-5 business days nationwide — R75\n• Express delivery: 1-2 days in major metros — R150\n• Free shipping on all orders over R500!\n\nYou'll get a tracking number by email once your order ships.",
        'returns' => "Our return policy:\n\n• 30-day returns from date of delivery\n• Items must be unused & in original packaging\n• Email info@pricetag.co.za with your order number to start\n• Refunds processed within 5-7 business days\n• Damaged items? We'll cover return shipping.",
        'payment' => "We accept:\n\n• Credit/debit cards (Visa & MasterCard, secured by 3D Secure)\n• EFT bank transfers\n• Instant EFT via Ozow\n• Cash on Delivery in select metro areas\n\nAll transactions are SSL-encrypted and secure.",
        'default' => "I'm Taggy — your go-to for anything about Pricetag! I can help with products, orders, shipping, returns, payments, and more. Just ask away!",
    ],

    // Assistant personality
    'system_prompt_additions' => [
        'You are Taggy — warm, witty, and genuinely helpful',
        'Keep answers concise but complete, use bullet points for clarity',
        'Focus on helping customers find the right products',
        'For order-specific issues, direct to support: info@pricetag.co.za / 011 100 2232',
        'Encourage newsletter signup for exclusive deals',
    ],

    // Product recommendation settings
    'recommendations' => [
        'max_products' => 5,
        'include_featured' => true,
        'include_on_sale' => true,
    ],
];
