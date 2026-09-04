<?php

namespace App\Http\Controllers;

use App\Core\View;
use App\Core\Database;
use App\Services\ProjectService;

class DashboardController
{
    public function index(): void
    {
        $stats = ProjectService::getDashboardStats();
        $watchlist = ProjectService::getWatchlist();
        $recentAudit = Database::query(
            "SELECT a.*, u.name as user_name, r.display_name as role_label 
             FROM audit_logs a 
             LEFT JOIN users u ON a.user_id = u.id 
             LEFT JOIN roles r ON u.role_id = r.id 
             ORDER BY a.id DESC LIMIT 6"
        );

        $fiscalYears = Database::query("SELECT * FROM fiscal_years ORDER BY year DESC");
        $departments = Database::query("SELECT * FROM departments ORDER BY id ASC");

        $subProjects = Database::query(
            "SELECT s.id, s.project_code, s.name, s.parent_id, s.budget, s.disbursed_amount, s.progress, s.status, s.start_date, s.end_date,
                    parent.project_code as parent_code, parent.name as parent_name,
                    d.name as department_name,
                    u.name as responsible_name,
                    (SELECT COUNT(*) FROM activities WHERE project_id = s.id) as activity_count,
                    (SELECT COUNT(*) FROM activities WHERE project_id = s.id AND status = 'completed') as completed_activity_count
             FROM projects s
             INNER JOIN projects parent ON s.parent_id = parent.id
             LEFT JOIN departments d ON s.department_id = d.id
             LEFT JOIN users u ON s.responsible_user_id = u.id
             WHERE s.parent_id IS NOT NULL
             ORDER BY s.id ASC"
        );

        View::render('dashboard.index', [
            'stats'        => $stats,
            'watchlist'    => $watchlist,
            'recentAudit'  => $recentAudit,
            'fiscalYears'  => $fiscalYears,
            'departments'  => $departments,
            'subProjects'  => $subProjects,
        ]);
    }

    public function statsJson(): void
    {
        $stats = ProjectService::getDashboardStats();
        View::json($stats);
    }
}
