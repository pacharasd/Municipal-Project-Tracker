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
            'status'         => $_GET['status'] ?? '',
            'search'         => trim($_GET['search'] ?? ''),
        ];

        $projects = ProjectService::getMainProjects($filters);
        $fiscalYears = Database::query("SELECT * FROM fiscal_years ORDER BY year DESC");
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

        $v = Validator::make($_POST, [
            'name'           => 'required|min:3|max:255',
            'project_code'   => 'required|min:2|max:50',
            'fiscal_year_id' => 'required|numeric',
            'category_id'    => 'required|numeric',
            'department_id'  => 'required|numeric',
            'budget'         => 'required|numeric|min:0',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date',
        ]);

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        // Check duplicate code
        $code = trim($_POST['project_code']);
        if (Database::fetchColumn("SELECT COUNT(*) FROM projects WHERE project_code = ?", [$code]) > 0) {
            Session::flash('error', "รหัสโครงการ '{$code}' มีอยู่ในระบบแล้ว กรุณาใช้รหัสอื่น");
            header('Location: ' . Router::url('/projects'));
            exit;
        }

        try {
            $projectId = Database::insert('projects', [
                'parent_id'           => null,
                'project_code'        => $code,
                'name'                => trim($_POST['name']),
                'description'         => trim($_POST['description'] ?? ''),
                'fiscal_year_id'      => (int)$_POST['fiscal_year_id'],
                'category_id'         => (int)$_POST['category_id'],
                'department_id'       => (int)$_POST['department_id'],
                'responsible_user_id' => !empty($_POST['responsible_user_id']) ? (int)$_POST['responsible_user_id'] : Auth::id(),
                'start_date'          => $_POST['start_date'],
                'end_date'            => $_POST['end_date'],
                'budget'              => (float)$_POST['budget'],
                'disbursed_amount'    => 0.00,
                'status'              => 'not_started',
                'progress'            => 0.00,
                'progress_mode'       => 'auto',
            ]);

            // Create initial budget record
            Database::insert('budgets', [
                'project_id'       => $projectId,
                'received_amount'  => (float)$_POST['budget'],
                'allocated_amount' => (float)$_POST['budget'],
                'disbursed_amount' => 0.00,
            ]);

            AuditLogService::log('CREATE', 'Project', $projectId, null, ['code' => $code, 'name' => $_POST['name']]);
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

        Database::update('projects', [
            'name'                => trim($_POST['name']),
            'description'         => trim($_POST['description'] ?? ''),
            'responsible_user_id' => !empty($_POST['responsible_user_id']) ? (int)$_POST['responsible_user_id'] : $project['responsible_user_id'],
            'start_date'          => $_POST['start_date'],
            'end_date'            => $_POST['end_date'],
            'notes'               => trim($_POST['notes'] ?? ''),
        ], "id = ?", [$projectId]);

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
        Database::execute("DELETE FROM projects WHERE id = ?", [$projectId]);
        AuditLogService::log('DELETE', 'Project', $projectId, ['name' => $name]);

        Session::flash('success', "ลบโครงการ '{$name}' ออกจากระบบเรียบร้อยแล้ว");
        header('Location: ' . Router::url('/projects'));
        exit;
    }
}
