<!-- Admin Dashboard -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Dashboard</h1>
        <p class="admin-page-subtitle">Welcome back! Here's what's happening with your store.</p>
    </div>
    <div class="admin-page-actions">
        <button class="admin-btn admin-btn-secondary" onclick="window.location.reload()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
            Refresh
        </button>
        <a href="<?= url('/admin/reports') ?>" class="admin-btn admin-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Export Report
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div class="admin-stat-icon orders">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                </svg>
            </div>
            <?php if ($stats['orders_growth'] >= 0): ?>
            <span class="admin-stat-change up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                    <polyline points="17 6 23 6 23 12"></polyline>
                </svg>
                +<?= number_format($stats['orders_growth'], 1) ?>%
            </span>
            <?php else: ?>
            <span class="admin-stat-change down">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                    <polyline points="17 18 23 18 23 12"></polyline>
                </svg>
                <?= number_format($stats['orders_growth'], 1) ?>%
            </span>
            <?php endif; ?>
        </div>
        <div class="admin-stat-value"><?= number_format($stats['today_orders']) ?></div>
        <div class="admin-stat-label">Today's Orders</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div class="admin-stat-icon revenue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <?php if ($stats['revenue_growth'] >= 0): ?>
            <span class="admin-stat-change up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                    <polyline points="17 6 23 6 23 12"></polyline>
                </svg>
                +<?= number_format($stats['revenue_growth'], 1) ?>%
            </span>
            <?php else: ?>
            <span class="admin-stat-change down">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                    <polyline points="17 18 23 18 23 12"></polyline>
                </svg>
                <?= number_format($stats['revenue_growth'], 1) ?>%
            </span>
            <?php endif; ?>
        </div>
        <div class="admin-stat-value"><?= formatPrice($stats['today_revenue']) ?></div>
        <div class="admin-stat-label">Today's Revenue</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div class="admin-stat-icon customers">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <span class="admin-stat-change up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                    <polyline points="17 6 23 6 23 12"></polyline>
                </svg>
                +<?= number_format($stats['new_customers_today']) ?> today
            </span>
        </div>
        <div class="admin-stat-value"><?= number_format($stats['total_customers']) ?></div>
        <div class="admin-stat-label">Total Customers</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div class="admin-stat-icon products">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </div>
            <?php if ($stats['low_stock_count'] > 0): ?>
            <span class="admin-stat-change down">
                <?= $stats['low_stock_count'] ?> low stock
            </span>
            <?php endif; ?>
        </div>
        <div class="admin-stat-value"><?= number_format($stats['total_products']) ?></div>
        <div class="admin-stat-label">Active Products</div>
    </div>
</div>

<!-- Dashboard Grid -->
<div class="admin-dashboard-grid">
    <div>
        <!-- Sales Chart -->
        <div class="admin-card admin-recent-orders">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Sales Overview</h3>
                <select class="admin-form-select" style="width: auto;" id="chartPeriod">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                </select>
            </div>
            <div class="admin-card-body">
                <div class="admin-chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Recent Orders</h3>
                <a href="<?= url('/admin/orders') ?>" class="admin-btn admin-btn-ghost admin-btn-sm">View All</a>
            </div>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted" style="padding: 2rem;">
                                No orders yet
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/admin/orders/' . $order['id']) ?>" class="font-semibold">
                                    #<?= e($order['order_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="admin-user-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                        <?= strtoupper(substr($order['customer_name'], 0, 2)) ?>
                                    </span>
                                    <span><?= e($order['customer_name']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="admin-badge <?= e($order['status']) ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td class="font-semibold"><?= formatPrice($order['total']) ?></td>
                            <td class="text-muted"><?= date('d M H:i', strtotime($order['created_at'])) ?></td>
                            <td>
                                <a href="<?= url('/admin/orders/' . $order['id']) ?>" class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon">
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
        </div>
    </div>

    <div>
        <!-- Top Selling Products -->
        <div class="admin-card admin-top-products">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Top Selling</h3>
                <a href="<?= url('/admin/reports/products') ?>" class="admin-btn admin-btn-ghost admin-btn-sm">View All</a>
            </div>
            <div class="admin-card-body">
                <?php if (empty($topProducts)): ?>
                <div class="admin-empty">
                    <p class="text-muted">No sales data yet</p>
                </div>
                <?php else: ?>
                <?php foreach ($topProducts as $product): ?>
                <div class="admin-product-row">
                    <img src="<?= e($product['image'] ?? '/assets/images/placeholder.jpg') ?>"
                         alt="<?= e($product['name']) ?>"
                         class="admin-product-image">
                    <div class="admin-product-info">
                        <div class="admin-product-name"><?= e($product['name']) ?></div>
                        <div class="admin-product-meta"><?= e($product['sku'] ?? 'N/A') ?></div>
                    </div>
                    <div class="admin-product-sales">
                        <div class="admin-product-revenue"><?= formatPrice($product['revenue']) ?></div>
                        <div class="admin-product-units"><?= number_format($product['units_sold']) ?> sold</div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Low Stock Alert</h3>
                <a href="<?= url('/admin/products?filter=low_stock') ?>" class="admin-btn admin-btn-ghost admin-btn-sm">View All</a>
            </div>
            <div class="admin-card-body">
                <?php if (empty($lowStockProducts)): ?>
                <div class="admin-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-empty-icon" style="width: 48px; height: 48px;">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <p class="text-muted">All products are well stocked!</p>
                </div>
                <?php else: ?>
                <?php foreach ($lowStockProducts as $product): ?>
                <div class="admin-low-stock-item">
                    <img src="<?= e($product['image'] ?? '/assets/images/placeholder.jpg') ?>"
                         alt="<?= e($product['name']) ?>">
                    <div class="admin-low-stock-info">
                        <div class="admin-low-stock-name"><?= e($product['name']) ?></div>
                        <div class="admin-low-stock-qty">Only <?= $product['stock'] ?> left in stock</div>
                    </div>
                    <a href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>" class="admin-btn admin-btn-sm admin-btn-secondary">
                        Restock
                    </a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Quick Stats</h3>
            </div>
            <div class="admin-card-body">
                <div style="display: grid; gap: 1rem;">
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--admin-border);">
                        <span class="text-muted">Pending Orders</span>
                        <span class="font-semibold"><?= $stats['pending_orders'] ?? 0 ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--admin-border);">
                        <span class="text-muted">Processing Orders</span>
                        <span class="font-semibold"><?= $stats['processing_orders'] ?? 0 ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--admin-border);">
                        <span class="text-muted">This Month Revenue</span>
                        <span class="font-semibold"><?= formatPrice($stats['month_revenue'] ?? 0) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem 0;">
                        <span class="text-muted">Average Order Value</span>
                        <span class="font-semibold"><?= formatPrice($stats['avg_order_value'] ?? 0) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesData = <?= json_encode($salesData) ?>;

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.map(d => d.date),
            datasets: [
                {
                    label: 'Revenue',
                    data: salesData.map(d => d.revenue),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    label: 'Orders',
                    data: salesData.map(d => d.orders),
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    borderDash: [5, 5],
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label === 'Revenue') {
                                return 'Revenue: R ' + context.parsed.y.toLocaleString();
                            }
                            return 'Orders: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: {
                        color: '#e2e8f0'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'R ' + value.toLocaleString();
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });

    // Period selector
    document.getElementById('chartPeriod').addEventListener('change', function() {
        // In production, this would fetch new data via AJAX
        // For now, just a placeholder
        console.log('Period changed to:', this.value);
    });
});
</script>
