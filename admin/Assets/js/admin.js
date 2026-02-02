/**
 * Admin Panel JavaScript
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

'use strict';

/**
 * Admin Application
 */
const AdminApp = {
    /**
     * Initialize admin application
     */
    init() {
        this.initSidebar();
        this.initTables();
        this.initForms();
        this.initConfirmations();
        this.initToasts();
    },

    /**
     * Initialize sidebar functionality
     */
    initSidebar() {
        const sidebar = document.querySelector('.admin-sidebar');
        const toggle = document.querySelector('[data-toggle-sidebar]');

        if (toggle && sidebar) {
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                document.body.classList.toggle('sidebar-collapsed');
            });
        }

        // Mark current page as active
        const currentPath = window.location.pathname;
        document.querySelectorAll('.admin-nav-link').forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });
    },

    /**
     * Initialize table functionality
     */
    initTables() {
        // Select all functionality
        document.querySelectorAll('[data-select-all]').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const table = e.target.closest('table');
                const checkboxes = table.querySelectorAll('tbody input[type="checkbox"]');
                checkboxes.forEach(cb => cb.checked = e.target.checked);
                this.updateBulkActions();
            });
        });

        // Individual checkbox
        document.querySelectorAll('.admin-table tbody input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateBulkActions());
        });

        // Sortable columns
        document.querySelectorAll('[data-sort]').forEach(th => {
            th.style.cursor = 'pointer';
            th.addEventListener('click', () => {
                const column = th.dataset.sort;
                const currentOrder = th.dataset.order || 'asc';
                const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';

                const url = new URL(window.location);
                url.searchParams.set('sort', column);
                url.searchParams.set('order', newOrder);
                window.location = url;
            });
        });
    },

    /**
     * Update bulk actions visibility
     */
    updateBulkActions() {
        const checked = document.querySelectorAll('.admin-table tbody input[type="checkbox"]:checked');
        const bulkActions = document.querySelector('.bulk-actions');

        if (bulkActions) {
            bulkActions.style.display = checked.length > 0 ? 'flex' : 'none';
            const count = bulkActions.querySelector('.selected-count');
            if (count) count.textContent = checked.length;
        }
    },

    /**
     * Initialize form functionality
     */
    initForms() {
        // Auto-generate slug from name
        document.querySelectorAll('[data-slug-source]').forEach(input => {
            input.addEventListener('input', (e) => {
                const target = document.querySelector(`[name="${input.dataset.slugSource}"]`);
                if (target && !target.dataset.manualEdit) {
                    target.value = this.slugify(e.target.value);
                }
            });
        });

        // Mark slug as manually edited
        document.querySelectorAll('[name="slug"]').forEach(input => {
            input.addEventListener('input', () => {
                input.dataset.manualEdit = 'true';
            });
        });

        // Image preview
        document.querySelectorAll('[data-image-preview]').forEach(input => {
            input.addEventListener('change', (e) => {
                const preview = document.querySelector(input.dataset.imagePreview);
                if (preview && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        preview.src = ev.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        });

        // Form validation
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    },

    /**
     * Initialize confirmation dialogs
     */
    initConfirmations() {
        document.querySelectorAll('[data-confirm]').forEach(element => {
            element.addEventListener('click', (e) => {
                const message = element.dataset.confirm || 'Are you sure?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    },

    /**
     * Initialize toast notifications
     */
    initToasts() {
        // Auto-hide toasts after 5 seconds
        document.querySelectorAll('.toast').forEach(toast => {
            setTimeout(() => {
                toast.classList.add('fade-out');
                setTimeout(() => toast.remove(), 300);
            }, 5000);

            // Close button
            const closeBtn = toast.querySelector('.toast-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    toast.classList.add('fade-out');
                    setTimeout(() => toast.remove(), 300);
                });
            }
        });
    },

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        const container = document.querySelector('.toast-container') ||
            this.createToastContainer();

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span class="toast-message">${message}</span>
            <button type="button" class="toast-close">&times;</button>
        `;

        container.appendChild(toast);

        // Initialize toast
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 300);
        }, 5000);

        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 300);
        });
    },

    /**
     * Create toast container
     */
    createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    },

    /**
     * Generate slug from string
     */
    slugify(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    },

    /**
     * Format currency
     */
    formatCurrency(amount) {
        return 'R ' + Number(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$& ');
    },

    /**
     * AJAX request helper
     */
    async ajax(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) {
            defaults.headers['X-CSRF-TOKEN'] = csrfToken;
        }

        const config = { ...defaults, ...options };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Request failed');
            }

            return data;
        } catch (error) {
            this.showToast(error.message, 'error');
            throw error;
        }
    },

    /**
     * Delete item with confirmation
     */
    async deleteItem(url, itemName = 'item') {
        if (!confirm(`Are you sure you want to delete this ${itemName}? This action cannot be undone.`)) {
            return false;
        }

        try {
            await this.ajax(url, { method: 'DELETE' });
            this.showToast(`${itemName} deleted successfully`, 'success');
            return true;
        } catch (error) {
            return false;
        }
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => AdminApp.init());

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminApp;
}
