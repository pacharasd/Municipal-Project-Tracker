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
                        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                            http_response_code(401);
                            header('Content-Type: application/json; charset=utf-8');
                            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated', 'redirect' => self::url('/login')]);
                            exit;
                        }
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
                    if (!Session::validateCsrf($token)) {
                        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

                        http_response_code(419);
                        if ($isAjax) {
                            header('Content-Type: application/json; charset=utf-8');
                            echo json_encode([
                                'status'   => 'error',
                                'message'  => 'CSRF Token ไม่ถูกต้องหรือหมดอายุ กรุณารีเฟรชหน้าจอแล้วลองใหม่อีกครั้ง'
                            ], JSON_UNESCAPED_UNICODE);
                            exit;
                        }
                        die("<!DOCTYPE html><html lang='th'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>419 Page Expired</title><link href='https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap' rel='stylesheet'><style>body{font-family:'Sarabun',sans-serif;background:#0f172a;color:#f8fafc;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;padding:1rem;box-sizing:border-box}.card{background:#1e293b;padding:2.5rem;border-radius:1.25rem;border:1px solid #334155;text-align:center;max-width:420px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.5)}h2{margin-top:0;color:#f43f5e;font-size:1.5rem}p{color:#94a3b8;font-size:0.95rem;line-height:1.6}a{display:inline-block;margin-top:1.5rem;padding:0.6rem 1.5rem;background:#10b981;color:#ffffff;border-radius:0.75rem;text-decoration:none;font-weight:600;transition:opacity 0.2s}a:hover{opacity:0.9}</style></head><body><div class='card'><h2>419 Page Expired</h2><p>CSRF Token ไม่ถูกต้องหรือหมดอายุ เพื่อความปลอดภัยของระบบ โปรดกลับไปรีเฟรชหน้าจอแล้วดำเนินการใหม่อีกครั้ง</p><a href='javascript:history.back()'>&larr; ย้อนกลับไปทำรายการ</a></div></body></html>");
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
