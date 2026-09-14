<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Database;

/**
 * Class RateLimiter
 * 
 * Enterprise-grade database-backed Rate Limiter for Authentication & Credential Protection.
 * Complies with OWASP Top 10 (A07) and NIST SP 800-63B guidelines against brute force and credential stuffing.
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

        $row = Database::fetch($sql, [$ip, $username, $decayMinutes]);
        if (!$row || !isset($row['elapsed_sec'])) {
            return 0;
        }

        $elapsed = (int)$row['elapsed_sec'];
        return max(1, $windowSeconds - $elapsed);
    }

    /**
     * Record an authentication attempt (failure, locked, or success).
     */
    public static function hit(string $ip, string $username, string $status = 'failure', ?string $userAgent = null): void
    {
        $ua = substr($userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'), 0, 255);
        $cleanUsername = substr(trim($username), 0, 150);

        // Omit attempted_at to let MySQL assign its own synchronized CURRENT_TIMESTAMP
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
    }

    /**
     * Clear failed attempts for a specific IP and username upon successful authentication.
     */
    public static function clear(string $ip, string $username): void
    {
        Database::query(
            "DELETE FROM login_attempts 
             WHERE (ip_address = ? OR (username = ? AND username != '')) 
               AND status IN ('failure', 'locked')",
            [$ip, $username]
        );
    }

    /**
     * Get the maximum failure count between IP and Username within the decay window.
     */
    public static function getRecentAttemptsCount(string $ip, string $username, int $decayMinutes = self::DEFAULT_DECAY_MINUTES): int
    {
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
        if (!empty($username)) {
            $userRow = Database::fetch(
                "SELECT COUNT(*) as cnt 
                 FROM login_attempts 
                 WHERE username = ? 
                   AND status IN ('failure', 'locked') 
                   AND attempted_at >= NOW() - INTERVAL ? MINUTE",
                [$username, $decayMinutes]
            );
            $userCount = (int)($userRow['cnt'] ?? 0);
        }

        return max($ipCount, $userCount);
    }

    /**
     * Housekeeping: Purge attempt records older than $days days.
     */
    public static function cleanOldAttempts(int $days = 7): void
    {
        Database::query("DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL ? DAY", [$days]);
    }
}
