<?php

namespace App\Services;

use App\Core\Database;
use App\Services\AuditLogService;
use Exception;

class ProgressService
{
    /**
     * Calculate and update progress for a sub-project
     */
    public static function updateSubProjectProgress(int $subProjectId, ?int $increment = null, ?float $manualProgress = null): array
    {
        $project = Database::fetch("SELECT * FROM projects WHERE id = ?", [$subProjectId]);
        if (!$project) {
            throw new Exception("ไม่พบโครงการที่ระบุ");
        }

        $mode = $project['progress_mode'] ?? 'auto';
        $planned = (int)($project['planned_activity_count'] ?? 1);
        $actual = (int)($project['actual_activity_count'] ?? 0);
        $oldProgress = (float)$project['progress'];
        $oldStatus = $project['status'];

        if ($increment !== null) {
            $newActual = $actual + $increment;
            // Rule #44 Test 6: ห้ามบันทึกกิจกรรมเกินจำนวนครั้งที่วางแผนไว้
            if ($newActual > $planned) {
                throw new Exception("ไม่สามารถเพิ่มจำนวนกิจกรรมได้ เนื่องจากดำเนินการครบตามจำนวนที่กำหนดแล้ว ({$planned}/{$planned})");
            }
            if ($newActual < 0) {
                throw new Exception("จำนวนกิจกรรมต้องไม่ติดลบ");
            }
            $actual = $newActual;
        }

        if ($mode === 'auto') {
            // Rule #6 & #46: ความสำเร็จ = กิจกรรมที่ทำแล้ว ÷ ทั้งหมด × 100
            $progress = $planned > 0 ? round(($actual / $planned) * 100, 2) : 0.0;
        } else {
            // Manual Mode: 0 - 100%
            if ($manualProgress !== null) {
                if ($manualProgress < 0 || $manualProgress > 100) {
                    throw new Exception("เปอร์เซ็นต์ความคืบหน้าต้องอยู่ระหว่าง 0 ถึง 100%");
                }
                $progress = round($manualProgress, 2);
            } else {
                $progress = $oldProgress;
            }
        }

        // Determine Status based on Progress & Existing condition
        $status = $project['status'];
        if ($progress >= 100.0) {
            $status = 'completed';
            $completionDate = date('Y-m-d');
        } elseif ($progress > 0) {
            if ($status !== 'has_problem') {
                $status = 'in_progress';
            }
            $completionDate = null;
        } else {
            if ($status !== 'has_problem') {
                $status = 'not_started';
            }
            $completionDate = null;
        }

        Database::update('projects', [
            'actual_activity_count' => $actual,
            'progress'              => $progress,
            'status'                => $status,
            'completion_date'       => $completionDate,
        ], "id = ?", [$subProjectId]);

        // Sync parent project average progress
        if (!empty($project['parent_id'])) {
            self::syncParentProjectProgress($project['parent_id']);
        }

        AuditLogService::log('UPDATE_PROGRESS', 'Project', $subProjectId, 
            ['progress' => $oldProgress, 'status' => $oldStatus, 'actual' => $project['actual_activity_count']], 
            ['progress' => $progress, 'status' => $status, 'actual' => $actual]
        );

        return [
            'success'  => true,
            'progress' => $progress,
            'status'   => $status,
            'actual'   => $actual,
            'planned'  => $planned,
        ];
    }

