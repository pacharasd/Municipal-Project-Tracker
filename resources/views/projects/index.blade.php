<?php
ob_start();
$title = "จัดการโครงการหลักและโครงการย่อย";

// Prepare JSON summary for reactive Alpine.js pagination & live search
$projectsSummary = [];
foreach ($projects as $p) {
    $subKeywords = [];
    $completedCount = 0;
    $hasProblem = false;
    foreach ($p['sub_projects'] as $sub) {
        $subKeywords[] = ($sub['project_code'] ?? '') . ' ' . ($sub['name'] ?? '') . ' ' . ($sub['responsible_name'] ?? '');
        if (($sub['status'] ?? '') === 'completed') $completedCount++;
        if (($sub['status'] ?? '') === 'has_problem') $hasProblem = true;
    }

    $calcStatus = 'in_progress';
    if (!empty($p['sub_projects'])) {
        if ($hasProblem) {
            $calcStatus = 'has_problem';
        } elseif ($completedCount === count($p['sub_projects'])) {
            $calcStatus = 'completed';
        }
    } elseif (($p['progress'] ?? 0) >= 100) {
        $calcStatus = 'completed';
    }

    $projectsSummary[] = [
        'id' => (int)$p['id'],
        'code' => (string)$p['project_code'],
        'name' => (string)$p['name'],
        'fiscal_year_id' => (string)$p['fiscal_year_id'],
        'department_id' => (string)$p['department_id'],
        'budget' => (float)$p['budget'],
        'sub_count' => count($p['sub_projects']),
        'status' => $calcStatus,
        'search_text' => mb_strtolower(
            ($p['project_code'] ?? '') . ' ' . 
            ($p['name'] ?? '') . ' ' . 
            ($p['department_name'] ?? '') . ' ' . 
            ($p['fiscal_year'] ?? '') . ' ' . 
            ($p['description'] ?? '') . ' ' . 
            implode(' ', $subKeywords),
            'UTF-8'
        ),
    ];
}
$projectsJson = json_encode($projectsSummary, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

$initPage = max(1, (int)($_GET['page'] ?? 1));
$initPerPageRaw = $_GET['per_page'] ?? '5';
$initPerPage = ($initPerPageRaw === 'all') ? 'all' : max(1, (int)$initPerPageRaw);
?>

<div class="space-y-6" x-data="mainProjectsPage()">
    <!-- Header with Action -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 mb-1">
                <a href="<?= \App\Core\Router::url('/dashboard') ?>" class="hover:underline">หน้าหลัก</a>
                <span>/</span>
                <span>โครงการหลักและย่อย</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">โครงการหลักและโครงการย่อย</h1>
            <p class="text-sm text-slate-500 mt-1">บริหารจัดการโครงการตามแผนพัฒนาเทศบาล ติดตามความก้าวหน้าและงบประมาณแบบลำดับชั้น</p>
        </div>
        <div class="flex items-center gap-3">
            <?php if (\App\Core\Auth::canManageProjects()): ?>
                <button type="button" @click="createModal = true" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-600/30 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> สร้างโครงการหลักใหม่
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters Section -->
    <form action="<?= \App\Core\Router::url('/projects') ?>" method="GET" @submit.prevent="currentPage = 1; scrollToTop(); syncUrl()" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- Search Keyword -->
        <div class="lg:col-span-2 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </div>
            <input type="text" name="search" x-model="search" @input="currentPage = 1; syncUrl()" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="ค้นหาชื่อ รหัสโครงการ หรือโครงการย่อย..." class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        </div>

        <!-- Fiscal Year Filter -->
        <div>
            <select name="fiscal_year_id" x-model="fiscalYearFilter" @change="currentPage = 1; syncUrl()" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">-- ทุกปีงบประมาณ --</option>
                <?php foreach ($fiscalYears as $fy): ?>
                    <option value="<?= $fy['id'] ?>" <?= $filters['fiscal_year_id'] == $fy['id'] ? 'selected' : '' ?>>ปี <?= $fy['year'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Department Filter -->
        <div>
            <select name="department_id" x-model="departmentFilter" @change="currentPage = 1; syncUrl()" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">-- ทุกสำนัก/กอง --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>" <?= $filters['department_id'] == $dept['id'] ? 'selected' : '' ?>><?= htmlspecialchars($dept['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Submit & Clear Buttons -->
        <div class="flex items-center gap-2">
            <button type="submit" class="w-full py-2 px-4 text-sm font-medium text-white bg-slate-800 rounded-xl hover:bg-slate-900 transition-colors cursor-pointer">
                ค้นหา
            </button>
            <button type="button" @click="resetFilters()" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer transition-colors" title="ล้างตัวกรอง">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
            </button>
        </div>
    </form>

    <!-- Toolbar: Per Page Selector, Total Counts & Status Filter Pills -->
    <div id="projects-toolbar" class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm text-xs text-slate-600">
        <!-- Left: Per Page Selector & Summary Counts -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-1.5">
                <span class="font-medium text-slate-500">แสดง:</span>
                <select x-model="perPage" @change="setPerPage($event.target.value)" class="px-2.5 py-1 text-xs rounded-xl border border-slate-300 bg-white font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer">
                    <option value="5">5 โครงการ / หน้า</option>
                    <option value="10">10 โครงการ / หน้า</option>
                    <option value="20">20 โครงการ / หน้า</option>
                    <option value="all">แสดงทั้งหมด</option>
                </select>
            </div>

            <div class="hidden sm:block text-slate-300">|</div>

            <div>
                พบ <span class="font-bold text-slate-900" x-text="filteredProjects.length"></span> โครงการหลัก 
                <span class="text-slate-400">(รวม <span class="font-bold text-emerald-600" x-text="totalFilteredSubCount"></span> โครงการย่อย)</span>
            </div>
        </div>

        <!-- Right: Status Filter Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
            <button type="button" @click="setStatusFilter('all')" 
                    :class="statusFilter === 'all' ? 'bg-slate-800 dark:bg-slate-700 text-white font-semibold shadow-sm' : 'bg-slate-50 dark:bg-white/[0.05] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.08] border border-slate-200 dark:border-white/[0.08]'"
                    class="px-2.5 py-1 rounded-xl text-xs transition cursor-pointer">
                ทั้งหมด (<span x-text="allProjects.length"></span>)
            </button>
            <button type="button" @click="setStatusFilter('in_progress')" 
                    :class="statusFilter === 'in_progress' ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'bg-blue-50/60 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 hover:bg-blue-100/60 dark:hover:bg-blue-900/40 border border-blue-200 dark:border-blue-800/40'"
                    class="px-2.5 py-1 rounded-xl text-xs transition cursor-pointer">
                กำลังดำเนินการ
            </button>
            <button type="button" @click="setStatusFilter('completed')" 
                    :class="statusFilter === 'completed' ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'bg-emerald-50/60 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100/60 dark:hover:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-800/40'"
                    class="px-2.5 py-1 rounded-xl text-xs transition cursor-pointer">
                เสร็จสิ้น
            </button>
            <button type="button" @click="setStatusFilter('has_problem')" 
                    :class="statusFilter === 'has_problem' ? 'bg-rose-600 text-white font-semibold shadow-sm' : 'bg-rose-50/60 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 hover:bg-rose-100/60 dark:hover:bg-rose-900/40 border border-rose-200 dark:border-rose-800/40'"
                    class="px-2.5 py-1 rounded-xl text-xs transition cursor-pointer">
                มีปัญหา
            </button>
        </div>
    </div>

    <!-- Main Projects List (Hierarchical Structure) -->
    <div id="projects-container" class="space-y-4">
        <!-- Empty state when search or filter yields no results -->
        <div x-show="filteredProjects.length === 0" x-cloak class="bg-white p-12 text-center rounded-2xl border border-slate-200 shadow-sm">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">ไม่พบโครงการตามเงื่อนไขที่ระบุ</h3>
            <p class="text-xs text-slate-500 mt-1">กรุณาลองปรับเปลี่ยนคำค้นหาหรือตัวกรองใหม่อีกครั้ง</p>
            <button type="button" @click="resetFilters()" class="mt-4 px-4 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl hover:bg-emerald-100 transition cursor-pointer">
                ล้างตัวกรองทั้งหมด
            </button>
        </div>

        <?php foreach ($projects as $index => $p): ?>
            <?php
            $isInitiallyVisible = ($initPerPage === 'all') || 
                ($index >= ($initPage - 1) * (int)$initPerPage && $index < $initPage * (int)$initPerPage);
            ?>
            <div class="bg-white dark:bg-[#181a20] rounded-2xl border border-slate-200/80 dark:border-white/[0.08] shadow-sm overflow-hidden" 
                 x-show="isProjectVisible(<?= $p['id'] ?>)" 
                 style="<?= $isInitiallyVisible ? '' : 'display: none;' ?>"
                 x-data="{ expanded: true }">
                <!-- Main Project Bar -->
                <div class="p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 dark:border-white/[0.06]">
                    <div class="flex items-start gap-4">
                        <button type="button" @click="expanded = !expanded" class="mt-1 p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-colors cursor-pointer" title="ย่อ/ขยายโครงการย่อย">
                            <i data-lucide="chevron-down" class="w-5 h-5 transform transition-transform" :class="expanded ? 'rotate-0' : '-rotate-90'"></i>
                        </button>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 text-xs font-mono font-bold rounded bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-white/[0.08]"><?= htmlspecialchars($p['project_code']) ?></span>
                                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/40"><?= htmlspecialchars($p['department_name']) ?></span>
                                <span class="px-2 py-0.5 text-xs text-slate-500 dark:text-slate-400">ปีงบ <?= $p['fiscal_year'] ?></span>
                            </div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white mt-1">
                                <a href="<?= \App\Core\Router::url("/projects/{$p['id']}") ?>" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                    <?= htmlspecialchars($p['name']) ?>
                                </a>
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1"><?= htmlspecialchars($p['description']) ?></p>
                        </div>
                    </div>

                    <!-- Right KPIs for Main Project -->
                    <div class="flex items-center gap-6 lg:justify-end">
                        <div class="text-right">
                            <div class="text-xs text-slate-400 dark:text-slate-400">งบประมาณรวม</div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white"><?= number_format($p['budget'], 2) ?> <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">บาท</span></div>
                        </div>

                        <!-- Progress indicator -->
                        <div class="w-36">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-slate-500 dark:text-slate-400">ความก้าวหน้า</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400"><?= number_format($p['progress'], 1) ?>%</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-white/[0.08] rounded-full h-2 overflow-hidden">
                                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-2 rounded-full" style="width: <?= min(100, $p['progress']) ?>%"></div>
                            </div>
                        </div>

                        <!-- View detail button -->
                        <a href="<?= \App\Core\Router::url("/projects/{$p['id']}") ?>" class="p-2 rounded-xl text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-colors" title="ดูรายละเอียดโครงการ">
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>

                <!-- Sub-projects accordion table -->
                <div x-show="expanded" class="bg-slate-50/70 dark:bg-[#101217] border-t border-slate-100 dark:border-white/[0.06] p-4 sm:p-5">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3.5 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="layers" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500"></i>
                            โครงการย่อยในความรับผิดชอบ (<?= count($p['sub_projects']) ?> โครงการ)
                        </span>
                        <a href="<?= \App\Core\Router::url("/projects/{$p['id']}") ?>" class="text-emerald-600 dark:text-emerald-400 hover:underline normal-case font-semibold text-xs flex items-center gap-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> เพิ่มโครงการย่อย
                        </a>
                    </div>

                    <?php if (empty($p['sub_projects'])): ?>
                        <div class="p-4 text-center text-xs text-slate-400 dark:text-slate-500 bg-white dark:bg-[#181a20] rounded-xl border border-slate-200 dark:border-white/[0.08]">
                            ยังไม่มีโครงการย่อยภายใต้โครงการหลักนี้
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                            <?php foreach ($p['sub_projects'] as $sub): ?>
                                <a href="<?= \App\Core\Router::url("/sub-projects/{$sub['id']}") ?>" 
                                   class="group flex flex-col justify-between p-4 rounded-xl bg-white dark:bg-[#181a20] border border-slate-200/90 dark:border-white/[0.08] hover:border-emerald-500/50 dark:hover:border-emerald-500/50 shadow-sm hover:shadow-md transition-all">
                                    <div>
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-[11px] font-mono text-slate-400 dark:text-slate-400 tracking-tight font-medium"><?= htmlspecialchars($sub['project_code']) ?></span>
                                            <?php
                                            $statusClass = match($sub['status']) {
                                                'completed' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/40',
                                                'in_progress' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800/40',
                                                'has_problem' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/40 animate-pulse',
                                                default => 'bg-slate-100 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700/60'
                                            };
                                            $statusLabel = match($sub['status']) {
                                                'completed' => 'เสร็จสิ้น',
                                                'in_progress' => 'กำลังดำเนินการ',
                                                'has_problem' => 'มีปัญหา',
                                                default => 'ยังไม่เริ่ม'
                                            };
                                            ?>
                                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold rounded-full border whitespace-nowrap <?= $statusClass ?>">
                                                <?= $statusLabel ?>
                                            </span>
                                        </div>
                                        <h3 class="text-sm font-bold text-slate-800 dark:text-white mt-2.5 line-clamp-2 min-h-[2.5rem] group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors" title="<?= htmlspecialchars($sub['name']) ?>">
                                            <?= htmlspecialchars($sub['name']) ?>
                                        </h3>
                                    </div>
                                    
                                    <div class="mt-3.5">
                                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                            <span>กิจกรรม: <b class="text-slate-700 dark:text-slate-200"><?= $sub['actual_activity_count'] ?></b> / <?= $sub['planned_activity_count'] ?> ครั้ง</span>
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400"><?= number_format($sub['progress'], 1) ?>%</span>
                                        </div>

                                        <div class="w-full bg-slate-100 dark:bg-white/[0.08] rounded-full h-1.5 mt-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full <?= $sub['status'] == 'has_problem' ? 'bg-rose-500' : 'bg-emerald-500' ?>" style="width: <?= min(100, $sub['progress']) ?>%"></div>
                                        </div>

                                        <div class="mt-3 pt-2.5 pb-0.5 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                            <span>งบ: <strong class="font-semibold text-slate-700 dark:text-slate-300"><?= number_format($sub['budget'], 0) ?></strong> บ.</span>
                                            <span class="truncate max-w-[150px] text-right" title="<?= htmlspecialchars($sub['responsible_name'] ?? 'ผู้รับผิดชอบ') ?>"><?= htmlspecialchars($sub['responsible_name'] ?? 'ผู้รับผิดชอบ') ?></span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination & Summary Footer Card -->
    <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500">
        <!-- Left: Showing X to Y of Z & Budget totals -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div>
                <template x-if="filteredProjects.length > 0">
                    <span>
                        แสดง <span class="font-bold text-slate-800" x-text="startIndex"></span> ถึง <span class="font-bold text-slate-800" x-text="endIndex"></span> 
                        จาก <span class="font-bold text-slate-800" x-text="filteredProjects.length"></span> โครงการหลัก
                        <template x-if="filteredProjects.length !== allProjects.length">
                            <span class="text-slate-400">(จากทั้งหมด <span x-text="allProjects.length"></span> โครงการ)</span>
                        </template>
                    </span>
                </template>
                <template x-if="filteredProjects.length === 0">
                    <span class="text-slate-400">ไม่พบรายการ</span>
                </template>
            </div>

            <div class="hidden sm:block text-slate-300">|</div>

            <div class="flex items-center gap-4 text-[11px]">
                <div>งบประมาณรวม: <span class="font-bold text-slate-900" x-text="totalFilteredBudget"></span> บาท</div>
                <div>โครงการย่อยรวม: <span class="font-bold text-emerald-600" x-text="totalFilteredSubCount"></span> โครงการ</div>
            </div>
        </div>

        <!-- Right: Pagination Buttons -->
        <template x-if="totalPages > 1 && perPage !== 'all'">
            <div class="flex items-center gap-1.5">
                <!-- First Page Button -->
                <button type="button" 
                        @click="setPage(1)" 
                        :disabled="currentPage === 1"
                        class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer"
                        title="หน้าแรก">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
                </button>

                <!-- Prev Button -->
                <button type="button" 
                        @click="prevPage()" 
                        :disabled="currentPage === 1"
                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed font-medium transition cursor-pointer flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    <span class="hidden sm:inline">ก่อนหน้า</span>
                </button>

                <!-- Page Numbers -->
                <div class="flex items-center gap-1">
                    <template x-for="(p, i) in visiblePages" :key="i">
                        <div>
                            <template x-if="p === '...'">
                                <span class="px-2 py-1 text-slate-400 font-semibold select-none">...</span>
                            </template>
                            <template x-if="p !== '...'">
                                <button type="button" 
                                        @click="setPage(p)" 
                                        :class="currentPage === p ? 'bg-emerald-600 text-white font-bold shadow-sm shadow-emerald-600/30 border-emerald-600' : 'bg-white text-slate-700 hover:bg-slate-100 border-slate-200'"
                                        class="w-8 h-8 rounded-lg border text-xs flex items-center justify-center font-medium transition cursor-pointer"
                                        x-text="p">
                                </button>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Next Button -->
                <button type="button" 
                        @click="nextPage()" 
                        :disabled="currentPage === totalPages"
                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed font-medium transition cursor-pointer flex items-center gap-1">
                    <span class="hidden sm:inline">ถัดไป</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </button>

                <!-- Last Page Button -->
                <button type="button" 
                        @click="setPage(totalPages)" 
                        :disabled="currentPage === totalPages"
                        class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer"
                        title="หน้าสุดท้าย">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Modal: Create Main Project -->
    <template x-teleport="body">
        <div x-show="createModal" x-cloak @click.self="createModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">สร้างโครงการหลักใหม่</h3>
                        <p class="text-xs text-slate-500 mt-0.5">เพิ่มโครงการหลักประจำปีงบประมาณตามแผนพัฒนาเทศบาล</p>
                    </div>
                    <button type="button" @click.stop="createModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                        <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                    </button>
                </div>

                <form action="<?= \App\Core\Router::url('/projects') ?>" method="POST" class="p-6 space-y-4">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">รหัสโครงการ <span class="text-rose-500">*</span></label>
                            <input type="text" name="project_code" required placeholder="เช่น PRJ-2568-004" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">ปีงบประมาณ <span class="text-rose-500">*</span></label>
                            <select name="fiscal_year_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <?php foreach ($fiscalYears as $fy): ?>
                                    <option value="<?= $fy['id'] ?>" <?= $fy['is_active'] ? 'selected' : '' ?>>ปี <?= $fy['year'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">ชื่อโครงการหลัก <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="ระบุชื่อโครงการหลัก..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">คำอธิบายและวัตถุประสงค์</label>
                        <textarea name="description" rows="2" placeholder="รายละเอียดของโครงการ..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">หมวดหมู่โครงการ <span class="text-rose-500">*</span></label>
                            <select name="category_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">หน่วยงาน / สำนัก / กอง <span class="text-rose-500">*</span></label>
                            <select name="department_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">งบประมาณรวม (บาท) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0" name="budget" required placeholder="0.00" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">วันที่เริ่มต้น <span class="text-rose-500">*</span></label>
                            <input type="date" name="start_date" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">วันที่สิ้นสุด <span class="text-rose-500">*</span></label>
                            <input type="date" name="end_date" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="createModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer">
                            ยกเลิก
                        </button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-sm shadow-emerald-600/30 cursor-pointer">
                            บันทึกโครงการหลัก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<script>
function mainProjectsPage() {
    return {
        createModal: false,
        allProjects: <?= $projectsJson ?>,
        search: <?= json_encode($filters['search'], JSON_UNESCAPED_UNICODE) ?> || '',
        fiscalYearFilter: <?= json_encode($filters['fiscal_year_id'], JSON_UNESCAPED_UNICODE) ?> || '',
        departmentFilter: <?= json_encode($filters['department_id'], JSON_UNESCAPED_UNICODE) ?> || '',
        statusFilter: 'all',
        currentPage: 1,
        perPage: 5,

        init() {
            const params = new URLSearchParams(window.location.search);
            const pageParam = parseInt(params.get('page'));
            if (pageParam && pageParam > 0) {
                this.currentPage = pageParam;
            }
            const perPageParam = params.get('per_page');
            if (perPageParam) {
                this.perPage = perPageParam === 'all' ? 'all' : parseInt(perPageParam);
            }
            if (params.get('status')) {
                this.statusFilter = params.get('status');
            }
        },

        get filteredProjects() {
            const q = (this.search || '').trim().toLowerCase();
            const fy = this.fiscalYearFilter;
            const dept = this.departmentFilter;
            const status = this.statusFilter;

            return this.allProjects.filter(p => {
                const matchSearch = !q || (p.search_text && p.search_text.includes(q));
                const matchFy = !fy || String(p.fiscal_year_id) === String(fy);
                const matchDept = !dept || String(p.department_id) === String(dept);
                const matchStatus = status === 'all' || p.status === status;
                return matchSearch && matchFy && matchDept && matchStatus;
            });
        },

        get totalPages() {
            if (this.perPage === 'all') return 1;
            const per = parseInt(this.perPage) || 5;
            return Math.max(1, Math.ceil(this.filteredProjects.length / per));
        },

        get paginatedIds() {
            if (this.perPage === 'all') {
                return new Set(this.filteredProjects.map(p => p.id));
            }
            const per = parseInt(this.perPage) || 5;
            const start = (this.currentPage - 1) * per;
            const slice = this.filteredProjects.slice(start, start + per);
            return new Set(slice.map(p => p.id));
        },

        isProjectVisible(id) {
            return this.paginatedIds.has(id);
        },

        get startIndex() {
            if (this.filteredProjects.length === 0) return 0;
            if (this.perPage === 'all') return 1;
            const per = parseInt(this.perPage) || 5;
            return (this.currentPage - 1) * per + 1;
        },

        get endIndex() {
            if (this.filteredProjects.length === 0) return 0;
            if (this.perPage === 'all') return this.filteredProjects.length;
            const per = parseInt(this.perPage) || 5;
            return Math.min(this.currentPage * per, this.filteredProjects.length);
        },

        get totalFilteredBudget() {
            const total = this.filteredProjects.reduce((sum, p) => sum + (p.budget || 0), 0);
            return new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total);
        },

        get totalFilteredSubCount() {
            return this.filteredProjects.reduce((sum, p) => sum + (p.sub_count || 0), 0);
        },

        get visiblePages() {
            const total = this.totalPages;
            const current = this.currentPage;
            if (total <= 7) {
                const pages = [];
                for (let i = 1; i <= total; i++) pages.push(i);
                return pages;
            }
            if (current <= 4) {
                return [1, 2, 3, 4, 5, '...', total];
            }
            if (current >= total - 3) {
                return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
            }
            return [1, '...', current - 1, current, current + 1, '...', total];
        },

        setPage(p) {
            if (p === '...' || p < 1 || p > this.totalPages || p === this.currentPage) return;
            this.currentPage = p;
            this.syncUrl();
            this.$nextTick(() => {
                this.scrollToTop();
            });
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.setPage(this.currentPage - 1);
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.setPage(this.currentPage + 1);
            }
        },

        setPerPage(val) {
            this.perPage = val === 'all' ? 'all' : parseInt(val);
            this.currentPage = 1;
            this.syncUrl();
            this.$nextTick(() => {
                this.scrollToTop();
            });
        },

        setStatusFilter(val) {
            this.statusFilter = val;
            this.currentPage = 1;
            this.syncUrl();
            this.$nextTick(() => {
                this.scrollToTop();
            });
        },

        resetFilters() {
            this.search = '';
            this.fiscalYearFilter = '';
            this.departmentFilter = '';
            this.statusFilter = 'all';
            this.currentPage = 1;
            this.syncUrl();
            this.$nextTick(() => {
                this.scrollToTop();
            });
        },

        syncUrl() {
            const url = new URL(window.location.href);
            if (this.currentPage > 1) {
                url.searchParams.set('page', this.currentPage);
            } else {
                url.searchParams.delete('page');
            }
            if (this.perPage !== 5) {
                url.searchParams.set('per_page', this.perPage);
            } else {
                url.searchParams.delete('per_page');
            }
            if (this.statusFilter !== 'all') {
                url.searchParams.set('status', this.statusFilter);
            } else {
                url.searchParams.delete('status');
            }
            window.history.replaceState({}, '', url.toString());
        },

        scrollToTop() {
            const main = document.querySelector('main');
            const target = document.getElementById('projects-toolbar') || document.getElementById('projects-container');
            if (main && target) {
                const targetRect = target.getBoundingClientRect();
                const mainRect = main.getBoundingClientRect();
                const scrollOffset = main.scrollTop + (targetRect.top - mainRect.top) - 16;
                main.scrollTo({
                    top: Math.max(0, scrollOffset),
                    behavior: 'smooth'
                });
            } else if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else if (main) {
                main.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>
