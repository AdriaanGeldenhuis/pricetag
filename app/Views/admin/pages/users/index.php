<div class="admin-page">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Admin Users</h1>
            <p class="page-subtitle">Manage administrator accounts and permissions</p>
        </div>
        <?php if (user()['role'] === 'super_admin'): ?>
        <a href="<?= url('/admin/users/create') ?>" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Admin User
        </a>
        <?php endif; ?>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div>
            <div class="stat-content">
                <span class="stat-value"><?= ($roleCounts['super_admin'] ?? 0) + ($roleCounts['admin'] ?? 0) ?></span>
                <span class="stat-label">Total Admins</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
            <div class="stat-content">
                <span class="stat-value"><?= $roleCounts['super_admin'] ?? 0 ?></span>
                <span class="stat-label">Super Admins</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-secondary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div>
            <div class="stat-content">
                <span class="stat-value"><?= $roleCounts['admin'] ?? 0 ?></span>
                <span class="stat-label">Admins</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <input type="text" name="search" class="form-input" placeholder="Search by name or email..."
                               value="<?= e($filters['search']) ?>">
                    </div>
                    <div class="filter-group">
                        <select name="role" class="form-select">
                            <option value="">All Roles</option>
                            <option value="super_admin" <?= $filters['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                            <option value="admin" <?= $filters['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="suspended" <?= $filters['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    <?php if ($filters['search'] || $filters['role'] || $filters['status']): ?>
                    <a href="<?= url('/admin/users') ?>" class="btn btn-ghost">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>MFA</th>
                        <th>Last Login</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-8 text-muted">No admin users found.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar">
                                    <?php if ($u['avatar']): ?>
                                    <img src="<?= e($u['avatar']) ?>" alt="">
                                    <?php else: ?>
                                    <?= strtoupper(substr($u['first_name'], 0, 1) . substr($u['last_name'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <span class="user-name"><?= e($u['first_name'] . ' ' . $u['last_name']) ?></span>
                                    <span class="user-email"><?= e($u['email']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-<?= $u['role'] === 'super_admin' ? 'primary' : 'secondary' ?>">
                                <?= $u['role'] === 'super_admin' ? 'Super Admin' : 'Admin' ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?= $u['status'] ?>">
                                <?= ucfirst($u['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['mfa_enabled']): ?>
                            <span class="badge badge-success">Enabled</span>
                            <?php else: ?>
                            <span class="badge badge-warning">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['last_login_at']): ?>
                            <span title="<?= e($u['last_login_at']) ?>"><?= timeAgo($u['last_login_at']) ?></span>
                            <?php else: ?>
                            <span class="text-muted">Never</span>
                            <?php endif; ?>
                        </td>
                        <td><?= formatDate($u['created_at']) ?></td>
                        <td class="text-right">
                            <div class="action-buttons">
                                <a href="<?= url('/admin/users/' . $u['id']) ?>" class="btn btn-sm btn-ghost" title="View">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
                                <?php if (user()['role'] === 'super_admin' || user()['id'] === $u['id']): ?>
                                <a href="<?= url('/admin/users/' . $u['id'] . '/edit') ?>" class="btn btn-sm btn-ghost" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>
                                <?php endif; ?>
                                <?php if (user()['role'] === 'super_admin' && user()['id'] !== $u['id']): ?>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-ghost dropdown-toggle" title="More actions">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                            <circle cx="12" cy="12" r="1"></circle>
                                            <circle cx="12" cy="5" r="1"></circle>
                                            <circle cx="12" cy="19" r="1"></circle>
                                        </svg>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="<?= url('/admin/users/' . $u['id'] . '/audit') ?>" class="dropdown-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline points="14 2 14 8 20 8"></polyline>
                                            </svg>
                                            View Audit Log
                                        </a>
                                        <button onclick="terminateSessions(<?= $u['id'] ?>)" class="dropdown-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                                <polyline points="10 17 15 12 10 7"></polyline>
                                                <line x1="15" y1="12" x2="3" y2="12"></line>
                                            </svg>
                                            Terminate Sessions
                                        </button>
                                        <button onclick="forcePasswordReset(<?= $u['id'] ?>)" class="dropdown-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            </svg>
                                            Force Password Reset
                                        </button>
                                        <?php if ($u['mfa_enabled']): ?>
                                        <button onclick="disableMFA(<?= $u['id'] ?>)" class="dropdown-item text-warning">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                            </svg>
                                            Disable MFA
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($u['role'] !== 'super_admin'): ?>
                                        <hr class="dropdown-divider">
                                        <button onclick="impersonateUser(<?= $u['id'] ?>)" class="dropdown-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                            Impersonate
                                        </button>
                                        <?php endif; ?>
                                        <hr class="dropdown-divider">
                                        <button onclick="confirmDelete(<?= $u['id'] ?>, '<?= e($u['email']) ?>', '<?= $u['role'] ?>')" class="dropdown-item text-danger">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                            Delete User
                                        </button>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination['total'] > 1): ?>
        <div class="card-footer">
            <?php include APP_PATH . '/Views/admin/partials/pagination.php'; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.user-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: var(--color-primary-100);
    color: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    overflow: hidden;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 500;
}

.user-email {
    font-size: 0.8125rem;
    color: var(--color-text-muted);
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

/* Dropdown styles */
.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-menu {
    position: absolute;
    right: 0;
    top: 100%;
    min-width: 12rem;
    background: var(--color-background);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-lg);
    z-index: 100;
    display: none;
    padding: 0.5rem 0;
}

.dropdown:hover .dropdown-menu,
.dropdown:focus-within .dropdown-menu {
    display: block;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    color: var(--color-text);
    background: none;
    border: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    text-decoration: none;
}

.dropdown-item:hover {
    background: var(--color-background-secondary);
}

.dropdown-item.text-danger {
    color: var(--color-danger);
}

.dropdown-item.text-warning {
    color: var(--color-warning-700);
}

.dropdown-divider {
    margin: 0.5rem 0;
    border: none;
    border-top: 1px solid var(--color-border);
}

/* Modal styles */
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
</style>

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
            <div id="superAdminWarning" class="alert alert-warning mt-3" style="display: none;">
                <strong>Warning:</strong> This is a super admin account. Extra confirmation is required.
                <div class="form-group mt-2">
                    <label class="checkbox-label">
                        <input type="checkbox" id="confirmSuperAdminDelete">
                        <span>I confirm I want to delete this super admin</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="deleteUser()">Delete User</button>
        </div>
    </div>
</div>

<script>
let deleteUserId = null;
let deleteUserRole = null;

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
    if (!confirm('Are you sure you want to force a password reset? The user will be logged out.')) return;

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
    if (!confirm('Are you sure you want to impersonate this user?')) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/users/${userId}/impersonate`;
    form.innerHTML = `<input type="hidden" name="csrf_token" value="<?= csrf() ?>">`;
    document.body.appendChild(form);
    form.submit();
}

function confirmDelete(userId, email, role) {
    deleteUserId = userId;
    deleteUserRole = role;
    document.getElementById('deleteUserEmail').textContent = email;
    document.getElementById('superAdminWarning').style.display = role === 'super_admin' ? 'block' : 'none';
    if (role !== 'super_admin') {
        document.getElementById('confirmSuperAdminDelete').checked = false;
    }
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    deleteUserId = null;
    deleteUserRole = null;
}

function deleteUser() {
    if (!deleteUserId) return;

    if (deleteUserRole === 'super_admin' && !document.getElementById('confirmSuperAdminDelete').checked) {
        alert('Please confirm super admin deletion');
        return;
    }

    fetch(`/admin/users/${deleteUserId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            csrf_token: '<?= csrf() ?>',
            confirm_super_admin_delete: deleteUserRole === 'super_admin' ? true : false
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error);
        }
    });
}
</script>
