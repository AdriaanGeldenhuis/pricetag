<!-- Admin Products List -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle"><?= number_format($pagination['count']) ?> products total</p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/products/import') ?>" class="admin-btn admin-btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            Import / Export
        </a>
        <a href="<?= url('/admin/products/create') ?>" class="admin-btn admin-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Product
        </a>
    </div>
</div>

<!-- Filters -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="admin-card-body">
        <form method="GET" action="<?= url('/admin/products') ?>" class="admin-filters-form">
            <div style="display: grid; grid-template-columns: 1fr 200px 150px 150px auto; gap: 1rem; align-items: end;">
                <div class="admin-form-group" style="margin: 0;">
                    <label class="admin-form-label">Search</label>
                    <input type="text" name="search" value="<?= e($filters['search']) ?>"
                           placeholder="Search by name or SKU..."
                           class="admin-form-input">
                </div>
                <div class="admin-form-group" style="margin: 0;">
                    <label class="admin-form-label">Category</label>
                    <select name="category" class="admin-form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $filters['category'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group" style="margin: 0;">
                    <label class="admin-form-label">Status</label>
                    <select name="status" class="admin-form-select">
                        <option value="">All Status</option>
                        <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="admin-form-group" style="margin: 0;">
                    <label class="admin-form-label">Stock</label>
                    <select name="filter" class="admin-form-select">
                        <option value="">All Stock</option>
                        <option value="low_stock" <?= $filters['filter'] === 'low_stock' ? 'selected' : '' ?>>Low Stock</option>
                        <option value="out_of_stock" <?= $filters['filter'] === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                    </select>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="admin-btn admin-btn-primary">Filter</button>
                    <a href="<?= url('/admin/products') ?>" class="admin-btn admin-btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
// Reusable pagination renderer
function renderProductPagination($pagination, $filters) {
    if ($pagination['total'] <= 1) return;

    $queryString = http_build_query($filters);
    $current = $pagination['current'];
    $total = $pagination['total'];
    $count = $pagination['count'];
    $perPage = $pagination['perPage'];

    $start = max(1, $current - 3);
    $end = min($total, $current + 3);
    ?>
    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="font-size: 0.875rem; color: #6b7280;">
            Showing <strong style="color: #111827;"><?= (($current - 1) * $perPage) + 1 ?></strong>
            to <strong style="color: #111827;"><?= min($current * $perPage, $count) ?></strong>
            of <strong style="color: #111827;"><?= number_format($count) ?></strong> products
        </div>
        <div style="display: flex; align-items: center; gap: 4px; list-style: none; margin: 0; padding: 0;">
            <?php if ($current > 1): ?>
            <a href="?page=1&<?= $queryString ?>"
               style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; color: #374151; text-decoration: none; background: #fff; transition: all 0.15s;"
               title="First page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                    <polyline points="11 17 6 12 11 7"></polyline>
                    <polyline points="18 17 13 12 18 7"></polyline>
                </svg>
            </a>
            <a href="?page=<?= $current - 1 ?>&<?= $queryString ?>"
               style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; color: #374151; text-decoration: none; background: #fff; transition: all 0.15s;"
               title="Previous page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <?php endif; ?>

            <?php if ($start > 1): ?>
            <a href="?page=1&<?= $queryString ?>"
               style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; color: #374151; text-decoration: none; background: #fff;">1</a>
            <?php if ($start > 2): ?>
            <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; font-size: 0.875rem; color: #9ca3af;">...</span>
            <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i === $current): ?>
            <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #4f46e5; border-radius: 6px; font-size: 0.875rem; font-weight: 600; color: #fff; background: #4f46e5;"><?= $i ?></span>
            <?php else: ?>
            <a href="?page=<?= $i ?>&<?= $queryString ?>"
               style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; color: #374151; text-decoration: none; background: #fff; transition: all 0.15s;"><?= $i ?></a>
            <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $total): ?>
            <?php if ($end < $total - 1): ?>
            <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; font-size: 0.875rem; color: #9ca3af;">...</span>
            <?php endif; ?>
            <a href="?page=<?= $total ?>&<?= $queryString ?>"
               style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; color: #374151; text-decoration: none; background: #fff;"><?= $total ?></a>
            <?php endif; ?>

            <?php if ($current < $total): ?>
            <a href="?page=<?= $current + 1 ?>&<?= $queryString ?>"
               style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; color: #374151; text-decoration: none; background: #fff; transition: all 0.15s;"
               title="Next page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
            <a href="?page=<?= $total ?>&<?= $queryString ?>"
               style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; color: #374151; text-decoration: none; background: #fff; transition: all 0.15s;"
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

