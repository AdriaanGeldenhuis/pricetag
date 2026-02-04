<div class="admin-page">
    <div class="page-header">
        <div class="page-header-content">
            <nav class="breadcrumb">
                <a href="<?= url('/admin/users') ?>">Admin Users</a>
                <span>/</span>
                <span><?= e($user['first_name'] . ' ' . $user['last_name']) ?></span>
            </nav>
            <h1 class="page-title"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></h1>
        </div>
        <div class="page-actions">
            <?php if (user()['role'] === 'super_admin' || user()['id'] === $user['id']): ?>
            <a href="<?= url('/admin/users/' . $user['id'] . '/edit') ?>" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Edit User
            </a>
            <?php endif; ?>
            <?php if (user()['role'] === 'super_admin'): ?>
            <a href="<?= url('/admin/users/' . $user['id'] . '/audit') ?>" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                View Audit Log
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Info Card -->
        <div class="lg:col-span-1">
            <div class="card">
                <div class="card-body text-center">
                    <div class="user-avatar-large">
                        <?php if ($user['avatar']): ?>
                        <img src="<?= e($user['avatar']) ?>" alt="">
                        <?php else: ?>
                        <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <h2 class="mt-4 text-xl font-semibold"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></h2>
                    <p class="text-muted"><?= e($user['email']) ?></p>
                    <div class="mt-3">
                        <span class="badge badge-<?= $user['role'] === 'super_admin' ? 'primary' : 'secondary' ?> mr-2">
                            <?= $user['role'] === 'super_admin' ? 'Super Admin' : 'Admin' ?>
                        </span>
                        <span class="status-badge status-<?= $user['status'] ?>">
                            <?= ucfirst($user['status']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Security Status -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Security Status</h3>
                </div>
                <div class="card-body">
                    <div class="security-list">
                        <div class="security-item">
                            <span class="security-icon <?= $user['mfa_enabled'] ? 'text-success' : 'text-danger' ?>">
                                <?php if ($user['mfa_enabled']): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <?php else: ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                                <?php endif; ?>
                            </span>
                            <div class="security-content">
                                <span class="security-label">Two-Factor Authentication</span>
                                <span class="security-value"><?= $user['mfa_enabled'] ? 'Enabled' : 'Disabled' ?></span>
                            </div>
                        </div>
                        <div class="security-item">
                            <span class="security-icon <?= $user['email_verified_at'] ? 'text-success' : 'text-warning' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </span>
                            <div class="security-content">
                                <span class="security-label">Email Verified</span>
                                <span class="security-value"><?= $user['email_verified_at'] ? formatDateTime($user['email_verified_at']) : 'Not verified' ?></span>
                            </div>
                        </div>
                        <div class="security-item">
                            <span class="security-icon text-muted">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                            <div class="security-content">
                                <span class="security-label">Password Changed</span>
                                <span class="security-value"><?= $user['password_changed_at'] ? timeAgo($user['password_changed_at']) : 'Never' ?></span>
                            </div>
                        </div>
                        <div class="security-item">
                            <span class="security-icon text-muted">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                    <polyline points="10 17 15 12 10 7"></polyline>
                                    <line x1="15" y1="12" x2="3" y2="12"></line>
                                </svg>
                            </span>
                            <div class="security-content">
                                <span class="security-label">Last Login</span>
                                <span class="security-value"><?= $user['last_login_at'] ? timeAgo($user['last_login_at']) : 'Never' ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if (user()['role'] === 'super_admin' && user()['id'] !== $user['id']): ?>
                    <hr class="my-4">
                    <h4 class="text-sm font-medium mb-3">Quick Actions</h4>
                    <div class="space-y-2">
                        <button onclick="terminateSessions(<?= $user['id'] ?>)" class="btn btn-sm btn-secondary btn-block">
                            Terminate All Sessions
                        </button>
                        <button onclick="forcePasswordReset(<?= $user['id'] ?>)" class="btn btn-sm btn-secondary btn-block">
                            Force Password Reset
                        </button>
                        <?php if ($user['mfa_enabled']): ?>
                        <button onclick="disableMFA(<?= $user['id'] ?>)" class="btn btn-sm btn-warning btn-block">
                            Disable MFA
                        </button>
                        <?php endif; ?>
                        <?php if ($user['role'] !== 'super_admin'): ?>
                        <button onclick="impersonateUser(<?= $user['id'] ?>)" class="btn btn-sm btn-secondary btn-block">
                            Impersonate User
                        </button>
                        <?php endif; ?>
                        <button onclick="confirmDelete(<?= $user['id'] ?>, '<?= e($user['email']) ?>')" class="btn btn-sm btn-danger btn-block">
                            Delete User
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- User Details -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Details</h3>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">User ID</span>
                            <span class="info-value">#<?= $user['id'] ?></span>
                        </div>
                        <?php if ($user['phone']): ?>
                        <div class="info-item">
                            <span class="info-label">Phone</span>
                            <span class="info-value"><?= e($user['phone']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="info-item">
                            <span class="info-label">Last Login IP</span>
                            <span class="info-value"><code><?= e($user['last_login_ip'] ?? 'N/A') ?></code></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Created</span>
                            <span class="info-value"><?= formatDateTime($user['created_at']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Updated</span>
                            <span class="info-value"><?= formatDateTime($user['updated_at']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Role History (Super Admin Only) -->
            <?php if (user()['role'] === 'super_admin' && !empty($roleHistory)): ?>
            <div class="card mb-6">
                <div class="card-header">
                    <h3 class="card-title">Role History</h3>
                </div>
                <div class="card-body p-0">
                    <div class="timeline">
                        <?php foreach ($roleHistory as $change): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-title">
                                    <?php if ($change['old_role']): ?>
                                    <span class="badge badge-secondary"><?= ucfirst(str_replace('_', ' ', $change['old_role'])) ?></span>
                                    <span class="mx-2">-></span>
                                    <?php endif; ?>
                                    <span class="badge badge-<?= $change['new_role'] === 'super_admin' ? 'primary' : 'secondary' ?>">
                                        <?= ucfirst(str_replace('_', ' ', $change['new_role'])) ?>
                                    </span>
                                </div>
                                <div class="timeline-meta">
                                    by <?= e($change['changer_first_name'] . ' ' . $change['changer_last_name']) ?>
                                </div>
                                <?php if ($change['reason']): ?>
                                <div class="timeline-note"><?= e($change['reason']) ?></div>
                                <?php endif; ?>
                                <time class="timeline-time"><?= formatDateTime($change['created_at']) ?></time>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Active Sessions -->
            <div class="card mb-6">
                <div class="card-header d-flex justify-between items-center">
                    <h3 class="card-title">Active Sessions</h3>
                    <span class="badge badge-secondary"><?= count($sessions) ?></span>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Device</th>
                                <th>Last Active</th>
                                <th>Started</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sessions)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No active sessions</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($sessions as $session): ?>
                            <tr>
                                <td><code><?= e($session['ip_address']) ?></code></td>
                                <td class="text-sm"><?= e(substr($session['user_agent'] ?? 'Unknown', 0, 50)) ?>...</td>
                                <td><?= timeAgo($session['last_activity']) ?></td>
                                <td><?= formatDateTime($session['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Login History -->
            <div class="card mb-6">
                <div class="card-header">
                    <h3 class="card-title">Login History</h3>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>IP Address</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($loginHistory)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No login history</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($loginHistory as $attempt): ?>
                            <tr>
                                <td><?= formatDateTime($attempt['created_at']) ?></td>
                                <td><code><?= e($attempt['ip_address']) ?></code></td>
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

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Activity</h3>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activity)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No recent activity</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($activity as $log): ?>
                            <tr>
                                <td><?= formatDateTime($log['created_at']) ?></td>
                                <td><span class="badge badge-secondary"><?= e($log['type']) ?></span></td>
                                <td class="text-sm"><?= e(substr($log['description'], 0, 60)) ?><?= strlen($log['description']) > 60 ? '...' : '' ?></td>
                                <td><code><?= e($log['ip_address'] ?? 'N/A') ?></code></td>
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

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeDeleteModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Delete User</h3>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this user?</p>
            <p class="text-danger"><strong id="deleteUserEmail"></strong></p>
            <p class="text-muted text-sm">This action will soft-delete the user and terminate all their sessions.</p>
            <?php if ($user['role'] === 'super_admin'): ?>
            <div class="alert alert-warning mt-3">
                <strong>Warning:</strong> This is a super admin account. Extra confirmation is required.
            </div>
            <div class="form-group mt-3">
                <label class="checkbox-label">
                    <input type="checkbox" id="confirmSuperAdminDelete">
                    <span>I confirm I want to delete this super admin</span>
                </label>
            </div>
            <?php endif; ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="deleteUser()">Delete User</button>
        </div>
    </div>
</div>

<style>
.user-avatar-large {
    width: 6rem;
    height: 6rem;
    border-radius: 50%;
    background: var(--color-primary-100);
    color: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 auto;
    overflow: hidden;
}

.user-avatar-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.security-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.security-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.security-icon {
    flex-shrink: 0;
    margin-top: 2px;
}

.security-content {
    display: flex;
    flex-direction: column;
}

.security-label {
    font-size: 0.875rem;
    color: var(--color-text-muted);
}

.security-value {
    font-weight: 500;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-label {
    color: var(--color-text-muted);
    font-size: 0.875rem;
}

.info-value {
    font-weight: 500;
}

.timeline {
    padding: 1rem;
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

.timeline-title {
    font-weight: 500;
}

.timeline-meta {
    font-size: 0.875rem;
    color: var(--color-text-muted);
    margin-top: 0.25rem;
}

.timeline-note {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
    margin-top: 0.25rem;
    font-style: italic;
}

.timeline-time {
    font-size: 0.75rem;
    color: var(--color-text-muted);
    display: block;
    margin-top: 0.25rem;
}

.status-badge {
    display: inline-flex;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 500;
}

.status-active {
    background: var(--color-success-100);
    color: var(--color-success-700);
}

.status-pending {
    background: var(--color-warning-100);
    color: var(--color-warning-700);
}

.status-suspended {
    background: var(--color-danger-100);
    color: var(--color-danger-700);
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
    max-width: 28rem;
    box-shadow: var(--shadow-xl);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--color-border);
}

.modal-title {
    font-weight: 600;
    font-size: 1.125rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--color-text-muted);
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--color-border);
}

.space-y-2 > * + * {
    margin-top: 0.5rem;
}
</style>

<script>
let deleteUserId = null;

function terminateSessions(userId) {
    if (!confirm('Are you sure you want to terminate all sessions for this user?')) return;

    fetch(`/admin/users/${userId}/sessions/terminate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ csrf_token: '<?= csrf() ?>' })
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message || data.error);
        if (data.success) location.reload();
    });
}

function forcePasswordReset(userId) {
    if (!confirm('Are you sure you want to force a password reset? The user will be logged out of all sessions.')) return;

    fetch(`/admin/users/${userId}/password/reset`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ csrf_token: '<?= csrf() ?>' })
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message || data.error);
        if (data.success) location.reload();
    });
}

function disableMFA(userId) {
    if (!confirm('Are you sure you want to disable MFA for this user?')) return;

    fetch(`/admin/users/${userId}/mfa/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ csrf_token: '<?= csrf() ?>' })
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message || data.error);
        if (data.success) location.reload();
    });
}

function impersonateUser(userId) {
    if (!confirm('Are you sure you want to impersonate this user? You can stop impersonating from the header.')) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/users/${userId}/impersonate`;
    form.innerHTML = `<input type="hidden" name="csrf_token" value="<?= csrf() ?>">`;
    document.body.appendChild(form);
    form.submit();
}

function confirmDelete(userId, email) {
    deleteUserId = userId;
    document.getElementById('deleteUserEmail').textContent = email;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    deleteUserId = null;
}

function deleteUser() {
    if (!deleteUserId) return;

    <?php if ($user['role'] === 'super_admin'): ?>
    if (!document.getElementById('confirmSuperAdminDelete').checked) {
        alert('Please confirm super admin deletion');
        return;
    }
    <?php endif; ?>

    fetch(`/admin/users/${deleteUserId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            csrf_token: '<?= csrf() ?>',
            confirm_super_admin_delete: <?= $user['role'] === 'super_admin' ? 'true' : 'false' ?>
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/admin/users';
        } else {
            alert(data.error);
        }
    });
}
</script>
