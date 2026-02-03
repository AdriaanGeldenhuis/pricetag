<!-- Admin Vendors -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Vendors</h1>
        <p class="admin-page-subtitle">Manage marketplace vendors and API integrations</p>
    </div>
    <a href="<?= url('/admin/vendors/create') ?>" class="admin-btn admin-btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Add Vendor
    </a>
</div>

<!-- Stats -->
<div class="admin-stats-grid mb-6">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Total Vendors</div>
        <div class="admin-stat-value"><?= count($vendors ?? []) ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Active</div>
        <div class="admin-stat-value text-success"><?= count(array_filter($vendors ?? [], fn($v) => $v['status'] === 'active')) ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Total Products</div>
        <div class="admin-stat-value"><?= number_format(array_sum(array_column($vendors ?? [], 'product_count'))) ?></div>
    </div>
</div>

<!-- Vendors Grid -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">All Vendors</h2>
    </div>
    <div class="admin-card-body">
        <?php if (empty($vendors)): ?>
        <div class="admin-empty py-8">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" class="admin-empty-icon">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <h3 class="admin-empty-title">No vendors yet</h3>
            <p class="admin-empty-text">Add vendors to import and manage products from external sources.</p>
            <a href="<?= url('/admin/vendors/create') ?>" class="admin-btn admin-btn-primary">Add First Vendor</a>
        </div>
        <?php else: ?>
        <div class="admin-vendor-grid">
            <?php foreach ($vendors as $vendor): ?>
            <div class="admin-vendor-card">
                <div class="admin-vendor-header">
                    <?php if (!empty($vendor['logo'])): ?>
                    <img src="<?= e($vendor['logo']) ?>" alt="<?= e($vendor['name']) ?>" class="admin-vendor-logo">
                    <?php else: ?>
                    <div class="admin-vendor-logo-placeholder">
                        <?= strtoupper(substr($vendor['name'], 0, 2)) ?>
                    </div>
                    <?php endif; ?>
                    <div class="admin-vendor-info">
                        <h3 class="admin-vendor-name"><?= e($vendor['name']) ?></h3>
                        <span class="admin-badge <?= $vendor['status'] === 'active' ? 'active' : ($vendor['status'] === 'pending' ? 'warning' : 'inactive') ?> sm">
                            <?= ucfirst($vendor['status']) ?>
                        </span>
                    </div>
                </div>

                <div class="admin-vendor-stats">
                    <div class="admin-vendor-stat">
                        <span class="admin-vendor-stat-value"><?= number_format($vendor['product_count'] ?? 0) ?></span>
                        <span class="admin-vendor-stat-label">Products</span>
                    </div>
                    <div class="admin-vendor-stat">
                        <span class="admin-vendor-stat-value"><?= $vendor['commission_rate'] ?? 0 ?>%</span>
                        <span class="admin-vendor-stat-label">Commission</span>
                    </div>
                    <div class="admin-vendor-stat">
                        <span class="admin-vendor-stat-value"><?= $vendor['last_sync_at'] ? date('M j', strtotime($vendor['last_sync_at'])) : 'Never' ?></span>
                        <span class="admin-vendor-stat-label">Last Sync</span>
                    </div>
                </div>

                <?php if ($vendor['sync_enabled']): ?>
                <div class="admin-vendor-api">
                    <span class="text-success text-xs">Auto-sync enabled</span>
                </div>
                <?php endif; ?>

                <div class="admin-vendor-actions">
                    <a href="<?= url('/admin/vendors/' . $vendor['id'] . '/edit') ?>" class="admin-btn admin-btn-ghost admin-btn-sm">Edit</a>
                    <?php if (!empty($vendor['api_endpoint'])): ?>
                    <form method="POST" action="<?= url('/admin/vendors/' . $vendor['id'] . '/sync') ?>" style="display: inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm">Sync Now</button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" action="<?= url('/admin/vendors/' . $vendor['id']) ?>" style="display: inline;" onsubmit="return confirm('Delete this vendor? This will NOT delete their products.')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="admin-btn admin-btn-ghost admin-btn-sm text-danger">Delete</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-vendor-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}
.admin-vendor-card {
    background: var(--admin-bg-secondary);
    border: 1px solid var(--admin-border);
    border-radius: 12px;
    padding: 20px;
}
.admin-vendor-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.admin-vendor-logo {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    object-fit: contain;
    background: white;
}
.admin-vendor-logo-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    background: var(--admin-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 16px;
}
.admin-vendor-info {
    flex: 1;
}
.admin-vendor-name {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 4px 0;
}
.admin-vendor-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    padding: 16px 0;
    border-top: 1px solid var(--admin-border);
    border-bottom: 1px solid var(--admin-border);
}
.admin-vendor-stat {
    text-align: center;
}
.admin-vendor-stat-value {
    display: block;
    font-size: 18px;
    font-weight: 600;
}
.admin-vendor-stat-label {
    display: block;
    font-size: 11px;
    color: var(--admin-text-muted);
    text-transform: uppercase;
}
.admin-vendor-api {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
}
.admin-vendor-actions {
    display: flex;
    gap: 8px;
    padding-top: 12px;
}
</style>
