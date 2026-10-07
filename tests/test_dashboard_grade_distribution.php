<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Services/ProjectService.php';

use App\Services\ProjectService;

$cases = [
    [null, null],
    [0, 'D'],
    [60, 'C'],
    [70, 'B'],
    [80, 'A'],
    [90, 'A+'],
    [100, 'A+'],
];

foreach ($cases as [$score, $expected]) {
    $actual = ProjectService::calculateEvaluationGrade($score === null ? null : (float)$score)['grade'] ?? null;
    if ($actual !== $expected) {
        throw new RuntimeException("Grade boundary failed for score {$score}");
    }
}

$scores = [null, 0, 60, 70, 80, 90, 100];
$expectedDistribution = ['A+' => 2, 'A' => 1, 'B' => 1, 'C' => 1, 'D' => 1, 'ungraded' => 1];
$distribution = ProjectService::countEvaluationGrades($scores);
if ($distribution !== $expectedDistribution || array_sum($distribution) !== count($scores)) {
    throw new RuntimeException('Grade distribution or total count is incorrect');
}

if (ProjectService::countEvaluationGrades([]) !== array_fill_keys(array_keys($expectedDistribution), 0)) {
    throw new RuntimeException('Empty grade distribution is incorrect');
}

echo "Dashboard grade distribution tests passed\n";
