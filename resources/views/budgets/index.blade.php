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
    allBudgets: <?= htmlspecialchars(json_encode($mainBudgets, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>,
    allDisbursements: <?= htmlspecialchars(json_encode($recentDisbursements, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>,
    subProjects: <?= htmlspecialchars(json_encode($subProjects, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>,
    
    disburseModalOpen: false,
    selectedSubProject: '',
    disburseAmount: '',

    // Pagination for Main Budgets
    budgetSearch: '',
    budgetPage: 1,
    budgetPerPage: 10,

    // Pagination for Disbursements
    disbSearch: '',
    disbPage: 1,
    disbPerPage: 10,

    get selectedProjectInfo() {
        return this.subProjects.find(p => p.id == this.selectedSubProject) || null;
    },

    // --- Main Budgets Pagination Getters ---
    get filteredBudgets() {
        const q = this.budgetSearch.trim().toLowerCase();
        if (!q) return this.allBudgets;
        return this.allBudgets.filter(b => {
            const name = (b.name || '').toLowerCase();
            const code = (b.project_code || '').toLowerCase();
            const dept = (b.department_name || '').toLowerCase();
            const fy = String(b.fiscal_year || '');
            return name.includes(q) || code.includes(q) || dept.includes(q) || fy.includes(q);
        });
    },

    get totalBudgetPages() {
        if (this.budgetPerPage === 'all') return 1;
        const per = parseInt(this.budgetPerPage) || 10;
        return Math.max(1, Math.ceil(this.filteredBudgets.length / per));
    },

    get paginatedBudgets() {
        if (this.budgetPerPage === 'all') return this.filteredBudgets;
        const per = parseInt(this.budgetPerPage) || 10;
        const start = (this.budgetPage - 1) * per;
        return this.filteredBudgets.slice(start, start + per);
    },

    get budgetStartIndex() {
        if (this.filteredBudgets.length === 0) return 0;
        if (this.budgetPerPage === 'all') return 1;
        const per = parseInt(this.budgetPerPage) || 10;
        return (this.budgetPage - 1) * per + 1;
    },

    get budgetEndIndex() {
        if (this.filteredBudgets.length === 0) return 0;
        if (this.budgetPerPage === 'all') return this.filteredBudgets.length;
        const per = parseInt(this.budgetPerPage) || 10;
        return Math.min(this.filteredBudgets.length, this.budgetPage * per);
    },

    get budgetVisiblePages() {
        return this.calculateVisiblePages(this.budgetPage, this.totalBudgetPages);
    },

    // --- Disbursements Pagination Getters ---
    get filteredDisbursements() {
        const q = this.disbSearch.trim().toLowerCase();
        if (!q) return this.allDisbursements;
        return this.allDisbursements.filter(d => {
            const pName = (d.project_name || '').toLowerCase();
            const pCode = (d.project_code || '').toLowerCase();
            const desc = (d.description || '').toLowerCase();
            const recip = (d.recipient || '').toLowerCase();
            const creator = (d.creator_name || '').toLowerCase();
            return pName.includes(q) || pCode.includes(q) || desc.includes(q) || recip.includes(q) || creator.includes(q);
        });
    },

    get totalDisbPages() {
        if (this.disbPerPage === 'all') return 1;
        const per = parseInt(this.disbPerPage) || 10;
        return Math.max(1, Math.ceil(this.filteredDisbursements.length / per));
    },

    get paginatedDisbursements() {
        if (this.disbPerPage === 'all') return this.filteredDisbursements;
        const per = parseInt(this.disbPerPage) || 10;
        const start = (this.disbPage - 1) * per;
        return this.filteredDisbursements.slice(start, start + per);
    },

    get disbStartIndex() {
        if (this.filteredDisbursements.length === 0) return 0;
        if (this.disbPerPage === 'all') return 1;
        const per = parseInt(this.disbPerPage) || 10;
        return (this.disbPage - 1) * per + 1;
    },

    get disbEndIndex() {
        if (this.filteredDisbursements.length === 0) return 0;
        if (this.disbPerPage === 'all') return this.filteredDisbursements.length;
        const per = parseInt(this.disbPerPage) || 10;
        return Math.min(this.filteredDisbursements.length, this.disbPage * per);
    },

    get disbVisiblePages() {
        return this.calculateVisiblePages(this.disbPage, this.totalDisbPages);
    },

    calculateVisiblePages(current, total) {
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

    formatCurrency(val) {
        return Number(val || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    formatDate(dtStr) {
        if (!dtStr) return '-';
        const d = new Date(dtStr.replace(/-/g, '/'));
        if (isNaN(d.getTime())) return dtStr;
        const day = String(d.getDate()).padStart(2, '0');
        const mon = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear() + 543;
        return `${day}/${mon}/${year}`;
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
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-medium rounded-xl shadow-sm hover:shadow-md transition-all text-sm cursor-pointer">
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
                    <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-500"></i>
                    <span>คงเหลือพร้อมจัดสรร</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform duration-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">ภาพรวมอัตราการเบิกจ่าย</span>
                <span class="p-2 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-xl">
                    <i data-lucide="percent" class="w-5 h-5"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 tracking-tight">
                    <?= $overallPercentage ?>%
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full mt-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-emerald-500 h-full rounded-full transition-all duration-500" style="width: <?= min(100, $overallPercentage) ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. Main Project Budget Ceiling Table (with Search & Pagination) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                    สถานะเพดานงบประมาณโครงการหลัก (Project Budget Allocations)
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">ยอดเบิกจ่ายจะคำนวณและสรุปอัตโนมัติจากโครงการย่อยทั้งหมดภายใต้โครงการหลัก</p>
            </div>

            <!-- Search & Per-Page Controls for Budgets -->
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" x-model="budgetSearch" @input="budgetPage = 1" placeholder="ค้นหาโครงการหลัก, รหัส..." 
                           class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                    <span>แสดง:</span>
                    <select x-model="budgetPerPage" @change="budgetPage = 1" class="px-2 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="all">ทั้งหมด</option>
                    </select>
                </div>
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
                    <template x-if="paginatedBudgets.length === 0">
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                ไม่พบข้อมูลโครงการที่ค้นหา
                            </td>
                        </tr>
                    </template>

                    <template x-for="b in paginatedBudgets" :key="b.id">
                        <tr class="hover:bg-slate-50/75 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-900 dark:text-white" x-text="b.name"></div>
                                <div class="text-xs font-mono text-slate-400 dark:text-slate-500" x-text="b.project_code"></div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-700 dark:text-slate-300" x-text="b.department_name || 'ไม่ระบุ'"></div>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400"
                                      x-text="`ปี ${b.fiscal_year || '-'}`"></span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-semibold text-slate-900 dark:text-white"
                                x-text="`฿${formatCurrency(b.budget)}`"></td>
                            <td class="py-3.5 px-4 text-right font-mono font-medium text-purple-600 dark:text-purple-400"
                                x-text="`฿${formatCurrency(b.disbursed_amount)}`"></td>
                            <td class="py-3.5 px-4 text-right font-mono font-medium text-emerald-600 dark:text-emerald-400"
                                x-text="`฿${formatCurrency(b.remaining_amount)}`"></td>
                            <td class="py-3.5 px-4">
                                <div class="w-36 mx-auto">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="font-medium text-slate-600 dark:text-slate-300" x-text="`${b.disbursement_percentage}%`"></span>
                                        <template x-if="Number(b.disbursement_percentage) >= 100">
                                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">ครบงบ</span>
                                        </template>
                                        <template x-if="Number(b.disbursement_percentage) >= 80 && Number(b.disbursement_percentage) < 100">
                                            <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold">ใกล้เต็ม</span>
                                        </template>
                                    </div>
                                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500"
                                             :class="Number(b.disbursement_percentage) > 90 ? 'bg-amber-500' : 'bg-blue-600'"
                                             :style="`width: ${Math.min(100, Number(b.disbursement_percentage))}%`"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a :href="'<?= Router::url('/projects/') ?>' + b.id" 
                                   class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                                    <span>ดูโครงการ</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Budget Pagination Bar -->
        <div class="p-4 bg-slate-50/60 dark:bg-slate-900/50 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
            <div>
                <template x-if="filteredBudgets.length > 0">
                    <span>
                        แสดง <strong class="text-slate-800 dark:text-slate-200" x-text="budgetStartIndex"></strong> ถึง <strong class="text-slate-800 dark:text-slate-200" x-text="budgetEndIndex"></strong> จากทั้งหมด <strong class="text-slate-800 dark:text-slate-200" x-text="filteredBudgets.length"></strong> โครงการหลัก
                    </span>
                </template>
                <template x-if="filteredBudgets.length === 0">
                    <span>ไม่มีข้อมูลสำหรับแสดงผล</span>
                </template>
            </div>

            <!-- Page Navigation Buttons -->
            <template x-if="totalBudgetPages > 1 && budgetPerPage !== 'all'">
                <div class="flex items-center gap-1">
                    <button type="button" @click="budgetPage = 1" :disabled="budgetPage === 1"
                            class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าแรก">
                        <i data-lucide="chevrons-left" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" @click="if(budgetPage > 1) budgetPage--" :disabled="budgetPage === 1"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed font-medium transition cursor-pointer flex items-center gap-1">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">ก่อนหน้า</span>
                    </button>
                    <div class="flex items-center gap-1">
                        <template x-for="(p, i) in budgetVisiblePages" :key="i">
                            <div>
                                <template x-if="p === '...'">
                                    <span class="px-2 py-1 text-slate-400 select-none">...</span>
                                </template>
                                <template x-if="p !== '...'">
                                    <button type="button" @click="budgetPage = p"
                                            :class="budgetPage === p ? 'bg-emerald-600 text-white font-bold shadow-sm shadow-emerald-600/30 border-emerald-600' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border-slate-200 dark:border-slate-700'"
                                            class="w-8 h-8 rounded-lg border text-xs flex items-center justify-center font-medium transition cursor-pointer"
                                            x-text="p"></button>
                                </template>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="if(budgetPage < totalBudgetPages) budgetPage++" :disabled="budgetPage === totalBudgetPages"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed font-medium transition cursor-pointer flex items-center gap-1">
                        <span class="hidden sm:inline">ถัดไป</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" @click="budgetPage = totalBudgetPages" :disabled="budgetPage === totalBudgetPages"
                            class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าสุดท้าย">
                        <i data-lucide="chevrons-right" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- 2. Recent Disbursements Log Table (with Search & Pagination) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="receipt" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                    ประวัติรายการเบิกจ่ายงบประมาณ (Disbursement Logs)
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">บันทึกประวัติการเบิกเงินพร้อมรายละเอียดผู้รับเงินและเอกสารหลักฐาน</p>
            </div>

            <!-- Search & Per-Page Controls for Disbursements -->
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" x-model="disbSearch" @input="disbPage = 1" placeholder="ค้นหารายการ, ผู้รับเงิน, โครงการ..." 
                           class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white">
                </div>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                    <span>แสดง:</span>
                    <select x-model="disbPerPage" @change="disbPage = 1" class="px-2 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="all">ทั้งหมด</option>
                    </select>
                </div>
            </div>
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
                    <template x-if="paginatedDisbursements.length === 0">
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                ไม่พบข้อมูลรายการเบิกจ่ายตามเงื่อนไขที่ค้นหา
                            </td>
                        </tr>
                    </template>

                    <template x-for="d in paginatedDisbursements" :key="d.id">
                        <tr class="hover:bg-slate-50/75 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-medium text-slate-900 dark:text-white" x-text="formatDate(d.disbursement_date)"></div>
                            </td>
                            <td class="py-3.5 px-4">
                                <a :href="'<?= Router::url('/sub-projects/') ?>' + d.project_id" 
                                   class="font-medium text-blue-600 dark:text-blue-400 hover:underline"
                                   x-text="d.project_name"></a>
                                <div class="text-xs font-mono text-slate-400" x-text="d.project_code"></div>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs text-slate-700 dark:text-slate-300" x-text="d.description"></td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400" x-text="d.recipient || '-'"></td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400"
                                x-text="`฿${formatCurrency(d.amount)}`"></td>
                            <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 text-xs" x-text="d.creator_name || 'ระบบ'"></td>
                            <td class="py-3.5 px-4 text-center">
                                <template x-if="d.evidence_file">
                                    <a :href="'<?= Router::url('/uploads/') ?>' + d.evidence_file" target="_blank"
                                       class="inline-flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                        <i data-lucide="paperclip" class="w-3.5 h-3.5"></i>
                                        <span>ไฟล์</span>
                                    </a>
                                </template>
                                <template x-if="!d.evidence_file">
                                    <span class="text-slate-300 dark:text-slate-600 text-xs">-</span>
                                </template>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form :action="'<?= Router::url('/budgets/disbursements/') ?>' + d.id + '/delete'" method="POST"
                                      @submit="if(!confirm(`ยืนยันยกเลิกรายการเบิกจ่ายจำนวน ฿${formatCurrency(d.amount)} บาท? (ยอดเงินจะคืนกลับเข้างบโครงการ)`)) $event.preventDefault();">
                                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                    <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded transition cursor-pointer" title="ยกเลิกรายการเบิกจ่าย">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Disbursements Pagination Bar -->
        <div class="p-4 bg-slate-50/60 dark:bg-slate-900/50 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
            <div>
                <template x-if="filteredDisbursements.length > 0">
                    <span>
                        แสดง <strong class="text-slate-800 dark:text-slate-200" x-text="disbStartIndex"></strong> ถึง <strong class="text-slate-800 dark:text-slate-200" x-text="disbEndIndex"></strong> จากทั้งหมด <strong class="text-slate-800 dark:text-slate-200" x-text="filteredDisbursements.length"></strong> รายการเบิกจ่าย
                    </span>
                </template>
                <template x-if="filteredDisbursements.length === 0">
                    <span>ไม่มีข้อมูลสำหรับแสดงผล</span>
                </template>
            </div>

            <!-- Page Navigation Buttons -->
            <template x-if="totalDisbPages > 1 && disbPerPage !== 'all'">
                <div class="flex items-center gap-1">
                    <button type="button" @click="disbPage = 1" :disabled="disbPage === 1"
                            class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าแรก">
                        <i data-lucide="chevrons-left" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" @click="if(disbPage > 1) disbPage--" :disabled="disbPage === 1"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed font-medium transition cursor-pointer flex items-center gap-1">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">ก่อนหน้า</span>
                    </button>
                    <div class="flex items-center gap-1">
                        <template x-for="(p, i) in disbVisiblePages" :key="i">
                            <div>
                                <template x-if="p === '...'">
                                    <span class="px-2 py-1 text-slate-400 select-none">...</span>
                                </template>
                                <template x-if="p !== '...'">
                                    <button type="button" @click="disbPage = p"
                                            :class="disbPage === p ? 'bg-emerald-600 text-white font-bold shadow-sm shadow-emerald-600/30 border-emerald-600' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border-slate-200 dark:border-slate-700'"
                                            class="w-8 h-8 rounded-lg border text-xs flex items-center justify-center font-medium transition cursor-pointer"
                                            x-text="p"></button>
                                </template>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="if(disbPage < totalDisbPages) disbPage++" :disabled="disbPage === totalDisbPages"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed font-medium transition cursor-pointer flex items-center gap-1">
                        <span class="hidden sm:inline">ถัดไป</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" @click="disbPage = totalDisbPages" :disabled="disbPage === totalDisbPages"
                            class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าสุดท้าย">
                        <i data-lucide="chevrons-right" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Modal: Disburse Budget -->
    <template x-teleport="body">
    <div x-show="disburseModalOpen" 
         x-cloak 
         @click.self="disburseModalOpen = false" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-md"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5"
             @click.outside="disburseModalOpen = false">
            
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
                            class="px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors cursor-pointer">
                        ยกเลิก
                    </button>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl shadow-sm transition-all text-sm cursor-pointer">
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
