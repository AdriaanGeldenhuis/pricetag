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
use App\Core\Database;
use App\Policies\UserPolicy;

class UserController extends Controller
{
    private ?UserPolicy $policy = null;

    private function policy(): UserPolicy
    {
        // Lazy-init so a missing UserPolicy class doesn't break the rest of
        // the controller. user() pulls the current admin from session.
        if ($this->policy === null) {
            $this->policy = new UserPolicy(user());
        }
        return $this->policy;
    }

    public function index(): void
    {
        $db = Database::getInstance();

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
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE {$whereClause}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $totalPages = (int) ceil($total / $perPage);

        // Get users
        $offset = ($page - 1) * $perPage;
        $stmt = $db->prepare("
            SELECT id, email, first_name, last_name, phone, role, status,
                   avatar, mfa_enabled, last_login_at, created_at
            FROM users
            WHERE {$whereClause}
            ORDER BY created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        // Get role counts
        $stmt = $db->prepare("
            SELECT role, COUNT(*) as count
            FROM users
            WHERE role IN ('admin', 'super_admin')
            GROUP BY role
        ");
        $stmt->execute();
        $roleCounts = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        $this->layout('admin');
        $this->view('pages/users/index', [
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
        $this->layout('admin');
        $this->view('pages/users/form', [
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

        $db = Database::getInstance();

        // Check email uniqueness
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([strtolower($_POST['email'])]);
        $existing = $stmt->fetch();
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
        $stmt = $db->prepare("
            INSERT INTO users (email, password, first_name, last_name, phone, role, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
        ");
        $stmt->execute([
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
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT id, email, first_name, last_name, phone, role, status,
                   avatar, mfa_enabled, email_verified_at, last_login_at, last_login_ip,
                   created_at, updated_at
            FROM users
            WHERE id = ? AND role IN ('admin', 'super_admin')
        ");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        // Get recent activity
        $stmt = $db->prepare("
            SELECT type, description, ip_address, created_at
            FROM activity_logs
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$id]);
        $activity = $stmt->fetchAll();

        // Get login history
        $stmt = $db->prepare("
            SELECT ip_address, user_agent, success, created_at
            FROM login_attempts
            WHERE email = ?
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$user['email']]);
        $loginHistory = $stmt->fetchAll();

        // Get active sessions
        $stmt = $db->prepare("
            SELECT id, ip_address, user_agent, last_activity, created_at
            FROM user_sessions
            WHERE user_id = ?
            ORDER BY last_activity DESC
        ");
        $stmt->execute([$id]);
        $sessions = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/users/show', [
            'title' => $user['first_name'] . ' ' . $user['last_name'],
            'user' => $user,
            'activity' => $activity,
            'loginHistory' => $loginHistory,
            'sessions' => $sessions,
        ]);
    }

    public function edit(int $id): void
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT * FROM users
            WHERE id = ? AND role IN ('admin', 'super_admin')
        ");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        $this->layout('admin');
        $this->view('pages/users/form', [
            'title' => 'Edit Admin User',
            'user' => $user,
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $currentUser = user();

        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role IN ('admin', 'super_admin')");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

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
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([strtolower($_POST['email']), $id]);
        $existing = $stmt->fetch();

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

        $stmt = $db->prepare("UPDATE users SET {$updateClause} WHERE id = ?");
        $stmt->execute($params);

        // Log activity
        try {
            $stmt = $db->prepare("
                INSERT INTO activity_logs (user_id, type, description, ip_address, created_at)
                VALUES (?, 'admin_user_updated', ?, ?, NOW())
            ");
            $stmt->execute([$currentUser['id'], "Updated user: {$user['email']}", clientIp()]);
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

        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role IN ('admin', 'super_admin')");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

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
        $stmt = $db->prepare("UPDATE users SET status = 'deleted', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);

        // Terminate all sessions
        $stmt = $db->prepare("DELETE FROM user_sessions WHERE user_id = ?");
        $stmt->execute([$id]);

        // Log activity
        try {
            $stmt = $db->prepare("
                INSERT INTO activity_logs (user_id, type, description, ip_address, created_at)
                VALUES (?, 'admin_user_deleted', ?, ?, NOW())
            ");
            $stmt->execute([$currentUser['id'], "Deleted user: {$user['email']}", clientIp()]);
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

        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->json(['success' => false, 'error' => 'User not found.'], 404);
            return;
        }

        if ($user['mfa_enabled']) {
            // Disable MFA
            $stmt = $db->prepare("UPDATE users SET mfa_enabled = 0, mfa_secret = NULL WHERE id = ?");
            $stmt->execute([$id]);
            $stmt = $db->prepare("DELETE FROM user_mfa_backup_codes WHERE user_id = ?");
            $stmt->execute([$id]);
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

        $db = Database::getInstance();

        // Get session files to delete
        $stmt = $db->prepare("SELECT session_id FROM user_sessions WHERE user_id = ?");
        $stmt->execute([$id]);
        $sessions = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($sessions as $sessionId) {
            $sessionFile = STORAGE_PATH . '/sessions/sess_' . $sessionId;
            if (file_exists($sessionFile)) {
                @unlink($sessionFile);
            }
        }

        $stmt = $db->prepare("DELETE FROM user_sessions WHERE user_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->rowCount();

        $this->json(['success' => true, 'message' => "Terminated {$count} session(s)."]);
    }

    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        $viewPath = ADMIN_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View '$view' not found at '$viewPath'");
        }
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        if (isset($this->data['_layout'])) {
            $layoutPath = ADMIN_PATH . '/Views/layouts/' . $this->data['_layout'] . '.php';
            if (file_exists($layoutPath)) {
                include $layoutPath;
                return;
            }
        }
        echo $content;
    }

    protected function layout(string $layout): self
    {
        $this->data['_layout'] = $layout;
        return $this;
    }

    // ============================================================================
    // Security features ported from app/Admin/Controllers/UserController.php
    // (Phase 5b - 2026-05-13). Routes for these are registered in
    // app/routes.php (admin.users.audit, admin.users.password.reset,
    // admin.users.impersonate, admin.users.impersonate.stop) but were
    // returning 500 because the live controller didn't have the methods.
    // ============================================================================

    /**
     * Run a SQL statement with optional bound params. Wraps prepare/execute
     * so callers can chain ->fetch() / ->fetchAll() / ->fetchColumn() like
     * they would on a raw PDO::query() result. Falls back to PDO::query()
     * when there are no params.
     */
    private function q(\PDO $db, string $sql, array $params = []): \PDOStatement
    {
        if (empty($params)) {
            return $db->query($sql);
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Force a password reset on a target user. Sends them through the
     * standard reset-token flow and kills all their existing sessions.
     */
    public function forcePasswordReset(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 400);
            return;
        }

        $currentUser = user();
        $db = Database::getInstance();
        $targetUser = $this->q($db, "SELECT * FROM users WHERE id = ?", [$id])->fetch();
        if (!$targetUser) {
            $this->jsonResponse(['success' => false, 'error' => 'User not found.'], 404);
            return;
        }
        if (!$this->policy()->forcePasswordReset($targetUser)) {
            $this->jsonResponse(['success' => false, 'error' => 'Permission denied.'], 403);
            return;
        }

        $db->beginTransaction();
        try {
            $token = bin2hex(random_bytes(32));
            $this->q($db, "DELETE FROM password_resets WHERE email = ?", [$targetUser['email']]);
            $this->q($db, "INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())", [$targetUser['email'], $token]);
            $this->q($db, "DELETE FROM user_sessions WHERE user_id = ?", [$id]);
            $this->logActivity((int) $currentUser['id'], 'force_password_reset', "Forced password reset for user: {$targetUser['email']}", ['target_user_id' => $id]);
            $db->commit();
            $this->jsonResponse([
                'success' => true,
                'message' => 'Password reset initiated. User has been logged out of all sessions.',
            ]);
        } catch (\Exception $e) {
            $db->rollBack();
            error_log('Failed to force password reset: ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Failed to initiate password reset.'], 500);
        }
    }

    /**
     * View the audit log for a user. Renders pages/users/audit which lists
     * activities BY the user, ABOUT the user, role-change history, and
     * login attempts.
     */
    public function auditLog(int $id): void
    {
        $db = Database::getInstance();
        $targetUser = $this->q($db, "SELECT * FROM users WHERE id = ?", [$id])->fetch();
        if (!$targetUser) {
            if (function_exists('flash')) {
                flash('error', 'User not found.');
            }
            $this->redirect('/admin/users');
            return;
        }
        if (!$this->policy()->viewAuditLog($targetUser)) {
            if (function_exists('flash')) {
                flash('error', 'Only super admins can view audit logs.');
            }
            $this->redirect('/admin/users/' . $id);
            return;
        }

        $logs = $this->q($db, 
            "SELECT al.*, u.first_name AS actor_first_name, u.last_name AS actor_last_name, u.email AS actor_email
             FROM activity_logs al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE al.user_id = ? OR al.subject_id = ? OR JSON_EXTRACT(al.properties, '$.target_user_id') = ?
             ORDER BY al.created_at DESC LIMIT 200",
            [$id, $id, $id]
        )->fetchAll() ?: [];

        $roleHistory = [];
        try {
            $roleHistory = $this->q($db, 
                "SELECT rh.*, u.first_name AS changer_first_name, u.last_name AS changer_last_name, u.email AS changer_email
                 FROM role_history rh
                 LEFT JOIN users u ON rh.changed_by = u.id
                 WHERE rh.user_id = ? ORDER BY rh.created_at DESC",
                [$id]
            )->fetchAll() ?: [];
        } catch (\Throwable $e) {
            // role_history table may not exist on older installs
        }

        $loginHistory = [];
        try {
            $loginHistory = $this->q($db, 
                "SELECT ip_address, user_agent, success, created_at FROM login_attempts WHERE email = ? ORDER BY created_at DESC LIMIT 50",
                [$targetUser['email']]
            )->fetchAll() ?: [];
        } catch (\Throwable $e) {
            // login_attempts table may not exist on older installs
        }

        $this->layout('admin');
        $this->view('pages/users/audit', [
            'page_title' => 'Audit Log: ' . $targetUser['first_name'] . ' ' . $targetUser['last_name'],
            'active_page' => 'users',
            'user' => $targetUser,
            'logs' => $logs,
            'roleHistory' => $roleHistory,
            'loginHistory' => $loginHistory,
        ]);
    }

    /**
     * Start impersonating another user. Stashes the original session id in
     * impersonating_from so stopImpersonation() can restore it.
     */
    public function impersonate(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/admin/users');
            return;
        }
        $currentUser = user();
        $db = Database::getInstance();
        $targetUser = $this->q($db, "SELECT * FROM users WHERE id = ?", [$id])->fetch();
        if (!$targetUser) {
            if (function_exists('flash')) { flash('error', 'User not found.'); }
            $this->redirect('/admin/users');
            return;
        }
        if (!$this->policy()->impersonate($targetUser)) {
            if (function_exists('flash')) {
                flash('error', $this->policy()->getDenialReason('impersonate', $targetUser));
            }
            $this->redirect('/admin/users');
            return;
        }

        $_SESSION['impersonating_from'] = (int) $currentUser['id'];
        $_SESSION['impersonation_started'] = time();
        $this->logActivity((int) $currentUser['id'], 'impersonation_start', "Started impersonating user: {$targetUser['email']}", ['target_user_id' => $id]);

        $_SESSION['user_id'] = $id;
        unset($_SESSION['_user_cache']);

        if (function_exists('flash')) {
            flash('warning', "You are now impersonating {$targetUser['first_name']} {$targetUser['last_name']}. Click 'Stop Impersonating' in the header to return to your account.");
        }
        $this->redirect('/');
    }

    /**
     * Restore the original admin session after an impersonate() call.
     */
    public function stopImpersonation(): void
    {
        if (!isset($_SESSION['impersonating_from'])) {
            if (function_exists('flash')) { flash('error', 'You are not currently impersonating anyone.'); }
            $this->redirect('/');
            return;
        }
        $originalUserId = (int) $_SESSION['impersonating_from'];
        $impersonatedUserId = (int) ($_SESSION['user_id'] ?? 0);

        $db = Database::getInstance();
        $impersonatedUser = $this->q($db, "SELECT email FROM users WHERE id = ?", [$impersonatedUserId])->fetch();

        $_SESSION['user_id'] = $originalUserId;
        unset($_SESSION['impersonating_from']);
        unset($_SESSION['impersonation_started']);
        unset($_SESSION['_user_cache']);

        $this->logActivity($originalUserId, 'impersonation_end', 'Stopped impersonating user: ' . ($impersonatedUser['email'] ?? 'unknown'), ['impersonated_user_id' => $impersonatedUserId]);

        if (function_exists('flash')) { flash('success', 'You have returned to your own account.'); }
        $this->redirect('/admin/users');
    }

    private function logActivity(int $userId, string $type, string $description, array $properties = []): void
    {
        try {
            $this->q(
                Database::getInstance(),
                "INSERT INTO activity_logs (user_id, type, description, properties, ip_address, user_agent, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [
                    $userId, $type, $description,
                    json_encode($properties),
                    function_exists('clientIp') ? clientIp() : ($_SERVER['REMOTE_ADDR'] ?? null),
                    $_SERVER['HTTP_USER_AGENT'] ?? null,
                ]
            );
        } catch (\Exception $e) {
            error_log('Failed to log activity: ' . $e->getMessage());
        }
    }

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
