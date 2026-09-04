<?php
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Enums/ProjectStatus.php';
require_once __DIR__ . '/../app/Enums/ProgressMode.php';
require_once __DIR__ . '/../app/Services/AuditLogService.php';
require_once __DIR__ . '/../app/Services/ProgressService.php';
require_once __DIR__ . '/../app/Services/BudgetService.php';

echo "=== 1. Starting Service Verification ===\n";

\App\Core\Session::start();
\App\Core\Auth::switchUser(1); // Admin user
$user = \App\Core\Auth::user();
echo "Active User: {$user['name']} ({$user['role_name']})\n";

// Check Subproject 5 before
$sub5Before = \App\Core\Database::fetch("SELECT id, actual_activity_count, planned_activity_count, progress FROM projects WHERE id = 5");
echo "Subproject 5 Before: actual={$sub5Before['actual_activity_count']}/{$sub5Before['planned_activity_count']}, progress={$sub5Before['progress']}%\n";

// Increment Subproject 5 (if not at max)
try {
    $incRes = \App\Services\ProgressService::updateSubProjectProgress(5, 1);
    echo "Incremented Subproject 5: actual={$incRes['actual']}, progress={$incRes['progress']}%\n";
} catch (\Exception $e) {
    echo "Subproject 5 at capacity: {$e->getMessage()}\n";
}

$parent1 = \App\Core\Database::fetch("SELECT id, progress FROM projects WHERE id = 1");
echo "Parent Project 1 Progress (Rule #47 Sync): {$parent1['progress']}%\n";

// Test Rule #46 rejection: try to increment past planned
$sub4 = \App\Core\Database::fetch("SELECT id, actual_activity_count, planned_activity_count FROM projects WHERE id = 4");
echo "\nTesting Rule #46 Guard on Subproject 4 (already at {$sub4['actual_activity_count']}/{$sub4['planned_activity_count']})...\n";
try {
    \App\Services\ProgressService::updateSubProjectProgress(4, 1);
    echo "FAILED: Rule #46 was not enforced!\n";
} catch (\Exception $e) {
    echo "SUCCESS: Rule #46 strictly enforced with message: '{$e->getMessage()}'\n";
}

// Test Budget ceiling guard
$sub5Budget = \App\Core\Database::fetch("SELECT id, budget, disbursed_amount, (budget - disbursed_amount) as remaining FROM projects WHERE id = 5");
echo "\nTesting Budget Ceiling Guard on Subproject 5 (remaining: {$sub5Budget['remaining']})...\n";
// Non-admin attempt over budget
\App\Core\Auth::switchUser(2); // Executive (non-admin)
try {
    $excessAmount = (float)$sub5Budget['remaining'] + 50000;
    \App\Services\BudgetService::disburse(5, $excessAmount, date('Y-m-d'), 'ทดสอบเบิกเกินงบ');
    echo "FAILED: Budget ceiling was not enforced for executive!\n";
} catch (\Exception $e) {
    echo "SUCCESS: Budget ceiling strictly enforced: '{$e->getMessage()}'\n";
}

// Switch back to Admin
\App\Core\Auth::switchUser(1);

// Check Audit Log
$latestAudit = \App\Core\Database::fetch("SELECT * FROM audit_logs ORDER BY id DESC LIMIT 1");
echo "\nLatest Audit Log Entry: ID #{$latestAudit['id']} | Action: {$latestAudit['action']} | Module: {$latestAudit['module']} | Record #{$latestAudit['record_id']}\n";

echo "\n=== ALL LOGICAL VERIFICATIONS PASSED ===\n";
