<div class="admin-page">
    <div class="page-header">
        <div class="page-header-content">
            <nav class="breadcrumb">
                <a href="<?= url('/admin/users') ?>">Users</a>
                <span>/</span>
                <span><?= $user ? 'Edit' : 'Add' ?> User</span>
            </nav>
            <h1 class="page-title"><?= $user ? 'Edit Admin User' : 'Add Admin User' ?></h1>
        </div>
    </div>

    <form method="POST" action="<?= $user ? url('/admin/users/' . $user['id']) : url('/admin/users') ?>" class="admin-form">
        <?= csrfField() ?>
        <?php if ($user): ?>
        <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <div class="form-layout">
            <div class="form-main">
                <!-- Basic Information -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Basic Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">First Name <span class="required">*</span></label>
                                <input type="text" name="first_name" class="form-input" required
                                       value="<?= e($user['first_name'] ?? $_SESSION['form_data']['first_name'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name <span class="required">*</span></label>
                                <input type="text" name="last_name" class="form-input" required
                                       value="<?= e($user['last_name'] ?? $_SESSION['form_data']['last_name'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address <span class="required">*</span></label>
                            <input type="email" name="email" class="form-input" required
                                   value="<?= e($user['email'] ?? $_SESSION['form_data']['email'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-input"
                                   value="<?= e($user['phone'] ?? $_SESSION['form_data']['phone'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?= $user ? 'Change Password' : 'Password' ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">
                                <?= $user ? 'New Password' : 'Password' ?>
                                <?php if (!$user): ?><span class="required">*</span><?php endif; ?>
                            </label>
                            <input type="password" name="password" class="form-input"
                                   <?= $user ? '' : 'required' ?> minlength="8">
                            <p class="form-hint">
                                <?= $user ? 'Leave blank to keep current password. ' : '' ?>
                                Minimum 8 characters.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-sidebar">
                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Actions</h2>
                    </div>
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <?= $user ? 'Update User' : 'Create User' ?>
                        </button>
                        <a href="<?= url('/admin/users') ?>" class="btn btn-secondary btn-block mt-2">Cancel</a>
                    </div>
                </div>

                <!-- Role & Status -->
                <?php if (user()['role'] === 'super_admin'): ?>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Role & Status</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Role <span class="required">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="admin" <?= ($user['role'] ?? 'admin') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="super_admin" <?= ($user['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                            </select>
                            <p class="form-hint">Super Admins have full access to all features.</p>
                        </div>

                        <?php if ($user): ?>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= ($user['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="pending" <?= ($user['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="suspended" <?= ($user['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- User Info -->
                <?php if ($user): ?>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="info-list">
                            <div class="info-item">
                                <span class="info-label">Created</span>
                                <span class="info-value"><?= formatDateTime($user['created_at']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Last Login</span>
                                <span class="info-value">
                                    <?= $user['last_login_at'] ? timeAgo($user['last_login_at']) : 'Never' ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">MFA Status</span>
                                <span class="info-value">
                                    <?php if ($user['mfa_enabled']): ?>
                                    <span class="badge badge-success">Enabled</span>
                                    <?php else: ?>
                                    <span class="badge badge-warning">Disabled</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<?php unset($_SESSION['form_data'], $_SESSION['form_errors']); ?>
