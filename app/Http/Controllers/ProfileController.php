<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\Router;
use App\Services\AuditLogService;

class ProfileController
{
    /**
     * อัปเดตข้อมูลส่วนตัว (ชื่อ เบอร์โทร ตำแหน่ง)
     */
    public function updateProfile(): void
    {
        if (!Auth::check()) {
            header('Location: ' . Router::url('/login'));
            exit;
        }

        $currentUser = Auth::user();
        $userId = (int)($currentUser['id'] ?? 0);
        $redirect = $_POST['redirect'] ?? Router::url('/dashboard');

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $position = trim($_POST['position'] ?? '');

        if (empty($name)) {
            Session::flash('error', 'กรุณาระบุชื่อ-นามสกุล');
            header('Location: ' . $redirect);
            exit;
        }

        // ค่าเดิมสำหรับ Audit Log
        $oldValues = [
            'name'     => $currentUser['name'] ?? '',
            'phone'    => $currentUser['phone'] ?? '',
            'position' => $currentUser['position'] ?? '',
        ];

        $newValues = [
            'name'     => $name,
            'phone'    => $phone,
            'position' => $position,
        ];

        Database::update('users', [
            'name'     => $name,
            'phone'    => $phone,
            'position' => $position,
        ], 'id = ?', [$userId]);

        // ปรับปรุงชื่อใน Session
        Session::set('user_name', $name);

        AuditLogService::log('UPDATE_PROFILE', 'Profile', $userId, $oldValues, $newValues);
        Session::flash('success', 'บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว');

        header('Location: ' . $redirect);
        exit;
    }

    /**
     * เปลี่ยนรหัสผ่านของผู้ใช้งาน
     */
    public function updatePassword(): void
    {
        if (!Auth::check()) {
            header('Location: ' . Router::url('/login'));
            exit;
        }

        $userId = (int)Auth::id();
        $redirect = $_POST['redirect'] ?? Router::url('/dashboard');

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['new_password_confirmation'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            Session::flash('error', 'กรุณากรอกข้อมูลรหัสผ่านให้ครบทุกช่อง');
            header('Location: ' . $redirect);
            exit;
        }

        $dbUser = Database::fetch("SELECT password FROM users WHERE id = ?", [$userId]);
        if (!$dbUser || !password_verify($currentPassword, $dbUser['password'])) {
            Session::flash('error', 'รหัสผ่านปัจจุบันไม่ถูกต้อง');
            header('Location: ' . $redirect);
            exit;
        }

        if (strlen($newPassword) < 8) {
            Session::flash('error', 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 8 ตัวอักษร');
            header('Location: ' . $redirect);
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน');
            header('Location: ' . $redirect);
            exit;
        }

        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        Database::update('users', ['password' => $hashed], 'id = ?', [$userId]);

        AuditLogService::log('CHANGE_PASSWORD', 'Profile', $userId, null, ['status' => 'password_updated']);
        Session::flash('success', 'เปลี่ยนรหัสผ่านสำเร็จเรียบร้อยแล้ว');

        header('Location: ' . $redirect);
        exit;
    }
}
