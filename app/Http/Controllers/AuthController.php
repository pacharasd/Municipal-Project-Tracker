<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\View;
use App\Core\Router;
use App\Services\AuditLogService;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            header('Location: ' . Router::url('/dashboard'));
            exit;
        }

        $demoUsers = Database::query("SELECT u.id, u.name, u.email, r.name as role_name, r.display_name as role_label 
                                     FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.role_id ASC");

        View::render('auth.login', [
            'demoUsers' => $demoUsers,
        ]);
    }

    public function login(): void
    {
        $username = trim($_POST['name'] ?? $_POST['username'] ?? $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            Session::flash('error', 'กรุณากรอกชื่อผู้ใช้งานและรหัสผ่าน');
            header('Location: ' . Router::url('/login'));
            exit;
        }

        $user = Database::fetch("SELECT u.*, r.name as role_name, r.display_name as role_label 
                                FROM users u JOIN roles r ON u.role_id = r.id 
                                WHERE u.name = ? OR u.email = ? 
                                LIMIT 1", [$username, $username]);

        if ($user && password_verify($password, $user['password'])) {
            Auth::login($user);
            AuditLogService::log('LOGIN', 'Auth', $user['id'], null, ['name' => $user['name'], 'role' => $user['role_name']]);
            Session::flash('success', "ยินดีต้อนรับเข้าสู่ระบบ, {$user['name']}");
            header('Location: ' . Router::url('/dashboard'));
            exit;
        }

        Session::flash('error', 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง');
        header('Location: ' . Router::url('/login'));
        exit;
    }


    public function logout(): void
    {
        $userId = Auth::id();
        if ($userId) {
            AuditLogService::log('LOGOUT', 'Auth', $userId);
        }
        Auth::logout();
        Session::flash('success', 'ออกจากระบบเรียบร้อยแล้ว');
        header('Location: ' . Router::url('/login'));
        exit;
    }
}
