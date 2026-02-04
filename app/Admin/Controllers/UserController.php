<?php
declare(strict_types=1);

/**
 * Admin User Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Manages admin users and their permissions.
 * Includes enhanced security features:
 * - Policy-based authorization
 * - Role change history tracking
 * - Comprehensive audit logging
 * - Last super admin protection
 * - User impersonation
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Policies\UserPolicy;

class UserController extends Controller
{
    private UserPolicy $policy;

    public function __construct()
    {
        $this->policy = new UserPolicy(user());
    }

    public function index(): void
    {
        $db = db();

        // Get filter parameters
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';

        // Build query - only admin users
        $where = ["role IN ('admin', 'super_admin')"];
        $params = [];

        if ($search) {
            $where[] = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($role && in_array($role, ['admin', 'super_admin'])) {
            $where[] = 'role = ?';
            $params[] = $role;
        }

        if ($status && in_array($status, ['active', 'pending', 'suspended'])) {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $total = (int) $db->query("SELECT COUNT(*) FROM users WHERE {$whereClause}", $params)->fetchColumn();
        $totalPages = (int) ceil($total / $perPage);

        // Get users
        $offset = ($page - 1) * $perPage;
        $users = $db->query("
            SELECT id, email, first_name, last_name, phone, role, status,
                   avatar, mfa_enabled, last_login_at, created_at
            FROM users
            WHERE {$whereClause}
            ORDER BY created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ", $params)->fetchAll();

        // Get role counts
        $roleCounts = $db->query("
            SELECT role, COUNT(*) as count
            FROM users
            WHERE role IN ('admin', 'super_admin')
            GROUP BY role
        ")->fetchAll(\PDO::FETCH_KEY_PAIR);

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/users/index', [
            'title' => 'Admin Users',
            'users' => $users,
            'roleCounts' => $roleCounts,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'count' => $total,
                'perPage' => $perPage,
            ],
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status,
            ],
        ]);
    }

    public function create(): void
    {
        // Check permission
        if (!$this->policy->createAdminUser()) {
            flash('error', 'You do not have permission to create admin users.');
            $this->redirect('/admin/users');
            return;
        }

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/users/form', [
            'title' => 'Add Admin User',
            'user' => null,
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        // Check permission
        if (!$this->policy->createAdminUser()) {
            flash('error', 'You do not have permission to create admin users.');
            $this->redirect('/admin/users');
            return;
        }

        $currentUser = user();

        $validation = $this->validate([
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'role' => 'required',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please correct the errors below.');
            $_SESSION['form_errors'] = $validation['errors'];
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/users/create');
            return;
        }

        $db = db();

        // Check email uniqueness
        $existing = $db->query("SELECT id FROM users WHERE email = ?", [strtolower($_POST['email'])])->fetch();
        if ($existing) {
            flash('error', 'An account with this email already exists.');
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/users/create');
            return;
        }

        // Validate role
        if (!in_array($_POST['role'], ['admin', 'super_admin'])) {
            flash('error', 'Invalid role selected.');
            $this->redirect('/admin/users/create');
            return;
        }

        // Check super admin limit (optional security measure)
        if ($_POST['role'] === 'super_admin') {
            $superAdminCount = (int) $db->query(
                "SELECT COUNT(*) FROM users WHERE role = 'super_admin'"
            )->fetchColumn();

            $maxSuperAdmins = config('admin.roles.super_admin.max_users', 10);
            if ($maxSuperAdmins > 0 && $superAdminCount >= $maxSuperAdmins) {
                flash('error', "Maximum number of super admins ({$maxSuperAdmins}) reached.");
                $_SESSION['form_data'] = $_POST;
                $this->redirect('/admin/users/create');
                return;
            }
        }

        $db->beginTransaction();
        try {
            // Create user
            $db->query("
                INSERT INTO users (email, password, first_name, last_name, phone, role, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
            ", [
                strtolower(trim($_POST['email'])),
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                trim($_POST['first_name']),
                trim($_POST['last_name']),
                trim($_POST['phone'] ?? '') ?: null,
                $_POST['role'],
            ]);

            $newUserId = (int) $db->lastInsertId();

            // Log role assignment in role_history
            $this->logRoleChange($newUserId, null, $_POST['role'], $currentUser['id'], 'Initial role assignment on user creation');

            // Log activity
            $this->logActivity($currentUser['id'], 'admin_user_created', "Created admin user: {$_POST['email']} with role: {$_POST['role']}", [
                'target_user_id' => $newUserId,
                'role' => $_POST['role'],
            ]);

            // Notify other super admins if a new super admin was created
            if ($_POST['role'] === 'super_admin') {
                $this->notifySuperAdmins(
                    'New Super Admin Created',
                    "{$currentUser['first_name']} {$currentUser['last_name']} created a new super admin: {$_POST['email']}",
                    $currentUser['id']
                );
            }

            $db->commit();

            flash('success', 'Admin user created successfully.');
            $this->redirect('/admin/users/' . $newUserId);

        } catch (\Exception $e) {
            $db->rollBack();
            logMessage('error', 'Failed to create admin user', ['error' => $e->getMessage()]);
            flash('error', 'Failed to create user. Please try again.');
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/users/create');
        }
    }

    public function show(int $id): void
    {
        $db = db();

        $targetUser = $db->query("
            SELECT id, email, first_name, last_name, phone, role, status,
                   avatar, mfa_enabled, email_verified_at, last_login_at, last_login_ip,
                   password_changed_at, created_at, updated_at
            FROM users
            WHERE id = ? AND role IN ('admin', 'super_admin')
        ", [$id])->fetch();

        if (!$targetUser) {
            flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        // Check view permission
        if (!$this->policy->viewAdminUser($targetUser)) {
            flash('error', 'You do not have permission to view this user.');
            $this->redirect('/admin/users');
            return;
        }

        // Get recent activity
        $activity = $db->query("
            SELECT type, description, ip_address, created_at
            FROM activity_logs
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 20
        ", [$id])->fetchAll();

        // Get login history
        $loginHistory = $db->query("
            SELECT ip_address, user_agent, success, created_at
            FROM login_attempts
            WHERE email = ?
            ORDER BY created_at DESC
            LIMIT 10
        ", [$targetUser['email']])->fetchAll();

        // Get active sessions
        $sessions = $db->query("
            SELECT id, ip_address, user_agent, last_activity, created_at
            FROM user_sessions
            WHERE user_id = ?
            ORDER BY last_activity DESC
        ", [$id])->fetchAll();

        // Get role history (for super admins)
        $roleHistory = [];
        if ($this->policy->isSuperAdmin()) {
            $roleHistory = $db->query("
                SELECT rh.*, u.first_name as changer_first_name, u.last_name as changer_last_name
                FROM role_history rh
                LEFT JOIN users u ON rh.changed_by = u.id
                WHERE rh.user_id = ?
                ORDER BY rh.created_at DESC
                LIMIT 10
            ", [$id])->fetchAll();
        }

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/users/show', [
            'title' => $targetUser['first_name'] . ' ' . $targetUser['last_name'],
            'user' => $targetUser,
            'activity' => $activity,
            'loginHistory' => $loginHistory,
            'sessions' => $sessions,
            'roleHistory' => $roleHistory,
        ]);
    }

    public function edit(int $id): void
    {
        $db = db();

        $targetUser = $db->query("
            SELECT * FROM users
            WHERE id = ? AND role IN ('admin', 'super_admin')
        ", [$id])->fetch();

        if (!$targetUser) {
            flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        // Check edit permission
        if (!$this->policy->edit($targetUser)) {
            flash('error', 'You do not have permission to edit this user.');
            $this->redirect('/admin/users');
            return;
        }

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/users/form', [
            'title' => 'Edit Admin User',
            'user' => $targetUser,
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();
        $currentUser = user();

        $targetUser = $db->query("SELECT * FROM users WHERE id = ? AND role IN ('admin', 'super_admin')", [$id])->fetch();

        if (!$targetUser) {
            flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        // Check edit permission
        if (!$this->policy->edit($targetUser)) {
            flash('error', 'You do not have permission to edit this user.');
            $this->redirect('/admin/users');
            return;
        }

        $validation = $this->validate([
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please correct the errors below.');
            $_SESSION['form_errors'] = $validation['errors'];
            $this->redirect('/admin/users/' . $id . '/edit');
            return;
        }

        // Check email uniqueness (excluding current user)
        $existing = $db->query(
            "SELECT id FROM users WHERE email = ? AND id != ?",
            [strtolower($_POST['email']), $id]
        )->fetch();

        if ($existing) {
            flash('error', 'An account with this email already exists.');
            $this->redirect('/admin/users/' . $id . '/edit');
            return;
        }

        $db->beginTransaction();
        try {
            // Build update query
            $updates = [
                'first_name = ?',
                'last_name = ?',
                'email = ?',
                'phone = ?',
                'updated_at = NOW()',
            ];
            $params = [
                trim($_POST['first_name']),
                trim($_POST['last_name']),
                strtolower(trim($_POST['email'])),
                trim($_POST['phone'] ?? '') ?: null,
            ];

            // Handle role change (super admin only)
            if (isset($_POST['role']) && in_array($_POST['role'], ['admin', 'super_admin'])) {
                $newRole = $_POST['role'];

                if ($newRole !== $targetUser['role']) {
                    // Check permission to change role
                    if (!$this->policy->changeRole($targetUser, $newRole)) {
                        $db->rollBack();
                        flash('error', $this->policy->getDenialReason('changeRole', $targetUser));
                        $this->redirect('/admin/users/' . $id . '/edit');
                        return;
                    }

                    $updates[] = 'role = ?';
                    $params[] = $newRole;

                    // Log role change
                    $this->logRoleChange($id, $targetUser['role'], $newRole, $currentUser['id'], $_POST['role_change_reason'] ?? null);

                    // Notify if super admin role changed
                    if ($targetUser['role'] === 'super_admin' || $newRole === 'super_admin') {
                        $this->notifySuperAdmins(
                            'Super Admin Role Changed',
                            "{$currentUser['first_name']} {$currentUser['last_name']} changed {$targetUser['email']} from {$targetUser['role']} to {$newRole}",
                            $currentUser['id']
                        );
                    }
                }
            }

            // Handle status change (super admin only)
            if (isset($_POST['status']) && in_array($_POST['status'], ['active', 'pending', 'suspended'])) {
                $newStatus = $_POST['status'];

                if ($newStatus !== $targetUser['status']) {
                    // Check permission to change status
                    if (!$this->policy->changeStatus($targetUser, $newStatus)) {
                        $db->rollBack();
                        flash('error', $this->policy->getDenialReason('changeStatus', $targetUser));
                        $this->redirect('/admin/users/' . $id . '/edit');
                        return;
                    }

                    $updates[] = 'status = ?';
                    $params[] = $newStatus;

                    // If suspending, terminate all sessions
                    if ($newStatus === 'suspended') {
                        $db->query("DELETE FROM user_sessions WHERE user_id = ?", [$id]);
                    }
                }
            }

            // Handle password update
            if (!empty($_POST['password'])) {
                if (strlen($_POST['password']) < 8) {
                    $db->rollBack();
                    flash('error', 'Password must be at least 8 characters.');
                    $this->redirect('/admin/users/' . $id . '/edit');
                    return;
                }
                $updates[] = 'password = ?';
                $updates[] = 'password_changed_at = NOW()';
                $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);

                // Terminate sessions on password change if not self
                if ($currentUser['id'] !== $id) {
                    $db->query("DELETE FROM user_sessions WHERE user_id = ?", [$id]);
                }
            }

            $params[] = $id;
            $updateClause = implode(', ', $updates);

            $db->query("UPDATE users SET {$updateClause} WHERE id = ?", $params);

            // Log activity
            $this->logActivity($currentUser['id'], 'admin_user_updated', "Updated admin user: {$targetUser['email']}", [
                'target_user_id' => $id,
            ]);

            $db->commit();

            flash('success', 'User updated successfully.');
            $this->redirect('/admin/users/' . $id);

        } catch (\Exception $e) {
            $db->rollBack();
            logMessage('error', 'Failed to update user', ['error' => $e->getMessage()]);
            flash('error', 'Failed to update user. Please try again.');
            $this->redirect('/admin/users/' . $id . '/edit');
        }
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $currentUser = user();
        $db = db();

        $targetUser = $db->query("SELECT * FROM users WHERE id = ? AND role IN ('admin', 'super_admin')", [$id])->fetch();

        if (!$targetUser) {
            $this->respondError('User not found.', 404);
            return;
        }

        // Check delete permission (includes last super admin check)
        if (!$this->policy->delete($targetUser)) {
            $this->respondError($this->policy->getDenialReason('delete', $targetUser), 403);
            return;
        }

        // Require explicit confirmation for super admin deletion
        if ($targetUser['role'] === 'super_admin') {
            $confirmParam = $_POST['confirm_super_admin_delete'] ?? $_GET['confirm'] ?? false;
            if (!$confirmParam) {
                $this->respondError('Super admin deletion requires explicit confirmation. Add confirm_super_admin_delete=1 to confirm.', 400);
                return;
            }
        }

        $db->beginTransaction();
        try {
            // Soft delete - change status to deleted
            $db->query("UPDATE users SET status = 'deleted', updated_at = NOW() WHERE id = ?", [$id]);

            // Terminate all sessions
            $db->query("DELETE FROM user_sessions WHERE user_id = ?", [$id]);

            // Invalidate remember token
            $db->query("UPDATE users SET remember_token = NULL WHERE id = ?", [$id]);

            // Log activity
            $this->logActivity($currentUser['id'], 'admin_user_deleted', "Deleted admin user: {$targetUser['email']}", [
                'target_user_id' => $id,
                'target_email' => $targetUser['email'],
                'target_role' => $targetUser['role'],
            ]);

            // Notify super admins if a super admin was deleted
            if ($targetUser['role'] === 'super_admin') {
                $this->notifySuperAdmins(
                    'Super Admin Deleted',
                    "{$currentUser['first_name']} {$currentUser['last_name']} deleted super admin: {$targetUser['email']}",
                    $currentUser['id']
                );
            }

            $db->commit();

            $this->respondSuccess('User deleted successfully.');

        } catch (\Exception $e) {
            $db->rollBack();
            logMessage('error', 'Failed to delete user', ['error' => $e->getMessage()]);
            $this->respondError('Failed to delete user.', 500);
        }
    }

    /**
     * Toggle MFA for a user (super_admin only can disable for others)
     */
    public function toggleMfa(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $currentUser = user();
        $db = db();

        $targetUser = $db->query("SELECT * FROM users WHERE id = ?", [$id])->fetch();

        if (!$targetUser) {
            $this->json(['success' => false, 'error' => 'User not found.'], 404);
            return;
        }

        if (!$this->policy->toggleMFA($targetUser)) {
            $this->json(['success' => false, 'error' => 'Permission denied.'], 403);
            return;
        }

        if ($targetUser['mfa_enabled']) {
            // Disable MFA
            $db->query("UPDATE users SET mfa_enabled = 0, mfa_secret = NULL WHERE id = ?", [$id]);
            $db->query("DELETE FROM user_mfa_backup_codes WHERE user_id = ?", [$id]);

            // Log activity
            $this->logActivity($currentUser['id'], 'mfa_disabled', "Disabled MFA for user: {$targetUser['email']}", [
                'target_user_id' => $id,
            ]);

            $message = 'MFA disabled for user.';
        } else {
            $message = 'User must enable MFA themselves through their account settings.';
        }

        $this->json(['success' => true, 'message' => $message]);
    }

    /**
     * Terminate all sessions for a user
     */
    public function terminateSessions(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $currentUser = user();
        $db = db();

        $targetUser = $db->query("SELECT * FROM users WHERE id = ?", [$id])->fetch();

        if (!$targetUser) {
            $this->json(['success' => false, 'error' => 'User not found.'], 404);
            return;
        }

        if (!$this->policy->terminateSessions($targetUser)) {
            $this->json(['success' => false, 'error' => 'Permission denied.'], 403);
            return;
        }

        // Get session files to delete
        $sessions = $db->query("SELECT session_id FROM user_sessions WHERE user_id = ?", [$id])->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($sessions as $sessionId) {
            $sessionFile = STORAGE_PATH . '/sessions/sess_' . $sessionId;
            if (file_exists($sessionFile)) {
                @unlink($sessionFile);
            }
        }

        $count = $db->query("DELETE FROM user_sessions WHERE user_id = ?", [$id])->rowCount();

        // Log activity
        $this->logActivity($currentUser['id'], 'sessions_terminated', "Terminated {$count} session(s) for user: {$targetUser['email']}", [
            'target_user_id' => $id,
            'session_count' => $count,
        ]);

        $this->json(['success' => true, 'message' => "Terminated {$count} session(s)."]);
    }

    /**
     * Force password reset for a user
     */
    public function forcePasswordReset(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $currentUser = user();
        $db = db();

        $targetUser = $db->query("SELECT * FROM users WHERE id = ?", [$id])->fetch();

        if (!$targetUser) {
            $this->json(['success' => false, 'error' => 'User not found.'], 404);
            return;
        }

        if (!$this->policy->forcePasswordReset($targetUser)) {
            $this->json(['success' => false, 'error' => 'Permission denied.'], 403);
            return;
        }

        $db->beginTransaction();
        try {
            // Generate reset token
            $token = bin2hex(random_bytes(32));

            $db->query("DELETE FROM password_resets WHERE email = ?", [$targetUser['email']]);
            $db->query(
                "INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())",
                [$targetUser['email'], $token]
            );

            // Invalidate all sessions
            $db->query("DELETE FROM user_sessions WHERE user_id = ?", [$id]);

            // Log activity
            $this->logActivity($currentUser['id'], 'force_password_reset', "Forced password reset for user: {$targetUser['email']}", [
                'target_user_id' => $id,
            ]);

            $db->commit();

            // In production, you would send an email here
            // sendEmail($targetUser['email'], 'Password Reset Required', ...);

            $this->json([
                'success' => true,
                'message' => 'Password reset initiated. User has been logged out of all sessions.',
            ]);

        } catch (\Exception $e) {
            $db->rollBack();
            logMessage('error', 'Failed to force password reset', ['error' => $e->getMessage()]);
            $this->json(['success' => false, 'error' => 'Failed to initiate password reset.'], 500);
        }
    }

    /**
     * View audit log for a user
     */
    public function auditLog(int $id): void
    {
        $db = db();

        $targetUser = $db->query("SELECT * FROM users WHERE id = ?", [$id])->fetch();

        if (!$targetUser) {
            flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        if (!$this->policy->viewAuditLog($targetUser)) {
            flash('error', 'Only super admins can view audit logs.');
            $this->redirect('/admin/users/' . $id);
            return;
        }

        // Get comprehensive audit log - activities BY this user and ABOUT this user
        $logs = $db->query("
            SELECT
                al.*,
                u.first_name as actor_first_name,
                u.last_name as actor_last_name,
                u.email as actor_email
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.user_id = ?
               OR al.subject_id = ?
               OR JSON_EXTRACT(al.properties, '$.target_user_id') = ?
            ORDER BY al.created_at DESC
            LIMIT 200
        ", [$id, $id, $id])->fetchAll();

        // Get role change history
        $roleHistory = $db->query("
            SELECT
                rh.*,
                u.first_name as changer_first_name,
                u.last_name as changer_last_name,
                u.email as changer_email
            FROM role_history rh
            LEFT JOIN users u ON rh.changed_by = u.id
            WHERE rh.user_id = ?
            ORDER BY rh.created_at DESC
        ", [$id])->fetchAll();

        // Get login history
        $loginHistory = $db->query("
            SELECT ip_address, user_agent, success, created_at
            FROM login_attempts
            WHERE email = ?
            ORDER BY created_at DESC
            LIMIT 50
        ", [$targetUser['email']])->fetchAll();

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/users/audit', [
            'title' => 'Audit Log: ' . $targetUser['first_name'] . ' ' . $targetUser['last_name'],
            'user' => $targetUser,
            'logs' => $logs,
            'roleHistory' => $roleHistory,
            'loginHistory' => $loginHistory,
        ]);
    }

    /**
     * Impersonate a user
     */
    public function impersonate(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $currentUser = user();
        $db = db();

        $targetUser = $db->query("SELECT * FROM users WHERE id = ?", [$id])->fetch();

        if (!$targetUser) {
            flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        if (!$this->policy->impersonate($targetUser)) {
            flash('error', $this->policy->getDenialReason('impersonate', $targetUser));
            $this->redirect('/admin/users');
            return;
        }

        // Store original user for returning
        $_SESSION['impersonating_from'] = $currentUser['id'];
        $_SESSION['impersonation_started'] = time();

        // Log the impersonation
        $this->logActivity($currentUser['id'], 'impersonation_start', "Started impersonating user: {$targetUser['email']}", [
            'target_user_id' => $id,
        ]);

        // Set new user session
        $_SESSION['user_id'] = $id;
        unset($_SESSION['_user_cache']);

        flash('warning', "You are now impersonating {$targetUser['first_name']} {$targetUser['last_name']}. Click 'Stop Impersonating' in the header to return to your account.");
        $this->redirect('/');
    }

    /**
     * Stop impersonating and return to original account
     */
    public function stopImpersonation(): void
    {
        if (!isset($_SESSION['impersonating_from'])) {
            flash('error', 'You are not currently impersonating anyone.');
            $this->redirect('/');
            return;
        }

        $originalUserId = $_SESSION['impersonating_from'];
        $impersonatedUserId = $_SESSION['user_id'];

        // Get user info for logging
        $db = db();
        $impersonatedUser = $db->query("SELECT email FROM users WHERE id = ?", [$impersonatedUserId])->fetch();

        // Restore original user
        $_SESSION['user_id'] = $originalUserId;
        unset($_SESSION['impersonating_from']);
        unset($_SESSION['impersonation_started']);
        unset($_SESSION['_user_cache']);

        // Log the end of impersonation
        $this->logActivity($originalUserId, 'impersonation_end', "Stopped impersonating user: " . ($impersonatedUser['email'] ?? 'unknown'), [
            'impersonated_user_id' => $impersonatedUserId,
        ]);

        flash('success', 'You have returned to your own account.');
        $this->redirect('/admin/users');
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Log a role change to role_history table
     */
    private function logRoleChange(int $userId, ?string $oldRole, string $newRole, int $changedBy, ?string $reason = null): void
    {
        db()->query("
            INSERT INTO role_history (user_id, old_role, new_role, changed_by, reason, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ", [$userId, $oldRole, $newRole, $changedBy, $reason, clientIp()]);
    }

    /**
     * Log an activity to activity_logs table
     */
    private function logActivity(int $userId, string $type, string $description, array $properties = []): void
    {
        try {
            db()->query("
                INSERT INTO activity_logs (user_id, type, description, properties, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ", [
                $userId,
                $type,
                $description,
                json_encode($properties),
                clientIp(),
                $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (\Exception $e) {
            // Don't let logging errors break the main operation
            logMessage('error', 'Failed to log activity', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send notification to all super admins
     */
    private function notifySuperAdmins(string $title, string $message, int $excludeUserId = 0): void
    {
        try {
            $db = db();
            $superAdmins = $db->query(
                "SELECT id FROM users WHERE role = 'super_admin' AND status = 'active' AND id != ?",
                [$excludeUserId]
            )->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($superAdmins as $adminId) {
                $db->query("
                    INSERT INTO admin_notifications (user_id, type, title, message, created_at)
                    VALUES (?, 'security_alert', ?, ?, NOW())
                ", [$adminId, $title, $message]);
            }
        } catch (\Exception $e) {
            // Don't let notification errors break the main operation
            logMessage('error', 'Failed to notify super admins', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Respond with success (supports both AJAX and regular requests)
     */
    private function respondSuccess(string $message): void
    {
        if (isAjax()) {
            $this->json(['success' => true, 'message' => $message]);
        } else {
            flash('success', $message);
            $this->redirect('/admin/users');
        }
    }

    /**
     * Respond with error (supports both AJAX and regular requests)
     */
    private function respondError(string $message, int $code = 400): void
    {
        if (isAjax()) {
            $this->json(['success' => false, 'error' => $message], $code);
        } else {
            flash('error', $message);
            $this->redirect('/admin/users');
        }
    }
}
