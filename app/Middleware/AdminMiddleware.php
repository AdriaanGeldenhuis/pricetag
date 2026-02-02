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
            }

            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            redirect('/login');
            return false;
        }

        // Check admin role
        if (!isAdmin()) {
            if (isAjax()) {
                jsonResponse(['error' => 'Forbidden'], 403);
            }

            redirect('/');
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
                }

                http_response_code(403);
                exit('Access denied');
            }
        }

        return true;
    }
}
