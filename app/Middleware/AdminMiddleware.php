<?php
/**
 * Admin Middleware
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Middleware;

class AdminMiddleware
{
    public function handle(): bool
    {
        // Check authentication
        if (!auth()) {
            if (isAjax()) {
                jsonResponse(['error' => 'Unauthorized'], 401);
                return false;
            }

            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            redirect('/login');
            return false;
        }

        // Check admin role
        if (!isAdmin()) {
            if (isAjax()) {
                jsonResponse(['error' => 'Forbidden'], 403);
                return false;
            }

            // Show 403 page instead of silently redirecting
            $this->show403('You do not have permission to access the admin area.');
            return false;
        }

        // Check IP whitelist
        $whitelist = config('app.security.admin_ip_whitelist', '');
        if (!empty($whitelist)) {
            $allowedIps = array_map('trim', explode(',', $whitelist));
            $clientIp = clientIp();

            if (!in_array($clientIp, $allowedIps, true)) {
                logMessage('warning', 'Admin access denied from IP', ['ip' => $clientIp]);

                if (isAjax()) {
                    jsonResponse(['error' => 'Access denied'], 403);
                    return false;
                }

                $this->show403('Your IP address is not authorized to access this area.');
                return false;
            }
        }

        return true;
    }

    /**
     * Show 403 error page
     */
    private function show403(string $message = ''): void
    {
        http_response_code(403);

        // Try to load the 403 view
        $viewPath = APP_PATH . '/Views/errors/403.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            // Fallback
            echo '<!DOCTYPE html><html><head><title>403 Forbidden</title></head>';
            echo '<body><h1>403 - Access Denied</h1>';
            echo '<p>' . ($message ?: 'You do not have permission to access this page.') . '</p>';
            echo '<p><a href="/">Go Home</a></p></body></html>';
        }
        exit;
    }
}
