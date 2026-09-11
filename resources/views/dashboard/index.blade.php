<?php
ob_start();

// Thai Date formatting for footer timestamps
$thaiMonths = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];
$currentDateThai = date('j') . ' ' . $thaiMonths[(int)date('n')] . ' ' . (date('Y') + 543);

// Calculations for Status Breakdown (AGENTS.md Rule #10.1: 5 Statuses)
$totalSub = max(1, (int)$stats['sub_total']);
$inProgPct = round(((int)$stats['in_progress'] / $totalSub) * 100, 1);
$notStartPct = round(((int)$stats['not_started'] / $totalSub) * 100, 1);
$compPct = round(((int)$stats['completed'] / $totalSub) * 100, 1);
$probPct = round(((int)$stats['has_problem'] / $totalSub) * 100, 1);
$cancPct = round(((int)($stats['cancelled'] ?? 0) / $totalSub) * 100, 1);

// AGENTS.md Rule #10.3 & 10.4 Data
$topProjects = $stats['top_projects'] ?? [];
$bottomProjects = $stats['bottom_projects'] ?? [];
$categoryData = $stats['category_data'] ?? [];


// Calculations for Progress Tiers
$tierOver75 = 0;
$tier50to75 = 0;
$tier25to50 = 0;
$tierUnder25 = 0;
$tierNotStarted = 0;

$subCount = max(1, count($subProjects));
foreach ($subProjects as $sp) {
    $prog = (float)($sp['progress'] ?? 0);
    $st = $sp['status'] ?? 'not_started';
    if ($st === 'not_started' || $prog <= 0) {
        $tierNotStarted++;
    } elseif ($prog >= 75) {
        $tierOver75++;
    } elseif ($prog >= 50) {
        $tier50to75++;
    } elseif ($prog >= 25) {
        $tier25to50++;
    } else {
        $tierUnder25++;
    }
}

$tierOver75Pct = round(($tierOver75 / $subCount) * 100, 1);
$tier50to75Pct = round(($tier50to75 / $subCount) * 100, 1);
$tier25to50Pct = round(($tier25to50 / $subCount) * 100, 1);
$tierUnder25Pct = round(($tierUnder25 / $subCount) * 100, 1);
$tierNotStartedPct = round(($tierNotStarted / $subCount) * 100, 1);

// Fiscal Year Label Resolution for Custom Dropdown
$currentSelectedLabel = 'ทุกปีงบประมาณ';
$currentSelectedIsActive = false;
if (isset($selectedYearId) && $selectedYearId !== 'all') {
    foreach ($fiscalYears as $fy) {
        if ((string)$fy['id'] === (string)$selectedYearId) {
            $currentSelectedLabel = 'ปี ' . $fy['year'];
            $currentSelectedIsActive = !empty($fy['is_active']);
            break;
        }
    }
}
?>

