<?php
/**
 * Base Policy Class
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Policies determine if a user is authorized to perform a given action.
 */

declare(strict_types=1);

namespace App\Policies;

abstract class Policy
{
    /**
     * Current user data
     */
    protected ?array $user;

    public function __construct(?array $user = null)
    {
        $this->user = $user ?? user();
    }

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool
    {
        return $this->user !== null;
    }

    /**
     * Check if user has specific role
     */
    protected function hasRole(string $role): bool
    {
        return $this->user && ($this->user['role'] ?? null) === $role;
    }

    /**
     * Check if user is admin
     */
    protected function isAdmin(): bool
    {
        return $this->user && in_array($this->user['role'] ?? '', ['admin', 'super_admin'], true);
    }

    /**
     * Check if user is super admin
     */
    protected function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user owns a resource
     */
    protected function owns(int $userId): bool
    {
        return $this->user && ($this->user['id'] ?? 0) === $userId;
    }

    /**
     * Deny access
     */
    protected function deny(): bool
    {
        return false;
    }

    /**
     * Allow access
     */
    protected function allow(): bool
    {
        return true;
    }
}
