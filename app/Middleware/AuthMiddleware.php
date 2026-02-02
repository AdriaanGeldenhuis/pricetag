<?php
/**
 * Authentication Middleware
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Middleware;

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

        return true;
    }
}
