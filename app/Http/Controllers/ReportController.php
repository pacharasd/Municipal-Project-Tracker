<?php

namespace App\Http\Controllers;

use App\Core\Database;
use App\Core\View;

class ReportController
{
    /**
     * Query projects according to active GET filter parameters.
     */
    private function getFilteredProjects(): array
    {
        $fiscalYearId = $_GET['fiscal_year_id'] ?? '';
        $departmentId = $_GET['department_id'] ?? '';
        $status       = $_GET['status'] ?? '';
        $search       = trim($_GET['search'] ?? '');

        $sql = "SELECT p.*, 
                       parent.name as parent_name,
                       d.name as department_name,
                       f.year as fiscal_year,
                       u.name as responsible_name
                FROM projects p
                LEFT JOIN projects parent ON p.parent_id = parent.id
                LEFT JOIN departments d ON p.department_id = d.id
                LEFT JOIN fiscal_years f ON p.fiscal_year_id = f.id
                LEFT JOIN users u ON p.responsible_user_id = u.id
                WHERE 1=1";

        $params = [];
        if (!empty($fiscalYearId)) {
            $sql .= " AND p.fiscal_year_id = ?";
            $params[] = $fiscalYearId;
        }
        if (!empty($departmentId)) {
            $sql .= " AND p.department_id = ?";
            $params[] = $departmentId;
        }
        if (!empty($status)) {
            $mappedStatus = match($status) {
                'ยังไม่เริ่มดำเนินการ', 'ยังไม่เริ่ม', 'not_started' => 'not_started',
                'กำลังดำเนินการ', 'in_progress' => 'in_progress',
                'เสร็จสิ้น', 'completed' => 'completed',
                'มีปัญหา', 'has_problem' => 'has_problem',
                'ยกเลิก', 'cancelled' => 'cancelled',
                default => $status
            };
            $sql .= " AND p.status = ?";
            $params[] = $mappedStatus;
        }
        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.project_code LIKE ? OR d.name LIKE ? OR parent.name LIKE ? OR u.name LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY p.parent_id IS NULL DESC, p.id DESC";

        return Database::query($sql, $params);
    }

    public function index(): void
    {
        $fiscalYearId = $_GET['fiscal_year_id'] ?? '';
        $departmentId = $_GET['department_id'] ?? '';
        $status       = $_GET['status'] ?? '';

        $projects = $this->getFilteredProjects();
        $fiscalYears = \App\Services\FiscalYearService::getFilterableYears();
        $departments = \App\Services\DepartmentService::getAll();

        // Always provide full dataset for fluid real-time client-side Alpine filtering
        $allProjectsSql = "SELECT p.*, 
                       parent.name as parent_name,
                       d.name as department_name,
                       f.year as fiscal_year,
                       u.name as responsible_name
                FROM projects p
                LEFT JOIN projects parent ON p.parent_id = parent.id
                LEFT JOIN departments d ON p.department_id = d.id
                LEFT JOIN fiscal_years f ON p.fiscal_year_id = f.id
                LEFT JOIN users u ON p.responsible_user_id = u.id
                ORDER BY p.parent_id IS NULL DESC, p.id DESC";
        $allProjects = Database::query($allProjectsSql);

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPageRaw = $_GET['per_page'] ?? '15';
        $perPage = ($perPageRaw === 'all') ? 'all' : max(1, (int)$perPageRaw);

        View::render('reports.index', [
            'projects'     => $projects,
            'allProjects'  => $allProjects,
            'fiscalYears'  => $fiscalYears,
            'departments'  => $departments,
            'fiscalYearId' => $fiscalYearId,
            'departmentId' => $departmentId,
            'status'       => $status,
            'page'         => $page,
            'perPage'      => $perPage,
        ]);
    }

