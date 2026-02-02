<?php
/**
 * Account Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Handles all user account-related functionality including dashboard,
 * orders, addresses, settings, security, and MFA.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;
use App\Models\Order;
use App\Services\SessionManager;
use App\Services\MfaService;
use PDOException;

class AccountController extends Controller
{
    private ?User $currentUser = null;

    public function __construct()
    {
        $this->requireAuth();
        $this->loadCurrentUser();
    }

    /**
     * Load and cache the current user
     */
    private function loadCurrentUser(): void
    {
        if (!isset($_SESSION['user_id'])) {
            return;
        }

        try {
            $this->currentUser = User::find($_SESSION['user_id']);

            if (!$this->currentUser) {
                $this->handleInvalidSession();
            }
        } catch (PDOException $e) {
            logMessage('error', 'Failed to load user: ' . $e->getMessage());
            $this->handleInvalidSession();
        }
    }

    /**
     * Handle invalid or expired session
     */
    private function handleInvalidSession(): void
    {
        unset($_SESSION['user_id'], $_SESSION['_user_cache']);
        flash('error', 'Your session has expired. Please login again.');
        $this->redirect('/login');
    }

    /**
     * Get database instance with error handling
     */
    private function db(): Database
    {
        return Database::getInstance();
    }

    /**
     * Account Dashboard
     */
    public function dashboard(): void
    {
        if (!$this->currentUser) {
            $this->handleInvalidSession();
            return;
        }

        $stats = $this->getDashboardStats();
        $recentOrders = $this->getRecentOrders(5);
        $recentActivity = $this->getRecentActivity(5);

        $this->layout('main');
        $this->view('pages/account/dashboard', [
            'meta_title' => 'My Account | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'user' => $this->currentUser,
            'recentOrders' => $recentOrders,
            'recentActivity' => $recentActivity,
            'stats' => $stats,
        ]);
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        $stats = [
            'orders' => 0,
            'pending_orders' => 0,
            'wishlist' => 0,
            'addresses' => 0,
            'total_spent' => 0,
        ];

        try {
            $db = $this->db();

            // Total orders
            $stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
            $stmt->execute([$this->currentUser->id]);
            $stats['orders'] = (int) $stmt->fetchColumn();

            // Pending orders
            $stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status IN ('pending', 'processing', 'paid')");
            $stmt->execute([$this->currentUser->id]);
            $stats['pending_orders'] = (int) $stmt->fetchColumn();

            // Total spent
            $stmt = $db->prepare("SELECT COALESCE(SUM(total), 0) FROM orders WHERE user_id = ? AND payment_status = 'paid'");
            $stmt->execute([$this->currentUser->id]);
            $stats['total_spent'] = (float) $stmt->fetchColumn();

        } catch (PDOException $e) {
            // Orders table might not exist yet
            logMessage('debug', 'Could not fetch order stats: ' . $e->getMessage());
        }

        try {
            $db = $this->db();

            // Wishlist count
            $stmt = $db->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
            $stmt->execute([$this->currentUser->id]);
            $stats['wishlist'] = (int) $stmt->fetchColumn();

        } catch (PDOException $e) {
            logMessage('debug', 'Could not fetch wishlist count: ' . $e->getMessage());
        }

        try {
            $db = $this->db();

            // Addresses count
            $stmt = $db->prepare("SELECT COUNT(*) FROM user_addresses WHERE user_id = ?");
            $stmt->execute([$this->currentUser->id]);
            $stats['addresses'] = (int) $stmt->fetchColumn();

        } catch (PDOException $e) {
            logMessage('debug', 'Could not fetch addresses count: ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Get recent orders
     */
    private function getRecentOrders(int $limit): array
    {
        try {
            return $this->currentUser->getOrders($limit);
        } catch (PDOException $e) {
            logMessage('debug', 'Could not fetch recent orders: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(int $limit): array
    {
        try {
            $db = $this->db();
            $stmt = $db->prepare("
                SELECT type, description, created_at
                FROM activity_logs
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$this->currentUser->id, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            logMessage('debug', 'Could not fetch activity: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Orders List
     */
    public function orders(): void
    {
        if (!$this->currentUser) {
            $this->handleInvalidSession();
            return;
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $status = $_GET['status'] ?? '';

        $orders = [];
        $total = 0;

        try {
            $db = $this->db();

            // Build query based on status filter
            $whereClause = "WHERE user_id = ?";
            $params = [$this->currentUser->id];

            if ($status && in_array($status, ['pending', 'processing', 'paid', 'shipped', 'delivered', 'cancelled', 'refunded'])) {
                $whereClause .= " AND status = ?";
                $params[] = $status;
            }

            // Count total
            $stmt = $db->prepare("SELECT COUNT(*) FROM orders $whereClause");
            $stmt->execute($params);
            $total = (int) $stmt->fetchColumn();

            // Get orders with pagination
            $params[] = $perPage;
            $params[] = $offset;
            $stmt = $db->prepare("
                SELECT * FROM orders
                $whereClause
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute($params);
            $orders = $stmt->fetchAll();

        } catch (PDOException $e) {
            logMessage('error', 'Could not fetch orders: ' . $e->getMessage());
            flash('error', 'Unable to load orders. Please try again.');
        }

        $this->layout('main');
        $this->view('pages/account/orders', [
            'meta_title' => 'My Orders | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'orders' => $orders,
            'currentStatus' => $status,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    /**
     * Order Detail
     */
    public function orderDetail(string $id): void
    {
        if (!$this->currentUser) {
            $this->handleInvalidSession();
            return;
        }

        try {
            $order = Order::find((int) $id);

            if (!$order || $order->user_id !== $this->currentUser->id) {
                $this->show404('Order not found');
                return;
            }

            $items = $order->getItems();
            $statusHistory = $order->getStatusHistory();
            $payment = $order->getPayment();

            $this->layout('main');
            $this->view('pages/account/order-detail', [
                'meta_title' => 'Order #' . $order->order_number . ' | ' . config('app.name'),
                'meta_robots' => 'noindex, nofollow',
                'order' => $order,
                'items' => $items,
                'statusHistory' => $statusHistory,
                'payment' => $payment,
            ]);

        } catch (PDOException $e) {
            logMessage('error', 'Could not fetch order detail: ' . $e->getMessage());
            $this->show404('Order not found');
        }
    }

    /**
     * Show 404 error page
     */
    private function show404(string $message = 'Page not found'): void
    {
        http_response_code(404);
        $this->layout('main');
        $this->view('errors/404', ['message' => $message]);
    }

    /**
     * Order Invoice
     */
    public function invoice(string $id): void
    {
        if (!$this->currentUser) {
            http_response_code(401);
            exit;
        }

        try {
            $order = Order::find((int) $id);

            if (!$order || $order->user_id !== $this->currentUser->id) {
                http_response_code(404);
                exit;
            }

            $items = $order->getItems();

            header('Content-Type: text/html; charset=utf-8');
            include APP_PATH . '/Views/pages/account/invoice.php';
            exit;

        } catch (PDOException $e) {
            logMessage('error', 'Could not generate invoice: ' . $e->getMessage());
            http_response_code(500);
            exit;
        }
    }

    /**
     * Addresses Management
     */
    public function addresses(): void
    {
        if (!$this->currentUser) {
            $this->handleInvalidSession();
            return;
        }

        $addresses = [];
        try {
            $addresses = $this->currentUser->getAddresses();
        } catch (PDOException $e) {
            logMessage('error', 'Could not fetch addresses: ' . $e->getMessage());
        }

        $this->layout('main');
        $this->view('pages/account/addresses', [
            'meta_title' => 'My Addresses | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'addresses' => $addresses,
            'provinces' => $this->getSouthAfricanProvinces(),
        ]);
    }

    /**
     * Get South African provinces
     */
    private function getSouthAfricanProvinces(): array
    {
        return [
            'EC' => 'Eastern Cape',
            'FS' => 'Free State',
            'GP' => 'Gauteng',
            'KZN' => 'KwaZulu-Natal',
            'LP' => 'Limpopo',
            'MP' => 'Mpumalanga',
            'NC' => 'Northern Cape',
            'NW' => 'North West',
            'WC' => 'Western Cape',
        ];
    }

    /**
     * Save Address (Create/Update)
     */
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
                return;
            }
            flash('error', 'Please correct the errors below.');
            $this->redirect('/account/addresses');
            return;
        }

        try {
            $db = $this->db();
            $userId = $this->currentUser->id;
            $addressId = $_POST['address_id'] ?? null;
            $isDefault = !empty($_POST['is_default']);
            $type = in_array($_POST['type'] ?? '', ['billing', 'shipping']) ? $_POST['type'] : 'shipping';

            // If setting as default, unset other defaults of same type
            if ($isDefault) {
                $stmt = $db->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ? AND type = ?");
                $stmt->execute([$userId, $type]);
            }

            $data = [
                'type' => $type,
                'is_default' => $isDefault ? 1 : 0,
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'company' => trim($_POST['company'] ?? '') ?: null,
                'address_line_1' => trim($_POST['address_line_1']),
                'address_line_2' => trim($_POST['address_line_2'] ?? '') ?: null,
                'city' => trim($_POST['city']),
                'province' => $_POST['province'],
                'postal_code' => trim($_POST['postal_code']),
                'country' => $_POST['country'] ?? 'ZA',
                'phone' => trim($_POST['phone'] ?? '') ?: null,
            ];

            if ($addressId) {
                // Verify ownership before update
                $stmt = $db->prepare("SELECT id FROM user_addresses WHERE id = ? AND user_id = ?");
                $stmt->execute([(int) $addressId, $userId]);

                if (!$stmt->fetch()) {
                    if (isAjax()) {
                        $this->json(['success' => false, 'error' => 'Address not found'], 404);
                        return;
                    }
                    flash('error', 'Address not found.');
                    $this->redirect('/account/addresses');
                    return;
                }

                $stmt = $db->prepare("
                    UPDATE user_addresses SET
                        type = :type, is_default = :is_default, first_name = :first_name,
                        last_name = :last_name, company = :company, address_line_1 = :address_line_1,
                        address_line_2 = :address_line_2, city = :city, province = :province,
                        postal_code = :postal_code, country = :country, phone = :phone, updated_at = NOW()
                    WHERE id = :id AND user_id = :user_id
                ");
                $data['id'] = (int) $addressId;
                $data['user_id'] = $userId;
                $stmt->execute($data);

                $message = 'Address updated successfully.';
            } else {
                $stmt = $db->prepare("
                    INSERT INTO user_addresses
                        (user_id, type, is_default, first_name, last_name, company,
                         address_line_1, address_line_2, city, province, postal_code, country, phone)
                    VALUES
                        (:user_id, :type, :is_default, :first_name, :last_name, :company,
                         :address_line_1, :address_line_2, :city, :province, :postal_code, :country, :phone)
                ");
                $data['user_id'] = $userId;
                $stmt->execute($data);

                $message = 'Address added successfully.';
            }

            if (isAjax()) {
                $this->json(['success' => true, 'message' => $message]);
                return;
            }

            flash('success', $message);
            $this->redirect('/account/addresses');

        } catch (PDOException $e) {
            logMessage('error', 'Failed to save address: ' . $e->getMessage());

            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'Failed to save address'], 500);
                return;
            }

            flash('error', 'Failed to save address. Please try again.');
            $this->redirect('/account/addresses');
        }
    }

    /**
     * Delete Address
     */
    public function deleteAddress(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        try {
            $db = $this->db();
            $stmt = $db->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([(int) $id, $this->currentUser->id]);

            $deleted = $stmt->rowCount() > 0;

            if (isAjax()) {
                $this->json(['success' => $deleted]);
                return;
            }

            flash($deleted ? 'success' : 'error', $deleted ? 'Address deleted.' : 'Address not found.');
            $this->redirect('/account/addresses');

        } catch (PDOException $e) {
            logMessage('error', 'Failed to delete address: ' . $e->getMessage());

            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'Failed to delete address'], 500);
                return;
            }

            flash('error', 'Failed to delete address.');
            $this->redirect('/account/addresses');
        }
    }

    /**
     * Account Settings Page
     */
    public function settings(): void
    {
        if (!$this->currentUser) {
            $this->handleInvalidSession();
            return;
        }

        $this->layout('main');
        $this->view('pages/account/settings', [
            'meta_title' => 'Account Settings | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'user' => $this->currentUser,
        ]);
    }

    /**
     * Update Account Settings
     */
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
            flash('error', 'Please correct the errors below.');
            $this->redirect('/account/settings');
            return;
        }

        try {
            $newEmail = strtolower(trim($_POST['email']));

            // Check if email changed and is unique
            if ($newEmail !== strtolower($this->currentUser->email)) {
                $existing = User::findByEmail($newEmail);
                if ($existing && $existing->id !== $this->currentUser->id) {
                    flash('error', 'This email address is already in use.');
                    $this->redirect('/account/settings');
                    return;
                }
            }

            $this->currentUser->first_name = trim($_POST['first_name']);
            $this->currentUser->last_name = trim($_POST['last_name']);
            $this->currentUser->email = $newEmail;
            $this->currentUser->phone = trim($_POST['phone'] ?? '') ?: null;
            $this->currentUser->save();

            // Clear user cache
            unset($_SESSION['_user_cache']);

            // Log activity
            $this->logUserActivity('profile_updated', 'Profile information updated');

            flash('success', 'Your profile has been updated.');
            $this->redirect('/account/settings');

        } catch (PDOException $e) {
            logMessage('error', 'Failed to update settings: ' . $e->getMessage());
            flash('error', 'Failed to update settings. Please try again.');
            $this->redirect('/account/settings');
        }
    }

    /**
     * Update Password
     */
    public function updatePassword(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['new_password_confirmation'] ?? '';

        // Verify current password
        if (!$this->currentUser->verifyPassword($currentPassword)) {
            flash('error', 'Current password is incorrect.');
            $this->redirect('/account/settings');
            return;
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            flash('error', 'New password must be at least 8 characters.');
            $this->redirect('/account/settings');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            flash('error', 'New passwords do not match.');
            $this->redirect('/account/settings');
            return;
        }

        // Check password is not the same as current
        if ($this->currentUser->verifyPassword($newPassword)) {
            flash('error', 'New password must be different from current password.');
            $this->redirect('/account/settings');
            return;
        }

        try {
            $this->currentUser->updatePassword($newPassword);

            // Log activity
            $this->logUserActivity('password_changed', 'Password changed');

            flash('success', 'Your password has been updated.');
            $this->redirect('/account/settings');

        } catch (PDOException $e) {
            logMessage('error', 'Failed to update password: ' . $e->getMessage());
            flash('error', 'Failed to update password. Please try again.');
            $this->redirect('/account/settings');
        }
    }

    /**
     * Security Page
     */
    public function security(): void
    {
        if (!$this->currentUser) {
            $this->handleInvalidSession();
            return;
        }

        $sessions = [];
        $loginHistory = [];
        $warnings = [];

        try {
            $sessionManager = new SessionManager();
            $sessions = $sessionManager->getUserSessions($this->currentUser->id);
            $loginHistory = $sessionManager->getLoginHistory($this->currentUser->id, 10);
            $warnings = $sessionManager->checkSuspiciousActivity($this->currentUser->id);
        } catch (\Exception $e) {
            logMessage('error', 'Failed to load security data: ' . $e->getMessage());
        }

        $this->layout('main');
        $this->view('pages/account/security', [
            'meta_title' => 'Security | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'user' => $this->currentUser,
            'sessions' => $sessions,
            'loginHistory' => $loginHistory,
            'warnings' => $warnings,
            'mfaEnabled' => (bool) $this->currentUser->mfa_enabled,
        ]);
    }

    /**
     * Activity Log Page
     */
    public function activity(): void
    {
        if (!$this->currentUser) {
            $this->handleInvalidSession();
            return;
        }

        $activities = [];

        try {
            $sessionManager = new SessionManager();
            $activities = $sessionManager->getActivityLogs($this->currentUser->id, 50);
        } catch (\Exception $e) {
            logMessage('error', 'Failed to load activity log: ' . $e->getMessage());
        }

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

        try {
            $sessionManager = new SessionManager();
            $success = $sessionManager->terminateSession($this->currentUser->id, (int) $id);

            if ($success) {
                $this->logUserActivity('session_terminated', 'Terminated session');
            }

            if (isAjax()) {
                $this->json(['success' => $success]);
                return;
            }

            flash($success ? 'success' : 'error', $success ? 'Session terminated.' : 'Failed to terminate session.');
            $this->redirect('/account/security');

        } catch (\Exception $e) {
            logMessage('error', 'Failed to terminate session: ' . $e->getMessage());

            if (isAjax()) {
                $this->json(['success' => false], 500);
                return;
            }

            flash('error', 'Failed to terminate session.');
            $this->redirect('/account/security');
        }
    }

    /**
     * Terminate all other sessions
     */
    public function terminateOtherSessions(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        try {
            $sessionManager = new SessionManager();
            $count = $sessionManager->terminateOtherSessions($this->currentUser->id);

            $this->logUserActivity('sessions_terminated', "Terminated $count other sessions");

            if (isAjax()) {
                $this->json(['success' => true, 'terminated' => $count]);
                return;
            }

            flash('success', "Terminated $count other session(s).");
            $this->redirect('/account/security');

        } catch (\Exception $e) {
            logMessage('error', 'Failed to terminate sessions: ' . $e->getMessage());

            if (isAjax()) {
                $this->json(['success' => false], 500);
                return;
            }

            flash('error', 'Failed to terminate sessions.');
            $this->redirect('/account/security');
        }
    }

    /**
     * Show MFA setup page
     */
    public function setupMfa(): void
    {
        if (!$this->currentUser) {
            $this->handleInvalidSession();
            return;
        }

        if ($this->currentUser->mfa_enabled) {
            flash('info', 'Two-factor authentication is already enabled.');
            $this->redirect('/account/security');
            return;
        }

        try {
            $mfaService = new MfaService();
            $secret = $mfaService->generateSecret();

            // Store secret temporarily in session
            $_SESSION['mfa_setup_secret'] = $secret;

            $qrCodeUrl = $mfaService->getQrCodeUrl(
                $secret,
                $this->currentUser->email,
                config('app.name', 'Pricetag')
            );

            $this->layout('main');
            $this->view('pages/account/mfa-setup', [
                'meta_title' => 'Setup Two-Factor Authentication | ' . config('app.name'),
                'meta_robots' => 'noindex, nofollow',
                'secret' => $secret,
                'qrCodeUrl' => $qrCodeUrl,
            ]);

        } catch (\Exception $e) {
            logMessage('error', 'Failed to setup MFA: ' . $e->getMessage());
            flash('error', 'Failed to initialize MFA setup. Please try again.');
            $this->redirect('/account/security');
        }
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
        $code = trim($_POST['code'] ?? '');

        if (!$secret) {
            flash('error', 'MFA setup session expired. Please try again.');
            $this->redirect('/account/security');
            return;
        }

        try {
            $mfaService = new MfaService();

            if (!$mfaService->verifyCode($secret, $code)) {
                flash('error', 'Invalid verification code. Please try again.');
                $this->redirect('/account/mfa/setup');
                return;
            }

            // Generate backup codes
            $backupCodes = $mfaService->generateBackupCodes();
            $hashedBackupCodes = $mfaService->hashBackupCodes($backupCodes);

            $db = $this->db();

            // Enable MFA for user
            $stmt = $db->prepare("
                UPDATE users SET mfa_enabled = 1, mfa_secret = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$secret, $this->currentUser->id]);

            // Store backup codes
            $stmt = $db->prepare("DELETE FROM user_mfa_backup_codes WHERE user_id = ?");
            $stmt->execute([$this->currentUser->id]);

            $stmt = $db->prepare("
                INSERT INTO user_mfa_backup_codes (user_id, code_hash, created_at)
                VALUES (?, ?, NOW())
            ");
            foreach ($hashedBackupCodes as $hashedCode) {
                $stmt->execute([$this->currentUser->id, $hashedCode]);
            }

            // Clear session secret and user cache
            unset($_SESSION['mfa_setup_secret'], $_SESSION['_user_cache']);

            // Log activity
            $this->logUserActivity('mfa_enabled', 'Two-factor authentication enabled');

            // Show backup codes once
            $this->layout('main');
            $this->view('pages/account/mfa-backup-codes', [
                'meta_title' => 'Backup Codes | ' . config('app.name'),
                'meta_robots' => 'noindex, nofollow',
                'backupCodes' => $backupCodes,
            ]);

        } catch (\Exception $e) {
            logMessage('error', 'Failed to enable MFA: ' . $e->getMessage());
            flash('error', 'Failed to enable two-factor authentication. Please try again.');
            $this->redirect('/account/mfa/setup');
        }
    }

    /**
     * Disable MFA
     */
    public function disableMfa(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        // Require password confirmation
        if (!$this->currentUser->verifyPassword($_POST['password'] ?? '')) {
            flash('error', 'Incorrect password.');
            $this->redirect('/account/security');
            return;
        }

        try {
            $db = $this->db();

            // Disable MFA
            $stmt = $db->prepare("
                UPDATE users SET mfa_enabled = 0, mfa_secret = NULL, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$this->currentUser->id]);

            // Delete backup codes
            $stmt = $db->prepare("DELETE FROM user_mfa_backup_codes WHERE user_id = ?");
            $stmt->execute([$this->currentUser->id]);

            // Clear user cache
            unset($_SESSION['_user_cache']);

            // Log activity
            $this->logUserActivity('mfa_disabled', 'Two-factor authentication disabled');

            flash('success', 'Two-factor authentication has been disabled.');
            $this->redirect('/account/security');

        } catch (\Exception $e) {
            logMessage('error', 'Failed to disable MFA: ' . $e->getMessage());
            flash('error', 'Failed to disable two-factor authentication.');
            $this->redirect('/account/security');
        }
    }

    /**
     * Regenerate backup codes
     */
    public function regenerateBackupCodes(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        if (!$this->currentUser->mfa_enabled) {
            flash('error', 'Two-factor authentication is not enabled.');
            $this->redirect('/account/security');
            return;
        }

        // Require password confirmation
        if (!$this->currentUser->verifyPassword($_POST['password'] ?? '')) {
            flash('error', 'Incorrect password.');
            $this->redirect('/account/security');
            return;
        }

        try {
            $mfaService = new MfaService();
            $backupCodes = $mfaService->generateBackupCodes();
            $hashedBackupCodes = $mfaService->hashBackupCodes($backupCodes);

            $db = $this->db();

            // Replace backup codes
            $stmt = $db->prepare("DELETE FROM user_mfa_backup_codes WHERE user_id = ?");
            $stmt->execute([$this->currentUser->id]);

            $stmt = $db->prepare("
                INSERT INTO user_mfa_backup_codes (user_id, code_hash, created_at)
                VALUES (?, ?, NOW())
            ");
            foreach ($hashedBackupCodes as $hashedCode) {
                $stmt->execute([$this->currentUser->id, $hashedCode]);
            }

            // Log activity
            $this->logUserActivity('backup_codes_regenerated', 'MFA backup codes regenerated');

            // Show new backup codes
            $this->layout('main');
            $this->view('pages/account/mfa-backup-codes', [
                'meta_title' => 'New Backup Codes | ' . config('app.name'),
                'meta_robots' => 'noindex, nofollow',
                'backupCodes' => $backupCodes,
                'regenerated' => true,
            ]);

        } catch (\Exception $e) {
            logMessage('error', 'Failed to regenerate backup codes: ' . $e->getMessage());
            flash('error', 'Failed to regenerate backup codes.');
            $this->redirect('/account/security');
        }
    }

    /**
     * Log user activity
     */
    private function logUserActivity(string $type, string $description): void
    {
        try {
            $sessionManager = new SessionManager();
            $sessionManager->logActivity($this->currentUser->id, $type, $description);
        } catch (\Exception $e) {
            logMessage('debug', 'Failed to log activity: ' . $e->getMessage());
        }
    }
}
