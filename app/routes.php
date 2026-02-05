<?php
/**
 * Application Routes
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

use App\Core\Router;

$router = new Router();

// ============================================================================
// PUBLIC ROUTES
// ============================================================================

// Home
$router->get('/', 'App\Controllers\HomeController@index', 'home');

// Offline page (for PWA)
$router->get('/offline', 'App\Controllers\PageController@offline', 'offline');

// Products
$router->get('/products', 'App\Controllers\ProductController@index', 'products.index');
$router->get('/products/{slug}', 'App\Controllers\ProductController@show', 'products.show');

// Categories
$router->get('/categories', 'App\Controllers\CategoryController@index', 'categories.index');
$router->get('/categories/{slug}', 'App\Controllers\CategoryController@show', 'categories.show');

// Search
$router->get('/search', 'App\Controllers\SearchController@index', 'search');
$router->get('/search/suggest', 'App\Controllers\SearchController@suggest', 'search.suggest');
$router->post('/search/filter', 'App\Controllers\SearchController@filter', 'search.filter');

// Cart
$router->get('/cart', 'App\Controllers\CartController@index', 'cart.index');
$router->post('/cart/add', 'App\Controllers\CartController@add', 'cart.add');
$router->post('/cart/update', 'App\Controllers\CartController@update', 'cart.update');
$router->post('/cart/remove', 'App\Controllers\CartController@remove', 'cart.remove');
$router->get('/cart/data', 'App\Controllers\CartController@data', 'cart.data');
$router->post('/cart/apply-coupon', 'App\Controllers\CartController@applyCoupon', 'cart.coupon');

// Wishlist
$router->get('/wishlist', 'App\Controllers\WishlistController@index', 'wishlist.index');
$router->post('/wishlist/toggle', 'App\Controllers\WishlistController@toggle', 'wishlist.toggle');
$router->post('/wishlist/remove', 'App\Controllers\WishlistController@remove', 'wishlist.remove');
$router->post('/wishlist/move-to-cart', 'App\Controllers\WishlistController@moveToCart', 'wishlist.move');

// Checkout
$router->get('/checkout', 'App\Controllers\CheckoutController@index', 'checkout.index');
$router->post('/checkout/process', 'App\Controllers\CheckoutController@process', 'checkout.process');
$router->post('/checkout/process-payment', 'App\Controllers\CheckoutController@processPayment', 'checkout.payment');
$router->post('/checkout/process-eft', 'App\Controllers\CheckoutController@processEft', 'checkout.eft');
$router->get('/checkout/success/{order}', 'App\Controllers\CheckoutController@success', 'checkout.success');
$router->post('/checkout/webhook', 'App\Controllers\CheckoutController@webhook', 'checkout.webhook');

// Pages (CMS)
$router->get('/page/{slug}', 'App\Controllers\PageController@show', 'pages.show');

// Contact
$router->get('/contact', 'App\Controllers\ContactController@index', 'contact.index');
$router->post('/contact', 'App\Controllers\ContactController@submit', 'contact.submit');

// ============================================================================
// AUTHENTICATION ROUTES
// ============================================================================

$router->get('/login', 'App\Controllers\AuthController@showLogin', 'login');
$router->post('/login', 'App\Controllers\AuthController@login', 'login.post');
$router->get('/register', 'App\Controllers\AuthController@showRegister', 'register');
$router->post('/register', 'App\Controllers\AuthController@register', 'register.post');
$router->post('/logout', 'App\Controllers\AuthController@logout', 'logout');
$router->get('/forgot-password', 'App\Controllers\AuthController@showForgotPassword', 'password.forgot');
$router->post('/forgot-password', 'App\Controllers\AuthController@sendResetLink', 'password.email');
$router->get('/reset-password/{token}', 'App\Controllers\AuthController@showResetPassword', 'password.reset');
$router->post('/reset-password', 'App\Controllers\AuthController@resetPassword', 'password.update');
$router->get('/verify-email/{token}', 'App\Controllers\AuthController@verifyEmail', 'verify.email');

// ============================================================================
// ACCOUNT ROUTES (Authenticated)
// ============================================================================

$router->group(['prefix' => 'account', 'middleware' => ['App\Middleware\AuthMiddleware']], function ($router) {
    $router->get('/', 'App\Controllers\AccountController@dashboard', 'account.dashboard');
    $router->get('/orders', 'App\Controllers\AccountController@orders', 'account.orders');
    $router->get('/orders/{id}', 'App\Controllers\AccountController@orderDetail', 'account.order');
    $router->get('/orders/{id}/invoice', 'App\Controllers\AccountController@invoice', 'account.invoice');
    $router->get('/addresses', 'App\Controllers\AccountController@addresses', 'account.addresses');
    $router->post('/addresses', 'App\Controllers\AccountController@saveAddress', 'account.addresses.save');
    $router->delete('/addresses/{id}', 'App\Controllers\AccountController@deleteAddress', 'account.addresses.delete');
    $router->get('/settings', 'App\Controllers\AccountController@settings', 'account.settings');
    $router->post('/settings', 'App\Controllers\AccountController@updateSettings', 'account.settings.update');
    $router->post('/settings/password', 'App\Controllers\AccountController@updatePassword', 'account.password.update');
    $router->get('/security', 'App\Controllers\AccountController@security', 'account.security');
    $router->get('/activity', 'App\Controllers\AccountController@activity', 'account.activity');

    // Session Management
    $router->post('/sessions/{id}/terminate', 'App\Controllers\AccountController@terminateSession', 'account.sessions.terminate');
    $router->post('/sessions/terminate-others', 'App\Controllers\AccountController@terminateOtherSessions', 'account.sessions.terminate-others');

    // Two-Factor Authentication (MFA)
    $router->get('/mfa/setup', 'App\Controllers\AccountController@setupMfa', 'account.mfa.setup');
    $router->post('/mfa/enable', 'App\Controllers\AccountController@enableMfa', 'account.mfa.enable');
    $router->post('/mfa/disable', 'App\Controllers\AccountController@disableMfa', 'account.mfa.disable');
    $router->post('/mfa/backup-codes', 'App\Controllers\AccountController@regenerateBackupCodes', 'account.mfa.backup-codes');
});

// ============================================================================
// API ROUTES
// ============================================================================

$router->group(['prefix' => 'api'], function ($router) {
    // Products API
    $router->get('/products', 'App\Controllers\Api\ProductApiController@index');
    $router->get('/products/{id}', 'App\Controllers\Api\ProductApiController@show');

    // Cart API
    $router->get('/cart', 'App\Controllers\Api\CartApiController@index');
    $router->post('/cart', 'App\Controllers\Api\CartApiController@add');
    $router->put('/cart/{id}', 'App\Controllers\Api\CartApiController@update');
    $router->delete('/cart/{id}', 'App\Controllers\Api\CartApiController@remove');

    // AI Assistant
    $router->post('/assistant', 'App\Controllers\Api\AssistantApiController@chat');
});

// ============================================================================
// SEO ROUTES
// ============================================================================

$router->get('/sitemap.xml', 'App\Controllers\SeoController@sitemap');
$router->get('/robots.txt', 'App\Controllers\SeoController@robots');

// ============================================================================
// ADMIN ROUTES
// ============================================================================

$router->group(['prefix' => 'admin', 'middleware' => ['App\Middleware\AdminMiddleware']], function ($router) {
    // Dashboard
    $router->get('/', 'Admin\Controllers\DashboardController@index', 'admin.dashboard');

    // Products
    $router->get('/products', 'Admin\Controllers\ProductController@index', 'admin.products.index');
    $router->get('/products/create', 'Admin\Controllers\ProductController@create', 'admin.products.create');
    $router->get('/products/export', 'Admin\Controllers\ProductController@export', 'admin.products.export');
    $router->get('/products/import', 'Admin\Controllers\ProductController@importForm', 'admin.products.import.form');
    $router->post('/products/import', 'Admin\Controllers\ProductController@import', 'admin.products.import');
    $router->get('/products/import/template', 'Admin\Controllers\ProductController@importTemplate', 'admin.products.import.template');
    $router->post('/products/import/process', 'Admin\Controllers\ProductController@importProcess', 'admin.products.import.process');
    $router->post('/products', 'Admin\Controllers\ProductController@store', 'admin.products.store');
    $router->get('/products/{id}/edit', 'Admin\Controllers\ProductController@edit', 'admin.products.edit');
    $router->put('/products/{id}', 'Admin\Controllers\ProductController@update', 'admin.products.update');
    $router->delete('/products/{id}', 'Admin\Controllers\ProductController@destroy', 'admin.products.destroy');
    $router->delete('/products/images/{id}', 'Admin\Controllers\ProductController@deleteImage', 'admin.products.images.destroy');
    $router->post('/products/images/{id}/primary', 'Admin\Controllers\ProductController@setPrimaryImage', 'admin.products.images.primary');
    $router->post('/products/images/reorder', 'Admin\Controllers\ProductController@reorderImages', 'admin.products.images.reorder');
    $router->put('/products/reviews/{id}', 'Admin\Controllers\ProductController@updateReview', 'admin.products.reviews.update');
    $router->delete('/products/reviews/{id}', 'Admin\Controllers\ProductController@deleteReview', 'admin.products.reviews.destroy');
    $router->post('/products/{id}/ai-generate', 'Admin\Controllers\ProductController@generateAiContent', 'admin.products.ai.generate');
    $router->post('/products/{id}/ai-regenerate-sku', 'Admin\Controllers\ProductController@regenerateFromSku', 'admin.products.ai.regenerate');
    $router->post('/products/ai-search', 'Admin\Controllers\ProductController@searchAiInfo', 'admin.products.ai.search');
    $router->post('/products/bulk-action', 'Admin\Controllers\ProductController@bulkAction', 'admin.products.bulk');

    // Categories
    $router->get('/categories', 'Admin\Controllers\CategoryController@index', 'admin.categories.index');
    $router->get('/categories/create', 'Admin\Controllers\CategoryController@create', 'admin.categories.create');
    $router->post('/categories', 'Admin\Controllers\CategoryController@store', 'admin.categories.store');
    $router->get('/categories/{id}/edit', 'Admin\Controllers\CategoryController@edit', 'admin.categories.edit');
    $router->put('/categories/{id}', 'Admin\Controllers\CategoryController@update', 'admin.categories.update');
    $router->delete('/categories/{id}', 'Admin\Controllers\CategoryController@destroy', 'admin.categories.destroy');

    // Attributes
    $router->get('/attributes', 'Admin\Controllers\AttributeController@index', 'admin.attributes.index');
    $router->post('/attributes', 'Admin\Controllers\AttributeController@store', 'admin.attributes.store');
    $router->put('/attributes/{id}', 'Admin\Controllers\AttributeController@update', 'admin.attributes.update');
    $router->delete('/attributes/{id}', 'Admin\Controllers\AttributeController@destroy', 'admin.attributes.destroy');
    $router->post('/attributes/{id}/values', 'Admin\Controllers\AttributeController@storeValue', 'admin.attributes.values.store');
    $router->put('/attributes/values/{id}', 'Admin\Controllers\AttributeController@updateValue', 'admin.attributes.values.update');
    $router->delete('/attributes/values/{id}', 'Admin\Controllers\AttributeController@destroyValue', 'admin.attributes.values.destroy');

    // Orders
    $router->get('/orders', 'Admin\Controllers\OrderController@index', 'admin.orders.index');
    $router->get('/orders/{id}', 'Admin\Controllers\OrderController@show', 'admin.orders.show');
    $router->post('/orders/{id}/status', 'Admin\Controllers\OrderController@updateStatus', 'admin.orders.status');
    $router->post('/orders/{id}/note', 'Admin\Controllers\OrderController@addNote', 'admin.orders.note');

    // Coupons
    $router->get('/coupons', 'Admin\Controllers\CouponController@index', 'admin.coupons.index');
    $router->get('/coupons/create', 'Admin\Controllers\CouponController@create', 'admin.coupons.create');
    $router->post('/coupons', 'Admin\Controllers\CouponController@store', 'admin.coupons.store');
    $router->get('/coupons/{id}/edit', 'Admin\Controllers\CouponController@edit', 'admin.coupons.edit');
    $router->put('/coupons/{id}', 'Admin\Controllers\CouponController@update', 'admin.coupons.update');
    $router->delete('/coupons/{id}', 'Admin\Controllers\CouponController@destroy', 'admin.coupons.destroy');
    $router->post('/coupons/{id}/toggle', 'Admin\Controllers\CouponController@toggle', 'admin.coupons.toggle');

    // Customers
    $router->get('/customers', 'Admin\Controllers\CustomerController@index', 'admin.customers.index');
    $router->get('/customers/{id}', 'Admin\Controllers\CustomerController@show', 'admin.customers.show');
    $router->get('/customers/{id}/edit', 'Admin\Controllers\CustomerController@edit', 'admin.customers.edit');
    $router->put('/customers/{id}', 'Admin\Controllers\CustomerController@update', 'admin.customers.update');

    // Users (Admin)
    $router->get('/users', 'Admin\Controllers\UserController@index', 'admin.users.index');
    $router->get('/users/create', 'Admin\Controllers\UserController@create', 'admin.users.create');
    $router->post('/users', 'Admin\Controllers\UserController@store', 'admin.users.store');
    $router->get('/users/{id}', 'Admin\Controllers\UserController@show', 'admin.users.show');
    $router->get('/users/{id}/edit', 'Admin\Controllers\UserController@edit', 'admin.users.edit');
    $router->put('/users/{id}', 'Admin\Controllers\UserController@update', 'admin.users.update');
    $router->delete('/users/{id}', 'Admin\Controllers\UserController@destroy', 'admin.users.destroy');

    // User Management - Extended (Super Admin)
    $router->get('/users/{id}/audit', 'Admin\Controllers\UserController@auditLog', 'admin.users.audit');
    $router->post('/users/{id}/mfa/toggle', 'Admin\Controllers\UserController@toggleMfa', 'admin.users.mfa.toggle');
    $router->post('/users/{id}/sessions/terminate', 'Admin\Controllers\UserController@terminateSessions', 'admin.users.sessions.terminate');
    $router->post('/users/{id}/password/reset', 'Admin\Controllers\UserController@forcePasswordReset', 'admin.users.password.reset');
    $router->post('/users/{id}/impersonate', 'Admin\Controllers\UserController@impersonate', 'admin.users.impersonate');
    $router->post('/users/impersonate/stop', 'Admin\Controllers\UserController@stopImpersonation', 'admin.users.impersonate.stop');

    // Settings
    $router->get('/settings', 'Admin\Controllers\SettingsController@index', 'admin.settings.index');
    $router->post('/settings', 'Admin\Controllers\SettingsController@update', 'admin.settings.update');

    // Branding
    $router->get('/branding', 'Admin\Controllers\SettingsController@branding', 'admin.branding.index');
    $router->post('/branding', 'Admin\Controllers\SettingsController@updateBranding', 'admin.branding.update');

    // Appearance
    $router->get('/appearance', 'Admin\Controllers\SettingsController@appearance', 'admin.appearance.index');
    $router->post('/appearance', 'Admin\Controllers\SettingsController@updateAppearance', 'admin.appearance.update');

    // Banners
    $router->get('/banners', 'Admin\Controllers\BannerController@index', 'admin.banners.index');
    $router->get('/banners/create', 'Admin\Controllers\BannerController@create', 'admin.banners.create');
    $router->post('/banners', 'Admin\Controllers\BannerController@store', 'admin.banners.store');
    $router->get('/banners/{id}/edit', 'Admin\Controllers\BannerController@edit', 'admin.banners.edit');
    $router->put('/banners/{id}', 'Admin\Controllers\BannerController@update', 'admin.banners.update');
    $router->delete('/banners/{id}', 'Admin\Controllers\BannerController@destroy', 'admin.banners.destroy');
    $router->post('/banners/reorder', 'Admin\Controllers\BannerController@reorder', 'admin.banners.reorder');
    $router->post('/banners/{id}/toggle', 'Admin\Controllers\BannerController@toggle', 'admin.banners.toggle');

    // Homepage Builder
    $router->get('/homepage', 'Admin\Controllers\HomepageController@index', 'admin.homepage.index');
    $router->post('/homepage', 'Admin\Controllers\HomepageController@store', 'admin.homepage.store');
    $router->get('/homepage/{id}/edit', 'Admin\Controllers\HomepageController@edit', 'admin.homepage.edit');
    $router->put('/homepage/{id}', 'Admin\Controllers\HomepageController@update', 'admin.homepage.update');
    $router->delete('/homepage/{id}', 'Admin\Controllers\HomepageController@destroy', 'admin.homepage.destroy');
    $router->post('/homepage/reorder', 'Admin\Controllers\HomepageController@reorder', 'admin.homepage.reorder');
    $router->post('/homepage/{id}/toggle', 'Admin\Controllers\HomepageController@toggle', 'admin.homepage.toggle');

    // Menu Builder
    $router->get('/menus', 'Admin\Controllers\MenuController@index', 'admin.menus.index');
    $router->post('/menus', 'Admin\Controllers\MenuController@store', 'admin.menus.store');
    $router->put('/menus/{id}', 'Admin\Controllers\MenuController@update', 'admin.menus.update');
    $router->delete('/menus/{id}', 'Admin\Controllers\MenuController@destroy', 'admin.menus.destroy');
    $router->post('/menus/{id}/items', 'Admin\Controllers\MenuController@storeItem', 'admin.menus.items.store');
    $router->post('/menus/{id}/reorder', 'Admin\Controllers\MenuController@reorder', 'admin.menus.reorder');
    $router->delete('/menus/items/{id}', 'Admin\Controllers\MenuController@destroyItem', 'admin.menus.items.destroy');

    // Pages (CMS)
    $router->get('/pages', 'Admin\Controllers\PageController@index', 'admin.pages.index');
    $router->get('/pages/create', 'Admin\Controllers\PageController@create', 'admin.pages.create');
    $router->post('/pages', 'Admin\Controllers\PageController@store', 'admin.pages.store');
    $router->get('/pages/{id}/edit', 'Admin\Controllers\PageController@edit', 'admin.pages.edit');
    $router->put('/pages/{id}', 'Admin\Controllers\PageController@update', 'admin.pages.update');
    $router->delete('/pages/{id}', 'Admin\Controllers\PageController@destroy', 'admin.pages.destroy');

    // SEO
    $router->get('/seo', 'Admin\Controllers\SeoController@index', 'admin.seo.index');
    $router->post('/seo', 'Admin\Controllers\SeoController@update', 'admin.seo.update');
    $router->post('/seo/sitemap', 'Admin\Controllers\SeoController@generateSitemap', 'admin.seo.sitemap');

    // Stock Sync
    $router->get('/stock-sync', 'Admin\Controllers\StockSyncController@index', 'admin.stock.index');
    $router->post('/stock-sync/import', 'Admin\Controllers\StockSyncController@import', 'admin.stock.import');
    $router->post('/stock-sync/run', 'Admin\Controllers\StockSyncController@run', 'admin.stock.run');
    $router->get('/stock-sync/template', 'Admin\Controllers\StockSyncController@template', 'admin.stock.template');
    $router->get('/stock-sync/log/{id}', 'Admin\Controllers\StockSyncController@log', 'admin.stock.log');
    $router->post('/stock-sync/bulk-update', 'Admin\Controllers\StockSyncController@bulkUpdate', 'admin.stock.bulk');

    // Reports
    $router->get('/reports', 'Admin\Controllers\ReportController@index', 'admin.reports.index');
    $router->get('/reports/sales', 'Admin\Controllers\ReportController@sales', 'admin.reports.sales');
    $router->get('/reports/products', 'Admin\Controllers\ReportController@products', 'admin.reports.products');
    $router->get('/reports/customers', 'Admin\Controllers\ReportController@customers', 'admin.reports.customers');
    $router->get('/reports/export', 'Admin\Controllers\ReportController@export', 'admin.reports.export');

    // Newsletter Subscribers
    $router->get('/newsletter', 'Admin\Controllers\NewsletterController@index', 'admin.newsletter.index');
    $router->delete('/newsletter/{id}', 'Admin\Controllers\NewsletterController@destroy', 'admin.newsletter.destroy');
    $router->get('/newsletter/export', 'Admin\Controllers\NewsletterController@export', 'admin.newsletter.export');

    // Reviews
    $router->get('/reviews', 'Admin\Controllers\ReviewController@index', 'admin.reviews.index');
    $router->post('/reviews/{id}/approve', 'Admin\Controllers\ReviewController@approve', 'admin.reviews.approve');
    $router->post('/reviews/{id}/reject', 'Admin\Controllers\ReviewController@reject', 'admin.reviews.reject');
    $router->delete('/reviews/{id}', 'Admin\Controllers\ReviewController@destroy', 'admin.reviews.destroy');

    // Contact Messages
    $router->get('/contact', 'Admin\Controllers\ContactController@index', 'admin.contact.index');
    $router->get('/contact/{id}', 'Admin\Controllers\ContactController@show', 'admin.contact.show');
    $router->post('/contact/{id}/status', 'Admin\Controllers\ContactController@updateStatus', 'admin.contact.status');
    $router->delete('/contact/{id}', 'Admin\Controllers\ContactController@destroy', 'admin.contact.destroy');

    // URL Redirects
    $router->get('/redirects', 'Admin\Controllers\RedirectController@index', 'admin.redirects.index');
    $router->get('/redirects/create', 'Admin\Controllers\RedirectController@create', 'admin.redirects.create');
    $router->post('/redirects', 'Admin\Controllers\RedirectController@store', 'admin.redirects.store');
    $router->get('/redirects/{id}/edit', 'Admin\Controllers\RedirectController@edit', 'admin.redirects.edit');
    $router->put('/redirects/{id}', 'Admin\Controllers\RedirectController@update', 'admin.redirects.update');
    $router->delete('/redirects/{id}', 'Admin\Controllers\RedirectController@destroy', 'admin.redirects.destroy');
    $router->post('/redirects/{id}/toggle', 'Admin\Controllers\RedirectController@toggle', 'admin.redirects.toggle');

    // Search Analytics
    $router->get('/search-analytics', 'Admin\Controllers\SearchAnalyticsController@index', 'admin.search.index');
    $router->post('/search/synonyms', 'Admin\Controllers\SearchAnalyticsController@storeSynonym', 'admin.search.synonyms.store');
    $router->delete('/search/synonyms/{id}', 'Admin\Controllers\SearchAnalyticsController@destroySynonym', 'admin.search.synonyms.destroy');

    // Vendors
    $router->get('/vendors', 'Admin\Controllers\VendorController@index', 'admin.vendors.index');
    $router->get('/vendors/create', 'Admin\Controllers\VendorController@create', 'admin.vendors.create');
    $router->post('/vendors', 'Admin\Controllers\VendorController@store', 'admin.vendors.store');
    $router->get('/vendors/{id}/edit', 'Admin\Controllers\VendorController@edit', 'admin.vendors.edit');
    $router->put('/vendors/{id}', 'Admin\Controllers\VendorController@update', 'admin.vendors.update');
    $router->delete('/vendors/{id}', 'Admin\Controllers\VendorController@destroy', 'admin.vendors.destroy');
    $router->post('/vendors/{id}/sync', 'Admin\Controllers\VendorController@sync', 'admin.vendors.sync');
});

return $router;
