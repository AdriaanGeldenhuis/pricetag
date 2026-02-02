<!-- Account - Settings -->
<div class="account-page">
    <div class="container py-8">
        <div class="account-layout">
            <!-- Sidebar Navigation -->
            <?php include APP_PATH . '/Views/pages/account/_sidebar.php'; ?>

            <!-- Main Content -->
            <div class="account-main">
                <!-- Page Header -->
                <div class="account-page-header">
                    <div>
                        <h1 class="account-page-title">Account Settings</h1>
                        <p class="text-muted text-sm">Manage your profile information and preferences</p>
                    </div>
                </div>

                <!-- Profile Information -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold">Profile Information</h2>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/account/settings') ?>" method="POST">
                            <?= csrfField() ?>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label for="first_name" class="form-label required">First Name</label>
                                    <input type="text" id="first_name" name="first_name" class="form-input"
                                           value="<?= e($user->first_name) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="last_name" class="form-label required">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" class="form-input"
                                           value="<?= e($user->last_name) ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label required">Email Address</label>
                                <input type="email" id="email" name="email" class="form-input"
                                       value="<?= e($user->email) ?>" required>
                                <?php if (!$user->email_verified_at): ?>
                                <p class="form-hint text-warning">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    Email not verified.
                                    <a href="<?= url('/resend-verification') ?>" class="text-primary">Resend verification email</a>
                                </p>
                                <?php endif; ?>
                            </div>

                            <div class="form-group mb-0">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-input"
                                       value="<?= e($user->phone ?? '') ?>" placeholder="+27 72 123 4567">
                            </div>

                            <div class="flex justify-end mt-6">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold">Change Password</h2>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/account/settings/password') ?>" method="POST">
                            <?= csrfField() ?>

                            <div class="form-group">
                                <label for="current_password" class="form-label required">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-input" required>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label for="new_password" class="form-label required">New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-input"
                                           minlength="8" required>
                                    <p class="form-hint">Minimum 8 characters</p>
                                </div>
                                <div class="form-group">
                                    <label for="new_password_confirmation" class="form-label required">Confirm New Password</label>
                                    <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                                           class="form-input" minlength="8" required>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="btn btn-primary">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Email Preferences -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold">Email Preferences</h2>
                    </div>
                    <div class="card-body">
                        <div class="space-y-4">
                            <label class="flex items-start gap-3">
                                <input type="checkbox" class="form-check-input mt-1" checked disabled>
                                <div>
                                    <span class="font-medium">Order Updates</span>
                                    <p class="text-sm text-muted">Receive updates about your orders (required)</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-3">
                                <input type="checkbox" name="newsletter" class="form-check-input mt-1"
                                       <?= $user->newsletter_subscribed ? 'checked' : '' ?>>
                                <div>
                                    <span class="font-medium">Newsletter</span>
                                    <p class="text-sm text-muted">Receive news, promotions, and special offers</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-3">
                                <input type="checkbox" name="product_updates" class="form-check-input mt-1"
                                       <?= $user->product_updates ? 'checked' : '' ?>>
                                <div>
                                    <span class="font-medium">Product Updates</span>
                                    <p class="text-sm text-muted">Be notified when items in your wishlist go on sale</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card border-danger">
                    <div class="card-header">
                        <h2 class="font-semibold text-danger">Danger Zone</h2>
                    </div>
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium">Delete Account</h3>
                                <p class="text-sm text-muted">Permanently delete your account and all associated data</p>
                            </div>
                            <button type="button" class="btn btn-outline btn-danger" onclick="openDeleteAccountModal()">
                                Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal-overlay" id="delete-account-modal">
    <div class="modal" style="max-width: 450px;">
        <div class="modal-header">
            <h2 class="modal-title text-danger">Delete Account</h2>
            <button type="button" class="modal-close" onclick="closeDeleteAccountModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form action="<?= url('/account/delete') ?>" method="POST">
            <?= csrfField() ?>
            <div class="modal-body">
                <div class="alert alert-danger mb-4">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>This action is permanent and cannot be undone.</span>
                </div>
                <p class="mb-4">Deleting your account will:</p>
                <ul class="list-disc list-inside text-sm text-muted mb-4">
                    <li>Remove all your personal information</li>
                    <li>Delete your order history</li>
                    <li>Remove your saved addresses</li>
                    <li>Clear your wishlist</li>
                </ul>
                <div class="form-group mb-0">
                    <label for="delete_password" class="form-label required">Enter your password to confirm</label>
                    <input type="password" id="delete_password" name="password" class="form-input" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeDeleteAccountModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete My Account</button>
            </div>
        </form>
    </div>
</div>

<?php include APP_PATH . '/Views/pages/account/_styles.php'; ?>

<style>
.border-danger {
    border-color: var(--color-danger-300);
}
</style>

<script>
function openDeleteAccountModal() {
    document.getElementById('delete-account-modal').classList.add('open');
}

function closeDeleteAccountModal() {
    document.getElementById('delete-account-modal').classList.remove('open');
}

// Close modal on overlay click
document.getElementById('delete-account-modal').addEventListener('click', (e) => {
    if (e.target.id === 'delete-account-modal') {
        closeDeleteAccountModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeDeleteAccountModal();
    }
});
</script>
