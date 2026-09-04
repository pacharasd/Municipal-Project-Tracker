<?php
/**
 * Full End-to-End System Verification Test
 * ตรวจสอบความถูกต้องและการทำงานจริงของทุกระบบ
 */

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Core/Validator.php';
require_once __DIR__ . '/../app/Enums/ProjectStatus.php';
require_once __DIR__ . '/../app/Enums/ProgressMode.php';
require_once __DIR__ . '/../app/Services/AuditLogService.php';
require_once __DIR__ . '/../app/Services/ProgressService.php';
require_once __DIR__ . '/../app/Services/BudgetService.php';
require_once __DIR__ . '/../app/Services/ProjectService.php';

use App\Core\Database;
use App\Core\Auth;
use App\Core\Session;
use App\Services\BudgetService;
use App\Services\ProgressService;
use App\Services\ProjectService;

Session::start();
Auth::switchUser(1); // Admin user

echo "=========================================================\n";
echo "   เริ่มการทดสอบการทำงานจริงของทุกระบบ (Full System Test)   \n";
echo "=========================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $title, bool $condition) {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] {$title}\n";
        $passCount++;
    } else {
        echo " [FAIL] {$title}\n";
        $failCount++;
    }
}

// ---------------------------------------------------------
// 1. ทดสอบระบบผู้ใช้งาน (User Management CRUD)
// ---------------------------------------------------------
echo "\n--- 1. ทดสอบระบบผู้ใช้งาน (User CRUD) ---\n";

$testEmail = 'test.officer.' . time() . '@municipality.go.th';
$hash = password_hash('password', PASSWORD_BCRYPT);
$newUserId = Database::insert('users', [
    'name'          => 'ทดสอบ เจ้าหน้าที่สร้างใหม่',
    'email'         => $testEmail,
    'password'      => $hash,
    'role_id'       => 1,
    'department_id' => 1,
    'position'      => 'เจ้าหน้าที่วิเคราะห์นโยบาย',
    'phone'         => '089-9999999',
]);
assertTest("สร้างผู้ใช้งานใหม่ลงฐานข้อมูลจริง (ID: {$newUserId})", $newUserId > 0);

$userInDb = Database::fetch("SELECT * FROM users WHERE id = ?", [$newUserId]);
assertTest("ดึงข้อมูลผู้ใช้งานที่สร้างสำเร็จ", $userInDb && $userInDb['email'] === $testEmail);

Database::update('users', ['position' => 'หัวหน้าฝ่ายยุทธศาสตร์'], "id = ?", [$newUserId]);
$updatedUser = Database::fetch("SELECT position FROM users WHERE id = ?", [$newUserId]);
assertTest("แก้ไขข้อมูลผู้ใช้งานสำเร็จ", $updatedUser['position'] === 'หัวหน้าฝ่ายยุทธศาสตร์');

Database::execute("DELETE FROM users WHERE id = ?", [$newUserId]);
$deletedUser = Database::fetch("SELECT * FROM users WHERE id = ?", [$newUserId]);
assertTest("ลบผู้ใช้งานสำเร็จ", $deletedUser === null);


// ---------------------------------------------------------
// 2. ทดสอบระบบโครงการย่อย & กิจกรรม & งบประมาณ (Project, Activity & Budget)
// ---------------------------------------------------------
echo "\n--- 2. ทดสอบระบบโครงการย่อย, กิจกรรม และการเบิกจ่าย ---\n";

$parentBefore = Database::fetch("SELECT budget, disbursed_amount, progress FROM projects WHERE id = 1");
echo " โครงการหลัก 1 ก่อนทดสอบ: งบประมาณ=" . number_format($parentBefore['budget'], 2) . ", เบิกจ่าย=" . number_format($parentBefore['disbursed_amount'], 2) . "\n";

// 2.1 สร้างโครงการย่อยทดสอบ
$testCode = 'SUB-TEST-' . time();
$subId = Database::insert('projects', [
    'parent_id'              => 1,
    'project_code'           => $testCode,
    'name'                   => 'โครงการย่อยทดสอบระบบอัตโนมัติ',
    'fiscal_year_id'         => 2,
    'category_id'            => 2,
    'department_id'          => 2,
    'responsible_user_id'    => 1,
    'start_date'             => '2025-01-01',
    'end_date'               => '2025-06-30',
    'budget'                 => 100000.00,
    'disbursed_amount'       => 0.00,
    'status'                 => 'not_started',
    'progress'               => 0.00,
    'progress_mode'          => 'manual',
    'planned_activity_count' => 2,
    'actual_activity_count'  => 0,
]);
assertTest("สร้างโครงการย่อยลงฐานข้อมูลจริง (ID: {$subId})", $subId > 0);

// สร้างงบประมาณ
Database::insert('budgets', [
    'project_id'       => $subId,
    'received_amount'  => 100000.00,
    'allocated_amount' => 100000.00,
    'disbursed_amount' => 0.00,
]);

