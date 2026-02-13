<?php
/**
 * Wishlist Page
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Features:
 * - Wishlist display with product details
 * - Move to cart functionality
 * - Remove items with animation
 * - Empty wishlist state
 * - Share wishlist feature
 * - Stock status indicators
 */

$isEmpty = empty($items);
?>

<!-- Breadcrumbs -->
<nav class="breadcrumb-rings container">
    <div class="breadcrumb-rings-list">
        <a href="<?= url('/') ?>" class="bc-ring-item">
            <div class="bc-ring-wrap">
                <div class="bc-ring-border"></div>
                <div class="bc-ring-inner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
            </div>
            <span class="bc-ring-label">Home</span>
        </a>
        <span class="bc-ring-sep"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></span>
        <a href="<?= url('/account') ?>" class="bc-ring-item">
            <div class="bc-ring-wrap">
                <div class="bc-ring-border"></div>
                <div class="bc-ring-inner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
            </div>
            <span class="bc-ring-label">Account</span>
        </a>
        <span class="bc-ring-sep"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></span>
        <span class="bc-ring-item bc-ring-active">
            <div class="bc-ring-wrap">
                <div class="bc-ring-border"></div>
                <div class="bc-ring-inner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </div>
            </div>
            <span class="bc-ring-label">Wishlist</span>
        </span>
    </div>
</nav>

<div class="wishlist-page container py-6">
    <div class="wishlist-header">
        <div class="wishlist-header-content">
            <h1 class="wishlist-title">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                My Wishlist
                <?php if (!$isEmpty): ?>
                <span class="wishlist-count">(<?= count($items) ?> items)</span>
                <?php endif; ?>
            </h1>
            <?php if (!$isEmpty): ?>
            <p class="wishlist-subtitle">Items you've saved for later</p>
            <?php endif; ?>
        </div>

        <?php if (!$isEmpty): ?>
        <div class="wishlist-actions">
            <button type="button" class="btn btn-outline btn-sm" id="share-wishlist">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="18" cy="5" r="3"></circle>
                    <circle cx="6" cy="12" r="3"></circle>
                    <circle cx="18" cy="19" r="3"></circle>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                </svg>
                Share
            </button>
            <button type="button" class="btn btn-primary btn-sm" id="add-all-to-cart">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                Add All to Cart
            </button>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($isEmpty): ?>
    <!-- Empty Wishlist State -->
    <div class="wishlist-empty">
        <div class="wishlist-empty-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
        </div>
        <h2 class="wishlist-empty-title">Your wishlist is empty</h2>
        <p class="wishlist-empty-text">Save items you love by clicking the heart icon on any product.</p>
        <a href="<?= url('/categories') ?>" class="btn btn-primary btn-lg">Browse Categories</a>
    </div>
    <?php else: ?>
    <!-- Wishlist Items Grid -->
    <div class="wishlist-grid" id="wishlist-grid">
        <?php foreach ($items as $item): ?>
        <article class="wishlist-item" data-wishlist-id="<?= $item['id'] ?>" data-product-id="<?= $item['product_id'] ?>">
            <!-- Remove Button -->
            <button type="button" class="wishlist-item-remove" data-action="remove" aria-label="Remove from wishlist">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>

            <!-- Product Image -->
            <a href="<?= url('/products/' . $item['slug']) ?>" class="wishlist-item-image">
                <?php if (!empty($item['image'])): ?>
                <img src="<?= url('storage/uploads/' . $item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                <?php else: ?>
                <div class="wishlist-item-placeholder">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                </div>
                <?php endif; ?>

                <!-- Stock Badge -->
                <?php if ($item['status'] !== 'active'): ?>
                <span class="wishlist-item-badge wishlist-item-badge-unavailable">Unavailable</span>
                <?php elseif (isset($item['stock_quantity']) && $item['stock_quantity'] <= 0): ?>
                <span class="wishlist-item-badge wishlist-item-badge-out">Out of Stock</span>
                <?php elseif (isset($item['stock_quantity']) && $item['stock_quantity'] <= 5): ?>
                <span class="wishlist-item-badge wishlist-item-badge-low">Only <?= $item['stock_quantity'] ?> left</span>
                <?php endif; ?>
            </a>

            <!-- Product Details -->
            <div class="wishlist-item-details">
                <h3 class="wishlist-item-title">
                    <a href="<?= url('/products/' . $item['slug']) ?>"><?= e($item['name']) ?></a>
                </h3>

                <div class="wishlist-item-price">
                    <span class="wishlist-item-price-current"><?= formatPrice($item['price']) ?></span>
                    <?php if (!empty($item['compare_price']) && $item['compare_price'] > $item['price']): ?>
                    <span class="wishlist-item-price-original"><?= formatPrice($item['compare_price']) ?></span>
                    <span class="wishlist-item-discount">
                        -<?= round((1 - $item['price'] / $item['compare_price']) * 100) ?>%
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Added Date -->
                <p class="wishlist-item-date">
                    Added <?= date('M j, Y', strtotime($item['created_at'])) ?>
                </p>

                <!-- Actions -->
                <div class="wishlist-item-actions">
                    <?php if ($item['status'] === 'active' && (!isset($item['stock_quantity']) || $item['stock_quantity'] > 0)): ?>
                    <button type="button" class="btn btn-primary btn-sm btn-block" data-action="add-to-cart">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        Add to Cart
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn btn-outline btn-sm btn-block" data-action="notify" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        Notify Me
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <!-- Continue Shopping -->
    <div class="wishlist-continue">
        <a href="<?= url('/categories') ?>" class="wishlist-continue-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Continue Shopping
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Share Modal -->
<div class="modal" id="share-modal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Share Your Wishlist</h3>
            <button class="modal-close" data-close-modal aria-label="Close">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <p class="share-modal-text">Share your wishlist with friends and family</p>
            <div class="share-modal-buttons">
                <button type="button" class="share-btn share-btn-whatsapp" data-share="whatsapp">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                </button>
                <button type="button" class="share-btn share-btn-facebook" data-share="facebook">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </button>
                <button type="button" class="share-btn share-btn-twitter" data-share="twitter">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                </button>
                <button type="button" class="share-btn share-btn-email" data-share="email">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </button>
                <button type="button" class="share-btn share-btn-copy" data-share="copy">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                </button>
            </div>
            <div class="share-modal-link">
                <input type="text" readonly class="share-modal-input" id="share-link" value="<?= url('/wishlist/share/' . ($_SESSION['user_id'] ?? '')) ?>">
                <button type="button" class="share-modal-copy" id="copy-link">Copy</button>
            </div>
        </div>
    </div>
