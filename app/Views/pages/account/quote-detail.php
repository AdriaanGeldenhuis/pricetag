<!-- Account - Quote Detail -->
<div class="account-page">
    <div class="container py-8">
        <div class="account-layout">
            <!-- Sidebar Navigation -->
            <?php include APP_PATH . '/Views/pages/account/_sidebar.php'; ?>

            <!-- Main Content -->
            <div class="account-main">
                <!-- Breadcrumb -->
                <div class="account-breadcrumb">
                    <a href="<?= url('/account/quotes') ?>">Quotes</a>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    <span>#<?= e($quote->quote_number) ?></span>
                </div>

                <!-- Page Header -->
                <div class="account-page-header">
                    <div>
                        <h1 class="account-page-title"><?= e($quote->title ?? 'Quote #' . $quote->quote_number) ?></h1>
                        <p class="text-muted text-sm">
                            Created <?= date('d M Y', strtotime($quote->created_at)) ?>
                            <?php if ($quote->valid_until): ?>
                            &middot; Valid until <?= date('d M Y', strtotime($quote->valid_until)) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <?php
                        $statusColors = [
                            'draft' => 'status-pending', 'submitted' => 'status-processing',
                            'reviewed' => 'status-shipped', 'accepted' => 'status-delivered',
                            'rejected' => 'status-cancelled', 'expired' => 'status-cancelled',
                            'converted' => 'status-delivered',
                        ];
                        $statusClass = $statusColors[$quote->status] ?? 'status-pending';
                        ?>
                        <span class="order-status <?= $statusClass ?>"><?= ucfirst($quote->status) ?></span>
                        <a href="<?= url('/account/quotes/' . $quote->id . '/print') ?>" class="btn btn-sm btn-ghost" target="_blank">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <rect x="6" y="14" width="12" height="8"></rect>
                            </svg>
                            Print
                        </a>
                    </div>
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

                <?php if ($quote->isEditable()): ?>
                <!-- Product Search -->
                <div class="card">
                    <div class="card-header" style="display: flex; align-items: center; gap: 0.75rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <div>
                            <h2 style="font-size: 1rem; font-weight: 600; margin: 0;">Add Products</h2>
                            <p class="text-muted text-sm" style="margin: 0;">Search and add products to your quote</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="product-search-wrapper">
                            <input type="text" id="productSearch" class="form-input" placeholder="Search products by name or SKU..." autocomplete="off">
                            <div id="searchResults" class="search-results" style="display: none;"></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quote Items -->
                <div class="card">
                    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
                        <h2 style="font-size: 1rem; font-weight: 600; margin: 0;">
                            Quote Items
                            <span class="text-muted text-sm" style="font-weight: 400;" id="itemCount">(<?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?>)</span>
                        </h2>
                    </div>

                    <?php if (empty($items)): ?>
                    <div class="card-body">
                        <div class="empty-state" style="padding: 2rem;">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                            </div>
                            <h3>No products yet</h3>
                            <p class="text-muted">Use the search above to add products to your quote.</p>
                        </div>
                    </div>
                    <?php else: ?>
                    <div id="quoteItemsList">
                        <!-- Mobile Cards -->
                        <div class="quote-items-mobile lg:hidden">
                            <?php foreach ($items as $item): ?>
                            <div class="quote-item-card" data-item-id="<?= $item['id'] ?>">
                                <div class="quote-item-card-main">
                                    <?php if (!empty($item['product_image'])): ?>
                                    <img src="<?= e(asset('uploads/' . $item['product_image'])) ?>" alt="" class="quote-item-thumb">
                                    <?php else: ?>
                                    <div class="quote-item-thumb quote-item-thumb-placeholder">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                    </div>
                                    <?php endif; ?>
                                    <div class="quote-item-info">
                                        <div class="quote-item-name"><?= e($item['name']) ?></div>
                                        <?php if ($item['sku']): ?>
                                        <div class="text-muted text-sm">SKU: <?= e($item['sku']) ?></div>
                                        <?php endif; ?>
                                        <div style="margin-top: 0.25rem;">
                                            <span class="font-semibold"><?= formatPrice((float)$item['price']) ?></span>
                                            <span class="text-muted">&times; <?= $item['quantity'] ?></span>
                                            <span class="font-semibold" style="margin-left: 0.5rem;">=  <?= formatPrice((float)$item['total']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($quote->isEditable()): ?>
                                <div class="quote-item-card-actions">
                                    <div class="qty-control">
                                        <button type="button" class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)">-</button>
                                        <span class="qty-value"><?= $item['quantity'] ?></span>
                                        <button type="button" class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)">+</button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-ghost" style="color: var(--color-danger);" onclick="removeItem(<?= $item['id'] ?>)">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        Remove
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Desktop Table -->
                        <div class="orders-table-wrapper hidden lg:block">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th style="width: 45%;">Product</th>
                                        <th>Unit Price</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                        <?php if ($quote->isEditable()): ?>
                                        <th></th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr data-item-id="<?= $item['id'] ?>">
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <?php if (!empty($item['product_image'])): ?>
                                                <img src="<?= e(asset('uploads/' . $item['product_image'])) ?>" alt="" class="quote-item-thumb-sm">
                                                <?php endif; ?>
                                                <div>
                                                    <div class="font-semibold"><?= e($item['name']) ?></div>
                                                    <?php if ($item['sku']): ?>
                                                    <div class="text-muted text-sm">SKU: <?= e($item['sku']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= formatPrice((float)$item['price']) ?></td>
                                        <td>
                                            <?php if ($quote->isEditable()): ?>
                                            <div class="qty-control">
                                                <button type="button" class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)">-</button>
                                                <span class="qty-value"><?= $item['quantity'] ?></span>
                                                <button type="button" class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)">+</button>
                                            </div>
                                            <?php else: ?>
                                            <?= $item['quantity'] ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="font-semibold"><?= formatPrice((float)$item['total']) ?></td>
                                        <?php if ($quote->isEditable()): ?>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-ghost" style="color: var(--color-danger);" onclick="removeItem(<?= $item['id'] ?>)">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            </button>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Totals -->
                    <div class="quote-totals-section">
                        <div class="quote-totals">
                            <div class="quote-totals-row">
                                <span>Subtotal</span>
                                <span id="totalSubtotal"><?= formatPrice((float)$quote->subtotal) ?></span>
                            </div>
                            <?php if ((float)$quote->tax_amount > 0): ?>
                            <div class="quote-totals-row">
                                <span>VAT (incl.)</span>
                                <span id="totalTax"><?= formatPrice((float)$quote->tax_amount) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="quote-totals-row total">
                                <span>Total</span>
                                <span id="totalAmount"><?= formatPrice((float)$quote->total) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quote Details -->
                <?php if ($quote->customer_notes || $quote->admin_notes || $quote->contact_name): ?>
                <div class="card">
                    <div class="card-header">
                        <h2 style="font-size: 1rem; font-weight: 600; margin: 0;">Quote Details</h2>
                    </div>
                    <div class="card-body">
                        <div class="account-info-grid">
                            <?php if ($quote->contact_name): ?>
                            <div class="account-info-item">
                                <span class="account-info-label">Contact</span>
                                <span class="account-info-value"><?= e($quote->contact_name) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($quote->contact_email): ?>
                            <div class="account-info-item">
                                <span class="account-info-label">Email</span>
                                <span class="account-info-value"><?= e($quote->contact_email) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($quote->company_name): ?>
                            <div class="account-info-item">
                                <span class="account-info-label">Company</span>
                                <span class="account-info-value"><?= e($quote->company_name) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($quote->customer_notes): ?>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--color-border);">
                            <span class="account-info-label">Your Notes</span>
                            <p style="margin: 0.25rem 0 0; font-size: 0.9375rem; white-space: pre-wrap;"><?= e($quote->customer_notes) ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($quote->admin_notes): ?>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--color-border);">
                            <span class="account-info-label">Admin Response</span>
                            <p style="margin: 0.25rem 0 0; font-size: 0.9375rem; white-space: pre-wrap;"><?= e($quote->admin_notes) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; flex-wrap: wrap;">
                    <?php if ($quote->status === 'draft'): ?>
                    <form action="<?= url('/account/quotes/' . $quote->id) ?>" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this draft quote?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-ghost" style="color: var(--color-danger);">Delete Draft</button>
                    </form>
                    <form action="<?= url('/account/quotes/' . $quote->id . '/submit') ?>" method="POST" style="margin: 0;">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-primary"
                                <?= count($items) === 0 ? 'disabled title="Add products first"' : '' ?>>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                            Submit Quote
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/Views/pages/account/_styles.php'; ?>

