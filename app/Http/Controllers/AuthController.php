<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\RateLimiter;
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

        View::render('auth.login');
    }

    public function login(): void
    {
        $username = trim((string)($_POST['name'] ?? $_POST['username'] ?? $_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $ip = substr($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', 0, 45);
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

        // 1. Rate Limiting Check (NIST SP 800-63B & OWASP ASVS 2.2 Brute Force Mitigation)
        if (RateLimiter::tooManyAttempts($ip, $username)) {
            $seconds = RateLimiter::availableIn($ip, $username);
            $minutes = (int)ceil($seconds / 60);

            // Record security audit event
            AuditLogService::log('ACCOUNT_LOCKED', 'Auth', null, null, [
                'attempted_username' => $username,
                'ip'                 => $ip,
                'wait_seconds'       => $seconds,
            ]);

            RateLimiter::hit($ip, $username, 'locked', $userAgent);

            http_response_code(429);
            Session::flash('lockout_seconds', $seconds);
            Session::flash('error', "คุณพยายามเข้าสู่ระบบผิดพลาดเกินกำหนด เพื่อความปลอดภัยของระบบโปรดรออีก {$minutes} นาที ก่อนลองใหม่อีกครั้ง");
            header('Location: ' . Router::url('/login'));
            exit;
        }

        // 2. Validate Required Inputs
        if ($username === '' || $password === '') {
            Session::flash('error', 'กรุณากรอกชื่อผู้ใช้งานและรหัสผ่าน');
            header('Location: ' . Router::url('/login'));
            exit;
        }

        // 3. User Lookup via Prepared Statement (SQL Injection Safe)
        $user = Database::fetch(
            "SELECT u.*, r.name as role_name, r.display_name as role_label 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.name = ? OR u.email = ? 
             LIMIT 1",
            [$username, $username]
        );

        // 4. Verify Password with Timing Attack Mitigation (OWASP ASVS 2.1.8)
        $isValid = false;
        if ($user) {
            $isValid = password_verify($password, $user['password']);

            // Automatic Password Re-hash Upgrade (OWASP Cryptographic Standard)
            if ($isValid && password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                try {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    Database::query("UPDATE users SET password = ? WHERE id = ?", [$newHash, $user['id']]);
                } catch (\Throwable $e) {
                    // Non-blocking rehash failure
                }
            }
        } else {
            // Equalize CPU execution time for non-existent users
            Auth::dummyPasswordVerify($password);
        }

        // 5. Successful Authentication
        if ($isValid && $user) {
            // Clear rate limiting counter upon successful authentication
            RateLimiter::clear($ip, $username);

            // Establish secure session with Session Fixation Defense (session_regenerate_id)
            Auth::login($user);

            // Comprehensive Security Audit Trail
            AuditLogService::log('LOGIN_SUCCESS', 'Auth', (int)$user['id'], null, [
                'username' => $user['name'],
                'role'     => $user['role_name'],
                'ip'       => $ip,
            ], (int)$user['id']);

            Session::flash('success', "ยินดีต้อนรับเข้าสู่ระบบ, {$user['name']}");
            header('Location: ' . Router::url('/dashboard'));
            exit;
        }

        // 6. Failed Authentication Handling
        RateLimiter::hit($ip, $username, 'failure', $userAgent);
        $remaining = RateLimiter::remainingAttempts($ip, $username);

        // Record security audit event (NEVER log plaintext password!)
        AuditLogService::log('LOGIN_FAILED', 'Auth', $user ? (int)$user['id'] : null, null, [
            'attempted_username' => $username,
            'ip'                 => $ip,
            'remaining_attempts' => $remaining,
            'reason'             => 'INVALID_CREDENTIALS',
        ], $user ? (int)$user['id'] : null);

        if ($remaining > 0) {
            Session::flash('error', "ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง (เหลือโอกาสอีก {$remaining} ครั้งก่อนระงับชั่วคราว)");
        } else {
            $seconds = RateLimiter::availableIn($ip, $username);
            $minutes = (int)ceil($seconds / 60);
            Session::flash('lockout_seconds', $seconds);
            Session::flash('error', "คุณพยายามเข้าสู่ระบบผิดพลาดเกินกำหนด เพื่อความปลอดภัยของระบบโปรดรออีก {$minutes} นาที ก่อนลองใหม่อีกครั้ง");
        }

        header('Location: ' . Router::url('/login'));
        exit;
    }

    public function logout(): void
    {
        $userId = Auth::id();
        if ($userId) {
            AuditLogService::log('LOGOUT', 'Auth', $userId, null, [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ], $userId);
        }
        Auth::logout();
        Session::flash('success', 'ออกจากระบบเรียบร้อยแล้ว');
        header('Location: ' . Router::url('/login'));
        exit;
    }
}
