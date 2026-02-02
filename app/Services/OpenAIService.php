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