<div class="space-y-5 sm:space-y-6 w-full max-w-full min-w-0">

    <!-- 1. Header Section (Title & Fiscal Year Selector) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 w-full max-w-full">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold font-heading text-slate-900 dark:text-white tracking-tight">Dashboard</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">ภาพรวมโครงการของเทศบาล</p>
        </div>

        <!-- Fiscal Year Custom Dropdown (Right) -->
        <div class="relative w-full sm:w-auto shrink-0" 
             x-data="{ open: false }" 
             @click.outside="open = false" 
             @keydown.escape.window="open = false">
            
            <!-- Trigger Button -->
            <button type="button" 
                    id="fiscal-year-selector-btn"
                    @click="open = !open; $nextTick(() => { if (window.lucide) lucide.createIcons(); })" 
                    class="w-full sm:w-auto flex items-center justify-between sm:justify-start gap-2.5 px-3.5 py-2 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:border-emerald-400/60 dark:hover:border-emerald-500/40 hover:shadow transition-all cursor-pointer text-left">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-7 h-7 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 whitespace-nowrap">ปีงบประมาณ:</span>
                    <div class="flex items-center gap-1.5 truncate">
                        <span class="text-xs font-bold text-slate-900 dark:text-white font-heading truncate"><?= htmlspecialchars($currentSelectedLabel) ?></span>
                        <?php if ($currentSelectedIsActive): ?>
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-emerald-500/15 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 whitespace-nowrap shrink-0">ปัจจุบัน</span>
                        <?php endif; ?>
                    </div>
                </div>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200 shrink-0 ml-1" :class="{ 'rotate-180': open }"></i>
            </button>

            <!-- Custom Dropdown Menu Panel -->
            <div x-show="open" 
                 x-cloak 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-full sm:w-64 bg-white dark:bg-[#181a20] rounded-2xl shadow-xl dark:shadow-2xl border border-slate-200 dark:border-white/10 p-1.5 z-50 text-left">
                
                <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider font-heading border-b border-slate-100 dark:border-white/[0.06] mb-1 flex items-center justify-between">
                    <span>เลือกปีงบประมาณ</span>
                    <span class="text-[9px] text-emerald-600 dark:text-emerald-400 font-medium">ถึงปีล่าสุด</span>
                </div>

                <div class="space-y-0.5 max-h-60 overflow-y-auto">
                    <!-- Option: ทุกปีงบประมาณ -->
                    <?php $isAllSelected = (isset($selectedYearId) && $selectedYearId === 'all'); ?>
                    <a href="<?= \App\Core\Router::url('/dashboard') ?>" 
                       @click="open = false"
                       class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center justify-between transition cursor-pointer hover:bg-slate-100 dark:hover:bg-white/5 <?= $isAllSelected ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/20' : 'text-slate-700 dark:text-slate-200 font-medium' ?>">
                        <div class="flex items-center gap-2">
                            <i data-lucide="layers" class="w-3.5 h-3.5 <?= $isAllSelected ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"></i>
                            <span>ทุกปีงบประมาณ</span>
                        </div>
                        <?php if ($isAllSelected): ?>
                            <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                        <?php endif; ?>
                    </a>

                    <!-- Options: รายการปีงบประมาณ (แสดงถึงปีล่าสุด 2569 ลงไป) -->
                    <?php if (!empty($fiscalYears)): ?>
                        <?php foreach ($fiscalYears as $fy): ?>
                            <?php 
                                $isYearSelected = (isset($selectedYearId) && (string)$selectedYearId === (string)$fy['id']);
                            ?>
                            <a href="<?= \App\Core\Router::url('/dashboard') ?>?fiscal_year_id=<?= $fy['id'] ?>" 
                               @click="open = false"
                               class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center justify-between transition cursor-pointer hover:bg-slate-100 dark:hover:bg-white/5 <?= $isYearSelected ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/20' : 'text-slate-700 dark:text-slate-200 font-medium' ?>">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono">ปี <?= htmlspecialchars((string)$fy['year']) ?></span>
                                    <?php if (!empty($fy['is_active'])): ?>
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-emerald-500/15 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">ปัจจุบัน</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($isYearSelected): ?>
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($suggestedYear)): ?>
        <!-- Alert Banner: แนะนำให้สลับไปยังปีงบประมาณที่มีข้อมูลโครงการ -->
        <div class="p-3.5 sm:p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs sm:text-sm">
            <div class="flex items-center gap-2.5 text-amber-900 dark:text-amber-200 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="info" class="w-4 h-4"></i>
                </div>
                <div class="truncate">
                    <span class="font-bold">ปีงบประมาณ <?= htmlspecialchars($currentSelectedLabel) ?> ยังไม่มีข้อมูลโครงการ</span>
                    <span class="text-amber-800/80 dark:text-amber-300/80 text-xs block sm:inline sm:ml-1">พบข้อมูลโครงการที่กำลังติดตามอยู่ในปีงบประมาณ <?= htmlspecialchars((string)$suggestedYear['year']) ?></span>
                </div>
            </div>
            <a href="<?= \App\Core\Router::url('/dashboard') ?>?fiscal_year_id=<?= $suggestedYear['id'] ?>" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm transition shrink-0 cursor-pointer">
                <span>สลับไปดูปี <?= htmlspecialchars((string)$suggestedYear['year']) ?></span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    <?php endif; ?>

    <!-- 2. 5 Primary KPI Metric Cards (Single Clean Row / 2-Col Grid on Mobile) -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-2.5 sm:gap-4 w-full max-w-full">
        <!-- 1. โครงการทั้งหมด (Full width on mobile) -->
        <div class="col-span-2 sm:col-span-1 group relative p-3 sm:p-4 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md hover:border-blue-400/60 dark:hover:border-blue-500/40 transition-all overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-500"></div>
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-heading truncate">โครงการทั้งหมด</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <i data-lucide="folder-kanban" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="my-1.5 sm:my-2">
                <div class="text-2xl sm:text-3xl font-black text-blue-600 dark:text-blue-400 font-heading tracking-tight leading-tight">
                    <?= number_format($stats['sub_total']) ?>
                </div>
                <div class="text-[10px] sm:text-[10.5px] text-slate-400 mt-0.5 truncate">โครงการย่อยในระบบ</div>
            </div>
            <div class="pt-2 border-t border-slate-100 dark:border-white/[0.06] text-[10px] sm:text-[10.5px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>โครงการหลัก</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 font-mono"><?= number_format($stats['main_total']) ?></span>
            </div>
        </div>

        <!-- 2. งบประมาณทั้งหมด -->
        <?php $cBudget = \App\Core\Helper::formatMillionCompact((float)($stats['total_budget'] ?? 0)); ?>
        <div class="group relative p-3 sm:p-4 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md hover:border-indigo-400/60 dark:hover:border-indigo-500/40 transition-all overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-indigo-500"></div>
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-heading truncate">งบประมาณทั้งหมด</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <i data-lucide="coins" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="my-1.5 sm:my-2">
                <div class="text-base sm:text-xl lg:text-2xl font-black text-slate-900 dark:text-white font-heading tracking-tight leading-tight truncate" title="<?= number_format((float)$stats['total_budget'], 2) ?> บาท">
                    <?= $cBudget['short'] ?>
                </div>
                <div class="text-[10px] sm:text-[10.5px] text-slate-400 dark:text-slate-500 mt-0.5 font-mono truncate" title="จำนวนเงินเต็ม">
                    <?= $cBudget['full'] ?>
                </div>
            </div>
            <div class="pt-2 border-t border-slate-100 dark:border-white/[0.06] text-[10px] sm:text-[10.5px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>กรอบงบ</span>
                <span class="font-semibold text-indigo-600 dark:text-indigo-400 font-mono">100%</span>
            </div>
        </div>

        <!-- 3. งบประมาณที่เบิกจ่าย -->
        <?php $cDisbursed = \App\Core\Helper::formatMillionCompact((float)($stats['total_disbursed'] ?? 0)); ?>
        <div class="group relative p-3 sm:p-4 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md hover:border-emerald-400/60 dark:hover:border-emerald-500/40 transition-all overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500"></div>
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-heading truncate">งบที่เบิกจ่าย</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="my-1.5 sm:my-2">
                <div class="text-base sm:text-xl lg:text-2xl font-black text-emerald-600 dark:text-emerald-400 font-heading tracking-tight leading-tight truncate" title="<?= number_format((float)$stats['total_disbursed'], 2) ?> บาท">
                    <?= $cDisbursed['short'] ?>
                </div>
                <div class="text-[10px] sm:text-[10.5px] text-slate-400 dark:text-slate-500 mt-0.5 font-mono truncate" title="จำนวนเงินเต็ม">
                    <?= $cDisbursed['full'] ?>
                </div>
            </div>
            <div class="pt-2 border-t border-slate-100 dark:border-white/[0.06] text-[10px] sm:text-[10.5px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>เบิกจ่ายแล้ว</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono"><?= number_format((float)$stats['disbursement_pct'], 1) ?>%</span>
            </div>
        </div>

        <!-- 4. งบประมาณคงเหลือ -->
        <?php $cRemaining = \App\Core\Helper::formatMillionCompact((float)($stats['total_remaining'] ?? 0)); ?>
        <div class="group relative p-3 sm:p-4 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md hover:border-sky-400/60 dark:hover:border-sky-500/40 transition-all overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-sky-500"></div>
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-heading truncate">งบคงเหลือ</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                    <i data-lucide="piggy-bank" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="my-1.5 sm:my-2">
                <div class="text-base sm:text-xl lg:text-2xl font-black text-slate-800 dark:text-slate-100 font-heading tracking-tight leading-tight truncate" title="<?= number_format((float)$stats['total_remaining'], 2) ?> บาท">
                    <?= $cRemaining['short'] ?>
                </div>
                <div class="text-[10px] sm:text-[10.5px] text-slate-400 dark:text-slate-500 mt-0.5 font-mono truncate" title="จำนวนเงินเต็ม">
                    <?= $cRemaining['full'] ?>
                </div>
            </div>
            <div class="pt-2 border-t border-slate-100 dark:border-white/[0.06] text-[10px] sm:text-[10.5px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>คงเหลือ</span>
                <span class="font-semibold text-sky-600 dark:text-sky-400 font-mono"><?= number_format(max(0, 100 - (float)$stats['disbursement_pct']), 1) ?>%</span>
            </div>
        </div>

        <!-- 5. เปอร์เซ็นต์ความสำเร็จเฉลี่ย -->
        <div class="group relative p-3 sm:p-4 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md hover:border-purple-400/60 dark:hover:border-purple-500/40 transition-all overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-purple-500"></div>
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-heading truncate">ความสำเร็จเฉลี่ย</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-purple-50 dark:bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                    <i data-lucide="target" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="my-1.5 sm:my-2">
                <div class="text-base sm:text-xl lg:text-2xl font-black text-purple-600 dark:text-purple-400 font-heading tracking-tight leading-tight">
                    <?= number_format((float)($stats['avg_progress'] ?? 0), 1) ?>%
                </div>
                <div class="w-full bg-slate-100 dark:bg-white/10 h-1.5 rounded-full overflow-hidden mt-1.5">
                    <div class="bg-purple-500 h-full rounded-full" style="width: <?= min(100, max(0, (float)($stats['avg_progress'] ?? 0))) ?>%"></div>
                </div>
            </div>
            <div class="pt-2 border-t border-slate-100 dark:border-white/[0.06] text-[10px] sm:text-[10.5px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>ภาพรวม</span>
                <span class="font-semibold text-purple-600 dark:text-purple-400">เฉลี่ยทุกโครงการ</span>
            </div>
        </div>
    </div>

    <!-- 3. 4 Core Dashboard Charts (AGENTS.md Rule #10: 2x2 Responsive Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 w-full max-w-full">

        <!-- กราฟที่ 1: จำนวนโครงการตามสถานะ (Rule #10.1: 5 Statuses) -->
        <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between w-full max-w-full min-w-0 overflow-hidden">
            <div>
                <!-- Title Header -->
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-white/[0.06]">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                            <i data-lucide="pie-chart" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading truncate">จำนวนโครงการตามสถานะ</h2>
                            <p class="text-[11px] text-slate-400 truncate">5 สถานะตามเกณฑ์มาตรฐานเทศบาล</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 shrink-0">
                        สัดส่วน 100%
                    </span>
                </div>

                <!-- Donut Chart & 5-Item Legend -->
                <div class="grid grid-cols-1 sm:grid-cols-12 items-center gap-4 sm:gap-6 py-2">
                    <!-- Donut Canvas with Center Text (5 cols) -->
                    <div class="sm:col-span-5 flex justify-center">
                        <div class="w-40 h-40 sm:w-48 sm:h-48 relative max-w-full aspect-square mx-auto flex items-center justify-center touch-pan-y" style="touch-action: pan-y;">
                            <canvas id="statusDonutChart"
                                    style="touch-action: pan-y;"
                                    data-not-started="<?= (int)$stats['not_started'] ?>"
                                    data-in-progress="<?= (int)$stats['in_progress'] ?>"
                                    data-completed="<?= (int)$stats['completed'] ?>"
                                    data-has-problem="<?= (int)$stats['has_problem'] ?>"
                                    data-cancelled="<?= (int)($stats['cancelled'] ?? 0) ?>"
                                    data-sub-total="<?= (int)$stats['sub_total'] ?>"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center select-none">
                                <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-widest font-sans">รวมทั้งหมด</span>
                                <span class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white font-heading tracking-tight my-0.5 leading-none"><?= $stats['sub_total'] ?></span>
                                <span class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 font-medium">โครงการย่อย</span>
                            </div>
                        </div>
                    </div>

                    <!-- 5-Item Legend (7 cols) -->
                    <div class="sm:col-span-7 space-y-2 text-xs">
                        <!-- 1. ยังไม่เริ่ม (Modern Tech Indigo) -->
                        <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-[#12141c] border border-slate-200/80 dark:border-white/[0.08] hover:border-indigo-400/60 dark:hover:border-indigo-500/40 flex items-center justify-between transition-all group <?= (int)$stats['not_started'] === 0 ? 'opacity-60 hover:opacity-100' : '' ?>">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-3 h-3 rounded-full bg-[#6366f1] shrink-0 shadow-[0_0_8px_rgba(99,102,241,0.5)]"></span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">ยังไม่เริ่ม</span>
                            </div>
                            <div class="flex items-center gap-2.5 shrink-0">
                                <span class="font-extrabold text-sm text-slate-900 dark:text-white font-mono"><?= $stats['not_started'] ?></span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold font-mono bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30"><?= $notStartPct ?>%</span>
                            </div>
                        </div>

                        <!-- 2. กำลังดำเนินการ (Electric Sky Blue) -->
                        <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-[#12141c] border border-slate-200/80 dark:border-white/[0.08] hover:border-sky-400/60 dark:hover:border-sky-500/40 flex items-center justify-between transition-all group <?= (int)$stats['in_progress'] === 0 ? 'opacity-60 hover:opacity-100' : '' ?>">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-3 h-3 rounded-full bg-[#0ea5e9] shrink-0 shadow-[0_0_8px_rgba(14,165,233,0.5)]"></span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors truncate">กำลังดำเนินการ</span>
                            </div>
                            <div class="flex items-center gap-2.5 shrink-0">
                                <span class="font-extrabold text-sm text-slate-900 dark:text-white font-mono"><?= $stats['in_progress'] ?></span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold font-mono bg-sky-500/10 dark:bg-sky-500/20 text-sky-700 dark:text-sky-300 border border-sky-500/30"><?= $inProgPct ?>%</span>
                            </div>
                        </div>

                        <!-- 3. เสร็จสิ้น (Luminous Jade Mint) -->
                        <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-[#12141c] border border-slate-200/80 dark:border-white/[0.08] hover:border-emerald-400/60 dark:hover:border-emerald-500/40 flex items-center justify-between transition-all group <?= (int)$stats['completed'] === 0 ? 'opacity-60 hover:opacity-100' : '' ?>">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-3 h-3 rounded-full bg-[#10b981] shrink-0 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors truncate">เสร็จสิ้น</span>
                            </div>
                            <div class="flex items-center gap-2.5 shrink-0">
                                <span class="font-extrabold text-sm text-slate-900 dark:text-white font-mono"><?= $stats['completed'] ?></span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold font-mono bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30"><?= $compPct ?>%</span>
                            </div>
                        </div>

                        <!-- 4. มีปัญหา (Radiant Coral Crimson) -->
                        <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-[#12141c] border border-slate-200/80 dark:border-white/[0.08] hover:border-rose-400/60 dark:hover:border-rose-500/40 flex items-center justify-between transition-all group <?= (int)$stats['has_problem'] === 0 ? 'opacity-60 hover:opacity-100' : '' ?>">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-3 h-3 rounded-full bg-[#f43f5e] shrink-0 shadow-[0_0_8px_rgba(244,63,94,0.5)]"></span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors truncate">มีปัญหา/ล่าช้า</span>
                            </div>
                            <div class="flex items-center gap-2.5 shrink-0">
                                <span class="font-extrabold text-sm text-slate-900 dark:text-white font-mono"><?= $stats['has_problem'] ?></span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold font-mono bg-rose-500/10 dark:bg-rose-500/20 text-rose-700 dark:text-rose-300 border border-rose-500/30"><?= $probPct ?>%</span>
                            </div>
                        </div>

                        <!-- 5. ยกเลิก (Deep Graphite Slate) -->
                        <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-[#12141c] border border-slate-200/80 dark:border-white/[0.08] hover:border-slate-400/60 dark:hover:border-slate-500/40 flex items-center justify-between transition-all group <?= (int)($stats['cancelled'] ?? 0) === 0 ? 'opacity-60 hover:opacity-100' : '' ?>">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-3 h-3 rounded-full bg-[#64748b] shrink-0 shadow-[0_0_8px_rgba(100,116,139,0.3)]"></span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors truncate">ยกเลิก</span>
                            </div>
                            <div class="flex items-center gap-2.5 shrink-0">
                                <span class="font-extrabold text-sm text-slate-900 dark:text-white font-mono"><?= $stats['cancelled'] ?? 0 ?></span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold font-mono bg-slate-500/10 dark:bg-slate-500/20 text-slate-700 dark:text-slate-300 border border-slate-500/30"><?= $cancPct ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Timestamp -->
            <div class="pt-3 mt-4 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500">
                <span>Rule #10.1: สถานะ 5 กลุ่ม</span>
                <span>ข้อมูล ณ วันที่ <?= $currentDateThai ?></span>
            </div>
        </div>

        <!-- กราฟที่ 2: งบประมาณ (Rule #10.2: Budget Comparison Chart) -->
        <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between w-full max-w-full min-w-0 overflow-hidden">
            <div>
                <!-- Title Header -->
                <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100 dark:border-white/[0.06]">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <i data-lucide="wallet" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading truncate">งบประมาณและการเบิกจ่าย</h2>
                            <p class="text-[11px] text-slate-400 truncate">งบประมาณทั้งหมด เทียบกับ ยอดเบิกจ่ายและคงเหลือ</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20 shrink-0">
                        เบิกจ่ายสะสม <?= number_format((float)$stats['disbursement_pct'], 1) ?>%
                    </span>
                </div>

                <!-- Budget Bar Chart Canvas -->
                <div class="h-56 sm:h-64 relative w-full max-w-full pt-1 touch-pan-y" style="touch-action: pan-y;">
                    <canvas id="budgetComparisonChart" 
                            data-total-budget="<?= (float)($stats['total_budget'] ?? 0) ?>"
                            data-total-disbursed="<?= (float)($stats['total_disbursed'] ?? 0) ?>"
                            data-total-remaining="<?= max(0, (float)($stats['total_remaining'] ?? 0)) ?>"
                            class="w-full h-full block"
                            style="touch-action: pan-y;"></canvas>
                </div>

                <!-- Bottom Quick Stats Strip -->
                <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-slate-100 dark:border-white/[0.06] text-center">
                    <div class="p-1.5 rounded-xl bg-slate-50 dark:bg-white/[0.03]">
                        <span class="text-[10px] text-slate-400 block mb-0.5">งบรวม</span>
                        <?= \App\Core\Helper::moneyDisplay((float)$stats['total_budget'], 'table', 'center', 'text-slate-800 dark:text-slate-200') ?>
                    </div>
                    <div class="p-1.5 rounded-xl bg-emerald-50/70 dark:bg-emerald-500/10">
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 block mb-0.5">เบิกจ่าย</span>
                        <?= \App\Core\Helper::moneyDisplay((float)$stats['total_disbursed'], 'table', 'center', 'text-emerald-700 dark:text-emerald-300') ?>
                    </div>
                    <div class="p-1.5 rounded-xl bg-sky-50/70 dark:bg-sky-500/10">
                        <span class="text-[10px] text-sky-600 dark:text-sky-400 block mb-0.5">คงเหลือ</span>
                        <?= \App\Core\Helper::moneyDisplay((float)$stats['total_remaining'], 'table', 'center', 'text-sky-700 dark:text-sky-300') ?>
                    </div>
                </div>
            </div>

            <!-- Footer Timestamp -->
            <div class="pt-3 mt-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500">
                <span>Rule #10.2: เปรียบเทียบงบประมาณ</span>
                <span>ข้อมูล ณ วันที่ <?= $currentDateThai ?></span>
            </div>
        </div>

        <!-- กราฟที่ 3: ความก้าวหน้าของโครงการหลัก (Rule #10.3: Top / Bottom Main Projects Horizontal Bar Chart) -->
        <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between w-full max-w-full min-w-0 overflow-hidden"
             x-data="{ mode: 'top' }">
            <div>
                <!-- Title Header & Toggle Buttons -->
                <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100 dark:border-white/[0.06]">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <i data-lucide="award" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading truncate">ความก้าวหน้าของโครงการหลัก</h2>
                            <p class="text-[11px] text-slate-400 truncate">Top / Bottom โครงการหลัก (ความก้าวหน้า 0 - 100%)</p>
                        </div>
                    </div>
                    <!-- Mode Selector Toggle -->
                    <div class="flex items-center bg-slate-100 dark:bg-white/10 p-0.5 rounded-xl text-[11px] font-semibold">
                        <button type="button" 
                                @click="mode = 'top'; if (window.toggleProjectSuccessMode) window.toggleProjectSuccessMode('top');"
                                :class="mode === 'top' ? 'bg-white dark:bg-[#161922] text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                                class="px-2.5 py-1 rounded-lg transition cursor-pointer">
                            Top 5
                        </button>
                        <button type="button" 
                                @click="mode = 'bottom'; if (window.toggleProjectSuccessMode) window.toggleProjectSuccessMode('bottom');"
                                :class="mode === 'bottom' ? 'bg-white dark:bg-[#161922] text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                                class="px-2.5 py-1 rounded-lg transition cursor-pointer">
                            Bottom 5
                        </button>
                    </div>
                </div>

                <!-- Project Success Horizontal Bar Chart Canvas -->
                <div class="h-64 sm:h-[270px] relative w-full max-w-full pt-1 touch-pan-y" style="touch-action: pan-y;">
                    <canvas id="projectSuccessChart" 
                            data-top='<?= htmlspecialchars(json_encode($stats['top_projects'] ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'
                            data-bottom='<?= htmlspecialchars(json_encode($stats['bottom_projects'] ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'
                            class="w-full h-full block"
                            style="touch-action: pan-y;"></canvas>
                </div>
            </div>

            <!-- Footer Timestamp -->
            <div class="pt-3 mt-4 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500">
                <span>Rule #10.3: ความก้าวหน้าของโครงการหลัก</span>
                <span>ข้อมูล ณ วันที่ <?= $currentDateThai ?></span>
            </div>
        </div>

        <!-- กราฟที่ 4: โครงการตามประเภท/หมวดหมู่ (Rule #10.4: Projects by Category Chart) -->
        <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between w-full max-w-full min-w-0 overflow-hidden">
            <div>
                <!-- Title Header -->
                <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100 dark:border-white/[0.06]">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-violet-500/10 text-violet-600 dark:text-violet-400 flex items-center justify-center shrink-0">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading truncate">โครงการตามประเภท</h2>
                            <p class="text-[11px] text-slate-400 truncate">หมวดหมู่ที่ 1 - 8 ตามมาตรฐานกองทุน กปท.</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono font-semibold px-2.5 py-0.5 rounded-full bg-violet-50 dark:bg-violet-500/15 text-violet-700 dark:text-violet-300 border border-violet-500/20 shrink-0">
                        8 หมวดหมู่
                    </span>
                </div>

                <!-- Category Distribution Bar Chart Canvas -->
                <div class="h-64 sm:h-[270px] relative w-full max-w-full pt-1 touch-pan-y" style="touch-action: pan-y;">
                    <canvas id="categoryBarChart" 
                            data-categories='<?= htmlspecialchars(json_encode($stats['category_data'] ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'
                            class="w-full h-full block"
                            style="touch-action: pan-y;"></canvas>
                </div>
            </div>

            <!-- Footer Timestamp -->
            <div class="pt-3 mt-4 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500">
                <span>Rule #10.4: จัดกลุ่มตามหมวดหมู่</span>
                <span>ข้อมูล ณ วันที่ <?= $currentDateThai ?></span>
            </div>
        </div>

    </div>

    <!-- 5. Row 4: โครงการที่ต้องติดตาม (Watchlist Table with Soft Pills) -->
    <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md transition-shadow w-full max-w-full min-w-0 overflow-hidden">
        <!-- Table Title Header -->
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-white/[0.06]">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">โครงการที่ต้องติดตาม</h2>
            </div>
            <span class="text-[11px] font-mono font-semibold px-2.5 py-0.5 rounded-full bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20">
                <?= count($watchlist) ?> รายการเฝ้าระวัง
            </span>
        </div>

        <div class="overflow-x-auto w-full max-w-full">
            <table class="w-full text-left text-xs border-collapse min-w-[580px] sm:min-w-[640px]">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-white/[0.06] text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">
                        <th class="py-3 px-3 w-12 text-center whitespace-nowrap">ลำดับ</th>
                        <th class="py-3 px-3 min-w-[200px]">โครงการ</th>
                        <th class="py-3 px-3 whitespace-nowrap min-w-[100px]">หน่วยงาน</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap min-w-[110px]">สถานะ</th>
                        <th class="py-3 px-3 w-36 whitespace-nowrap">ความก้าวหน้า</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap min-w-[110px]">กำหนดแล้วเสร็จ</th>
                        <th class="py-3 px-3 min-w-[160px]">หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04] text-slate-700 dark:text-slate-300">
                    <?php if (!empty($watchlist)): ?>
                        <?php $idx = 1; foreach (array_slice($watchlist, 0, 5) as $w): ?>
                            <?php
                            $isProblem = ($w['status'] === 'has_problem');
                            $wProg = (float)($w['progress'] ?? 0);
                            $wTier = \App\Services\ProgressService::getProgressTier($wProg, $w['status'] ?? null);
                            $statusBadge = $isProblem 
                                ? ['label' => 'ล่าช้า', 'class' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60']
                                : ['label' => 'ใกล้ครบกำหนด', 'class' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60'];
                            ?>
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-white/[0.02] transition">
                                <td class="py-3.5 px-3 text-center font-mono text-slate-400"><?= $idx++ ?></td>
                                <td class="py-3.5 px-3 font-semibold text-slate-900 dark:text-white">
                                    <a href="<?= \App\Core\Router::url("/sub-projects/{$w['id']}") ?>" class="hover:text-blue-600 dark:hover:text-blue-400 transition">
                                        <?= htmlspecialchars($w['name']) ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 dark:text-slate-400 font-medium"><?= htmlspecialchars($w['department_name'] ?? 'สำนักช่าง') ?></td>
                                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-[11px] font-bold border whitespace-nowrap <?= $statusBadge['class'] ?>">
                                        <?= $statusBadge['label'] ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="font-mono font-bold w-8 text-right <?= $wTier['textClass'] ?>"><?= (int)$wProg ?>%</span>
                                        <div class="flex-1 bg-slate-100 dark:bg-white/[0.06] h-2 rounded-full overflow-hidden p-0.5">
                                            <div class="bg-gradient-to-r <?= $wTier['gradient'] ?> h-1.5 rounded-full" style="width: <?= min(100, max(5, $wProg)) ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 text-center font-mono text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    <?= !empty($w['end_date']) ? date('j M Y', strtotime($w['end_date'])) : '30 ก.ย. 2568' ?>
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 dark:text-slate-400">
                                    <?= htmlspecialchars($w['problem_description'] ?? ($isProblem ? 'ล่าช้ากว่าแผนงาน' : 'ใกล้ถึงกำหนดสิ้นสุด')) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                ไม่พบโครงการที่ต้องติดตามเป็นพิเศษ ทุกโครงการดำเนินงานตามแผนปกติ
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ดูทั้งหมด Button -->
        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-white/[0.06] text-center">
            <a href="<?= \App\Core\Router::url('/projects') ?>" class="inline-flex items-center gap-1 px-4 py-1.5 rounded-xl bg-slate-50 dark:bg-white/5 text-blue-600 dark:text-blue-400 font-semibold hover:bg-blue-50 dark:hover:bg-blue-500/10 transition border border-slate-200/60 dark:border-white/10 text-xs">
                <span>ดูทั้งหมด</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>

    <!-- 6. Row 5: โครงการล่าสุด (Latest Projects Table) -->
    <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md transition-shadow w-full max-w-full min-w-0 overflow-hidden">
        <!-- Table Title Header -->
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-white/[0.06]">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i data-lucide="folder-open" class="w-4 h-4"></i>
                </div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">โครงการล่าสุด</h2>
            </div>
            <span class="text-[11px] font-mono font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300">
                รายการอัปเดต
            </span>
        </div>

        <div class="overflow-x-auto w-full max-w-full">
            <table class="w-full text-left text-xs border-collapse min-w-[580px] sm:min-w-[640px]">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-white/[0.06] text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">
                        <th class="py-3 px-3 min-w-[200px]">ชื่อโครงการ</th>
                        <th class="py-3 px-3 whitespace-nowrap min-w-[100px]">หน่วยงาน</th>
                        <th class="py-3 px-3 text-right whitespace-nowrap min-w-[110px]">งบประมาณ</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap min-w-[120px]">สถานะ</th>
                        <th class="py-3 px-3 w-36 whitespace-nowrap">ความก้าวหน้า</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap min-w-[100px]">อัปเดตล่าสุด</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04] text-slate-700 dark:text-slate-300">
                    <?php if (!empty($latestProjects)): ?>
                        <?php foreach ($latestProjects as $lp): ?>
                            <?php
                            $lpProg = (float)($lp['progress'] ?? 0);
                            $lpTier = \App\Services\ProgressService::getProgressTier($lpProg, $lp['status'] ?? null);
                            $lpStatus = (string)($lp['status'] ?? 'not_started');
                            $lpStatusBadge = match($lpStatus) {
                                'completed' => ['label' => 'เสร็จสิ้น', 'class' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60'],
                                'in_progress' => ['label' => 'กำลังดำเนินการ', 'class' => 'bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-300 border border-sky-200 dark:border-sky-800/60'],
                                'has_problem' => ['label' => 'มีปัญหา', 'class' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60'],
                                'cancelled' => ['label' => 'ยกเลิก', 'class' => 'bg-slate-100 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800/60'],
                                default => ['label' => 'ยังไม่เริ่ม', 'class' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60'],
                            };
                            ?>
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-white/[0.02] transition">
                                <td class="py-3.5 px-3 font-semibold text-slate-900 dark:text-white">
                                    <a href="<?= \App\Core\Router::url("/sub-projects/{$lp['id']}") ?>" class="hover:text-blue-600 dark:hover:text-blue-400 transition">
                                        <?= htmlspecialchars($lp['name']) ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 dark:text-slate-400 font-medium"><?= htmlspecialchars($lp['department_name'] ?? 'สำนักช่าง') ?></td>
                                <td class="py-3.5 px-3 text-right whitespace-nowrap"><?= \App\Core\Helper::moneyDisplay((float)$lp['budget'], 'table', 'right') ?></td>
                                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-[11px] font-bold border whitespace-nowrap <?= $lpStatusBadge['class'] ?>">
                                        <?= $lpStatusBadge['label'] ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="font-mono font-bold w-8 text-right <?= $lpTier['textClass'] ?>"><?= (int)$lpProg ?>%</span>
                                        <div class="flex-1 bg-slate-100 dark:bg-white/[0.06] h-2 rounded-full overflow-hidden p-0.5">
                                            <div class="bg-gradient-to-r <?= $lpTier['gradient'] ?> h-1.5 rounded-full" style="width: <?= min(100, max(5, $lpProg)) ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 text-center font-mono text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    <?= !empty($lp['updated_at']) ? date('j M Y', strtotime($lp['updated_at'])) : date('j M Y') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                ไม่พบข้อมูลโครงการ
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ดูทั้งหมด Button -->
        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-white/[0.06] text-center">
            <a href="<?= \App\Core\Router::url('/projects') ?>" class="inline-flex items-center gap-1 px-4 py-1.5 rounded-xl bg-slate-50 dark:bg-white/5 text-blue-600 dark:text-blue-400 font-semibold hover:bg-blue-50 dark:hover:bg-blue-500/10 transition border border-slate-200/60 dark:border-white/10 text-xs">
                <span>ดูทั้งหมด</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>

</div>

<!-- สคริปต์ Chart.js ตามมาตรฐาน AGENTS.md Rule #10: 4 Core Charts (Data-Driven + IIFE Scoped) -->
<script>
(function() {
    'use strict';

    let chartInitRetries = 0;
    let projectSuccessChartInstance = null;
    let dashboardChartsTimer = null;

    function renderDashboardCharts(isThemeChange = false) {
        if (typeof Chart === 'undefined') {
            if (chartInitRetries < 30) {
                chartInitRetries++;
                setTimeout(() => renderDashboardCharts(isThemeChange), 100);
            } else {
                console.error('MPT: Chart.js library is not available');
            }
            return;
        }
        chartInitRetries = 0;

        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
        const tickColor = isDark ? '#94a3b8' : '#64748b';
        const labelColor = isDark ? '#cbd5e1' : '#334155';
        const tooltipBg = isDark ? '#181b24' : '#ffffff';
        const tooltipTitle = isDark ? '#ffffff' : '#0f172a';
        const tooltipBody = isDark ? '#e2e8f0' : '#334155';
        const tooltipBorder = isDark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.08)';

        // กำหนด Default Border และ Color ให้โปร่งใส ป้องกันเส้นขอบดำในโหมดมืด
        if (typeof Chart !== 'undefined') {
            Chart.defaults.color = tickColor;
            Chart.defaults.borderColor = 'transparent';
        }

        // ทำลายกราฟเดิมเพื่อป้องกันทับซ้อนเมื่อมีการรีเฟรช SPA หรือเปลี่ยนธีม
        ['statusDonutChart', 'budgetComparisonChart', 'projectSuccessChart', 'categoryBarChart'].forEach(id => {
            try {
                const canvasEl = document.getElementById(id);
                if (canvasEl) {
                    const existing = Chart.getChart(canvasEl) || Chart.getChart(id);
                    if (existing) existing.destroy();
                }
            } catch (e) {
                console.warn('Destroy chart error:', e);
            }
        });

        const emptyChartColor = isDark ? 'rgba(255, 255, 255, 0.1)' : '#e2e8f0';

        // Helper Currency Formatter
        const formatCurrency = (val) => {
            if (val >= 1000000) {
                return (val / 1000000).toLocaleString('th-TH', { maximumFractionDigits: 1 }) + 'M';
            }
            if (val >= 1000) {
                return (val / 1000).toLocaleString('th-TH', { maximumFractionDigits: 0 }) + 'k';
            }
            return Number(val).toLocaleString('th-TH');
        };

        // -------------------------------------------------------------
        // กราฟที่ 1: สถานะโครงการ (Rule #10.1: 5 Statuses Donut Chart - Data Driven)
        // -------------------------------------------------------------
        try {
            const statusCanvas = document.getElementById('statusDonutChart');
            if (statusCanvas) {
                const notStarted = parseInt(statusCanvas.dataset.notStarted || '0', 10);
                const inProgress = parseInt(statusCanvas.dataset.inProgress || '0', 10);
                const completed  = parseInt(statusCanvas.dataset.completed || '0', 10);
                const hasProblem = parseInt(statusCanvas.dataset.hasProblem || '0', 10);
                const cancelled  = parseInt(statusCanvas.dataset.cancelled || '0', 10);

                const rawStatuses = [
                    { key: 'not_started', label: 'ยังไม่เริ่ม', count: notStarted, color: '#6366f1', hover: '#4f46e5' },
                    { key: 'in_progress', label: 'กำลังดำเนินการ', count: inProgress, color: '#0ea5e9', hover: '#0284c7' },
                    { key: 'completed',  label: 'เสร็จสิ้น',      count: completed,  color: '#10b981', hover: '#059669' },
                    { key: 'has_problem',label: 'มีปัญหา/ล่าช้า', count: hasProblem, color: '#f43f5e', hover: '#e11d48' },
                    { key: 'cancelled',  label: 'ยกเลิก',         count: cancelled,  color: '#64748b', hover: '#475569' }
                ];
                const totalCount = rawStatuses.reduce((acc, s) => acc + s.count, 0);
                const activeStatuses = rawStatuses.filter(s => s.count > 0);

                if (totalCount === 0) {
                    new Chart(statusCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: ['ไม่มีข้อมูลโครงการ'],
                            datasets: [{
                                data: [1],
                                backgroundColor: [emptyChartColor],
                                hoverBackgroundColor: [emptyChartColor],
                                borderWidth: 0,
                                borderRadius: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            cutout: '74%',
                            animation: isThemeChange ? false : { duration: 350, easing: 'easeOutQuad' },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: tooltipBg,
                                    titleColor: tooltipTitle,
                                    bodyColor: tooltipBody,
                                    borderColor: tooltipBorder,
                                    borderWidth: 1,
                                    padding: 10,
                                    cornerRadius: 10,
                                    callbacks: {
                                        label: function() { return ' ไม่มีข้อมูลโครงการในปีงบประมาณนี้'; }
                                    }
                                }
                            }
                        }
                    });
                } else {
                    new Chart(statusCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: activeStatuses.map(s => s.label),
                            datasets: [{
                                data: activeStatuses.map(s => s.count),
                                backgroundColor: activeStatuses.map(s => s.color),
                                hoverBackgroundColor: activeStatuses.map(s => s.hover),
                                borderWidth: 0,
                                borderRadius: activeStatuses.length > 1 ? 6 : 0,
                                spacing: activeStatuses.length > 1 ? 4 : 0,
                                hoverOffset: activeStatuses.length > 1 ? 6 : 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            cutout: '74%',
                            animation: isThemeChange ? false : { duration: 350, easing: 'easeOutQuad' },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: tooltipBg,
                                    titleColor: tooltipTitle,
                                    bodyColor: tooltipBody,
                                    borderColor: tooltipBorder,
                                    borderWidth: 1,
                                    padding: 10,
                                    cornerRadius: 10,
                                    boxPadding: 4,
                                    callbacks: {
                                        label: function(context) {
                                            const val = context.raw;
                                            const denom = totalCount > 0 ? totalCount : 1;
                                            const pct = ((val / denom) * 100).toFixed(1);
                                            return ` ${context.label}: ${val} โครงการ (${pct}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        } catch (err) {
            console.error('Error creating Status Donut Chart:', err);
        }

        // -------------------------------------------------------------
        // กราฟที่ 2: เปรียบเทียบงบประมาณ (Rule #10.2: Budget Comparison Bar Chart - Data Driven)
        // -------------------------------------------------------------
        try {
            const budgetCanvas = document.getElementById('budgetComparisonChart');
            if (budgetCanvas) {
                const totalBudget = parseFloat(budgetCanvas.dataset.totalBudget || '0');
                const totalDisbursed = parseFloat(budgetCanvas.dataset.totalDisbursed || '0');
                const totalRemaining = Math.max(0, parseFloat(budgetCanvas.dataset.totalRemaining || '0'));

                const bCtx = budgetCanvas.getContext('2d');
                const totalGrad = bCtx.createLinearGradient(0, 0, 0, 200);
                totalGrad.addColorStop(0, '#6366f1');
                totalGrad.addColorStop(1, '#4f46e5');

                const disbGrad = bCtx.createLinearGradient(0, 0, 0, 200);
                disbGrad.addColorStop(0, '#34d399');
                disbGrad.addColorStop(1, '#059669');

                const remGrad = bCtx.createLinearGradient(0, 0, 0, 200);
                remGrad.addColorStop(0, '#38bdf8');
                remGrad.addColorStop(1, '#0284c7');

                new Chart(bCtx, {
                    type: 'bar',
                    data: {
                        labels: ['งบประมาณทั้งหมด', 'ยอดเบิกจ่าย', 'งบประมาณคงเหลือ'],
                        datasets: [{
                            data: [totalBudget, totalDisbursed, totalRemaining],
                            backgroundColor: [totalGrad, disbGrad, remGrad],
                            borderRadius: 8,
                            barPercentage: 0.55
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: isThemeChange ? false : { duration: 400, easing: 'easeOutQuad' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: tooltipBg,
                                titleColor: tooltipTitle,
                                bodyColor: tooltipBody,
                                borderColor: tooltipBorder,
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 10,
                                callbacks: {
                                    label: function(context) {
                                        const val = Number(context.raw).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                        const total = totalBudget > 0 ? totalBudget : 1.0;
                                        const pct = ((Number(context.raw) / total) * 100).toFixed(1);
                                        return ` ${context.label}: ${val} บาท (${pct}%)`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grace: '10%',
                                ticks: {
                                    color: tickColor,
                                    font: { family: "'Prompt', 'Sarabun', sans-serif", size: 10.5 },
                                    callback: (val) => formatCurrency(val)
                                },
                                grid: {
                                    color: gridColor,
                                    borderDash: [3, 3],
                                    drawTicks: false
                                },
                                border: { display: false }
                            },
                            x: {
                                ticks: {
                                    color: labelColor,
                                    font: { family: "'Prompt', 'Sarabun', sans-serif", size: 11, weight: '600' },
                                    padding: 6
                                },
                                grid: { display: false },
                                border: { display: false }
                            }
                        }
                    }
                });
            }
        } catch (err) {
            console.error('Error creating Budget Comparison Chart:', err);
        }

        // -------------------------------------------------------------
        // กราฟที่ 3: ความก้าวหน้าของโครงการหลัก (Rule #10.3: Top / Bottom Main Projects Horizontal Bar - Data Driven)
        // -------------------------------------------------------------
        try {
            const successCanvas = document.getElementById('projectSuccessChart');
            if (successCanvas) {
                let topProjectsData = [];
                let bottomProjectsData = [];
                try {
                    topProjectsData = JSON.parse(successCanvas.dataset.top || '[]');
                    bottomProjectsData = JSON.parse(successCanvas.dataset.bottom || '[]');
                } catch (e) {
                    console.warn('MPT: Error parsing project success data:', e);
                }

                const getBarColor = (prog) => {
                    if (prog >= 75) return '#10b981'; // Emerald
                    if (prog >= 50) return '#0ea5e9'; // Sky Blue
                    if (prog >= 25) return '#f59e0b'; // Amber
                    return '#6366f1'; // Indigo
                };

                const currentMode = window._currentProjectSuccessMode || 'top';
                const activeList = currentMode === 'top' ? topProjectsData : bottomProjectsData;

                const labels = activeList.map(p => p.short_name);
                const progresses = activeList.map(p => parseFloat(p.progress || 0));
                const colors = progresses.map(p => getBarColor(p));

                projectSuccessChartInstance = new Chart(successCanvas, {
                    type: 'bar',
                    data: {
                        labels: labels.length > 0 ? labels : ['ไม่มีโครงการหลัก'],
                        datasets: [{
                            label: 'ความก้าวหน้า (%)',
                            data: progresses.length > 0 ? progresses : [0],
                            backgroundColor: colors.length > 0 ? colors : [emptyChartColor],
                            borderRadius: 6,
                            barPercentage: 0.6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: isThemeChange ? false : { duration: 400, easing: 'easeOutQuad' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: tooltipBg,
                                titleColor: tooltipTitle,
                                bodyColor: tooltipBody,
                                borderColor: tooltipBorder,
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 10,
                                callbacks: {
                                    title: (items) => {
                                        const idx = items[0]?.dataIndex;
                                        const p = activeList[idx];
                                        return p ? p.name : '';
                                    },
                                    label: (context) => {
                                        const val = context.parsed.x || 0;
                                        return ` ความก้าวหน้า: ${val}%`;
                                    },
                                    afterLabel: (context) => {
                                        const idx = context.dataIndex;
                                        const p = activeList[idx];
                                        if (!p) return '';
                                        const bg = Number(p.budget || 0).toLocaleString('th-TH');
                                        return `งบประมาณ: ${bg} บาท | สถานะ: ${p.status || '-'}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                min: 0,
                                max: 100,
                                ticks: {
                                    color: tickColor,
                                    font: { family: "'Prompt', 'Sarabun', sans-serif", size: 10.5 },
                                    callback: (val) => val + '%'
                                },
                                grid: {
                                    color: gridColor,
                                    borderDash: [3, 3],
                                    drawTicks: false
                                },
                                border: { display: false }
                            },
                            y: {
                                ticks: {
                                    color: labelColor,
                                    font: { family: "'Prompt', 'Sarabun', sans-serif", size: 10.5, weight: '500' },
                                    padding: 6
                                },
                                grid: { display: false },
                                border: { display: false }
                            }
                        }
                    }
                });

                // Expose Toggle Mode Handler
                window.toggleProjectSuccessMode = function(newMode) {
                    window._currentProjectSuccessMode = newMode;
                    if (!projectSuccessChartInstance) return;

                    const list = newMode === 'top' ? topProjectsData : bottomProjectsData;
                    projectSuccessChartInstance.data.labels = list.map(p => p.short_name);
                    const newProgs = list.map(p => parseFloat(p.progress || 0));
                    projectSuccessChartInstance.data.datasets[0].data = newProgs;
                    projectSuccessChartInstance.data.datasets[0].backgroundColor = newProgs.map(p => getBarColor(p));
                    projectSuccessChartInstance.update();
                };
            }
        } catch (err) {
            console.error('Error creating Project Success Chart:', err);
        }

        // -------------------------------------------------------------
        // กราฟที่ 4: โครงการตามประเภท/หมวดหมู่ (Rule #10.4: Projects by Category Bar Chart - Data Driven)
        // -------------------------------------------------------------
        try {
            const catCanvas = document.getElementById('categoryBarChart');
            if (catCanvas) {
                let catData = [];
                try {
                    catData = JSON.parse(catCanvas.dataset.categories || '[]');
                } catch (e) {
                    console.warn('MPT: Error parsing category data:', e);
                }

                const labels = catData.map(c => c.short_name);
                const subCounts = catData.map(c => parseInt(c.sub_project_count || 0));

                const catCtx = catCanvas.getContext('2d');
                const catGrad = catCtx.createLinearGradient(0, 0, 0, 220);
                catGrad.addColorStop(0, isDark ? 'rgba(139, 92, 246, 0.85)' : '#8b5cf6');
                catGrad.addColorStop(1, isDark ? 'rgba(124, 58, 237, 0.4)' : '#6d28d9');

                new Chart(catCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'จำนวนโครงการย่อย',
                            data: subCounts,
                            backgroundColor: catGrad,
                            borderColor: isDark ? '#a78bfa' : '#7c3aed',
                            borderWidth: 1,
                            borderRadius: 6,
                            barPercentage: 0.55
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: isThemeChange ? false : { duration: 400, easing: 'easeOutQuad' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: tooltipBg,
                                titleColor: tooltipTitle,
                                bodyColor: tooltipBody,
                                borderColor: tooltipBorder,
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 10,
                                callbacks: {
                                    title: (items) => {
                                        const idx = items[0]?.dataIndex;
                                        const c = catData[idx];
                                        return c ? `${c.short_name}: ${c.name}` : '';
                                    },
                                    label: (context) => {
                                        const idx = context.dataIndex;
                                        const c = catData[idx];
                                        return ` โครงการย่อย: ${context.raw} โครงการ (โครงการหลัก: ${c.project_count})`;
                                    },
                                    afterLabel: (context) => {
                                        const idx = context.dataIndex;
                                        const c = catData[idx];
                                        if (!c) return '';
                                        const bg = Number(c.total_budget || 0).toLocaleString('th-TH');
                                        return `งบประมาณรวม: ${bg} บาท`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    color: tickColor,
                                    font: { family: "'Prompt', 'Sarabun', sans-serif", size: 10.5 },
                                    callback: (val) => val + ' ค.'
                                },
                                grid: {
                                    color: gridColor,
                                    borderDash: [3, 3],
                                    drawTicks: false
                                },
                                border: { display: false }
                            },
                            x: {
                                ticks: {
                                    color: labelColor,
                                    font: { family: "'Prompt', 'Sarabun', sans-serif", size: 10, weight: '500' },
                                    padding: 6
                                },
                                grid: { display: false },
                                border: { display: false }
                            }
                        }
                    }
                });
            }
        } catch (err) {
            console.error('Error creating Category Bar Chart:', err);
        }
    }

    function scheduleDashboardChartsInit(isThemeChange = false) {
        clearTimeout(dashboardChartsTimer);
        dashboardChartsTimer = setTimeout(() => {
            renderDashboardCharts(isThemeChange);
        }, 30);
    }

    // Expose global functions safely
    window.initDashboardCharts = scheduleDashboardChartsInit;
    window.renderDashboardCharts = renderDashboardCharts;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => scheduleDashboardChartsInit(false));
    } else {
        scheduleDashboardChartsInit(false);
    }
    window.addEventListener('load', () => scheduleDashboardChartsInit(false));

    // React dynamically when theme toggles (Dark <-> Light <-> System)
    if (!window._dashboardThemeListenerAttached) {
        window._dashboardThemeListenerAttached = true;
        window.addEventListener('theme-changed', function() {
            if (document.getElementById('statusDonutChart') || document.getElementById('budgetComparisonChart') || document.getElementById('projectSuccessChart') || document.getElementById('categoryBarChart')) {
                scheduleDashboardChartsInit(true);
            }
        });
    }
})();
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>
