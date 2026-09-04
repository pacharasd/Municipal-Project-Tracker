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
            self::NOT_STARTED => 'ยังไม่เริ่มดำเนินการ',
            self::IN_PROGRESS => 'กำลังดำเนินการ',
            self::COMPLETED => 'เสร็จสิ้น',
            self::HAS_PROBLEM => 'มีปัญหา',
            self::CANCELLED => 'ยกเลิก',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::NOT_STARTED => 'bg-slate-100 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-300',
            self::IN_PROGRESS => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300',
            self::COMPLETED => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300',
            self::HAS_PROBLEM => 'bg-rose-50 text-rose-700 border-rose-200 animate-pulse dark:bg-rose-900/30 dark:text-rose-300',
            self::CANCELLED => 'bg-gray-100 text-gray-500 border-gray-300 dark:bg-gray-800 dark:text-gray-400',
        };
    }

    public function colorHex(): string
    {
        return match($this) {
            self::NOT_STARTED => '#94a3b8',
            self::IN_PROGRESS => '#3b82f6',
            self::COMPLETED => '#10b981',
            self::HAS_PROBLEM => '#ef4444',
            self::CANCELLED => '#6b7280',
        };
    }

    public static function badgeClassesFor(?string $status): string
    {
        $case = self::tryFrom($status ?? '');
        return $case ? $case->badgeClasses() : 'bg-slate-100 text-slate-700 border-slate-300';
    }

    public static function badgeClass(?string $status): string
    {
        return self::badgeClassesFor($status);
    }

    public static function labelFor(?string $status): string
    {
        $case = self::tryFrom($status ?? '');
        return $case ? $case->label() : ($status ?? '-');
    }
}