</div>

<style>
/* =========================================================================
   WISHLIST PAGE STYLES
   ========================================================================= */

.wishlist-page {
    min-height: 60vh;
}

/* Header */
.wishlist-header {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
    padding-bottom: var(--space-6);
    border-bottom: var(--border-1) solid var(--color-border-light);
}

@media (min-width: 768px) {
    .wishlist-header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.wishlist-title {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
}

.wishlist-title svg {
    color: var(--color-danger);
}

.wishlist-count {
    font-size: var(--text-lg);
    font-weight: var(--font-normal);
    color: var(--color-text-muted);
}

.wishlist-subtitle {
    color: var(--color-text-muted);
    margin-top: var(--space-1);
}

.wishlist-actions {
    display: flex;
    gap: var(--space-3);
}

/* Empty State */
.wishlist-empty {
    text-align: center;
    padding: var(--space-12) var(--space-4);
}

.wishlist-empty-icon {
    color: var(--color-neutral-300);
    margin-bottom: var(--space-6);
}

.wishlist-empty-title {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    margin-bottom: var(--space-2);
}

.wishlist-empty-text {
    color: var(--color-text-muted);
    margin-bottom: var(--space-6);
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

/* Grid */
.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

@media (min-width: 640px) {
    .wishlist-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1024px) {
    .wishlist-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Item */
.wishlist-item {
    position: relative;
    background-color: var(--color-background);
    border-radius: var(--radius-xl);
    border: var(--border-1) solid var(--color-border-light);
    overflow: hidden;
    transition: all var(--duration-200);
}

.wishlist-item:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.wishlist-item.is-removing {
    opacity: 0.5;
    pointer-events: none;
}

.wishlist-item.is-removed {
    animation: fadeScale 0.3s ease-out forwards;
}

@keyframes fadeScale {
    to {
        opacity: 0;
        transform: scale(0.9);
    }
}

/* Remove Button */
.wishlist-item-remove {
    position: absolute;
    top: var(--space-2);
    right: var(--space-2);
    z-index: 10;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--color-background);
    border-radius: var(--radius-full);
    box-shadow: var(--shadow-md);
    color: var(--color-text-muted);
    opacity: 0;
    transform: scale(0.8);
    transition: all var(--duration-200);
}

.wishlist-item:hover .wishlist-item-remove {
    opacity: 1;
    transform: scale(1);
}

.wishlist-item-remove:hover {
    background-color: var(--color-danger);
    color: var(--color-text-inverse);
}

/* Image */
.wishlist-item-image {
    display: block;
    position: relative;
    aspect-ratio: 1;
    background-color: var(--color-background-alt);
}

.wishlist-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-300);
}

