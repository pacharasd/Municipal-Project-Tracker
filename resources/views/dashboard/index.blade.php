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

    <!-- Main Projects Progress & Top/Bottom Projects -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Project Performance (2 cols) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="folder-kanban" class="w-5 h-5 text-blue-600"></i>
                    ผลงานและความก้าวหน้ารายโครงการหลัก
                </h2>
                <span class="text-xs text-slate-400">เรียงตามโครงการหลัก</span>
            </div>
            <div class="h-72 relative">
                <canvas id="mainProjectChart"></canvas>
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

    <!-- All Sub-Projects Table (ตารางโครงการย่อยทั้งหมดด้านล่างสุด) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" 
         x-data="{ 
             search: '', 
             statusFilter: 'all' 
         }">
        <!-- Table Header -->
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900">ตารางโครงการย่อยทั้งหมด</h2>
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                                <?= count($subProjects) ?> โครงการ
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">ติดตามความก้าวหน้า งบประมาณ การเบิกจ่าย และสถานะการดำเนินงานรายโครงการย่อยทั้งหมด</p>
                    </div>
                </div>
            </div>

            <!-- Controls: Search & Filter -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Search Input -->
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    <input type="text" 
                           x-model="search" 
                           placeholder="ค้นหารหัส หรือชื่อโครงการ..." 
                           class="text-xs pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition w-48 sm:w-60">
                </div>

                <!-- Status Filter -->
                <select x-model="statusFilter" 
                        class="text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 transition text-slate-700">
                    <option value="all">ทุกสถานะ</option>
                    <option value="in_progress">กำลังดำเนินการ</option>
                    <option value="completed">เสร็จสิ้น</option>
                    <option value="has_problem">มีปัญหา</option>
                    <option value="not_started">ยังไม่เริ่ม</option>
                </select>

                <!-- Link to Main Projects -->
                <a href="<?= \App\Core\Router::url('/projects') ?>" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition border border-emerald-200">
                    <span>ดูแบบผังโครงการหลัก</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4">รหัส / โครงการย่อย</th>
                        <th class="py-3.5 px-4">โครงการหลักที่สังกัด</th>
                        <th class="py-3.5 px-4">สำนัก / กอง</th>
                        <th class="py-3.5 px-4">ผู้รับผิดชอบ</th>
                        <th class="py-3.5 px-4 text-right">งบประมาณ</th>
                        <th class="py-3.5 px-4 text-right">เบิกจ่ายแล้ว</th>
                        <th class="py-3.5 px-4 text-center">กิจกรรม</th>
                        <th class="py-3.5 px-4 text-center w-36">ความก้าวหน้า</th>
                        <th class="py-3.5 px-4 text-center">สถานะ</th>
                        <th class="py-3.5 px-4 text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    <?php if (empty($subProjects)): ?>
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                ยังไม่มีโครงการย่อยในระบบ
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subProjects as $sp): ?>
                            <?php
                            $spSearchText = htmlspecialchars(mb_strtolower($sp['project_code'] . ' ' . $sp['name'] . ' ' . ($sp['department_name'] ?? '') . ' ' . ($sp['responsible_name'] ?? '')));
                            $statusClass = match($sp['status']) {
                                'completed'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'in_progress' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'has_problem' => 'bg-rose-50 text-rose-700 border-rose-200 animate-pulse',
                                default       => 'bg-slate-100 text-slate-600 border-slate-200'
                            };
                            $statusLabel = match($sp['status']) {
                                'completed'   => 'เสร็จสิ้น',
                                'in_progress' => 'กำลังดำเนินการ',
                                'has_problem' => 'มีปัญหา',
                                default       => 'ยังไม่เริ่ม'
                            };
                            $progressColor = match(true) {
                                (float)$sp['progress'] >= 100 => 'bg-emerald-500',
                                (float)$sp['progress'] >= 50  => 'bg-sky-500',
                                (float)$sp['progress'] > 0   => 'bg-amber-500',
                                default                       => 'bg-slate-300'
                            };
                            $disbursedPct = (float)$sp['budget'] > 0 ? round(((float)$sp['disbursed_amount'] / (float)$sp['budget']) * 100, 1) : 0;
                            ?>
                            <tr class="hover:bg-slate-50/80 transition-colors"
                                x-show="(!search || '<?= $spSearchText ?>'.includes(search.toLowerCase())) && (statusFilter === 'all' || statusFilter === '<?= $sp['status'] ?>')">
                                <!-- Code & Name -->
                                <td class="py-3 px-4">
                                    <div class="font-mono text-[11px] font-bold text-slate-500"><?= htmlspecialchars($sp['project_code']) ?></div>
                                    <a href="<?= \App\Core\Router::url("/sub-projects/{$sp['id']}") ?>" 
                                       class="font-semibold text-slate-900 hover:text-emerald-600 transition line-clamp-1 mt-0.5">
                                        <?= htmlspecialchars($sp['name']) ?>
                                    </a>
                                </td>

                                <!-- Parent Project -->
                                <td class="py-3 px-4">
                                    <div class="text-[11px] text-slate-500 font-medium line-clamp-1 max-w-[200px]" title="<?= htmlspecialchars($sp['parent_name']) ?>">
                                        <span class="font-mono text-slate-400"><?= htmlspecialchars($sp['parent_code']) ?></span><br>
                                        <?= htmlspecialchars($sp['parent_name']) ?>
                                    </div>
                                </td>

                                <!-- Department -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                        <?= htmlspecialchars($sp['department_name'] ?? 'ไม่ระบุ') ?>
                                    </span>
                                </td>

                                <!-- Responsible Person -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[10px] font-bold">
                                            <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <span class="text-xs text-slate-700"><?= htmlspecialchars($sp['responsible_name'] ?? 'ไม่ระบุ') ?></span>
                                    </div>
                                </td>

                                <!-- Budget -->
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="font-bold text-slate-900"><?= number_format($sp['budget'], 2) ?></div>
                                    <div class="text-[10px] text-slate-400">บาท</div>
                                </td>

                                <!-- Disbursed -->
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="font-semibold text-emerald-600"><?= number_format($sp['disbursed_amount'], 2) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= $disbursedPct ?>%</div>
                                </td>

                                <!-- Activities -->
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                        <?= $sp['completed_activity_count'] ?> / <?= $sp['activity_count'] ?>
                                    </span>
                                </td>

                                <!-- Progress -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center justify-between text-xs mb-1 font-bold">
                                        <span class="text-slate-600"><?= number_format($sp['progress'], 1) ?>%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="<?= $progressColor ?> h-1.5 rounded-full" style="width: <?= min(100, $sp['progress']) ?>%"></div>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-[10px] font-semibold rounded-full border <?= $statusClass ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>

                                <!-- Action -->
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <a href="<?= \App\Core\Router::url("/sub-projects/{$sp['id']}") ?>" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 transition text-xs font-semibold"
                                       title="ดูรายละเอียดโครงการย่อย">
                                        <span>ดูข้อมูล</span>
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Table Footer Summary -->
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                แสดงทั้งหมด <span class="font-bold text-slate-700"><?= count($subProjects) ?></span> รายการโครงการย่อย
            </div>
            <div class="flex items-center gap-6">
                <div>
                    งบประมาณรวม: <span class="font-bold text-slate-900"><?= number_format(array_sum(array_column($subProjects, 'budget')), 2) ?></span> บาท
                </div>
                <div>
                    เบิกจ่ายแล้วรวม: <span class="font-bold text-emerald-600"><?= number_format(array_sum(array_column($subProjects, 'disbursed_amount')), 2) ?></span> บาท
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

    // 3. Main Projects Performance Horizontal Bar
    const mainProjects = <?= json_encode($stats['main_projects_data'] ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const mainProjLabels = mainProjects.map(p => p.name);
    const mainProjProgress = mainProjects.map(p => Math.round((parseFloat(p.progress) || 0) * 10) / 10);
    const mainProjColors = mainProjProgress.map(p => {
        if (p >= 100) return '#10b981';
        if (p >= 50) return '#0ea5e9';
        if (p > 0) return '#f59e0b';
        return '#94a3b8';
    });

    const mainProjectCtx = document.getElementById('mainProjectChart').getContext('2d');
    new Chart(mainProjectCtx, {
        type: 'bar',
        data: {
            labels: mainProjLabels,
            datasets: [{
                label: 'ความก้าวหน้า (%)',
                data: mainProjProgress,
                backgroundColor: mainProjColors,
                borderRadius: 6,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            const item = mainProjects[context[0].dataIndex];
                            return item ? `[${item.project_code}] ${item.name}` : context[0].label;
                        },
                        label: function(context) {
                            return `ความก้าวหน้า: ${context.raw}%`;
                        },
                        afterLabel: function(context) {
                            const item = mainProjects[context.dataIndex];
                            if (!item) return '';
                            const budget = Number(item.budget || 0).toLocaleString('th-TH');
                            const disbursed = Number(item.disbursed_amount || 0).toLocaleString('th-TH');
                            const subCount = item.sub_project_count || 0;
                            return [
                                `หน่วยงาน: ${item.department_name || '-'}`,
                                `งบประมาณ: ${budget} บาท (เบิกจ่าย: ${disbursed} บาท)`,
                                `โครงการย่อย: ${subCount} โครงการ`
                            ];
                        }
                    }
                }
            },
            scales: {
                x: {
                    min: 0,
                    max: 100,
                    ticks: { callback: (val) => val + '%' }
                },
                y: {
                    ticks: {
                        callback: function(value) {
                            const label = this.getLabelForValue(value);
                            if (typeof label === 'string' && label.length > 32) {
                                return label.substring(0, 30) + '...';
                            }
                            return label;
                        },
                        font: {
                            family: "'Prompt', sans-serif",
                            size: 12
                        }
                    }
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
