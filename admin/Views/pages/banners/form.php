<!-- Admin Banner Form -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= $banner ? 'Edit Banner' : 'Add New Banner' ?></h1>
        <p class="admin-page-subtitle">
            <?= $banner ? 'Update banner settings and images' : 'Create a new promotional banner' ?>
        </p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/banners') ?>" class="admin-btn admin-btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Banners
        </a>
    </div>
</div>

<form action="<?= $banner ? url('/admin/banners/' . $banner['id']) : url('/admin/banners') ?>"
      method="POST" enctype="multipart/form-data" class="admin-form">
    <?= csrf_field() ?>
    <?php if ($banner): ?>
    <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Banner Image -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Banner Image</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Desktop Image <span class="text-danger">*</span></label>
                        <p class="form-help mb-3">Recommended: 1920x600px for hero, 1200x300px for other banners</p>

                        <div class="upload-zone" id="imageZone">
                            <?php if ($banner && $banner['image']): ?>
                            <div class="upload-preview" id="imagePreview">
                                <img src="<?= e($banner['image']) ?>" alt="Banner Image">
                            </div>
                            <?php else: ?>
                            <div class="upload-placeholder" id="imagePlaceholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="48" height="48">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <p>Click or drag to upload banner image</p>
                                <span class="text-xs text-muted">JPG, PNG, WebP up to 10MB</span>
                            </div>
                            <?php endif; ?>
                            <input type="file" name="image" id="imageInput" accept="image/*" class="upload-input"
                                   <?= $banner ? '' : 'required' ?>>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <label class="form-label">Mobile Image (Optional)</label>
                        <p class="form-help mb-3">Optimized image for mobile devices. Recommended: 768x400px</p>

                        <div class="upload-zone upload-zone-small" id="mobileImageZone">
                            <?php if ($banner && $banner['mobile_image']): ?>
                            <div class="upload-preview" id="mobileImagePreview">
                                <img src="<?= e($banner['mobile_image']) ?>" alt="Mobile Image" style="max-height: 150px;">
                                <button type="button" class="upload-remove" onclick="removeMobileImage()">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                            <?php else: ?>
                            <div class="upload-placeholder" id="mobileImagePlaceholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32">
                                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                    <line x1="12" y1="18" x2="12.01" y2="18"></line>
                                </svg>
                                <p>Upload mobile version</p>
                            </div>
                            <?php endif; ?>
                            <input type="file" name="mobile_image" id="mobileImageInput" accept="image/*" class="upload-input">
                            <input type="hidden" name="remove_mobile_image" id="removeMobileImageInput" value="">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner Content -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Banner Content</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="title" class="form-label">Title (Optional)</label>
                        <input type="text" id="title" name="title"
                               value="<?= e($banner['title'] ?? '') ?>"
                               class="form-input" placeholder="e.g., Summer Sale - Up to 50% Off">
                    </div>

                    <div class="form-group">
                        <label for="subtitle" class="form-label">Subtitle (Optional)</label>
                        <textarea id="subtitle" name="subtitle" rows="2"
                                  class="form-input" placeholder="e.g., Shop our hottest deals of the season"><?= e($banner['subtitle'] ?? '') ?></textarea>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="url" class="form-label">Link URL</label>
                            <input type="url" id="url" name="url"
                                   value="<?= e($banner['url'] ?? '') ?>"
                                   class="form-input" placeholder="https://example.com/sale">
                        </div>

                        <div class="form-group">
                            <label for="button_text" class="form-label">Button Text</label>
                            <input type="text" id="button_text" name="button_text"
                                   value="<?= e($banner['button_text'] ?? '') ?>"
                                   class="form-input" placeholder="e.g., Shop Now">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Styling Options -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Styling Options</h2>
                </div>
                <div class="card-body">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="text_color" class="form-label">Text Color</label>
                            <div class="color-picker-wrap">
                                <input type="color" id="text_color" name="text_color"
                                       value="<?= e($banner['text_color'] ?? '#ffffff') ?>" class="color-input">
                                <input type="text" id="text_color_hex"
                                       value="<?= e($banner['text_color'] ?? '#ffffff') ?>"
                                       class="form-input color-hex" maxlength="7">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="overlay_opacity" class="form-label">Overlay Opacity</label>
                            <div class="flex items-center gap-3">
                                <input type="range" id="overlay_opacity" name="overlay_opacity"
                                       value="<?= e($banner['overlay_opacity'] ?? 0) ?>"
                                       min="0" max="100" class="form-range flex-1"
                                       oninput="document.getElementById('overlayValue').textContent = this.value + '%'">
                                <span id="overlayValue" class="text-sm font-medium" style="min-width: 40px;">
                                    <?= e($banner['overlay_opacity'] ?? 0) ?>%
                                </span>
                            </div>
                            <p class="form-help">Darkens the image for better text readability</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Publish Settings -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Publish</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                        <select id="location" name="location" class="form-select" required>
                            <?php foreach ($locations as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($banner['location'] ?? 'hero') === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order"
                               value="<?= e($banner['sort_order'] ?? 0) ?>"
                               class="form-input" min="0">
                        <p class="form-help">Lower numbers appear first</p>
                    </div>

                    <div class="form-group">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                   <?= ($banner['is_active'] ?? 1) ? 'checked' : '' ?> class="form-checkbox">
                            <span>Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Schedule -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Schedule (Optional)</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="starts_at" class="form-label">Start Date</label>
                        <input type="datetime-local" id="starts_at" name="starts_at"
                               value="<?= $banner && $banner['starts_at'] ? date('Y-m-d\TH:i', strtotime($banner['starts_at'])) : '' ?>"
                               class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="expires_at" class="form-label">End Date</label>
                        <input type="datetime-local" id="expires_at" name="expires_at"
                               value="<?= $banner && $banner['expires_at'] ? date('Y-m-d\TH:i', strtotime($banner['expires_at'])) : '' ?>"
                               class="form-input">
                    </div>

                    <p class="form-help">Leave empty for always-on banners</p>
                </div>
            </div>

            <!-- Preview -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Preview</h2>
                </div>
                <div class="card-body p-0">
                    <div class="banner-preview" id="bannerPreview">
                        <div class="banner-preview-image" id="previewImage">
                            <?php if ($banner && $banner['image']): ?>
                            <img src="<?= e($banner['image']) ?>" alt="Preview">
                            <?php else: ?>
                            <div class="banner-preview-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="banner-preview-overlay" id="previewOverlay"></div>
                        <div class="banner-preview-content" id="previewContent">
                            <h3 id="previewTitle"><?= e($banner['title'] ?? 'Banner Title') ?></h3>
                            <p id="previewSubtitle"><?= e($banner['subtitle'] ?? 'Subtitle text goes here') ?></p>
                            <span id="previewButton" class="banner-preview-btn"><?= e($banner['button_text'] ?? 'Shop Now') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-3">
                <button type="submit" class="admin-btn admin-btn-primary w-full">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" class="mr-2">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <?= $banner ? 'Update Banner' : 'Create Banner' ?>
                </button>

                <?php if ($banner): ?>
                <button type="button" onclick="deleteBanner(<?= $banner['id'] ?>)"
                        class="admin-btn admin-btn-danger w-full">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" class="mr-2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                    </svg>
                    Delete Banner
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>

<?php if ($banner): ?>
<!-- Delete Modal -->
<div class="admin-modal-overlay" id="deleteModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Delete Banner</h3>
            <button class="admin-modal-close" onclick="closeDeleteModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="admin-modal-body">
            <p>Are you sure you want to delete this banner? This action cannot be undone.</p>
        </div>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form action="<?= url('/admin/banners/' . $banner['id']) ?>" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Banner</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

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

.upload-preview {
    position: relative;
    display: inline-block;
}

.upload-preview img {
    max-width: 100%;
    max-height: 300px;
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

.form-range {
    -webkit-appearance: none;
    height: 6px;
    border-radius: var(--radius-full);
    background: var(--color-border);
}

.form-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: var(--color-primary);
    cursor: pointer;
}

.banner-preview {
    position: relative;
    aspect-ratio: 16/9;
    overflow: hidden;
    background: var(--color-background-alt);
}

.banner-preview-image {
    position: absolute;
    inset: 0;
}

.banner-preview-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.banner-preview-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-muted);
}

