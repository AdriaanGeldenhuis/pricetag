<?php
/**
 * OpenAI Service
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Handles communication with OpenAI API for the shopping assistant.
 */

namespace App\Services;

class OpenAIService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->model = env('OPENAI_MODEL', 'gpt-4o-mini');
    }

    /**
     * Send a chat message and get a response
     */
    public function chat(string $message, array $context, ?string $conversationId = null): array
    {
        if (empty($this->apiKey)) {
            return $this->getFallbackResponse($message, $context);
        }

        $systemPrompt = $this->buildSystemPrompt($context);
        $messages = $this->buildMessages($systemPrompt, $message, $conversationId);

        try {
            $response = $this->makeRequest('/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            $assistantMessage = $response['choices'][0]['message']['content'] ?? '';

            // Extract any product recommendations from the response
            $products = $this->extractProductsFromResponse($assistantMessage, $context);

            return [
                'message' => $assistantMessage,
                'conversation_id' => $conversationId ?? $this->generateConversationId(),
                'products' => $products,
            ];

        } catch (\Exception $e) {
            error_log('OpenAI API Error: ' . $e->getMessage());
            return $this->getFallbackResponse($message, $context);
        }
    }

    /**
     * Build the system prompt with store context
     */
    private function buildSystemPrompt(array $context): string
    {
        $storeName = $context['store_name'] ?? 'Pricetag';
        $currency = $context['currency'] ?? 'ZAR';

        $prompt = "You are a friendly and helpful shopping assistant for {$storeName}, a South African e-commerce store. ";
        $prompt .= "Your job is to help customers find products, answer questions about the store, and provide excellent customer service.\n\n";

        $prompt .= "Guidelines:\n";
        $prompt .= "- Be friendly, professional, and concise\n";
        $prompt .= "- All prices are in {$currency} (South African Rand)\n";
        $prompt .= "- If asked about products, recommend from the available inventory\n";
        $prompt .= "- If you don't know something specific, offer to help find the information\n";
        $prompt .= "- Never make up product information - only reference products you know about\n";
        $prompt .= "- For order issues, suggest contacting customer support\n\n";

        // Add user context
        if (!empty($context['user'])) {
            $prompt .= "Customer: {$context['user']['name']}\n";
            if ($context['user']['has_orders']) {
                $prompt .= "This is a returning customer.\n";
            }
        }

        // Add cart context
        if (!empty($context['cart'])) {
            $prompt .= "\nCustomer's cart: {$context['cart']['item_count']} items, total R" . number_format($context['cart']['total'], 2) . "\n";
        }

        // Add available products
        if (!empty($context['relevant_products'])) {
            $prompt .= "\nRelevant products you can recommend:\n";
            foreach ($context['relevant_products'] as $product) {
                $prompt .= "- {$product['name']} (R" . number_format($product['price'], 2) . "): {$product['short_description']}\n";
            }
        }

        // Add categories
        if (!empty($context['categories'])) {
            $categoryNames = array_column($context['categories'], 'name');
            $prompt .= "\nProduct categories: " . implode(', ', $categoryNames) . "\n";
        }

        return $prompt;
    }

    /**
     * Build the messages array for the chat
     */
    private function buildMessages(string $systemPrompt, string $userMessage, ?string $conversationId): array
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Load conversation history if exists
        if ($conversationId) {
            $history = $this->getConversationHistory($conversationId);
            foreach ($history as $msg) {
                $messages[] = $msg;
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }

    /**
     * Get conversation history from session
     */
    private function getConversationHistory(string $conversationId): array
    {
        $key = 'ai_history_' . $conversationId;
        return $_SESSION[$key] ?? [];
    }

    /**
     * Save message to conversation history
     */
    private function saveToHistory(string $conversationId, string $role, string $content): void
    {
        $key = 'ai_history_' . $conversationId;
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }

        $_SESSION[$key][] = ['role' => $role, 'content' => $content];

        // Keep only last 10 messages
        if (count($_SESSION[$key]) > 10) {
            $_SESSION[$key] = array_slice($_SESSION[$key], -10);
        }
    }

    /**
     * Make HTTP request to OpenAI API
     */
    private function makeRequest(string $endpoint, array $data): array
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new \Exception('API Error: HTTP ' . $httpCode . ' - ' . $response);
        }

        return json_decode($response, true) ?: [];
    }

    /**
     * Extract products mentioned in the response
     */
    private function extractProductsFromResponse(string $response, array $context): array
    {
        if (empty($context['relevant_products'])) {
            return [];
        }

        $mentionedProducts = [];
        foreach ($context['relevant_products'] as $product) {
            if (stripos($response, $product['name']) !== false) {
                $mentionedProducts[] = $product;
            }
        }

        return $mentionedProducts;
    }

    /**
     * Generate a unique conversation ID
     */
    private function generateConversationId(): string
    {
        return 'conv_' . bin2hex(random_bytes(16));
    }

    /**
     * Generate product content using AI
     */
    public function generateProductContent(array $product): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'OpenAI API key not configured'];
        }

        $productName = $product['name'] ?? '';
        $currentDescription = $product['description'] ?? '';
        $currentShortDescription = $product['short_description'] ?? '';
        $price = $product['price'] ?? 0;
        $category = $product['category'] ?? '';

        $prompt = "You are a professional e-commerce copywriter. Generate content for the following product:\n\n";
        $prompt .= "Product Name: {$productName}\n";
        if ($category) {
            $prompt .= "Category: {$category}\n";
        }
        $prompt .= "Price: R" . number_format((float)$price, 2) . "\n";
        if ($currentDescription) {
            $prompt .= "Current Description: {$currentDescription}\n";
        }

        $prompt .= "\nGenerate the following in JSON format:\n";
        $prompt .= "1. short_description: A compelling 1-2 sentence product summary (max 160 characters)\n";
        $prompt .= "2. description: A detailed product description with features and benefits (2-3 paragraphs)\n";
        $prompt .= "3. meta_title: SEO-optimized page title (max 70 characters)\n";
        $prompt .= "4. meta_description: SEO meta description (max 160 characters)\n";
        $prompt .= "5. specifications: Array of key-value pairs for product specs (e.g., [{\"name\": \"Material\", \"value\": \"Cotton\"}])\n";
        $prompt .= "\nRespond ONLY with valid JSON, no other text.";

        try {
            $response = $this->makeRequest('/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant that generates e-commerce product content. Always respond with valid JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 1000,
                'temperature' => 0.7,
            ]);

            $content = $response['choices'][0]['message']['content'] ?? '';

            // Clean up potential markdown code blocks
            $content = preg_replace('/^```json\s*/', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
            $content = trim($content);

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Failed to parse AI response'];
            }

            return [
                'success' => true,
                'data' => $data,
            ];

        } catch (\Exception $e) {
            error_log('OpenAI Product Generation Error: ' . $e->getMessage());
            return ['error' => 'Failed to generate content: ' . $e->getMessage()];
        }
    }

    /**
     * Search and fill product information from the web
     */
    public function searchProductInfo(string $productName, string $sku = ''): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'OpenAI API key not configured'];
        }

        $prompt = "Research and provide comprehensive product information for:\n";
        $prompt .= "Product: {$productName}\n";
        if ($sku) {
            $prompt .= "SKU/Model: {$sku}\n";
        }

        $prompt .= "\nProvide the following in JSON format:\n";
        $prompt .= "1. description: Detailed product description based on typical features of this product type\n";
        $prompt .= "2. short_description: Brief summary (max 160 chars)\n";
        $prompt .= "3. meta_title: SEO title (max 70 chars)\n";
        $prompt .= "4. meta_description: SEO description (max 160 chars)\n";
        $prompt .= "5. specifications: Array of typical specifications [{\"name\": \"spec\", \"value\": \"value\"}]\n";
        $prompt .= "6. suggested_category: Best product category\n";
        $prompt .= "\nBe helpful but don't make up specific technical specs you're not sure about. Respond ONLY with valid JSON.";

        try {
            $response = $this->makeRequest('/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a product research assistant. Provide helpful product information based on general knowledge. Always respond with valid JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 1000,
                'temperature' => 0.5,
            ]);

            $content = $response['choices'][0]['message']['content'] ?? '';

            // Clean up potential markdown code blocks
            $content = preg_replace('/^```json\s*/', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
            $content = trim($content);

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Failed to parse AI response'];
            }

            return [
                'success' => true,
                'data' => $data,
            ];

        } catch (\Exception $e) {
            error_log('OpenAI Product Search Error: ' . $e->getMessage());
            return ['error' => 'Failed to search product: ' . $e->getMessage()];
        }
    }

    /**
     * Check if API is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Generate product name and description from SKU
     */
    public function generateFromSku(string $sku, array $additionalInfo = []): array
    {
        if (empty($this->apiKey)) {
            // Return basic fallback based on SKU pattern analysis
            return $this->generateFallbackFromSku($sku, $additionalInfo);
        }

        $prompt = "You are an e-commerce product specialist. Analyze the SKU/product code and generate professional product listing content for an online store.\n\n";
        $prompt .= "SKU/Product Code: {$sku}\n";

        if (!empty($additionalInfo['brand'])) {
            $prompt .= "Brand: {$additionalInfo['brand']}\n";
        }
        if (!empty($additionalInfo['category'])) {
            $prompt .= "Category hint: {$additionalInfo['category']}\n";
        }
        if (!empty($additionalInfo['price'])) {
            $prompt .= "Price: R{$additionalInfo['price']} (South African Rand)\n";
        }

        $prompt .= "\nGenerate the following (respond in JSON format only):\n";
        $prompt .= "1. name: Professional product name suitable for e-commerce (e.g., 'Intel Core i7-12700K Desktop Processor' not just 'Intel CPU')\n";
        $prompt .= "2. short_description: Compelling 1-2 sentence summary for product cards (max 160 chars, highlight key selling point)\n";
        $prompt .= "3. description: SEO-optimized product description with key features and benefits (2-3 paragraphs, include specs where known)\n";
        $prompt .= "4. suggested_category: Best product category (e.g., 'Computer Components', 'Processors', 'Graphics Cards')\n";
        $prompt .= "5. brand: Brand name extracted from SKU analysis\n";
        $prompt .= "\nSKU PATTERNS TO RECOGNIZE:\n";
        $prompt .= "- Intel: 'BX80' prefix = boxed processor. Model patterns: 12400=i5-12400, 14100=i3-14100, 13700=i7-13700, 13900=i9-13900\n";
        $prompt .= "- Intel suffixes: K=unlocked, F=no iGPU, KF=both, T=low power, KS=special edition\n";
        $prompt .= "- Intel: 'G' in model (e.g., G6900) = Celeron, G7400 = Pentium Gold\n";
        $prompt .= "- AMD: '100-' prefix or 'Ryzen' in code\n";
        $prompt .= "- NVIDIA: RTX/GTX prefix, AMD: RX prefix for graphics cards\n";
        $prompt .= "\nIMPORTANT: Be specific with the product name. 'Intel Core i5-12400F Processor' is correct, 'Intel CPU' is too generic.\n";
        $prompt .= "Respond ONLY with valid JSON, no markdown code blocks.";

        try {
            $response = $this->makeRequest('/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a product identification expert. Analyze SKU codes to identify products. Always respond with valid JSON only, no markdown.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 800,
                'temperature' => 0.3,
            ]);

            $content = $response['choices'][0]['message']['content'] ?? '';

            // Clean up potential markdown code blocks
            $content = preg_replace('/^```json\s*/i', '', $content);
            $content = preg_replace('/^```\s*/i', '', $content);
            $content = preg_replace('/\s*```$/i', '', $content);
            $content = trim($content);

            $data = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE && !empty($data['name'])) {
                return $data;
            }

            // Fallback if JSON parsing fails
            return $this->generateFallbackFromSku($sku, $additionalInfo);

        } catch (\Exception $e) {
            error_log('OpenAI SKU Generation Error: ' . $e->getMessage());
            return $this->generateFallbackFromSku($sku, $additionalInfo);
        }
    }

    /**
     * Generate fallback product info from SKU pattern analysis
     */
    private function generateFallbackFromSku(string $sku, array $additionalInfo = []): array
    {
        $name = $sku;
        $brand = $additionalInfo['brand'] ?? '';
        $category = $additionalInfo['category'] ?? 'Electronics';
        $shortDesc = '';
        $description = '';

        // Clean SKU - remove regional suffixes for pattern matching
        $cleanSku = preg_replace('/-(CA|US|EU|UK|AU|SA)$/i', '', $sku);
        $regionSuffix = '';
        if (preg_match('/-(CA|US|EU|UK|AU|SA)$/i', $sku, $regionMatch)) {
            $regionSuffix = $regionMatch[0];
        }

        // Intel Celeron/Pentium detection (BX80XXXGXXXX format)
        // Examples: BX80715G6900 = Celeron G6900, BX80715G7400 = Pentium Gold G7400
        if (preg_match('/^BX80\d{3}(G)(\d{4})([A-Z]*)$/i', $cleanSku, $matches)) {
            $brand = 'Intel';
            $modelPrefix = strtoupper($matches[1]); // G
            $modelNum = $matches[2]; // 6900, 7400
            $suffix = strtoupper($matches[3] ?? '');

            // G6xxx = Celeron, G7xxx = Pentium Gold
            $firstDigit = substr($modelNum, 0, 1);
            if ($firstDigit === '6') {
                $tierName = 'Celeron';
            } elseif ($firstDigit === '7' || $firstDigit === '8') {
                $tierName = 'Pentium Gold';
            } else {
                $tierName = 'Celeron';
            }

            $name = "Intel {$tierName} {$modelPrefix}{$modelNum}";
            if ($suffix) $name .= $suffix;
            $name .= ' Processor';
            $category = 'Computer Components';
            $shortDesc = "Intel {$tierName} desktop processor for everyday computing and basic tasks.";
            $description = "The Intel {$tierName} {$modelPrefix}{$modelNum}" . ($suffix ?: '') . " is a reliable entry-level desktop processor. Perfect for everyday computing tasks including web browsing, office applications, and light media consumption. Features Intel's efficient architecture for responsive performance with low power consumption.";
        }
        // Intel Core processors (BX80XXX + 5-digit model) - handles 12th gen and newer
        // Examples: BX8071512400F, BX8071514100, BX8071512700K, BX8071513900KS
        elseif (preg_match('/^BX80\d{3}(\d{5})([A-Z]*)$/i', $cleanSku, $matches)) {
            $brand = 'Intel';
            $modelNum = $matches[1];  // e.g., "12400", "14100", "13900"
            $suffix = strtoupper($matches[2] ?? '');  // e.g., "K", "KF", "F", "KS"

            // Parse 5-digit model: 12400 = 12th gen, 4xx tier (i5)
            $gen = substr($modelNum, 0, 2);  // "12", "13", "14"
            $tierDigit = substr($modelNum, 2, 1);  // "1", "4", "7", "9"

            // Determine processor tier
            $tierName = match($tierDigit) {
                '1' => 'Core i3',
                '4', '5' => 'Core i5',
                '6' => 'Core i5',  // i5-x600 variants
                '7' => 'Core i7',
                '9' => 'Core i9',
                default => 'Core i5'
            };

            $name = "Intel {$tierName}-{$modelNum}";
            if ($suffix) $name .= $suffix;
            $name .= ' Processor';
            $category = 'Computer Components';

            // Generate suffix-aware descriptions
            $suffixDesc = $this->getIntelSuffixDescription($suffix);
            $tierDesc = $this->getIntelTierDescription($tierName);
            $shortDesc = "{$gen}th Gen Intel {$tierName} desktop processor{$suffixDesc}.";
            $description = "The Intel {$tierName}-{$modelNum}" . ($suffix ?: '') . " is a {$gen}th generation desktop processor. {$tierDesc} {$suffixDesc} Features Intel's latest architecture for exceptional performance in demanding applications.";
        }
        // Intel Core processors (4-digit model - older generations)
        // Examples: BX80684I99900K, BX80684I78700K
        elseif (preg_match('/^BX\d+I(\d)(\d{4})([A-Z]*)$/i', $cleanSku, $matches)) {
            $brand = 'Intel';
            $tier = $matches[1];  // 3, 5, 7, 9
            $modelNum = $matches[2];  // 9900, 8700
            $suffix = strtoupper($matches[3] ?? '');

            $tierName = match($tier) {
                '3' => 'Core i3',
                '5' => 'Core i5',
                '7' => 'Core i7',
                '9' => 'Core i9',
                default => 'Core i5'
            };

            $gen = substr($modelNum, 0, 1);  // 9 for 9900, 8 for 8700
            $name = "Intel {$tierName}-{$modelNum}";
            if ($suffix) $name .= $suffix;
            $name .= ' Processor';
            $category = 'Computer Components';

            $suffixDesc = $this->getIntelSuffixDescription($suffix);
            $tierDesc = $this->getIntelTierDescription($tierName);
            $shortDesc = "{$gen}th Gen Intel {$tierName} desktop processor{$suffixDesc}.";
            $description = "The Intel {$tierName}-{$modelNum}" . ($suffix ?: '') . " is a {$gen}th generation desktop processor. {$tierDesc} {$suffixDesc}";
        }
        // Generic Intel BX pattern - try to extract any useful model info
        elseif (preg_match('/^BX80(\d{3})(\d+)([A-Z]*)$/i', $cleanSku, $matches)) {
            $brand = 'Intel';
            $prefix = $matches[1];
            $modelNum = $matches[2];
            $suffix = strtoupper($matches[3] ?? '');

            $name = "Intel Processor " . $modelNum;
            if ($suffix) $name .= $suffix;
            $category = 'Computer Components';
            $shortDesc = "Intel desktop processor for reliable computing performance.";
            $description = "Intel processor model {$modelNum}" . ($suffix ?: '') . ". A reliable desktop processor delivering consistent performance for various computing tasks.";
        }
        // Fallback for any Intel BX SKU
        elseif (preg_match('/^BX\d+/i', $cleanSku)) {
            $brand = 'Intel';
            $name = "Intel Processor";
            $category = 'Computer Components';
            $shortDesc = "Intel desktop processor (SKU: {$sku}).";
            $description = "Intel desktop processor. Please refer to Intel specifications for detailed product information. SKU: {$sku}";
        }
        // AMD Ryzen processors
        elseif (preg_match('/ryzen\s*(\d)\s*(\d{4})([A-Z]*)/i', $sku, $matches)) {
            $brand = 'AMD';
            $tier = $matches[1];
            $model = $matches[2];
            $suffix = strtoupper($matches[3] ?? '');

            $tierName = "Ryzen {$tier}";
            $name = "AMD {$tierName} {$model}";
            if ($suffix) $name .= $suffix;
            $name .= ' Processor';
            $category = 'Computer Components';
            $shortDesc = "AMD {$tierName} desktop processor for high performance computing.";
            $description = "The AMD {$tierName} {$model}" . ($suffix ?: '') . " processor delivers exceptional multi-threaded performance. Built on AMD's advanced architecture for gaming, content creation, and productivity.";
        }
        // AMD 100-xxxxxx format
        elseif (preg_match('/^100-\d{6}/i', $sku)) {
            $brand = 'AMD';
            $name = "AMD Processor";
            $category = 'Computer Components';
            $shortDesc = "AMD desktop processor (SKU: {$sku}).";
            $description = "AMD processor. Please refer to AMD specifications for detailed product information. SKU: {$sku}";
        }
        // NVIDIA Graphics cards
        elseif (preg_match('/(RTX|GTX)\s*(\d{4})\s*(Ti|SUPER)?/i', $sku, $matches)) {
            $brand = 'NVIDIA';
            $series = strtoupper($matches[1]);
            $model = $matches[2];
            $variant = isset($matches[3]) ? ' ' . ucfirst(strtolower($matches[3])) : '';

            $name = "NVIDIA GeForce {$series} {$model}{$variant}";
            $category = 'Graphics Cards';
            $shortDesc = "NVIDIA GeForce {$series} {$model}{$variant} graphics card for gaming and creative work.";
            $description = "The NVIDIA GeForce {$series} {$model}{$variant} graphics card delivers outstanding performance for gaming, streaming, and creative applications. Features NVIDIA's latest architecture with ray tracing and DLSS support.";
        }
        // AMD Graphics cards
        elseif (preg_match('/RX\s*(\d{4})\s*(XT|XTX)?/i', $sku, $matches)) {
            $brand = 'AMD';
            $model = $matches[1];
            $variant = isset($matches[2]) ? ' ' . strtoupper($matches[2]) : '';

            $name = "AMD Radeon RX {$model}{$variant}";
            $category = 'Graphics Cards';
            $shortDesc = "AMD Radeon RX {$model}{$variant} graphics card for gaming and content creation.";
            $description = "The AMD Radeon RX {$model}{$variant} graphics card provides exceptional gaming performance and creative capabilities. Built on AMD's RDNA architecture for immersive gaming experiences.";
        }

        // Use provided brand if available
        if (!empty($additionalInfo['brand'])) {
            $brand = $additionalInfo['brand'];
        }

        // Fallback descriptions if not set
        if (empty($shortDesc)) {
            $shortDesc = $brand ? "{$brand} {$name} - Quality product for reliable performance." : "{$name} - Quality product.";
        }
        if (empty($description)) {
            $description = $brand
                ? "The {$name} from {$brand} delivers reliable performance and quality. Designed for demanding users who expect the best."
                : "The {$name} delivers reliable performance and quality.";
        }

        return [
            'name' => $name,
            'brand' => $brand,
            'short_description' => $shortDesc,
            'description' => $description,
            'suggested_category' => $category
        ];
    }

    /**
     * Get description snippet based on Intel processor suffix
     */
    private function getIntelSuffixDescription(string $suffix): string
    {
        return match($suffix) {
            'K' => ' with unlocked multiplier for overclocking',
            'KF' => ' with unlocked multiplier (no integrated graphics)',
            'F' => ' (no integrated graphics, discrete GPU required)',
            'KS' => ' Special Edition with enhanced boost frequencies',
            'T' => ' power-optimized variant for efficient operation',
            'S' => ' performance-optimized variant',
            default => ''
        };
    }

    /**
     * Get description snippet based on Intel processor tier
     */
    private function getIntelTierDescription(string $tierName): string
    {
        return match($tierName) {
            'Core i3' => 'Ideal for everyday computing, office work, and light gaming.',
            'Core i5' => 'Perfect balance of performance and value for gaming and productivity.',
            'Core i7' => 'High-performance processor for demanding gaming and content creation.',
            'Core i9' => 'Flagship desktop processor for extreme performance in gaming and professional workloads.',
            default => 'Reliable processor for various computing tasks.'
        };
    }

    /**
     * Fallback response when API is unavailable
     */
    private function getFallbackResponse(string $message, array $context): array
    {
        $lowerMessage = strtolower($message);

        // Simple keyword-based responses
        if (str_contains($lowerMessage, 'hello') || str_contains($lowerMessage, 'hi')) {
            $name = $context['user']['name'] ?? 'there';
            $response = "Hello {$name}! Welcome to " . ($context['store_name'] ?? 'Pricetag') . ". How can I help you today?";
        } elseif (str_contains($lowerMessage, 'order') || str_contains($lowerMessage, 'track')) {
            $response = "To track your order, please go to your account page and click on 'My Orders'. If you need further assistance with an order, please contact our customer support.";
        } elseif (str_contains($lowerMessage, 'shipping') || str_contains($lowerMessage, 'delivery')) {
            $response = "We offer nationwide delivery across South Africa. Standard delivery takes 3-5 business days. Free shipping is available on orders over R500.";
        } elseif (str_contains($lowerMessage, 'return') || str_contains($lowerMessage, 'refund')) {
            $response = "We have a 30-day return policy. Items must be unused and in original packaging. Please contact customer support to initiate a return.";
        } elseif (str_contains($lowerMessage, 'payment') || str_contains($lowerMessage, 'pay')) {
            $response = "We accept credit/debit cards (Visa, MasterCard), EFT bank transfers, and Cash on Delivery in select areas.";
        } elseif (!empty($context['relevant_products'])) {
            $response = "Here are some products you might be interested in:\n\n";
            foreach (array_slice($context['relevant_products'], 0, 3) as $product) {
                $response .= "• " . $product['name'] . " - R" . number_format($product['price'], 2) . "\n";
            }
            $response .= "\nWould you like more details on any of these?";
        } else {
            $response = "Thank you for your message! I'm here to help you with product questions, order tracking, or any other inquiries. What would you like to know?";
        }

        return [
            'message' => $response,
            'conversation_id' => $this->generateConversationId(),
            'products' => $context['relevant_products'] ?? [],
        ];
    }
}
