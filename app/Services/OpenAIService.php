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
     * Generate a complete, production-ready product from SKU and short description.
     *
     * This is the comprehensive AI method that fills ALL fields needed for a
     * product to score 100% quality and be ready to sell. Used during import
     * and via the "Make Production Ready" button in the admin.
     *
     * @param string $sku Product SKU
     * @param string $shortDescription Short description / product info from supplier
     * @param array $context Additional context: brand, category, price, existingName
     * @return array Complete product data or ['error' => message]
     */
    public function generateCompleteProduct(string $sku, string $shortDescription = '', array $context = []): array
    {
        $price = $context['price'] ?? 0;
        $brand = $context['brand'] ?? '';
        $categoryHint = $context['category'] ?? '';
        $existingName = $context['existingName'] ?? '';
        $existingDescription = $context['existingDescription'] ?? '';

        // STEP 1: Always run local pattern matching FIRST - this is accurate for known SKU patterns
        $patternData = $this->generateFallbackFromSku($sku, [
            'brand' => $brand,
            'category' => $categoryHint,
            'price' => $price,
        ]);

        // The pattern matcher returns the SKU as name when it can't identify the product
        $patternRecognized = !empty($patternData['name']) && $patternData['name'] !== $sku;
        $verifiedName = $patternRecognized ? $patternData['name'] : '';
        $verifiedBrand = $patternData['brand'] ?? $brand;
        $verifiedCategory = $patternData['suggested_category'] ?? $categoryHint;

        error_log("AI Complete: SKU={$sku}, Pattern name='{$verifiedName}', Pattern recognized=" . ($patternRecognized ? 'YES' : 'NO'));

        if (empty($this->apiKey)) {
            return $this->generateFallbackComplete($sku, $shortDescription, $context, $patternData);
        }

        // STEP 2: Build AI prompt, passing the verified name as a HARD CONSTRAINT when we have one
        $prompt = "You are a senior e-commerce product specialist for Pricetag.co.za, a South African online store. ";
        $prompt .= "Create a COMPLETE, production-ready product listing from the following information.\n\n";
        $prompt .= "SKU: {$sku}\n";

        // If pattern matching identified the product, tell the AI explicitly
        if ($patternRecognized) {
            $prompt .= "\nPRODUCT IDENTIFIED: {$verifiedName}\n";
            $prompt .= "BRAND: {$verifiedBrand}\n";
            $prompt .= "CATEGORY: {$verifiedCategory}\n";
            $prompt .= "*** The product name above has been verified from the SKU. You MUST use this EXACT product name. Do NOT change or guess a different model number. ***\n\n";
        }

        if ($shortDescription) {
            $prompt .= "Supplier Description: {$shortDescription}\n";
        }
        if ($existingName && !$patternRecognized) {
            $prompt .= "Current Name: {$existingName}\n";
        }
        if ($existingDescription) {
            $prompt .= "Current Description: " . substr($existingDescription, 0, 300) . "\n";
        }
        if ($brand && !$patternRecognized) {
            $prompt .= "Brand: {$brand}\n";
        }
        if ($categoryHint && !$patternRecognized) {
            $prompt .= "Category Hint: {$categoryHint}\n";
        }
        if ($price > 0) {
            $prompt .= "Price: R" . number_format((float)$price, 2) . " (South African Rand)\n";
        }

        if (!$patternRecognized) {
            $prompt .= "\nSKU PATTERNS TO RECOGNIZE:\n";
            $prompt .= "- Intel: 'BX80' prefix = boxed processor, last 5 digits = model number. Examples: BX8071514900 = i9-14900, BX8071512400F = i5-12400F, BX8071514100 = i3-14100\n";
            $prompt .= "- Intel tier: x9xx=i9, x7xx=i7, x4xx/x5xx/x6xx=i5, x1xx=i3. Suffixes: K=unlocked, F=no iGPU, KF=both\n";
            $prompt .= "- AMD: '100-' prefix or 'Ryzen' in code\n";
            $prompt .= "- NVIDIA: RTX/GTX prefix. AMD: RX prefix for graphics cards\n";
            $prompt .= "- Corsair: 'CM'=memory, 'CP'=PSU, 'CC'=cases\n";
        }

        $prompt .= "\nGenerate ALL of the following fields in JSON format:\n";
        if ($patternRecognized) {
            $prompt .= "1. name: Use EXACTLY \"{$verifiedName}\" - do NOT change this\n";
        } else {
            $prompt .= "1. name: Professional product name (e.g., 'Intel Core i9-14900 Processor' NOT just the SKU). Extract the model number from the SKU digits.\n";
        }
        $prompt .= "2. short_description: Compelling 1-2 sentence summary highlighting key selling points (max 160 chars)\n";
        $prompt .= "3. description: Detailed HTML product description with features and benefits (3-4 paragraphs, use <p>, <ul>, <li>, <strong> tags)\n";
        $prompt .= "4. meta_title: SEO-optimized page title (max 70 chars, include brand + product + key feature)\n";
        $prompt .= "5. meta_description: SEO meta description (max 160 chars, include call-to-action)\n";
        $prompt .= "6. meta_keywords: Comma-separated SEO keywords (max 8 keywords relevant to the product)\n";
        $prompt .= "7. specifications: Array of product specs as [{\"name\": \"Spec Name\", \"value\": \"Spec Value\"}] - include at least 5 real specs\n";
        $prompt .= "8. suggested_category: Best product category (e.g., 'Processors', 'Graphics Cards', 'Memory', 'Storage')\n";
        $prompt .= "9. brand: Brand name\n";
        $prompt .= "10. weight: Estimated product weight in kg (decimal, e.g., 0.5)\n";
        $prompt .= "11. is_taxable: true\n";

        $prompt .= "\nIMPORTANT RULES:\n";
        $prompt .= "- NEVER use the raw SKU as the product name\n";
        $prompt .= "- NEVER guess a different model number than what the SKU encodes\n";
        $prompt .= "- All prices are in South African Rand (ZAR / R)\n";
        $prompt .= "- Write descriptions as if for a premium e-commerce store\n";
        $prompt .= "- Include real, accurate specifications based on the product identified\n";
        $prompt .= "\nRespond ONLY with valid JSON, no markdown code blocks, no extra text.";

        try {
            $response = $this->makeRequest('/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a product data specialist. Generate complete, accurate e-commerce product data. Always respond with valid JSON only, no markdown formatting.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 2500,
                'temperature' => 0.3,
            ]);

            $content = $response['choices'][0]['message']['content'] ?? '';

            // Clean up potential markdown code blocks
            $content = preg_replace('/^```json\s*/i', '', $content);
            $content = preg_replace('/^```\s*/i', '', $content);
            $content = preg_replace('/\s*```$/i', '', $content);
            $content = trim($content);

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('OpenAI Complete Product: JSON parse failed - ' . json_last_error_msg());
                return $this->generateFallbackComplete($sku, $shortDescription, $context, $patternData);
            }

            // STEP 3: Validate AI output - ALWAYS prefer pattern-matched name over AI name
            if ($patternRecognized) {
                // Force the verified name - AI cannot be trusted with model numbers
                $data['name'] = $verifiedName;
                $data['brand'] = $verifiedBrand;
                if (!empty($verifiedCategory)) {
                    $data['suggested_category'] = $verifiedCategory;
                }
            } else {
                // No pattern match - validate AI didn't just use raw SKU
                if (!empty($data['name'])) {
                    $aiName = trim($data['name']);
                    if (strcasecmp($aiName, $sku) === 0 || (stripos($aiName, $sku) === 0 && strlen($aiName) < strlen($sku) + 10)) {
                        $data['name'] = $existingName ?: $sku;
                    }
                }
            }

            // Ensure all expected keys exist with defaults
            $data = array_merge([
                'name' => $verifiedName ?: ($existingName ?: $sku),
                'short_description' => '',
                'description' => '',
                'meta_title' => '',
                'meta_description' => '',
                'meta_keywords' => '',
                'specifications' => [],
                'suggested_category' => $verifiedCategory ?: $categoryHint,
                'brand' => $verifiedBrand ?: $brand,
                'weight' => null,
                'is_taxable' => true,
            ], $data);

            return [
                'success' => true,
                'data' => $data,
            ];

        } catch (\Exception $e) {
            error_log('OpenAI Complete Product Error: ' . $e->getMessage());
            return $this->generateFallbackComplete($sku, $shortDescription, $context, $patternData);
        }
    }

    /**
     * Fallback when API is unavailable - generate basic complete product data
     */
    private function generateFallbackComplete(string $sku, string $shortDescription, array $context, ?array $patternData = null): array
    {
        $skuData = $patternData ?? $this->generateFallbackFromSku($sku, [
            'brand' => $context['brand'] ?? '',
            'category' => $context['category'] ?? '',
            'price' => $context['price'] ?? 0,
        ]);

        // Use pattern-matched name if available, otherwise existing name, otherwise SKU
        $patternName = ($skuData['name'] ?? '') !== $sku ? ($skuData['name'] ?? '') : '';
        $name = $patternName ?: ($context['existingName'] ?? $sku);
        $brand = $skuData['brand'] ?? ($context['brand'] ?? '');

        return [
            'success' => true,
            'data' => [
                'name' => $name,
                'short_description' => $skuData['short_description'] ?? $shortDescription,
                'description' => $skuData['description'] ?? '',
                'meta_title' => substr($name . ' | Buy Online at Pricetag.co.za', 0, 70),
                'meta_description' => substr(($skuData['short_description'] ?? $shortDescription) . ' Shop now at Pricetag.co.za with fast delivery across South Africa.', 0, 160),
                'meta_keywords' => implode(', ', array_filter([$brand, $skuData['suggested_category'] ?? '', 'buy online', 'South Africa', 'Pricetag'])),
                'specifications' => [],
                'suggested_category' => $skuData['suggested_category'] ?? ($context['category'] ?? ''),
                'brand' => $brand,
                'weight' => null,
                'is_taxable' => true,
            ],
        ];
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
                    ['role' => 'system', 'content' => 'You are a product identification expert. Analyze SKU codes to identify products. NEVER use the raw SKU as the product name - always identify the actual product. Always respond with valid JSON only, no markdown.'],
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
                // Validate: If AI returned the raw SKU as name, use fallback for name
                $aiName = trim($data['name']);
                $isNameJustSku = (strcasecmp($aiName, $sku) === 0) ||
                                 (stripos($aiName, $sku) === 0 && strlen($aiName) < strlen($sku) + 10);

                if ($isNameJustSku) {
                    // AI failed to generate a proper name - use fallback for name
                    $fallback = $this->generateFallbackFromSku($sku, $additionalInfo);
                    $data['name'] = $fallback['name'];
                    // Keep AI descriptions if they're better (longer), otherwise use fallback
                    if (empty($data['short_description']) || strlen($data['short_description']) < 20) {
                        $data['short_description'] = $fallback['short_description'];
                    }
                    if (empty($data['description']) || strlen($data['description']) < 50) {
                        $data['description'] = $fallback['description'];
                    }
                    if (empty($data['brand'])) {
                        $data['brand'] = $fallback['brand'];
                    }
                }
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
