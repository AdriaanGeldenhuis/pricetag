<?php
/**
 * CSRF Protection Middleware
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Middleware;

class CsrfMiddleware
{
    private array $excludedPaths = [
        '/checkout/webhook',
        '/api/',
    ];

    public function handle(): bool
    {
        // Skip for safe methods
        if (in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        // Skip for excluded paths
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        foreach ($this->excludedPaths as $excluded) {
            if (strpos($path, $excluded) === 0) {
                return true;
            }
        }

        // Verify token
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!verifyCsrfToken($token)) {
            logMessage('warning', 'CSRF token validation failed', [
                'ip' => clientIp(),
                'path' => $path,
            ]);

            if (isAjax()) {
                jsonResponse(['error' => 'Invalid CSRF token'], 403);
            }

            flash('error', 'Session expired. Please try again.');
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
            return false;
        }

        return true;
    }
}
