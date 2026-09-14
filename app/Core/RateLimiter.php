<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Database;
use App\Core\Session;
use PDOException;
use Throwable;

/**
 * Class RateLimiter
 * 
 * Enterprise-grade database-backed Rate Limiter for Authentication & Credential Protection.
 * Complies with OWASP Top 10 (A07) and NIST SP 800-63B guidelines against brute force and credential stuffing.
 * Features automatic self-healing table provisioning and graceful session-based fallback.
 */
class RateLimiter
{
    /**
     * Default maximum failed attempts allowed within the decay window.
     */
    public const DEFAULT_MAX_ATTEMPTS = 5;

    /**
     * Default decay window in minutes (15 minutes).
     */
    public const DEFAULT_DECAY_MINUTES = 15;

    /**
     * Flag indicating if table existence has already been verified in current process.
     */
    private static bool $tableEnsured = false;

    /**
     * Ensure the `login_attempts` table exists in the database.
     * Self-healing auto-migration for production resilience.
     */
    public static function ensureTable(): bool
    {
        if (self::$tableEnsured) {
            return true;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `login_attempts` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `ip_address` VARCHAR(45) NOT NULL COMMENT 'IPv4 or IPv6 client address',
          `username` VARCHAR(150) NOT NULL COMMENT 'Attempted username or email',
          `status` ENUM('success', 'failure', 'locked') NOT NULL DEFAULT 'failure',
          `user_agent` VARCHAR(255) NULL COMMENT 'Client User-Agent signature',
          `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of attempt',
          PRIMARY KEY (`id`),
          INDEX `idx_ip_attempted` (`ip_address`, `attempted_at`),
          INDEX `idx_user_attempted` (`username`, `attempted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            Database::execute($sql);
            self::$tableEnsured = true;
            return true;
        } catch (Throwable $e) {
            error_log('[RateLimiter] Failed to auto-create login_attempts table: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if either the IP address or username has exceeded the allowed attempt limit.
     */
    public static function tooManyAttempts(string $ip, string $username, int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS, int $decayMinutes = self::DEFAULT_DECAY_MINUTES): bool
    {
        $attempts = self::getRecentAttemptsCount($ip, $username, $decayMinutes);
        return $attempts >= $maxAttempts;
    }

    /**
     * Get remaining attempts before lockout.
     */
    public static function remainingAttempts(string $ip, string $username, int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS, int $decayMinutes = self::DEFAULT_DECAY_MINUTES): int
    {
        $attempts = self::getRecentAttemptsCount($ip, $username, $decayMinutes);
        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Calculate how many seconds remain until the lockout window expires.
     */
    public static function availableIn(string $ip, string $username, int $decayMinutes = self::DEFAULT_DECAY_MINUTES): int
    {
        $windowSeconds = $decayMinutes * 60;
        
        $sql = "SELECT TIMESTAMPDIFF(SECOND, attempted_at, NOW()) as elapsed_sec 
                FROM login_attempts 
                WHERE (ip_address = ? OR (username = ? AND username != ''))
                  AND status IN ('failure', 'locked')
                  AND attempted_at >= NOW() - INTERVAL ? MINUTE 
                ORDER BY attempted_at DESC 
                LIMIT 1";

        try {
            $row = Database::fetch($sql, [$ip, $username, $decayMinutes]);
            if (!$row || !isset($row['elapsed_sec'])) {
                return 0;
            }

            $elapsed = (int)$row['elapsed_sec'];
            return max(1, $windowSeconds - $elapsed);
        } catch (PDOException $e) {
            if (self::isMissingTableError($e) && self::ensureTable()) {
                try {
                    $row = Database::fetch($sql, [$ip, $username, $decayMinutes]);
                    if (!$row || !isset($row['elapsed_sec'])) {
                        return 0;
                    }
                    return max(1, $windowSeconds - (int)$row['elapsed_sec']);
                } catch (Throwable) {
                    // Fall through to session fallback
                }
            }
            return self::sessionAvailableIn($ip, $decayMinutes);
        } catch (Throwable $e) {
            error_log('[RateLimiter::availableIn] ' . $e->getMessage());
            return self::sessionAvailableIn($ip, $decayMinutes);
        }
    }

    /**
     * Record an authentication attempt (failure, locked, or success).
     */
    public static function hit(string $ip, string $username, string $status = 'failure', ?string $userAgent = null): void
    {
        $ua = substr($userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'), 0, 255);
        $cleanUsername = substr(trim($username), 0, 150);

        try {
            Database::insert('login_attempts', [
                'ip_address' => $ip,
                'username'   => $cleanUsername,
                'status'     => $status,
                'user_agent' => $ua,
            ]);

            // Probabilistic garbage collection (1% chance per hit)
            if (mt_rand(1, 100) === 1) {
                self::cleanOldAttempts(7);
            }
        } catch (PDOException $e) {
            if (self::isMissingTableError($e) && self::ensureTable()) {
                try {
                    Database::insert('login_attempts', [
                        'ip_address' => $ip,
                        'username'   => $cleanUsername,
                        'status'     => $status,
                        'user_agent' => $ua,
                    ]);
                    return;
                } catch (Throwable) {
                    // Fall through to session fallback
                }
            }
            // Graceful session-based fallback
            error_log('[RateLimiter::hit] Database unavailable, using session fallback: ' . $e->getMessage());
            self::sessionHit($ip);
            if (!empty($cleanUsername)) {
                self::sessionHit('user_' . md5($cleanUsername));
            }
        } catch (Throwable $e) {
            error_log('[RateLimiter::hit] ' . $e->getMessage());
            self::sessionHit($ip);
        }
    }

    /**
     * Clear failed attempts for a specific IP and username upon successful authentication.
     */
    public static function clear(string $ip, string $username): void
    {
        $cleanUsername = substr(trim($username), 0, 150);

        try {
            Database::query(
                "DELETE FROM login_attempts 
                 WHERE (ip_address = ? OR (username = ? AND username != '')) 
                   AND status IN ('failure', 'locked')",
                [$ip, $cleanUsername]
            );
        } catch (PDOException $e) {
            if (self::isMissingTableError($e) && self::ensureTable()) {
                try {
                    Database::query(
                        "DELETE FROM login_attempts 
                         WHERE (ip_address = ? OR (username = ? AND username != '')) 
                           AND status IN ('failure', 'locked')",
                        [$ip, $cleanUsername]
                    );
                } catch (Throwable) {
                    // Fall through
                }
            }
        } catch (Throwable $e) {
            error_log('[RateLimiter::clear] ' . $e->getMessage());
        }

        // Always clear session fallback cache as well
        self::sessionClear($ip);
        if (!empty($cleanUsername)) {
            self::sessionClear('user_' . md5($cleanUsername));
        }
    }

    /**
     * Get the maximum failure count between IP and Username within the decay window.
     */
    public static function getRecentAttemptsCount(string $ip, string $username, int $decayMinutes = self::DEFAULT_DECAY_MINUTES): int
    {
        $cleanUsername = substr(trim($username), 0, 150);

        try {
            $ipRow = Database::fetch(
                "SELECT COUNT(*) as cnt 
                 FROM login_attempts 
                 WHERE ip_address = ? 
                   AND status IN ('failure', 'locked') 
                   AND attempted_at >= NOW() - INTERVAL ? MINUTE",
                [$ip, $decayMinutes]
            );

            $ipCount = (int)($ipRow['cnt'] ?? 0);

            $userCount = 0;
            if (!empty($cleanUsername)) {
                $userRow = Database::fetch(
                    "SELECT COUNT(*) as cnt 
                     FROM login_attempts 
                     WHERE username = ? 
                       AND status IN ('failure', 'locked') 
                       AND attempted_at >= NOW() - INTERVAL ? MINUTE",
                    [$cleanUsername, $decayMinutes]
                );
                $userCount = (int)($userRow['cnt'] ?? 0);
            }

            return max($ipCount, $userCount);
        } catch (PDOException $e) {
            if (self::isMissingTableError($e) && self::ensureTable()) {
                try {
                    $ipRow = Database::fetch(
                        "SELECT COUNT(*) as cnt 
                         FROM login_attempts 
                         WHERE ip_address = ? 
                           AND status IN ('failure', 'locked') 
                           AND attempted_at >= NOW() - INTERVAL ? MINUTE",
                        [$ip, $decayMinutes]
                    );
                    $ipCount = (int)($ipRow['cnt'] ?? 0);

                    $userCount = 0;
                    if (!empty($cleanUsername)) {
                        $userRow = Database::fetch(
                            "SELECT COUNT(*) as cnt 
                             FROM login_attempts 
                             WHERE username = ? 
                               AND status IN ('failure', 'locked') 
                               AND attempted_at >= NOW() - INTERVAL ? MINUTE",
                            [$cleanUsername, $decayMinutes]
                        );
                        $userCount = (int)($userRow['cnt'] ?? 0);
                    }
                    return max($ipCount, $userCount);
                } catch (Throwable) {
                    // Fall through to session fallback
                }
            }

            // Graceful session-based fallback
            error_log('[RateLimiter::getRecentAttemptsCount] Database unavailable, using session fallback: ' . $e->getMessage());
            $ipCount = self::sessionGetAttempts($ip, $decayMinutes);
            $userCount = !empty($cleanUsername) ? self::sessionGetAttempts('user_' . md5($cleanUsername), $decayMinutes) : 0;
            return max($ipCount, $userCount);
        } catch (Throwable $e) {
            error_log('[RateLimiter::getRecentAttemptsCount] ' . $e->getMessage());
            return self::sessionGetAttempts($ip, $decayMinutes);
        }
    }

    /**
     * Housekeeping: Purge attempt records older than $days days.
     */
    public static function cleanOldAttempts(int $days = 7): void
    {
        try {
            Database::query("DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL ? DAY", [$days]);
        } catch (Throwable $e) {
            // Non-blocking cleanup error
            error_log('[RateLimiter::cleanOldAttempts] ' . $e->getMessage());
        }
    }

    /**
     * Determine if a PDOException was caused by a missing table (Error 1146 / 42S02).
     */
    private static function isMissingTableError(PDOException $e): bool
    {
        $message = $e->getMessage();
        $code = (string)$e->getCode();
        return str_contains($message, '1146') || 
               str_contains($message, '42S02') || 
               str_contains($message, "doesn't exist") || 
               $code === '42S02';
    }

    // ==========================================
    // Session Fallback Handlers (Graceful Degradation)
    // ==========================================

    private static function sessionGetAttempts(string $key, int $decayMinutes): int
    {
        Session::start();
        $attempts = $_SESSION['_rate_limiter'][$key] ?? [];
        if (!is_array($attempts)) {
            return 0;
        }

        $cutoff = time() - ($decayMinutes * 60);
        $valid = array_filter($attempts, fn($t) => is_numeric($t) && (int)$t >= $cutoff);
        $_SESSION['_rate_limiter'][$key] = array_values($valid);
        return count($valid);
    }

    private static function sessionHit(string $key): void
    {
        Session::start();
        if (!isset($_SESSION['_rate_limiter'][$key]) || !is_array($_SESSION['_rate_limiter'][$key])) {
            $_SESSION['_rate_limiter'][$key] = [];
        }
        $_SESSION['_rate_limiter'][$key][] = time();
    }

    private static function sessionClear(string $key): void
    {
        Session::start();
        unset($_SESSION['_rate_limiter'][$key]);
    }

    private static function sessionAvailableIn(string $key, int $decayMinutes): int
    {
        Session::start();
        $attempts = $_SESSION['_rate_limiter'][$key] ?? [];
        if (empty($attempts) || !is_array($attempts)) {
            return 0;
        }

        $latest = max($attempts);
        $windowSeconds = $decayMinutes * 60;
        $elapsed = time() - $latest;
        return max(1, $windowSeconds - $elapsed);
    }
}

