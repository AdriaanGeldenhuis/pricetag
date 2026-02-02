<!-- Admin Categories List -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle"><?= count($categories) ?> categories total</p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/categories/create') ?>" class="admin-btn admin-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Category
        </a>
    </div>
</div>

<!-- Categories Table -->
<div class="admin-card">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Slug</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Sort Order</th>
                    <th style="width: 100px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="6">
                        <div class="admin-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-empty-icon">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <h3 class="admin-empty-title">No categories yet</h3>
                            <p class="admin-empty-text">Create your first category to organize products.</p>
                            <a href="<?= url('/admin/categories/create') ?>" class="admin-btn admin-btn-primary">Add Category</a>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.75rem; padding-left: <?= $category['level'] * 1.5 ?>rem;">
                            <?php if ($category['image']): ?>
                            <img src="<?= e($category['image']) ?>" alt="<?= e($category['name']) ?>"
                                 class="admin-table-image" style="width: 40px; height: 40px;">
                            <?php else: ?>
                            <div style="width: 40px; height: 40px; background: var(--admin-bg); border-radius: var(--admin-radius); display: flex; align-items: center; justify-content: center;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; color: var(--admin-text-muted);">
                                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                                </svg>
                            </div>
                            <?php endif; ?>
                            <div>
                                <a href="<?= url('/admin/categories/' . $category['id'] . '/edit') ?>" class="font-semibold">
                                    <?= e($category['name']) ?>
                                </a>
                                <?php if ($category['child_count'] > 0): ?>
                                <span class="text-muted" style="font-size: 0.75rem;">(<?= $category['child_count'] ?> subcategories)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="text-muted"><?= e($category['slug']) ?></td>
                    <td>
                        <?php if ($category['product_count'] > 0): ?>
                        <a href="<?= url('/admin/products?category=' . $category['id']) ?>" class="text-primary">
                            <?= $category['product_count'] ?> products
                        </a>
                        <?php else: ?>
                        <span class="text-muted">0</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($category['is_active']): ?>
                        <span class="admin-badge active">Active</span>
                        <?php else: ?>
                        <span class="admin-badge inactive">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= $category['sort_order'] ?></td>
                    <td>
                        <div class="admin-table-actions">
                            <a href="<?= url('/categories/' . $category['slug']) ?>" target="_blank"
                               class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="View">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                </svg>
                            </a>
                            <a href="<?= url('/admin/categories/' . $category['id'] . '/edit') ?>"
                               class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            <?php if ($category['product_count'] == 0 && $category['child_count'] == 0): ?>
                            <button type="button" class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon"
                                    onclick="deleteCategory(<?= $category['id'] ?>, '<?= e($category['name']) ?>')" title="Delete">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Modal -->
<div class="admin-modal-overlay" id="deleteModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Delete Category</h3>
            <button class="admin-modal-close" onclick="closeDeleteModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="admin-modal-body">
            <p>Are you sure you want to delete "<strong id="categoryName"></strong>"? This action cannot be undone.</p>
        </div>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteForm" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Category</button>
            </form>
        </div>
    </div>
</div>

<script>
function deleteCategory(id, name) {
    document.getElementById('categoryName').textContent = name;
    document.getElementById('deleteForm').action = '/admin/categories/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>
