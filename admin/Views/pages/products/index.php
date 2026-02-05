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

<!-- Bulk Actions Bar (hidden by default) -->
<div id="bulk-actions-bar" class="card mb-4" style="display: none;">
    <div class="card-body py-3">
        <div class="flex items-center gap-4">
            <span id="selected-count" class="text-sm font-medium">0 selected</span>
            <div class="flex gap-2">
                <select id="bulk-action" class="form-select form-select-sm">
                    <option value="">Choose action...</option>
                    <option value="active">Set Active</option>
                    <option value="draft">Set Draft</option>
                    <option value="inactive">Set Inactive</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="button" id="apply-bulk-action" class="btn btn-primary btn-sm">Apply</button>
            </div>
            <button type="button" id="clear-selection" class="btn btn-ghost btn-sm">Clear Selection</button>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="select-all" class="form-checkbox">
                    </th>
                    <th style="width: 60px;"></th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-12">
                        No products found
                        <br>
                        <a href="<?= url('/admin/products/create') ?>" class="btn btn-primary mt-4">Add your first product</a>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <tr data-product-id="<?= $product['id'] ?>">
                    <td>
                        <input type="checkbox" class="form-checkbox product-checkbox" value="<?= $product['id'] ?>">
                    </td>
                    <td>
                        <?php if ($product['image']): ?>
                        <img src="<?= url('storage/uploads/' . e($product['image'])) ?>"
                             alt="" class="w-10 h-10 rounded object-cover">
                        <?php else: ?>
                        <div class="w-10 h-10 rounded bg-neutral-100"></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>" class="font-medium text-primary">
                            <?= e($product['name']) ?>
                        </a>
                        <?php if ($product['is_featured']): ?>
                        <span class="badge badge-accent ml-2">Featured</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted text-sm"><?= e($product['sku']) ?></td>
                    <td>
                        <span class="font-medium"><?= formatPrice($product['price']) ?></span>
                        <?php if ($product['compare_price']): ?>
                        <span class="text-xs text-muted line-through block"><?= formatPrice($product['compare_price']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($product['manage_stock']): ?>
                            <?php if ($product['stock_quantity'] <= 0): ?>
                            <span class="badge badge-danger">Out of Stock</span>
                            <?php elseif ($product['stock_quantity'] <= $product['low_stock_threshold']): ?>
                            <span class="badge badge-warning"><?= $product['stock_quantity'] ?> left</span>
                            <?php else: ?>
                            <span class="text-success"><?= $product['stock_quantity'] ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $statusColors = [
                            'active' => 'success',
                            'draft' => 'neutral',
                            'inactive' => 'warning',
                            'out_of_stock' => 'danger',
                        ];
                        $color = $statusColors[$product['status']] ?? 'neutral';
                        ?>
                        <span class="badge badge-<?= $color ?>"><?= ucfirst($product['status']) ?></span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>"
                               class="btn btn-ghost btn-sm btn-icon" title="Edit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            <a href="<?= url('/products/' . $product['slug']) ?>" target="_blank"
                               class="btn btn-ghost btn-sm btn-icon" title="View">
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
</div>

<!-- Pagination -->
<?php if ($pagination['last_page'] > 1): ?>
<div class="mt-6">
    <?= \App\Core\View::pagination($pagination, url('/admin/products')) ?>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const bulkActionsBar = document.getElementById('bulk-actions-bar');
    const selectedCount = document.getElementById('selected-count');
    const bulkAction = document.getElementById('bulk-action');
    const applyBulkAction = document.getElementById('apply-bulk-action');
    const clearSelection = document.getElementById('clear-selection');

    function updateBulkActionsBar() {
        const selected = document.querySelectorAll('.product-checkbox:checked');
        const count = selected.length;

        if (count > 0) {
            bulkActionsBar.style.display = 'block';
            selectedCount.textContent = count + ' selected';
        } else {
            bulkActionsBar.style.display = 'none';
        }

        // Update select all state
        selectAll.checked = count === checkboxes.length && count > 0;
        selectAll.indeterminate = count > 0 && count < checkboxes.length;
    }

    // Select all checkbox
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkActionsBar();
    });

    // Individual checkboxes
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActionsBar);
    });

    // Clear selection
    clearSelection.addEventListener('click', function() {
        checkboxes.forEach(cb => cb.checked = false);
        selectAll.checked = false;
        updateBulkActionsBar();
    });

    // Apply bulk action
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
        } else {
            confirmMsg += 'change the status of ' + selected.length + ' product(s) to "' + action + '"?';
        }

        if (!confirm(confirmMsg)) return;

        // Send bulk action request
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
</script>
