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

            // สำคัญ: กด +1 กิจกรรม แล้วเปอร์เซ็นต์ความสำเร็จโครงการไม่ขึ้นตามจำนวนครั้งที่กด
            // ให้เปอร์เซ็นต์ไปสัมพันธ์กับสถานะและความคืบหน้าโครงการแทน
            // เพราะบางโครงการมีกิจกรรม 1 ครั้ง หากคิดตามสัดส่วนจะข้ามไปสำเร็จ 100% ทันทีโดยไม่มีสถานะกำลังดำเนินการ
            $status = $oldStatus;
            if ($status === 'not_started' || $oldProgress == 0.0) {
                // เมื่อเริ่มบันทึกกิจกรรม สถานะจะเข้าสู่ "กำลังดำเนินการ" ทันที
                $status = 'in_progress';
                // ปรับความคืบหน้าให้อยู่ในสถานะกำลังดำเนินการ (เช่น 50% หรือค่าที่ผู้ใช้กำหนด แต่ไม่กระโดดไป 100% สำเร็จ)
                $progress = ($oldProgress > 0) ? $oldProgress : ($planned > 1 ? min(90.0, round(($actual / $planned) * 100, 2)) : 50.0);
            } else {
                // หากกำลังดำเนินการอยู่แล้ว ให้คงเปอร์เซ็นต์ความคืบหน้าที่ผู้ดูแลกำหนดไว้
                $progress = $oldProgress;
            }
            $mode = 'manual';
        } elseif ($manualProgress !== null) {
            if ($manualProgress < 0 || $manualProgress > 100) {
                throw new Exception("เปอร์เซ็นต์ความคืบหน้าต้องอยู่ระหว่าง 0 ถึง 100%");
            }
            $progress = round($manualProgress, 2);
            $mode = 'manual';

            // ปรับสถานะให้สัมพันธ์กับเปอร์เซ็นต์ความคืบหน้า
            if ($progress >= 100.0) {
                $status = ($oldStatus === 'cancelled') ? $oldStatus : 'completed';
            } elseif ($progress > 0.0) {
                $status = ($oldStatus === 'has_problem' || $oldStatus === 'cancelled') ? $oldStatus : 'in_progress';
            } else {
                $status = ($oldStatus === 'has_problem' || $oldStatus === 'cancelled') ? $oldStatus : 'not_started';
            }
        } else {
            $progress = $oldProgress;
            $status = $oldStatus;
            $mode = $project['progress_mode'] ?? 'manual';
        }

        // กำหนดวันที่เสร็จสิ้นเมื่อสถานะเป็นเสร็จสิ้นสมบูรณ์เท่านั้น
        $completionDate = ($status === 'completed') ? date('Y-m-d') : null;

        Database::update('projects', [
            'actual_activity_count' => $actual,
            'progress'              => $progress,
            'progress_mode'         => $mode,
            'status'                => $status,
            'completion_date'       => $completionDate,
        ], "id = ?", [$subProjectId]);

        // Sync parent project average progress
        if (!empty($project['parent_id'])) {
            self::syncParentProjectProgress((int)$project['parent_id']);
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

        // ให้เปอร์เซ็นต์สัมพันธ์กับสถานะและความคืบหน้าโครงการ
        if ($newProgress !== null) {
            if ($newProgress < 0 || $newProgress > 100) {
                throw new Exception("เปอร์เซ็นต์ความคืบหน้าต้องอยู่ระหว่าง 0 ถึง 100%");
            }
            $progress = round($newProgress, 2);

            // เมื่อกำหนดเปอร์เซ็นต์ ให้ปรับสถานะให้สอดคล้องกัน
            if ($progress >= 100.0 && $newStatus !== 'cancelled' && $newStatus !== 'has_problem') {
                $newStatus = 'completed';
            } elseif ($progress == 0.0 && $newStatus !== 'cancelled' && $newStatus !== 'has_problem') {
                $newStatus = 'not_started';
            } elseif ($progress > 0.0 && $progress < 100.0 && $newStatus !== 'cancelled' && $newStatus !== 'has_problem') {
                $newStatus = 'in_progress';
            }
        } else {
            // เมื่อเลือกสถานะ ให้กำหนดเปอร์เซ็นต์ที่สัมพันธ์กัน
            if ($newStatus === 'completed') {
                $progress = 100.0;
            } elseif ($newStatus === 'not_started') {
                $progress = 0.0;
            } elseif ($newStatus === 'in_progress') {
                $progress = ($oldProgress > 0 && $oldProgress < 100.0) ? $oldProgress : 50.0;
            } else {
                $progress = $oldProgress;
            }
        }

        // ปรับสถานะให้สอดคล้องกับเปอร์เซ็นต์อัตโนมัติ
        if ($progress >= 100.0 && $newStatus !== 'cancelled' && $newStatus !== 'has_problem') {
            $newStatus = 'completed';
            $actual = $planned;
        } elseif ($progress == 0.0 && $newStatus !== 'cancelled' && $newStatus !== 'has_problem') {
            $newStatus = 'not_started';
            $actual = 0;
        } elseif ($progress > 0 && $progress < 100.0 && $newStatus !== 'cancelled' && $newStatus !== 'has_problem') {
            $newStatus = 'in_progress';
            if ($planned > 0) {
                $actual = (int)round(($progress / 100.0) * $planned);
            }
        }

        if ($newStatus === 'completed') {
            $actual = $planned;
        } elseif ($newStatus === 'not_started') {
            $actual = 0;
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

    /**
     * ซิงค์ความก้าวหน้าและสถานะจากกิจกรรมในตาราง activities
     */
    public static function syncFromActivities(int $subProjectId): void
    {
        $project = Database::fetch("SELECT * FROM projects WHERE id = ?", [$subProjectId]);
        if (!$project) return;

        $totalActivities = (int)Database::fetchColumn("SELECT COUNT(*) FROM activities WHERE project_id = ?", [$subProjectId]);
        if ($totalActivities === 0) return;

        $completedActivities = (int)Database::fetchColumn("SELECT COUNT(*) FROM activities WHERE project_id = ? AND status = 'completed'", [$subProjectId]);

        $planned = max((int)$project['planned_activity_count'], $totalActivities);
        $actual = $completedActivities;
        $progress = $planned > 0 ? round(($actual / $planned) * 100, 2) : 0.0;

        $status = $project['status'];
        if ($status !== 'has_problem' && $status !== 'cancelled') {
            if ($progress >= 100.0) {
                $status = 'completed';
            } elseif ($progress > 0.0) {
                $status = 'in_progress';
            } else {
                $status = 'not_started';
            }
        }

        Database::update('projects', [
            'planned_activity_count' => $planned,
            'actual_activity_count'  => $actual,
            'progress'               => $progress,
            'status'                 => $status,
            'completion_date'        => ($status === 'completed') ? date('Y-m-d') : null,
        ], "id = ?", [$subProjectId]);

        if (!empty($project['parent_id'])) {
            self::syncParentProjectProgress((int)$project['parent_id']);
        }
    }
}
