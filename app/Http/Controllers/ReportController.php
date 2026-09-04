<?php

namespace App\Http\Controllers;

use App\Core\Database;
use App\Core\View;

class ReportController
{
    public function index(): void
    {
        $fiscalYearId = $_GET['fiscal_year_id'] ?? '';
        $departmentId = $_GET['department_id'] ?? '';
        $status       = $_GET['status'] ?? '';

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
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY p.parent_id IS NULL DESC, p.id DESC";

        $projects = Database::query($sql, $params);
        $fiscalYears = Database::query("SELECT * FROM fiscal_years ORDER BY year DESC");
        $departments = Database::query("SELECT * FROM departments ORDER BY id ASC");

        View::render('reports.index', [
            'projects'     => $projects,
            'fiscalYears'  => $fiscalYears,
            'departments'  => $departments,
            'fiscalYearId' => $fiscalYearId,
            'departmentId' => $departmentId,
            'status'       => $status,
        ]);
    }

    public function exportCsv(): void
    {
        $fiscalYearId = $_GET['fiscal_year_id'] ?? '';
        $departmentId = $_GET['department_id'] ?? '';
        $status       = $_GET['status'] ?? '';

        $sql = "SELECT p.project_code, p.name, 
                       CASE WHEN p.parent_id IS NULL THEN 'โครงการหลัก' ELSE 'โครงการย่อย' END as project_level,
                       f.year as fiscal_year,
                       d.name as department_name,
                       p.budget, p.disbursed_amount, (p.budget - p.disbursed_amount) as remaining,
                       p.progress, p.status, p.start_date, p.end_date,
                       u.name as responsible_name,
                       p.problem_description
                FROM projects p
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
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY p.parent_id IS NULL DESC, p.id DESC";
        $rows = Database::query($sql, $params);

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
            'วันที่เริ่ม',
            'วันที่สิ้นสุด',
            'ผู้รับผิดชอบ',
            'ปัญหาและอุปสรรค'
        ]);

        foreach ($rows as $r) {
            fputcsv($output, [
                $r['project_code'],
                $r['name'],
                $r['project_level'],
                $r['fiscal_year'],
                $r['department_name'],
                number_format($r['budget'], 2, '.', ''),
                number_format($r['disbursed_amount'], 2, '.', ''),
                number_format($r['remaining'], 2, '.', ''),
                $r['progress'],
                $r['status'],
                $r['start_date'],
                $r['end_date'],
                $r['responsible_name'],
                $r['problem_description'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }

    public function printReport(): void
    {
        $fiscalYearId = $_GET['fiscal_year_id'] ?? '';
        $departmentId = $_GET['department_id'] ?? '';
        $status       = $_GET['status'] ?? '';

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
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY p.parent_id IS NULL DESC, p.id DESC";

        $projects = Database::query($sql, $params);
        $totalBudget = array_sum(array_column($projects, 'budget'));
        $totalDisbursed = array_sum(array_column($projects, 'disbursed_amount'));

        View::render('reports.print', [
            'projects'       => $projects,
            'totalBudget'    => $totalBudget,
            'totalDisbursed' => $totalDisbursed,
            'fiscalYearId'   => $fiscalYearId,
            'departmentId'   => $departmentId,
            'status'         => $status,
        ]);
    }
}
