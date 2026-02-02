<style>
/* Account Page Styles */
.account-page {
    background: var(--color-background-alt);
    min-height: 100vh;
}

.account-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 1024px) {
    .account-layout {
        grid-template-columns: 260px 1fr;
    }
}

/* Account Sidebar */
.account-sidebar {
    background: var(--color-background);
    border-radius: var(--radius-lg);
    padding: 1rem;
    height: fit-content;
    position: sticky;
    top: 6rem;
}

@media (max-width: 1023px) {
    .account-sidebar {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 100;
        padding: 1.5rem;
        border-radius: 0;
        overflow-y: auto;
    }

    .account-sidebar.open {
        display: block;
    }
}

.account-nav-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.account-nav-item {
    margin-bottom: 0.25rem;
}

.account-nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: var(--radius-md);
    color: var(--color-text);
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
    background: none;
    border: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.account-nav-link:hover {
    background: var(--color-background-alt);
    color: var(--color-primary);
}

.account-nav-link.active {
    background: var(--color-primary-50);
    color: var(--color-primary);
}

.account-nav-link svg {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
}

.account-nav-divider {
    height: 1px;
    background: var(--color-border);
    margin: 1rem 0;
}

.account-nav-logout {
    color: var(--color-danger);
}

.account-nav-logout:hover {
    background: var(--color-danger-50);
    color: var(--color-danger);
}

.logout-form {
    margin: 0;
}

.account-mobile-menu-toggle {
    display: none;
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    width: 3.5rem;
    height: 3.5rem;
    background: var(--color-primary);
    color: white;
    border: none;
    border-radius: 50%;
    box-shadow: var(--shadow-lg);
    cursor: pointer;
    z-index: 99;
    align-items: center;
    justify-content: center;
}

@media (max-width: 1023px) {
    .account-mobile-menu-toggle {
        display: flex;
    }
}

.account-mobile-menu-toggle svg {
    width: 1.5rem;
    height: 1.5rem;
}

/* Account Main */
.account-main {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Welcome Header */
.account-welcome {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-700));
    border-radius: var(--radius-lg);
    color: white;
}

.account-welcome-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.25rem;
}

.account-welcome-text {
    margin: 0;
    opacity: 0.9;
}

.account-welcome-avatar {
    flex-shrink: 0;
}

.avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    font-weight: 600;
}

.avatar-lg {
    width: 3.5rem;
    height: 3.5rem;
    font-size: 1.25rem;
}

/* Stats Cards */
.account-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
}

.account-stat-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    background: var(--color-background);
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-border);
    transition: all 0.2s;
}

.account-stat-card:hover {
    border-color: var(--color-primary);
    box-shadow: var(--shadow-md);
}

.account-stat-icon {
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-primary-50);
    border-radius: var(--radius-md);
    color: var(--color-primary);
}

.account-stat-icon svg {
    width: 1.5rem;
    height: 1.5rem;
}

.account-stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
}

.account-stat-label {
    display: block;
    font-size: 0.75rem;
    color: var(--color-text-muted);
    margin-top: 0.25rem;
}

/* Section Title */
.account-section-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 1rem;
}

/* Quick Actions */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
}

.quick-action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    padding: 1.5rem 1rem;
    background: var(--color-background);
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-border);
    text-align: center;
    transition: all 0.2s;
}

.quick-action-card:hover {
    border-color: var(--color-primary);
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.quick-action-icon {
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-neutral-100);
    border-radius: 50%;
    color: var(--color-text-muted);
    transition: all 0.2s;
}

.quick-action-card:hover .quick-action-icon {
    background: var(--color-primary);
    color: white;
}

.quick-action-icon svg {
    width: 1.25rem;
    height: 1.25rem;
}

.quick-action-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--color-text);
}

/* Orders Table */
.orders-table-wrapper {
    overflow-x: auto;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th,
.orders-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--color-border);
}

.orders-table th {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--color-text-muted);
    background: var(--color-background-alt);
}

.orders-table tbody tr:hover {
    background: var(--color-background-alt);
}

.order-number {
    font-weight: 600;
    color: var(--color-primary);
}

.order-status {
    display: inline-flex;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
}

.order-status.status-pending {
    background: var(--color-warning-100);
    color: var(--color-warning-700);
}

.order-status.status-processing {
    background: var(--color-primary-100);
    color: var(--color-primary-700);
}

.order-status.status-shipped {
    background: var(--color-info-100);
    color: var(--color-info-700);
}

.order-status.status-delivered,
.order-status.status-completed {
    background: var(--color-success-100);
    color: var(--color-success-700);
}

.order-status.status-cancelled,
.order-status.status-refunded {
    background: var(--color-danger-100);
    color: var(--color-danger-700);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
}

.empty-state-icon {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-neutral-100);
    border-radius: 50%;
    color: var(--color-text-muted);
}

.empty-state-icon svg {
    width: 2rem;
    height: 2rem;
}

.empty-state h3 {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
}

.empty-state p {
    margin: 0 0 1.5rem;
}

/* Page Header */
.account-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.account-page-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
}

/* Breadcrumb */
.account-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--color-text-muted);
    margin-bottom: 0.5rem;
}

.account-breadcrumb a {
    color: var(--color-text-muted);
}

.account-breadcrumb a:hover {
    color: var(--color-primary);
}

.account-breadcrumb svg {
    width: 1rem;
    height: 1rem;
}

/* Addresses */
.addresses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}

