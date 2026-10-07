<?php

declare(strict_types=1);

namespace App\Core {
    class Database
    {
        private static array $projects = [
            ['id' => 1, 'parent_id' => null, 'fiscal_year_id' => 1, 'evaluation_score' => '90.00'],
            ['id' => 2, 'parent_id' => null, 'fiscal_year_id' => 1, 'evaluation_score' => null],
            ['id' => 3, 'parent_id' => null, 'fiscal_year_id' => 2, 'evaluation_score' => '60.00'],
            ['id' => 4, 'parent_id' => 1, 'fiscal_year_id' => 1, 'evaluation_score' => '100.00'],
        ];

        public static function fetch(string $sql, array $params = []): ?array
        {
            if (str_contains($sql, 'as main_total')) {
                return ['main_total' => count(self::mainProjects($params[0] ?? null))];
            }
            if (str_contains($sql, 'as sub_total')) {
                return ['sub_total' => 1];
            }
            throw new \RuntimeException('Unexpected dashboard fetch query');
        }

        public static function query(string $sql, array $params = []): array
        {
            if (str_contains($sql, 'COUNT(s.id) as sub_project_count')) {
                if (!str_contains($sql, 'WHERE p.parent_id IS NULL')) {
                    throw new \RuntimeException('Grade query must select main projects only');
                }
                return self::mainProjects($params[0] ?? null);
            }
            return [];
        }

        private static function mainProjects(?int $fiscalYearId): array
        {
            return array_values(array_filter(
                self::$projects,
                static fn(array $project) => $project['parent_id'] === null
                    && ($fiscalYearId === null || $project['fiscal_year_id'] === $fiscalYearId)
            ));
        }
    }
}

namespace {
    require_once __DIR__ . '/../app/Services/ProjectService.php';

    use App\Services\ProjectService;

    $cases = [
        [null, 3, ['A+' => 1, 'A' => 0, 'B' => 0, 'C' => 1, 'D' => 0, 'ungraded' => 1]],
        [1, 2, ['A+' => 1, 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'ungraded' => 1]],
        [2, 1, ['A+' => 0, 'A' => 0, 'B' => 0, 'C' => 1, 'D' => 0, 'ungraded' => 0]],
        [99, 0, ['A+' => 0, 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'ungraded' => 0]],
    ];

    foreach ($cases as [$yearId, $total, $expected]) {
        $stats = ProjectService::getDashboardStats($yearId);
        if ($stats['main_total'] !== $total
            || $stats['grade_distribution'] !== $expected
            || array_sum($stats['grade_distribution']) !== $stats['main_total']) {
            throw new \RuntimeException('Dashboard grade stats mismatch for fiscal year ' . ($yearId ?? 'all'));
        }
    }

    echo "Dashboard grade stats tests passed\n";
}
