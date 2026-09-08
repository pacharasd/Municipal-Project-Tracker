<?php

namespace App\Services;

use App\Core\Database;
use Exception;

class FiscalYearService
{
    /**
     * ดึงข้อมูลปีงบประมาณทั้งหมด พร้อมสร้างปีงบประมาณล่วงหน้าให้อัตโนมัติอย่างต่อเนื่อง
     */
    public static function getAll(int $futureYearsAhead = 6): array
    {
        self::ensureFiscalYears($futureYearsAhead);
        return Database::query("SELECT * FROM fiscal_years ORDER BY year DESC");
    }

    /**
     * ดึงข้อมูลปีงบประมาณสำหรับตัวกรอง (Dashboard/Reports)
     * กรองเฉพาะปีที่ <= ปีปัจจุบันที่ active (หรือปีปัจจุบันของราชการ) หรือปีที่มีโครงการอยู่จริง
     * เพื่อไม่ให้ปีล่วงหน้าที่ยังไม่มีข้อมูลมาแสดงให้รกตา
     */
    public static function getFilterableYears(): array
    {
        $activeFy = self::getActiveYear();
        $currentYear = $activeFy ? (int)$activeFy['year'] : self::getCurrentFiscalYear();

        $sql = "SELECT * FROM fiscal_years 
                WHERE year <= ? 
                   OR id IN (SELECT DISTINCT fiscal_year_id FROM projects WHERE fiscal_year_id IS NOT NULL)
                ORDER BY year DESC";

        return Database::query($sql, [$currentYear]);
    }

    /**
     * คำนวณปีงบประมาณปัจจุบันตามปฏิทินงบประมาณราชการไทย (พ.ศ.)
     * เริ่ม 1 ตุลาคม ของปีก่อนหน้า สิ้นสุด 30 กันยายน
     */
    public static function getCurrentFiscalYear(): int
    {
        $currentCE = (int)date('Y');
        $currentMonth = (int)date('n');
        return ($currentMonth >= 10 ? $currentCE + 1 : $currentCE) + 543;
    }

    /**
     * ตรวจสอบและสร้างปีงบประมาณให้อัตโนมัติถึงปีปัจจุบันและปีต่อๆ ไปเรื่อยๆ
     */
    public static function ensureFiscalYears(int $futureYearsAhead = 6): void
    {
        try {
            $currentFiscalYear = self::getCurrentFiscalYear();
            $targetMaxYear = $currentFiscalYear + $futureYearsAhead;

            $latestInDb = (int)Database::fetchColumn("SELECT MAX(year) FROM fiscal_years");
            if ($latestInDb < 2567) {
                $latestInDb = 2567;
            }

            if ($latestInDb < $targetMaxYear) {
                for ($y = $latestInDb + 1; $y <= $targetMaxYear; $y++) {
                    $ceYear = $y - 543;
                    $start = ($ceYear - 1) . "-10-01";
                    $end = $ceYear . "-09-30";
                    Database::execute(
                        "INSERT IGNORE INTO fiscal_years (year, is_active, start_date, end_date) VALUES (?, 0, ?, ?)",
                        [$y, $start, $end]
                    );
                }
            }

            // ซิงค์ is_active เริ่มต้นหากยังไม่มีปีใดเป็น active เลย
            $hasActive = (int)Database::fetchColumn("SELECT COUNT(*) FROM fiscal_years WHERE is_active = 1");
            if ($hasActive === 0) {
                Database::execute("UPDATE fiscal_years SET is_active = IF(year = ?, 1, 0)", [$currentFiscalYear]);
            }
        } catch (Exception $e) {
            error_log("FiscalYearService ensureFiscalYears error: " . $e->getMessage());
        }
    }

    /**
     * ดึงข้อมูลปีงบประมาณที่เป็นปีปัจจุบัน (is_active = 1)
     */
    public static function getActiveYear(): ?array
    {
        $year = Database::fetch("SELECT * FROM fiscal_years WHERE is_active = 1 LIMIT 1");
        if (!$year) {
            $currentYear = self::getCurrentFiscalYear();
            $year = Database::fetch("SELECT * FROM fiscal_years WHERE year = ? LIMIT 1", [$currentYear]);
        }
        if (!$year) {
            $year = Database::fetch("SELECT * FROM fiscal_years ORDER BY year DESC LIMIT 1");
        }
        return $year;
    }

    /**
     * กำหนดให้ปีงบประมาณที่ระบุเป็นปีปัจจุบัน (is_active = 1)
     */
    public static function setActiveYear(int $id): bool
    {
        return Database::transaction(function () use ($id) {
            Database::execute("UPDATE fiscal_years SET is_active = 0");
            Database::execute("UPDATE fiscal_years SET is_active = 1 WHERE id = ?", [$id]);
            return true;
        });
    }

    /**
     * ดึงข้อมูลปีงบประมาณทั้งหมดพร้อมสถิติจำนวนโครงการและงบประมาณ
     */
    public static function getYearsWithStats(): array
    {
        self::ensureFiscalYears();
        $sql = "SELECT fy.*,
                       COUNT(p.id) as project_count,
                       COALESCE(SUM(p.budget), 0) as total_budget,
                       COALESCE(SUM(p.disbursed_amount), 0) as total_disbursed,
                       (SELECT COUNT(*) FROM projects s 
                        WHERE s.parent_id IS NOT NULL 
                          AND s.parent_id IN (SELECT m.id FROM projects m WHERE m.fiscal_year_id = fy.id AND m.parent_id IS NULL)
                       ) as sub_project_count
                FROM fiscal_years fy
                LEFT JOIN projects p ON fy.id = p.fiscal_year_id AND p.parent_id IS NULL
                GROUP BY fy.id, fy.year, fy.is_active, fy.start_date, fy.end_date, fy.created_at, fy.updated_at
                ORDER BY fy.year DESC";
        return Database::query($sql);
    }

    /**
     * สร้างปีงบประมาณใหม่
     */
    public static function createYear(int $year, ?string $startDate = null, ?string $endDate = null): int
    {
        $ceYear = $year - 543;
        $start = $startDate ?: (($ceYear - 1) . "-10-01");
        $end = $endDate ?: ($ceYear . "-09-30");

        return Database::insert('fiscal_years', [
            'year'       => $year,
            'is_active'  => 0,
            'start_date' => $start,
            'end_date'   => $end,
        ]);
    }

    /**
     * อัปเดตช่วงเวลาของปีงบประมาณ
     */
    public static function updateYear(int $id, string $startDate, string $endDate): bool
    {
        Database::update('fiscal_years', [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ], "id = ?", [$id]);
        return true;
    }
}
