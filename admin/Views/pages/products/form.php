<!-- Admin Product Form -->
<div class="flex justify-between items-center mb-6">
    <h1 class="admin-page-title mb-0"><?= $product ? 'Edit Product' : 'Add Product' ?></h1>
    <a href="<?= url('/admin/products') ?>" class="btn btn-ghost">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
        Back to Products
    </a>
</div>

<form action="<?= $product ? url('/admin/products/' . $product->id) : url('/admin/products') ?>"
      method="POST" enctype="multipart/form-data" class="admin-form">
    <?= csrf_field() ?>
    <?php if ($product): ?>
    <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Basic Information</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" value="<?= e($product->name ?? '') ?>"
                               class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                        <input type="text" id="sku" name="sku" value="<?= e($product->sku ?? '') ?>"
                               class="form-input" <?= $product ? 'readonly' : 'required' ?>>
                        <?php if ($product): ?>
                        <p class="form-help">SKU cannot be changed after creation</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="short_description" class="form-label">Short Description</label>
                        <textarea id="short_description" name="short_description" rows="2"
                                  class="form-input"><?= e($product->short_description ?? '') ?></textarea>
                        <p class="form-help">Brief description for product cards (max 160 characters)</p>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Full Description</label>
                        <textarea id="description" name="description" rows="8"
                                  class="form-input"><?= e($product->description ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Pricing</h2>
                </div>
                <div class="card-body">
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">R</span>
                                <input type="number" id="price" name="price" step="0.01" min="0"
                                       value="<?= $product->price ?? '' ?>" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="compare_price" class="form-label">Compare at Price</label>
                            <div class="input-group">
                                <span class="input-group-text">R</span>
                                <input type="number" id="compare_price" name="compare_price" step="0.01" min="0"
                                       value="<?= $product->compare_price ?? '' ?>" class="form-input">
                            </div>
                            <p class="form-help">Original price for showing discounts</p>
                        </div>

                        <div class="form-group">
                            <label for="cost_price" class="form-label">Cost Price</label>
                            <div class="input-group">
                                <span class="input-group-text">R</span>
                                <input type="number" id="cost_price" name="cost_price" step="0.01" min="0"
                                       value="<?= $product->cost_price ?? '' ?>" class="form-input">
                            </div>
                            <p class="form-help">For profit calculations (not shown to customers)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Inventory</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="manage_stock" value="1"
                                   <?= !empty($product->manage_stock) ? 'checked' : '' ?>
                                   class="form-checkbox" id="manage_stock_toggle">
                            <span>Track stock quantity</span>
                        </label>
                    </div>

                    <div id="stock_fields" class="grid md:grid-cols-2 gap-4" style="<?= empty($product->manage_stock) ? 'display:none' : '' ?>">
                        <div class="form-group">
                            <label for="stock_quantity" class="form-label">Stock Quantity</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" min="0"
                                   value="<?= $product->stock_quantity ?? 0 ?>" class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="low_stock_threshold" class="form-label">Low Stock Alert Threshold</label>
                            <input type="number" id="low_stock_threshold" name="low_stock_threshold" min="0"
                                   value="<?= $product->low_stock_threshold ?? 5 ?>" class="form-input">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Images</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($images)): ?>
                    <div class="grid grid-cols-4 gap-4 mb-4">
                        <?php foreach ($images as $image): ?>
                        <div class="relative group">
                            <img src="<?= url('storage/uploads/' . e($image['path'])) ?>"
                                 alt="" class="w-full aspect-square object-cover rounded">
                            <?php if ($image['is_primary']): ?>
                            <span class="absolute top-2 left-2 badge badge-primary text-xs">Primary</span>
                            <?php endif; ?>
                            <button type="button"
                                    onclick="deleteImage(<?= $image['id'] ?>)"
                                    class="absolute top-2 right-2 btn btn-danger btn-sm btn-icon opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                </svg>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="image" class="form-label">Upload Image</label>
                        <input type="file" id="image" name="image" accept="image/*" class="form-input">
                        <p class="form-help">Accepted formats: JPEG, PNG, WebP. Max size: 5MB</p>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">SEO</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title"
                               value="<?= e($product->meta_title ?? '') ?>" class="form-input" maxlength="70">
                        <p class="form-help">Leave empty to use product name. Max 70 characters.</p>
                    </div>

                    <div class="form-group">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" rows="3"
                                  class="form-input" maxlength="160"><?= e($product->meta_description ?? '') ?></textarea>
                        <p class="form-help">Leave empty to use short description. Max 160 characters.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Status</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="status" class="form-label">Product Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="draft" <?= ($product->status ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="active" <?= ($product->status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($product->status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="type" class="form-label">Product Type</label>
                        <select id="type" name="type" class="form-select">
                            <option value="simple" <?= ($product->type ?? 'simple') === 'simple' ? 'selected' : '' ?>>Simple</option>
                            <option value="variable" <?= ($product->type ?? '') === 'variable' ? 'selected' : '' ?>>Variable</option>
                            <option value="bundle" <?= ($product->type ?? '') === 'bundle' ? 'selected' : '' ?>>Bundle</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Categories -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Categories</h2>
                </div>
                <div class="card-body">
                    <div class="max-h-64 overflow-y-auto space-y-2">
                        <?php
                        $productCategories = $productCategories ?? [];
                        function renderCategoryCheckboxes($categories, $productCategories, $level = 0) {
                            foreach ($categories as $category) {
                                $checked = in_array($category['id'], $productCategories) ? 'checked' : '';
                                $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                                echo '<label class="flex items-center gap-2 cursor-pointer">';
                                echo '<input type="checkbox" name="categories[]" value="' . $category['id'] . '" ' . $checked . ' class="form-checkbox">';
                                echo '<span>' . $indent . e($category['name']) . '</span>';
                                echo '</label>';
                                if (!empty($category['children'])) {
                                    renderCategoryCheckboxes($category['children'], $productCategories, $level + 1);
                                }
                            }
                        }
                        renderCategoryCheckboxes($categories, $productCategories);
                        ?>
                    </div>
                </div>
            </div>

            <!-- Vendor -->
            <?php if (!empty($vendors)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Vendor</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="vendor_id" class="form-label">Select Vendor</label>
                        <select id="vendor_id" name="vendor_id" class="form-select">
                            <option value="">-- No Vendor --</option>
                            <?php foreach ($vendors as $vendor): ?>
                            <option value="<?= $vendor['id'] ?>" <?= ($product->vendor_id ?? '') == $vendor['id'] ? 'selected' : '' ?>>
                                <?= e($vendor['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="form-help">Associate this product with a vendor/supplier</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Flags -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Product Flags</h2>
                </div>
                <div class="card-body space-y-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1"
                               <?= !empty($product->is_featured) ? 'checked' : '' ?> class="form-checkbox">
                        <span>Featured Product</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_new" value="1"
                               <?= !empty($product->is_new) ? 'checked' : '' ?> class="form-checkbox">
                        <span>New Arrival</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_on_sale" value="1"
                               <?= !empty($product->is_on_sale) ? 'checked' : '' ?> class="form-checkbox">
                        <span>On Sale</span>
                    </label>
                </div>
            </div>

            <!-- Attributes -->
            <?php if (!empty($attributes)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Attributes</h2>
                </div>
                <div class="card-body space-y-4">
                    <?php
                    $productAttributes = $productAttributes ?? [];
                    foreach ($attributes as $attr):
                        $selectedValue = $productAttributes[$attr['id']]['value_id'] ?? '';
                        $customValue = $productAttributes[$attr['id']]['custom_value'] ?? '';
                    ?>
                    <div class="form-group">
                        <label class="form-label"><?= e($attr['name']) ?></label>
                        <?php if ($attr['type'] === 'text'): ?>
                        <input type="hidden" name="attribute_values[<?= $attr['id'] ?>]" value="">
                        <input type="text" name="attribute_custom[<?= $attr['id'] ?>]"
                               value="<?= e($customValue) ?>" class="form-input"
                               placeholder="Enter <?= e(strtolower($attr['name'])) ?>">
                        <?php elseif ($attr['type'] === 'color'): ?>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($attr['values'] as $value): ?>
                            <label class="color-option cursor-pointer" title="<?= e($value['value']) ?>">
                                <input type="radio" name="attribute_values[<?= $attr['id'] ?>]"
                                       value="<?= $value['id'] ?>"
                                       <?= $selectedValue == $value['id'] ? 'checked' : '' ?>
                                       class="sr-only">
                                <span class="color-swatch <?= $selectedValue == $value['id'] ? 'selected' : '' ?>"
                                      style="background-color: <?= e($value['color_code'] ?? '#ccc') ?>;"></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <select name="attribute_values[<?= $attr['id'] ?>]" class="form-select">
                            <option value="">-- Select --</option>
                            <?php foreach ($attr['values'] as $value): ?>
                            <option value="<?= $value['id'] ?>" <?= $selectedValue == $value['id'] ? 'selected' : '' ?>>
                                <?= e($value['value']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="card">
                <div class="card-body space-y-3">
                    <button type="submit" class="btn btn-primary w-full">
                        <?= $product ? 'Update Product' : 'Create Product' ?>
                    </button>

                    <?php if ($product): ?>
                    <a href="<?= url('/products/' . $product->slug) ?>" target="_blank" class="btn btn-outline w-full">
                        View Product
                    </a>
                    <button type="button" onclick="deleteProduct(<?= $product->id ?>)" class="btn btn-danger btn-outline w-full">
                        Delete Product
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Toggle stock fields
document.getElementById('manage_stock_toggle').addEventListener('change', function() {
    document.getElementById('stock_fields').style.display = this.checked ? '' : 'none';
});

<?php if ($product): ?>
// Delete product
function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        fetch('<?= url('/admin/products/') ?>' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': '<?= csrf_token() ?>',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?= url('/admin/products') ?>';
            }
        });
    }
}

// Delete image
function deleteImage(id) {
    if (confirm('Delete this image?')) {
        fetch('<?= url('/admin/products/images/') ?>' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': '<?= csrf_token() ?>',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}
<?php endif; ?>
</script>

<style>
/* Color swatch styles for attributes */
.color-swatch {
    display: inline-block;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 2px solid transparent;
    box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
    transition: all 0.2s;
}

.color-swatch:hover {
    transform: scale(1.1);
}

.color-swatch.selected,
.color-option input:checked + .color-swatch {
    border-color: var(--color-primary, #3b82f6);
    box-shadow: 0 0 0 2px var(--color-primary, #3b82f6);
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>
