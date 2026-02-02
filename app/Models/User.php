<?php
/**
 * User Model
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class User extends Model
{
    protected static string $table = 'users';

    protected static array $fillable = [
        'email', 'password', 'first_name', 'last_name', 'phone',
        'role', 'status', 'avatar', 'mfa_enabled', 'mfa_secret',
        'email_verified_at', 'password_changed_at', 'last_login_at', 'last_login_ip',
        'remember_token'
    ];

    protected static array $hidden = ['password', 'mfa_secret', 'remember_token'];

    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?self
    {
        return self::where('email', $email)->first();
    }

    /**
     * Create a new user
     */
    public static function register(array $data): self
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['status'] = 'pending';
        $data['role'] = 'customer';

        return self::create($data);
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * Update password
     */
    public function updatePassword(string $password): bool
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->password_changed_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Record login
     */
    public function recordLogin(string $ip): void
    {
        $this->last_login_at = date('Y-m-d H:i:s');
        $this->last_login_ip = $ip;
        $this->save();

        // Log activity
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO login_attempts (email, ip_address, user_agent, success) VALUES (?, ?, ?, 1)");
        $stmt->execute([$this->email, $ip, $_SERVER['HTTP_USER_AGENT'] ?? '']);
    }

    /**
     * Get full name
     */
    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Check if user is verified
     */
    public function isVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    /**
     * Get addresses
     */
    public function getAddresses(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    /**
     * Get default shipping address
     */
    public function getDefaultShippingAddress(): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM user_addresses WHERE user_id = ? AND type = 'shipping' AND is_default = 1 LIMIT 1");
        $stmt->execute([$this->id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get orders
     */
    public function getOrders(int $limit = 10): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$this->id, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Generate email verification token
     */
    public function generateVerificationToken(): string
    {
        $token = bin2hex(random_bytes(32));

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM email_verifications WHERE user_id = ?");
        $stmt->execute([$this->id]);

        $stmt = $db->prepare("INSERT INTO email_verifications (user_id, token) VALUES (?, ?)");
        $stmt->execute([$this->id, $token]);

        return $token;
    }

    /**
     * Verify email with token
     */
    public static function verifyEmailToken(string $token): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT user_id FROM email_verifications WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stmt->execute([$token]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        $user = self::find($result['user_id']);
        if ($user) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->status = 'active';
            $user->save();

            $stmt = $db->prepare("DELETE FROM email_verifications WHERE user_id = ?");
            $stmt->execute([$user->id]);
        }

        return $user;
    }

    /**
     * Generate password reset token
     */
    public function generatePasswordResetToken(): string
    {
        $token = bin2hex(random_bytes(32));

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$this->email]);

        $stmt = $db->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
        $stmt->execute([$this->email, $token]);

        return $token;
    }

    /**
     * Find user by password reset token
     */
    public static function findByPasswordResetToken(string $token): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT email FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt->execute([$token]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        return self::findByEmail($result['email']);
    }

    /**
     * Clear password reset token
     */
    public function clearPasswordResetToken(): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$this->email]);
    }
}
