<!-- Account Sidebar Navigation -->
<aside class="account-sidebar" id="accountSidebar">
    <!-- Mobile Close Button -->
    <button type="button" class="account-sidebar-close" id="closeSidebar" aria-label="Close menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>

    <!-- User Info (Mobile) -->
    <div class="account-sidebar-user">
        <?php $currentUser = user(); ?>
        <?php if ($currentUser): ?>
        <div class="sidebar-user-avatar">
            <?php if (!empty($currentUser['avatar'])): ?>
            <img src="<?= e($currentUser['avatar']) ?>" alt="<?= e($currentUser['first_name']) ?>">
            <?php else: ?>
            <span><?= strtoupper(substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1)) ?></span>
            <?php endif; ?>
        </div>
        <div class="sidebar-user-info">
            <span class="sidebar-user-name"><?= e($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></span>
            <span class="sidebar-user-email"><?= e($currentUser['email']) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <nav class="account-nav">
        <ul class="account-nav-list">
            <li class="account-nav-item">
                <a href="<?= url('/account') ?>" class="account-nav-link <?= isCurrentPath('/account', true) ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="9"></rect>
                        <rect x="14" y="3" width="7" height="5"></rect>
                        <rect x="14" y="12" width="7" height="9"></rect>
                        <rect x="3" y="16" width="7" height="5"></rect>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="account-nav-item">
                <a href="<?= url('/account/orders') ?>" class="account-nav-link <?= isCurrentPath('/account/orders') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                    <span>Orders</span>
                    <?php
                    // Show pending orders count if available
                    $pendingCount = $_SESSION['pending_orders_count'] ?? 0;
                    if ($pendingCount > 0):
                    ?>
                    <span class="nav-badge"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="account-nav-item">
                <a href="<?= url('/wishlist') ?>" class="account-nav-link <?= isCurrentPath('/wishlist') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                    <span>Wishlist</span>
                </a>
            </li>
            <li class="account-nav-item">
                <a href="<?= url('/account/addresses') ?>" class="account-nav-link <?= isCurrentPath('/account/addresses') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span>Addresses</span>
                </a>
            </li>
        </ul>

        <div class="account-nav-divider"></div>

        <ul class="account-nav-list">
            <li class="account-nav-item">
                <a href="<?= url('/account/settings') ?>" class="account-nav-link <?= isCurrentPath('/account/settings') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>Profile Settings</span>
                </a>
            </li>
            <li class="account-nav-item">
                <a href="<?= url('/account/security') ?>" class="account-nav-link <?= isCurrentPath('/account/security') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <span>Security</span>
                    <?php
                    // Show indicator if MFA is not enabled
                    $userInfo = user();
                    if ($userInfo && empty($userInfo['mfa_enabled'])):
                    ?>
                    <span class="nav-indicator warning" title="Two-factor authentication not enabled"></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="account-nav-item">
                <a href="<?= url('/account/activity') ?>" class="account-nav-link <?= isCurrentPath('/account/activity') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                    <span>Activity Log</span>
                </a>
            </li>
        </ul>

        <div class="account-nav-divider"></div>

        <ul class="account-nav-list">
            <?php
            // Show Admin Panel link for admin/super_admin users
            $currentUserRole = $currentUser['role'] ?? '';
            if (in_array($currentUserRole, ['admin', 'super_admin'])):
            ?>
            <li class="account-nav-item">
                <a href="<?= url('/admin') ?>" class="account-nav-link account-nav-admin">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="3" y1="9" x2="21" y2="9"></line>
                        <line x1="9" y1="21" x2="9" y2="9"></line>
                    </svg>
                    <span>Admin Panel</span>
                    <span class="nav-badge admin"><?= $currentUserRole === 'super_admin' ? 'Super' : 'Admin' ?></span>
                </a>
            </li>
            <?php endif; ?>
            <li class="account-nav-item">
                <a href="<?= url('/contact') ?>" class="account-nav-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <span>Help & Support</span>
                </a>
            </li>
            <li class="account-nav-item">
                <form action="<?= url('/logout') ?>" method="POST" class="logout-form">
                    <?= csrfField() ?>
                    <button type="submit" class="account-nav-link account-nav-logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Log Out</span>
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</aside>

<!-- Mobile Menu Toggle -->
<button type="button" class="account-mobile-menu-toggle" id="openSidebar" aria-label="Open menu">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
    </svg>
</button>
