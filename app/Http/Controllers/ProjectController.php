<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\View;
use App\Core\Router;
use App\Core\Validator;
use App\Services\ProjectService;
use App\Services\AuditLogService;
use Exception;

class ProjectController
{
    public function index(): void
    {
        $filters = [
            'fiscal_year_id' => $_GET['fiscal_year_id'] ?? '',
            'department_id'  => $_GET['department_id'] ?? '',
            'category_id'    => $_GET['category_id'] ?? '',
            'status'         => $_GET['status'] ?? '',
            'search'         => trim($_GET['search'] ?? ''),
        ];

        $projects = ProjectService::getMainProjects($filters);
        $fiscalYears = \App\Services\FiscalYearService::getAll();
        $departments = Database::query("SELECT * FROM departments ORDER BY id ASC");
        $categories = Database::query("SELECT * FROM project_categories ORDER BY id ASC");

        View::render('projects.index', [
            'projects'    => $projects,
            'filters'     => $filters,
            'fiscalYears' => $fiscalYears,
            'departments' => $departments,
            'categories'  => $categories,
        ]);
    }

    public function show(string $id): void
    {
        $project = ProjectService::getProjectById((int)$id);
        if (!$project) {
            Session::flash('error', 'ไม่พบโครงการที่ระบุ');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $departments = Database::query("SELECT * FROM departments ORDER BY id ASC");
        $users = Database::query("SELECT id, name, position FROM users ORDER BY name ASC");

        View::render('projects.show', [
            'project'     => $project,
            'departments' => $departments,
            'users'       => $users,
        ]);
    }

    public function store(): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์สร้างโครงการ');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $rules = [
            'name'           => 'required|min:3|max:255',
            'fiscal_year_id' => 'required|numeric',
            'category_id'    => 'required|numeric',
            'budget'         => 'required|numeric|min:0',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date',
        ];

        if (!empty($_POST['department_id'])) {
            $rules['department_id'] = 'required|numeric';
        } else {
            $rules['responsible_person'] = 'required|min:2|max:255';
        }

        $v = Validator::make($_POST, $rules);

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $responsiblePerson = trim($_POST['responsible_person'] ?? '');
        $deptId = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
        $responsibleUserId = !empty($_POST['responsible_user_id']) ? (int)$_POST['responsible_user_id'] : null;

        if (!$deptId && $responsiblePerson) {
            $matchedDept = Database::fetch("SELECT id FROM departments WHERE name LIKE ? LIMIT 1", ["%{$responsiblePerson}%"]);
            if ($matchedDept) {
                $deptId = (int)$matchedDept['id'];
            } else {
                $matchedUser = Database::fetch("SELECT id, department_id FROM users WHERE name LIKE ? LIMIT 1", ["%{$responsiblePerson}%"]);
                if ($matchedUser) {
                    $responsibleUserId = (int)$matchedUser['id'];
                    $deptId = !empty($matchedUser['department_id']) ? (int)$matchedUser['department_id'] : 1;
                } else {
                    $user = Auth::user();
                    $deptId = !empty($user['department_id']) ? (int)$user['department_id'] : 1;
                }
            }
        }
        if (!$deptId) {
            $deptId = 1;
        }
        if (!$responsibleUserId) {
            $responsibleUserId = Auth::id() ?: 1;
        }

