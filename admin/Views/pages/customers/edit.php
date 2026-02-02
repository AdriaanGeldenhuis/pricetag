<!-- Admin Customer Edit -->
<div class="flex justify-between items-center mb-6">
    <div>
        <a href="<?= url('/admin/customers/' . $customer->id) ?>" class="text-sm text-muted hover:text-primary">&larr; Back to Customer</a>
        <h1 class="admin-page-title mb-0">Edit Customer</h1>
    </div>
</div>

<form action="<?= url('/admin/customers/' . $customer->id) ?>" method="POST" class="admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="PUT">

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Customer Information</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" id="first_name" name="first_name" value="<?= e($customer->first_name) ?>"
                                   class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" id="last_name" name="last_name" value="<?= e($customer->last_name) ?>"
                                   class="form-input" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" value="<?= e($customer->email) ?>"
                               class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?= e($customer->phone ?? '') ?>"
                               class="form-input">
                    </div>
                </div>
            </div>

            <!-- Password -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Password</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" id="password" name="password" class="form-input" minlength="8">
                        <p class="form-help">Leave blank to keep current password. Minimum 8 characters.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Account Status</h2>
                </div>
                <div class="card-body space-y-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               <?= $customer->is_active ? 'checked' : '' ?> class="form-checkbox">
                        <span>Account Active</span>
                    </label>
                    <p class="form-help text-xs">Inactive accounts cannot log in</p>

                    <div class="pt-4 border-t">
                        <p class="text-sm text-muted">
                            Email verified:
                            <?php if ($customer->email_verified_at): ?>
                            <span class="text-success"><?= date('d M Y', strtotime($customer->email_verified_at)) ?></span>
                            <?php else: ?>
                            <span class="text-warning">Not verified</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body space-y-3">
                    <button type="submit" class="btn btn-primary w-full">
                        Update Customer
                    </button>
                    <a href="<?= url('/admin/customers/' . $customer->id) ?>" class="btn btn-outline w-full">
                        Cancel
                    </a>
                </div>
            </div>

            <!-- Info -->
            <div class="card">
                <div class="card-body text-sm text-muted">
                    <p>ID: <?= $customer->id ?></p>
                    <p class="mt-1">Registered: <?= date('d M Y H:i', strtotime($customer->created_at)) ?></p>
                    <?php if ($customer->last_login_at): ?>
                    <p class="mt-1">Last login: <?= date('d M Y H:i', strtotime($customer->last_login_at)) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>
