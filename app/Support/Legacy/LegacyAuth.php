<?php

namespace App\Support\Legacy;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use PDO;
use Throwable;

class LegacyAuth
{
    private const SESSION_KEY = 'auth_user';

    private static ?bool $rateLimitTableAvailable = null;

    private static function pdo(): PDO
    {
        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }

    private static function config(string $key, mixed $default): mixed
    {
        return config('legacy_auth.'.$key, $default);
    }

    public static function minPasswordLength(): int
    {
        $value = (int) self::config('min_password_length', 12);

        return $value >= 8 ? $value : 12;
    }

    public static function passwordMeetsPolicy(string $password): bool
    {
        if (mb_strlen($password) < self::minPasswordLength()) {
            return false;
        }

        $hasLetter = (bool) preg_match('/[A-Za-z]/', $password);
        $hasDigit = (bool) preg_match('/[0-9]/', $password);

        return $hasLetter && $hasDigit;
    }

    public static function user(): ?array
    {
        $user = session(self::SESSION_KEY);

        return is_array($user) ? $user : null;
    }

    public static function logout(): void
    {
        session()->forget(self::SESSION_KEY);
        session()->invalidate();
        session()->regenerateToken();
    }

    private static function rateLimitIdentifier(string $email): string
    {
        return hash('sha256', strtolower(trim($email)));
    }

    private static function fallbackRateLimitKey(string $email): string
    {
        return 'legacy-login:'.self::rateLimitIdentifier($email).'|'.(request()->ip() ?? '0.0.0.0');
    }

    private static function isRateLimitAvailable(): bool
    {
        if (self::$rateLimitTableAvailable !== null) {
            return self::$rateLimitTableAvailable;
        }

        try {
            self::pdo()->query('SELECT 1 FROM auth_login_attempts LIMIT 1');
            self::$rateLimitTableAvailable = true;
        } catch (Throwable $e) {
            self::$rateLimitTableAvailable = false;
        }

        return self::$rateLimitTableAvailable;
    }