<!-- Products Table -->
<div class="admin-card">
    <!-- Top Pagination -->
    <?php renderProductPagination($pagination, $filters); ?>

    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 60px;"></th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th style="text-align: right;">Price</th>
                    <th style="text-align: center;">Stock</th>
                    <th style="text-align: center;">Status</th>
                    <th style="width: 100px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="8">
                        <div class="admin-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-empty-icon">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            <h3 class="admin-empty-title">No products found</h3>
                            <p class="admin-empty-text">Try adjusting your filters or add a new product.</p>
                            <a href="<?= url('/admin/products/create') ?>" class="admin-btn admin-btn-primary">Add Product</a>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <img src="<?= e($product['image'] ?? '/assets/images/placeholder.jpg') ?>"
                             alt="<?= e($product['name']) ?>"
                             style="width: 48px; height: 48px; object-fit: contain; border-radius: 6px; border: 1px solid #e5e7eb; background: #fff; padding: 2px;">
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <a href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>" style="font-weight: 600; color: #111827; text-decoration: none; line-height: 1.3;">
                                <?= e($product['name']) ?>
                            </a>
                            <?php if ($product['featured']): ?>
                            <span style="display: inline-flex; align-items: center; gap: 3px; font-size: 0.7rem; background: #fef3c7; color: #92400e; padding: 1px 6px; border-radius: 4px; width: fit-content; font-weight: 500;">
                                <svg viewBox="0 0 24 24" fill="currentColor" style="width: 10px; height: 10px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                Featured
                            </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <code style="font-size: 0.8rem; color: #6b7280; background: #f3f4f6; padding: 2px 6px; border-radius: 4px;"><?= e($product['sku']) ?></code>
                    </td>
                    <td>
                        <?php if (!empty($product['category_name'])): ?>
                        <span style="display: inline-block; font-size: 0.8rem; background: #ede9fe; color: #5b21b6; padding: 3px 10px; border-radius: 20px; font-weight: 500; white-space: nowrap;"><?= e($product['category_name']) ?></span>
                        <?php else: ?>
                        <span style="color: #d1d5db; font-size: 0.8rem;">No category</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right;">
                        <span style="font-weight: 600; color: #111827;"><?= formatPrice($product['price']) ?></span>
                        <?php if ($product['compare_price']): ?>
                        <br><span style="font-size: 0.75rem; color: #9ca3af; text-decoration: line-through;"><?= formatPrice($product['compare_price']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <?php if ($product['stock'] == 0): ?>
                        <span style="display: inline-block; font-size: 0.75rem; background: #fef2f2; color: #dc2626; padding: 3px 10px; border-radius: 20px; font-weight: 500;">Out of Stock</span>
                        <?php elseif ($product['stock'] <= ($product['low_stock_threshold'] ?? 10)): ?>
                        <span style="display: inline-block; font-size: 0.75rem; background: #fffbeb; color: #d97706; padding: 3px 10px; border-radius: 20px; font-weight: 500;"><?= $product['stock'] ?> left</span>
                        <?php else: ?>
                        <span style="font-size: 0.875rem; color: #6b7280;"><?= $product['stock'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <?php if ($product['status']): ?>
                        <span style="display: inline-block; font-size: 0.75rem; background: #ecfdf5; color: #059669; padding: 3px 10px; border-radius: 20px; font-weight: 500;">Active</span>
                        <?php else: ?>
                        <span style="display: inline-block; font-size: 0.75rem; background: #f3f4f6; color: #6b7280; padding: 3px 10px; border-radius: 20px; font-weight: 500;">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 2px; justify-content: flex-end;">
                            <a href="<?= url('/products/' . $product['slug']) ?>" target="_blank"
                               style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; color: #6b7280; text-decoration: none; transition: all 0.15s;"
                               title="View on site"
                               onmouseover="this.style.background='#f3f4f6';this.style.color='#111827'"
                               onmouseout="this.style.background='transparent';this.style.color='#6b7280'">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                </svg>
                            </a>
                            <a href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>"
                               style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; color: #6b7280; text-decoration: none; transition: all 0.15s;"
                               title="Edit"
                               onmouseover="this.style.background='#ede9fe';this.style.color='#5b21b6'"
                               onmouseout="this.style.background='transparent';this.style.color='#6b7280'">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            <button type="button"
                                    style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; color: #6b7280; background: transparent; border: none; cursor: pointer; transition: all 0.15s;"
                                    onclick="deleteProduct(<?= $product['id'] ?>)" title="Delete"
                                    onmouseover="this.style.background='#fef2f2';this.style.color='#dc2626'"
                                    onmouseout="this.style.background='transparent';this.style.color='#6b7280'">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bottom Pagination -->
    <?php renderProductPagination($pagination, $filters); ?>
</div>

<!-- Delete Confirmation Modal -->
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
            <p>Are you sure you want to delete this product? This action cannot be undone.</p>
        </div>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteForm" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Product</button>
            </form>
        </div>
    </div>
</div>

<style>
    .admin-table-wrapper table tbody tr:hover {
        background: #f9fafb;
    }
    .admin-table-wrapper table tbody td {
        padding: 0.625rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }
    .admin-table-wrapper table thead th {
        padding: 0.625rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        border-bottom: 2px solid #e5e7eb;
        background: #f9fafb;
    }
</style>

<script>
function deleteProduct(id) {
    document.getElementById('deleteForm').action = '/admin/products/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
