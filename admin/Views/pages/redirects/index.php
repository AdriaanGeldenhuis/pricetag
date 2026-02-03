<!-- Admin URL Redirects -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">URL Redirects</h1>
        <p class="admin-page-subtitle">Manage 301/302 redirects for SEO and broken links</p>
    </div>
    <a href="<?= url('/admin/redirects/create') ?>" class="admin-btn admin-btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Add Redirect
    </a>
</div>

<!-- Stats -->
<div class="admin-stats-grid mb-6">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Total Redirects</div>
        <div class="admin-stat-value"><?= count($redirects ?? []) ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Active</div>
        <div class="admin-stat-value text-success"><?php $activeCount = 0; foreach ($redirects ?? [] as $r) { if (!empty($r['is_active'])) $activeCount++; } echo $activeCount; ?></div>
    </div>
</div>

<!-- Redirects Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">All Redirects</h2>
    </div>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>From URL</th>
                    <th>To URL</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th style="width: 120px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($redirects)): ?>
                <tr>
                    <td colspan="5">
                        <div class="admin-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" class="admin-empty-icon">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <h3 class="admin-empty-title">No redirects yet</h3>
                            <p class="admin-empty-text">Create redirects to handle broken links or moved pages.</p>
                            <a href="<?= url('/admin/redirects/create') ?>" class="admin-btn admin-btn-primary">Add First Redirect</a>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($redirects as $redirect): ?>
                <tr>
                    <td>
                        <code class="text-sm"><?= e($redirect['from_url']) ?></code>
                    </td>
                    <td>
                        <code class="text-sm"><?= e($redirect['to_url']) ?></code>
                    </td>
                    <td>
                        <span class="admin-badge <?= $redirect['status_code'] == 301 ? 'active' : 'warning' ?>">
                            <?= $redirect['status_code'] ?>
                        </span>
                    </td>
                    <td>
                        <span class="admin-badge <?= $redirect['is_active'] ? 'active' : 'inactive' ?>">
                            <?= $redirect['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <div class="admin-table-actions">
                            <a href="<?= url('/admin/redirects/' . $redirect['id'] . '/edit') ?>" class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            <form method="POST" action="<?= url('/admin/redirects/' . $redirect['id'] . '/toggle') ?>" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="<?= $redirect['is_active'] ? 'Disable' : 'Enable' ?>">
                                    <?php if ($redirect['is_active']): ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                        <path d="M18.36 6.64a9 9 0 11-12.73 0"></path>
                                        <line x1="12" y1="2" x2="12" y2="12"></line>
                                    </svg>
                                    <?php else: ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    <?php endif; ?>
                                </button>
                            </form>
                            <form method="POST" action="<?= url('/admin/redirects/' . $redirect['id']) ?>" style="display: inline;" onsubmit="return confirm('Delete this redirect?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon text-danger" title="Delete">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
