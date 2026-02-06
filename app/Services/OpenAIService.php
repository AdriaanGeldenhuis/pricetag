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

    // =========================================================================
    // PRODUCT AI - IDENTIFICATION & CONTENT GENERATION
    // =========================================================================
    //
    // ARCHITECTURE:
    //   1. identifyProductFromSku() - LOCAL pattern matching. This is the SINGLE
    //      source of truth for product names. No AI involved. 100% accurate for
    //      known SKU formats (Intel BX80, AMD Ryzen, NVIDIA RTX/GTX, etc).
    //
    //   2. generateCompleteProduct() - THE main entry point for all AI product work.
    //      Calls identifyProductFromSku() first, then asks AI to write descriptions,
    //      SEO, specs. The AI NEVER decides the product name - it only writes content
    //      about the already-identified product.
    //
    //   3. generateFromSku() - Lightweight wrapper that calls identifyProductFromSku()
    //      and optionally enhances with AI. Used by older code paths.
    //
    //   RULE: The AI is NEVER allowed to decide what product a SKU is.
    //         Pattern matching decides the name. AI writes the copy.
    // =========================================================================

    /**
     * Check if API is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Identify a product from its SKU using LOCAL pattern matching only.
     * No AI involved. This is the single source of truth for product names.
     *
     * @return array{name: string, brand: string, category: string, shortDesc: string, description: string, recognized: bool}
     */
    public function identifyProductFromSku(string $sku): array
    {
        $name = '';
        $brand = '';
        $category = 'Electronics';
        $shortDesc = '';
        $description = '';

        // Clean SKU - remove regional suffixes
        $cleanSku = preg_replace('/-(CA|US|EU|UK|AU|SA)$/i', '', $sku);

        // ---- INTEL CELERON/PENTIUM: BX80xxxGxxxx ----
        if (preg_match('/^BX80\d{3}(G)(\d{4})([A-Z]*)$/i', $cleanSku, $m)) {
            $brand = 'Intel';
            $model = strtoupper($m[1]) . $m[2]; // G6900
            $suffix = strtoupper($m[3] ?? '');
            $firstDigit = substr($m[2], 0, 1);
            $tierName = ($firstDigit >= '7') ? 'Pentium Gold' : 'Celeron';

            $name = "Intel {$tierName} {$model}" . ($suffix ?: '') . " Processor";
            $category = 'Processors';
            $shortDesc = "Intel {$tierName} desktop processor for everyday computing and basic tasks.";
            $description = "The {$name} is a reliable entry-level desktop processor. Perfect for everyday computing tasks including web browsing, office applications, and light media consumption.";
        }

        // ---- INTEL CORE (12th gen+): BX80xxx + 5 digits + optional suffix ----
        // BX8071512400F -> 12400 + F  |  BX8071514900 -> 14900
        elseif (preg_match('/^BX80\d{3}(\d{5})([A-Z]*)$/i', $cleanSku, $m)) {
            $brand = 'Intel';
            $modelNum = $m[1];  // "14900"
            $suffix = strtoupper($m[2] ?? ''); // "K", "KF", "F", etc.

            $gen = substr($modelNum, 0, 2);       // "14"
            $tierDigit = substr($modelNum, 2, 1);  // "9"

            $tierName = match($tierDigit) {
                '1' => 'Core i3',
                '4', '5', '6' => 'Core i5',
                '7' => 'Core i7',
                '9' => 'Core i9',
                default => 'Core i5'
            };

            $name = "Intel {$tierName}-{$modelNum}" . ($suffix ?: '') . " Processor";
            $category = 'Processors';

            $suffixDesc = $this->getIntelSuffixDescription($suffix);
            $tierDesc = $this->getIntelTierDescription($tierName);
            $shortDesc = "{$gen}th Gen Intel {$tierName}-{$modelNum}" . ($suffix ?: '') . " desktop processor{$suffixDesc}.";
            $description = "The {$name} is a {$gen}th generation desktop processor. {$tierDesc}{$suffixDesc}.";

            error_log("SKU IDENTIFY: {$sku} -> modelNum={$modelNum}, tierDigit={$tierDigit}, tier={$tierName}, name={$name}");
        }

        // ---- INTEL CORE (older): BX80xxxIxYYYYz (e.g. BX80684I99900K) ----
        elseif (preg_match('/^BX\d+I(\d)(\d{4})([A-Z]*)$/i', $cleanSku, $m)) {
            $brand = 'Intel';
            $tier = $m[1];
            $modelNum = $m[2];
            $suffix = strtoupper($m[3] ?? '');

            $tierName = match($tier) {
                '3' => 'Core i3', '5' => 'Core i5',
                '7' => 'Core i7', '9' => 'Core i9',
                default => 'Core i5'
            };

            $gen = substr($modelNum, 0, 1);
            $name = "Intel {$tierName}-{$modelNum}" . ($suffix ?: '') . " Processor";
            $category = 'Processors';

            $suffixDesc = $this->getIntelSuffixDescription($suffix);
            $tierDesc = $this->getIntelTierDescription($tierName);
            $shortDesc = "{$gen}th Gen Intel {$tierName}-{$modelNum}" . ($suffix ?: '') . " desktop processor{$suffixDesc}.";
            $description = "The {$name} is a {$gen}th generation desktop processor. {$tierDesc}{$suffixDesc}.";
        }

        // ---- INTEL GENERIC BX80 with digits ----
        elseif (preg_match('/^BX80\d{3}(\d+)([A-Z]*)$/i', $cleanSku, $m)) {
            $brand = 'Intel';
            $modelNum = $m[1];
            $suffix = strtoupper($m[2] ?? '');
            $name = "Intel Processor {$modelNum}" . ($suffix ?: '');
            $category = 'Processors';
            $shortDesc = "Intel desktop processor for reliable computing performance.";
            $description = "Intel processor model {$modelNum}" . ($suffix ?: '') . ". A reliable desktop processor.";
        }

        // ---- ANY BX prefix = Intel ----
        elseif (preg_match('/^BX\d+/i', $cleanSku)) {
            $brand = 'Intel';
            $name = "Intel Processor ({$sku})";
            $category = 'Processors';
            $shortDesc = "Intel desktop processor (SKU: {$sku}).";
            $description = "Intel desktop processor. SKU: {$sku}";
        }

        // ---- AMD RYZEN ----
        elseif (preg_match('/ryzen\s*(\d)\s*(\d{4})([A-Z]*)/i', $sku, $m)) {
            $brand = 'AMD';
            $tier = $m[1]; $model = $m[2];
            $suffix = strtoupper($m[3] ?? '');
            $name = "AMD Ryzen {$tier} {$model}" . ($suffix ?: '') . " Processor";
            $category = 'Processors';
            $shortDesc = "AMD Ryzen {$tier} desktop processor for high performance computing.";
            $description = "The {$name} delivers exceptional multi-threaded performance. Built on AMD's advanced architecture for gaming, content creation, and productivity.";
        }

        // ---- AMD 100-xxxxxx ----
        elseif (preg_match('/^100-\d{6}/i', $sku)) {
            $brand = 'AMD';
            $name = "AMD Processor ({$sku})";
            $category = 'Processors';
            $shortDesc = "AMD desktop processor (SKU: {$sku}).";
            $description = "AMD processor. SKU: {$sku}";
        }

        // ---- NVIDIA GPUs ----
        elseif (preg_match('/(RTX|GTX)\s*(\d{4})\s*(Ti|SUPER)?/i', $sku, $m)) {
            $brand = 'NVIDIA';
            $series = strtoupper($m[1]); $model = $m[2];
            $variant = isset($m[3]) ? ' ' . ucfirst(strtolower($m[3])) : '';
            $name = "NVIDIA GeForce {$series} {$model}{$variant}";
            $category = 'Graphics Cards';
            $shortDesc = "NVIDIA GeForce {$series} {$model}{$variant} graphics card for gaming and creative work.";
            $description = "The {$name} delivers outstanding performance for gaming, streaming, and creative applications. Features ray tracing and DLSS support.";
        }

        // ---- AMD GPUs ----
        elseif (preg_match('/RX\s*(\d{4})\s*(XT|XTX)?/i', $sku, $m)) {
            $brand = 'AMD';
            $model = $m[1];
            $variant = isset($m[2]) ? ' ' . strtoupper($m[2]) : '';
            $name = "AMD Radeon RX {$model}{$variant}";
            $category = 'Graphics Cards';
            $shortDesc = "AMD Radeon RX {$model}{$variant} graphics card for gaming and content creation.";
            $description = "The {$name} provides exceptional gaming performance. Built on AMD's RDNA architecture.";
        }

        $recognized = !empty($name);

        return [
            'name' => $name,
            'brand' => $brand,
            'category' => $category,
            'short_description' => $shortDesc,
            'description' => $description,
            'recognized' => $recognized,
        ];
    }

    /**
     * Generate a complete, production-ready product from SKU and/or short description.
     *
     * This is THE main method for all AI product generation. Every button and
     * every import path should call this. Architecture:
     *   1. identifyProductFromSku() decides the name (pattern matching, no AI)
     *   2. AI writes descriptions, SEO, specs FOR that identified product
     *   3. The AI name output is ALWAYS discarded; pattern name is forced
     *
     * @return array{success: bool, data: array}
     */
    public function generateCompleteProduct(string $sku, string $shortDescription = '', array $context = []): array
    {
        // STEP 1: Identify the product from SKU - this is LAW
        $identity = $this->identifyProductFromSku($sku);
        $verifiedName = $identity['name'];
        $verifiedBrand = $identity['brand'] ?: ($context['brand'] ?? '');
        $verifiedCategory = $identity['category'] ?: ($context['category'] ?? 'Electronics');
        $recognized = $identity['recognized'];

        // Use existing name or short description to help identify unknown products
        $existingName = $context['existingName'] ?? '';
        $existingDescription = $context['existingDescription'] ?? '';
        $price = $context['price'] ?? 0;

        // If pattern didn't recognize, try the short description or existing name
        if (!$recognized && !empty($shortDescription)) {
            // Try to identify from the short description text
            $descIdentity = $this->identifyProductFromSku($shortDescription);
            if ($descIdentity['recognized']) {
                $verifiedName = $descIdentity['name'];
                $verifiedBrand = $descIdentity['brand'];
                $verifiedCategory = $descIdentity['category'];
                $recognized = true;
            }
        }

        // Final fallback name: use existing name if available, otherwise the SKU
        $productName = $recognized ? $verifiedName : ($existingName ?: $sku);

        error_log("AI COMPLETE: SKU={$sku}, Identified='" . ($recognized ? $verifiedName : 'NO') . "', Final name='{$productName}'");

        // If no API key, return pattern-matched data only
        if (empty($this->apiKey)) {
            return $this->buildFallbackResult($productName, $verifiedBrand, $verifiedCategory, $identity, $shortDescription);
        }

        // STEP 2: Ask AI to write content FOR the identified product
        $prompt = "You are a senior e-commerce product specialist for Pricetag.co.za, a South African online store.\n\n";
        $prompt .= "Write a COMPLETE product listing for the following product:\n\n";
        $prompt .= "PRODUCT NAME: {$productName}\n";
        $prompt .= "SKU: {$sku}\n";
        $prompt .= "BRAND: {$verifiedBrand}\n";
        $prompt .= "CATEGORY: {$verifiedCategory}\n";
        if ($shortDescription) {
            $prompt .= "SUPPLIER INFO: {$shortDescription}\n";
        }
        if ($existingDescription && strlen($existingDescription) > 20) {
            $prompt .= "EXISTING DESCRIPTION: " . substr($existingDescription, 0, 300) . "\n";
        }
        if ($price > 0) {
            $prompt .= "PRICE: R" . number_format((float)$price, 2) . "\n";
        }

        $prompt .= "\n*** CRITICAL: The product name \"{$productName}\" is VERIFIED and CORRECT. ";
        $prompt .= "You MUST use this EXACT name in the 'name' field. Do NOT change the model number. ***\n";

        $prompt .= "\nGenerate ALL of the following fields as valid JSON:\n";
        $prompt .= "{\n";
        $prompt .= "  \"name\": \"{$productName}\",   // USE THIS EXACT NAME\n";
        $prompt .= "  \"short_description\": \"...\",  // Max 160 chars, compelling selling point\n";
        $prompt .= "  \"description\": \"...\",         // Detailed HTML (use <p>, <ul>, <li>, <strong>), 3-4 paragraphs\n";
        $prompt .= "  \"meta_title\": \"...\",          // Max 70 chars, SEO optimized\n";
        $prompt .= "  \"meta_description\": \"...\",    // Max 160 chars, with call-to-action\n";
        $prompt .= "  \"meta_keywords\": \"...\",       // Comma-separated, max 8 keywords\n";
        $prompt .= "  \"specifications\": [{\"name\": \"...\", \"value\": \"...\"}],  // At least 5 real specs\n";
        $prompt .= "  \"suggested_category\": \"...\",  // Best category\n";
        $prompt .= "  \"brand\": \"{$verifiedBrand}\",\n";
        $prompt .= "  \"weight\": 0.5                  // Estimated weight in kg\n";
        $prompt .= "}\n";
        $prompt .= "\nRules: All prices in ZAR (R). Write for a premium store. Include REAL specs.\n";
        $prompt .= "Respond with ONLY valid JSON. No markdown. No extra text.";

        try {
            $response = $this->makeRequest('/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a product content writer. Write product descriptions and SEO content. Always respond with valid JSON only. NEVER change the product name - use it exactly as given.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 2500,
                'temperature' => 0.3,
            ]);

            $content = $response['choices'][0]['message']['content'] ?? '';
            $content = preg_replace('/^```json\s*/i', '', $content);
            $content = preg_replace('/^```\s*/i', '', $content);
            $content = preg_replace('/\s*```$/i', '', $content);
            $content = trim($content);

            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('AI JSON parse failed: ' . json_last_error_msg());
                return $this->buildFallbackResult($productName, $verifiedBrand, $verifiedCategory, $identity, $shortDescription);
            }

            // STEP 3: FORCE the correct identity - AI output for name/brand/category is DISCARDED
            $data['name'] = $productName;
            $data['brand'] = $verifiedBrand;
            if ($recognized) {
                $data['suggested_category'] = $verifiedCategory;
            }

            // Ensure all keys exist
            $data = array_merge([
                'name' => $productName,
                'short_description' => $identity['short_description'] ?? '',
                'description' => $identity['description'] ?? '',
                'meta_title' => '',
                'meta_description' => '',
                'meta_keywords' => '',
                'specifications' => [],
                'suggested_category' => $verifiedCategory,
                'brand' => $verifiedBrand,
                'weight' => null,
                'is_taxable' => true,
            ], $data);

            // Force name again after merge (in case AI put it in the response)
            $data['name'] = $productName;
            $data['brand'] = $verifiedBrand;

            return ['success' => true, 'data' => $data];

        } catch (\Exception $e) {
            error_log('AI Complete Product Error: ' . $e->getMessage());
            return $this->buildFallbackResult($productName, $verifiedBrand, $verifiedCategory, $identity, $shortDescription);
        }
    }

    /**
     * Generate product info from SKU - lightweight version.
     * Delegates to identifyProductFromSku() for the name, optionally enhances with AI.
     */
    public function generateFromSku(string $sku, array $additionalInfo = []): array
    {
        // Always use pattern matching first
        $identity = $this->identifyProductFromSku($sku);

        if ($identity['recognized']) {
            $result = [
                'name' => $identity['name'],
                'brand' => $identity['brand'],
                'short_description' => $identity['short_description'],
                'description' => $identity['description'],
                'suggested_category' => $identity['category'],
            ];

            // Optionally enhance descriptions with AI if available
            if (!empty($this->apiKey)) {
                $complete = $this->generateCompleteProduct($sku, '', $additionalInfo);
                if (!empty($complete['success'])) {
                    // Keep the verified name, take AI's richer descriptions
                    $result['short_description'] = $complete['data']['short_description'] ?: $result['short_description'];
                    $result['description'] = $complete['data']['description'] ?: $result['description'];
                    $result['meta_title'] = $complete['data']['meta_title'] ?? '';
                    $result['meta_description'] = $complete['data']['meta_description'] ?? '';
                    $result['meta_keywords'] = $complete['data']['meta_keywords'] ?? '';
                    $result['specifications'] = $complete['data']['specifications'] ?? [];
                }
            }

            return $result;
        }

        // Pattern not recognized - if we have API, let AI try (but name gets validated)
        if (!empty($this->apiKey)) {
            $complete = $this->generateCompleteProduct($sku, $additionalInfo['short_description'] ?? '', $additionalInfo);
            if (!empty($complete['success'])) {
                return $complete['data'];
            }
        }

        // Pure fallback
        $brand = $additionalInfo['brand'] ?? '';
        return [
            'name' => $sku,
            'brand' => $brand,
            'short_description' => $brand ? "{$brand} product - quality and reliability." : "Quality product.",
            'description' => $brand ? "The {$sku} from {$brand} delivers reliable performance." : "Product SKU: {$sku}.",
            'suggested_category' => $additionalInfo['category'] ?? 'Electronics',
        ];
    }

    /**
     * Generate product content (descriptions/SEO) for an existing named product.
     * Used by the "Generate AI Content" button in the SEO tab.
     */
    public function generateProductContent(array $product): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'OpenAI API key not configured'];
        }

        $productName = $product['name'] ?? '';
        $sku = $product['sku'] ?? '';

        // If we have a SKU, use the full pipeline (pattern match + AI)
        if (!empty($sku)) {
            $result = $this->generateCompleteProduct($sku, $product['short_description'] ?? '', [
                'existingName' => $productName,
                'existingDescription' => $product['description'] ?? '',
                'price' => $product['price'] ?? 0,
                'category' => $product['category'] ?? '',
            ]);

            if (!empty($result['success'])) {
                return $result;
            }
        }

        // Fallback: generate content based on the name alone
        $prompt = "Write e-commerce content for: {$productName}\n";
        $prompt .= "Price: R" . number_format((float)($product['price'] ?? 0), 2) . "\n";
        $prompt .= "\nJSON format: {\"short_description\": \"...\", \"description\": \"...\", \"meta_title\": \"...\", \"meta_description\": \"...\", \"meta_keywords\": \"...\", \"specifications\": [{\"name\":\"\",\"value\":\"\"}]}\n";
        $prompt .= "Respond ONLY with valid JSON.";

        try {
            $response = $this->makeRequest('/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Generate e-commerce product content. Respond with valid JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 1000,
                'temperature' => 0.7,
            ]);

            $content = $response['choices'][0]['message']['content'] ?? '';
            $content = preg_replace('/^```json\s*/i', '', $content);
            $content = preg_replace('/^```\s*/i', '', $content);
            $content = preg_replace('/\s*```$/i', '', $content);

            $data = json_decode(trim($content), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Failed to parse AI response'];
            }

            return ['success' => true, 'data' => $data];
        } catch (\Exception $e) {
            return ['error' => 'Failed to generate content: ' . $e->getMessage()];
        }
    }

    /**
     * Search and fill product information (used for unknown products)
     */
    public function searchProductInfo(string $productName, string $sku = ''): array
    {
        // If we have a SKU, try pattern matching first
        if (!empty($sku)) {
            $identity = $this->identifyProductFromSku($sku);
            if ($identity['recognized']) {
                $productName = $identity['name'];
            }
        }

        if (empty($this->apiKey)) {
            return ['error' => 'OpenAI API key not configured'];
        }

        $prompt = "Research product: {$productName}\n";
        if ($sku) $prompt .= "SKU: {$sku}\n";
        $prompt .= "\nJSON: {\"description\": \"\", \"short_description\": \"\", \"meta_title\": \"\", \"meta_description\": \"\", \"specifications\": [{\"name\":\"\",\"value\":\"\"}], \"suggested_category\": \"\"}\n";
        $prompt .= "Respond ONLY with valid JSON.";

        try {
            $response = $this->makeRequest('/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Product research assistant. Valid JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 1000,
                'temperature' => 0.5,
            ]);

            $content = $response['choices'][0]['message']['content'] ?? '';
            $content = preg_replace('/^```json\s*/i', '', $content);
            $content = preg_replace('/^```\s*/i', '', $content);
            $content = preg_replace('/\s*```$/i', '', $content);

            $data = json_decode(trim($content), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Failed to parse AI response'];
            }
            return ['success' => true, 'data' => $data];
        } catch (\Exception $e) {
            return ['error' => 'Failed to search product: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Build fallback result when AI is unavailable
     */
    private function buildFallbackResult(string $name, string $brand, string $category, array $identity, string $shortDescription): array
    {
        return [
            'success' => true,
            'data' => [
                'name' => $name,
                'short_description' => $identity['short_description'] ?: $shortDescription,
                'description' => $identity['description'] ?: '',
                'meta_title' => substr($name . ' | Buy Online at Pricetag.co.za', 0, 70),
                'meta_description' => substr(($identity['short_description'] ?: $shortDescription) . ' Shop at Pricetag.co.za - fast delivery across South Africa.', 0, 160),
                'meta_keywords' => implode(', ', array_filter([$brand, $category, 'buy online', 'South Africa', 'Pricetag'])),
                'specifications' => [],
                'suggested_category' => $category,
                'brand' => $brand,
                'weight' => null,
                'is_taxable' => true,
            ],
        ];
    }

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
