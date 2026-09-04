<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case EXECUTIVE = 'executive';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'ผู้ดูแลระบบ (Administrator)',
            self::EXECUTIVE => 'ผู้บริหาร (Executive)',
        };
    }
}
