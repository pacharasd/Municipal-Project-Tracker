<?php

declare(strict_types=1);

/**
 * One-time, idempotent status backfill for evaluated main projects.
 * Preview: php scripts/backfill_evaluated_main_project_status.php
 * Apply:   php scripts/backfill_evaluated_main_project_status.php --apply
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script can only run from the command line.\n");
    exit(1);
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $path = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use App\Services\ProjectEvaluationService;

$arguments = array_slice($argv, 1);
if (array_diff($arguments, ['--apply']) || count($arguments) > 1) {
    fwrite(STDERR, "Usage: php scripts/backfill_evaluated_main_project_status.php [--apply]\n");
    exit(1);
}

try {
    $candidates = ProjectEvaluationService::backfillCandidates();
    $apply = in_array('--apply', $arguments, true);
    echo ($apply ? 'Applying' : 'Previewing') . ' evaluated main project status backfill: ' . count($candidates) . " candidate(s)\n";
    $changed = 0;

    foreach ($candidates as $candidate) {
        $id = (int)$candidate['id'];
        echo "Project {$id}: {$candidate['status']} -> completed\n";
        if ($apply && ProjectEvaluationService::backfillProject($id)) {
            $changed++;
        }
    }

    echo $apply ? "Changed {$changed} project(s).\n" : "Preview only. Run with --apply to change data.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Backfill failed: {$exception->getMessage()}\n");
    exit(1);
}
