<!-- Admin Category Form -->
<div class="flex justify-between items-center mb-6">
    <h1 class="admin-page-title mb-0"><?= $category ? 'Edit Category' : 'Add Category' ?></h1>
    <a href="<?= url('/admin/categories') ?>" class="btn btn-ghost">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
        Back to Categories
    </a>
</div>

<form action="<?= $category ? url('/admin/categories/' . $category->id) : url('/admin/categories') ?>"
      method="POST" enctype="multipart/form-data" class="admin-form">
    <?= csrf_field() ?>
    <?php if ($category): ?>
    <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Category Information</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" value="<?= e($category->name ?? '') ?>"
                               class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="parent_id" class="form-label">Parent Category</label>
                        <select id="parent_id" name="parent_id" class="form-select">
                            <option value="">None (Top Level)</option>
                            <?php
                            function renderCategoryOptions($categories, $selectedId = null, $currentId = null, $level = 0) {
                                foreach ($categories as $cat) {
                                    // Skip current category and its children
                                    if ($currentId && $cat['id'] == $currentId) continue;

                                    $indent = str_repeat('— ', $level);
                                    $selected = $selectedId == $cat['id'] ? 'selected' : '';
                                    echo "<option value=\"{$cat['id']}\" {$selected}>{$indent}" . e($cat['name']) . "</option>";

                                    if (!empty($cat['children'])) {
                                        renderCategoryOptions($cat['children'], $selectedId, $currentId, $level + 1);
                                    }
                                }
                            }
                            renderCategoryOptions($categories, $category->parent_id ?? null, $category->id ?? null);
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="form-input"><?= e($category->description ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" min="0"
                               value="<?= $category->sort_order ?? 0 ?>" class="form-input" style="max-width: 100px;">
                        <p class="form-help">Lower numbers appear first</p>
                    </div>
                </div>
            </div>

            <!-- Image -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Category Image</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($category->image)): ?>
                    <div class="mb-4">
                        <img src="<?= url('storage/uploads/' . e($category->image)) ?>"
                             alt="" class="w-32 h-32 rounded object-cover">
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="image" class="form-label">Upload Image</label>
                        <input type="file" id="image" name="image" accept="image/*" class="form-input">
                        <p class="form-help">Recommended: 400x400px. Max size: 2MB</p>
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
                               value="<?= e($category->meta_title ?? '') ?>" class="form-input" maxlength="70">
                        <p class="form-help">Leave empty to use category name. Max 70 characters.</p>
                    </div>

                    <div class="form-group">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" rows="3"
                                  class="form-input" maxlength="160"><?= e($category->meta_description ?? '') ?></textarea>
                        <p class="form-help">Leave empty to use description. Max 160 characters.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Settings</h2>
                </div>
                <div class="card-body space-y-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               <?= ($category->is_active ?? true) ? 'checked' : '' ?> class="form-checkbox">
                        <span>Active</span>
                    </label>
                    <p class="form-help text-xs">Inactive categories are hidden from the storefront</p>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="show_in_menu" value="1"
                               <?= !empty($category->show_in_menu) ? 'checked' : '' ?> class="form-checkbox">
                        <span>Show in Menu</span>
                    </label>
                    <p class="form-help text-xs">Display this category in the navigation menu</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body space-y-3">
                    <button type="submit" class="btn btn-primary w-full">
                        <?= $category ? 'Update Category' : 'Create Category' ?>
                    </button>

                    <?php if ($category): ?>
                    <a href="<?= url('/category/' . $category->slug) ?>" target="_blank" class="btn btn-outline w-full">
                        View Category
                    </a>
                    <button type="button" onclick="deleteCategory(<?= $category->id ?>)" class="btn btn-danger btn-outline w-full">
                        Delete Category
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($category): ?>
            <!-- Info -->
            <div class="card">
                <div class="card-body text-sm text-muted">
                    <p>Slug: <code>/<?= e($category->slug) ?></code></p>
                    <p class="mt-1">Created: <?= date('d M Y', strtotime($category->created_at)) ?></p>
                    <?php if ($category->updated_at): ?>
                    <p class="mt-1">Updated: <?= date('d M Y', strtotime($category->updated_at)) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php if ($category): ?>
<script>
function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
        fetch('<?= url('/admin/categories/') ?>' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': '<?= csrf_token() ?>',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?= url('/admin/categories') ?>';
            }
        });
    }
}
</script>
<?php endif; ?>
