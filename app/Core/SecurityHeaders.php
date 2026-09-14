<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Class SecurityHeaders
 * Manages enterprise-grade HTTP security headers, Content Security Policy (CSP),
 * and HTTPS/HSTS enforcement for Municipal Project Tracker.
 */
class SecurityHeaders
{
    /**
     * Determine if current request is served over HTTPS or production SSL environment.
     */
    public static function isHttps(): bool
    {
        // 1. Explicit env flag
        $envSecure = getenv('SESSION_SECURE_COOKIE');
        if ($envSecure !== false && in_array(strtolower((string)$envSecure), ['true', '1', 'yes'], true)) {
            return true;
        }

        // 2. Direct HTTPS check
        if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        // 3. Port check
        if (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) {
            return true;
        }

        // 4. Reverse Proxy / Cloudflare / Load Balancer headers
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return true;
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
            return true;
        }

        if (!empty($_SERVER['HTTP_CF_VISITOR']) && str_contains((string)$_SERVER['HTTP_CF_VISITOR'], '"https"')) {
            return true;
        }

        // 5. Production app URL or environment
        $appEnv = getenv('APP_ENV');
        if ($appEnv !== false && strtolower((string)$appEnv) === 'production') {
            return true;
        }

        $appUrl = getenv('APP_URL');
        if ($appUrl !== false && str_starts_with(strtolower((string)$appUrl), 'https://')) {
            return true;
        }

        return false;
    }

    private static ?string $nonce = null;

    /**
     * Get or generate a cryptographically secure, per-request CSP nonce.
     */
    public static function nonce(): string
    {
        if (self::$nonce === null) {
            self::$nonce = base64_encode(random_bytes(16));
        }
        return self::$nonce;
    }

    /**
     * Build standard Content Security Policy directives compatible with Tailwind CDN,
     * Alpine.js (eval/Function runtime), Lucide Icons, Chart.js, and Google Fonts.
     * Complies with Mozilla Observatory strict CSP rules (removes 'unsafe-inline' from script-src, sets object-src 'none').
     */
    public static function getCspDirectives(): string
    {
        $nonce = self::nonce();

        $directives = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://unpkg.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: blob: https:",
            "connect-src 'self' https://cdn.tailwindcss.com",
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'",
        ];

        return implode('; ', $directives);
    }

    /**
     * Apply all security headers to the current HTTP response.
     */
    public static function apply(): void
    {
        if (headers_sent()) {
            return;
        }

        // 1. Content Security Policy (CSP)
        $cspEnabled = getenv('CSP_ENABLED');
        if ($cspEnabled === false || !in_array(strtolower((string)$cspEnabled), ['false', '0', 'no'], true)) {
            $reportOnly = getenv('CSP_REPORT_ONLY');
            $headerName = ($reportOnly !== false && in_array(strtolower((string)$reportOnly), ['true', '1', 'yes'], true))
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy';

            header("{$headerName}: " . self::getCspDirectives());
        }

        // 2. MIME type sniffing protection
        header('X-Content-Type-Options: nosniff');

        // 3. Clickjacking protection
        header('X-Frame-Options: SAMEORIGIN');

        // 4. Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // 5. Permissions Policy (Feature Policy)
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');

        // 6. Cross-Site Scripting filter (Legacy protection)
        header('X-XSS-Protection: 1; mode=block');

        // 7. HTTP Strict Transport Security (HSTS) when on HTTPS / Production
        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }
}
