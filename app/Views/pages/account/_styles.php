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
        grid-template-columns: 280px 1fr;
    }
}

/* Account Sidebar */
.account-sidebar {
    background: var(--color-background);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    height: fit-content;
    position: sticky;
    top: 6rem;
    box-shadow: var(--shadow-sm);
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
        background: var(--color-background);
    }

    .account-sidebar.open {
        display: block;
    }
}

.account-sidebar-close {
    display: none;
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: none;
    border: none;
    padding: 0.5rem;
    cursor: pointer;
    color: var(--color-text-muted);
    border-radius: var(--radius-md);
    transition: all 0.2s;
}

.account-sidebar-close:hover {
    background: var(--color-background-alt);
    color: var(--color-text);
}

.account-sidebar-close svg {
    width: 1.5rem;
    height: 1.5rem;
}

@media (max-width: 1023px) {
    .account-sidebar-close {
        display: flex;
    }
}

/* Sidebar User Info (Mobile) */
.account-sidebar-user {
    display: none;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    margin: -1.5rem -1.5rem 1.5rem;
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-700));
    color: white;
}

@media (max-width: 1023px) {
    .account-sidebar-user {
        display: flex;
    }
}

.sidebar-user-avatar {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
    flex-shrink: 0;
}

.sidebar-user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.sidebar-user-info {
    flex: 1;
    min-width: 0;
}

.sidebar-user-name {
    display: block;
    font-weight: 600;
    font-size: 1rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-user-email {
    display: block;
    font-size: 0.75rem;
    opacity: 0.9;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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
    text-decoration: none;
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

.nav-badge {
    margin-left: auto;
    padding: 0.125rem 0.5rem;
    background: var(--color-primary);
    color: white;
    font-size: 0.6875rem;
    font-weight: 700;
    border-radius: 9999px;
}

.nav-indicator {
    margin-left: auto;
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    background: var(--color-neutral-300);
}

.nav-indicator.warning {
    background: var(--color-warning);
}

.nav-indicator.success {
    background: var(--color-success);
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
    transition: transform 0.2s, box-shadow 0.2s;
}

.account-mobile-menu-toggle:hover {
    transform: scale(1.05);
    box-shadow: var(--shadow-xl);
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

@media (max-width: 640px) {
    .account-welcome-title {
        font-size: 1.25rem;
    }
}

.account-welcome-text {
    margin: 0;
    opacity: 0.9;
}

.account-welcome-alert {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.75rem;
    padding: 0.5rem 0.75rem;
    background: rgba(255, 255, 255, 0.15);
    border-radius: var(--radius-md);
    font-size: 0.875rem;
}

.account-welcome-alert svg {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
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
    overflow: hidden;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
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
    text-decoration: none;
    color: inherit;
}

.account-stat-card:hover {
    border-color: var(--color-primary);
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.account-stat-card.stat-highlight {
    border-color: var(--color-warning-200);
    background: var(--color-warning-50);
}

.account-stat-card.stat-spent {
    cursor: default;
}

.account-stat-card.stat-spent:hover {
    transform: none;
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
    flex-shrink: 0;
}

.account-stat-icon.icon-warning {
    background: var(--color-warning-100);
    color: var(--color-warning-700);
}

.account-stat-icon.icon-success {
    background: var(--color-success-100);
    color: var(--color-success-700);
}

.account-stat-icon svg {
    width: 1.5rem;
    height: 1.5rem;
}

.account-stat-content {
    min-width: 0;
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

/* Card */
.card {
    background: var(--color-background);
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-border);
    overflow: hidden;
}

.card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--color-border);
}

.card-body {
    padding: 1.5rem;
}

.card-body.p-0 {
    padding: 0;
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
    text-decoration: none;
    color: inherit;
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

/* Activity List */
.activity-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--color-border);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-neutral-100);
    border-radius: var(--radius-md);
    color: var(--color-text-muted);
    flex-shrink: 0;
}

.activity-icon svg {
    width: 1rem;
    height: 1rem;
}

.activity-content {
    flex: 1;
    min-width: 0;
}

.activity-description {
    margin: 0 0 0.25rem;
    font-size: 0.875rem;
}

.activity-time {
    font-size: 0.75rem;
    color: var(--color-text-muted);
}

/* Account Info Card */
.account-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.account-info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.account-info-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--color-text-muted);
    letter-spacing: 0.025em;
}

