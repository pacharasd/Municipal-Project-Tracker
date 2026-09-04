<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\View;
use App\Core\Router;
use App\Core\Validator;
use App\Services\BudgetService;
use Exception;

class BudgetController
{
    public function index(): void
    {
        $sql = "SELECT p.id, p.project_code, p.name, p.budget, p.disbursed_amount, 
                       (p.budget - p.disbursed_amount) as remaining_amount,
                       CASE WHEN p.budget > 0 THEN ROUND((p.disbursed_amount / p.budget) * 100, 2) ELSE 0 END as disbursement_percentage,
                       d.name as department_name, f.year as fiscal_year, p.status
                FROM projects p
                LEFT JOIN departments d ON p.department_id = d.id
                LEFT JOIN fiscal_years f ON p.fiscal_year_id = f.id
                WHERE p.parent_id IS NULL
                ORDER BY p.budget DESC";

        $mainBudgets = Database::query($sql);

        $recentDisbursements = Database::query(
            "SELECT d.*, p.name as project_name, p.project_code, u.name as creator_name 
             FROM budget_disbursements d 
             LEFT JOIN projects p ON d.project_id = p.id 
             LEFT JOIN users u ON d.created_by = u.id 
             ORDER BY d.disbursement_date DESC LIMIT 15"
        );

        $subProjects = Database::query(
            "SELECT id, project_code, name, budget, disbursed_amount, (budget - disbursed_amount) as remaining 
             FROM projects WHERE parent_id IS NOT NULL ORDER BY name ASC"
        );

        View::render('budgets.index', [
            'mainBudgets'         => $mainBudgets,
            'recentDisbursements' => $recentDisbursements,
            'subProjects'         => $subProjects,
        ]);
    }

    public function disburse(): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์บันทึกการเบิกจ่ายงบประมาณ');
            header('Location: ' . Router::url('/budgets'));
            exit;
        }

        $v = Validator::make($_POST, [
            'project_id'        => 'required|numeric',
            'amount'            => 'required|numeric|min:1',
            'disbursement_date' => 'required|date',
            'description'       => 'required|min:3|max:255',
        ]);

        $projectId = (int)($_POST['project_id'] ?? 0);

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            $redirect = $_POST['redirect'] ?? Router::url("/sub-projects/{$projectId}");
            header('Location: ' . $redirect);
            exit;
        }

        try {
            $amount = (float)$_POST['amount'];
            $date = $_POST['disbursement_date'];
            $desc = trim($_POST['description']);
            $recipient = trim($_POST['recipient'] ?? '');

            // Handle file upload if any
            $evidenceFile = null;
            if (!empty($_FILES['evidence_file']['name'])) {
                $ext = strtolower(pathinfo($_FILES['evidence_file']['name'], PATHINFO_EXTENSION));
                $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
                if (in_array($ext, $allowed)) {
                    $newFilename = 'disb_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $destPath = dirname(__DIR__, 2) . '/public/uploads/' . $newFilename;
                    if (!is_dir(dirname($destPath))) {
                        mkdir(dirname($destPath), 0777, true);
                    }
                    if (move_uploaded_file($_FILES['evidence_file']['tmp_name'], $destPath)) {
                        $evidenceFile = $newFilename;
                    }
                }
            }

            $res = BudgetService::disburse($projectId, $amount, $date, $desc, $recipient, $evidenceFile);
            Session::flash('success', "บันทึกการเบิกจ่ายจำนวน " . number_format($amount, 2) . " บาท เรียบร้อยแล้ว (คงเหลือ " . number_format($res['remaining'], 2) . " บาท)");
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        $redirect = $_POST['redirect'] ?? Router::url("/sub-projects/{$projectId}");
        header('Location: ' . $redirect);
        exit;
    }

    public function deleteDisbursement(string $id): void
    {
        if (!Auth::isAdmin()) {
            Session::flash('error', 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถยกเลิกรายการเบิกจ่ายได้');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/budgets')));
            exit;
        }

        $disbId = (int)$id;
        try {
            $res = BudgetService::deleteDisbursement($disbId);
            Session::flash('success', 'ยกเลิกรายการเบิกจ่ายและคืนยอดงบประมาณเรียบร้อยแล้ว');
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/budgets')));
        exit;
    }
}