<style>
/* Product Search */
.product-search-wrapper {
    position: relative;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--color-background);
    border: 1px solid var(--color-border);
    border-top: none;
    border-radius: 0 0 var(--radius-md) var(--radius-md);
    max-height: 400px;
    overflow-y: auto;
    z-index: 50;
    box-shadow: var(--shadow-lg);
}

.search-result-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: background 0.15s;
    border-bottom: 1px solid var(--color-border);
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-item:hover {
    background: var(--color-background-alt);
}

.search-result-image {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: var(--radius-sm);
    object-fit: cover;
    flex-shrink: 0;
    background: var(--color-neutral-100);
}

.search-result-info {
    flex: 1;
    min-width: 0;
}

.search-result-name {
    font-weight: 500;
    font-size: 0.875rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.search-result-meta {
    display: flex;
    gap: 0.75rem;
    font-size: 0.75rem;
    color: var(--color-text-muted);
}

.search-result-price {
    font-weight: 600;
    color: var(--color-text);
    white-space: nowrap;
}

.search-no-results {
    padding: 1.5rem;
    text-align: center;
    color: var(--color-text-muted);
    font-size: 0.875rem;
}

.search-loading {
    padding: 1.5rem;
    text-align: center;
    color: var(--color-text-muted);
    font-size: 0.875rem;
}

/* Quote Items */
.quote-item-card {
    padding: 1rem;
    border-bottom: 1px solid var(--color-border);
}

.quote-item-card:last-child {
    border-bottom: none;
}

.quote-item-card-main {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.quote-item-card-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--color-border);
}

