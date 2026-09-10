<?php
ob_start();
$title = "ประเภทโครงการ (Project Categories)";
use App\Core\Router;
use App\Core\Auth;
use App\Services\ProgressService;

$isAdmin = Auth::isAdmin();

// Prepare JSON summary for Alpine search & pagination
$categoriesSummary = [];
foreach ($categories as $cat) {
    $categoriesSummary[] = [
        'id' => (int)$cat['id'],
        'name' => (string)$cat['name'],
        'description' => (string)($cat['description'] ?? ''),
        'search_text' => mb_strtolower(($cat['name'] ?? '') . ' ' . ($cat['description'] ?? ''), 'UTF-8'),
    ];
}
$categoriesJson = json_encode($categoriesSummary, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
?>

<div class="space-y-6 max-w-7xl mx-auto pb-12"
     x-data="categoriesPage()">

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

        <!-- Action Button & Toolbar -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0">
            <!-- Search Input (shared across Grid and Table) -->
            <div class="relative min-w-[220px]">
                <input type="text" x-model="searchQuery" @input="onSearchChange" placeholder="ค้นหาประเภทโครงการ..." 
                       class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#181a20] text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-sm">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <button type="button" x-show="searchQuery" @click="searchQuery = ''; onSearchChange();" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5 cursor-pointer">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>

            <!-- View Mode Switcher -->
            <div class="flex items-center p-1 bg-slate-100 dark:bg-white/[0.06] rounded-xl border border-slate-200 dark:border-white/10 text-xs font-semibold">
                <button type="button" @click="activeTab = 'grid'; $nextTick(() => { if (window.lucide) lucide.createIcons(); });" 
                        :class="activeTab === 'grid' ? 'bg-white dark:bg-[#181c26] text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">การ์ดสรุป</span>
                </button>
                <button type="button" @click="activeTab = 'table'; $nextTick(() => { if (window.lucide) lucide.createIcons(); });" 
                        :class="activeTab === 'table' ? 'bg-white dark:bg-[#181c26] text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="table-2" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">ตารางเปรียบเทียบ</span>
                </button>
            </div>

            <?php if ($isAdmin): ?>
                <button type="button" @click="createModal = true; $nextTick(() => { if (window.lucide) lucide.createIcons(); });" 
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs sm:text-sm font-bold shadow-md shadow-emerald-500/20 hover:shadow-lg transition-all cursor-pointer">
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
    <div x-show="activeTab === 'grid'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($categories as $cat): ?>
                <?php
                $pCount = (int)$cat['project_count'];
                $subCount = (int)$cat['sub_project_count'];
                $bTotal = (float)$cat['total_budget'];
                $dTotal = (float)$cat['total_disbursed'];
                $avgProg = (float)$cat['avg_progress'];
                $disbPct = $bTotal > 0 ? ($dTotal / $bTotal) * 100 : 0;
                $tier = ProgressService::getProgressTier($avgProg);
                ?>
                <div x-show="isGridVisible(<?= $cat['id'] ?>)"
                     class="bg-white dark:bg-[#181a20] rounded-3xl border border-slate-200/80 dark:border-white/10 p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                    <div>
                        <!-- Card Top: Category ID, Title & Action Menu -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold font-mono bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        #<?= $cat['id'] ?>
                                    </span>
                                    <h3 class="font-bold text-base text-slate-900 dark:text-white truncate font-heading group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors" title="<?= htmlspecialchars($cat['name']) ?>">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </h3>
                                </div>
                            </div>

                            <!-- Dropdown Actions for Admin -->
                            <?php if ($isAdmin): ?>
                                <div class="flex items-center gap-1 shrink-0">
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
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2.5 line-clamp-2 leading-relaxed min-h-[36px]">
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
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-white/[0.06]">
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="text-slate-500 dark:text-slate-400 font-medium">ความคืบหน้าเฉลี่ย</span>
                                <span class="font-mono font-bold <?= $tier['textClass'] ?>"><?= number_format($avgProg, 1) ?>%</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-white/[0.06] h-2 rounded-full overflow-hidden p-0.5">
                                <div class="bg-gradient-to-r <?= $tier['gradient'] ?> h-full rounded-full transition-all duration-500" 
                                     style="width: <?= $avgProg > 0 ? min(100, max(5, $avgProg)) : 0 ?>%"></div>
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

            <!-- Empty State when no categories exist at all -->
            <div x-show="allCategories.length === 0" class="col-span-full p-12 text-center bg-white dark:bg-[#181a20] rounded-3xl border border-dashed border-slate-200 dark:border-white/10 shadow-sm">
                <div class="w-16 h-16 rounded-3xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-4 border border-emerald-500/20 shadow-sm">
                    <i data-lucide="folder-plus" class="w-8 h-8"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">ยังไม่มีประเภทโครงการในระบบ</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">
                    ท่านสามารถเริ่มต้นสร้างประเภทโครงการใหม่เพื่อจัดกลุ่มโครงการหลักและติดตามงบประมาณ หรือเลือกนำเข้าหมวดหมู่มาตรฐานของเทศบาล
                </p>
                <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                    <?php if ($isAdmin): ?>
                        <button type="button" @click="createModal = true" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-500/20 transition-all flex items-center gap-2 cursor-pointer">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>เพิ่มประเภทโครงการใหม่</span>
                        </button>
                        <form action="<?= Router::url('/categories/seed-defaults') ?>" method="POST" onsubmit="return confirm('ต้องการนำเข้า 5 หมวดหมู่มาตรฐานของเทศบาลหรือไม่?');">
                            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/15 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all flex items-center gap-2 cursor-pointer">
                                <i data-lucide="download-cloud" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                <span>นำเข้าหมวดหมู่มาตรฐาน 5 ด้าน</span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Empty State when search returns 0 results -->
            <div x-show="allCategories.length > 0 && filteredCategories.length === 0" class="col-span-full p-12 text-center bg-white dark:bg-[#181a20] rounded-3xl border border-dashed border-slate-200 dark:border-white/10">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-white/[0.05] text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="search-x" class="w-6 h-6"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">ไม่พบประเภทโครงการที่ค้นหา</h4>
                <p class="text-xs text-slate-400 mt-1">ไม่มีหมวดหมู่ที่ตรงกับ "<span class="font-semibold text-slate-600 dark:text-slate-300" x-text="searchQuery"></span>"</p>
                <button type="button" @click="searchQuery = ''; onSearchChange();" class="mt-3.5 px-3.5 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition cursor-pointer">
                    ล้างคำค้นหา
                </button>
            </div>
        </div>

        <!-- Grid View Pagination Bar -->
        <div x-show="filteredCategories.length > 0" class="p-4 rounded-2xl bg-white dark:bg-[#181a20] border border-slate-200/80 dark:border-white/10 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
            <div class="flex items-center gap-3">
                <span class="text-slate-500 dark:text-slate-400">
                    แสดง <span class="font-bold text-slate-900 dark:text-white font-mono" x-text="gridStartIndex"></span> ถึง 
                    <span class="font-bold text-slate-900 dark:text-white font-mono" x-text="gridEndIndex"></span> จาก 
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono" x-text="filteredCategories.length"></span> หมวดหมู่
                </span>
                <div class="relative shrink-0 ml-2 border-l border-slate-200 dark:border-white/10 pl-3" x-data="{ openGridPerPage: false }" @click.outside="openGridPerPage = false">
                    <button type="button" 
                            @click="openGridPerPage = !openGridPerPage" 
                            class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-white/[0.04] hover:bg-slate-100 dark:hover:bg-white/[0.08] px-2.5 py-1 rounded-xl border border-slate-200 dark:border-white/10 hover:border-emerald-500/40 transition-all cursor-pointer shadow-2xs">
                        <span class="text-slate-400 dark:text-slate-500 font-normal text-[11px]">แสดงต่อหน้า:</span>
                        <span class="font-bold text-slate-900 dark:text-white font-mono" x-text="gridPerPage === 'all' ? 'ทั้งหมด' : gridPerPage"></span>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-150 shrink-0" :class="{ 'rotate-180': openGridPerPage }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Themed Dropdown Flyout (Pops Up) -->
                    <div x-show="openGridPerPage" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-3 bottom-full mb-1.5 w-32 bg-white dark:bg-[#181a20] rounded-2xl shadow-xl dark:shadow-2xl border border-slate-200 dark:border-white/10 p-1.5 z-50 text-xs text-left">
                        <div class="px-2.5 py-1 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider font-heading border-b border-slate-100 dark:border-white/[0.06] mb-1">
                            จำนวนต่อหน้า
                        </div>
                        <div class="space-y-0.5">
                            <template x-for="opt in [6, 9, 12, 'all']" :key="opt">
                                <button type="button" 
                                        @click="setGridPerPage(opt); openGridPerPage = false" 
                                        class="w-full text-left px-2.5 py-1.5 rounded-xl text-xs flex items-center justify-between transition cursor-pointer"
                                        :class="gridPerPage == opt 
                                            ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/20' 
                                            : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5'">
                                    <span x-text="opt === 'all' ? 'ทั้งหมด' : opt + ' รายการ'"></span>
                                    <svg x-show="gridPerPage == opt" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination buttons -->
            <template x-if="gridTotalPages > 1 && gridPerPage !== 'all'">
                <div class="flex items-center gap-1">
                    <button type="button" @click="setGridPage(1)" :disabled="gridPage === 1"
                            class="p-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าแรก">
                        <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                    </button>
                    <button type="button" @click="prevGridPage()" :disabled="gridPage === 1"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer flex items-center gap-1">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">ก่อนหน้า</span>
                    </button>
                    <div class="flex items-center gap-1 px-1">
                        <template x-for="(p, idx) in gridVisiblePages" :key="idx">
                            <button type="button" 
                                    @click="setGridPage(p)"
                                    :disabled="p === '...'"
                                    :class="p === gridPage ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-500/20 border border-emerald-600' : (p === '...' ? 'text-slate-400 cursor-default' : 'bg-white dark:bg-[#12141a] border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5')"
                                    class="min-w-[32px] h-8 px-2 rounded-lg text-xs font-mono font-semibold transition cursor-pointer flex items-center justify-center"
                                    x-text="p">
                            </button>
                        </template>
                    </div>
                    <button type="button" @click="nextGridPage()" :disabled="gridPage === gridTotalPages"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer flex items-center gap-1">
                        <span class="hidden sm:inline">ถัดไป</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                    <button type="button" @click="setGridPage(gridTotalPages)" :disabled="gridPage === gridTotalPages"
                            class="p-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าสุดท้าย">
                        <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- TAB 2: Table Comparison View -->
    <div x-show="activeTab === 'table'" class="bg-white dark:bg-[#181a20] rounded-3xl border border-slate-200/80 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-bold font-heading text-slate-900 dark:text-white">ตารางเปรียบเทียบสถิติทุกประเภทโครงการ</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">เปรียบเทียบความคืบหน้า งบประมาณ และยอดเบิกจ่ายตามหมวดหมู่</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">พบ <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="filteredCategories.length"></span> หมวดหมู่</span>
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
                        <th class="py-3.5 px-4 font-bold text-left min-w-[170px]">ความคืบหน้าเฉลี่ย</th>
                        <th class="py-3.5 px-4 font-bold text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04] text-slate-700 dark:text-slate-200">
                    <?php $idx = 1; foreach ($categories as $cat): ?>
                        <?php
                        $pCount = (int)$cat['project_count'];
                        $subCount = (int)$cat['sub_project_count'];
                        $bTotal = (float)$cat['total_budget'];
                        $dTotal = (float)$cat['total_disbursed'];
                        $avgProg = (float)$cat['avg_progress'];
                        $disbPct = $bTotal > 0 ? ($dTotal / $bTotal) * 100 : 0;
                        $tier = ProgressService::getProgressTier($avgProg);
                        ?>
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-white/[0.02] transition"
                            x-show="isTableVisible(<?= $cat['id'] ?>)">
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
                            <td class="py-4 px-4 min-w-[170px]">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono font-bold text-xs w-11 text-right shrink-0 <?= $tier['textClass'] ?>"><?= number_format($avgProg, 1) ?>%</span>
                                    <div class="flex-1 bg-slate-100 dark:bg-white/[0.08] h-2.5 rounded-full overflow-hidden p-0.5 border border-slate-200/50 dark:border-white/5" title="ความคืบหน้าเฉลี่ย <?= number_format($avgProg, 1) ?>%">
                                        <div class="bg-gradient-to-r <?= $tier['gradient'] ?> h-full rounded-full transition-all duration-500 shadow-sm" 
                                             style="width: <?= $avgProg > 0 ? min(100, max(6, $avgProg)) : 0 ?>%"></div>
                                    </div>
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

                    <!-- Empty state in Table when allCategories is empty -->
                    <tr x-show="allCategories.length === 0">
                        <td colspan="8" class="text-center py-12 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="folder-plus" class="w-8 h-8 text-emerald-500/50 mb-2"></i>
                                <span class="font-bold text-slate-700 dark:text-slate-300">ยังไม่มีประเภทโครงการในระบบ</span>
                                <span class="text-xs text-slate-400 mt-0.5">กดปุ่ม "เพิ่มประเภทโครงการ" ด้านบน เพื่อสร้างประเภทโครงการใหม่</span>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty state in Table when search returns 0 -->
                    <tr x-show="allCategories.length > 0 && filteredCategories.length === 0">
                        <td colspan="8" class="text-center py-10 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="search-x" class="w-8 h-8 text-slate-300 dark:text-slate-600 mb-2"></i>
                                <span class="font-medium text-slate-600 dark:text-slate-400">ไม่พบประเภทโครงการที่ค้นหา</span>
                                <span class="text-xs text-slate-400 mt-0.5">ไม่มีข้อมูลที่ตรงกับคำค้นหา</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table View Pagination Bar -->
        <div x-show="filteredCategories.length > 0" class="p-4 border-t border-slate-100 dark:border-white/[0.06] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
            <div class="flex items-center gap-3">
                <span class="text-slate-500 dark:text-slate-400">
                    แสดง <span class="font-bold text-slate-900 dark:text-white font-mono" x-text="tableStartIndex"></span> ถึง 
                    <span class="font-bold text-slate-900 dark:text-white font-mono" x-text="tableEndIndex"></span> จาก 
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono" x-text="filteredCategories.length"></span> หมวดหมู่
                </span>
                <div class="relative shrink-0 ml-2 border-l border-slate-200 dark:border-white/10 pl-3" x-data="{ openTablePerPage: false }" @click.outside="openTablePerPage = false">
                    <button type="button" 
                            @click="openTablePerPage = !openTablePerPage" 
                            class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-white/[0.04] hover:bg-slate-100 dark:hover:bg-white/[0.08] px-2.5 py-1 rounded-xl border border-slate-200 dark:border-white/10 hover:border-emerald-500/40 transition-all cursor-pointer shadow-2xs">
                        <span class="text-slate-400 dark:text-slate-500 font-normal text-[11px]">แสดงต่อหน้า:</span>
                        <span class="font-bold text-slate-900 dark:text-white font-mono" x-text="tablePerPage === 'all' ? 'ทั้งหมด' : tablePerPage"></span>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-150 shrink-0" :class="{ 'rotate-180': openTablePerPage }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Themed Dropdown Flyout (Pops Up) -->
                    <div x-show="openTablePerPage" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-3 bottom-full mb-1.5 w-32 bg-white dark:bg-[#181a20] rounded-2xl shadow-xl dark:shadow-2xl border border-slate-200 dark:border-white/10 p-1.5 z-50 text-xs text-left">
                        <div class="px-2.5 py-1 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider font-heading border-b border-slate-100 dark:border-white/[0.06] mb-1">
                            จำนวนต่อหน้า
                        </div>
                        <div class="space-y-0.5">
                            <template x-for="opt in [10, 20, 50, 'all']" :key="opt">
                                <button type="button" 
                                        @click="setTablePerPage(opt); openTablePerPage = false" 
                                        class="w-full text-left px-2.5 py-1.5 rounded-xl text-xs flex items-center justify-between transition cursor-pointer"
                                        :class="tablePerPage == opt 
                                            ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/20' 
                                            : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5'">
                                    <span x-text="opt === 'all' ? 'ทั้งหมด' : opt + ' รายการ'"></span>
                                    <svg x-show="tablePerPage == opt" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination buttons -->
            <template x-if="tableTotalPages > 1 && tablePerPage !== 'all'">
                <div class="flex items-center gap-1">
                    <button type="button" @click="setTablePage(1)" :disabled="tablePage === 1"
                            class="p-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าแรก">
                        <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                    </button>
                    <button type="button" @click="prevTablePage()" :disabled="tablePage === 1"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer flex items-center gap-1">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">ก่อนหน้า</span>
                    </button>
                    <div class="flex items-center gap-1 px-1">
                        <template x-for="(p, idx) in tableVisiblePages" :key="idx">
                            <button type="button" 
                                    @click="setTablePage(p)"
                                    :disabled="p === '...'"
                                    :class="p === tablePage ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-500/20 border border-emerald-600' : (p === '...' ? 'text-slate-400 cursor-default' : 'bg-white dark:bg-[#12141a] border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5')"
                                    class="min-w-[32px] h-8 px-2 rounded-lg text-xs font-mono font-semibold transition cursor-pointer flex items-center justify-center"
                                    x-text="p">
                            </button>
                        </template>
                    </div>
                    <button type="button" @click="nextTablePage()" :disabled="tablePage === tableTotalPages"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer flex items-center gap-1">
                        <span class="hidden sm:inline">ถัดไป</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                    <button type="button" @click="setTablePage(tableTotalPages)" :disabled="tablePage === tableTotalPages"
                            class="p-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าสุดท้าย">
                        <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- MODAL 1: Create New Category -->
    <?php if ($isAdmin): ?>
    <template x-teleport="body">
    <div x-show="createModal" 
         x-cloak
         @click.self="createModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop-smooth"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white dark:bg-[#181a20] rounded-3xl max-w-lg w-full border border-slate-200 dark:border-white/10 shadow-2xl overflow-hidden modal-box-smooth transform-gpu"
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
                    <input type="text" name="name" required maxlength="255" placeholder="เช่น กิจกรรมเพื่อสนับสนุนและส่งเสริมการจัดบริการสาธารณสุข หรือ งานป้องกันและบรรเทาสาธารณภัย"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#12141a] text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">ความยาว 2 - 255 ตัวอักษร (รองรับชื่อหมวดหมู่ตามระเบียบ สปสช. / กองทุนสุขภาพ)</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        รายละเอียดคำอธิบาย
                    </label>
                    <textarea name="description" rows="3" placeholder="ระบุขอบเขตหรือลักษณะโครงการที่อยู่ในหมวดหมู่นี้..."
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#12141a] text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
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
    </template>
    <?php endif; ?>

    <!-- MODAL 2: Edit Category -->
    <?php if ($isAdmin): ?>
    <template x-teleport="body">
    <div x-show="editModal" 
         x-cloak
         @click.self="editModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop-smooth"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white dark:bg-[#181a20] rounded-3xl max-w-lg w-full border border-slate-200 dark:border-white/10 shadow-2xl overflow-hidden modal-box-smooth transform-gpu"
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
                    <input type="text" name="name" x-model="editData.name" required maxlength="255"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#12141a] text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">ความยาว 2 - 255 ตัวอักษร (รองรับชื่อหมวดหมู่ตามระเบียบ สปสช. / กองทุนสุขภาพ)</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        รายละเอียดคำอธิบาย
                    </label>
                    <textarea name="description" x-model="editData.description" rows="3"
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#12141a] text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
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
    </template>
    <?php endif; ?>

    <!-- MODAL 3: Delete Confirmation Modal (with Safety Protection) -->
    <?php if ($isAdmin): ?>
    <template x-teleport="body">
    <div x-show="deleteModal" 
         x-cloak
         @click.self="deleteModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop-smooth"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white dark:bg-[#181a20] rounded-3xl max-w-md w-full border border-slate-200 dark:border-white/10 shadow-2xl overflow-hidden modal-box-smooth transform-gpu"
             @click.outside="deleteModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="p-6 text-center">
                <!-- Icon based on linked project count -->
                <div class="w-14 h-14 rounded-3xl mx-auto flex items-center justify-center mb-4"
                     :class="deleteData.project_count > 0 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'">
                    <i x-show="deleteData.project_count > 0" data-lucide="alert-triangle" class="w-7 h-7"></i>
                    <i x-show="!(deleteData.project_count > 0)" data-lucide="trash-2" class="w-7 h-7"></i>
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
    </template>
    <?php endif; ?>

</div>

<script>
function categoriesPage() {
    return {
        allCategories: Object.freeze(<?= $categoriesJson ?>),
        createModal: false,
        editModal: false,
        deleteModal: false,
        activeTab: 'grid', // 'grid' | 'table'
        searchQuery: '',
        editData: { id: '', name: '', description: '' },
        deleteData: { id: '', name: '', project_count: 0 },

        // Grid pagination
        gridPage: 1,
        gridPerPage: 6,

        // Table pagination
        tablePage: 1,
        tablePerPage: 10,

        get filteredCategories() {
            if (!this.searchQuery.trim()) return this.allCategories;
            const q = this.searchQuery.toLowerCase().trim();
            return this.allCategories.filter(c => c.search_text.includes(q));
        },

        // Grid getters
        get gridTotalPages() {
            if (this.gridPerPage === 'all') return 1;
            const per = parseInt(this.gridPerPage) || 6;
            return Math.ceil(this.filteredCategories.length / per) || 1;
        },
        get paginatedGridIds() {
            if (this.gridPerPage === 'all') {
                return new Set(this.filteredCategories.map(c => c.id));
            }
            const per = parseInt(this.gridPerPage) || 6;
            const start = (this.gridPage - 1) * per;
            return new Set(this.filteredCategories.slice(start, start + per).map(c => c.id));
        },
        isGridVisible(id) {
            return this.paginatedGridIds.has(id);
        },
        get gridStartIndex() {
            if (this.filteredCategories.length === 0) return 0;
            if (this.gridPerPage === 'all') return 1;
            const per = parseInt(this.gridPerPage) || 6;
            return (this.gridPage - 1) * per + 1;
        },
        get gridEndIndex() {
            if (this.filteredCategories.length === 0) return 0;
            if (this.gridPerPage === 'all') return this.filteredCategories.length;
            const per = parseInt(this.gridPerPage) || 6;
            return Math.min(this.gridPage * per, this.filteredCategories.length);
        },
        get gridVisiblePages() {
            return this.getVisiblePages(this.gridTotalPages, this.gridPage);
        },
        setGridPage(p) {
            if (p === '...' || p < 1 || p > this.gridTotalPages || p === this.gridPage) return;
            this.gridPage = p;
            this.$nextTick(() => { window.safeCreateIcons && window.safeCreateIcons(this.$el); });
        },
        prevGridPage() {
            if (this.gridPage > 1) this.setGridPage(this.gridPage - 1);
        },
        nextGridPage() {
            if (this.gridPage < this.gridTotalPages) this.setGridPage(this.gridPage + 1);
        },
        setGridPerPage(val) {
            this.gridPerPage = val === 'all' ? 'all' : parseInt(val);
            this.gridPage = 1;
            this.$nextTick(() => { window.safeCreateIcons && window.safeCreateIcons(this.$el); });
        },

        // Table getters
        get tableTotalPages() {
            if (this.tablePerPage === 'all') return 1;
            const per = parseInt(this.tablePerPage) || 10;
            return Math.ceil(this.filteredCategories.length / per) || 1;
        },
        get paginatedTableIds() {
            if (this.tablePerPage === 'all') {
                return new Set(this.filteredCategories.map(c => c.id));
            }
            const per = parseInt(this.tablePerPage) || 10;
            const start = (this.tablePage - 1) * per;
            return new Set(this.filteredCategories.slice(start, start + per).map(c => c.id));
        },
        isTableVisible(id) {
            return this.paginatedTableIds.has(id);
        },
        get tableStartIndex() {
            if (this.filteredCategories.length === 0) return 0;
            if (this.tablePerPage === 'all') return 1;
            const per = parseInt(this.tablePerPage) || 10;
            return (this.tablePage - 1) * per + 1;
        },
        get tableEndIndex() {
            if (this.filteredCategories.length === 0) return 0;
            if (this.tablePerPage === 'all') return this.filteredCategories.length;
            const per = parseInt(this.tablePerPage) || 10;
            return Math.min(this.tablePage * per, this.filteredCategories.length);
        },
        get tableVisiblePages() {
            return this.getVisiblePages(this.tableTotalPages, this.tablePage);
        },
        setTablePage(p) {
            if (p === '...' || p < 1 || p > this.tableTotalPages || p === this.tablePage) return;
            this.tablePage = p;
            this.$nextTick(() => { window.safeCreateIcons && window.safeCreateIcons(this.$el); });
        },
        prevTablePage() {
            if (this.tablePage > 1) this.setTablePage(this.tablePage - 1);
        },
        nextTablePage() {
            if (this.tablePage < this.tableTotalPages) this.setTablePage(this.tablePage + 1);
        },
        setTablePerPage(val) {
            this.tablePerPage = val === 'all' ? 'all' : parseInt(val);
            this.tablePage = 1;
            this.$nextTick(() => { window.safeCreateIcons && window.safeCreateIcons(this.$el); });
        },

        getVisiblePages(total, current) {
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

        onSearchChange() {
            this.gridPage = 1;
            this.tablePage = 1;
            this.$nextTick(() => { window.safeCreateIcons && window.safeCreateIcons(this.$el); });
        },

        openEdit(cat) {
            this.editData = {
                id: cat.id,
                name: cat.name || '',
                description: cat.description || ''
            };
            this.editModal = true;
            this.$nextTick(() => { window.safeCreateIcons && window.safeCreateIcons(); });
        },

        openDelete(cat) {
            this.deleteData = {
                id: cat.id,
                name: cat.name,
                project_count: parseInt(cat.project_count || 0)
            };
            this.deleteModal = true;
            this.$nextTick(() => { window.safeCreateIcons && window.safeCreateIcons(); });
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>
