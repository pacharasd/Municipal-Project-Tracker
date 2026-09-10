<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\Router;
use App\Core\Validator;
use App\Services\AuditLogService;
use App\Services\ProgressService;
use Exception;

class ActivityController
{
    public function store(): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์เพิ่มกิจกรรม');
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
            Session::flash('success', "เพิ่มกิจกรรม '{$_POST['name']}' เรียบร้อยแล้ว");
        } catch (Exception $e) {
            Session::flash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        header('Location: ' . Router::url("/sub-projects/{$projectId}"));
        exit;
    }

    public function updateStatus(string $id): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์ปรับสถานะกิจกรรม');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $actId = (int)$id;
        $act = Database::fetch("SELECT * FROM activities WHERE id = ?", [$actId]);
        if (!$act) {
            Session::flash('error', 'ไม่พบกิจกรรม');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $newStatus = $_POST['status'] ?? 'completed';
        if ($newStatus === 'completed') {
            $progress = 100.00;
        } else {
            $progress = 0.00;
        }

        Database::update('activities', [
            'status'   => $newStatus,
            'progress' => $progress,
        ], "id = ?", [$actId]);

        AuditLogService::log('UPDATE_ACTIVITY_STATUS', 'Activity', $actId, ['status' => $act['status']], ['status' => $newStatus]);
        ProgressService::syncFromActivities((int)$act['project_id']);
        Session::flash('success', "อัปเดตสถานะกิจกรรมเป็น {$newStatus} เรียบร้อยแล้ว");
        header('Location: ' . Router::url("/sub-projects/{$act['project_id']}"));
        exit;
    }

    public function update(string $id): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์แก้ไขกิจกรรม');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/projects')));
            exit;
        }

        $actId = (int)$id;
        $act = Database::fetch("SELECT * FROM activities WHERE id = ?", [$actId]);
        if (!$act) {
            Session::flash('error', 'ไม่พบกิจกรรม');
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
        $rawProgress = isset($_POST['progress']) ? (float)$_POST['progress'] : (float)($act['progress'] ?? 0);

        if ($status === 'completed') {
            $progress = 100.00;
        } elseif ($status === 'not_started' || $status === 'cancelled') {
            $progress = 0.00;
        } elseif ($status === 'in_progress') {
            if ($rawProgress >= 100.00) {
                $progress = 0.00;
            } else {
                $progress = max(0.00, round($rawProgress, 2));
            }
        } elseif ($status === 'has_problem') {
            if ($rawProgress >= 100.00) {
                $progress = 0.00;
            } else {
                $progress = max(0.00, round($rawProgress, 2));
            }
        } else {
            $progress = min(100.00, max(0.00, round($rawProgress, 2)));
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
        Session::flash('success', "แก้ไขกิจกรรม '{$_POST['name']}' เรียบร้อยแล้ว");
        header('Location: ' . Router::url("/sub-projects/{$act['project_id']}"));
        exit;
    }

    public function delete(string $id): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์ลบกิจกรรม');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/projects')));
            exit;
        }

        $actId = (int)$id;
        $act = Database::fetch("SELECT * FROM activities WHERE id = ?", [$actId]);
        if (!$act) {
            Session::flash('error', 'ไม่พบกิจกรรม');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $projectId = (int)$act['project_id'];
        $name = $act['name'];

        Database::execute("DELETE FROM activities WHERE id = ?", [$actId]);
        AuditLogService::log('DELETE_ACTIVITY', 'Activity', $actId, ['name' => $name, 'project_id' => $projectId]);
        ProgressService::syncFromActivities($projectId);

        Session::flash('success', "ลบกิจกรรม '{$name}' เรียบร้อยแล้ว");
        header('Location: ' . Router::url("/sub-projects/{$projectId}"));
        exit;
    }
}
