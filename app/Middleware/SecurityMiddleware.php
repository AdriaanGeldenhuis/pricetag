<?php
declare(strict_types=1);

/**
 * Security Middleware
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Applies security headers, rate limiting, and protection measures.
 */

namespace App\Middleware;

class SecurityMiddleware
{
    private array $config;

    public function __construct()
    {
        $this->config = config('security', []);
    }

    /**
     * Handle the request
     */
    public function handle(): bool
    {
        // Set security headers
        $this->setSecurityHeaders();

        // Check IP blacklist
        if (!$this->checkIPAllowed()) {
            http_response_code(403);
            exit('Access denied');
        }

        // Apply rate limiting
        if (!$this->checkRateLimit()) {
            http_response_code(429);
            header('Retry-After: 60');
            exit('Too many requests');
        }

        // Configure session security
        $this->configureSession();

        return true;
    }

    /**
     * Set security headers
     */
    private function setSecurityHeaders(): void
    {
        // Basic security headers
        foreach ($this->config['headers'] ?? [] as $header => $value) {
            header("{$header}: {$value}");
        }

        // HSTS header (only on HTTPS)
        if (($this->config['hsts']['enabled'] ?? false) && $this->isSecure()) {
            $hsts = 'max-age=' . ($this->config['hsts']['max_age'] ?? 31536000);
            if ($this->config['hsts']['include_subdomains'] ?? false) {
                $hsts .= '; includeSubDomains';
            }
            if ($this->config['hsts']['preload'] ?? false) {
                $hsts .= '; preload';
            }
            header("Strict-Transport-Security: {$hsts}");
        }

        // Remove sensitive headers
        header_remove('X-Powered-By');
        header_remove('Server');
    }

    /**
     * Check if IP is allowed
     */
    private function checkIPAllowed(): bool
    {
        $clientIP = $this->getClientIP();

        // Check blacklist
        $blacklist = $this->config['ip']['blacklist'] ?? [];
        if (in_array($clientIP, $blacklist)) {
            logMessage('warning', 'Blocked IP attempted access', ['ip' => $clientIP]);
            return false;
        }

        // Check admin whitelist for admin routes
        if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin')) {
            $whitelist = $this->config['ip']['admin_whitelist'] ?? [];
            if (!empty($whitelist) && !in_array($clientIP, $whitelist)) {
                logMessage('warning', 'Unauthorized admin access attempt', ['ip' => $clientIP]);
                return false;
            }
        }

