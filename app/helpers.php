<?php
/**
 * Helper Functions
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

declare(strict_types=1);

/**
 * Load environment variables from .env file
 */
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Remove quotes
        if (preg_match('/^(["\']).*\1$/', $value)) {
            $value = substr($value, 1, -1);
        }

        // Handle variable substitution
        $value = preg_replace_callback('/\$\{([^}]+)\}/', function ($matches) {
            return $_ENV[$matches[1]] ?? getenv($matches[1]) ?: '';
        }, $value);

        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

/**
 * Get environment variable
 */
function env(string $key, $default = null)
{
    $value = $_ENV[$key] ?? getenv($key);

    if ($value === false) {
        return $default;
    }

    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'null':
        case '(null)':
            return null;
        case 'empty':
        case '(empty)':
            return '';
    }

    return $value;
}

/**
 * Get configuration value using dot notation
 */
function config(string $key, $default = null)
{
    $keys = explode('.', $key);
    $value = $GLOBALS['config'] ?? [];

    foreach ($keys as $k) {
        if (!is_array($value) || !array_key_exists($k, $value)) {
            return $default;
        }
        $value = $value[$k];
    }

    return $value;
}

/**
 * Generate URL
 */
function url(string $path = ''): string
{
    $base = rtrim(config('app.url', ''), '/');
    $path = ltrim($path, '/');
    return $path ? "$base/$path" : $base;
}

/**
 * Generate asset URL (CDN-aware)
 */
function asset(string $path): string
{
    $path = ltrim($path, '/');

    if (env('CDN_ENABLED', false) && env('CDN_URL')) {
        return rtrim(env('CDN_URL'), '/') . '/' . $path;
    }

    return url("assets/$path");
}

/**
 * Escape HTML
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Get CSRF token
 */
function csrfToken(): string
{
    return $_SESSION['csrf_token'] ?? '';
}

/**
 * Alias for csrfToken (snake_case version used in views)
 */
function csrf_token(): string
{
    return csrfToken();
}

/**
 * Generate CSRF field
 */
function csrfField(): string
{
    return '<input type="hidden" name="_token" value="' . csrfToken() . '">';
}

/**
 * Alias for csrfField (snake_case version used in views)
 */
function csrf_field(): string
{
    return csrfField();
}

/**
 * Regenerate CSRF token
 */
function regenerateCsrfToken(): void
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_time'] = time();
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Redirect
 */
function redirect(string $url, int $status = 302): void
{
    header("Location: $url", true, $status);
    exit;
}

/**
 * JSON response
 */
function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_THROW_ON_ERROR);
    exit;
}

/**
 * Format price
 */
function formatPrice(float $amount): string
{
    $config = config('payment.currency', []);
    $symbol = $config['symbol'] ?? 'R';
    $decimals = $config['decimal_places'] ?? 2;
    $decSep = $config['decimal_separator'] ?? '.';
    $thousandsSep = $config['thousands_separator'] ?? ' ';
    $position = $config['symbol_position'] ?? 'before';

    $formatted = number_format($amount, $decimals, $decSep, $thousandsSep);

    return $position === 'before' ? "$symbol$formatted" : "$formatted$symbol";
}

/**
 * Generate slug
 */
function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    return $text ?: 'n-a';
}

/**
 * Truncate text
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length - mb_strlen($suffix)) . $suffix;
}

/**
 * Get old form input
 */
function old(string $key, $default = ''): mixed
{
    return $_SESSION['_old_input'][$key] ?? $default;
}

/**
 * Flash message
 */
function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

/**
 * Check if user is logged in
 */
