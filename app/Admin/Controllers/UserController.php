<?php
declare(strict_types=1);

/**
 * Admin User Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Manages admin users and their permissions
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Models\User;

class UserController extends Controller
{
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

        // Only super_admin can create users
        $currentUser = user();
        if ($currentUser['role'] !== 'super_admin') {
            flash('error', 'You do not have permission to create admin users.');
            $this->redirect('/admin/users');
            return;
        }

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

        flash('success', 'Admin user created successfully.');
        $this->redirect('/admin/users');
    }

    public function show(int $id): void
    {
        $db = db();

        $user = $db->query("
            SELECT id, email, first_name, last_name, phone, role, status,
                   avatar, mfa_enabled, email_verified_at, last_login_at, last_login_ip,
                   created_at, updated_at
            FROM users
            WHERE id = ? AND role IN ('admin', 'super_admin')
        ", [$id])->fetch();

        if (!$user) {
            flash('error', 'User not found.');
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
        ", [$user['email']])->fetchAll();

        // Get active sessions
        $sessions = $db->query("
            SELECT id, ip_address, user_agent, last_activity, created_at
            FROM user_sessions
            WHERE user_id = ?
            ORDER BY last_activity DESC
        ", [$id])->fetchAll();

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/users/show', [
            'title' => $user['first_name'] . ' ' . $user['last_name'],
            'user' => $user,
            'activity' => $activity,
            'loginHistory' => $loginHistory,
            'sessions' => $sessions,
        ]);
    }

    public function edit(int $id): void
    {
        $db = db();

        $user = $db->query("
            SELECT * FROM users
            WHERE id = ? AND role IN ('admin', 'super_admin')
        ", [$id])->fetch();

        if (!$user) {
            flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/users/form', [
            'title' => 'Edit Admin User',
            'user' => $user,
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();
        $currentUser = user();

        $user = $db->query("SELECT * FROM users WHERE id = ? AND role IN ('admin', 'super_admin')", [$id])->fetch();

        if (!$user) {
            flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        // Only super_admin can edit other users, or user can edit themselves
        if ($currentUser['role'] !== 'super_admin' && $currentUser['id'] !== $id) {
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

        // Only super_admin can change role and status
        if ($currentUser['role'] === 'super_admin') {
            // Prevent removing own super_admin role
            if ($currentUser['id'] === $id && $_POST['role'] !== 'super_admin') {
                flash('error', 'You cannot remove your own super admin role.');
                $this->redirect('/admin/users/' . $id . '/edit');
                return;
            }

            if (isset($_POST['role']) && in_array($_POST['role'], ['admin', 'super_admin'])) {
                $updates[] = 'role = ?';
                $params[] = $_POST['role'];
            }

            if (isset($_POST['status']) && in_array($_POST['status'], ['active', 'pending', 'suspended'])) {
                // Prevent suspending own account
                if ($currentUser['id'] === $id && $_POST['status'] !== 'active') {
                    flash('error', 'You cannot change your own account status.');
                    $this->redirect('/admin/users/' . $id . '/edit');
                    return;
                }

                $updates[] = 'status = ?';
                $params[] = $_POST['status'];
            }
        }

        // Handle password update
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 8) {
                flash('error', 'Password must be at least 8 characters.');
                $this->redirect('/admin/users/' . $id . '/edit');
                return;
            }
            $updates[] = 'password = ?';
            $updates[] = 'password_changed_at = NOW()';
            $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $params[] = $id;
        $updateClause = implode(', ', $updates);

        $db->query("UPDATE users SET {$updateClause} WHERE id = ?", $params);

        // Log activity
        try {
            $db->query("
                INSERT INTO activity_logs (user_id, type, description, ip_address, created_at)
                VALUES (?, 'admin_user_updated', ?, ?, NOW())
            ", [$currentUser['id'], "Updated user: {$user['email']}", clientIp()]);
        } catch (\Exception $e) {
            // Ignore logging errors
        }

        flash('success', 'User updated successfully.');
        $this->redirect('/admin/users/' . $id);
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $currentUser = user();

        // Only super_admin can delete users
        if ($currentUser['role'] !== 'super_admin') {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'Permission denied.'], 403);
                return;
            }
            flash('error', 'You do not have permission to delete users.');
            $this->redirect('/admin/users');
            return;
        }

        // Prevent self-deletion
        if ($currentUser['id'] === $id) {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'You cannot delete your own account.'], 400);
                return;
            }
            flash('error', 'You cannot delete your own account.');
            $this->redirect('/admin/users');
            return;
        }

        $db = db();

        $user = $db->query("SELECT * FROM users WHERE id = ? AND role IN ('admin', 'super_admin')", [$id])->fetch();

        if (!$user) {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'User not found.'], 404);
                return;
            }
            flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        // Soft delete - change status to deleted
        $db->query("UPDATE users SET status = 'deleted', updated_at = NOW() WHERE id = ?", [$id]);

        // Terminate all sessions
        $db->query("DELETE FROM user_sessions WHERE user_id = ?", [$id]);

        // Log activity
        try {
            $db->query("
                INSERT INTO activity_logs (user_id, type, description, ip_address, created_at)
                VALUES (?, 'admin_user_deleted', ?, ?, NOW())
            ", [$currentUser['id'], "Deleted user: {$user['email']}", clientIp()]);
        } catch (\Exception $e) {
            // Ignore logging errors
        }

        if (isAjax()) {
            $this->json(['success' => true, 'message' => 'User deleted successfully.']);
            return;
        }

        flash('success', 'User deleted successfully.');
        $this->redirect('/admin/users');
    }

    /**
     * Toggle MFA for a user (super_admin only)
     */
    public function toggleMfa(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $currentUser = user();

        if ($currentUser['role'] !== 'super_admin') {
            $this->json(['success' => false, 'error' => 'Permission denied.'], 403);
            return;
        }

        $db = db();

        $user = $db->query("SELECT * FROM users WHERE id = ?", [$id])->fetch();

        if (!$user) {
            $this->json(['success' => false, 'error' => 'User not found.'], 404);
            return;
        }

        if ($user['mfa_enabled']) {
            // Disable MFA
            $db->query("UPDATE users SET mfa_enabled = 0, mfa_secret = NULL WHERE id = ?", [$id]);
            $db->query("DELETE FROM user_mfa_backup_codes WHERE user_id = ?", [$id]);
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

        if ($currentUser['role'] !== 'super_admin' && $currentUser['id'] !== $id) {
            $this->json(['success' => false, 'error' => 'Permission denied.'], 403);
            return;
        }

        $db = db();

        // Get session files to delete
        $sessions = $db->query("SELECT session_id FROM user_sessions WHERE user_id = ?", [$id])->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($sessions as $sessionId) {
            $sessionFile = STORAGE_PATH . '/sessions/sess_' . $sessionId;
            if (file_exists($sessionFile)) {
                @unlink($sessionFile);
            }
        }

        $count = $db->query("DELETE FROM user_sessions WHERE user_id = ?", [$id])->rowCount();

        $this->json(['success' => true, 'message' => "Terminated {$count} session(s)."]);
    }
}
