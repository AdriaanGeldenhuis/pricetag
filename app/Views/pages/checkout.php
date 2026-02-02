<!-- Checkout Page -->
<div class="container py-8">
    <h1 class="text-2xl font-bold mb-8">Checkout</h1>

    <form id="checkout-form" class="grid lg:grid-cols-3 gap-8">
        <?= csrfField() ?>

        <!-- Checkout Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contact Information -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Contact Information</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="email" class="form-label required">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input"
                               value="<?= e(auth() ? user()['email'] : old('email')) ?>" required>
                    </div>

                    <div class="form-group mb-0">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-input"
                               value="<?= e(old('phone')) ?>">
                    </div>
                </div>
            </div>

            <!-- Billing Address -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Billing Address</h2>
                </div>
                <div class="card-body">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="billing_first_name" class="form-label required">First Name</label>
                            <input type="text" id="billing_first_name" name="billing_first_name" class="form-input"
                                   value="<?= e(old('billing_first_name')) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="billing_last_name" class="form-label required">Last Name</label>
                            <input type="text" id="billing_last_name" name="billing_last_name" class="form-input"
                                   value="<?= e(old('billing_last_name')) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="billing_company" class="form-label">Company (Optional)</label>
                        <input type="text" id="billing_company" name="billing_company" class="form-input"
                               value="<?= e(old('billing_company')) ?>">
                    </div>

                    <div class="form-group">
                        <label for="billing_address_1" class="form-label required">Address</label>
                        <input type="text" id="billing_address_1" name="billing_address_1" class="form-input"
                               placeholder="Street address" value="<?= e(old('billing_address_1')) ?>" required>
                    </div>

                    <div class="form-group">
                        <input type="text" id="billing_address_2" name="billing_address_2" class="form-input"
                               placeholder="Apartment, suite, etc. (optional)" value="<?= e(old('billing_address_2')) ?>">
                    </div>

                    <div class="grid sm:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label for="billing_city" class="form-label required">City</label>
                            <input type="text" id="billing_city" name="billing_city" class="form-input"
                                   value="<?= e(old('billing_city')) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="billing_province" class="form-label required">Province</label>
                            <select id="billing_province" name="billing_province" class="form-select" required>
                                <option value="">Select province</option>
                                <option value="Eastern Cape">Eastern Cape</option>
                                <option value="Free State">Free State</option>
                                <option value="Gauteng">Gauteng</option>
                                <option value="KwaZulu-Natal">KwaZulu-Natal</option>
                                <option value="Limpopo">Limpopo</option>
                                <option value="Mpumalanga">Mpumalanga</option>
                                <option value="North West">North West</option>
                                <option value="Northern Cape">Northern Cape</option>
                                <option value="Western Cape">Western Cape</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="billing_postal_code" class="form-label required">Postal Code</label>
                            <input type="text" id="billing_postal_code" name="billing_postal_code" class="form-input"
                                   value="<?= e(old('billing_postal_code')) ?>" required>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-check">
                            <input type="checkbox" id="same_shipping" name="same_shipping" value="1" class="form-check-input" checked>
                            <span class="form-check-label">Shipping address same as billing</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Shipping Method -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Shipping Method</h2>
                </div>
                <div class="card-body space-y-3">
                    <?php foreach ($shippingOptions as $option): ?>
                    <label class="flex items-center justify-between p-4 border rounded-lg cursor-pointer hover:border-primary transition-colors">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="shipping_method" value="<?= e($option['id']) ?>" class="form-check-input"
                                   <?= $option['id'] === 'standard' ? 'checked' : '' ?>>
                            <div>
                                <span class="font-medium"><?= e($option['name']) ?></span>
                                <span class="text-sm text-muted block"><?= e($option['description']) ?></span>
                            </div>
                        </div>
                        <span class="font-semibold"><?= $option['price'] > 0 ? formatPrice($option['price']) : 'Free' ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Order Notes -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Order Notes (Optional)</h2>
                </div>
                <div class="card-body">
                    <textarea name="notes" rows="3" class="form-textarea"
                              placeholder="Special instructions for delivery..."></textarea>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div>
            <div class="card sticky top-24">
                <div class="card-header">
                    <h2 class="font-semibold">Order Summary</h2>
                </div>
                <div class="card-body">
                    <!-- Cart Items -->
                    <div class="space-y-4 mb-6">
                        <?php foreach ($items as $item): ?>
                        <div class="flex gap-3">
                            <div class="w-16 h-16 rounded-lg bg-background-alt overflow-hidden flex-shrink-0">
                                <?php if ($item['image']): ?>
                                <img src="<?= url('storage/uploads/' . e($item['image'])) ?>" alt="" class="w-full h-full object-cover">
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm truncate"><?= e($item['name']) ?></p>
                                <p class="text-sm text-muted">Qty: <?= $item['quantity'] ?></p>
                            </div>
                            <p class="font-medium text-sm"><?= formatPrice($item['price'] * $item['quantity']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Totals -->
                    <div class="border-t pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted">Subtotal</span>
                            <span><?= formatPrice($cart->getSubtotal()) ?></span>
                        </div>
                        <?php if ($cart->getDiscount() > 0): ?>
                        <div class="flex justify-between text-sm text-success">
                            <span>Discount</span>
                            <span>-<?= formatPrice($cart->getDiscount()) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-muted">Shipping</span>
                            <span id="checkout-shipping"><?= $cart->getShipping() > 0 ? formatPrice($cart->getShipping()) : 'Free' ?></span>
                        </div>
                        <div class="flex justify-between font-bold text-lg pt-2 border-t">
                            <span>Total</span>
                            <span id="checkout-total"><?= formatPrice($cart->getTotal()) ?></span>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="mt-6">
                        <div id="yoco-checkout"></div>
                        <button type="submit" id="pay-button" class="btn btn-primary btn-block btn-lg mt-4">
                            Pay <?= formatPrice($cart->getTotal()) ?>
                        </button>
                    </div>

                    <!-- Security -->
                    <div class="mt-4 text-center">
                        <p class="text-xs text-muted flex items-center justify-center gap-1">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Secure checkout powered by Yoco
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php if ($yocoPublicKey): ?>
<script src="https://js.yoco.com/sdk/v1/yoco-sdk-web.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const yoco = new window.YocoSDK({
        publicKey: '<?= e($yocoPublicKey) ?>'
    });

    const checkoutForm = document.getElementById('checkout-form');
    const payButton = document.getElementById('pay-button');

    checkoutForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        payButton.disabled = true;
        payButton.classList.add('is-loading');

        try {
            // First create the order
            const formData = new FormData(checkoutForm);
            const orderResponse = await fetch('<?= url('/checkout/process') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const orderData = await orderResponse.json();

            if (!orderData.success) {
                throw new Error(orderData.message || 'Order creation failed');
            }

            // Then process payment with Yoco
            yoco.showPopup({
                amountInCents: orderData.amount,
                currency: 'ZAR',
                name: '<?= e(config('app.name')) ?>',
                description: 'Order #' + orderData.order_number,
                callback: async function(result) {
                    if (result.error) {
                        window.Pricetag.Toast.error(result.error.message);
                        payButton.disabled = false;
                        payButton.classList.remove('is-loading');
                        return;
                    }

                    // Process the payment
                    const paymentFormData = new FormData();
                    paymentFormData.append('_token', '<?= csrfToken() ?>');
                    paymentFormData.append('order_id', orderData.order_id);
                    paymentFormData.append('yoco_token', result.id);

                    const paymentResponse = await fetch('<?= url('/checkout/process-payment') ?>', {
                        method: 'POST',
                        body: paymentFormData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const paymentData = await paymentResponse.json();

                    if (paymentData.success) {
                        window.location.href = paymentData.redirect;
                    } else {
                        window.Pricetag.Toast.error(paymentData.message || 'Payment failed');
                        payButton.disabled = false;
                        payButton.classList.remove('is-loading');
                    }
                }
            });
        } catch (err) {
            window.Pricetag.Toast.error(err.message || 'Something went wrong');
            payButton.disabled = false;
            payButton.classList.remove('is-loading');
        }
    });
});
</script>
<?php endif; ?>
