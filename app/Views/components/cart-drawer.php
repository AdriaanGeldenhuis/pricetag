<!-- Cart Drawer -->
<div class="cart-drawer" id="cart-drawer" aria-hidden="true">
    <div class="cart-drawer-backdrop" id="cart-drawer-backdrop"></div>
    <div class="cart-drawer-panel" role="dialog" aria-label="Shopping cart">
        <!-- Header -->
        <div class="cart-drawer-header">
            <h2 class="cart-drawer-title">
                Your Cart
                <span class="cart-drawer-count">(<span id="cart-drawer-count">0</span> items)</span>
            </h2>
            <button type="button" class="cart-drawer-close" id="cart-drawer-close" aria-label="Close cart">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="cart-drawer-body" id="cart-drawer-body">
            <!-- Empty state -->
            <div class="cart-drawer-empty" id="cart-empty">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <p class="mt-4 font-medium">Your cart is empty</p>
                <p class="text-sm mt-2">Add some items to get started!</p>
                <a href="<?= url('/products') ?>" class="btn btn-primary mt-6">Browse Products</a>
            </div>

            <!-- Cart items container -->
            <div id="cart-items"></div>
        </div>

        <!-- Footer -->
        <div class="cart-drawer-footer" id="cart-drawer-footer" style="display: none;">
            <div class="cart-summary">
                <div class="cart-summary-row">
                    <span>Subtotal</span>
                    <span id="cart-subtotal">R0.00</span>
                </div>
                <div class="cart-summary-row">
                    <span>Shipping</span>
                    <span id="cart-shipping">Calculated at checkout</span>
                </div>
                <div class="cart-summary-row total">
                    <span>Total</span>
                    <span id="cart-total">R0.00</span>
                </div>
            </div>
            <div class="cart-drawer-actions">
                <a href="<?= url('/checkout') ?>" class="btn btn-primary btn-block btn-lg">
                    Proceed to Checkout
                </a>
                <a href="<?= url('/cart') ?>" class="btn btn-outline btn-block">
                    View Cart
                </a>
            </div>
        </div>
    </div>
</div>
