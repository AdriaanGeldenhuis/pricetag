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
                        <?php if (!$user->isVerified()): ?>
                        <div class="account-welcome-alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <span>Please verify your email address to unlock all features.</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="account-welcome-avatar">
                        <?php if ($user->avatar): ?>
                        <img src="<?= e($user->avatar) ?>" alt="<?= e($user->getFullName()) ?>" class="avatar avatar-lg">
                        <?php else: ?>
                        <span class="avatar avatar-lg"><?= strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) ?></span>
                        <?php endif; ?>
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
                            <span class="account-stat-label">Total Orders</span>
                        </div>
                    </a>

                    <?php if (($stats['pending_orders'] ?? 0) > 0): ?>
                    <a href="<?= url('/account/orders?status=processing') ?>" class="account-stat-card stat-highlight">
                        <div class="account-stat-icon icon-warning">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="account-stat-content">
                            <span class="account-stat-value"><?= $stats['pending_orders'] ?></span>
                            <span class="account-stat-label">In Progress</span>
                        </div>
                    </a>
                    <?php endif; ?>

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
                            <span class="account-stat-value"><?= $stats['addresses'] ?? 0 ?></span>
                            <span class="account-stat-label">Saved Addresses</span>
                        </div>
                    </a>

                    <?php if (($stats['total_spent'] ?? 0) > 0): ?>
                    <div class="account-stat-card stat-spent">
                        <div class="account-stat-icon icon-success">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </div>
                        <div class="account-stat-content">
                            <span class="account-stat-value"><?= formatPrice($stats['total_spent']) ?></span>
                            <span class="account-stat-label">Total Spent</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Orders -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold">Recent Orders</h2>
                        <a href="<?= url('/account/orders') ?>" class="text-primary text-sm hover-underline">View All Orders</a>
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
                            <a href="<?= url('/categories') ?>" class="btn btn-primary">Browse Categories</a>
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
                                            <span title="<?= formatDateTime($order['created_at']) ?>">
                                                <?= timeAgo($order['created_at']) ?>
                                            </span>
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

                <!-- Recent Activity -->
                <?php if (!empty($recentActivity)): ?>
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold">Recent Activity</h2>
                        <a href="<?= url('/account/activity') ?>" class="text-primary text-sm hover-underline">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <ul class="activity-list">
                            <?php foreach ($recentActivity as $activity): ?>
                            <li class="activity-item">
                                <div class="activity-icon">
                                    <?php
                                    $iconMap = [
                                        'login' => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line>',
                                        'password_changed' => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path>',
                                        'profile_updated' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>',
                                        'mfa_enabled' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>',
                                        'default' => '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>',
                                    ];
                                    $icon = $iconMap[$activity['type']] ?? $iconMap['default'];
                                    ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $icon ?></svg>
                                </div>
                                <div class="activity-content">
                                    <p class="activity-description"><?= e($activity['description']) ?></p>
                                    <span class="activity-time"><?= timeAgo($activity['created_at']) ?></span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div class="account-quick-actions">
                    <h2 class="account-section-title">Quick Actions</h2>
                    <div class="quick-actions-grid">
                        <a href="<?= url('/account/settings') ?>" class="quick-action-card">
                            <div class="quick-action-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
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
                            <span class="quick-action-label">Security Settings</span>
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

                <!-- Account Info Summary -->
                <div class="card account-info-card">
                    <div class="card-header">
                        <h2 class="font-semibold">Account Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="account-info-grid">
                            <div class="account-info-item">
                                <span class="account-info-label">Full Name</span>
                                <span class="account-info-value"><?= e($user->getFullName()) ?></span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Email Address</span>
                                <span class="account-info-value">
                                    <?= e($user->email) ?>
                                    <?php if ($user->isVerified()): ?>
                                    <span class="badge badge-success" title="Email verified">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Phone Number</span>
                                <span class="account-info-value"><?= $user->phone ? e($user->phone) : '<span class="text-muted">Not set</span>' ?></span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Member Since</span>
                                <span class="account-info-value"><?= formatDate($user->created_at, 'F Y') ?></span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Two-Factor Auth</span>
                                <span class="account-info-value">
                                    <?php if ($user->mfa_enabled): ?>
                                    <span class="badge badge-success">Enabled</span>
                                    <?php else: ?>
                                    <span class="badge badge-warning">Not enabled</span>
                                    <a href="<?= url('/account/security') ?>" class="text-primary text-sm ml-2">Enable</a>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Last Login</span>
                                <span class="account-info-value">
                                    <?php if ($user->last_login_at): ?>
                                    <?= timeAgo($user->last_login_at) ?>
                                    <?php else: ?>
                                    <span class="text-muted">First login</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/Views/pages/account/_styles.php'; ?>
