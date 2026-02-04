<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Dashboard') ?> | Admin - <?= e(config('app.name')) ?></title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('css/design-system.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Mobile Sidebar Overlay -->
        <div class="admin-sidebar-overlay" id="sidebar-overlay"></div>

        <!-- Sidebar -->
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="admin-sidebar-header">
                <a href="<?= url('/admin') ?>" class="admin-logo">
                    <div class="admin-logo-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <path d="M2 17l10 5 10-5"/>
                            <path d="M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                    <div>
                        <span class="admin-logo-text"><?= e(config('app.name')) ?></span>
                        <?php if ((user()['role'] ?? '') === 'super_admin'): ?>
                        <span class="admin-super-indicator">Super Admin</span>
                        <?php endif; ?>
                    </div>
                </a>
            </div>

            <nav class="admin-sidebar-nav">
                <!-- Dashboard -->
                <div class="admin-nav-section">
                    <a href="<?= url('/admin') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'dashboard' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="9"/>
                            <rect x="14" y="3" width="7" height="5"/>
                            <rect x="14" y="12" width="7" height="9"/>
                            <rect x="3" y="16" width="7" height="5"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </div>

                <!-- Catalog -->
                <div class="admin-nav-section">
                    <span class="admin-nav-title">Catalog</span>
                    <a href="<?= url('/admin/products') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'products' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                        <span>Products</span>
                    </a>
                    <a href="<?= url('/admin/categories') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'categories' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>
                        </svg>
                        <span>Categories</span>
                    </a>
                    <a href="<?= url('/admin/attributes') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'attributes' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="4" y1="21" x2="4" y2="14"/>
                            <line x1="4" y1="10" x2="4" y2="3"/>
                            <line x1="12" y1="21" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12" y2="3"/>
                            <line x1="20" y1="21" x2="20" y2="16"/>
                            <line x1="20" y1="12" x2="20" y2="3"/>
                            <line x1="1" y1="14" x2="7" y2="14"/>
                            <line x1="9" y1="8" x2="15" y2="8"/>
                            <line x1="17" y1="16" x2="23" y2="16"/>
                        </svg>
                        <span>Attributes</span>
                    </a>
                    <a href="<?= url('/admin/vendors') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'vendors' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span>Vendors</span>
                    </a>
                </div>

                <!-- Sales -->
                <div class="admin-nav-section">
                    <span class="admin-nav-title">Sales</span>
                    <a href="<?= url('/admin/orders') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'orders' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 01-8 0"/>
                        </svg>
                        <span>Orders</span>
                    </a>
                    <a href="<?= url('/admin/coupons') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'coupons' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                            <line x1="7" y1="7" x2="7.01" y2="7"/>
                        </svg>
                        <span>Coupons</span>
                    </a>
                </div>

                <!-- Customers -->
                <div class="admin-nav-section">
                    <span class="admin-nav-title">Customers</span>
                    <a href="<?= url('/admin/customers') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'customers' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                            <path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                        <span>Customers</span>
                    </a>
                    <a href="<?= url('/admin/newsletter') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'newsletter' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <span>Newsletter</span>
                    </a>
                    <a href="<?= url('/admin/reviews') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'reviews' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        <span>Reviews</span>
                    </a>
                    <a href="<?= url('/admin/contact') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'contact' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                        </svg>
                        <span>Contact Messages</span>
                    </a>
                </div>

                <!-- Content -->
                <div class="admin-nav-section">
                    <span class="admin-nav-title">Content</span>
                    <a href="<?= url('/admin/pages') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'pages' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        <span>Pages</span>
                    </a>
                    <a href="<?= url('/admin/menus') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'menus' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                        <span>Menus</span>
                    </a>
                </div>

                <!-- Appearance -->
                <div class="admin-nav-section">
                    <span class="admin-nav-title">Appearance</span>
                    <a href="<?= url('/admin/branding') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'branding' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <circle cx="12" cy="12" r="6"/>
                            <circle cx="12" cy="12" r="2"/>
                        </svg>
                        <span>Logo & Branding</span>
                    </a>
                    <a href="<?= url('/admin/banners') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'banners' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                        <span>Banners & Sliders</span>
                    </a>
                    <a href="<?= url('/admin/homepage') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'homepage' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <line x1="3" y1="9" x2="21" y2="9"/>
                            <line x1="9" y1="21" x2="9" y2="9"/>
                        </svg>
                        <span>Homepage Builder</span>
                    </a>
                    <a href="<?= url('/admin/appearance') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'appearance' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/>
                        </svg>
                        <span>Theme Settings</span>
                    </a>
                </div>

                <!-- Settings -->
                <div class="admin-nav-section">
                    <span class="admin-nav-title">Settings</span>
                    <a href="<?= url('/admin/settings') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'settings' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
                        </svg>
                        <span>General Settings</span>
                    </a>
                    <a href="<?= url('/admin/seo') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'seo' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <span>SEO</span>
                    </a>
                    <a href="<?= url('/admin/redirects') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'redirects' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 14 4 9 9 4"/>
                            <path d="M20 20v-7a4 4 0 00-4-4H4"/>
                        </svg>
                        <span>URL Redirects</span>
                    </a>
                    <a href="<?= url('/admin/reports') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'reports' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="20" x2="18" y2="10"/>
                            <line x1="12" y1="20" x2="12" y2="4"/>
                            <line x1="6" y1="20" x2="6" y2="14"/>
                        </svg>
                        <span>Reports</span>
                    </a>
                    <a href="<?= url('/admin/search-analytics') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'search-analytics' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            <path d="M10 7v6m-3-3h6"/>
                        </svg>
                        <span>Search Analytics</span>
                    </a>
                    <?php if ((user()['role'] ?? '') === 'super_admin'): ?>
                    <a href="<?= url('/admin/users') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'users' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span>Admin Users</span>
                    </a>
                    <a href="<?= url('/admin/stock-sync') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'stock-sync' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Stock Sync</span>
                    </a>
                    <?php endif; ?>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="admin-sidebar-footer">
                <div class="admin-user-menu">
                    <div class="admin-user-avatar">
                        <?= strtoupper(substr(user()['first_name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div class="admin-user-info">
                        <div class="admin-user-name"><?= e(user()['first_name'] ?? 'Admin') ?> <?= e(user()['last_name'] ?? '') ?></div>
                        <div class="admin-user-role">
                            <?= ucfirst(str_replace('_', ' ', user()['role'] ?? 'Admin')) ?>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="admin-header-left">
                    <button type="button" class="admin-menu-toggle" id="sidebar-toggle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>

                    <div class="admin-search">
                        <svg class="admin-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" class="admin-search-input" placeholder="Search products, orders, customers...">
                        <span class="admin-search-shortcut">Ctrl+K</span>
                    </div>
                </div>

                <div class="admin-header-right">
                    <a href="<?= url('/') ?>" class="admin-header-btn" target="_blank" title="View Site">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                    </a>

                    <button type="button" class="admin-header-btn" title="Notifications">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 01-3.46 0"/>
                        </svg>
                    </button>

                    <div class="admin-header-divider"></div>

                    <form action="<?= url('/logout') ?>" method="POST" style="display: inline;">
                        <?= csrfField() ?>
                        <button type="submit" class="admin-btn admin-btn-ghost admin-btn-sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($flash_success = flash('success')): ?>
                <div class="admin-alert admin-alert-success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <div class="admin-alert-content">
                        <div class="admin-alert-text"><?= e($flash_success) ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($flash_error = flash('error')): ?>
                <div class="admin-alert admin-alert-error">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <div class="admin-alert-content">
                        <div class="admin-alert-text"><?= e($flash_error) ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </div>
        </main>
    </div>

    <script>
        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const adminSidebar = document.getElementById('admin-sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        if (sidebarToggle && adminSidebar && sidebarOverlay) {
            sidebarToggle.addEventListener('click', function() {
                adminSidebar.classList.toggle('open');
                sidebarOverlay.classList.toggle('show');
            });

            sidebarOverlay.addEventListener('click', function() {
                adminSidebar.classList.remove('open');
                sidebarOverlay.classList.remove('show');
            });
        }

        // Search shortcut
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.querySelector('.admin-search-input')?.focus();
            }
        });
    </script>
</body>
</html>
