<div class="admin-page">
    <div class="page-header">
        <div class="page-header-content">
            <nav class="breadcrumb">
                <a href="<?= url('/admin/users') ?>">Admin Users</a>
                <span>/</span>
                <a href="<?= url('/admin/users/' . $user['id']) ?>"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></a>
                <span>/</span>
                <span>Audit Log</span>
            </nav>
            <h1 class="page-title">Audit Log</h1>
            <p class="page-subtitle">Complete activity history for <?= e($user['first_name'] . ' ' . $user['last_name']) ?></p>
        </div>
        <a href="<?= url('/admin/users/' . $user['id']) ?>" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to User
        </a>
    </div>

    <!-- User Summary -->
    <div class="card mb-6">
        <div class="card-body">
            <div class="flex items-center gap-4">
                <div class="user-avatar">
                    <?php if ($user['avatar']): ?>
                    <img src="<?= e($user['avatar']) ?>" alt="">
                    <?php else: ?>
                    <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div>
                    <h2 class="text-lg font-semibold"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></h2>
                    <p class="text-muted"><?= e($user['email']) ?></p>
                </div>
                <div class="ml-auto">
                    <span class="badge badge-<?= $user['role'] === 'super_admin' ? 'primary' : 'secondary' ?>">
                        <?= $user['role'] === 'super_admin' ? 'Super Admin' : 'Admin' ?>
                    </span>
                    <span class="status-badge status-<?= $user['status'] ?>">
                        <?= ucfirst($user['status']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Role History -->
        <div class="lg:col-span-1">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Role History</h3>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($roleHistory)): ?>
                    <p class="p-4 text-muted text-center">No role changes recorded</p>
                    <?php else: ?>
                    <div class="timeline p-4">
                        <?php foreach ($roleHistory as $change): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-title">
                                    <?php if ($change['old_role']): ?>
                                    <span class="badge badge-secondary text-xs"><?= ucfirst(str_replace('_', ' ', $change['old_role'])) ?></span>
                                    <span class="mx-1 text-muted">-></span>
                                    <?php else: ?>
                                    <span class="text-muted text-sm">Assigned: </span>
                                    <?php endif; ?>
                                    <span class="badge badge-<?= $change['new_role'] === 'super_admin' ? 'primary' : 'secondary' ?> text-xs">
                                        <?= ucfirst(str_replace('_', ' ', $change['new_role'])) ?>
                                    </span>
                                </div>
                                <div class="timeline-meta">
                                    by <?= e($change['changer_first_name'] . ' ' . $change['changer_last_name']) ?>
                                </div>
                                <?php if ($change['reason']): ?>
                                <div class="timeline-note">"<?= e($change['reason']) ?>"</div>
                                <?php endif; ?>
                                <div class="timeline-details">
                                    <code class="text-xs"><?= e($change['ip_address'] ?? 'N/A') ?></code>
                                </div>
                                <time class="timeline-time"><?= formatDateTime($change['created_at']) ?></time>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Login Statistics -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Login Statistics</h3>
                </div>
                <div class="card-body">
                    <?php
                    $totalLogins = count(array_filter($loginHistory, fn($a) => $a['success']));
                    $failedLogins = count(array_filter($loginHistory, fn($a) => !$a['success']));
                    $uniqueIps = count(array_unique(array_column($loginHistory, 'ip_address')));
                    ?>
                    <div class="stats-mini">
                        <div class="stat-mini">
                            <span class="stat-mini-value text-success"><?= $totalLogins ?></span>
                            <span class="stat-mini-label">Successful</span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-value text-danger"><?= $failedLogins ?></span>
                            <span class="stat-mini-label">Failed</span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-value"><?= $uniqueIps ?></span>
                            <span class="stat-mini-label">Unique IPs</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="lg:col-span-2">
            <!-- Login History -->
            <div class="card mb-6">
                <div class="card-header">
                    <h3 class="card-title">Login History (Last 50)</h3>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($loginHistory)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No login history available</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($loginHistory as $attempt): ?>
                            <tr>
                                <td class="whitespace-nowrap"><?= formatDateTime($attempt['created_at']) ?></td>
                                <td><code><?= e($attempt['ip_address']) ?></code></td>
                                <td class="text-sm text-muted" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e(substr($attempt['user_agent'] ?? 'Unknown', 0, 40)) ?>...
                                </td>
                                <td>
                                    <?php if ($attempt['success']): ?>
                                    <span class="badge badge-success">Success</span>
                                    <?php else: ?>
                                    <span class="badge badge-danger">Failed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Full Activity Log -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Activity Log (Last 200)</h3>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Actor</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No activity logged</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="whitespace-nowrap text-sm"><?= formatDateTime($log['created_at']) ?></td>
                                <td class="text-sm">
                                    <?php if ($log['actor_first_name']): ?>
                                    <?= e($log['actor_first_name'] . ' ' . $log['actor_last_name']) ?>
                                    <?php else: ?>
                                    <span class="text-muted">System</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= getActivityBadgeClass($log['type']) ?> text-xs">
                                        <?= formatActivityType($log['type']) ?>
                                    </span>
                                </td>
                                <td class="text-sm" style="max-width: 300px;">
                                    <?= e($log['description']) ?>
                                    <?php if ($log['properties']): ?>
                                    <button class="btn-link text-xs ml-1" onclick="showDetails('<?= e(json_encode($log['properties'])) ?>')">
                                        [details]
                                    </button>
                                    <?php endif; ?>
                                </td>
                                <td><code class="text-xs"><?= e($log['ip_address'] ?? 'N/A') ?></code></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeDetailsModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Activity Details</h3>
            <button type="button" class="modal-close" onclick="closeDetailsModal()">&times;</button>
        </div>
        <div class="modal-body">
            <pre id="detailsContent" class="bg-gray-100 p-3 rounded text-sm overflow-auto" style="max-height: 400px;"></pre>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDetailsModal()">Close</button>
        </div>
    </div>
