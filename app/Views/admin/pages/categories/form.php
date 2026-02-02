<!-- Admin Category Form -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle">
            <?= $category ? 'Edit category details' : 'Create a new category' ?>
        </p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/categories') ?>" class="admin-btn admin-btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Categories
        </a>
    </div>
</div>

<?php $formData = $_SESSION['form_data'] ?? []; unset($_SESSION['form_data']); ?>
<?php $errors = $_SESSION['form_errors'] ?? []; unset($_SESSION['form_errors']); ?>

<form method="POST" action="<?= $category ? url('/admin/categories/' . $category['id']) : url('/admin/categories') ?>"
      enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <?php if ($category): ?>
    <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- Main Content -->
        <div>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Category Details</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <label class="admin-form-label required">Category Name</label>
                        <input type="text" name="name" value="<?= e($formData['name'] ?? $category['name'] ?? '') ?>"
                               class="admin-form-input <?= isset($errors['name']) ? 'error' : '' ?>" required>
                        <?php if (isset($errors['name'])): ?>
                        <p class="admin-form-error"><?= e($errors['name']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Parent Category</label>
                        <select name="parent_id" class="admin-form-select">
                            <option value="">None (Top Level)</option>
                            <?php foreach ($parentCategories as $parent): ?>
                            <option value="<?= $parent['id'] ?>"
                                <?= ($formData['parent_id'] ?? $category['parent_id'] ?? '') == $parent['id'] ? 'selected' : '' ?>>
                                <?= e($parent['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="admin-form-hint">Leave empty for a top-level category</p>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Description</label>
                        <textarea name="description" class="admin-form-textarea" rows="4"
                                  placeholder="Category description for SEO and display..."><?= e($formData['description'] ?? $category['description'] ?? '') ?></textarea>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Category Image</label>
                        <?php if (!empty($category['image'])): ?>
                        <div style="margin-bottom: 1rem;">
                            <img src="<?= e($category['image']) ?>" alt="Current image"
                                 style="max-width: 200px; border-radius: var(--admin-radius);">
                        </div>
                        <?php endif; ?>
                        <div class="admin-image-upload" onclick="document.getElementById('imageInput').click()" style="padding: 1.5rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-image-upload-icon">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <p class="admin-image-upload-text">Click to upload image</p>
                            <p class="admin-image-upload-hint">PNG, JPG, WEBP up to 2MB</p>
                        </div>
                        <input type="file" id="imageInput" name="image" accept="image/*" style="display: none;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Status</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group" style="margin-bottom: 0;">
                        <label class="admin-checkbox-group">
                            <input type="checkbox" name="is_active" value="1" class="admin-checkbox"
                                <?= ($formData['is_active'] ?? $category['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <span>Active (visible in store)</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Display Order</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group" style="margin-bottom: 0;">
                        <label class="admin-form-label">Sort Order</label>
                        <input type="number" name="sort_order" min="0"
                               value="<?= e($formData['sort_order'] ?? $category['sort_order'] ?? 0) ?>"
                               class="admin-form-input">
                        <p class="admin-form-hint">Lower numbers appear first</p>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-body">
                    <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; margin-bottom: 0.75rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <?= $category ? 'Update Category' : 'Create Category' ?>
                    </button>

                    <?php if ($category): ?>
                    <?php
                    $productCount = db()->query("SELECT COUNT(*) FROM products WHERE category_id = ?", [$category['id']])->fetchColumn();
                    $childCount = db()->query("SELECT COUNT(*) FROM categories WHERE parent_id = ?", [$category['id']])->fetchColumn();
                    ?>
                    <?php if ($productCount == 0 && $childCount == 0): ?>
                    <button type="button" class="admin-btn admin-btn-danger" style="width: 100%;" onclick="showDeleteModal()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        Delete Category
                    </button>
                    <?php else: ?>
                    <p class="text-muted text-center" style="font-size: 0.75rem; margin-top: 0.5rem;">
                        Cannot delete: has <?= $productCount ?> products<?= $childCount > 0 ? " and {$childCount} subcategories" : '' ?>
                    </p>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<?php if ($category): ?>
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
            <p>Are you sure you want to delete "<strong><?= e($category['name']) ?></strong>"? This action cannot be undone.</p>
        </div>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form method="POST" action="<?= url('/admin/categories/' . $category['id']) ?>" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Category</button>
            </form>
        </div>
    </div>
</div>

<script>
function showDeleteModal() {
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>
<?php endif; ?>
