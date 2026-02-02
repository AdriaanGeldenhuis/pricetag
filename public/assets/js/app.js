/**
 * Main Application JavaScript
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Mobile-first, performance-focused, no page reloads.
 */

(function() {
  'use strict';

  // ==========================================================================
  // UTILITIES
  // ==========================================================================

  const $ = (selector, context = document) => context.querySelector(selector);
  const $$ = (selector, context = document) => [...context.querySelectorAll(selector)];

  const debounce = (fn, delay) => {
    let timeout;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => fn(...args), delay);
    };
  };

  const formatPrice = (amount) => {
    const { symbol, decimals } = window.Pricetag.currency;
    return symbol + Number(amount).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
  };

  const fetchJSON = async (url, options = {}) => {
    const defaultOptions = {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': window.Pricetag.csrfToken,
      },
    };

    const response = await fetch(url, { ...defaultOptions, ...options });
    return response.json();
  };

  const postForm = async (url, data) => {
    const formData = new FormData();
    formData.append('_token', window.Pricetag.csrfToken);
    Object.entries(data).forEach(([key, value]) => formData.append(key, value));

    const response = await fetch(url, {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    return response.json();
  };

  // ==========================================================================
  // TOAST NOTIFICATIONS
  // ==========================================================================

  const Toast = {
    container: null,

    init() {
      this.container = $('#toast-container');
    },

    show(message, type = 'info', duration = 4000) {
      const icons = {
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
        error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
      };

      const toast = document.createElement('div');
      toast.className = `toast toast-${type}`;
      toast.innerHTML = `
        <span class="toast-icon">${icons[type]}</span>
        <div class="toast-content">
          <p class="toast-message">${message}</p>
        </div>
        <button class="toast-close" aria-label="Close">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      `;

      this.container.appendChild(toast);

      const close = () => {
        toast.style.animation = 'fadeOut 0.2s ease-out forwards';
        setTimeout(() => toast.remove(), 200);
      };

      toast.querySelector('.toast-close').addEventListener('click', close);
      setTimeout(close, duration);
    },

    success(message) { this.show(message, 'success'); },
    error(message) { this.show(message, 'error'); },
    warning(message) { this.show(message, 'warning'); },
    info(message) { this.show(message, 'info'); },
  };

  // ==========================================================================
  // HEADER
  // ==========================================================================

  const Header = {
    element: null,
    scrolled: false,
    shrunk: false,
    lastScrollY: 0,

    init() {
      this.element = $('#site-header');
      if (!this.element) return;

      window.addEventListener('scroll', () => this.handleScroll(), { passive: true });
      this.handleScroll();
    },

    handleScroll() {
      const scrollY = window.scrollY;

      // Add shadow when scrolled
      const shouldBeScrolled = scrollY > 50;
      if (shouldBeScrolled !== this.scrolled) {
        this.scrolled = shouldBeScrolled;
        this.element.classList.toggle('is-scrolled', shouldBeScrolled);
      }

      // Shrink header when scrolled down more than 100px
      const shouldBeShrunk = scrollY > 100;
      if (shouldBeShrunk !== this.shrunk) {
        this.shrunk = shouldBeShrunk;
        this.element.classList.toggle('is-shrunk', shouldBeShrunk);
      }

      this.lastScrollY = scrollY;
    },
  };

  // ==========================================================================
  // MEGA MENU
  // ==========================================================================

  const MegaMenu = {
    activeItem: null,
    hoverTimeout: null,

    init() {
      const navItems = $$('.nav-item');
      if (!navItems.length) return;

      navItems.forEach(item => {
        const megaMenu = item.querySelector('.mega-menu');
        if (!megaMenu) return;

        // Mouse enter with delay
        item.addEventListener('mouseenter', () => {
          clearTimeout(this.hoverTimeout);
          this.hoverTimeout = setTimeout(() => {
            this.openMenu(item, megaMenu);
          }, 100);
        });

        // Mouse leave with delay for smooth UX
        item.addEventListener('mouseleave', () => {
          clearTimeout(this.hoverTimeout);
          this.hoverTimeout = setTimeout(() => {
            this.closeMenu(item, megaMenu);
          }, 200);
        });

        // Keyboard accessibility
        const link = item.querySelector('.nav-link');
        if (link) {
          link.addEventListener('focus', () => this.openMenu(item, megaMenu));
          link.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
              this.closeMenu(item, megaMenu);
              link.focus();
            }
          });
        }
      });
    },

    openMenu(item, menu) {
      if (this.activeItem && this.activeItem !== item) {
        this.closeMenu(this.activeItem, this.activeItem.querySelector('.mega-menu'));
      }
      this.activeItem = item;
      item.classList.add('is-active');
      const link = item.querySelector('.nav-link');
      if (link) link.setAttribute('aria-expanded', 'true');
    },

    closeMenu(item, menu) {
      item.classList.remove('is-active');
      const link = item.querySelector('.nav-link');
      if (link) link.setAttribute('aria-expanded', 'false');
      if (this.activeItem === item) this.activeItem = null;
    },
  };

  // ==========================================================================
  // MOBILE MENU
  // ==========================================================================

  const MobileMenu = {
    menu: null,
    toggle: null,
    isOpen: false,

    init() {
      this.menu = $('#mobile-menu');
      this.toggle = $('#mobile-menu-toggle');
      if (!this.menu || !this.toggle) return;

      this.toggle.addEventListener('click', () => this.open());
      $('#mobile-menu-close')?.addEventListener('click', () => this.close());
      $('#mobile-menu-backdrop')?.addEventListener('click', () => this.close());

      // Expandable items
      $$('.mobile-nav-item button', this.menu).forEach(btn => {
        btn.addEventListener('click', () => {
          btn.parentElement.classList.toggle('is-expanded');
          btn.setAttribute('aria-expanded', btn.parentElement.classList.contains('is-expanded'));
        });
      });

      // Close on escape
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && this.isOpen) this.close();
      });
    },

    open() {
      this.isOpen = true;
      this.menu.classList.add('is-open');
      this.menu.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    },

    close() {
      this.isOpen = false;
      this.menu.classList.remove('is-open');
      this.menu.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    },
  };

  // ==========================================================================
  // CART DRAWER
  // ==========================================================================

  const CartDrawer = {
    drawer: null,
    isOpen: false,

    init() {
      this.drawer = $('#cart-drawer');
      if (!this.drawer) return;

      $('#cart-toggle')?.addEventListener('click', () => this.open());
      $('#cart-drawer-close')?.addEventListener('click', () => this.close());
      $('#cart-drawer-backdrop')?.addEventListener('click', () => this.close());

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && this.isOpen) this.close();
      });

      // Initial load
      this.refresh();
    },

    open() {
      this.isOpen = true;
      this.drawer.classList.add('is-open');
      this.drawer.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      this.refresh();
    },

    close() {
      this.isOpen = false;
      this.drawer.classList.remove('is-open');
      this.drawer.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    },

    async refresh() {
      try {
        const data = await fetchJSON(window.Pricetag.baseUrl + '/cart/data');
        this.render(data);
      } catch (err) {
        console.error('Failed to load cart', err);
      }
    },

    render(data) {
      const { items, count, subtotal, shipping, total } = data;

      // Update counts
      $('#cart-count').textContent = count || '';
      $('#cart-count').dataset.count = count;
      $('#cart-drawer-count').textContent = count;

      // Toggle empty state
      const isEmpty = !items || items.length === 0;
      $('#cart-empty').style.display = isEmpty ? 'flex' : 'none';
      $('#cart-drawer-footer').style.display = isEmpty ? 'none' : 'block';

      // Render items
      const container = $('#cart-items');
      if (!container) return;

      container.innerHTML = items.map(item => `
        <div class="cart-item" data-item-id="${item.id}">
          <div class="cart-item-image">
            ${item.image ? `<img src="${window.Pricetag.baseUrl}/storage/uploads/${item.image}" alt="${item.name}">` : ''}
          </div>
          <div class="cart-item-details">
            <h4 class="cart-item-title">${item.name}</h4>
            ${item.variant_name ? `<p class="cart-item-variant">${item.variant_name}</p>` : ''}
            <p class="cart-item-price">${formatPrice(item.price)}</p>
            <div class="cart-item-actions">
              <div class="cart-item-quantity">
                <button type="button" class="cart-item-qty-btn" data-action="decrease" aria-label="Decrease quantity">-</button>
                <input type="number" class="cart-item-qty-input" value="${item.quantity}" min="1" max="99" aria-label="Quantity">
                <button type="button" class="cart-item-qty-btn" data-action="increase" aria-label="Increase quantity">+</button>
              </div>
              <button type="button" class="cart-item-remove" data-action="remove">Remove</button>
            </div>
          </div>
        </div>
      `).join('');

      // Update totals
      $('#cart-subtotal').textContent = formatPrice(subtotal);
      $('#cart-shipping').textContent = shipping > 0 ? formatPrice(shipping) : 'Free';
      $('#cart-total').textContent = formatPrice(total);

      // Attach events
      this.attachItemEvents();
    },

    attachItemEvents() {
      $$('.cart-item', this.drawer).forEach(item => {
        const itemId = item.dataset.itemId;
        const qtyInput = item.querySelector('.cart-item-qty-input');

        item.querySelector('[data-action="decrease"]')?.addEventListener('click', () => {
          const newQty = Math.max(1, parseInt(qtyInput.value) - 1);
          this.updateQuantity(itemId, newQty);
        });

        item.querySelector('[data-action="increase"]')?.addEventListener('click', () => {
          const newQty = Math.min(99, parseInt(qtyInput.value) + 1);
          this.updateQuantity(itemId, newQty);
        });

        qtyInput?.addEventListener('change', () => {
          this.updateQuantity(itemId, parseInt(qtyInput.value));
        });

        item.querySelector('[data-action="remove"]')?.addEventListener('click', () => {
          this.removeItem(itemId);
        });
      });
    },

    async updateQuantity(itemId, quantity) {
      try {
        const data = await postForm(window.Pricetag.baseUrl + '/cart/update', {
          item_id: itemId,
          quantity: quantity,
        });

        if (data.success) {
          this.render(data.cart);
          this.animateCount();
        }
      } catch (err) {
        Toast.error('Failed to update cart');
      }
    },

    async removeItem(itemId) {
      try {
        const data = await postForm(window.Pricetag.baseUrl + '/cart/remove', {
          item_id: itemId,
        });

        if (data.success) {
          this.render(data.cart);
          this.animateCount();
          Toast.success('Item removed from cart');
        }
      } catch (err) {
        Toast.error('Failed to remove item');
      }
    },

    async addItem(productId, quantity = 1, variantId = null) {
      try {
        const data = await postForm(window.Pricetag.baseUrl + '/cart/add', {
          product_id: productId,
          quantity: quantity,
          variant_id: variantId || '',
        });

        if (data.success) {
          this.render(data.cart);
          this.animateCount();
          Toast.success(`${data.product.name} added to cart`);
          this.open();
        } else {
          Toast.error(data.message || 'Could not add to cart');
        }
      } catch (err) {
        Toast.error('Failed to add to cart');
      }
    },

    animateCount() {
      const countEl = $('#cart-count');
      if (countEl) {
        countEl.classList.remove('count-animate');
        void countEl.offsetWidth; // Trigger reflow
        countEl.classList.add('count-animate');
      }
    },
  };

  // ==========================================================================
  // SEARCH
  // ==========================================================================

  const Search = {
    input: null,
    suggestions: null,
    debounceTimer: null,

    init() {
      this.input = $('#header-search-input');
      this.suggestions = $('#search-suggestions');
      if (!this.input || !this.suggestions) return;

      this.input.addEventListener('input', debounce(() => this.search(), 300));
      this.input.addEventListener('focus', () => {
        if (this.input.value.length >= 2) this.showSuggestions();
      });

      document.addEventListener('click', (e) => {
        if (!this.input.contains(e.target) && !this.suggestions.contains(e.target)) {
          this.hideSuggestions();
        }
      });
    },

    async search() {
      const query = this.input.value.trim();
      if (query.length < 2) {
        this.hideSuggestions();
        return;
      }

      try {
        const data = await fetchJSON(`${window.Pricetag.baseUrl}/search/suggest?q=${encodeURIComponent(query)}`);
        this.renderSuggestions(data.results || []);
      } catch (err) {
        console.error('Search failed', err);
      }
    },

    renderSuggestions(results) {
      if (results.length === 0) {
        this.suggestions.innerHTML = '<div class="search-suggestion-item"><p class="text-muted">No results found</p></div>';
      } else {
        this.suggestions.innerHTML = results.map(item => `
          <a href="${window.Pricetag.baseUrl}/products/${item.slug}" class="search-suggestion-item">
            ${item.image ? `<img src="${window.Pricetag.baseUrl}/storage/uploads/${item.image}" class="search-suggestion-image" alt="">` : ''}
            <div class="search-suggestion-text">
              <span class="search-suggestion-title">${item.name}</span>
              <span class="search-suggestion-category">${item.category || ''}</span>
            </div>
            <span class="font-semibold">${formatPrice(item.price)}</span>
          </a>
        `).join('');
      }
      this.showSuggestions();
    },

    showSuggestions() {
      this.suggestions.classList.add('is-active');
    },

    hideSuggestions() {
      this.suggestions.classList.remove('is-active');
    },
  };

  // ==========================================================================
  // ADD TO CART BUTTONS
  // ==========================================================================

  const AddToCart = {
    init() {
      document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-add-to-cart]');
        if (!btn) return;

        e.preventDefault();
        const productId = btn.dataset.addToCart;
        const quantity = btn.dataset.quantity || 1;
        const variantId = btn.dataset.variantId || null;

        btn.classList.add('is-loading');
        CartDrawer.addItem(productId, quantity, variantId)
          .finally(() => btn.classList.remove('is-loading'));
      });
    },
  };

  // ==========================================================================
  // WISHLIST
  // ==========================================================================

  const Wishlist = {
    init() {
      document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-wishlist-toggle]');
        if (!btn) return;

        e.preventDefault();
        const productId = btn.dataset.wishlistToggle;

        try {
          const data = await postForm(window.Pricetag.baseUrl + '/wishlist/toggle', {
            product_id: productId,
          });

          if (data.success) {
            btn.classList.toggle('is-active', data.in_wishlist);

            // Update wishlist count with animation
            const countEl = $('#wishlist-count');
            if (countEl) {
              countEl.textContent = data.count || '';
              this.animateCount(countEl);
            }

            // Animate the header wishlist button
            const headerBtn = $('a[href$="/wishlist"].header-action-btn');
            if (headerBtn && data.in_wishlist) {
              this.animateWishlistButton(headerBtn);
            }

            Toast.success(data.in_wishlist ? 'Added to wishlist' : 'Removed from wishlist');
          }
        } catch (err) {
          Toast.error('Please login to use wishlist');
        }
      });
    },

    animateCount(el) {
      el.classList.remove('is-animating');
      void el.offsetWidth; // Trigger reflow
      el.classList.add('is-animating');
      setTimeout(() => el.classList.remove('is-animating'), 300);
    },

    animateWishlistButton(btn) {
      btn.classList.remove('wishlist-animating');
      void btn.offsetWidth; // Trigger reflow
      btn.classList.add('wishlist-animating');
      setTimeout(() => btn.classList.remove('wishlist-animating'), 1000);
    },
  };

  // ==========================================================================
  // QUICK VIEW MODAL
  // ==========================================================================

  const QuickView = {
    modal: null,
    currentProduct: null,

    init() {
      // Create modal element if not exists
      this.createModal();

      // Listen for quick view button clicks
      document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-quick-view]');
        if (!btn) return;

        e.preventDefault();
        const productId = btn.dataset.quickView;
        await this.open(productId);
      });
    },

    createModal() {
      // Check if modal already exists
      if ($('#quick-view-modal')) {
        this.modal = $('#quick-view-modal');
        return;
      }

      const modalHtml = `
        <div class="modal quick-view-modal" id="quick-view-modal" aria-hidden="true">
          <div class="modal-backdrop" data-close-modal></div>
          <div class="modal-content">
            <button class="modal-close" data-close-modal aria-label="Close">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
            <div class="modal-body">
              <div class="quick-view-grid">
                <div class="quick-view-gallery">
                  <img class="quick-view-gallery-main" id="quick-view-image" src="" alt="">
                  <div class="quick-view-gallery-thumbs" id="quick-view-thumbs"></div>
                </div>
                <div class="quick-view-details">
                  <span class="quick-view-category" id="quick-view-category"></span>
                  <h2 class="quick-view-title" id="quick-view-title"></h2>
                  <div class="quick-view-rating">
                    <div class="quick-view-stars" id="quick-view-stars"></div>
                    <span class="quick-view-rating-count" id="quick-view-reviews"></span>
                  </div>
                  <div class="quick-view-price">
                    <span class="quick-view-price-current" id="quick-view-price"></span>
                    <span class="quick-view-price-original" id="quick-view-original-price"></span>
                    <span class="quick-view-price-discount" id="quick-view-discount"></span>
                  </div>
                  <p class="quick-view-description" id="quick-view-description"></p>
                  <div class="quick-view-variants" id="quick-view-variants"></div>
                  <div class="quick-view-actions">
                    <div class="quick-view-qty">
                      <button type="button" class="quick-view-qty-btn" id="quick-view-qty-dec">-</button>
                      <input type="number" class="quick-view-qty-input" id="quick-view-qty" value="1" min="1" max="99">
                      <button type="button" class="quick-view-qty-btn" id="quick-view-qty-inc">+</button>
                    </div>
                    <button type="button" class="btn btn-primary quick-view-add-btn" id="quick-view-add">
                      Add to Cart
                    </button>
                  </div>
                  <a href="#" class="quick-view-link" id="quick-view-link">View Full Details</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;

      document.body.insertAdjacentHTML('beforeend', modalHtml);
      this.modal = $('#quick-view-modal');

      // Attach events
      $$('[data-close-modal]', this.modal).forEach(el => {
        el.addEventListener('click', () => this.close());
      });

      $('#quick-view-qty-dec')?.addEventListener('click', () => {
        const input = $('#quick-view-qty');
        input.value = Math.max(1, parseInt(input.value) - 1);
      });

      $('#quick-view-qty-inc')?.addEventListener('click', () => {
        const input = $('#quick-view-qty');
        input.value = Math.min(99, parseInt(input.value) + 1);
      });

      $('#quick-view-add')?.addEventListener('click', () => {
        if (!this.currentProduct) return;
        const qty = parseInt($('#quick-view-qty').value) || 1;
        const variantId = $('#quick-view-variant-id')?.value || null;
        CartDrawer.addItem(this.currentProduct.id, qty, variantId);
        this.close();
      });

      // Close on escape
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && this.modal.classList.contains('is-open')) {
          this.close();
        }
      });
    },

    async open(productId) {
      try {
        const data = await fetchJSON(`${window.Pricetag.baseUrl}/api/products/${productId}`);
        if (data.product) {
          this.render(data.product);
          this.modal.classList.add('is-open');
          this.modal.setAttribute('aria-hidden', 'false');
          document.body.style.overflow = 'hidden';
        }
      } catch (err) {
        Toast.error('Failed to load product details');
      }
    },

    close() {
      this.modal.classList.remove('is-open');
      this.modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      this.currentProduct = null;
    },

    render(product) {
      this.currentProduct = product;

      // Main image
      const mainImg = $('#quick-view-image');
      mainImg.src = product.image ? `${window.Pricetag.baseUrl}/storage/uploads/${product.image}` : '';
      mainImg.alt = product.name;

      // Thumbnails
      const thumbsContainer = $('#quick-view-thumbs');
      if (product.images && product.images.length > 1) {
        thumbsContainer.innerHTML = product.images.map((img, i) => `
          <button class="quick-view-thumb ${i === 0 ? 'is-active' : ''}" data-image="${window.Pricetag.baseUrl}/storage/uploads/${img}">
            <img src="${window.Pricetag.baseUrl}/storage/uploads/${img}" alt="">
          </button>
        `).join('');

        $$('.quick-view-thumb', thumbsContainer).forEach(thumb => {
          thumb.addEventListener('click', () => {
            mainImg.src = thumb.dataset.image;
            $$('.quick-view-thumb', thumbsContainer).forEach(t => t.classList.remove('is-active'));
            thumb.classList.add('is-active');
          });
        });
      } else {
        thumbsContainer.innerHTML = '';
      }

      // Basic info
      $('#quick-view-category').textContent = product.category || '';
      $('#quick-view-title').textContent = product.name;
      $('#quick-view-description').textContent = product.short_description || product.description || '';

      // Price
      $('#quick-view-price').textContent = formatPrice(product.sale_price || product.price);
      const originalPrice = $('#quick-view-original-price');
      const discount = $('#quick-view-discount');

      if (product.sale_price && product.sale_price < product.price) {
        originalPrice.textContent = formatPrice(product.price);
        originalPrice.style.display = 'inline';
        const discountPercent = Math.round((1 - product.sale_price / product.price) * 100);
        discount.textContent = `-${discountPercent}%`;
        discount.style.display = 'inline';
      } else {
        originalPrice.style.display = 'none';
        discount.style.display = 'none';
      }

      // Rating
      const stars = $('#quick-view-stars');
      const rating = product.rating || 0;
      stars.innerHTML = Array(5).fill(0).map((_, i) => `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="${i < Math.round(rating) ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2">
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
        </svg>
      `).join('');
      $('#quick-view-reviews').textContent = product.review_count ? `(${product.review_count} reviews)` : '';

      // Link
      $('#quick-view-link').href = `${window.Pricetag.baseUrl}/products/${product.slug}`;

      // Reset quantity
      $('#quick-view-qty').value = 1;
    },
  };

  // ==========================================================================
  // PRODUCT PAGE
  // ==========================================================================

  const ProductPage = {
    init() {
      const productForm = $('#product-form');
      if (!productForm) return;

      // Image gallery
      this.initGallery();

      // Variant selection
      $$('[data-variant-select]').forEach(select => {
        select.addEventListener('change', () => this.updateVariant());
      });

      // Quantity controls
      const qtyInput = $('#product-quantity');
      if (qtyInput) {
        $('#qty-decrease')?.addEventListener('click', () => {
          qtyInput.value = Math.max(1, parseInt(qtyInput.value) - 1);
        });
        $('#qty-increase')?.addEventListener('click', () => {
          qtyInput.value = Math.min(99, parseInt(qtyInput.value) + 1);
        });
      }
    },

    initGallery() {
      const mainImage = $('#product-main-image');
      const thumbs = $$('.product-thumb');
      if (!mainImage || !thumbs.length) return;

      thumbs.forEach(thumb => {
        thumb.addEventListener('click', () => {
          mainImage.src = thumb.dataset.image;
          thumbs.forEach(t => t.classList.remove('is-active'));
          thumb.classList.add('is-active');
        });
      });
    },

    updateVariant() {
      const selects = $$('[data-variant-select]');
      const selectedValues = [...selects].map(s => s.value);

      // Find matching variant
      const variants = window.productVariants || [];
      const variant = variants.find(v => {
        const variantValues = Object.values(v.attributes);
        return selectedValues.every(val => variantValues.includes(val));
      });

      if (variant) {
        $('#product-price').textContent = formatPrice(variant.price);
        $('#variant-id').value = variant.id;

        if (variant.stock_quantity <= 0) {
          $('#add-to-cart-btn').disabled = true;
          $('#stock-status').textContent = 'Out of Stock';
          $('#stock-status').className = 'badge badge-danger';
        } else {
          $('#add-to-cart-btn').disabled = false;
          $('#stock-status').textContent = 'In Stock';
          $('#stock-status').className = 'badge badge-success';
        }
      }
    },
  };

  // ==========================================================================
  // FORM VALIDATION
  // ==========================================================================

  const FormValidation = {
    init() {
      $$('form[data-validate]').forEach(form => {
        form.addEventListener('submit', (e) => {
          if (!this.validate(form)) {
            e.preventDefault();
          }
        });

        // Real-time validation
        $$('input, select, textarea', form).forEach(input => {
          input.addEventListener('blur', () => this.validateField(input));
        });
      });
    },

    validate(form) {
      let isValid = true;
      $$('[required]', form).forEach(field => {
        if (!this.validateField(field)) {
          isValid = false;
        }
      });
      return isValid;
    },

    validateField(field) {
      const value = field.value.trim();
      let error = null;

      if (field.required && !value) {
        error = 'This field is required';
      } else if (field.type === 'email' && value && !this.isValidEmail(value)) {
        error = 'Please enter a valid email address';
      } else if (field.minLength && value.length < field.minLength) {
        error = `Must be at least ${field.minLength} characters`;
      }

      this.showError(field, error);
      return !error;
    },

    isValidEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    showError(field, message) {
      const container = field.closest('.form-group') || field.parentElement;
      let errorEl = container.querySelector('.form-error');

      if (message) {
        field.classList.add('is-invalid');
        if (!errorEl) {
          errorEl = document.createElement('span');
          errorEl.className = 'form-error';
          container.appendChild(errorEl);
        }
        errorEl.textContent = message;
      } else {
        field.classList.remove('is-invalid');
        errorEl?.remove();
      }
    },
  };

  // ==========================================================================
  // LAZY LOADING
  // ==========================================================================

  const LazyLoad = {
    observer: null,

    init() {
      if (!('IntersectionObserver' in window)) return;

      this.observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            img.classList.add('is-loaded');
            this.observer.unobserve(img);
          }
        });
      }, { rootMargin: '200px' });

      $$('img[data-src]').forEach(img => this.observer.observe(img));
    },
  };

  // ==========================================================================
  // INITIALIZE
  // ==========================================================================

  document.addEventListener('DOMContentLoaded', () => {
    Toast.init();
    Header.init();
    MegaMenu.init();
    MobileMenu.init();
    CartDrawer.init();
    Search.init();
    AddToCart.init();
    Wishlist.init();
    QuickView.init();
    ProductPage.init();
    FormValidation.init();
    LazyLoad.init();
  });

  // Expose for global use
  window.Pricetag = window.Pricetag || {};
  window.Pricetag.Toast = Toast;
  window.Pricetag.Cart = CartDrawer;
  window.Pricetag.QuickView = QuickView;

})();
