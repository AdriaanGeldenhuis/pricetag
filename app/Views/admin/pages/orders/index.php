<!-- Admin Orders List -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle"><?= number_format($pagination['count']) ?> orders total</p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/reports/sales') ?>" class="admin-btn admin-btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            View Reports
        </a>
    </div>
</div>

<!-- Status Tabs -->
<div class="admin-tabs" style="margin-bottom: 1.5rem;">
    <a href="<?= url('/admin/orders') ?>" class="admin-tab <?= empty($filters['status']) ? 'active' : '' ?>">
        All Orders
        <span style="color: var(--admin-text-muted); margin-left: 0.25rem;">(<?= array_sum($statusCounts) ?>)</span>
    </a>
    <a href="<?= url('/admin/orders?status=pending') ?>" class="admin-tab <?= $filters['status'] === 'pending' ? 'active' : '' ?>">
        Pending
        <?php if (!empty($statusCounts['pending'])): ?>
        <span class="admin-nav-badge"><?= $statusCounts['pending'] ?></span>
        <?php endif; ?>
    </a>
    <a href="<?= url('/admin/orders?status=processing') ?>" class="admin-tab <?= $filters['status'] === 'processing' ? 'active' : '' ?>">
        Processing
        <?php if (!empty($statusCounts['processing'])): ?>
        <span style="color: var(--admin-text-muted); margin-left: 0.25rem;">(<?= $statusCounts['processing'] ?>)</span>
        <?php endif; ?>
    </a>
    <a href="<?= url('/admin/orders?status=shipped') ?>" class="admin-tab <?= $filters['status'] === 'shipped' ? 'active' : '' ?>">
        Shipped
        <?php if (!empty($statusCounts['shipped'])): ?>
        <span style="color: var(--admin-text-muted); margin-left: 0.25rem;">(<?= $statusCounts['shipped'] ?>)</span>
        <?php endif; ?>
    </a>
    <a href="<?= url('/admin/orders?status=delivered') ?>" class="admin-tab <?= $filters['status'] === 'delivered' ? 'active' : '' ?>">
        Delivered
    </a>
    <a href="<?= url('/admin/orders?status=cancelled') ?>" class="admin-tab <?= $filters['status'] === 'cancelled' ? 'active' : '' ?>">
        Cancelled
    </a>
</div>

<!-- Filters -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="admin-card-body">
        <form method="GET" action="<?= url('/admin/orders') ?>">
            <?php if ($filters['status']): ?>
            <input type="hidden" name="status" value="<?= e($filters['status']) ?>">
            <?php endif; ?>
            <div style="display: grid; grid-template-columns: 1fr 150px 150px auto; gap: 1rem; align-items: end;">
                <div class="admin-form-group" style="margin: 0;">
                    <label class="admin-form-label">Search</label>
                    <input type="text" name="search" value="<?= e($filters['search']) ?>"
                           placeholder="Order number, email, or name..."
                           class="admin-form-input">
                </div>
                <div class="admin-form-group" style="margin: 0;">
                    <label class="admin-form-label">From Date</label>
                    <input type="date" name="date_from" value="<?= e($filters['date_from']) ?>"
                           class="admin-form-input">
                </div>
                <div class="admin-form-group" style="margin: 0;">
                    <label class="admin-form-label">To Date</label>
                    <input type="date" name="date_to" value="<?= e($filters['date_to']) ?>"
                           class="admin-form-input">
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="admin-btn admin-btn-primary">Filter</button>
                    <a href="<?= url('/admin/orders') ?>" class="admin-btn admin-btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="admin-card">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th style="width: 80px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="7">
                        <div class="admin-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-empty-icon">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            <h3 class="admin-empty-title">No orders found</h3>
                            <p class="admin-empty-text">Orders will appear here once customers place them.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>
                        <a href="<?= url('/admin/orders/' . $order['id']) ?>" class="font-semibold">
                            #<?= e($order['order_number']) ?>
                        </a>
                    </td>
                    <td class="text-muted">
                        <?= date('d M Y', strtotime($order['created_at'])) ?>
                        <br>
                        <span style="font-size: 0.75rem;"><?= date('H:i', strtotime($order['created_at'])) ?></span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="admin-user-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                <?= strtoupper(substr($order['customer_name'] ?? 'G', 0, 2)) ?>
                            </span>
                            <div>
                                <div class="font-semibold"><?= e($order['customer_name'] ?? 'Guest') ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= e($order['billing_email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php
                        $paymentBadge = match($order['payment_status']) {
                            'paid' => ['class' => 'delivered', 'label' => 'Paid'],
                            'pending' => ['class' => 'pending', 'label' => 'Pending'],
                            'failed' => ['class' => 'cancelled', 'label' => 'Failed'],
                            'refunded' => ['class' => 'shipped', 'label' => 'Refunded'],
                            default => ['class' => 'inactive', 'label' => ucfirst($order['payment_status'])],
                        };
                        ?>
                        <span class="admin-badge <?= $paymentBadge['class'] ?>"><?= $paymentBadge['label'] ?></span>
                        <div class="text-muted" style="font-size: 0.75rem; margin-top: 0.25rem;">
                            <?= ucfirst($order['payment_method'] ?? 'N/A') ?>
                        </div>
                    </td>
                    <td>
                        <span class="admin-badge <?= e($order['status']) ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </td>
                    <td class="font-semibold"><?= formatPrice($order['total']) ?></td>
                    <td>
                        <a href="<?= url('/admin/orders/' . $order['id']) ?>"
                           class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagination['total'] > 1): ?>
    <div class="admin-pagination">
        <div class="admin-pagination-info">
            Showing <?= (($pagination['current'] - 1) * $pagination['perPage']) + 1 ?>
            to <?= min($pagination['current'] * $pagination['perPage'], $pagination['count']) ?>
            of <?= number_format($pagination['count']) ?> orders
        </div>
        <div class="admin-pagination-nav">
            <?php if ($pagination['current'] > 1): ?>
            <a href="?page=<?= $pagination['current'] - 1 ?>&<?= http_build_query($filters) ?>"
               class="admin-pagination-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <?php endif; ?>

            <?php
            $start = max(1, $pagination['current'] - 2);
            $end = min($pagination['total'], $pagination['current'] + 2);
            ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
            <a href="?page=<?= $i ?>&<?= http_build_query($filters) ?>"
               class="admin-pagination-btn <?= $i === $pagination['current'] ? 'active' : '' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($pagination['current'] < $pagination['total']): ?>
            <a href="?page=<?= $pagination['current'] + 1 ?>&<?= http_build_query($filters) ?>"
               class="admin-pagination-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
