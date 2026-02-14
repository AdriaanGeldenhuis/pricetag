<div class="admin-page">
    <div class="admin-page-header">
        <div class="admin-page-header-content">
            <h1 class="admin-page-title">Admin Users</h1>
            <p class="admin-page-subtitle">Manage administrator accounts and permissions</p>
        </div>
        <?php if (user()['role'] === 'super_admin'): ?>
        <div class="admin-page-actions">
            <a href="<?= url('/admin/users/create') ?>" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Admin User
            </a>
        </div>
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
        <div class="admin-table-wrapper">
            <table class="admin-table">
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

