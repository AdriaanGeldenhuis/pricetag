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

    init() {
      this.element = $('#site-header');
      if (!this.element) return;

      window.addEventListener('scroll', debounce(() => this.handleScroll(), 10), { passive: true });
      this.handleScroll();
    },

    handleScroll() {
      const shouldBeScrolled = window.scrollY > 50;
      if (shouldBeScrolled !== this.scrolled) {
        this.scrolled = shouldBeScrolled;
        this.element.classList.toggle('is-scrolled', shouldBeScrolled);
      }
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
            $('#wishlist-count').textContent = data.count || '';
            Toast.success(data.in_wishlist ? 'Added to wishlist' : 'Removed from wishlist');
          }
        } catch (err) {
          Toast.error('Please login to use wishlist');
        }
      });
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
    MobileMenu.init();
    CartDrawer.init();
    Search.init();
    AddToCart.init();
    Wishlist.init();
    ProductPage.init();
    FormValidation.init();
    LazyLoad.init();
  });

  // Expose for global use
  window.Pricetag = window.Pricetag || {};
  window.Pricetag.Toast = Toast;
  window.Pricetag.Cart = CartDrawer;

})();
