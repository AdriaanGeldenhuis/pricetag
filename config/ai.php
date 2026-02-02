<?php
/**
 * AI Configuration
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Configure OpenAI integration and AI assistant features.
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
    'widget_title' => env('AI_WIDGET_TITLE', 'Shopping Assistant'),
    'widget_position' => env('AI_WIDGET_POSITION', 'right'), // 'left' or 'right'

    // Rate limiting
    'rate_limit' => [
        'requests_per_minute' => (int) env('AI_RATE_LIMIT', 30),
        'window_seconds' => 60,
    ],

    // Fallback responses when API is unavailable
    'fallback' => [
        'greeting' => "Hello! Welcome to our store. How can I help you today?",
        'shipping' => "We offer nationwide delivery across South Africa. Standard delivery takes 3-5 business days. Free shipping is available on orders over R500.",
        'returns' => "We have a 30-day return policy. Items must be unused and in original packaging. Please contact customer support to initiate a return.",
        'payment' => "We accept credit/debit cards (Visa, MasterCard), EFT bank transfers, and Cash on Delivery in select areas.",
        'default' => "Thank you for your message! I'm here to help you with product questions, order tracking, or any other inquiries. What would you like to know?",
    ],

    // Assistant personality
    'system_prompt_additions' => [
        'Be friendly, professional, and concise',
        'Focus on helping customers find the right products',
        'Always mention our excellent customer service',
        'Encourage newsletter signup for exclusive deals',
    ],

    // Product recommendation settings
    'recommendations' => [
        'max_products' => 5,
        'include_featured' => true,
        'include_on_sale' => true,
    ],
];
