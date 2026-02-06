<!-- Checkout Page - Multi-Step Checkout Flow -->
<div class="checkout-page">
    <div class="container py-8">
        <!-- Checkout Header -->
        <div class="checkout-header">
            <a href="<?= url('/cart') ?>" class="checkout-back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Cart
            </a>
            <h1 class="checkout-title">Checkout</h1>
        </div>

        <!-- Progress Steps -->
        <div class="checkout-progress">
            <div class="checkout-step active" data-step="1">
                <div class="checkout-step-number">1</div>
                <span class="checkout-step-label">Information</span>
            </div>
            <div class="checkout-step-line"></div>
            <div class="checkout-step" data-step="2">
                <div class="checkout-step-number">2</div>
                <span class="checkout-step-label">Shipping</span>
            </div>
            <div class="checkout-step-line"></div>
            <div class="checkout-step" data-step="3">
                <div class="checkout-step-number">3</div>
                <span class="checkout-step-label">Payment</span>
            </div>
        </div>

        <form id="checkout-form" class="checkout-grid">
            <?= csrfField() ?>

            <!-- Main Content -->
            <div class="checkout-main">
                <!-- Step 1: Contact & Address Information -->
                <div class="checkout-step-content active" data-step="1">
                    <!-- Express Checkout (Guest users) -->
                    <?php if (!auth()): ?>
                    <div class="card checkout-express">
                        <div class="card-body">
                            <div class="checkout-express-header">
                                <span class="checkout-express-badge">Express</span>
                                <h3>Already have an account?</h3>
                            </div>
                            <p class="text-muted text-sm mb-4">Log in for faster checkout with saved addresses and order history.</p>
                            <a href="<?= url('/login?redirect=' . urlencode('/checkout')) ?>" class="btn btn-outline btn-block">
                                Log in to your account
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Contact Information -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold">Contact Information</h2>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="email" class="form-label required">Email Address</label>
                                <input type="email" id="email" name="email" class="form-input"
                                       value="<?= e(auth() ? user()['email'] : old('email')) ?>"
                                       placeholder="your@email.com" required>
                                <p class="form-hint">Order confirmation and tracking updates will be sent here</p>
                            </div>

                            <div class="form-group mb-0">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-input"
                                       value="<?= e(old('phone', auth() ? (user()['phone'] ?? '') : '')) ?>"
                                       placeholder="072 123 4567">
                                <p class="form-hint">For delivery updates (optional but recommended)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Saved Addresses (for logged in users) -->
                    <?php if (auth() && !empty($addresses)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold">Saved Addresses</h2>
                        </div>
                        <div class="card-body">
                            <div class="saved-addresses">
                                <?php foreach ($addresses as $index => $address): ?>
                                <label class="saved-address-option <?= $index === 0 ? 'selected' : '' ?>">
                                    <input type="radio" name="saved_address" value="<?= $address['id'] ?>"
                                           <?= $index === 0 ? 'checked' : '' ?> class="saved-address-radio">
                                    <div class="saved-address-content">
                                        <?php if ($address['is_default']): ?>
                                        <span class="saved-address-badge">Default</span>
                                        <?php endif; ?>
                                        <p class="saved-address-name"><?= e($address['first_name'] . ' ' . $address['last_name']) ?></p>
                                        <p class="saved-address-line"><?= e($address['address_1']) ?></p>
                                        <?php if ($address['address_2']): ?>
                                        <p class="saved-address-line"><?= e($address['address_2']) ?></p>
                                        <?php endif; ?>
                                        <p class="saved-address-line"><?= e($address['city'] . ', ' . $address['province'] . ' ' . $address['postal_code']) ?></p>
                                    </div>
                                    <svg class="saved-address-check" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </label>
                                <?php endforeach; ?>

                                <label class="saved-address-option new-address-option">
                                    <input type="radio" name="saved_address" value="new" class="saved-address-radio">
                                    <div class="saved-address-content">
                                        <p class="saved-address-name">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                            </svg>
                                            Use a different address
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Billing Address -->
                    <div class="card" id="billing-address-card" <?= auth() && !empty($addresses) ? 'style="display:none"' : '' ?>>
                        <div class="card-header">
                            <h2 class="font-semibold">Billing Address</h2>
                        </div>
                        <div class="card-body">
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label for="billing_first_name" class="form-label required">First Name</label>
                                    <input type="text" id="billing_first_name" name="billing_first_name" class="form-input"
                                           value="<?= e(old('billing_first_name', auth() ? (user()['first_name'] ?? '') : '')) ?>"
                                           autocomplete="given-name" required>
                                </div>
                                <div class="form-group">
                                    <label for="billing_last_name" class="form-label required">Last Name</label>
                                    <input type="text" id="billing_last_name" name="billing_last_name" class="form-input"
                                           value="<?= e(old('billing_last_name', auth() ? (user()['last_name'] ?? '') : '')) ?>"
                                           autocomplete="family-name" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="billing_company" class="form-label">Company (Optional)</label>
                                <input type="text" id="billing_company" name="billing_company" class="form-input"
                                       value="<?= e(old('billing_company')) ?>" autocomplete="organization">
                            </div>

                            <div class="form-group">
                                <label for="billing_address_1" class="form-label required">Street Address</label>
                                <input type="text" id="billing_address_1" name="billing_address_1" class="form-input"
                                       placeholder="123 Main Street" value="<?= e(old('billing_address_1')) ?>"
                                       autocomplete="address-line1" required>
                            </div>

                            <div class="form-group">
                                <label for="billing_address_2" class="form-label">Apartment, Suite, etc.</label>
                                <input type="text" id="billing_address_2" name="billing_address_2" class="form-input"
                                       placeholder="Apt 4B (optional)" value="<?= e(old('billing_address_2')) ?>"
                                       autocomplete="address-line2">
                            </div>

                            <div class="grid sm:grid-cols-3 gap-4">
                                <div class="form-group">
                                    <label for="billing_city" class="form-label required">City</label>
                                    <input type="text" id="billing_city" name="billing_city" class="form-input"
                                           value="<?= e(old('billing_city')) ?>" autocomplete="address-level2" required>
                                </div>
                                <div class="form-group">
                                    <label for="billing_province" class="form-label required">Province</label>
                                    <select id="billing_province" name="billing_province" class="form-select"
                                            autocomplete="address-level1" required>
                                        <option value="">Select...</option>
                                        <option value="Eastern Cape" <?= old('billing_province') === 'Eastern Cape' ? 'selected' : '' ?>>Eastern Cape</option>
                                        <option value="Free State" <?= old('billing_province') === 'Free State' ? 'selected' : '' ?>>Free State</option>
                                        <option value="Gauteng" <?= old('billing_province') === 'Gauteng' ? 'selected' : '' ?>>Gauteng</option>
                                        <option value="KwaZulu-Natal" <?= old('billing_province') === 'KwaZulu-Natal' ? 'selected' : '' ?>>KwaZulu-Natal</option>
                                        <option value="Limpopo" <?= old('billing_province') === 'Limpopo' ? 'selected' : '' ?>>Limpopo</option>
                                        <option value="Mpumalanga" <?= old('billing_province') === 'Mpumalanga' ? 'selected' : '' ?>>Mpumalanga</option>
                                        <option value="North West" <?= old('billing_province') === 'North West' ? 'selected' : '' ?>>North West</option>
                                        <option value="Northern Cape" <?= old('billing_province') === 'Northern Cape' ? 'selected' : '' ?>>Northern Cape</option>
                                        <option value="Western Cape" <?= old('billing_province') === 'Western Cape' ? 'selected' : '' ?>>Western Cape</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="billing_postal_code" class="form-label required">Postal Code</label>
                                    <input type="text" id="billing_postal_code" name="billing_postal_code" class="form-input"
                                           value="<?= e(old('billing_postal_code')) ?>" autocomplete="postal-code"
                                           pattern="[0-9]{4}" maxlength="4" required>
                                </div>
                            </div>

                            <?php if (!auth()): ?>
                            <div class="form-group mb-0">
                                <label class="form-check">
                                    <input type="checkbox" name="create_account" value="1" class="form-check-input">
                                    <span class="form-check-label">Create an account for faster checkout next time</span>
                                </label>
                            </div>

                            <div id="account-password-fields" class="mt-4" style="display: none;">
                                <div class="form-group mb-0">
                                    <label for="password" class="form-label required">Create Password</label>
                                    <input type="password" id="password" name="password" class="form-input"
                                           minlength="8" autocomplete="new-password">
                                    <p class="form-hint">Minimum 8 characters</p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Shipping Address Toggle -->
                    <div class="card">
                        <div class="card-body">
                            <label class="form-check">
                                <input type="checkbox" id="same_shipping" name="same_shipping" value="1" class="form-check-input" checked>
                                <span class="form-check-label">Ship to the same address</span>
                            </label>
                        </div>
                    </div>

                    <!-- Shipping Address (different from billing) -->
                    <div class="card" id="shipping-address-card" style="display: none;">
                        <div class="card-header">
                            <h2 class="font-semibold">Shipping Address</h2>
                        </div>
                        <div class="card-body">
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label for="shipping_first_name" class="form-label required">First Name</label>
                                    <input type="text" id="shipping_first_name" name="shipping_first_name" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label for="shipping_last_name" class="form-label required">Last Name</label>
                                    <input type="text" id="shipping_last_name" name="shipping_last_name" class="form-input">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="shipping_company" class="form-label">Company (Optional)</label>
                                <input type="text" id="shipping_company" name="shipping_company" class="form-input">
                            </div>

                            <div class="form-group">
                                <label for="shipping_address_1" class="form-label required">Street Address</label>
                                <input type="text" id="shipping_address_1" name="shipping_address_1" class="form-input">
                            </div>

                            <div class="form-group">
                                <label for="shipping_address_2" class="form-label">Apartment, Suite, etc.</label>
                                <input type="text" id="shipping_address_2" name="shipping_address_2" class="form-input">
                            </div>

                            <div class="grid sm:grid-cols-3 gap-4">
                                <div class="form-group">
                                    <label for="shipping_city" class="form-label required">City</label>
                                    <input type="text" id="shipping_city" name="shipping_city" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label for="shipping_province" class="form-label required">Province</label>
                                    <select id="shipping_province" name="shipping_province" class="form-select">
                                        <option value="">Select...</option>
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
                                    <label for="shipping_postal_code" class="form-label required">Postal Code</label>
                                    <input type="text" id="shipping_postal_code" name="shipping_postal_code" class="form-input"
                                           pattern="[0-9]{4}" maxlength="4">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-step-actions">
                        <button type="button" class="btn btn-primary btn-lg" data-next-step="2">
                            Continue to Shipping
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Shipping Method -->
                <div class="checkout-step-content" data-step="2">
                    <!-- Address Summary -->
                    <div class="card">
                        <div class="card-header flex items-center justify-between">
                            <h2 class="font-semibold">Delivery Address</h2>
                            <button type="button" class="btn btn-sm btn-ghost" data-goto-step="1">Edit</button>
                        </div>
                        <div class="card-body">
                            <div class="checkout-address-summary" id="shipping-address-summary">
                                <!-- Populated by JS -->
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Method Selection -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold">Shipping Method</h2>
                        </div>
                        <div class="card-body">
                            <div class="shipping-methods">
                                <?php foreach ($shippingOptions as $index => $option): ?>
                                <label class="shipping-method-option <?= $index === 0 ? 'selected' : '' ?>">
                                    <input type="radio" name="shipping_method" value="<?= e($option['id']) ?>"
                                           data-price="<?= $option['price'] ?>"
                                           <?= $index === 0 ? 'checked' : '' ?> class="shipping-method-radio">
                                    <div class="shipping-method-content">
                                        <div class="shipping-method-icon">
                                            <?php if ($option['id'] === 'express'): ?>
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                            </svg>
                                            <?php else: ?>
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="1" y="3" width="15" height="13"></rect>
                                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                            </svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="shipping-method-details">
                                            <span class="shipping-method-name"><?= e($option['name']) ?></span>
                                            <span class="shipping-method-time"><?= e($option['description']) ?></span>
                                        </div>
                                        <span class="shipping-method-price">
                                            <?= $option['price'] > 0 ? formatPrice($option['price']) : 'FREE' ?>
                                        </span>
                                    </div>
                                    <svg class="shipping-method-check" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </label>
                                <?php endforeach; ?>
                            </div>

                            <!-- Delivery Estimate -->
                            <div class="delivery-estimate">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <span>Estimated delivery: <strong id="delivery-estimate">3-5 business days</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold">Delivery Instructions (Optional)</h2>
                        </div>
                        <div class="card-body">
                            <textarea name="notes" rows="3" class="form-textarea" id="order-notes"
                                      placeholder="Gate code, special instructions, etc."></textarea>
                        </div>
                    </div>

                    <div class="checkout-step-actions">
                        <button type="button" class="btn btn-outline btn-lg" data-prev-step="1">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                            Back
                        </button>
                        <button type="button" class="btn btn-primary btn-lg" data-next-step="3">
                            Continue to Payment
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Payment -->
                <div class="checkout-step-content" data-step="3">
                    <!-- Order Review -->
                    <div class="card">
                        <div class="card-header flex items-center justify-between">
                            <h2 class="font-semibold">Order Review</h2>
                            <button type="button" class="btn btn-sm btn-ghost" data-goto-step="2">Edit</button>
                        </div>
                        <div class="card-body">
                            <div class="checkout-review-grid">
                                <div class="checkout-review-section">
                                    <h4 class="checkout-review-label">Contact</h4>
                                    <p id="review-contact"></p>
                                </div>
                                <div class="checkout-review-section">
                                    <h4 class="checkout-review-label">Ship to</h4>
                                    <p id="review-shipping-address"></p>
                                </div>
                                <div class="checkout-review-section">
                                    <h4 class="checkout-review-label">Shipping</h4>
                                    <p id="review-shipping-method"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold">Payment</h2>
                        </div>
                        <div class="card-body">
                            <p class="text-muted text-sm mb-4">All transactions are secure and encrypted.</p>

                            <div class="payment-methods">
                                <label class="payment-method-option selected">
                                    <input type="radio" name="payment_method" value="card" checked class="payment-method-radio">
                                    <div class="payment-method-content">
                                        <span class="payment-method-name">Credit / Debit Card</span>
                                        <div class="payment-method-icons">
                                            <img src="<?= url('assets/images/payments/visa.svg') ?>" alt="Visa" height="24">
                                            <img src="<?= url('assets/images/payments/mastercard.svg') ?>" alt="Mastercard" height="24">
                                        </div>
                                    </div>
                                </label>

                                <label class="payment-method-option">
                                    <input type="radio" name="payment_method" value="eft" class="payment-method-radio">
                                    <div class="payment-method-content">
                                        <span class="payment-method-name">Instant EFT</span>
                                        <span class="payment-method-note">Pay securely with your bank</span>
                                    </div>
                                </label>
                            </div>

                            <!-- Card Payment Form (Yoco) -->
                            <div id="card-payment-form" class="mt-6">
                                <div id="yoco-checkout"></div>
                            </div>

                            <!-- EFT Instructions (hidden by default) -->
                            <div id="eft-payment-info" class="mt-6" style="display: none;">
                                <div class="alert alert-info">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                    </svg>
                                    <span>You will be redirected to your bank to complete the payment securely.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="card">
                        <div class="card-body">
                            <label class="form-check">
                                <input type="checkbox" name="terms" value="1" class="form-check-input" required>
                                <span class="form-check-label">
                                    I agree to the <a href="<?= url('/page/terms') ?>" target="_blank" class="text-primary">Terms & Conditions</a>
                                    and <a href="<?= url('/page/privacy') ?>" target="_blank" class="text-primary">Privacy Policy</a>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="checkout-step-actions">
                        <button type="button" class="btn btn-outline btn-lg" data-prev-step="2">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                            Back
                        </button>
                        <button type="submit" id="pay-button" class="btn btn-primary btn-lg">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Pay <?= formatPrice($cart->getTotal()) ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="checkout-sidebar">
                <div class="card checkout-summary sticky top-24">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold">Order Summary</h2>
                        <button type="button" class="checkout-summary-toggle" aria-expanded="false">
                            <span><?= $cart->getCount() ?> items</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Cart Items -->
                        <div class="checkout-items">
                            <?php foreach ($items as $item): ?>
                            <div class="checkout-item">
                                <div class="checkout-item-image">
                                    <?php if ($item['image']): ?>
                                    <img src="<?= url('storage/uploads/' . e($item['image'])) ?>" alt="">
                                    <?php endif; ?>
                                    <span class="checkout-item-qty"><?= $item['quantity'] ?></span>
                                </div>
                                <div class="checkout-item-details">
                                    <p class="checkout-item-name"><?= e($item['name']) ?></p>
                                    <?php if (!empty($item['variant'])): ?>
                                    <p class="checkout-item-variant"><?= e($item['variant']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <p class="checkout-item-price"><?= formatPrice($item['price'] * $item['quantity']) ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Coupon Code -->
                        <div class="checkout-coupon">
                            <div class="checkout-coupon-form" id="coupon-form">
                                <input type="text" name="coupon_code" class="form-input" placeholder="Discount code">
                                <button type="button" class="btn btn-outline" id="apply-coupon-btn">Apply</button>
                            </div>
                            <?php if ($cart->getCouponCode()): ?>
                            <div class="checkout-coupon-applied">
                                <span class="checkout-coupon-tag">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    <?= e($cart->getCouponCode()) ?>
                                </span>
                                <button type="button" class="checkout-coupon-remove" id="remove-coupon-btn">&times;</button>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Totals -->
                        <div class="checkout-totals">
                            <div class="checkout-total-row">
                                <span>Subtotal</span>
                                <span id="checkout-subtotal"><?= formatPrice($cart->getSubtotal()) ?></span>
                            </div>
                            <?php if ($cart->getDiscount() > 0): ?>
                            <div class="checkout-total-row checkout-discount">
                                <span>Discount</span>
                                <span id="checkout-discount">-<?= formatPrice($cart->getDiscount()) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="checkout-total-row">
                                <span>Shipping</span>
                                <span id="checkout-shipping"><?= $cart->getShipping() > 0 ? formatPrice($cart->getShipping()) : 'Free' ?></span>
                            </div>
                            <div class="checkout-total-row checkout-total-final">
                                <span>Total</span>
                                <span id="checkout-total">
                                    <small class="checkout-currency">ZAR</small>
                                    <?= formatPrice($cart->getTotal()) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Security Badge -->
                        <div class="checkout-security">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <span>Secure checkout powered by Yoco</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Checkout CSS loaded via external file above -->

<?php if ($yocoPublicKey): ?>
<script src="https://js.yoco.com/sdk/v1/yoco-sdk-web.js"></script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const Checkout = {
        currentStep: 1,
        totalSteps: 3,
        form: document.getElementById('checkout-form'),
        payButton: document.getElementById('pay-button'),
        yoco: null,

        init() {
            this.bindEvents();
            this.initYoco();
            this.updateSummary();
        },

        bindEvents() {
            // Step navigation
            document.querySelectorAll('[data-next-step]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const nextStep = parseInt(btn.dataset.nextStep);
                    if (this.validateStep(this.currentStep)) {
                        this.goToStep(nextStep);
                    }
                });
            });

            document.querySelectorAll('[data-prev-step]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const prevStep = parseInt(btn.dataset.prevStep);
                    this.goToStep(prevStep);
                });
            });

            document.querySelectorAll('[data-goto-step]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const step = parseInt(btn.dataset.gotoStep);
                    this.goToStep(step);
                });
            });

            // Saved address selection
            document.querySelectorAll('.saved-address-option').forEach(option => {
                option.addEventListener('click', () => {
                    document.querySelectorAll('.saved-address-option').forEach(o => o.classList.remove('selected'));
                    option.classList.add('selected');
                    option.querySelector('input').checked = true;

                    const billingCard = document.getElementById('billing-address-card');
                    if (option.classList.contains('new-address-option')) {
                        billingCard.style.display = 'block';
                    } else {
                        billingCard.style.display = 'none';
                    }
                });
            });

            // Shipping method selection
            document.querySelectorAll('.shipping-method-option').forEach(option => {
                option.addEventListener('click', () => {
                    document.querySelectorAll('.shipping-method-option').forEach(o => o.classList.remove('selected'));
                    option.classList.add('selected');
                    option.querySelector('input').checked = true;
                    this.updateShipping();
                });
            });

            // Payment method selection
            document.querySelectorAll('.payment-method-option').forEach(option => {
                option.addEventListener('click', () => {
                    document.querySelectorAll('.payment-method-option').forEach(o => o.classList.remove('selected'));
                    option.classList.add('selected');
                    option.querySelector('input').checked = true;

                    const method = option.querySelector('input').value;
                    document.getElementById('card-payment-form').style.display = method === 'card' ? 'block' : 'none';
                    document.getElementById('eft-payment-info').style.display = method === 'eft' ? 'block' : 'none';
                });
            });

            // Same shipping checkbox
            const sameShipping = document.getElementById('same_shipping');
            const shippingCard = document.getElementById('shipping-address-card');
            if (sameShipping && shippingCard) {
                sameShipping.addEventListener('change', () => {
                    shippingCard.style.display = sameShipping.checked ? 'none' : 'block';
                });
            }

            // Create account checkbox
            const createAccount = document.querySelector('input[name="create_account"]');
            const passwordFields = document.getElementById('account-password-fields');
            if (createAccount && passwordFields) {
                createAccount.addEventListener('change', () => {
                    passwordFields.style.display = createAccount.checked ? 'block' : 'none';
                    const passwordInput = passwordFields.querySelector('input');
                    if (passwordInput) {
                        passwordInput.required = createAccount.checked;
                    }
                });
            }

            // Coupon form
            const applyCouponBtn = document.getElementById('apply-coupon-btn');
            if (applyCouponBtn) {
                applyCouponBtn.addEventListener('click', () => this.applyCoupon());
            }

            const removeCouponBtn = document.getElementById('remove-coupon-btn');
            if (removeCouponBtn) {
                removeCouponBtn.addEventListener('click', () => this.removeCoupon());
            }

            // Mobile summary toggle
            const summaryToggle = document.querySelector('.checkout-summary-toggle');
            if (summaryToggle) {
                summaryToggle.addEventListener('click', () => {
                    const expanded = summaryToggle.getAttribute('aria-expanded') === 'true';
                    summaryToggle.setAttribute('aria-expanded', !expanded);
                });
            }

            // Form submission
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        },

        initYoco() {
            <?php if ($yocoPublicKey): ?>
            this.yoco = new window.YocoSDK({
                publicKey: '<?= e($yocoPublicKey) ?>'
            });
            <?php endif; ?>
        },

        goToStep(step) {
            // Update progress
            document.querySelectorAll('.checkout-step').forEach(s => {
                const stepNum = parseInt(s.dataset.step);
                s.classList.remove('active', 'completed');
                if (stepNum < step) {
                    s.classList.add('completed');
                } else if (stepNum === step) {
                    s.classList.add('active');
                }
            });

            // Update content
            document.querySelectorAll('.checkout-step-content').forEach(c => {
                c.classList.remove('active');
                if (parseInt(c.dataset.step) === step) {
                    c.classList.add('active');
                }
            });

            this.currentStep = step;

            // Update review data when going to step 3
            if (step === 3) {
                this.updateReview();
            }

            // Update address summary when going to step 2
            if (step === 2) {
                this.updateAddressSummary();
            }

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        validateStep(step) {
            let valid = true;
            const content = document.querySelector(`.checkout-step-content[data-step="${step}"]`);

            // Clear previous errors
            content.querySelectorAll('.form-input.error, .form-select.error').forEach(el => {
                el.classList.remove('error');
            });
            content.querySelectorAll('.form-error').forEach(el => el.remove());

            // Get required fields based on step
            let fields = [];

            if (step === 1) {
                fields = ['email'];

                // Check if using saved address or new address
                const savedAddressSelected = document.querySelector('input[name="saved_address"]:checked');
                if (!savedAddressSelected || savedAddressSelected.value === 'new') {
                    fields.push('billing_first_name', 'billing_last_name', 'billing_address_1',
                               'billing_city', 'billing_province', 'billing_postal_code');
                }

                // Check different shipping address
                const sameShipping = document.getElementById('same_shipping');
                if (sameShipping && !sameShipping.checked) {
                    fields.push('shipping_first_name', 'shipping_last_name', 'shipping_address_1',
                               'shipping_city', 'shipping_province', 'shipping_postal_code');
                }

                // Check create account
                const createAccount = document.querySelector('input[name="create_account"]');
                if (createAccount && createAccount.checked) {
                    fields.push('password');
                }
            }

            fields.forEach(fieldName => {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (field && (!field.value || (field.type === 'email' && !this.isValidEmail(field.value)))) {
                    valid = false;
                    field.classList.add('error');

                    const error = document.createElement('p');
                    error.className = 'form-error';
                    error.textContent = field.type === 'email' ? 'Please enter a valid email address' : 'This field is required';
                    field.parentNode.appendChild(error);
                }
            });

            if (!valid) {
                const firstError = content.querySelector('.form-input.error, .form-select.error');
                if (firstError) {
                    firstError.focus();
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            return valid;
        },

        isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },

        updateAddressSummary() {
            const summary = document.getElementById('shipping-address-summary');
            if (!summary) return;

            // Check if using saved address
            const savedAddress = document.querySelector('input[name="saved_address"]:checked');
            if (savedAddress && savedAddress.value !== 'new') {
                const option = savedAddress.closest('.saved-address-option');
                summary.innerHTML = option.querySelector('.saved-address-content').innerHTML;
                return;
            }

            // Use billing address (or shipping if different)
            const sameShipping = document.getElementById('same_shipping');
            const prefix = (sameShipping && !sameShipping.checked) ? 'shipping' : 'billing';

            const firstName = document.getElementById(`${prefix}_first_name`)?.value || '';
            const lastName = document.getElementById(`${prefix}_last_name`)?.value || '';
            const address1 = document.getElementById(`${prefix}_address_1`)?.value || '';
            const address2 = document.getElementById(`${prefix}_address_2`)?.value || '';
            const city = document.getElementById(`${prefix}_city`)?.value || '';
            const province = document.getElementById(`${prefix}_province`)?.value || '';
            const postalCode = document.getElementById(`${prefix}_postal_code`)?.value || '';

            let html = `<p><strong>${firstName} ${lastName}</strong></p>`;
            html += `<p>${address1}</p>`;
            if (address2) html += `<p>${address2}</p>`;
            html += `<p>${city}, ${province} ${postalCode}</p>`;

            summary.innerHTML = html;
        },

        updateReview() {
            // Contact
            const contactEl = document.getElementById('review-contact');
            if (contactEl) {
                const email = document.getElementById('email')?.value || '';
                const phone = document.getElementById('phone')?.value || '';
                contactEl.textContent = email + (phone ? ` | ${phone}` : '');
            }

            // Shipping address
            const addressEl = document.getElementById('review-shipping-address');
            if (addressEl) {
                const summary = document.getElementById('shipping-address-summary');
                if (summary) {
                    addressEl.innerHTML = summary.innerHTML;
                }
            }

            // Shipping method
            const shippingEl = document.getElementById('review-shipping-method');
            if (shippingEl) {
                const selected = document.querySelector('input[name="shipping_method"]:checked');
                if (selected) {
                    const option = selected.closest('.shipping-method-option');
                    const name = option.querySelector('.shipping-method-name')?.textContent || '';
                    const price = option.querySelector('.shipping-method-price')?.textContent || '';
                    shippingEl.textContent = `${name} - ${price}`;
                }
            }
        },

        updateShipping() {
            const selected = document.querySelector('input[name="shipping_method"]:checked');
            if (!selected) return;

            const price = parseFloat(selected.dataset.price) || 0;
            const shippingEl = document.getElementById('checkout-shipping');

            if (shippingEl) {
                shippingEl.textContent = price > 0 ? this.formatPrice(price) : 'Free';
            }

            // Update delivery estimate
            const estimateEl = document.getElementById('delivery-estimate');
            if (estimateEl) {
                const option = selected.closest('.shipping-method-option');
                const time = option.querySelector('.shipping-method-time')?.textContent || '';
                estimateEl.textContent = time;
            }

            this.updateTotal();
        },

        updateTotal() {
            // This would typically fetch from server, but for now just update display
            // In production, call API to get accurate totals
        },

        updateSummary() {
            // Initial update
            this.updateShipping();
        },

        formatPrice(amount) {
            return 'R ' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        },

        async applyCoupon() {
            const input = document.querySelector('input[name="coupon_code"]');
            const code = input?.value.trim();

            if (!code) return;

            const btn = document.getElementById('apply-coupon-btn');
            btn.disabled = true;
            btn.textContent = 'Applying...';

            try {
                const formData = new FormData();
                formData.append('_token', '<?= csrfToken() ?>');
                formData.append('code', code);

                const response = await fetch('<?= url('/cart/apply-coupon') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    window.Pricetag?.Toast?.error(data.message || 'Invalid coupon code');
                }
            } catch (err) {
                window.Pricetag?.Toast?.error('Could not apply coupon');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Apply';
            }
        },

        async removeCoupon() {
            try {
                const formData = new FormData();
                formData.append('_token', '<?= csrfToken() ?>');
                formData.append('remove', '1');

                const response = await fetch('<?= url('/cart/apply-coupon') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                }
            } catch (err) {
                console.error('Remove coupon error:', err);
            }
        },

        async handleSubmit(e) {
            e.preventDefault();

            // Validate terms
            const terms = document.querySelector('input[name="terms"]');
            if (terms && !terms.checked) {
                window.Pricetag?.Toast?.error('Please agree to the terms and conditions');
                return;
            }

            this.payButton.disabled = true;
            this.payButton.classList.add('is-loading');

            try {
                // Create order first
                const formData = new FormData(this.form);
                const orderResponse = await fetch('<?= url('/checkout/process') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const orderData = await orderResponse.json();

                if (!orderData.success) {
                    throw new Error(orderData.message || 'Order creation failed');
                }

                // Process payment with Yoco
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;

                if (paymentMethod === 'card' && this.yoco) {
                    this.yoco.showPopup({
                        amountInCents: orderData.amount,
                        currency: 'ZAR',
                        name: '<?= e(config('app.name')) ?>',
                        description: 'Order #' + orderData.order_number,
                        callback: async (result) => {
                            if (result.error) {
                                window.Pricetag?.Toast?.error(result.error.message);
                                this.payButton.disabled = false;
                                this.payButton.classList.remove('is-loading');
                                return;
                            }

                            await this.processPayment(orderData.order_id, result.id);
                        }
                    });
                } else if (paymentMethod === 'eft') {
                    // Handle EFT payment
                    await this.processEftPayment(orderData.order_id);
                }
            } catch (err) {
                window.Pricetag?.Toast?.error(err.message || 'Something went wrong');
                this.payButton.disabled = false;
                this.payButton.classList.remove('is-loading');
            }
        },

        async processPayment(orderId, token) {
            try {
                const paymentFormData = new FormData();
                paymentFormData.append('_token', '<?= csrfToken() ?>');
                paymentFormData.append('order_id', orderId);
                paymentFormData.append('yoco_token', token);

                const response = await fetch('<?= url('/checkout/process-payment') ?>', {
                    method: 'POST',
                    body: paymentFormData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    window.Pricetag?.Toast?.error(data.message || 'Payment failed');
                    this.payButton.disabled = false;
                    this.payButton.classList.remove('is-loading');
                }
            } catch (err) {
                window.Pricetag?.Toast?.error('Payment processing failed');
                this.payButton.disabled = false;
                this.payButton.classList.remove('is-loading');
            }
        },

        async processEftPayment(orderId) {
            // EFT payment flow - redirect to bank
            window.Pricetag?.Toast?.info('Redirecting to your bank...');

            try {
                const formData = new FormData();
                formData.append('_token', '<?= csrfToken() ?>');
                formData.append('order_id', orderId);
                formData.append('method', 'eft');

                const response = await fetch('<?= url('/checkout/process-eft') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();

                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    throw new Error(data.message || 'EFT setup failed');
                }
            } catch (err) {
                window.Pricetag?.Toast?.error(err.message || 'Could not process EFT payment');
                this.payButton.disabled = false;
                this.payButton.classList.remove('is-loading');
            }
        }
    };

    Checkout.init();
});
</script>
