<?php
/**
 * Authentication Middleware
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Middleware;

use App\Services\SessionManager;

class AuthMiddleware
{
    public function handle(): bool
    {
        if (!auth()) {
            if (isAjax()) {
                jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            redirect('/login');
            return false;
        }

        // Validate session fingerprint for additional security
        $sessionManager = new SessionManager();

        if (!$sessionManager->validateFingerprint()) {
            // Fingerprint mismatch - possible session hijacking
            logMessage('warning', 'Session fingerprint mismatch', [
                'user_id' => $_SESSION['user_id'],
                'ip' => clientIp(),
            ]);

            // Destroy session and require re-login
            session_destroy();

            if (isAjax()) {
                jsonResponse(['error' => 'Session expired. Please login again.'], 401);
            }

            redirect('/login?session=expired');
            return false;
        }

        // Update session activity
        $sessionManager->trackSession($_SESSION['user_id']);

        return true;
    }
}
