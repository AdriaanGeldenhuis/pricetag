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
    $router->post('/products', 'Admin\Controllers\ProductController@store', 'admin.products.store');
    $router->get('/products/{id}/edit', 'Admin\Controllers\ProductController@edit', 'admin.products.edit');
    $router->put('/products/{id}', 'Admin\Controllers\ProductController@update', 'admin.products.update');
    $router->delete('/products/{id}', 'Admin\Controllers\ProductController@destroy', 'admin.products.destroy');

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

    // Orders
    $router->get('/orders', 'Admin\Controllers\OrderController@index', 'admin.orders.index');
    $router->get('/orders/{id}', 'Admin\Controllers\OrderController@show', 'admin.orders.show');
    $router->post('/orders/{id}/status', 'Admin\Controllers\OrderController@updateStatus', 'admin.orders.status');
    $router->post('/orders/{id}/note', 'Admin\Controllers\OrderController@addNote', 'admin.orders.note');

    // Customers
    $router->get('/customers', 'Admin\Controllers\CustomerController@index', 'admin.customers.index');
    $router->get('/customers/{id}', 'Admin\Controllers\CustomerController@show', 'admin.customers.show');
    $router->get('/customers/{id}/edit', 'Admin\Controllers\CustomerController@edit', 'admin.customers.edit');
    $router->put('/customers/{id}', 'Admin\Controllers\CustomerController@update', 'admin.customers.update');

    // Users (Admin)
    $router->get('/users', 'Admin\Controllers\UserController@index', 'admin.users.index');
    $router->get('/users/{id}', 'Admin\Controllers\UserController@show', 'admin.users.show');
    $router->put('/users/{id}', 'Admin\Controllers\UserController@update', 'admin.users.update');

    // Settings
    $router->get('/settings', 'Admin\Controllers\SettingsController@index', 'admin.settings.index');
    $router->post('/settings', 'Admin\Controllers\SettingsController@update', 'admin.settings.update');

    // Menu Builder
    $router->get('/menus', 'Admin\Controllers\MenuController@index', 'admin.menus.index');
    $router->post('/menus', 'Admin\Controllers\MenuController@store', 'admin.menus.store');
    $router->put('/menus/{id}', 'Admin\Controllers\MenuController@update', 'admin.menus.update');
    $router->delete('/menus/{id}', 'Admin\Controllers\MenuController@destroy', 'admin.menus.destroy');

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

    // Reports
    $router->get('/reports', 'Admin\Controllers\ReportController@index', 'admin.reports.index');
    $router->get('/reports/sales', 'Admin\Controllers\ReportController@sales', 'admin.reports.sales');
    $router->get('/reports/products', 'Admin\Controllers\ReportController@products', 'admin.reports.products');
});

return $router;
