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
            Session::flash('error', 'ไม่พบกิจกรรมหลักที่ระบุ');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $users = Database::query("SELECT u.id, u.name, u.position FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name != 'executive' ORDER BY u.name ASC");

        View::render('sub_projects.show', [
            'project' => $project,
            'users'   => $users,
        ]);
    }

    public function store(): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์เพิ่มกิจกรรมหลัก');
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
            Session::flash('error', 'วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุดของกิจกรรมหลัก');
            header('Location: ' . Router::url("/projects/{$parentId}"));
            exit;
        }

        $parentStartDate = !empty($parent['start_date']) ? $parent['start_date'] : null;
        $parentEndDate = !empty($parent['end_date']) ? $parent['end_date'] : null;

        $confirmOutside = !empty($_POST['confirm_outside_dates']) && $_POST['confirm_outside_dates'] === '1';
        $isOutsideDateBounds = false;
        $outsideReasons = [];

        if ($parentStartDate && $startDate && $startDate < $parentStartDate) {
            $formattedParentStart = date('d/m/', strtotime($parentStartDate)) . (date('Y', strtotime($parentStartDate)) + 543);
            if (!$confirmOutside) {
                Session::flash('error', "วันที่เริ่มต้นของกิจกรรมหลักต้องเท่ากับหรือมากกว่าวันที่เริ่มต้นของโครงการหลัก ({$formattedParentStart}) หรือต้องได้รับการยืนยันตาม Rule #18");
                header('Location: ' . Router::url("/projects/{$parentId}"));
                exit;
            }
            $isOutsideDateBounds = true;
            $outsideReasons[] = "เริ่มก่อนโครงการหลัก ({$formattedParentStart})";
        }

        if ($parentEndDate && $endDate && $endDate > $parentEndDate) {
            $formattedParentEnd = date('d/m/', strtotime($parentEndDate)) . (date('Y', strtotime($parentEndDate)) + 543);
            if (!$confirmOutside) {
                Session::flash('error', "วันที่สิ้นสุดของกิจกรรมหลักต้องไม่เกินวันที่สิ้นสุดของโครงการหลัก ({$formattedParentEnd}) หรือต้องได้รับการยืนยันตาม Rule #18");
                header('Location: ' . Router::url("/projects/{$parentId}"));
                exit;
            }
            $isOutsideDateBounds = true;
            $outsideReasons[] = "สิ้นสุดหลังโครงการหลัก ({$formattedParentEnd})";
        }

        $budget = (float)$_POST['budget'];
        $parentBudget = (float)($parent['budget'] ?? 0);
        $totalAllocated = (float)Database::fetchColumn(
            "SELECT COALESCE(SUM(budget), 0) FROM projects WHERE parent_id = ?",
            [$parentId]
        );
        $remainingBudget = max(0, $parentBudget - $totalAllocated);
        if ($budget > $remainingBudget) {
            Session::flash('error', "งบประมาณกิจกรรมหลัก (" . number_format($budget, 2) . " บาท) เกินกว่างบประมาณคงเหลือของโครงการหลักที่สามารถจัดสรรได้ (คงเหลือ: " . number_format($remainingBudget, 2) . " บาท)");
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

            // ป้องกันการมอบหมายโครงการให้ผู้บริหาร (Role Segregation & Least Privilege)
            if ($responsibleUserId) {
                $userRoleCheck = Database::fetch("SELECT r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?", [$responsibleUserId]);
                if ($userRoleCheck && $userRoleCheck['role_name'] === 'executive') {
                    Session::flash('error', 'ไม่สามารถมอบหมายโครงการให้ผู้ใช้งานในบทบาท "ผู้บริหาร" ได้ (ผู้บริหารมีหน้าที่กำกับดูแลภาพรวม)');
                    header('Location: ' . Router::url("/projects/{$parentId}"));
                    exit;
                }
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
                'progress_mode'          => 'auto',
            ]);

            // Sync parent budget & progress
            BudgetService::syncParentProjectBudget($parentId);
            ProgressService::syncParentProjectProgress($parentId);

            AuditLogService::log('CREATE_SUBPROJECT', 'Project', $subId, null, ['name' => $_POST['name']]);
            if ($isOutsideDateBounds) {
                AuditLogService::log(
                    'CONFIRM_DATE_OUTSIDE_PARENT',
                    'Project',
                    $subId,
                    null,
                    [
                        'action'            => 'บันทึกกิจกรรมหลักนอกกรอบเวลาโครงการหลักตาม Rule #18',
                        'sub_project_id'    => $subId,
                        'parent_id'         => $parentId,
                        'sub_start_date'    => $startDate,
                        'sub_end_date'      => $endDate,
                        'parent_start_date' => $parentStartDate,
                        'parent_end_date'   => $parentEndDate,
                        'reasons'           => implode(', ', $outsideReasons),
                    ]
                );
            }
            Session::flash('success', "เพิ่มกิจกรรมหลัก '{$_POST['name']}' เรียบร้อยแล้ว");
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

        try {
            $res = ProgressService::updateStatusAndProgress($subId);
            $statusLabel = \App\Enums\ProjectStatus::labelFor($res['status']);
            Session::flash('success', "สถานะโครงการคำนวณจากกิจกรรมย่อยเป็น '{$statusLabel}' เรียบร้อยแล้ว");
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
            Session::flash('success', 'บันทึกการแก้ไขปัญหาเรียบร้อยแล้ว (เปลี่ยนสถานะกิจกรรมย่อยเป็นกำลังดำเนินการ)');
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        header('Location: ' . Router::url("/sub-projects/{$subId}"));
        exit;
    }

    public function update(string $id): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์แก้ไขกิจกรรมหลัก');
            header('Location: ' . Router::url("/sub-projects/{$id}"));
            exit;
        }

        $subId = (int)$id;
        $project = Database::fetch("SELECT * FROM projects WHERE id = ? AND parent_id IS NOT NULL", [$subId]);
        if (!$project) {
            Session::flash('error', 'ไม่พบกิจกรรมหลัก');
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
            Session::flash('error', 'วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุดของกิจกรรมหลัก');
            header('Location: ' . Router::url("/sub-projects/{$subId}"));
            exit;
        }

        $parentStartDate = !empty($parent['start_date']) ? $parent['start_date'] : null;
        $parentEndDate = !empty($parent['end_date']) ? $parent['end_date'] : null;

        $confirmOutside = !empty($_POST['confirm_outside_dates']) && $_POST['confirm_outside_dates'] === '1';
        $isOutsideDateBounds = false;
        $outsideReasons = [];

        if ($parentStartDate && $startDate && $startDate < $parentStartDate) {
            $formattedParentStart = date('d/m/', strtotime($parentStartDate)) . (date('Y', strtotime($parentStartDate)) + 543);
            if (!$confirmOutside) {
                Session::flash('error', "วันที่เริ่มต้นของกิจกรรมหลักต้องเท่ากับหรือมากกว่าวันที่เริ่มต้นของโครงการหลัก ({$formattedParentStart}) หรือต้องได้รับการยืนยันตาม Rule #18");
                header('Location: ' . Router::url("/sub-projects/{$subId}"));
                exit;
            }
            $isOutsideDateBounds = true;
            $outsideReasons[] = "เริ่มก่อนโครงการหลัก ({$formattedParentStart})";
        }

        if ($parentEndDate && $endDate && $endDate > $parentEndDate) {
            $formattedParentEnd = date('d/m/', strtotime($parentEndDate)) . (date('Y', strtotime($parentEndDate)) + 543);
            if (!$confirmOutside) {
                Session::flash('error', "วันที่สิ้นสุดของกิจกรรมหลักต้องไม่เกินวันที่สิ้นสุดของโครงการหลัก ({$formattedParentEnd}) หรือต้องได้รับการยืนยันตาม Rule #18");
                header('Location: ' . Router::url("/sub-projects/{$subId}"));
                exit;
            }
            $isOutsideDateBounds = true;
            $outsideReasons[] = "สิ้นสุดหลังโครงการหลัก ({$formattedParentEnd})";
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
            Session::flash('error', "งบประมาณกิจกรรมหลัก (" . number_format($newBudget, 2) . " บาท) เกินกว่างบประมาณคงเหลือของโครงการหลักที่สามารถจัดสรรได้ (สามารถจัดสรรได้สูงสุด: " . number_format($maxAllowed, 2) . " บาท)");
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

        // ป้องกันการมอบหมายโครงการให้ผู้บริหาร (Role Segregation & Least Privilege)
        if ($responsibleUserId) {
            $userRoleCheck = Database::fetch("SELECT r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?", [$responsibleUserId]);
            if ($userRoleCheck && $userRoleCheck['role_name'] === 'executive') {
                Session::flash('error', 'ไม่สามารถมอบหมายโครงการให้ผู้ใช้งานในบทบาท "ผู้บริหาร" ได้ (ผู้บริหารมีหน้าที่กำกับดูแลภาพรวม)');
                header('Location: ' . Router::url("/sub-projects/{$subId}"));
                exit;
            }
        }

        // Data Integrity: จำนวนครั้งกิจกรรมย่อยที่วางแผนไว้ต้องไม่น้อยกว่าจำนวนกิจกรรมย่อยที่มีอยู่จริง
        $newPlannedCount = !empty($_POST['planned_activity_count']) ? (int)$_POST['planned_activity_count'] : (int)$project['planned_activity_count'];
        $existingActivityCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM activities WHERE project_id = ?", [$subId]);
        if ($newPlannedCount < $existingActivityCount) {
            Session::flash('error', "จำนวนครั้งกิจกรรมย่อยที่วางแผนไว้ ({$newPlannedCount} ครั้ง) ต้องไม่น้อยกว่าจำนวนกิจกรรมย่อยที่มีอยู่แล้วจริงในระบบ ({$existingActivityCount} รายการ)");
            header('Location: ' . Router::url("/sub-projects/{$subId}"));
            exit;
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
            'planned_activity_count' => $newPlannedCount,
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
        if ($isOutsideDateBounds) {
            AuditLogService::log(
                'CONFIRM_DATE_OUTSIDE_PARENT',
                'Project',
                $subId,
                ['start_date' => $project['start_date'], 'end_date' => $project['end_date']],
                [
                    'action'            => 'แก้ไขกิจกรรมหลักให้มีวันที่นอกกรอบเวลาโครงการหลักตาม Rule #18',
                    'sub_project_id'    => $subId,
                    'parent_id'         => $project['parent_id'],
                    'sub_start_date'    => $startDate,
                    'sub_end_date'      => $endDate,
                    'parent_start_date' => $parentStartDate,
                    'parent_end_date'   => $parentEndDate,
                    'reasons'           => implode(', ', $outsideReasons),
                ]
            );
        }
        Session::flash('success', 'อัปเดตข้อมูลกิจกรรมหลักเรียบร้อยแล้ว');
        header('Location: ' . Router::url("/sub-projects/{$subId}"));
        exit;
    }

    public function delete(string $id): void
    {
        if (!Auth::canManageSubProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์ลบกิจกรรมหลัก');
            header('Location: ' . Router::url("/sub-projects/{$id}"));
            exit;
        }

        $subId = (int)$id;
        $project = Database::fetch("SELECT * FROM projects WHERE id = ? AND parent_id IS NOT NULL", [$subId]);
        if (!$project) {
            Session::flash('error', 'ไม่พบกิจกรรมหลัก');
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
        Session::flash('success', "ลบกิจกรรมหลัก '{$name}' เรียบร้อยแล้ว");
        header('Location: ' . Router::url("/projects/{$parentId}"));
        exit;
    }
}
