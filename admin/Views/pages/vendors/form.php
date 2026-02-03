<!-- Admin Vendor Form -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= isset($vendor) ? 'Edit Vendor' : 'Add Vendor' ?></h1>
        <p class="admin-page-subtitle"><?= isset($vendor) ? 'Update vendor settings and API configuration' : 'Set up a new vendor integration' ?></p>
    </div>
    <a href="<?= url('/admin/vendors') ?>" class="admin-btn admin-btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Back to Vendors
    </a>
</div>

<form method="POST" action="<?= isset($vendor) ? url('/admin/vendors/' . $vendor['id']) : url('/admin/vendors') ?>" enctype="multipart/form-data" class="admin-form">
    <?= csrf_field() ?>
    <?php if (isset($vendor)): ?>
    <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="admin-grid admin-grid-2">
        <!-- Basic Info -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Basic Information</h2>
            </div>
            <div class="admin-card-body">
                <div class="admin-form-group">
                    <label class="admin-label">Vendor Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="admin-input" value="<?= e($vendor['name'] ?? '') ?>" required>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label">Description</label>
                    <textarea name="description" class="admin-textarea" rows="3"><?= e($vendor['description'] ?? '') ?></textarea>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label">Logo</label>
                    <?php if (!empty($vendor['logo'])): ?>
                    <div class="admin-current-image mb-3">
                        <img src="<?= e($vendor['logo']) ?>" alt="Current logo" style="max-width: 120px; max-height: 60px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="logo" class="admin-input" accept="image/*">
                    <p class="admin-help">Recommended: 200x100px PNG with transparent background</p>
                </div>

                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label class="admin-label">Commission Rate (%)</label>
                        <input type="number" name="commission_rate" class="admin-input" value="<?= e($vendor['commission_rate'] ?? '0') ?>" min="0" max="100" step="0.1">
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-label">Status</label>
                        <select name="status" class="admin-select">
                            <option value="pending" <?= ($vendor['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="active" <?= ($vendor['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="suspended" <?= ($vendor['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Configuration -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">API Configuration</h2>
            </div>
            <div class="admin-card-body">
                <div class="admin-form-group">
                    <label class="admin-label">API Endpoint / Feed URL</label>
                    <input type="url" name="api_endpoint" class="admin-input" value="<?= e($vendor['api_endpoint'] ?? '') ?>" placeholder="https://api.vendor.com/products">
                    <p class="admin-help">The URL to fetch product data from</p>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label">API Key / Token</label>
                    <input type="password" name="api_key" class="admin-input" value="<?= e($vendor['api_key'] ?? '') ?>" placeholder="Enter API key">
                    <p class="admin-help">Authentication key for the API</p>
                </div>

                <div class="admin-form-group">
                    <label class="admin-checkbox">
                        <input type="checkbox" name="sync_enabled" value="1" <?= ($vendor['sync_enabled'] ?? 0) ? 'checked' : '' ?>>
                        <span>Enable automatic sync</span>
                    </label>
                </div>

                <?php if (isset($vendor) && !empty($vendor['last_sync_at'])): ?>
                <div class="admin-form-group mt-4 pt-4" style="border-top: 1px solid var(--admin-border);">
                    <label class="admin-label">Last Sync</label>
                    <p class="text-muted"><?= date('M j, Y H:i', strtotime($vendor['last_sync_at'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="admin-card mt-6">
        <div class="admin-card-footer">
            <a href="<?= url('/admin/vendors') ?>" class="admin-btn admin-btn-ghost">Cancel</a>
            <button type="submit" class="admin-btn admin-btn-primary">
                <?= isset($vendor) ? 'Update Vendor' : 'Create Vendor' ?>
            </button>
        </div>
    </div>
</form>
