<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Core\Router;
use App\Core\Session;
use App\Services\FiscalYearService;
use App\Services\AuditLogService;
use Exception;
use Throwable;

class FiscalYearController
{
    /**
     * แสดงหน้ารายการปีงบประมาณและสถิติภาพรวม
     */
    public function index(): void
    {
        $years = FiscalYearService::getYearsWithStats();
        $activeYear = FiscalYearService::getActiveYear();

        // Calculate summary metrics
        $totalYears = count($years);
        $totalProjectsAll = 0;
        $totalSubProjectsAll = 0;
        $grandTotalBudget = 0.0;
        $grandTotalDisbursed = 0.0;

        foreach ($years as $y) {
            $totalProjectsAll += (int)($y['project_count'] ?? 0);
            $totalSubProjectsAll += (int)($y['sub_project_count'] ?? 0);
            $grandTotalBudget += (float)($y['total_budget'] ?? 0);
            $grandTotalDisbursed += (float)($y['total_disbursed'] ?? 0);
        }

        $metrics = [
            'active_year'            => $activeYear,
            'total_years'            => $totalYears,
            'total_projects'         => $totalProjectsAll,
            'total_sub_projects'     => $totalSubProjectsAll,
            'grand_total_budget'     => $grandTotalBudget,
            'grand_total_disbursed'  => $grandTotalDisbursed,
            'overall_disbursed_pct'  => $grandTotalBudget > 0 ? round(($grandTotalDisbursed / $grandTotalBudget) * 100, 2) : 0.0,
        ];

        View::render('fiscal_years.index', [
            'years'      => $years,
            'activeYear' => $activeYear,
            'metrics'    => $metrics,
        ]);
    }

    /**
     * ตั้งค่าปีงบประมาณที่เลือกเป็นปีปัจจุบัน (is_active = 1)
     */
    public function setActive(string $id): void
    {
        if (!Auth::isAdmin()) {
            Session::flash('error', 'เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถเปลี่ยนปีงบประมาณปัจจุบันได้');
            header('Location: ' . Router::url('/fiscal-years'));
            exit;
        }

        $fyId = (int)$id;
        $fy = Database::fetch("SELECT * FROM fiscal_years WHERE id = ?", [$fyId]);
        if (!$fy) {
            Session::flash('error', 'ไม่พบปีงบประมาณที่ระบุ');
            header('Location: ' . Router::url('/fiscal-years'));
            exit;
        }

        try {
            $oldActive = FiscalYearService::getActiveYear();
            FiscalYearService::setActiveYear($fyId);

            AuditLogService::log(
                'SET_ACTIVE_FISCAL_YEAR',
                'FiscalYear',
                $fyId,
                ['active_year' => $oldActive['year'] ?? null, 'is_active' => 0],
                ['active_year' => $fy['year'], 'is_active' => 1]
            );

            Session::flash('success', "ตั้งปีงบประมาณ {$fy['year']} เป็นปีปัจจุบันเรียบร้อยแล้ว");
        } catch (Throwable $e) {
            Session::flash('error', 'เกิดข้อผิดพลาดในการเปลี่ยนปีปัจจุบัน: ' . $e->getMessage());
        }

        header('Location: ' . Router::url('/fiscal-years'));
        exit;
    }

    /**
     * สร้างปีงบประมาณใหม่
     */
    public function store(): void
    {
        if (!Auth::isAdmin()) {
            Session::flash('error', 'เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถเพิ่มปีงบประมาณได้');
            header('Location: ' . Router::url('/fiscal-years'));
            exit;
        }

        $year = (int)($_POST['year'] ?? 0);
        $startDate = !empty($_POST['start_date']) ? trim($_POST['start_date']) : null;
        $endDate = !empty($_POST['end_date']) ? trim($_POST['end_date']) : null;

        if ($year < 2500 || $year > 2650) {
            Session::flash('error', 'กรุณาระบุปีงบประมาณ พ.ศ. ให้ถูกต้อง (เช่น 2570)');
            header('Location: ' . Router::url('/fiscal-years'));
            exit;
        }

        $exists = Database::fetch("SELECT id FROM fiscal_years WHERE year = ?", [$year]);
        if ($exists) {
            Session::flash('error', "ปีงบประมาณ {$year} มีอยู่ในระบบแล้ว");
            header('Location: ' . Router::url('/fiscal-years'));
            exit;
        }

        try {
            $newId = FiscalYearService::createYear($year, $startDate, $endDate);

            AuditLogService::log(
                'CREATE_FISCAL_YEAR',
                'FiscalYear',
                $newId,
                null,
                ['year' => $year, 'start_date' => $startDate, 'end_date' => $endDate]
            );

            Session::flash('success', "เพิ่มปีงบประมาณ {$year} เข้าสู่ระบบเรียบร้อยแล้ว");
        } catch (Throwable $e) {
            Session::flash('error', 'เกิดข้อผิดพลาดในการสร้างปีงบประมาณ: ' . $e->getMessage());
        }

        header('Location: ' . Router::url('/fiscal-years'));
        exit;
    }

    /**
     * แก้ไขช่วงเวลาของปีงบประมาณ
     */
    public function update(string $id): void
    {
        if (!Auth::isAdmin()) {
            Session::flash('error', 'เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถแก้ไขข้อมูลปีงบประมาณได้');
            header('Location: ' . Router::url('/fiscal-years'));
            exit;
        }

        $fyId = (int)$id;
        $fy = Database::fetch("SELECT * FROM fiscal_years WHERE id = ?", [$fyId]);
        if (!$fy) {
            Session::flash('error', 'ไม่พบปีงบประมาณที่ต้องการแก้ไข');
            header('Location: ' . Router::url('/fiscal-years'));
            exit;
        }

        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');

        if (empty($startDate) || empty($endDate)) {
            Session::flash('error', 'กรุณาระบุวันที่เริ่มต้นและสิ้นสุดของปีงบประมาณ');
            header('Location: ' . Router::url('/fiscal-years'));
            exit;
        }

        if ($startDate >= $endDate) {
            Session::flash('error', 'วันที่เริ่มต้นต้องมาก่อนวันที่สิ้นสุดปีงบประมาณ');
            header('Location: ' . Router::url('/fiscal-years'));
            exit;
        }

        try {
            FiscalYearService::updateYear($fyId, $startDate, $endDate);

            AuditLogService::log(
                'UPDATE_FISCAL_YEAR',
                'FiscalYear',
                $fyId,
                ['start_date' => $fy['start_date'], 'end_date' => $fy['end_date']],
                ['start_date' => $startDate, 'end_date' => $endDate]
            );

            Session::flash('success', "อัปเดตช่วงเวลาปีงบประมาณ {$fy['year']} เรียบร้อยแล้ว");
        } catch (Throwable $e) {
            Session::flash('error', 'เกิดข้อผิดพลาดในการอัปเดต: ' . $e->getMessage());
        }

        header('Location: ' . Router::url('/fiscal-years'));
        exit;
    }
}
