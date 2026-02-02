<?php
/**
 * Session Manager
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Manages user sessions, device tracking, and session fingerprinting.
 */

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

class SessionManager
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create or update session record
     */
    public function trackSession(int $userId): void
    {
        $sessionId = session_id();
        $fingerprint = $this->generateFingerprint();
        $ip = clientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Check if session exists
        $stmt = $this->db->prepare("
            SELECT id FROM user_sessions
            WHERE user_id = ? AND session_id = ?
        ");
        $stmt->execute([$userId, $sessionId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update existing session
            $stmt = $this->db->prepare("
                UPDATE user_sessions
                SET last_activity = NOW(), ip_address = ?, device_fingerprint = ?
                WHERE id = ?
            ");
            $stmt->execute([$ip, $fingerprint, $existing['id']]);
        } else {
            // Create new session record
            $stmt = $this->db->prepare("
                INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, device_fingerprint, last_activity)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $sessionId, $ip, $userAgent, $fingerprint]);
        }

        // Store fingerprint in session for validation
        $_SESSION['device_fingerprint'] = $fingerprint;
    }

    /**
     * Generate device fingerprint
     */
    public function generateFingerprint(): string
    {
        $components = [
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
        ];

        return hash('sha256', implode('|', $components));
    }

    /**
     * Validate session fingerprint
     */
    public function validateFingerprint(): bool
    {
        if (!isset($_SESSION['device_fingerprint'])) {
            return true; // No fingerprint set yet
        }

        $currentFingerprint = $this->generateFingerprint();
        return hash_equals($_SESSION['device_fingerprint'], $currentFingerprint);
    }

    /**
     * Get all active sessions for a user
     */
    public function getUserSessions(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                session_id,
                ip_address,
                user_agent,
                last_activity,
                created_at,
                CASE WHEN session_id = ? THEN 1 ELSE 0 END as is_current
            FROM user_sessions
            WHERE user_id = ?
            ORDER BY last_activity DESC
        ");
        $stmt->execute([session_id(), $userId]);
        $sessions = $stmt->fetchAll();

        // Parse user agent for device info
        foreach ($sessions as &$session) {
            $session['device'] = $this->parseUserAgent($session['user_agent']);
        }

        return $sessions;
    }

    /**
     * Terminate a specific session
     */
    public function terminateSession(int $userId, int $sessionId): bool
    {
        $stmt = $this->db->prepare("
            SELECT session_id FROM user_sessions
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$sessionId, $userId]);
        $session = $stmt->fetch();

        if (!$session) {
            return false;
        }

        // Delete session file if it exists
        $sessionFile = STORAGE_PATH . '/sessions/sess_' . $session['session_id'];
        if (file_exists($sessionFile)) {
            unlink($sessionFile);
        }

        // Delete session record
        $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE id = ?");
        $stmt->execute([$sessionId]);

        return true;
    }

    /**
     * Terminate all other sessions for a user
     */
    public function terminateOtherSessions(int $userId): int
    {
        $currentSessionId = session_id();

        // Get all other sessions
        $stmt = $this->db->prepare("
            SELECT id, session_id FROM user_sessions
            WHERE user_id = ? AND session_id != ?
        ");
        $stmt->execute([$userId, $currentSessionId]);
        $sessions = $stmt->fetchAll();

        $terminated = 0;
        foreach ($sessions as $session) {
            // Delete session file
            $sessionFile = STORAGE_PATH . '/sessions/sess_' . $session['session_id'];
            if (file_exists($sessionFile)) {
                unlink($sessionFile);
            }
            $terminated++;
        }

        // Delete session records
        $stmt = $this->db->prepare("
            DELETE FROM user_sessions
            WHERE user_id = ? AND session_id != ?
        ");
        $stmt->execute([$userId, $currentSessionId]);

        return $terminated;
    }

    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions(): int
    {
        $lifetime = (int) env('SESSION_LIFETIME', 120) * 60;

        $stmt = $this->db->prepare("
            DELETE FROM user_sessions
            WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$lifetime]);

        return $stmt->rowCount();
    }

    /**
     * Get login history for a user
     */
    public function getLoginHistory(int $userId, int $limit = 20): array
    {
        // Get user email first
        $stmt = $this->db->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT
                ip_address,
                user_agent,
                success,
                created_at
            FROM login_attempts
            WHERE email = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$user['email'], $limit]);
        $history = $stmt->fetchAll();

        // Parse user agent for device info
        foreach ($history as &$record) {
            $record['device'] = $this->parseUserAgent($record['user_agent']);
        }

        return $history;
    }

    /**
     * Get activity logs for a user
     */
    public function getActivityLogs(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT type, description, ip_address, user_agent, created_at
            FROM activity_logs
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Parse user agent string to extract device info
     */
    private function parseUserAgent(string $userAgent): array
    {
        $device = [
            'browser' => 'Unknown',
            'platform' => 'Unknown',
            'device_type' => 'Desktop',
        ];

        // Detect browser
        if (preg_match('/Firefox/i', $userAgent)) {
            $device['browser'] = 'Firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $device['browser'] = 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $device['browser'] = 'Safari';
        } elseif (preg_match('/Opera|OPR/i', $userAgent)) {
            $device['browser'] = 'Opera';
        } elseif (preg_match('/Edge/i', $userAgent)) {
            $device['browser'] = 'Edge';
        } elseif (preg_match('/MSIE|Trident/i', $userAgent)) {
            $device['browser'] = 'Internet Explorer';
        }

        // Detect platform
        if (preg_match('/Windows/i', $userAgent)) {
            $device['platform'] = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS/i', $userAgent)) {
            $device['platform'] = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $device['platform'] = 'Linux';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $device['platform'] = 'Android';
            $device['device_type'] = 'Mobile';
        } elseif (preg_match('/iPhone|iPad/i', $userAgent)) {
            $device['platform'] = 'iOS';
            $device['device_type'] = preg_match('/iPad/i', $userAgent) ? 'Tablet' : 'Mobile';
        }

        // Detect mobile
        if (preg_match('/Mobile|Android|iPhone/i', $userAgent) && $device['device_type'] === 'Desktop') {
            $device['device_type'] = 'Mobile';
        }

        return $device;
    }

    /**
     * Log activity
     */
    public function logActivity(int $userId, string $type, string $description): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO activity_logs (user_id, type, description, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId,
            $type,
            $description,
            clientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }

    /**
     * Check for suspicious activity
     */
    public function checkSuspiciousActivity(int $userId): array
    {
        $warnings = [];

        // Check for multiple IPs in short time
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT ip_address) as ip_count
            FROM login_attempts
            WHERE email = (SELECT email FROM users WHERE id = ?)
            AND success = 1
            AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        if ($result['ip_count'] > 3) {
            $warnings[] = [
                'type' => 'multiple_ips',
                'message' => 'Login detected from ' . $result['ip_count'] . ' different IP addresses in the last hour',
            ];
        }

        // Check for new device login
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM user_sessions
            WHERE user_id = ?
            AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        if ($result['count'] > 3) {
            $warnings[] = [
                'type' => 'multiple_devices',
                'message' => 'Multiple new device logins detected',
            ];
        }

        return $warnings;
    }
}
