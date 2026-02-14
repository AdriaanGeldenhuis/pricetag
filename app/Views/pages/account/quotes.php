<!-- Account - Quotes List -->
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
                        <h1 class="account-page-title">My Quotes</h1>
                        <p class="text-muted text-sm">Request quotes for bulk or custom orders</p>
                    </div>
                    <a href="<?= url('/account/quotes/create') ?>" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        New Quote
                    </a>
                </div>

                <!-- Flash Messages -->
                <?php if ($msg = flash('success')): ?>
                <div class="alert alert-success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <div class="alert-content"><?= e($msg) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($msg = flash('error')): ?>
                <div class="alert alert-error">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    <div class="alert-content"><?= e($msg) ?></div>
                </div>
                <?php endif; ?>

                <!-- Status Filter -->
                <div class="quote-filters">
                    <a href="<?= url('/account/quotes') ?>" class="filter-chip <?= empty($currentStatus) ? 'active' : '' ?>">All</a>
                    <a href="<?= url('/account/quotes?status=draft') ?>" class="filter-chip <?= $currentStatus === 'draft' ? 'active' : '' ?>">Drafts</a>
                    <a href="<?= url('/account/quotes?status=submitted') ?>" class="filter-chip <?= $currentStatus === 'submitted' ? 'active' : '' ?>">Submitted</a>
                    <a href="<?= url('/account/quotes?status=accepted') ?>" class="filter-chip <?= $currentStatus === 'accepted' ? 'active' : '' ?>">Accepted</a>
                    <a href="<?= url('/account/quotes?status=converted') ?>" class="filter-chip <?= $currentStatus === 'converted' ? 'active' : '' ?>">Converted</a>
                </div>

                <!-- Quotes List -->
                <div class="card">
                    <?php if (empty($quotes)): ?>
                    <div class="card-body">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                            </div>
                            <h3>No quotes yet</h3>
                            <p class="text-muted">Create a quote to get pricing for multiple products at once.</p>
                            <a href="<?= url('/account/quotes/create') ?>" class="btn btn-primary">Create Your First Quote</a>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Mobile Cards -->
                    <div class="orders-cards lg:hidden">
                        <?php foreach ($quotes as $quote): ?>
                        <a href="<?= url('/account/quotes/' . $quote['id']) ?>" class="order-card">
                            <div class="order-card-header">
                                <span class="order-card-number">#<?= e($quote['quote_number']) ?></span>
                                <?php
                                $statusColors = [
                                    'draft' => 'status-pending', 'submitted' => 'status-processing',
                                    'reviewed' => 'status-shipped', 'accepted' => 'status-delivered',
                                    'rejected' => 'status-cancelled', 'expired' => 'status-cancelled',
                                    'converted' => 'status-delivered',
                                ];
                                $statusClass = $statusColors[$quote['status']] ?? 'status-pending';
                                ?>
                                <span class="order-status <?= $statusClass ?>">
                                    <?= ucfirst($quote['status']) ?>
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
                                        <?= date('d M Y', strtotime($quote['created_at'])) ?>
                                    </span>
                                    <span class="order-card-items">
                                        <?= $quote['item_count'] ?? 0 ?> item<?= ($quote['item_count'] ?? 0) !== 1 ? 's' : '' ?>
                                    </span>
                                </div>
                                <div style="font-size: 0.8125rem; color: var(--color-text-muted); margin-bottom: 0.25rem;"><?= e($quote['title'] ?? 'Untitled Quote') ?></div>
                                <div class="order-card-total">
                                    <span class="order-card-total-label">Total</span>
                                    <span class="order-card-total-value"><?= formatPrice((float)$quote['total']) ?></span>
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
                                    <th>Quote</th>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($quotes as $quote): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('/account/quotes/' . $quote['id']) ?>" class="order-number">
                                            #<?= e($quote['quote_number']) ?>
                                        </a>
                                    </td>
                                    <td><?= e(truncate($quote['title'] ?? 'Untitled', 40)) ?></td>
                                    <td class="text-muted">
                                        <?= date('d M Y', strtotime($quote['created_at'])) ?>
                                    </td>
                                    <td class="text-muted">
                                        <?= $quote['item_count'] ?? 0 ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = $statusColors[$quote['status']] ?? 'status-pending';
                                        ?>
                                        <span class="order-status <?= $statusClass ?>">
                                            <?= ucfirst($quote['status']) ?>
                                        </span>
                                    </td>
                                    <td class="font-semibold"><?= formatPrice((float)$quote['total']) ?></td>
                                    <td class="text-right">
                                        <a href="<?= url('/account/quotes/' . $quote['id']) ?>" class="btn btn-sm btn-ghost">
                                            View
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
                            <a href="<?= url('/account/quotes?page=' . ($pagination['current_page'] - 1) . ($currentStatus ? '&status=' . $currentStatus : '')) ?>" class="pagination-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                            </a>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $pagination['current_page'] - 2);
                            $end = min($pagination['last_page'], $pagination['current_page'] + 2);
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                            <a href="<?= url('/account/quotes?page=' . $i . ($currentStatus ? '&status=' . $currentStatus : '')) ?>"
                               class="pagination-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                            <?php endfor; ?>

                            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <a href="<?= url('/account/quotes?page=' . ($pagination['current_page'] + 1) . ($currentStatus ? '&status=' . $currentStatus : '')) ?>" class="pagination-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
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
.quote-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.875rem;
    font-size: 0.8125rem;
    font-weight: 500;
    border-radius: 9999px;
    border: 1px solid var(--color-border);
    background: var(--color-background);
    color: var(--color-text-muted);
    text-decoration: none;
    transition: all 0.2s;
}

.filter-chip:hover {
    border-color: var(--color-primary);
    color: var(--color-primary);
}

.filter-chip.active {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: white;
}
</style>
