<!-- Admin Categories List -->
<div class="flex justify-between items-center mb-6">
    <h1 class="admin-page-title mb-0">Categories</h1>
    <a href="<?= url('/admin/categories/create') ?>" class="btn btn-primary">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add Category
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($categories)): ?>
        <div class="text-center py-12">
            <p class="text-muted mb-4">No categories found</p>
            <a href="<?= url('/admin/categories/create') ?>" class="btn btn-primary">Add your first category</a>
        </div>
        <?php else: ?>
        <div class="category-tree">
            <?php
            function renderCategoryTree($categories, $level = 0) {
                foreach ($categories as $category) {
                    $indent = $level * 24;
                    $hasChildren = !empty($category['children']);
                    ?>
                    <div class="category-tree-item" style="padding-left: <?= $indent ?>px;">
                        <div class="flex items-center gap-3 py-3 px-2 hover:bg-neutral-50 rounded">
                            <div class="flex-shrink-0">
                                <?php if ($category['image']): ?>
                                <img src="<?= url('storage/uploads/' . e($category['image'])) ?>"
                                     alt="" class="w-10 h-10 rounded object-cover">
                                <?php else: ?>
                                <div class="w-10 h-10 rounded bg-neutral-100 flex items-center justify-center">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted">
                                        <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>
                                    </svg>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex-1 min-w-0">
                                <a href="<?= url('/admin/categories/' . $category['id'] . '/edit') ?>" class="font-medium text-primary hover:underline">
                                    <?= e($category['name']) ?>
                                </a>
                                <p class="text-xs text-muted truncate">/<?= e($category['slug']) ?></p>
                            </div>

                            <div class="flex items-center gap-2">
                                <?php if ($category['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                <span class="badge badge-neutral">Inactive</span>
                                <?php endif; ?>

                                <?php if ($category['show_in_menu']): ?>
                                <span class="badge badge-primary">Menu</span>
                                <?php endif; ?>
                            </div>

                            <div class="flex gap-1">
                                <a href="<?= url('/admin/categories/' . $category['id'] . '/edit') ?>"
                                   class="btn btn-ghost btn-sm btn-icon" title="Edit">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                <a href="<?= url('/category/' . $category['slug']) ?>" target="_blank"
                                   class="btn btn-ghost btn-sm btn-icon" title="View">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                                        <polyline points="15 3 21 3 21 9"/>
                                        <line x1="10" y1="14" x2="21" y2="3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                    if ($hasChildren) {
                        renderCategoryTree($category['children'], $level + 1);
                    }
                }
            }
            renderCategoryTree($categories);
            ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.category-tree-item {
    border-bottom: 1px solid var(--border-color);
}
.category-tree-item:last-child {
    border-bottom: none;
}
</style>