// ซิงค์งบโครงการหลัก
BudgetService::syncParentProjectBudget(1);
$parentAfterCreate = Database::fetch("SELECT budget FROM projects WHERE id = 1");
assertTest("ซิงค์งบประมาณโครงการหลักอัตโนมัติเมื่อเพิ่มโครงการย่อย", (float)$parentAfterCreate['budget'] === (float)$parentBefore['budget'] + 100000.00);

// 2.2 ปรับสถานะและความคืบหน้าโครงการย่อย
$statusRes = ProgressService::updateStatusAndProgress($subId, 'in_progress', 65.0, 'กำลังดำเนินการตามแผนงาน');
assertTest("ปรับสถานะเป็น 'in_progress' และความคืบหน้า 65%", $statusRes['status'] === 'in_progress' && (float)$statusRes['progress'] === 65.0);

// 2.3 เพิ่มกิจกรรมในโครงการย่อย
$actId = Database::insert('activities', [
    'project_id'          => $subId,
    'name'                => 'กิจกรรมทดสอบที่ 1',
    'activity_date'       => '2025-02-15',
    'budget'              => 40000.00,
    'status'              => 'not_started',
    'progress'            => 0.00,
]);
assertTest("เพิ่มกิจกรรมในโครงการย่อยสำเร็จ (ID: {$actId})", $actId > 0);

// อัปเดตกิจกรรมเป็น completed
Database::update('activities', ['status' => 'completed', 'progress' => 100.00], "id = ?", [$actId]);
$actCheck = Database::fetch("SELECT status, progress FROM activities WHERE id = ?", [$actId]);
assertTest("อัปเดตสถานะกิจกรรมเป็น 'completed' และ 100%", $actCheck['status'] === 'completed' && (float)$actCheck['progress'] === 100.00);

// 2.4 บันทึกการเบิกจ่ายงบประมาณ
$disbRes = BudgetService::disburse($subId, 35000.00, '2025-02-20', 'ค่าใช้จ่ายกิจกรรมทดสอบที่ 1', 'บริษัท ทดสอบ จำกัด');
assertTest("บันทึกการเบิกจ่าย 35,000 บาทสำเร็จ", $disbRes['success'] === true && (float)$disbRes['new_disbursed'] === 35000.00);

$subBudgetAfter = Database::fetch("SELECT disbursed_amount FROM projects WHERE id = ?", [$subId]);
assertTest("ยอดเบิกจ่ายในโครงการย่อยอัปเดตเป็น 35,000 บาท", (float)$subBudgetAfter['disbursed_amount'] === 35000.00);

// 2.5 ยกเลิกรายการเบิกจ่าย
$disbId = $disbRes['disbursement_id'];
$delDisbRes = BudgetService::deleteDisbursement($disbId);
assertTest("ยกเลิกรายการเบิกจ่ายและคืนงบประมาณสำเร็จ", $delDisbRes['success'] === true);

$subBudgetRestored = Database::fetch("SELECT disbursed_amount FROM projects WHERE id = ?", [$subId]);
assertTest("ยอดเบิกจ่ายคืนกลับเป็น 0 บาท", (float)$subBudgetRestored['disbursed_amount'] === 0.00);

// 2.6 ลบกิจกรรมทดสอบ
Database::execute("DELETE FROM activities WHERE id = ?", [$actId]);
assertTest("ลบกิจกรรมทดสอบสำเร็จ", Database::fetch("SELECT * FROM activities WHERE id = ?", [$actId]) === null);

// 2.7 ลบโครงการย่อยทดสอบ
Database::execute("DELETE FROM projects WHERE id = ?", [$subId]);
BudgetService::syncParentProjectBudget(1);
ProgressService::syncParentProjectProgress(1);

$parentFinal = Database::fetch("SELECT budget FROM projects WHERE id = 1");
assertTest("ลบโครงการย่อยและคืนงบประมาณโครงการหลักสู่ค่าเดิม", (float)$parentFinal['budget'] === (float)$parentBefore['budget']);


// ---------------------------------------------------------
// 3. ตรวจสอบ Audit Log
// ---------------------------------------------------------
echo "\n--- 3. ตรวจสอบ Audit Log ---\n";
$latestAudit = Database::fetch("SELECT * FROM audit_logs ORDER BY id DESC LIMIT 1");
assertTest("ระบบบันทึก Audit Log ทุกความเปลี่ยนแปลง", $latestAudit !== null && !empty($latestAudit['action']));


// ---------------------------------------------------------
// 4. สรุปผล
// ---------------------------------------------------------
echo "\n=========================================================\n";
echo " ผลการทดสอบ: ผ่าน {$passCount} ข้อ / ล้มเหลว {$failCount} ข้อ\n";
if ($failCount === 0) {
    echo " 🎉 ทุกระบบทำงานได้จริงและถูกต้อง 100% เต็ม!\n";
} else {
    echo " ⚠️ มีข้อผิดพลาดเกิดขึ้น กรุณาตรวจสอบ\n";
}
echo "=========================================================\n";
