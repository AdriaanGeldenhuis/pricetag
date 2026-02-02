<!-- Admin Product Form -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle">
            <?= $product ? 'Edit product details' : 'Create a new product' ?>
        </p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/products') ?>" class="admin-btn admin-btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Products
        </a>
        <?php if ($product): ?>
        <a href="<?= url('/products/' . $product['slug']) ?>" target="_blank" class="admin-btn admin-btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                <polyline points="15 3 21 3 21 9"></polyline>
                <line x1="10" y1="14" x2="21" y2="3"></line>
            </svg>
            View Product
        </a>
        <?php endif; ?>
    </div>
</div>

<?php $formData = $_SESSION['form_data'] ?? []; unset($_SESSION['form_data']); ?>
<?php $errors = $_SESSION['form_errors'] ?? []; unset($_SESSION['form_errors']); ?>

<form method="POST" action="<?= $product ? url('/admin/products/' . $product['id']) : url('/admin/products') ?>"
      enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <?php if ($product): ?>
    <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- Main Content -->
        <div>
            <!-- Basic Info -->
            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Basic Information</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <label class="admin-form-label required">Product Name</label>
                        <input type="text" name="name" value="<?= e($formData['name'] ?? $product['name'] ?? '') ?>"
                               class="admin-form-input <?= isset($errors['name']) ? 'error' : '' ?>" required>
                        <?php if (isset($errors['name'])): ?>
                        <p class="admin-form-error"><?= e($errors['name']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="admin-form-group">
                            <label class="admin-form-label required">SKU</label>
                            <input type="text" name="sku" value="<?= e($formData['sku'] ?? $product['sku'] ?? '') ?>"
                                   class="admin-form-input <?= isset($errors['sku']) ? 'error' : '' ?>" required>
                            <?php if (isset($errors['sku'])): ?>
                            <p class="admin-form-error"><?= e($errors['sku']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label required">Category</label>
                            <select name="category_id" class="admin-form-select <?= isset($errors['category_id']) ? 'error' : '' ?>" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"
                                    <?= ($formData['category_id'] ?? $product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name_display']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Short Description</label>
                        <textarea name="short_description" class="admin-form-textarea" rows="2"
                                  placeholder="Brief product summary for listing pages..."><?= e($formData['short_description'] ?? $product['short_description'] ?? '') ?></textarea>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Full Description</label>
                        <textarea name="description" class="admin-form-textarea" rows="6"
                                  placeholder="Detailed product description..."><?= e($formData['description'] ?? $product['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Pricing</h3>
                </div>
                <div class="admin-card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div class="admin-form-group">
                            <label class="admin-form-label required">Price (ZAR)</label>
                            <input type="number" name="price" step="0.01" min="0"
                                   value="<?= e($formData['price'] ?? $product['price'] ?? '') ?>"
                                   class="admin-form-input <?= isset($errors['price']) ? 'error' : '' ?>" required>
                            <?php if (isset($errors['price'])): ?>
                            <p class="admin-form-error"><?= e($errors['price']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Compare at Price</label>
                            <input type="number" name="compare_price" step="0.01" min="0"
                                   value="<?= e($formData['compare_price'] ?? $product['compare_price'] ?? '') ?>"
                                   class="admin-form-input">
                            <p class="admin-form-hint">Original price (shows as strikethrough)</p>
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Cost Price</label>
                            <input type="number" name="cost_price" step="0.01" min="0"
                                   value="<?= e($formData['cost_price'] ?? $product['cost_price'] ?? '') ?>"
                                   class="admin-form-input">
                            <p class="admin-form-hint">For profit calculations (not shown)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Images</h3>
                </div>
                <div class="admin-card-body">
                    <?php if (!empty($images)): ?>
                    <div class="admin-image-preview" style="margin-bottom: 1rem;">
                        <?php foreach ($images as $img): ?>
                        <div class="admin-image-preview-item">
                            <img src="<?= e($img['image_url']) ?>" alt="Product image">
                            <button type="button" class="admin-image-preview-remove"
                                    onclick="deleteImage(<?= $img['id'] ?>)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                            <?php if ($img['is_primary']): ?>
                            <span style="position: absolute; bottom: 0.25rem; left: 0.25rem; background: var(--admin-primary); color: #fff; font-size: 0.625rem; padding: 0.125rem 0.375rem; border-radius: 4px;">Primary</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="admin-image-upload" onclick="document.getElementById('imageInput').click()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-image-upload-icon">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <p class="admin-image-upload-text">Click to upload images</p>
                        <p class="admin-image-upload-hint">PNG, JPG, WEBP up to 5MB</p>
                    </div>
                    <input type="file" id="imageInput" name="images[]" multiple accept="image/*" style="display: none;"
                           onchange="previewImages(this)">

                    <div id="newImagePreview" class="admin-image-preview" style="margin-top: 1rem;"></div>
                </div>
            </div>

            <!-- Attributes -->
            <?php if (!empty($attributes)): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Attributes</h3>
                </div>
                <div class="admin-card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <?php foreach ($attributes as $attr): ?>
                        <div class="admin-form-group">
                            <label class="admin-form-label"><?= e($attr['name']) ?></label>
                            <input type="text" name="attributes[<?= $attr['id'] ?>]"
                                   value="<?= e($productAttributes[$attr['id']] ?? '') ?>"
                                   class="admin-form-input"
                                   placeholder="<?= e($attr['placeholder'] ?? '') ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Status -->
            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Status</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <label class="admin-checkbox-group">
                            <input type="checkbox" name="status" value="1" class="admin-checkbox"
                                <?= ($formData['status'] ?? $product['status'] ?? 1) ? 'checked' : '' ?>>
                            <span>Active (visible in store)</span>
                        </label>
                    </div>
                    <div class="admin-form-group" style="margin-bottom: 0;">
                        <label class="admin-checkbox-group">
                            <input type="checkbox" name="featured" value="1" class="admin-checkbox"
                                <?= ($formData['featured'] ?? $product['featured'] ?? 0) ? 'checked' : '' ?>>
                            <span>Featured product</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Inventory -->
            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Inventory</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Stock Quantity</label>
                        <input type="number" name="stock" min="0"
                               value="<?= e($formData['stock'] ?? $product['stock'] ?? 0) ?>"
                               class="admin-form-input">
                    </div>
                    <div class="admin-form-group" style="margin-bottom: 0;">
                        <label class="admin-form-label">Low Stock Threshold</label>
                        <input type="number" name="low_stock_threshold" min="0"
                               value="<?= e($formData['low_stock_threshold'] ?? $product['low_stock_threshold'] ?? 10) ?>"
                               class="admin-form-input">
                        <p class="admin-form-hint">Alert when stock falls below this</p>
                    </div>
                </div>
            </div>

            <!-- Shipping -->
            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Shipping</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group" style="margin-bottom: 0;">
                        <label class="admin-form-label">Weight (kg)</label>
                        <input type="number" name="weight" step="0.01" min="0"
                               value="<?= e($formData['weight'] ?? $product['weight'] ?? '') ?>"
                               class="admin-form-input">
                        <p class="admin-form-hint">Used for shipping calculations</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="admin-card">
                <div class="admin-card-body">
                    <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; margin-bottom: 0.75rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <?= $product ? 'Update Product' : 'Create Product' ?>
                    </button>

                    <?php if ($product): ?>
                    <button type="button" class="admin-btn admin-btn-danger" style="width: 100%;"
                            onclick="deleteProduct(<?= $product['id'] ?>)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        Delete Product
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Delete Modal -->
<?php if ($product): ?>
<div class="admin-modal-overlay" id="deleteModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Delete Product</h3>
            <button class="admin-modal-close" onclick="closeDeleteModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="admin-modal-body">
            <p>Are you sure you want to delete "<strong><?= e($product['name']) ?></strong>"? This action cannot be undone.</p>
        </div>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form method="POST" action="<?= url('/admin/products/' . $product['id']) ?>" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Product</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function previewImages(input) {
    const preview = document.getElementById('newImagePreview');
    preview.innerHTML = '';

    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'admin-image-preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="admin-image-preview-remove" onclick="removeNewImage(${index}, this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

function removeNewImage(index, button) {
    button.closest('.admin-image-preview-item').remove();
    // Note: This is a visual removal only. For full functionality,
    // implement proper file handling with DataTransfer
}

function deleteImage(imageId) {
    if (confirm('Delete this image?')) {
        fetch('/admin/products/images/' + imageId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': '<?= csrf_token() ?>',
                'Content-Type': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  location.reload();
              }
          });
    }
}

function deleteProduct() {
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>
