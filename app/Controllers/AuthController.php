<?php
/**
 * Auth Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;
use App\Models\Cart;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (auth()) {
            $this->redirect('/account');
            return;
        }

        $this->layout('main');
        $this->view('pages/auth/login', [
            'meta_title' => 'Login | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
        ]);
    }

    public function login(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $validation = $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please check your input');
            $this->redirect('/login');
            return;
        }

        $email = $_POST['email'];
        $password = $_POST['password'];
        $remember = !empty($_POST['remember']);

        // Check rate limiting
        if (!$this->checkLoginAttempts($email)) {
            flash('error', 'Too many login attempts. Please try again later.');
            $this->redirect('/login');
            return;
        }

        $user = User::findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            $this->recordLoginAttempt($email, false);
            flash('error', 'Invalid email or password');
            $this->redirect('/login');
            return;
        }

        if (!$user->isActive()) {
            if ($user->status === 'pending') {
                flash('error', 'Please verify your email address');
            } else {
                flash('error', 'Your account has been suspended');
            }
            $this->redirect('/login');
            return;
        }

        // Login successful
        $this->recordLoginAttempt($email, true);
        $user->recordLogin(clientIp());

        // Set session
        $_SESSION['user_id'] = $user->id;
        unset($_SESSION['_user_cache']);

        // Merge cart
        $cart = new Cart();
        $cart->mergeWithUser($user->id);

        // Remember me
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $user->remember_token = $token;
            $user->save();

            setcookie('remember_token', $token, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        // Log activity
        $this->logActivity($user->id, 'login', 'User logged in');

        // Redirect
        $intended = $_SESSION['intended_url'] ?? '/account';
        unset($_SESSION['intended_url']);

        flash('success', 'Welcome back, ' . $user->first_name . '!');
        $this->redirect($intended);
    }

    public function showRegister(): void
    {
        if (auth()) {
            $this->redirect('/account');
            return;
        }

        $this->layout('main');
        $this->view('pages/auth/register', [
            'meta_title' => 'Create Account | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
        ]);
    }

    public function register(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $validation = $this->validate([
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!$validation['valid']) {
            $this->redirect('/register');
            return;
        }

        // Validate password strength
        $password = $_POST['password'];
        if (!$this->validatePasswordStrength($password)) {
            flash('error', 'Password must contain at least one uppercase letter and one number');
            $this->redirect('/register');
            return;
        }

        // Create user
        $user = User::register([
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'],
            'password' => $password,
        ]);

        // Generate verification token
        $token = $user->generateVerificationToken();

        // TODO: Send verification email
        // $this->sendVerificationEmail($user, $token);

        // For now, auto-verify in development
        if (env('APP_DEBUG', false)) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->status = 'active';
            $user->save();

            $_SESSION['user_id'] = $user->id;

            $cart = new Cart();
            $cart->mergeWithUser($user->id);

            flash('success', 'Account created successfully!');
            $this->redirect('/account');
            return;
        }

        flash('success', 'Account created! Please check your email to verify your account.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;

        // Clear remember token
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $user->remember_token = null;
                $user->save();
            }
            $this->logActivity($userId, 'logout', 'User logged out');
        }

        // Clear session
        session_destroy();

        // Clear remember cookie
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        flash('success', 'You have been logged out');
        $this->redirect('/');
    }

    public function showForgotPassword(): void
    {
        $this->layout('main');
        $this->view('pages/auth/forgot-password', [
            'meta_title' => 'Forgot Password | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
        ]);
    }

    public function sendResetLink(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $email = $_POST['email'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address');
            $this->redirect('/forgot-password');
            return;
        }

        $user = User::findByEmail($email);

        if ($user) {
            $token = $user->generatePasswordResetToken();
            // TODO: Send reset email
            // $this->sendPasswordResetEmail($user, $token);
        }

        // Always show success to prevent email enumeration
        flash('success', 'If an account exists with that email, you will receive a password reset link.');
        $this->redirect('/login');
    }

    public function showResetPassword(string $token): void
    {
        $user = User::findByPasswordResetToken($token);

        if (!$user) {
            flash('error', 'Invalid or expired reset link');
            $this->redirect('/forgot-password');
            return;
        }

        $this->layout('main');
        $this->view('pages/auth/reset-password', [
            'meta_title' => 'Reset Password | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'token' => $token,
        ]);
    }

    public function resetPassword(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        if (strlen($password) < 8) {
            flash('error', 'Password must be at least 8 characters');
            $this->redirect('/reset-password/' . $token);
            return;
        }

        if ($password !== $passwordConfirmation) {
            flash('error', 'Passwords do not match');
            $this->redirect('/reset-password/' . $token);
            return;
        }

        $user = User::findByPasswordResetToken($token);

        if (!$user) {
            flash('error', 'Invalid or expired reset link');
            $this->redirect('/forgot-password');
            return;
        }

        $user->updatePassword($password);
        $user->clearPasswordResetToken();

        $this->logActivity($user->id, 'password_reset', 'Password was reset');

        flash('success', 'Your password has been reset. Please login.');
        $this->redirect('/login');
    }

    public function verifyEmail(string $token): void
    {
        $user = User::verifyEmailToken($token);

        if ($user) {
            $this->logActivity($user->id, 'email_verified', 'Email address verified');
            flash('success', 'Your email has been verified. Please login.');
        } else {
            flash('error', 'Invalid or expired verification link');
        }

        $this->redirect('/login');
    }

    private function checkLoginAttempts(string $email): bool
    {
        $db = Database::getInstance();
        $maxAttempts = config('app.security.max_login_attempts', 5);
        $lockoutDuration = config('app.security.lockout_duration', 900);

        $stmt = $db->prepare("
            SELECT COUNT(*) FROM login_attempts
            WHERE email = ? AND success = 0 AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$email, $lockoutDuration]);

        return $stmt->fetchColumn() < $maxAttempts;
    }

    private function recordLoginAttempt(string $email, bool $success): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO login_attempts (email, ip_address, user_agent, success)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$email, clientIp(), $_SERVER['HTTP_USER_AGENT'] ?? '', $success ? 1 : 0]);
    }

    private function validatePasswordStrength(string $password): bool
    {
        $config = config('app.security', []);

        if ($config['password_require_uppercase'] ?? true) {
            if (!preg_match('/[A-Z]/', $password)) {
                return false;
            }
        }

        if ($config['password_require_number'] ?? true) {
            if (!preg_match('/[0-9]/', $password)) {
                return false;
            }
        }

        if ($config['password_require_special'] ?? false) {
            if (!preg_match('/[^A-Za-z0-9]/', $password)) {
                return false;
            }
        }

        return true;
    }

    private function logActivity(int $userId, string $type, string $description): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, type, description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $type, $description, clientIp(), $_SERVER['HTTP_USER_AGENT'] ?? '']);
    }
}
