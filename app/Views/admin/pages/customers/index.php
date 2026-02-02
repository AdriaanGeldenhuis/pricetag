<!-- Admin Customers List -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle"><?= number_format($pagination['count']) ?> customers total</p>
    </div>
</div>

<!-- Filters -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="admin-card-body">
        <form method="GET" action="<?= url('/admin/customers') ?>">
            <div style="display: grid; grid-template-columns: 1fr 150px auto; gap: 1rem; align-items: end;">
                <div class="admin-form-group" style="margin: 0;">
                    <label class="admin-form-label">Search</label>
                    <input type="text" name="search" value="<?= e($filters['search']) ?>"
                           placeholder="Name, email, or phone..."
                           class="admin-form-input">
                </div>
                <div class="admin-form-group" style="margin: 0;">
                    <label class="admin-form-label">Sort By</label>
                    <select name="sort" class="admin-form-select">
                        <option value="created_at" <?= $filters['sort'] === 'created_at' ? 'selected' : '' ?>>Join Date</option>
                        <option value="first_name" <?= $filters['sort'] === 'first_name' ? 'selected' : '' ?>>Name</option>
                        <option value="order_count" <?= $filters['sort'] === 'order_count' ? 'selected' : '' ?>>Orders</option>
                        <option value="total_spent" <?= $filters['sort'] === 'total_spent' ? 'selected' : '' ?>>Total Spent</option>
                    </select>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="admin-btn admin-btn-primary">Search</button>
                    <a href="<?= url('/admin/customers') ?>" class="admin-btn admin-btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="admin-card">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Last Order</th>
                    <th>Joined</th>
                    <th style="width: 80px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="7">
                        <div class="admin-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-empty-icon">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <h3 class="admin-empty-title">No customers found</h3>
                            <p class="admin-empty-text">Customers will appear here once they register.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($customers as $customer): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span class="admin-user-avatar">
                                <?= strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)) ?>
                            </span>
                            <div>
                                <a href="<?= url('/admin/customers/' . $customer['id']) ?>" class="font-semibold">
                                    <?= e($customer['first_name'] . ' ' . $customer['last_name']) ?>
                                </a>
                                <?php if ($customer['phone']): ?>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= e($customer['phone']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="mailto:<?= e($customer['email']) ?>" class="text-muted">
                            <?= e($customer['email']) ?>
                        </a>
                    </td>
                    <td>
                        <?php if ($customer['order_count'] > 0): ?>
                        <a href="<?= url('/admin/orders?search=' . urlencode($customer['email'])) ?>">
                            <?= $customer['order_count'] ?> orders
                        </a>
                        <?php else: ?>
                        <span class="text-muted">0 orders</span>
                        <?php endif; ?>
                    </td>
                    <td class="font-semibold"><?= formatPrice($customer['total_spent'] ?? 0) ?></td>
                    <td class="text-muted">
                        <?= $customer['last_order_at'] ? date('d M Y', strtotime($customer['last_order_at'])) : '-' ?>
                    </td>
                    <td class="text-muted"><?= date('d M Y', strtotime($customer['created_at'])) ?></td>
                    <td>
                        <a href="<?= url('/admin/customers/' . $customer['id']) ?>"
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
            of <?= number_format($pagination['count']) ?> customers
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
