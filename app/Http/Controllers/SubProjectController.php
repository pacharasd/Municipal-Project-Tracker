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

        $startDate = !empty($_POST['start_date']) ? trim($_POST['start_date']) : null;
        $endDate = !empty($_POST['end_date']) ? trim($_POST['end_date']) : null;

        if ($startDate && $endDate && $startDate > $endDate) {
            Session::flash('error', 'วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุดของโครงการย่อย');
            header('Location: ' . Router::url("/projects/{$parentId}"));
            exit;
        }

        $parentStartDate = !empty($parent['start_date']) ? $parent['start_date'] : null;
        $parentEndDate = !empty($parent['end_date']) ? $parent['end_date'] : null;

        if ($parentStartDate && $startDate && $startDate < $parentStartDate) {
            $formattedParentStart = date('d/m/', strtotime($parentStartDate)) . (date('Y', strtotime($parentStartDate)) + 543);
            Session::flash('error', "วันที่เริ่มต้นของโครงการย่อยต้องเท่ากับหรือมากกว่าวันที่เริ่มต้นของโครงการหลัก ({$formattedParentStart})");
            header('Location: ' . Router::url("/projects/{$parentId}"));
            exit;
        }

        if ($parentEndDate && $endDate && $endDate > $parentEndDate) {
            $formattedParentEnd = date('d/m/', strtotime($parentEndDate)) . (date('Y', strtotime($parentEndDate)) + 543);
            Session::flash('error', "วันที่สิ้นสุดของโครงการย่อยต้องไม่เกินวันที่สิ้นสุดของโครงการหลัก ({$formattedParentEnd})");
            header('Location: ' . Router::url("/projects/{$parentId}"));
            exit;
        }

        $budget = (float)$_POST['budget'];
        $parentBudget = (float)($parent['budget'] ?? 0);
        $totalAllocated = (float)Database::fetchColumn(
            "SELECT COALESCE(SUM(budget), 0) FROM projects WHERE parent_id = ?",
            [$parentId]
        );
        $remainingBudget = max(0, $parentBudget - $totalAllocated);
        if ($budget > $remainingBudget) {
            Session::flash('error', "งบประมาณโครงการย่อย (" . number_format($budget, 2) . " บาท) เกินกว่างบประมาณคงเหลือของโครงการหลักที่สามารถจัดสรรได้ (คงเหลือ: " . number_format($remainingBudget, 2) . " บาท)");
            header('Location: ' . Router::url("/projects/{$parentId}"));
            exit;
        }

        try {
            $responsiblePerson = trim($_POST['responsible_person'] ?? '');
            $responsibleUserId = null;
            if (!empty($_POST['responsible_user_id'])) {
                $responsibleUserId = (int)$_POST['responsible_user_id'];
            } elseif (!empty($responsiblePerson)) {
                $matchedUser = Database::fetch("SELECT id FROM users WHERE name LIKE ? LIMIT 1", ["%{$responsiblePerson}%"]);
                if ($matchedUser) {
                    $responsibleUserId = (int)$matchedUser['id'];
                }
            }
            if (!$responsibleUserId) {
                $responsibleUserId = Auth::id() ?: 1;
            }

            $subId = Database::insert('projects', [
                'parent_id'              => $parentId,
                'name'                   => trim($_POST['name']),
                'description'            => trim($_POST['description'] ?? ''),
                'fiscal_year_id'         => $parent['fiscal_year_id'],
                'category_id'            => $parent['category_id'],
                'department_id'          => $parent['department_id'],
                'responsible_user_id'    => $responsibleUserId,
                'responsible_person'     => $responsiblePerson ?: null,
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

            AuditLogService::log('CREATE_SUBPROJECT', 'Project', $subId, null, ['name' => $_POST['name']]);
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
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์ปรับความคืบหน้าโครงการ');
            header('Location: ' . Router::url("/sub-projects/{$id}"));
            exit;
        }

        $subId = (int)$id;
        $wantsJson = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        try {
            $res = ProgressService::updateSubProjectProgress($subId, +1);
            if ($wantsJson) {
                View::json($res);
            }
            Session::flash('success', "เพิ่มความคืบหน้าสำเร็จ: ดำเนินการแล้ว {$res['actual']}/{$res['planned']} ครั้ง");
        } catch (Exception $e) {
            if ($wantsJson) {
                View::json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            Session::flash('error', $e->getMessage());
        }

        header('Location: ' . Router::url("/sub-projects/{$subId}"));
        exit;
    }

    public function updateManualProgress(string $id): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์ปรับความคืบหน้าโครงการ');
            header('Location: ' . Router::url("/sub-projects/{$id}"));
            exit;
        }

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
            Session::flash('error', 'คุณไม่มีสิทธิ์ปรับสถานะโครงการ');
            header('Location: ' . Router::url("/sub-projects/{$id}"));
            exit;
        }

        $subId = (int)$id;
        $status = trim($_POST['status'] ?? 'in_progress');
        $note = trim($_POST['problem_description'] ?? '');

        try {
            $res = ProgressService::updateStatusAndProgress($subId, $status, null, $note);
            $statusLabel = \App\Enums\ProjectStatus::labelFor($res['status']);
            Session::flash('success', "อัปเดตสถานะโครงการเป็น '{$statusLabel}' เรียบร้อยแล้ว");
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        header('Location: ' . Router::url("/sub-projects/{$subId}"));
        exit;
    }

    public function reportProblem(string $id): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์บันทึกปัญหาโครงการ');
            header('Location: ' . Router::url("/sub-projects/{$id}"));
            exit;
        }

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
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์บันทึกการแก้ไขปัญหาโครงการ');
            header('Location: ' . Router::url("/sub-projects/{$id}"));
            exit;
        }

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

        $parent = Database::fetch("SELECT * FROM projects WHERE id = ?", [$project['parent_id']]);
        $startDate = !empty($_POST['start_date']) ? trim($_POST['start_date']) : null;
        $endDate = !empty($_POST['end_date']) ? trim($_POST['end_date']) : null;

        if ($startDate && $endDate && $startDate > $endDate) {
            Session::flash('error', 'วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุดของโครงการย่อย');
            header('Location: ' . Router::url("/sub-projects/{$subId}"));
            exit;
        }

        $parentStartDate = !empty($parent['start_date']) ? $parent['start_date'] : null;
        $parentEndDate = !empty($parent['end_date']) ? $parent['end_date'] : null;

        if ($parentStartDate && $startDate && $startDate < $parentStartDate) {
            $formattedParentStart = date('d/m/', strtotime($parentStartDate)) . (date('Y', strtotime($parentStartDate)) + 543);
            Session::flash('error', "วันที่เริ่มต้นของโครงการย่อยต้องเท่ากับหรือมากกว่าวันที่เริ่มต้นของโครงการหลัก ({$formattedParentStart})");
            header('Location: ' . Router::url("/sub-projects/{$subId}"));
            exit;
        }

        if ($parentEndDate && $endDate && $endDate > $parentEndDate) {
            $formattedParentEnd = date('d/m/', strtotime($parentEndDate)) . (date('Y', strtotime($parentEndDate)) + 543);
            Session::flash('error', "วันที่สิ้นสุดของโครงการย่อยต้องไม่เกินวันที่สิ้นสุดของโครงการหลัก ({$formattedParentEnd})");
            header('Location: ' . Router::url("/sub-projects/{$subId}"));
            exit;
        }

        $newBudget = (float)$_POST['budget'];
        $parent = Database::fetch("SELECT * FROM projects WHERE id = ?", [$project['parent_id']]);
        $parentBudget = (float)($parent['budget'] ?? 0);
        $otherSubsBudget = (float)Database::fetchColumn(
            "SELECT COALESCE(SUM(budget), 0) FROM projects WHERE parent_id = ? AND id != ?",
            [$project['parent_id'], $subId]
        );
        $maxAllowed = max(0, $parentBudget - $otherSubsBudget);
        if ($parent && $newBudget > $maxAllowed) {
            Session::flash('error', "งบประมาณโครงการย่อย (" . number_format($newBudget, 2) . " บาท) เกินกว่างบประมาณคงเหลือของโครงการหลักที่สามารถจัดสรรได้ (สามารถจัดสรรได้สูงสุด: " . number_format($maxAllowed, 2) . " บาท)");
            header('Location: ' . Router::url("/sub-projects/{$subId}"));
            exit;
        }
        $responsiblePerson = isset($_POST['responsible_person']) ? trim($_POST['responsible_person']) : ($project['responsible_person'] ?? '');
        $responsibleUserId = $project['responsible_user_id'];
        if (!empty($_POST['responsible_user_id'])) {
            $responsibleUserId = (int)$_POST['responsible_user_id'];
        } elseif (!empty($responsiblePerson)) {
            $matchedUser = Database::fetch("SELECT id FROM users WHERE name LIKE ? LIMIT 1", ["%{$responsiblePerson}%"]);
            if ($matchedUser) {
                $responsibleUserId = (int)$matchedUser['id'];
            }
        }

        Database::update('projects', [
            'name'                   => trim($_POST['name']),
            'description'            => trim($_POST['description'] ?? ''),
            'activity_type'          => trim($_POST['activity_type'] ?? ''),
            'objective'              => trim($_POST['objective'] ?? ''),
            'target_group'           => trim($_POST['target_group'] ?? ''),
            'target_quantity'        => (int)($_POST['target_quantity'] ?? 0),
            'location'               => trim($_POST['location'] ?? ''),
            'methodology'            => trim($_POST['methodology'] ?? ''),
            'responsible_user_id'    => $responsibleUserId,
            'responsible_person'     => $responsiblePerson ?: null,
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

        Database::transaction(function () use ($subId) {
            Database::execute("DELETE FROM activities WHERE project_id = ?", [$subId]);
            Database::execute("DELETE FROM budget_disbursements WHERE project_id = ?", [$subId]);
            Database::execute("DELETE FROM attachments WHERE project_id = ?", [$subId]);
            Database::execute("DELETE FROM budgets WHERE project_id = ?", [$subId]);
            Database::execute("DELETE FROM projects WHERE id = ?", [$subId]);
        });

        // Recalculate parent project budget & progress
        BudgetService::syncParentProjectBudget($parentId);
        ProgressService::syncParentProjectProgress($parentId);

        AuditLogService::log('DELETE_SUBPROJECT', 'Project', $subId, ['name' => $name, 'parent_id' => $parentId]);
        Session::flash('success', "ลบโครงการย่อย '{$name}' เรียบร้อยแล้ว");
        header('Location: ' . Router::url("/projects/{$parentId}"));
        exit;
    }
}
