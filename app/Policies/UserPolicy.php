<?php
/**
 * User Policy
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Authorization rules for user management actions.
 * Handles both admin user management and customer management.
 */

declare(strict_types=1);

namespace App\Policies;

class UserPolicy extends Policy
{
    /**
     * Can view admin users list?
     */
    public function viewAdminUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Can view a specific admin user?
     */
    public function viewAdminUser(array $targetUser): bool
    {
        // Must be an admin to view admin users
        if (!$this->isAdmin()) {
            return $this->deny();
        }

        // Regular admin can only view other admins, not super_admins (unless self)
        if (!$this->isSuperAdmin() && $targetUser['role'] === 'super_admin') {
            return $this->owns((int)$targetUser['id']);
        }

        return $this->allow();
    }

    /**
     * Can create new admin users?
     */
    public function createAdminUser(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Can edit a specific user?
     */
    public function edit(array $targetUser): bool
    {
        // Self-edit is always allowed for basic fields
        if ($this->owns((int)$targetUser['id'])) {
            return $this->allow();
        }

        // Only super admin can edit other admin/super_admin users
        if (in_array($targetUser['role'] ?? '', ['admin', 'super_admin'], true)) {
            return $this->isSuperAdmin();
        }

        // Regular admin can edit customers/vendors
        return $this->isAdmin();
    }

    /**
     * Can change a user's role?
     */
    public function changeRole(array $targetUser, string $newRole): bool
    {
        // Must be super admin to change roles
        if (!$this->isSuperAdmin()) {
            return $this->deny();
        }

        // Cannot remove own super_admin role
        if ($this->owns((int)$targetUser['id']) && $newRole !== 'super_admin') {
            return $this->deny();
        }

        // Validate new role is valid
        if (!in_array($newRole, ['customer', 'vendor', 'admin', 'super_admin'], true)) {
            return $this->deny();
        }

        return $this->allow();
    }

    /**
     * Can change a user's status?
     */
    public function changeStatus(array $targetUser, string $newStatus): bool
    {
        // Must be super admin to change admin user status
        if (in_array($targetUser['role'] ?? '', ['admin', 'super_admin'], true)) {
            if (!$this->isSuperAdmin()) {
                return $this->deny();
            }
        } else {
            // Regular admin can change customer/vendor status
            if (!$this->isAdmin()) {
                return $this->deny();
            }
        }

        // Cannot change own status to non-active
        if ($this->owns((int)$targetUser['id']) && $newStatus !== 'active') {
            return $this->deny();
        }

        return $this->allow();
    }

    /**
     * Can delete a user?
     */
    public function delete(array $targetUser): bool
    {
        // Must be super admin to delete admin users
        if (in_array($targetUser['role'] ?? '', ['admin', 'super_admin'], true)) {
            if (!$this->isSuperAdmin()) {
                return $this->deny();
            }
        } else {
            // Regular admin can delete customers/vendors
            if (!$this->isAdmin()) {
                return $this->deny();
            }
        }

        // Cannot delete yourself
        if ($this->owns((int)$targetUser['id'])) {
            return $this->deny();
        }

        // Cannot delete the last super admin
        if ($targetUser['role'] === 'super_admin') {
            if (!$this->canDeleteSuperAdmin()) {
                return $this->deny();
            }
        }

        return $this->allow();
    }

    /**
     * Check if there would be at least one super admin remaining after deletion
     */
    private function canDeleteSuperAdmin(): bool
    {
        $count = db()->query(
            "SELECT COUNT(*) FROM users WHERE role = 'super_admin' AND status = 'active'"
        )->fetchColumn();

        return $count > 1;
    }

    /**
     * Can toggle MFA for a user?
     */
    public function toggleMFA(array $targetUser): bool
    {
        // Can always manage own MFA
        if ($this->owns((int)$targetUser['id'])) {
            return $this->allow();
        }

        // Super admin can disable (not enable) others' MFA
        return $this->isSuperAdmin();
    }

    /**
     * Can force a password reset for a user?
     */
    public function forcePasswordReset(array $targetUser): bool
    {
        // Must be super admin
        if (!$this->isSuperAdmin()) {
            return $this->deny();
        }

        // Cannot force reset own password through admin panel
        if ($this->owns((int)$targetUser['id'])) {
            return $this->deny();
        }

        return $this->allow();
    }

    /**
     * Can terminate sessions for a user?
     */
    public function terminateSessions(array $targetUser): bool
    {
        // Can terminate own sessions
        if ($this->owns((int)$targetUser['id'])) {
            return $this->allow();
        }

        // Super admin can terminate anyone's sessions
        return $this->isSuperAdmin();
    }

    /**
     * Can impersonate a user?
     */
    public function impersonate(array $targetUser): bool
    {
        // Must be super admin
        if (!$this->isSuperAdmin()) {
            return $this->deny();
        }

        // Cannot impersonate yourself
        if ($this->owns((int)$targetUser['id'])) {
            return $this->deny();
        }

        // Cannot impersonate other super admins
        if ($targetUser['role'] === 'super_admin') {
            return $this->deny();
        }

        return $this->allow();
    }

    /**
     * Can view audit log for a user?
     */
    public function viewAuditLog(array $targetUser): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Can perform bulk operations on users?
     */
    public function bulkOperations(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Get the reason why a permission was denied
     */
    public function getDenialReason(string $action, array $targetUser = []): string
    {
        switch ($action) {
            case 'delete':
                if ($this->owns((int)($targetUser['id'] ?? 0))) {
                    return 'You cannot delete your own account.';
                }
                if (($targetUser['role'] ?? '') === 'super_admin' && !$this->canDeleteSuperAdmin()) {
                    return 'Cannot delete the last super admin. At least one super admin must remain.';
                }
                return 'You do not have permission to delete this user.';

            case 'changeRole':
                if ($this->owns((int)($targetUser['id'] ?? 0))) {
                    return 'You cannot change your own role.';
                }
                return 'Only super admins can change user roles.';

            case 'changeStatus':
                if ($this->owns((int)($targetUser['id'] ?? 0))) {
                    return 'You cannot change your own account status.';
                }
                return 'You do not have permission to change this user\'s status.';

            case 'impersonate':
                if ($this->owns((int)($targetUser['id'] ?? 0))) {
                    return 'You cannot impersonate yourself.';
                }
                if (($targetUser['role'] ?? '') === 'super_admin') {
                    return 'You cannot impersonate other super admins.';
                }
                return 'Only super admins can impersonate users.';

            default:
                return 'You do not have permission to perform this action.';
        }
    }
}
