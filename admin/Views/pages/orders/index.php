<!-- Admin Orders List -->
<div class="flex justify-between items-center mb-6">
    <h1 class="admin-page-title mb-0">Orders</h1>
</div>

<!-- Filters -->
<div class="card mb-6">
    <div class="card-body">
        <form action="<?= url('/admin/orders') ?>" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search orders..."
                       class="form-input">
            </div>
            <div>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    <option value="refunded" <?= $status === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($search || $status): ?>
            <a href="<?= url('/admin/orders') ?>" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="width: 80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-12">
                        No orders found
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>
                        <a href="<?= url('/admin/orders/' . $order['id']) ?>" class="font-medium text-primary">
                            #<?= e($order['order_number']) ?>
                        </a>
                    </td>
                    <td>
                        <span class="font-medium">
                            <?= e($order['first_name'] ?? $order['billing_first_name']) ?>
                            <?= e($order['last_name'] ?? $order['billing_last_name']) ?>
                        </span>
                        <span class="text-xs text-muted block"><?= e($order['email'] ?? $order['customer_email']) ?></span>
                    </td>
                    <td class="text-muted"><?= $order['item_count'] ?? '-' ?></td>
                    <td class="font-medium"><?= formatPrice($order['total']) ?></td>
                    <td>
                        <?php
                        $paymentColors = [
                            'pending' => 'warning',
                            'paid' => 'success',
                            'failed' => 'danger',
                            'refunded' => 'neutral',
                        ];
                        $pColor = $paymentColors[$order['payment_status']] ?? 'neutral';
                        ?>
                        <span class="badge badge-<?= $pColor ?>"><?= ucfirst($order['payment_status']) ?></span>
                    </td>
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
                    <td class="text-muted text-sm">
                        <?= date('d M Y', strtotime($order['created_at'])) ?>
                        <span class="block text-xs"><?= date('H:i', strtotime($order['created_at'])) ?></span>
                    </td>
                    <td>
                        <a href="<?= url('/admin/orders/' . $order['id']) ?>"
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
    <?= \App\Core\View::pagination($pagination, url('/admin/orders')) ?>
</div>
<?php endif; ?>
