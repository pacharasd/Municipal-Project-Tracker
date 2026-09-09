<?php

namespace App\Services;

use App\Core\Database;
use App\Services\AuditLogService;
use Exception;

class ProjectService
{
    private static ?bool $hasRespCol = null;

    public static function hasResponsiblePersonColumn(): bool
    {
        if (self::$hasRespCol !== null) {
            return self::$hasRespCol;
        }
        try {
            $col = Database::fetch("SHOW COLUMNS FROM `projects` LIKE 'responsible_person'");
            self::$hasRespCol = !empty($col);
        } catch (Exception $e) {
            self::$hasRespCol = false;
        }
        return self::$hasRespCol;
    }

    public static function generateNextProjectCode(int $fiscalYearId): string
    {
        $yearRow = Database::fetch("SELECT year FROM fiscal_years WHERE id = ?", [$fiscalYearId]);
        $year = !empty($yearRow['year']) ? (int)$yearRow['year'] : (date('Y') + 543);

        $latestCode = Database::fetchColumn(
            "SELECT project_code FROM projects 
             WHERE parent_id IS NULL AND fiscal_year_id = ? AND project_code LIKE ? 
             ORDER BY id DESC LIMIT 1",
            [$fiscalYearId, "PRJ-{$year}-%"]
        );

        $nextNum = 1;
        if ($latestCode && preg_match('/-(\d+)$/', $latestCode, $m)) {
            $nextNum = (int)$m[1] + 1;
        } else {
            $totalInYear = (int)Database::fetchColumn(
                "SELECT COUNT(*) FROM projects WHERE parent_id IS NULL AND fiscal_year_id = ?",
                [$fiscalYearId]
            );
            $nextNum = max(1, $totalInYear + 1);
        }

        do {
            $code = sprintf("PRJ-%d-%03d", $year, $nextNum);
            $exists = (int)Database::fetchColumn("SELECT COUNT(*) FROM projects WHERE project_code = ?", [$code]);
            if (!$exists) {
                return $code;
            }
            $nextNum++;
        } while ($nextNum < 10000);

        return sprintf("PRJ-%d-%04d", $year, $nextNum);
    }

    public static function getMainProjects(array $filters = []): array
    {
        $respExpr = self::hasResponsiblePersonColumn()
            ? "COALESCE(NULLIF(p.responsible_person, ''), u.name, d.name)"
            : "COALESCE(u.name, d.name)";

        $sql = "SELECT p.*, 
                       d.name as department_name, d.code as department_code,
                       c.name as category_name, c.icon as category_icon,
                       f.year as fiscal_year,
                       {$respExpr} as responsible_name,
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
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            if (self::hasResponsiblePersonColumn()) {
                $sql .= " AND (p.name LIKE ? OR p.project_code LIKE ? OR p.responsible_person LIKE ?)";
                $params[] = "%{$filters['search']}%";
                $params[] = "%{$filters['search']}%";
                $params[] = "%{$filters['search']}%";
            } else {
                $sql .= " AND (p.name LIKE ? OR p.project_code LIKE ?)";
                $params[] = "%{$filters['search']}%";
                $params[] = "%{$filters['search']}%";
            }
        }

        $sql .= " ORDER BY p.id DESC";

        $projects = Database::query($sql, $params);

