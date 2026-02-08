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
    form: null,
    selectedIndex: -1,
    results: [],

    init() {
      this.input = $('#header-search-input');
      this.suggestions = $('#search-suggestions');
      this.form = this.input?.closest('form');
      if (!this.input || !this.suggestions) return;

      // Input events
      this.input.addEventListener('input', debounce(() => this.search(), 300));
      this.input.addEventListener('focus', () => {
        if (this.input.value.length >= 2) this.showSuggestions();
      });

      // Keyboard navigation
      this.input.addEventListener('keydown', (e) => this.handleKeydown(e));

      // Close on outside click
      document.addEventListener('click', (e) => {
        if (!this.input.contains(e.target) && !this.suggestions.contains(e.target)) {
          this.hideSuggestions();
        }
      });

      // Close on escape
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') this.hideSuggestions();
      });
    },

    async search() {
      const query = this.input.value.trim();
      if (query.length < 2) {
        this.hideSuggestions();
        return;
      }

      // Add loading state
      this.suggestions.innerHTML = `
        <div class="search-suggestion-loading">
          <svg class="search-spinner" width="20" height="20" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="60" stroke-dashoffset="20"/>
          </svg>
          Searching...
        </div>
      `;
      this.showSuggestions();

      try {
        const data = await fetchJSON(`${window.Pricetag.baseUrl}/search/suggest?q=${encodeURIComponent(query)}`);
        this.renderSuggestions(data, query);
      } catch (err) {
        console.error('Search failed', err);
        this.suggestions.innerHTML = '<div class="search-suggestion-item"><p class="text-muted">Search failed. Please try again.</p></div>';
      }
    },

    renderSuggestions(data, query) {
      const { results = [], categories = [], suggestions = [] } = data;
      this.results = results;
      this.selectedIndex = -1;

      let html = '';

      // Search suggestions (popular terms)
      if (suggestions.length > 0) {
        html += `
          <div class="search-suggestion-section">
            <div class="search-suggestion-section-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
              </svg>
              Popular Searches
            </div>
            ${suggestions.map(term => `
              <a href="${window.Pricetag.baseUrl}/search?q=${encodeURIComponent(term)}" class="search-suggestion-term" data-suggestion>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="11" cy="11" r="8"></circle>
                  <path d="M21 21l-4.35-4.35"></path>
                </svg>
                ${this.highlightMatch(term, query)}
              </a>
            `).join('')}
          </div>
        `;
      }

      // Categories
      if (categories.length > 0) {
        html += `
          <div class="search-suggestion-section">
            <div class="search-suggestion-section-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
              </svg>
              Categories
            </div>
            ${categories.map(cat => `
              <a href="${window.Pricetag.baseUrl}/categories/${cat.slug}" class="search-suggestion-category-item" data-suggestion>
                ${cat.image ? `<img src="${window.Pricetag.baseUrl}/storage/uploads/${cat.image}" class="search-suggestion-cat-image" alt="">` : `
                  <span class="search-suggestion-cat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="3" y="3" width="7" height="7"></rect>
                      <rect x="14" y="3" width="7" height="7"></rect>
                      <rect x="14" y="14" width="7" height="7"></rect>
                      <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                  </span>
                `}
                <span class="search-suggestion-cat-name">${this.highlightMatch(cat.name, query)}</span>
              </a>
            `).join('')}
          </div>
        `;
      }

      // Products
      if (results.length > 0) {
        html += `
          <div class="search-suggestion-section">
            <div class="search-suggestion-section-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
              </svg>
              Products
            </div>
            ${results.map((item, index) => `
              <a href="${window.Pricetag.baseUrl}/products/${item.slug}" class="search-suggestion-item" data-suggestion data-index="${index}">
                ${item.image ? `<img src="${window.Pricetag.baseUrl}/storage/uploads/${item.image}" class="search-suggestion-image" alt="">` : `
                  <span class="search-suggestion-placeholder">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                      <circle cx="8.5" cy="8.5" r="1.5"></circle>
                      <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                  </span>
                `}
                <div class="search-suggestion-text">
                  <span class="search-suggestion-title">${this.highlightMatch(item.name, query)}</span>
                  <span class="search-suggestion-category">${item.category || ''}</span>
                </div>
                <div class="search-suggestion-price">
                  <span class="search-suggestion-price-current">${formatPrice(item.price)}</span>
                  ${item.compare_price && item.compare_price > item.price ? `
                    <span class="search-suggestion-price-original">${formatPrice(item.compare_price)}</span>
                  ` : ''}
                </div>
              </a>
            `).join('')}
          </div>
        `;
      }

      // No results
      if (results.length === 0 && categories.length === 0 && suggestions.length === 0) {
        html = `
          <div class="search-no-suggestions">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="11" cy="11" r="8"></circle>
              <path d="M21 21l-4.35-4.35"></path>
            </svg>
            <p>No results found for "<strong>${this.escapeHtml(query)}</strong>"</p>
            <span>Try different keywords or browse categories</span>
          </div>
        `;
      }

      // View all results link
      if (results.length > 0 || categories.length > 0) {
        html += `
          <a href="${window.Pricetag.baseUrl}/search?q=${encodeURIComponent(query)}" class="search-view-all">
            View all results for "${this.escapeHtml(query)}"
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </a>
        `;
      }

      this.suggestions.innerHTML = html;
      this.showSuggestions();
    },

    highlightMatch(text, query) {
      if (!query) return this.escapeHtml(text);
      const regex = new RegExp(`(${this.escapeRegex(query)})`, 'gi');
      return this.escapeHtml(text).replace(regex, '<mark>$1</mark>');
    },

    escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str;
      return div.innerHTML;
    },

    escapeRegex(str) {
      return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    },

    handleKeydown(e) {
      const items = $$('[data-suggestion]', this.suggestions);
      if (!items.length) return;

      switch (e.key) {
        case 'ArrowDown':
          e.preventDefault();
          this.selectedIndex = Math.min(this.selectedIndex + 1, items.length - 1);
          this.updateSelection(items);
          break;

        case 'ArrowUp':
          e.preventDefault();
          this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
          this.updateSelection(items);
          break;

        case 'Enter':
          if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
            e.preventDefault();
            items[this.selectedIndex].click();
          }
          break;
      }
    },

    updateSelection(items) {
      items.forEach((item, index) => {
        item.classList.toggle('is-selected', index === this.selectedIndex);
      });

      // Scroll into view
      if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
        items[this.selectedIndex].scrollIntoView({ block: 'nearest' });
      }
    },

    showSuggestions() {
      this.suggestions.classList.add('is-active');
    },

    hideSuggestions() {
      this.suggestions.classList.remove('is-active');
      this.selectedIndex = -1;
    },
  };

  // ==========================================================================
  // PRODUCT CARD MENU (3-DOT)
  // ==========================================================================

  const ProductMenu = {
    activeMenu: null,

    init() {
      // Toggle menu on 3-dot button click
      document.addEventListener('click', (e) => {
        const toggleBtn = e.target.closest('[data-product-menu-toggle]');
        if (toggleBtn) {
          e.preventDefault();
          e.stopPropagation();
          this.toggle(toggleBtn);
          return;
        }

        const closeBtn = e.target.closest('[data-product-menu-close]');
        if (closeBtn) {
          e.preventDefault();
          e.stopPropagation();
          this.closeAll();
          return;
        }

        // Clicking a dropdown item (not compare checkbox) closes menu
        const dropdownItem = e.target.closest('.product-dropdown-item:not(.product-dropdown-compare)');
        if (dropdownItem && dropdownItem.closest('.product-card-dropdown')) {
          // Let the item's own handler fire, then close
          setTimeout(() => this.closeAll(), 50);
          return;
        }

        // Click outside closes any open menu
        if (!e.target.closest('.product-card-menu-wrapper')) {
          this.closeAll();
        }
      });

      // Close on escape
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') this.closeAll();
      });
    },

    toggle(btn) {
      const wrapper = btn.closest('.product-card-menu-wrapper');
      const dropdown = wrapper.querySelector('[data-product-dropdown]');
      if (!dropdown) return;

      const isOpen = dropdown.classList.contains('is-open');

      // Close any other open menus first
      this.closeAll();

      if (!isOpen) {
        dropdown.classList.add('is-open');
        btn.classList.add('is-active');
        btn.setAttribute('aria-expanded', 'true');
        this.activeMenu = { dropdown, btn };
      }
    },

    closeAll() {
      if (this.activeMenu) {
        this.activeMenu.dropdown.classList.remove('is-open');
        this.activeMenu.btn.classList.remove('is-active');
        this.activeMenu.btn.setAttribute('aria-expanded', 'false');
        this.activeMenu = null;
      }
      // Also close any stale open menus
      $$('.product-card-dropdown.is-open').forEach(d => d.classList.remove('is-open'));
      $$('.product-card-menu-btn.is-active').forEach(b => {
        b.classList.remove('is-active');
        b.setAttribute('aria-expanded', 'false');
      });
    },
  };

  // ==========================================================================
  // SIMILAR PRODUCTS MODAL
  // ==========================================================================

  const SimilarProducts = {
    modal: null,

    init() {
      this.createModal();

      document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-similar-products]');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();
        const productId = btn.dataset.similarProducts;
        await this.open(productId);
      });
    },

    createModal() {
      if ($('#similar-products-modal')) {
        this.modal = $('#similar-products-modal');
        return;
      }

      const html = `
        <div class="modal similar-products-modal" id="similar-products-modal" aria-hidden="true">
          <div class="modal-backdrop" data-close-similar></div>
          <div class="modal-content modal-content-lg">
            <div class="modal-header">
              <h3 class="modal-title">Similar Products</h3>
              <button class="modal-close" data-close-similar aria-label="Close">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>
            <div class="modal-body" id="similar-products-body">
              <div class="modal-loading">
                <svg class="search-spinner" width="24" height="24" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="60" stroke-dashoffset="20"/>
                </svg>
                Loading similar products...
              </div>
            </div>
          </div>
        </div>
      `;
      document.body.insertAdjacentHTML('beforeend', html);
      this.modal = $('#similar-products-modal');

      this.modal.addEventListener('click', (e) => {
        if (e.target.closest('[data-close-similar]')) this.close();
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && this.modal.classList.contains('is-open')) this.close();
      });
    },

    async open(productId) {
      this.modal.classList.add('is-open');
      this.modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';

      const body = $('#similar-products-body');
      body.innerHTML = `
        <div class="modal-loading">
          <svg class="search-spinner" width="24" height="24" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="60" stroke-dashoffset="20"/>
          </svg>
          Loading similar products...
        </div>
      `;

      try {
        const data = await fetchJSON(`${window.Pricetag.baseUrl}/api/products/${productId}/similar`);

        if (data.success && data.products.length > 0) {
          body.innerHTML = `
            <div class="similar-products-grid">
              ${data.products.map(p => `
                <a href="${window.Pricetag.baseUrl}/products/${p.slug}" class="similar-product-card">
                  <div class="similar-product-image">
                    ${p.image
                      ? `<img src="${window.Pricetag.baseUrl}/storage/uploads/${p.image}" alt="${this.esc(p.name)}" loading="lazy">`
                      : `<div class="similar-product-placeholder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg></div>`
                    }
                  </div>
                  <div class="similar-product-info">
                    ${p.category ? `<span class="similar-product-category">${this.esc(p.category)}</span>` : ''}
                    <h4 class="similar-product-name">${this.esc(p.name)}</h4>
                    <div class="similar-product-price">
                      <span class="similar-price-current">${formatPrice(p.price)}</span>
                      ${p.compare_price && p.compare_price > p.price
                        ? `<span class="similar-price-original">${formatPrice(p.compare_price)}</span>`
                        : ''
                      }
                    </div>
                  </div>
                </a>
              `).join('')}
            </div>
          `;
        } else {
          body.innerHTML = `
            <div class="modal-empty">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
              </svg>
              <p>No similar products found</p>
            </div>
          `;
        }
      } catch (err) {
        body.innerHTML = `<div class="modal-empty"><p>Failed to load similar products. Please try again.</p></div>`;
      }
    },

    close() {
      if (document.activeElement && this.modal.contains(document.activeElement)) {
        document.activeElement.blur();
      }
      this.modal.classList.remove('is-open');
      this.modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    },

    esc(str) {
      const div = document.createElement('div');
      div.textContent = str;
      return div.innerHTML;
    },
  };

  // ==========================================================================
  // ASK A QUESTION MODAL
  // ==========================================================================

  const AskQuestion = {
    modal: null,
    currentProductId: null,

    init() {
      this.createModal();

      document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-ask-question]');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();
        const productId = btn.dataset.askQuestion;

        // Get product name from the card
        const card = btn.closest('.product-card');
        const nameEl = card?.querySelector('.product-card-title a');
        const productName = nameEl ? nameEl.textContent.trim() : 'this product';

        this.open(productId, productName);
      });
    },

    createModal() {
      if ($('#ask-question-modal')) {
        this.modal = $('#ask-question-modal');
        return;
      }

      const html = `
        <div class="modal ask-question-modal" id="ask-question-modal" aria-hidden="true">
          <div class="modal-backdrop" data-close-question></div>
          <div class="modal-content modal-content-sm">
            <div class="modal-header">
              <h3 class="modal-title">Ask a Question</h3>
              <button class="modal-close" data-close-question aria-label="Close">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>
            <div class="modal-body">
              <p class="ask-question-about" id="ask-question-about"></p>
              <form id="ask-question-form" class="ask-question-form">
                <div class="form-group">
                  <label for="aq-name">Your Name</label>
                  <input type="text" id="aq-name" name="name" required minlength="2" placeholder="John Doe" class="form-input">
                </div>
                <div class="form-group">
                  <label for="aq-email">Email Address</label>
                  <input type="email" id="aq-email" name="email" required placeholder="john@example.com" class="form-input">
                </div>
                <div class="form-group">
                  <label for="aq-question">Your Question</label>
                  <textarea id="aq-question" name="question" required minlength="10" rows="4" placeholder="What would you like to know about this product?" class="form-input"></textarea>
                </div>
                <div class="form-actions">
                  <button type="button" class="btn btn-ghost" data-close-question>Cancel</button>
                  <button type="submit" class="btn btn-primary" id="aq-submit">
                    <span class="aq-submit-text">Submit Question</span>
                    <svg class="aq-submit-spinner" width="18" height="18" viewBox="0 0 24 24" style="display:none">
                      <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="60" stroke-dashoffset="20"/>
                    </svg>
                  </button>
                </div>
              </form>
              <div id="ask-question-success" class="ask-question-success" style="display:none">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                  <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <h4>Question Submitted!</h4>
                <p>We'll get back to you via email as soon as possible.</p>
                <button type="button" class="btn btn-primary" data-close-question>Close</button>
              </div>
            </div>
          </div>
        </div>
      `;
      document.body.insertAdjacentHTML('beforeend', html);
      this.modal = $('#ask-question-modal');

      this.modal.addEventListener('click', (e) => {
        if (e.target.closest('[data-close-question]')) this.close();
      });

      $('#ask-question-form').addEventListener('submit', (e) => {
        e.preventDefault();
        this.submit();
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && this.modal.classList.contains('is-open')) this.close();
      });
    },

    open(productId, productName) {
      this.currentProductId = productId;
      $('#ask-question-about').textContent = `About: ${productName}`;
      $('#ask-question-form').style.display = '';
      $('#ask-question-success').style.display = 'none';
      $('#ask-question-form').reset();
      this.modal.classList.add('is-open');
      this.modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      setTimeout(() => $('#aq-name').focus(), 200);
    },

    close() {
      if (document.activeElement && this.modal.contains(document.activeElement)) {
        document.activeElement.blur();
      }
      this.modal.classList.remove('is-open');
      this.modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      this.currentProductId = null;
    },

    async submit() {
      const submitBtn = $('#aq-submit');
      const spinner = submitBtn.querySelector('.aq-submit-spinner');
      const text = submitBtn.querySelector('.aq-submit-text');

      submitBtn.disabled = true;
      text.style.display = 'none';
      spinner.style.display = '';

      try {
        const data = await postForm(
          `${window.Pricetag.baseUrl}/api/products/${this.currentProductId}/question`,
          {
            name: $('#aq-name').value.trim(),
            email: $('#aq-email').value.trim(),
            question: $('#aq-question').value.trim(),
          }
        );

        if (data.success) {
          $('#ask-question-form').style.display = 'none';
          $('#ask-question-success').style.display = '';
        } else {
          Toast.error(data.message || 'Failed to submit question');
        }
      } catch (err) {
        Toast.error('Failed to submit question. Please try again.');
      } finally {
        submitBtn.disabled = false;
        text.style.display = '';
        spinner.style.display = 'none';
      }
    },
  };

  // ==========================================================================
  // COMPARE LIST
  // ==========================================================================

  const CompareList = {
    items: [],
    bar: null,
    MAX_ITEMS: 4,

    init() {
      this.items = JSON.parse(localStorage.getItem('pricetag_compare') || '[]');
      this.createBar();
      this.syncCheckboxes();
      if (this.items.length > 0) this.showBar();

      // Listen for compare checkbox changes
      document.addEventListener('change', (e) => {
        const checkbox = e.target.closest('[data-compare]');
        if (!checkbox) return;

        e.stopPropagation();
        const productId = checkbox.dataset.compare;
        const card = checkbox.closest('.product-card');

        if (checkbox.checked) {
          this.add(productId, card);
        } else {
          this.remove(productId);
        }
      });
    },

    createBar() {
      if ($('#compare-bar')) {
        this.bar = $('#compare-bar');
        return;
      }

      const html = `
        <div class="compare-bar" id="compare-bar" style="display:none">
          <div class="compare-bar-inner">
            <div class="compare-bar-items" id="compare-bar-items"></div>
            <div class="compare-bar-actions">
              <span class="compare-bar-count" id="compare-bar-count">0 items</span>
              <button type="button" class="btn btn-primary btn-sm" id="compare-bar-view" disabled>Compare Now</button>
              <button type="button" class="btn btn-ghost btn-sm" id="compare-bar-clear">Clear All</button>
            </div>
          </div>
        </div>
      `;
      document.body.insertAdjacentHTML('beforeend', html);
      this.bar = $('#compare-bar');

      $('#compare-bar-clear').addEventListener('click', () => this.clearAll());
      $('#compare-bar-view').addEventListener('click', () => this.viewComparison());
    },

    add(productId, card) {
      if (this.items.length >= this.MAX_ITEMS) {
        Toast.warning(`You can compare up to ${this.MAX_ITEMS} products at a time`);
        // Uncheck the checkbox
        const cb = card?.querySelector(`[data-compare="${productId}"]`);
        if (cb) cb.checked = false;
        return;
      }

      if (this.items.find(i => i.id === productId)) return;

      // Get product info from the card
      const nameEl = card?.querySelector('.product-card-title a');
      const imgEl = card?.querySelector('.product-card-img');

      this.items.push({
        id: productId,
        name: nameEl?.textContent?.trim() || 'Product',
        image: imgEl?.src || '',
        url: nameEl?.href || '#',
      });

      this.save();
      this.render();
      this.showBar();
      Toast.success('Added to compare list');
    },

    remove(productId) {
      this.items = this.items.filter(i => i.id !== productId);
      this.save();
      this.render();
      this.syncCheckboxes();

      if (this.items.length === 0) this.hideBar();
    },

    clearAll() {
      this.items = [];
      this.save();
      this.render();
      this.syncCheckboxes();
      this.hideBar();
    },

    save() {
      localStorage.setItem('pricetag_compare', JSON.stringify(this.items));
    },

    syncCheckboxes() {
      $$('[data-compare]').forEach(cb => {
        cb.checked = this.items.some(i => i.id === cb.dataset.compare);
      });
    },

    render() {
      const container = $('#compare-bar-items');
      const count = $('#compare-bar-count');
      const viewBtn = $('#compare-bar-view');

      if (!container) return;

      container.innerHTML = this.items.map(item => `
        <div class="compare-bar-item" data-compare-item="${item.id}">
          ${item.image
            ? `<img src="${item.image}" alt="${item.name}">`
            : `<div class="compare-bar-placeholder"></div>`
          }
          <button type="button" class="compare-bar-item-remove" data-remove-compare="${item.id}" aria-label="Remove">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
      `).join('');

      // Add empty slots
      for (let i = this.items.length; i < this.MAX_ITEMS; i++) {
        container.innerHTML += `<div class="compare-bar-item compare-bar-item-empty"><span>+</span></div>`;
      }

      count.textContent = `${this.items.length} of ${this.MAX_ITEMS}`;
      viewBtn.disabled = this.items.length < 2;

      // Attach remove events
      $$('[data-remove-compare]', container).forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          this.remove(btn.dataset.removeCompare);
        });
      });
    },

    showBar() {
      this.bar.style.display = '';
      this.render();
    },

    hideBar() {
      this.bar.style.display = 'none';
    },

    viewComparison() {
      if (this.items.length < 2) return;
      const ids = this.items.map(i => i.id).join(',');
      window.location.href = `${window.Pricetag.baseUrl}/products?compare=${ids}`;
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
          const response = await fetch(window.Pricetag.baseUrl + '/wishlist/toggle', {
            method: 'POST',
            body: (() => {
              const fd = new FormData();
              fd.append('_token', window.Pricetag.csrfToken);
              fd.append('product_id', productId);
              return fd;
            })(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
          });

          if (response.status === 401) {
            Toast.warning('Please log in to add items to your wishlist');
            return;
          }

          const data = await response.json();

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
          } else {
            Toast.error(data.message || 'Could not update wishlist');
          }
        } catch (err) {
          Toast.error('Something went wrong. Please try again.');
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
        if (data.success && data.product) {
          this.render(data.product);
          this.modal.classList.add('is-open');
          this.modal.setAttribute('aria-hidden', 'false');
          document.body.style.overflow = 'hidden';
        } else {
          Toast.error(data.message || 'Product not available');
        }
      } catch (err) {
        Toast.error('Failed to load product details');
      }
    },

    close() {
      if (document.activeElement && this.modal.contains(document.activeElement)) {
        document.activeElement.blur();
      }
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

      // Quantity controls handled in product page inline script
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
    ProductMenu.init();
    SimilarProducts.init();
    AskQuestion.init();
    CompareList.init();
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
