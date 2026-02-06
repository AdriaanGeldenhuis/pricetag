<?php
/**
 * AI Assistant API Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Shopping assistant powered by OpenAI for product recommendations,
 * order tracking, and customer support.
 */

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
use App\Services\OpenAIService;

class AssistantApiController extends Controller
{
    private OpenAIService $ai;

    public function __construct()
    {
        $this->ai = new OpenAIService();
    }

    public function chat(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');
        $conversationId = $input['conversation_id'] ?? null;

        if (empty($message)) {
            echo json_encode(['error' => 'Message is required']);
            return;
        }

        // Rate limiting
        if (!$this->checkRateLimit()) {
            echo json_encode(['error' => 'Too many requests. Please try again later.']);
            return;
        }

        try {
            // Build context for the assistant
            $context = $this->buildContext($message);

            // Get response from OpenAI
            $response = $this->ai->chat($message, $context, $conversationId);

            // Log conversation
            $this->logConversation($message, $response, $conversationId);

            echo json_encode([
                'success' => true,
                'response' => $response['message'],
                'conversation_id' => $response['conversation_id'],
                'products' => $response['products'] ?? [],
            ]);

        } catch (\Exception $e) {
            error_log('AI Assistant Error: ' . $e->getMessage());
            echo json_encode([
                'error' => 'Sorry, I encountered an error. Please try again.',
            ]);
        }
    }

    private function buildContext(string $message): array
    {
        $context = [
            'store_name' => config('app.name'),
            'currency' => config('app.currency', 'ZAR'),
            'user' => null,
            'cart' => null,
            'relevant_products' => [],
            'categories' => [],
        ];

        // Add user context if logged in
        if (auth()) {
            $user = user();
            $context['user'] = [
                'name' => $user['first_name'] ?? 'Customer',
                'has_orders' => $this->getUserOrderCount($user['id']) > 0,
            ];
        }

        // Add cart context
        try {
            $cart = new \App\Models\Cart();
            $items = $cart->getItems();
            if (!empty($items)) {
                $context['cart'] = [
                    'item_count' => $cart->getCount(),
                    'total' => $cart->getSubtotal(),
                ];
            }
        } catch (\Exception $e) {
            // Cart unavailable — continue without cart context
        }

        // Search for relevant products based on the message
        $context['relevant_products'] = $this->searchProducts($message);

        // Get category list
        $context['categories'] = $this->getCategories();

        return $context;
    }

    private function searchProducts(string $query): array
    {
        $db = Database::getInstance();

        // Extract keywords from the message
        $keywords = $this->extractKeywords($query);

        if (empty($keywords)) {
            // Return featured products if no specific search
            $stmt = $db->prepare("
                SELECT id, name, slug, price, short_description
                FROM products
                WHERE status = 'active' AND is_featured = 1
                LIMIT 5
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        }

        // Search products matching keywords
        $searchTerm = '%' . implode('%', $keywords) . '%';
        $stmt = $db->prepare("
            SELECT id, name, slug, price, short_description
            FROM products
            WHERE status = 'active'
            AND (name LIKE ? OR description LIKE ? OR short_description LIKE ?)
            ORDER BY is_featured DESC, RAND()
            LIMIT 5
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);

        return $stmt->fetchAll();
    }

    private function extractKeywords(string $text): array
    {
        // Remove common words and extract potential product keywords
        $stopWords = ['i', 'me', 'my', 'the', 'a', 'an', 'is', 'are', 'was', 'were', 'be', 'been',
            'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should',
            'may', 'might', 'must', 'shall', 'can', 'need', 'dare', 'ought', 'used', 'to', 'of',
            'in', 'for', 'on', 'with', 'at', 'by', 'from', 'as', 'into', 'through', 'during',
            'before', 'after', 'above', 'below', 'between', 'under', 'again', 'further', 'then',
            'once', 'here', 'there', 'when', 'where', 'why', 'how', 'all', 'each', 'few', 'more',
            'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so',
            'than', 'too', 'very', 'just', 'and', 'but', 'if', 'or', 'because', 'until', 'while',
            'about', 'against', 'this', 'that', 'these', 'those', 'am', 'what', 'which', 'who',
            'whom', 'its', 'it', 'you', 'your', 'he', 'him', 'his', 'she', 'her', 'we', 'they',
            'them', 'our', 'their', 'looking', 'want', 'need', 'like', 'find', 'show', 'get',
            'help', 'please', 'thanks', 'thank', 'hi', 'hello', 'hey'];

        $words = preg_split('/\s+/', strtolower($text));
        $keywords = [];

        foreach ($words as $word) {
            $word = preg_replace('/[^a-z0-9]/', '', $word);
            if (strlen($word) > 2 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }

        return array_slice($keywords, 0, 5);
    }

    private function getCategories(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT id, name, slug FROM categories
            WHERE is_active = 1 AND parent_id IS NULL
            ORDER BY sort_order, name
            LIMIT 10
        ");
        return $stmt->fetchAll();
    }

    private function getUserOrderCount(int $userId): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    private function checkRateLimit(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'ai_rate_' . md5($ip);
        $limit = 30; // requests per minute
        $window = 60; // seconds

        // Simple rate limiting using session
        if (!isset($_SESSION['rate_limits'])) {
            $_SESSION['rate_limits'] = [];
        }

        $now = time();
        $requests = $_SESSION['rate_limits'][$key] ?? [];

        // Clean old requests
        $requests = array_filter($requests, fn($time) => $time > ($now - $window));

        if (count($requests) >= $limit) {
            return false;
        }

        $requests[] = $now;
        $_SESSION['rate_limits'][$key] = $requests;

        return true;
    }

    private function logConversation(string $message, array $response, ?string $conversationId): void
    {
        $db = Database::getInstance();

        try {
            $stmt = $db->prepare("
                INSERT INTO ai_conversations (conversation_id, user_id, user_message, assistant_response, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $response['conversation_id'] ?? $conversationId,
                auth() ? auth()->id : null,
                $message,
                $response['message']
            ]);
        } catch (\Exception $e) {
            // Table might not exist, silently fail
            error_log('AI conversation log failed: ' . $e->getMessage());
        }
    }
}