        // Attach sub-projects for each main project
        foreach ($projects as &$p) {
            $p['sub_projects'] = Database::query(
                "SELECT sub.*, 
                        (SELECT COUNT(*) FROM activities a WHERE a.project_id = sub.id) as actual_activity_count,
                        COALESCE(NULLIF(sub.responsible_person, ''), u.name) as responsible_name 
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
        $respExpr = self::hasResponsiblePersonColumn()
            ? "COALESCE(NULLIF(p.responsible_person, ''), u.name, d.name)"
            : "COALESCE(u.name, d.name)";

        $sql = "SELECT p.*, 
                       COALESCE(d.name, parent_dept.name) as department_name, 
                       COALESCE(d.code, parent_dept.code) as department_code,
                       COALESCE(c.name, parent_cat.name) as category_name, 
                       COALESCE(c.icon, parent_cat.icon) as category_icon,
                       COALESCE(f.year, parent_fy.year) as fiscal_year,
                       {$respExpr} as responsible_name, u.position as responsible_position,
                       parent.name as parent_name,
                       parent.budget as parent_budget,
                       parent.start_date as parent_start_date,
                       parent.end_date as parent_end_date
                FROM projects p
                LEFT JOIN departments d ON p.department_id = d.id
                LEFT JOIN project_categories c ON p.category_id = c.id
                LEFT JOIN fiscal_years f ON p.fiscal_year_id = f.id
                LEFT JOIN users u ON p.responsible_user_id = u.id
                LEFT JOIN projects parent ON p.parent_id = parent.id
                LEFT JOIN departments parent_dept ON parent.department_id = parent_dept.id
                LEFT JOIN project_categories parent_cat ON parent.category_id = parent_cat.id
                LEFT JOIN fiscal_years parent_fy ON parent.fiscal_year_id = parent_fy.id
                WHERE p.id = ? LIMIT 1";

        $project = Database::fetch($sql, [$id]);
        if (!$project) {
            return null;
        }

        if ($project['parent_id'] === null) {
            // Main project: fetch sub-projects
            $project['sub_projects'] = Database::query(
                "SELECT s.*, 
                        (SELECT COUNT(*) FROM activities a WHERE a.project_id = s.id) as actual_activity_count,
                        COALESCE(NULLIF(s.responsible_person, ''), u.name) as responsible_name 
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
            $project['actual_activity_count'] = count($project['activities']);

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

            // Calculate parent remaining budget that this sub-project can occupy (Parent budget minus other sub-projects' budgets)
            $otherSubsBudget = (float)Database::fetchColumn(
                "SELECT COALESCE(SUM(budget), 0) FROM projects WHERE parent_id = ? AND id != ?",
                [$project['parent_id'], $id]
            );
            $parentBudget = (float)($project['parent_budget'] ?? 0);
            $project['other_subs_budget'] = $otherSubsBudget;
            $project['parent_remaining_budget'] = max(0, $parentBudget - $otherSubsBudget);
        }

        return $project;
    }

    public static function getWatchlist(?int $fiscalYearId = null): array
    {
        $respExpr = self::hasResponsiblePersonColumn()
            ? "COALESCE(NULLIF(p.responsible_person, ''), u.name, d.name)"
            : "COALESCE(u.name, d.name)";

        $sql = "SELECT p.*, parent.name as parent_name, 
                       COALESCE(d.name, parent_dept.name) as department_name, 
                       {$respExpr} as responsible_name
                FROM projects p
                LEFT JOIN projects parent ON p.parent_id = parent.id
                LEFT JOIN departments d ON p.department_id = d.id
                LEFT JOIN departments parent_dept ON parent.department_id = parent_dept.id
                LEFT JOIN users u ON p.responsible_user_id = u.id
                WHERE p.parent_id IS NOT NULL 
                  AND (p.status = 'has_problem' OR (p.end_date < CURDATE() AND p.status != 'completed'))";
        
        $params = [];
        if ($fiscalYearId !== null) {
            $sql .= " AND (p.fiscal_year_id = ? OR parent.fiscal_year_id = ?)";
            $params = [$fiscalYearId, $fiscalYearId];
        }

        $sql .= " ORDER BY p.status = 'has_problem' DESC, p.end_date ASC";
        return Database::query($sql, $params);
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

    public static function getDashboardStats(?int $fiscalYearId = null): array
    {
        // 1. Overall stats filters
        $mainCond = "WHERE parent_id IS NULL";
        $mainParams = [];
        if ($fiscalYearId !== null) {
            $mainCond .= " AND fiscal_year_id = ?";
            $mainParams[] = $fiscalYearId;
        }

        $subBase = "FROM projects s INNER JOIN projects p ON s.parent_id = p.id WHERE p.parent_id IS NULL";
        $subParams = [];
        if ($fiscalYearId !== null) {
            $subBase .= " AND (s.fiscal_year_id = ? OR p.fiscal_year_id = ?)";
            $subParams = [$fiscalYearId, $fiscalYearId];
        }

        $mainTotal = (int)Database::fetchColumn("SELECT COUNT(*) FROM projects {$mainCond}", $mainParams);
        $subTotal  = (int)Database::fetchColumn("SELECT COUNT(*) {$subBase}", $subParams);
        
        $notStarted = (int)Database::fetchColumn("SELECT COUNT(*) {$subBase} AND s.status = 'not_started'", $subParams);
        $inProgress = (int)Database::fetchColumn("SELECT COUNT(*) {$subBase} AND s.status = 'in_progress'", $subParams);
        $completed  = (int)Database::fetchColumn("SELECT COUNT(*) {$subBase} AND s.status = 'completed'", $subParams);
        $hasProblem = (int)Database::fetchColumn("SELECT COUNT(*) {$subBase} AND s.status = 'has_problem'", $subParams);
        $cancelled  = (int)Database::fetchColumn("SELECT COUNT(*) {$subBase} AND s.status = 'cancelled'", $subParams);

        // Budgets
        $budgetRow = Database::fetch("SELECT SUM(budget) as total_budget, SUM(disbursed_amount) as total_disbursed FROM projects {$mainCond}", $mainParams);
        $totalBudget = (float)($budgetRow['total_budget'] ?? 0);
        $totalDisbursed = (float)($budgetRow['total_disbursed'] ?? 0);
        $totalRemaining = $totalBudget - $totalDisbursed;
        $disbursementPct = $totalBudget > 0 ? round(($totalDisbursed / $totalBudget) * 100, 2) : 0.0;

        // Average progress
        $avgProgress = (float)Database::fetchColumn("SELECT AVG(progress) FROM projects {$mainCond}", $mainParams);

        // 2. Department Chart Data
        if ($fiscalYearId !== null) {
            $deptSql = "SELECT d.name, 
                               COUNT(s.id) as project_count, 
                               COALESCE(SUM(s.budget), 0) as total_budget, 
                               COALESCE(SUM(s.disbursed_amount), 0) as total_disbursed, 
                               COALESCE(AVG(s.progress), 0) as avg_progress 
                        FROM departments d 
                        LEFT JOIN projects s ON d.id = s.department_id AND s.parent_id IS NOT NULL AND (s.fiscal_year_id = ? OR s.parent_id IN (SELECT id FROM projects WHERE parent_id IS NULL AND fiscal_year_id = ?))
                        GROUP BY d.id, d.name ORDER BY project_count DESC, d.id ASC";
            $deptData = Database::query($deptSql, [$fiscalYearId, $fiscalYearId]);
        } else {
            $deptSql = "SELECT d.name, 
                               COUNT(s.id) as project_count, 
                               COALESCE(SUM(s.budget), 0) as total_budget, 
                               COALESCE(SUM(s.disbursed_amount), 0) as total_disbursed, 
                               COALESCE(AVG(s.progress), 0) as avg_progress 
                        FROM departments d 
                        LEFT JOIN projects s ON d.id = s.department_id AND s.parent_id IS NOT NULL AND s.parent_id IN (SELECT id FROM projects WHERE parent_id IS NULL)
                        GROUP BY d.id, d.name ORDER BY project_count DESC, d.id ASC";
            $deptData = Database::query($deptSql);
        }

        // 3. Category Distribution (ประเภทโครงการ 1 - 8 กปท.)
        if ($fiscalYearId !== null) {
            $catSql = "SELECT c.id, c.name, c.icon,
                              COUNT(p.id) as project_count, 
                              COALESCE(SUM(p.budget), 0) as total_budget,
                              COALESCE(SUM(p.disbursed_amount), 0) as total_disbursed,
                              (SELECT COUNT(*) FROM projects sub WHERE sub.parent_id IN (SELECT p2.id FROM projects p2 WHERE p2.category_id = c.id AND p2.parent_id IS NULL AND p2.fiscal_year_id = ?)) as sub_project_count
                       FROM project_categories c 
                       LEFT JOIN projects p ON c.id = p.category_id AND p.parent_id IS NULL AND p.fiscal_year_id = ?
                       GROUP BY c.id, c.name, c.icon 
                       ORDER BY c.id ASC";
            $catData = Database::query($catSql, [$fiscalYearId, $fiscalYearId]);
        } else {
            $catSql = "SELECT c.id, c.name, c.icon,
                              COUNT(p.id) as project_count, 
                              COALESCE(SUM(p.budget), 0) as total_budget,
                              COALESCE(SUM(p.disbursed_amount), 0) as total_disbursed,
                              (SELECT COUNT(*) FROM projects sub WHERE sub.parent_id IN (SELECT p2.id FROM projects p2 WHERE p2.category_id = c.id AND p2.parent_id IS NULL)) as sub_project_count
                       FROM project_categories c 
                       LEFT JOIN projects p ON c.id = p.category_id AND p.parent_id IS NULL
                       GROUP BY c.id, c.name, c.icon 
                       ORDER BY c.id ASC";
            $catData = Database::query($catSql);
        }

        foreach ($catData as &$cat) {
            if (preg_match('/^(ประเภท\s*\d+)/u', $cat['name'], $m)) {
                $cat['short_name'] = $m[1];
            } else {
                $cat['short_name'] = mb_substr($cat['name'], 0, 15, 'UTF-8') . (mb_strlen($cat['name'], 'UTF-8') > 15 ? '...' : '');
            }
        }
        unset($cat);

        // 4. Top and Bottom main projects (ความก้าวหน้าของโครงการหลัก)
        $topSql = "SELECT m.id, m.name, m.progress, m.status, m.budget 
                   FROM projects m 
                   WHERE m.parent_id IS NULL";
        $topParams = [];
        if ($fiscalYearId !== null) {
            $topSql .= " AND m.fiscal_year_id = ?";
            $topParams = [$fiscalYearId];
        }
        $topProjects = Database::query($topSql . " ORDER BY m.progress DESC, m.id ASC LIMIT 5", $topParams);
        $bottomProjects = Database::query($topSql . " ORDER BY m.progress ASC, m.id ASC LIMIT 5", $topParams);

        foreach ($topProjects as &$tp) {
            $tp['short_name'] = mb_substr($tp['name'], 0, 32, 'UTF-8') . (mb_strlen($tp['name'], 'UTF-8') > 32 ? '...' : '');
            $tp['progress'] = (float)$tp['progress'];
        }
        unset($tp);

        foreach ($bottomProjects as &$bp) {
            $bp['short_name'] = mb_substr($bp['name'], 0, 32, 'UTF-8') . (mb_strlen($bp['name'], 'UTF-8') > 32 ? '...' : '');
            $bp['progress'] = (float)$bp['progress'];
        }
        unset($bp);

        // 5. Main Projects Progress Data for Dashboard Chart
        $mainProjectsSql = "SELECT p.id, p.name, p.progress, p.budget, p.disbursed_amount, p.status,
                                   d.name as department_name,
                                   COUNT(s.id) as sub_project_count
                            FROM projects p
                            LEFT JOIN departments d ON p.department_id = d.id
                            LEFT JOIN projects s ON s.parent_id = p.id
                            WHERE p.parent_id IS NULL";
        $mainProjParams = [];
        if ($fiscalYearId !== null) {
            $mainProjectsSql .= " AND p.fiscal_year_id = ?";
            $mainProjParams[] = $fiscalYearId;
        }
        $mainProjectsSql .= " GROUP BY p.id, p.name, p.progress, p.budget, p.disbursed_amount, p.status, d.name ORDER BY p.id ASC";
        $mainProjectsData = Database::query($mainProjectsSql, $mainProjParams);

        // 6. Fiscal Year Budget Data (for Yearly Budget Comparison Chart)
        $fiscalYearData = Database::query(
            "SELECT fy.id, fy.year, fy.is_active,
                    COUNT(p.id) as project_count,
                    COALESCE(SUM(p.budget), 0) as total_budget,
                    COALESCE(SUM(p.disbursed_amount), 0) as total_disbursed
             FROM fiscal_years fy
             LEFT JOIN projects p ON fy.id = p.fiscal_year_id AND p.parent_id IS NULL
             GROUP BY fy.id, fy.year, fy.is_active
             ORDER BY fy.year ASC"
        );

        // 7. Monthly Disbursement Timeline & Quarters (Disbursement Trend Line Chart)
        $disbSql = "SELECT MONTH(d.disbursement_date) as month_num,
                           COALESCE(SUM(d.amount), 0) as total_amount
                    FROM budget_disbursements d
                    INNER JOIN projects p ON d.project_id = p.id
                    LEFT JOIN projects parent ON p.parent_id = parent.id";
        $disbParams = [];
        if ($fiscalYearId !== null) {
            $disbSql .= " WHERE (p.fiscal_year_id = ? OR parent.fiscal_year_id = ?)";
            $disbParams = [$fiscalYearId, $fiscalYearId];
        }
        $disbSql .= " GROUP BY MONTH(d.disbursement_date)";
        $disbRows = Database::query($disbSql, $disbParams);

        $monthAmountMap = [];
        foreach ($disbRows as $r) {
            $monthAmountMap[(int)$r['month_num']] = (float)$r['total_amount'];
        }

        $fiscalMonthsMeta = [
            ['name' => 'ต.ค.', 'full_name' => 'ตุลาคม', 'month_num' => 10],
            ['name' => 'พ.ย.', 'full_name' => 'พฤศจิกายน', 'month_num' => 11],
            ['name' => 'ธ.ค.', 'full_name' => 'ธันวาคม', 'month_num' => 12],
            ['name' => 'ม.ค.', 'full_name' => 'มกราคม', 'month_num' => 1],
            ['name' => 'ก.พ.', 'full_name' => 'กุมภาพันธ์', 'month_num' => 2],
            ['name' => 'มี.ค.', 'full_name' => 'มีนาคม', 'month_num' => 3],
            ['name' => 'เม.ย.', 'full_name' => 'เมษายน', 'month_num' => 4],
            ['name' => 'พ.ค.', 'full_name' => 'พฤษภาคม', 'month_num' => 5],
            ['name' => 'มิ.ย.', 'full_name' => 'มิถุนายน', 'month_num' => 6],
            ['name' => 'ก.ค.', 'full_name' => 'กรกฎาคม', 'month_num' => 7],
            ['name' => 'ส.ค.', 'full_name' => 'สิงหาคม', 'month_num' => 8],
            ['name' => 'ก.ย.', 'full_name' => 'กันยายน', 'month_num' => 9],
        ];

        $disbursementTimeline = [];
        $runningCumulative = 0.0;
        foreach ($fiscalMonthsMeta as $m) {
            $monthNum = $m['month_num'];
            $monthlyAmt = (float)($monthAmountMap[$monthNum] ?? 0.0);
            $runningCumulative += $monthlyAmt;

            $disbursementTimeline[] = [
                'name'         => $m['name'],
                'full_name'    => $m['full_name'],
                'monthly'      => $monthlyAmt,
                'cumulative'   => $runningCumulative,
                'cum_pct'      => $totalBudget > 0 ? round(($runningCumulative / $totalBudget) * 100, 2) : 0.0,
            ];
        }

        return [
            'main_total'            => $mainTotal,
            'sub_total'             => $subTotal,
            'not_started'           => $notStarted,
            'in_progress'           => $inProgress,
            'completed'             => $completed,
            'has_problem'           => $hasProblem,
            'cancelled'             => $cancelled,
            'total_budget'          => $totalBudget,
            'total_disbursed'       => $totalDisbursed,
            'total_remaining'       => $totalRemaining,
            'disbursement_pct'      => $disbursementPct,
            'avg_progress'          => round($avgProgress, 2),
            'department_data'       => $deptData,
            'fiscal_year_data'      => $fiscalYearData,
            'main_projects_data'    => $mainProjectsData,
            'category_data'         => $catData,
            'disbursement_timeline' => $disbursementTimeline,
            'top_projects'          => $topProjects,
            'bottom_projects'       => $bottomProjects,
        ];
    }
}
