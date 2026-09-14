<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            $isSecure = SecurityHeaders::isHttps();

            // Enforce hardened session settings
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Lax');

            if ($isSecure) {
                ini_set('session.cookie_secure', '1');
            }

            $lifetimeMinutes = (int)(getenv('SESSION_LIFETIME') ?: 120);

            session_set_cookie_params([
                'lifetime' => $lifetimeMinutes * 60,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            @session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    private static array $flashCache = [];

    public static function flash(string $key, mixed $value = null): mixed
    {
        self::start();
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            unset(self::$flashCache[$key]);
            return null;
        }

        if (array_key_exists($key, self::$flashCache)) {
            return self::$flashCache[$key];
        }

        $msg = $_SESSION['_flash'][$key] ?? null;
        self::$flashCache[$key] = $msg;
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }

    public static function hasFlash(string $key): bool
    {
        self::start();
        return array_key_exists($key, self::$flashCache) && self::$flashCache[$key] !== null 
            || !empty($_SESSION['_flash'][$key]);
    }

    public static function csrfToken(): string
    {
        self::start();
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function csrfField(): string
    {
        $token = self::csrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validateCsrf(?string $token): bool
    {
        self::start();
        $stored = $_SESSION['_csrf_token'] ?? '';
        return !empty($token) && hash_equals($stored, $token);
    }
}
