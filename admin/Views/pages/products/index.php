<!-- Admin Products List -->
<div class="flex justify-between items-center mb-6">
    <h1 class="admin-page-title mb-0">Products</h1>
    <div class="flex gap-2">
        <a href="<?= url('/admin/products/import') ?>" class="btn btn-outline">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Import / Export
        </a>
        <a href="<?= url('/admin/products/create') ?>" class="btn btn-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Product
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-6">
    <div class="card-body">
        <form action="<?= url('/admin/products') ?>" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search products..."
                       class="form-input">
            </div>
            <div>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="out_of_stock" <?= $status === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                </select>
            </div>
            <?php if (!empty($vendors)): ?>
            <div>
                <select name="vendor" class="form-select">
                    <option value="">All Vendors</option>
                    <?php foreach ($vendors as $v): ?>
                    <option value="<?= $v['id'] ?>" <?= ($vendor_filter ?? '') == $v['id'] ? 'selected' : '' ?>>
                        <?= e($v['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($search || $status || ($vendor_filter ?? '')): ?>
            <a href="<?= url('/admin/products') ?>" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php
// Reusable pagination renderer for product listing
function renderPagination($pagination, $baseUrl) {
    if ($pagination['last_page'] <= 1) return;

    $current = $pagination['current_page'];
    $last = $pagination['last_page'];
    $total = $pagination['total'];
    $perPage = $pagination['per_page'];

    $start = max(1, $current - 3);
    $end = min($last, $current + 3);

    // Build query string preserving current filters
    $queryParams = $_GET;
    unset($queryParams['page']);
    $queryString = http_build_query($queryParams);
    $separator = $queryString ? '&' : '';
    ?>
    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; flex-wrap: wrap; gap: 10px;">
        <div style="font-size: 13px; color: #6b7280;">
            Showing <strong style="color: #111827;"><?= number_format((($current - 1) * $perPage) + 1) ?></strong>
            to <strong style="color: #111827;"><?= number_format(min($current * $perPage, $total)) ?></strong>
            of <strong style="color: #111827;"><?= number_format($total) ?></strong> products
        </div>
        <div style="display: flex; align-items: center; gap: 4px;">
            <?php if ($current > 1): ?>
            <a href="<?= $baseUrl ?>?page=1<?= $separator . $queryString ?>"
               style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; color: #374151; text-decoration: none; background: #fff;"
               title="First page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                    <polyline points="11 17 6 12 11 7"></polyline>
                    <polyline points="18 17 13 12 18 7"></polyline>
                </svg>
            </a>
            <a href="<?= $baseUrl ?>?page=<?= $current - 1 ?><?= $separator . $queryString ?>"
               style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; color: #374151; text-decoration: none; background: #fff;"
               title="Previous page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <?php endif; ?>

            <?php if ($start > 1): ?>
            <a href="<?= $baseUrl ?>?page=1<?= $separator . $queryString ?>"
               style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; color: #374151; text-decoration: none; background: #fff;">1</a>
            <?php if ($start > 2): ?>
            <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; font-size: 13px; color: #9ca3af;">...</span>
            <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i === $current): ?>
            <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #4f46e5; border-radius: 6px; font-size: 13px; font-weight: 600; color: #fff; background: #4f46e5;"><?= $i ?></span>
            <?php else: ?>
            <a href="<?= $baseUrl ?>?page=<?= $i ?><?= $separator . $queryString ?>"
               style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; color: #374151; text-decoration: none; background: #fff;"><?= $i ?></a>
            <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $last): ?>
            <?php if ($end < $last - 1): ?>
            <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; font-size: 13px; color: #9ca3af;">...</span>
            <?php endif; ?>
            <a href="<?= $baseUrl ?>?page=<?= $last ?><?= $separator . $queryString ?>"
               style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; color: #374151; text-decoration: none; background: #fff;"><?= $last ?></a>
            <?php endif; ?>

            <?php if ($current < $last): ?>
            <a href="<?= $baseUrl ?>?page=<?= $current + 1 ?><?= $separator . $queryString ?>"
               style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; color: #374151; text-decoration: none; background: #fff;"
               title="Next page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
            <a href="<?= $baseUrl ?>?page=<?= $last ?><?= $separator . $queryString ?>"
               style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; color: #374151; text-decoration: none; background: #fff;"
               title="Last page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                    <polyline points="13 17 18 12 13 7"></polyline>
                    <polyline points="6 17 11 12 6 7"></polyline>
                </svg>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>

<!-- Bulk Actions Bar (hidden by default) -->
<div id="bulk-actions-bar" class="card mb-4" style="display: none;">
    <div class="card-body py-3">
        <div class="flex items-center gap-4">
            <span id="selected-count" class="text-sm font-medium">0 selected</span>
            <div class="flex gap-2">
                <select id="bulk-action" class="form-select form-select-sm">
                    <option value="">Quick actions...</option>
                    <option value="active">Set Active</option>
                    <option value="draft">Set Draft</option>
                    <option value="inactive">Set Inactive</option>
                    <option value="ai-images">Generate AI Images</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="button" id="apply-bulk-action" class="btn btn-primary btn-sm">Apply</button>
                <button type="button" id="open-bulk-edit" class="btn btn-outline btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1">
                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Bulk Edit
                </button>
            </div>
            <button type="button" id="clear-selection" class="btn btn-ghost btn-sm">Clear Selection</button>
        </div>
    </div>
</div>

<!-- Bulk Edit Modal -->
<div id="bulk-edit-modal" class="modal-backdrop" style="display: none;">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 class="modal-title">Bulk Edit Products</h3>
            <button type="button" class="modal-close" onclick="closeBulkEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p class="text-muted mb-4">Select the fields you want to update. Only checked fields will be changed.</p>

            <form id="bulk-edit-form">
                <!-- Price Updates -->
                <div class="mb-4 p-4 border rounded">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" id="bulk-update-price" class="form-checkbox mr-2">
                        <label for="bulk-update-price" class="font-medium">Update Prices</label>
                    </div>
                    <div id="price-fields" class="grid grid-cols-2 gap-4" style="display: none;">
                        <div>
                            <label class="form-label">Price Action</label>
                            <select name="price_action" class="form-select">
                                <option value="set">Set fixed price</option>
                                <option value="increase_percent">Increase by %</option>
                                <option value="decrease_percent">Decrease by %</option>
                                <option value="increase_fixed">Increase by fixed amount</option>
                                <option value="decrease_fixed">Decrease by fixed amount</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Value</label>
                            <input type="number" name="price_value" class="form-input" step="0.01" min="0">
                        </div>
                    </div>
                </div>

                <!-- Stock Updates -->
                <div class="mb-4 p-4 border rounded">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" id="bulk-update-stock" class="form-checkbox mr-2">
                        <label for="bulk-update-stock" class="font-medium">Update Stock</label>
                    </div>
                    <div id="stock-fields" class="grid grid-cols-2 gap-4" style="display: none;">
                        <div>
                            <label class="form-label">Stock Action</label>
                            <select name="stock_action" class="form-select">
                                <option value="set">Set stock quantity</option>
                                <option value="add">Add to stock</option>
                                <option value="subtract">Subtract from stock</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Quantity</label>
                            <input type="number" name="stock_value" class="form-input" min="0">
                        </div>
                    </div>
                </div>

                <!-- Vendor -->
                <div class="mb-4 p-4 border rounded">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" id="bulk-update-vendor" class="form-checkbox mr-2">
                        <label for="bulk-update-vendor" class="font-medium">Update Vendor</label>
                    </div>
                    <div id="vendor-fields" style="display: none;">
                        <select name="vendor_id" class="form-select">
                            <option value="">No Vendor</option>
                            <?php foreach ($vendors as $v): ?>
                            <option value="<?= $v['id'] ?>"><?= e($v['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-4 p-4 border rounded">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" id="bulk-update-status" class="form-checkbox mr-2">
                        <label for="bulk-update-status" class="font-medium">Update Status</label>
                    </div>
                    <div id="status-fields" style="display: none;">
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="draft">Draft</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Flags -->
                <div class="mb-4 p-4 border rounded">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" id="bulk-update-flags" class="form-checkbox mr-2">
                        <label for="bulk-update-flags" class="font-medium">Update Flags</label>
                    </div>
                    <div id="flags-fields" class="flex gap-6" style="display: none;">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_featured" class="form-checkbox">
                            Featured
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_new" class="form-checkbox">
                            New
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_on_sale" class="form-checkbox">
                            On Sale
                        </label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="closeBulkEditModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="applyBulkEdit()">Apply Changes</button>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <!-- Top Pagination -->
    <?php renderPagination($pagination, url('/admin/products')); ?>

    <div class="overflow-x-auto">
        <table class="admin-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                    <th style="width: 40px; padding: 10px 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; text-align: left;">
                        <input type="checkbox" id="select-all" class="form-checkbox">
                    </th>
                    <th style="width: 60px; padding: 10px 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;"></th>
                    <th style="padding: 10px 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; text-align: left;">Product</th>
                    <th style="padding: 10px 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; text-align: left;">SKU</th>
                    <th style="padding: 10px 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; text-align: left;">Category</th>
                    <th style="padding: 10px 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; text-align: right;">Price</th>
                    <th style="padding: 10px 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; text-align: center;">Stock</th>
                    <th style="padding: 10px 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; text-align: center;">Status</th>
                    <th style="width: 100px; padding: 10px 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-12">
                        No products found
                        <br>
                        <a href="<?= url('/admin/products/create') ?>" class="btn btn-primary mt-4">Add your first product</a>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <tr data-product-id="<?= $product['id'] ?>" style="border-bottom: 1px solid #f3f4f6;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 10px 12px; vertical-align: middle;">
                        <input type="checkbox" class="form-checkbox product-checkbox" value="<?= $product['id'] ?>">
                    </td>
                    <td style="padding: 10px 12px; vertical-align: middle;">
                        <?php if ($product['image']): ?>
                        <img src="<?= url('storage/uploads/' . e($product['image'])) ?>"
                             alt="" style="width: 44px; height: 44px; border-radius: 6px; object-fit: cover; border: 1px solid #e5e7eb;">
                        <?php else: ?>
                        <div style="width: 44px; height: 44px; border-radius: 6px; background: #f3f4f6; border: 1px solid #e5e7eb;"></div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 10px 12px; vertical-align: middle;">
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <a href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>" style="font-weight: 600; color: #111827; text-decoration: none; line-height: 1.3;">
                                <?= e($product['name']) ?>
                            </a>
                            <?php if (!empty($product['is_featured'])): ?>
                            <span style="display: inline-flex; align-items: center; gap: 3px; font-size: 11px; background: #fef3c7; color: #92400e; padding: 1px 6px; border-radius: 4px; width: fit-content; font-weight: 500;">
                                <svg viewBox="0 0 24 24" fill="currentColor" style="width: 10px; height: 10px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                Featured
                            </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td style="padding: 10px 12px; vertical-align: middle; white-space: nowrap;">
                        <code style="font-size: 12px; color: #6b7280; background: #f3f4f6; padding: 2px 6px; border-radius: 4px;"><?= e($product['sku']) ?></code>
                    </td>
                    <td style="padding: 10px 12px; vertical-align: middle;">
                        <?php if (!empty($product['category_name'])): ?>
                        <span style="display: inline-block; font-size: 12px; background: #ede9fe; color: #5b21b6; padding: 3px 10px; border-radius: 20px; font-weight: 500; white-space: nowrap;"><?= e($product['category_name']) ?></span>
                        <?php else: ?>
                        <span style="color: #d1d5db; font-size: 12px;">No category</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 10px 12px; vertical-align: middle; text-align: right; white-space: nowrap;">
                        <span style="font-weight: 600; color: #111827;"><?= formatPrice($product['price']) ?></span>
                        <?php if ($product['compare_price']): ?>
                        <br><span style="font-size: 11px; color: #9ca3af; text-decoration: line-through;"><?= formatPrice($product['compare_price']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 10px 12px; vertical-align: middle; text-align: center;">
                        <?php if (!empty($product['manage_stock'])): ?>
                            <?php if ($product['stock_quantity'] <= 0): ?>
                            <span style="display: inline-block; font-size: 11px; background: #fef2f2; color: #dc2626; padding: 3px 10px; border-radius: 20px; font-weight: 500;">Out of Stock</span>
                            <?php elseif ($product['stock_quantity'] <= ($product['low_stock_threshold'] ?? 10)): ?>
                            <span style="display: inline-block; font-size: 11px; background: #fffbeb; color: #d97706; padding: 3px 10px; border-radius: 20px; font-weight: 500;"><?= $product['stock_quantity'] ?> left</span>
                            <?php else: ?>
                            <span style="font-size: 13px; color: #6b7280;"><?= $product['stock_quantity'] ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                        <span style="color: #d1d5db; font-size: 12px;">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 10px 12px; vertical-align: middle; text-align: center;">
                        <?php
                        $statusColors = [
                            'active' => ['bg' => '#ecfdf5', 'color' => '#059669'],
                            'draft' => ['bg' => '#f3f4f6', 'color' => '#6b7280'],
                            'inactive' => ['bg' => '#fffbeb', 'color' => '#d97706'],
                            'out_of_stock' => ['bg' => '#fef2f2', 'color' => '#dc2626'],
                        ];
                        $sc = $statusColors[$product['status']] ?? ['bg' => '#f3f4f6', 'color' => '#6b7280'];
                        ?>
                        <span style="display: inline-block; font-size: 11px; background: <?= $sc['bg'] ?>; color: <?= $sc['color'] ?>; padding: 3px 10px; border-radius: 20px; font-weight: 500;"><?= ucfirst($product['status']) ?></span>
                    </td>
                    <td style="padding: 10px 12px; vertical-align: middle;">
                        <div style="display: flex; gap: 2px; justify-content: flex-end;">
                            <a href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>"
                               style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; color: #6b7280; text-decoration: none;"
                               title="Edit"
                               onmouseover="this.style.background='#ede9fe';this.style.color='#5b21b6'"
                               onmouseout="this.style.background='transparent';this.style.color='#6b7280'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            <a href="<?= url('/products/' . $product['slug']) ?>" target="_blank"
                               style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; color: #6b7280; text-decoration: none;"
                               title="View on site"
                               onmouseover="this.style.background='#f3f4f6';this.style.color='#111827'"
                               onmouseout="this.style.background='transparent';this.style.color='#6b7280'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                                    <polyline points="15 3 21 3 21 9"/>
                                    <line x1="10" y1="14" x2="21" y2="3"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bottom Pagination -->
    <?php renderPagination($pagination, url('/admin/products')); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const bulkActionsBar = document.getElementById('bulk-actions-bar');
    const selectedCount = document.getElementById('selected-count');
    const bulkAction = document.getElementById('bulk-action');
    const applyBulkAction = document.getElementById('apply-bulk-action');
    const clearSelection = document.getElementById('clear-selection');
    const openBulkEdit = document.getElementById('open-bulk-edit');
    const bulkEditModal = document.getElementById('bulk-edit-modal');

    // Toggle field visibility checkboxes
    const toggleFields = [
        { checkbox: 'bulk-update-price', fields: 'price-fields' },
        { checkbox: 'bulk-update-stock', fields: 'stock-fields' },
        { checkbox: 'bulk-update-vendor', fields: 'vendor-fields' },
        { checkbox: 'bulk-update-status', fields: 'status-fields' },
        { checkbox: 'bulk-update-flags', fields: 'flags-fields' }
    ];

    toggleFields.forEach(item => {
        const cb = document.getElementById(item.checkbox);
        const fields = document.getElementById(item.fields);
        if (cb && fields) {
            cb.addEventListener('change', function() {
                fields.style.display = this.checked ? 'grid' : 'none';
                if (item.fields === 'flags-fields' || item.fields === 'vendor-fields' || item.fields === 'status-fields') {
                    fields.style.display = this.checked ? 'flex' : 'none';
                }
            });
        }
    });

    function updateBulkActionsBar() {
        const selected = document.querySelectorAll('.product-checkbox:checked');
        const count = selected.length;

        if (count > 0) {
            bulkActionsBar.style.display = 'block';
            selectedCount.textContent = count + ' selected';
        } else {
            bulkActionsBar.style.display = 'none';
        }

        selectAll.checked = count === checkboxes.length && count > 0;
        selectAll.indeterminate = count > 0 && count < checkboxes.length;
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkActionsBar();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActionsBar);
    });

    clearSelection.addEventListener('click', function() {
        checkboxes.forEach(cb => cb.checked = false);
        selectAll.checked = false;
        updateBulkActionsBar();
    });

    // Open bulk edit modal
    openBulkEdit.addEventListener('click', function() {
        bulkEditModal.style.display = 'flex';
    });

    // Apply quick bulk action
    applyBulkAction.addEventListener('click', function() {
        const action = bulkAction.value;
        if (!action) {
            alert('Please select an action');
            return;
        }

        const selected = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one product');
            return;
        }

        let confirmMsg = 'Are you sure you want to ';
        if (action === 'delete') {
            confirmMsg += 'delete ' + selected.length + ' product(s)? This cannot be undone.';
        } else if (action === 'ai-images') {
            confirmMsg += 'generate AI images for ' + selected.length + ' product(s)? This may take a moment.';
        } else {
            confirmMsg += 'change the status of ' + selected.length + ' product(s) to "' + action + '"?';
        }

        if (!confirm(confirmMsg)) return;

        fetch('<?= url('/admin/products/bulk-action') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= csrf_token() ?>'
            },
            body: JSON.stringify({
                action: action,
                ids: selected
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'An error occurred');
            }
        })
        .catch(err => {
            alert('An error occurred: ' + err.message);
        });
    });
});