function auth(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Get current user
 */
function user(bool $fresh = false): ?array
{
    if (!auth()) {
        return null;
    }

    if ($fresh || !isset($_SESSION['_user_cache'])) {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
        $stmt->execute([$_SESSION['user_id']]);
        $_SESSION['_user_cache'] = $stmt->fetch() ?: null;
    }

    return $_SESSION['_user_cache'];
}

/**
 * Clear user cache (call after updating user data)
 */
function clearUserCache(): void
{
    unset($_SESSION['_user_cache']);
}

/**
 * Check user role
 */
function hasRole(string $role): bool
{
    $user = user();
    return $user && $user['role'] === $role;
}

/**
 * Check if user is admin (always fetches fresh data)
 */
function isAdmin(): bool
{
    $user = user(true); // Always get fresh data for admin check
    return $user && in_array($user['role'] ?? '', ['admin', 'super_admin'], true);
}

/**
 * Get cart count
 */
function cartCount(): int
{
    return $_SESSION['cart_count'] ?? 0;
}

/**
 * Get wishlist count
 */
function wishlistCount(): int
{
    return $_SESSION['wishlist_count'] ?? 0;
}

/**
 * Log message
 */
function logMessage(string $level, string $message, array $context = []): void
{
    $logFile = STORAGE_PATH . '/logs/app.log';
    $formatted = sprintf(
        "[%s] %s: %s %s\n",
        date('Y-m-d H:i:s'),
        strtoupper($level),
        $message,
        $context ? json_encode($context) : ''
    );

    error_log($formatted, 3, $logFile);
}

/**
 * Check if request is AJAX
 */
function isAjax(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP
 */
function clientIp(): string
{
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return '0.0.0.0';
}

/**
 * Generate unique order number
 */
function generateOrderNumber(): string
{
    return 'PT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Generate invoice number
 */
function generateInvoiceNumber(): string
{
    $db = \App\Core\Database::getInstance();
    $stmt = $db->query("SELECT MAX(invoice_number) as max_num FROM orders WHERE invoice_number IS NOT NULL");
    $result = $stmt->fetch();

    $lastNum = $result['max_num'] ?? 'INV-00000';
    $num = (int) substr($lastNum, 4) + 1;

    return 'INV-' . str_pad((string) $num, 5, '0', STR_PAD_LEFT);
}

// ============================================================================
// CACHE HELPERS
// ============================================================================

/**
 * Get cache instance
 */
function cache(): \App\Services\Cache
{
    return \App\Services\Cache::getInstance();
}

/**
 * Get value from cache
 */
function cacheGet(string $key, mixed $default = null): mixed
{
    return cache()->get($key, $default);
}

/**
 * Store value in cache
 */
function cachePut(string $key, mixed $value, ?int $ttl = null): bool
{
    return cache()->put($key, $value, $ttl);
}

/**
 * Remember value (get from cache or execute callback and store)
 */
function cacheRemember(string $key, int $ttl, callable $callback): mixed
{
    return cache()->remember($key, $ttl, $callback);
}

/**
 * Clear specific cache key
 */
function cacheForget(string $key): bool
{
    return cache()->forget($key);
}

/**
 * Clear all cache
 */
function cacheFlush(): bool
{
    return cache()->flush();
}

/**
 * Check if current path matches given path
 */
function isCurrentPath(string $path, bool $exact = false): bool
{
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $currentPath = rtrim($currentPath, '/') ?: '/';
    $path = rtrim($path, '/') ?: '/';

    if ($exact) {
        return $currentPath === $path;
    }

    // Check if current path starts with given path (for nested routes)
    if ($path === '/') {
        return $currentPath === '/';
    }

    return $currentPath === $path || str_starts_with($currentPath, $path . '/');
}

/**
 * Get relative time (e.g., "2 hours ago")
 */
function timeAgo(string $datetime): string
{
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($diff / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}

/**
 * Format date for display
 */
function formatDate(string $datetime, string $format = 'd M Y'): string
{
    return date($format, strtotime($datetime));
}

/**
 * Format datetime for display
 */
function formatDateTime(string $datetime, string $format = 'd M Y, H:i'): string
{
    return date($format, strtotime($datetime));
}

// ============================================================================
// BRANDING HELPERS
// ============================================================================

/**
 * Get a setting from the database
 */
function getSetting(string $key, string $group = 'general', mixed $default = null): mixed
{
    static $cache = [];
    $cacheKey = "{$group}.{$key}";

    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    try {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT `value`, `type` FROM settings WHERE `group` = ? AND `key` = ? LIMIT 1");
        $stmt->execute([$group, $key]);
        $result = $stmt->fetch();

        if (!$result) {
            return $default;
        }

        $value = $result['value'];

        // Cast based on type
        switch ($result['type']) {
            case 'integer':
                $value = (int) $value;
                break;
            case 'boolean':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'json':
                $value = json_decode($value, true) ?? $default;
                break;
        }

        $cache[$cacheKey] = $value;
        return $value;
    } catch (\Exception $e) {
        return $default;
    }
}

/**
 * Get all branding settings with defaults
 */
function getBranding(): array
{
    static $branding = null;

    if ($branding !== null) {
        return $branding;
    }

    $branding = [
        'logo' => getSetting('logo', 'branding'),
        'logo_height' => getSetting('logo_height', 'branding', '50'),
        'favicon' => getSetting('favicon', 'branding'),
        'header_bg' => getSetting('header_bg', 'branding'),
        'primary_color' => getSetting('primary_color', 'branding', '#2563eb'),
        'secondary_color' => getSetting('secondary_color', 'branding', '#1e40af'),
        'accent_color' => getSetting('accent_color', 'branding', '#f59e0b'),
        'font_family' => getSetting('font_family', 'branding', 'Inter'),
    ];

    return $branding;
}

/**
 * Get all appearance settings with defaults
 */
function getAppearance(): array
{
    static $appearance = null;

    if ($appearance !== null) {
        return $appearance;
    }

    $appearance = [
        // Announcement Bar
        'announcement_enabled' => getSetting('announcement_enabled', 'appearance', '0'),
        'announcement_text' => getSetting('announcement_text', 'appearance', ''),
        'announcement_link' => getSetting('announcement_link', 'appearance', ''),
        'announcement_bg_color' => getSetting('announcement_bg_color', 'appearance', '#1e40af'),
        'announcement_text_color' => getSetting('announcement_text_color', 'appearance', '#ffffff'),
        // WhatsApp Widget
        'whatsapp_enabled' => getSetting('whatsapp_enabled', 'appearance', '0'),
        'whatsapp_number' => getSetting('whatsapp_number', 'appearance', ''),
        'whatsapp_message' => getSetting('whatsapp_message', 'appearance', 'Hi! I have a question about your products.'),
        'whatsapp_position' => getSetting('whatsapp_position', 'appearance', 'bottom-right'),
        // Trust Badges
        'trust_badges_enabled' => getSetting('trust_badges_enabled', 'appearance', '1'),
        'trust_badge_1_title' => getSetting('trust_badge_1_title', 'appearance', 'Free Delivery'),
        'trust_badge_1_subtitle' => getSetting('trust_badge_1_subtitle', 'appearance', 'Orders over R500'),
        'trust_badge_2_title' => getSetting('trust_badge_2_title', 'appearance', 'Secure Payment'),
        'trust_badge_2_subtitle' => getSetting('trust_badge_2_subtitle', 'appearance', '100% Protected'),
        'trust_badge_3_title' => getSetting('trust_badge_3_title', 'appearance', 'Quality Products'),
        'trust_badge_3_subtitle' => getSetting('trust_badge_3_subtitle', 'appearance', 'Best brands only'),
        'trust_badge_4_title' => getSetting('trust_badge_4_title', 'appearance', '24/7 Support'),
        'trust_badge_4_subtitle' => getSetting('trust_badge_4_subtitle', 'appearance', 'Always here to help'),
        // Footer
        'footer_description' => getSetting('footer_description', 'appearance', 'Your trusted destination for premium products at competitive prices. Fast delivery across South Africa.'),
        'footer_email' => getSetting('footer_email', 'appearance', 'info@pricetag.co.za'),
        'footer_phone' => getSetting('footer_phone', 'appearance', '+27 (0) 10 000 0000'),
        'footer_hours' => getSetting('footer_hours', 'appearance', 'Mon - Fri: 8am - 5pm'),
        'footer_copyright' => getSetting('footer_copyright', 'appearance', ''),
        // Cookie Consent
        'cookie_consent_enabled' => getSetting('cookie_consent_enabled', 'appearance', '0'),
        'cookie_consent_text' => getSetting('cookie_consent_text', 'appearance', 'We use cookies to enhance your browsing experience.'),
        'cookie_policy_link' => getSetting('cookie_policy_link', 'appearance', '/page/privacy'),
        // Back to Top
        'back_to_top_enabled' => getSetting('back_to_top_enabled', 'appearance', '1'),
    ];

    return $appearance;
}

/**
 * Get active banners for a specific location
 */
function getBanners(string $location): array
{
    static $cache = [];

    if (isset($cache[$location])) {
        return $cache[$location];
    }

    try {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("
            SELECT * FROM banners
            WHERE location = ? AND is_active = 1
            AND (starts_at IS NULL OR starts_at <= NOW())
            AND (expires_at IS NULL OR expires_at > NOW())
            ORDER BY sort_order
        ");
        $stmt->execute([$location]);
        $cache[$location] = $stmt->fetchAll() ?: [];
    } catch (\Throwable $e) {
        $cache[$location] = [];
    }

    return $cache[$location];
}
