<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Dashboard') ?> | Admin - <?= e(config('app.name')) ?></title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('css/design-system.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        :root {
            --admin-sidebar-width: 260px;
            --admin-header-height: 60px;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: var(--admin-sidebar-width);
            background-color: var(--color-neutral-900);
            color: var(--color-neutral-300);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: var(--z-fixed);
            display: flex;
            flex-direction: column;
            transition: transform var(--duration-300);
        }

        @media (max-width: 1023px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            .admin-sidebar.is-open {
                transform: translateX(0);
            }
        }

        .admin-sidebar-header {
            padding: var(--space-4) var(--space-5);
            border-bottom: 1px solid var(--color-neutral-800);
        }

        .admin-sidebar-logo {
            font-size: var(--text-lg);
            font-weight: var(--font-bold);
            color: var(--color-neutral-0);
        }

        .admin-sidebar-nav {
            flex: 1;
            padding: var(--space-4) 0;
            overflow-y: auto;
        }

        .admin-nav-section {
            margin-bottom: var(--space-4);
        }

        .admin-nav-section-title {
            padding: var(--space-2) var(--space-5);
            font-size: var(--text-xs);
            font-weight: var(--font-semibold);
            text-transform: uppercase;
            letter-spacing: var(--tracking-wider);
            color: var(--color-neutral-500);
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-3) var(--space-5);
            color: var(--color-neutral-400);
            transition: var(--transition-colors);
        }

        .admin-nav-link:hover,
        .admin-nav-link.is-active {
            color: var(--color-neutral-0);
            background-color: var(--color-neutral-800);
        }

        .admin-nav-link.is-active {
            border-left: 3px solid var(--color-primary);
        }

        .admin-nav-link svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .admin-main {
            flex: 1;
            margin-left: var(--admin-sidebar-width);
            background-color: var(--color-background-alt);
            min-height: 100vh;
        }

        @media (max-width: 1023px) {
            .admin-main {
                margin-left: 0;
            }
        }

        .admin-header {
            height: var(--admin-header-height);
            background-color: var(--color-background);
            border-bottom: 1px solid var(--color-border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 var(--space-6);
            position: sticky;
            top: 0;
            z-index: var(--z-sticky);
        }

        .admin-content {
            padding: var(--space-6);
        }

        .admin-page-title {
            font-size: var(--text-2xl);
            font-weight: var(--font-bold);
            margin-bottom: var(--space-6);
        }

        .admin-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-4);
            margin-bottom: var(--space-6);
        }

        .admin-stat-card {
            background-color: var(--color-background);
            border-radius: var(--radius-xl);
            padding: var(--space-5);
            border: 1px solid var(--color-border-light);
        }

        .admin-stat-label {
            font-size: var(--text-sm);
            color: var(--color-text-muted);
            margin-bottom: var(--space-1);
        }

        .admin-stat-value {
            font-size: var(--text-2xl);
            font-weight: var(--font-bold);
        }

        .admin-stat-change {
            font-size: var(--text-xs);
            margin-top: var(--space-1);
        }

        .admin-stat-change.positive { color: var(--color-success); }
        .admin-stat-change.negative { color: var(--color-danger); }

        .admin-table {
            width: 100%;
            background-color: var(--color-background);
            border-radius: var(--radius-xl);
            border: 1px solid var(--color-border-light);
            overflow: hidden;
        }

        .admin-table th,
        .admin-table td {
            padding: var(--space-3) var(--space-4);
            text-align: left;
            border-bottom: 1px solid var(--color-border-light);
        }

        .admin-table th {
            background-color: var(--color-background-alt);
            font-size: var(--text-sm);
            font-weight: var(--font-semibold);
            color: var(--color-text-muted);
        }

        .admin-table tr:last-child td {
            border-bottom: none;
        }

        .admin-table tr:hover td {
            background-color: var(--color-background-alt);
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="admin-sidebar-header">
                <a href="<?= url('/admin') ?>" class="admin-sidebar-logo"><?= e(config('app.name')) ?> Admin</a>
            </div>

            <nav class="admin-sidebar-nav">
                <div class="admin-nav-section">
                    <a href="<?= url('/admin') ?>" class="admin-nav-link <?= $active_page === 'dashboard' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </div>

                <div class="admin-nav-section">
                    <span class="admin-nav-section-title">Catalog</span>
                    <a href="<?= url('/admin/products') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'products' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                        <span>Products</span>
                    </a>
                    <a href="<?= url('/admin/categories') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'categories' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>
                        </svg>
                        <span>Categories</span>
                    </a>
                    <a href="<?= url('/admin/attributes') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'attributes' ? 'is-active' : '' ?>">
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
                    <a href="<?= url('/admin/vendors') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'vendors' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span>Vendors</span>
                    </a>
                </div>

                <div class="admin-nav-section">
                    <span class="admin-nav-section-title">Sales</span>
                    <a href="<?= url('/admin/orders') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'orders' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 01-8 0"/>
                        </svg>
                        <span>Orders</span>
                    </a>
                    <a href="<?= url('/admin/coupons') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'coupons' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"></path>
                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        </svg>
                        <span>Coupons</span>
                    </a>
                </div>

                <div class="admin-nav-section">
                    <span class="admin-nav-section-title">Customers</span>
                    <a href="<?= url('/admin/customers') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'customers' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                            <path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                        <span>Customers</span>
                    </a>
                    <a href="<?= url('/admin/newsletter') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'newsletter' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <span>Newsletter</span>
                    </a>
                    <a href="<?= url('/admin/reviews') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'reviews' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>Reviews</span>
                    </a>
                    <a href="<?= url('/admin/contact') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'contact' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path>
                        </svg>
                        <span>Contact Messages</span>
                    </a>
                </div>

                <div class="admin-nav-section">
                    <span class="admin-nav-section-title">Content</span>
                    <a href="<?= url('/admin/pages') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'pages' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        <span>Pages</span>
                    </a>
                    <a href="<?= url('/admin/menus') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'menus' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                        <span>Menus</span>
                    </a>
                </div>

                <div class="admin-nav-section">
                    <span class="admin-nav-section-title">Appearance</span>
                    <a href="<?= url('/admin/branding') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'branding' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="13.5" cy="6.5" r="2.5"/>
                            <circle cx="6.5" cy="12.5" r="2.5"/>
                            <circle cx="17.5" cy="17.5" r="2.5"/>
                            <path d="M13.5 9v12"/>
                            <path d="M6.5 15v4"/>
                        </svg>
                        <span>Logo & Branding</span>
                    </a>
                    <a href="<?= url('/admin/banners') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'banners' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        <span>Banners & Sliders</span>
                    </a>
                    <a href="<?= url('/admin/homepage') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'homepage' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span>Homepage Builder</span>
                    </a>
                    <a href="<?= url('/admin/appearance') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'appearance' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="3" y1="9" x2="21" y2="9"></line>
                            <line x1="9" y1="21" x2="9" y2="9"></line>
                        </svg>
                        <span>Appearance</span>
                    </a>
                </div>

                <div class="admin-nav-section">
                    <span class="admin-nav-section-title">Settings</span>
                    <a href="<?= url('/admin/settings') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'settings' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
                        </svg>
                        <span>Settings</span>
                    </a>
                    <a href="<?= url('/admin/seo') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'seo' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <span>SEO</span>
                    </a>
                    <a href="<?= url('/admin/redirects') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'redirects' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>URL Redirects</span>
                    </a>
                    <a href="<?= url('/admin/search-analytics') ?>" class="admin-nav-link <?= ($active_page ?? '') === 'search-analytics' ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                        <span>Search Analytics</span>
                    </a>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div style="padding: var(--space-4); border-top: 1px solid var(--color-neutral-800);">
                <a href="<?= url('/') ?>" class="admin-nav-link" target="_blank">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    <span>View Site</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <button type="button" class="btn btn-ghost btn-icon lg:hidden" id="sidebar-toggle">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>

                <div class="flex-1"></div>

                <div class="flex items-center gap-4">
                    <span class="text-sm"><?= e(user()['first_name'] ?? 'Admin') ?></span>
                    <form action="<?= url('/logout') ?>" method="POST" style="display: inline;">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-ghost btn-sm">Logout</button>
                    </form>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($flash_success = flash('success')): ?>
                <div class="alert alert-success mb-4"><?= e($flash_success) ?></div>
                <?php endif; ?>
                <?php if ($flash_error = flash('error')): ?>
                <div class="alert alert-danger mb-4"><?= e($flash_error) ?></div>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </div>
        </main>
    </div>

    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
            document.getElementById('admin-sidebar').classList.toggle('is-open');
        });
    </script>
</body>
</html>
