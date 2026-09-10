<?php
ob_start();
$title = 'ประวัติการใช้งานและการตรวจสอบ (Audit Trail) - Municipal Project Tracker';
?>

<div class="space-y-6" x-data="{
    allLogs: <?= htmlspecialchars(json_encode($logs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>,
    selectedLog: null,
    searchQuery: '',
    currentPage: 1,
    perPage: 15,
    
    get filteredLogs() {
        const q = this.searchQuery.trim().toLowerCase();
        if (!q) return this.allLogs;
        return this.allLogs.filter(log => {
            const act = (log.action || '').toLowerCase();
            const mod = (log.module || '').toLowerCase();
            const user = (log.user_name || '').toLowerCase();
            const ip = (log.ip_address || '').toLowerCase();
            const rec = String(log.record_id || '');
            return act.includes(q) || mod.includes(q) || user.includes(q) || ip.includes(q) || rec.includes(q);
        });
    },

    get totalPages() {
        if (this.perPage === 'all') return 1;
        const per = parseInt(this.perPage) || 15;
        return Math.max(1, Math.ceil(this.filteredLogs.length / per));
    },

    get paginatedLogs() {
        if (this.perPage === 'all') return this.filteredLogs;
        const per = parseInt(this.perPage) || 15;
        const start = (this.currentPage - 1) * per;
        return this.filteredLogs.slice(start, start + per);
    },

    get startIndex() {
        if (this.filteredLogs.length === 0) return 0;
        if (this.perPage === 'all') return 1;
        const per = parseInt(this.perPage) || 15;
        return (this.currentPage - 1) * per + 1;
    },

    get endIndex() {
        if (this.filteredLogs.length === 0) return 0;
        if (this.perPage === 'all') return this.filteredLogs.length;
        const per = parseInt(this.perPage) || 15;
        return Math.min(this.filteredLogs.length, this.currentPage * per);
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

    formatJson(jsonStr) {
        if (!jsonStr) return '-';
        try {
            return JSON.stringify(JSON.parse(jsonStr), null, 2);
        } catch (e) {
            return jsonStr;
        }
    },

    formatDate(dtStr) {
        if (!dtStr) return '-';
        const d = new Date(dtStr.replace(/-/g, '/'));
        if (isNaN(d.getTime())) return dtStr;
        const day = String(d.getDate()).padStart(2, '0');
        const mon = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear() + 543;
        return `${day}/${mon}/${year}`;
    },

    formatTime(dtStr) {
        if (!dtStr) return '';
        const d = new Date(dtStr.replace(/-/g, '/'));
        if (isNaN(d.getTime())) return '';
        const hh = String(d.getHours()).padStart(2, '0');
        const mm = String(d.getMinutes()).padStart(2, '0');
        const ss = String(d.getSeconds()).padStart(2, '0');
        return `${hh}:${mm}:${ss} น.`;
    },

    getActionClass(act) {
        switch (act) {
            case 'CREATE': return 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800';
            case 'UPDATE': return 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-800';
            case 'INCREMENT_PROGRESS': return 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800';
            case 'DELETE':
            case 'REPORT_PROBLEM': return 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-900';
            case 'RESOLVE_PROBLEM': return 'bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 border-teal-200 dark:border-teal-800';
            case 'DISBURSE': return 'bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-800';
            default: return 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700';
        }
    }
}">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl">
                    <i data-lucide="shield-alert" class="w-6 h-6"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">บันทึกประวัติการตรวจสอบระบบ (Audit Trail)</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">บันทึกการกระทำทุกขั้นตอนแบบถาวร (Immutable Log) พร้อมข้อมูลก่อนและหลังแก้ไขเพื่อความโปร่งใส</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl text-xs font-semibold border border-emerald-200 dark:border-emerald-800">
                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                <span>เฉพาะผู้ดูแลระบบ (Administrator)</span>
            </span>
        </div>
    </div>

    <!-- Search & Control Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-80">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            <input type="text" x-model="searchQuery" @input="currentPage = 1" placeholder="ค้นหาการกระทำ, ผู้ใช้, โมดูล, IP..."
                   class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none dark:text-white">
        </div>
        
        <!-- Per Page Selector & Total Counter -->
        <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 w-full sm:w-auto justify-between sm:justify-end">
            <div class="relative shrink-0" x-data="{ openPerPage: false }" @click.outside="openPerPage = false">
                <button type="button" 
                        @click="openPerPage = !openPerPage" 
                        class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-amber-500/40 transition-all cursor-pointer shadow-2xs">
                    <span class="text-slate-400 dark:text-slate-500 font-normal">แสดงหน้าละ:</span>
                    <span class="font-bold text-slate-900 dark:text-white font-mono" x-text="perPage === 'all' ? 'ทั้งหมด' : perPage"></span>
                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-150 shrink-0" :class="{ 'rotate-180': openPerPage }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Themed Dropdown Flyout -->
                <div x-show="openPerPage" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-1.5 w-32 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-1.5 z-50 text-xs text-left">
                    <div class="px-2.5 py-1 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider font-heading border-b border-slate-100 dark:border-slate-800 mb-1">
                        จำนวนต่อหน้า
                    </div>
                    <div class="space-y-0.5">
                        <template x-for="opt in [10, 15, 25, 50, 'all']" :key="opt">
                            <button type="button" 
                                    @click="perPage = opt; currentPage = 1; openPerPage = false" 
                                    class="w-full text-left px-2.5 py-1.5 rounded-xl text-xs flex items-center justify-between transition cursor-pointer"
                                    :class="perPage == opt 
                                        ? 'bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 font-bold border border-amber-500/20' 
                                        : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5'">
                                <span x-text="opt === 'all' ? 'ทั้งหมด' : opt + ' รายการ'"></span>
                                <svg x-show="perPage == opt" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            <div class="font-medium text-slate-600 dark:text-slate-300">
                รวม <span class="font-bold text-slate-900 dark:text-white" x-text="filteredLogs.length"></span> รายการ
            </div>
        </div>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/70 text-slate-600 dark:text-slate-400 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-3 w-12 text-center">ID</th>
                        <th class="py-3 px-3 w-36">วันเวลา</th>
                        <th class="py-3 px-3">ผู้ดำเนินการ / บทบาท</th>
                        <th class="py-3 px-3 text-center">โมดูล</th>
                        <th class="py-3 px-3 text-center">การกระทำ (Action)</th>
                        <th class="py-3 px-3 text-center">Record ID</th>
                        <th class="py-3 px-3">IP Address</th>
                        <th class="py-3 px-3 text-center">ข้อมูลการเปลี่ยนแปลง (Diff)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <template x-if="paginatedLogs.length === 0">
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                ไม่พบบันทึกประวัติการตรวจสอบตามเงื่อนไขที่ค้นหา
                            </td>
                        </tr>
                    </template>

                    <template x-for="log in paginatedLogs" :key="log.id">
                        <tr class="hover:bg-slate-50/75 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3 px-3 text-center text-slate-400 font-mono" x-text="log.id"></td>
                            <td class="py-3 px-3 whitespace-nowrap text-slate-600 dark:text-slate-400 font-mono">
                                <div x-text="formatDate(log.created_at)"></div>
                                <div class="text-[10px] text-slate-400" x-text="formatTime(log.created_at)"></div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-medium text-slate-900 dark:text-white" x-text="log.user_name || 'ระบบอัตโนมัติ'"></div>
                                <div class="text-[10px] text-slate-400" x-text="log.role_label || 'System'"></div>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300" x-text="log.module"></span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border"
                                      :class="getActionClass(log.action)"
                                      x-text="log.action"></span>
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-slate-600 dark:text-slate-400" x-text="log.record_id ? `#${log.record_id}` : '-'"></td>
                            <td class="py-3 px-3 font-mono text-[11px] text-slate-500 dark:text-slate-400" x-text="log.ip_address || '127.0.0.1'"></td>
                            <td class="py-3 px-3 text-center">
                                <template x-if="log.old_values || log.new_values">
                                    <button type="button" 
                                            @click="selectedLog = log"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/60 rounded-lg text-[11px] font-medium transition-colors cursor-pointer">
                                        <i data-lucide="eye" class="w-3 h-3"></i>
                                        <span>ดูการเปลี่ยนแปลง</span>
                                    </button>
                                </template>
                                <template x-if="!log.old_values && !log.new_values">
                                    <span class="text-slate-300 dark:text-slate-600">-</span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <div class="p-4 bg-slate-50/60 dark:bg-slate-900/50 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
            <div>
                <template x-if="filteredLogs.length > 0">
                    <span>
                        แสดง <strong class="text-slate-800 dark:text-slate-200" x-text="startIndex"></strong> ถึง <strong class="text-slate-800 dark:text-slate-200" x-text="endIndex"></strong> จากทั้งหมด <strong class="text-slate-800 dark:text-slate-200" x-text="filteredLogs.length"></strong> รายการ
                    </span>
                </template>
                <template x-if="filteredLogs.length === 0">
                    <span>ไม่มีข้อมูลสำหรับแสดงผล</span>
                </template>
            </div>

            <!-- Page Navigation Buttons -->
            <template x-if="totalPages > 1 && perPage !== 'all'">
                <div class="flex items-center gap-1">
                    <!-- First Page -->
                    <button type="button" @click="setPage(1)" :disabled="currentPage === 1"
                            class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าแรก">
                        <i data-lucide="chevrons-left" class="w-3.5 h-3.5"></i>
                    </button>

                    <!-- Prev Page -->
                    <button type="button" @click="prevPage()" :disabled="currentPage === 1"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed font-medium transition cursor-pointer flex items-center gap-1">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">ก่อนหน้า</span>
                    </button>

                    <!-- Page Numbers -->
                    <div class="flex items-center gap-1">
                        <template x-for="(p, i) in visiblePages" :key="i">
                            <div>
                                <template x-if="p === '...'">
                                    <span class="px-2 py-1 text-slate-400 select-none">...</span>
                                </template>
                                <template x-if="p !== '...'">
                                    <button type="button" 
                                            @click="setPage(p)" 
                                            :class="currentPage === p ? 'bg-emerald-600 text-white font-bold shadow-sm shadow-emerald-600/30 border-emerald-600' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border-slate-200 dark:border-slate-700'"
                                            class="w-8 h-8 rounded-lg border text-xs flex items-center justify-center font-medium transition cursor-pointer"
                                            x-text="p">
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Next Page -->
                    <button type="button" @click="nextPage()" :disabled="currentPage === totalPages"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed font-medium transition cursor-pointer flex items-center gap-1">
                        <span class="hidden sm:inline">ถัดไป</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </button>

                    <!-- Last Page -->
                    <button type="button" @click="setPage(totalPages)" :disabled="currentPage === totalPages"
                            class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าสุดท้าย">
                        <i data-lucide="chevrons-right" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Modal: View JSON Diff Detail -->
    <template x-teleport="body">
    <div x-show="selectedLog" 
         x-cloak 
         @click.self="selectedLog = null"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop-smooth"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4 modal-box-smooth transform-gpu"
             @click.outside="selectedLog = null">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="p-2 bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl">
                        <i data-lucide="code" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>รายละเอียดการเปลี่ยนแปลงข้อมูล</span>
                            <span class="text-xs px-2 py-0.5 rounded font-mono bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300" x-text="`ID #${selectedLog?.id}`"></span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-text="`${selectedLog?.module} > ${selectedLog?.action} โดย ${selectedLog?.user_name || 'System'}`"></p>
                    </div>
                </div>
                <button type="button" @click.stop="selectedLog = null" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                    <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Old Values -->
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 flex items-center gap-1">
                        <i data-lucide="minus-circle" class="w-3.5 h-3.5"></i>
                        <span>ข้อมูลเดิมก่อนแก้ไข (Old Values)</span>
                    </span>
                    <pre class="p-3 bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/60 rounded-xl text-[11px] font-mono text-slate-800 dark:text-slate-200 overflow-x-auto max-h-60"
                         x-text="formatJson(selectedLog?.old_values)"></pre>
                </div>

                <!-- New Values -->
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                        <span>ข้อมูลใหม่หลังแก้ไข (New Values)</span>
                    </span>
                    <pre class="p-3 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/60 rounded-xl text-[11px] font-mono text-slate-800 dark:text-slate-200 overflow-x-auto max-h-60"
                         x-text="formatJson(selectedLog?.new_values)"></pre>
                </div>
            </div>

            <!-- Client metadata -->
            <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400 space-y-1">
                <div><span class="font-semibold text-slate-700 dark:text-slate-300">IP Address:</span> <span class="font-mono" x-text="selectedLog?.ip_address || '-'"></span></div>
                <div class="truncate"><span class="font-semibold text-slate-700 dark:text-slate-300">User Agent:</span> <span class="font-mono text-[10px]" x-text="selectedLog?.user_agent || '-'"></span></div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" @click="selectedLog = null"
                        class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium rounded-xl text-xs transition-colors cursor-pointer">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>
    </template>

</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>
