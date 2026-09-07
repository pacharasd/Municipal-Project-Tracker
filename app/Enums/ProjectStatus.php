<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case HAS_PROBLEM = 'has_problem';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::NOT_STARTED => 'ยังไม่เริ่ม',
            self::IN_PROGRESS => 'กำลังดำเนินการ',
            self::COMPLETED => 'เสร็จสิ้น',
            self::HAS_PROBLEM => 'มีปัญหา',
            self::CANCELLED => 'ยกเลิก',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::NOT_STARTED => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/40 whitespace-nowrap',
            self::IN_PROGRESS => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800/40 whitespace-nowrap',
            self::COMPLETED => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800/40 whitespace-nowrap',
            self::HAS_PROBLEM => 'bg-rose-50 text-rose-700 border-rose-200 animate-pulse dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-800/40 whitespace-nowrap',
            self::CANCELLED => 'bg-gray-100 text-gray-500 border-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 whitespace-nowrap',
        };
    }

    public function colorHex(): string
    {
        return match($this) {
            self::NOT_STARTED => '#f59e0b',
            self::IN_PROGRESS => '#3b82f6',
            self::COMPLETED => '#10b981',
            self::HAS_PROBLEM => '#ef4444',
            self::CANCELLED => '#6b7280',
        };
    }

    public static function badgeClassesFor(?string $status): string
    {
        if ($status === 'ยังไม่เริ่ม' || $status === 'ยังไม่เริ่มดำเนินการ') {
            return self::NOT_STARTED->badgeClasses();
        }
        if ($status === 'กำลังดำเนินการ') {
            return self::IN_PROGRESS->badgeClasses();
        }
        if ($status === 'เสร็จสิ้น') {
            return self::COMPLETED->badgeClasses();
        }
        if ($status === 'มีปัญหา') {
            return self::HAS_PROBLEM->badgeClasses();
        }
        if ($status === 'ยกเลิก') {
            return self::CANCELLED->badgeClasses();
        }

        $case = self::tryFrom($status ?? '');
        return $case ? $case->badgeClasses() : 'bg-slate-100 text-slate-700 border-slate-300 whitespace-nowrap';
    }

    public static function badgeClass(?string $status): string
    {
        return self::badgeClassesFor($status);
    }

    public static function labelFor(?string $status): string
    {
        if ($status === 'ยังไม่เริ่มดำเนินการ' || $status === 'not_started') {
            return 'ยังไม่เริ่ม';
        }
        $case = self::tryFrom($status ?? '');
        return $case ? $case->label() : ($status ?? '-');
    }
}
