<?php

namespace App\Enums;

enum ActivityStatus: string
{
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED   = 'completed';
    case HAS_PROBLEM = 'has_problem';
    case CANCELLED   = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::NOT_STARTED => 'ยังไม่เริ่มดำเนินการ',
            self::IN_PROGRESS => 'กำลังดำเนินการ',
            self::COMPLETED   => 'เสร็จสิ้นสมบูรณ์',
            self::HAS_PROBLEM => 'มีปัญหา / อุปสรรค',
            self::CANCELLED   => 'ยกเลิกกิจกรรม',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::NOT_STARTED => 'รอดำเนินการตามแผนงาน',
            self::IN_PROGRESS => 'อยู่ระหว่างการจัดกิจกรรม',
            self::COMPLETED   => 'ดำเนินกิจกรรมครบถ้วนตามแผน',
            self::HAS_PROBLEM => 'พบอุปสรรคหรือปัญหาในการดำเนินงาน',
            self::CANCELLED   => 'ระงับการจัดกิจกรรม',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::NOT_STARTED => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/40 whitespace-nowrap',
            self::IN_PROGRESS => 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800/40 whitespace-nowrap',
            self::COMPLETED   => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/40 whitespace-nowrap',
            self::HAS_PROBLEM => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/40 whitespace-nowrap',
            self::CANCELLED   => 'bg-slate-100 dark:bg-slate-900/40 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-800/40 whitespace-nowrap',
        };
    }

    public function dotClass(): string
    {
        return match($this) {
            self::NOT_STARTED => 'bg-indigo-500',
            self::IN_PROGRESS => 'bg-sky-500',
            self::COMPLETED   => 'bg-emerald-500',
            self::HAS_PROBLEM => 'bg-rose-500',
            self::CANCELLED   => 'bg-slate-400',
        };
    }

    public function colorHex(): string
    {
        return match($this) {
            self::NOT_STARTED => '#6366f1',
            self::IN_PROGRESS => '#0ea5e9',
            self::COMPLETED   => '#10b981',
            self::HAS_PROBLEM => '#f43f5e',
            self::CANCELLED   => '#64748b',
        };
    }

    public static function labelFor(?string $status): string
    {
        if (empty($status)) {
            return self::NOT_STARTED->label();
        }

        // Support Thai aliases if previously saved
        if ($status === 'ยังไม่เริ่ม' || $status === 'ยังไม่เริ่มดำเนินการ' || $status === 'not_started') {
            return self::NOT_STARTED->label();
        }
        if ($status === 'กำลังดำเนินการ' || $status === 'in_progress') {
            return self::IN_PROGRESS->label();
        }
        if ($status === 'เสร็จสิ้น' || $status === 'เสร็จสิ้นสมบูรณ์' || $status === 'completed') {
            return self::COMPLETED->label();
        }
        if ($status === 'มีปัญหา' || $status === 'มีปัญหา / อุปสรรค' || $status === 'has_problem') {
            return self::HAS_PROBLEM->label();
        }
        if ($status === 'ยกเลิก' || $status === 'ยกเลิกกิจกรรม' || $status === 'cancelled') {
            return self::CANCELLED->label();
        }

        $case = self::tryFrom($status);
        return $case ? $case->label() : $status;
    }

    public static function badgeClassesFor(?string $status): string
    {
        $case = self::resolveCase($status);
        return $case ? $case->badgeClasses() : 'bg-slate-100 text-slate-700 border-slate-300 whitespace-nowrap';
    }

    public static function dotClassFor(?string $status): string
    {
        $case = self::resolveCase($status);
        return $case ? $case->dotClass() : 'bg-slate-400';
    }

    public static function resolveCase(?string $status): ?self
    {
        if (empty($status)) {
            return self::NOT_STARTED;
        }
        if ($status === 'ยังไม่เริ่ม' || $status === 'ยังไม่เริ่มดำเนินการ' || $status === 'not_started') {
            return self::NOT_STARTED;
        }
        if ($status === 'กำลังดำเนินการ' || $status === 'in_progress') {
            return self::IN_PROGRESS;
        }
        if ($status === 'เสร็จสิ้น' || $status === 'เสร็จสิ้นสมบูรณ์' || $status === 'completed') {
            return self::COMPLETED;
        }
        if ($status === 'มีปัญหา' || $status === 'มีปัญหา / อุปสรรค' || $status === 'has_problem') {
            return self::HAS_PROBLEM;
        }
        if ($status === 'ยกเลิก' || $status === 'ยกเลิกกิจกรรม' || $status === 'cancelled') {
            return self::CANCELLED;
        }

        return self::tryFrom($status);
    }

    /**
     * Return all 5 statuses as associative array for Blade loops and Alpine.js JSON
     */
    public static function all(): array
    {
        return array_map(fn(self $case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'desc'  => $case->description(),
            'dot'   => $case->dotClass(),
            'badge' => $case->badgeClasses(),
            'color' => $case->colorHex(),
        ], self::cases());
    }
}
