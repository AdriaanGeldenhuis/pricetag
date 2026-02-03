<!-- Admin Newsletter Subscribers -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Newsletter Subscribers</h1>
        <p class="admin-page-subtitle"><?= $stats['total'] ?? 0 ?> subscribers total</p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/newsletter/export?status=subscribed') ?>" class="admin-btn admin-btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Export CSV
        </a>
    </div>
</div>

<!-- Stats -->
<div class="admin-stats-grid mb-6">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Active Subscribers</div>
        <div class="admin-stat-value"><?= $stats['active'] ?? 0 ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Unsubscribed</div>
        <div class="admin-stat-value"><?= $stats['unsubscribed'] ?? 0 ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">New Today</div>
        <div class="admin-stat-value"><?= $stats['today'] ?? 0 ?></div>
    </div>
</div>

<!-- Filters -->
<div class="admin-card mb-6">
    <form method="GET" class="admin-filters p-4">
        <div class="admin-filter-group">
            <label>Status</label>
            <select name="status" class="admin-select" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="subscribed" <?= ($filters['status'] ?? '') === 'subscribed' ? 'selected' : '' ?>>Subscribed</option>
                <option value="unsubscribed" <?= ($filters['status'] ?? '') === 'unsubscribed' ? 'selected' : '' ?>>Unsubscribed</option>
                <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
            </select>
        </div>
        <div class="admin-filter-group">
            <label>Search</label>
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" class="admin-input" placeholder="Search by email...">
        </div>
        <button type="submit" class="admin-btn admin-btn-secondary">Filter</button>
    </form>
</div>

<!-- Subscribers Table -->
<div class="admin-card">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Subscribed</th>
                    <th>Created</th>
                    <th style="width: 60px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subscribers)): ?>
                <tr>
                    <td colspan="5">
                        <div class="admin-empty">
                            <h3 class="admin-empty-title">No subscribers yet</h3>
                            <p class="admin-empty-text">Subscribers will appear here when they sign up.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($subscribers as $sub): ?>
                <tr>
                    <td><strong><?= e($sub['email']) ?></strong></td>
                    <td>
                        <?php if ($sub['status'] === 'subscribed'): ?>
                        <span class="admin-badge active">Subscribed</span>
                        <?php elseif ($sub['status'] === 'unsubscribed'): ?>
                        <span class="admin-badge inactive">Unsubscribed</span>
                        <?php else: ?>
                        <span class="admin-badge warning">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= $sub['subscribed_at'] ? date('M j, Y', strtotime($sub['subscribed_at'])) : '-' ?></td>
                    <td class="text-muted"><?= date('M j, Y', strtotime($sub['created_at'])) ?></td>
                    <td>
                        <form method="POST" action="<?= url('/admin/newsletter/' . $sub['id']) ?>" style="display: inline;" onsubmit="return confirm('Delete this subscriber?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon text-danger" title="Delete">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
