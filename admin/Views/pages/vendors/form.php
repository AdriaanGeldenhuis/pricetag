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
                    <label class="admin-label">Slug</label>
                    <input type="text" name="slug" class="admin-input" value="<?= e($vendor['slug'] ?? '') ?>" placeholder="auto-generated-from-name">
                    <p class="admin-help">URL-friendly identifier. Leave blank to auto-generate.</p>
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

                <div class="admin-form-group">
                    <label class="admin-label">Website URL</label>
                    <input type="url" name="website" class="admin-input" value="<?= e($vendor['website'] ?? '') ?>" placeholder="https://vendor.com">
                </div>

                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label class="admin-label">Commission Rate (%)</label>
                        <input type="number" name="commission_rate" class="admin-input" value="<?= e($vendor['commission_rate'] ?? '0') ?>" min="0" max="100" step="0.1">
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-label">Status</label>
                        <select name="is_active" class="admin-select">
                            <option value="1" <?= ($vendor['is_active'] ?? 1) ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= isset($vendor) && !$vendor['is_active'] ? 'selected' : '' ?>>Inactive</option>
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
                    <label class="admin-label">API Type</label>
                    <select name="api_type" class="admin-select" id="apiType" onchange="toggleApiFields()">
                        <option value="">None (Manual)</option>
                        <option value="rest" <?= ($vendor['api_type'] ?? '') === 'rest' ? 'selected' : '' ?>>REST API</option>
                        <option value="csv" <?= ($vendor['api_type'] ?? '') === 'csv' ? 'selected' : '' ?>>CSV Feed</option>
                        <option value="xml" <?= ($vendor['api_type'] ?? '') === 'xml' ? 'selected' : '' ?>>XML Feed</option>
                        <option value="woocommerce" <?= ($vendor['api_type'] ?? '') === 'woocommerce' ? 'selected' : '' ?>>WooCommerce</option>
                        <option value="shopify" <?= ($vendor['api_type'] ?? '') === 'shopify' ? 'selected' : '' ?>>Shopify</option>
                    </select>
                </div>

                <div id="apiFields" style="<?= empty($vendor['api_type']) ? 'display: none;' : '' ?>">
                    <div class="admin-form-group">
                        <label class="admin-label">API Endpoint / Feed URL</label>
                        <input type="url" name="api_endpoint" class="admin-input" value="<?= e($vendor['api_endpoint'] ?? '') ?>" placeholder="https://api.vendor.com/products">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label">API Key / Token</label>
                        <input type="password" name="api_key" class="admin-input" value="<?= e($vendor['api_key'] ?? '') ?>" placeholder="Enter API key">
                        <p class="admin-help">Stored encrypted. Leave blank to keep existing.</p>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label">API Secret (if required)</label>
                        <input type="password" name="api_secret" class="admin-input" value="<?= e($vendor['api_secret'] ?? '') ?>" placeholder="Enter API secret">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="sync_enabled" value="1" <?= ($vendor['sync_enabled'] ?? 0) ? 'checked' : '' ?>>
                            <span>Enable automatic sync</span>
                        </label>
                    </div>

                    <div class="admin-form-group" id="syncIntervalField" style="<?= empty($vendor['sync_enabled']) ? 'display: none;' : '' ?>">
                        <label class="admin-label">Sync Interval</label>
                        <select name="sync_interval" class="admin-select">
                            <option value="hourly" <?= ($vendor['sync_interval'] ?? '') === 'hourly' ? 'selected' : '' ?>>Every Hour</option>
                            <option value="daily" <?= ($vendor['sync_interval'] ?? 'daily') === 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="weekly" <?= ($vendor['sync_interval'] ?? '') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                        </select>
                    </div>
                </div>

                <?php if (isset($vendor) && !empty($vendor['last_sync'])): ?>
                <div class="admin-form-group mt-4 pt-4" style="border-top: 1px solid var(--admin-border);">
                    <label class="admin-label">Sync Status</label>
                    <div class="admin-sync-status">
                        <div class="admin-sync-info">
                            <span class="text-muted">Last sync:</span>
                            <span class="font-medium"><?= date('M j, Y H:i', strtotime($vendor['last_sync'])) ?></span>
                        </div>
                        <?php if (!empty($vendor['last_sync_status'])): ?>
                        <span class="admin-badge <?= $vendor['last_sync_status'] === 'success' ? 'active' : 'danger' ?>">
                            <?= ucfirst($vendor['last_sync_status']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($vendor['last_sync_message'])): ?>
                    <p class="text-muted text-sm mt-2"><?= e($vendor['last_sync_message']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="admin-card mt-6">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Contact Information</h2>
        </div>
        <div class="admin-card-body">
            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label class="admin-label">Contact Name</label>
                    <input type="text" name="contact_name" class="admin-input" value="<?= e($vendor['contact_name'] ?? '') ?>">
                </div>
                <div class="admin-form-group">
                    <label class="admin-label">Contact Email</label>
                    <input type="email" name="contact_email" class="admin-input" value="<?= e($vendor['contact_email'] ?? '') ?>">
                </div>
                <div class="admin-form-group">
                    <label class="admin-label">Contact Phone</label>
                    <input type="tel" name="contact_phone" class="admin-input" value="<?= e($vendor['contact_phone'] ?? '') ?>">
                </div>
            </div>
        </div>
        <div class="admin-card-footer">
            <a href="<?= url('/admin/vendors') ?>" class="admin-btn admin-btn-ghost">Cancel</a>
            <button type="submit" class="admin-btn admin-btn-primary">
                <?= isset($vendor) ? 'Update Vendor' : 'Create Vendor' ?>
            </button>
        </div>
    </div>
</form>

<style>
.admin-sync-status {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px;
    background: var(--admin-bg-secondary);
    border-radius: 8px;
}
.admin-sync-info {
    display: flex;
    gap: 8px;
}
</style>

<script>
function toggleApiFields() {
    const apiType = document.getElementById('apiType').value;
    document.getElementById('apiFields').style.display = apiType ? 'block' : 'none';
}

document.querySelector('input[name="sync_enabled"]')?.addEventListener('change', function() {
    document.getElementById('syncIntervalField').style.display = this.checked ? 'block' : 'none';
});
</script>