        try {
            $insertData = [
                'parent_id'           => null,
                'name'                => trim($_POST['name']),
                'description'         => trim($_POST['description'] ?? ''),
                'fiscal_year_id'      => (int)$_POST['fiscal_year_id'],
                'category_id'         => (int)$_POST['category_id'],
                'department_id'       => $deptId,
                'responsible_user_id' => $responsibleUserId,
                'start_date'          => $_POST['start_date'],
                'end_date'            => $_POST['end_date'],
                'budget'              => (float)$_POST['budget'],
                'disbursed_amount'    => 0.00,
                'status'              => 'not_started',
                'progress'            => 0.00,
                'progress_mode'       => 'auto',
            ];
            if (ProjectService::hasResponsiblePersonColumn()) {
                $insertData['responsible_person'] = $responsiblePerson ?: null;
            }
            $projectId = Database::insert('projects', $insertData);

            // Create initial budget record
            Database::insert('budgets', [
                'project_id'       => $projectId,
                'received_amount'  => (float)$_POST['budget'],
                'allocated_amount' => (float)$_POST['budget'],
                'disbursed_amount' => 0.00,
            ]);

            AuditLogService::log('CREATE', 'Project', $projectId, null, ['name' => $_POST['name']]);
            Session::flash('success', "บันทึกโครงการหลัก '{$_POST['name']}' เรียบร้อยแล้ว");
            header('Location: ' . Router::url("/projects/{$projectId}"));
            exit;
        } catch (Exception $e) {
            Session::flash('error', 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage());
            header('Location: ' . Router::url('/projects'));
            exit;
        }
    }

    public function update(string $id): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์แก้ไขโครงการ');
            header('Location: ' . Router::url("/projects/{$id}"));
            exit;
        }

        $projectId = (int)$id;
        $project = Database::fetch("SELECT * FROM projects WHERE id = ?", [$projectId]);
        if (!$project) {
            Session::flash('error', 'ไม่พบโครงการ');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $v = Validator::make($_POST, [
            'name'       => 'required|min:3|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            header('Location: ' . Router::url("/projects/{$projectId}"));
            exit;
        }

        $newStartDate = trim($_POST['start_date']);
        $newEndDate = trim($_POST['end_date']);

        if ($newStartDate > $newEndDate) {
            Session::flash('error', 'วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุดของโครงการ');
            header('Location: ' . Router::url("/projects/{$projectId}"));
            exit;
        }

        // Check if any subproject starts earlier than new start_date
        $earliestSub = Database::fetch("SELECT id, name, start_date FROM projects WHERE parent_id = ? AND start_date < ? ORDER BY start_date ASC LIMIT 1", [$projectId, $newStartDate]);
        if ($earliestSub) {
            $subStartThai = date('d/m/', strtotime($earliestSub['start_date'])) . (date('Y', strtotime($earliestSub['start_date'])) + 543);
            Session::flash('error', "ไม่สามารถเปลี่ยนวันเริ่มต้นโครงการหลักเป็นวันที่หลังโครงการย่อยได้ เนื่องจากมีโครงการย่อย '{$earliestSub['name']}' เริ่มต้นตั้งแต่วันที่ {$subStartThai}");
            header('Location: ' . Router::url("/projects/{$projectId}"));
            exit;
        }

        // Check if any subproject ends later than new end_date
        $latestSub = Database::fetch("SELECT id, name, end_date FROM projects WHERE parent_id = ? AND end_date > ? ORDER BY end_date DESC LIMIT 1", [$projectId, $newEndDate]);
        if ($latestSub) {
            $subEndThai = date('d/m/', strtotime($latestSub['end_date'])) . (date('Y', strtotime($latestSub['end_date'])) + 543);
            Session::flash('error', "ไม่สามารถเปลี่ยนวันสิ้นสุดโครงการหลักเป็นวันก่อนหน้าโครงการย่อยได้ เนื่องจากมีโครงการย่อย '{$latestSub['name']}' สิ้นสุดวันที่ {$subEndThai}");
            header('Location: ' . Router::url("/projects/{$projectId}"));
            exit;
        }

        $updateData = [
            'name'                => trim($_POST['name']),
            'description'         => trim($_POST['description'] ?? ''),
            'responsible_user_id' => !empty($_POST['responsible_user_id']) ? (int)$_POST['responsible_user_id'] : $project['responsible_user_id'],
            'start_date'          => $_POST['start_date'],
            'end_date'            => $_POST['end_date'],
            'notes'               => trim($_POST['notes'] ?? ''),
        ];
        if (isset($_POST['responsible_person']) && ProjectService::hasResponsiblePersonColumn()) {
            $updateData['responsible_person'] = trim($_POST['responsible_person']);
        }

        Database::update('projects', $updateData, "id = ?", [$projectId]);

        AuditLogService::log('UPDATE', 'Project', $projectId, ['name' => $project['name']], ['name' => $_POST['name']]);
        Session::flash('success', 'อัปเดตข้อมูลโครงการเรียบร้อยแล้ว');
        header('Location: ' . Router::url("/projects/{$projectId}"));
        exit;
    }

    public function delete(string $id): void
    {
        if (!Auth::isAdmin()) {
            Session::flash('error', 'เฉพาะผู้ดูแลระบบ (Administrator) เท่านั้นที่มีสิทธิ์ลบโครงการ');
            header('Location: ' . Router::url("/projects/{$id}"));
            exit;
        }

        $projectId = (int)$id;
        $project = Database::fetch("SELECT * FROM projects WHERE id = ?", [$projectId]);
        if (!$project) {
            Session::flash('error', 'ไม่พบโครงการ');
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        $name = $project['name'];
        Database::transaction(function () use ($projectId) {
            // Find all sub-project IDs
            $subs = Database::query("SELECT id FROM projects WHERE parent_id = ?", [$projectId]);
            $subIds = array_column($subs, 'id');
            $allIds = array_merge([$projectId], $subIds);

            if (!empty($allIds)) {
                $placeholders = implode(',', array_fill(0, count($allIds), '?'));
                Database::execute("DELETE FROM activities WHERE project_id IN ({$placeholders})", $allIds);
                Database::execute("DELETE FROM budget_disbursements WHERE project_id IN ({$placeholders})", $allIds);
                Database::execute("DELETE FROM attachments WHERE project_id IN ({$placeholders})", $allIds);
                Database::execute("DELETE FROM budgets WHERE project_id IN ({$placeholders})", $allIds);
            }

            // Delete sub-projects and main project
            Database::execute("DELETE FROM projects WHERE parent_id = ?", [$projectId]);
            Database::execute("DELETE FROM projects WHERE id = ?", [$projectId]);
        });

        AuditLogService::log('DELETE', 'Project', $projectId, ['name' => $name]);

        Session::flash('success', "ลบโครงการ '{$name}' และโครงการย่อยทั้งหมดเรียบร้อยแล้ว");
        header('Location: ' . Router::url('/projects'));
        exit;
    }
}
