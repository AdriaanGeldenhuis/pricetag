<?php
/**
 * Account Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;
use App\Models\Order;
use App\Services\SessionManager;
use App\Services\MfaService;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    public function dashboard(): void
    {
        $user = User::find($_SESSION['user_id']);

        if (!$user) {
            // User not found in database, force re-login
            unset($_SESSION['user_id']);
            flash('error', 'Please login again');
            $this->redirect('/login');
            return;
        }

        $db = Database::getInstance();

        // Get recent orders (with graceful fallback)
        $recentOrders = [];
        try {
            $recentOrders = $user->getOrders(5);
        } catch (\PDOException $e) {
            // orders table might not exist
        }

        // Get stats with graceful fallback
        $totalOrders = 0;
        $wishlistCount = 0;

        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
            $stmt->execute([$user->id]);
            $totalOrders = $stmt->fetchColumn();
        } catch (\PDOException $e) {
            // orders table might not exist
        }

        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
            $stmt->execute([$user->id]);
            $wishlistCount = $stmt->fetchColumn();
        } catch (\PDOException $e) {
            // wishlists table might not exist
        }

        $this->layout('main');
        $this->view('pages/account/dashboard', [
            'meta_title' => 'My Account | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'user' => $user,
            'recentOrders' => $recentOrders,
            'stats' => [
                'orders' => $totalOrders,
                'wishlist' => $wishlistCount,
            ],
        ]);
    }

    public function orders(): void
    {
        $user = User::find($_SESSION['user_id']);
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $db = Database::getInstance();
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Count total
        $stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $stmt->execute([$user->id]);
        $total = (int) $stmt->fetchColumn();

        // Get orders
        $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$user->id, $perPage, $offset]);
        $orders = $stmt->fetchAll();

        $this->layout('main');
        $this->view('pages/account/orders', [
            'meta_title' => 'My Orders | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'orders' => $orders,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function orderDetail(string $id): void
    {
        $order = Order::find((int) $id);

        if (!$order || $order->user_id !== $_SESSION['user_id']) {
            http_response_code(404);
            $this->layout('main');
            $this->view('errors/404');
            return;
        }

        $items = $order->getItems();
        $statusHistory = $order->getStatusHistory();

        $this->layout('main');
        $this->view('pages/account/order-detail', [
            'meta_title' => 'Order #' . $order->order_number . ' | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'order' => $order,
            'items' => $items,
            'statusHistory' => $statusHistory,
        ]);
    }

    public function invoice(string $id): void
    {
        $order = Order::find((int) $id);

        if (!$order || $order->user_id !== $_SESSION['user_id']) {
            http_response_code(404);
            exit;
        }

        $items = $order->getItems();

        // For now, just output HTML invoice
        // In production, use a PDF library like TCPDF or Dompdf
        header('Content-Type: text/html');

        include APP_PATH . '/Views/pages/account/invoice.php';
        exit;
    }

    public function addresses(): void
    {
        $user = User::find($_SESSION['user_id']);
        $addresses = $user->getAddresses();

        $this->layout('main');
        $this->view('pages/account/addresses', [
            'meta_title' => 'My Addresses | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'addresses' => $addresses,
        ]);
    }

    public function saveAddress(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $validation = $this->validate([
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'address_line_1' => 'required|min:5|max:255',
            'city' => 'required|min:2|max:100',
            'province' => 'required',
            'postal_code' => 'required|min:4|max:20',
        ]);

        if (!$validation['valid']) {
            if (isAjax()) {
                $this->json(['success' => false, 'errors' => $validation['errors']], 422);
            }
            $this->redirect('/account/addresses');
            return;
        }

        $db = Database::getInstance();
        $userId = $_SESSION['user_id'];
        $addressId = $_POST['address_id'] ?? null;
        $isDefault = !empty($_POST['is_default']);

        // If setting as default, unset other defaults
        if ($isDefault) {
            $stmt = $db->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ? AND type = ?");
            $stmt->execute([$userId, $_POST['type'] ?? 'shipping']);
        }

        $data = [
            $_POST['type'] ?? 'shipping',
            $isDefault ? 1 : 0,
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['company'] ?? null,
            $_POST['address_line_1'],
            $_POST['address_line_2'] ?? null,
            $_POST['city'],
            $_POST['province'],
            $_POST['postal_code'],
            $_POST['country'] ?? 'ZA',
            $_POST['phone'] ?? null,
        ];

        if ($addressId) {
            // Update existing
            $stmt = $db->prepare("
                UPDATE user_addresses SET
                    type = ?, is_default = ?, first_name = ?, last_name = ?, company = ?,
                    address_line_1 = ?, address_line_2 = ?, city = ?, province = ?,
                    postal_code = ?, country = ?, phone = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $data[] = $addressId;
            $data[] = $userId;
            $stmt->execute($data);
        } else {
            // Create new
            $stmt = $db->prepare("
                INSERT INTO user_addresses
                    (user_id, type, is_default, first_name, last_name, company,
                     address_line_1, address_line_2, city, province, postal_code, country, phone)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            array_unshift($data, $userId);
            $stmt->execute($data);
        }

        if (isAjax()) {
            $this->json(['success' => true]);
            return;
        }

        flash('success', 'Address saved');
        $this->redirect('/account/addresses');
    }

    public function deleteAddress(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([(int) $id, $_SESSION['user_id']]);

        if (isAjax()) {
            $this->json(['success' => true]);
            return;
        }

        flash('success', 'Address deleted');
        $this->redirect('/account/addresses');
    }

    public function settings(): void
    {
        $user = User::find($_SESSION['user_id']);

        $this->layout('main');
        $this->view('pages/account/settings', [
            'meta_title' => 'Account Settings | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'user' => $user,
        ]);
    }

    public function updateSettings(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $validation = $this->validate([
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email',
        ]);

        if (!$validation['valid']) {
            $this->redirect('/account/settings');
            return;
        }

        $user = User::find($_SESSION['user_id']);

        // Check if email changed and is unique
        if ($_POST['email'] !== $user->email) {
            $existing = User::findByEmail($_POST['email']);
            if ($existing) {
                flash('error', 'Email address is already in use');
                $this->redirect('/account/settings');
                return;
            }
        }

        $user->first_name = $_POST['first_name'];
        $user->last_name = $_POST['last_name'];
        $user->email = $_POST['email'];
        $user->phone = $_POST['phone'] ?? null;
        $user->save();

        unset($_SESSION['_user_cache']);

        flash('success', 'Settings updated');
        $this->redirect('/account/settings');
    }

    public function updatePassword(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $user = User::find($_SESSION['user_id']);

        // Verify current password
        if (!$user->verifyPassword($_POST['current_password'] ?? '')) {
            flash('error', 'Current password is incorrect');
            $this->redirect('/account/settings');
            return;
        }

        // Validate new password
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['new_password_confirmation'] ?? '';

        if (strlen($newPassword) < 8) {
            flash('error', 'New password must be at least 8 characters');
            $this->redirect('/account/settings');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            flash('error', 'Passwords do not match');
            $this->redirect('/account/settings');
            return;
        }

        $user->updatePassword($newPassword);

        flash('success', 'Password updated');
        $this->redirect('/account/settings');
    }

    public function security(): void
    {
        $user = User::find($_SESSION['user_id']);
        $sessionManager = new SessionManager();

        $sessions = $sessionManager->getUserSessions($user->id);
        $loginHistory = $sessionManager->getLoginHistory($user->id, 10);
        $warnings = $sessionManager->checkSuspiciousActivity($user->id);

        $this->layout('main');
        $this->view('pages/account/security', [
            'meta_title' => 'Security | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'user' => $user,
            'sessions' => $sessions,
            'loginHistory' => $loginHistory,
            'warnings' => $warnings,
            'mfaEnabled' => (bool) $user->mfa_enabled,
        ]);
    }

    public function activity(): void
    {
        $user = User::find($_SESSION['user_id']);
        $sessionManager = new SessionManager();

        $activities = $sessionManager->getActivityLogs($user->id, 50);

        $this->layout('main');
        $this->view('pages/account/activity', [
            'meta_title' => 'Activity Log | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'activities' => $activities,
        ]);
    }

    /**
     * Terminate a specific session
     */
    public function terminateSession(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $sessionManager = new SessionManager();
        $success = $sessionManager->terminateSession($_SESSION['user_id'], (int) $id);

        if (isAjax()) {
            $this->json(['success' => $success]);
            return;
        }

        flash($success ? 'success' : 'error', $success ? 'Session terminated' : 'Failed to terminate session');
        $this->redirect('/account/security');
    }

    /**
     * Terminate all other sessions
     */
    public function terminateOtherSessions(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $sessionManager = new SessionManager();
        $count = $sessionManager->terminateOtherSessions($_SESSION['user_id']);

        $sessionManager->logActivity($_SESSION['user_id'], 'sessions_terminated', "Terminated $count other sessions");

        if (isAjax()) {
            $this->json(['success' => true, 'terminated' => $count]);
            return;
        }

        flash('success', "Terminated $count other sessions");
        $this->redirect('/account/security');
    }

    /**
     * Show MFA setup page
     */
    public function setupMfa(): void
    {
        $user = User::find($_SESSION['user_id']);

        if ($user->mfa_enabled) {
            flash('info', 'Two-factor authentication is already enabled');
            $this->redirect('/account/security');
            return;
        }

        $mfaService = new MfaService();
        $secret = $mfaService->generateSecret();

        // Store secret temporarily in session
        $_SESSION['mfa_setup_secret'] = $secret;

        $qrCodeUrl = $mfaService->getQrCodeUrl($secret, $user->email, config('app.name', 'Pricetag'));

        $this->layout('main');
        $this->view('pages/account/mfa-setup', [
            'meta_title' => 'Setup Two-Factor Authentication | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    /**
     * Verify and enable MFA
     */
    public function enableMfa(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $secret = $_SESSION['mfa_setup_secret'] ?? null;
        $code = $_POST['code'] ?? '';

        if (!$secret) {
            flash('error', 'MFA setup session expired. Please try again.');
            $this->redirect('/account/security');
            return;
        }

        $mfaService = new MfaService();

        if (!$mfaService->verifyCode($secret, $code)) {
            flash('error', 'Invalid verification code. Please try again.');
            $this->redirect('/account/mfa/setup');
            return;
        }

        // Generate backup codes
        $backupCodes = $mfaService->generateBackupCodes();
        $hashedBackupCodes = $mfaService->hashBackupCodes($backupCodes);

        // Save to user
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE users SET mfa_enabled = 1, mfa_secret = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$secret, $_SESSION['user_id']]);

        // Store backup codes (in practice, store hashed versions in a separate table)
        $stmt = $db->prepare("
            DELETE FROM user_mfa_backup_codes WHERE user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);

        foreach ($hashedBackupCodes as $hashedCode) {
            $stmt = $db->prepare("
                INSERT INTO user_mfa_backup_codes (user_id, code_hash, created_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$_SESSION['user_id'], $hashedCode]);
        }

        // Clear session secret
        unset($_SESSION['mfa_setup_secret']);
        unset($_SESSION['_user_cache']);

        // Log activity
        $sessionManager = new SessionManager();
        $sessionManager->logActivity($_SESSION['user_id'], 'mfa_enabled', 'Two-factor authentication enabled');

        // Show backup codes once
        $this->layout('main');
        $this->view('pages/account/mfa-backup-codes', [
            'meta_title' => 'Backup Codes | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'backupCodes' => $backupCodes,
        ]);
    }

    /**
     * Disable MFA
     */
    public function disableMfa(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $user = User::find($_SESSION['user_id']);

        // Require password confirmation
        if (!$user->verifyPassword($_POST['password'] ?? '')) {
            flash('error', 'Incorrect password');
            $this->redirect('/account/security');
            return;
        }

        // Disable MFA
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE users SET mfa_enabled = 0, mfa_secret = NULL, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);

        // Delete backup codes
        $stmt = $db->prepare("DELETE FROM user_mfa_backup_codes WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        unset($_SESSION['_user_cache']);

        // Log activity
        $sessionManager = new SessionManager();
        $sessionManager->logActivity($_SESSION['user_id'], 'mfa_disabled', 'Two-factor authentication disabled');

        flash('success', 'Two-factor authentication has been disabled');
        $this->redirect('/account/security');
    }

    /**
     * Regenerate backup codes
     */
    public function regenerateBackupCodes(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $user = User::find($_SESSION['user_id']);

        if (!$user->mfa_enabled) {
            flash('error', 'Two-factor authentication is not enabled');
            $this->redirect('/account/security');
            return;
        }

        // Require password confirmation
        if (!$user->verifyPassword($_POST['password'] ?? '')) {
            flash('error', 'Incorrect password');
            $this->redirect('/account/security');
            return;
        }

        $mfaService = new MfaService();
        $backupCodes = $mfaService->generateBackupCodes();
        $hashedBackupCodes = $mfaService->hashBackupCodes($backupCodes);

        // Replace backup codes
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM user_mfa_backup_codes WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        foreach ($hashedBackupCodes as $hashedCode) {
            $stmt = $db->prepare("
                INSERT INTO user_mfa_backup_codes (user_id, code_hash, created_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$_SESSION['user_id'], $hashedCode]);
        }

        // Log activity
        $sessionManager = new SessionManager();
        $sessionManager->logActivity($_SESSION['user_id'], 'backup_codes_regenerated', 'MFA backup codes regenerated');

        // Show new backup codes
        $this->layout('main');
        $this->view('pages/account/mfa-backup-codes', [
            'meta_title' => 'New Backup Codes | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'backupCodes' => $backupCodes,
            'regenerated' => true,
        ]);
    }
}
