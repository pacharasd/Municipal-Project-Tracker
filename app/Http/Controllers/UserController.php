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
        $users = Database::query(
            "SELECT u.*, r.display_name as role_label, r.name as role_name, d.name as department_name 
             FROM users u 
             LEFT JOIN roles r ON u.role_id = r.id 
             LEFT JOIN departments d ON u.department_id = d.id 
             ORDER BY u.id ASC"
        );

        $roles = Database::query("SELECT * FROM roles ORDER BY id ASC");
        $departments = Database::query("SELECT * FROM departments ORDER BY id ASC");

        View::render('users.index', [
            'users'       => $users,
            'roles'       => $roles,
            'departments' => $departments,
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
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? 'password';
        $departmentId = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
        $position = trim($_POST['position'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name) || empty($email)) {
            Session::flash('error', 'กรุณาระบุชื่อ-นามสกุล และอีเมล');
            header('Location: ' . Router::url('/users'));
            exit;
        }

        // Check duplicate email
        $exists = Database::fetchColumn("SELECT COUNT(*) FROM users WHERE email = ?", [$email]);
        if ($exists > 0) {
            Session::flash('error', "อีเมล '{$email}' มีอยู่ในระบบแล้ว กรุณาใช้อีเมลอื่น");
            header('Location: ' . Router::url('/users'));
            exit;
        }

        $roleId = !empty($_POST['role_id']) ? (int)$_POST['role_id'] : 3; // Default to Officer

        $hash = password_hash($password ?: 'password', PASSWORD_BCRYPT);
        $userId = Database::insert('users', [
            'name'          => $name,
            'email'         => $email,
            'password'      => $hash,
            'role_id'       => $roleId,
            'department_id' => $departmentId,
            'position'      => $position,
            'phone'         => $phone,
        ]);

        \App\Services\AuditLogService::log('CREATE_USER', 'User', $userId, null, ['name' => $name, 'email' => $email, 'role_id' => $roleId]);
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
        $email = trim($_POST['email'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $departmentId = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
        $phone = trim($_POST['phone'] ?? '');
        $newPassword = $_POST['password'] ?? '';

        if (empty($name) || empty($email)) {
            Session::flash('error', 'กรุณาระบุชื่อ-นามสกุล และอีเมล');
            header('Location: ' . Router::url('/users'));
            exit;
        }

        // Check duplicate email for another user
        $exists = Database::fetchColumn("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?", [$email, $userId]);
        if ($exists > 0) {
            Session::flash('error', "อีเมล '{$email}' ถูกใช้งานโดยผู้ใช้อื่นแล้ว");
            header('Location: ' . Router::url('/users'));
            exit;
        }

        $updateData = [
            'name'          => $name,
            'email'         => $email,
            'position'      => $position,
            'department_id' => $departmentId,
            'phone'         => $phone,
        ];

        if (!empty($_POST['role_id'])) {
            $updateData['role_id'] = (int)$_POST['role_id'];
        }

        if (!empty($newPassword)) {
            $updateData['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        Database::update('users', $updateData, "id = ?", [$userId]);

        \App\Services\AuditLogService::log('UPDATE_USER', 'User', $userId, 
            ['name' => $user['name'], 'email' => $user['email'], 'role_id' => $user['role_id']], 
            ['name' => $name, 'email' => $email, 'role_id' => $updateData['role_id'] ?? $user['role_id']]
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