        return true;
    }

    /**
     * Check rate limits
     */
    private function checkRateLimit(): bool
    {
        $clientIP = $this->getClientIP();
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Determine rate limit type
        $limitConfig = $this->config['rate_limits']['global'] ?? ['requests' => 1000, 'window' => 60];

        // Check specific limits
        foreach (['auth', 'api', 'search', 'cart'] as $type) {
            $typeConfig = $this->config['rate_limits'][$type] ?? null;
            if ($typeConfig) {
                $endpoints = $typeConfig['endpoints'] ?? ["/{$type}"];
                foreach ($endpoints as $endpoint) {
                    if (str_starts_with($path, $endpoint) || str_contains($path, $endpoint)) {
                        $limitConfig = $typeConfig;
                        break 2;
                    }
                }
            }
        }

        $key = 'rate_' . md5($clientIP . '_' . $method);
        $maxRequests = $limitConfig['requests'] ?? 1000;
        $window = $limitConfig['window'] ?? 60;

        return $this->checkLimit($key, $maxRequests, $window);
    }

    /**
     * Check and update rate limit counter
     */
    private function checkLimit(string $key, int $maxRequests, int $window): bool
    {
        // Use session-based rate limiting (consider Redis/Memcached for production)
        if (!isset($_SESSION['rate_limits'])) {
            $_SESSION['rate_limits'] = [];
        }

        $now = time();
        $windowStart = $now - $window;

        // Get existing requests in window
        $requests = $_SESSION['rate_limits'][$key] ?? [];

        // Filter to only requests within window
        $requests = array_filter($requests, fn($time) => $time > $windowStart);

        // Check if limit exceeded
        if (count($requests) >= $maxRequests) {
            logMessage('warning', 'Rate limit exceeded', [
                'key' => $key,
                'requests' => count($requests),
                'limit' => $maxRequests,
            ]);
            return false;
        }

        // Add current request
        $requests[] = $now;
        $_SESSION['rate_limits'][$key] = $requests;

        // Add rate limit headers
        header("X-RateLimit-Limit: {$maxRequests}");
        header("X-RateLimit-Remaining: " . ($maxRequests - count($requests)));
        header("X-RateLimit-Reset: " . ($now + $window));

        return true;
    }

    /**
     * Configure session security
     */
    private function configureSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return; // Already started
        }

        $sessionConfig = $this->config['session'] ?? [];

        ini_set('session.cookie_httponly', $sessionConfig['cookie_httponly'] ?? true ? '1' : '0');
        ini_set('session.cookie_samesite', $sessionConfig['cookie_samesite'] ?? 'Lax');
        ini_set('session.gc_maxlifetime', (string) ($sessionConfig['gc_maxlifetime'] ?? 86400));

        if ($this->isSecure() && ($sessionConfig['cookie_secure'] ?? false)) {
            ini_set('session.cookie_secure', '1');
        }
    }

    /**
     * Get real client IP (handling proxies)
     */
    private function getClientIP(): string
    {
        $trustedProxies = array_filter(explode(',', $this->config['ip']['trusted_proxies'] ?? ''));

        // Check forwarded headers if behind proxy
        if (!empty($trustedProxies)) {
            $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
            if (in_array($remoteAddr, $trustedProxies)) {
                // Check X-Forwarded-For
                if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ips = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
                    return $ips[0];
                }

                // Check X-Real-IP
                if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
                    return $_SERVER['HTTP_X_REAL_IP'];
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Check if request is over HTTPS
     */
    private function isSecure(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }

        return false;
    }

    /**
     * Validate CSRF token
     */
    public static function validateCSRF(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return true;
        }

        $config = config('security.csrf', []);
        if (!($config['enabled'] ?? true)) {
            return true;
        }

        // Check excluded paths
        $path = $_SERVER['REQUEST_URI'] ?? '';
        foreach ($config['exclude'] ?? [] as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }

        $tokenName = $config['token_name'] ?? 'csrf_token';
        $submittedToken = $_POST[$tokenName] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $sessionToken = $_SESSION[$tokenName] ?? '';

        if (empty($submittedToken) || !hash_equals($sessionToken, $submittedToken)) {
            logMessage('warning', 'CSRF token validation failed', [
                'path' => $path,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);
            return false;
        }

        // Regenerate token if configured
        if ($config['regenerate'] ?? true) {
            $_SESSION[$tokenName] = bin2hex(random_bytes($config['token_length'] ?? 32));
        }

        return true;
    }

    /**
     * Log authentication attempt
     */
    public static function logAuthAttempt(string $email, bool $success, ?int $userId = null): void
    {
        if (!config('security.audit.log_auth', true)) {
            return;
        }

        $db = db();

        try {
            $db->query("
                INSERT INTO auth_logs (email, user_id, success, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ", [
                $email,
                $userId,
                $success ? 1 : 0,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]);
        } catch (\Exception $e) {
            // Table might not exist, log to file
            logMessage('info', 'Auth attempt', [
                'email' => $email,
                'success' => $success,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);
        }
    }

    /**
     * Check if account is locked
     */
    public static function isAccountLocked(string $email): bool
    {
        $config = config('security.login', []);
        $maxAttempts = $config['max_attempts'] ?? 5;
        $lockoutDuration = $config['lockout_duration'] ?? 900;

        $db = db();

        try {
            $count = $db->query("
                SELECT COUNT(*) FROM auth_logs
                WHERE email = ?
                AND success = 0
                AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
            ", [$email, $lockoutDuration])->fetchColumn();

            return (int) $count >= $maxAttempts;
        } catch (\Exception $e) {
            return false;
        }
    }
}
