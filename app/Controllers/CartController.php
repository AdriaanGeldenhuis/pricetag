<?php
/**
 * Cart Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    public function index(): void
    {
        $cart = new Cart();

        $this->layout('main');
        $this->view('pages/cart', [
            'meta_title' => 'Shopping Cart | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'cart' => $cart,
            'items' => $cart->getItems(),
            'subtotal' => $cart->getSubtotal(),
            'discount' => $cart->getDiscount(),
            'shipping' => $cart->getShipping(),
            'total' => $cart->getTotal(),
        ]);
    }

    public function add(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $variantId = !empty($_POST['variant_id']) ? (int) $_POST['variant_id'] : null;
        $options = $_POST['options'] ?? [];

        $cart = new Cart();
        $success = $cart->add($productId, $quantity, $variantId, $options);

        if (isAjax()) {
            if ($success) {
                $product = Product::find($productId);
                $this->json([
                    'success' => true,
                    'message' => 'Added to cart',
                    'cart' => [
                        'count' => $cart->getCount(),
                        'subtotal' => $cart->getSubtotal(),
                        'total' => $cart->getTotal(),
                        'items' => $cart->getItems(),
                    ],
                    'product' => [
                        'name' => $product->name,
                        'image' => $product->getPrimaryImage(),
                    ],
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Could not add to cart'], 400);
            }
            return;
        }

        if ($success) {
            flash('success', 'Item added to cart');
        } else {
            flash('error', 'Could not add item to cart');
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/cart');
    }

    public function update(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $itemId = (int) ($_POST['item_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);

        $cart = new Cart();
        $success = $cart->update($itemId, $quantity);

        if (isAjax()) {
            if ($success) {
                $this->json([
                    'success' => true,
                    'cart' => [
                        'count' => $cart->getCount(),
                        'subtotal' => $cart->getSubtotal(),
                        'discount' => $cart->getDiscount(),
                        'shipping' => $cart->getShipping(),
                        'total' => $cart->getTotal(),
                        'items' => $cart->getItems(),
                    ],
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Could not update cart'], 400);
            }
            return;
        }

        $this->redirect('/cart');
    }

    public function remove(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $itemId = (int) ($_POST['item_id'] ?? 0);

        $cart = new Cart();
        $success = $cart->remove($itemId);

        if (isAjax()) {
            $this->json([
                'success' => $success,
                'cart' => [
                    'count' => $cart->getCount(),
                    'subtotal' => $cart->getSubtotal(),
                    'discount' => $cart->getDiscount(),
                    'shipping' => $cart->getShipping(),
                    'total' => $cart->getTotal(),
                    'items' => $cart->getItems(),
                ],
            ]);
            return;
        }

        $this->redirect('/cart');
    }

    public function data(): void
    {
        $cart = new Cart();

        $this->json([
            'count' => $cart->getCount(),
            'subtotal' => $cart->getSubtotal(),
            'discount' => $cart->getDiscount(),
            'shipping' => $cart->getShipping(),
            'total' => $cart->getTotal(),
            'items' => $cart->getItems(),
        ]);
    }

    public function applyCoupon(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $code = trim($_POST['coupon_code'] ?? '');

        if (empty($code)) {
            if (isAjax()) {
                $this->json(['success' => false, 'message' => 'Please enter a coupon code'], 400);
            }
            flash('error', 'Please enter a coupon code');
            $this->redirect('/cart');
            return;
        }

        $cart = new Cart();
        $result = $cart->applyCoupon($code);

        if (isAjax()) {
            if ($result['success']) {
                $this->json([
                    'success' => true,
                    'message' => 'Coupon applied',
                    'cart' => [
                        'subtotal' => $cart->getSubtotal(),
                        'discount' => $cart->getDiscount(),
                        'shipping' => $cart->getShipping(),
                        'total' => $cart->getTotal(),
                    ],
                ]);
            } else {
                $this->json(['success' => false, 'message' => $result['message']], 400);
            }
            return;
        }

        if ($result['success']) {
            flash('success', 'Coupon applied successfully');
        } else {
            flash('error', $result['message']);
        }

        $this->redirect('/cart');
    }
}
