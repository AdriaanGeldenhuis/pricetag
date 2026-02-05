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

<!-- Products Table -->
<div class="admin-card">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 50px;"></th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
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
                             class="admin-table-image">
                    </td>
                    <td>
                        <a href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>" class="font-semibold">
                            <?= e($product['name']) ?>
                        </a>
                        <?php if ($product['featured']): ?>
                        <span class="admin-badge" style="background: #fef3c7; color: #92400e; margin-left: 0.5rem;">Featured</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= e($product['sku']) ?></td>
                    <td><?= e($product['category_name'] ?? '-') ?></td>
                    <td>
                        <span class="font-semibold"><?= formatPrice($product['price']) ?></span>
                        <?php if ($product['compare_price']): ?>
                        <br><span class="text-muted text-sm" style="text-decoration: line-through;"><?= formatPrice($product['compare_price']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($product['stock'] == 0): ?>
                        <span class="admin-badge out-of-stock">Out of Stock</span>
                        <?php elseif ($product['stock'] <= ($product['low_stock_threshold'] ?? 10)): ?>
                        <span class="admin-badge low-stock"><?= $product['stock'] ?> left</span>
                        <?php else: ?>
                        <span class="text-muted"><?= $product['stock'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($product['status']): ?>
                        <span class="admin-badge active">Active</span>
                        <?php else: ?>
                        <span class="admin-badge inactive">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="admin-table-actions">
                            <a href="<?= url('/products/' . $product['slug']) ?>" target="_blank"
                               class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="View">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                </svg>
                            </a>
                            <a href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>"
                               class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            <button type="button" class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon"
                                    onclick="deleteProduct(<?= $product['id'] ?>)" title="Delete">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

    <?php if ($pagination['total'] > 1): ?>
    <div class="admin-pagination">
        <div class="admin-pagination-info">
            Showing <?= (($pagination['current'] - 1) * $pagination['perPage']) + 1 ?>
            to <?= min($pagination['current'] * $pagination['perPage'], $pagination['count']) ?>
            of <?= number_format($pagination['count']) ?> products
        </div>
        <div class="admin-pagination-nav">
            <?php if ($pagination['current'] > 1): ?>
            <a href="?page=<?= $pagination['current'] - 1 ?>&<?= http_build_query($filters) ?>"
               class="admin-pagination-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <?php endif; ?>

            <?php
            $start = max(1, $pagination['current'] - 2);
            $end = min($pagination['total'], $pagination['current'] + 2);
            ?>

            <?php if ($start > 1): ?>
            <a href="?page=1&<?= http_build_query($filters) ?>" class="admin-pagination-btn">1</a>
            <?php if ($start > 2): ?>
            <span class="admin-pagination-btn" style="border: none;">...</span>
            <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
            <a href="?page=<?= $i ?>&<?= http_build_query($filters) ?>"
               class="admin-pagination-btn <?= $i === $pagination['current'] ? 'active' : '' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($end < $pagination['total']): ?>
            <?php if ($end < $pagination['total'] - 1): ?>
            <span class="admin-pagination-btn" style="border: none;">...</span>
            <?php endif; ?>
            <a href="?page=<?= $pagination['total'] ?>&<?= http_build_query($filters) ?>" class="admin-pagination-btn">
                <?= $pagination['total'] ?>
            </a>
            <?php endif; ?>

            <?php if ($pagination['current'] < $pagination['total']): ?>
            <a href="?page=<?= $pagination['current'] + 1 ?>&<?= http_build_query($filters) ?>"
               class="admin-pagination-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
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
