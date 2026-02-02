<!-- Account Dashboard -->
<div class="account-page">
    <div class="container py-8">
        <div class="account-layout">
            <!-- Sidebar Navigation -->
            <?php include APP_PATH . '/Views/pages/account/_sidebar.php'; ?>

            <!-- Main Content -->
            <div class="account-main">
                <!-- Welcome Header -->
                <div class="account-welcome">
                    <div class="account-welcome-content">
                        <h1 class="account-welcome-title">Welcome back, <?= e($user->first_name) ?>!</h1>
                        <p class="account-welcome-text">Manage your orders, addresses, and account settings.</p>
                    </div>
                    <div class="account-welcome-avatar">
                        <span class="avatar avatar-lg"><?= strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) ?></span>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="account-stats">
                    <a href="<?= url('/account/orders') ?>" class="account-stat-card">
                        <div class="account-stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                        </div>
                        <div class="account-stat-content">
                            <span class="account-stat-value"><?= $stats['orders'] ?? 0 ?></span>
                            <span class="account-stat-label">Orders</span>
                        </div>
                    </a>
                    <a href="<?= url('/wishlist') ?>" class="account-stat-card">
                        <div class="account-stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </div>
                        <div class="account-stat-content">
                            <span class="account-stat-value"><?= $stats['wishlist'] ?? 0 ?></span>
                            <span class="account-stat-label">Wishlist Items</span>
                        </div>
                    </a>
                    <a href="<?= url('/account/addresses') ?>" class="account-stat-card">
                        <div class="account-stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <div class="account-stat-content">
                            <span class="account-stat-value"><?= count($user->getAddresses()) ?></span>
                            <span class="account-stat-label">Addresses</span>
                        </div>
                    </a>
                </div>

                <!-- Recent Orders -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold">Recent Orders</h2>
                        <a href="<?= url('/account/orders') ?>" class="text-primary text-sm">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentOrders)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                </svg>
                            </div>
                            <h3>No orders yet</h3>
                            <p class="text-muted">Start shopping to see your orders here.</p>
                            <a href="<?= url('/products') ?>" class="btn btn-primary">Browse Products</a>
                        </div>
                        <?php else: ?>
                        <div class="orders-table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= url('/account/orders/' . $order['id']) ?>" class="order-number">
                                                #<?= e($order['order_number']) ?>
                                            </a>
                                        </td>
                                        <td class="text-muted">
                                            <?= date('d M Y', strtotime($order['created_at'])) ?>
                                        </td>
                                        <td>
                                            <span class="order-status status-<?= e($order['status']) ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td class="font-semibold"><?= formatPrice($order['total']) ?></td>
                                        <td class="text-right">
                                            <a href="<?= url('/account/orders/' . $order['id']) ?>" class="btn btn-sm btn-ghost">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="account-quick-actions">
                    <h2 class="account-section-title">Quick Actions</h2>
                    <div class="quick-actions-grid">
                        <a href="<?= url('/account/settings') ?>" class="quick-action-card">
                            <div class="quick-action-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                </svg>
                            </div>
                            <span class="quick-action-label">Edit Profile</span>
                        </a>
                        <a href="<?= url('/account/addresses') ?>" class="quick-action-card">
                            <div class="quick-action-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <span class="quick-action-label">Manage Addresses</span>
                        </a>
                        <a href="<?= url('/account/security') ?>" class="quick-action-card">
                            <div class="quick-action-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                </svg>
                            </div>
                            <span class="quick-action-label">Security</span>
                        </a>
                        <a href="<?= url('/contact') ?>" class="quick-action-card">
                            <div class="quick-action-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                            </div>
                            <span class="quick-action-label">Get Help</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/Views/pages/account/_styles.php'; ?>
