<?php

namespace App\Core;

use App\Core\Database;
use App\Core\Session;

class Auth
{
    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        $userId = Session::get('user_id');
        $sql = "SELECT u.*, r.name as role_name, r.display_name as role_label, d.name as department_name, d.code as department_code 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                LEFT JOIN departments d ON u.department_id = d.id 
                WHERE u.id = ? LIMIT 1";
        return Database::fetch($sql, [$userId]);
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function role(): string
    {
        $user = self::user();
        return $user['role_name'] ?? 'guest';
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function isExecutive(): bool
    {
        return in_array(self::role(), ['admin', 'executive']);
    }

    public static function isOfficer(): bool
    {
        return in_array(self::role(), ['admin', 'officer']);
    }

    public static function isProjectManager(): bool
    {
        return in_array(self::role(), ['admin', 'project_manager']);
    }

    public static function canManageProjects(): bool
    {
        return in_array(self::role(), ['admin', 'officer', 'project_manager']);
    }

    public static function canDisburse(): bool
    {
        return in_array(self::role(), ['admin', 'officer']);
    }

    public static function canManageUsers(): bool
    {
        return self::isAdmin();
    }

    public static function login(array $user): void
    {
        Session::set('user_id', $user['id']);
        Session::set('user_role', $user['role_name'] ?? 'officer');
        Session::set('user_name', $user['name']);
    }

    public static function logout(): void
    {
        Session::remove('user_id');
        Session::remove('user_role');
        Session::remove('user_name');
    }

    public static function switchUser(int $userId): bool
    {
        $user = Database::fetch("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?", [$userId]);
        if ($user) {
            self::login($user);
            return true;
        }
        return false;
    }
}
