<!-- Admin Contact Messages -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Contact Messages</h1>
        <p class="admin-page-subtitle"><?= $stats['unread'] ?? 0 ?> unread message<?= ($stats['unread'] ?? 0) != 1 ? 's' : '' ?></p>
    </div>
</div>

<!-- Stats -->
<div class="admin-stats-grid mb-6">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Total Messages</div>
        <div class="admin-stat-value"><?= $stats['total'] ?? 0 ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Unread</div>
        <div class="admin-stat-value text-warning"><?= $stats['unread'] ?? 0 ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Received Today</div>
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
                <option value="new" <?= ($filters['status'] ?? '') === 'new' ? 'selected' : '' ?>>New</option>
                <option value="read" <?= ($filters['status'] ?? '') === 'read' ? 'selected' : '' ?>>Read</option>
                <option value="replied" <?= ($filters['status'] ?? '') === 'replied' ? 'selected' : '' ?>>Replied</option>
                <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>
    </form>
</div>

<!-- Messages Table -->
<div class="admin-card">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>From</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="width: 100px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                <tr>
                    <td colspan="5">
                        <div class="admin-empty">
                            <h3 class="admin-empty-title">No messages</h3>
                            <p class="admin-empty-text">Contact form submissions will appear here.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                <tr class="<?= $msg['status'] === 'new' ? 'font-semibold' : '' ?>">
                    <td>
                        <div><?= e($msg['name']) ?></div>
                        <div class="text-muted text-xs"><?= e($msg['email']) ?></div>
                    </td>
                    <td>
                        <a href="<?= url('/admin/contact/' . $msg['id']) ?>">
                            <?= e($msg['subject'] ?: '(No subject)') ?>
                        </a>
                        <div class="text-muted text-xs"><?= e(substr($msg['message'], 0, 50)) ?>...</div>
                    </td>
                    <td>
                        <?php
                        $badges = ['new' => 'warning', 'read' => 'neutral', 'replied' => 'active', 'archived' => 'inactive'];
                        ?>
                        <span class="admin-badge <?= $badges[$msg['status']] ?? 'neutral' ?>"><?= ucfirst($msg['status']) ?></span>
                    </td>
                    <td class="text-muted"><?= date('M j, Y H:i', strtotime($msg['created_at'])) ?></td>
                    <td>
                        <div class="admin-table-actions">
                            <a href="<?= url('/admin/contact/' . $msg['id']) ?>" class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="View">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                            <form method="POST" action="<?= url('/admin/contact/' . $msg['id']) ?>" style="display: inline;" onsubmit="return confirm('Delete this message?')">
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