.quote-item-thumb {
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius-sm);
    object-fit: cover;
    flex-shrink: 0;
}

.quote-item-thumb-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-neutral-100);
    color: var(--color-text-muted);
}

.quote-item-thumb-placeholder svg {
    width: 1.25rem;
    height: 1.25rem;
}

.quote-item-thumb-sm {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: var(--radius-sm);
    object-fit: cover;
}

.quote-item-info {
    flex: 1;
    min-width: 0;
}

.quote-item-name {
    font-weight: 600;
    font-size: 0.9375rem;
}

/* Quantity Control */
.qty-control {
    display: inline-flex;
    align-items: center;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    overflow: hidden;
}

.qty-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    background: var(--color-background-alt);
    border: none;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    color: var(--color-text);
    transition: background 0.15s;
}

.qty-btn:hover {
    background: var(--color-neutral-200);
}

.qty-value {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    height: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
}

/* Totals */
.quote-totals-section {
    display: flex;
    justify-content: flex-end;
    padding: 1.5rem;
    border-top: 1px solid var(--color-border);
}

.quote-totals {
    width: 280px;
}

.quote-totals-row {
    display: flex;
    justify-content: space-between;
    padding: 0.375rem 0;
    font-size: 0.9375rem;
}

.quote-totals-row.total {
    border-top: 2px solid var(--color-text);
    margin-top: 0.5rem;
    padding-top: 0.75rem;
    font-size: 1.125rem;
    font-weight: 700;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('productSearch');
    const searchResults = document.getElementById('searchResults');
    if (!searchInput || !searchResults) return;

    let searchTimeout;
    const csrfToken = '<?= csrfToken() ?>';
    const quoteId = <?= (int)$quote->id ?>;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        searchResults.innerHTML = '<div class="search-loading">Searching...</div>';
        searchResults.style.display = 'block';

        searchTimeout = setTimeout(function() {
            fetch('/account/quotes/search-products?q=' + encodeURIComponent(query), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.products || data.products.length === 0) {
                    searchResults.innerHTML = '<div class="search-no-results">No products found for "' + query.replace(/</g, '&lt;') + '"</div>';
                    return;
                }

                let html = '';
                data.products.forEach(function(p) {
                    const imgSrc = p.image ? '/assets/uploads/' + p.image : '';
                    const imgHtml = imgSrc
                        ? '<img src="' + imgSrc + '" alt="" class="search-result-image">'
                        : '<div class="search-result-image" style="display:flex;align-items:center;justify-content:center;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"></rect></svg></div>';

                    html += '<div class="search-result-item" data-product-id="' + p.id + '">'
                        + imgHtml
                        + '<div class="search-result-info">'
                        + '<div class="search-result-name">' + p.name.replace(/</g, '&lt;') + '</div>'
                        + '<div class="search-result-meta">'
                        + '<span>SKU: ' + (p.sku || '-').replace(/</g, '&lt;') + '</span>'
                        + '</div>'
                        + '</div>'
                        + '<div class="search-result-price">R' + parseFloat(p.price).toFixed(2) + '</div>'
                        + '</div>';
                });

                searchResults.innerHTML = html;

                // Click handlers for results
                searchResults.querySelectorAll('.search-result-item').forEach(function(item) {
                    item.addEventListener('click', function() {
                        addProductToQuote(this.dataset.productId);
                    });
                });
            })
            .catch(function() {
                searchResults.innerHTML = '<div class="search-no-results">Search failed. Please try again.</div>';
            });
        }, 300);
    });

    // Close search results on click outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    // Add product to quote
    window.addProductToQuote = function(productId) {
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('product_id', productId);
        formData.append('quantity', 1);

        fetch('/account/quotes/' + quoteId + '/items', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                searchInput.value = '';
                searchResults.style.display = 'none';
                // Reload to show updated items
                window.location.reload();
            } else {
                alert(data.error || 'Failed to add product');
            }
        })
        .catch(function() {
            alert('Failed to add product. Please try again.');
        });
    };

    // Update quantity
    window.updateQty = function(itemId, newQty) {
        if (newQty < 1) {
            removeItem(itemId);
            return;
        }

        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('item_id', itemId);
        formData.append('quantity', newQty);

        fetch('/account/quotes/' + quoteId + '/items/update', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    };

    // Remove item
    window.removeItem = function(itemId) {
        if (!confirm('Remove this item from the quote?')) return;

        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('item_id', itemId);

        fetch('/account/quotes/' + quoteId + '/items/remove', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    };
});
</script>