.wishlist-item:hover .wishlist-item-image img {
    transform: scale(1.05);
}

.wishlist-item-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-muted);
}

/* Badge */
.wishlist-item-badge {
    position: absolute;
    bottom: var(--space-2);
    left: var(--space-2);
    padding: var(--space-1) var(--space-2);
    font-size: var(--text-xs);
    font-weight: var(--font-medium);
    border-radius: var(--radius-sm);
}

.wishlist-item-badge-out,
.wishlist-item-badge-unavailable {
    background-color: var(--color-danger);
    color: var(--color-text-inverse);
}

.wishlist-item-badge-low {
    background-color: var(--color-warning);
    color: var(--color-neutral-900);
}

/* Details */
.wishlist-item-details {
    padding: var(--space-4);
}

.wishlist-item-title {
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    line-height: var(--leading-snug);
    margin-bottom: var(--space-2);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.wishlist-item-title a:hover {
    color: var(--color-primary);
}

.wishlist-item-price {
    display: flex;
    align-items: baseline;
    gap: var(--space-2);
    flex-wrap: wrap;
    margin-bottom: var(--space-2);
}

.wishlist-item-price-current {
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
    color: var(--color-text);
}

.wishlist-item-price-original {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    text-decoration: line-through;
}

.wishlist-item-discount {
    font-size: var(--text-xs);
    font-weight: var(--font-semibold);
    color: var(--color-danger);
    background-color: var(--color-danger-50);
    padding: var(--space-0-5) var(--space-1);
    border-radius: var(--radius-sm);
}

.wishlist-item-date {
    font-size: var(--text-xs);
    color: var(--color-text-muted);
    margin-bottom: var(--space-3);
}

.wishlist-item-actions {
    display: flex;
    gap: var(--space-2);
}

/* Continue Shopping */
.wishlist-continue {
    margin-top: var(--space-8);
    text-align: center;
}

.wishlist-continue-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    color: var(--color-primary);
    font-weight: var(--font-medium);
}

.wishlist-continue-link:hover {
    text-decoration: underline;
}

/* Share Modal */
.share-modal-text {
    text-align: center;
    color: var(--color-text-muted);
    margin-bottom: var(--space-4);
}

.share-modal-buttons {
    display: flex;
    justify-content: center;
    gap: var(--space-3);
    margin-bottom: var(--space-4);
}

.share-btn {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-full);
    color: white;
    transition: transform var(--duration-200);
}

.share-btn:hover {
    transform: scale(1.1);
}

