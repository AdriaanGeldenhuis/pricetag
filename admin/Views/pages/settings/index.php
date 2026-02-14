<!-- Admin Settings -->
<?php
$section = $_GET['section'] ?? 'general';
$sections = [
    'general' => 'General',
    'invoice' => 'Invoice & Company',
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

            <?php elseif ($section === 'invoice'): ?>
            <!-- Invoice & Company Settings -->
            <div class="card mb-6">
                <div class="card-header">
                    <h2 class="font-semibold">Company Details</h2>
                    <p class="text-sm text-muted mt-1">These details appear on your customer invoices, quotes, and credit notes.</p>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="invoice_company_name" class="form-label">Company / Trading Name</label>
                        <input type="text" id="invoice_company_name" name="invoice_company_name"
                               value="<?= e($settings['invoice_company_name'] ?? $settings['site_name'] ?? 'Pricetag') ?>" class="form-input"
                               placeholder="Your company name as it appears on invoices">
                        <p class="form-help">Leave blank to use your site name</p>
                    </div>

                    <div class="form-group">
                        <label for="invoice_address" class="form-label">Business Address</label>
                        <textarea id="invoice_address" name="invoice_address" rows="3"
                                  class="form-input" placeholder="123 Main Street&#10;Johannesburg, Gauteng&#10;2000"><?= e($settings['invoice_address'] ?? $settings['site_address'] ?? '') ?></textarea>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="invoice_email" class="form-label">Invoice Email</label>
                            <input type="email" id="invoice_email" name="invoice_email"
                                   value="<?= e($settings['invoice_email'] ?? $settings['site_email'] ?? '') ?>" class="form-input"
                                   placeholder="accounts@yourcompany.co.za">
                        </div>

                        <div class="form-group">
                            <label for="invoice_phone" class="form-label">Invoice Phone</label>
                            <input type="text" id="invoice_phone" name="invoice_phone"
                                   value="<?= e($settings['invoice_phone'] ?? $settings['site_phone'] ?? '') ?>" class="form-input"
                                   placeholder="+27 11 123 4567">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="invoice_website" class="form-label">Website URL</label>
                        <input type="url" id="invoice_website" name="invoice_website"
                               value="<?= e($settings['invoice_website'] ?? '') ?>" class="form-input"
                               placeholder="https://yourcompany.co.za">
                    </div>
                </div>
            </div>

            <div class="card mb-6">
                <div class="card-header">
                    <h2 class="font-semibold">Tax & Registration</h2>
                    <p class="text-sm text-muted mt-1">Legal details displayed on invoices for compliance.</p>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="invoice_vat_number" class="form-label">VAT Number</label>
                            <input type="text" id="invoice_vat_number" name="invoice_vat_number"
                                   value="<?= e($settings['invoice_vat_number'] ?? '') ?>" class="form-input"
                                   placeholder="e.g. 4123456789">
                            <p class="form-help">Your SARS VAT registration number</p>
                        </div>

                        <div class="form-group">
                            <label for="invoice_reg_number" class="form-label">Company Registration Number</label>
                            <input type="text" id="invoice_reg_number" name="invoice_reg_number"
                                   value="<?= e($settings['invoice_reg_number'] ?? '') ?>" class="form-input"
                                   placeholder="e.g. 2024/123456/07">
                            <p class="form-help">CIPC registration number</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-6">
                <div class="card-header">
                    <h2 class="font-semibold">Banking Details (shown on invoices)</h2>
                    <p class="text-sm text-muted mt-1">For customers paying via EFT / bank transfer.</p>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="invoice_bank_name" class="form-label">Bank Name</label>
                            <input type="text" id="invoice_bank_name" name="invoice_bank_name"
                                   value="<?= e($settings['invoice_bank_name'] ?? $settings['eft_bank_name'] ?? '') ?>" class="form-input"
                                   placeholder="e.g. FNB, Standard Bank, Nedbank">
                        </div>

                        <div class="form-group">
                            <label for="invoice_bank_account" class="form-label">Account Number</label>
                            <input type="text" id="invoice_bank_account" name="invoice_bank_account"
                                   value="<?= e($settings['invoice_bank_account'] ?? $settings['eft_account_number'] ?? '') ?>" class="form-input"
                                   placeholder="e.g. 62000000000">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="invoice_bank_branch" class="form-label">Branch Code</label>
                            <input type="text" id="invoice_bank_branch" name="invoice_bank_branch"
                                   value="<?= e($settings['invoice_bank_branch'] ?? $settings['eft_branch_code'] ?? '') ?>" class="form-input"
                                   placeholder="e.g. 250655">
                        </div>

                        <div class="form-group">
                            <label for="invoice_bank_type" class="form-label">Account Type</label>
                            <select id="invoice_bank_type" name="invoice_bank_type" class="form-select">
                                <option value="" <?= empty($settings['invoice_bank_type'] ?? '') ? 'selected' : '' ?>>Select account type</option>
                                <option value="Cheque/Current" <?= ($settings['invoice_bank_type'] ?? '') === 'Cheque/Current' ? 'selected' : '' ?>>Cheque / Current</option>
                                <option value="Savings" <?= ($settings['invoice_bank_type'] ?? '') === 'Savings' ? 'selected' : '' ?>>Savings</option>
                                <option value="Transmission" <?= ($settings['invoice_bank_type'] ?? '') === 'Transmission' ? 'selected' : '' ?>>Transmission</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Invoice Options</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="invoice_payment_terms" class="form-label">Default Payment Terms</label>
                        <select id="invoice_payment_terms" name="invoice_payment_terms" class="form-select">
                            <option value="due_on_receipt" <?= ($settings['invoice_payment_terms'] ?? 'due_on_receipt') === 'due_on_receipt' ? 'selected' : '' ?>>Due on Receipt</option>
                            <option value="net_7" <?= ($settings['invoice_payment_terms'] ?? '') === 'net_7' ? 'selected' : '' ?>>Net 7 (7 days)</option>
                            <option value="net_14" <?= ($settings['invoice_payment_terms'] ?? '') === 'net_14' ? 'selected' : '' ?>>Net 14 (14 days)</option>
                            <option value="net_30" <?= ($settings['invoice_payment_terms'] ?? '') === 'net_30' ? 'selected' : '' ?>>Net 30 (30 days)</option>
                            <option value="net_60" <?= ($settings['invoice_payment_terms'] ?? '') === 'net_60' ? 'selected' : '' ?>>Net 60 (60 days)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="invoice_notes" class="form-label">Default Invoice Notes</label>
                        <textarea id="invoice_notes" name="invoice_notes" rows="3"
                                  class="form-input" placeholder="Thank you for your business!"><?= e($settings['invoice_notes'] ?? '') ?></textarea>
                        <p class="form-help">Shown at the bottom of every invoice</p>
                    </div>

                    <div class="form-group">
                        <label for="invoice_footer_text" class="form-label">Custom Footer Text</label>
                        <textarea id="invoice_footer_text" name="invoice_footer_text" rows="2"
                                  class="form-input" placeholder="This document is computer generated and is valid without signature."><?= e($settings['invoice_footer_text'] ?? '') ?></textarea>
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
