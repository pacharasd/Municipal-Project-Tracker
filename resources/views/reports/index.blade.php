<?php
ob_start();
use App\Core\Router;
use App\Enums\ProjectStatus;

$title = 'ระบบรายงานโครงการและงบประมาณ - Municipal Project Tracker';

// Calculate totals from filtered dataset
$totalBudget = array_sum(array_column($projects, 'budget'));
$totalDisbursed = array_sum(array_column($projects, 'disbursed_amount'));
$totalRemaining = $totalBudget - $totalDisbursed;
$avgProgress = count($projects) > 0 ? round(array_sum(array_column($projects, 'progress')) / count($projects), 1) : 0;
?>

<style>
@media print {
    /* Hide non-printable elements */
    header, aside, #role-switcher-banner, .no-print, nav, button {
        display: none !important;
    }
    main {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    body {
        background: #fff !important;
        color: #000 !important;
        font-size: 11pt !important;
    }
    .print-only {
        display: block !important;
    }
    table {
        page-break-inside: auto;
        border-collapse: collapse !important;
        width: 100% !important;
    }
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    th, td {
        border: 1px solid #ccc !important;
        padding: 6px 8px !important;
    }
}
.print-only {
    display: none;
}
</style>

<div class="space-y-6">

    <!-- Official Print Header (Visible only when printed) -->
    <div class="print-only text-center mb-6 pb-4 border-b-2 border-slate-900">
        <h1 class="text-xl font-bold">รายงานติดตามความก้าวหน้าโครงการและงบประมาณเทศบาล</h1>
        <p class="text-sm mt-1">ข้อมูล ณ วันที่ <?= date('d/m/Y H:i น.') ?></p>
        <p class="text-xs text-slate-600 mt-1">
            <?php if (!empty($fiscalYearId)): ?>ปีงบประมาณ: <?= htmlspecialchars($fiscalYearId) ?> | <?php endif; ?>
            <?php if (!empty($status)): ?>สถานะ: <?= htmlspecialchars($status) ?> | <?php endif; ?>
            พิมพ์จากระบบสารสนเทศติดตามและบริหารโครงการเทศบาล
        </p>
    </div>

    <!-- Page Header (Screen) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">ระบบรายงานและวิเคราะห์โครงการ</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">สรุปรายงานผลการดำเนินงาน งบประมาณ และสถานะความสำเร็จตามเกณฑ์มาตรฐาน</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <!-- Print Button -->
            <?php 
                $exportQuery = http_build_query([
                    'fiscal_year_id' => $fiscalYearId,
                    'department_id'  => $departmentId,
                    'status'         => $status
                ]);
            ?>
            <a href="<?= Router::url('/reports/print?' . $exportQuery) ?>" target="_blank"
               class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 text-slate-700 dark:text-slate-200 font-medium rounded-xl shadow-sm transition-all text-sm">
                <i data-lucide="printer" class="w-4 h-4 text-slate-500"></i>
                <span>พิมพ์รายงานราชการ (Official Form)</span>
            </a>

            <!-- Export CSV Button -->
            <?php 
                $exportQuery = http_build_query([
                    'fiscal_year_id' => $fiscalYearId,
                    'department_id'  => $departmentId,
                    'status'         => $status
                ]);
            ?>
            <a href="<?= Router::url('/reports/export-csv?' . $exportQuery) ?>" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-medium rounded-xl shadow-sm hover:shadow-md transition-all text-sm">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>ส่งออก CSV (Excel UTF-8)</span>
            </a>
        </div>
    </div>

    <!-- Filter Card (Screen) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm no-print">
        <form action="<?= Router::url('/reports') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <!-- Fiscal Year -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">ปีงบประมาณ</label>
                <select name="fiscal_year_id" 
                        class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                    <option value="">-- ทั้งหมดทุกปีงบประมาณ --</option>
                    <?php foreach ($fiscalYears as $fy): ?>
                        <option value="<?= $fy['id'] ?>" <?= $fiscalYearId == $fy['id'] ? 'selected' : '' ?>>
                            ปี <?= $fy['year'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Department -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">หน่วยงาน / สำนัก / กอง</label>
                <select name="department_id" 
                        class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                    <option value="">-- ทุกหน่วยงาน --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= $departmentId == $dept['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">สถานะโครงการ</label>
                <select name="status" 
                        class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                    <option value="">-- ทุกสถานะ --</option>
                    <option value="ยังไม่เริ่มดำเนินการ" <?= $status === 'ยังไม่เริ่มดำเนินการ' ? 'selected' : '' ?>>ยังไม่เริ่มดำเนินการ</option>
                    <option value="กำลังดำเนินการ" <?= $status === 'กำลังดำเนินการ' ? 'selected' : '' ?>>กำลังดำเนินการ</option>
                    <option value="เสร็จสิ้น" <?= $status === 'เสร็จสิ้น' ? 'selected' : '' ?>>เสร็จสิ้น</option>
                    <option value="มีปัญหา" <?= $status === 'มีปัญหา' ? 'selected' : '' ?>>มีปัญหา</option>
                    <option value="ยกเลิก" <?= $status === 'ยกเลิก' ? 'selected' : '' ?>>ยกเลิก</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl text-sm shadow-sm transition-colors flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>กรองข้อมูล</span>
                </button>
                <a href="<?= Router::url('/reports') ?>" 
                   class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-xl text-sm transition-colors text-center"
                   title="ล้างตัวกรอง">
                    รีเซ็ต
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Metrics for Report -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">จำนวนโครงการที่รายงาน</span>
            <div class="text-xl font-bold text-slate-900 dark:text-white mt-1"><?= number_format(count($projects)) ?> โครงการ</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">งบประมาณรวมตามเกณฑ์</span>
            <div class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">฿<?= number_format($totalBudget, 2) ?></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">ยอดเบิกจ่ายสะสม</span>
            <div class="text-xl font-bold text-purple-600 dark:text-purple-400 mt-1">฿<?= number_format($totalDisbursed, 2) ?></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">ความคืบหน้าเฉลี่ย</span>
            <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1"><?= $avgProgress ?>%</div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between no-print">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="table" class="w-4 h-4 text-indigo-600"></i>
                ตารางข้อมูลรายงานความคืบหน้าโครงการ
            </h2>
            <span class="text-xs text-slate-500 dark:text-slate-400">พบ <?= count($projects) ?> รายการ</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/70 text-slate-600 dark:text-slate-400 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-3 w-10 text-center">#</th>
                        <th class="py-3 px-3">รหัสโครงการ</th>
                        <th class="py-3 px-3">ชื่อโครงการ / ระดับ</th>
                        <th class="py-3 px-3">สำนัก / กอง</th>
                        <th class="py-3 px-3 text-center">ปีงบ</th>
                        <th class="py-3 px-3 text-right">งบประมาณ (บาท)</th>
                        <th class="py-3 px-3 text-right">เบิกจ่าย (บาท)</th>
                        <th class="py-3 px-3 text-right">คงเหลือ (บาท)</th>
                        <th class="py-3 px-3 text-center w-28">ความคืบหน้า</th>
                        <th class="py-3 px-3 text-center">สถานะ</th>
                        <th class="py-3 px-3">ผู้รับผิดชอบ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if (empty($projects)): ?>
                        <tr>
                            <td colspan="11" class="py-8 text-center text-slate-400">
                                ไม่พบข้อมูลโครงการตามเงื่อนไขที่กำหนด
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($projects as $idx => $p): 
                            $isParent = empty($p['parent_id']);
                            $remaining = $p['budget'] - $p['disbursed_amount'];
                            $badgeClass = ProjectStatus::badgeClass($p['status']);
                        ?>
                            <tr class="hover:bg-slate-50/75 dark:hover:bg-slate-800/40 transition-colors <?= $isParent ? 'bg-slate-50/30 dark:bg-slate-800/20 font-medium' : '' ?>">
                                <td class="py-3 px-3 text-center text-slate-400 font-mono"><?= $idx + 1 ?></td>
                                <td class="py-3 px-3 font-mono font-semibold text-slate-700 dark:text-slate-300">
                                    <?= htmlspecialchars($p['project_code']) ?>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-medium text-slate-900 dark:text-white">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        <?= $isParent ? '<span class="text-blue-600 dark:text-blue-400 font-semibold">[โครงการหลัก]</span>' : 'โครงการย่อยภายใต้: ' . htmlspecialchars($p['parent_name'] ?? '-') ?>
                                    </div>
                                    <?php if (!empty($p['problem_description'])): ?>
                                        <div class="text-[11px] text-rose-600 dark:text-rose-400 mt-0.5 flex items-center gap-1">
                                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                            <span>ปัญหา: <?= htmlspecialchars($p['problem_description']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 text-slate-600 dark:text-slate-300">
                                    <?= htmlspecialchars($p['department_name'] ?? 'ไม่ระบุ') ?>
                                </td>
                                <td class="py-3 px-3 text-center text-slate-600 dark:text-slate-400">
                                    <?= htmlspecialchars($p['fiscal_year'] ?? '-') ?>
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-semibold text-slate-900 dark:text-white">
                                    <?= number_format($p['budget'], 2) ?>
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-purple-600 dark:text-purple-400">
                                    <?= number_format($p['disbursed_amount'], 2) ?>
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-emerald-600 dark:text-emerald-400">
                                    <?= number_format($remaining, 2) ?>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5 font-semibold">
                                        <span><?= $p['progress'] ?>%</span>
                                    </div>
                                    <div class="w-20 mx-auto bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden mt-1">
                                        <div class="h-full rounded-full <?= $p['progress'] >= 100 ? 'bg-emerald-500' : 'bg-blue-600' ?>" style="width: <?= min(100, (float)$p['progress']) ?>%"></div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border <?= $badgeClass ?>">
                                        <?= htmlspecialchars(ProjectStatus::labelFor($p['status'])) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    <?= htmlspecialchars($p['responsible_name'] ?? '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>

