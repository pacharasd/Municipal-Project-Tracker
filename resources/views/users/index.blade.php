<?php
ob_start();
use App\Core\Auth;
use App\Core\Router;
use App\Core\Session;

$title = 'จัดการผู้ใช้งานและสิทธิ์การเข้าถึง - Municipal Project Tracker';
$currentUserId = Auth::id();
$csrfToken = $csrfToken ?? Session::token();

$totalUsersCount = count($users);
$adminCount = 0;
$executiveCount = 0;
$officerCount = 0;

foreach ($users as $u) {
    $rName = $u['role_name'] ?? '';
    if ($rName === 'admin') {
        $adminCount++;
    } elseif ($rName === 'executive') {
        $executiveCount++;
    } else {
        $officerCount++;
    }
}
?>

<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ 
    allUsers: <?= htmlspecialchars(json_encode($users, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>,
    rolesList: <?= htmlspecialchars(json_encode($roles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>,
    searchQuery: '', 
    selectedRole: '',
    currentPage: 1,
    perPage: 10,
    openPerPage: false,
    openRoleFilter: false,
    createModal: false,
    editModal: false,
    createRoleId: '',
    createRoleOpen: false,
    editRoleOpen: false,
    currentUserId: <?= (int)$currentUserId ?>,
    editUser: { id: '', name: '', email: '', position: '', phone: '', role_id: '' },
    
    getRoleNameById(id) {
        if (!id) return '';
        const r = this.rolesList.find(item => String(item.id) === String(id));
        return r ? r.display_name : '';
    },

    openCreate() {
        this.createRoleId = '';
        this.createRoleOpen = false;
        this.createModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openEdit(u) {
        this.editUser = Object.assign({}, u);
        this.editRoleOpen = false;
        this.editModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    get filteredUsers() {
        const q = this.searchQuery.trim().toLowerCase();
        const roleFilter = this.selectedRole;
        return this.allUsers.filter(u => {
            if (roleFilter && String(u.role_id) !== String(roleFilter)) {
                return false;
            }
            if (!q) return true;
            const name = (u.name || '').toLowerCase();
            const email = (u.email || '').toLowerCase();
            const pos = (u.position || '').toLowerCase();
            const role = (u.role_label || '').toLowerCase();
            const phone = (u.phone || '').toLowerCase();
            return name.includes(q) || email.includes(q) || pos.includes(q) || role.includes(q) || phone.includes(q);
        });
    },

    get totalPages() {
        if (this.perPage === 'all') return 1;
        const per = parseInt(this.perPage) || 10;
        return Math.max(1, Math.ceil(this.filteredUsers.length / per));
    },

    get paginatedUsers() {
        if (this.perPage === 'all') return this.filteredUsers;
        const per = parseInt(this.perPage) || 10;
        const start = (this.currentPage - 1) * per;
        return this.filteredUsers.slice(start, start + per);
    },

    get startIndex() {
        if (this.filteredUsers.length === 0) return 0;
        if (this.perPage === 'all') return 1;
        const per = parseInt(this.perPage) || 10;
        return (this.currentPage - 1) * per + 1;
    },

    get endIndex() {
        if (this.filteredUsers.length === 0) return 0;
        if (this.perPage === 'all') return this.filteredUsers.length;
        const per = parseInt(this.perPage) || 10;
        return Math.min(this.filteredUsers.length, this.currentPage * per);
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
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        }
    },

    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        }
    },

    getRoleBadgeClass(roleName) {
        switch (roleName) {
            case 'admin': return 'bg-purple-50 dark:bg-purple-500/15 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-500/30';
            case 'executive': return 'bg-blue-50 dark:bg-blue-500/15 text-blue-700 dark:text-blue-300 border-blue-200/80 dark:border-blue-500/30';
            default: return 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-500/30';
        }
    }
}">

    <!-- Breadcrumb & Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1.5 font-medium">
                <a href="<?= Router::url('/dashboard') ?>" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">หน้าหลัก</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-60"></i>
                <span class="text-slate-700 dark:text-slate-300 font-semibold">ผู้ใช้งานและบทบาท</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center shrink-0">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-heading text-slate-900 dark:text-white tracking-tight">
                        การจัดการผู้ใช้งานและสิทธิ์
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        บริหารจัดการบัญชีผู้ใช้งาน เจ้าหน้าที่ และบทบาทการเข้าถึงระบบ
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <button type="button" @click="openCreate()"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md shadow-emerald-500/20 hover:shadow-lg transition-all cursor-pointer">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>เพิ่มผู้ใช้งานใหม่</span>
            </button>
        </div>
    </div>

    <!-- 4 Summary KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Users -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#181a20] border border-slate-200/80 dark:border-white/10 shadow-sm relative overflow-hidden group hover:border-emerald-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 font-heading">ผู้ใช้งานทั้งหมด</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 dark:text-white"><?= number_format($totalUsersCount) ?></span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">บัญชี</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 truncate">
                บุคลากรและเจ้าหน้าที่ในระบบเทศบาล
            </p>
        </div>

        <!-- Card 2: Administrators -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#181a20] border border-slate-200/80 dark:border-white/10 shadow-sm relative overflow-hidden group hover:border-purple-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 font-heading">ผู้ดูแลระบบสูงสุด</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-500/20">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold font-heading text-purple-600 dark:text-purple-400"><?= number_format($adminCount) ?></span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">บัญชี</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 truncate">
                สิทธิ์จัดการระบบ, ฐานข้อมูล และ Audit Log
            </p>
        </div>

        <!-- Card 3: Executives -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#181a20] border border-slate-200/80 dark:border-white/10 shadow-sm relative overflow-hidden group hover:border-blue-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 font-heading">ผู้บริหารเทศบาล</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-500/20">
                    <i data-lucide="award" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold font-heading text-blue-600 dark:text-blue-400"><?= number_format($executiveCount) ?></span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">บัญชี</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 truncate">
                สิทธิ์ดูแดชบอร์ด, ภาพรวมงบประมาณ และรายงาน
            </p>
        </div>

        <!-- Card 4: Officers / Staff -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#181a20] border border-slate-200/80 dark:border-white/10 shadow-sm relative overflow-hidden group hover:border-teal-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 font-heading">เจ้าหน้าที่ / ผู้ดูแลโครงการ</span>
                <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center border border-teal-500/20">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold font-heading text-teal-600 dark:text-teal-400"><?= number_format($officerCount) ?></span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">บัญชี</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 truncate">
                ผู้บันทึกโครงการ, กิจกรรม และความคืบหน้า
            </p>
        </div>
    </div>

    <!-- Search & Control Toolbar -->
    <div class="bg-white dark:bg-[#181a20] rounded-2xl p-4 border border-slate-200/80 dark:border-white/10 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3.5">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
            <!-- Search Keyword -->
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input type="text" x-model="searchQuery" @input="currentPage = 1; $nextTick(() => { if (window.lucide) lucide.createIcons(); });" placeholder="ค้นหาชื่อ, อีเมล, ตำแหน่ง, เบอร์โทร..."
                       class="w-full pl-9 pr-8 py-2 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white transition-colors shadow-2xs">
                <button type="button" x-show="searchQuery" @click="searchQuery = ''; currentPage = 1; $nextTick(() => { if (window.lucide) lucide.createIcons(); });" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5 cursor-pointer">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>

            <!-- Role Filter Dropdown -->
            <div class="relative shrink-0" @click.outside="openRoleFilter = false">
                <button type="button" 
                        @click="openRoleFilter = !openRoleFilter" 
                        class="w-full sm:w-auto px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.04] text-slate-800 dark:text-slate-200 font-medium text-xs sm:text-sm flex items-center justify-between sm:justify-start gap-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer transition-all shadow-2xs">
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span class="text-slate-500 dark:text-slate-400">บทบาท:</span>
                        <span class="font-semibold text-slate-900 dark:text-white truncate" x-text="selectedRole ? getRoleNameById(selectedRole) : 'ทุกบทบาท'"></span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180 text-emerald-600 dark:text-emerald-400': openRoleFilter }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="openRoleFilter" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute left-0 sm:left-auto sm:right-0 mt-1.5 w-52 bg-white dark:bg-[#181c26] rounded-2xl shadow-xl border border-slate-200 dark:border-white/10 py-1 z-30" 
                     style="display: none;">
                    <div @click="selectedRole = ''; currentPage = 1; openRoleFilter = false; $nextTick(() => { if (window.lucide) lucide.createIcons(); });"
                         class="px-3 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 cursor-pointer flex items-center justify-between transition-colors"
                         :class="{ 'bg-emerald-50/80 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 font-bold': !selectedRole }">
                        <span>-- ทุกบทบาท --</span>
                        <svg x-show="!selectedRole" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <template x-for="r in rolesList" :key="r.id">
                        <div @click="selectedRole = r.id; currentPage = 1; openRoleFilter = false; $nextTick(() => { if (window.lucide) lucide.createIcons(); });"
                             class="px-3 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 cursor-pointer flex items-center justify-between transition-colors"
                             :class="{ 'bg-emerald-50/80 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 font-bold': String(selectedRole) === String(r.id) }">
                            <span x-text="r.display_name"></span>
                            <svg x-show="String(selectedRole) === String(r.id)" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        
        <!-- Per Page Selector & Counter -->
        <div class="flex items-center gap-3 text-xs sm:text-sm text-slate-500 dark:text-slate-400 justify-between md:justify-end shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100 dark:border-white/5">
            <div class="relative" @click.outside="openPerPage = false">
                <div class="flex items-center gap-1.5">
                    <span class="text-xs">แสดงหน้าละ:</span>
                    <button type="button" 
                            @click="openPerPage = !openPerPage"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.04] text-slate-800 dark:text-slate-200 font-semibold text-xs flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer transition-all shadow-2xs">
                        <span x-text="perPage === 'all' ? 'ทั้งหมด' : perPage + ' บัญชี'"></span>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180 text-emerald-600 dark:text-emerald-400': openPerPage }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
                <div x-show="openPerPage" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-1.5 w-32 bg-white dark:bg-[#181c26] rounded-xl shadow-xl border border-slate-200 dark:border-white/10 py-1 z-30" 
                     style="display: none;">
                    <template x-for="opt in [{val: 10, label: '10 บัญชี'}, {val: 20, label: '20 บัญชี'}, {val: 50, label: '50 บัญชี'}, {val: 'all', label: 'ทั้งหมด'}]" :key="opt.val">
                        <div @click="perPage = opt.val; currentPage = 1; openPerPage = false; $nextTick(() => { if (window.lucide) lucide.createIcons(); });"
                             class="px-3 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 cursor-pointer flex items-center justify-between transition-colors"
                             :class="{ 'bg-emerald-50/80 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 font-bold': String(perPage) === String(opt.val) }">
                            <span x-text="opt.label"></span>
                            <svg x-show="String(perPage) === String(opt.val)" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </template>
                </div>
            </div>
            <div class="font-medium text-slate-600 dark:text-slate-300">
                รวม <span class="font-bold text-slate-900 dark:text-white" x-text="filteredUsers.length"></span> บัญชี
            </div>
        </div>
    </div>

    <!-- MODE 1: Mobile User Cards (Shown on mobile screens < 768px, perfect for 390px viewport) -->
    <div class="block md:hidden space-y-3">
        <template x-if="paginatedUsers.length === 0">
            <div class="bg-white dark:bg-[#181a20] rounded-2xl border border-slate-200/80 dark:border-white/10 p-8 text-center text-slate-400 dark:text-slate-500">
                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                ไม่พบข้อมูลผู้ใช้งานตามเงื่อนไขที่ค้นหา
            </div>
        </template>

        <template x-for="(u, idx) in paginatedUsers" :key="u.id">
            <div class="bg-white dark:bg-[#181a20] rounded-2xl border border-slate-200/80 dark:border-white/10 p-4 shadow-sm hover:shadow-md transition-all space-y-3">
                <!-- Card Header: Avatar, Name, Email, Role -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0 font-heading"
                             x-text="(u.name || '').substring(0, 1)">
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-1.5 truncate">
                                <span class="truncate" x-text="u.name"></span>
                                <template x-if="parseInt(u.id) === currentUserId">
                                    <span class="text-[10px] px-1.5 py-0.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 rounded font-normal shrink-0">คุณ</span>
                                </template>
                            </div>
                            <div class="text-xs text-slate-400 font-mono truncate mt-0.5" x-text="u.email"></div>
                        </div>
                    </div>

                    <!-- Role Badge -->
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border shrink-0"
                          :class="getRoleBadgeClass(u.role_name)"
                          x-text="u.role_label || 'เจ้าหน้าที่'">
                    </span>
                </div>

                <!-- Info Details: Position, Phone -->
                <div class="grid grid-cols-2 gap-2 text-xs py-2.5 px-3 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.05]">
                    <div>
                        <span class="text-slate-400 text-[10px] font-semibold uppercase block">ตำแหน่ง / สังกัด</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200 truncate block mt-0.5" x-text="u.position || 'เจ้าหน้าที่'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] font-semibold uppercase block">เบอร์โทรศัพท์</span>
                        <span class="font-mono text-slate-700 dark:text-slate-300 block mt-0.5" x-text="u.phone || '-'"></span>
                    </div>
                </div>

                <!-- Bottom Status & Actions -->
                <div class="flex items-center justify-between pt-1">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>เปิดใช้งาน</span>
                    </span>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="openEdit(u)"
                                class="px-2.5 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/60 rounded-xl transition-colors flex items-center gap-1 cursor-pointer">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            <span>แก้ไข</span>
                        </button>
                        <template x-if="parseInt(u.id) !== currentUserId">
                            <form :action="'<?= Router::url('/users/') ?>' + u.id + '/delete'" method="POST" 
                                  @submit="if(!confirm(`ยืนยันการลบผู้ใช้ ${u.name} ออกจากระบบ?`)) $event.preventDefault();">
                                <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                <button type="submit" 
                                        class="px-2.5 py-1.5 text-xs font-medium text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 rounded-xl transition-colors flex items-center gap-1 cursor-pointer">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>ลบ</span>
                                </button>
                            </form>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- MODE 2: Desktop Users Table (Shown on screens >= 768px) -->
    <div class="hidden md:block bg-white dark:bg-[#181a20] rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-white/10 bg-slate-50/70 dark:bg-white/[0.02] text-slate-600 dark:text-slate-400 font-semibold uppercase tracking-wider text-xs">
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4">ผู้ใช้งาน (เจ้าหน้าที่)</th>
                        <th class="py-3.5 px-4">ตำแหน่ง / สังกัด</th>
                        <th class="py-3.5 px-4 text-center">สิทธิ์การใช้งาน</th>
                        <th class="py-3.5 px-4">เบอร์โทรศัพท์</th>
                        <th class="py-3.5 px-4 text-center">สถานะ</th>
                        <th class="py-3.5 px-4 text-center w-28">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    <template x-if="paginatedUsers.length === 0">
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                ไม่พบข้อมูลผู้ใช้งานตามเงื่อนไขที่ค้นหา
                            </td>
                        </tr>
                    </template>

                    <template x-for="(u, idx) in paginatedUsers" :key="u.id">
                        <tr class="hover:bg-slate-50/75 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3.5 px-4 text-center text-slate-400 font-mono text-xs" x-text="(perPage === 'all' ? idx + 1 : (currentPage - 1) * perPage + idx + 1)"></td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-600 text-white flex items-center justify-center font-bold text-xs shadow-sm font-heading"
                                         x-text="(u.name || '').substring(0, 1)">
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <span x-text="u.name"></span>
                                            <template x-if="parseInt(u.id) === currentUserId">
                                                <span class="text-[10px] px-1.5 py-0.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 rounded font-normal">คุณ</span>
                                            </template>
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono" x-text="u.email"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-800 dark:text-slate-200 font-medium text-xs sm:text-sm" x-text="u.position || 'เจ้าหน้าที่'"></div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border"
                                      :class="getRoleBadgeClass(u.role_name)"
                                      x-text="u.role_label || 'เจ้าหน้าที่'">
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-400 text-xs sm:text-sm" x-text="u.phone || '-'"></td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>เปิดใช้งาน</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEdit(u)"
                                            class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/40 rounded-lg transition cursor-pointer" title="แก้ไขข้อมูล">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <template x-if="parseInt(u.id) !== currentUserId">
                                        <form :action="'<?= Router::url('/users/') ?>' + u.id + '/delete'" method="POST" 
                                              @submit="if(!confirm(`ยืนยันการลบผู้ใช้ ${u.name} ออกจากระบบ?`)) $event.preventDefault();">
                                            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                            <button type="submit" 
                                                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition cursor-pointer" title="ลบผู้ใช้งาน">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Bar (Responsive for both Mobile & Desktop) -->
    <div class="bg-white dark:bg-[#181a20] rounded-2xl border border-slate-200/80 dark:border-white/10 p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
        <div>
            <template x-if="filteredUsers.length > 0">
                <span>
                    แสดง <strong class="text-slate-800 dark:text-slate-200" x-text="startIndex"></strong> ถึง <strong class="text-slate-800 dark:text-slate-200" x-text="endIndex"></strong> จากทั้งหมด <strong class="text-slate-800 dark:text-slate-200" x-text="filteredUsers.length"></strong> บัญชี
                </span>
            </template>
            <template x-if="filteredUsers.length === 0">
                <span>ไม่มีข้อมูลสำหรับแสดงผล</span>
            </template>
        </div>

        <!-- Page Navigation Buttons -->
        <template x-if="totalPages > 1 && perPage !== 'all'">
            <div class="flex items-center gap-1">
                <!-- First Page -->
                <button type="button" @click="setPage(1)" :disabled="currentPage === 1"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.04] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.08] disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าแรก">
                    <i data-lucide="chevrons-left" class="w-3.5 h-3.5"></i>
                </button>

                <!-- Prev Page -->
                <button type="button" @click="prevPage()" :disabled="currentPage === 1"
                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.04] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.08] disabled:opacity-30 disabled:cursor-not-allowed font-medium transition cursor-pointer flex items-center gap-1 text-xs">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">ก่อนหน้า</span>
                </button>

                <!-- Page Numbers -->
                <div class="flex items-center gap-1">
                    <template x-for="(p, i) in visiblePages" :key="i">
                        <div>
                            <template x-if="p === '...'">
                                <span class="px-2 py-1 text-slate-400 select-none text-xs">...</span>
                            </template>
                            <template x-if="p !== '...'">
                                <button type="button" 
                                        @click="setPage(p)" 
                                        :class="currentPage === p ? 'bg-emerald-600 text-white font-bold shadow-sm shadow-emerald-600/30 border-emerald-600' : 'bg-slate-50 dark:bg-white/[0.04] text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.08] border-slate-200 dark:border-white/10'"
                                        class="w-8 h-8 rounded-lg border text-xs flex items-center justify-center font-medium transition cursor-pointer"
                                        x-text="p">
                                </button>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Next Page -->
                <button type="button" @click="nextPage()" :disabled="currentPage === totalPages"
                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.04] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.08] disabled:opacity-30 disabled:cursor-not-allowed font-medium transition cursor-pointer flex items-center gap-1 text-xs">
                    <span class="hidden sm:inline">ถัดไป</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </button>

                <!-- Last Page -->
                <button type="button" @click="setPage(totalPages)" :disabled="currentPage === totalPages"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.04] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.08] disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าสุดท้าย">
                    <i data-lucide="chevrons-right" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </template>
    </div>

    <!-- Create User Modal -->
    <template x-teleport="body">
    <div x-show="createModal" 
         x-cloak 
         data-teleport-modal="true"
         style="display: none;"
         @click.self="createModal = false" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto modal-backdrop-smooth"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white dark:bg-[#181a20] rounded-3xl shadow-2xl border border-slate-200 dark:border-white/10 max-w-lg w-full overflow-visible modal-box-smooth transform-gpu"
             @click.outside="createModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-white/5 flex items-center justify-between rounded-t-3xl">
                <div class="flex items-center gap-2.5">
                    <div class="p-2.5 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-slate-900 dark:text-white">เพิ่มผู้ใช้งานใหม่</h3>
                        <p class="text-xs text-slate-400">สร้างบัญชีผู้ใช้งานสำหรับเจ้าหน้าที่เทศบาล</p>
                    </div>
                </div>
                <button type="button" @click.stop="createModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                    <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                </button>
            </div>

            <form action="<?= Router::url('/users') ?>" method="POST" 
                  @submit="if(!createRoleId) { alert('กรุณาเลือกบทบาท / สิทธิ์'); $event.preventDefault(); return false; }"
                  class="p-5 sm:p-6 space-y-4 rounded-b-3xl">
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            ชื่อ - นามสกุล <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="เช่น นายสมคิด สถิตย์คง"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            อีเมล (Email) <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" required placeholder="name@municipality.go.th"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white font-mono transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            รหัสผ่านเริ่มต้น <span class="text-slate-400 font-normal">(ค่าเริ่มต้น: password)</span>
                        </label>
                        <input type="text" name="password" value="password" placeholder="password"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white font-mono transition-colors">
                    </div>
                    <div class="relative" @click.outside="createRoleOpen = false">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            บทบาท / สิทธิ์ (Role) <span class="text-rose-500">*</span>
                        </label>
                        <input type="hidden" name="role_id" :value="createRoleId">
                        <button type="button" 
                                @click="createRoleOpen = !createRoleOpen"
                                class="w-full px-3.5 py-2 min-h-[38px] bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm flex items-center justify-between text-left focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all cursor-pointer shadow-2xs"
                                :class="{ 'ring-2 ring-emerald-500 border-emerald-500 bg-white dark:bg-[#181c26]': createRoleOpen }">
                            <div class="flex items-center gap-2 truncate">
                                <template x-if="createRoleId">
                                    <span class="inline-flex items-center gap-2 font-semibold text-slate-900 dark:text-white truncate">
                                        <span class="w-2 h-2 rounded-full shrink-0" 
                                              :class="createRoleId == 1 ? 'bg-purple-500' : 'bg-blue-500'"></span>
                                        <span x-text="getRoleNameById(createRoleId)"></span>
                                    </span>
                                </template>
                                <template x-if="!createRoleId">
                                    <span class="text-slate-400 font-normal">-- เลือกบทบาท / สิทธิ์ --</span>
                                </template>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0 ml-1" 
                                 :class="{ 'rotate-180 text-emerald-600 dark:text-emerald-400': createRoleOpen }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Custom Dropdown Menu -->
                        <div x-show="createRoleOpen" 
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                             class="absolute left-0 right-0 z-50 mt-1.5 bg-white dark:bg-[#181c26] rounded-2xl shadow-xl border border-slate-200 dark:border-white/10 p-1.5" 
                             style="display: none;">
                            <template x-for="r in rolesList" :key="r.id">
                                <div @click="createRoleId = r.id; createRoleOpen = false"
                                     class="p-2.5 rounded-xl cursor-pointer transition-all flex items-start justify-between gap-3 text-left group hover:bg-emerald-50 dark:hover:bg-emerald-500/10"
                                     :class="{ 'bg-emerald-50/80 dark:bg-emerald-500/20 border border-emerald-200/60 dark:border-emerald-500/30': String(createRoleId) === String(r.id) }">
                                    <div class="flex items-start gap-2.5 min-w-0">
                                        <div class="p-1.5 rounded-lg shrink-0 mt-0.5"
                                             :class="r.name === 'admin' ? 'bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-300' : 'bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300'">
                                            <template x-if="r.name === 'admin'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                </svg>
                                            </template>
                                            <template x-if="r.name !== 'admin'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                </svg>
                                            </template>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-300"
                                                 x-text="r.display_name"></div>
                                            <div class="text-[11px] text-slate-400 dark:text-slate-400 truncate"
                                                 x-text="r.description"></div>
                                        </div>
                                    </div>
                                    <svg x-show="String(createRoleId) === String(r.id)" 
                                         class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-1" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            ตำแหน่ง / สังกัด
                        </label>
                        <input type="text" name="position" placeholder="เช่น นักวิเคราะห์นโยบายและแผน"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            เบอร์โทรศัพท์
                        </label>
                        <input type="text" name="phone" placeholder="เช่น 081-2345678"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white font-mono transition-colors">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-white/5 flex items-center justify-end gap-2.5">
                    <button type="button" @click="createModal = false"
                            class="px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5 rounded-xl transition cursor-pointer">
                        ยกเลิก
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 rounded-xl shadow-md shadow-emerald-500/20 transition cursor-pointer">
                        บันทึกผู้ใช้งาน
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    <!-- Edit User Modal -->
    <template x-teleport="body">
    <div x-show="editModal" 
         x-cloak 
         data-teleport-modal="true"
         style="display: none;"
         @click.self="editModal = false" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto modal-backdrop-smooth"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white dark:bg-[#181a20] rounded-3xl shadow-2xl border border-slate-200 dark:border-white/10 max-w-lg w-full overflow-visible modal-box-smooth transform-gpu"
             @click.outside="editModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-white/5 flex items-center justify-between rounded-t-3xl">
                <div class="flex items-center gap-2.5">
                    <div class="p-2.5 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-slate-900 dark:text-white">แก้ไขข้อมูลผู้ใช้งาน</h3>
                        <p class="text-xs text-slate-400">อัปเดตรายละเอียดและสิทธิ์ของผู้ใช้งานในระบบ</p>
                    </div>
                </div>
                <button type="button" @click.stop="editModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                    <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                </button>
            </div>

            <form :action="'<?= Router::url('/users/') ?>' + editUser.id + '/update'" method="POST" 
                  @submit="if(!editUser.role_id) { alert('กรุณาเลือกบทบาท / สิทธิ์'); $event.preventDefault(); return false; }"
                  class="p-5 sm:p-6 space-y-4 rounded-b-3xl">
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            ชื่อ - นามสกุล <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" x-model="editUser.name" required
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            อีเมล (Email) <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" x-model="editUser.email" required
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white font-mono transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            เปลี่ยนรหัสผ่าน <span class="text-slate-400 font-normal">(เว้นว่างหากไม่เปลี่ยน)</span>
                        </label>
                        <input type="password" name="password" placeholder="••••••••"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white font-mono transition-colors">
                    </div>
                    <div class="relative" @click.outside="editRoleOpen = false">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            บทบาท / สิทธิ์ (Role) <span class="text-rose-500">*</span>
                        </label>
                        <input type="hidden" name="role_id" :value="editUser.role_id">
                        <button type="button" 
                                @click="editRoleOpen = !editRoleOpen"
                                class="w-full px-3.5 py-2 min-h-[38px] bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm flex items-center justify-between text-left focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all cursor-pointer shadow-2xs"
                                :class="{ 'ring-2 ring-emerald-500 border-emerald-500 bg-white dark:bg-[#181c26]': editRoleOpen }">
                            <div class="flex items-center gap-2 truncate">
                                <template x-if="editUser.role_id">
                                    <span class="inline-flex items-center gap-2 font-semibold text-slate-900 dark:text-white truncate">
                                        <span class="w-2 h-2 rounded-full shrink-0" 
                                              :class="editUser.role_id == 1 ? 'bg-purple-500' : 'bg-blue-500'"></span>
                                        <span x-text="getRoleNameById(editUser.role_id)"></span>
                                    </span>
                                </template>
                                <template x-if="!editUser.role_id">
                                    <span class="text-slate-400 font-normal">-- เลือกบทบาท / สิทธิ์ --</span>
                                </template>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0 ml-1" 
                                 :class="{ 'rotate-180 text-emerald-600 dark:text-emerald-400': editRoleOpen }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Custom Dropdown Menu -->
                        <div x-show="editRoleOpen" 
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                             class="absolute left-0 right-0 z-50 mt-1.5 bg-white dark:bg-[#181c26] rounded-2xl shadow-xl border border-slate-200 dark:border-white/10 p-1.5" 
                             style="display: none;">
                            <template x-for="r in rolesList" :key="r.id">
                                <div @click="editUser.role_id = r.id; editRoleOpen = false"
                                     class="p-2.5 rounded-xl cursor-pointer transition-all flex items-start justify-between gap-3 text-left group hover:bg-emerald-50 dark:hover:bg-emerald-500/10"
                                     :class="{ 'bg-emerald-50/80 dark:bg-emerald-500/20 border border-emerald-200/60 dark:border-emerald-500/30': String(editUser.role_id) === String(r.id) }">
                                    <div class="flex items-start gap-2.5 min-w-0">
                                        <div class="p-1.5 rounded-lg shrink-0 mt-0.5"
                                             :class="r.name === 'admin' ? 'bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-300' : 'bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300'">
                                            <template x-if="r.name === 'admin'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                </svg>
                                            </template>
                                            <template x-if="r.name !== 'admin'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                </svg>
                                            </template>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-300"
                                                 x-text="r.display_name"></div>
                                            <div class="text-[11px] text-slate-400 dark:text-slate-400 truncate"
                                                 x-text="r.description"></div>
                                        </div>
                                    </div>
                                    <svg x-show="String(editUser.role_id) === String(r.id)" 
                                         class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-1" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            ตำแหน่ง / สังกัด
                        </label>
                        <input type="text" name="position" x-model="editUser.position"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            เบอร์โทรศัพท์
                        </label>
                        <input type="text" name="phone" x-model="editUser.phone"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white font-mono transition-colors">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-white/5 flex items-center justify-end gap-2.5">
                    <button type="button" @click="editModal = false"
                            class="px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5 rounded-xl transition cursor-pointer">
                        ยกเลิก
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 rounded-xl shadow-md shadow-emerald-500/20 transition cursor-pointer">
                        บันทึกการแก้ไข
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
