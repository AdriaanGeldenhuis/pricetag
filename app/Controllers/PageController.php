<?php
declare(strict_types=1);

/**
 * Page Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Handles static pages and CMS content.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class PageController extends Controller
{
    /**
     * Show a CMS page by slug
     */
    public function show(string $slug): void
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT * FROM pages
            WHERE slug = ? AND status = 'published'
        ");
        $stmt->execute([$slug]);
        $page = $stmt->fetch();

        // Fallback to built-in pages if not in database
        if (!$page) {
            $page = $this->getBuiltInPage($slug);
        }

        if (!$page) {
            http_response_code(404);
            $this->layout('main');
            $this->view('errors/404');
            return;
        }

        $this->layout('main');
        $this->view('pages/cms-page', [
            'meta_title' => $page['meta_title'] ?: $page['title'] . ' | ' . config('app.name'),
            'meta_description' => $page['meta_description'] ?: truncate(strip_tags($page['content']), 160),
            'page' => $page,
        ]);
    }

    /**
     * Offline page for PWA
     */
    public function offline(): void
    {
        include APP_PATH . '/Views/pages/offline.php';
        exit;
    }

    /**
     * Get built-in page content for known support pages
     */
    private function getBuiltInPage(string $slug): ?array
    {
        $pages = $this->getBuiltInPages();
        return $pages[$slug] ?? null;
    }

    /**
     * Built-in support pages (used when not in database)
     */
    private function getBuiltInPages(): array
    {
        $appName = config('app.name');

        return [
            'shipping' => [
                'title' => 'Shipping Information',
                'slug' => 'shipping',
                'excerpt' => 'Everything you need to know about our shipping options, delivery times, and costs.',
                'meta_title' => "Shipping Information | {$appName}",
                'meta_description' => "Learn about {$appName} shipping options, delivery times, costs, and tracking. We deliver across South Africa.",
                'template' => 'default',
                'updated_at' => date('Y-m-d H:i:s'),
                'content' => <<<'HTML'
<h2>Shipping Options</h2>
<p>We offer reliable shipping across South Africa through trusted courier partners. All orders are carefully packaged to ensure your items arrive safely.</p>

<table>
<thead>
<tr><th>Shipping Method</th><th>Delivery Time</th><th>Cost</th></tr>
</thead>
<tbody>
<tr><td><strong>Standard Delivery</strong></td><td>3–5 business days</td><td>R99 (Free over R1,000)</td></tr>
<tr><td><strong>Express Delivery</strong></td><td>1–2 business days</td><td>R199</td></tr>
<tr><td><strong>Economy Delivery</strong></td><td>5–7 business days</td><td>R59</td></tr>
<tr><td><strong>Collect (Johannesburg)</strong></td><td>Same day</td><td>Free</td></tr>
</tbody>
</table>

<h2>Free Shipping</h2>
<p>Enjoy <strong>free standard shipping</strong> on all orders over <strong>R1,000</strong>. No coupon code required — the discount is automatically applied at checkout.</p>

<h2>Delivery Areas</h2>
<p>We currently deliver to all major cities and towns across South Africa, including:</p>
<ul>
<li>Gauteng (Johannesburg, Pretoria, Centurion)</li>
<li>Western Cape (Cape Town, Stellenbosch)</li>
<li>KwaZulu-Natal (Durban, Pietermaritzburg)</li>
<li>Eastern Cape (Port Elizabeth, East London)</li>
<li>Free State (Bloemfontein)</li>
<li>All other provinces</li>
</ul>

<h2>Order Processing</h2>
<p>Orders placed before <strong>12:00 PM (SAST)</strong> on weekdays are processed the same day. Orders placed after 12:00 PM or on weekends are processed on the next business day.</p>

<h2>Tracking Your Order</h2>
<p>Once your order has been dispatched, you will receive an email with a tracking number. You can also track your order from your <a href="/account">account dashboard</a> or our <a href="/page/track-order">Track Order</a> page.</p>

<div class="info-box highlight">
<strong>Please Note:</strong> Delivery times are estimates and may vary during peak seasons, public holidays, or due to unforeseen circumstances. We will notify you of any significant delays.
</div>

<h2>Damaged or Missing Items</h2>
<p>If your package arrives damaged or items are missing, please contact us within <strong>48 hours</strong> of delivery. Take photos of the damaged packaging and items, and email them to <a href="mailto:info@pricetag.co.za">info@pricetag.co.za</a> with your order number.</p>
HTML
            ],

            'returns' => [
                'title' => 'Returns & Refunds',
                'slug' => 'returns',
                'excerpt' => 'Our returns policy, how to request a return, and refund processing times.',
                'meta_title' => "Returns & Refunds Policy | {$appName}",
                'meta_description' => "Learn about {$appName} return and refund policies. Easy 30-day returns on most items.",
                'template' => 'default',
                'updated_at' => date('Y-m-d H:i:s'),
                'content' => <<<'HTML'
<h2>Return Policy Overview</h2>
<p>We want you to be completely satisfied with your purchase. If you're not happy with your order, you can return most items within <strong>30 days</strong> of delivery for a full refund or exchange.</p>

<h2>Return Conditions</h2>
<p>To be eligible for a return, items must be:</p>
<ul>
<li>In their original, unused condition</li>
<li>In the original packaging with all accessories and documentation</li>
<li>Returned within 30 days of delivery</li>
<li>Accompanied by proof of purchase (order number or invoice)</li>
</ul>

<h3>Non-Returnable Items</h3>
<p>The following items cannot be returned unless defective:</p>
<ul>
<li>Opened software or digital products</li>
<li>Consumable items (ink cartridges, toner, etc.) once opened</li>
<li>Custom or special-order items</li>
<li>Items marked as "Final Sale"</li>
</ul>

<h2>How to Request a Return</h2>
<ol>
<li><strong>Log in</strong> to your <a href="/account">account</a> and go to your orders</li>
<li><strong>Select the order</strong> containing the item(s) you wish to return</li>
<li><strong>Click "Request Return"</strong> and select the reason for your return</li>
<li><strong>Pack the item(s)</strong> securely in the original packaging</li>
<li><strong>Ship it back</strong> using the return label provided, or drop it off at a designated point</li>
</ol>
<p>Alternatively, email us at <a href="mailto:info@pricetag.co.za">info@pricetag.co.za</a> with your order number and reason for return.</p>

<h2>Refund Processing</h2>
<table>
<thead>
<tr><th>Refund Method</th><th>Processing Time</th></tr>
</thead>
<tbody>
<tr><td>Original payment method</td><td>5–10 business days after item is received</td></tr>
<tr><td>Store credit</td><td>1–2 business days after item is received</td></tr>
<tr><td>Exchange</td><td>Ships within 2–3 business days after item is received</td></tr>
</tbody>
</table>

<div class="info-box highlight">
<strong>Defective Products:</strong> If you received a defective item, we will cover the return shipping costs and process your refund or replacement as a priority. Contact us immediately at <a href="mailto:info@pricetag.co.za">info@pricetag.co.za</a>.
</div>

<h2>Return Shipping Costs</h2>
<p>For change-of-mind returns, the customer is responsible for return shipping costs. For defective items or incorrect orders, Pricetag covers all return shipping costs.</p>
HTML
            ],

            'faq' => [
                'title' => 'Frequently Asked Questions',
                'slug' => 'faq',
                'excerpt' => 'Find answers to the most commonly asked questions about shopping at Pricetag.',
                'meta_title' => "FAQ - Frequently Asked Questions | {$appName}",
                'meta_description' => "Find answers to common questions about orders, shipping, returns, payments, and more at {$appName}.",
                'template' => 'faq',
                'updated_at' => date('Y-m-d H:i:s'),
                'content' => <<<'HTML'
<h2>Orders & Shopping</h2>

<div class="faq-item">
<button class="faq-question">How do I place an order?</button>
<div class="faq-answer">
<p>Simply browse our products, add items to your cart, and proceed to checkout. You can checkout as a guest or create an account for a faster experience next time. Follow the steps to enter your delivery address, choose a shipping method, and complete your payment.</p>
</div>
</div>

<div class="faq-item">
<button class="faq-question">Can I modify or cancel my order?</button>
<div class="faq-answer">
<p>You can modify or cancel your order as long as it hasn't been dispatched yet. Log into your account, go to your orders, and select the order you'd like to change. If it's already been dispatched, you'll need to wait for delivery and then request a return.</p>
</div>
</div>

<div class="faq-item">
<button class="faq-question">Do I need an account to place an order?</button>
<div class="faq-answer">
<p>No, you can checkout as a guest. However, creating an account allows you to track orders, save addresses, view order history, and earn loyalty rewards on future purchases.</p>
</div>
</div>

<h2>Shipping & Delivery</h2>

<div class="faq-item">
<button class="faq-question">How long does delivery take?</button>
<div class="faq-answer">
<p>Standard delivery takes 3–5 business days, express delivery takes 1–2 business days, and economy delivery takes 5–7 business days. Visit our <a href="/page/shipping">Shipping Information</a> page for full details.</p>
</div>
</div>

<div class="faq-item">
<button class="faq-question">How much does shipping cost?</button>
<div class="faq-answer">
<p>Standard shipping is R99, but it's <strong>free on orders over R1,000</strong>. Express delivery is R199 and economy is R59. Collection from our Johannesburg location is free.</p>
</div>
</div>

<div class="faq-item">
<button class="faq-question">How do I track my order?</button>
<div class="faq-answer">
<p>Once your order is dispatched, you'll receive a tracking number via email. You can also track your order from your <a href="/account">account dashboard</a> or on our <a href="/page/track-order">Track Order</a> page.</p>
</div>
</div>

<div class="faq-item">
<button class="faq-question">Do you deliver outside South Africa?</button>
<div class="faq-answer">
<p>Currently, we only deliver within South Africa. We're working on expanding to neighbouring countries soon. Sign up for our newsletter to be notified when international shipping becomes available.</p>
</div>
</div>

<h2>Returns & Refunds</h2>

<div class="faq-item">
<button class="faq-question">What is your return policy?</button>
<div class="faq-answer">
<p>We accept returns within 30 days of delivery. Items must be unused, in original packaging, and accompanied by proof of purchase. See our full <a href="/page/returns">Returns & Refunds</a> policy for details.</p>
</div>
</div>

<div class="faq-item">
<button class="faq-question">How long do refunds take?</button>
<div class="faq-answer">
<p>Refunds to your original payment method take 5–10 business days after we receive the returned item. Store credit refunds are processed within 1–2 business days.</p>
</div>
</div>

<h2>Payments & Security</h2>

<div class="faq-item">
<button class="faq-question">What payment methods do you accept?</button>
<div class="faq-answer">
<p>We accept Visa, Mastercard, American Express, and EFT (Electronic Funds Transfer). All card payments are processed securely through our payment gateway with SSL encryption.</p>
</div>
</div>

<div class="faq-item">
<button class="faq-question">Is my payment information secure?</button>
<div class="faq-answer">
<p>Absolutely. We use industry-standard SSL encryption and never store your full credit card details on our servers. All transactions are processed through PCI-DSS compliant payment gateways.</p>
</div>
</div>

<h2>Account & Support</h2>

<div class="faq-item">
<button class="faq-question">How do I create an account?</button>
<div class="faq-answer">
<p>Click the <a href="/register">Create Account</a> link and fill in your details. You'll receive a confirmation email to verify your account. Once verified, you can start shopping with all the benefits of a registered user.</p>
</div>
</div>

<div class="faq-item">
<button class="faq-question">I forgot my password. What do I do?</button>
<div class="faq-answer">
<p>Click on <a href="/forgot-password">Forgot Password</a> on the login page, enter your email address, and we'll send you a link to reset your password.</p>
</div>
</div>

<div class="faq-item">
<button class="faq-question">How do I contact customer support?</button>
<div class="faq-answer">
<p>You can reach us through our <a href="/contact">Contact Us</a> page, by email at <a href="mailto:info@pricetag.co.za">info@pricetag.co.za</a>, or during business hours (Mon–Fri 9am–5pm, Sat 9am–1pm).</p>
</div>
</div>
HTML
            ],

            'track-order' => [
                'title' => 'Track Your Order',
                'slug' => 'track-order',
                'excerpt' => 'Enter your order number to check the status of your delivery.',
                'meta_title' => "Track Order | {$appName}",
                'meta_description' => "Track your {$appName} order status. Enter your order number to see real-time delivery updates.",
                'template' => 'default',
                'updated_at' => date('Y-m-d H:i:s'),
                'content' => <<<'HTML'
<h2>Track Your Order</h2>
<p>Enter your order number below to check the current status of your delivery. Your order number can be found in your confirmation email or in your <a href="/account">account dashboard</a>.</p>

<div class="track-form">
<input type="text" placeholder="Enter your order number (e.g. PT-10045)" id="track-order-input">
<button onclick="trackOrder()">Track</button>
</div>

<div id="track-result"></div>

<h2>Order Status Guide</h2>
<table>
<thead>
<tr><th>Status</th><th>What It Means</th></tr>
</thead>
<tbody>
<tr><td><strong>Pending</strong></td><td>Your order has been received and is awaiting processing</td></tr>
<tr><td><strong>Processing</strong></td><td>We're preparing your order for shipment</td></tr>
<tr><td><strong>Shipped</strong></td><td>Your order is on its way — check your email for tracking details</td></tr>
<tr><td><strong>Out for Delivery</strong></td><td>The courier is delivering your package today</td></tr>
<tr><td><strong>Delivered</strong></td><td>Your order has been delivered</td></tr>
<tr><td><strong>Cancelled</strong></td><td>The order has been cancelled — a refund will be processed if applicable</td></tr>
</tbody>
</table>

<h2>Need Help?</h2>
<p>If you have any questions about your order or delivery, please don't hesitate to <a href="/contact">contact us</a>. Our support team is available Monday to Friday, 9am–5pm SAST.</p>

<script>
function trackOrder() {
    var num = document.getElementById('track-order-input').value.trim();
    if (!num) return;
    window.location.href = '/account/orders?search=' + encodeURIComponent(num);
}
document.getElementById('track-order-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') trackOrder();
});
</script>
HTML
            ],

            'privacy' => [
                'title' => 'Privacy Policy',
                'slug' => 'privacy',
                'excerpt' => 'How we collect, use, and protect your personal information.',
                'meta_title' => "Privacy Policy | {$appName}",
                'meta_description' => "{$appName} privacy policy. Learn how we collect, use, and protect your personal data in accordance with POPIA.",
                'template' => 'default',
                'updated_at' => date('Y-m-d H:i:s'),
                'content' => <<<'HTML'
<h2>Introduction</h2>
<p>Pricetag ("we", "our", "us") is committed to protecting your personal information and your right to privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and make purchases, in accordance with the Protection of Personal Information Act (POPIA).</p>

<h2>Information We Collect</h2>
<h3>Information You Provide</h3>
<ul>
<li><strong>Account Information:</strong> Name, email address, password, phone number</li>
<li><strong>Shipping Information:</strong> Delivery address, city, province, postal code</li>
<li><strong>Payment Information:</strong> Card details are processed securely by our payment provider — we do not store full card numbers</li>
<li><strong>Communication:</strong> Messages you send through our contact form or customer support</li>
</ul>

<h3>Information Collected Automatically</h3>
<ul>
<li><strong>Device Information:</strong> Browser type, operating system, screen resolution</li>
<li><strong>Usage Data:</strong> Pages visited, time spent, click patterns</li>
<li><strong>Cookies:</strong> Session cookies for functionality and analytics cookies for improving our service</li>
<li><strong>IP Address:</strong> For security, fraud prevention, and approximate location</li>
</ul>

<h2>How We Use Your Information</h2>
<ul>
<li>Process and fulfil your orders</li>
<li>Send order confirmations and shipping updates</li>
<li>Provide customer support</li>
<li>Improve our website and product offerings</li>
<li>Send promotional emails (only with your consent — you can opt out anytime)</li>
<li>Prevent fraud and ensure security</li>
<li>Comply with legal obligations</li>
</ul>

<h2>Information Sharing</h2>
<p>We do not sell your personal information. We share your data only with:</p>
<ul>
<li><strong>Courier partners:</strong> To deliver your orders</li>
<li><strong>Payment processors:</strong> To process your payments securely</li>
<li><strong>Analytics services:</strong> To understand and improve our website</li>
<li><strong>Legal authorities:</strong> When required by law or to protect our rights</li>
</ul>

<h2>Data Security</h2>
<p>We implement industry-standard security measures including SSL encryption, secure payment processing, regular security audits, and access controls. However, no method of transmission over the Internet is 100% secure, and we cannot guarantee absolute security.</p>

<h2>Your Rights Under POPIA</h2>
<p>You have the right to:</p>
<ul>
<li>Access your personal information</li>
<li>Correct inaccurate information</li>
<li>Request deletion of your data</li>
<li>Object to processing of your data</li>
<li>Withdraw consent for marketing communications</li>
<li>Lodge a complaint with the Information Regulator</li>
</ul>

<h2>Cookies</h2>
<p>We use essential cookies for site functionality (login sessions, shopping cart) and optional analytics cookies. You can manage cookie preferences through your browser settings.</p>

<h2>Changes to This Policy</h2>
<p>We may update this Privacy Policy from time to time. We will notify you of any significant changes by posting the new policy on this page and updating the "last updated" date.</p>

<h2>Contact Us</h2>
<p>If you have any questions about this Privacy Policy or wish to exercise your rights, please contact us at <a href="mailto:info@pricetag.co.za">info@pricetag.co.za</a> or through our <a href="/contact">Contact Us</a> page.</p>
HTML
            ],

            'terms' => [
                'title' => 'Terms of Service',
                'slug' => 'terms',
                'excerpt' => 'The terms and conditions governing your use of Pricetag.',
                'meta_title' => "Terms of Service | {$appName}",
                'meta_description' => "{$appName} terms and conditions. Read our terms of service for using our e-commerce platform.",
                'template' => 'default',
                'updated_at' => date('Y-m-d H:i:s'),
                'content' => <<<'HTML'
<h2>Agreement to Terms</h2>
<p>By accessing and using the Pricetag website ("Site"), you agree to be bound by these Terms of Service. If you do not agree with any part of these terms, please do not use our Site.</p>

<h2>Products & Pricing</h2>
<ul>
<li>All prices are displayed in South African Rand (ZAR) and include VAT unless otherwise stated</li>
<li>We reserve the right to change prices at any time without prior notice</li>
<li>While we strive for accuracy, we are not responsible for pricing errors. If an error is discovered, we will notify you and offer the option to proceed at the correct price or cancel the order</li>
<li>Product images are for illustration purposes. Actual products may vary slightly in appearance</li>
<li>Product availability is subject to stock levels and may change without notice</li>
</ul>

<h2>Orders & Payment</h2>
<ul>
<li>Placing an order constitutes an offer to purchase. We reserve the right to accept or decline any order</li>
<li>An order confirmation email does not constitute acceptance — acceptance occurs when the order is dispatched</li>
<li>Payment must be made in full before orders are processed</li>
<li>We accept Visa, Mastercard, American Express, and EFT payments</li>
</ul>

<h2>Shipping & Delivery</h2>
<p>Please refer to our <a href="/page/shipping">Shipping Information</a> page for full details on delivery options, timeframes, and costs. Delivery dates are estimates and not guaranteed.</p>

<h2>Returns & Refunds</h2>
<p>Our return and refund policies are detailed on our <a href="/page/returns">Returns & Refunds</a> page. By making a purchase, you agree to the conditions outlined there.</p>

<h2>User Accounts</h2>
<ul>
<li>You are responsible for maintaining the confidentiality of your account credentials</li>
<li>You must provide accurate and complete information when creating an account</li>
<li>You are responsible for all activities that occur under your account</li>
<li>We reserve the right to suspend or terminate accounts that violate these terms</li>
</ul>

<h2>Intellectual Property</h2>
<p>All content on this Site — including text, graphics, logos, images, and software — is the property of Pricetag or its content suppliers and is protected by South African and international copyright laws. You may not reproduce, distribute, or create derivative works without our explicit written permission.</p>

<h2>Limitation of Liability</h2>
<p>To the maximum extent permitted by law, Pricetag shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of the Site or purchase of products. Our total liability shall not exceed the amount paid for the product in question.</p>

<h2>Warranty</h2>
<p>Products are covered by the manufacturer's warranty where applicable. Pricetag does not provide additional warranties beyond those required by the Consumer Protection Act (CPA). Defective products within the warranty period should be reported to us for resolution.</p>

<h2>Governing Law</h2>
<p>These terms are governed by and construed in accordance with the laws of the Republic of South Africa. Any disputes arising from these terms shall be subject to the jurisdiction of the South African courts.</p>

<h2>Changes to Terms</h2>
<p>We may revise these Terms of Service at any time. Changes will be effective upon posting to the Site. Your continued use of the Site after changes are posted constitutes acceptance of the revised terms.</p>

<h2>Contact</h2>
<p>For questions about these Terms of Service, please contact us at <a href="mailto:info@pricetag.co.za">info@pricetag.co.za</a> or via our <a href="/contact">Contact Us</a> page.</p>
HTML
            ],
        ];
    }
}
