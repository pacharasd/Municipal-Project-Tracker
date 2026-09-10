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
        $sql = "SELECT u.*, r.name as role_name, r.display_name as role_label,
                       d.name as department_name, d.code as department_code 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                LEFT JOIN departments d ON u.department_id = d.id 
                WHERE u.id = ? LIMIT 1";
        return Database::fetch($sql, [$userId]);
    }

    public static function userStats(?int $userId = null): array
    {
        $userId = $userId ?: self::id();
        if (!$userId) {
            return [
                'project_count'     => 0,
                'in_progress_count' => 0,
                'completed_count'   => 0,
                'total_budget'      => 0.0,
                'assigned_projects' => [],
                'recent_activities' => [],
            ];
        }

        // Summary counts
        $counts = Database::fetch(
            "SELECT 
                COUNT(*) as total_projects,
                COALESCE(SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END), 0) as in_progress_projects,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed_projects,
                COALESCE(SUM(budget), 0) as total_budget
             FROM projects 
             WHERE responsible_user_id = ?",
            [$userId]
        ) ?: [];

        // Top 5 assigned projects
        $assignedProjects = Database::query(
            "SELECT id, parent_id, project_code, name, status, progress, budget, start_date, end_date 
             FROM projects 
             WHERE responsible_user_id = ? 
             ORDER BY updated_at DESC, id DESC 
             LIMIT 5",
            [$userId]
        ) ?: [];

        // Latest 5 audit logs
        $recentLogs = Database::query(
            "SELECT action, module, record_id, created_at 
             FROM audit_logs 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT 5",
            [$userId]
        ) ?: [];

        return [
            'project_count'     => (int)($counts['total_projects'] ?? 0),
            'in_progress_count' => (int)($counts['in_progress_projects'] ?? 0),
            'completed_count'   => (int)($counts['completed_projects'] ?? 0),
            'total_budget'      => (float)($counts['total_budget'] ?? 0.0),
            'assigned_projects' => $assignedProjects,
            'recent_activities' => $recentLogs,
        ];
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
        $user = self::user();
        if (!$user) return false;
        $role = strtolower($user['role_name'] ?? '');
        return in_array($role, ['admin', 'administrator']) || ((int)($user['role_id'] ?? 0) === 1);
    }

    public static function isExecutive(): bool
    {
        return in_array(self::role(), ['admin', 'executive']);
    }

    public static function isOfficer(): bool
    {
        return self::isAdmin();
    }

    public static function isProjectManager(): bool
    {
        return self::isAdmin();
    }

    public static function canManageProjects(): bool
    {
        return self::isAdmin();
    }

    public static function canDisburse(): bool
    {
        return self::isAdmin();
    }

    public static function canManageUsers(): bool
    {
        return self::isAdmin();
    }

    public static function login(array $user): void
    {
        Session::set('user_id', $user['id']);
        Session::set('user_role', $user['role_name'] ?? 'admin');
        Session::set('user_name', $user['name']);
    }

    public static function logout(): void
    {
        Session::remove('user_id');
        Session::remove('user_role');
        Session::remove('user_name');
    }
}
