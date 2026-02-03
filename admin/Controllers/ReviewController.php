<?php
/**
 * Admin Review Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ReviewController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();

        $status = $_GET['status'] ?? '';
        $rating = $_GET['rating'] ?? '';

        $sql = "SELECT r.*, p.name as product_name, p.slug as product_slug,
                       u.first_name, u.last_name, u.email
                FROM reviews r
                LEFT JOIN products p ON r.product_id = p.id
                LEFT JOIN users u ON r.user_id = u.id
                WHERE 1=1";
        $params = [];

        if ($status === 'pending') {
            $sql .= " AND r.is_approved = 0";
        } elseif ($status === 'approved') {
            $sql .= " AND r.is_approved = 1";
        }

        if ($rating) {
            $sql .= " AND r.rating = ?";
            $params[] = (int)$rating;
        }

        $sql .= " ORDER BY r.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $reviews = $stmt->fetchAll();

        // Stats
        $stmt = $db->query("SELECT
            COUNT(*) as total,
            SUM(is_approved = 0) as pending,
            AVG(rating) as avg_rating
            FROM reviews");
        $stats = $stmt->fetch();

        $this->layout('admin');
        $this->view('pages/reviews/index', [
            'page_title' => 'Reviews',
            'active_page' => 'reviews',
            'reviews' => $reviews,
            'stats' => $stats,
            'filters' => ['status' => $status, 'rating' => $rating],
        ]);
    }

    public function approve(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false]);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE reviews SET is_approved = 1, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);

        // Update product rating
        $this->updateProductRating($db, $id);

        $this->json(['success' => true]);
    }

    public function reject(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false]);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE reviews SET is_approved = 0, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);

        $this->updateProductRating($db, $id);

        $this->json(['success' => true]);
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        // Get product_id before deleting
        $stmt = $db->prepare("SELECT product_id FROM reviews WHERE id = ?");
        $stmt->execute([$id]);
        $review = $stmt->fetch();

        $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$id]);

        // Update product rating
        if ($review) {
            $this->recalculateProductRating($db, $review['product_id']);
        }

        flash('success', 'Review deleted successfully');
        $this->redirect('/admin/reviews');
    }

    private function updateProductRating($db, int $reviewId): void
    {
        $stmt = $db->prepare("SELECT product_id FROM reviews WHERE id = ?");
        $stmt->execute([$reviewId]);
        $review = $stmt->fetch();

        if ($review) {
            $this->recalculateProductRating($db, $review['product_id']);
        }
    }

    private function recalculateProductRating($db, int $productId): void
    {
        $stmt = $db->prepare("
            SELECT AVG(rating) as avg, COUNT(*) as count
            FROM reviews WHERE product_id = ? AND is_approved = 1
        ");
        $stmt->execute([$productId]);
        $result = $stmt->fetch();

        $stmt = $db->prepare("UPDATE products SET rating_average = ?, rating_count = ? WHERE id = ?");
        $stmt->execute([$result['avg'], $result['count'], $productId]);
    }

    private function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);

        $viewPath = ADMIN_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View '$view' not found at '$viewPath'");
        }

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        if (isset($this->data['_layout'])) {
            $layoutPath = ADMIN_PATH . '/Views/layouts/' . $this->data['_layout'] . '.php';
            if (file_exists($layoutPath)) {
                include $layoutPath;
                return;
            }
        }

        echo $content;
    }

    protected function layout(string $layout): self
    {
        $this->data['_layout'] = $layout;
        return $this;
    }
}