function closeBulkEditModal() {
    document.getElementById('bulk-edit-modal').style.display = 'none';
}

function applyBulkEdit() {
    const selected = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        alert('Please select at least one product');
        return;
    }

    const updates = { ids: selected };

    // Collect price updates
    if (document.getElementById('bulk-update-price').checked) {
        const form = document.getElementById('bulk-edit-form');
        updates.price_action = form.querySelector('[name="price_action"]').value;
        updates.price_value = parseFloat(form.querySelector('[name="price_value"]').value) || 0;
    }

    // Collect stock updates
    if (document.getElementById('bulk-update-stock').checked) {
        const form = document.getElementById('bulk-edit-form');
        updates.stock_action = form.querySelector('[name="stock_action"]').value;
        updates.stock_value = parseInt(form.querySelector('[name="stock_value"]').value) || 0;
    }

    // Collect vendor update
    if (document.getElementById('bulk-update-vendor').checked) {
        const form = document.getElementById('bulk-edit-form');
        updates.vendor_id = form.querySelector('[name="vendor_id"]').value;
    }

    // Collect status update
    if (document.getElementById('bulk-update-status').checked) {
        const form = document.getElementById('bulk-edit-form');
        updates.status = form.querySelector('[name="status"]').value;
    }

    // Collect flags
    if (document.getElementById('bulk-update-flags').checked) {
        const form = document.getElementById('bulk-edit-form');
        updates.is_featured = form.querySelector('[name="is_featured"]').checked ? 1 : 0;
        updates.is_new = form.querySelector('[name="is_new"]').checked ? 1 : 0;
        updates.is_on_sale = form.querySelector('[name="is_on_sale"]').checked ? 1 : 0;
    }

    // Check if any updates selected
    const hasUpdates = updates.price_action || updates.stock_action ||
                       updates.vendor_id !== undefined || updates.status ||
                       updates.is_featured !== undefined;

    if (!hasUpdates) {
        alert('Please select at least one field to update');
        return;
    }

    if (!confirm('Apply changes to ' + selected.length + ' product(s)?')) return;

    fetch('<?= url('/admin/products/bulk-edit') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= csrf_token() ?>'
        },
        body: JSON.stringify(updates)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeBulkEditModal();
            window.location.reload();
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(err => {
        alert('An error occurred: ' + err.message);
    });
}

// Close modal on backdrop click
document.getElementById('bulk-edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeBulkEditModal();
});
</script>
