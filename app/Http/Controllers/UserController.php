<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Core\Router;
use App\Core\Session;

class UserController
{
    public function index(): void
    {
        if (!Auth::isAdmin()) {
            Session::flash('error', 'เฉพาะผู้ดูแลระบบ (Administrator) เท่านั้นที่สามารถเข้าถึงระบบจัดการผู้ใช้งานได้');
            header('Location: ' . Router::url('/dashboard'));
            exit;
        }

        $users = Database::query(
            "SELECT u.*, r.display_name as role_label, r.name as role_name 
             FROM users u 
             LEFT JOIN roles r ON u.role_id = r.id 
             ORDER BY u.id ASC"
        );

        $roles = Database::query("SELECT * FROM roles ORDER BY id ASC");

        View::render('users.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function store(): void
    {
        if (!Auth::isAdmin()) {
            Session::flash('error', 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถเพิ่มผู้ใช้งานได้');
            header('Location: ' . Router::url('/users'));
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = !empty($_POST['email']) ? trim($_POST['email']) : null;
        $password = $_POST['password'] ?? 'password';
        $position = trim($_POST['position'] ?? '');
        $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;

        if (empty($name)) {
            Session::flash('error', 'กรุณาระบุชื่อ - นามสกุล');
            header('Location: ' . Router::url('/users'));
            exit;
        }

        // Check duplicate name
        $existsName = Database::fetchColumn("SELECT COUNT(*) FROM users WHERE name = ?", [$name]);
        if ($existsName > 0) {
            Session::flash('error', "ชื่อผู้ใช้งาน '{$name}' มีอยู่ในระบบแล้ว กรุณาใช้ชื่ออื่น");
            header('Location: ' . Router::url('/users'));
            exit;
        }

        // Check duplicate email only if provided
        if ($email !== null) {
            $exists = Database::fetchColumn("SELECT COUNT(*) FROM users WHERE email = ?", [$email]);
            if ($exists > 0) {
                Session::flash('error', "อีเมล '{$email}' มีอยู่ในระบบแล้ว กรุณาใช้อีเมลอื่น");
                header('Location: ' . Router::url('/users'));
                exit;
            }
        }

        $roleId = !empty($_POST['role_id']) ? (int)$_POST['role_id'] : 1; // Default to Admin

        $hash = password_hash($password ?: 'password', PASSWORD_BCRYPT);
        $userId = Database::insert('users', [
            'name'     => $name,
            'email'    => $email,
            'password' => $hash,
            'role_id'  => $roleId,
            'position' => $position,
            'phone'    => $phone,
        ]);

        \App\Services\AuditLogService::log('CREATE_USER', 'User', $userId, null, ['name' => $name, 'role_id' => $roleId]);
        Session::flash('success', "เพิ่มผู้ใช้งาน '{$name}' เรียบร้อยแล้ว (รหัสผ่านเริ่มต้น: {$password})");
        header('Location: ' . Router::url('/users'));
        exit;
    }

    public function update(string $id): void
    {
        if (!Auth::isAdmin()) {
            Session::flash('error', 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถแก้ไขข้อมูลผู้ใช้ได้');
            header('Location: ' . Router::url('/users'));
            exit;
        }

        $userId = (int)$id;
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            Session::flash('error', 'ไม่พบผู้ใช้งาน');
            header('Location: ' . Router::url('/users'));
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $newPassword = $_POST['password'] ?? '';

        if (empty($name)) {
            Session::flash('error', 'กรุณาระบุชื่อ - นามสกุล');
            header('Location: ' . Router::url('/users'));
            exit;
        }

        // Check duplicate name for another user
        $existsName = Database::fetchColumn("SELECT COUNT(*) FROM users WHERE name = ? AND id != ?", [$name, $userId]);
        if ($existsName > 0) {
            Session::flash('error', "ชื่อผู้ใช้งาน '{$name}' ถูกใช้งานโดยผู้ใช้อื่นแล้ว");
            header('Location: ' . Router::url('/users'));
            exit;
        }

        $updateData = [
            'name'     => $name,
            'position' => $position,
        ];

        // If email or phone was explicitly passed in POST, update it; otherwise keep existing or null
        if (isset($_POST['email'])) {
            $email = trim($_POST['email']);
            if (!empty($email)) {
                $exists = Database::fetchColumn("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?", [$email, $userId]);
                if ($exists > 0) {
                    Session::flash('error', "อีเมล '{$email}' ถูกใช้งานโดยผู้ใช้อื่นแล้ว");
                    header('Location: ' . Router::url('/users'));
                    exit;
                }
                $updateData['email'] = $email;
            } else {
                $updateData['email'] = null;
            }
        }

        if (isset($_POST['phone'])) {
            $phone = trim($_POST['phone']);
            $updateData['phone'] = !empty($phone) ? $phone : null;
        }

        if (!empty($_POST['role_id'])) {
            $updateData['role_id'] = (int)$_POST['role_id'];
        }

        if (!empty($newPassword)) {
            $updateData['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        Database::update('users', $updateData, "id = ?", [$userId]);

        \App\Services\AuditLogService::log('UPDATE_USER', 'User', $userId, 
            ['name' => $user['name'], 'role_id' => $user['role_id']], 
            ['name' => $name, 'role_id' => $updateData['role_id'] ?? $user['role_id']]
        );
        Session::flash('success', "อัปเดตข้อมูลผู้ใช้ '{$name}' เรียบร้อยแล้ว");
        header('Location: ' . Router::url('/users'));
        exit;
    }

    public function delete(string $id): void
    {
        if (!Auth::isAdmin()) {
            Session::flash('error', 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถลบผู้ใช้งานได้');
            header('Location: ' . Router::url('/users'));
            exit;
        }

        $userId = (int)$id;
        if ($userId === Auth::id()) {
            Session::flash('error', 'ไม่สามารถลบบัญชีของตนเองที่กำลังใช้งานอยู่ได้');
            header('Location: ' . Router::url('/users'));
            exit;
        }

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            Session::flash('error', 'ไม่พบผู้ใช้งาน');
            header('Location: ' . Router::url('/users'));
            exit;
        }

        Database::execute("DELETE FROM users WHERE id = ?", [$userId]);
        \App\Services\AuditLogService::log('DELETE_USER', 'User', $userId, ['name' => $user['name'], 'email' => $user['email']]);

        Session::flash('success', "ลบผู้ใช้ '{$user['name']}' ออกจากระบบเรียบร้อยแล้ว");
        header('Location: ' . Router::url('/users'));
        exit;
    }
}
