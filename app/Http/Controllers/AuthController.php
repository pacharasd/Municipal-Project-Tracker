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
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            Session::flash('error', 'กรุณากรอกอีเมลและรหัสผ่าน');
            header('Location: ' . Router::url('/login'));
            exit;
        }

        $user = Database::fetch("SELECT u.*, r.name as role_name, r.display_name as role_label 
                                FROM users u JOIN roles r ON u.role_id = r.id 
                                WHERE u.email = ?", [$email]);

        if ($user && password_verify($password, $user['password'])) {
            Auth::login($user);
            AuditLogService::log('LOGIN', 'Auth', $user['id'], null, ['name' => $user['name'], 'role' => $user['role_name']]);
            Session::flash('success', "ยินดีต้อนรับเข้าสู่ระบบ, {$user['name']}");
            header('Location: ' . Router::url('/dashboard'));
            exit;
        }

        Session::flash('error', 'อีเมลหรือรหัสผ่านไม่ถูกต้อง');
        header('Location: ' . Router::url('/login'));
        exit;
    }

    public function quickSwitch(): void
    {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId > 0 && Auth::switchUser($userId)) {
            $user = Auth::user();
            AuditLogService::log('SWITCH_ROLE', 'Auth', $userId, null, ['switched_to' => $user['role_name']]);
            Session::flash('success', "สลับเข้าใช้งานในบทบาท: {$user['role_label']} ({$user['name']}) เรียบร้อยแล้ว");
        } else {
            Session::flash('error', 'ไม่พบบัญชีผู้ใช้งานที่ต้องการสลับ');
        }

        $redirect = $_POST['redirect'] ?? Router::url('/dashboard');
        header('Location: ' . $redirect);
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
