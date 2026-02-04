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
                        <label for="images" class="form-label">Upload Images</label>
                        <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/webp" class="form-input" multiple>
                        <p class="form-help">Select multiple images. All images will be converted to WebP and resized to 600x600. Max 5MB per image.</p>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <h2 class="font-semibold">SEO</h2>
                    <button type="button" onclick="generateAiContent()" class="btn btn-sm btn-primary" id="ai-generate-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <path d="M2 17l10 5 10-5"/>
                            <path d="M2 12l10 5 10-5"/>
                        </svg>
                        Generate AI Info
                    </button>
                </div>
                <div class="card-body space-y-4">
                    <div id="ai-status" class="alert alert-info hidden">
                        <span id="ai-status-text">Generating content...</span>
                    </div>
                    <div class="form-group">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title"
                               value="<?php echo e($product->meta_title ?? ''); ?>" class="form-input" maxlength="70">
                        <p class="form-help">Leave empty to use product name. Max 70 characters.</p>
                    </div>

                    <div class="form-group">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" rows="3"
                                  class="form-input" maxlength="160"><?php echo e($product->meta_description ?? ''); ?></textarea>
                        <p class="form-help">Leave empty to use short description. Max 160 characters.</p>
                    </div>
                </div>
            </div>

            <!-- Specifications -->
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <h2 class="font-semibold">Specifications</h2>
                    <button type="button" onclick="addSpecification()" class="btn btn-sm btn-outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Add Specification
                    </button>
                </div>
                <div class="card-body">
                    <div id="specifications-container" class="space-y-3">
                        <?php
                        $specifications = $specifications ?? [];
                        if (empty($specifications)): ?>
                        <p class="text-muted text-sm" id="no-specs-msg">No specifications added yet. Click "Add Specification" to add one.</p>
                        <?php else:
                            foreach ($specifications as $i => $spec): ?>
                        <div class="spec-row flex gap-2 items-start">
                            <input type="text" name="spec_name[]" value="<?php echo e($spec['spec_name']); ?>"
                                   class="form-input flex-1" placeholder="Name (e.g., Weight)">
                            <input type="text" name="spec_value[]" value="<?php echo e($spec['spec_value']); ?>"
                                   class="form-input flex-1" placeholder="Value (e.g., 1.5kg)">
                            <button type="button" onclick="removeSpecification(this)" class="btn btn-danger btn-icon btn-sm">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>
                        <?php endforeach;
                        endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($product && !empty($reviews)): ?>
            <!-- Reviews -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Reviews (<?php echo count($reviews); ?>)</h2>
                </div>
                <div class="card-body">
                    <div class="space-y-4" id="reviews-container">
                        <?php foreach ($reviews as $review): ?>
                        <div class="review-item border rounded p-4" data-review-id="<?php echo $review['id']; ?>">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <span class="font-medium"><?php echo e($review['user_name'] ?? 'Anonymous'); ?></span>
                                    <span class="text-muted text-sm ml-2"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="flex items-center gap-1 text-sm cursor-pointer">
                                        <input type="checkbox" class="form-checkbox review-approved"
                                               <?php echo $review['is_approved'] ? 'checked' : ''; ?>
                                               onchange="toggleReviewApproval(<?php echo $review['id']; ?>, this.checked)">
                                        <span>Approved</span>
                                    </label>
                                    <button type="button" onclick="deleteReview(<?php echo $review['id']; ?>)"
                                            class="btn btn-danger btn-icon btn-sm">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="<?php echo $i <= $review['rating'] ? 'currentColor' : 'none'; ?>"
                                     stroke="currentColor" stroke-width="2" class="text-warning">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                                <?php endfor; ?>
                                <span class="text-sm text-muted ml-2">(<?php echo $review['rating']; ?>/5)</span>
                                <?php if ($review['is_verified']): ?>
                                <span class="badge badge-success ml-2 text-xs">Verified Purchase</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($review['title']): ?>
                            <h4 class="font-medium mb-1"><?php echo e($review['title']); ?></h4>
                            <?php endif; ?>
                            <p class="text-sm"><?php echo e($review['content']); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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

