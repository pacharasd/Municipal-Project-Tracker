<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Core\Router;
use App\Core\Session;

class AuditLogController
{
    public function index(): void
    {
        if (!Auth::isAdmin()) {
            Session::flash('error', 'เฉพาะผู้ดูแลระบบ (Administrator) เท่านั้นที่สามารถดู Audit Log ได้');
            header('Location: ' . Router::url('/dashboard'));
            exit;
        }

        $logs = Database::query(
            "SELECT a.*, u.name as user_name, u.email as user_email, r.display_name as role_label 
             FROM audit_logs a 
             LEFT JOIN users u ON a.user_id = u.id 
             LEFT JOIN roles r ON u.role_id = r.id 
             ORDER BY a.id DESC LIMIT 100"
        );

        View::render('audit_logs.index', [
            'logs' => $logs,
        ]);
    }
}
