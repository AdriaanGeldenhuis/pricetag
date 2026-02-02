<!-- Admin Settings -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle">Manage your store configuration</p>
    </div>
</div>

<form method="POST" action="<?= url('/admin/settings') ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <!-- Tabs -->
    <div class="admin-tabs">
        <button type="button" class="admin-tab active" data-tab="general">General</button>
        <button type="button" class="admin-tab" data-tab="store">Store</button>
        <button type="button" class="admin-tab" data-tab="shipping">Shipping</button>
        <button type="button" class="admin-tab" data-tab="payment">Payment</button>
        <button type="button" class="admin-tab" data-tab="email">Email</button>
        <button type="button" class="admin-tab" data-tab="social">Social</button>
    </div>

    <!-- General Settings -->
    <div class="admin-tab-content active" id="tab-general">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">General Settings</h3>
            </div>
            <div class="admin-card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Site Name</label>
                            <input type="text" name="site_name" value="<?= e($settings['general']['site_name']) ?>"
                                   class="admin-form-input">
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Site Tagline</label>
                            <input type="text" name="site_tagline" value="<?= e($settings['general']['site_tagline']) ?>"
                                   class="admin-form-input" placeholder="Your store tagline...">
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Site Description</label>
                            <textarea name="site_description" class="admin-form-textarea" rows="3"
                                      placeholder="Brief description for SEO..."><?= e($settings['general']['site_description']) ?></textarea>
                        </div>
                    </div>
                    <div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Logo</label>
                            <div class="admin-image-upload" onclick="document.getElementById('logoInput').click()" style="padding: 1rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 32px; height: 32px; margin: 0 auto 0.5rem;">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <p class="admin-image-upload-text" style="margin: 0;">Click to upload logo</p>
                            </div>
                            <input type="file" id="logoInput" name="site_logo" accept="image/*" style="display: none;">
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Favicon</label>
                            <div class="admin-image-upload" onclick="document.getElementById('faviconInput').click()" style="padding: 1rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 32px; height: 32px; margin: 0 auto 0.5rem;">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                </svg>
                                <p class="admin-image-upload-text" style="margin: 0;">Click to upload favicon</p>
                            </div>
                            <input type="file" id="faviconInput" name="site_favicon" accept=".ico,.png,.svg" style="display: none;">
                        </div>
                    </div>
                </div>

                <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid var(--admin-border);">

                <h4 style="margin-bottom: 1rem; font-size: 1rem;">Contact Information</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Contact Email</label>
                        <input type="email" name="contact_email" value="<?= e($settings['general']['contact_email']) ?>"
                               class="admin-form-input" placeholder="support@example.com">
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Contact Phone</label>
                        <input type="text" name="contact_phone" value="<?= e($settings['general']['contact_phone']) ?>"
                               class="admin-form-input" placeholder="+27 12 345 6789">
                    </div>
                </div>
                <div class="admin-form-group" style="margin-bottom: 0;">
                    <label class="admin-form-label">Address</label>
                    <textarea name="contact_address" class="admin-form-textarea" rows="2"
                              placeholder="Physical address..."><?= e($settings['general']['contact_address']) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Settings -->
    <div class="admin-tab-content" id="tab-store">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Store Settings</h3>
            </div>
            <div class="admin-card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Currency</label>
                        <select name="currency" class="admin-form-select">
                            <option value="ZAR" <?= $settings['store']['currency'] === 'ZAR' ? 'selected' : '' ?>>South African Rand (ZAR)</option>
                            <option value="USD" <?= $settings['store']['currency'] === 'USD' ? 'selected' : '' ?>>US Dollar (USD)</option>
                            <option value="EUR" <?= $settings['store']['currency'] === 'EUR' ? 'selected' : '' ?>>Euro (EUR)</option>
                            <option value="GBP" <?= $settings['store']['currency'] === 'GBP' ? 'selected' : '' ?>>British Pound (GBP)</option>
                        </select>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Currency Symbol</label>
                        <input type="text" name="currency_symbol" value="<?= e($settings['store']['currency_symbol']) ?>"
                               class="admin-form-input" placeholder="R">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Tax Rate (%)</label>
                        <input type="number" name="tax_rate" step="0.01" min="0" max="100"
                               value="<?= e($settings['store']['tax_rate']) ?>"
                               class="admin-form-input">
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Tax Display</label>
                        <select name="tax_included" class="admin-form-select">
                            <option value="1" <?= $settings['store']['tax_included'] === '1' ? 'selected' : '' ?>>Prices include tax</option>
                            <option value="0" <?= $settings['store']['tax_included'] === '0' ? 'selected' : '' ?>>Prices exclude tax</option>
                        </select>
                    </div>
                </div>

                <div class="admin-form-group" style="margin-bottom: 0;">
                    <label class="admin-form-label">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" min="0"
                           value="<?= e($settings['store']['low_stock_threshold']) ?>"
                           class="admin-form-input" style="max-width: 200px;">
                    <p class="admin-form-hint">Products with stock below this will show low stock warning</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Shipping Settings -->
    <div class="admin-tab-content" id="tab-shipping">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Shipping Settings</h3>
            </div>
            <div class="admin-card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Standard Shipping Rate (ZAR)</label>
                        <input type="number" name="shipping_default_rate" step="0.01" min="0"
                               value="<?= e($settings['shipping']['shipping_default_rate']) ?>"
                               class="admin-form-input">
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Express Shipping Rate (ZAR)</label>
                        <input type="number" name="shipping_express_rate" step="0.01" min="0"
                               value="<?= e($settings['shipping']['shipping_express_rate']) ?>"
                               class="admin-form-input">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Free Shipping Threshold (ZAR)</label>
                        <input type="number" name="shipping_free_threshold" step="0.01" min="0"
                               value="<?= e($settings['shipping']['shipping_free_threshold']) ?>"
                               class="admin-form-input">
                        <p class="admin-form-hint">Orders above this amount get free standard shipping</p>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Estimated Delivery Days</label>
                        <input type="text" name="shipping_estimate_days"
                               value="<?= e($settings['shipping']['shipping_estimate_days']) ?>"
                               class="admin-form-input" placeholder="e.g., 3-5">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Settings -->
    <div class="admin-tab-content" id="tab-payment">
        <div class="admin-card" style="margin-bottom: 1.5rem;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Yoco Payments</h3>
            </div>
            <div class="admin-card-body">
                <div class="admin-form-group" style="margin-bottom: 0;">
                    <label class="admin-checkbox-group">
                        <input type="checkbox" name="yoco_enabled" value="1" class="admin-checkbox"
                            <?= $settings['payment']['yoco_enabled'] === '1' ? 'checked' : '' ?>>
                        <span>Enable Yoco card payments</span>
                    </label>
                    <p class="admin-form-hint">Configure Yoco API keys in your .env file</p>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">EFT / Bank Transfer</h3>
            </div>
            <div class="admin-card-body">
                <div class="admin-form-group">
                    <label class="admin-checkbox-group">
                        <input type="checkbox" name="eft_enabled" value="1" class="admin-checkbox"
                            <?= $settings['payment']['eft_enabled'] === '1' ? 'checked' : '' ?>>
                        <span>Enable EFT bank transfer</span>
                    </label>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Bank Name</label>
                        <input type="text" name="eft_bank_name" value="<?= e($settings['payment']['eft_bank_name']) ?>"
                               class="admin-form-input" placeholder="e.g., FNB">
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Account Name</label>
                        <input type="text" name="eft_account_name" value="<?= e($settings['payment']['eft_account_name']) ?>"
                               class="admin-form-input" placeholder="Your business name">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Account Number</label>
                        <input type="text" name="eft_account_number" value="<?= e($settings['payment']['eft_account_number']) ?>"
                               class="admin-form-input">
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Branch Code</label>
                        <input type="text" name="eft_branch_code" value="<?= e($settings['payment']['eft_branch_code']) ?>"
                               class="admin-form-input">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Settings -->
    <div class="admin-tab-content" id="tab-email">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Email Settings</h3>
            </div>
            <div class="admin-card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">From Name</label>
                        <input type="text" name="mail_from_name" value="<?= e($settings['email']['mail_from_name']) ?>"
                               class="admin-form-input" placeholder="Your Store Name">
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">From Email</label>
                        <input type="email" name="mail_from_address" value="<?= e($settings['email']['mail_from_address']) ?>"
                               class="admin-form-input" placeholder="noreply@example.com">
                    </div>
                </div>
                <div class="admin-form-group" style="margin-bottom: 0;">
                    <label class="admin-form-label">Order Notification Email</label>
                    <input type="email" name="order_notification_email" value="<?= e($settings['email']['order_notification_email']) ?>"
                           class="admin-form-input" placeholder="orders@example.com">
                    <p class="admin-form-hint">Receive email notifications for new orders</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Social Settings -->
    <div class="admin-tab-content" id="tab-social">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Social Media Links</h3>
            </div>
            <div class="admin-card-body">
                <div class="admin-form-group">
                    <label class="admin-form-label">Facebook URL</label>
                    <input type="url" name="facebook_url" value="<?= e($settings['social']['facebook_url']) ?>"
                           class="admin-form-input" placeholder="https://facebook.com/yourpage">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Instagram URL</label>
                    <input type="url" name="instagram_url" value="<?= e($settings['social']['instagram_url']) ?>"
                           class="admin-form-input" placeholder="https://instagram.com/yourpage">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Twitter/X URL</label>
                    <input type="url" name="twitter_url" value="<?= e($settings['social']['twitter_url']) ?>"
                           class="admin-form-input" placeholder="https://twitter.com/yourpage">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">YouTube URL</label>
                    <input type="url" name="youtube_url" value="<?= e($settings['social']['youtube_url']) ?>"
                           class="admin-form-input" placeholder="https://youtube.com/@yourchannel">
                </div>
                <div class="admin-form-group" style="margin-bottom: 0;">
                    <label class="admin-form-label">TikTok URL</label>
                    <input type="url" name="tiktok_url" value="<?= e($settings['social']['tiktok_url']) ?>"
                           class="admin-form-input" placeholder="https://tiktok.com/@yourpage">
                </div>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
        <button type="submit" class="admin-btn admin-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Save Settings
        </button>
    </div>
</form>

<script>
document.querySelectorAll('.admin-tab').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();

        // Remove active from all tabs and contents
        document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.admin-tab-content').forEach(c => c.classList.remove('active'));

        // Add active to clicked tab
        this.classList.add('active');

        // Show corresponding content
        const tabId = this.dataset.tab;
        document.getElementById('tab-' + tabId).classList.add('active');
    });
});
</script>
