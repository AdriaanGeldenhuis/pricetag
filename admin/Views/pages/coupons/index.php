<!-- Admin Coupons List -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Coupons & Discounts</h1>
        <p class="admin-page-subtitle"><?= $stats['total'] ?> coupons total</p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/coupons/create') ?>" class="admin-btn admin-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Create Coupon
        </a>
    </div>
</div>

<!-- Stats -->
<div class="admin-stats-grid mb-6">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Active Coupons</div>
        <div class="admin-stat-value"><?= $stats['active'] ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Used Today</div>
        <div class="admin-stat-value"><?= $stats['used_today'] ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Total Savings Given</div>
        <div class="admin-stat-value">R <?= number_format($stats['total_savings'], 2) ?></div>
    </div>
</div>

<!-- Filters -->
<div class="admin-card mb-6">
    <form method="GET" class="admin-filters p-4">
        <div class="admin-filter-group">
            <label>Status</label>
            <select name="status" class="admin-select" onchange="this.form.submit()">
                <option value="">All Coupons</option>
                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="expired" <?= ($filters['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option>
                <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="admin-filter-group">
            <label>Search</label>
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>"
                   class="admin-input" placeholder="Search by code...">
        </div>
        <button type="submit" class="admin-btn admin-btn-secondary">Filter</button>
        <?php if (!empty($filters['status']) || !empty($filters['search'])): ?>
        <a href="<?= url('/admin/coupons') ?>" class="admin-btn admin-btn-ghost">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Coupons Table -->
<div class="admin-card">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Usage</th>
                    <th>Valid Period</th>
                    <th>Status</th>
                    <th style="width: 100px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($coupons)): ?>
                <tr>
                    <td colspan="7">
                        <div class="admin-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-empty-icon">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"></path>
                                <line x1="7" y1="7" x2="7.01" y2="7"></line>
                            </svg>
                            <h3 class="admin-empty-title">No coupons yet</h3>
                            <p class="admin-empty-text">Create your first discount coupon.</p>
                            <a href="<?= url('/admin/coupons/create') ?>" class="admin-btn admin-btn-primary">Create Coupon</a>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($coupons as $coupon):
                    $isExpired = $coupon['expires_at'] && strtotime($coupon['expires_at']) < time();
                    $isNotStarted = $coupon['starts_at'] && strtotime($coupon['starts_at']) > time();
                    $usageFull = $coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit'];
                ?>
                <tr>
                    <td>
                        <code class="coupon-code"><?= e($coupon['code']) ?></code>
                    </td>
                    <td>
                        <?php if ($coupon['type'] === 'percentage'): ?>
                        <span class="admin-badge neutral">Percentage</span>
                        <?php elseif ($coupon['type'] === 'fixed'): ?>
                        <span class="admin-badge neutral">Fixed Amount</span>
                        <?php else: ?>
                        <span class="admin-badge neutral">Free Shipping</span>
                        <?php endif; ?>
                    </td>
                    <td class="font-semibold">
                        <?php if ($coupon['type'] === 'percentage'): ?>
                        <?= number_format($coupon['value']) ?>%
                        <?php elseif ($coupon['type'] === 'fixed'): ?>
                        R <?= number_format($coupon['value'], 2) ?>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                        <?php if ($coupon['minimum_amount']): ?>
                        <span class="text-muted text-xs block">Min: R<?= number_format($coupon['minimum_amount']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="<?= $usageFull ? 'text-danger' : '' ?>">
                            <?= $coupon['used_count'] ?><?= $coupon['usage_limit'] ? '/' . $coupon['usage_limit'] : '' ?>
                        </span>
                    </td>
                    <td class="text-sm">
                        <?php if ($coupon['starts_at'] || $coupon['expires_at']): ?>
                        <div class="text-muted">
                            <?php if ($coupon['starts_at']): ?>
                            <div>From: <?= date('M j, Y', strtotime($coupon['starts_at'])) ?></div>
                            <?php endif; ?>
                            <?php if ($coupon['expires_at']): ?>
                            <div>Until: <?= date('M j, Y', strtotime($coupon['expires_at'])) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <span class="text-muted">No limits</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$coupon['is_active']): ?>
                        <span class="admin-badge inactive">Inactive</span>
                        <?php elseif ($isExpired): ?>
                        <span class="admin-badge danger">Expired</span>
                        <?php elseif ($isNotStarted): ?>
                        <span class="admin-badge warning">Scheduled</span>
                        <?php elseif ($usageFull): ?>
                        <span class="admin-badge warning">Fully Used</span>
                        <?php else: ?>
                        <span class="admin-badge active">Active</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="admin-table-actions">
                            <button type="button" onclick="toggleCoupon(<?= $coupon['id'] ?>, this)"
                                    class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="Toggle">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                            <a href="<?= url('/admin/coupons/' . $coupon['id'] . '/edit') ?>"
                               class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            <button type="button" onclick="deleteCoupon(<?= $coupon['id'] ?>, '<?= e($coupon['code']) ?>')"
                                    class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon text-danger" title="Delete">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
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
            <h3 class="admin-modal-title">Delete Coupon</h3>
            <button class="admin-modal-close" onclick="closeDeleteModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="admin-modal-body">
            <p>Are you sure you want to delete coupon "<strong id="couponCode"></strong>"? This action cannot be undone.</p>
        </div>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteForm" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Coupon</button>
            </form>
        </div>
    </div>
</div>

<style>
.coupon-code {
    font-family: monospace;
    font-size: var(--text-sm);
    font-weight: var(--font-bold);
    background: var(--color-background-alt);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-md);
    border: 1px dashed var(--color-border);
}

.admin-filters {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-4);
    align-items: flex-end;
}

.admin-filter-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.admin-filter-group label {
    font-size: var(--text-xs);
    font-weight: var(--font-medium);
    color: var(--color-text-muted);
}
</style>

<script>
function deleteCoupon(id, code) {
    document.getElementById('couponCode').textContent = code;
    document.getElementById('deleteForm').action = '/admin/coupons/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});

async function toggleCoupon(id, button) {
    try {
        const response = await fetch('/admin/coupons/' + id + '/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= csrf_token() ?>'
            }
        });

        const data = await response.json();
        if (data.success) {
            location.reload();
        }
    } catch (error) {
        console.error('Error toggling coupon:', error);
    }
}
</script>
