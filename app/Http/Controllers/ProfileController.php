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
        if (!str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = Router::url('/dashboard');
        }

        $name = trim(strip_tags($_POST['name'] ?? ''));
        $fullName = isset($_POST['full_name']) ? (trim(strip_tags($_POST['full_name'])) !== '' ? trim(strip_tags($_POST['full_name'])) : null) : ($currentUser['full_name'] ?? null);
        $position = trim(strip_tags($_POST['position'] ?? ''));
        $phone = isset($_POST['phone']) ? trim(strip_tags($_POST['phone'])) : ($currentUser['phone'] ?? '');

        if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            Session::flash('error', 'ชื่อผู้ใช้งาน (Username) ต้องมีความยาวระหว่าง 2 ถึง 150 ตัวอักษร');
            header('Location: ' . $redirect);
            exit;
        }

        if ($fullName !== null && mb_strlen($fullName) > 150) {
            Session::flash('error', 'ชื่อ-นามสกุลต้องมีความยาวไม่เกิน 150 ตัวอักษร');
            header('Location: ' . $redirect);
            exit;
        }

        // Check duplicate username for another user
        $existsName = Database::fetchColumn("SELECT COUNT(*) FROM users WHERE name = ? AND id != ?", [$name, $userId]);
        if ($existsName > 0) {
            Session::flash('error', "ชื่อผู้ใช้งาน (Username) '{$name}' ถูกใช้งานโดยผู้ใช้อื่นแล้ว");
            header('Location: ' . $redirect);
            exit;
        }

        if (isset($_POST['phone']) && !empty($phone)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($cleanPhone) < 9 || strlen($cleanPhone) > 10) {
                Session::flash('error', 'เบอร์โทรศัพท์ต้องประกอบด้วยตัวเลข 9-10 หลัก');
                header('Location: ' . $redirect);
                exit;
            }
        }

        if (mb_strlen($position) > 100) {
            Session::flash('error', 'ตำแหน่งงานต้องมีความยาวไม่เกิน 100 ตัวอักษร');
            header('Location: ' . $redirect);
            exit;
        }

        // ค่าเดิมสำหรับ Audit Log
        $oldValues = [
            'name'      => $currentUser['name'] ?? '',
            'full_name' => $currentUser['full_name'] ?? '',
            'position'  => $currentUser['position'] ?? '',
        ];

        $newValues = [
            'name'      => $name,
            'full_name' => $fullName ?? '',
            'position'  => $position,
        ];

        $updateData = [
            'name'      => $name,
            'full_name' => $fullName,
            'position'  => $position,
        ];

        if (isset($_POST['phone'])) {
            $oldValues['phone'] = $currentUser['phone'] ?? '';
            $newValues['phone'] = $phone;
            $updateData['phone'] = $phone;
        }

        Database::update('users', $updateData, 'id = ?', [$userId]);

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
        if (!str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = Router::url('/dashboard');
        }

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
