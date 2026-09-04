<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\View;
use App\Core\Router;
use App\Core\Validator;
use App\Services\ProgressService;
use App\Services\ProjectService;
use App\Services\BudgetService;
use App\Services\AuditLogService;
use Exception;

class SubProjectController
{
    public function show(string $id): void
    {
        $project = ProjectService::getProjectById((int)$id);
        if (!$project || $project['parent_id'] === null) {
            Session::flash('error', 'ไม่พบโครงการย่อยที่ระบุ');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $users = Database::query("SELECT id, name, position FROM users ORDER BY name ASC");

        View::render('sub_projects.show', [
            'project' => $project,
            'users'   => $users,
        ]);
    }

    public function store(): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์เพิ่มโครงการย่อย');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $parentId = (int)($_POST['parent_id'] ?? 0);
        $parent = Database::fetch("SELECT * FROM projects WHERE id = ? AND parent_id IS NULL", [$parentId]);
        if (!$parent) {
            Session::flash('error', 'ไม่พบโครงการหลัก');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $v = Validator::make($_POST, [
            'name'                   => 'required|min:3|max:255',
            'project_code'           => 'required|min:2|max:50',
            'budget'                 => 'required|numeric|min:0',
            'planned_activity_count' => 'required|numeric|min:1',
            'start_date'             => 'required|date',
            'end_date'               => 'required|date',
        ]);

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            header('Location: ' . Router::url("/projects/{$parentId}"));
            exit;
        }

        $code = trim($_POST['project_code']);
        if (Database::fetchColumn("SELECT COUNT(*) FROM projects WHERE project_code = ?", [$code]) > 0) {
            Session::flash('error', "รหัสโครงการย่อย '{$code}' มีอยู่ในระบบแล้ว");
            header('Location: ' . Router::url("/projects/{$parentId}"));
            exit;
        }

        try {
            $budget = (float)$_POST['budget'];
            $subId = Database::insert('projects', [
                'parent_id'              => $parentId,
                'project_code'           => $code,
                'name'                   => trim($_POST['name']),
                'description'            => trim($_POST['description'] ?? ''),
                'fiscal_year_id'         => $parent['fiscal_year_id'],
                'category_id'            => $parent['category_id'],
                'department_id'          => $parent['department_id'],
                'responsible_user_id'    => !empty($_POST['responsible_user_id']) ? (int)$_POST['responsible_user_id'] : Auth::id(),
                'activity_type'          => trim($_POST['activity_type'] ?? ''),
                'objective'              => trim($_POST['objective'] ?? ''),
                'target_group'           => trim($_POST['target_group'] ?? ''),
                'target_quantity'        => (int)($_POST['target_quantity'] ?? 0),
                'location'               => trim($_POST['location'] ?? ''),
                'methodology'            => trim($_POST['methodology'] ?? ''),
                'start_date'             => $_POST['start_date'],
                'end_date'               => $_POST['end_date'],
                'planned_activity_count' => (int)$_POST['planned_activity_count'],
                'actual_activity_count'  => 0,
                'budget'                 => $budget,
                'disbursed_amount'       => 0.00,
                'status'                 => 'not_started',
                'progress'               => 0.00,
                'progress_mode'          => $_POST['progress_mode'] ?? 'auto',
            ]);

            // Sync parent budget & progress
            BudgetService::syncParentProjectBudget($parentId);
            ProgressService::syncParentProjectProgress($parentId);

            AuditLogService::log('CREATE_SUBPROJECT', 'Project', $subId, null, ['code' => $code, 'name' => $_POST['name']]);
            Session::flash('success', "เพิ่มโครงการย่อย '{$_POST['name']}' เรียบร้อยแล้ว");
            header('Location: ' . Router::url("/sub-projects/{$subId}"));
            exit;
        } catch (Exception $e) {
            Session::flash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            header('Location: ' . Router::url("/projects/{$parentId}"));
            exit;
        }
    }