    /**
     * Export projects to Microsoft Excel spreadsheet (.xls) with international standards.
     */
    public function exportExcel(): void
    {
        $projects = $this->getFilteredProjects();
        $filename = "municipal_projects_report_" . date('Ymd_His') . ".xls";

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate');
        header('Pragma: public');

        // UTF-8 Byte Order Mark
        echo chr(0xEF) . chr(0xBB) . chr(0xBF);
        ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>ข้อมูลโครงการ</x:Name>
    <x:WorksheetOptions>
     <x:DisplayGridlines/>
     <x:Print>
      <x:Orientation>Landscape</x:Orientation>
     </x:Print>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
  </x:ExcelWorksheets>
 </x:ExcelWorkbook>
</xml>
<![endif]-->
<style>
    body { font-family: 'Sarabun', Tahoma, sans-serif; font-size: 10.5pt; }
    table { border-collapse: collapse; }
    th { background-color: #f1f5f9; color: #0f172a; font-weight: bold; border: 1px solid #cbd5e1; padding: 6px 10px; text-align: center; white-space: nowrap; }
    td { border: 1px solid #e2e8f0; padding: 5px 8px; vertical-align: middle; }
    .num { mso-number-format: "\#\,\#\#0\.00"; text-align: right; }
    .pct { mso-number-format: "0\.0%"; text-align: center; }
    .txt { mso-number-format: "\@"; }
    .date { mso-number-format: "yyyy\-mm\-dd"; text-align: center; }
    .center { text-align: center; }
</style>
</head>
<body>

<table>
    <thead>
        <tr>
            <th style="width: 50px;">ลำดับ</th>
            <th style="width: 130px;">รหัสโครงการ</th>
            <th style="width: 320px;">ชื่อโครงการ_กิจกรรม</th>
            <th style="width: 120px;">ระดับโครงการ</th>
            <th style="width: 250px;">โครงการหลักที่สังกัด</th>
            <th style="width: 160px;">สำนัก_กอง</th>
            <th style="width: 90px;">ปีงบประมาณ</th>
            <th style="width: 130px;">งบประมาณ_บาท</th>
            <th style="width: 130px;">ยอดเบิกจ่าย_บาท</th>
            <th style="width: 130px;">คงเหลือ_บาท</th>
            <th style="width: 110px;">ความคืบหน้า</th>
            <th style="width: 120px;">สถานะ</th>
            <th style="width: 90px;">คะแนนประเมิน</th>
            <th style="width: 80px;">เกรด</th>
            <th style="width: 110px;">วันที่เริ่มต้น</th>
            <th style="width: 110px;">วันที่สิ้นสุด</th>
            <th style="width: 160px;">ผู้รับผิดชอบ</th>
            <th style="width: 250px;">ปัญหาและอุปสรรค</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($projects as $idx => $p): 
            $isMain = empty($p['parent_id']);
            $levelText = $isMain ? 'โครงการหลัก' : 'กิจกรรมหลัก';
            $parentName = $isMain ? '-' : ($p['parent_name'] ?? '-');
            $remaining = (float)$p['budget'] - (float)$p['disbursed_amount'];
            $statusLabel = \App\Enums\ProjectStatus::labelFor($p['status']);
        ?>
        <tr>
            <td class="center txt"><?= $idx + 1 ?></td>
            <td class="center txt"><?= htmlspecialchars($p['project_code'] ?? '-') ?></td>
            <td class="txt"><?= htmlspecialchars($p['name']) ?></td>
            <td class="center txt"><?= $levelText ?></td>
            <td class="txt"><?= htmlspecialchars($parentName) ?></td>
            <td class="txt"><?= htmlspecialchars($p['department_name'] ?? '-') ?></td>
            <td class="center txt"><?= htmlspecialchars($p['fiscal_year'] ?? '-') ?></td>
            <td class="num"><?= number_format((float)$p['budget'], 2, '.', '') ?></td>
            <td class="num"><?= number_format((float)$p['disbursed_amount'], 2, '.', '') ?></td>
            <td class="num"><?= number_format((float)$remaining, 2, '.', '') ?></td>
            <td class="pct"><?= number_format(((float)$p['progress']) / 100, 3, '.', '') ?></td>
            <td class="center txt"><?= htmlspecialchars($statusLabel) ?></td>
            <td class="center num"><?= $p['evaluation_score'] !== null ? number_format((float)$p['evaluation_score'], 1, '.', '') : '-' ?></td>
            <td class="center txt"><?= htmlspecialchars($p['evaluation_grade'] ?? '-') ?></td>
            <td class="date"><?= htmlspecialchars($p['start_date'] ?? '-') ?></td>
            <td class="date"><?= htmlspecialchars($p['end_date'] ?? '-') ?></td>
            <td class="txt"><?= htmlspecialchars($p['responsible_name'] ?? $p['responsible_person'] ?? '-') ?></td>
            <td class="txt"><?= htmlspecialchars($p['problem_description'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
        <?php
        exit;
    }

    /**
     * Export projects to official PDF report view with automatic print / PDF save dialog.
     */
    public function exportPdf(): void
    {
        $projects = $this->getFilteredProjects();
        $fiscalYearId = $_GET['fiscal_year_id'] ?? '';
        $departmentId = $_GET['department_id'] ?? '';
        $status       = $_GET['status'] ?? '';
        $search       = trim($_GET['search'] ?? '');

        $totalBudget = array_sum(array_column($projects, 'budget'));
        $totalDisbursed = array_sum(array_column($projects, 'disbursed_amount'));

        View::render('reports.pdf', [
            'projects'       => $projects,
            'totalBudget'    => $totalBudget,
            'totalDisbursed' => $totalDisbursed,
            'fiscalYearId'   => $fiscalYearId,
            'departmentId'   => $departmentId,
            'status'         => $status,
            'search'         => $search,
        ]);
    }

    public function exportCsv(): void
    {
        $projects = $this->getFilteredProjects();
        $filename = "municipal_projects_report_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        // Add UTF-8 BOM for Excel support with Thai characters
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header row
        fputcsv($output, [
            'รหัสโครงการ',
            'ชื่อโครงการ',
            'ระดับโครงการ',
            'ปีงบประมาณ',
            'หน่วยงาน/สำนัก/กอง',
            'งบประมาณ (บาท)',
            'ยอดเบิกจ่าย (บาท)',
            'คงเหลือ (บาท)',
            'ความคืบหน้า (%)',
            'สถานะ',
            'คะแนนประเมิน',
            'เกรดประเมิน',
            'วันที่เริ่ม',
            'วันที่สิ้นสุด',
            'ผู้รับผิดชอบ',
            'ปัญหาและอุปสรรค'
        ]);

        foreach ($projects as $r) {
            $isMain = empty($r['parent_id']);
            $level = $isMain ? 'โครงการหลัก' : 'กิจกรรมหลัก';
            $remaining = (float)$r['budget'] - (float)$r['disbursed_amount'];
            $statusLabel = \App\Enums\ProjectStatus::labelFor($r['status']);

            fputcsv($output, [
                $r['project_code'] ?? '',
                $r['name'],
                $level,
                $r['fiscal_year'] ?? '',
                $r['department_name'] ?? '',
                number_format((float)$r['budget'], 2, '.', ''),
                number_format((float)$r['disbursed_amount'], 2, '.', ''),
                number_format((float)$remaining, 2, '.', ''),
                number_format((float)$r['progress'], 1, '.', ''),
                $statusLabel,
                $r['evaluation_score'] !== null ? number_format((float)$r['evaluation_score'], 1, '.', '') : '-',
                $r['evaluation_grade'] ?? '-',
                $r['start_date'] ?? '',
                $r['end_date'] ?? '',
                $r['responsible_name'] ?? $r['responsible_person'] ?? '',
                $r['problem_description'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }

    public function printReport(): void
    {
        $projects = $this->getFilteredProjects();
        $fiscalYearId = $_GET['fiscal_year_id'] ?? '';
        $departmentId = $_GET['department_id'] ?? '';
        $status       = $_GET['status'] ?? '';
        $search       = trim($_GET['search'] ?? '');

        $totalBudget = array_sum(array_column($projects, 'budget'));
        $totalDisbursed = array_sum(array_column($projects, 'disbursed_amount'));

        View::render('reports.print', [
            'projects'       => $projects,
            'totalBudget'    => $totalBudget,
            'totalDisbursed' => $totalDisbursed,
            'fiscalYearId'   => $fiscalYearId,
            'departmentId'   => $departmentId,
            'status'         => $status,
            'search'         => $search,
        ]);
    }
}