.banner-preview-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0);
    transition: var(--transition-colors);
}

.banner-preview-content {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: var(--space-4);
    text-align: center;
    color: white;
}

.banner-preview-content h3 {
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
    margin-bottom: var(--space-1);
}

.banner-preview-content p {
    font-size: var(--text-sm);
    opacity: 0.9;
    margin-bottom: var(--space-3);
}

.banner-preview-btn {
    padding: var(--space-2) var(--space-4);
    background: rgba(255,255,255,0.2);
    border-radius: var(--radius-md);
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
}
</style>

<script>
// Image upload preview
document.getElementById('imageInput').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const zone = document.getElementById('imageZone');
            const placeholder = document.getElementById('imagePlaceholder');
            const preview = document.getElementById('imagePreview');

            if (placeholder) placeholder.style.display = 'none';

            if (preview) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Banner Image">';
            } else {
                const newPreview = document.createElement('div');
                newPreview.className = 'upload-preview';
                newPreview.id = 'imagePreview';
                newPreview.innerHTML = '<img src="' + e.target.result + '" alt="Banner Image">';
                zone.insertBefore(newPreview, zone.querySelector('.upload-input'));
            }

            // Update live preview
            document.querySelector('#previewImage').innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
        };
        reader.readAsDataURL(this.files[0]);
    }
});

