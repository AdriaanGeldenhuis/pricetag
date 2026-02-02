<?php
/**
 * Wishlist Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Product;

class WishlistController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $userId = $_SESSION['user_id'];
        $db = Database::getInstance();

        // Get wishlist items with product data
        $stmt = $db->prepare("
            SELECT w.*, p.name, p.slug, p.price, p.compare_price, p.status, p.stock_quantity,
                   pi.path as image
            FROM wishlists w
            JOIN products p ON p.id = w.product_id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll();

        $this->layout('main');
        $this->view('pages/wishlist', [
            'meta_title' => 'My Wishlist | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'items' => $items,
        ]);
    }

    public function toggle(): void
    {
        if (!auth()) {
            if (isAjax()) {
                $this->json(['success' => false, 'message' => 'Please login to use wishlist'], 401);
            }
            $this->redirect('/login');
            return;
        }

        if (!$this->validateCsrf()) {
            return;
        }

        $productId = (int) ($_POST['product_id'] ?? 0);
        $userId = $_SESSION['user_id'];

        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Invalid product'], 400);
            return;
        }

        $db = Database::getInstance();

        // Check if already in wishlist
        $stmt = $db->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Remove from wishlist
            $stmt = $db->prepare("DELETE FROM wishlists WHERE id = ?");
            $stmt->execute([$existing['id']]);
            $inWishlist = false;
        } else {
            // Add to wishlist
            $stmt = $db->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
            $stmt->execute([$userId, $productId]);
            $inWishlist = true;
        }

        // Get count
        $stmt = $db->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
        $stmt->execute([$userId]);
        $count = (int) $stmt->fetchColumn();

        $_SESSION['wishlist_count'] = $count;

        $this->json([
            'success' => true,
            'in_wishlist' => $inWishlist,
            'count' => $count,
        ]);
    }

    public function remove(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $wishlistId = (int) ($_POST['wishlist_id'] ?? 0);
        $userId = $_SESSION['user_id'];

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM wishlists WHERE id = ? AND user_id = ?");
        $stmt->execute([$wishlistId, $userId]);

        // Update count
        $stmt = $db->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
        $stmt->execute([$userId]);
        $_SESSION['wishlist_count'] = (int) $stmt->fetchColumn();

        if (isAjax()) {
            $this->json(['success' => true]);
            return;
        }

        flash('success', 'Item removed from wishlist');
        $this->redirect('/wishlist');
    }

    public function moveToCart(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $wishlistId = (int) ($_POST['wishlist_id'] ?? 0);
        $userId = $_SESSION['user_id'];

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT product_id, variant_id FROM wishlists WHERE id = ? AND user_id = ?");
        $stmt->execute([$wishlistId, $userId]);
        $item = $stmt->fetch();

        if (!$item) {
            if (isAjax()) {
                $this->json(['success' => false, 'message' => 'Item not found'], 404);
            }
            $this->redirect('/wishlist');
            return;
        }

        // Add to cart
        $cart = new \App\Models\Cart();
        $success = $cart->add($item['product_id'], 1, $item['variant_id']);

        if ($success) {
            // Remove from wishlist
            $stmt = $db->prepare("DELETE FROM wishlists WHERE id = ?");
            $stmt->execute([$wishlistId]);

            // Update wishlist count
            $stmt = $db->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
            $stmt->execute([$userId]);
            $_SESSION['wishlist_count'] = (int) $stmt->fetchColumn();
        }

        if (isAjax()) {
            $this->json([
                'success' => $success,
                'cart_count' => $cart->getCount(),
                'wishlist_count' => $_SESSION['wishlist_count'],
            ]);
            return;
        }

        flash($success ? 'success' : 'error', $success ? 'Item moved to cart' : 'Could not add to cart');
        $this->redirect('/wishlist');
    }
}
