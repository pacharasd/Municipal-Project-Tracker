<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            $isSecure = SecurityHeaders::isHttps();
            $lifetimeMinutes = max(1, (int)(getenv('SESSION_LIFETIME') ?: 120));
            $lifetimeSeconds = $lifetimeMinutes * 60;
            $savePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';

            if (!is_dir($savePath) && !mkdir($savePath, 0700, true) && !is_dir($savePath)) {
                throw new \RuntimeException('Unable to create the application session directory');
            }

            // Isolate this application's files and cookie from other PHP sites on localhost.
            session_save_path($savePath);
            session_name('MPTSESSID');

            // Enforce hardened session settings
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', (string)$lifetimeSeconds);

            if ($isSecure) {
                ini_set('session.cookie_secure', '1');
            }

            session_set_cookie_params([
                'lifetime' => $lifetimeSeconds,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            if (!session_start()) {
                throw new \RuntimeException('Unable to start the application session');
            }

            $now = time();
            if (isset($_SESSION['_last_activity']) && $now - (int)$_SESSION['_last_activity'] > $lifetimeSeconds) {
                $_SESSION = [];
                session_regenerate_id(true);
            }
            $_SESSION['_last_activity'] = $now;

            // PHP does not extend a persistent session cookie on every request.
            // Refresh it when the user is active so it matches the server-side idle window.
            setcookie(session_name(), session_id(), [
                'expires'  => $now + $lifetimeSeconds,
                'path'     => '/',
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
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

    /**
     * Regenerate session ID to mitigate session fixation attacks (OWASP ASVS 3.2.1).
     */
    public static function regenerate(bool $deleteOldSession = true): bool
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            return session_regenerate_id($deleteOldSession);
        }
        return false;
    }

    /**
     * Destroy current session cleanly and clear session cookies.
     */
    public static function destroy(): void
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            @session_destroy();
        }
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
