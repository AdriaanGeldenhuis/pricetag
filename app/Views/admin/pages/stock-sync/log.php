<!-- Admin Stock Sync Log Detail -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle">
            <?= ucfirst($log['type']) ?> sync on <?= date('F j, Y \a\t g:i A', strtotime($log['created_at'])) ?>
        </p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/stock-sync') ?>" class="admin-btn admin-btn-ghost">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Stock Sync
        </a>
    </div>
</div>

<!-- Log Summary -->
<div class="admin-stats-grid" style="margin-bottom: 1.5rem;">
    <div class="admin-stat-card">
        <div class="admin-stat-icon <?= $log['status'] === 'completed' ? 'green' : ($log['status'] === 'running' ? 'blue' : 'red') ?>">
            <?php if ($log['status'] === 'completed'): ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <?php elseif ($log['status'] === 'running'): ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 4 23 10 17 10"></polyline>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
            </svg>
            <?php else: ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <?php endif; ?>
        </div>
        <div class="admin-stat-content">
            <span class="admin-stat-value"><?= ucfirst($log['status']) ?></span>
            <span class="admin-stat-label">Status</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-icon blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
        </div>
        <div class="admin-stat-content">
            <span class="admin-stat-value"><?= number_format($log['total_products'] ?? 0) ?></span>
            <span class="admin-stat-label">Total Processed</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-icon green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 20h9"></path>
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
            </svg>
        </div>
        <div class="admin-stat-content">
            <span class="admin-stat-value"><?= number_format($log['updated_products'] ?? 0) ?></span>
            <span class="admin-stat-label">Updated</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-icon purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
        </div>
        <div class="admin-stat-content">
            <span class="admin-stat-value"><?= number_format($log['created_products'] ?? 0) ?></span>
            <span class="admin-stat-label">Created</span>
        </div>
    </div>
</div>

<div class="log-detail-grid">
    <!-- Log Info -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">Sync Details</h3>
        </div>
        <div class="admin-card-body">
            <dl class="log-details">
                <div class="log-detail-row">
                    <dt>Type</dt>
                    <dd><span class="admin-badge neutral"><?= ucfirst($log['type']) ?></span></dd>
                </div>

                <?php if (!empty($log['vendor_name'])): ?>
                <div class="log-detail-row">
                    <dt>Vendor</dt>
                    <dd><?= e($log['vendor_name']) ?></dd>
                </div>
                <?php endif; ?>

                <div class="log-detail-row">
                    <dt>Started</dt>
                    <dd><?= date('M j, Y g:i:s A', strtotime($log['started_at'] ?? $log['created_at'])) ?></dd>
                </div>

                <?php if (!empty($log['completed_at'])): ?>
                <div class="log-detail-row">
                    <dt>Completed</dt>
                    <dd><?= date('M j, Y g:i:s A', strtotime($log['completed_at'])) ?></dd>
                </div>

                <div class="log-detail-row">
                    <dt>Duration</dt>
                    <dd>
                        <?php
                        $start = strtotime($log['started_at'] ?? $log['created_at']);
                        $end = strtotime($log['completed_at']);
                        $duration = $end - $start;
                        if ($duration < 60) {
                            echo $duration . ' seconds';
                        } elseif ($duration < 3600) {
                            echo floor($duration / 60) . ' minutes ' . ($duration % 60) . ' seconds';
                        } else {
                            echo floor($duration / 3600) . ' hours ' . floor(($duration % 3600) / 60) . ' minutes';
                        }
                        ?>
                    </dd>
                </div>
                <?php endif; ?>

                <?php if (!empty($log['failed_products'])): ?>
                <div class="log-detail-row">
                    <dt>Failed</dt>
                    <dd><span class="text-danger"><?= number_format($log['failed_products']) ?> products</span></dd>
                </div>
                <?php endif; ?>
            </dl>
        </div>
    </div>

    <!-- Errors -->
    <?php if (!empty($log['errors'])): ?>
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                Errors
                <span class="admin-badge inactive"><?= count($log['errors']) ?></span>
            </h3>
        </div>
        <div class="admin-card-body" style="padding: 0;">
            <div class="error-list">
                <?php foreach ($log['errors'] as $error): ?>
                <div class="error-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span><?= e($error) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.log-detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
}

.log-details {
    margin: 0;
}

.log-detail-row {
    display: flex;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--admin-border);
}

.log-detail-row:last-child {
    border-bottom: none;
}

.log-detail-row dt {
    width: 120px;
    color: var(--admin-text-muted);
    font-weight: 500;
}

.log-detail-row dd {
    flex: 1;
    margin: 0;
}

.error-list {
    max-height: 400px;
    overflow-y: auto;
}

.error-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--admin-border);
    font-size: 0.875rem;
}

.error-item:last-child {
    border-bottom: none;
}

.error-item svg {
    width: 16px;
    height: 16px;
    color: var(--admin-danger);
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.text-danger {
    color: var(--admin-danger);
}

@media (max-width: 768px) {
    .log-detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>
