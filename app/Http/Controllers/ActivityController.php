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
            $actId = Database::insert('activities', [
                'project_id'          => $projectId,
                'name'                => trim($_POST['name']),
                'description'         => trim($_POST['description'] ?? ''),
                'activity_date'       => $_POST['activity_date'],
                'location'            => trim($_POST['location'] ?? ''),
                'responsible_user_id' => !empty($_POST['responsible_user_id']) ? (int)$_POST['responsible_user_id'] : Auth::id(),
                'participant_count'   => (int)($_POST['participant_count'] ?? 0),
                'budget'              => (float)$_POST['budget'],
                'status'              => $_POST['status'] ?? 'not_started',
                'progress'            => ($_POST['status'] ?? '') === 'completed' ? 100.00 : 0.00,
                'notes'               => trim($_POST['notes'] ?? ''),
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
        $actId = (int)$id;
        $act = Database::fetch("SELECT * FROM activities WHERE id = ?", [$actId]);
        if (!$act) {
            Session::flash('error', 'ไม่พบกิจกรรม');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $newStatus = $_POST['status'] ?? 'completed';
        $progress = $newStatus === 'completed' ? 100.00 : ($newStatus === 'in_progress' ? 50.00 : 0.00);

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
        $progress = isset($_POST['progress']) ? (float)$_POST['progress'] : ($status === 'completed' ? 100.00 : $act['progress']);

        Database::update('activities', [
            'name'                => trim($_POST['name']),
            'description'         => trim($_POST['description'] ?? ''),
            'activity_date'       => $_POST['activity_date'],
            'location'            => trim($_POST['location'] ?? ''),
            'participant_count'   => (int)($_POST['participant_count'] ?? 0),
            'budget'              => (float)$_POST['budget'],
            'status'              => $status,
            'progress'            => $progress,
            'notes'               => trim($_POST['notes'] ?? ''),
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
