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
     * Format currency compactly when reaching 1 million (1,000,000+)
     * Returns structured array for rich UI rendering:
     * - 'value': Compact value string e.g. '40.70'
     * - 'unit': Unit label e.g. 'ล้านบาท'
     * - 'full': Exact full number e.g. '40,700,000.00'
     * - 'is_compact': Boolean flag if compacted
     * - 'formatted': Full display string e.g. '40.70 ล้านบาท'
     */
    public static function compactMoney(float|int|string|null $amount, string $suffixType = 'th'): array
    {
        $amt = (float)($amount ?? 0);
        $abs = abs($amt);

        if ($abs >= 1_000_000_000) {
            $val = number_format($amt / 1_000_000_000, 2);
            $unit = ($suffixType === 'en' || $suffixType === 'M') ? 'B บาท' : 'พันล้านบาท';
            return [
                'value'      => $val,
                'unit'       => $unit,
                'full'       => number_format($amt, 2),
                'is_compact' => true,
                'formatted'  => "{$val} {$unit}"
            ];
        }

        if ($abs >= 1_000_000) {
            $val = number_format($amt / 1_000_000, 2);
            $unit = ($suffixType === 'en' || $suffixType === 'M') ? 'M บาท' : 'ล้านบาท';
            return [
                'value'      => $val,
                'unit'       => $unit,
                'full'       => number_format($amt, 2),
                'is_compact' => true,
                'formatted'  => "{$val} {$unit}"
            ];
        }

        return [
            'value'      => number_format($amt, 2),
            'unit'       => 'บาท',
            'full'       => number_format($amt, 2),
            'is_compact' => false,
            'formatted'  => number_format($amt, 2) . ' บาท'
        ];
    }

    /**
     * Format currency compactly in Thai when reaching 1 million (1,000,000+)
     * e.g. 1,000,000 -> "1 ล้าน", 40,700,000 -> "40.7 ล้าน"
     * Returns:
     * - 'short': Large display string e.g. '1 ล้าน' or '40.7 ล้าน'
     * - 'full': Small text e.g. '40,700,000.00 บาท' or 'บาท' (if < 1M)
     * - 'is_million': Boolean flag if amount >= 1,000,000
     */
    public static function formatMillionCompact(float|int|string|null $amount, int $decimals = 2, bool $trimZeros = true): array
    {
        $amt = (float)($amount ?? 0);
        $abs = abs($amt);

        if ($abs >= 1_000_000) {
            $m = $amt / 1_000_000;
            $mFormatted = number_format($m, $decimals);
            if ($trimZeros) {
                $mFormatted = rtrim(rtrim($mFormatted, '0'), '.');
            }
            return [
                'short'      => "{$mFormatted} ล้าน",
                'full'       => number_format($amt, 2) . ' บาท',
                'is_million' => true,
            ];
        }

        return [
            'short'      => number_format($amt, 2),
            'full'       => 'บาท',
            'is_million' => false,
        ];
    }

    /**
     * Render standard dual-line money HTML:
     * - If >= 1,000,000: Displays "40.7 ล้าน" on top, and "40,700,000.00 บาท" underneath in small text.
     * - If < 1,000,000: Displays "850,000.00" on top, and "บาท" underneath.
     *
     * @param float|int|string|null $amount
     * @param string $size 'card' | 'table' | 'inline'
     * @param string $align 'left' | 'center' | 'right'
     * @param string $customColor Optional text color class for top value
     */
    public static function moneyDisplay(
        float|int|string|null $amount,
        string $size = 'table',
        string $align = 'right',
        string $customColor = ''
    ): string {
        $data = self::formatMillionCompact($amount);
        $alignClass = match($align) {
            'center' => 'text-center items-center',
            'left'   => 'text-left items-start',
            default  => 'text-right items-end',
        };

        if ($size === 'card') {
            $topColor = $customColor ?: 'text-slate-900 dark:text-white';
            return '<div class="flex flex-col ' . $alignClass . '">' .
                '<div class="text-base sm:text-xl lg:text-2xl font-black font-heading tracking-tight leading-tight truncate ' . $topColor . '">' . $data['short'] . '</div>' .
                '<div class="text-[10px] sm:text-[10.5px] text-slate-400 dark:text-slate-500 mt-0.5 font-mono truncate" title="จำนวนเงินเต็ม">' . $data['full'] . '</div>' .
                '</div>';
        }

        if ($size === 'inline') {
            return '<span class="font-semibold">' . $data['short'] . '</span> <span class="text-[10px] sm:text-[10.5px] text-slate-400 dark:text-slate-500 font-mono">(' . $data['full'] . ')</span>';
        }

        // Default: 'table' or row
        $topColor = $customColor ?: 'text-slate-800 dark:text-slate-100';
        return '<div class="flex flex-col ' . $alignClass . ' leading-tight">' .
            '<span class="font-bold font-mono text-xs sm:text-sm ' . $topColor . '">' . $data['short'] . '</span>' .
            '<span class="text-[10px] sm:text-[10.5px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">' . $data['full'] . '</span>' .
            '</div>';
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