.account-info-value {
    font-size: 0.9375rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Badge */
.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.125rem 0.5rem;
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    border-radius: var(--radius-sm);
}

.badge-success {
    background: var(--color-success-100);
    color: var(--color-success-700);
}

.badge-warning {
    background: var(--color-warning-100);
    color: var(--color-warning-700);
}

.badge-danger {
    background: var(--color-danger-100);
    color: var(--color-danger-700);
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
    padding: 1rem 1.5rem;
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

.orders-table tbody tr:last-child td {
    border-bottom: none;
}

.order-number {
    font-weight: 600;
    color: var(--color-primary);
    text-decoration: none;
}

.order-number:hover {
    text-decoration: underline;
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

.order-status.status-processing,
.order-status.status-paid {
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
    color: var(--color-text-muted);
}

/* Utility Classes */
.flex {
    display: flex;
}

.items-center {
    align-items: center;
}

.justify-between {
    justify-content: space-between;
}

.text-right {
    text-align: right;
}

.text-muted {
    color: var(--color-text-muted);
}

.text-primary {
    color: var(--color-primary);
}

.text-sm {
    font-size: 0.875rem;
}

.font-semibold {
    font-weight: 600;
}

.ml-2 {
    margin-left: 0.5rem;
}

.hover-underline:hover {
    text-decoration: underline;
}

/* Button styles */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: var(--radius-md);
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-primary {
    background: var(--color-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--color-primary-700);
}

.btn-ghost {
    background: transparent;
    color: var(--color-text);
}

.btn-ghost:hover {
    background: var(--color-background-alt);
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.8125rem;
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
    text-decoration: none;
}

.account-breadcrumb a:hover {
    color: var(--color-primary);
}

.account-breadcrumb svg {
    width: 1rem;
    height: 1rem;
}

/* Form styles */
.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--color-text);
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 0.625rem 0.875rem;
    font-size: 0.9375rem;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    background: var(--color-background);
    color: var(--color-text);
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px var(--color-primary-100);
}

.form-input.error,
.form-select.error,
.form-textarea.error {
    border-color: var(--color-danger);
}

.form-error {
    font-size: 0.8125rem;
    color: var(--color-danger);
    margin-top: 0.25rem;
}

.form-hint {
    font-size: 0.8125rem;
    color: var(--color-text-muted);
    margin-top: 0.25rem;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
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
    text-decoration: none;
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

/* Alert/Flash Messages */
.alert {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    border-radius: var(--radius-md);
    margin-bottom: 1.5rem;
}

.alert-success {
    background: var(--color-success-50);
    border: 1px solid var(--color-success-200);
    color: var(--color-success-700);
}

.alert-error {
    background: var(--color-danger-50);
    border: 1px solid var(--color-danger-200);
    color: var(--color-danger-700);
}

.alert-warning {
    background: var(--color-warning-50);
    border: 1px solid var(--color-warning-200);
    color: var(--color-warning-700);
}

.alert-info {
    background: var(--color-info-50);
    border: 1px solid var(--color-info-200);
    color: var(--color-info-700);
}

.alert svg {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
}

.alert-content {
    flex: 1;
}

/* Settings sections */
.settings-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--color-border);
}

.settings-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.settings-section-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
}

.settings-section-description {
    font-size: 0.875rem;
    color: var(--color-text-muted);
    margin: 0 0 1.5rem;
}

/* Addresses Grid */
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
    text-decoration: none;
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

/* Security Styles */
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
    flex-shrink: 0;
}

.session-icon svg {
    width: 1.25rem;
    height: 1.25rem;
}

.session-content {
    flex: 1;
    min-width: 0;
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

.session-actions {
    flex-shrink: 0;
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const openBtn = document.getElementById('openSidebar');
    const closeBtn = document.getElementById('closeSidebar');
    const sidebar = document.getElementById('accountSidebar');

    if (openBtn && sidebar) {
        openBtn.addEventListener('click', () => {
            sidebar.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    }

    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', () => {
            sidebar.classList.remove('open');
            document.body.style.overflow = '';
        });
    }

    // Close on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
            document.body.style.overflow = '';
        }
    });

    // Close on backdrop click
    if (sidebar) {
        sidebar.addEventListener('click', (e) => {
            if (e.target === sidebar) {
                sidebar.classList.remove('open');
                document.body.style.overflow = '';
            }
        });
    }
});
</script>
