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
    header, aside, #role-switcher-banner, .no-print, nav, button, .pagination-bar {
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
    .print-show-all {
        display: table-row !important;
    }
}
.print-only {
    display: none;
}
</style>

<div class="space-y-6" x-data="{
    allProjects: <?= htmlspecialchars(json_encode($projects, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>,
    searchKeyword: '',
    currentPage: 1,
    perPage: 15,

    get filteredProjects() {
        const q = this.searchKeyword.trim().toLowerCase();
        if (!q) return this.allProjects;
        return this.allProjects.filter(p => {
            const name = (p.name || '').toLowerCase();
            const dept = (p.department_name || '').toLowerCase();
            const parent = (p.parent_name || '').toLowerCase();
            const resp = (p.responsible_person || p.responsible_name || '').toLowerCase();
            return name.includes(q) || dept.includes(q) || parent.includes(q) || resp.includes(q);
        });
    },

    get totalPages() {
        if (this.perPage === 'all') return 1;
        const per = parseInt(this.perPage) || 15;
        return Math.max(1, Math.ceil(this.filteredProjects.length / per));
    },

    get paginatedProjects() {
        if (this.perPage === 'all') return this.filteredProjects;
        const per = parseInt(this.perPage) || 15;
        const start = (this.currentPage - 1) * per;
        return this.filteredProjects.slice(start, start + per);
    },

    get startIndex() {
        if (this.filteredProjects.length === 0) return 0;
        if (this.perPage === 'all') return 1;
        const per = parseInt(this.perPage) || 15;
        return (this.currentPage - 1) * per + 1;
    },

    get endIndex() {
        if (this.filteredProjects.length === 0) return 0;
        if (this.perPage === 'all') return this.filteredProjects.length;
        const per = parseInt(this.perPage) || 15;
        return Math.min(this.filteredProjects.length, this.currentPage * per);
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
        if (p === '...') return;
        this.currentPage = Math.max(1, Math.min(this.totalPages, parseInt(p)));
    },

    prevPage() {
        if (this.currentPage > 1) this.currentPage--;
    },

    nextPage() {
        if (this.currentPage < this.totalPages) this.currentPage++;
    },

    formatNumber(val) {
        return Number(val || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    getStatusBadge(status) {
        switch (status) {
            case 'completed': return { label: 'เสร็จสิ้น', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
            case 'in_progress': return { label: 'กำลังดำเนินการ', class: 'bg-blue-50 text-blue-700 border-blue-200' };
            case 'has_problem': return { label: 'มีปัญหา', class: 'bg-rose-50 text-rose-700 border-rose-200' };
            case 'cancelled': return { label: 'ยกเลิก', class: 'bg-slate-50 text-slate-700 border-slate-200' };
            default: return { label: 'ยังไม่เริ่ม', class: 'bg-amber-50 text-amber-700 border-amber-200' };
        }
    }
}">

    <!-- Print-Only Header -->
    <div class="print-only text-center mb-6">
        <h1 class="text-xl font-bold text-black">รายงานความคืบหน้าและการติดตามโครงการเทศบาล</h1>
        <p class="text-sm text-gray-600">ข้อมูล ณ วันที่ <?= date('d/m/Y H:i น.') ?> โดยระบบ Municipal Project Tracker</p>
    </div>

    <!-- Page Header (No Print) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl">
                    <i data-lucide="file-spreadsheet" class="w-6 h-6"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">ระบบรายงานและการส่งออกข้อมูล</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">สร้างรายงานสรุปความก้าวหน้าโครงการและงบประมาณ พร้อมพิมพ์หรือส่งออกไฟล์</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Export Excel (Filtered) -->
            <a href="<?= Router::url('/reports/export-excel?' . http_build_query($_GET)) ?>" target="_blank"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>ส่งออก Excel</span>
            </a>

            <!-- Export PDF (Filtered) -->
            <a href="<?= Router::url('/reports/export-pdf?' . http_build_query($_GET)) ?>" target="_blank"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>ส่งออก PDF</span>
            </a>

            <!-- Print Page (Standard Browser Print) -->
            <button onclick="window.print()" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl shadow-sm transition-all cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>พิมพ์รายงาน</span>
            </button>
        </div>
    </div>

    <!-- Filter Card (No Print) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm no-print">
        <form action="<?= Router::url('/reports') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            
            <!-- Fiscal Year -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">ปีงบประมาณ</label>
                <select name="fiscal_year_id" 
                        class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                    <option value="">-- ทุกปีงบประมาณ --</option>
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
                    <option value="not_started" <?= in_array($status, ['not_started', 'ยังไม่เริ่ม', 'ยังไม่เริ่มดำเนินการ']) ? 'selected' : '' ?>>ยังไม่เริ่ม</option>
                    <option value="in_progress" <?= in_array($status, ['in_progress', 'กำลังดำเนินการ']) ? 'selected' : '' ?>>กำลังดำเนินการ</option>
                    <option value="completed" <?= in_array($status, ['completed', 'เสร็จสิ้น']) ? 'selected' : '' ?>>เสร็จสิ้น</option>
                    <option value="has_problem" <?= in_array($status, ['has_problem', 'มีปัญหา']) ? 'selected' : '' ?>>มีปัญหา</option>
                    <option value="cancelled" <?= in_array($status, ['cancelled', 'ยกเลิก']) ? 'selected' : '' ?>>ยกเลิก</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl text-sm shadow-sm transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>กรองข้อมูล</span>
                </button>
                <a href="<?= Router::url('/reports') ?>" 
                   class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-xl text-sm transition-colors text-center cursor-pointer"
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

    <!-- Report Table with In-Page Search & Pagination (No Print Controls) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="table" class="w-4 h-4 text-indigo-600"></i>
                ตารางข้อมูลรายงานความคืบหน้าโครงการ
            </h2>
            
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" x-model="searchKeyword" @input="currentPage = 1" placeholder="ค้นหาในตารางรายงาน..." 
                           class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                </div>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                    <span>แสดง:</span>
                    <select x-model="perPage" @change="currentPage = 1" class="px-2 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="all">ทั้งหมด</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/70 text-slate-600 dark:text-slate-400 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-3 w-10 text-center whitespace-nowrap">#</th>
                        <th class="py-3 px-3 min-w-[220px]">ชื่อโครงการ / ระดับ</th>
                        <th class="py-3 px-3 whitespace-nowrap">สำนัก / กอง</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap">ปีงบ</th>
                        <th class="py-3 px-3 text-right whitespace-nowrap">งบประมาณ (บาท)</th>
                        <th class="py-3 px-3 text-right whitespace-nowrap">เบิกจ่าย (บาท)</th>
                        <th class="py-3 px-3 text-right whitespace-nowrap">คงเหลือ (บาท)</th>
                        <th class="py-3 px-3 text-center w-28 whitespace-nowrap">ความคืบหน้า</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap">สถานะ</th>
                        <th class="py-3 px-3 whitespace-nowrap">ผู้รับผิดชอบ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <template x-if="paginatedProjects.length === 0">
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                ไม่พบข้อมูลโครงการตามเงื่อนไขที่ค้นหา
                            </td>
                        </tr>
                    </template>

                    <template x-for="(p, idx) in paginatedProjects" :key="p.id">
                        <tr class="hover:bg-slate-50/75 dark:hover:bg-slate-800/40 transition-colors"
                            :class="!p.parent_id ? 'bg-slate-50/40 dark:bg-slate-800/20 font-medium' : ''">
                            <td class="py-3 px-3 text-center text-slate-400 font-mono whitespace-nowrap"
                                x-text="(perPage === 'all' ? idx + 1 : (currentPage - 1) * perPage + idx + 1)"></td>
                            <td class="py-3 px-3">
                                <div class="font-medium text-slate-900 dark:text-white" x-text="p.name"></div>
                                <div class="text-[11px] text-slate-500">
                                    <template x-if="!p.parent_id">
                                        <span class="text-blue-600 dark:text-blue-400 font-semibold">[โครงการหลัก]</span>
                                    </template>
                                    <template x-if="p.parent_id">
                                        <span x-text="'โครงการย่อยภายใต้: ' + (p.parent_name || '-')"></span>
                                    </template>
                                </div>
                                <template x-if="p.problem_description">
                                    <div class="text-[11px] text-rose-600 dark:text-rose-400 mt-0.5 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                        <span x-text="'ปัญหา: ' + p.problem_description"></span>
                                    </div>
                                </template>
                            </td>
                            <td class="py-3 px-3 text-slate-600 dark:text-slate-300 whitespace-nowrap" x-text="p.department_name || 'ไม่ระบุ'"></td>
                            <td class="py-3 px-3 text-center text-slate-600 dark:text-slate-400 whitespace-nowrap" x-text="p.fiscal_year || '-'"></td>
                            <td class="py-3 px-3 text-right font-mono font-semibold text-slate-900 dark:text-white whitespace-nowrap"
                                x-text="formatNumber(p.budget)"></td>
                            <td class="py-3 px-3 text-right font-mono text-purple-600 dark:text-purple-400 whitespace-nowrap"
                                x-text="formatNumber(p.disbursed_amount)"></td>
                            <td class="py-3 px-3 text-right font-mono text-emerald-600 dark:text-emerald-400 whitespace-nowrap"
                                x-text="formatNumber(Number(p.budget || 0) - Number(p.disbursed_amount || 0))"></td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1 font-semibold text-slate-700 dark:text-slate-200">
                                    <span x-text="`${Number(p.progress || 0).toFixed(1)}%`"></span>
                                </div>
                                <div class="w-20 mx-auto bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden mt-1">
                                    <div class="h-full rounded-full bg-emerald-500" :style="`width: ${Math.min(100, Number(p.progress || 0))}%`"></div>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border whitespace-nowrap"
                                      :class="getStatusBadge(p.status).class"
                                      x-text="getStatusBadge(p.status).label"></span>
                            </td>
                            <td class="py-3 px-3 text-slate-600 dark:text-slate-400 whitespace-nowrap" x-text="p.responsible_person || p.responsible_name || '-'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar (Hidden in Print) -->
        <div class="pagination-bar p-4 bg-slate-50/60 dark:bg-slate-900/50 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
            <div>
                <template x-if="filteredProjects.length > 0">
                    <span>
                        แสดง <strong class="text-slate-800 dark:text-slate-200" x-text="startIndex"></strong> ถึง <strong class="text-slate-800 dark:text-slate-200" x-text="endIndex"></strong> จากทั้งหมด <strong class="text-slate-800 dark:text-slate-200" x-text="filteredProjects.length"></strong> รายการ
                    </span>
                </template>
                <template x-if="filteredProjects.length === 0">
                    <span>ไม่มีข้อมูลสำหรับแสดงผล</span>
                </template>
            </div>

            <!-- Page Navigation Buttons -->
            <template x-if="totalPages > 1 && perPage !== 'all'">
                <div class="flex items-center gap-1">
                    <button type="button" @click="setPage(1)" :disabled="currentPage === 1"
                            class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าแรก">
                        <i data-lucide="chevrons-left" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" @click="prevPage()" :disabled="currentPage === 1"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed font-medium transition cursor-pointer flex items-center gap-1">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">ก่อนหน้า</span>
                    </button>
                    <div class="flex items-center gap-1">
                        <template x-for="(p, i) in visiblePages" :key="i">
                            <div>
                                <template x-if="p === '...'">
                                    <span class="px-2 py-1 text-slate-400 select-none">...</span>
                                </template>
                                <template x-if="p !== '...'">
                                    <button type="button" @click="setPage(p)"
                                            :class="currentPage === p ? 'bg-emerald-600 text-white font-bold shadow-sm shadow-emerald-600/30 border-emerald-600' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border-slate-200 dark:border-slate-700'"
                                            class="w-8 h-8 rounded-lg border text-xs flex items-center justify-center font-medium transition cursor-pointer"
                                            x-text="p"></button>
                                </template>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="nextPage()" :disabled="currentPage === totalPages"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed font-medium transition cursor-pointer flex items-center gap-1">
                        <span class="hidden sm:inline">ถัดไป</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" @click="setPage(totalPages)" :disabled="currentPage === totalPages"
                            class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าสุดท้าย">
                        <i data-lucide="chevrons-right" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </template>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>