</div>

<?php
function getActivityBadgeClass(string $type): string {
    $classes = [
        'admin_user_created' => 'success',
        'admin_user_updated' => 'primary',
        'admin_user_deleted' => 'danger',
        'impersonation_start' => 'warning',
        'impersonation_end' => 'warning',
        'mfa_disabled' => 'warning',
        'mfa_enabled' => 'success',
        'force_password_reset' => 'warning',
        'sessions_terminated' => 'secondary',
        'login' => 'success',
        'logout' => 'secondary',
    ];
    return $classes[$type] ?? 'secondary';
}

function formatActivityType(string $type): string {
    return ucwords(str_replace('_', ' ', $type));
}
?>

<style>
.user-avatar {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    background: var(--color-primary-100);
    color: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 600;
    overflow: hidden;
    flex-shrink: 0;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.status-badge {
    display: inline-flex;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 500;
}

.status-active { background: var(--color-success-100); color: var(--color-success-700); }
.status-pending { background: var(--color-warning-100); color: var(--color-warning-700); }
.status-suspended { background: var(--color-danger-100); color: var(--color-danger-700); }

.timeline {
    position: relative;
}

.timeline-item {
    position: relative;
    padding-left: 1.5rem;
    padding-bottom: 1.5rem;
    border-left: 2px solid var(--color-border);
}

.timeline-item:last-child {
    padding-bottom: 0;
    border-left-color: transparent;
}

.timeline-marker {
    position: absolute;
    left: -5px;
    width: 8px;
    height: 8px;
    background: var(--color-primary);
    border-radius: 50%;
}

.timeline-content {
    padding-left: 0.5rem;
}

.timeline-title { font-weight: 500; }
.timeline-meta { font-size: 0.8125rem; color: var(--color-text-muted); margin-top: 0.25rem; }
.timeline-note { font-size: 0.8125rem; color: var(--color-text-secondary); margin-top: 0.25rem; font-style: italic; }
.timeline-details { font-size: 0.75rem; margin-top: 0.25rem; }
.timeline-time { font-size: 0.75rem; color: var(--color-text-muted); display: block; margin-top: 0.25rem; }

.stats-mini {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    text-align: center;
}

.stat-mini-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 600;
}

.stat-mini-label {
    display: block;
    font-size: 0.75rem;
    color: var(--color-text-muted);
}

.modal {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
}

.modal-content {
    position: relative;
    background: var(--color-background);
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 32rem;
    box-shadow: var(--shadow-xl);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--color-border);
}

.modal-title { font-weight: 600; font-size: 1.125rem; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-muted); }
.modal-body { padding: 1.5rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.5rem; border-top: 1px solid var(--color-border); }

.btn-link {
    background: none;
    border: none;
    color: var(--color-primary);
    cursor: pointer;
    text-decoration: underline;
}

.whitespace-nowrap { white-space: nowrap; }
</style>

<script>
function showDetails(jsonString) {
    try {
        const data = JSON.parse(jsonString);
        document.getElementById('detailsContent').textContent = JSON.stringify(data, null, 2);
        document.getElementById('detailsModal').style.display = 'flex';
    } catch (e) {
        alert('Could not parse details');
    }
}

function closeDetailsModal() {
    document.getElementById('detailsModal').style.display = 'none';
}
</script>
