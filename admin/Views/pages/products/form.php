<!-- Admin Product Form - Enhanced Version with Tabs, Auto-save, Quality Score -->
<?php
$isEdit = !empty($product);
$productId = $isEdit ? $product->id : 0;
$variants = $variants ?? [];
$specifications = $specifications ?? [];
$images = $images ?? [];
$reviews = $reviews ?? [];
$productCategories = $productCategories ?? [];
$productAttributes = $productAttributes ?? [];
?>

<div class="product-form-wrapper">
    <!-- Header with Actions -->
    <div class="product-form-header">
        <div class="flex items-center gap-4">
            <a href="<?= url('/admin/products') ?>" class="btn btn-ghost btn-sm">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                </svg>
            </a>
            <div>
                <h1 class="admin-page-title mb-0"><?= $isEdit ? 'Edit Product' : 'Add Product' ?></h1>
                <?php if ($isEdit): ?>
                <p class="text-sm text-muted">SKU: <?= e($product->sku) ?> | Last updated: <?= date('M j, Y g:i A', strtotime($product->updated_at ?? 'now')) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <!-- Quality Score -->
            <div class="quality-score-badge" id="quality-score" title="Product completeness score">
                <div class="quality-score-ring">
                    <svg viewBox="0 0 36 36">
                        <path class="quality-score-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="quality-score-fill" stroke-dasharray="0, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                    <span class="quality-score-value">0%</span>
                </div>
                <span class="quality-score-label">Quality</span>
            </div>

            <!-- Auto-save indicator -->
            <div class="autosave-indicator" id="autosave-indicator">
                <span class="autosave-icon"></span>
                <span class="autosave-text">All changes saved</span>
            </div>

            <?php if ($isEdit): ?>
            <!-- AI Production Ready Button -->
            <button type="button" onclick="makeProductionReady()" class="btn btn-secondary btn-sm" id="ai-complete-btn" title="Use AI to fill all missing fields and make this product ready to sell">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                </svg>
                AI: Make Production Ready
            </button>

            <!-- Duplicate Button -->
            <button type="button" onclick="duplicateProduct(<?= $productId ?>)" class="btn btn-outline btn-sm" title="Duplicate this product">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                </svg>
                Duplicate
            </button>

            <!-- Preview Button -->
            <a href="<?= url('/products/' . ($product->slug ?? '')) ?>" target="_blank" class="btn btn-outline btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
                Preview
            </a>
            <?php endif; ?>

            <!-- Save Button -->
            <button type="submit" form="product-form" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                </svg>
                <?= $isEdit ? 'Save Changes' : 'Create Product' ?>
            </button>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="product-tabs">
        <button type="button" class="product-tab active" data-tab="basic">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
            </svg>
            Basic Info
        </button>
        <button type="button" class="product-tab" data-tab="pricing">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
            </svg>
            Pricing & Stock
        </button>
        <button type="button" class="product-tab" data-tab="media">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
            </svg>
            Media
            <?php if (count($images) > 0): ?>
            <span class="tab-badge"><?= count($images) ?></span>
            <?php endif; ?>
        </button>
        <button type="button" class="product-tab" data-tab="attributes">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
            </svg>
            Attributes
        </button>
        <button type="button" class="product-tab" data-tab="seo">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            SEO
        </button>
        <?php if ($isEdit && count($reviews) > 0): ?>
        <button type="button" class="product-tab" data-tab="reviews">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            Reviews
            <span class="tab-badge"><?= count($reviews) ?></span>
        </button>
        <?php endif; ?>
    </div>

    <!-- Form -->
    <form id="product-form" action="<?= $isEdit ? url('/admin/products/' . $productId) : url('/admin/products') ?>"
          method="POST" enctype="multipart/form-data" class="product-form">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="product_id" value="<?= $productId ?>">
        <?php endif; ?>

        <div class="product-form-content">
            <!-- Tab: Basic Info -->
            <div class="tab-content active" id="tab-basic">
                <div class="form-grid">
                    <div class="form-main">
                        <div class="card">
                            <div class="card-header flex justify-between items-center">
                                <h2 class="font-semibold">Basic Information</h2>
                                <?php if ($isEdit && !empty($product->sku)): ?>
                                <button type="button" onclick="regenerateFromSku()" class="btn btn-sm btn-secondary" id="ai-sku-btn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1">
                                        <path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
                                    </svg>
                                    AI from SKU
                                </button>
                                <?php endif; ?>
                            </div>
                            <div class="card-body space-y-4">
                                <div class="form-group">
                                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" value="<?= e($product->name ?? '') ?>"
                                           class="form-input" required data-quality="20">
                                    <p class="form-help">Clear, descriptive name that customers will see</p>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                        <div class="input-with-button">
                                            <input type="text" id="sku" name="sku" value="<?= e($product->sku ?? '') ?>"
                                                   class="form-input" <?= $isEdit ? 'readonly' : 'required' ?>>
                                            <?php if (!$isEdit): ?>
                                            <button type="button" onclick="generateSku()" class="btn btn-outline btn-sm" title="Auto-generate SKU">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.778 7.778 5.5 5.5 0 017.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                                                </svg>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($isEdit): ?>
                                        <p class="form-help">SKU cannot be changed after creation</p>
                                        <?php else: ?>
                                        <p class="form-help">Unique product identifier. Click button to auto-generate.</p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="barcode" class="form-label">Barcode / EAN</label>
                                        <input type="text" id="barcode" name="barcode" value="<?= e($product->barcode ?? '') ?>"
                                               class="form-input" placeholder="e.g., 5901234123457">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="short_description" class="form-label">Short Description</label>
                                    <textarea id="short_description" name="short_description" rows="5" maxlength="500"
                                              class="form-input seo-field" data-max-length="500" data-quality="15"
                                              oninput="updateCharCount(this)"><?= e($product->short_description ?? '') ?></textarea>
                                    <div class="flex justify-between text-sm mt-1">
                                        <span class="text-muted">4-5 bullet points highlighting key features</span>
                                        <span class="char-counter" data-for="short_description">
                                            <span class="char-count"><?= strlen($product->short_description ?? '') ?></span>/500
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description" class="form-label">Full Description</label>
                                    <textarea id="description" name="description" rows="10"
                                              class="form-input tinymce-editor" data-quality="20"><?= e($product->description ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-sidebar">
                        <!-- Status Card -->
                        <div class="card">
                            <div class="card-header"><h2 class="font-semibold">Status</h2></div>
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
                                        <option value="digital" <?= ($product->type ?? '') === 'digital' ? 'selected' : '' ?>>Digital</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="card">
                            <div class="card-header"><h2 class="font-semibold">Categories</h2></div>
                            <div class="card-body">
                                <div class="categories-tree max-h-64 overflow-y-auto">
                                    <?php
                                    function renderCategoryTree($categories, $selected, $level = 0) {
                                        foreach ($categories as $cat) {
                                            $checked = in_array($cat['id'], $selected) ? 'checked' : '';
                                            $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
                                            echo '<label class="category-checkbox">';
                                            echo '<input type="checkbox" name="categories[]" value="'.$cat['id'].'" '.$checked.' data-quality="10">';
                                            echo '<span>'.$indent.e($cat['name']).'</span>';
                                            echo '</label>';
                                            if (!empty($cat['children'])) {
                                                renderCategoryTree($cat['children'], $selected, $level + 1);
                                            }
                                        }
                                    }
                                    renderCategoryTree($categories ?? [], $productCategories);
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Flags -->
                        <div class="card">
                            <div class="card-header"><h2 class="font-semibold">Product Flags</h2></div>
                            <div class="card-body space-y-3">
                                <label class="toggle-checkbox">
                                    <input type="checkbox" name="is_featured" value="1" <?= !empty($product->is_featured) ? 'checked' : '' ?>>
                                    <span class="toggle-switch"></span>
                                    <span>Featured Product</span>
                                </label>
                                <label class="toggle-checkbox">
                                    <input type="checkbox" name="is_new" value="1" <?= !empty($product->is_new) ? 'checked' : '' ?>>
                                    <span class="toggle-switch"></span>
                                    <span>New Arrival</span>
                                </label>
                                <label class="toggle-checkbox">
                                    <input type="checkbox" name="is_on_sale" value="1" <?= !empty($product->is_on_sale) ? 'checked' : '' ?>>
                                    <span class="toggle-switch"></span>
                                    <span>On Sale</span>
                                </label>
                            </div>
                        </div>

                        <?php if (!empty($vendors)): ?>
                        <!-- Vendor -->
                        <div class="card">
                            <div class="card-header"><h2 class="font-semibold">Vendor</h2></div>
                            <div class="card-body">
                                <select id="vendor_id" name="vendor_id" class="form-select">
                                    <option value="">-- No Vendor --</option>
                                    <?php foreach ($vendors as $v): ?>
                                    <option value="<?= $v['id'] ?>" <?= ($product->vendor_id ?? '') == $v['id'] ? 'selected' : '' ?>><?= e($v['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tab: Pricing & Stock -->
            <div class="tab-content" id="tab-pricing">
                <div class="form-grid">
                    <div class="form-main">
                        <!-- Pricing -->
                        <div class="card">
                            <div class="card-header"><h2 class="font-semibold">Pricing</h2></div>
                            <div class="card-body">
                                <div class="grid md:grid-cols-3 gap-4">
                                    <div class="form-group">
                                        <label for="price" class="form-label">Selling Price <span class="text-danger">*</span></label>
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
                                        <p class="form-help">Original price to show discount</p>
                                    </div>
                                    <div class="form-group">
                                        <label for="cost_price" class="form-label">Cost Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">R</span>
                                            <input type="number" id="cost_price" name="cost_price" step="0.01" min="0"
                                                   value="<?= $product->cost_price ?? '' ?>" class="form-input">
                                        </div>
                                        <p class="form-help">For profit calculations</p>
                                    </div>
                                </div>

                                <!-- Profit Calculator -->
                                <div class="profit-calculator mt-4 p-4 bg-neutral-50 rounded-lg" id="profit-calculator">
                                    <div class="grid grid-cols-3 gap-4 text-center">
                                        <div>
                                            <p class="text-sm text-muted">Profit</p>
                                            <p class="text-xl font-bold text-success" id="calc-profit">R0.00</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-muted">Margin</p>
                                            <p class="text-xl font-bold" id="calc-margin">0%</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-muted">Markup</p>
                                            <p class="text-xl font-bold" id="calc-markup">0%</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inventory -->
                        <div class="card">
                            <div class="card-header"><h2 class="font-semibold">Inventory</h2></div>
                            <div class="card-body space-y-4">
                                <label class="toggle-checkbox">
                                    <input type="checkbox" name="manage_stock" value="1" id="manage_stock_toggle"
                                           <?= !empty($product->manage_stock) ? 'checked' : '' ?>>
                                    <span class="toggle-switch"></span>
                                    <span>Track stock quantity</span>
                                </label>

                                <div id="stock_fields" class="grid md:grid-cols-3 gap-4" style="<?= empty($product->manage_stock) ? 'display:none' : '' ?>">
                                    <div class="form-group">
                                        <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                        <input type="number" id="stock_quantity" name="stock_quantity" min="0"
                                               value="<?= $product->stock_quantity ?? 0 ?>" class="form-input">
                                    </div>
                                    <div class="form-group">
                                        <label for="low_stock_threshold" class="form-label">Low Stock Alert</label>
                                        <input type="number" id="low_stock_threshold" name="low_stock_threshold" min="0"
                                               value="<?= $product->low_stock_threshold ?? 5 ?>" class="form-input">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Allow Backorders</label>
                                        <select name="backorders_allowed" class="form-select">
                                            <option value="0" <?= empty($product->backorders_allowed) ? 'selected' : '' ?>>No</option>
                                            <option value="1" <?= !empty($product->backorders_allowed) ? 'selected' : '' ?>>Yes</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping -->
                        <div class="card">
                            <div class="card-header"><h2 class="font-semibold">Shipping</h2></div>
                            <div class="card-body">
                                <div class="grid md:grid-cols-4 gap-4">
                                    <div class="form-group">
                                        <label for="weight" class="form-label">Weight (kg)</label>
                                        <input type="number" id="weight" name="weight" step="0.01" min="0"
                                               value="<?= $product->weight ?? '' ?>" class="form-input" placeholder="0.5">
                                    </div>
                                    <div class="form-group">
                                        <label for="length" class="form-label">Length (cm)</label>
                                        <input type="number" id="length" name="length" step="0.1" min="0"
                                               value="<?= $product->length ?? '' ?>" class="form-input" placeholder="20">
                                    </div>
                                    <div class="form-group">
                                        <label for="width" class="form-label">Width (cm)</label>
                                        <input type="number" id="width" name="width" step="0.1" min="0"
                                               value="<?= $product->width ?? '' ?>" class="form-input" placeholder="15">
                                    </div>
                                    <div class="form-group">
                                        <label for="height" class="form-label">Height (cm)</label>
                                        <input type="number" id="height" name="height" step="0.1" min="0"
                                               value="<?= $product->height ?? '' ?>" class="form-input" placeholder="10">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Variants Section -->
                        <div class="card" id="variants-section" style="<?= ($product->type ?? 'simple') !== 'variable' ? 'display:none' : '' ?>">
                            <div class="card-header flex justify-between items-center">
                                <h2 class="font-semibold">Product Variants</h2>
                                <button type="button" id="add-variant-btn" class="btn btn-primary btn-sm">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    Add Variant
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="variants-container">
                                    <?php if (empty($variants)): ?>
                                    <p class="text-muted text-center py-8" id="no-variants-msg">No variants yet. Click "Add Variant" to create variations.</p>
                                    <?php endif; ?>
                                    <?php foreach ($variants as $idx => $variant): ?>
                                    <div class="variant-item" data-variant-id="<?= $variant['id'] ?>">
                                        <div class="variant-header">
                                            <h4>Variant #<?= $idx + 1 ?></h4>
                                            <button type="button" class="btn btn-ghost btn-sm text-danger remove-variant-btn">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                                </svg>
                                            </button>
                                        </div>
                                        <input type="hidden" name="variants[<?= $idx ?>][id]" value="<?= $variant['id'] ?>">
                                        <div class="grid md:grid-cols-4 gap-4">
                                            <div class="form-group">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="variants[<?= $idx ?>][name]" value="<?= e($variant['name'] ?? '') ?>" class="form-input" placeholder="Red / Large">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">SKU</label>
                                                <input type="text" name="variants[<?= $idx ?>][sku]" value="<?= e($variant['sku']) ?>" class="form-input">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Price (R)</label>
                                                <input type="number" name="variants[<?= $idx ?>][price]" value="<?= $variant['price'] ?>" class="form-input" step="0.01" min="0">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Stock</label>
                                                <input type="number" name="variants[<?= $idx ?>][stock_quantity]" value="<?= $variant['stock_quantity'] ?>" class="form-input" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-sidebar"></div>
                </div>
            </div>

            <!-- Tab: Media -->
            <div class="tab-content" id="tab-media">
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <h2 class="font-semibold">Product Images</h2>
                        <div class="flex items-center gap-3">
                            <?php if (!empty($product->id)): ?>
                            <button type="button" onclick="generateAiImages()" class="btn btn-outline btn-sm" id="btn-ai-images" title="Generate 4 AI product info cards">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                                </svg>
                                AI Generate Images
                            </button>
                            <?php endif; ?>
                            <span class="text-sm text-muted">Drag to reorder</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($images)): ?>
                        <div id="sortable-images" class="product-images-grid" data-quality="20">
                            <?php foreach ($images as $idx => $image): ?>
                            <div class="product-image-item" draggable="true" data-image-id="<?= $image['id'] ?>">
                                <img src="<?= url('storage/uploads/' . e($image['path'])) ?>" alt="" class="<?= $image['is_primary'] ? 'is-primary' : '' ?>">
                                <?php if ($image['is_primary']): ?>
                                <span class="image-badge primary">Primary</span>
                                <?php else: ?>
                                <button type="button" onclick="setPrimaryImage(<?= $image['id'] ?>)" class="image-action set-primary">Set Primary</button>
                                <?php endif; ?>
                                <button type="button" onclick="deleteImage(<?= $image['id'] ?>)" class="image-action delete">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                    </svg>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-muted text-center py-4">No images uploaded yet</p>
                        <?php endif; ?>

                        <div class="upload-zone mt-4" id="image-upload-zone">
                            <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="upload-input">
                            <div class="upload-content">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                                </svg>
                                <p><strong>Click to upload</strong> or drag and drop</p>
                                <p class="text-sm text-muted">PNG, JPG or WebP (max 5MB each)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Attributes -->
            <div class="tab-content" id="tab-attributes">
                <div class="form-grid">
                    <div class="form-main">
                        <!-- Specifications -->
                        <div class="card">
                            <div class="card-header flex justify-between items-center">
                                <h2 class="font-semibold">Specifications</h2>
                                <button type="button" onclick="addSpecification()" class="btn btn-sm btn-outline">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    Add Spec
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="specifications-container" class="space-y-3" data-quality="10">
                                    <?php if (empty($specifications)): ?>
                                    <p class="text-muted text-sm" id="no-specs-msg">No specifications added yet.</p>
                                    <?php else: ?>
                                    <?php foreach ($specifications as $spec): ?>
                                    <div class="spec-row">
                                        <input type="text" name="spec_name[]" value="<?= e($spec['spec_name']) ?>" class="form-input" placeholder="Name">
                                        <input type="text" name="spec_value[]" value="<?= e($spec['spec_value']) ?>" class="form-input" placeholder="Value">
                                        <button type="button" onclick="removeSpecification(this)" class="btn btn-danger btn-icon btn-sm">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($attributes)): ?>
                        <!-- Product Attributes -->
                        <div class="card">
                            <div class="card-header"><h2 class="font-semibold">Attributes</h2></div>
                            <div class="card-body space-y-4">
                                <?php foreach ($attributes as $attr):
                                    $selectedValue = $productAttributes[$attr['id']]['value_id'] ?? '';
                                    $customValue = $productAttributes[$attr['id']]['custom_value'] ?? '';
                                ?>
                                <div class="form-group">
                                    <label class="form-label"><?= e($attr['name']) ?></label>
                                    <?php if ($attr['type'] === 'text'): ?>
                                    <input type="hidden" name="attribute_values[<?= $attr['id'] ?>]" value="">
                                    <input type="text" name="attribute_custom[<?= $attr['id'] ?>]" value="<?= e($customValue) ?>" class="form-input" placeholder="Enter <?= strtolower($attr['name']) ?>">
                                    <?php elseif ($attr['type'] === 'color'): ?>
                                    <div class="color-options">
                                        <?php foreach ($attr['values'] as $val): ?>
                                        <label class="color-option" title="<?= e($val['value']) ?>">
                                            <input type="radio" name="attribute_values[<?= $attr['id'] ?>]" value="<?= $val['id'] ?>" <?= $selectedValue == $val['id'] ? 'checked' : '' ?>>
                                            <span class="color-swatch <?= $selectedValue == $val['id'] ? 'selected' : '' ?>" style="background-color: <?= e($val['color_code'] ?? '#ccc') ?>"></span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php else: ?>
                                    <select name="attribute_values[<?= $attr['id'] ?>]" class="form-select">
                                        <option value="">-- Select --</option>
                                        <?php foreach ($attr['values'] as $val): ?>
                                        <option value="<?= $val['id'] ?>" <?= $selectedValue == $val['id'] ? 'selected' : '' ?>><?= e($val['value']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-sidebar"></div>
                </div>
            </div>

            <!-- Tab: SEO -->
            <div class="tab-content" id="tab-seo">
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <h2 class="font-semibold">Search Engine Optimization</h2>
                        <button type="button" onclick="generateAiContent()" class="btn btn-sm btn-primary" id="ai-generate-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                            </svg>
                            Generate AI Content
                        </button>
                    </div>
                    <div class="card-body space-y-4">
                        <div id="ai-status" class="alert alert-info hidden">
                            <span id="ai-status-text">Generating content...</span>
                        </div>

                        <div class="form-group">
                            <label for="meta_title" class="form-label">Meta Title</label>
                            <input type="text" id="meta_title" name="meta_title" value="<?= e($product->meta_title ?? '') ?>"
                                   class="form-input seo-field" maxlength="70" data-max-length="70" data-quality="10" oninput="updateCharCount(this)">
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-muted">Leave empty to use product name</span>
                                <span class="char-counter" data-for="meta_title"><span class="char-count"><?= strlen($product->meta_title ?? '') ?></span>/70</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea id="meta_description" name="meta_description" rows="3" class="form-input seo-field"
                                      maxlength="160" data-max-length="160" data-quality="10" oninput="updateCharCount(this)"><?= e($product->meta_description ?? '') ?></textarea>
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-muted">Leave empty to use short description</span>
                                <span class="char-counter" data-for="meta_description"><span class="char-count"><?= strlen($product->meta_description ?? '') ?></span>/160</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta_keywords" class="form-label">Meta Keywords</label>
                            <input type="text" id="meta_keywords" name="meta_keywords" value="<?= e($product->meta_keywords ?? '') ?>"
                                   class="form-input" maxlength="255" placeholder="e.g., processor, intel, gaming, desktop">
                            <p class="form-help">Comma-separated keywords for SEO (auto-filled by AI)</p>
                        </div>

                        <!-- SEO Preview -->
                        <div class="seo-preview">
                            <p class="text-sm text-muted mb-2">Search Engine Preview</p>
                            <div class="seo-preview-box">
                                <p class="seo-preview-title" id="seo-preview-title"><?= e($product->meta_title ?? $product->name ?? 'Product Title') ?></p>
                                <p class="seo-preview-url"><?= url('/products/' . ($product->slug ?? 'product-name')) ?></p>
                                <p class="seo-preview-desc" id="seo-preview-desc"><?= e($product->meta_description ?? $product->short_description ?? 'Product description will appear here...') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Reviews -->
            <?php if ($isEdit && !empty($reviews)): ?>
            <div class="tab-content" id="tab-reviews">
                <div class="card">
                    <div class="card-header"><h2 class="font-semibold">Customer Reviews (<?= count($reviews) ?>)</h2></div>
                    <div class="card-body">
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                            <div class="review-item" data-review-id="<?= $review['id'] ?>">
                                <div class="review-header">
                                    <div>
                                        <span class="font-medium"><?= e($review['user_name'] ?? 'Anonymous') ?></span>
                                        <span class="text-muted text-sm ml-2"><?= date('M j, Y', strtotime($review['created_at'])) ?></span>
                                        <?php if ($review['is_verified']): ?>
                                        <span class="badge badge-success ml-2">Verified Purchase</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="review-actions">
                                        <label class="toggle-checkbox small">
                                            <input type="checkbox" <?= $review['is_approved'] ? 'checked' : '' ?> onchange="toggleReviewApproval(<?= $review['id'] ?>, this.checked)">
                                            <span class="toggle-switch"></span>
                                            <span>Approved</span>
                                        </label>
                                        <button type="button" onclick="deleteReview(<?= $review['id'] ?>)" class="btn btn-danger btn-icon btn-sm">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="<?= $i <= $review['rating'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" class="text-warning">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                    </svg>
                                    <?php endfor; ?>
                                </div>
                                <?php if ($review['title']): ?>
                                <h4 class="font-medium"><?= e($review['title']) ?></h4>
                                <?php endif; ?>
                                <p class="text-sm"><?= e($review['content']) ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="delete-modal">
    <div class="modal-backdrop" onclick="closeDeleteModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Delete Product</h3>
            <button type="button" onclick="closeDeleteModal()" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this product?</p>
            <p class="text-muted text-sm mt-2">This will move the product to trash. You can restore it later.</p>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeDeleteModal()" class="btn btn-ghost">Cancel</button>
            <button type="button" onclick="confirmDelete()" class="btn btn-danger">Delete Product</button>
        </div>
    </div>
</div>

<script>
// Configuration
const productId = <?= $productId ?>;
const csrfToken = '<?= csrf_token() ?>';
const baseUrl = '<?= url('/admin/products') ?>';
let autoSaveTimer = null;
let formChanged = false;
let variantIndex = <?= count($variants) ?>;

// Tab Navigation
document.querySelectorAll('.product-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const tabId = this.dataset.tab;
        document.querySelectorAll('.product-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    });
});

// Auto-save
function initAutoSave() {
    const form = document.getElementById('product-form');
    form.querySelectorAll('input, textarea, select').forEach(el => {
        el.addEventListener('change', scheduleAutoSave);
        el.addEventListener('input', scheduleAutoSave);
    });
}

function scheduleAutoSave() {
    formChanged = true;
    updateAutoSaveIndicator('unsaved');
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(autoSave, 3000);
}

function autoSave() {
    if (!productId || !formChanged) return;
    updateAutoSaveIndicator('saving');

    const form = document.getElementById('product-form');
    const formData = new FormData(form);
    formData.append('auto_save', '1');

    fetch(baseUrl + '/' + productId + '/autosave', {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-Token': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        formChanged = false;
        updateAutoSaveIndicator(data.success ? 'saved' : 'error');
    })
    .catch(() => updateAutoSaveIndicator('error'));
}

function updateAutoSaveIndicator(status) {
    const indicator = document.getElementById('autosave-indicator');
    const text = indicator.querySelector('.autosave-text');
    indicator.className = 'autosave-indicator ' + status;
    switch(status) {
        case 'saving': text.textContent = 'Saving...'; break;
        case 'saved': text.textContent = 'All changes saved'; break;
        case 'unsaved': text.textContent = 'Unsaved changes'; break;
        case 'error': text.textContent = 'Save failed'; break;
    }
}

// Quality Score
function calculateQualityScore() {
    let score = 0;
    let missing = [];

    // Check name (20 points)
    if (document.getElementById('name').value.trim()) score += 20;
    else missing.push('Product name');

    // Check description (20 points)
    const desc = document.getElementById('description').value.trim();
    if (desc && desc.length > 50) score += 20;
    else missing.push('Description');

    // Check short description (15 points)
    if (document.getElementById('short_description').value.trim()) score += 15;
    else missing.push('Short description');

    // Check images (20 points)
    const images = document.querySelectorAll('#sortable-images .product-image-item');
    if (images.length > 0) score += 10;
    if (images.length > 1) score += 10;
    else missing.push('2nd image');

    // Check meta title (10 points)
    if (document.getElementById('meta_title').value.trim()) score += 10;
    else missing.push('Meta title');

    // Check meta description (10 points)
    if (document.getElementById('meta_description').value.trim()) score += 10;
    else missing.push('Meta description');

    // Check category (5 points)
    if (document.querySelector('input[name="categories[]"]:checked')) score += 5;
    else missing.push('Category');

    updateQualityDisplay(score, missing);
}

function updateQualityDisplay(score, missing) {
    const ring = document.querySelector('.quality-score-fill');
    const value = document.querySelector('.quality-score-value');
    const badge = document.getElementById('quality-score');

    ring.style.strokeDasharray = score + ', 100';
    value.textContent = score + '%';

    badge.classList.remove('poor', 'fair', 'good', 'excellent');
    if (score < 40) badge.classList.add('poor');
    else if (score < 60) badge.classList.add('fair');
    else if (score < 80) badge.classList.add('good');
    else badge.classList.add('excellent');

    badge.title = 'Quality: ' + score + '%' + (missing.length ? '\nMissing: ' + missing.join(', ') : '');
}

// Character Counter
function updateCharCount(element) {
    const maxLength = parseInt(element.dataset.maxLength || 160);
    const currentLength = element.value.length;
    const counter = document.querySelector('[data-for="' + element.id + '"] .char-count');
    if (counter) {
        counter.textContent = currentLength;
        const parent = counter.parentNode;
        parent.classList.remove('text-success', 'text-warning', 'text-danger');
        if (currentLength <= maxLength * 0.7) parent.classList.add('text-success');
        else if (currentLength <= maxLength * 0.9) parent.classList.add('text-warning');
        else parent.classList.add('text-danger');
    }
    updateSeoPreview();
}

// SEO Preview
function updateSeoPreview() {
    const title = document.getElementById('meta_title').value || document.getElementById('name').value || 'Product Title';
    const desc = document.getElementById('meta_description').value || document.getElementById('short_description').value || 'Description...';
    document.getElementById('seo-preview-title').textContent = title.substring(0, 70);
    document.getElementById('seo-preview-desc').textContent = desc.substring(0, 160);
}

// Profit Calculator
function updateProfitCalculator() {
    const price = parseFloat(document.getElementById('price').value) || 0;
    const cost = parseFloat(document.getElementById('cost_price').value) || 0;

    const profit = price - cost;
    const margin = price > 0 ? (profit / price * 100) : 0;
    const markup = cost > 0 ? (profit / cost * 100) : 0;

    document.getElementById('calc-profit').textContent = 'R' + profit.toFixed(2);
    document.getElementById('calc-margin').textContent = margin.toFixed(1) + '%';
    document.getElementById('calc-markup').textContent = markup.toFixed(1) + '%';

    document.getElementById('calc-profit').className = profit >= 0 ? 'text-xl font-bold text-success' : 'text-xl font-bold text-danger';
}

// Stock Toggle
document.getElementById('manage_stock_toggle').addEventListener('change', function() {
    document.getElementById('stock_fields').style.display = this.checked ? '' : 'none';
});

// Product Type Toggle
document.getElementById('type').addEventListener('change', function() {
    document.getElementById('variants-section').style.display = this.value === 'variable' ? '' : 'none';
});

// Variants
document.getElementById('add-variant-btn')?.addEventListener('click', addVariant);

function addVariant() {
    const container = document.getElementById('variants-container');
    document.getElementById('no-variants-msg')?.remove();

    const idx = variantIndex++;
    const html = `<div class="variant-item" data-variant-idx="${idx}">
        <div class="variant-header">
            <h4>New Variant</h4>
            <button type="button" class="btn btn-ghost btn-sm text-danger" onclick="removeVariant(this)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                </svg>
            </button>
        </div>
        <div class="grid md:grid-cols-4 gap-4">
            <div class="form-group"><label class="form-label">Name</label><input type="text" name="variants[${idx}][name]" class="form-input" placeholder="Red / Large"></div>
            <div class="form-group"><label class="form-label">SKU</label><input type="text" name="variants[${idx}][sku]" class="form-input"></div>
            <div class="form-group"><label class="form-label">Price (R)</label><input type="number" name="variants[${idx}][price]" class="form-input" step="0.01" min="0"></div>
            <div class="form-group"><label class="form-label">Stock</label><input type="number" name="variants[${idx}][stock_quantity]" class="form-input" min="0" value="0"></div>
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
}

function removeVariant(btn) {
    const item = btn.closest('.variant-item');
    const variantId = item.dataset.variantId;
    if (variantId) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_variants[]';
        input.value = variantId;
        document.getElementById('product-form').appendChild(input);
    }
    item.remove();
}

// Specifications
function addSpecification() {
    document.getElementById('no-specs-msg')?.remove();
    const container = document.getElementById('specifications-container');
    const html = `<div class="spec-row">
        <input type="text" name="spec_name[]" class="form-input" placeholder="Name">
        <input type="text" name="spec_value[]" class="form-input" placeholder="Value">
        <button type="button" onclick="removeSpecification(this)" class="btn btn-danger btn-icon btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
}

function removeSpecification(btn) {
    btn.closest('.spec-row').remove();
}

// SKU Generation
function generateSku() {
    const name = document.getElementById('name').value;
    const prefix = name ? name.substring(0, 3).toUpperCase() : 'PRD';
    const random = Math.random().toString(36).substring(2, 8).toUpperCase();
    document.getElementById('sku').value = prefix + '-' + random;
}

// Product Duplication
function duplicateProduct(id) {
    if (!confirm('Create a duplicate of this product?')) return;

    fetch(baseUrl + '/' + id + '/duplicate', {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrfToken, 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = baseUrl + '/' + data.product_id + '/edit';
        } else {
            alert('Failed to duplicate: ' + (data.message || 'Unknown error'));
        }
    });
}

// Delete Product
function deleteProduct() {
    document.getElementById('delete-modal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('delete-modal').classList.remove('active');
}

function confirmDelete() {
    fetch(baseUrl + '/' + productId, {
        method: 'DELETE',
        headers: { 'X-CSRF-Token': csrfToken, 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = baseUrl;
        }
    });
}

// Image Management
function setPrimaryImage(imageId) {
    fetch(baseUrl + '/images/' + imageId + '/primary', {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrfToken }
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); });
}

