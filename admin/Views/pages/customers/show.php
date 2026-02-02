<!-- Admin Customer Detail -->
<div class="flex justify-between items-center mb-6">
    <div>
        <a href="<?= url('/admin/customers') ?>" class="text-sm text-muted hover:text-primary">&larr; Back to Customers</a>
        <h1 class="admin-page-title mb-0"><?= e($customer['first_name']) ?> <?= e($customer['last_name']) ?></h1>
        <p class="text-muted">Customer since <?= date('d M Y', strtotime($customer['created_at'])) ?></p>
    </div>
    <a href="<?= url('/admin/customers/' . $customer['id'] . '/edit') ?>" class="btn btn-outline">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
        </svg>
        Edit Customer
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Orders -->
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h2 class="font-semibold">Recent Orders</h2>
                <a href="<?= url('/admin/orders?search=' . urlencode($customer['email'])) ?>" class="text-sm text-primary">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-8">No orders yet</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/admin/orders/' . $order['id']) ?>" class="font-medium text-primary">
                                    #<?= e($order['order_number']) ?>
                                </a>
                            </td>
                            <td class="text-muted text-sm"><?= date('d M Y H:i', strtotime($order['created_at'])) ?></td>
                            <td class="font-medium"><?= formatPrice($order['total']) ?></td>
                            <td>
                                <?php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'processing' => 'primary',
                                    'paid' => 'success',
                                    'shipped' => 'primary',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger',
                                    'refunded' => 'neutral',
                                ];
                                $color = $statusColors[$order['status']] ?? 'neutral';
                                ?>
                                <span class="badge badge-<?= $color ?>"><?= ucfirst($order['status']) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Addresses -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Saved Addresses</h2>
            </div>
            <div class="card-body">
                <?php if (empty($addresses)): ?>
                <p class="text-center text-muted py-4">No saved addresses</p>
                <?php else: ?>
                <div class="grid md:grid-cols-2 gap-4">
                    <?php foreach ($addresses as $address): ?>
                    <div class="border rounded p-4 <?= $address['is_default'] ? 'border-primary' : '' ?>">
                        <?php if ($address['is_default']): ?>
                        <span class="badge badge-primary mb-2">Default</span>
                        <?php endif; ?>
                        <p class="font-medium"><?= e($address['first_name']) ?> <?= e($address['last_name']) ?></p>
                        <?php if ($address['company']): ?>
                        <p><?= e($address['company']) ?></p>
                        <?php endif; ?>
                        <p><?= e($address['address']) ?></p>
                        <?php if ($address['address2']): ?>
                        <p><?= e($address['address2']) ?></p>
                        <?php endif; ?>
                        <p><?= e($address['city']) ?>, <?= e($address['province']) ?> <?= e($address['postal_code']) ?></p>
                        <p><?= e($address['country']) ?></p>
                        <?php if ($address['phone']): ?>
                        <p class="mt-2 text-muted"><?= e($address['phone']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Customer Info -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Customer Information</h2>
            </div>
            <div class="card-body">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 rounded-full bg-primary text-white flex items-center justify-center text-xl font-medium">
                        <?= strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <p class="font-medium text-lg"><?= e($customer['first_name']) ?> <?= e($customer['last_name']) ?></p>
                        <?php if ($customer['is_active']): ?>
                        <span class="badge badge-success">Active</span>
                        <?php else: ?>
                        <span class="badge badge-warning">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <a href="mailto:<?= e($customer['email']) ?>" class="text-primary"><?= e($customer['email']) ?></a>
                        <?php if ($customer['email_verified_at']): ?>
                        <span class="badge badge-success text-xs">Verified</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($customer['phone']): ?>
                    <div class="flex items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                        </svg>
                        <span><?= e($customer['phone']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Statistics</h2>
            </div>
            <div class="card-body space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-muted">Total Orders</span>
                    <span class="font-medium"><?= $customer['order_count'] ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-muted">Total Spent</span>
                    <span class="font-medium"><?= formatPrice($customer['total_spent']) ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-muted">Average Order</span>
                    <span class="font-medium">
                        <?= $customer['order_count'] > 0 ? formatPrice($customer['total_spent'] / $customer['order_count']) : formatPrice(0) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-body space-y-3">
                <a href="<?= url('/admin/customers/' . $customer['id'] . '/edit') ?>" class="btn btn-outline w-full">
                    Edit Customer
                </a>
                <a href="mailto:<?= e($customer['email']) ?>" class="btn btn-outline w-full">
                    Send Email
                </a>
            </div>
        </div>
    </div>
</div>
