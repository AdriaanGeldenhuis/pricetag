<!-- Admin Settings -->
<?php
$section = $_GET['section'] ?? 'general';
$sections = [
    'general' => 'General',
    'store' => 'Store',
    'payment' => 'Payment',
    'social' => 'Social Media',
    'seo' => 'SEO & Analytics',
    'email' => 'Email',
];
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="admin-page-title mb-0">Settings</h1>
</div>

<div class="grid lg:grid-cols-4 gap-6">
    <!-- Sidebar Navigation -->
    <div class="space-y-1">
        <?php foreach ($sections as $key => $label): ?>
        <a href="<?= url('/admin/settings?section=' . $key) ?>"
           class="block px-4 py-2 rounded <?= $section === $key ? 'bg-primary text-white' : 'hover:bg-neutral-100' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Settings Form -->
    <div class="lg:col-span-3">
        <form action="<?= url('/admin/settings') ?>" method="POST" class="admin-form">
            <?= csrf_field() ?>
            <input type="hidden" name="section" value="<?= $section ?>">

            <?php if ($section === 'general'): ?>
            <!-- General Settings -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">General Settings</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="site_name" class="form-label">Site Name</label>
                        <input type="text" id="site_name" name="site_name"
                               value="<?= e($settings['site_name'] ?? 'Pricetag') ?>" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="site_tagline" class="form-label">Tagline</label>
                        <input type="text" id="site_tagline" name="site_tagline"
                               value="<?= e($settings['site_tagline'] ?? '') ?>" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="site_email" class="form-label">Contact Email</label>
                        <input type="email" id="site_email" name="site_email"
                               value="<?= e($settings['site_email'] ?? '') ?>" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="site_phone" class="form-label">Phone Number</label>
                        <input type="text" id="site_phone" name="site_phone"
                               value="<?= e($settings['site_phone'] ?? '') ?>" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="site_address" class="form-label">Business Address</label>
                        <textarea id="site_address" name="site_address" rows="3"
                                  class="form-input"><?= e($settings['site_address'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <?php elseif ($section === 'store'): ?>
            <!-- Store Settings -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Store Settings</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="currency_code" class="form-label">Currency Code</label>
                            <input type="text" id="currency_code" name="currency_code"
                                   value="<?= e($settings['currency_code'] ?? 'ZAR') ?>" class="form-input" maxlength="3">
                        </div>

                        <div class="form-group">
                            <label for="currency_symbol" class="form-label">Currency Symbol</label>
                            <input type="text" id="currency_symbol" name="currency_symbol"
                                   value="<?= e($settings['currency_symbol'] ?? 'R') ?>" class="form-input" maxlength="5">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="tax_rate" class="form-label">VAT Rate (%)</label>
                            <input type="number" id="tax_rate" name="tax_rate" step="0.01"
                                   value="<?= e($settings['tax_rate'] ?? '15') ?>" class="form-input">
                        </div>

                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="tax_included" value="1"
                                       <?= !empty($settings['tax_included']) ? 'checked' : '' ?> class="form-checkbox">
                                <span>Prices include VAT</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="free_shipping_threshold" class="form-label">Free Shipping Threshold (R)</label>
                            <input type="number" id="free_shipping_threshold" name="free_shipping_threshold" step="0.01"
                                   value="<?= e($settings['free_shipping_threshold'] ?? '500') ?>" class="form-input">
                            <p class="form-help">Set to 0 to disable free shipping</p>
                        </div>

                        <div class="form-group">
                            <label for="default_shipping_cost" class="form-label">Default Shipping Cost (R)</label>
                            <input type="number" id="default_shipping_cost" name="default_shipping_cost" step="0.01"
                                   value="<?= e($settings['default_shipping_cost'] ?? '50') ?>" class="form-input">
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif ($section === 'payment'): ?>
            <!-- Payment Settings -->
            <div class="card mb-6">
                <div class="card-header">
                    <h2 class="font-semibold">Yoco Payment Gateway</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="yoco_public_key" class="form-label">Public Key</label>
                        <input type="text" id="yoco_public_key" name="yoco_public_key"
                               value="<?= e($settings['yoco_public_key'] ?? '') ?>" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="yoco_secret_key" class="form-label">Secret Key</label>
                        <input type="password" id="yoco_secret_key" name="yoco_secret_key"
                               value="<?= e($settings['yoco_secret_key'] ?? '') ?>" class="form-input">
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="yoco_test_mode" value="1"
                               <?= !empty($settings['yoco_test_mode']) ? 'checked' : '' ?> class="form-checkbox">
                        <span>Test Mode</span>
                    </label>
                </div>
            </div>

            <div class="card mb-6">
                <div class="card-header">
                    <h2 class="font-semibold">Cash on Delivery</h2>
                </div>
                <div class="card-body">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="cod_enabled" value="1"
                               <?= !empty($settings['cod_enabled']) ? 'checked' : '' ?> class="form-checkbox">
                        <span>Enable Cash on Delivery</span>
                    </label>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">EFT/Bank Transfer</h2>
                </div>
                <div class="card-body space-y-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="eft_enabled" value="1"
                               <?= !empty($settings['eft_enabled']) ? 'checked' : '' ?> class="form-checkbox">
                        <span>Enable EFT/Bank Transfer</span>
                    </label>

                    <div class="form-group">
                        <label for="eft_bank_name" class="form-label">Bank Name</label>
                        <input type="text" id="eft_bank_name" name="eft_bank_name"
                               value="<?= e($settings['eft_bank_name'] ?? '') ?>" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="eft_account_name" class="form-label">Account Name</label>
                        <input type="text" id="eft_account_name" name="eft_account_name"
                               value="<?= e($settings['eft_account_name'] ?? '') ?>" class="form-input">
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="eft_account_number" class="form-label">Account Number</label>
                            <input type="text" id="eft_account_number" name="eft_account_number"
                                   value="<?= e($settings['eft_account_number'] ?? '') ?>" class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="eft_branch_code" class="form-label">Branch Code</label>
                            <input type="text" id="eft_branch_code" name="eft_branch_code"
                                   value="<?= e($settings['eft_branch_code'] ?? '') ?>" class="form-input">
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif ($section === 'social'): ?>
            <!-- Social Media Settings -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Social Media Links</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="social_facebook" class="form-label">Facebook URL</label>
                        <input type="url" id="social_facebook" name="social_facebook"
                               value="<?= e($settings['social_facebook'] ?? '') ?>" class="form-input"
                               placeholder="https://facebook.com/yourpage">
                    </div>

                    <div class="form-group">
                        <label for="social_instagram" class="form-label">Instagram URL</label>
                        <input type="url" id="social_instagram" name="social_instagram"
                               value="<?= e($settings['social_instagram'] ?? '') ?>" class="form-input"
                               placeholder="https://instagram.com/yourpage">
                    </div>

                    <div class="form-group">
                        <label for="social_twitter" class="form-label">Twitter/X URL</label>
                        <input type="url" id="social_twitter" name="social_twitter"
                               value="<?= e($settings['social_twitter'] ?? '') ?>" class="form-input"
                               placeholder="https://twitter.com/yourhandle">
                    </div>

                    <div class="form-group">
                        <label for="social_linkedin" class="form-label">LinkedIn URL</label>
                        <input type="url" id="social_linkedin" name="social_linkedin"
                               value="<?= e($settings['social_linkedin'] ?? '') ?>" class="form-input"
                               placeholder="https://linkedin.com/company/yourcompany">
                    </div>

                    <div class="form-group">
                        <label for="social_youtube" class="form-label">YouTube URL</label>
                        <input type="url" id="social_youtube" name="social_youtube"
                               value="<?= e($settings['social_youtube'] ?? '') ?>" class="form-input"
                               placeholder="https://youtube.com/@yourchannel">
                    </div>
                </div>
            </div>

            <?php elseif ($section === 'seo'): ?>
            <!-- SEO Settings -->
            <div class="card mb-6">
                <div class="card-header">
                    <h2 class="font-semibold">Default SEO</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="meta_title" class="form-label">Default Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title"
                               value="<?= e($settings['meta_title'] ?? '') ?>" class="form-input" maxlength="70">
                    </div>

                    <div class="form-group">
                        <label for="meta_description" class="form-label">Default Meta Description</label>
                        <textarea id="meta_description" name="meta_description" rows="3"
                                  class="form-input" maxlength="160"><?= e($settings['meta_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Analytics & Tracking</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="google_analytics" class="form-label">Google Analytics ID</label>
                        <input type="text" id="google_analytics" name="google_analytics"
                               value="<?= e($settings['google_analytics'] ?? '') ?>" class="form-input"
                               placeholder="G-XXXXXXXXXX">
                    </div>

                    <div class="form-group">
                        <label for="google_tag_manager" class="form-label">Google Tag Manager ID</label>
                        <input type="text" id="google_tag_manager" name="google_tag_manager"
                               value="<?= e($settings['google_tag_manager'] ?? '') ?>" class="form-input"
                               placeholder="GTM-XXXXXXX">
                    </div>

                    <div class="form-group">
                        <label for="facebook_pixel" class="form-label">Facebook Pixel ID</label>
                        <input type="text" id="facebook_pixel" name="facebook_pixel"
                               value="<?= e($settings['facebook_pixel'] ?? '') ?>" class="form-input"
                               placeholder="XXXXXXXXXXXXXXX">
                    </div>
                </div>
            </div>

            <?php elseif ($section === 'email'): ?>
            <!-- Email Settings -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">SMTP Configuration</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="smtp_host" class="form-label">SMTP Host</label>
                            <input type="text" id="smtp_host" name="smtp_host"
                                   value="<?= e($settings['smtp_host'] ?? '') ?>" class="form-input"
                                   placeholder="smtp.example.com">
                        </div>

                        <div class="form-group">
                            <label for="smtp_port" class="form-label">SMTP Port</label>
                            <input type="number" id="smtp_port" name="smtp_port"
                                   value="<?= e($settings['smtp_port'] ?? '587') ?>" class="form-input">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="smtp_username" class="form-label">SMTP Username</label>
                            <input type="text" id="smtp_username" name="smtp_username"
                                   value="<?= e($settings['smtp_username'] ?? '') ?>" class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="smtp_password" class="form-label">SMTP Password</label>
                            <input type="password" id="smtp_password" name="smtp_password"
                                   value="<?= e($settings['smtp_password'] ?? '') ?>" class="form-input">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="smtp_encryption" class="form-label">Encryption</label>
                        <select id="smtp_encryption" name="smtp_encryption" class="form-select">
                            <option value="tls" <?= ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            <option value="" <?= empty($settings['smtp_encryption']) ? 'selected' : '' ?>>None</option>
                        </select>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="mail_from_address" class="form-label">From Address</label>
                            <input type="email" id="mail_from_address" name="mail_from_address"
                                   value="<?= e($settings['mail_from_address'] ?? '') ?>" class="form-input"
                                   placeholder="noreply@example.com">
                        </div>

                        <div class="form-group">
                            <label for="mail_from_name" class="form-label">From Name</label>
                            <input type="text" id="mail_from_name" name="mail_from_name"
                                   value="<?= e($settings['mail_from_name'] ?? '') ?>" class="form-input"
                                   placeholder="Pricetag">
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="mt-6">
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
</div>