.share-btn-whatsapp { background-color: #25D366; }
.share-btn-facebook { background-color: #1877F2; }
.share-btn-twitter { background-color: #000000; }
.share-btn-email { background-color: var(--color-text); }
.share-btn-copy { background-color: var(--color-neutral-400); color: white; }

.share-modal-link {
    display: flex;
    gap: var(--space-2);
}

.share-modal-input {
    flex: 1;
    height: 40px;
    padding: 0 var(--space-3);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
    font-size: var(--text-sm);
    background-color: var(--color-background-alt);
}

.share-modal-copy {
    padding: 0 var(--space-4);
    height: 40px;
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    background-color: var(--color-primary);
    color: var(--color-text-inverse);
    border-radius: var(--radius-lg);
    transition: var(--transition-colors);
}

.share-modal-copy:hover {
    background-color: var(--color-primary-600);
}
</style>

<script>
(function() {
    'use strict';

    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

    const WishlistPage = {
        init() {
            this.bindRemoveButtons();
            this.bindAddToCartButtons();
            this.bindAddAllToCart();
            this.bindShareModal();
        },

        bindRemoveButtons() {
            $$('[data-action="remove"]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const item = btn.closest('.wishlist-item');
                    const wishlistId = item.dataset.wishlistId;

                    item.classList.add('is-removing');

                    try {
                        const formData = new FormData();
                        formData.append('_token', window.Pricetag.csrfToken);
                        formData.append('wishlist_id', wishlistId);

                        const response = await fetch(window.Pricetag.baseUrl + '/wishlist/remove', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        const data = await response.json();
                        if (data.success) {
                            item.classList.add('is-removed');
                            setTimeout(() => {
                                item.remove();
                                this.updateCount();
                            }, 300);
                            window.Pricetag.Toast?.success('Item removed from wishlist');
                        }
                    } catch (err) {
                        item.classList.remove('is-removing');
                        window.Pricetag.Toast?.error('Failed to remove item');
                    }
                });
            });
        },

        bindAddToCartButtons() {
            $$('[data-action="add-to-cart"]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const item = btn.closest('.wishlist-item');
                    const wishlistId = item.dataset.wishlistId;

                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner"></span> Adding...';

                    try {
                        const formData = new FormData();
                        formData.append('_token', window.Pricetag.csrfToken);
                        formData.append('wishlist_id', wishlistId);

                        const response = await fetch(window.Pricetag.baseUrl + '/wishlist/move-to-cart', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        const data = await response.json();
                        if (data.success) {
                            item.classList.add('is-removed');
                            setTimeout(() => {
                                item.remove();
                                this.updateCount();
                            }, 300);

                            // Update cart count
                            const cartCount = document.querySelector('#cart-count');
                            if (cartCount) {
                                cartCount.textContent = data.cart_count || '';
                                cartCount.classList.add('is-animating');
                            }

                            window.Pricetag.Toast?.success('Item moved to cart');
                            window.Pricetag.Cart?.open?.();
                        } else {
                            btn.disabled = false;
                            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg> Add to Cart';
                            window.Pricetag.Toast?.error(data.message || 'Could not add to cart');
                        }
                    } catch (err) {
                        btn.disabled = false;
                        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg> Add to Cart';
                        window.Pricetag.Toast?.error('Failed to add to cart');
                    }
                });
            });
        },

        bindAddAllToCart() {
            $('#add-all-to-cart')?.addEventListener('click', async () => {
                const items = $$('.wishlist-item');
                let added = 0;

                for (const item of items) {
                    const btn = item.querySelector('[data-action="add-to-cart"]');
                    if (btn && !btn.disabled) {
                        btn.click();
                        added++;
                        await new Promise(r => setTimeout(r, 500)); // Small delay between each
                    }
                }

                if (added === 0) {
                    window.Pricetag.Toast?.info('No items available to add');
                }
            });
        },

        bindShareModal() {
            const modal = $('#share-modal');
            if (!modal) return;

            $('#share-wishlist')?.addEventListener('click', () => {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            });

            $$('[data-close-modal]', modal).forEach(el => {
                el.addEventListener('click', () => {
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                });
            });

            // Share buttons
            $$('[data-share]', modal).forEach(btn => {
                btn.addEventListener('click', () => {
                    const type = btn.dataset.share;
                    const url = $('#share-link').value;
                    const text = 'Check out my wishlist!';

                    switch (type) {
                        case 'whatsapp':
                            window.open(`https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`, '_blank');
                            break;
                        case 'facebook':
                            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
                            break;
                        case 'twitter':
                            window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`, '_blank');
                            break;
                        case 'email':
                            window.location.href = `mailto:?subject=${encodeURIComponent(text)}&body=${encodeURIComponent(url)}`;
                            break;
                        case 'copy':
                            navigator.clipboard.writeText(url).then(() => {
                                window.Pricetag.Toast?.success('Link copied to clipboard');
                            });
                            break;
                    }
                });
            });

            $('#copy-link')?.addEventListener('click', () => {
                const input = $('#share-link');
                navigator.clipboard.writeText(input.value).then(() => {
                    window.Pricetag.Toast?.success('Link copied to clipboard');
                });
            });
        },

        updateCount() {
            const items = $$('.wishlist-item');
            const countEl = $('.wishlist-count');
            const wishlistCount = document.querySelector('#wishlist-count');

            if (countEl) {
                countEl.textContent = `(${items.length} items)`;
            }

            if (wishlistCount) {
                wishlistCount.textContent = items.length || '';
            }

            // If empty, reload to show empty state
            if (items.length === 0) {
                location.reload();
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => WishlistPage.init());
})();
</script>
