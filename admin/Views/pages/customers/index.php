<!-- Admin Customers List -->
<div class="flex justify-between items-center mb-6">
    <h1 class="admin-page-title mb-0">Customers</h1>
</div>

<!-- Search -->
<div class="card mb-6">
    <div class="card-body">
        <form action="<?= url('/admin/customers') ?>" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name, email, or phone..."
                       class="form-input">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?>
            <a href="<?= url('/admin/customers') ?>" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Joined</th>
                    <th style="width: 80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-12">
                        No customers found
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($customers as $customer): ?>
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-medium">
                                <?= strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <a href="<?= url('/admin/customers/' . $customer['id']) ?>" class="font-medium text-primary">
                                    <?= e($customer['first_name']) ?> <?= e($customer['last_name']) ?>
                                </a>
                                <?php if (!$customer['is_active']): ?>
                                <span class="badge badge-warning ml-2">Inactive</span>
                                <?php endif; ?>
                                <?php if ($customer['email_verified_at']): ?>
                                <span class="badge badge-success ml-1" title="Verified">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="text-muted"><?= e($customer['email']) ?></td>
                    <td class="text-muted"><?= e($customer['phone'] ?? '-') ?></td>
                    <td><?= $customer['order_count'] ?></td>
                    <td class="font-medium"><?= formatPrice($customer['total_spent']) ?></td>
                    <td class="text-muted text-sm"><?= date('d M Y', strtotime($customer['created_at'])) ?></td>
                    <td>
                        <a href="<?= url('/admin/customers/' . $customer['id']) ?>"
                           class="btn btn-ghost btn-sm btn-icon" title="View">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($pagination['last_page'] > 1): ?>
<div class="mt-6">
    <?= \App\Core\View::pagination($pagination, url('/admin/customers')) ?>
</div>
<?php endif; ?>