    public function incrementProgress(string $id): void
    {
        $subId = (int)$id;
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

        try {
            $res = ProgressService::updateSubProjectProgress($subId, +1);
            if ($isAjax) {
                View::json($res);
            }
            Session::flash('success', "เพิ่มความคืบหน้าสำเร็จ: ดำเนินการแล้ว {$res['actual']}/{$res['planned']} ครั้ง ({$res['progress']}%)");
        } catch (Exception $e) {
            if ($isAjax) {
                View::json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            Session::flash('error', $e->getMessage());
        }

        header('Location: ' . Router::url("/sub-projects/{$subId}"));
        exit;
    }

    public function updateManualProgress(string $id): void
    {
        $subId = (int)$id;
        $manual = (float)($_POST['manual_progress'] ?? 0);

        try {
            $res = ProgressService::updateSubProjectProgress($subId, null, $manual);
            Session::flash('success', "อัปเดตเปอร์เซ็นต์ความสำเร็จเป็น {$res['progress']}% เรียบร้อยแล้ว");
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        header('Location: ' . Router::url("/sub-projects/{$subId}"));
        exit;
    }

    public function updateStatusAndProgress(string $id): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์ปรับสถานะหรือความคืบหน้าโครงการ');
            header('Location: ' . Router::url("/sub-projects/{$id}"));
            exit;
        }

        $subId = (int)$id;
        $status = trim($_POST['status'] ?? 'in_progress');
        $progress = (isset($_POST['progress']) && $_POST['progress'] !== '') ? (float)$_POST['progress'] : null;
        $note = trim($_POST['problem_description'] ?? '');

        try {
            $res = ProgressService::updateStatusAndProgress($subId, $status, $progress, $note);
            $statusLabel = \App\Enums\ProjectStatus::labelFor($res['status']);
            Session::flash('success', "อัปเดตสถานะเป็น '{$statusLabel}' และความก้าวหน้าเป็น {$res['progress']}% เรียบร้อยแล้ว");
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        header('Location: ' . Router::url("/sub-projects/{$subId}"));
        exit;
    }

    public function reportProblem(string $id): void
    {
        $subId = (int)$id;
        $desc = trim($_POST['problem_description'] ?? '');

        if (empty($desc)) {
            Session::flash('error', 'กรุณาระบุรายละเอียดปัญหาที่พบ');
            header('Location: ' . Router::url("/sub-projects/{$subId}"));
            exit;
        }

        try {
            ProjectService::reportProblem($subId, $desc);
            Session::flash('success', 'บันทึกปัญหาและอัปเดตสถานะโครงการเป็น "มีปัญหา" เรียบร้อยแล้ว');
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        header('Location: ' . Router::url("/sub-projects/{$subId}"));
        exit;
    }

    public function resolveProblem(string $id): void
    {
        $subId = (int)$id;
        $note = trim($_POST['resolution_note'] ?? '');

        try {
            ProjectService::resolveProblem($subId, $note);
            Session::flash('success', 'บันทึกการแก้ไขปัญหาเรียบร้อยแล้ว');
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        header('Location: ' . Router::url("/sub-projects/{$subId}"));
        exit;
    }

    public function update(string $id): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์แก้ไขโครงการย่อย');
            header('Location: ' . Router::url("/sub-projects/{$id}"));
            exit;
        }

        $subId = (int)$id;
        $project = Database::fetch("SELECT * FROM projects WHERE id = ? AND parent_id IS NOT NULL", [$subId]);
        if (!$project) {
            Session::flash('error', 'ไม่พบโครงการย่อย');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $v = Validator::make($_POST, [
            'name'       => 'required|min:3|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
            'budget'     => 'required|numeric|min:0',
        ]);

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            header('Location: ' . Router::url("/sub-projects/{$subId}"));
            exit;
        }

        $newBudget = (float)$_POST['budget'];

        Database::update('projects', [
            'name'                   => trim($_POST['name']),
            'description'            => trim($_POST['description'] ?? ''),
            'activity_type'          => trim($_POST['activity_type'] ?? ''),
            'objective'              => trim($_POST['objective'] ?? ''),
            'target_group'           => trim($_POST['target_group'] ?? ''),
            'target_quantity'        => (int)($_POST['target_quantity'] ?? 0),
            'location'               => trim($_POST['location'] ?? ''),
            'methodology'            => trim($_POST['methodology'] ?? ''),
            'responsible_user_id'    => !empty($_POST['responsible_user_id']) ? (int)$_POST['responsible_user_id'] : $project['responsible_user_id'],
            'start_date'             => $_POST['start_date'],
            'end_date'               => $_POST['end_date'],
            'budget'                 => $newBudget,
            'planned_activity_count' => !empty($_POST['planned_activity_count']) ? (int)$_POST['planned_activity_count'] : $project['planned_activity_count'],
            'notes'                  => trim($_POST['notes'] ?? ''),
        ], "id = ?", [$subId]);

        // Update budget table
        Database::update('budgets', [
            'received_amount'  => $newBudget,
            'allocated_amount' => $newBudget,
        ], "project_id = ?", [$subId]);

        // Sync parent budget
        BudgetService::syncParentProjectBudget($project['parent_id']);

        AuditLogService::log('UPDATE_SUBPROJECT', 'Project', $subId, ['name' => $project['name'], 'budget' => $project['budget']], ['name' => $_POST['name'], 'budget' => $newBudget]);
        Session::flash('success', 'อัปเดตข้อมูลโครงการย่อยเรียบร้อยแล้ว');
        header('Location: ' . Router::url("/sub-projects/{$subId}"));
        exit;
    }

    public function delete(string $id): void
    {
        if (!Auth::isAdmin()) {
            Session::flash('error', 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถลบโครงการย่อยได้');
            header('Location: ' . Router::url("/sub-projects/{$id}"));
            exit;
        }

        $subId = (int)$id;
        $project = Database::fetch("SELECT * FROM projects WHERE id = ? AND parent_id IS NOT NULL", [$subId]);
        if (!$project) {
            Session::flash('error', 'ไม่พบโครงการย่อย');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $parentId = (int)$project['parent_id'];
        $name = $project['name'];

        // Delete subproject
        Database::execute("DELETE FROM projects WHERE id = ?", [$subId]);

        // Recalculate parent project budget & progress
        BudgetService::syncParentProjectBudget($parentId);
        ProgressService::syncParentProjectProgress($parentId);

        AuditLogService::log('DELETE_SUBPROJECT', 'Project', $subId, ['name' => $name, 'parent_id' => $parentId]);
        Session::flash('success', "ลบโครงการย่อย '{$name}' เรียบร้อยแล้ว");
        header('Location: ' . Router::url("/projects/{$parentId}"));
        exit;
    }
}