// Mobile image upload
document.getElementById('mobileImageInput').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const zone = document.getElementById('mobileImageZone');
            const placeholder = document.getElementById('mobileImagePlaceholder');

            if (placeholder) placeholder.style.display = 'none';

            let preview = document.getElementById('mobileImagePreview');
            if (!preview) {
                preview = document.createElement('div');
                preview.className = 'upload-preview';
                preview.id = 'mobileImagePreview';
                zone.insertBefore(preview, zone.querySelector('.upload-input'));
            }

            preview.innerHTML = `
                <img src="${e.target.result}" alt="Mobile Image" style="max-height: 150px;">
                <button type="button" class="upload-remove" onclick="removeMobileImage()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            `;
        };
        reader.readAsDataURL(this.files[0]);
    }
});

function removeMobileImage() {
    document.getElementById('removeMobileImageInput').value = '1';
    document.getElementById('mobileImageInput').value = '';
    const preview = document.getElementById('mobileImagePreview');
    const placeholder = document.getElementById('mobileImagePlaceholder');
    if (preview) preview.remove();
    if (placeholder) placeholder.style.display = 'block';
}

// Live preview updates
document.getElementById('title').addEventListener('input', function() {
    document.getElementById('previewTitle').textContent = this.value || 'Banner Title';
});

document.getElementById('subtitle').addEventListener('input', function() {
    document.getElementById('previewSubtitle').textContent = this.value || 'Subtitle text goes here';
});

document.getElementById('button_text').addEventListener('input', function() {
    document.getElementById('previewButton').textContent = this.value || 'Shop Now';
});

document.getElementById('text_color').addEventListener('input', function() {
    document.getElementById('text_color_hex').value = this.value;
    document.getElementById('previewContent').style.color = this.value;
});

document.getElementById('text_color_hex').addEventListener('input', function() {
    if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
        document.getElementById('text_color').value = this.value;
        document.getElementById('previewContent').style.color = this.value;
    }
});

document.getElementById('overlay_opacity').addEventListener('input', function() {
    const opacity = this.value / 100;
    document.getElementById('previewOverlay').style.background = 'rgba(0,0,0,' + opacity + ')';
});

// Initialize overlay
document.getElementById('previewOverlay').style.background = 'rgba(0,0,0,' + (<?= $banner['overlay_opacity'] ?? 0 ?> / 100) + ')';

// Delete modal
function deleteBanner(id) {
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>