    /**
     * Directly update Status and Progress by Admin or Project Manager
     * Supports manual override regardless of activity iterations
     */
    public static function updateStatusAndProgress(int $subProjectId, string $newStatus, ?float $newProgress = null, ?string $note = null): array
    {
        $project = Database::fetch("SELECT * FROM projects WHERE id = ?", [$subProjectId]);
        if (!$project) {
            throw new Exception("ไม่พบโครงการที่ระบุ");
        }

        $validStatuses = ['not_started', 'in_progress', 'completed', 'has_problem', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            throw new Exception("สถานะโครงการไม่ถูกต้อง ({$newStatus})");
        }

        $oldStatus = $project['status'];
        $oldProgress = (float)$project['progress'];
        $planned = (int)($project['planned_activity_count'] ?? 1);
        $actual = (int)($project['actual_activity_count'] ?? 0);

        // Determine progress based on explicit input or adaptive status defaults
        if ($newProgress !== null) {
            if ($newProgress < 0 || $newProgress > 100) {
                throw new Exception("เปอร์เซ็นต์ความคืบหน้าต้องอยู่ระหว่าง 0 ถึง 100%");
            }
            $progress = round($newProgress, 2);
        } else {
            // Adaptive progress linked directly to project status
            if ($newStatus === 'completed') {
                $progress = 100.0;
            } elseif ($newStatus === 'not_started') {
                $progress = 0.0;
            } elseif ($newStatus === 'in_progress') {
                $progress = $oldProgress > 0 ? $oldProgress : 50.0;
            } else {
                $progress = $oldProgress;
            }
        }

        // Auto-adjust status if progress is 100% and not cancelled or flagged as problem
        if ($progress >= 100.0 && $newStatus !== 'cancelled' && $newStatus !== 'has_problem') {
            $newStatus = 'completed';
        } elseif ($progress == 0.0 && $newStatus !== 'cancelled' && $newStatus !== 'has_problem') {
            $newStatus = 'not_started';
        }

        $completionDate = ($newStatus === 'completed') ? date('Y-m-d') : null;
        $problemDescription = $project['problem_description'];

        if ($newStatus === 'has_problem') {
            if (!empty($note)) {
                $problemDescription = trim($note);
            }
        } elseif ($newStatus === 'completed' || $newStatus === 'in_progress') {
            // If problem is resolved or completed, clear problem flag
            $problemDescription = null;
        }

        // Sync actual count with completion if needed
        if ($newStatus === 'completed') {
            $actual = $planned;
        } elseif ($newStatus === 'not_started') {
            $actual = 0;
        }

        Database::update('projects', [
            'status'                => $newStatus,
            'progress'              => $progress,
            'progress_mode'         => 'manual',
            'actual_activity_count' => $actual,
            'completion_date'       => $completionDate,
            'problem_description'   => $problemDescription,
        ], "id = ?", [$subProjectId]);

        // Sync parent project average progress (Rule #47)
        if (!empty($project['parent_id'])) {
            self::syncParentProjectProgress((int)$project['parent_id']);
        }

        AuditLogService::log('UPDATE_STATUS_PROGRESS', 'Project', $subProjectId,
            ['status' => $oldStatus, 'progress' => $oldProgress],
            ['status' => $newStatus, 'progress' => $progress, 'problem_description' => $problemDescription]
        );

        return [
            'success'  => true,
            'status'   => $newStatus,
            'progress' => $progress,
            'actual'   => $actual,
            'planned'  => $planned,
        ];
    }

    /**
     * Rule #47: ความสำเร็จรวมของโครงการหลัก = ค่าเฉลี่ยของเปอร์เซ็นต์โครงการย่อยทั้งหมด
     */
    public static function syncParentProjectProgress(int $parentId): void
    {
        $subs = Database::query("SELECT progress, status FROM projects WHERE parent_id = ?", [$parentId]);
        if (empty($subs)) {
            return;
        }

        $totalProgress = 0.0;
        $count = count($subs);
        $allCompleted = true;
        $hasAnyProblem = false;
        $hasAnyInProgress = false;

        foreach ($subs as $s) {
            $totalProgress += (float)$s['progress'];
            if ($s['status'] !== 'completed') {
                $allCompleted = false;
            }
            if ($s['status'] === 'has_problem') {
                $hasAnyProblem = true;
            }
            if ($s['status'] === 'in_progress' || (float)$s['progress'] > 0) {
                $hasAnyInProgress = true;
            }
        }

        $avgProgress = round($totalProgress / $count, 2);

        $parentStatus = 'not_started';
        if ($allCompleted && $avgProgress >= 100.0) {
            $parentStatus = 'completed';
        } elseif ($hasAnyProblem) {
            $parentStatus = 'has_problem';
        } elseif ($hasAnyInProgress || $avgProgress > 0) {
            $parentStatus = 'in_progress';
        }

        Database::update('projects', [
            'progress' => $avgProgress,
            'status'   => $parentStatus,
        ], "id = ?", [$parentId]);
    }
}
