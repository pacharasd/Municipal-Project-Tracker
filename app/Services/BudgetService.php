<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Auth;
use App\Services\AuditLogService;
use Exception;

class BudgetService
{
    public static function disburse(int $projectId, float $amount, string $date, string $description, ?string $recipient = null, ?string $evidenceFile = null): array
    {
        if ($amount <= 0) {
            throw new Exception("ยอดเงินที่ต้องการเบิกจ่ายต้องมากกว่า 0 บาท");
        }

        return Database::transaction(function() use ($projectId, $amount, $date, $description, $recipient, $evidenceFile) {
            $project = Database::fetch("SELECT * FROM projects WHERE id = ?", [$projectId]);
            if (!$project) {
                throw new Exception("ไม่พบข้อมูลโครงการที่ระบุ");
            }

            $currentDisbursed = (float)$project['disbursed_amount'];
            $totalBudget = (float)$project['budget'];
            $newDisbursed = $currentDisbursed + $amount;

            // Rule #8 & #17: ห้ามเบิกจ่ายเกินงบประมาณที่ได้รับอนุมัติ
            if ($newDisbursed > $totalBudget && !Auth::isAdmin()) {
                throw new Exception("ยอดการเบิกจ่ายรวม (" . number_format($newDisbursed, 2) . " บาท) เกินกว่างบประมาณที่ได้รับอนุมัติ (" . number_format($totalBudget, 2) . " บาท)");
            }

            // Ensure budget record exists
            $budget = Database::fetch("SELECT * FROM budgets WHERE project_id = ?", [$projectId]);
            if (!$budget) {
                $budgetId = Database::insert('budgets', [
                    'project_id'       => $projectId,
                    'received_amount'  => $totalBudget,
                    'allocated_amount' => $totalBudget,
                    'disbursed_amount' => 0.00,
                ]);
            } else {
                $budgetId = $budget['id'];
            }

            // Record disbursement
            $disbursementId = Database::insert('budget_disbursements', [
                'budget_id'         => $budgetId,
                'project_id'        => $projectId,
                'amount'            => $amount,
                'disbursement_date' => $date,
                'description'       => $description,
                'recipient'         => $recipient,
                'evidence_file'     => $evidenceFile,
                'created_by'        => Auth::id() ?: 1,
            ]);

            // Update sub-project disbursed amount
            Database::update('projects', ['disbursed_amount' => $newDisbursed], "id = ?", [$projectId]);
            Database::update('budgets', ['disbursed_amount' => $newDisbursed], "id = ?", [$budgetId]);

            // If this is a sub-project, sync parent project disbursed amount
            if (!empty($project['parent_id'])) {
                self::syncParentProjectBudget($project['parent_id']);
            }

            AuditLogService::log('DISBURSE', 'Budget', $disbursementId, 
                ['previous_disbursed' => $currentDisbursed], 
                ['amount' => $amount, 'new_disbursed' => $newDisbursed, 'description' => $description]
            );

            return [
                'success' => true,
                'disbursement_id' => $disbursementId,
                'new_disbursed' => $newDisbursed,
                'remaining' => $totalBudget - $newDisbursed,
            ];
        });
    }

    public static function syncParentProjectBudget(int $parentId): void
    {
        $sql = "SELECT SUM(budget) as total_budget, SUM(disbursed_amount) as total_disbursed 
                FROM projects WHERE parent_id = ?";
        $totals = Database::fetch($sql, [$parentId]);

        $newBudget = (float)($totals['total_budget'] ?? 0);
        $newDisbursed = (float)($totals['total_disbursed'] ?? 0);

        Database::update('projects', [
            'budget' => $newBudget,
            'disbursed_amount' => $newDisbursed
        ], "id = ?", [$parentId]);

        // Sync parent budget table
        Database::update('budgets', [
            'received_amount' => $newBudget,
            'allocated_amount' => $newBudget,
            'disbursed_amount' => $newDisbursed
        ], "project_id = ?", [$parentId]);
    }

    public static function deleteDisbursement(int $disbursementId): array
    {
        return Database::transaction(function() use ($disbursementId) {
            $disb = Database::fetch("SELECT * FROM budget_disbursements WHERE id = ?", [$disbursementId]);
            if (!$disb) {
                throw new Exception("ไม่พบรายการเบิกจ่ายที่ต้องการลบ");
            }

            $projectId = (int)$disb['project_id'];
            $amount = (float)$disb['amount'];

            $project = Database::fetch("SELECT * FROM projects WHERE id = ?", [$projectId]);
            if ($project) {
                $newDisbursed = max(0, (float)$project['disbursed_amount'] - $amount);
                Database::update('projects', ['disbursed_amount' => $newDisbursed], "id = ?", [$projectId]);
                Database::update('budgets', ['disbursed_amount' => $newDisbursed], "id = ?", [$disb['budget_id']]);

                if (!empty($project['parent_id'])) {
                    self::syncParentProjectBudget($project['parent_id']);
                }
            }

            // Remove file if exists
            if (!empty($disb['evidence_file'])) {
                $filePath = dirname(__DIR__, 2) . '/public/uploads/' . $disb['evidence_file'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            Database::execute("DELETE FROM budget_disbursements WHERE id = ?", [$disbursementId]);

            AuditLogService::log('DELETE_DISBURSEMENT', 'Budget', $disbursementId, ['amount' => $amount, 'description' => $disb['description']]);

            return ['success' => true, 'project_id' => $projectId];
        });
    }
}
