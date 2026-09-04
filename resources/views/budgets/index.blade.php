<?php
ob_start();
use App\Core\Auth;
use App\Core\Router;
use App\Core\Session;

$title = 'ระบบงบประมาณและการเบิกจ่าย - Municipal Project Tracker';
$totalBudget = array_sum(array_column($mainBudgets, 'budget'));
$totalDisbursed = array_sum(array_column($mainBudgets, 'disbursed_amount'));
$totalRemaining = $totalBudget - $totalDisbursed;
$overallPercentage = $totalBudget > 0 ? round(($totalDisbursed / $totalBudget) * 100, 2) : 0;
?>

<div class="space-y-6" x-data="{ 
    disburseModalOpen: false,
    selectedSubProject: '',
    disburseAmount: '',
    subProjects: <?= htmlspecialchars(json_encode($subProjects), ENT_QUOTES, 'UTF-8') ?>,
    get selectedProjectInfo() {
        return this.subProjects.find(p => p.id == this.selectedSubProject) || null;
    }
}">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <i data-lucide="wallet" class="w-6 h-6"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">ระบบบริหารและติดตามงบประมาณ</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">ควบคุมเพดานงบประมาณ การจัดสรร และการเบิกจ่ายโครงการตามระเบียบกระทรวงมหาดไทย</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <?php if (Auth::canManageProjects()): ?>
            <button @click="disburseModalOpen = true" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-medium rounded-xl shadow-sm hover:shadow-md transition-all text-sm">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>บันทึกการเบิกจ่ายเงิน</span>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 4 Summary Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform duration-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">งบประมาณรวมทั้งหมด</span>
                <span class="p-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl">
                    <i data-lucide="coins" class="w-5 h-5"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    ฿<?= number_format($totalBudget, 2) ?>
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-xs text-slate-500 dark:text-slate-400">
                    <i data-lucide="layers" class="w-3.5 h-3.5 text-blue-500"></i>
                    <span>งบประมาณโครงการหลัก <?= count($mainBudgets) ?> โครงการ</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-purple-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform duration-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">ยอดเบิกจ่ายสะสม</span>
                <span class="p-2 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-xl">
                    <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 tracking-tight">
                    ฿<?= number_format($totalDisbursed, 2) ?>
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-xs text-slate-500 dark:text-slate-400">
                    <i data-lucide="activity" class="w-3.5 h-3.5 text-purple-500"></i>
                    <span>เบิกจ่ายแล้วคิดเป็น <?= $overallPercentage ?>%</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform duration-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">งบประมาณคงเหลือ</span>
                <span class="p-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 tracking-tight">
                    ฿<?= number_format($totalRemaining, 2) ?>
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-xs text-slate-500 dark:text-slate-400">
                    <i data-lucide="percent" class="w-3.5 h-3.5 text-emerald-500"></i>
                    <span>คงเหลือ <?= 100 - $overallPercentage ?>% ของงบทั้งหมด</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform duration-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">อัตราการเบิกจ่ายรวม</span>
                <span class="p-2 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-xl">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    <?= $overallPercentage ?>%
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full mt-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-emerald-500 h-full rounded-full transition-all duration-500" style="width: <?= min(100, $overallPercentage) ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Project Budget Ceiling Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                    สถานะเพดานงบประมาณโครงการหลัก (Project Budget Allocations)
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">ยอดเบิกจ่ายจะคำนวณและสรุปอัตโนมัติจากโครงการย่อยทั้งหมดภายใต้โครงการหลัก</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4">รหัส / โครงการหลัก</th>
                        <th class="py-3.5 px-4">ปีงบ / กองสำนัก</th>
                        <th class="py-3.5 px-4 text-right">งบประมาณที่ได้รับ</th>
                        <th class="py-3.5 px-4 text-right">เบิกจ่ายแล้ว</th>
                        <th class="py-3.5 px-4 text-right">คงเหลือ</th>
                        <th class="py-3.5 px-4 text-center">สัดส่วนการเบิกจ่าย</th>
                        <th class="py-3.5 px-4 text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    <?php if (empty($mainBudgets)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                ยังไม่มีข้อมูลโครงการในระบบ
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($mainBudgets as $b): ?>
                            <tr class="hover:bg-slate-50/75 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-900 dark:text-white">
                                        <?= htmlspecialchars($b['name']) ?>
                                    </div>
                                    <div class="text-xs font-mono text-slate-400 dark:text-slate-500">
                                        <?= htmlspecialchars($b['project_code']) ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-slate-700 dark:text-slate-300">
                                        <?= htmlspecialchars($b['department_name'] ?? 'ไม่ระบุ') ?>
                                    </div>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                        ปี <?= htmlspecialchars($b['fiscal_year'] ?? '-') ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-semibold text-slate-900 dark:text-white">
                                    ฿<?= number_format($b['budget'], 2) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-medium text-purple-600 dark:text-purple-400">
                                    ฿<?= number_format($b['disbursed_amount'], 2) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-medium text-emerald-600 dark:text-emerald-400">
                                    ฿<?= number_format($b['remaining_amount'], 2) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="w-36 mx-auto">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="font-medium text-slate-600 dark:text-slate-300"><?= $b['disbursement_percentage'] ?>%</span>
                                            <?php if ($b['disbursement_percentage'] >= 100): ?>
                                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">ครบงบ</span>
                                            <?php elseif ($b['disbursement_percentage'] >= 80): ?>
                                                <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold">ใกล้เต็ม</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500 <?= $b['disbursement_percentage'] > 90 ? 'bg-amber-500' : 'bg-blue-600' ?>"
                                                 style="width: <?= min(100, (float)$b['disbursement_percentage']) ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="<?= Router::url('/projects/' . $b['id']) ?>" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                                        <span>ดูโครงการ</span>
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Disbursements Log -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="receipt" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                    ประวัติรายการเบิกจ่ายงบประมาณล่าสุด (Recent Disbursement Logs)
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">บันทึกประวัติการเบิกเงินพร้อมรายละเอียดผู้รับเงินและเอกสารหลักฐาน</p>
            </div>
            <span class="text-xs px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-lg">
                แสดง 15 รายการล่าสุด
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4">วันที่เบิกจ่าย</th>
                        <th class="py-3.5 px-4">โครงการย่อย</th>
                        <th class="py-3.5 px-4">รายละเอียด / วัตถุประสงค์</th>
                        <th class="py-3.5 px-4">ผู้รับเงิน / หน่วยงาน</th>
                        <th class="py-3.5 px-4 text-right">จำนวนเงิน (บาท)</th>
                        <th class="py-3.5 px-4">ผู้บันทึก</th>
                        <th class="py-3.5 px-4 text-center">หลักฐาน</th>
                        <th class="py-3.5 px-4 text-center w-20">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    <?php if (empty($recentDisbursements)): ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                ยังไม่มีประวัติการเบิกจ่ายในระบบ
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentDisbursements as $d): ?>
                            <tr class="hover:bg-slate-50/75 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-900 dark:text-white">
                                        <?= date('d/m/Y', strtotime($d['disbursement_date'])) ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        <?= date('H:i น.', strtotime($d['created_at'])) ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <a href="<?= Router::url('/sub-projects/' . $d['project_id']) ?>" 
                                       class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                        <?= htmlspecialchars($d['project_name']) ?>
                                    </a>
                                    <div class="text-xs font-mono text-slate-400">
                                        <?= htmlspecialchars($d['project_code']) ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs text-slate-700 dark:text-slate-300">
                                    <?= htmlspecialchars($d['description']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">
                                    <?= htmlspecialchars($d['recipient'] ?: '-') ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    ฿<?= number_format($d['amount'], 2) ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 text-xs">
                                    <?= htmlspecialchars($d['creator_name'] ?? 'ระบบ') ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if (!empty($d['evidence_file'])): ?>
                                        <a href="<?= Router::url('/uploads/' . $d['evidence_file']) ?>" target="_blank"
                                           class="inline-flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                            <i data-lucide="paperclip" class="w-3.5 h-3.5"></i>
                                            <span>ไฟล์</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-slate-300 dark:text-slate-600 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <form action="<?= Router::url('/budgets/disbursements/' . $d['id'] . '/delete') ?>" method="POST"
                                          onsubmit="return confirm('ยืนยันยกเลิกรายการเบิกจ่ายจำนวน <?= number_format($d['amount'], 2) ?> บาท? (ยอดเงินจะคืนกลับเข้างบโครงการ)');">
                                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                        <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded transition" title="ยกเลิกรายการเบิกจ่าย">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Disburse Budget -->
    <template x-teleport="body">
    <div x-show="disburseModalOpen" 
         x-cloak 
         @click.self="disburseModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5">
            
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="p-2.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <i data-lucide="coins" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">บันทึกการเบิกจ่ายงบประมาณ</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">ห้ามเบิกจ่ายเกินเพดานงบประมาณที่ได้รับจัดสรร</p>
                    </div>
                </div>
                <button type="button" @click.stop="disburseModalOpen = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                    <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                </button>
            </div>

            <form action="<?= Router::url('/budgets/disburse') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                <?= Session::csrfField() ?>
                <input type="hidden" name="redirect" value="<?= Router::url('/budgets') ?>">

                <!-- Select Sub Project -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        เลือกโครงการย่อยที่ทำการเบิกจ่าย <span class="text-rose-500">*</span>
                    </label>
                    <select name="project_id" x-model="selectedSubProject" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white">
                        <option value="">-- กรุณาเลือกโครงการย่อย --</option>
                        <?php foreach ($subProjects as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['project_code'] . ' - ' . $p['name'] . ' (คงเหลือ ฿' . number_format($p['remaining'], 2) . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Info Box When Project Selected -->
                <div x-show="selectedProjectInfo" class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">งบประมาณจัดสรร:</span>
                        <span class="font-bold text-slate-900 dark:text-white" x-text="`฿${Number(selectedProjectInfo ? selectedProjectInfo.budget : 0).toLocaleString('th-TH', {minimumFractionDigits: 2})}`"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">เบิกจ่ายแล้ว:</span>
                        <span class="font-bold text-purple-600 dark:text-purple-400" x-text="`฿${Number(selectedProjectInfo ? selectedProjectInfo.disbursed_amount : 0).toLocaleString('th-TH', {minimumFractionDigits: 2})}`"></span>
                    </div>
                    <div class="flex justify-between pt-1 border-t border-slate-200 dark:border-slate-700">
                        <span class="text-slate-700 dark:text-slate-300 font-semibold">งบประมาณคงเหลือ:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="`฿${Number(selectedProjectInfo ? selectedProjectInfo.remaining : 0).toLocaleString('th-TH', {minimumFractionDigits: 2})}`"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            จำนวนเงินที่เบิกจ่าย (บาท) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.01" min="1" name="amount" x-model="disburseAmount" required placeholder="0.00"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            วันที่เบิกจ่าย <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="disbursement_date" value="<?= date('Y-m-d') ?>" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white">
                    </div>
                </div>

                <!-- Over-budget warning alert in client -->
                <div x-show="selectedProjectInfo && Number(disburseAmount) > Number(selectedProjectInfo.remaining)" 
                     class="p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 rounded-xl text-xs text-rose-600 dark:text-rose-400 flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0"></i>
                    <span>คำเตือน: ยอดเบิกจ่ายเกินงบประมาณคงเหลือ (ระบบจะปฏิเสธรายการ เว้นแต่ได้รับสิทธิ์ Administrator Override)</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        ผู้รับเงิน / บริษัทคู่สัญญา / หน่วยงาน
                    </label>
                    <input type="text" name="recipient" placeholder="เช่น บจก. รวมมิตรการค้า, นายสมชาย ใจดี"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        วัตถุประสงค์ / รายการค่าใช้จ่าย <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="description" rows="2" required placeholder="ระบุรายละเอียดค่าใช้จ่าย เช่น ค่าอาหารกลางวัน, ค่าจ้างเหมาบริการ..."
                              class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        แนบเอกสารหลักฐาน / ใบเสร็จรับเงิน (PDF, รูปภาพ)
                    </label>
                    <input type="file" name="evidence_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                           class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-slate-800 dark:file:text-slate-300">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="disburseModalOpen = false" 
                            class="px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        ยกเลิก
                    </button>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl shadow-sm transition-all text-sm">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>ยืนยันการเบิกจ่าย</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>

