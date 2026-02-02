<?php
/**
 * MFA Service (Multi-Factor Authentication)
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Time-based One-Time Password (TOTP) implementation for 2FA.
 */

declare(strict_types=1);

namespace App\Services;

class MfaService
{
    private const DIGITS = 6;
    private const PERIOD = 30;
    private const ALGORITHM = 'sha1';
    private const SECRET_LENGTH = 32;

    /**
     * Generate a new secret key
     */
    public function generateSecret(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';

        for ($i = 0; $i < self::SECRET_LENGTH; $i++) {
            $secret .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $secret;
    }

    /**
     * Generate QR code URL for authenticator apps
     */
    public function getQrCodeUrl(string $secret, string $email, string $issuer = 'Pricetag'): string
    {
        $issuer = rawurlencode($issuer);
        $email = rawurlencode($email);

        $otpauthUrl = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=%s&digits=%d&period=%d',
            $issuer,
            $email,
            $secret,
            $issuer,
            strtoupper(self::ALGORITHM),
            self::DIGITS,
            self::PERIOD
        );

        // Return URL for QR code generation service
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpauthUrl);
    }

    /**
     * Verify a TOTP code
     */
    public function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\s+/', '', $code);

        if (strlen($code) !== self::DIGITS || !ctype_digit($code)) {
            return false;
        }

        $timestamp = time();

        // Check current time and +/- window periods
        for ($i = -$window; $i <= $window; $i++) {
            $checkTime = $timestamp + ($i * self::PERIOD);
            $calculatedCode = $this->generateCode($secret, $checkTime);

            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate TOTP code for a given time
     */
    public function generateCode(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $counter = floor($timestamp / self::PERIOD);

        // Decode base32 secret
        $key = $this->base32Decode($secret);

        // Pack counter as 64-bit big-endian
        $data = pack('N*', 0, $counter);

        // Calculate HMAC
        $hash = hash_hmac(self::ALGORITHM, $data, $key, true);

        // Dynamic truncation
        $offset = ord($hash[strlen($hash) - 1]) & 0x0f;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % pow(10, self::DIGITS);

        return str_pad((string) $code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Generate backup codes
     */
    public function generateBackupCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $code = '';
            for ($j = 0; $j < 8; $j++) {
                $code .= random_int(0, 9);
            }
            $codes[] = substr($code, 0, 4) . '-' . substr($code, 4, 4);
        }

        return $codes;
    }

    /**
     * Hash backup codes for storage
     */
    public function hashBackupCodes(array $codes): array
    {
        return array_map(function ($code) {
            return password_hash(str_replace('-', '', $code), PASSWORD_DEFAULT);
        }, $codes);
    }

    /**
     * Verify a backup code against hashed codes
     */
    public function verifyBackupCode(string $code, array $hashedCodes): int|false
    {
        $code = str_replace('-', '', $code);

        foreach ($hashedCodes as $index => $hashedCode) {
            if (password_verify($code, $hashedCode)) {
                return $index;
            }
        }

        return false;
    }

    /**
     * Base32 decode
     */
    private function base32Decode(string $input): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input = strtoupper($input);
        $input = str_replace('=', '', $input);

        $output = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < strlen($input); $i++) {
            $buffer <<= 5;
            $buffer += strpos($alphabet, $input[$i]);
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xff);
            }
        }

        return $output;
    }
}
