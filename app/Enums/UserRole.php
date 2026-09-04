<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case EXECUTIVE = 'executive';
    case OFFICER = 'officer';
    case PROJECT_MANAGER = 'project_manager';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'ผู้ดูแลระบบ (Administrator)',
            self::EXECUTIVE => 'ผู้บริหาร (Executive)',
            self::OFFICER => 'เจ้าหน้าที่ (Officer)',
            self::PROJECT_MANAGER => 'ผู้ดูแลโครงการ (Project Manager)',
        };
    }
}
