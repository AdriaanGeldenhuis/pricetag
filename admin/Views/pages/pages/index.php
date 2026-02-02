<!-- Admin CMS Pages List -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle"><?= count($pages) ?> pages total</p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/pages/create') ?>" class="admin-btn admin-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Page
        </a>
    </div>
</div>

<!-- Filters -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <form method="GET" class="admin-filters">
        <div class="admin-filter-group">
            <label>Status</label>
            <select name="status" class="admin-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
            </select>
        </div>
        <div class="admin-filter-group">
            <label>Search</label>
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>"
                   class="admin-input" placeholder="Search pages...">
        </div>
        <button type="submit" class="admin-btn admin-btn-secondary">Filter</button>
        <?php if (!empty($filters['status']) || !empty($filters['search'])): ?>
        <a href="<?= url('/admin/pages') ?>" class="admin-btn admin-btn-ghost">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Pages Table -->
<div class="admin-card">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Template</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th style="width: 100px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pages)): ?>
                <tr>
                    <td colspan="6">
                        <div class="admin-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-empty-icon">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            <h3 class="admin-empty-title">No pages yet</h3>
                            <p class="admin-empty-text">Create your first CMS page.</p>
                            <a href="<?= url('/admin/pages/create') ?>" class="admin-btn admin-btn-primary">Add Page</a>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($pages as $page): ?>
                <tr>
                    <td>
                        <a href="<?= url('/admin/pages/' . $page['id'] . '/edit') ?>" class="font-semibold">
                            <?= e($page['title']) ?>
                        </a>
                        <?php if (!empty($page['meta_title'])): ?>
                        <span class="text-muted" style="font-size: 0.75rem; display: block;">
                            <?= e(substr($page['meta_title'], 0, 50)) ?>...
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted">/<?= e($page['slug']) ?></td>
                    <td>
                        <span class="admin-badge neutral"><?= e(ucfirst($page['template'] ?? 'default')) ?></span>
                    </td>
                    <td>
                        <?php if ($page['status'] === 'published'): ?>
                        <span class="admin-badge active">Published</span>
                        <?php else: ?>
                        <span class="admin-badge inactive">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted">
                        <?= date('M j, Y', strtotime($page['updated_at'] ?? $page['created_at'])) ?>
                    </td>
                    <td>
                        <div class="admin-table-actions">
                            <a href="<?= url('/page/' . $page['slug']) ?>" target="_blank"
                               class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="View">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                </svg>
                            </a>
                            <a href="<?= url('/admin/pages/' . $page['id'] . '/edit') ?>"
                               class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            <button type="button" class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon"
                                    onclick="deletePage(<?= $page['id'] ?>, '<?= e(addslashes($page['title'])) ?>')" title="Delete">
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
</div>

<!-- Delete Modal -->
<div class="admin-modal-overlay" id="deleteModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Delete Page</h3>
            <button class="admin-modal-close" onclick="closeDeleteModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="admin-modal-body">
            <p>Are you sure you want to delete "<strong id="pageName"></strong>"? This action cannot be undone.</p>
        </div>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteForm" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Page</button>
            </form>
        </div>
    </div>
</div>

<script>
function deletePage(id, name) {
    document.getElementById('pageName').textContent = name;
    document.getElementById('deleteForm').action = '/admin/pages/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>
