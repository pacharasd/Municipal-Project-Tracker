<?php

namespace App\Core;

use App\Core\Auth;
use App\Core\Session;
use Exception;

class Router
{
    private static array $routes = [];

    public static function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        self::add('GET', $path, $handler, $middlewares);
    }

    public static function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        self::add('POST', $path, $handler, $middlewares);
    }

    private static function add(string $method, string $path, array|callable $handler, array $middlewares): void
    {
        self::$routes[] = [
            'method'      => $method,
            'path'        => $path,
            'handler'     => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public static function getBaseUrl(): string
    {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        // If accessed directly via public or via root rewrite
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $projectRoot = preg_replace('#/public$#', '', $scriptDir);

        if (strpos($requestUri, $scriptDir) === 0) {
            return rtrim($scriptDir, '/');
        }
        if (strpos($requestUri, $projectRoot) === 0) {
            return rtrim($projectRoot, '/');
        }
        return '';
    }

    public static function url(string $path = ''): string
    {
        $base = self::getBaseUrl();
        return $base . '/' . ltrim($path, '/');
    }

    public static function dispatch(string $uri, string $method): void
    {
        $uri = strtok($uri, '?');
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $projectRoot = preg_replace('#/public$#', '', $scriptDir);

        if (!empty($scriptDir) && $scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        } elseif (!empty($projectRoot) && $projectRoot !== '/' && strpos($uri, $projectRoot) === 0) {
            $uri = substr($uri, strlen($projectRoot));
        }

        $uri = '/' . ltrim($uri, '/');


        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Convert route pattern to regex e.g. /projects/{id}
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[0-9a-zA-Z_-]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                // Filter named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run Middlewares
                foreach ($route['middlewares'] as $mw) {
                    if ($mw === 'auth' && !Auth::check()) {
                        header('Location: ' . self::url('/login'));
                        exit;
                    }
                    if ($mw === 'admin' && !Auth::isAdmin()) {
                        http_response_code(403);
                        die("<h1>403 Forbidden - คุณไม่มีสิทธิ์เข้าถึงหน้านี้ (เฉพาะ Administrator)</h1>");
                    }
                }

                // CSRF Validation for POST requests
                if ($method === 'POST') {
                    $token = $_POST['_token'] ?? $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
                    if (!Session::validateCsrf($token) && empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                        // Soft allow for ajax if header present, else reject
                        if (!Session::validateCsrf($token)) {
                            http_response_code(419);
                            die("<h1>419 Page Expired - CSRF Token ไม่ถูกต้องหรือหมดอายุ</h1>");
                        }
                    }
                }

                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func_array($handler, $params);
                    return;
                }

                if (is_array($handler)) {
                    [$class, $methodName] = $handler;
                    $controller = new $class();
                    call_user_func_array([$controller, $methodName], $params);
                    return;
                }
            }
        }

        // Route not found
        http_response_code(404);
        echo "<h1>404 Not Found - ไม่พบหน้าที่เรียก ({$uri})</h1>";
    }
}
