<?php
ob_start();
?>

<div class="space-y-6">
    <!-- Top Welcome Banner -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                แดชบอร์ดติดตามและบริหารโครงการ
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> ข้อมูลสดจาก MySQL
                </span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">สรุปสถานะโครงการหลัก โครงการย่อย การใช้จ่ายงบประมาณ และอัตราความสำเร็จประจำปีงบประมาณ 2568</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= \App\Core\Router::url('/reports') ?>" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                <i data-lucide="file-text" class="w-4 h-4"></i> รายงานสรุป
            </a>
            <a href="<?= \App\Core\Router::url('/projects') ?>" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-600/30">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> จัดการโครงการ
            </a>
        </div>
    </div>

    <!-- Alert Banner if Watchlist has items -->
    <?php if (!empty($watchlist)): ?>
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 shadow-sm flex items-start gap-3">
            <div class="p-2 rounded-lg bg-amber-100 text-amber-700 flex-shrink-0 mt-0.5">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div class="flex-1">
                <div class="text-sm font-bold flex items-center gap-2">
                    แจ้งเตือนโครงการในบัญชีเฝ้าระวัง (Watchlist) จำนวน <?= count($watchlist) ?> โครงการ
                </div>
                <div class="text-xs text-amber-800 mt-1">
                    มีโครงการที่ติดปัญหาการดำเนินงาน หรือเลยกำหนดระยะเวลาสิ้นสุด กรุณาตรวจสอบและดำเนินการแก้ไข
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <?php foreach (array_slice($watchlist, 0, 3) as $w): ?>
                        <a href="<?= \App\Core\Router::url("/sub-projects/{$w['id']}") ?>" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-white border border-amber-300 text-amber-900 hover:bg-amber-100/50 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <?= htmlspecialchars($w['name']) ?> (<?= htmlspecialchars($w['status'] == 'has_problem' ? 'มีปัญหา' : 'เลยกำหนด') ?>)
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 4 KPI Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Card 1: Total Projects -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">โครงการทั้งหมด</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="folder-kanban" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-slate-900"><?= number_format($stats['main_total']) ?></span>
                <span class="text-xs text-slate-500">โครงการหลัก</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center justify-between border-t border-slate-100 pt-2">
                <span>ประกอบด้วย <?= number_format($stats['sub_total']) ?> โครงการย่อย</span>
                <span class="text-emerald-600 font-semibold"><?= number_format($stats['completed']) ?> เสร็จสิ้น</span>
            </div>
        </div>

        <!-- Card 2: Status Breakdown -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">สถานะการดำเนินงาน</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-emerald-600"><?= number_format($stats['in_progress']) ?></span>
                <span class="text-xs text-slate-500">กำลังดำเนินการ</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs border-t border-slate-100 pt-2">
                <span class="text-slate-500">ยังไม่เริ่ม: <?= number_format($stats['not_started']) ?></span>
                <span class="text-rose-600 font-bold">มีปัญหา: <?= number_format($stats['has_problem']) ?></span>
            </div>
        </div>

        <!-- Card 3: Budget & Disbursement -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">งบประมาณรวม</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900"><?= number_format($stats['total_budget'], 2) ?> <span class="text-xs font-normal text-slate-500">บาท</span></div>
                <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                    <span>เบิกจ่ายแล้ว: <?= number_format($stats['disbursement_pct'], 1) ?>%</span>
                    <span class="font-semibold text-emerald-600"><?= number_format($stats['total_disbursed'], 2) ?> บ.</span>
                </div>
            </div>
            <!-- Disbursement Progress Bar -->
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                <div class="bg-purple-600 h-1.5 rounded-full transition-all duration-500" style="width: <?= min(100, $stats['disbursement_pct']) ?>%"></div>
            </div>
        </div>

        <!-- Card 4: Average Progress -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">ความสำเร็จเฉลี่ยรวม</span>
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <i data-lucide="award" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-teal-600"><?= number_format($stats['avg_progress'], 2) ?>%</span>
                <span class="text-xs text-slate-400">จากเป้าหมาย 100%</span>
            </div>
            <!-- Average Progress Bar -->
            <div class="w-full bg-slate-100 rounded-full h-2 mt-3 overflow-hidden">
                <div class="bg-gradient-to-r from-teal-500 to-emerald-500 h-2 rounded-full transition-all duration-500" style="width: <?= min(100, $stats['avg_progress']) ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Charts Section (2 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart 1: Project Status Doughnut -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-emerald-600"></i>
                    สัดส่วนโครงการย่อยตามสถานะ
                </h2>
                <span class="text-xs text-slate-400">ทั้งหมด <?= $stats['sub_total'] ?> โครงการ</span>
            </div>
            <div class="h-64 relative flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Budget vs Disbursement -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-5 h-5 text-purple-600"></i>
                    งบประมาณที่ได้รับ เทียบกับ ยอดเบิกจ่าย
                </h2>
                <span class="text-xs text-slate-400">คงเหลือ <?= number_format($stats['total_remaining'], 2) ?> บาท</span>
            </div>
            <div class="h-64 relative">
                <canvas id="budgetChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Department Progress & Top/Bottom Projects -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Department Performance (2 cols) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="building-2" class="w-5 h-5 text-blue-600"></i>
                    ผลงานและความก้าวหน้ารายสำนัก / กอง
                </h2>
                <span class="text-xs text-slate-400">เรียงตามสำนัก/กอง</span>
            </div>
            <div class="h-72 relative">
                <canvas id="deptChart"></canvas>
            </div>
        </div>

        <!-- Top / Bottom Projects (1 col) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2 mb-4">
                    <i data-lucide="trending-up" class="w-5 h-5 text-emerald-600"></i>
                    โครงการที่มีความก้าวหน้าสูงสุด
                </h2>
                <div class="space-y-3">
                    <?php foreach ($stats['top_projects'] as $tp): ?>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                            <div class="truncate mr-2">
                                <div class="text-xs font-semibold text-slate-800 truncate"><?= htmlspecialchars($tp['name']) ?></div>
                                <div class="text-[11px] text-slate-400 mt-0.5">งบ <?= number_format($tp['budget'], 0) ?> บาท</div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-sm font-bold text-emerald-600"><?= number_format($tp['progress'], 1) ?>%</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">โครงการที่ต้องเร่งรัด</div>
                <div class="space-y-2">
                    <?php foreach (array_slice($stats['bottom_projects'], 0, 2) as $bp): ?>
                        <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-rose-50/50 border border-rose-100">
                            <span class="truncate mr-2 text-rose-900"><?= htmlspecialchars($bp['name']) ?></span>
                            <span class="font-bold text-rose-600"><?= number_format($bp['progress'], 1) ?>%</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts Initialization -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Status Doughnut Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['เสร็จสิ้น', 'กำลังดำเนินการ', 'มีปัญหา', 'ยังไม่เริ่ม'],
            datasets: [{
                data: [
                    <?= $stats['completed'] ?>,
                    <?= $stats['in_progress'] ?>,
                    <?= $stats['has_problem'] ?>,
                    <?= $stats['not_started'] ?>
                ],
                backgroundColor: ['#10b981', '#3b82f6', '#ef4444', '#94a3b8'],
                borderWidth: 2,
                borderColor: '#ffffff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '68%'
        }
    });

    // 2. Budget vs Disbursement Bar Chart
    const budgetCtx = document.getElementById('budgetChart').getContext('2d');
    new Chart(budgetCtx, {
        type: 'bar',
        data: {
            labels: ['งบประมาณรวม', 'ยอดเบิกจ่ายแล้ว', 'งบประมาณคงเหลือ'],
            datasets: [{
                label: 'จำนวนเงิน (บาท)',
                data: [
                    <?= $stats['total_budget'] ?>,
                    <?= $stats['total_disbursed'] ?>,
                    <?= $stats['total_remaining'] ?>
                ],
                backgroundColor: ['#6366f1', '#10b981', '#f59e0b'],
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => value.toLocaleString('th-TH') + ' บ.'
                    }
                }
            }
        }
    });

    // 3. Department Performance Horizontal Bar
    const deptLabels = <?= json_encode(array_column($stats['department_data'], 'name'), JSON_UNESCAPED_UNICODE) ?>;
    const deptProgress = <?= json_encode(array_map(fn($d) => round((float)($d['avg_progress'] ?? 0), 1), $stats['department_data'])) ?>;

    const deptCtx = document.getElementById('deptChart').getContext('2d');
    new Chart(deptCtx, {
        type: 'bar',
        data: {
            labels: deptLabels,
            datasets: [{
                label: 'ความก้าวหน้าเฉลี่ย (%)',
                data: deptProgress,
                backgroundColor: '#0ea5e9',
                borderRadius: 6,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    min: 0,
                    max: 100,
                    ticks: { callback: (val) => val + '%' }
                }
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>
