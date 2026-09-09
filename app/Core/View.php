<?php

namespace App\Core;

use App\Core\Auth;
use App\Core\Session;
use App\Core\Router;
use Exception;

class View
{
    public static function render(string $viewPath, array $data = []): void
    {
        $file = dirname(__DIR__, 2) . '/resources/views/' . str_replace('.', '/', $viewPath) . '.php';
        if (!file_exists($file)) {
            // Also check .blade.php
            $bladeFile = dirname(__DIR__, 2) . '/resources/views/' . str_replace('.', '/', $viewPath) . '.blade.php';
            if (file_exists($bladeFile)) {
                $file = $bladeFile;
            } else {
                throw new Exception("ไม่พบไฟล์ View: {$viewPath} ({$file})");
            }
        }

        // Global helpers and variables available in all views
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }
        $currentUser = Auth::user();
        $csrfToken = Session::csrfToken();
        $flashSuccess = Session::flash('success');
        $flashError = Session::flash('error');
        $flashInfo = Session::flash('info');

        extract($data);

        include $file;
    }

    public static function component(string $componentName, array $data = []): void
    {
        $normalized = str_starts_with($componentName, 'components.') 
            ? substr($componentName, 11) 
            : $componentName;

        $file = dirname(__DIR__, 2) . '/resources/views/components/' . str_replace('.', '/', $normalized) . '.php';
        if (!file_exists($file)) {
            $bladeFile = dirname(__DIR__, 2) . '/resources/views/components/' . str_replace('.', '/', $normalized) . '.blade.php';
            if (file_exists($bladeFile)) {
                $file = $bladeFile;
            } else {
                throw new Exception("ไม่พบไฟล์ View Component: {$componentName}");
            }
        }

        extract($data);
        include $file;
    }

    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

if (!function_exists('component')) {
    function component(string $name, array $data = []): void {
        \App\Core\View::component($name, $data);
    }
}
