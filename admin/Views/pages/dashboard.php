<!-- Admin Dashboard -->
<h1 class="admin-page-title">Dashboard</h1>

<!-- Stats Grid -->
<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <p class="admin-stat-label">Orders Today</p>
        <p class="admin-stat-value"><?= $stats['orders_today']['count'] ?></p>
        <p class="admin-stat-value text-lg"><?= formatPrice($stats['orders_today']['total']) ?></p>
    </div>
    <div class="admin-stat-card">
        <p class="admin-stat-label">Orders This Month</p>
        <p class="admin-stat-value"><?= $stats['orders_month']['count'] ?></p>
        <p class="admin-stat-value text-lg"><?= formatPrice($stats['orders_month']['total']) ?></p>
    </div>
    <div class="admin-stat-card">
        <p class="admin-stat-label">Pending Orders</p>
        <p class="admin-stat-value"><?= $stats['pending_orders'] ?></p>
        <?php if ($stats['pending_orders'] > 0): ?>
        <a href="<?= url('/admin/orders?status=pending') ?>" class="text-sm text-primary">View orders &rarr;</a>
        <?php endif; ?>
    </div>
    <div class="admin-stat-card">
        <p class="admin-stat-label">Active Products</p>
        <p class="admin-stat-value"><?= $stats['total_products'] ?></p>
        <?php if ($stats['low_stock'] > 0): ?>
        <span class="text-sm text-warning"><?= $stats['low_stock'] ?> low stock</span>
        <?php endif; ?>
    </div>
    <div class="admin-stat-card">
        <p class="admin-stat-label">Total Customers</p>
        <p class="admin-stat-value"><?= $stats['total_users'] ?></p>
        <p class="admin-stat-change positive">+<?= $stats['new_users_month'] ?> this month</p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header flex justify-between items-center">
            <h2 class="font-semibold">Recent Orders</h2>
            <a href="<?= url('/admin/orders') ?>" class="text-sm text-primary">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-8">No orders yet</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td>
                            <a href="<?= url('/admin/orders/' . $order['id']) ?>" class="font-medium text-primary">
                                #<?= e($order['order_number']) ?>
                            </a>
                            <span class="text-xs text-muted block"><?= date('d M H:i', strtotime($order['created_at'])) ?></span>
                        </td>
                        <td><?= e($order['first_name'] ?? $order['billing_first_name']) ?> <?= e($order['last_name'] ?? $order['billing_last_name']) ?></td>
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

    <!-- Top Products -->
    <div class="card">
        <div class="card-header flex justify-between items-center">
            <h2 class="font-semibold">Top Products (30 days)</h2>
            <a href="<?= url('/admin/reports/products') ?>" class="text-sm text-primary">View report</a>
        </div>
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($topProducts)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-8">No sales data yet</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($topProducts as $product): ?>
                    <tr>
                        <td>
                            <span class="font-medium"><?= e($product['name']) ?></span>
                            <span class="text-xs text-muted block"><?= e($product['sku']) ?></span>
                        </td>
                        <td><?= $product['sold'] ?></td>
                        <td class="font-medium"><?= formatPrice($product['revenue']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-6">
    <h2 class="font-semibold mb-4">Quick Actions</h2>
    <div class="flex flex-wrap gap-3">
        <a href="<?= url('/admin/products/create') ?>" class="btn btn-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Product
        </a>
        <a href="<?= url('/admin/orders?status=pending') ?>" class="btn btn-outline">
            Process Orders
        </a>
        <a href="<?= url('/admin/stock-sync') ?>" class="btn btn-outline">
            Sync Stock
        </a>
        <a href="<?= url('/admin/reports') ?>" class="btn btn-outline">
            View Reports
        </a>
    </div>
</div>
