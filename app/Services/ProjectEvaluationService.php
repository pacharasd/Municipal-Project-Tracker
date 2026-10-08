<?php

namespace App\Services;

use App\Core\Database;
use App\Enums\ProjectStatus;
use Exception;

class ProjectEvaluationService
{
    public static function evaluate(int $projectId, float $score, ?string $notes, int $userId): array
    {
        if (!is_finite($score) || $score < 0 || $score > 100) {
            throw new Exception('คะแนนการประเมินต้องอยู่ระหว่าง 0 ถึง 100');
        }

        return Database::transaction(function () use ($projectId, $score, $notes, $userId): array {
            $project = Database::fetch(
                'SELECT * FROM projects WHERE id = ? AND parent_id IS NULL FOR UPDATE',
                [$projectId]
            );
            if (!$project) {
                throw new Exception('ไม่พบโครงการหลักที่ต้องการประเมิน');
            }
            if ($project['status'] === ProjectStatus::CANCELLED->value) {
                throw new Exception('โครงการที่ยกเลิกต้องเปิดดำเนินการใหม่ก่อนประเมินผล');
            }

            $score = round($score, 2);
            $grade = ProjectService::calculateEvaluationGrade($score)['grade'];
            $notes = trim($notes ?? '');
            $oldValues = [
                'score' => $project['evaluation_score'],
                'grade' => $project['evaluation_grade'],
                'notes' => $project['evaluation_notes'],
                'status' => $project['status'],
            ];
            $newValues = [
                'score' => $score,
                'grade' => $grade,
                'notes' => $notes ?: null,
                'status' => ProjectStatus::COMPLETED->value,
            ];

            Database::update('projects', [
                'evaluation_score' => $score,
                'evaluation_grade' => $grade,
                'evaluated_at' => date('Y-m-d H:i:s'),
                'evaluated_by' => $userId,
                'evaluation_notes' => $notes ?: null,
                'status' => ProjectStatus::COMPLETED->value,
            ], 'id = ?', [$projectId]);

            AuditLogService::log('EVALUATE', 'Project', $projectId, $oldValues, $newValues, $userId);

            return ['score' => $score, 'grade' => $grade];
        });
    }

    /** @return array<int, array{id: int|string, status: string}> */
    public static function backfillCandidates(): array
    {
        return Database::query(
            "SELECT id, status FROM projects
             WHERE parent_id IS NULL AND evaluation_score IS NOT NULL
               AND status NOT IN (?, ?)
             ORDER BY id ASC",
            [ProjectStatus::COMPLETED->value, ProjectStatus::CANCELLED->value]
        );
    }

    public static function backfillProject(int $projectId): bool
    {
        return Database::transaction(function () use ($projectId): bool {
            $project = Database::fetch(
                'SELECT id, status, evaluation_score FROM projects WHERE id = ? AND parent_id IS NULL FOR UPDATE',
                [$projectId]
            );
            if (!$project || $project['evaluation_score'] === null
                || in_array($project['status'], [ProjectStatus::COMPLETED->value, ProjectStatus::CANCELLED->value], true)) {
                return false;
            }

            Database::update('projects', ['status' => ProjectStatus::COMPLETED->value], 'id = ?', [$projectId]);
            AuditLogService::log(
                'BACKFILL_EVALUATION_STATUS', 'Project', $projectId,
                ['status' => $project['status']],
                ['status' => ProjectStatus::COMPLETED->value, 'reason' => 'existing_evaluation']
            );
            return true;
        });
    }
}
