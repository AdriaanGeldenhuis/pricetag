<?php
/**
 * Claude Service
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Talks to Anthropic's Messages API with web_search enabled so the import
 * flow can verify product names and pull real image URLs from manufacturer
 * pages. Drop-in replacement for OpenAIService::generateCompleteProduct():
 * same call signature, same return shape, same fallback semantics.
 *
 * Raw cURL (matches the existing OpenAIService pattern - no composer
 * dependency on anthropic-ai/sdk required for the deployed environment).
 */

namespace App\Services;

class ClaudeService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://api.anthropic.com/v1';
    private string $apiVersion = '2023-06-01';

    // Sonnet 4.6 pricing per million tokens (USD) - update if Anthropic changes pricing
    // input / output / cache_write_5m / cache_read
    private const PRICING = [
        'claude-sonnet-4-6' => [3.00, 15.00, 3.75, 0.30],
        'claude-haiku-4-5'  => [1.00,  5.00, 1.25, 0.10],
        'claude-opus-4-7'   => [5.00, 25.00, 6.25, 0.50],
        'claude-opus-4-6'   => [5.00, 25.00, 6.25, 0.50],
    ];

    public function __construct()
    {
        $this->apiKey = env('ANTHROPIC_API_KEY', '');
        $this->model = env('ANTHROPIC_MODEL', 'claude-sonnet-4-6');
    }

    public function hasApiKey(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Generate a complete product from a SKU.
     *
     * Returns the same shape as OpenAIService::generateCompleteProduct():
     *   [
     *     'success' => bool,
     *     'data' => [
     *       'name', 'short_description', 'description',
     *       'meta_title', 'meta_description', 'meta_keywords',
     *       'specifications', 'attributes', 'suggested_category',
     *       'brand', 'weight', 'length', 'width', 'height', 'is_new',
     *       'image_url', 'image_candidates',   // NEW - from web search
     *     ],
     *     'method' => 'ai' | 'fallback',
     *     'fallback_reason' => string,         // when method='fallback'
     *     'usage' => [...],                    // token counts
     *   ]
     */
    public function generateCompleteProduct(string $sku, string $shortDescription = '', array $context = []): array
    {
        $sku = trim($sku);
        if ($sku === '') {
            return $this->buildFallback($sku, 'empty_sku');
        }

        if (!$this->hasApiKey()) {
            return $this->buildFallback($sku, 'no_api_key');
        }

        $isBulkImport = !empty($context['bulk_import']);
        $maxTokens = $isBulkImport ? 16000 : 16000;
        $timeout = $isBulkImport ? 120 : 180;

        $systemBlocks = $this->buildSystemBlocks();
        $userPrompt = $this->buildUserPrompt($sku, $shortDescription, $context);

        $body = [
            'model' => $this->model,
            'max_tokens' => $maxTokens,
            'thinking' => ['type' => 'adaptive'],
            'system' => $systemBlocks,
            'tools' => [
                ['type' => 'web_search_20260209', 'name' => 'web_search'],
            ],
            'messages' => [
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ];

        try {
            $response = $this->callMessages($body, $timeout);

            // Handle server-side tool loops that hit the 10-iteration cap
            $hops = 0;
            while (($response['stop_reason'] ?? '') === 'pause_turn' && $hops < 3) {
                $body['messages'] = [
                    ['role' => 'user', 'content' => $userPrompt],
                    ['role' => 'assistant', 'content' => $response['content'] ?? []],
                ];
                $response = $this->callMessages($body, $timeout);
                $hops++;
            }

            // Extract image URLs from web_search results
            $imageCandidates = $this->extractImageCandidates($response);

            $stopReason = (string) ($response['stop_reason'] ?? '');

            // Extract the final text block - the JSON we asked for
            $jsonText = $this->extractFinalText($response);
            if ($jsonText === '') {
                $reason = $stopReason !== '' ? "no_text_response(stop={$stopReason})" : 'no_text_response';
                return $this->buildFallback($sku, $reason, $this->extractUsage($response));
            }

            $jsonText = $this->stripCodeFences($jsonText);
            $data = json_decode($jsonText, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                error_log('ClaudeService JSON parse failed: ' . json_last_error_msg() . ' :: stop=' . $stopReason . ' :: raw="' . substr($jsonText, 0, 200) . '"');
                $reason = $stopReason === 'max_tokens'
                    ? 'json_truncated_at_max_tokens'
                    : 'json_parse_failed: ' . json_last_error_msg();
                return $this->buildFallback($sku, $reason, $this->extractUsage($response));
            }

            $data = $this->validateAndNormalize($data, $sku, $imageCandidates);
            $usage = $this->extractUsage($response);
            $this->logUsage($usage);

            return [
                'success' => true,
                'data' => $data,
                'method' => 'ai',
                'usage' => $usage,
            ];
        } catch (\Exception $e) {
            error_log('ClaudeService error: ' . $e->getMessage());
            return $this->buildFallback($sku, $e->getMessage());
        }
    }

    /**
     * Cacheable system prompt. The bulk of the prompt is identical for every
     * SKU in a batch import, so we mark it with cache_control - subsequent
     * rows in the same import hit the cache at ~0.1x the input price.
     */
    private function buildSystemBlocks(): array
    {
        $rules = <<<PROMPT
You are a product content writer for Pricetag.co.za, a South African e-commerce store specializing in computer hardware, electronics, and tech accessories. All prices are in ZAR (R). You write for a premium South African retail audience.

YOUR WORKFLOW for every product:
1. Use the web_search tool to verify the SKU and find authoritative product information from the manufacturer's official page (Intel, AMD, NVIDIA, ASUS, HP, Dell, Lenovo, etc.) or a reputable retailer (Amazon, Newegg, Takealot).
2. If the official manufacturer page returns an image, prefer that image URL.
3. Extract the verified product name, real specifications, and at least one product image URL.
4. ONLY THEN write the structured JSON output - never write JSON before searching, because that's where the existing system goes wrong.

PRODUCT NAMING CONVENTION (MANDATORY):
Names MUST follow: {Brand} {Series/Model} {Key Specs} {Category Type}
- Brand is ALWAYS first, category type is ALWAYS last.
- LAPTOPS: brand, model line, CPU, RAM, screen size, then "Laptop"
- PROJECTORS: brand, model, technology, brightness, then "Projector"
- MONITORS: brand, model, screen size, resolution, then "Monitor"
- PRINTERS: brand, model, type, then "Printer"
- GPUs: brand, model, memory, then "Graphics Card"
- CPUs: brand, model, cores, then "Processor"
Examples:
- "ASUS ROG Strix GeForce RTX 4070 OC 12GB Graphics Card"
- "Intel Core i5-14600K 14-Core 5.3GHz LGA1700 Processor"
- "Acer Aspire 5 A515-58M i5-1335U 8GB 15.6-inch Laptop"
- "Dell UltraSharp U2723QE 27-inch 4K IPS Monitor"
- "BenQ TH685P DLP 1080p 3500 Lumens Projector"

ABSOLUTE RULES:
- NEVER use the raw SKU as the product name.
- NEVER include "|" or ";" characters in the name (these come from raw supplier data).
- NEVER fabricate specifications - if web search did not yield a value, leave it out of the specs array rather than guessing.
- If web search returns NOTHING usable for the SKU, set "name" to empty string "" and "ai_identified" to false - the caller will then create a draft stub instead.

OUTPUT FORMAT:
You will respond with ONLY a valid JSON object - no preamble, no markdown fences, no commentary. The exact schema is in the user message.
PROMPT;

        return [
            ['type' => 'text', 'text' => $rules, 'cache_control' => ['type' => 'ephemeral']],
        ];
    }

    private function buildUserPrompt(string $sku, string $shortDescription, array $context): string
    {
        $brand = trim((string) ($context['brand'] ?? ''));
        $category = trim((string) ($context['category'] ?? ''));
        $existingName = trim((string) ($context['existingName'] ?? ''));

        $lines = [];
        $lines[] = "Generate a complete product from this SKU.";
        $lines[] = "";
        $lines[] = "SKU: {$sku}";
        if ($brand !== '') {
            $lines[] = "Vendor/Brand hint: {$brand}";
        }
        if ($category !== '') {
            $lines[] = "Category hint: {$category}";
        }
        if ($existingName !== '' && $existingName !== $sku) {
            $lines[] = "Existing name (use as starting hint, may be wrong): {$existingName}";
        }
        if ($shortDescription !== '') {
            $lines[] = "Supplier data (may contain real specs):";
            $lines[] = "---";
            $lines[] = $shortDescription;
            $lines[] = "---";
        }
        $lines[] = "";
        $lines[] = "STEP 1: Use web_search to find this SKU on the manufacturer's official site. Try queries like \"{$sku}\", \"{$sku} site:intel.com OR site:amd.com OR site:nvidia.com\", or brand-specific searches. Find the canonical product name and at least one image URL.";
        $lines[] = "";
        $lines[] = "STEP 2: Once identified, respond with ONLY this JSON object (no markdown, no preamble):";
        $lines[] = '{';
        $lines[] = '  "ai_identified": true,                  // false if web search failed to identify the SKU - then leave name empty';
        $lines[] = '  "name": "...",                          // Brand Model Specs CategoryType - empty string "" if not identified';
        $lines[] = '  "brand": "...",';
        $lines[] = '  "image_url": "https://...",             // single best product image URL from manufacturer or trusted retailer';
        $lines[] = '  "image_candidates": ["https://...", "..."],  // 2-4 candidate image URLs in order of preference';
        $lines[] = '  "short_description": "...",             // EXACTLY 4 bullet points using bullet character separated by newlines, max 20 words each';
        $lines[] = '  "description": "...",                   // 250-400 word HTML with ONLY <h3> and <p> tags, 5 sections, no bullets/emoji/symbols';
        $lines[] = '  "meta_title": "...",                    // max 70 chars, SEO optimized';
        $lines[] = '  "meta_description": "...",              // max 160 chars, with CTA';
        $lines[] = '  "meta_keywords": "...",                 // comma-separated, max 8 keywords';
        $lines[] = '  "specifications": [{"name": "...", "value": "..."}],  // at least 8 real specs from web search - NO guessing';
        $lines[] = '  "attributes": {"Series": "...", "Memory Size": "..."},  // filterable attributes as name/value pairs';
        $lines[] = '  "suggested_category": "...",            // best-fit category (e.g. "Graphics Cards", "Laptops", "Processors")';
        $lines[] = '  "weight": 0.5,                          // estimated kg including retail packaging';
        $lines[] = '  "length": 30.0,                         // estimated package length in cm';
        $lines[] = '  "width": 20.0,                          // estimated package width in cm';
        $lines[] = '  "height": 10.0,                         // estimated package height in cm';
        $lines[] = '  "is_new": false                         // true ONLY if released in last 12 months';
        $lines[] = '}';
        $lines[] = "";
        $lines[] = "Remember: ai_identified=false + name=\"\" if you cannot confirm the SKU. Do not invent a product.";

        return implode("\n", $lines);
    }

    /**
     * Send the request with stream:true and reassemble the SSE event stream
     * into a non-streaming-shaped response. Required for any request that
     * involves adaptive thinking + web_search + large JSON output - those
     * total response times routinely exceed 60-120s and a non-streaming
     * call returns zero bytes until completion, which trips CURLOPT_TIMEOUT.
     * Streaming sends bytes throughout, so the connection stays alive.
     */
    private function callMessages(array $body, int $timeout): array
    {
        $body['stream'] = true;

        $blocks = [];
        $stopReason = null;
        $usage = [
            'input_tokens' => 0,
            'output_tokens' => 0,
            'cache_creation_input_tokens' => 0,
            'cache_read_input_tokens' => 0,
        ];
        $errorPayload = null;
        $buffer = '';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/messages',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            // Anthropic's stream emits keep-alives every ~15s. If we go this
            // long without bytes the connection is genuinely dead.
            CURLOPT_LOW_SPEED_LIMIT => 1,
            CURLOPT_LOW_SPEED_TIME => 60,
            CURLOPT_HTTPHEADER => [
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: ' . $this->apiVersion,
                'content-type: application/json',
                'accept: text/event-stream',
            ],
            CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$buffer, &$blocks, &$stopReason, &$usage, &$errorPayload) {
                $buffer .= $chunk;
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);
                    $line = rtrim($line, "\r");
                    if ($line === '' || strpos($line, 'data:') !== 0) {
                        continue;
                    }
                    $json = trim(substr($line, 5));
                    if ($json === '' || $json === '[DONE]') {
                        continue;
                    }
                    $event = json_decode($json, true);
                    if (!is_array($event)) {
                        continue;
                    }
                    $this->applyStreamEvent($event, $blocks, $stopReason, $usage, $errorPayload);
                }
                return strlen($chunk);
            },
        ]);

        $ok = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($ok === false) {
            throw new \RuntimeException("curl_error: {$err}");
        }

        if ($errorPayload !== null) {
            $errType = $errorPayload['type'] ?? 'unknown';
            $errMsg = $errorPayload['message'] ?? 'unknown';
            throw new \RuntimeException("anthropic_api_error: {$status} {$errType} - {$errMsg}");
        }

        if ($status >= 400) {
            // Some 4xx responses come back as a single non-streamed JSON body
            // (e.g. malformed request rejected before the stream opens).
            $decoded = json_decode($buffer, true);
            $errType = $decoded['error']['type'] ?? 'unknown';
            $errMsg = $decoded['error']['message'] ?? trim($buffer) ?: 'unknown';
            throw new \RuntimeException("anthropic_api_error: {$status} {$errType} - " . substr((string) $errMsg, 0, 200));
        }

        return [
            'content' => array_values($blocks),
            'stop_reason' => $stopReason,
            'usage' => $usage,
        ];
    }

    /**
     * Apply one SSE event to the reassembled message state. Anthropic's
     * stream format documents each event type; we only care about the four
     * that actually carry payload.
     */
    private function applyStreamEvent(array $event, array &$blocks, ?string &$stopReason, array &$usage, ?array &$errorPayload): void
    {
        $type = $event['type'] ?? '';
        switch ($type) {
            case 'message_start':
                $msg = $event['message'] ?? [];
                if (!empty($msg['usage']) && is_array($msg['usage'])) {
                    foreach ($msg['usage'] as $k => $v) {
                        if (array_key_exists($k, $usage)) {
                            $usage[$k] = (int) $v;
                        }
                    }
                }
                break;
            case 'content_block_start':
                $idx = $event['index'] ?? null;
                if ($idx !== null) {
                    $blocks[$idx] = $event['content_block'] ?? [];
                }
                break;
            case 'content_block_delta':
                $idx = $event['index'] ?? null;
                $delta = $event['delta'] ?? [];
                if ($idx === null || !isset($blocks[$idx])) {
                    break;
                }
                $dtype = $delta['type'] ?? '';
                if ($dtype === 'text_delta') {
                    $blocks[$idx]['text'] = ($blocks[$idx]['text'] ?? '') . ($delta['text'] ?? '');
                } elseif ($dtype === 'thinking_delta') {
                    $blocks[$idx]['thinking'] = ($blocks[$idx]['thinking'] ?? '') . ($delta['thinking'] ?? '');
                } elseif ($dtype === 'input_json_delta') {
                    $blocks[$idx]['partial_json'] = ($blocks[$idx]['partial_json'] ?? '') . ($delta['partial_json'] ?? '');
                }
                break;
            case 'content_block_stop':
                $idx = $event['index'] ?? null;
                if ($idx === null || !isset($blocks[$idx])) {
                    break;
                }
                if (isset($blocks[$idx]['partial_json'])) {
                    $parsed = json_decode($blocks[$idx]['partial_json'], true);
                    $blocks[$idx]['input'] = is_array($parsed) ? $parsed : [];
                    unset($blocks[$idx]['partial_json']);
                }
                break;
            case 'message_delta':
                $delta = $event['delta'] ?? [];
                if (!empty($delta['stop_reason'])) {
                    $stopReason = (string) $delta['stop_reason'];
                }
                if (!empty($event['usage']) && is_array($event['usage'])) {
                    foreach ($event['usage'] as $k => $v) {
                        if (array_key_exists($k, $usage)) {
                            $usage[$k] = (int) $v;
                        }
                    }
                }
                break;
            case 'error':
                $errorPayload = $event['error'] ?? ['type' => 'unknown', 'message' => 'unknown'];
                break;
        }
    }

    /**
     * Walk content blocks and grab the last text block (Claude's final answer
     * after any tool use). Empty string if there is no text block.
     */
    private function extractFinalText(array $response): string
    {
        $content = $response['content'] ?? [];
        $text = '';
        foreach ($content as $block) {
            if (($block['type'] ?? '') === 'text' && isset($block['text'])) {
                $text = (string) $block['text']; // keep overwriting so we end up with the last one
            }
        }
        return trim($text);
    }

    /**
     * Pull every image URL the model saw via web_search. We don't trust the
     * model's claimed image_url unless one of these candidates corroborates
     * it - this is a guard against hallucinated URLs.
     */
    private function extractImageCandidates(array $response): array
    {
        $urls = [];
        $content = $response['content'] ?? [];
        foreach ($content as $block) {
            $type = $block['type'] ?? '';
            if ($type === 'web_search_tool_result' || $type === 'web_search_result') {
                $results = $block['content'] ?? [];
                if (!is_array($results)) {
                    continue;
                }
                foreach ($results as $r) {
                    if (!is_array($r)) {
                        continue;
                    }
                    foreach (['image_url', 'thumbnail_url', 'image'] as $key) {
                        if (!empty($r[$key]) && is_string($r[$key])) {
                            $urls[] = $r[$key];
                        }
                    }
                }
            }
        }
        return array_values(array_unique($urls));
    }

    private function extractUsage(array $response): array
    {
        $u = $response['usage'] ?? [];
        return [
            'input_tokens' => (int) ($u['input_tokens'] ?? 0),
            'output_tokens' => (int) ($u['output_tokens'] ?? 0),
            'cache_creation_input_tokens' => (int) ($u['cache_creation_input_tokens'] ?? 0),
            'cache_read_input_tokens' => (int) ($u['cache_read_input_tokens'] ?? 0),
            'model' => $this->model,
        ];
    }

    private function stripCodeFences(string $text): string
    {
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/i', '', $text);
        $text = preg_replace('/\s*```\s*$/i', '', $text);
        return trim($text);
    }

    /**
     * Sanity-check the model's output. We strip anything that looks like a
     * hallucination so the import flow gets either trustworthy data or the
     * explicit "ai_identified=false" stub signal.
     */
    private function validateAndNormalize(array $data, string $sku, array $imageCandidates): array
    {
        $aiIdentified = !empty($data['ai_identified']);
        $name = trim((string) ($data['name'] ?? ''));

        // Name must not be the raw SKU and must contain at least 2 words for ai_identified=true
        if ($name === $sku || str_word_count($name) < 2) {
            $aiIdentified = false;
            $name = '';
        }

        // Clean leaked supplier separators
        $name = str_replace(['|', ';'], ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name) ?? '';
        $name = trim($name);

        // Image URL: prefer model's pick, but only if it shows up in candidates
        // (or candidates are empty - we'd rather take the model's word than nothing)
        $imageUrl = trim((string) ($data['image_url'] ?? ''));
        if ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $imageUrl = '';
        }

        $modelCandidates = $data['image_candidates'] ?? [];
        if (!is_array($modelCandidates)) {
            $modelCandidates = [];
        }
        $allCandidates = array_values(array_unique(array_filter(array_merge(
            $imageUrl !== '' ? [$imageUrl] : [],
            $modelCandidates,
            $imageCandidates
        ), fn($u) => is_string($u) && filter_var($u, FILTER_VALIDATE_URL))));

        if ($imageUrl === '' && !empty($allCandidates)) {
            $imageUrl = $allCandidates[0];
        }

        // Dimensions: reject absurd values (model sometimes outputs 0.1 or 9999)
        $weight = $this->sanitizeDimension($data['weight'] ?? null, 0.05, 200);
        $length = $this->sanitizeDimension($data['length'] ?? null, 1, 300);
        $width = $this->sanitizeDimension($data['width'] ?? null, 1, 300);
        $height = $this->sanitizeDimension($data['height'] ?? null, 1, 300);

        // Specs: drop any with empty name or value
        $specs = [];
        foreach ((array) ($data['specifications'] ?? []) as $s) {
            if (!is_array($s)) {
                continue;
            }
            $sn = trim((string) ($s['name'] ?? ''));
            $sv = trim((string) ($s['value'] ?? ''));
            if ($sn !== '' && $sv !== '') {
                $specs[] = ['name' => substr($sn, 0, 100), 'value' => substr($sv, 0, 500)];
            }
        }

        // Attributes: same cleanup
        $attrs = [];
        foreach ((array) ($data['attributes'] ?? []) as $k => $v) {
            $k = trim((string) $k);
            $v = trim((string) $v);
            if ($k !== '' && $v !== '') {
                $attrs[$k] = $v;
            }
        }

        return [
            'ai_identified' => $aiIdentified,
            'name' => $aiIdentified ? $name : '',
            'brand' => trim((string) ($data['brand'] ?? '')),
            'image_url' => $imageUrl,
            'image_candidates' => $allCandidates,
            'short_description' => trim((string) ($data['short_description'] ?? '')),
            'description' => trim((string) ($data['description'] ?? '')),
            'meta_title' => substr(trim((string) ($data['meta_title'] ?? '')), 0, 255),
            'meta_description' => trim((string) ($data['meta_description'] ?? '')),
            'meta_keywords' => substr(trim((string) ($data['meta_keywords'] ?? '')), 0, 255),
            'specifications' => $specs,
            'attributes' => $attrs,
            'suggested_category' => trim((string) ($data['suggested_category'] ?? '')),
            'weight' => $weight,
            'length' => $length,
            'width' => $width,
            'height' => $height,
            'is_new' => !empty($data['is_new']),
        ];
    }

    private function sanitizeDimension($value, float $min, float $max): ?float
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }
        $f = (float) $value;
        if ($f < $min || $f > $max) {
            return null;
        }
        return round($f, 2);
    }

    private function buildFallback(string $sku, string $reason, ?array $usage = null): array
    {
        return [
            'success' => false,
            'data' => [
                'ai_identified' => false,
                'name' => '',
                'brand' => '',
                'image_url' => '',
                'image_candidates' => [],
                'short_description' => '',
                'description' => '',
                'meta_title' => '',
                'meta_description' => '',
                'meta_keywords' => '',
                'specifications' => [],
                'attributes' => [],
                'suggested_category' => '',
                'weight' => null,
                'length' => null,
                'width' => null,
                'height' => null,
                'is_new' => false,
            ],
            'method' => 'fallback',
            'fallback_reason' => $reason,
            'usage' => $usage ?? ['input_tokens' => 0, 'output_tokens' => 0, 'cache_creation_input_tokens' => 0, 'cache_read_input_tokens' => 0, 'model' => $this->model],
        ];
    }

    private function logUsage(array $usage): void
    {
        try {
            $db = \App\Core\Database::getInstance();
            $pricing = self::PRICING[$this->model] ?? [3.00, 15.00, 3.75, 0.30];
            [$inputRate, $outputRate, $cacheWriteRate, $cacheReadRate] = $pricing;
            $cost = (
                $usage['input_tokens'] * $inputRate
                + $usage['output_tokens'] * $outputRate
                + $usage['cache_creation_input_tokens'] * $cacheWriteRate
                + $usage['cache_read_input_tokens'] * $cacheReadRate
            ) / 1_000_000;

            $stmt = $db->prepare(
                "INSERT INTO ai_usage_log (endpoint, model, prompt_tokens, completion_tokens, total_tokens, estimated_cost_usd, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                'messages',
                $this->model,
                $usage['input_tokens'] + $usage['cache_creation_input_tokens'] + $usage['cache_read_input_tokens'],
                $usage['output_tokens'],
                $usage['input_tokens'] + $usage['output_tokens'] + $usage['cache_creation_input_tokens'] + $usage['cache_read_input_tokens'],
                round($cost, 6),
            ]);
        } catch (\Throwable $e) {
            // Don't let logging failures break the import
            error_log('ClaudeService logUsage failed: ' . $e->getMessage());
        }
    }
}