// Specification functions
function addSpecification() {
    var container = document.getElementById('specifications-container');
    var noMsg = document.getElementById('no-specs-msg');
    if (noMsg) noMsg.remove();

    var row = document.createElement('div');
    row.className = 'spec-row flex gap-2 items-start';
    row.innerHTML = '<input type="text" name="spec_name[]" class="form-input flex-1" placeholder="Name (e.g., Weight)">' +
        '<input type="text" name="spec_value[]" class="form-input flex-1" placeholder="Value (e.g., 1.5kg)">' +
        '<button type="button" onclick="removeSpecification(this)" class="btn btn-danger btn-icon btn-sm">' +
        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
        '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>';
    container.appendChild(row);
}

function removeSpecification(btn) {
    btn.closest('.spec-row').remove();
}

// AI Content Generation
function generateAiContent() {
    var btn = document.getElementById('ai-generate-btn');
    var statusDiv = document.getElementById('ai-status');
    var statusText = document.getElementById('ai-status-text');

    <?php if ($product): ?>
    var productId = <?php echo $product->id; ?>;
    var endpoint = '<?php echo url('/admin/products/'); ?>' + productId + '/ai-generate';
    <?php else: ?>
    var productName = document.getElementById('name').value;
    var sku = document.getElementById('sku').value;

    if (!productName) {
        alert('Please enter a product name first');
        return;
    }

    var endpoint = '<?php echo url('/admin/products/ai-search'); ?>';
    <?php endif; ?>

    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin mr-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Generating...';
    statusDiv.classList.remove('hidden');
    statusText.textContent = 'AI is generating content for your product...';

    <?php if ($product): ?>
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'X-CSRF-Token': '<?php echo csrf_token(); ?>',
            'Content-Type': 'application/json'
        }
    })
    <?php else: ?>
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'X-CSRF-Token': '<?php echo csrf_token(); ?>',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name: productName, sku: sku })
    })
    <?php endif; ?>
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg> Generate AI Info';

        if (data.success) {
            statusDiv.classList.add('hidden');
            applyAiContent(data.data);
        } else {
            statusDiv.className = 'alert alert-danger';
            statusText.textContent = 'Error: ' + data.message;
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg> Generate AI Info';
        statusDiv.className = 'alert alert-danger';
        statusText.textContent = 'Error: Failed to connect to AI service';
    });
}

function applyAiContent(data) {
    // Apply SEO content
    if (data.meta_title) {
        document.getElementById('meta_title').value = data.meta_title;
    }
    if (data.meta_description) {
        document.getElementById('meta_description').value = data.meta_description;
    }
    if (data.short_description) {
        document.getElementById('short_description').value = data.short_description;
    }
    if (data.description) {
        document.getElementById('description').value = data.description;
    }

    // Apply specifications
    if (data.specifications && Array.isArray(data.specifications)) {
        var container = document.getElementById('specifications-container');
        var noMsg = document.getElementById('no-specs-msg');
        if (noMsg) noMsg.remove();

        // Clear existing specifications
        container.innerHTML = '';

        data.specifications.forEach(function(spec) {
            var row = document.createElement('div');
            row.className = 'spec-row flex gap-2 items-start';
            row.innerHTML = '<input type="text" name="spec_name[]" value="' + escapeHtml(spec.name) + '" class="form-input flex-1" placeholder="Name">' +
                '<input type="text" name="spec_value[]" value="' + escapeHtml(spec.value) + '" class="form-input flex-1" placeholder="Value">' +
                '<button type="button" onclick="removeSpecification(this)" class="btn btn-danger btn-icon btn-sm">' +
                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>';
            container.appendChild(row);
        });
    }

    alert('AI content has been generated and applied. Please review and save the product.');
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

<?php if ($product): ?>
// Delete product
function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        fetch('<?php echo url('/admin/products/'); ?>' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': '<?php echo csrf_token(); ?>',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?php echo url('/admin/products'); ?>';
            }
        });
    }
}

// Delete image
function deleteImage(id) {
    if (confirm('Delete this image?')) {
        fetch('<?php echo url('/admin/products/images/'); ?>' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': '<?php echo csrf_token(); ?>',
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

// Toggle review approval
function toggleReviewApproval(id, approved) {
    fetch('<?php echo url('/admin/products/reviews/'); ?>' + id, {
        method: 'PUT',
        headers: {
            'X-CSRF-Token': '<?php echo csrf_token(); ?>',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ is_approved: approved })
    });
}

// Delete review
function deleteReview(id) {
    if (confirm('Delete this review?')) {
        fetch('<?php echo url('/admin/products/reviews/'); ?>' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': '<?php echo csrf_token(); ?>',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector('[data-review-id="' + id + '"]').remove();
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
