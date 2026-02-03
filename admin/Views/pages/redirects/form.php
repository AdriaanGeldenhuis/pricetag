<!-- Admin Redirect Form -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= isset($redirect) ? 'Edit Redirect' : 'Create Redirect' ?></h1>
        <p class="admin-page-subtitle"><?= isset($redirect) ? 'Update redirect settings' : 'Set up a new URL redirect' ?></p>
    </div>
    <a href="<?= url('/admin/redirects') ?>" class="admin-btn admin-btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Back to Redirects
    </a>
</div>

<form method="POST" action="<?= isset($redirect) ? url('/admin/redirects/' . $redirect['id']) : url('/admin/redirects') ?>" class="admin-form">
    <?= csrf_field() ?>
    <?php if (isset($redirect)): ?>
    <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Redirect Settings</h2>
        </div>
        <div class="admin-card-body">
            <div class="admin-form-group">
                <label class="admin-label">From URL <span class="text-danger">*</span></label>
                <div class="admin-input-group">
                    <span class="admin-input-prefix"><?= rtrim(url(''), '/') ?></span>
                    <input type="text" name="from_url" class="admin-input" value="<?= e($redirect['from_url'] ?? '') ?>" placeholder="/old-page" required>
                </div>
                <p class="admin-help">The URL path to redirect from (e.g., /old-page or /products/old-item)</p>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">To URL <span class="text-danger">*</span></label>
                <input type="text" name="to_url" class="admin-input" value="<?= e($redirect['to_url'] ?? '') ?>" placeholder="/new-page or https://example.com/page" required>
                <p class="admin-help">The URL to redirect to. Can be relative (/new-page) or absolute (https://...)</p>
            </div>

            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label class="admin-label">Redirect Type</label>
                    <select name="status_code" class="admin-select">
                        <option value="301" <?= ($redirect['status_code'] ?? 301) == 301 ? 'selected' : '' ?>>301 - Permanent</option>
                        <option value="302" <?= ($redirect['status_code'] ?? 301) == 302 ? 'selected' : '' ?>>302 - Temporary</option>
                    </select>
                    <p class="admin-help">301 is best for SEO when a page permanently moves. 302 for temporary redirects.</p>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label">Status</label>
                    <select name="is_active" class="admin-select">
                        <option value="1" <?= ($redirect['is_active'] ?? 1) ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= isset($redirect) && !$redirect['is_active'] ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="admin-card-footer">
            <a href="<?= url('/admin/redirects') ?>" class="admin-btn admin-btn-ghost">Cancel</a>
            <button type="submit" class="admin-btn admin-btn-primary">
                <?= isset($redirect) ? 'Update Redirect' : 'Create Redirect' ?>
            </button>
        </div>
    </div>
</form>

<style>
.admin-input-group {
    display: flex;
    align-items: stretch;
}
.admin-input-prefix {
    display: flex;
    align-items: center;
    padding: 0 12px;
    background: var(--admin-bg-tertiary);
    border: 1px solid var(--admin-border);
    border-right: 0;
    border-radius: 6px 0 0 6px;
    color: var(--admin-text-muted);
    font-size: 13px;
    white-space: nowrap;
}
.admin-input-group .admin-input {
    border-radius: 0 6px 6px 0;
    flex: 1;
}
</style>
