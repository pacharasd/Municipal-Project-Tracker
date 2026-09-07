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

            // ซิงค์ is_active ให้เป็นปีงบประมาณปัจจุบัน
            Database::execute("UPDATE fiscal_years SET is_active = IF(year = ?, 1, 0)", [$currentFiscalYear]);
        } catch (Exception $e) {
            error_log("FiscalYearService ensureFiscalYears error: " . $e->getMessage());
        }
    }
}
