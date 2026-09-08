<?php
ob_start();

// Thai Date formatting for footer timestamps
$thaiMonths = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];
$currentDateThai = date('j') . ' ' . $thaiMonths[(int)date('n')] . ' ' . (date('Y') + 543);

// Calculations for Status Breakdown
$totalSub = max(1, (int)$stats['sub_total']);
$inProgPct = round(((int)$stats['in_progress'] / $totalSub) * 100, 1);
$notStartPct = round(((int)$stats['not_started'] / $totalSub) * 100, 1);
$compPct = round(((int)$stats['completed'] / $totalSub) * 100, 1);
$probPct = round(((int)$stats['has_problem'] / $totalSub) * 100, 1);

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
?>

<div class="space-y-5 sm:space-y-6 w-full max-w-full min-w-0">

    <!-- 1. Header Section (Title & Fiscal Year Selector) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 w-full max-w-full">
        <div>
            <h1 class="text-xl sm:text-3xl font-extrabold font-heading text-slate-900 dark:text-white tracking-tight">Dashboard</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">ภาพรวมโครงการของเทศบาล</p>
        </div>

        <!-- Fiscal Year Dropdown (Right) -->
        <div class="flex items-center justify-between sm:justify-start gap-2 w-full sm:w-auto bg-white dark:bg-[#161922] px-3 sm:px-3.5 py-2 rounded-2xl border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:border-emerald-400/50 transition shrink-0">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <i data-lucide="calendar" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                <label for="dashboard-fiscal-year-select" class="text-xs font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap cursor-pointer">ปีงบประมาณ</label>
            </div>
            <div class="relative shrink-0">
                <select id="dashboard-fiscal-year-select" 
                        onchange="window.location.href='<?= \App\Core\Router::url('/dashboard') ?>?fiscal_year_id=' + this.value"
                        class="pl-2.5 pr-8 py-1.5 rounded-xl bg-slate-50 dark:bg-[#1f222e] text-xs font-bold text-slate-800 dark:text-white border border-slate-200/80 dark:border-white/10 focus:ring-2 focus:ring-emerald-500 cursor-pointer transition">
                    <option value="all" <?= (isset($selectedYearId) && $selectedYearId === 'all') ? 'selected' : '' ?>>ทุกปีงบประมาณ</option>
                    <?php if (!empty($fiscalYears)): ?>
                        <?php foreach ($fiscalYears as $fy): ?>
                            <?php 
                                $isYearSelected = (isset($selectedYearId) && (string)$selectedYearId === (string)$fy['id']);
                                $isActiveTag = !empty($fy['is_active']) ? ' (ปัจจุบัน)' : '';
                            ?>
                            <option value="<?= $fy['id'] ?>" <?= $isYearSelected ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)$fy['year']) . $isActiveTag ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- 2. 4 Top KPI Metric Cards (Ultra-Premium Polish with Exact Color Palette) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 w-full max-w-full">
        
        <!-- การ์ด 1: โครงการทั้งหมด (Royal Blue Accent) -->
        <div class="group relative p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-lg hover:border-blue-400/60 dark:hover:border-blue-500/40 transition-all duration-300 overflow-hidden flex flex-col justify-between w-full max-w-full min-w-0">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-sky-400 to-indigo-500 opacity-80 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-heading truncate">
                    โครงการทั้งหมด
                </span>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-200/60 dark:border-blue-500/20 shadow-sm shadow-blue-500/10 group-hover:scale-110 transition-transform shrink-0">
                    <i data-lucide="folder-kanban" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>
            <div class="my-2.5 sm:my-3">
                <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-blue-600 dark:text-blue-400 font-heading tracking-tight leading-tight">
                    <?= number_format($stats['sub_total']) ?>
                </div>
                <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 truncate">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 shrink-0"></span>
                    <span class="truncate">โครงการย่อยในระบบทั้งหมด</span>
                </div>
            </div>
            <div class="pt-2.5 sm:pt-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between gap-1 text-[11px] text-slate-500 dark:text-slate-400">
                <span class="truncate">โครงการหลัก: <strong class="text-slate-800 dark:text-slate-200"><?= number_format($stats['main_total']) ?></strong></span>
                <span class="font-semibold text-blue-600 dark:text-blue-400 shrink-0 text-[10.5px] sm:text-[11px] whitespace-nowrap">100% ในแผน</span>
            </div>
        </div>

        <!-- การ์ด 2: กำลังดำเนินการ (Golden Amber Accent) -->
        <div class="group relative p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-lg hover:border-amber-400/60 dark:hover:border-amber-500/40 transition-all duration-300 overflow-hidden flex flex-col justify-between w-full max-w-full min-w-0">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 via-yellow-400 to-orange-400 opacity-80 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-heading truncate">
                    กำลังดำเนินการ
                </span>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200/60 dark:border-amber-500/20 shadow-sm shadow-amber-500/10 group-hover:scale-110 transition-transform shrink-0">
                    <i data-lucide="clock" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>
            <div class="my-2.5 sm:my-3">
                <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-amber-500 dark:text-amber-400 font-heading tracking-tight leading-tight">
                    <?= number_format($stats['in_progress']) ?>
                </div>
                <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 truncate">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span class="truncate">คิดเป็น <strong class="text-slate-800 dark:text-slate-200"><?= $inProgPct ?>%</strong> ของทั้งหมด</span>
                </div>
            </div>
            <div class="pt-2.5 sm:pt-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between gap-1 text-[11px] text-slate-500 dark:text-slate-400">
                <span class="truncate">การขับเคลื่อน</span>
                <span class="font-semibold text-amber-600 dark:text-amber-400 shrink-0 text-[10.5px] sm:text-[11px] whitespace-nowrap">กำลังดำเนินงาน</span>
            </div>
        </div>

        <!-- การ์ด 3: เสร็จแล้ว (Emerald Green Accent) -->
        <div class="group relative p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-lg hover:border-emerald-400/60 dark:hover:border-emerald-500/40 transition-all duration-300 overflow-hidden flex flex-col justify-between w-full max-w-full min-w-0">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 via-teal-400 to-green-500 opacity-80 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-heading truncate">
                    เสร็จแล้ว
                </span>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-200/60 dark:border-emerald-500/20 shadow-sm shadow-emerald-500/10 group-hover:scale-110 transition-transform shrink-0">
                    <i data-lucide="check-circle-2" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>
            <div class="my-2.5 sm:my-3">
                <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-emerald-500 dark:text-emerald-400 font-heading tracking-tight leading-tight">
                    <?= number_format($stats['completed']) ?>
                </div>
                <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 truncate">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span class="truncate">คิดเป็น <strong class="text-slate-800 dark:text-slate-200"><?= $compPct ?>%</strong> ของทั้งหมด</span>
                </div>
            </div>
            <div class="pt-2.5 sm:pt-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between gap-1 text-[11px] text-slate-500 dark:text-slate-400">
                <span class="truncate">ผลสัมฤทธิ์</span>
                <span class="font-semibold text-emerald-600 dark:text-emerald-400 shrink-0 text-[10.5px] sm:text-[11px] whitespace-nowrap">สำเร็จ 100%</span>
            </div>
        </div>

        <!-- การ์ด 4: มีปัญหา/ล่าช้า (Rose Red Accent) -->
        <div class="group relative p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-lg hover:border-rose-400/60 dark:hover:border-rose-500/40 transition-all duration-300 overflow-hidden flex flex-col justify-between w-full max-w-full min-w-0">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 via-red-500 to-orange-500 opacity-80 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-heading truncate">
                    มีปัญหา/ล่าช้า
                </span>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center border border-rose-200/60 dark:border-rose-500/20 shadow-sm shadow-rose-500/10 group-hover:scale-110 transition-transform shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>
            <div class="my-2.5 sm:my-3">
                <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-rose-500 dark:text-red-400 font-heading tracking-tight leading-tight">
                    <?= number_format($stats['has_problem']) ?>
                </div>
                <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 truncate">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                    <span class="truncate">คิดเป็น <strong class="text-slate-800 dark:text-slate-200"><?= $probPct ?>%</strong> ของทั้งหมด</span>
                </div>
            </div>
            <div class="pt-2.5 sm:pt-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between gap-1 text-[11px] text-slate-500 dark:text-slate-400">
                <span class="truncate">เฝ้าระวัง</span>
                <span class="font-semibold text-rose-600 dark:text-rose-400 shrink-0 text-[10.5px] sm:text-[11px] whitespace-nowrap">เร่งรัดติดตาม</span>
            </div>
        </div>

    </div>

    <!-- 3. Row 2: 2 กราฟหลัก (สถานะโครงการ & งบประมาณ) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 w-full max-w-full">

        <!-- กราฟที่ 1: สถานะโครงการ (Status Doughnut Ring with Rounded Pills & Spacing) -->
        <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between w-full max-w-full min-w-0 overflow-hidden">
            <div>
                <!-- Title Header -->
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-white/[0.06]">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                            <i data-lucide="pie-chart" class="w-4 h-4"></i>
                        </div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">สถานะโครงการ</h2>
                    </div>
                    <span class="text-[11px] font-mono font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300">
                        สัดส่วน 100%
                    </span>
                </div>

                <!-- Donut Chart & Legend -->
                <div class="grid grid-cols-1 sm:grid-cols-12 items-center gap-6 py-2">
                    <!-- Donut with Glassmorphic Center Badge (6 cols) -->
                    <div class="sm:col-span-6 flex justify-center">
                        <div class="w-40 h-40 sm:w-48 sm:h-48 relative max-w-full aspect-square mx-auto">
                            <canvas id="statusDonutChart"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider font-sans">รวม</span>
                                <span class="text-3xl font-black text-slate-900 dark:text-white font-heading tracking-tight my-0.5"><?= $stats['sub_total'] ?></span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">โครงการ</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Legend with Interactive Styled Cards (6 cols) -->
                    <div class="sm:col-span-6 space-y-2.5 text-xs">
                        <!-- กำลังดำเนินการ (Blue) -->
                        <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.04] hover:border-blue-500/30 transition flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-[#3b82f6] shadow-sm shadow-blue-500/40"></span>
                                <span class="font-medium text-slate-700 dark:text-slate-300">กำลังดำเนินการ</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 dark:text-white font-mono"><?= $stats['in_progress'] ?></span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold font-mono bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20"><?= $inProgPct ?>%</span>
                            </div>
                        </div>

                        <!-- ยังไม่เริ่ม (Yellow/Amber) -->
                        <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.04] hover:border-amber-500/30 transition flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-[#f59e0b] shadow-sm shadow-amber-500/40"></span>
                                <span class="font-medium text-slate-700 dark:text-slate-300">ยังไม่เริ่ม</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 dark:text-white font-mono"><?= $stats['not_started'] ?></span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold font-mono bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20"><?= $notStartPct ?>%</span>
                            </div>
                        </div>

                        <!-- เสร็จแล้ว (Green) -->
                        <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.04] hover:border-emerald-500/30 transition flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-[#10b981] shadow-sm shadow-emerald-500/40"></span>
                                <span class="font-medium text-slate-700 dark:text-slate-300">เสร็จแล้ว</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 dark:text-white font-mono"><?= $stats['completed'] ?></span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold font-mono bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20"><?= $compPct ?>%</span>
                            </div>
                        </div>

                        <!-- มีปัญหา/ล่าช้า (Red) -->
                        <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.04] hover:border-rose-500/30 transition flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-[#f43f5e] shadow-sm shadow-rose-500/40"></span>
                                <span class="font-medium text-slate-700 dark:text-slate-300">มีปัญหา/ล่าช้า</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 dark:text-white font-mono"><?= $stats['has_problem'] ?></span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold font-mono bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20"><?= $probPct ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Timestamp -->
            <div class="pt-3 mt-4 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500">
                <span>ระบบติดตามโครงการเทศบาล</span>
                <span>ข้อมูล ณ วันที่ <?= $currentDateThai ?></span>
            </div>
        </div>

        <!-- กราฟที่ 2: งบประมาณ (Budget Donut Gauge) -->
        <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between w-full max-w-full min-w-0 overflow-hidden">
            <div>
                <!-- Title Header -->
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-white/[0.06]">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i data-lucide="wallet" class="w-4 h-4"></i>
                        </div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">งบประมาณ</h2>
                    </div>
                    <span class="text-[11px] font-mono font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-500/15 text-blue-700 dark:text-blue-300 border border-blue-500/20">
                        เบิกจ่ายสะสม <?= number_format($stats['disbursement_pct'], 1) ?>%
                    </span>
                </div>

                <!-- Stats and Gauge Donut side by side -->
                <div class="grid grid-cols-1 sm:grid-cols-12 items-center gap-6 py-2">
                    <!-- Left Stats in 3 Elevated Cards (6 cols) -->
                    <div class="sm:col-span-6 space-y-2.5">
                        <!-- งบประมาณทั้งหมด -->
                        <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.04]">
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">งบประมาณทั้งหมด</div>
                            <div class="text-xl font-black text-slate-900 dark:text-white font-heading tracking-tight mt-0.5">
                                <?= number_format($stats['total_budget']) ?>
                            </div>
                            <div class="text-[10px] text-slate-400 font-sans">บาท</div>
                        </div>

                        <!-- เบิกจ่ายแล้ว (Vivid Blue Accent) -->
                        <div class="p-3 rounded-xl bg-blue-50/60 dark:bg-blue-500/10 border border-blue-200/50 dark:border-blue-500/20">
                            <div class="text-[11px] text-blue-600 dark:text-blue-400 font-semibold">เบิกจ่ายแล้ว</div>
                            <div class="text-xl font-black text-blue-600 dark:text-blue-400 font-heading tracking-tight mt-0.5">
                                <?= number_format($stats['total_disbursed']) ?>
                            </div>
                            <div class="text-[10px] text-blue-500/80 font-sans">บาท</div>
                        </div>

                        <!-- คงเหลือ -->
                        <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.04]">
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">คงเหลือ</div>
                            <div class="text-xl font-black text-slate-700 dark:text-slate-300 font-heading tracking-tight mt-0.5">
                                <?= number_format($stats['total_remaining']) ?>
                            </div>
                            <div class="text-[10px] text-slate-400 font-sans">บาท</div>
                        </div>
                    </div>

                    <!-- Right Donut Gauge (6 cols) -->
                    <div class="sm:col-span-6 flex justify-center">
                        <div class="w-40 h-40 sm:w-48 sm:h-48 relative max-w-full aspect-square mx-auto">
                            <canvas id="budgetDonutChart"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider font-sans">เบิกจ่ายแล้ว</span>
                                <span class="text-3xl font-black text-slate-900 dark:text-white font-heading tracking-tight my-0.5"><?= number_format($stats['disbursement_pct'], 1) ?>%</span>
                                <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold font-mono mt-0.5">จากงบรวม</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Timestamp -->
            <div class="pt-3 mt-4 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500">
                <span>งบประมาณปี <?= date('Y') + 543 ?></span>
                <span>ข้อมูล ณ วันที่ <?= $currentDateThai ?></span>
            </div>
        </div>

    </div>

    <!-- 4. Row 3: โครงการตามหน่วยงาน (ซ้าย) & ความก้าวหน้าโครงการ (ขวา) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 w-full max-w-full">

        <!-- กราฟที่ 3: โครงการตามประเภท (Project Categories Horizontal Bar Chart) -->
        <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between w-full max-w-full min-w-0 overflow-hidden">
            <div>
                <!-- Title Header -->
                <div class="flex items-center justify-between gap-2 pb-3 mb-2 border-b border-slate-100 dark:border-white/[0.06]">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <i data-lucide="shapes" class="w-4 h-4"></i>
                            </div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading truncate">โครงการตามประเภท</h2>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5 truncate">สัดส่วนโครงการและงบประมาณตามประเภท 1 - 8 (กปท.)</div>
                    </div>
                    <!-- Right Legend Pills (Clean, no text wrapping) -->
                    <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-xs font-semibold whitespace-nowrap border border-emerald-200/60 dark:border-emerald-500/20 shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            โครงการหลัก
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 text-xs font-semibold whitespace-nowrap border border-blue-200/60 dark:border-blue-500/20 shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            โครงการย่อย
                        </span>
                    </div>
                </div>

                <!-- Horizontal Grouped Bar Chart Canvas -->
                <div class="h-64 sm:h-72 relative w-full max-w-full overflow-hidden pt-2">
                    <canvas id="categoryBarChart" class="w-full h-full block"></canvas>
                </div>
            </div>

            <!-- Footer Timestamp -->
            <?php 
                $catWithProj = count(array_filter($stats['category_data'] ?? [], fn($c) => (int)($c['project_count'] ?? 0) > 0)); 
            ?>
            <div class="pt-3 mt-4 border-t border-slate-100 dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-[11px] text-slate-400 dark:text-slate-500">
                <span>มีโครงการดำเนินการ: <?= $catWithProj ?> จาก 8 ประเภท | โครงการหลักรวม: <?= (int)$stats['main_total'] ?> โครงการ</span>
                <span>ข้อมูล ณ วันที่ <?= $currentDateThai ?></span>
            </div>
        </div>

        <!-- กราฟที่ 4: ความก้าวหน้าโครงการ (Elevated Progress Tiers) -->
        <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between w-full max-w-full min-w-0 overflow-hidden">
            <div>
                <!-- Title Header -->
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-white/[0.06]">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i data-lucide="trending-up" class="w-4 h-4"></i>
                        </div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">ความก้าวหน้าโครงการ</h2>
                    </div>
                    <span class="text-[11px] font-mono font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300">
                        เกณฑ์ความคืบหน้า
                    </span>
                </div>

                <!-- 5 Tier Progress Bars (Sleek Gradient Tracks) -->
                <div class="space-y-3.5 sm:space-y-4 pt-1">
                    <!-- Tier 1: มากกว่า 75% (Green Gradient) -->
                    <div class="flex items-center gap-2 sm:gap-3 text-xs">
                        <div class="w-20 sm:w-28 shrink-0 flex items-center gap-1.5 sm:gap-2 text-slate-700 dark:text-slate-300 font-semibold">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#10b981] shadow-sm shadow-emerald-500/50 shrink-0"></span>
                            <span class="truncate">มากกว่า 75%</span>
                        </div>
                        <div class="flex-1 min-w-[50px] bg-slate-100 dark:bg-white/[0.06] h-3 rounded-full overflow-hidden p-0.5">
                            <div class="bg-gradient-to-r from-emerald-400 to-teal-500 h-2 rounded-full transition-all duration-700 shadow-sm" style="width: <?= min(100, max(5, $tierOver75Pct)) ?>%"></div>
                        </div>
                        <div class="flex items-center justify-end gap-1.5 sm:gap-2 text-right shrink-0">
                            <span class="text-[11px] sm:text-xs font-medium text-slate-600 dark:text-slate-400 whitespace-nowrap"><?= $tierOver75 ?> <span class="hidden sm:inline">โครงการ</span><span class="sm:hidden">ค.</span></span>
                            <span class="w-10 sm:w-12 text-right font-black text-slate-900 dark:text-white font-mono text-xs sm:text-sm"><?= $tierOver75Pct ?>%</span>
                        </div>
                    </div>

                    <!-- Tier 2: 50% - 75% (Blue Gradient) -->
                    <div class="flex items-center gap-2 sm:gap-3 text-xs">
                        <div class="w-20 sm:w-28 shrink-0 flex items-center gap-1.5 sm:gap-2 text-slate-700 dark:text-slate-300 font-semibold">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#3b82f6] shadow-sm shadow-blue-500/50 shrink-0"></span>
                            <span class="truncate">50% - 75%</span>
                        </div>
                        <div class="flex-1 min-w-[50px] bg-slate-100 dark:bg-white/[0.06] h-3 rounded-full overflow-hidden p-0.5">
                            <div class="bg-gradient-to-r from-blue-400 to-indigo-500 h-2 rounded-full transition-all duration-700 shadow-sm" style="width: <?= min(100, max(5, $tier50to75Pct)) ?>%"></div>
                        </div>
                        <div class="flex items-center justify-end gap-1.5 sm:gap-2 text-right shrink-0">
                            <span class="text-[11px] sm:text-xs font-medium text-slate-600 dark:text-slate-400 whitespace-nowrap"><?= $tier50to75 ?> <span class="hidden sm:inline">โครงการ</span><span class="sm:hidden">ค.</span></span>
                            <span class="w-10 sm:w-12 text-right font-black text-slate-900 dark:text-white font-mono text-xs sm:text-sm"><?= $tier50to75Pct ?>%</span>
                        </div>
                    </div>

                    <!-- Tier 3: 25% - 50% (Yellow/Amber Gradient) -->
                    <div class="flex items-center gap-2 sm:gap-3 text-xs">
                        <div class="w-20 sm:w-28 shrink-0 flex items-center gap-1.5 sm:gap-2 text-slate-700 dark:text-slate-300 font-semibold">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#f59e0b] shadow-sm shadow-amber-500/50 shrink-0"></span>
                            <span class="truncate">25% - 50%</span>
                        </div>
                        <div class="flex-1 min-w-[50px] bg-slate-100 dark:bg-white/[0.06] h-3 rounded-full overflow-hidden p-0.5">
                            <div class="bg-gradient-to-r from-amber-400 to-yellow-500 h-2 rounded-full transition-all duration-700 shadow-sm" style="width: <?= min(100, max(5, $tier25to50Pct)) ?>%"></div>
                        </div>
                        <div class="flex items-center justify-end gap-1.5 sm:gap-2 text-right shrink-0">
                            <span class="text-[11px] sm:text-xs font-medium text-slate-600 dark:text-slate-400 whitespace-nowrap"><?= $tier25to50 ?> <span class="hidden sm:inline">โครงการ</span><span class="sm:hidden">ค.</span></span>
                            <span class="w-10 sm:w-12 text-right font-black text-slate-900 dark:text-white font-mono text-xs sm:text-sm"><?= $tier25to50Pct ?>%</span>
                        </div>
                    </div>

                    <!-- Tier 4: น้อยกว่า 25% (Orange Gradient) -->
                    <div class="flex items-center gap-2 sm:gap-3 text-xs">
                        <div class="w-20 sm:w-28 shrink-0 flex items-center gap-1.5 sm:gap-2 text-slate-700 dark:text-slate-300 font-semibold">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#f97316] shadow-sm shadow-orange-500/50 shrink-0"></span>
                            <span class="truncate">น้อยกว่า 25%</span>
                        </div>
                        <div class="flex-1 min-w-[50px] bg-slate-100 dark:bg-white/[0.06] h-3 rounded-full overflow-hidden p-0.5">
                            <div class="bg-gradient-to-r from-orange-400 to-amber-500 h-2 rounded-full transition-all duration-700 shadow-sm" style="width: <?= min(100, max(5, $tierUnder25Pct)) ?>%"></div>
                        </div>
                        <div class="flex items-center justify-end gap-1.5 sm:gap-2 text-right shrink-0">
                            <span class="text-[11px] sm:text-xs font-medium text-slate-600 dark:text-slate-400 whitespace-nowrap"><?= $tierUnder25 ?> <span class="hidden sm:inline">โครงการ</span><span class="sm:hidden">ค.</span></span>
                            <span class="w-10 sm:w-12 text-right font-black text-slate-900 dark:text-white font-mono text-xs sm:text-sm"><?= $tierUnder25Pct ?>%</span>
                        </div>
                    </div>

                    <!-- Tier 5: ยังไม่เริ่ม (Red Gradient) -->
                    <div class="flex items-center gap-2 sm:gap-3 text-xs">
                        <div class="w-20 sm:w-28 shrink-0 flex items-center gap-1.5 sm:gap-2 text-slate-700 dark:text-slate-300 font-semibold">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#f43f5e] shadow-sm shadow-rose-500/50 shrink-0"></span>
                            <span class="truncate">ยังไม่เริ่ม</span>
                        </div>
                        <div class="flex-1 min-w-[50px] bg-slate-100 dark:bg-white/[0.06] h-3 rounded-full overflow-hidden p-0.5">
                            <div class="bg-gradient-to-r from-rose-500 to-red-500 h-2 rounded-full transition-all duration-700 shadow-sm" style="width: <?= min(100, max(5, $tierNotStartedPct)) ?>%"></div>
                        </div>
                        <div class="flex items-center justify-end gap-1.5 sm:gap-2 text-right shrink-0">
                            <span class="text-[11px] sm:text-xs font-medium text-slate-600 dark:text-slate-400 whitespace-nowrap"><?= $tierNotStarted ?> <span class="hidden sm:inline">โครงการ</span><span class="sm:hidden">ค.</span></span>
                            <span class="w-10 sm:w-12 text-right font-black text-slate-900 dark:text-white font-mono text-xs sm:text-sm"><?= $tierNotStartedPct ?>%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Timestamp -->
            <div class="pt-3 mt-4 border-t border-slate-100 dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-[11px] text-slate-400 dark:text-slate-500">
                <span>เกณฑ์ชี้วัดตามมาตรฐาน</span>
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
                            $isComp = ($lp['status'] === 'completed');
                            $lpProg = (float)($lp['progress'] ?? 0);
                            $lpTier = \App\Services\ProgressService::getProgressTier($lpProg, $lp['status'] ?? null);
                            $lpStatusBadge = $isComp
                                ? ['label' => 'เสร็จแล้ว', 'class' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60']
                                : ['label' => 'กำลังดำเนินการ', 'class' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60'];
                            ?>
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-white/[0.02] transition">
                                <td class="py-3.5 px-3 font-semibold text-slate-900 dark:text-white">
                                    <a href="<?= \App\Core\Router::url("/sub-projects/{$lp['id']}") ?>" class="hover:text-blue-600 dark:hover:text-blue-400 transition">
                                        <?= htmlspecialchars($lp['name']) ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 dark:text-slate-400 font-medium"><?= htmlspecialchars($lp['department_name'] ?? 'สำนักช่าง') ?></td>
                                <td class="py-3.5 px-3 text-right font-mono font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap"><?= number_format((float)$lp['budget']) ?> <span class="text-[10px] text-slate-400">บาท</span></td>
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

<!-- สคริปต์ Chart.js กำหนดสีกราฟให้สวยงามระดับพรีเมียม (Gradient & Pill Arcs) -->
<script>
let chartInitRetries = 0;
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
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : '#f1f5f9';
    const tickColor = isDark ? '#94a3b8' : '#64748b';
    const labelColor = isDark ? '#e2e8f0' : '#1e293b';
    const tooltipBg = isDark ? '#161922' : '#ffffff';
    const tooltipTitle = isDark ? '#ffffff' : '#0f172a';
    const tooltipBody = isDark ? '#e2e8f0' : '#334155';
    const tooltipBorder = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)';

    // ทำลายกราฟเดิมเพื่อป้องกันทับซ้อนเมื่อมีการรีเฟรช SPA หรือเปลี่ยนธีม
    ['statusDonutChart', 'budgetDonutChart', 'deptBarChart', 'yearlyBudgetChart'].forEach(id => {
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

    // -------------------------------------------------------------
    // กราฟที่ 1: สถานะโครงการ (Status Doughnut Ring with Rounded Pills & Spacing)
    // -------------------------------------------------------------
    try {
        const statusCanvas = document.getElementById('statusDonutChart');
        if (statusCanvas) {
            new Chart(statusCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['กำลังดำเนินการ', 'ยังไม่เริ่ม', 'เสร็จแล้ว', 'มีปัญหา/ล่าช้า'],
                    datasets: [{
                        data: [
                            <?= (int)$stats['in_progress'] ?>,
                            <?= (int)$stats['not_started'] ?>,
                            <?= (int)$stats['completed'] ?>,
                            <?= (int)$stats['has_problem'] ?>
                        ],
                        backgroundColor: [
                            '#3b82f6', // กำลังดำเนินการ (Vivid Royal Blue)
                            '#f59e0b', // ยังไม่เริ่ม (Golden Amber)
                            '#10b981', // เสร็จแล้ว (Fresh Emerald)
                            '#f43f5e'  // มีปัญหา/ล่าช้า (Rose Red)
                        ],
                        hoverBackgroundColor: [
                            '#2563eb',
                            '#d97706',
                            '#059669',
                            '#e11d48'
                        ],
                        borderWidth: 0,
                        borderRadius: 6,
                        spacing: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '72%',
                    animation: isThemeChange ? false : {
                        duration: 350,
                        easing: 'easeOutQuad'
                    },
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
                                    const total = <?= max(1, (int)$stats['sub_total']) ?>;
                                    const val = context.raw;
                                    const pct = ((val / total) * 100).toFixed(1);
                                    return ` ${context.label}: ${val} โครงการ (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    } catch (err) {
        console.error('Error creating Status Donut Chart:', err);
    }

    // -------------------------------------------------------------
    // กราฟที่ 2: งบประมาณ (Budget Donut Gauge with Rounded Arc)
    // -------------------------------------------------------------
    try {
        const budgetCanvas = document.getElementById('budgetDonutChart');
        if (budgetCanvas) {
            const remainingColor = isDark ? 'rgba(255, 255, 255, 0.08)' : '#f1f5f9';
            new Chart(budgetCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['เบิกจ่ายแล้ว', 'คงเหลือ'],
                    datasets: [{
                        data: [
                            <?= (float)$stats['total_disbursed'] ?>,
                            <?= max(0, (float)$stats['total_remaining']) ?>
                        ],
                        backgroundColor: [
                            '#3b82f6', // เบิกจ่ายแล้ว
                            remainingColor // คงเหลือ
                        ],
                        hoverBackgroundColor: [
                            '#2563eb',
                            isDark ? 'rgba(255, 255, 255, 0.14)' : '#e2e8f0'
                        ],
                        borderWidth: 0,
                        borderRadius: 6,
                        spacing: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '72%',
                    animation: isThemeChange ? false : {
                        duration: 350,
                        easing: 'easeOutQuad'
                    },
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
                                    const val = Number(context.raw).toLocaleString('th-TH');
                                    return ` ${context.label}: ${val} บาท`;
                                }
                            }
                        }
                    }
                }
            });
        }
    } catch (err) {
        console.error('Error creating Budget Donut Chart:', err);
    }

    // -------------------------------------------------------------
    // กราฟที่ 3: โครงการตามประเภท (Project Categories Horizontal Bar Chart)
    // -------------------------------------------------------------
    try {
        const catCanvas = document.getElementById('categoryBarChart');
        const catData = <?= json_encode($stats['category_data'] ?? [], JSON_UNESCAPED_UNICODE) ?>;
        if (catCanvas && catData && catData.length > 0) {
            const catCtx = catCanvas.getContext('2d');
            const isMobile = window.innerWidth < 640;

            const labels = catData.map(c => c.short_name || ('ประเภท ' + c.id));
            const mainCounts = catData.map(c => parseInt(c.project_count || 0));
            const subCounts = catData.map(c => parseInt(c.sub_project_count || 0));

            // Gradients for Main Projects (Emerald)
            const mainGrad = catCtx.createLinearGradient(0, 0, 300, 0);
            mainGrad.addColorStop(0, '#059669');
            mainGrad.addColorStop(1, '#10b981');

            const mainHoverGrad = catCtx.createLinearGradient(0, 0, 300, 0);
            mainHoverGrad.addColorStop(0, '#10b981');
            mainHoverGrad.addColorStop(1, '#34d399');

            // Gradients for Sub Projects (Blue)
            const subGrad = catCtx.createLinearGradient(0, 0, 300, 0);
            subGrad.addColorStop(0, '#2563eb');
            subGrad.addColorStop(1, '#60a5fa');

            const subHoverGrad = catCtx.createLinearGradient(0, 0, 300, 0);
            subHoverGrad.addColorStop(0, '#3b82f6');
            subHoverGrad.addColorStop(1, '#93c5fd');

            // Custom Plugin: แสดงตัวเลขโครงการกำกับที่ปลายแท่งกราฟเมื่อมีโครงการ
            const categoryValueLabelsPlugin = {
                id: 'categoryValueLabels',
                afterDatasetsDraw(chart) {
                    const { ctx } = chart;
                    chart.data.datasets.forEach((dataset, datasetIndex) => {
                        const meta = chart.getDatasetMeta(datasetIndex);
                        if (!meta.hidden) {
                            meta.data.forEach((element, index) => {
                                const val = dataset.data[index];
                                if (val > 0) {
                                    ctx.save();
                                    ctx.font = 'bold ' + (isMobile ? '9.5px' : '10.5px') + " 'Prompt', 'Sarabun', sans-serif";
                                    ctx.fillStyle = datasetIndex === 0 
                                        ? (isDark ? '#34d399' : '#059669') 
                                        : (isDark ? '#60a5fa' : '#2563eb');
                                    ctx.textAlign = 'left';
                                    ctx.textBaseline = 'middle';
                                    ctx.fillText(val.toString(), element.x + 5, element.y);
                                    ctx.restore();
                                }
                            });
                        }
                    });
                }
            };

            const maxCount = Math.max(...mainCounts, ...subCounts, 1);

            new Chart(catCanvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'โครงการหลัก',
                            data: mainCounts,
                            backgroundColor: mainGrad,
                            hoverBackgroundColor: mainHoverGrad,
                            borderRadius: { topRight: 6, bottomRight: 6, topLeft: 2, bottomLeft: 2 },
                            borderSkipped: false,
                            barPercentage: 0.75,
                            categoryPercentage: 0.82
                        },
                        {
                            label: 'โครงการย่อย',
                            data: subCounts,
                            backgroundColor: subGrad,
                            hoverBackgroundColor: subHoverGrad,
                            borderRadius: { topRight: 6, bottomRight: 6, topLeft: 2, bottomLeft: 2 },
                            borderSkipped: false,
                            barPercentage: 0.75,
                            categoryPercentage: 0.82
                        }
                    ]
                },
                plugins: [categoryValueLabelsPlugin],
                options: {
                    indexAxis: 'y', // แนวนอน
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 4,
                            bottom: 4,
                            left: 4,
                            right: isMobile ? 32 : 44
                        }
                    },
                    animation: isThemeChange ? false : {
                        duration: 350,
                        easing: 'easeOutQuad'
                    },
                    plugins: {
                        legend: {
                            display: false // มี Custom Pill บนหัวการ์ดแล้ว
                        },
                        tooltip: {
                            backgroundColor: tooltipBg,
                            titleColor: tooltipTitle,
                            bodyColor: tooltipBody,
                            borderColor: tooltipBorder,
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 10,
                            boxPadding: 4,
                            callbacks: {
                                title: (items) => {
                                    const idx = items[0]?.dataIndex;
                                    const c = catData[idx];
                                    return c ? c.name : '';
                                },
                                label: (context) => {
                                    const val = context.parsed.x || 0;
                                    const label = context.dataset.label || '';
                                    return ` ${label}: ${val} โครงการ`;
                                },
                                afterBody: (items) => {
                                    const idx = items[0]?.dataIndex;
                                    const c = catData[idx];
                                    if (!c) return [];
                                    const budget = parseFloat(c.total_budget || 0);
                                    const disbursed = parseFloat(c.total_disbursed || 0);
                                    return [
                                        `งบประมาณรวม: ${budget.toLocaleString('th-TH')} บาท`,
                                        `ยอดเบิกจ่าย: ${disbursed.toLocaleString('th-TH')} บาท`
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            suggestedMax: maxCount + (maxCount < 5 ? 1 : 2),
                            ticks: {
                                stepSize: 1,
                                precision: 0,
                                color: tickColor,
                                font: { family: "'Prompt', 'Sarabun', sans-serif", size: isMobile ? 9.5 : 10.5 }
                            },
                            grid: {
                                color: gridColor
                            },
                            border: {
                                display: false
                            }
                        },
                        y: {
                            ticks: {
                                color: labelColor,
                                font: {
                                    family: "'Prompt', 'Sarabun', sans-serif",
                                    size: isMobile ? 9.5 : 11,
                                    weight: '600'
                                },
                                padding: 6
                            },
                            grid: { display: false },
                            border: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    } catch (err) {
        console.error('Error creating Category Bar Chart:', err);
    }
}

let dashboardChartsTimer = null;
function scheduleDashboardChartsInit(isThemeChange = false) {
    clearTimeout(dashboardChartsTimer);
    dashboardChartsTimer = setTimeout(() => {
        renderDashboardCharts(isThemeChange);
    }, 30);
}

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
        if (document.getElementById('statusDonutChart') || document.getElementById('budgetDonutChart') || document.getElementById('categoryBarChart')) {
            scheduleDashboardChartsInit(true);
        }
    });
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>
