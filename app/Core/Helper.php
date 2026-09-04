<?php

declare(strict_types=1);

namespace App\Core;

class Helper
{
    /**
     * Format currency amount in Thai Baht
     */
    public static function money(float|int|string|null $amount): string
    {
        return number_format((float)($amount ?? 0), 2);
    }

    /**
     * Format percentage
     */
    public static function percent(float|int|string|null $percent, int $decimals = 1): string
    {
        return number_format((float)($percent ?? 0), $decimals) . '%';
    }

    /**
     * Format date into Thai Buddhist calendar
     * Example: 2026-09-04 -> 4 ก.ย. 2569 (or full month)
     */
    public static function thaiDate(?string $dateStr, bool $short = true, bool $showTime = false): string
    {
        if (empty($dateStr)) {
            return '-';
        }

        $time = strtotime($dateStr);
        if (!$time) {
            return $dateStr;
        }

        $shortMonths = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
            5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
            9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];

        $fullMonths = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];

        $d = (int)date('j', $time);
        $m = (int)date('n', $time);
        $y = (int)date('Y', $time) + 543;

        $monthName = $short ? ($shortMonths[$m] ?? '') : ($fullMonths[$m] ?? '');
        $res = "{$d} {$monthName} {$y}";

        if ($showTime) {
            $res .= ' ' . date('H:i', $time) . ' น.';
        }

        return $res;
    }

    /**
     * Calculate disbursement percentage safely
     */
    public static function disbursementRate(float|int $budget, float|int $disbursed): float
    {
        if ($budget <= 0) {
            return 0.0;
        }
        return round(($disbursed / $budget) * 100, 2);
    }
}
