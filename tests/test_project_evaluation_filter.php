<?php

declare(strict_types=1);

namespace App\Core {
    final class Database
    {
        public static function query(string $sql): array { return []; }
    }

    final class View
    {
        public static array $data = [];
        public static function render(string $name, array $data): void
        {
            if ($name !== 'projects.index') throw new \RuntimeException('Wrong view');
            self::$data = $data;
        }
    }
}

namespace App\Services {
    final class FiscalYearService
    {
        public static function getAll(): array { return []; }
    }

    final class ProjectService
    {
        public static array $lastFilters = [];
        public static function getMainProjects(array $filters): array
        {
            self::$lastFilters = $filters;
            return [];
        }
    }
}

namespace {
    require_once __DIR__ . '/../app/Http/Controllers/ProjectController.php';

    $controller = new \App\Http\Controllers\ProjectController();
    $_GET = [
        'fiscal_year_id' => '2',
        'department_id' => '3',
        'category_id' => '4',
        'search' => '  ถนน  ',
        'status' => 'completed',
        'evaluation' => 'evaluated',
    ];
    $controller->index();
    if (\App\Core\View::$data['filters']['evaluation'] !== 'evaluated'
        || \App\Core\View::$data['filters']['search'] !== 'ถนน'
        || \App\Services\ProjectService::$lastFilters !== [
            'fiscal_year_id' => '2', 'department_id' => '3', 'category_id' => '4',
        ]) {
        throw new \RuntimeException('Controller did not preserve the shared filter dataset');
    }

    $_GET = ['evaluation' => 'ungraded'];
    $controller->index();
    if (\App\Core\View::$data['filters']['evaluation'] !== 'ungraded') {
        throw new \RuntimeException('Ungraded URL was not accepted');
    }

    $_GET = ['evaluation' => 'unknown'];
    $controller->index();
    if (\App\Core\View::$data['filters']['evaluation'] !== '') {
        throw new \RuntimeException('Unknown evaluation value should mean all');
    }

    $viewSource = file_get_contents(__DIR__ . '/../resources/views/projects/index.blade.php');
    $firstPhpEnd = strpos($viewSource, '?>');
    if ($firstPhpEnd === false) throw new \RuntimeException('View prologue missing');
    $viewPrologue = substr($viewSource, strlen('<?php'), $firstPhpEnd - strlen('<?php'));
    $projects = [
        [
            'id' => 1, 'project_code' => 'MAIN-1', 'name' => 'Zero score',
            'fiscal_year_id' => 1, 'department_id' => 1, 'fiscal_year' => 2570,
            'budget' => '100.00', 'progress' => 0, 'evaluation_score' => '0.00',
            'sub_projects' => [[
                'id' => 3, 'name' => 'Child project', 'status' => 'completed',
                'evaluation_score' => null,
            ]],
        ],
        [
            'id' => 2, 'project_code' => 'MAIN-2', 'name' => 'No score',
            'fiscal_year_id' => 1, 'department_id' => 1, 'fiscal_year' => 2570,
            'budget' => '200.00', 'progress' => 0, 'evaluation_score' => null,
            'sub_projects' => [],
        ],
    ];
    $filters = ['search' => '', 'fiscal_year_id' => '', 'department_id' => '', 'evaluation' => ''];
    $fiscalYears = [];
    $_GET = [];
    eval($viewPrologue);
    ob_end_clean();

    if (count($projectsSummary) !== 2
        || $projectsSummary[0]['evaluated'] !== true
        || $projectsSummary[1]['evaluated'] !== false
        || $projectsSummary[0]['sub_count'] !== 1) {
        throw new \RuntimeException('A score of zero must count as evaluated, NULL as ungraded, and children must not count as main projects');
    }

    $name = 'evaluation';
    $id = 'project-evaluation-filter';
    $value = 'evaluated';
    $ariaLabel = 'ผลประเมินโครงการหลัก';
    $searchable = false;
    $options = [
        ['value' => 'all', 'label' => 'ผลประเมินทั้งหมด'],
        ['value' => 'evaluated', 'label' => 'ประเมินแล้ว'],
        ['value' => 'ungraded', 'label' => 'ยังไม่ประเมิน'],
    ];
    ob_start();
    include __DIR__ . '/../resources/views/components/custom-select.blade.php';
    $selectMarkup = ob_get_clean();
    if (!str_contains($selectMarkup, '<span class="sr-only">ผลประเมินโครงการหลัก: </span>')
        || !str_contains($selectMarkup, 'role="listbox"')
        || !str_contains($selectMarkup, 'name="evaluation"')) {
        throw new \RuntimeException('Themed evaluation menu is missing accessibility or form attributes');
    }

    echo "Project evaluation PHP checks passed\n";
}
