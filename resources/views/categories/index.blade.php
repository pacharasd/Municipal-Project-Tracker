<?php
ob_start();
$title = "ประเภทโครงการ (Project Categories)";
use App\Core\Router;
use App\Core\Auth;
use App\Services\ProgressService;

$isAdmin = Auth::isAdmin();

// Color themes mapping for icons
$colorThemes = [
    'truck'          => ['bg' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20', 'gradient' => 'from-blue-500 to-indigo-600'],
    'heart-pulse'    => ['bg' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20', 'gradient' => 'from-rose-500 to-pink-600'],
    'graduation-cap' => ['bg' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20', 'gradient' => 'from-amber-500 to-orange-600'],
    'leaf'           => ['bg' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20', 'gradient' => 'from-emerald-500 to-teal-600'],
    'users'          => ['bg' => 'bg-violet-500/10 text-violet-600 dark:text-violet-400 border-violet-500/20', 'gradient' => 'from-violet-500 to-purple-600'],
    'building-2'     => ['bg' => 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20', 'gradient' => 'from-cyan-500 to-blue-600'],
    'shield'         => ['bg' => 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20', 'gradient' => 'from-red-500 to-rose-600'],
    'landmark'       => ['bg' => 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20', 'gradient' => 'from-slate-600 to-slate-800'],
];

$defaultTheme = ['bg' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20', 'gradient' => 'from-emerald-500 to-teal-600'];

// Preset icons for picker
$presetIcons = [
    ['icon' => 'truck', 'label' => 'โครงสร้าง/คมนาคม'],
    ['icon' => 'heart-pulse', 'label' => 'สาธารณสุข/สุขภาพ'],
    ['icon' => 'graduation-cap', 'label' => 'การศึกษา/วัฒนธรรม'],
    ['icon' => 'leaf', 'label' => 'สิ่งแวดล้อม/เกษตร'],
    ['icon' => 'users', 'label' => 'สังคม/ชุมชน'],
    ['icon' => 'building-2', 'label' => 'ผังเมือง/อาคาร'],
    ['icon' => 'shield', 'label' => 'ความปลอดภัย/ป้องกันภัย'],
    ['icon' => 'wallet', 'label' => 'เศรษฐกิจ/การเงิน'],
    ['icon' => 'activity', 'label' => 'กีฬา/นันทนาการ'],
    ['icon' => 'landmark', 'label' => 'การปกครอง/นิติการ'],
    ['icon' => 'lightbulb', 'label' => 'นวัตกรรม/พัฒนา'],
    ['icon' => 'folder', 'label' => 'ทั่วไป/อื่นๆ'],
];
?>

<div class="space-y-6 max-w-7xl mx-auto pb-12"
     x-data="{
        createModal: false,
        editModal: false,
        deleteModal: false,
        activeTab: 'grid', // 'grid' | 'table'
        searchQuery: '',
        editData: { id: '', name: '', description: '', icon: 'folder' },
        deleteData: { id: '', name: '', project_count: 0 },
        createIcon: 'folder',

        openEdit(cat) {
            this.editData = {
                id: cat.id,
                name: cat.name || '',
                description: cat.description || '',
                icon: cat.icon || 'folder'
            };
            this.editModal = true;
            $nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        openDelete(cat) {
            this.deleteData = {
                id: cat.id,
                name: cat.name,
                project_count: parseInt(cat.project_count || 0)
            };
            this.deleteModal = true;
            $nextTick(() => { if (window.lucide) lucide.createIcons(); });
        }
     }">

    <!-- Breadcrumb & Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1.5 font-medium">
                <a href="<?= Router::url('/dashboard') ?>" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">หน้าหลัก</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-60"></i>
                <span class="text-slate-700 dark:text-slate-300 font-semibold">ประเภทโครงการ</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-bold font-heading text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <i data-lucide="tags" class="w-6 h-6"></i>
                </span>
                ประเภทโครงการ (Project Categories)
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                บริหารจัดการหมวดหมู่โครงการ สถิติการจัดสรรงบประมาณ และติดตามความคืบหน้าเฉลี่ยตามประเภทงาน
            </p>
        </div>

        <!-- Action Button -->
        <div class="flex items-center gap-2.5 shrink-0">
            <!-- View Mode Switcher -->
            <div class="flex items-center p-1 bg-slate-100 dark:bg-white/[0.06] rounded-xl border border-slate-200 dark:border-white/10 text-xs font-semibold">
                <button type="button" @click="activeTab = 'grid'" 
                        :class="activeTab === 'grid' ? 'bg-white dark:bg-[#181c26] text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">การ์ดสรุป</span>
                </button>
                <button type="button" @click="activeTab = 'table'" 
                        :class="activeTab === 'table' ? 'bg-white dark:bg-[#181c26] text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="table-2" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">ตารางเปรียบเทียบ</span>
                </button>
            </div>

            <?php if ($isAdmin): ?>
                <button type="button" @click="createModal = true; createIcon = 'folder'; $nextTick(() => { if (window.lucide) lucide.createIcons(); });" 
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs sm:text-sm font-bold shadow-md shadow-emerald-500/20 hover:shadow-lg transition-all cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>เพิ่มประเภทโครงการ</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 4 Summary Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. จำนวนหมวดหมู่ทั้งหมด -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#181a20] border border-slate-200/80 dark:border-white/10 shadow-sm relative overflow-hidden group hover:border-emerald-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 font-heading">หมวดหมู่ทั้งหมด</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                    <i data-lucide="folder-tree" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 dark:text-white"><?= number_format($metrics['total_categories']) ?></span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">ประเภท</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 truncate">
                ครอบคลุมโครงการหลักทั้งหมด <span class="font-bold text-emerald-600 dark:text-emerald-400"><?= number_format($metrics['total_projects']) ?></span> โครงการ
            </p>
        </div>

        <!-- 2. หมวดหมู่ที่มีโครงการมากที่สุด -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#181a20] border border-slate-200/80 dark:border-white/10 shadow-sm relative overflow-hidden group hover:border-amber-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 font-heading">หมวดหมู่โครงการสูงสุด</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-500/20">
                    <i data-lucide="award" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-lg sm:text-xl font-bold font-heading text-slate-900 dark:text-white truncate max-w-[170px]" title="<?= htmlspecialchars($metrics['top_category']['name'] ?? '-') ?>">
                    <?= htmlspecialchars($metrics['top_category']['name'] ?? '-') ?>
                </span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                มี <span class="font-bold text-amber-600 dark:text-amber-400"><?= $metrics['top_category']['project_count'] ?? 0 ?></span> โครงการ (งบ <?= number_format((float)($metrics['top_category']['total_budget'] ?? 0)) ?> ฿)
            </p>
        </div>

        <!-- 3. งบประมาณรวมทุกหมวดหมู่ -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#181a20] border border-slate-200/80 dark:border-white/10 shadow-sm relative overflow-hidden group hover:border-blue-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 font-heading">งบประมาณรวมทุกหมวด</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-500/20">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-1">
                <span class="text-xl sm:text-2xl font-extrabold font-heading text-slate-900 dark:text-white"><?= number_format($metrics['grand_total_budget']) ?></span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">บาท</span>
            </div>
            <?php 
                $disbursedPct = $metrics['grand_total_budget'] > 0 ? ($metrics['grand_total_disbursed'] / $metrics['grand_total_budget']) * 100 : 0;
            ?>
            <div class="mt-2 flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                <div class="flex-1 bg-slate-100 dark:bg-white/[0.08] h-1.5 rounded-full overflow-hidden">
                    <div class="bg-blue-500 h-full rounded-full" style="width: <?= min(100, $disbursedPct) ?>%"></div>
                </div>
                <span class="font-bold text-blue-600 dark:text-blue-400"><?= number_format($disbursedPct, 1) ?>%</span>
            </div>
        </div>

        <!-- 4. ความคืบหน้าเฉลี่ยรวม -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#181a20] border border-slate-200/80 dark:border-white/10 shadow-sm relative overflow-hidden group hover:border-purple-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 font-heading">ความคืบหน้าเฉลี่ยรวม</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-500/20">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold font-heading text-purple-600 dark:text-purple-400">
                    <?= number_format($metrics['overall_progress'], 1) ?>%
                </span>
                <?php $ovTier = ProgressService::getProgressTier((float)$metrics['overall_progress']); ?>
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold border <?= $ovTier['textClass'] ?> bg-purple-500/10 border-purple-500/20">
                    <?= $ovTier['label'] ?>
                </span>
            </div>
            <div class="mt-2 flex-1 bg-slate-100 dark:bg-white/[0.08] h-1.5 rounded-full overflow-hidden">
                <div class="bg-gradient-to-r <?= $ovTier['gradient'] ?> h-full rounded-full" style="width: <?= min(100, (float)$metrics['overall_progress']) ?>%"></div>
            </div>
        </div>
    </div>

    <!-- TAB 1: Grid Cards View -->
    <div x-show="activeTab === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($categories as $cat): ?>
            <?php
            $catIcon = $cat['icon'] ?: 'folder';
            $theme = $colorThemes[$catIcon] ?? $defaultTheme;
            $pCount = (int)$cat['project_count'];
            $subCount = (int)$cat['sub_project_count'];
            $bTotal = (float)$cat['total_budget'];
            $dTotal = (float)$cat['total_disbursed'];
            $avgProg = (float)$cat['avg_progress'];
            $disbPct = $bTotal > 0 ? ($dTotal / $bTotal) * 100 : 0;
            $tier = ProgressService::getProgressTier($avgProg);
            ?>
            <div class="bg-white dark:bg-[#181a20] rounded-3xl border border-slate-200/80 dark:border-white/10 p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                <div>
                    <!-- Card Top: Icon & Action Menu -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 border <?= $theme['bg'] ?> shadow-sm">
                                <i data-lucide="<?= htmlspecialchars($catIcon) ?>" class="w-6 h-6"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-base text-slate-900 dark:text-white truncate font-heading group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors" title="<?= htmlspecialchars($cat['name']) ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </h3>
                                <span class="text-[11px] font-medium text-slate-400">รหัสหมวดหมู่ #<?= $cat['id'] ?></span>
                            </div>
                        </div>

                        <!-- Dropdown Actions for Admin -->
                        <?php if ($isAdmin): ?>
                            <div class="flex items-center gap-1">
                                <button type="button" 
                                        @click="openEdit(<?= htmlspecialchars(json_encode($cat)) ?>)"
                                        class="p-2 rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition cursor-pointer" title="แก้ไข">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                <button type="button" 
                                        @click="openDelete(<?= htmlspecialchars(json_encode($cat)) ?>)"
                                        class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition cursor-pointer" title="ลบ">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Category Description -->
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 line-clamp-2 leading-relaxed min-h-[36px]">
                        <?= htmlspecialchars($cat['description'] ?: 'ไม่มีรายละเอียดคำอธิบายเพิ่มเติมสำหรับหมวดหมู่นี้') ?>
                    </p>

                    <!-- Stats Pill Grid -->
                    <div class="grid grid-cols-2 gap-2.5 mt-4 p-3 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.05]">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400">โครงการหลัก</span>
                            <div class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5 mt-0.5">
                                <i data-lucide="folder-kanban" class="w-3.5 h-3.5 text-emerald-500"></i>
                                <span><?= number_format($pCount) ?> โครงการ</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400">โครงการย่อย</span>
                            <div class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5 mt-0.5">
                                <i data-lucide="layers" class="w-3.5 h-3.5 text-blue-500"></i>
                                <span><?= number_format($subCount) ?> รายการ</span>
                            </div>
                        </div>
                    </div>

                    <!-- Budget & Disbursement Bar -->
                    <div class="mt-4 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">งบประมาณที่ได้รับ</span>
                            <span class="font-bold font-mono text-slate-900 dark:text-white"><?= number_format($bTotal) ?> ฿</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span>เบิกจ่ายแล้ว <?= number_format($dTotal) ?> ฿</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400"><?= number_format($disbPct, 1) ?>%</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-white/[0.06] h-2 rounded-full overflow-hidden p-0.5">
                            <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-full rounded-full" style="width: <?= min(100, $disbPct) ?>%"></div>
                        </div>
                    </div>

                    <!-- Progress Average Status -->
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">ความคืบหน้าเฉลี่ย</span>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-bold <?= $tier['textClass'] ?>"><?= number_format($avgProg, 1) ?>%</span>
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold border whitespace-nowrap <?= $tier['badgeClass'] ?>">
                                <?= $tier['label'] ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Footer Card Action: Filter Projects -->
                <div class="mt-5 pt-3 border-t border-slate-100 dark:border-white/[0.06]">
                    <a href="<?= Router::url('/projects?category_id=' . $cat['id']) ?>" 
                       class="w-full py-2 px-3 rounded-xl bg-slate-100 dark:bg-white/[0.05] hover:bg-emerald-50 dark:hover:bg-emerald-500/15 text-slate-700 dark:text-slate-200 hover:text-emerald-700 dark:hover:text-emerald-300 text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>ดูโครงการในหมวดหมู่นี้ (<?= number_format($pCount) ?>)</span>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- TAB 2: Table Comparison View -->
    <div x-show="activeTab === 'table'" class="bg-white dark:bg-[#181a20] rounded-3xl border border-slate-200/80 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-bold font-heading text-slate-900 dark:text-white">ตารางเปรียบเทียบสถิติทุกประเภทโครงการ</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">เปรียบเทียบความคืบหน้า งบประมาณ และยอดเบิกจ่ายตามหมวดหมู่</p>
            </div>
            <div class="w-full sm:w-64 relative">
                <input type="text" x-model="searchQuery" placeholder="ค้นหาชื่อประเภท..." 
                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#12141a] text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-white/[0.02] border-b border-slate-100 dark:border-white/[0.06] text-slate-500 dark:text-slate-400 uppercase font-heading text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 font-bold text-center w-12">#</th>
                        <th class="py-3.5 px-4 font-bold">ประเภทโครงการ</th>
                        <th class="py-3.5 px-3 font-bold text-center">โครงการหลัก</th>
                        <th class="py-3.5 px-3 font-bold text-center">โครงการย่อย</th>
                        <th class="py-3.5 px-3 font-bold text-right">งบประมาณรวม</th>
                        <th class="py-3.5 px-3 font-bold text-right">ยอดเบิกจ่าย</th>
                        <th class="py-3.5 px-4 font-bold text-center">ความคืบหน้าเฉลี่ย</th>
                        <th class="py-3.5 px-4 font-bold text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04] text-slate-700 dark:text-slate-200">
                    <?php $idx = 1; foreach ($categories as $cat): ?>
                        <?php
                        $catIcon = $cat['icon'] ?: 'folder';
                        $theme = $colorThemes[$catIcon] ?? $defaultTheme;
                        $pCount = (int)$cat['project_count'];
                        $subCount = (int)$cat['sub_project_count'];
                        $bTotal = (float)$cat['total_budget'];
                        $dTotal = (float)$cat['total_disbursed'];
                        $avgProg = (float)$cat['avg_progress'];
                        $disbPct = $bTotal > 0 ? ($dTotal / $bTotal) * 100 : 0;
                        $tier = ProgressService::getProgressTier($avgProg);
                        ?>
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-white/[0.02] transition"
                            x-show="!searchQuery || '<?= addslashes(mb_strtolower($cat['name'], 'UTF-8')) ?>'.includes(searchQuery.toLowerCase())">
                            <td class="py-4 px-4 text-center text-slate-400 font-mono font-medium">
                                <?= $idx++ ?>
                            </td>
                            <td class="py-4 px-4">
                                <a href="<?= Router::url('/projects?category_id=' . $cat['id']) ?>" 
                                   class="font-bold text-sm text-slate-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors block">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </a>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1 max-w-sm mt-0.5">
                                    <?= htmlspecialchars($cat['description'] ?: 'ไม่มีรายละเอียดคำอธิบายเพิ่มเติม') ?>
                                </p>
                            </td>
                            <td class="py-4 px-3 text-center">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-white/[0.05] font-bold text-slate-800 dark:text-white font-mono">
                                    <?= number_format($pCount) ?>
                                </span>
                            </td>
                            <td class="py-4 px-3 text-center">
                                <span class="px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold font-mono">
                                    <?= number_format($subCount) ?>
                                </span>
                            </td>
                            <td class="py-4 px-3 text-right font-mono font-semibold text-slate-900 dark:text-white">
                                <?= number_format($bTotal) ?> ฿
                            </td>
                            <td class="py-4 px-3 text-right">
                                <div class="font-mono font-semibold text-slate-900 dark:text-white"><?= number_format($dTotal) ?> ฿</div>
                                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold"><?= number_format($disbPct, 1) ?>%</div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <span class="font-mono font-bold text-xs <?= $tier['textClass'] ?>"><?= number_format($avgProg, 1) ?>%</span>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border <?= $tier['badgeClass'] ?>">
                                        <?= $tier['label'] ?>
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="<?= Router::url('/projects?category_id=' . $cat['id']) ?>" 
                                       class="p-1.5 rounded-lg text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/15 transition cursor-pointer" title="ดูโครงการ">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <?php if ($isAdmin): ?>
                                        <button type="button" 
                                                @click="openEdit(<?= htmlspecialchars(json_encode($cat)) ?>)"
                                                class="p-1.5 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/15 transition cursor-pointer" title="แก้ไข">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" 
                                                @click="openDelete(<?= htmlspecialchars(json_encode($cat)) ?>)"
                                                class="p-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/15 transition cursor-pointer" title="ลบ">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: Create New Category -->
    <?php if ($isAdmin): ?>
    <div x-show="createModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white dark:bg-[#181a20] rounded-3xl max-w-lg w-full border border-slate-200 dark:border-white/10 shadow-2xl overflow-hidden"
             @click.outside="createModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <!-- Modal Header -->
            <div class="p-6 pb-4 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base font-heading text-slate-900 dark:text-white">เพิ่มประเภทโครงการใหม่</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">กำหนดหมวดหมู่หลักสำหรับจัดกลุ่มโครงการ</p>
                    </div>
                </div>
                <button type="button" @click="createModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-xl transition cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="<?= Router::url('/categories') ?>" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        ชื่อประเภทโครงการ <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" required placeholder="เช่น งานป้องกันและบรรเทาสาธารณภัย"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#12141a] text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        รายละเอียดคำอธิบาย
                    </label>
                    <textarea name="description" rows="2" placeholder="ระบุขอบเขตหรือลักษณะโครงการที่อยู่ในหมวดหมู่นี้..."
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#12141a] text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <!-- Icon Picker -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                        เลือกไอคอนประจำหมวดหมู่
                    </label>
                    <input type="hidden" name="icon" :value="createIcon">

                    <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 max-h-40 overflow-y-auto p-2 bg-slate-50 dark:bg-[#12141a] rounded-2xl border border-slate-200 dark:border-white/10">
                        <?php foreach ($presetIcons as $p): ?>
                            <button type="button" 
                                    @click="createIcon = '<?= $p['icon'] ?>'; $nextTick(() => { if (window.lucide) lucide.createIcons(); });"
                                    :class="createIcon === '<?= $p['icon'] ?>' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20 ring-2 ring-emerald-500' : 'bg-white dark:bg-[#181a20] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] border border-slate-200/80 dark:border-white/10'"
                                    class="p-2 rounded-xl flex flex-col items-center justify-center gap-1 transition cursor-pointer"
                                    title="<?= htmlspecialchars($p['label']) ?>">
                                <i data-lucide="<?= $p['icon'] ?>" class="w-4 h-4"></i>
                                <span class="text-[9px] truncate max-w-full text-center"><?= explode('/', $p['label'])[0] ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Form Buttons -->
                <div class="pt-4 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-end gap-2.5">
                    <button type="button" @click="createModal = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-white/10 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] transition cursor-pointer">
                        ยกเลิก
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold shadow-md shadow-emerald-500/20 transition cursor-pointer flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>บันทึกข้อมูล</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- MODAL 2: Edit Category -->
    <?php if ($isAdmin): ?>
    <div x-show="editModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white dark:bg-[#181a20] rounded-3xl max-w-lg w-full border border-slate-200 dark:border-white/10 shadow-2xl overflow-hidden"
             @click.outside="editModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <!-- Modal Header -->
            <div class="p-6 pb-4 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-500/20">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base font-heading text-slate-900 dark:text-white">แก้ไขประเภทโครงการ</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-text="'รหัสหมวดหมู่ #' + editData.id"></p>
                    </div>
                </div>
                <button type="button" @click="editModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-xl transition cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form :action="'<?= Router::url('/categories/') ?>' + editData.id + '/update'" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        ชื่อประเภทโครงการ <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" x-model="editData.name" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#12141a] text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        รายละเอียดคำอธิบาย
                    </label>
                    <textarea name="description" x-model="editData.description" rows="2"
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#12141a] text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <!-- Icon Picker -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                        เลือกไอคอนประจำหมวดหมู่
                    </label>
                    <input type="hidden" name="icon" :value="editData.icon">

                    <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 max-h-40 overflow-y-auto p-2 bg-slate-50 dark:bg-[#12141a] rounded-2xl border border-slate-200 dark:border-white/10">
                        <?php foreach ($presetIcons as $p): ?>
                            <button type="button" 
                                    @click="editData.icon = '<?= $p['icon'] ?>'; $nextTick(() => { if (window.lucide) lucide.createIcons(); });"
                                    :class="editData.icon === '<?= $p['icon'] ?>' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 ring-2 ring-blue-500' : 'bg-white dark:bg-[#181a20] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] border border-slate-200/80 dark:border-white/10'"
                                    class="p-2 rounded-xl flex flex-col items-center justify-center gap-1 transition cursor-pointer"
                                    title="<?= htmlspecialchars($p['label']) ?>">
                                <i data-lucide="<?= $p['icon'] ?>" class="w-4 h-4"></i>
                                <span class="text-[9px] truncate max-w-full text-center"><?= explode('/', $p['label'])[0] ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Form Buttons -->
                <div class="pt-4 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-end gap-2.5">
                    <button type="button" @click="editModal = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-white/10 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] transition cursor-pointer">
                        ยกเลิก
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition cursor-pointer flex items-center gap-1.5">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>บันทึกการแก้ไข</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- MODAL 3: Delete Confirmation Modal (with Safety Protection) -->
    <?php if ($isAdmin): ?>
    <div x-show="deleteModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white dark:bg-[#181a20] rounded-3xl max-w-md w-full border border-slate-200 dark:border-white/10 shadow-2xl overflow-hidden"
             @click.outside="deleteModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="p-6 text-center">
                <!-- Icon based on linked project count -->
                <div class="w-14 h-14 rounded-3xl mx-auto flex items-center justify-center mb-4"
                     :class="deleteData.project_count > 0 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'">
                    <i :data-lucide="deleteData.project_count > 0 ? 'alert-triangle' : 'trash-2'" class="w-7 h-7"></i>
                </div>

                <h3 class="font-bold text-lg font-heading text-slate-900 dark:text-white" x-text="'ยืนยันการลบประเภทโครงการ'"></h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    ประเภท: <strong class="text-slate-800 dark:text-slate-200 font-semibold" x-text="deleteData.name"></strong>
                </p>

                <!-- Warning if projects are linked -->
                <template x-if="deleteData.project_count > 0">
                    <div class="mt-4 p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-left">
                        <div class="flex items-start gap-2.5">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                            <div class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                                <strong>ไม่สามารถลบได้ในขณะนี้:</strong> เนื่องจากมีโครงการหลักผูกอยู่กับประเภทนี้จำนวน <span class="font-bold underline" x-text="deleteData.project_count"></span> โครงการ
                                <div class="mt-1 text-[11px] opacity-90">
                                    กรุณาย้ายหรือเปลี่ยนประเภทโครงการเหล่านั้นก่อน จึงจะสามารถลบหมวดหมู่นี้ได้
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="deleteData.project_count === 0">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-3">
                        คุณแน่ใจหรือไม่ว่าต้องการลบประเภทโครงการนี้? ข้อมูลนี้จะถูกนำออกจากระบบถาวร
                    </p>
                </template>
            </div>

            <!-- Modal Footer Buttons -->
            <div class="p-4 bg-slate-50 dark:bg-white/[0.02] border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-end gap-2.5">
                <button type="button" @click="deleteModal = false"
                        class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-white/10 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] transition cursor-pointer">
                    ปิดหน้าต่าง
                </button>

                <!-- Delete Form only enabled if project_count === 0 -->
                <template x-if="deleteData.project_count === 0">
                    <form :action="'<?= Router::url('/categories/') ?>' + deleteData.id + '/delete'" method="POST">
                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-500/20 transition cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>ยืนยันการลบ</span>
                        </button>
                    </form>
                </template>

                <template x-if="deleteData.project_count > 0">
                    <a :href="'<?= Router::url('/projects?category_id=') ?>' + deleteData.id"
                       class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-md shadow-amber-500/20 transition cursor-pointer flex items-center gap-1.5">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        <span>ไปจัดการโครงการ</span>
                    </a>
                </template>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>
