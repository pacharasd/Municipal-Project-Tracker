<?php

namespace App\Http\Controllers;

use App\Core\View;
use App\Core\Database;
use App\Services\ProjectService;

class DashboardController
{
    public function index(): void
    {
        $fiscalYears = \App\Services\FiscalYearService::getFilterableYears();
        $activeYear = \App\Services\FiscalYearService::getActiveYear();

        // Determine selected fiscal year with Smart Fallback
        $rawYearParam = $_GET['fiscal_year_id'] ?? null;
        if ($rawYearParam === null) {
            // Default to active fiscal year if not provided in URL
            $defaultYearId = $activeYear ? (int)$activeYear['id'] : null;
            if ($defaultYearId !== null) {
                // Check if active fiscal year has projects
                $hasProjectsInActive = (int)Database::fetchColumn(
                    "SELECT COUNT(*) FROM projects WHERE fiscal_year_id = ? OR parent_id IN (SELECT id FROM projects WHERE fiscal_year_id = ?)",
                    [$defaultYearId, $defaultYearId]
                ) > 0;

                if (!$hasProjectsInActive) {
                    // Smart fallback: Check if there is another fiscal year that has projects
                    $yearWithProjects = Database::fetch(
                        "SELECT fy.id, fy.year FROM fiscal_years fy 
                         INNER JOIN projects p ON p.fiscal_year_id = fy.id 
                         ORDER BY fy.year DESC LIMIT 1"
                    );
                    if ($yearWithProjects) {
                        $defaultYearId = (int)$yearWithProjects['id'];
                    }
                }
            }
            $selectedYearId = $defaultYearId !== null ? $defaultYearId : 'all';
            $filterYearId = $defaultYearId;
        } elseif ($rawYearParam === 'all') {
            $selectedYearId = 'all';
            $filterYearId = null;
        } else {
            $selectedYearId = (int)$rawYearParam;
            $filterYearId = $selectedYearId;
        }

        $stats = ProjectService::getDashboardStats($filterYearId);
        $watchlist = ProjectService::getWatchlist($filterYearId);
        $recentAudit = Database::query(
            "SELECT a.*, u.name as user_name, r.display_name as role_label 
             FROM audit_logs a 
             LEFT JOIN users u ON a.user_id = u.id 
             LEFT JOIN roles r ON u.role_id = r.id 
             ORDER BY a.id DESC LIMIT 6"
        );

        $departments = Database::query("SELECT * FROM departments ORDER BY id ASC");

        $subProjectSql = "SELECT s.id, s.name, s.parent_id, s.budget, s.disbursed_amount, s.progress, s.status, s.start_date, s.end_date,
                    parent.name as parent_name,
                    d.name as department_name,
                    u.name as responsible_name,
                    (SELECT COUNT(*) FROM activities WHERE project_id = s.id) as activity_count,
                    (SELECT COUNT(*) FROM activities WHERE project_id = s.id AND status = 'completed') as completed_activity_count
             FROM projects s
             INNER JOIN projects parent ON s.parent_id = parent.id
             LEFT JOIN departments d ON s.department_id = d.id
             LEFT JOIN users u ON s.responsible_user_id = u.id
             WHERE s.parent_id IS NOT NULL";
        $subParams = [];
        if ($filterYearId !== null) {
            $subProjectSql .= " AND (s.fiscal_year_id = ? OR parent.fiscal_year_id = ?)";
            $subParams = [$filterYearId, $filterYearId];
        }
        $subProjectSql .= " ORDER BY s.id ASC";
        $subProjects = Database::query($subProjectSql, $subParams);

        $latestSql = "SELECT s.id, s.name, s.budget, s.progress, s.status, s.updated_at, s.created_at,
                    d.name as department_name
             FROM projects s
             INNER JOIN projects parent ON s.parent_id = parent.id
             LEFT JOIN departments d ON s.department_id = d.id
             WHERE s.parent_id IS NOT NULL";
        $latestParams = [];
        if ($filterYearId !== null) {
            $latestSql .= " AND (s.fiscal_year_id = ? OR parent.fiscal_year_id = ?)";
            $latestParams = [$filterYearId, $filterYearId];
        }
        $latestSql .= " ORDER BY s.id DESC LIMIT 5";
        $latestProjects = Database::query($latestSql, $latestParams);

        $suggestedYear = null;
        if ($filterYearId !== null && ($stats['sub_total'] ?? 0) === 0) {
            $suggestedYear = Database::fetch(
                "SELECT fy.id, fy.year FROM fiscal_years fy 
                 INNER JOIN projects p ON p.fiscal_year_id = fy.id 
                 WHERE fy.id != ?
                 ORDER BY fy.year DESC LIMIT 1",
                [$filterYearId]
            );
        }

        View::render('dashboard.index', [
            'stats'          => $stats,
            'watchlist'      => $watchlist,
            'recentAudit'    => $recentAudit,
            'fiscalYears'    => $fiscalYears,
            'activeYear'     => $activeYear,
            'selectedYearId' => $selectedYearId,
            'departments'    => $departments,
            'subProjects'    => $subProjects,
            'latestProjects' => $latestProjects,
            'suggestedYear'  => $suggestedYear,
        ]);
    }

    public function statsJson(): void
    {
        $rawYearParam = $_GET['fiscal_year_id'] ?? null;
        $activeYear = \App\Services\FiscalYearService::getActiveYear();
        $filterYearId = null;
        if ($rawYearParam === null && $activeYear) {
            $filterYearId = (int)$activeYear['id'];
        } elseif ($rawYearParam !== null && $rawYearParam !== 'all') {
            $filterYearId = (int)$rawYearParam;
        }
        $stats = ProjectService::getDashboardStats($filterYearId);
        View::json($stats);
    }
}
