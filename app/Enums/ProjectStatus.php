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
            self::NOT_STARTED => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800/40 whitespace-nowrap',
            self::IN_PROGRESS => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800/40 whitespace-nowrap',
            self::COMPLETED => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/40 whitespace-nowrap',
            self::HAS_PROBLEM => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/40 whitespace-nowrap',
            self::CANCELLED => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-900/40 dark:text-slate-300 dark:border-slate-800/40 whitespace-nowrap',
        };
    }

    public function colorHex(): string
    {
        return match($this) {
            self::NOT_STARTED => '#6366f1',
            self::IN_PROGRESS => '#0ea5e9',
            self::COMPLETED => '#10b981',
            self::HAS_PROBLEM => '#f43f5e',
            self::CANCELLED => '#64748b',
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
