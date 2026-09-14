<?php

namespace App\Services;

use App\Core\Database;

class DepartmentService
{
    private static ?array $cachedDepartments = null;

    public static function getAll(): array
    {
        if (self::$cachedDepartments !== null) {
            return self::$cachedDepartments;
        }

        self::$cachedDepartments = Database::query("SELECT * FROM departments ORDER BY id ASC");
        return self::$cachedDepartments;
    }

    public static function clearCache(): void
    {
        self::$cachedDepartments = null;
    }
}
