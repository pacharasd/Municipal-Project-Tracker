<?php

namespace App\Core;

use App\Core\Database;
use App\Core\Session;

class Auth
{
    private static ?array $currentUser = null;

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            self::$currentUser = null;
            return null;
        }

        $userId = Session::get('user_id');
        if (self::$currentUser !== null && (int)(self::$currentUser['id'] ?? 0) === (int)$userId) {
            return self::$currentUser;
        }

        $sql = "SELECT u.*, r.name as role_name, r.display_name as role_label,
                       d.name as department_name, d.code as department_code 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                LEFT JOIN departments d ON u.department_id = d.id 
                WHERE u.id = ? LIMIT 1";
        self::$currentUser = Database::fetch($sql, [$userId]);
        return self::$currentUser;
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

        // บทบาทผู้บริหาร (Executive) มีหน้าที่กำกับดูแลภาพรวม ไม่ได้เป็นผู้รับผิดชอบโครงการรายโครงการ (Least Privilege & Role Segregation)
        $targetUser = Database::fetch("SELECT r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?", [$userId]);
        $isExecutive = ($targetUser && ($targetUser['role_name'] ?? '') === 'executive');

        if ($isExecutive) {
            $recentLogs = Database::query(
                "SELECT action, module, record_id, created_at 
                 FROM audit_logs 
                 WHERE user_id = ? 
                 ORDER BY created_at DESC 
                 LIMIT 5",
                [$userId]
            ) ?: [];

            return [
                'project_count'     => 0,
                'in_progress_count' => 0,
                'completed_count'   => 0,
                'total_budget'      => 0.0,
                'assigned_projects' => [],
                'recent_activities' => $recentLogs,
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

    public static function setUser(?array $user): void
    {
        self::$currentUser = $user;
        if ($user) {
            Session::set('user_id', $user['id'] ?? null);
            Session::set('user_role', $user['role_name'] ?? null);
            Session::set('user_name', $user['name'] ?? null);
        } else {
            Session::remove('user_id');
            Session::remove('user_role');
            Session::remove('user_name');
        }
    }

    public static function role(): string
    {
        $user = self::user();
        if ($user && !empty($user['role_name'])) {
            return $user['role_name'];
        }
        return Session::get('user_role') ?? 'guest';
    }

    public static function isAdmin(): bool
    {
        $role = strtolower(self::role());
        if (in_array($role, ['admin', 'administrator'])) {
            return true;
        }
        $user = self::user();
        return $user && ((int)($user['role_id'] ?? 0) === 1);
    }

    public static function isExecutive(): bool
    {
        return in_array(self::role(), ['admin', 'executive']);
    }

    public static function isStaff(): bool
    {
        $role = strtolower(self::role());
        return in_array($role, ['staff', 'officer']);
    }

    /**
     * เฉพาะ Administrator เท่านั้นที่สามารถจัดการโครงการหลักได้ (Parent Projects)
     */
    public static function canManageParentProjects(): bool
    {
        return self::isAdmin();
    }

    /**
     * Administrator และ Staff สามารถจัดการกิจกรรมหลักได้ (Sub-projects)
     */
    public static function canManageSubProjects(): bool
    {
        return self::isAdmin() || self::isStaff();
    }

    /**
     * Administrator และ Staff สามารถจัดการกิจกรรมย่อยได้ (Activities)
     */
    public static function canManageActivities(): bool
    {
        return self::isAdmin() || self::isStaff();
    }

    /**
     * สิทธิ์เดิมสำหรับการจัดการโครงการย่อย/กิจกรรม (คงไว้เพื่อ Backward Compatibility)
     */
    public static function canManageProjects(): bool
    {
        return self::canManageSubProjects();
    }

    /**
     * เฉพาะ Administrator เท่านั้นที่สามารถบันทึกหรือแก้ไขการเบิกจ่ายงบประมาณได้
     */
    public static function canDisburse(): bool
    {
        return self::isAdmin();
    }

    /**
     * เฉพาะ Administrator เท่านั้นที่สามารถจัดการผู้ใช้งานได้
     */
    public static function canManageUsers(): bool
    {
        return self::isAdmin();
    }

    /**
     * เฉพาะ Administrator เท่านั้นที่สามารถจัดการประเภทโครงการได้
     */
    public static function canManageCategories(): bool
    {
        return self::isAdmin();
    }

    /**
     * เฉพาะ Administrator เท่านั้นที่สามารถดูและจัดการ Audit Logs ได้
     */
    public static function canViewAuditLogs(): bool
    {
        return self::isAdmin();
    }

    /**
     * Pre-calculated dummy Bcrypt hash to equalize timing against enumeration attacks (OWASP ASVS 2.1.8).
     */
    private const DUMMY_BCRYPT_HASH = '$2y$10$EulcO7BKJ9sqnUMTvwK8z.hXVkqIbW480F5H/MFrScpCTlxgfWfzO';

    /**
     * Equalize timing for non-existent users to mitigate timing attacks.
     */
    public static function dummyPasswordVerify(string $password): void
    {
        password_verify($password, self::DUMMY_BCRYPT_HASH);
    }

    /**
     * Authenticate and establish a session for the user with Session Fixation mitigation (OWASP ASVS 3.2.1).
     */
    public static function login(array $user): void
    {
        self::$currentUser = null;

        // Mitigate Session Fixation by issuing a brand-new session ID
        Session::regenerate(true);

        Session::set('user_id', $user['id']);
        Session::set('user_role', $user['role_name'] ?? 'admin');
        Session::set('user_name', $user['name']);
        Session::set('login_time', time());

        // Update user telemetry
        $ip = substr($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', 0, 45);
        try {
            Database::query(
                "UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?",
                [$ip, $user['id']]
            );
        } catch (\Throwable $e) {
            // Non-blocking telemetry failure
        }
    }

    /**
     * Log out current user, clear session tokens, and invalidate session ID cleanly.
     */
    public static function logout(): void
    {
        self::$currentUser = null;
        Session::remove('user_id');
        Session::remove('user_role');
        Session::remove('user_name');
        Session::remove('login_time');

        // Regenerate to prevent session reuse
        Session::regenerate(true);
    }
}
