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

        View::render('dashboard.index', [
            'stats'        => $stats,
            'watchlist'    => $watchlist,
            'recentAudit'  => $recentAudit,
            'fiscalYears'  => $fiscalYears,
            'departments'  => $departments,
        ]);
    }

    public function statsJson(): void
    {
        $stats = ProjectService::getDashboardStats();
        View::json($stats);
    }
}
