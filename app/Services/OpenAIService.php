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

        $prompt = "You are **Taggy**, the AI shopping assistant for {$storeName} (pricetag.co.za), a South African e-commerce store.\n\n";

        $prompt .= "YOUR PERSONALITY:\n";
        $prompt .= "- You are warm, witty, and genuinely helpful — like a knowledgeable friend who works at the store\n";
        $prompt .= "- Keep answers concise but complete — no fluff, just value\n";
        $prompt .= "- Use a casual-professional tone. Friendly but never sloppy\n";
        $prompt .= "- When listing info, use bullet points with • for readability\n";
        $prompt .= "- If you don't know something specific, say so honestly and offer alternatives\n\n";

        $prompt .= "STORE KNOWLEDGE:\n";
        $prompt .= "- All prices are in {$currency} (South African Rand, symbol: R)\n";
        $prompt .= "- Shipping: Nationwide delivery across South Africa. Standard 3-5 business days. Free shipping on orders over R500. Express delivery available in major metros (1-2 days, R150)\n";
        $prompt .= "- Returns: 30-day return policy. Items must be unused, in original packaging. Contact support to initiate. Refunds processed within 5-7 business days after receiving the return\n";
        $prompt .= "- Payment: Visa, MasterCard, EFT bank transfers, Cash on Delivery (select areas), Instant EFT via Ozow\n";
        $prompt .= "- Customer support: info@pricetag.co.za / 011 100 2232 / Mon-Fri 8am-5pm\n";
        $prompt .= "- Order tracking: Customers can track via 'My Orders' in their account page, or use the tracking number emailed after dispatch\n\n";

        $prompt .= "RULES:\n";
        $prompt .= "- NEVER make up product info — only reference products provided in context below\n";
        $prompt .= "- When recommending products, mention the name and price\n";
        $prompt .= "- For order-specific issues (cancellation, damaged items), direct to customer support\n";
        $prompt .= "- If asked about competitor prices or stores, stay neutral — focus on our value\n";
        $prompt .= "- Always sign off helpfully: offer to help with anything else\n\n";

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

        // Detect manufacturer brand prefix in SKU (but don't strip yet - try matching first)
        // e.g. "ASUS DUAL-RTX3050-O6G" → skuBrand="ASUS", strippedSku="DUAL-RTX3050-O6G"
        $skuBrand = '';
        $strippedSku = '';
        $brandPrefixes = [
            'ASUS' => 'ASUS', 'Gigabyte' => 'Gigabyte', 'GIGABYTE' => 'Gigabyte',
            'MSI' => 'MSI', 'EVGA' => 'EVGA', 'Zotac' => 'Zotac', 'ZOTAC' => 'Zotac',
            'Corsair' => 'Corsair', 'Samsung' => 'Samsung', 'Kingston' => 'Kingston',
            'Intel' => 'Intel', 'AMD' => 'AMD', 'Logitech' => 'Logitech', 'Razer' => 'Razer',
            'HyperX' => 'HyperX', 'Crucial' => 'Crucial', 'Sapphire' => 'Sapphire',
            'XFX' => 'XFX', 'PowerColor' => 'PowerColor', 'ASRock' => 'ASRock',
            'PNY' => 'PNY', 'Palit' => 'Palit', 'Gainward' => 'Gainward', 'Inno3D' => 'Inno3D',
            'Thermaltake' => 'Thermaltake', 'NZXT' => 'NZXT', 'Biostar' => 'Biostar',
            'Cooler Master' => 'Cooler Master', 'be quiet!' => 'be quiet!',
        ];
        foreach ($brandPrefixes as $prefix => $normalizedBrand) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '[\s\-]+(.+)$/i', $cleanSku, $prefixMatch)) {
                $skuBrand = $normalizedBrand;
                $strippedSku = trim($prefixMatch[1]);
                break;
            }
        }

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

        // ---- GIGABYTE NVIDIA GPUs: GV-N{model}{variant}-{memory}GD ----
        // e.g. GV-N5090GAMING OC-32GD -> Gigabyte GeForce RTX 5090 Gaming OC 32GB
        // e.g. GV-N4070EAGLE OC-12GD -> Gigabyte GeForce RTX 4070 Eagle OC 12GB
        elseif (preg_match('/^GV-N(\d{3,4})\s*([A-Z\s]+?)[\s-]+(\d+)G/i', $cleanSku, $m)) {
            $brand = 'Gigabyte';
            $gpuModel = $m[1]; // 5090, 4070, etc.
            $variant = trim($m[2]); // GAMING OC, EAGLE OC, AERO OC, WINDFORCE, etc.
            $memory = $m[3]; // 32, 12, etc.

            // Determine GPU series from model number
            $gpuSeries = ((int)$gpuModel >= 2000) ? 'RTX' : 'GTX';
            $variantClean = ucwords(strtolower($variant)); // "Gaming Oc" -> clean up

            $name = "Gigabyte GeForce {$gpuSeries} {$gpuModel} {$variantClean} {$memory}GB";
            $category = 'Graphics Cards';
            $shortDesc = "Gigabyte GeForce {$gpuSeries} {$gpuModel} {$variantClean} with {$memory}GB GDDR memory for high-performance gaming.";
            $description = "The {$name} graphics card delivers exceptional gaming and creative performance. Built with Gigabyte's advanced cooling solution for optimal thermals and low noise. Features NVIDIA Ray Tracing and DLSS for next-gen visuals.";
        }

        // ---- GIGABYTE AMD GPUs: GV-R{model}{variant}-{memory}GD ----
        elseif (preg_match('/^GV-R(\d{3,4})\s*([A-Z\s]+?)[\s-]+(\d+)G/i', $cleanSku, $m)) {
            $brand = 'Gigabyte';
            $gpuModel = $m[1];
            $variant = trim($m[2]);
            $memory = $m[3];
            $variantClean = ucwords(strtolower($variant));

            $name = "Gigabyte Radeon RX {$gpuModel} {$variantClean} {$memory}GB";
            $category = 'Graphics Cards';
            $shortDesc = "Gigabyte Radeon RX {$gpuModel} {$variantClean} with {$memory}GB memory for high-performance gaming.";
            $description = "The {$name} graphics card delivers outstanding gaming performance. Built with Gigabyte's advanced cooling for optimal thermals.";
        }

        // ---- ASUS GPUs (Premium lines): ROG-STRIX-RTX5090-O32G, TUF-RTX4070TI-O12G, DUAL-RTX4060-O8G ----
        elseif (preg_match('/^(ROG[- ]?STRIX|TUF|DUAL|PRIME|PROART)[- ]?(RTX|GTX|RX)\s*(\d{3,4})\s*(Ti|SUPER|XT|XTX)?[- ]?O?(\d+)G/i', $cleanSku, $m)) {
            $brand = 'ASUS';
            $line = str_replace('-', ' ', strtoupper($m[1])); // ROG STRIX, TUF, DUAL
            $series = strtoupper($m[2]); // RTX, GTX, RX
            $gpuModel = $m[3];
            $variant = !empty($m[4]) ? ' ' . ucfirst(strtolower($m[4])) : '';
            $memory = $m[5];

            $gpuBrand = ($series === 'RX') ? 'Radeon' : 'GeForce';
            $name = "ASUS {$line} {$gpuBrand} {$series} {$gpuModel}{$variant} OC {$memory}GB";
            $category = 'Graphics Cards';
            $shortDesc = "ASUS {$line} {$gpuBrand} {$series} {$gpuModel}{$variant} with {$memory}GB memory. Factory overclocked for maximum performance.";
            $description = "The {$name} features ASUS's premium {$line} design with advanced cooling and factory overclocking. Delivers exceptional performance for 4K gaming and content creation.";
        }

        // ---- Generic GPU from SKU structure: GT710-SL-2GD5-BRK-EVO, PH-GTX1650-O4G, EX-RX570-O4G ----
        // Extracts GPU model and memory WITHOUT assuming brand (brand comes from CSV context)
        elseif (preg_match('/^(?:(?:PH|EX|Phoenix)[- ]?)?(GT|GTX|RTX|RX)\s*(\d{3,4})\s*(Ti|SUPER|XT|XTX)?[- ].*?(\d+)GD?\d?/i', $cleanSku, $m)) {
            // Don't set $brand - let it come from CSV context or AI
            $series = strtoupper($m[1]); // GT, GTX, RTX, RX
            $gpuModel = $m[2]; // 710, 1030, 1650, etc.
            $variant = !empty($m[3]) ? ' ' . ucfirst(strtolower($m[3])) : '';
            $memory = $m[4];

            $gpuBrand = ($series === 'RX') ? 'Radeon' : 'GeForce';
            $name = "{$gpuBrand} {$series} {$gpuModel}{$variant} {$memory}GB";
            $category = 'Graphics Cards';
            $shortDesc = "{$gpuBrand} {$series} {$gpuModel}{$variant} graphics card with {$memory}GB memory.";
            $description = "The {$gpuBrand} {$series} {$gpuModel}{$variant} {$memory}GB is a reliable graphics card for everyday computing, multimedia, and gaming.";
        }

        // ---- MSI GPUs: MSI RTX 5090 GAMING X TRIO 32G, MSI GeForce RTX 4070 VENTUS 3X OC 12G ----
        elseif (preg_match('/^MSI\s*(?:GeForce|Radeon)?\s*(RTX|GTX|GT|RX)\s*(\d{3,4})\s*(Ti|SUPER|XT|XTX)?\s*([A-Z0-9\s]+?)\s+(\d+)G/i', $cleanSku, $m)) {
            $brand = 'MSI';
            $series = strtoupper($m[1]);
            $gpuModel = $m[2];
            $variant = !empty($m[3]) ? ' ' . ucfirst(strtolower($m[3])) : '';
            $line = ucwords(strtolower(trim($m[4]))); // Gaming X Trio, Ventus 3X OC
            $memory = $m[5];

            $gpuBrand = ($series === 'RX') ? 'Radeon' : 'GeForce';
            $name = "MSI {$gpuBrand} {$series} {$gpuModel}{$variant} {$line} {$memory}GB";
            $category = 'Graphics Cards';
            $shortDesc = "MSI {$gpuBrand} {$series} {$gpuModel}{$variant} {$line} with {$memory}GB memory for gaming and creative work.";
            $description = "The {$name} features MSI's premium cooling and build quality. Designed for maximum performance in gaming and content creation.";
        }

        // ---- EVGA GPUs: xxG-Px-xxxx ----
        elseif (preg_match('/^(\d{2})G-P(\d)-(\d{4})/i', $cleanSku, $m)) {
            $brand = 'EVGA';
            $memory = $m[1];
            $name = "EVGA GeForce Graphics Card ({$sku})";
            $category = 'Graphics Cards';
            $shortDesc = "EVGA GeForce graphics card with {$memory}GB memory.";
            $description = "EVGA graphics card. SKU: {$sku}. Known for excellent cooling and customer support.";
        }

        // ---- ZOTAC GPUs: ZT-{model} ----
        elseif (preg_match('/^ZT-(\w+)/i', $cleanSku, $m)) {
            $brand = 'Zotac';
            $name = "Zotac Gaming GeForce Graphics Card ({$sku})";
            $category = 'Graphics Cards';
            $shortDesc = "Zotac Gaming graphics card for gaming performance.";
            $description = "Zotac Gaming graphics card. SKU: {$sku}. Compact design with efficient cooling.";
        }

        // ---- NVIDIA GPUs (generic catch-all, after manufacturer-specific patterns) ----
        elseif (preg_match('/(RTX|GTX|GT)\s*(\d{3,4})\s*(Ti|SUPER)?/i', $sku, $m)) {
            $brand = 'NVIDIA';
            $series = strtoupper($m[1]); $model = $m[2];
            $variant = isset($m[3]) ? ' ' . ucfirst(strtolower($m[3])) : '';
            $name = "NVIDIA GeForce {$series} {$model}{$variant}";
            $category = 'Graphics Cards';
            $shortDesc = "NVIDIA GeForce {$series} {$model}{$variant} graphics card for gaming and creative work.";
            $description = "The {$name} delivers outstanding performance for gaming, streaming, and creative applications. Features ray tracing and DLSS support.";
        }

        // ---- AMD GPUs (generic catch-all, after manufacturer-specific patterns) ----
        elseif (preg_match('/RX\s*(\d{3,4})\s*(XT|XTX)?/i', $sku, $m)) {
            $brand = 'AMD';
            $model = $m[1];
            $variant = isset($m[2]) ? ' ' . strtoupper($m[2]) : '';
            $name = "AMD Radeon RX {$model}{$variant}";
            $category = 'Graphics Cards';
            $shortDesc = "AMD Radeon RX {$model}{$variant} graphics card for gaming and content creation.";
            $description = "The {$name} provides exceptional gaming performance. Built on AMD's RDNA architecture.";
        }

        // ---- CORSAIR products: CMx/CMK (RAM), RM/HX/AX (PSU), etc. ----
        elseif (preg_match('/^CM([KW])(\d+)GX(\d)M(\d)([A-Z])(\d+)([A-Z]\d+)?/i', $cleanSku, $m)) {
            $brand = 'Corsair';
            $totalMem = $m[2];
            $ddrGen = $m[3];
            $modules = $m[4];
            $speed = $m[6];
            $perModule = (int)$totalMem / (int)$modules;
            $name = "Corsair Vengeance DDR{$ddrGen} {$totalMem}GB ({$modules}x{$perModule}GB) {$speed}MHz";
            $category = 'Memory / RAM';
            $shortDesc = "Corsair Vengeance DDR{$ddrGen} {$totalMem}GB ({$modules}x{$perModule}GB) desktop memory running at {$speed}MHz.";
            $description = "The {$name} delivers high-performance memory for gaming and productivity. XMP support for easy overclocking.";
        }

        // ---- SAMSUNG SSDs: MZ-V{gen}... ----
        elseif (preg_match('/^MZ-/i', $cleanSku)) {
            $brand = 'Samsung';
            $name = "Samsung SSD ({$sku})";
            $category = 'Storage';
            $shortDesc = "Samsung solid-state drive for fast storage performance.";
            $description = "Samsung SSD. SKU: {$sku}. Industry-leading performance and reliability.";
        }

        // ---- WESTERN DIGITAL / WD: WD prefix or WDS ----
        elseif (preg_match('/^WD[S]?\d/i', $cleanSku)) {
            $brand = 'Western Digital';
            $name = "Western Digital Drive ({$sku})";
            $category = 'Storage';
            $shortDesc = "Western Digital storage drive for reliable data storage.";
            $description = "Western Digital drive. SKU: {$sku}";
        }

        // ---- LOGITECH: prefix 910- (mice), 920- (keyboards), 981- (headsets) ----
        elseif (preg_match('/^(910|920|981|980|993)-/i', $cleanSku, $m)) {
            $brand = 'Logitech';
            $prodType = match($m[1]) {
                '910' => 'Mouse',
                '920' => 'Keyboard',
                '981', '980' => 'Headset',
                '993' => 'Webcam',
                default => 'Peripheral',
            };
            $name = "Logitech {$prodType} ({$sku})";
            $category = 'Peripherals';
            $shortDesc = "Logitech {$prodType} for computing.";
            $description = "Logitech {$prodType}. SKU: {$sku}";
        }

        // If SKU had a brand prefix, try ALSO matching the stripped version for a more specific result
        // e.g. "ASUS DUAL-RTX3050-O6G" matched generic NVIDIA catch-all → but "DUAL-RTX3050-O6G"
        //       matches the ASUS-specific pattern, giving a richer name with line/memory details
        // Only prefer stripped result if it's more specific (non-generic brand or richer name)
        $genericBrandNames = ['NVIDIA', 'AMD'];
        if (!empty($skuBrand) && !empty($strippedSku)) {
            $originalIsGeneric = empty($brand) || in_array($brand, $genericBrandNames);
            // Only retry if original didn't match or matched a generic catch-all pattern
            if (empty($name) || $originalIsGeneric) {
                $strippedResult = $this->identifyProductFromSku($strippedSku);
                if ($strippedResult['recognized']) {
                    // Always prefer stripped result when original was generic -
                    // the stripped SKU matches manufacturer-specific patterns with richer details
                    $name = $strippedResult['name'];
                    $brand = $strippedResult['brand'];
                    $category = $strippedResult['category'];
                    $shortDesc = $strippedResult['short_description'];
                    $description = $strippedResult['description'];
                }
            }
        }

        $recognized = !empty($name);

        // If we detected a brand prefix from the SKU, it's the real manufacturer brand.
        // Override generic chipset brands (NVIDIA, AMD) with the actual product brand.
        // e.g. SKU "ASUS GT710-SL-2GD5-BRK" → skuBrand="ASUS" overrides empty brand from generic GPU pattern
        if (!empty($skuBrand)) {
            $genericBrands = ['NVIDIA', 'AMD'];
            if (empty($brand) || in_array($brand, $genericBrands)) {
                $brand = $skuBrand;
            }
        }

        // Ensure brand name is always first in the product name
        if ($recognized && !empty($brand) && !empty($name) && stripos($name, $brand) !== 0) {
            // Remove any existing brand occurrence to avoid duplication then prepend
            $nameWithoutBrand = preg_replace('/\b' . preg_quote($brand, '/') . '\b\s*/i', '', $name);
            $name = $brand . ' ' . trim($nameWithoutBrand);
        }

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
     * Look up real product data from the web using search results.
     * Searches for the product specs and returns combined search result data
     * (titles + snippets + any fetchable pages) for AI context.
     *
     * @return string|null Extracted product data text, or null if lookup failed
     */
    public function fetchManufacturerData(string $sku, string $brand, string $productName): ?string
    {
        // Strategy 1: Search DuckDuckGo for product specs - extract titles + snippets
        $searchData = $this->searchProductSpecs($sku, $brand, $productName);

        // Strategy 2: Try to fetch a full product page for more detailed specs
        $pageData = $this->searchAndFetchProductPage($sku, $brand, $productName);
        if ($pageData) {
            $pageText = $this->extractProductText($pageData);
            if ($pageText && strlen($pageText) > 200) {
                // Combine search snippets with full page data
                $combined = $searchData ? $searchData . "\n\nDETAILED PRODUCT PAGE:\n" . $pageText : $pageText;
                return substr($combined, 0, 5000);
            }
        }

        return $searchData;
    }

    /**
     * Detect bot-check, captcha, or rate-limit pages.
     * Returns true if the response is NOT real content.
     */
    private function isBotCheckPage(?string $html): bool
    {
        if (empty($html)) return true;

        $botIndicators = [
            'bot check in progress',
            'captcha',
            'Select all squares',
            'Please complete the following challenge',
            'anomaly-modal',
            'Automated bot check',
            'Please verify you are a human',
            'Access denied',
            'Just a moment...',     // Cloudflare
            'Enable JavaScript and cookies to continue', // Cloudflare
            'Checking your browser', // Cloudflare
            'cf-browser-verification',
        ];

        $htmlLower = strtolower($html);
        foreach ($botIndicators as $indicator) {
            if (strpos($htmlLower, strtolower($indicator)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Search DuckDuckGo for the product and extract titles + snippets from results.
     * Tries SKU first, falls back to product name if SKU returns no results.
     */
    private function searchProductSpecs(string $sku, string $brand, string $productName): ?string
    {
        // Clean the SKU for search - remove brand prefix to avoid redundancy
        $searchSku = preg_replace('/^' . preg_quote($brand, '/') . '[\s\-]+/i', '', $sku);
        if (empty($searchSku)) $searchSku = $sku;

        // Try SKU-based search first, fall back to product name
        $queries = [
            "{$searchSku} specifications",
        ];
        // If product name is different from SKU, add it as a fallback query
        if (!empty($productName) && $productName !== $sku && $productName !== $searchSku) {
            $queries[] = "{$productName} specifications";
        }

        foreach ($queries as $query) {
            $result = $this->performDDGSearch($query, $brand, $searchSku);
            if ($result) return $result;
        }

        return null;
    }

    /**
     * Perform a single DDG search and extract titles + snippets.
     */
    private function performDDGSearch(string $query, string $brand, string $searchSku): ?string
    {
        $searchQuery = urlencode($query);
        $searchUrl = "https://html.duckduckgo.com/html/?q={$searchQuery}";

        $html = $this->fetchPage($searchUrl);
        if (empty($html) || $this->isBotCheckPage($html)) {
            return null;
        }

        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Extract titles and snippets from search results
        $results = [];
        if (preg_match_all('/<a class="result__a"[^>]*>(.+?)<\/a>/si', $html, $titleMatches)) {
            foreach ($titleMatches[1] as $title) {
                $results[] = strip_tags($title);
            }
        }

        $snippets = [];
        if (preg_match_all('/class="result__snippet"[^>]*>(.*?)<\/td>/si', $html, $snippetMatches)) {
            foreach ($snippetMatches[1] as $snippet) {
                $text = strip_tags($snippet);
                $text = preg_replace('/\s+/', ' ', trim($text));
                if (strlen($text) > 30) {
                    $snippets[] = $text;
                }
            }
        }

        if (empty($results) && empty($snippets)) {
            return null;
        }

        // Build context from search results
        $output = "WEB SEARCH RESULTS for \"{$brand} {$searchSku}\":\n";
        $maxResults = min(count($results), 6);
        for ($i = 0; $i < $maxResults; $i++) {
            $output .= "\n" . ($results[$i] ?? '');
            if (!empty($snippets[$i])) {
                $output .= "\n  " . $snippets[$i];
            }
        }

        $output = trim($output);
        return strlen($output) > 80 ? $output : null;
    }

    /**
     * Build a direct manufacturer URL from the SKU/brand.
     * Returns null for brands without predictable URL patterns.
     */
    private function buildManufacturerUrl(string $sku, string $brand, string $productName): ?string
    {
        $cleanSku = preg_replace('/-(CA|US|EU|UK|AU|SA)$/i', '', $sku);

        // Strip brand prefix from SKU for URL construction
        $skuBody = preg_replace('/^' . preg_quote($brand, '/') . '[\s\-]+/i', '', $cleanSku);

        switch (strtolower($brand)) {
            case 'gigabyte':
                // Gigabyte: model number maps directly to URL
                // GV-N4070EAGLE OC-12GD → /Graphics-Card/GV-N4070EAGLE-OC-12GD/sp
                $model = str_replace(' ', '-', $cleanSku);
                return "https://www.gigabyte.com/Graphics-Card/{$model}/sp";

            case 'asus':
                // ASUS: product slug uses the SKU line name
                // DUAL-RTX3050-O6G → try asus.com search
                return null; // ASUS URLs are too complex to construct - use search fallback

            case 'msi':
                // MSI uses marketing names, not SKUs - construct from product name
                // "MSI GeForce RTX 5090 Gaming X Trio 32GB" → GeForce-RTX-5090-32G-GAMING-X-TRIO
                if (!empty($productName)) {
                    $slug = preg_replace('/^MSI\s+/i', '', $productName);
                    $slug = str_replace(' ', '-', $slug);
                    return "https://www.msi.com/Graphics-Card/{$slug}/Specification";
                }
                return null;

            case 'corsair':
                // Corsair: part number is in the URL path
                $partNum = strtolower($skuBody ?: $cleanSku);
                return "https://www.corsair.com/us/en/p/memory/{$partNum}/";

            case 'intel':
                // Intel ARK search - use search fallback instead of direct URL
                return null;

            case 'amd':
                // AMD: construct from Ryzen model name
                // "AMD Ryzen 7 7800X3D" → /ryzen/7000-series/amd-ryzen-7-7800x3d.html
                if (preg_match('/Ryzen\s+(\d)\s+(\d)(\d{3})(\w*)/i', $productName, $m)) {
                    $tier = $m[1];
                    $series = $m[2] . '000';
                    $model = strtolower($m[2] . $m[3] . $m[4]);
                    return "https://www.amd.com/en/products/processors/desktops/ryzen/{$series}-series/amd-ryzen-{$tier}-{$model}.html";
                }
                return null;

            default:
                return null;
        }
    }

    /**
     * Search DuckDuckGo for the product spec page and fetch it.
     * Tries manufacturer site first, then trusted spec sites (TechPowerUp, Icecat, etc.)
     */
    private function searchAndFetchProductPage(string $sku, string $brand, string $productName): ?string
    {
        // Strategy A: Try direct manufacturer URL first (fastest, no DDG needed)
        $directUrl = $this->buildManufacturerUrl($sku, $brand, $productName);
        if ($directUrl) {
            $page = $this->fetchPage($directUrl);
            if ($page && strlen($page) > 1000 && !$this->isBotCheckPage($page)) {
                return $page;
            }
        }

        // Strategy B: Search DuckDuckGo for the product spec page
        $searchSku = preg_replace('/^' . preg_quote($brand, '/') . '[\s\-]+/i', '', $sku);
        if (empty($searchSku)) $searchSku = $sku;

        $searchQuery = urlencode("{$searchSku} {$brand} specifications");
        $searchUrl = "https://html.duckduckgo.com/html/?q={$searchQuery}";

        $html = $this->fetchPage($searchUrl);
        if (empty($html) || $this->isBotCheckPage($html)) {
            return null;
        }

        // Manufacturer domains (preferred) and trusted spec sites (fallback)
        $brandDomains = [
            'asus' => 'asus.com', 'gigabyte' => 'gigabyte.com', 'msi' => 'msi.com',
            'intel' => 'intel.com', 'amd' => 'amd.com', 'corsair' => 'corsair.com',
            'samsung' => 'samsung.com', 'kingston' => 'kingston.com', 'logitech' => 'logitech.com',
            'razer' => 'razer.com', 'evga' => 'evga.com', 'zotac' => 'zotac.com',
            'sapphire' => 'sapphire-tech.com', 'xfx' => 'xfxforce.com',
            'powercolor' => 'powercolor.com', 'asrock' => 'asrock.com',
        ];
        $trustedSpecSites = [
            'techpowerup.com', 'pangoly.com', 'icecat.biz',
            'ark.intel.com', 'gpu-monkey.com', 'nanoreview.net',
        ];

        $manufacturerDomain = $brandDomains[strtolower($brand)] ?? null;

        // Extract result URLs from DuckDuckGo HTML
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $resultUrls = [];
        if (preg_match_all('/class="result__a"[^>]*href="([^"]+)"/i', $html, $matches)) {
            foreach ($matches[1] as $rawUrl) {
                if (preg_match('/uddg=([^&]+)/', $rawUrl, $urlMatch)) {
                    $resultUrls[] = urldecode($urlMatch[1]);
                }
            }
        }

        // Try manufacturer site first
        if ($manufacturerDomain) {
            foreach ($resultUrls as $url) {
                if (stripos($url, $manufacturerDomain) !== false) {
                    $page = $this->fetchPage($url);
                    if ($page && strlen($page) > 1000 && !$this->isBotCheckPage($page)) return $page;
                }
            }
        }

        // Fall back to trusted spec sites (these rarely block scraping)
        foreach ($resultUrls as $url) {
            foreach ($trustedSpecSites as $specSite) {
                if (stripos($url, $specSite) !== false) {
                    $page = $this->fetchPage($url);
                    if ($page && strlen($page) > 1000 && !$this->isBotCheckPage($page)) return $page;
                }
            }
        }

        return null;
    }

    /**
     * Fetch a URL with browser-like headers. Reuses the same approach as ProductImageService.
     */
    private function fetchPage(string $url): ?string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.9',
                'Accept-Encoding: gzip, deflate',
                'Cache-Control: no-cache',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_COOKIEFILE => '',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($response)) {
            return null;
        }

        return $response;
    }

    /**
     * Extract useful product text from HTML page.
     * Strips navigation, scripts, styles and keeps spec tables and descriptions.
     * Truncates to a reasonable length for AI context.
     */
    private function extractProductText(string $html): ?string
    {
        // Remove scripts, styles, nav, header, footer
        $html = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);
        $html = preg_replace('/<nav[^>]*>.*?<\/nav>/si', '', $html);
        $html = preg_replace('/<header[^>]*>.*?<\/header>/si', '', $html);
        $html = preg_replace('/<footer[^>]*>.*?<\/footer>/si', '', $html);
        $html = preg_replace('/<!--.*?-->/s', '', $html);

        // Convert table cells to readable format
        $html = preg_replace('/<\/t[hd]>/i', ' | ', $html);
        $html = preg_replace('/<tr[^>]*>/i', "\n", $html);

        // Convert list items and headings to lines
        $html = preg_replace('/<\/li>/i', "\n", $html);
        $html = preg_replace('/<\/[hH]\d>/i', "\n", $html);
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\/p>/i', "\n", $html);
        $html = preg_replace('/<\/div>/i', "\n", $html);

        // Strip all remaining HTML tags
        $text = strip_tags($html);

        // Clean up whitespace
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $text);
        $text = trim($text);

        // Truncate to ~4000 chars to keep AI prompt reasonable
        if (strlen($text) > 4000) {
            $text = substr($text, 0, 4000) . "\n[... truncated]";
        }

        // Only return if we got meaningful content (not just navigation/cookie text)
        if (strlen($text) < 100) {
            return null;
        }

        return $text;
    }

    /**
     * Generate a complete, production-ready product from SKU and/or short description.
     *
     * This is THE main method for all AI product generation. Every button and
     * every import path should call this. Architecture:
     *   1. identifyProductFromSku() decides the name (pattern matching, no AI)
     *   2. Fetch real specs from manufacturer website when possible
     *   3. AI writes descriptions, SEO, specs using real manufacturer data
     *   4. The AI name output is ALWAYS discarded; pattern name is forced
     *
     * @return array{success: bool, data: array}
     */
    public function generateCompleteProduct(string $sku, string $shortDescription = '', array $context = []): array
    {
        // Clean SKU - remove regional suffixes (needed for name validation below)
        $cleanSku = preg_replace('/-(CA|US|EU|UK|AU|SA)$/i', '', $sku);

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

        // Extract product name from short description when pattern matching failed
        // Supplier short descriptions contain the real product name, separated by ";" or "/"
        // e.g. "GIGABYTE nVidia GeForce RTX 5090 GAMING OC - 32GB GDDR7; 512-Bit Memory Bus; ..."
        // e.g. "ASUS Graphics Card/NVIDIA/PCIe2.0/2GB GDDR5/1xHDMI/1xD-Sub/1xDVI/300w/ 17x6.9x3.9cm."
        $nameFromDesc = '';
        if (!$recognized && !empty($shortDescription)) {
            // Split on common supplier separators: semicolons or forward slashes
            if (strpos($shortDescription, ';') !== false) {
                $extractedName = trim(explode(';', $shortDescription)[0]);
            } elseif (strpos($shortDescription, '/') !== false) {
                $extractedName = trim(explode('/', $shortDescription)[0]);
            } else {
                $extractedName = trim($shortDescription);
            }
            // Clean up: remove trailing dots, dashes, and extra whitespace
            $extractedName = rtrim($extractedName, ' .-');
            if (!empty($extractedName) && strlen($extractedName) > 5 && strlen($extractedName) < 200) {
                $nameFromDesc = $extractedName;
                // Try to extract brand from the beginning of the name
                if (empty($verifiedBrand)) {
                    $firstWord = explode(' ', $extractedName)[0];
                    $knownBrands = ['ASUS', 'Gigabyte', 'GIGABYTE', 'MSI', 'EVGA', 'Zotac', 'Corsair', 'Samsung',
                        'Kingston', 'Seagate', 'Intel', 'AMD', 'Logitech', 'Razer', 'HyperX', 'Crucial',
                        'Western', 'SanDisk', 'Thermaltake', 'Cooler', 'NZXT', 'be', 'Sapphire', 'XFX',
                        'PowerColor', 'ASRock', 'Biostar', 'PNY', 'Palit', 'Gainward', 'Inno3D'];
                    foreach ($knownBrands as $kb) {
                        if (strcasecmp($firstWord, $kb) === 0) {
                            $verifiedBrand = $kb;
                            break;
                        }
                    }
                }
            }
        }

        // Final product name: pattern match → extracted from description → existing name → SKU
        $productName = $recognized ? $verifiedName : ($nameFromDesc ?: ($existingName ?: $sku));

        // If pattern recognized the product model but not the brand, prepend context brand
        // Also check the name doesn't already contain the brand to avoid duplication
        if ($recognized && empty($identity['brand']) && !empty($verifiedBrand) && stripos($productName, $verifiedBrand) === false) {
            $productName = $verifiedBrand . ' ' . $productName;
        }

        error_log("AI COMPLETE: SKU={$sku}, Identified='" . ($recognized ? $verifiedName : 'NO') . "', Final name='{$productName}'");

        // If no API key, return pattern-matched data only
        if (empty($this->apiKey)) {
            return $this->buildFallbackResult($productName, $verifiedBrand, $verifiedCategory, $identity, $shortDescription);
        }

        // STEP 1.5: Fetch real specs from manufacturer website (non-blocking - fails gracefully)
        $manufacturerData = null;
        if (!empty($verifiedBrand)) {
            try {
                $manufacturerData = $this->fetchManufacturerData($sku, $verifiedBrand, $productName);
                if ($manufacturerData) {
                    error_log("AI COMPLETE: Got manufacturer data for {$sku} (" . strlen($manufacturerData) . " chars)");
                }
            } catch (\Throwable $e) {
                error_log("AI COMPLETE: Manufacturer lookup failed for {$sku}: " . $e->getMessage());
            }
        }

        // STEP 2: Ask AI to write content FOR the identified product
        $prompt = "You are a senior e-commerce product specialist for Pricetag.co.za, a South African online store.\n\n";

        if ($recognized || !empty($nameFromDesc)) {
            // We have a known product name (from pattern or short description) - write content for it
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
            $prompt .= "\n*** CRITICAL: The product name \"{$productName}\" is CORRECT. ";
            $prompt .= "You MUST use this EXACT name in the 'name' field. Do NOT change the model number or brand. ***\n";
            if ($manufacturerData) {
                $prompt .= "\nMANUFACTURER PAGE DATA (real specs from the official product page - USE THESE for accurate specifications, descriptions, and technical details):\n";
                $prompt .= "---\n{$manufacturerData}\n---\n";
                $prompt .= "IMPORTANT: Base your specifications and description on the REAL data above. Do NOT guess specs when manufacturer data is available.\n";
            }
        } else {
            // Pattern NOT matched - ask AI to IDENTIFY the product from the SKU
            $prompt .= "IDENTIFY this product from its SKU and write a COMPLETE product listing.\n\n";
            $prompt .= "SKU: {$sku}\n";
            if ($verifiedBrand) {
                $prompt .= "BRAND: {$verifiedBrand}\n";
            }
            if ($verifiedCategory && $verifiedCategory !== 'Electronics') {
                $prompt .= "CATEGORY: {$verifiedCategory}\n";
            }
            if ($existingName && $existingName !== $sku) {
                $prompt .= "CURRENT NAME (may be inaccurate): {$existingName}\n";
            }
            if ($shortDescription) {
                $prompt .= "SUPPLIER INFO: {$shortDescription}\n";
            }
            if ($existingDescription && strlen($existingDescription) > 20) {
                $prompt .= "EXISTING DESCRIPTION: " . substr($existingDescription, 0, 300) . "\n";
            }
            if ($price > 0) {
                $prompt .= "PRICE: R" . number_format((float)$price, 2) . "\n";
            }
            $prompt .= "\n*** CRITICAL IDENTIFICATION RULES:\n";
            $prompt .= "1. Decode the SKU to determine the EXACT product model. SKUs encode manufacturer, model, variant, and specs.\n";
            $prompt .= "2. USE THE SUPPLIER INFO - it often contains the brand, chipset, memory, and interface details. Parse it carefully.\n";
            $prompt .= "3. Common GPU SKU formats:\n";
            $prompt .= "   - ASUS: GT710-SL-2GD5-BRK, DUAL-RTX4060-O8G, ROG-STRIX-RTX5090-O32G, PH-GTX1650-O4G\n";
            $prompt .= "   - Gigabyte: GV-N4070EAGLE OC-12GD, GV-R76XTGAMING OC-16GD\n";
            $prompt .= "   - MSI: MSI RTX 5090 GAMING X TRIO 32G, MSI GTX 1650 VENTUS XS 4G\n";
            $prompt .= "   - EVGA: 12G-P5-3657 (memory-P-series-model)\n";
            $prompt .= "   - Zotac: ZT-T20610D-10M\n";
            $prompt .= "4. Common non-GPU SKU formats: CMK = Corsair RAM, MZ- = Samsung SSD, WD = Western Digital, BX80 = Intel CPU.\n";
            $prompt .= "5. GPU memory in SKUs: 2GD5 = 2GB GDDR5, 8GD6 = 8GB GDDR6, O8G = OC 8GB, 32GD = 32GB GDDR.\n";
            $prompt .= "6. The 'name' MUST be a SPECIFIC customer-facing product name with brand, model, and key specs.\n";
            $prompt .= "   GOOD: 'ASUS GeForce GT 710 2GB GDDR5 Silent Low Profile'\n";
            $prompt .= "   BAD: 'Asus Graphics Cards Nvidia' (too generic!)\n";
            $prompt .= "   BAD: 'GT710-SL-2GD5-BRK-EVO' (raw SKU!)\n";
            $prompt .= "7. NEVER return a generic category name as the product name. Every product has a SPECIFIC model. ***\n";
            if ($manufacturerData) {
                $prompt .= "\nMANUFACTURER PAGE DATA (real specs from the official product page - USE THESE for accurate identification and specifications):\n";
                $prompt .= "---\n{$manufacturerData}\n---\n";
                $prompt .= "IMPORTANT: Use the manufacturer data above to identify the product name AND extract real specifications. Do NOT guess when this data is available.\n";
            }
        }

        $prompt .= "\nGenerate ALL of the following fields as valid JSON:\n";
        $prompt .= "{\n";
        $prompt .= "  \"name\": \"{$productName}\",   // USE THIS EXACT NAME\n";
        $prompt .= "  \"short_description\": \"...\",  // MUST be exactly 4 bullet points. Use bullet character. Format: \\n• Point one\\n• Point two\\n• Point three\\n• Point four. Each point is a key spec or selling feature (max 20 words each). No intro text before bullets. Focus on specs: memory, interface, connectivity, power.\n";
        $prompt .= "  \"description\": \"...\",         // DETAILED product description in HTML format. Must be 250-400 words. DO NOT repeat short_description bullet points. Structure with these HTML sections:\\n\\n";
        $prompt .= "    <h3>section title</h3> followed by <p>paragraph text</p> for each section. Use EXACTLY this structure:\\n\\n";
        $prompt .= "    <h3>A compelling opening title about the product</h3>\\n<p>Opening hook - What is this product and who is it for? Why should someone buy it? (3-4 sentences)</p>\\n\\n";
        $prompt .= "    <h3>A title about performance/technology/what makes it special</h3>\\n<p>Deep dive into what makes this product stand out. Technology, architecture, build quality, cooling. Be SPECIFIC with real technical details. (4-5 sentences)</p>\\n\\n";
        $prompt .= "    <h3>A title about real-world usage and experience</h3>\\n<p>Real-world performance and use cases. What can the customer DO with this? Gaming, content creation, everyday tasks? Give concrete examples. (3-4 sentences)</p>\\n\\n";
        $prompt .= "    <h3>A title about connectivity/compatibility</h3>\\n<p>Ports, interfaces, compatibility, system requirements, installation considerations. (3-4 sentences)</p>\\n\\n";
        $prompt .= "    <h3>A closing title about value/recommendation</h3>\\n<p>Summarize why this is a smart purchase. Warranty/reliability note. Confident recommendation. (2-3 sentences)</p>\\n\\n";
        $prompt .= "    IMPORTANT RULES FOR DESCRIPTION:\\n";
        $prompt .= "    - Write in a knowledgeable, helpful tone like an expert advisor helping a customer choose\\n";
        $prompt .= "    - Use REAL technical details from the supplier info and specs - never make up performance numbers\\n";
        $prompt .= "    - Use ONLY <h3> and <p> tags. NO other HTML tags (no <ul>, <li>, <strong>, <br>, <div>, etc.)\\n";
        $prompt .= "    - Each <h3> heading must be SHORT (3-7 words), SPECIFIC to this product, and NOT generic (e.g. 'Blazing 4K Gaming Performance' not 'Performance')\\n";
        $prompt .= "    - NO emoji, NO symbols (★ ✓ ⚡ 📦) anywhere in the description\\n";
        $prompt .= "    - NO bullet points in paragraphs - write flowing prose only\\n";
        $prompt .= "    - The description value must be a single string with HTML tags inside it\\n\n";
        $prompt .= "  \"meta_title\": \"...\",          // Max 70 chars, SEO optimized\n";
        $prompt .= "  \"meta_description\": \"...\",    // Max 160 chars, with call-to-action\n";
        $prompt .= "  \"meta_keywords\": \"...\",       // Comma-separated, max 8 keywords\n";
        $prompt .= "  \"specifications\": [{\"name\": \"...\", \"value\": \"...\"}],  // At least 8 real specs. Include ALL specs from supplier info. Common specs: Chipset, Memory Size, Memory Type, Interface, Clock Speed, TDP/Power, Cores/Threads, Socket, Form Factor, Connectivity, Dimensions, etc.\n";
        $prompt .= "  \"suggested_category\": \"...\",  // Best category\n";
        $prompt .= "  \"brand\": \"{$verifiedBrand}\",\n";
        $prompt .= "  \"weight\": 0.5,                 // Estimated product weight in kg (just the product, with retail packaging)\n";
        $prompt .= "  \"length\": 30.0,                // Estimated package length in cm\n";
        $prompt .= "  \"width\": 20.0,                 // Estimated package width in cm\n";
        $prompt .= "  \"height\": 10.0,                // Estimated package height in cm\n";
        $prompt .= "  \"is_new\": false                 // true ONLY if this is a current-generation product released within the last 12 months (e.g. RTX 50-series, Intel 15th gen, AMD 9000-series, DDR5 latest). false for older/established products.\n";
        $prompt .= "}\n";
        $prompt .= "\nRules: All prices in ZAR (R). Write for a South African premium online store. Include REAL specs from supplier info.\n";
        $prompt .= "CRITICAL FORMAT RULES:\n";
        $prompt .= "- short_description: EXACTLY 4 bullet points using • character, separated by \\n. Specs only. No prose.\n";
        $prompt .= "- description: 250-400 words of HTML using ONLY <h3> and <p> tags. 5 sections, each with <h3>short heading</h3> then <p>paragraph</p>. Write like an expert helping someone decide to buy. Must be DIFFERENT content from short_description.\n";
        $prompt .= "- description headings (<h3>) must be SHORT (3-7 words), creative, and specific to THIS product. NOT generic like 'Overview' or 'Performance'.\n";
        $prompt .= "- description MUST NOT contain any bullet points (•), checkmarks (✓), stars (★), emoji, or symbols. Only <h3> and <p> tags with flowing text.\n";
        $prompt .= "- specifications: Include ALL technical specs you can determine from the supplier info and SKU. Parse memory size, memory type, interface, bus width, clock speeds, TDP, cores/threads, socket type, ports, etc. from the supplier text. Minimum 8 specs for recognized products.\n";
        $prompt .= "- weight/length/width/height: Estimate realistic shipping dimensions based on the product type. GPUs are typically 35x15x6cm, CPUs 13x13x8cm, RAM 22x14x4cm, SSDs 15x10x3cm. Use your knowledge of real product dimensions.\n";
        $prompt .= "Respond with ONLY valid JSON. No markdown. No extra text.";

        try {
            $response = $this->makeRequest('/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a product content writer. Write product descriptions and SEO content. Always respond with valid JSON only. NEVER change the product name - use it exactly as given.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 3000,
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

            // STEP 3: Enforce identity based on recognition status
            if ($recognized || !empty($nameFromDesc)) {
                // We have a known name (from pattern or short description) - FORCE it
                $data['name'] = $productName;
                if (!empty($verifiedBrand)) {
                    $data['brand'] = $verifiedBrand;
                }
                if ($verifiedCategory !== 'Electronics') {
                    $data['suggested_category'] = $verifiedCategory;
                }
            } else {
                // No name at all - TRUST the AI name (it was asked to identify)
                // But validate: never allow the raw SKU as the name
                $aiName = $data['name'] ?? '';
                if (empty($aiName) || $aiName === $sku || $aiName === $cleanSku) {
                    // AI failed to identify - keep whatever existing name we have
                    $data['name'] = ($existingName && $existingName !== $sku) ? $existingName : $sku;
                }
                // Trust AI brand/category when we didn't recognize the SKU
                if (!empty($data['brand'])) {
                    $verifiedBrand = $data['brand'];
                }
            }

            // Ensure all keys exist
            $data = array_merge([
                'name' => $data['name'] ?? $productName,
                'short_description' => $identity['short_description'] ?? '',
                'description' => $identity['description'] ?? '',
                'meta_title' => '',
                'meta_description' => '',
                'meta_keywords' => '',
                'specifications' => [],
                'suggested_category' => $data['suggested_category'] ?? $verifiedCategory,
                'brand' => $verifiedBrand,
                'weight' => null,
                'length' => null,
                'width' => null,
                'height' => null,
                'is_new' => false,
                'is_taxable' => true,
            ], $data);

            if ($recognized) {
                // Force name again after merge for recognized products
                $data['name'] = $productName;
                $data['brand'] = $verifiedBrand;
            }

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
                'length' => null,
                'width' => null,
                'height' => null,
                'is_new' => false,
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
     * Fallback response when API is unavailable.
     * Taggy answers comprehensively with keyword matching across many topics.
     */
    private function getFallbackResponse(string $message, array $context): array
    {
        $q = strtolower($message);
        $storeName = $context['store_name'] ?? 'Pricetag';
        $name = $context['user']['name'] ?? 'there';
        $products = $context['relevant_products'] ?? [];

        // --- Greetings ---
        if (preg_match('/\b(hello|hi|hey|howzit|good\s*(morning|afternoon|evening)|sup)\b/', $q)) {
            $response = "Hey {$name}! Welcome to {$storeName} — I'm Taggy, your personal shopping sidekick. I can help you with:\n\n• Finding products & deals\n• Tracking your order\n• Shipping & delivery info\n• Returns & refunds\n• Payment options\n\nWhat can I help you with?";
        }

        // --- Order tracking ---
        elseif (preg_match('/\b(track|tracking|order\s*status|where.*(order|package|parcel)|my\s*order|dispatch)\b/', $q)) {
            $response = "Here's how to track your order:\n\n• Log in to your account and go to **My Orders**\n• Click on the order to see its current status and tracking number\n• Once dispatched, you'll also get a tracking email with a direct link\n\nIf your order hasn't moved in a while or something seems off, reach out to our team at info@pricetag.co.za or call 011 100 2232 (Mon-Fri, 8am-5pm) and we'll sort it out.";
        }

        // --- Shipping & delivery ---
        elseif (preg_match('/\b(ship|shipping|deliver|delivery|courier|postage|dispatch|how\s*long)\b/', $q)) {
            $response = "Here's the full breakdown on delivery:\n\n• **Standard delivery**: 3-5 business days nationwide — R75\n• **Express delivery**: 1-2 business days in major metros — R150\n• **Free shipping**: On all orders over R500!\n\nWe ship to all provinces in South Africa. You'll get a tracking number by email once your order is dispatched.\n\nNeed anything else?";
        }

        // --- Returns & refunds ---
        elseif (preg_match('/\b(return|refund|exchange|send\s*back|money\s*back|broken|damaged|defective|warranty)\b/', $q)) {
            $response = "Our return policy is straightforward:\n\n• **30-day returns** — from the date you receive your order\n• Items must be **unused** and in the **original packaging**\n• To start a return, email info@pricetag.co.za with your order number\n• Refunds are processed within **5-7 business days** after we receive the item\n• **Damaged or defective** items? We'll cover return shipping — just send us a photo of the issue\n\nIs there something specific you'd like to return?";
        }

        // --- Payment methods ---
        elseif (preg_match('/\b(pay|payment|card|visa|mastercard|eft|credit|debit|cash\s*on|cod|ozow|how.*pay)\b/', $q)) {
            $response = "We offer several convenient payment options:\n\n• **Credit/Debit cards** — Visa & MasterCard (secured by 3D Secure)\n• **EFT** — Direct bank transfer\n• **Instant EFT** — Via Ozow (immediate confirmation)\n• **Cash on Delivery** — Available in select metro areas\n\nAll transactions are SSL-encrypted and 100% secure. Which method works best for you?";
        }

        // --- Best sellers / popular ---
        elseif (preg_match('/\b(best\s*sell|popular|top\s*product|trending|most\s*bought|hot|recommend)\b/', $q)) {
            if (!empty($products)) {
                $response = "Here are some of our most popular picks right now:\n\n";
                foreach (array_slice($products, 0, 5) as $p) {
                    $response .= "• **{$p['name']}** — R" . number_format($p['price'], 2) . "\n";
                }
                $response .= "\nWant me to find something more specific? Just tell me what you're looking for.";
            } else {
                $response = "Our best sellers change fast! Browse the homepage for featured picks, or tell me what kind of product you're after and I'll help you find the best options.";
            }
        }

        // --- Sales & deals ---
        elseif (preg_match('/\b(sale|deal|discount|promo|coupon|voucher|special|cheap|bargain|on\s*sale|clearance)\b/', $q)) {
            if (!empty($products)) {
                $response = "Great timing — here are some deals worth checking out:\n\n";
                foreach (array_slice($products, 0, 5) as $p) {
                    $response .= "• **{$p['name']}** — R" . number_format($p['price'], 2) . "\n";
                }
                $response .= "\nKeep an eye on our homepage for flash sales, or sign up for our newsletter to get exclusive discount codes!";
            } else {
                $response = "We regularly run sales and specials! Check our homepage for current deals, or sign up for our newsletter to get exclusive discount codes delivered straight to your inbox.\n\nIs there a specific product category you're hunting deals in?";
            }
        }

        // --- Contact / support ---
        elseif (preg_match('/\b(contact|support|email|phone|call|speak|reach|help\s*line|customer\s*service|complaint)\b/', $q)) {
            $response = "Here's how to reach our team:\n\n• **Email**: info@pricetag.co.za\n• **Phone**: 011 100 2232\n• **Hours**: Monday to Friday, 8am - 5pm\n\nFor quick questions, I'm right here! For order-specific issues, the team above can pull up your details and sort things out.";
        }

        // --- Account related ---
        elseif (preg_match('/\b(account|sign\s*up|register|log\s*in|login|password|reset|forgot)\b/', $q)) {
            $response = "Here's what you need to know about your account:\n\n• **Sign up**: Click 'Register' at the top of the page — it only takes a minute\n• **Log in**: Use your email and password at the login page\n• **Forgot password?**: Click 'Forgot Password' on the login page and we'll email you a reset link\n• **Benefits**: Track orders, save addresses, faster checkout, and order history\n\nHaving trouble accessing your account? Drop a line to info@pricetag.co.za and we'll help.";
        }

        // --- Store hours / about ---
        elseif (preg_match('/\b(hours|open|close|about|who\s*are|store\s*info|where\s*are|location|address)\b/', $q)) {
            $response = "{$storeName} is an online store — we're always open for browsing and ordering, 24/7!\n\n• **Customer support hours**: Mon-Fri, 8am-5pm\n• **Processing**: Orders placed before 2pm on weekdays are dispatched same day\n\nWe're proudly South African, serving customers nationwide. Anything else you'd like to know?";
        }

        // --- Price / cost questions ---
        elseif (preg_match('/\b(how\s*much|price|cost|expensive|budget|affordable)\b/', $q)) {
            if (!empty($products)) {
                $response = "Here's what I found that might match:\n\n";
                foreach (array_slice($products, 0, 5) as $p) {
                    $response .= "• **{$p['name']}** — R" . number_format($p['price'], 2) . "\n";
                }
                $response .= "\nAll prices include VAT. Free shipping on orders over R500! Want me to narrow it down?";
            } else {
                $response = "All our prices include VAT and are displayed in South African Rand (R). Free shipping on orders over R500!\n\nTell me what product you're interested in and I'll get you the exact pricing.";
            }
        }

        // --- Thank you ---
        elseif (preg_match('/\b(thank|thanks|cheers|appreciate|ta)\b/', $q)) {
            $response = "You're welcome! Happy to help. If anything else comes up, just ask — I'm always here. Happy shopping!";
        }

        // --- Goodbye ---
        elseif (preg_match('/\b(bye|goodbye|see\s*you|later|ciao|cheers)\b/', $q)) {
            $response = "Cheers, {$name}! Happy shopping and come back anytime. Taggy's always here if you need me!";
        }

        // --- Product search (catch-all with products) ---
        elseif (!empty($products)) {
            $response = "Based on what you're looking for, here are some options:\n\n";
            foreach (array_slice($products, 0, 5) as $p) {
                $response .= "• **{$p['name']}** — R" . number_format($p['price'], 2) . "\n";
            }
            $response .= "\nWant more details on any of these, or should I search for something else?";
        }

        // --- General fallback ---
        else {
            $response = "Great question! I want to make sure I give you the right answer. Here's what I can help with:\n\n• **Products** — finding, comparing, and recommending\n• **Orders** — tracking and status updates\n• **Shipping** — delivery times, costs, and free shipping\n• **Returns** — our 30-day return policy\n• **Payments** — accepted methods and security\n• **Account** — sign up, login, password reset\n\nTry asking about any of these, or tell me what product you're after!";
        }

        return [
            'message' => $response,
            'conversation_id' => $this->generateConversationId(),
            'products' => $products,
        ];
    }
}
