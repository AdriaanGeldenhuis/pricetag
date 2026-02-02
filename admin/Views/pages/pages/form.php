<!-- Admin CMS Page Form -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle"><?= isset($page) ? 'Edit page details' : 'Create a new CMS page' ?></p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/pages') ?>" class="admin-btn admin-btn-ghost">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Pages
        </a>
    </div>
</div>

<form method="POST" action="<?= isset($page) ? url('/admin/pages/' . $page['id']) : url('/admin/pages') ?>" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <?php if (isset($page)): ?>
    <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="admin-form-grid">
        <!-- Main Content -->
        <div class="admin-form-main">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Page Content</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <label class="admin-label required">Title</label>
                        <input type="text" name="title" id="title" class="admin-input"
                               value="<?= e($page['title'] ?? '') ?>" required>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label required">Slug</label>
                        <div class="admin-input-group">
                            <span class="admin-input-prefix">/page/</span>
                            <input type="text" name="slug" id="slug" class="admin-input"
                                   value="<?= e($page['slug'] ?? '') ?>" required>
                        </div>
                        <p class="admin-help">The URL-friendly version of the title.</p>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label">Content</label>
                        <textarea name="content" id="content" class="admin-textarea" rows="20"
                                  placeholder="Enter page content (HTML supported)..."><?= e($page['content'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- SEO Settings -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">SEO Settings</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <label class="admin-label">Meta Title</label>
                        <input type="text" name="meta_title" class="admin-input"
                               value="<?= e($page['meta_title'] ?? '') ?>"
                               placeholder="Enter meta title for search engines">
                        <p class="admin-help">Leave empty to use page title.</p>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label">Meta Description</label>
                        <textarea name="meta_description" class="admin-textarea" rows="3"
                                  placeholder="Enter meta description for search engines..."><?= e($page['meta_description'] ?? '') ?></textarea>
                        <p class="admin-help">Recommended: 150-160 characters.</p>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="admin-input"
                               value="<?= e($page['meta_keywords'] ?? '') ?>"
                               placeholder="keyword1, keyword2, keyword3">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="admin-form-sidebar">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Publish</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <label class="admin-label">Status</label>
                        <select name="status" class="admin-select">
                            <option value="draft" <?= ($page['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= ($page['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label">Template</label>
                        <select name="template" class="admin-select">
                            <option value="default" <?= ($page['template'] ?? '') === 'default' ? 'selected' : '' ?>>Default</option>
                            <option value="full-width" <?= ($page['template'] ?? '') === 'full-width' ? 'selected' : '' ?>>Full Width</option>
                            <option value="sidebar" <?= ($page['template'] ?? '') === 'sidebar' ? 'selected' : '' ?>>With Sidebar</option>
                            <option value="contact" <?= ($page['template'] ?? '') === 'contact' ? 'selected' : '' ?>>Contact Page</option>
                            <option value="landing" <?= ($page['template'] ?? '') === 'landing' ? 'selected' : '' ?>>Landing Page</option>
                        </select>
                    </div>

                    <?php if (isset($page)): ?>
                    <div class="admin-form-meta">
                        <p><strong>Created:</strong> <?= date('M j, Y g:i A', strtotime($page['created_at'])) ?></p>
                        <?php if (!empty($page['updated_at'])): ?>
                        <p><strong>Updated:</strong> <?= date('M j, Y g:i A', strtotime($page['updated_at'])) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="admin-card-footer">
                    <button type="submit" name="action" value="save" class="admin-btn admin-btn-primary admin-btn-block">
                        <?= isset($page) ? 'Update Page' : 'Create Page' ?>
                    </button>
                </div>
            </div>

            <!-- Featured Image -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Featured Image</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <div class="admin-image-upload" id="imageUpload">
                            <?php if (!empty($page['featured_image'])): ?>
                            <img src="<?= e($page['featured_image']) ?>" alt="Featured Image" id="imagePreview">
                            <input type="hidden" name="featured_image" value="<?= e($page['featured_image']) ?>">
                            <?php else: ?>
                            <div class="admin-image-placeholder" id="imagePlaceholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <p>Click to upload image</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="featured_image_file" id="imageInput" accept="image/*" style="display: none;">
                        <p class="admin-help">Recommended size: 1200x630 pixels.</p>
                    </div>
                </div>
            </div>

            <!-- Page Settings -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Settings</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="show_in_menu" value="1"
                                   <?= !empty($page['show_in_menu']) ? 'checked' : '' ?>>
                            <span>Show in navigation menu</span>
                        </label>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="show_in_footer" value="1"
                                   <?= !empty($page['show_in_footer']) ? 'checked' : '' ?>>
                            <span>Show in footer links</span>
                        </label>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="allow_comments" value="1"
                                   <?= !empty($page['allow_comments']) ? 'checked' : '' ?>>
                            <span>Allow comments</span>
                        </label>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label">Sort Order</label>
                        <input type="number" name="sort_order" class="admin-input"
                               value="<?= e($page['sort_order'] ?? 0) ?>" min="0">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
.admin-form-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
}

.admin-form-main {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.admin-form-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.admin-input-group {
    display: flex;
    align-items: center;
}

.admin-input-prefix {
    padding: 0.625rem 0.75rem;
    background: var(--admin-bg);
    border: 1px solid var(--admin-border);
    border-right: none;
    border-radius: var(--admin-radius) 0 0 var(--admin-radius);
    color: var(--admin-text-muted);
    font-size: 0.875rem;
}

.admin-input-group .admin-input {
    border-radius: 0 var(--admin-radius) var(--admin-radius) 0;
}

.admin-form-meta {
    padding-top: 1rem;
    border-top: 1px solid var(--admin-border);
    font-size: 0.875rem;
    color: var(--admin-text-muted);
}

.admin-form-meta p {
    margin-bottom: 0.25rem;
}

.admin-image-upload {
    border: 2px dashed var(--admin-border);
    border-radius: var(--admin-radius);
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s;
}

.admin-image-upload:hover {
    border-color: var(--admin-primary);
}

.admin-image-upload img {
    max-width: 100%;
    border-radius: var(--admin-radius);
}

.admin-image-placeholder {
    padding: 2rem;
    color: var(--admin-text-muted);
}

.admin-image-placeholder svg {
    width: 48px;
    height: 48px;
    margin-bottom: 0.5rem;
}

.admin-card-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--admin-border);
}

.admin-btn-block {
    width: 100%;
}

@media (max-width: 1024px) {
    .admin-form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Auto-generate slug from title
document.getElementById('title').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (!slugInput.dataset.edited) {
        slugInput.value = this.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
    }
});

document.getElementById('slug').addEventListener('input', function() {
    this.dataset.edited = 'true';
});

// Image upload
document.getElementById('imageUpload').addEventListener('click', function() {
    document.getElementById('imageInput').click();
});

document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const upload = document.getElementById('imageUpload');
            upload.innerHTML = '<img src="' + e.target.result + '" alt="Preview" id="imagePreview">';
        };
        reader.readAsDataURL(file);
    }
});
</script>
