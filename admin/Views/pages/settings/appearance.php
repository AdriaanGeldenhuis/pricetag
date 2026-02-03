<!-- Admin Appearance Settings -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="admin-page-title mb-0">Appearance Settings</h1>
        <p class="text-muted mt-1">Customize your store's look and feel</p>
    </div>
</div>

<form action="<?= url('/admin/appearance') ?>" method="POST" class="admin-form">
    <?= csrf_field() ?>

    <!-- Announcement Bar -->
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-semibold">Announcement Bar</h2>
            <p class="text-muted text-sm">Display a promotional message at the top of your site</p>
        </div>
        <div class="card-body space-y-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="announcement_enabled" value="1"
                       <?= !empty($settings['announcement_enabled']) ? 'checked' : '' ?> class="form-checkbox">
                <span>Enable Announcement Bar</span>
            </label>

            <div class="form-group">
                <label for="announcement_text" class="form-label">Announcement Text</label>
                <input type="text" id="announcement_text" name="announcement_text"
                       value="<?= e($settings['announcement_text'] ?? '') ?>" class="form-input"
                       placeholder="Free shipping on orders over R500!">
            </div>

            <div class="form-group">
                <label for="announcement_link" class="form-label">Link URL (Optional)</label>
                <input type="text" id="announcement_link" name="announcement_link"
                       value="<?= e($settings['announcement_link'] ?? '') ?>" class="form-input"
                       placeholder="/products?on_sale=1">
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label for="announcement_bg_color" class="form-label">Background Color</label>
                    <div class="color-picker-wrap">
                        <input type="color" id="announcement_bg_color" name="announcement_bg_color"
                               value="<?= e($settings['announcement_bg_color'] ?? '#1e40af') ?>" class="color-input">
                        <input type="text" value="<?= e($settings['announcement_bg_color'] ?? '#1e40af') ?>"
                               class="form-input color-hex" maxlength="7" id="announcement_bg_color_hex">
                    </div>
                </div>

                <div class="form-group">
                    <label for="announcement_text_color" class="form-label">Text Color</label>
                    <div class="color-picker-wrap">
                        <input type="color" id="announcement_text_color" name="announcement_text_color"
                               value="<?= e($settings['announcement_text_color'] ?? '#ffffff') ?>" class="color-input">
                        <input type="text" value="<?= e($settings['announcement_text_color'] ?? '#ffffff') ?>"
                               class="form-input color-hex" maxlength="7" id="announcement_text_color_hex">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating WhatsApp Widget -->
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-semibold">WhatsApp Chat Button</h2>
            <p class="text-muted text-sm">Add a floating WhatsApp button for quick customer support</p>
        </div>
        <div class="card-body space-y-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="whatsapp_enabled" value="1"
                       <?= !empty($settings['whatsapp_enabled']) ? 'checked' : '' ?> class="form-checkbox">
                <span>Enable WhatsApp Button</span>
            </label>

            <div class="form-group">
                <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                <input type="text" id="whatsapp_number" name="whatsapp_number"
                       value="<?= e($settings['whatsapp_number'] ?? '') ?>" class="form-input"
                       placeholder="27123456789 (without + or spaces)">
                <p class="form-help">Enter number in international format without + or spaces</p>
            </div>

            <div class="form-group">
                <label for="whatsapp_message" class="form-label">Pre-filled Message</label>
                <input type="text" id="whatsapp_message" name="whatsapp_message"
                       value="<?= e($settings['whatsapp_message'] ?? 'Hi! I have a question about your products.') ?>" class="form-input">
            </div>

            <div class="form-group">
                <label for="whatsapp_position" class="form-label">Button Position</label>
                <select id="whatsapp_position" name="whatsapp_position" class="form-select">
                    <option value="bottom-right" <?= ($settings['whatsapp_position'] ?? 'bottom-right') === 'bottom-right' ? 'selected' : '' ?>>Bottom Right</option>
                    <option value="bottom-left" <?= ($settings['whatsapp_position'] ?? '') === 'bottom-left' ? 'selected' : '' ?>>Bottom Left</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Trust Badges -->
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-semibold">Trust Badges</h2>
            <p class="text-muted text-sm">Customize the trust indicators shown below the hero section</p>
        </div>
        <div class="card-body space-y-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="trust_badges_enabled" value="1"
                       <?= ($settings['trust_badges_enabled'] ?? '1') ? 'checked' : '' ?> class="form-checkbox">
                <span>Show Trust Badges</span>
            </label>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Badge 1</label>
                    <input type="text" name="trust_badge_1_title"
                           value="<?= e($settings['trust_badge_1_title'] ?? 'Free Delivery') ?>" class="form-input mb-2" placeholder="Title">
                    <input type="text" name="trust_badge_1_subtitle"
                           value="<?= e($settings['trust_badge_1_subtitle'] ?? 'Orders over R500') ?>" class="form-input" placeholder="Subtitle">
                </div>

                <div class="form-group">
                    <label class="form-label">Badge 2</label>
                    <input type="text" name="trust_badge_2_title"
                           value="<?= e($settings['trust_badge_2_title'] ?? 'Secure Payment') ?>" class="form-input mb-2" placeholder="Title">
                    <input type="text" name="trust_badge_2_subtitle"
                           value="<?= e($settings['trust_badge_2_subtitle'] ?? '100% Protected') ?>" class="form-input" placeholder="Subtitle">
                </div>

                <div class="form-group">
                    <label class="form-label">Badge 3</label>
                    <input type="text" name="trust_badge_3_title"
                           value="<?= e($settings['trust_badge_3_title'] ?? 'Quality Products') ?>" class="form-input mb-2" placeholder="Title">
                    <input type="text" name="trust_badge_3_subtitle"
                           value="<?= e($settings['trust_badge_3_subtitle'] ?? 'Best brands only') ?>" class="form-input" placeholder="Subtitle">
                </div>

                <div class="form-group">
                    <label class="form-label">Badge 4</label>
                    <input type="text" name="trust_badge_4_title"
                           value="<?= e($settings['trust_badge_4_title'] ?? '24/7 Support') ?>" class="form-input mb-2" placeholder="Title">
                    <input type="text" name="trust_badge_4_subtitle"
                           value="<?= e($settings['trust_badge_4_subtitle'] ?? 'Always here to help') ?>" class="form-input" placeholder="Subtitle">
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Settings -->
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-semibold">Footer Content</h2>
            <p class="text-muted text-sm">Customize footer description and contact details</p>
        </div>
        <div class="card-body space-y-4">
            <div class="form-group">
                <label for="footer_description" class="form-label">Footer Description</label>
                <textarea id="footer_description" name="footer_description" rows="3" class="form-input"
                          placeholder="Your trusted destination for premium products..."><?= e($settings['footer_description'] ?? 'Your trusted destination for premium products at competitive prices. Fast delivery across South Africa.') ?></textarea>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label for="footer_email" class="form-label">Contact Email</label>
                    <input type="email" id="footer_email" name="footer_email"
                           value="<?= e($settings['footer_email'] ?? 'info@pricetag.co.za') ?>" class="form-input">
                </div>

                <div class="form-group">
                    <label for="footer_phone" class="form-label">Contact Phone</label>
                    <input type="text" id="footer_phone" name="footer_phone"
                           value="<?= e($settings['footer_phone'] ?? '+27 (0) 10 000 0000') ?>" class="form-input">
                </div>
            </div>

            <div class="form-group">
                <label for="footer_hours" class="form-label">Business Hours</label>
                <input type="text" id="footer_hours" name="footer_hours"
                       value="<?= e($settings['footer_hours'] ?? 'Mon - Fri: 8am - 5pm') ?>" class="form-input">
            </div>

            <div class="form-group">
                <label for="footer_copyright" class="form-label">Copyright Text</label>
                <input type="text" id="footer_copyright" name="footer_copyright"
                       value="<?= e($settings['footer_copyright'] ?? '') ?>" class="form-input"
                       placeholder="Leave empty for default">
                <p class="form-help">Leave empty for default: "&copy; [Year] [Site Name]. All rights reserved."</p>
            </div>
        </div>
    </div>

    <!-- Cookie Consent -->
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-semibold">Cookie Consent Banner</h2>
            <p class="text-muted text-sm">GDPR/POPIA compliance cookie notice</p>
        </div>
        <div class="card-body space-y-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="cookie_consent_enabled" value="1"
                       <?= !empty($settings['cookie_consent_enabled']) ? 'checked' : '' ?> class="form-checkbox">
                <span>Enable Cookie Consent Banner</span>
            </label>

            <div class="form-group">
                <label for="cookie_consent_text" class="form-label">Consent Message</label>
                <textarea id="cookie_consent_text" name="cookie_consent_text" rows="2" class="form-input"><?= e($settings['cookie_consent_text'] ?? 'We use cookies to enhance your browsing experience. By continuing to use our site, you agree to our use of cookies.') ?></textarea>
            </div>

            <div class="form-group">
                <label for="cookie_policy_link" class="form-label">Privacy Policy Link</label>
                <input type="text" id="cookie_policy_link" name="cookie_policy_link"
                       value="<?= e($settings['cookie_policy_link'] ?? '/page/privacy') ?>" class="form-input">
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-semibold">Back to Top Button</h2>
        </div>
        <div class="card-body">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="back_to_top_enabled" value="1"
                       <?= ($settings['back_to_top_enabled'] ?? '1') ? 'checked' : '' ?> class="form-checkbox">
                <span>Show "Back to Top" button when scrolling</span>
            </label>
        </div>
    </div>

    <!-- Save Button -->
    <div class="flex justify-end">
        <button type="submit" class="btn btn-primary btn-lg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" class="mr-2">
                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Save Appearance Settings
        </button>
    </div>
</form>

<style>
.color-picker-wrap {
    display: flex;
    gap: var(--space-2);
    align-items: center;
}

.color-input {
    width: 50px;
    height: 40px;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    cursor: pointer;
    padding: 2px;
}

.color-hex {
    width: 100px;
    font-family: monospace;
}
</style>

<script>
// Color picker sync
document.querySelectorAll('.color-input').forEach(input => {
    input.addEventListener('input', function() {
        const hexInput = document.getElementById(this.id + '_hex');
        if (hexInput) hexInput.value = this.value;
    });
});

document.querySelectorAll('.color-hex').forEach(input => {
    input.addEventListener('input', function() {
        const colorId = this.id.replace('_hex', '');
        const colorInput = document.getElementById(colorId);
        if (colorInput && /^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            colorInput.value = this.value;
        }
    });
});
</script>
