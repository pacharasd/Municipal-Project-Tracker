<?php

namespace App\Enums;

enum ProgressMode: string
{
    case AUTO = 'auto';
    case MANUAL = 'manual';

    public function label(): string
    {
        return match($this) {
            self::AUTO => 'อัตโนมัติ (จากกิจกรรม)',
            self::MANUAL => 'กำหนดเอง (Manual)',
        };
    }
}
