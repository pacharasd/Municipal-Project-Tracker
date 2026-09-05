<?php

/**
 * Municipal Project Tracker - Front Controller
 * ระบบติดตามและบริหารโครงการเทศบาล
 * Developed for XAMPP / Apache / MySQL Environment
 */

declare(strict_types=1);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Send Anti-Caching Headers (Ensures instant updates during development)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Autoload Classes in App\ namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load .env variables if present
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
        }
    }
}

// Start Session
\App\Core\Session::start();

// If not visiting /login or /logout, default to Administrator for seamless testing
$reqUri = $_SERVER['REQUEST_URI'] ?? '';
$isAuthRoute = strpos($reqUri, '/login') !== false || strpos($reqUri, '/logout') !== false;

if (!$isAuthRoute && !\App\Core\Auth::check()) {
    $admin = \App\Core\Database::fetch("SELECT u.*, r.name as role_name, r.display_name as role_label FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = 1");
    if ($admin) {
        \App\Core\Auth::login($admin);
    }
}

// Load Web Routes
require_once dirname(__DIR__) . '/routes/web.php';

// Dispatch Current Request
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

\App\Core\Router::dispatch($uri, $method);
