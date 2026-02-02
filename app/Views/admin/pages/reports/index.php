<div class="admin-page">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Reports & Analytics</h1>
            <p class="page-subtitle">Overview of store performance</p>
        </div>
        <div class="page-actions">
            <form method="GET" class="date-range-form">
                <input type="date" name="start_date" value="<?= e($dateRange['start']) ?>" class="form-input">
                <span>to</span>
                <input type="date" name="end_date" value="<?= e($dateRange['end']) ?>" class="form-input">
                <button type="submit" class="btn btn-secondary">Apply</button>
            </form>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon icon-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?= formatPrice($stats['revenue']) ?></span>
                <span class="stat-label">Revenue</span>
                <?php if ($stats['revenue_growth'] != 0): ?>
                <span class="stat-change <?= $stats['revenue_growth'] > 0 ? 'positive' : 'negative' ?>">
                    <?= $stats['revenue_growth'] > 0 ? '+' : '' ?><?= $stats['revenue_growth'] ?>%
                </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?= number_format($stats['orders']) ?></span>
                <span class="stat-label">Orders</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?= formatPrice($stats['aov']) ?></span>
                <span class="stat-label">Avg. Order Value</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?= number_format($stats['customers']) ?></span>
                <span class="stat-label">Customers</span>
            </div>
        </div>
    </div>

    <div class="reports-grid">
        <!-- Revenue Chart -->
        <div class="card col-span-2">
            <div class="card-header">
                <h2 class="card-title">Revenue Over Time</h2>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>

        <!-- Order Status Breakdown -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Orders by Status</h2>
            </div>
            <div class="card-body">
                <?php if (empty($ordersByStatus)): ?>
                <p class="text-muted text-center">No orders in this period</p>
                <?php else: ?>
                <div class="status-breakdown">
                    <?php foreach ($ordersByStatus as $status): ?>
                    <div class="status-item">
                        <div class="status-info">
                            <span class="status-badge status-<?= $status['status'] ?>"><?= ucfirst($status['status']) ?></span>
                            <span class="status-count"><?= $status['count'] ?> orders</span>
                        </div>
                        <span class="status-total"><?= formatPrice($status['total'] ?? 0) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Products -->
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h2 class="card-title">Top Selling Products</h2>
                <a href="<?= url('/admin/reports/products') ?>" class="text-primary text-sm">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($topProducts)): ?>
                <p class="text-muted text-center py-8">No sales in this period</p>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Sold</th>
                            <th class="text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topProducts as $product): ?>
                        <tr>
                            <td>
                                <span class="font-medium"><?= e($product['name']) ?></span>
                                <span class="text-muted text-sm block"><?= e($product['sku']) ?></span>
                            </td>
                            <td class="text-right"><?= number_format($product['quantity_sold']) ?></td>
                            <td class="text-right font-medium"><?= formatPrice($product['revenue']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Categories -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Top Categories</h2>
            </div>
            <div class="card-body p-0">
                <?php if (empty($topCategories)): ?>
                <p class="text-muted text-center py-8">No sales in this period</p>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-right">Items Sold</th>
                            <th class="text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topCategories as $category): ?>
                        <tr>
                            <td class="font-medium"><?= e($category['name']) ?></td>
                            <td class="text-right"><?= number_format($category['quantity_sold']) ?></td>
                            <td class="text-right font-medium"><?= formatPrice($category['revenue']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card col-span-2">
            <div class="card-header flex items-center justify-between">
                <h2 class="card-title">Recent Orders</h2>
                <a href="<?= url('/admin/orders') ?>" class="text-primary text-sm">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th class="text-right">Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/admin/orders/' . $order['id']) ?>" class="font-medium text-primary">
                                    #<?= e($order['order_number']) ?>
                                </a>
                            </td>
                            <td><?= e($order['customer_name']) ?></td>
                            <td><span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                            <td><span class="status-badge status-<?= $order['payment_status'] ?>"><?= ucfirst($order['payment_status']) ?></span></td>
                            <td class="text-right font-medium"><?= formatPrice($order['total']) ?></td>
                            <td><?= timeAgo($order['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="card mt-6">
        <div class="card-header">
            <h2 class="card-title">Detailed Reports</h2>
        </div>
        <div class="card-body">
            <div class="report-links">
                <a href="<?= url('/admin/reports/sales?start_date=' . $dateRange['start'] . '&end_date=' . $dateRange['end']) ?>" class="report-link">
                    <div class="report-link-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                    </div>
                    <div class="report-link-content">
                        <h4>Sales Report</h4>
                        <p>Detailed sales analysis, payment methods, coupons</p>
                    </div>
                </a>
                <a href="<?= url('/admin/reports/products?start_date=' . $dateRange['start'] . '&end_date=' . $dateRange['end']) ?>" class="report-link">
                    <div class="report-link-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                    </div>
                    <div class="report-link-content">
                        <h4>Product Report</h4>
                        <p>Performance, stock levels, views vs sales</p>
                    </div>
                </a>
                <a href="<?= url('/admin/reports/customers?start_date=' . $dateRange['start'] . '&end_date=' . $dateRange['end']) ?>" class="report-link">
                    <div class="report-link-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div class="report-link-content">
                        <h4>Customer Report</h4>
                        <p>Customer acquisition, top buyers, retention</p>
                    </div>
                </a>
                <a href="<?= url('/admin/reports/export?type=orders&start_date=' . $dateRange['start'] . '&end_date=' . $dateRange['end']) ?>" class="report-link">
                    <div class="report-link-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                    </div>
                    <div class="report-link-content">
                        <h4>Export Data</h4>
                        <p>Download orders, products, customers as CSV</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.date-range-form {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.date-range-form .form-input {
    width: auto;
}

.reports-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.col-span-2 {
    grid-column: span 2;
}

@media (max-width: 1024px) {
    .reports-grid {
        grid-template-columns: 1fr;
    }
    .col-span-2 {
        grid-column: span 1;
    }
}

.stat-change {
    display: block;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.stat-change.positive {
    color: var(--color-success);
}

.stat-change.negative {
    color: var(--color-danger);
}

.status-breakdown {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: var(--color-neutral-50);
    border-radius: var(--radius-md);
}

.status-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.status-total {
    font-weight: 600;
}

.report-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.report-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--color-neutral-50);
    border-radius: var(--radius-lg);
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
}

.report-link:hover {
    background: var(--color-primary-50);
}

.report-link-icon {
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-background);
    border-radius: var(--radius-md);
    color: var(--color-primary);
}

.report-link-icon svg {
    width: 1.5rem;
    height: 1.5rem;
}

.report-link-content h4 {
    font-weight: 600;
    margin: 0 0 0.25rem;
}

.report-link-content p {
    font-size: 0.8125rem;
    color: var(--color-text-muted);
    margin: 0;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const revenueData = <?= json_encode($revenueChart) ?>;

const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: revenueData.map(d => d.date),
        datasets: [{
            label: 'Revenue',
            data: revenueData.map(d => d.revenue),
            borderColor: 'rgb(37, 99, 235)',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            fill: true,
            tension: 0.3,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {display: false},
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => 'R ' + value.toLocaleString()
                }
            }
        }
    }
});
</script>
