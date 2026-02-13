<!-- Account - Orders List -->
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
                        <h1 class="account-page-title">My Orders</h1>
                        <p class="text-muted text-sm">View and track your order history</p>
                    </div>
                </div>

                <!-- Orders List -->
                <div class="card">
                    <?php if (empty($orders)): ?>
                    <div class="card-body">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                </svg>
                            </div>
                            <h3>No orders yet</h3>
                            <p class="text-muted">You haven't placed any orders yet. Start shopping!</p>
                            <a href="<?= url('/categories') ?>" class="btn btn-primary">Browse Categories</a>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Mobile Cards -->
                    <div class="orders-cards lg:hidden">
                        <?php foreach ($orders as $order): ?>
                        <a href="<?= url('/account/orders/' . $order['id']) ?>" class="order-card">
                            <div class="order-card-header">
                                <span class="order-card-number">#<?= e($order['order_number']) ?></span>
                                <span class="order-status status-<?= e($order['status']) ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </div>
                            <div class="order-card-body">
                                <div class="order-card-meta">
                                    <span class="order-card-date">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        <?= date('d M Y', strtotime($order['created_at'])) ?>
                                    </span>
                                    <span class="order-card-items">
                                        <?= $order['item_count'] ?? 0 ?> item<?= ($order['item_count'] ?? 0) !== 1 ? 's' : '' ?>
                                    </span>
                                </div>
                                <div class="order-card-total">
                                    <span class="order-card-total-label">Total</span>
                                    <span class="order-card-total-value"><?= formatPrice($order['total']) ?></span>
                                </div>
                            </div>
                            <div class="order-card-arrow">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Desktop Table -->
                    <div class="orders-table-wrapper hidden lg:block">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('/account/orders/' . $order['id']) ?>" class="order-number">
                                            #<?= e($order['order_number']) ?>
                                        </a>
                                    </td>
                                    <td class="text-muted">
                                        <?= date('d M Y', strtotime($order['created_at'])) ?>
                                    </td>
                                    <td class="text-muted">
                                        <?= $order['item_count'] ?? 0 ?> item<?= ($order['item_count'] ?? 0) !== 1 ? 's' : '' ?>
                                    </td>
                                    <td>
                                        <span class="order-status status-<?= e($order['status']) ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td class="font-semibold"><?= formatPrice($order['total']) ?></td>
                                    <td class="text-right">
                                        <a href="<?= url('/account/orders/' . $order['id']) ?>" class="btn btn-sm btn-ghost">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['last_page'] > 1): ?>
                    <div class="card-body border-t">
                        <nav class="pagination">
                            <?php if ($pagination['current_page'] > 1): ?>
                            <a href="<?= url('/account/orders?page=' . ($pagination['current_page'] - 1)) ?>" class="pagination-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                            </a>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $pagination['current_page'] - 2);
                            $end = min($pagination['last_page'], $pagination['current_page'] + 2);
                            ?>

                            <?php if ($start > 1): ?>
                            <a href="<?= url('/account/orders?page=1') ?>" class="pagination-item">1</a>
                            <?php if ($start > 2): ?>
                            <span class="pagination-item disabled">...</span>
                            <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start; $i <= $end; $i++): ?>
                            <a href="<?= url('/account/orders?page=' . $i) ?>"
                               class="pagination-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                            <?php endfor; ?>

                            <?php if ($end < $pagination['last_page']): ?>
                            <?php if ($end < $pagination['last_page'] - 1): ?>
                            <span class="pagination-item disabled">...</span>
                            <?php endif; ?>
                            <a href="<?= url('/account/orders?page=' . $pagination['last_page']) ?>" class="pagination-item">
                                <?= $pagination['last_page'] ?>
                            </a>
                            <?php endif; ?>

                            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <a href="<?= url('/account/orders?page=' . ($pagination['current_page'] + 1)) ?>" class="pagination-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/Views/pages/account/_styles.php'; ?>

<style>
/* Orders Cards (Mobile) */
.orders-cards {
    display: flex;
    flex-direction: column;
}

.order-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--color-border);
    transition: background 0.2s;
}

.order-card:hover {
    background: var(--color-background-alt);
}

.order-card:last-child {
    border-bottom: none;
}

.order-card-header {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 100px;
}

.order-card-number {
    font-weight: 600;
    color: var(--color-primary);
}

.order-card-body {
    flex: 1;
    padding: 0 1rem;
}

.order-card-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.75rem;
    color: var(--color-text-muted);
    margin-bottom: 0.25rem;
}

.order-card-date {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.order-card-date svg {
    width: 0.875rem;
    height: 0.875rem;
}

.order-card-total {
    display: flex;
    flex-direction: column;
}

.order-card-total-label {
    font-size: 0.625rem;
    text-transform: uppercase;
    color: var(--color-text-muted);
}

.order-card-total-value {
    font-weight: 700;
}

.order-card-arrow {
    color: var(--color-text-muted);
}

.order-card-arrow svg {
    width: 1.25rem;
    height: 1.25rem;
}
</style>