.address-card {
    position: relative;
    padding: 1.25rem;
    background: var(--color-background);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all 0.2s;
}

.address-card:hover {
    border-color: var(--color-primary-300);
}

.address-card.is-default {
    border-color: var(--color-primary);
}

.address-card-badge {
    position: absolute;
    top: -0.5rem;
    right: 1rem;
    padding: 0.25rem 0.5rem;
    background: var(--color-primary);
    color: white;
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    border-radius: var(--radius-sm);
}

.address-card-type {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--color-text-muted);
    margin-bottom: 0.5rem;
}

.address-card-name {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.address-card-line {
    font-size: 0.875rem;
    color: var(--color-text-muted);
    margin: 0;
}

.address-card-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--color-border);
}

.address-card-add {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 180px;
    border: 2px dashed var(--color-border);
    border-radius: var(--radius-lg);
    color: var(--color-text-muted);
    cursor: pointer;
    transition: all 0.2s;
}

.address-card-add:hover {
    border-color: var(--color-primary);
    color: var(--color-primary);
}

.address-card-add svg {
    width: 2rem;
    height: 2rem;
    margin-bottom: 0.5rem;
}

/* Settings Form */
.settings-section {
    margin-bottom: 2rem;
}

.settings-section:last-child {
    margin-bottom: 0;
}

.settings-section-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--color-border);
}

/* Security */
.security-warning {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    background: var(--color-warning-50);
    border: 1px solid var(--color-warning-200);
    border-radius: var(--radius-md);
    margin-bottom: 1.5rem;
}

.security-warning svg {
    width: 1.25rem;
    height: 1.25rem;
    color: var(--color-warning);
    flex-shrink: 0;
}

.security-warning-content {
    flex: 1;
}

.security-warning-title {
    font-weight: 600;
    margin: 0 0 0.25rem;
}

.security-warning-text {
    font-size: 0.875rem;
    color: var(--color-text-muted);
    margin: 0;
}

.sessions-list {
    display: grid;
    gap: 0.75rem;
}

.session-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    background: var(--color-background-alt);
    border-radius: var(--radius-md);
}

.session-item.current {
    background: var(--color-primary-50);
    border: 1px solid var(--color-primary-200);
}

.session-icon {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-background);
    border-radius: var(--radius-md);
    color: var(--color-text-muted);
}

.session-icon svg {
    width: 1.25rem;
    height: 1.25rem;
}

.session-content {
    flex: 1;
}

.session-title {
    font-weight: 600;
    margin: 0 0 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.session-current-badge {
    font-size: 0.625rem;
    padding: 0.125rem 0.375rem;
    background: var(--color-primary);
    color: white;
    border-radius: var(--radius-sm);
    text-transform: uppercase;
    font-weight: 700;
}

.session-details {
    font-size: 0.75rem;
    color: var(--color-text-muted);
    margin: 0;
}

/* Order Detail */
.order-detail-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.order-detail-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.order-detail-meta-item {
    display: flex;
    flex-direction: column;
}

.order-detail-meta-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--color-text-muted);
}

.order-detail-meta-value {
    font-weight: 600;
}

.order-timeline {
    position: relative;
    padding-left: 2rem;
}

.order-timeline::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--color-border);
}

.order-timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.order-timeline-item:last-child {
    padding-bottom: 0;
}

.order-timeline-dot {
    position: absolute;
    left: -1.625rem;
    width: 0.75rem;
    height: 0.75rem;
    background: var(--color-border);
    border-radius: 50%;
}

.order-timeline-item.active .order-timeline-dot {
    background: var(--color-primary);
}

.order-timeline-item.completed .order-timeline-dot {
    background: var(--color-success);
}

.order-timeline-date {
    font-size: 0.75rem;
    color: var(--color-text-muted);
}

.order-timeline-title {
    font-weight: 600;
    margin: 0.25rem 0;
}

.order-timeline-note {
    font-size: 0.875rem;
    color: var(--color-text-muted);
    margin: 0;
}

/* Pagination */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2rem;
}

.pagination-item {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    height: 2.5rem;
    padding: 0 0.75rem;
    background: var(--color-background);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--color-text);
    transition: all 0.2s;
}

.pagination-item:hover:not(.active):not(.disabled) {
    border-color: var(--color-primary);
    color: var(--color-primary);
}

.pagination-item.active {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: white;
}

.pagination-item.disabled {
    opacity: 0.5;
    pointer-events: none;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
}

.modal-overlay.open {
    opacity: 1;
    visibility: visible;
}

.modal {
    background: var(--color-background);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xl);
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.95);
    transition: transform 0.3s;
}

.modal-overlay.open .modal {
    transform: scale(1);
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--color-border);
}

.modal-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    padding: 0.5rem;
    cursor: pointer;
    color: var(--color-text-muted);
    border-radius: var(--radius-md);
    transition: all 0.2s;
}

.modal-close:hover {
    background: var(--color-background-alt);
    color: var(--color-text);
}

.modal-close svg {
    width: 1.25rem;
    height: 1.25rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--color-border);
}

/* Mobile Menu JS */
@media (max-width: 1023px) {
    .account-sidebar-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: none;
        border: none;
        padding: 0.5rem;
        cursor: pointer;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.querySelector('.account-mobile-menu-toggle');
    const sidebar = document.querySelector('.account-sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Close on click outside
        sidebar.addEventListener('click', (e) => {
            if (e.target === sidebar) {
                sidebar.classList.remove('open');
            }
        });
    }
});
</script>
