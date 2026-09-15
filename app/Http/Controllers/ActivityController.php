<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\Router;
use App\Core\Validator;
use App\Services\AuditLogService;
use App\Services\ProgressService;
use App\Enums\ActivityStatus;
use Exception;

class ActivityController
{
    public function store(): void
    {
        if (!Auth::canManageActivities()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์เพิ่มกิจกรรมย่อย');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $projectId = (int)($_POST['project_id'] ?? 0);
        $project = Database::fetch("SELECT * FROM projects WHERE id = ?", [$projectId]);
        if (!$project) {
            Session::flash('error', 'ไม่พบโครงการ');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $v = Validator::make($_POST, [
            'name'          => 'required|min:3|max:255',
            'activity_date' => 'required|date',
            'budget'        => 'required|numeric|min:0',
        ]);

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            header('Location: ' . Router::url("/sub-projects/{$projectId}"));
            exit;
        }

        try {
            $targetParticipants = (int)($_POST['target_participant_count'] ?? $_POST['participant_count'] ?? 0);
            $actualParticipants = (int)($_POST['actual_participant_count'] ?? 0);
            if (!isset($_POST['actual_participant_count']) && isset($_POST['participant_count'])) {
                $actualParticipants = (int)$_POST['participant_count'];
            }

            $status = $_POST['status'] ?? 'not_started';
            $rawProgress = isset($_POST['progress']) ? (float)$_POST['progress'] : 0.00;
            if ($status === 'completed') {
                $progress = 100.00;
            } elseif ($status === 'not_started' || $status === 'cancelled') {
                $progress = 0.00;
            } else {
                $progress = ($rawProgress > 0.00 && $rawProgress < 100.00) ? round($rawProgress, 2) : 0.00;
            }

            $actId = Database::insert('activities', [
                'project_id'               => $projectId,
                'name'                     => trim($_POST['name']),
                'description'              => trim($_POST['description'] ?? ''),
                'activity_date'            => $_POST['activity_date'],
                'location'                 => trim($_POST['location'] ?? ''),
                'responsible_user_id'      => !empty($_POST['responsible_user_id']) ? (int)$_POST['responsible_user_id'] : Auth::id(),
                'target_participant_count' => $targetParticipants,
                'actual_participant_count' => $actualParticipants,
                'participant_count'        => $actualParticipants,
                'budget'                   => (float)$_POST['budget'],
                'status'                   => $status,
                'progress'                 => $progress,
                'notes'                    => trim($_POST['notes'] ?? ''),
            ]);

            AuditLogService::log('CREATE_ACTIVITY', 'Activity', $actId, null, ['name' => $_POST['name'], 'project_id' => $projectId]);
            ProgressService::syncFromActivities($projectId);
            Session::flash('success', "เพิ่มกิจกรรมย่อย '{$_POST['name']}' เรียบร้อยแล้ว");
        } catch (Exception $e) {
            Session::flash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        header('Location: ' . Router::url("/sub-projects/{$projectId}"));
        exit;
    }

    public function updateStatus(string $id): void
    {
        if (!Auth::canManageActivities()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์ปรับสถานะกิจกรรมย่อย');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $actId = (int)$id;
        $act = Database::fetch("SELECT * FROM activities WHERE id = ?", [$actId]);
        if (!$act) {
            Session::flash('error', 'ไม่พบกิจกรรมย่อย');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $newStatus = trim($_POST['status'] ?? 'completed');
        $validStatuses = ['not_started', 'in_progress', 'completed', 'has_problem', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            Session::flash('error', "สถานะกิจกรรมย่อยไม่ถูกต้อง ({$newStatus})");
            header('Location: ' . Router::url("/sub-projects/{$act['project_id']}"));
            exit;
        }

        if ($newStatus === 'completed') {
            $progress = 100.00;
        } elseif ($newStatus === 'not_started' || $newStatus === 'cancelled') {
            $progress = 0.00;
        } elseif ($newStatus === 'in_progress') {
            $currProg = (float)($act['progress'] ?? 0);
            $progress = ($currProg > 0 && $currProg < 100) ? $currProg : 50.00;
        } else {
            $progress = (float)($act['progress'] ?? 0);
        }

        $updateFields = [
            'status'   => $newStatus,
            'progress' => $progress,
        ];

        if (isset($_POST['notes'])) {
            $updateFields['notes'] = trim($_POST['notes']);
        }

        Database::update('activities', $updateFields, "id = ?", [$actId]);

        AuditLogService::log('UPDATE_ACTIVITY_STATUS', 'Activity', $actId, ['status' => $act['status']], ['status' => $newStatus]);
        ProgressService::syncFromActivities((int)$act['project_id']);

        $statusLabel = ActivityStatus::labelFor($newStatus);

        Session::flash('success', "อัปเดตสถานะกิจกรรมย่อยเป็น '{$statusLabel}' เรียบร้อยแล้ว (สถานะโครงการอัปเดตอัตโนมัติ)");
        header('Location: ' . Router::url("/sub-projects/{$act['project_id']}"));
        exit;
    }

    public function update(string $id): void
    {
        if (!Auth::canManageActivities()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์แก้ไขกิจกรรมย่อย');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/projects')));
            exit;
        }

        $actId = (int)$id;
        $act = Database::fetch("SELECT * FROM activities WHERE id = ?", [$actId]);
        if (!$act) {
            Session::flash('error', 'ไม่พบกิจกรรมย่อย');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $v = Validator::make($_POST, [
            'name'          => 'required|min:3|max:255',
            'activity_date' => 'required|date',
            'budget'        => 'required|numeric|min:0',
        ]);

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            header('Location: ' . Router::url("/sub-projects/{$act['project_id']}"));
            exit;
        }

        $status = $_POST['status'] ?? $act['status'];
        $currProgress = (float)($act['progress'] ?? 0);

        if ($status === 'completed') {
            $progress = 100.00;
        } elseif ($status === 'not_started' || $status === 'cancelled') {
            $progress = 0.00;
        } elseif ($status === 'in_progress' || $status === 'has_problem') {
            $progress = ($currProgress > 0 && $currProgress < 100) ? $currProgress : 50.00;
        } else {
            $progress = min(100.00, max(0.00, $currProgress));
        }

        $targetParticipants = isset($_POST['target_participant_count']) 
            ? (int)$_POST['target_participant_count'] 
            : (int)($act['target_participant_count'] ?? 0);
        $actualParticipants = isset($_POST['actual_participant_count']) 
            ? (int)$_POST['actual_participant_count'] 
            : (int)($_POST['participant_count'] ?? $act['actual_participant_count'] ?? $act['participant_count'] ?? 0);

        Database::update('activities', [
            'name'                     => trim($_POST['name']),
            'description'              => trim($_POST['description'] ?? ''),
            'activity_date'            => $_POST['activity_date'],
            'location'                 => trim($_POST['location'] ?? ''),
            'target_participant_count' => $targetParticipants,
            'actual_participant_count' => $actualParticipants,
            'participant_count'        => $actualParticipants,
            'budget'                   => (float)$_POST['budget'],
            'status'                   => $status,
            'progress'                 => $progress,
            'notes'                    => trim($_POST['notes'] ?? ''),
        ], "id = ?", [$actId]);

        AuditLogService::log('UPDATE_ACTIVITY', 'Activity', $actId, ['name' => $act['name']], ['name' => $_POST['name']]);
        ProgressService::syncFromActivities((int)$act['project_id']);
        Session::flash('success', "แก้ไขกิจกรรมย่อย '{$_POST['name']}' เรียบร้อยแล้ว");
        header('Location: ' . Router::url("/sub-projects/{$act['project_id']}"));
        exit;
    }

    public function delete(string $id): void
    {
        if (!Auth::canManageActivities()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์ลบกิจกรรมย่อย');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/projects')));
            exit;
        }

        $actId = (int)$id;
        $act = Database::fetch("SELECT * FROM activities WHERE id = ?", [$actId]);
        if (!$act) {
            Session::flash('error', 'ไม่พบกิจกรรมย่อย');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $projectId = (int)$act['project_id'];
        $name = $act['name'];

        Database::execute("DELETE FROM activities WHERE id = ?", [$actId]);
        AuditLogService::log('DELETE_ACTIVITY', 'Activity', $actId, ['name' => $name, 'project_id' => $projectId]);
        ProgressService::syncFromActivities($projectId);

        Session::flash('success', "ลบกิจกรรมย่อย '{$name}' เรียบร้อยแล้ว");
        header('Location: ' . Router::url("/sub-projects/{$projectId}"));
        exit;
    }
}