    private static function getLoginAttempt(string $email): ?array
    {
        if (! self::isRateLimitAvailable()) {
            return null;
        }

        $stmt = self::pdo()->prepare(
            'SELECT attempt_count, locked_until
             FROM auth_login_attempts
             WHERE identifier_hash = :identifier_hash AND ip_address = :ip_address
             LIMIT 1'
        );

        $stmt->execute([
            'identifier_hash' => self::rateLimitIdentifier($email),
            'ip_address' => request()->ip() ?? '0.0.0.0',
        ]);

        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    private static function lockMaxAttempts(): int
    {
        $value = (int) self::config('lock_max_attempts', 5);

        return $value > 0 ? $value : 5;
    }

    private static function lockSeconds(): int
    {
        $value = (int) self::config('lock_seconds', 900);

        return $value > 0 ? $value : 900;
    }

    public static function isLoginLocked(string $email): bool
    {
        if (! self::isRateLimitAvailable()) {
            return RateLimiter::tooManyAttempts(self::fallbackRateLimitKey($email), self::lockMaxAttempts());
        }

        $data = self::getLoginAttempt($email);

        if (! $data) {
            return false;
        }

        $lockedUntil = $data['locked_until'] ?? null;
        if ($lockedUntil === null) {
            return false;
        }

        return strtotime((string) $lockedUntil) > time();
    }

    public static function lockRemainingSeconds(string $email): int
    {
        if (! self::isRateLimitAvailable()) {
            return max(0, RateLimiter::availableIn(self::fallbackRateLimitKey($email)));
        }

        $data = self::getLoginAttempt($email);

        if (! $data || empty($data['locked_until'])) {
            return 0;
        }

        $seconds = strtotime((string) $data['locked_until']) - time();

        return $seconds > 0 ? $seconds : 0;
    }

    private static function registerLoginFailure(string $email): void
    {
        if (! self::isRateLimitAvailable()) {
            RateLimiter::hit(self::fallbackRateLimitKey($email), self::lockSeconds());

            return;
        }

        $current = self::getLoginAttempt($email);
        $newCount = (int) ($current['attempt_count'] ?? 0) + 1;
        $lockedUntil = null;

        if ($newCount >= self::lockMaxAttempts()) {
            $newCount = 0;
            $lockedUntil = date('Y-m-d H:i:s', time() + self::lockSeconds());
        }

        $stmt = self::pdo()->prepare(
            'INSERT INTO auth_login_attempts (identifier_hash, ip_address, attempt_count, locked_until)
             VALUES (:identifier_hash, :ip_address, :attempt_count, :locked_until)
             ON DUPLICATE KEY UPDATE
             attempt_count = VALUES(attempt_count),
             locked_until = VALUES(locked_until),
             last_attempt_at = CURRENT_TIMESTAMP'
        );

        $stmt->execute([
            'identifier_hash' => self::rateLimitIdentifier($email),
            'ip_address' => request()->ip() ?? '0.0.0.0',
            'attempt_count' => $newCount,
            'locked_until' => $lockedUntil,
        ]);
    }

    private static function clearLoginFailures(string $email): void
    {
        if (! self::isRateLimitAvailable()) {
            RateLimiter::clear(self::fallbackRateLimitKey($email));

            return;
        }

        $stmt = self::pdo()->prepare(
            'DELETE FROM auth_login_attempts WHERE identifier_hash = :identifier_hash AND ip_address = :ip_address'
        );

        $stmt->execute([
            'identifier_hash' => self::rateLimitIdentifier($email),
            'ip_address' => request()->ip() ?? '0.0.0.0',
        ]);
    }

    public static function loginByRole(string $role): bool
    {
        $stmt = self::pdo()->prepare(
            'SELECT id, full_name, email, is_active, must_change_password
             FROM users
             WHERE role = :role AND is_active = 1
             LIMIT 1'
        );
        $stmt->execute(['role' => $role]);
        $user = $stmt->fetch();

        if (! $user) {
            return false;
        }

        session([
            self::SESSION_KEY => [
                'id' => (int) $user['id'],
                'full_name' => (string) $user['full_name'],
                'email' => (string) $user['email'],
                'must_change_password' => (int) ($user['must_change_password'] ?? 0),
            ],
        ]);

        session()->regenerate();

        return true;
    }

    public static function attempt(string $email, string $password): bool
    {
        if (self::isLoginLocked($email)) {
            return false;
        }

        $stmt = self::pdo()->prepare(
            'SELECT id, full_name, email, password_hash, is_active, must_change_password
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (! $user || (int) $user['is_active'] !== 1) {
            self::registerLoginFailure($email);

            return false;
        }

        if (! password_verify($password, (string) $user['password_hash'])) {
            self::registerLoginFailure($email);

            return false;
        }

        self::clearLoginFailures($email);

        try {
            $updateStmt = self::pdo()->prepare('UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id');
            $updateStmt->execute(['id' => (int) $user['id']]);
        } catch (Throwable $e) {
            // Keep login flow alive.
        }

        session([
            self::SESSION_KEY => [
                'id' => (int) $user['id'],
                'full_name' => (string) $user['full_name'],
                'email' => (string) $user['email'],
                'must_change_password' => (int) ($user['must_change_password'] ?? 0),
            ],
        ]);

        session()->regenerate();

        return true;
    }

    public static function requiresPasswordChange(): bool
    {
        if (! (bool) self::config('force_password_change', true)) {
            return false;
        }

        $user = self::user();
        if (! $user) {
            return false;
        }

        return (int) ($user['must_change_password'] ?? 0) === 1;
    }

    public static function verifyUserPassword(int $userId, string $password): bool
    {
        $stmt = self::pdo()->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();

        if (! $row) {
            return false;
        }

        return password_verify($password, (string) $row['password_hash']);
    }

    public static function updateUserPassword(int $userId, string $newPassword): void
    {
        $stmt = self::pdo()->prepare(
            'UPDATE users
             SET password_hash = :password_hash,
                 must_change_password = 0,
                 password_changed_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $userId,
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        $user = self::user();
        if ($user) {
            $user['must_change_password'] = 0;
            session([self::SESSION_KEY => $user]);
        }
    }

    /**
     * Vendor/Subcon login - authenticate by company name and auto-password
     */
    public static function vendorAttempt(string $companyName, string $password): bool
    {
        $allowPasswordless = (bool) self::config('vendor_passwordless', true);

        // Get vendor from internal_companies
        $stmt = self::pdo()->prepare(
            'SELECT id, company_name, password_hash
             FROM internal_companies
             WHERE company_name = :company_name AND group_id != 58
             LIMIT 1'
        );
        $stmt->execute(['company_name' => $companyName]);
        $vendor = $stmt->fetch();

        if (! $vendor) {
            return false;
        }

        // If passwordless login is not allowed, verify vendor password if present
        if (! $allowPasswordless) {
            if (! empty($vendor['password_hash'])) {
                if (! password_verify($password, (string) $vendor['password_hash'])) {
                    return false;
                }
            } else {
                // If no password is set and passwordless disabled, reject
                return false;
            }
        }

        // Create vendor session
        session([
            self::SESSION_KEY => [
                'id' => 'vendor_'.(int) $vendor['id'],
                'full_name' => (string) $vendor['company_name'],
                'email' => 'vendor@internal',
                'vendor_id' => (int) $vendor['id'],
                'vendor_name' => (string) $vendor['company_name'],
                'vendor_type' => 'subcon',
                'must_change_password' => 0,
            ],
        ]);

        session()->regenerate();

        return true;
    }

    /**
     * Check if current session is vendor login
     */
    public static function isVendor(): bool
    {
        $user = self::user();

        return $user && isset($user['vendor_type']) && $user['vendor_type'] === 'subcon';
    }

    /**
     * Get vendor ID from current session
     */
    public static function vendorId(): ?int
    {
        $user = self::user();

        return $user && isset($user['vendor_id']) ? (int) $user['vendor_id'] : null;
    }
}