function deleteImage(imageId) {
    if (!confirm('Delete this image?')) return;
    fetch(baseUrl + '/images/' + imageId, {
        method: 'DELETE',
        headers: { 'X-CSRF-Token': csrfToken }
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); });
}

// Review Management
function toggleReviewApproval(id, approved) {
    fetch('<?= url('/admin/products/reviews/') ?>' + id, {
        method: 'PUT',
        headers: { 'X-CSRF-Token': csrfToken, 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_approved: approved })
    });
}

function deleteReview(id) {
    if (!confirm('Delete this review?')) return;
    fetch('<?= url('/admin/products/reviews/') ?>' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-Token': csrfToken }
    })
    .then(r => r.json())
    .then(data => { if (data.success) document.querySelector('[data-review-id="' + id + '"]').remove(); });
}

// AI Content Generation
function generateAiContent() {
    const btn = document.getElementById('ai-generate-btn');
    const status = document.getElementById('ai-status');
    const statusText = document.getElementById('ai-status-text');

    btn.disabled = true;
    status.classList.remove('hidden');
    statusText.textContent = 'Generating AI content...';

    fetch(baseUrl + '/' + productId + '/ai-generate', {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            status.classList.add('hidden');
            const d = data.data;

            // Helper to set field value and fire events
            function setField(id, value) {
                const field = document.getElementById(id);
                if (field && value) {
                    field.value = value;
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // Apply ALL returned fields
            setField('meta_title', d.meta_title);
            setField('meta_description', d.meta_description);
            setField('meta_keywords', d.meta_keywords);
            setField('short_description', d.short_description);
            setField('description', d.description);

            updateSeoPreview();
            calculateQualityScore();
            document.querySelectorAll('.seo-field').forEach(f => updateCharCount(f));
            alert('AI content generated! Review and save.');
        } else {
            statusText.textContent = 'Error: ' + data.message;
            status.className = 'alert alert-danger';
        }
    })
    .catch(err => {
        btn.disabled = false;
        statusText.textContent = 'Error: ' + err.message;
        status.className = 'alert alert-danger';
    });
}

function regenerateFromSku() {
    const btn = document.getElementById('ai-sku-btn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin" width="16" height="16" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/></svg> Analyzing...';

    fetch(baseUrl + '/' + productId + '/ai-regenerate-sku', {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg> AI from SKU';
        if (data.success && data.data) {
            const d = data.data;

            // Helper to set field value and fire events
            function setField(id, value) {
                const field = document.getElementById(id);
                if (field && value) {
                    field.value = value;
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // Apply all AI-generated fields
            setField('name', d.name);
            setField('short_description', d.short_description);
            setField('description', d.description);
            setField('meta_title', d.meta_title);
            setField('meta_description', d.meta_description);
            setField('meta_keywords', d.meta_keywords);
            setField('weight', d.weight);

            // Apply specifications if returned
            if (d.specifications && Array.isArray(d.specifications) && d.specifications.length > 0) {
                const container = document.getElementById('specifications-container');
                if (container) {
                    const noSpecsMsg = document.getElementById('no-specs-msg');
                    if (noSpecsMsg) noSpecsMsg.remove();

                    const existingNames = new Set();
                    container.querySelectorAll('input[name="spec_name[]"]').forEach(input => {
                        if (input.value.trim()) existingNames.add(input.value.trim().toLowerCase());
                    });

                    d.specifications.forEach(spec => {
                        const specName = spec.name || spec.spec_name || '';
                        const specValue = spec.value || spec.spec_value || '';
                        if (!specName || !specValue) return;
                        if (existingNames.has(specName.toLowerCase())) return;

                        const row = document.createElement('div');
                        row.className = 'spec-row';
                        row.innerHTML = `
                            <input type="text" name="spec_name[]" value="${specName.replace(/"/g, '&quot;')}" class="form-input" placeholder="Name">
                            <input type="text" name="spec_value[]" value="${specValue.replace(/"/g, '&quot;')}" class="form-input" placeholder="Value">
                            <button type="button" onclick="removeSpecification(this)" class="btn btn-danger btn-icon btn-sm">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        `;
                        container.appendChild(row);
                    });
                }
            }

            updateSeoPreview();
            calculateQualityScore();
            document.querySelectorAll('.seo-field').forEach(f => updateCharCount(f));
            alert('Product identified from SKU and all fields generated! Review and save.');
        } else {
            alert('Error: ' + (data.message || 'Could not identify product from SKU'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg> AI from SKU';
        alert('Error: ' + err.message);
    });
}

// AI: Make Production Ready
function makeProductionReady() {
    const btn = document.getElementById('ai-complete-btn');
    if (!btn || !productId) return;

    if (!confirm('This will use AI to fill all missing product fields (name, descriptions, SEO, specifications, category, weight). Continue?')) return;

    btn.disabled = true;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<svg class="animate-spin" width="16" height="16" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="30 70"/></svg> Generating...';

    fetch(baseUrl + '/' + productId + '/ai-complete', {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;

        if (data.success && data.data) {
            const d = data.data;

            // Helper to set field value and fire events for auto-save
            function setField(id, value) {
                const field = document.getElementById(id);
                if (field && value) {
                    field.value = value;
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // Apply ALL AI-generated fields
            setField('name', d.name);
            setField('short_description', d.short_description);
            setField('description', d.description);
            setField('meta_title', d.meta_title);
            setField('meta_description', d.meta_description);
            setField('meta_keywords', d.meta_keywords);
            setField('weight', d.weight);

            // Apply specifications to the Attributes tab
            if (d.specifications && Array.isArray(d.specifications) && d.specifications.length > 0) {
                const container = document.getElementById('specifications-container');
                if (container) {
                    // Remove the "no specs" placeholder
                    const noSpecsMsg = document.getElementById('no-specs-msg');
                    if (noSpecsMsg) noSpecsMsg.remove();

                    // Clear existing empty specs, keep ones with values
                    const existingRows = container.querySelectorAll('.spec-row');
                    existingRows.forEach(row => {
                        const nameInput = row.querySelector('input[name="spec_name[]"]');
                        const valueInput = row.querySelector('input[name="spec_value[]"]');
                        if (nameInput && valueInput && !nameInput.value.trim() && !valueInput.value.trim()) {
                            row.remove();
                        }
                    });

                    // Collect existing spec names to avoid duplicates
                    const existingNames = new Set();
                    container.querySelectorAll('input[name="spec_name[]"]').forEach(input => {
                        if (input.value.trim()) existingNames.add(input.value.trim().toLowerCase());
                    });

                    // Add new specs from AI
                    d.specifications.forEach(spec => {
                        const specName = spec.name || spec.spec_name || '';
                        const specValue = spec.value || spec.spec_value || '';
                        if (!specName || !specValue) return;
                        if (existingNames.has(specName.toLowerCase())) return;

                        const row = document.createElement('div');
                        row.className = 'spec-row';
                        row.innerHTML = `
                            <input type="text" name="spec_name[]" value="${specName.replace(/"/g, '&quot;')}" class="form-input" placeholder="Name">
                            <input type="text" name="spec_value[]" value="${specValue.replace(/"/g, '&quot;')}" class="form-input" placeholder="Value">
                            <button type="button" onclick="removeSpecification(this)" class="btn btn-danger btn-icon btn-sm">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        `;
                        container.appendChild(row);
                    });
                }
            }

            // Update UI
            updateSeoPreview();
            calculateQualityScore();
            document.querySelectorAll('.seo-field').forEach(f => updateCharCount(f));

            const updatedFields = data.updates_applied || [];
            let msg = 'Product enhanced! ' + (data.message || updatedFields.length + ' fields updated.');
            if (d.specifications && d.specifications.length > 0) {
                msg += '\n' + d.specifications.length + ' specifications added.';
            }
            if (data.images_generated > 0) {
                msg += '\n' + data.images_generated + ' images downloaded.';
            }
            msg += '\n\nReview the changes and click Save to keep them.';
            alert(msg);

            // If images were generated, reload the media section
            if (data.images_generated > 0) {
                window.location.reload();
            }
        } else {
            alert('Error: ' + (data.message || 'AI generation failed'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        alert('Error: ' + err.message);
    });
}

// AI: Generate Product Images
function generateAiImages() {
    const btn = document.getElementById('btn-ai-images');
    if (!btn || !productId) return;

    if (!confirm('Generate AI product info-card images? This will create up to 4 branded images for this product.')) return;

    btn.disabled = true;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<svg class="animate-spin" width="14" height="14" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="30 70"/></svg> Generating...';

    fetch(baseUrl + '/' + productId + '/ai-images', {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;

        if (data.success) {
            alert(data.message + '\n\nRefresh the page to see the new images.');
            if (data.generated > 0) {
                window.location.reload();
            }
        } else {
            alert('Error: ' + (data.message || 'Image generation failed'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        alert('Error: ' + err.message);
    });
}

// Keyboard Shortcuts
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        document.getElementById('product-form').submit();
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initAutoSave();
    calculateQualityScore();
    updateProfitCalculator();
    document.querySelectorAll('.seo-field').forEach(f => updateCharCount(f));

    // Price/cost change listeners
    document.getElementById('price').addEventListener('input', updateProfitCalculator);
    document.getElementById('cost_price').addEventListener('input', updateProfitCalculator);

    // Quality score updates
    document.querySelectorAll('[data-quality]').forEach(el => {
        el.addEventListener('change', calculateQualityScore);
        el.addEventListener('input', calculateQualityScore);
    });
});
</script>

<style>
/* Product Form Styles - Admin Dark Theme */
.product-form-wrapper { max-width: 1400px; margin: 0 auto; }
.product-form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding: 1rem; background: var(--admin-bg-card, #27272a); border-radius: var(--admin-radius, 10px); }

/* Quality Score */
.quality-score-badge { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; }
.quality-score-ring { position: relative; width: 40px; height: 40px; }
.quality-score-ring svg { transform: rotate(-90deg); }
.quality-score-bg { fill: none; stroke: var(--admin-border, #27272a); stroke-width: 3; }
.quality-score-fill { fill: none; stroke: var(--admin-primary, #8B2B2B); stroke-width: 3; stroke-linecap: round; transition: stroke-dasharray 0.3s; }
.quality-score-value { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.65rem; font-weight: 600; color: var(--admin-text, #fafafa); }
.quality-score-badge.poor .quality-score-fill { stroke: var(--admin-danger, #ef4444); }
.quality-score-badge.fair .quality-score-fill { stroke: var(--admin-warning, #f59e0b); }
.quality-score-badge.good .quality-score-fill { stroke: var(--admin-info, #3b82f6); }
.quality-score-badge.excellent .quality-score-fill { stroke: var(--admin-success, #22c55e); }

/* Auto-save Indicator */
.autosave-indicator { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--admin-text-muted, #71717a); }
.autosave-indicator.saving .autosave-icon::before { content: ''; display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--admin-warning, #f59e0b); animation: pulse 1s infinite; }
.autosave-indicator.saved .autosave-icon::before { content: '\2713'; color: var(--admin-success, #22c55e); }
.autosave-indicator.unsaved .autosave-icon::before { content: ''; display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--admin-warning, #f59e0b); }
.autosave-indicator.error .autosave-icon::before { content: '!'; color: var(--admin-danger, #ef4444); }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

/* Tabs */
.product-tabs { display: flex; gap: 0.25rem; border-bottom: 1px solid var(--admin-glass-border, rgba(255,255,255,0.08)); margin-bottom: 1.5rem; overflow-x: auto; }
.product-tab { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; border: none; background: transparent; color: var(--admin-text-muted, #71717a); cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s; white-space: nowrap; font-size: 0.875rem; }
.product-tab:hover { color: var(--admin-text, #fafafa); background: var(--admin-glass-hover, rgba(255,255,255,0.06)); }
.product-tab.active { color: var(--admin-primary, #8B2B2B); border-bottom-color: var(--admin-primary, #8B2B2B); }
.tab-badge { background: var(--admin-primary, #8B2B2B); color: white; font-size: 0.7rem; padding: 0.125rem 0.375rem; border-radius: 999px; }
.tab-content { display: none; }
.tab-content.active { display: block; }

/* Form Grid */
.form-grid { display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; max-width: 100%; }
.form-main { display: flex; flex-direction: column; gap: 1.5rem; min-width: 0; }
.form-sidebar { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
.form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
@media (max-width: 1024px) { .form-grid { grid-template-columns: 1fr; } .form-sidebar { order: -1; } .form-row { grid-template-columns: 1fr; } }

/* Input with Button */
.input-with-button { display: flex; gap: 0.5rem; }
.input-with-button .form-input { flex: 1; min-width: 0; }

/* Toggle Checkbox */
.toggle-checkbox { display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.25rem 0; }
.toggle-checkbox input { display: none; }
.toggle-switch { width: 40px; height: 22px; background: var(--admin-bg-hover, #2d2d31); border: 1px solid var(--admin-border-light, #3f3f46); border-radius: 999px; position: relative; transition: all 0.2s; flex-shrink: 0; }
.toggle-switch::after { content: ''; position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; background: var(--admin-text-muted, #71717a); border-radius: 50%; transition: all 0.2s; }
.toggle-checkbox input:checked + .toggle-switch { background: var(--admin-primary, #8B2B2B); border-color: var(--admin-primary, #8B2B2B); }
.toggle-checkbox input:checked + .toggle-switch::after { transform: translateX(18px); background: #fff; }
.toggle-checkbox span:last-child { font-size: 0.875rem; color: var(--admin-text, #fafafa); }
.toggle-checkbox.small .toggle-switch { width: 32px; height: 18px; }
.toggle-checkbox.small .toggle-switch::after { width: 12px; height: 12px; }
.toggle-checkbox.small input:checked + .toggle-switch::after { transform: translateX(14px); }

/* Category Tree */
.categories-tree { display: flex; flex-direction: column; gap: 0.25rem; }
.category-checkbox { display: flex; align-items: center; gap: 0.5rem; padding: 0.375rem 0; cursor: pointer; }
.category-checkbox input { accent-color: var(--admin-primary, #8B2B2B); width: 16px; height: 16px; flex-shrink: 0; }
.category-checkbox span { font-size: 0.875rem; color: var(--admin-text, #fafafa); }

/* Profit Calculator */
.profit-calculator { border: 1px solid var(--admin-glass-border, rgba(255,255,255,0.08)); border-radius: var(--admin-radius, 10px); }

/* Variants */
.variant-item { background: var(--admin-bg-elevated, #1f1f23); border: 1px solid var(--admin-glass-border, rgba(255,255,255,0.08)); border-radius: var(--admin-radius, 10px); padding: 1rem; margin-bottom: 1rem; }
.variant-item:last-child { margin-bottom: 0; }
.variant-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }

/* Image Grid */
.product-images-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; }
.product-image-item { position: relative; aspect-ratio: 1; border-radius: var(--admin-radius, 10px); overflow: hidden; border: 2px solid var(--admin-glass-border, rgba(255,255,255,0.08)); cursor: move; background: var(--admin-bg-elevated, #1f1f23); }
.product-image-item img { width: 100%; height: 100%; object-fit: cover; }
.product-image-item img.is-primary { border-color: var(--admin-primary, #8B2B2B); }
.image-badge { position: absolute; top: 0.5rem; left: 0.5rem; background: var(--admin-primary, #8B2B2B); color: white; font-size: 0.7rem; padding: 0.125rem 0.5rem; border-radius: 999px; z-index: 2; }
.image-action { position: absolute; background: rgba(0,0,0,0.7); color: white; border: none; cursor: pointer; opacity: 0; transition: opacity 0.2s; z-index: 2; }
.product-image-item:hover .image-action { opacity: 1; }
.image-action.set-primary { bottom: 0; left: 0; right: 0; padding: 0.5rem; font-size: 0.75rem; }
.image-action.delete { top: 0.5rem; right: 0.5rem; padding: 0.375rem; border-radius: 0.25rem; }

/* Upload Zone */
.upload-zone { position: relative; border: 2px dashed var(--admin-border, #27272a); border-radius: var(--admin-radius, 10px); padding: 2rem; text-align: center; transition: all 0.2s; background: var(--admin-glass-bg, rgba(255,255,255,0.03)); }
.upload-zone:hover { border-color: var(--admin-primary, #8B2B2B); background: var(--admin-primary-light, rgba(139,43,43,0.15)); }
.upload-zone .upload-input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.upload-content svg { color: var(--admin-text-muted, #71717a); margin: 0 auto 1rem; }
.upload-content p { color: var(--admin-text, #fafafa); }
.upload-content small { color: var(--admin-text-muted, #71717a); }

/* Specs */
.spec-row { display: grid; grid-template-columns: 1fr 1fr auto; gap: 0.5rem; align-items: start; }

/* SEO Preview */
.seo-preview-box { background: var(--admin-bg-elevated, #1f1f23); border: 1px solid var(--admin-glass-border, rgba(255,255,255,0.08)); border-radius: var(--admin-radius, 10px); padding: 1rem; }
.seo-preview-title { color: #8ab4f8; font-size: 1.125rem; margin-bottom: 0.25rem; }
.seo-preview-url { color: #bdc1c6; font-size: 0.875rem; margin-bottom: 0.25rem; }
.seo-preview-desc { color: var(--admin-text-secondary, #a1a1aa); font-size: 0.875rem; line-height: 1.4; }

/* Reviews */
.reviews-list { display: flex; flex-direction: column; gap: 1rem; }
.review-item { border: 1px solid var(--admin-glass-border, rgba(255,255,255,0.08)); border-radius: var(--admin-radius, 10px); padding: 1rem; background: var(--admin-bg-elevated, #1f1f23); }
.review-header { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
.review-rating { margin-bottom: 0.5rem; }
.review-actions { display: flex; align-items: center; gap: 0.5rem; }

/* Color Options */
.color-options { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.color-option { cursor: pointer; }
.color-option input { display: none; }
.color-swatch { display: block; width: 32px; height: 32px; border-radius: 50%; border: 2px solid transparent; transition: all 0.2s; box-shadow: 0 0 0 1px rgba(255,255,255,0.1); }
.color-swatch:hover { transform: scale(1.1); }
.color-swatch.selected, .color-option input:checked + .color-swatch { border-color: var(--admin-primary, #8B2B2B); box-shadow: 0 0 0 2px var(--admin-primary, #8B2B2B); }

/* Modal */
.modal { display: none; position: fixed; inset: 0; z-index: 1000; align-items: center; justify-content: center; }
.modal.active { display: flex; }
.modal-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); }
.modal-content { position: relative; background: var(--admin-bg-card, #27272a); border: 1px solid var(--admin-glass-border, rgba(255,255,255,0.08)); border-radius: var(--admin-radius-lg, 14px); width: 100%; max-width: 400px; margin: 1rem; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--admin-glass-border, rgba(255,255,255,0.08)); }
.modal-header h3 { color: var(--admin-text, #fafafa); margin: 0; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--admin-text-muted, #71717a); transition: color 0.2s; }
.modal-close:hover { color: var(--admin-text, #fafafa); }
.modal-body { padding: 1.25rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid var(--admin-glass-border, rgba(255,255,255,0.08)); }
</style>
