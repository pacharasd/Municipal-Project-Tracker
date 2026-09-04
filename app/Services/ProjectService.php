<?php

namespace App\Services;

use App\Core\Database;
use App\Services\AuditLogService;
use Exception;

class ProjectService
{
    public static function getMainProjects(array $filters = []): array
    {
        $sql = "SELECT p.*, 
                       d.name as department_name, d.code as department_code,
                       c.name as category_name, c.icon as category_icon,
                       f.year as fiscal_year,
                       u.name as responsible_name,
                       (SELECT COUNT(*) FROM projects sub WHERE sub.parent_id = p.id) as sub_project_count,
                       (SELECT COUNT(*) FROM projects sub WHERE sub.parent_id = p.id AND sub.status = 'completed') as completed_sub_count,
                       (SELECT COUNT(*) FROM projects sub WHERE sub.parent_id = p.id AND sub.status = 'has_problem') as problem_sub_count
                FROM projects p
                LEFT JOIN departments d ON p.department_id = d.id
                LEFT JOIN project_categories c ON p.category_id = c.id
                LEFT JOIN fiscal_years f ON p.fiscal_year_id = f.id
                LEFT JOIN users u ON p.responsible_user_id = u.id
                WHERE p.parent_id IS NULL";

        $params = [];

        if (!empty($filters['fiscal_year_id'])) {
            $sql .= " AND p.fiscal_year_id = ?";
            $params[] = $filters['fiscal_year_id'];
        }
        if (!empty($filters['department_id'])) {
            $sql .= " AND p.department_id = ?";
            $params[] = $filters['department_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.project_code LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }

        $sql .= " ORDER BY p.id DESC";

        $projects = Database::query($sql, $params);

        // Attach sub-projects for each main project
        foreach ($projects as &$p) {
            $p['sub_projects'] = Database::query(
                "SELECT sub.*, u.name as responsible_name 
                 FROM projects sub 
                 LEFT JOIN users u ON sub.responsible_user_id = u.id
                 WHERE sub.parent_id = ? ORDER BY sub.id ASC",
                [$p['id']]
            );
        }

        return $projects;
    }

    public static function getProjectById(int $id): ?array
    {
        $sql = "SELECT p.*, 
                       d.name as department_name, d.code as department_code,
                       c.name as category_name, c.icon as category_icon,
                       f.year as fiscal_year,
                       u.name as responsible_name, u.position as responsible_position,
                       parent.name as parent_name, parent.project_code as parent_code
                FROM projects p
                LEFT JOIN departments d ON p.department_id = d.id
                LEFT JOIN project_categories c ON p.category_id = c.id
                LEFT JOIN fiscal_years f ON p.fiscal_year_id = f.id
                LEFT JOIN users u ON p.responsible_user_id = u.id
                LEFT JOIN projects parent ON p.parent_id = parent.id
                WHERE p.id = ? LIMIT 1";

        $project = Database::fetch($sql, [$id]);
        if (!$project) {
            return null;
        }

        if ($project['parent_id'] === null) {
            // Main project: fetch sub-projects
            $project['sub_projects'] = Database::query(
                "SELECT s.*, u.name as responsible_name 
                 FROM projects s 
                 LEFT JOIN users u ON s.responsible_user_id = u.id
                 WHERE s.parent_id = ? ORDER BY s.id ASC",
                [$id]
            );
        } else {
            // Sub-project: fetch activities & disbursements
            $project['activities'] = Database::query(
                "SELECT a.*, u.name as responsible_name 
                 FROM activities a 
                 LEFT JOIN users u ON a.responsible_user_id = u.id 
                 WHERE a.project_id = ? ORDER BY a.activity_date ASC",
                [$id]
            );

            $project['disbursements'] = Database::query(
                "SELECT d.*, u.name as creator_name 
                 FROM budget_disbursements d 
                 LEFT JOIN users u ON d.created_by = u.id 
                 WHERE d.project_id = ? ORDER BY d.disbursement_date DESC",
                [$id]
            );

            $project['attachments'] = Database::query(
                "SELECT * FROM attachments WHERE project_id = ? ORDER BY id DESC",
                [$id]
            );
        }

        return $project;
    }

    public static function getWatchlist(): array
    {
        $sql = "SELECT p.*, parent.name as parent_name, d.name as department_name, u.name as responsible_name
                FROM projects p
                LEFT JOIN projects parent ON p.parent_id = parent.id
                LEFT JOIN departments d ON p.department_id = d.id
                LEFT JOIN users u ON p.responsible_user_id = u.id
                WHERE p.parent_id IS NOT NULL 
                  AND (p.status = 'has_problem' OR (p.end_date < CURDATE() AND p.status != 'completed'))
                ORDER BY p.status = 'has_problem' DESC, p.end_date ASC";
        return Database::query($sql);
    }

    public static function reportProblem(int $projectId, string $problemDescription): bool
    {
        $project = Database::fetch("SELECT * FROM projects WHERE id = ?", [$projectId]);
        if (!$project) {
            throw new Exception("ไม่พบโครงการที่ต้องการแจ้งปัญหา");
        }

        $oldStatus = $project['status'];
        Database::update('projects', [
            'status' => 'has_problem',
            'problem_description' => $problemDescription,
        ], "id = ?", [$projectId]);

        if (!empty($project['parent_id'])) {
            ProgressService::syncParentProjectProgress($project['parent_id']);
        }

        AuditLogService::log('REPORT_PROBLEM', 'Project', $projectId, 
            ['status' => $oldStatus], 
            ['status' => 'has_problem', 'problem' => $problemDescription]
        );

        return true;
    }

    public static function resolveProblem(int $projectId, ?string $resolutionNote = null): bool
    {
        $project = Database::fetch("SELECT * FROM projects WHERE id = ?", [$projectId]);
        if (!$project) {
            throw new Exception("ไม่พบโครงการ");
        }

        $progress = (float)$project['progress'];
        $newStatus = $progress >= 100.0 ? 'completed' : ($progress > 0 ? 'in_progress' : 'not_started');

        Database::update('projects', [
            'status' => $newStatus,
            'problem_description' => null,
            'notes' => $resolutionNote ? ($project['notes'] . " | แก้ไขปัญหา: " . $resolutionNote) : $project['notes'],
        ], "id = ?", [$projectId]);

        if (!empty($project['parent_id'])) {
            ProgressService::syncParentProjectProgress($project['parent_id']);
        }

        AuditLogService::log('RESOLVE_PROBLEM', 'Project', $projectId, 
            ['status' => 'has_problem'], 
            ['status' => $newStatus, 'resolution' => $resolutionNote]
        );

        return true;
    }

    public static function getDashboardStats(): array
    {
        // 1. Overall stats
        $mainTotal = (int)Database::fetchColumn("SELECT COUNT(*) FROM projects WHERE parent_id IS NULL");
        $subTotal  = (int)Database::fetchColumn("SELECT COUNT(*) FROM projects WHERE parent_id IS NOT NULL");
        
        $notStarted = (int)Database::fetchColumn("SELECT COUNT(*) FROM projects WHERE parent_id IS NOT NULL AND status = 'not_started'");
        $inProgress = (int)Database::fetchColumn("SELECT COUNT(*) FROM projects WHERE parent_id IS NOT NULL AND status = 'in_progress'");
        $completed  = (int)Database::fetchColumn("SELECT COUNT(*) FROM projects WHERE parent_id IS NOT NULL AND status = 'completed'");
        $hasProblem = (int)Database::fetchColumn("SELECT COUNT(*) FROM projects WHERE parent_id IS NOT NULL AND status = 'has_problem'");
        $cancelled  = (int)Database::fetchColumn("SELECT COUNT(*) FROM projects WHERE parent_id IS NOT NULL AND status = 'cancelled'");

        // Budgets
        $budgetRow = Database::fetch("SELECT SUM(budget) as total_budget, SUM(disbursed_amount) as total_disbursed FROM projects WHERE parent_id IS NULL");
        $totalBudget = (float)($budgetRow['total_budget'] ?? 0);
        $totalDisbursed = (float)($budgetRow['total_disbursed'] ?? 0);
        $totalRemaining = $totalBudget - $totalDisbursed;
        $disbursementPct = $totalBudget > 0 ? round(($totalDisbursed / $totalBudget) * 100, 2) : 0.0;

        // Average progress
        $avgProgress = (float)Database::fetchColumn("SELECT AVG(progress) FROM projects WHERE parent_id IS NULL");

        // 2. Department Chart Data
        $deptData = Database::query(
            "SELECT d.name, COUNT(p.id) as project_count, SUM(p.budget) as total_budget, AVG(p.progress) as avg_progress 
             FROM departments d 
             LEFT JOIN projects p ON d.id = p.department_id AND p.parent_id IS NULL
             GROUP BY d.id, d.name ORDER BY d.id ASC"
        );

        // 3. Category Distribution
        $catData = Database::query(
            "SELECT c.name, COUNT(p.id) as project_count, SUM(p.budget) as total_budget 
             FROM project_categories c 
             LEFT JOIN projects p ON c.id = p.category_id AND p.parent_id IS NULL
             GROUP BY c.id, c.name ORDER BY project_count DESC"
        );

        // 4. Top and Bottom sub-projects
        $topProjects = Database::query(
            "SELECT name, progress, status, budget FROM projects WHERE parent_id IS NOT NULL ORDER BY progress DESC LIMIT 4"
        );
        $bottomProjects = Database::query(
            "SELECT name, progress, status, budget FROM projects WHERE parent_id IS NOT NULL ORDER BY progress ASC LIMIT 4"
        );

        return [
            'main_total'        => $mainTotal,
            'sub_total'         => $subTotal,
            'not_started'       => $notStarted,
            'in_progress'       => $inProgress,
            'completed'         => $completed,
            'has_problem'       => $hasProblem,
            'cancelled'         => $cancelled,
            'total_budget'      => $totalBudget,
            'total_disbursed'   => $totalDisbursed,
            'total_remaining'   => $totalRemaining,
            'disbursement_pct'  => $disbursementPct,
            'avg_progress'      => round($avgProgress, 2),
            'department_data'   => $deptData,
            'category_data'     => $catData,
            'top_projects'      => $topProjects,
            'bottom_projects'   => $bottomProjects,
        ];
    }
}
