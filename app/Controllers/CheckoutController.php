<?php
/**
 * Checkout Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use App\Services\YocoPayment;

class CheckoutController extends Controller
{
    public function index(): void
    {
        $cart = new Cart();

        if ($cart->getCount() === 0) {
            flash('error', 'Your cart is empty');
            $this->redirect('/cart');
            return;
        }

        // Get saved addresses if logged in
        $addresses = [];
        if (auth()) {
            $user = User::find($_SESSION['user_id']);
            $addresses = $user ? $user->getAddresses() : [];
        }

        // Get shipping options
        $shippingOptions = [
            [
                'id' => 'standard',
                'name' => 'Standard Delivery',
                'description' => '3-5 business days',
                'price' => config('payment.shipping.default_rate', 75),
            ],
            [
                'id' => 'express',
                'name' => 'Express Delivery',
                'description' => '1-2 business days',
                'price' => config('payment.shipping.express_rate', 150),
            ],
        ];

        // Check if free shipping applies
        if ($cart->getSubtotal() >= config('payment.shipping.free_threshold', 500)) {
            $shippingOptions[0]['price'] = 0;
            $shippingOptions[0]['name'] = 'Free Standard Delivery';
        }

        $this->layout('main');
        $this->view('pages/checkout', [
            'meta_title' => 'Checkout | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'cart' => $cart,
            'items' => $cart->getItems(),
            'addresses' => $addresses,
            'shippingOptions' => $shippingOptions,
            'yocoPublicKey' => config('payment.gateways.yoco.public_key'),
        ]);
    }

    public function process(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        // Validate input
        $validation = $this->validate([
            'email' => 'required|email',
            'billing_first_name' => 'required|min:2|max:100',
            'billing_last_name' => 'required|min:2|max:100',
            'billing_address_1' => 'required|min:5|max:255',
            'billing_city' => 'required|min:2|max:100',
            'billing_province' => 'required|min:2|max:100',
            'billing_postal_code' => 'required|min:4|max:20',
            'shipping_method' => 'required|in:standard,express',
        ]);

        if (!$validation['valid']) {
            if (isAjax()) {
                $this->json(['success' => false, 'errors' => $validation['errors']], 422);
            }
            $this->redirect('/checkout');
            return;
        }

        $cart = new Cart();

        if ($cart->getCount() === 0) {
            if (isAjax()) {
                $this->json(['success' => false, 'message' => 'Cart is empty'], 400);
            }
            $this->redirect('/cart');
            return;
        }

        // Create order
        $order = Order::createFromCart($cart, $_POST);

        if (!$order) {
            if (isAjax()) {
                $this->json(['success' => false, 'message' => 'Could not create order'], 500);
            }
            flash('error', 'Could not process your order. Please try again.');
            $this->redirect('/checkout');
            return;
        }

        // Create payment
        $paymentId = $order->createPayment('yoco', $order->total);

        if (isAjax()) {
            $this->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_id' => $paymentId,
                'amount' => $order->total * 100, // Cents for Yoco
                'currency' => 'ZAR',
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);
            return;
        }

        // Store order ID in session for payment processing
        $_SESSION['pending_order_id'] = $order->id;
        $this->redirect('/checkout/pay/' . $order->order_number);
    }

    public function processPayment(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $orderId = $_POST['order_id'] ?? null;
        $yocoToken = $_POST['yoco_token'] ?? null;

        if (!$orderId || !$yocoToken) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        $order = Order::find($orderId);

        if (!$order || $order->payment_status !== 'pending') {
            $this->json(['success' => false, 'message' => 'Order not found'], 404);
            return;
        }

        // Process payment with Yoco
        $yoco = new YocoPayment();
        $result = $yoco->charge([
            'token' => $yocoToken,
            'amountInCents' => (int) ($order->total * 100),
            'currency' => 'ZAR',
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ]);

        if ($result['success']) {
            $order->updatePaymentStatus('completed', $result['transaction_id'], $result['data'] ?? null);

            // Clear cart
            $cart = new Cart();
            $cart->clear();

            $this->json([
                'success' => true,
                'redirect' => url('/checkout/success/' . $order->order_number),
            ]);
        } else {
            $order->updatePaymentStatus('failed', null, ['error' => $result['message']]);

            $this->json([
                'success' => false,
                'message' => $result['message'] ?? 'Payment failed',
            ], 400);
        }
    }

    public function success(string $orderNumber): void
    {
        $order = Order::findByOrderNumber($orderNumber);

        if (!$order) {
            http_response_code(404);
            $this->layout('main');
            $this->view('errors/404');
            return;
        }

        // Verify user owns this order or it's a guest order
        if ($order->user_id && $order->user_id !== ($_SESSION['user_id'] ?? null)) {
            http_response_code(403);
            $this->redirect('/');
            return;
        }

        $this->layout('main');
        $this->view('pages/checkout-success', [
            'meta_title' => 'Order Confirmed | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'order' => $order,
            'items' => $order->getItems(),
        ]);
    }

    public function webhook(): void
    {
        $payload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_YOCO_SIGNATURE'] ?? '';

        $yoco = new YocoPayment();

        if (!$yoco->verifyWebhook($payload, $signature)) {
            http_response_code(401);
            exit('Invalid signature');
        }

        $data = json_decode($payload, true);

        if (!$data) {
            http_response_code(400);
            exit('Invalid payload');
        }

        $event = $data['type'] ?? '';
        $chargeData = $data['payload'] ?? [];

        logMessage('info', 'Yoco webhook received', ['type' => $event, 'data' => $chargeData]);

        if ($event === 'payment.succeeded') {
            $orderId = $chargeData['metadata']['order_id'] ?? null;

            if ($orderId) {
                $order = Order::find($orderId);
                if ($order && $order->payment_status !== 'paid') {
                    $order->updatePaymentStatus('completed', $chargeData['id'] ?? null);
                }
            }
        } elseif ($event === 'payment.failed') {
            $orderId = $chargeData['metadata']['order_id'] ?? null;

            if ($orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    $order->updatePaymentStatus('failed', null, $chargeData);
                }
            }
        }

        http_response_code(200);
        exit('OK');
    }
}
