<!-- Admin Logo & Branding Settings -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="admin-page-title mb-0">Logo & Branding</h1>
        <p class="text-muted mt-1">Customize your store's visual identity</p>
    </div>
</div>

<form action="<?= url('/admin/branding') ?>" method="POST" enctype="multipart/form-data" class="admin-form">
    <?= csrf_field() ?>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Logo Upload -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Logo</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Site Logo</label>
                    <p class="form-help mb-3">Recommended size: 200x60px. Formats: PNG, SVG, JPG</p>

                    <div class="upload-zone" id="logoZone">
                        <?php if (!empty($settings['logo'])): ?>
                        <div class="upload-preview" id="logoPreview">
                            <img src="<?= e($settings['logo']) ?>" alt="Current Logo" style="max-height: 80px;">
                            <button type="button" class="upload-remove" onclick="removeLogo()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="upload-placeholder" id="logoPlaceholder">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="48" height="48">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <p>Click or drag to upload logo</p>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="logo" id="logoInput" accept="image/*" class="upload-input">
                        <input type="hidden" name="remove_logo" id="removeLogo" value="">
                    </div>
                </div>
            </div>
        </div>

        <!-- Favicon Upload -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Favicon</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Site Favicon</label>
                    <p class="form-help mb-3">Recommended size: 32x32px or 64x64px. Format: ICO, PNG</p>

                    <div class="upload-zone upload-zone-small" id="faviconZone">
                        <?php if (!empty($settings['favicon'])): ?>
                        <div class="upload-preview" id="faviconPreview">
                            <img src="<?= e($settings['favicon']) ?>" alt="Current Favicon" style="max-height: 64px;">
                            <button type="button" class="upload-remove" onclick="removeFavicon()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="upload-placeholder" id="faviconPlaceholder">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <p>Upload favicon</p>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="favicon" id="faviconInput" accept=".ico,.png,.svg" class="upload-input">
                        <input type="hidden" name="remove_favicon" id="removeFavicon" value="">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Brand Colors -->
    <div class="card mt-6">
        <div class="card-header">
            <h2 class="font-semibold">Brand Colors</h2>
        </div>
        <div class="card-body">
            <div class="grid md:grid-cols-3 gap-6">
                <div class="form-group">
                    <label for="primary_color" class="form-label">Primary Color</label>
                    <p class="form-help mb-2">Main brand color (buttons, links, accents)</p>
                    <div class="color-picker-wrap">
                        <input type="color" id="primary_color" name="primary_color"
                               value="<?= e($settings['primary_color'] ?? '#2563eb') ?>" class="color-input">
                        <input type="text" id="primary_color_hex"
                               value="<?= e($settings['primary_color'] ?? '#2563eb') ?>"
                               class="form-input color-hex" maxlength="7"
                               onchange="document.getElementById('primary_color').value = this.value">
                    </div>
                </div>

                <div class="form-group">
                    <label for="secondary_color" class="form-label">Secondary Color</label>
                    <p class="form-help mb-2">Supporting color (hover states, secondary elements)</p>
                    <div class="color-picker-wrap">
                        <input type="color" id="secondary_color" name="secondary_color"
                               value="<?= e($settings['secondary_color'] ?? '#1e40af') ?>" class="color-input">
                        <input type="text" id="secondary_color_hex"
                               value="<?= e($settings['secondary_color'] ?? '#1e40af') ?>"
                               class="form-input color-hex" maxlength="7"
                               onchange="document.getElementById('secondary_color').value = this.value">
                    </div>
                </div>

                <div class="form-group">
                    <label for="accent_color" class="form-label">Accent Color</label>
                    <p class="form-help mb-2">Highlight color (badges, notifications, CTAs)</p>
                    <div class="color-picker-wrap">
                        <input type="color" id="accent_color" name="accent_color"
                               value="<?= e($settings['accent_color'] ?? '#f59e0b') ?>" class="color-input">
                        <input type="text" id="accent_color_hex"
                               value="<?= e($settings['accent_color'] ?? '#f59e0b') ?>"
                               class="form-input color-hex" maxlength="7"
                               onchange="document.getElementById('accent_color').value = this.value">
                    </div>
                </div>
            </div>

            <!-- Color Preview -->
            <div class="color-preview mt-6">
                <h4 class="text-sm font-semibold mb-3">Preview</h4>
                <div class="preview-buttons">
                    <button type="button" class="preview-btn primary" id="previewPrimary">Primary Button</button>
                    <button type="button" class="preview-btn secondary" id="previewSecondary">Secondary</button>
                    <span class="preview-badge" id="previewAccent">Sale</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Typography -->
    <div class="card mt-6">
        <div class="card-header">
            <h2 class="font-semibold">Typography</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="font_family" class="form-label">Font Family</label>
                <p class="form-help mb-2">Choose the primary font for your store</p>
                <select id="font_family" name="font_family" class="form-select" onchange="updateFontPreview()">
                    <?php
                    $fonts = [
                        'Inter' => 'Inter (Modern, Clean)',
                        'Roboto' => 'Roboto (Google\'s Default)',
                        'Open Sans' => 'Open Sans (Friendly, Readable)',
                        'Lato' => 'Lato (Professional)',
                        'Poppins' => 'Poppins (Geometric, Modern)',
                        'Montserrat' => 'Montserrat (Bold Headers)',
                        'Source Sans Pro' => 'Source Sans Pro (Technical)',
                        'Nunito' => 'Nunito (Rounded, Friendly)',
                        'Raleway' => 'Raleway (Elegant)',
                        'Ubuntu' => 'Ubuntu (Tech-focused)',
                    ];
                    $currentFont = $settings['font_family'] ?? 'Inter';
                    foreach ($fonts as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $currentFont === $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Font Preview -->
            <div class="font-preview mt-4 p-4 bg-neutral-50 rounded-lg" id="fontPreview">
                <h3 style="font-family: <?= e($currentFont) ?>, sans-serif; font-size: 24px; margin-bottom: 8px;">
                    The quick brown fox jumps over the lazy dog
                </h3>
                <p style="font-family: <?= e($currentFont) ?>, sans-serif; font-size: 14px; color: #666;">
                    ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz 0123456789
                </p>
            </div>
        </div>
    </div>

    <!-- Header Background (Optional) -->
    <div class="card mt-6">
        <div class="card-header">
            <h2 class="font-semibold">Header Background (Optional)</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Header Background Image</label>
                <p class="form-help mb-3">Optional background image for the header. Recommended: 1920x200px</p>

                <div class="upload-zone" id="headerBgZone">
                    <?php if (!empty($settings['header_bg'])): ?>
                    <div class="upload-preview" id="headerBgPreview">
                        <img src="<?= e($settings['header_bg']) ?>" alt="Header Background" style="max-height: 100px; width: 100%; object-fit: cover;">
                        <button type="button" class="upload-remove" onclick="removeHeaderBg()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="upload-placeholder" id="headerBgPlaceholder">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="48" height="48">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <p>Click or drag to upload header background</p>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="header_bg" id="headerBgInput" accept="image/*" class="upload-input">
                    <input type="hidden" name="remove_header_bg" id="removeHeaderBg" value="">
                </div>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="mt-6 flex justify-end">
        <button type="submit" class="btn btn-primary btn-lg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" class="mr-2">
                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Save Branding Settings
        </button>
    </div>
</form>

<style>
.upload-zone {
    border: 2px dashed var(--color-border);
    border-radius: var(--radius-lg);
    padding: var(--space-6);
    text-align: center;
    position: relative;
    transition: var(--transition-colors);
    background: var(--color-background);
    cursor: pointer;
}

.upload-zone:hover {
    border-color: var(--color-primary);
    background: var(--color-primary-50, #eff6ff);
}

.upload-zone-small {
    padding: var(--space-4);
}

.upload-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.upload-placeholder {
    color: var(--color-text-muted);
}

.upload-placeholder svg {
    margin: 0 auto var(--space-2);
    opacity: 0.5;
}

.upload-placeholder p {
    margin: 0;
    font-size: var(--text-sm);
}

.upload-preview {
    position: relative;
    display: inline-block;
}

.upload-preview img {
    border-radius: var(--radius-md);
}

.upload-remove {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--color-danger);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition-all);
}

.upload-remove:hover {
    transform: scale(1.1);
}

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

.color-preview {
    padding: var(--space-4);
    background: var(--color-background-alt);
    border-radius: var(--radius-lg);
}

.preview-buttons {
    display: flex;
    gap: var(--space-3);
    align-items: center;
    flex-wrap: wrap;
}

.preview-btn {
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius-md);
    font-weight: var(--font-medium);
    border: none;
    cursor: default;
}

.preview-btn.primary {
    background: var(--preview-primary, #2563eb);
    color: white;
}

.preview-btn.secondary {
    background: var(--preview-secondary, #1e40af);
    color: white;
}

.preview-badge {
    padding: var(--space-1) var(--space-2);
    background: var(--preview-accent, #f59e0b);
    color: white;
    font-size: var(--text-xs);
    font-weight: var(--font-bold);
    border-radius: var(--radius-full);
    text-transform: uppercase;
}

.font-preview {
    border: 1px solid var(--color-border-light);
}
</style>

<script>
// Color picker sync
document.querySelectorAll('.color-input').forEach(input => {
    input.addEventListener('input', function() {
        const hexInput = document.getElementById(this.id + '_hex');
        if (hexInput) hexInput.value = this.value;
        updateColorPreview();
    });
});

document.querySelectorAll('.color-hex').forEach(input => {
    input.addEventListener('input', function() {
        const colorId = this.id.replace('_hex', '');
        const colorInput = document.getElementById(colorId);
        if (colorInput && /^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            colorInput.value = this.value;
            updateColorPreview();
        }
    });
});

function updateColorPreview() {
    const primary = document.getElementById('primary_color').value;
    const secondary = document.getElementById('secondary_color').value;
    const accent = document.getElementById('accent_color').value;

    document.getElementById('previewPrimary').style.background = primary;
    document.getElementById('previewSecondary').style.background = secondary;
    document.getElementById('previewAccent').style.background = accent;
}

function updateFontPreview() {
    const font = document.getElementById('font_family').value;
    const preview = document.getElementById('fontPreview');
    preview.querySelectorAll('h3, p').forEach(el => {
        el.style.fontFamily = font + ', sans-serif';
    });

    // Load Google Font
    const link = document.createElement('link');
    link.href = 'https://fonts.googleapis.com/css2?family=' + font.replace(' ', '+') + ':wght@400;500;600;700&display=swap';
    link.rel = 'stylesheet';
    document.head.appendChild(link);
}

// File upload preview
function setupUploadPreview(inputId, previewId, placeholderId, removeInputId) {
    const input = document.getElementById(inputId);
    const zone = input.closest('.upload-zone');

    input.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const existingPreview = document.getElementById(previewId);
                const placeholder = document.getElementById(placeholderId);

                if (placeholder) placeholder.style.display = 'none';

                if (existingPreview) {
                    existingPreview.querySelector('img').src = e.target.result;
                } else {
                    const preview = document.createElement('div');
                    preview.className = 'upload-preview';
                    preview.id = previewId;
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" style="max-height: 80px;">
                        <button type="button" class="upload-remove" onclick="${removeInputId.replace('Input', '')}()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    `;
                    zone.insertBefore(preview, input);
                }

                document.getElementById(removeInputId.replace('Input', '')).value = '';
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
}

setupUploadPreview('logoInput', 'logoPreview', 'logoPlaceholder', 'removeLogo');
setupUploadPreview('faviconInput', 'faviconPreview', 'faviconPlaceholder', 'removeFavicon');
setupUploadPreview('headerBgInput', 'headerBgPreview', 'headerBgPlaceholder', 'removeHeaderBg');

function removeLogo() {
    document.getElementById('removeLogo').value = '1';
    document.getElementById('logoInput').value = '';
    const preview = document.getElementById('logoPreview');
    const placeholder = document.getElementById('logoPlaceholder');
    if (preview) preview.remove();
    if (placeholder) placeholder.style.display = 'block';
    else location.reload();
}

function removeFavicon() {
    document.getElementById('removeFavicon').value = '1';
    document.getElementById('faviconInput').value = '';
    const preview = document.getElementById('faviconPreview');
    const placeholder = document.getElementById('faviconPlaceholder');
    if (preview) preview.remove();
    if (placeholder) placeholder.style.display = 'block';
    else location.reload();
}

function removeHeaderBg() {
    document.getElementById('removeHeaderBg').value = '1';
    document.getElementById('headerBgInput').value = '';
    const preview = document.getElementById('headerBgPreview');
    const placeholder = document.getElementById('headerBgPlaceholder');
    if (preview) preview.remove();
    if (placeholder) placeholder.style.display = 'block';
    else location.reload();
}

// Initialize preview colors
updateColorPreview();
</script>
