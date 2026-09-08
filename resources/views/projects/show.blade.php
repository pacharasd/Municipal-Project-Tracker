<?php
ob_start();
$title = htmlspecialchars($project['name']);

// Prepare JSON summary for sub-projects reactive pagination & search
$subProjectsSummary = [];
foreach ($project['sub_projects'] as $sub) {
    $subProjectsSummary[] = [
        'id' => (int)$sub['id'],
        'name' => (string)$sub['name'],
        'status' => (string)($sub['status'] ?? 'not_started'),
        'search_text' => mb_strtolower(($sub['name'] ?? '') . ' ' . (!empty($sub['responsible_person']) ? $sub['responsible_person'] : ($sub['responsible_name'] ?? '')), 'UTF-8'),
    ];
}
$subProjectsJson = json_encode($subProjectsSummary, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
?>

<div class="space-y-6 w-full max-w-full min-w-0" x-data="projectShowPage()">
    <!-- Breadcrumb & Back -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="<?= \App\Core\Router::url('/projects') ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> ย้อนกลับไปหน้ารายการโครงการ
        </a>
        <div class="flex items-center gap-2">
            <?php if (\App\Core\Auth::canManageProjects()): ?>
                <button type="button" @click="editModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-[#181a20] border border-slate-300 dark:border-white/10 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-all shadow-sm cursor-pointer">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> แก้ไขข้อมูล
                </button>
            <?php endif; ?>
            <?php if (\App\Core\Auth::isAdmin()): ?>
                <form action="<?= \App\Core\Router::url("/projects/{$project['id']}/delete") ?>" method="POST" onsubmit="return confirm('ยืนยันการลบโครงการนี้และโครงการย่อยทั้งหมดหรือไม่? ข้อมูลจะไม่สามารถกู้คืนได้');">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/15 border border-rose-200 dark:border-rose-500/30 rounded-xl hover:bg-rose-100 dark:hover:bg-rose-500/25 transition-all cursor-pointer">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> ลบโครงการ
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Project Info Card -->
    <div class="bg-white dark:bg-[#161922] p-6 sm:p-8 rounded-2xl border border-slate-200/80 dark:border-white/[0.08] shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-50 dark:bg-blue-500/15 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-500/30"><?= htmlspecialchars(!empty($project['responsible_person']) ? $project['responsible_person'] : ($project['department_name'] ?? 'ไม่ระบุหน่วยงาน')) ?></span>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-white/10">ปีงบประมาณ <?= htmlspecialchars((string)($project['fiscal_year'] ?? '-')) ?></span>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-50 dark:bg-purple-500/15 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-500/30"><?= htmlspecialchars($project['category_name'] ?? 'ทั่วไป') ?></span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 dark:text-white tracking-tight mt-3">
            <?= htmlspecialchars($project['name']) ?>
        </h1>

        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 max-w-4xl leading-relaxed">
            <?= nl2br(htmlspecialchars($project['description'] ?? 'ไม่มีคำอธิบายเพิ่มเติม')) ?>
        </p>

        <?php if (!empty($project['objective'])): ?>
            <div class="mt-4 p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100/80 dark:border-indigo-500/20 text-slate-800 dark:text-slate-200 max-w-4xl">
                <div class="text-xs font-bold text-indigo-900 dark:text-indigo-300 flex items-center gap-1.5 mb-1.5">
                    <i data-lucide="target" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                    วัตถุประสงค์โครงการ
                </div>
                <div class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                    <?= nl2br(htmlspecialchars($project['objective'] ?? '')) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php
            $totalSubBudget = array_sum(array_column($project['sub_projects'] ?? [], 'budget'));
            $remainingParentBudget = max(0, (float)($project['budget'] ?? 0) - $totalSubBudget);
        ?>
        <!-- KPI Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100 dark:border-white/[0.08]">
            <div>
                <div class="text-xs text-slate-400 dark:text-slate-500">งบประมาณที่ได้รับจัดสรร</div>
                <div class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white mt-0.5"><?= number_format($project['budget'], 2) ?> <span class="text-xs font-normal text-slate-500">บาท</span></div>
            </div>
            <div>
                <div class="text-xs text-slate-400 dark:text-slate-500">ยอดเบิกจ่ายแล้ว</div>
                <div class="text-lg sm:text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-0.5"><?= number_format($project['disbursed_amount'], 2) ?> <span class="text-xs font-normal text-slate-500">บาท</span></div>
            </div>
            <div>
                <div class="text-xs text-slate-400 dark:text-slate-500">คงเหลือ</div>
                <div class="text-lg sm:text-xl font-bold text-purple-600 dark:text-purple-400 mt-0.5"><?= number_format($remainingParentBudget, 2) ?> <span class="text-xs font-normal text-slate-500">บาท</span></div>
            </div>
            <?php $pTier = \App\Services\ProgressService::getProgressTier((float)$project['progress'], $project['status'] ?? null); ?>
            <div>
                <div class="text-xs text-slate-400 dark:text-slate-500 flex items-center justify-between">
                    <span>ความก้าวหน้าเฉลี่ย (Rule #47)</span>
                    <span class="font-bold <?= $pTier['textClass'] ?>"><?= number_format($project['progress'], 1) ?>%</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-[#1f222e] rounded-full h-2 mt-2 overflow-hidden p-0.5">
                    <div class="bg-gradient-to-r <?= $pTier['gradient'] ?> h-1.5 rounded-full transition-all duration-500" style="width: <?= min(100, (float)$project['progress']) ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub-projects Section -->
    <div class="bg-white dark:bg-[#161922] p-6 sm:p-8 rounded-2xl border border-slate-200/80 dark:border-white/[0.08] shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold font-heading text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="layers" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                    โครงการย่อยในความรับผิดชอบ (<?= count($project['sub_projects']) ?> โครงการ)
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">คลิกเพื่อดูรายละเอียดกิจกรรม งบประมาณ บันทึกปัญหา และอัปเดตความคืบหน้า</p>
            </div>

            <!-- Search and Action Button -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <?php if (!empty($project['sub_projects'])): ?>
                    <div class="relative min-w-[200px]">
                        <input type="text" x-model="subSearch" @input="subPage = 1" placeholder="ค้นหาชื่อโครงการย่อย..." 
                               class="w-full pl-8 pr-8 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#1f222e] text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <button type="button" x-show="subSearch" @click="subSearch = ''; subPage = 1;" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5 cursor-pointer">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (\App\Core\Auth::canManageProjects()): ?>
                    <button type="button" @click="createSubModal = true; $nextTick(() => { window.safeCreateIcons && window.safeCreateIcons($el); });" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all shadow-md shadow-emerald-600/20 whitespace-nowrap cursor-pointer shrink-0">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i> เพิ่มโครงการย่อย
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($project['sub_projects'])): ?>
            <div class="p-8 text-center text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-[#181a20] rounded-2xl border border-slate-200 dark:border-white/[0.08]">
                ยังไม่มีโครงการย่อยภายใต้โครงการหลักนี้ กรุณากดปุ่ม "เพิ่มโครงการย่อย"
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[780px]">
                    <thead>
                        <tr class="border-b border-slate-200/80 dark:border-white/[0.08] text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="py-3 px-4 min-w-[240px]">ชื่อโครงการย่อย</th>
                            <th class="py-3 px-4 whitespace-nowrap">งบประมาณ (บาท)</th>
                            <th class="py-3 px-4 whitespace-nowrap text-center">จำนวนครั้งกิจกรรม</th>
                            <th class="py-3 px-4 whitespace-nowrap min-w-[140px]">ความก้าวหน้า</th>
                            <th class="py-3 px-4 whitespace-nowrap text-center">สถานะ</th>
                            <th class="py-3 px-4 whitespace-nowrap text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06] text-sm">
                        <?php foreach ($project['sub_projects'] as $sub): ?>
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-white/[0.02] transition-colors"
                                x-show="isSubVisible(<?= $sub['id'] ?>)">
                                <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">
                                    <a href="<?= \App\Core\Router::url("/sub-projects/{$sub['id']}") ?>" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                        <?= htmlspecialchars($sub['name'] ?? '') ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white whitespace-nowrap"><?= number_format($sub['budget'], 2) ?></td>
                                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium whitespace-nowrap text-center">
                                    <?= $sub['actual_activity_count'] ?> / <?= $sub['planned_activity_count'] ?> ครั้ง
                                </td>
                                <?php $subTier = \App\Services\ProgressService::getProgressTier((float)$sub['progress'], $sub['status'] ?? null); ?>
                                <td class="py-3.5 px-4 w-44">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="font-bold <?= $subTier['textClass'] ?>"><?= number_format($sub['progress'], 1) ?>%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 dark:bg-[#1f222e] rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full bg-gradient-to-r <?= $subTier['gradient'] ?> transition-all duration-500" style="width: <?= min(100, (float)$sub['progress']) ?>%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <?php
                                    $sClass = match($sub['status']) {
                                        'completed' => 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-200/80 dark:border-emerald-500/30',
                                        'in_progress' => 'bg-blue-50 dark:bg-blue-500/15 text-blue-700 dark:text-blue-400 border-blue-200/80 dark:border-blue-500/30',
                                        'has_problem' => 'bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-400 border-rose-200/80 dark:border-rose-500/30 animate-pulse',
                                        default => 'bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400 border-amber-200/80 dark:border-amber-500/30'
                                    };
                                    $sLabel = match($sub['status']) {
                                        'completed' => 'เสร็จสิ้น',
                                        'in_progress' => 'กำลังดำเนินการ',
                                        'has_problem' => 'มีปัญหา',
                                        default => 'ยังไม่เริ่ม'
                                    };
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full border whitespace-nowrap <?= $sClass ?>">
                                        <?= $sLabel ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <a href="<?= \App\Core\Router::url("/sub-projects/{$sub['id']}") ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200/60 dark:border-emerald-500/30 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-500/25 transition-all whitespace-nowrap shadow-sm">
                                        ดูรายละเอียด <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Empty state when search has no match -->
                        <tr x-show="filteredSubProjects.length === 0">
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i data-lucide="search-x" class="w-8 h-8 text-slate-300 dark:text-slate-600 mb-2"></i>
                                    <span class="font-medium text-slate-600 dark:text-slate-400">ไม่พบโครงการย่อยที่ค้นหา</span>
                                    <span class="text-xs text-slate-400 mt-0.5">ไม่มีข้อมูลที่ตรงกับคำค้นหา</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Sub-projects Pagination Footer -->
            <div x-show="filteredSubProjects.length > 0" class="pt-4 border-t border-slate-100 dark:border-white/[0.06] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-3">
                    <span class="text-slate-500 dark:text-slate-400">
                        แสดง <span class="font-bold text-slate-900 dark:text-white font-mono" x-text="subStartIndex"></span> ถึง 
                        <span class="font-bold text-slate-900 dark:text-white font-mono" x-text="subEndIndex"></span> จาก 
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono" x-text="filteredSubProjects.length"></span> โครงการย่อย
                    </span>
                    <div class="flex items-center gap-1.5 ml-2 border-l border-slate-200 dark:border-white/10 pl-3">
                        <span class="text-slate-400 text-[11px]">แสดงต่อหน้า:</span>
                        <select :value="subPerPage" @change="setSubPerPage($event.target.value)" 
                                class="px-2 py-1 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#1f222e] text-slate-700 dark:text-slate-200 text-xs font-medium focus:ring-1 focus:ring-emerald-500 focus:outline-none cursor-pointer">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="all">ทั้งหมด</option>
                        </select>
                    </div>
                </div>

                <!-- Pagination buttons -->
                <template x-if="subTotalPages > 1 && subPerPage !== 'all'">
                    <div class="flex items-center gap-1">
                        <button type="button" @click="setSubPage(1)" :disabled="subPage === 1"
                                class="p-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าแรก">
                            <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                        </button>
                        <button type="button" @click="prevSubPage()" :disabled="subPage === 1"
                                class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer flex items-center gap-1">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">ก่อนหน้า</span>
                        </button>
                        <div class="flex items-center gap-1 px-1">
                            <template x-for="(p, idx) in subVisiblePages" :key="idx">
                                <button type="button" 
                                        @click="setSubPage(p)"
                                        :disabled="p === '...'"
                                        :class="p === subPage ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-500/20 border border-emerald-600' : (p === '...' ? 'text-slate-400 cursor-default' : 'bg-white dark:bg-[#1f222e] border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5')"
                                        class="min-w-[32px] h-8 px-2 rounded-lg text-xs font-mono font-semibold transition cursor-pointer flex items-center justify-center"
                                        x-text="p">
                                </button>
                            </template>
                        </div>
                        <button type="button" @click="nextSubPage()" :disabled="subPage === subTotalPages"
                                class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer flex items-center gap-1">
                            <span class="hidden sm:inline">ถัดไป</span>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                        <button type="button" @click="setSubPage(subTotalPages)" :disabled="subPage === subTotalPages"
                                class="p-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าสุดท้าย">
                            <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </template>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal: Create Sub-project -->
    <template x-teleport="body">
        <div x-show="createSubModal" x-cloak @click.self="createSubModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop-smooth">
            <div class="bg-white dark:bg-[#161922] w-full max-w-3xl rounded-2xl shadow-2xl border border-slate-200 dark:border-white/10 overflow-hidden max-h-[90vh] flex flex-col modal-box-smooth transform-gpu">
                <div class="p-6 border-b border-slate-100 dark:border-white/[0.08] flex items-center justify-between flex-shrink-0">
                    <div>
                        <h3 class="text-lg font-bold font-heading text-slate-900 dark:text-white">เพิ่มโครงการย่อย</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">ภายใต้: <?= htmlspecialchars($project['name']) ?></p>
                    </div>
                    <button type="button" @click.stop="createSubModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                        <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                    </button>
                </div>

                <?php
                    $parentStartRaw = $project['start_date'] ?? '';
                    $parentEndRaw = $project['end_date'] ?? '';
                    $parentStartThai = !empty($parentStartRaw) ? date('d/m/', strtotime($parentStartRaw)) . (date('Y', strtotime($parentStartRaw)) + 543) : 'ไม่ระบุ';
                    $parentEndThai = !empty($parentEndRaw) ? date('d/m/', strtotime($parentEndRaw)) . (date('Y', strtotime($parentEndRaw)) + 543) : 'ไม่ระบุ';
                ?>
                <form action="<?= \App\Core\Router::url('/sub-projects') ?>" method="POST" 
                      @submit="
                        const pStart = '<?= $parentStartRaw ?>';
                        const pEnd = '<?= $parentEndRaw ?>';
                        const start = $el.querySelector('input[name=start_date]')?.value;
                        const end = $el.querySelector('input[name=end_date]')?.value;
                        if (!start) {
                            alert('กรุณาเลือกวันที่เริ่มต้น');
                            $event.preventDefault();
                            return false;
                        }
                        if (!end) {
                            alert('กรุณาเลือกวันที่สิ้นสุด');
                            $event.preventDefault();
                            return false;
                        }
                        if (pStart && start < pStart) {
                            alert('วันที่เริ่มต้นของโครงการย่อยต้องเท่ากับหรือมากกว่าวันที่เริ่มต้นของโครงการหลัก (<?= $parentStartThai ?>)');
                            $event.preventDefault();
                            return false;
                        }
                        if (pEnd && end > pEnd) {
                            alert('วันที่สิ้นสุดของโครงการย่อยต้องไม่เกินวันที่สิ้นสุดของโครงการหลัก (<?= $parentEndThai ?>)');
                            $event.preventDefault();
                            return false;
                        }
                        if (start > end) {
                            alert('วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุดของโครงการย่อย');
                            $event.preventDefault();
                            return false;
                        }
                        const maxBudget = <?= (float)$remainingParentBudget ?>;
                        const budgetVal = parseFloat($el.querySelector('input[name=budget]')?.value || 0);
                        if (budgetVal > maxBudget) {
                            alert('งบประมาณโครงการย่อย (' + budgetVal.toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท) ต้องไม่เกินงบประมาณคงเหลือของโครงการหลักที่จัดสรรได้ (' + maxBudget.toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท)');
                            $event.preventDefault();
                            return false;
                        }
                      "
                      class="p-6 space-y-4 overflow-y-auto flex-1">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="parent_id" value="<?= $project['id'] ?>">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">ชื่อโครงการย่อย <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="ระบุชื่อโครงการย่อย..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">ผู้รับผิดชอบโครงการ <span class="text-rose-500">*</span></label>
                        <input type="text" name="responsible_person" required maxlength="255" placeholder="ระบุชื่อผู้รับผิดชอบ เช่น นางสาวสมใจ รักดี หรือ กองสาธารณสุข" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">ประเภทกิจกรรม</label>
                            <input type="text" name="activity_type" placeholder="เช่น ตรวจสุขภาพ, งานก่อสร้าง, ฝึกอบรม" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">พื้นที่ดำเนินการ</label>
                            <input type="text" name="location" placeholder="เช่น ชุมชนวัดใหม่, สวนสาธารณะ" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">กลุ่มเป้าหมาย</label>
                            <input type="text" name="target_group" placeholder="เช่น ประชาชนทั่วไป, ผู้สูงอายุ" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">จำนวนกลุ่มเป้าหมาย (คน)</label>
                            <input type="number" min="0" name="target_quantity" placeholder="0" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">วัตถุประสงค์</label>
                        <textarea name="objective" rows="2" placeholder="วัตถุประสงค์ของโครงการ..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"></textarea>
                    </div>

                    <!-- Informational Box: Parent Project Date Bounds -->
                    <?php if (!empty($parentStartRaw) || !empty($parentEndRaw)): ?>
                        <div class="p-3 rounded-xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-800/60 text-xs text-blue-800 dark:text-blue-300 flex items-start gap-2.5">
                            <i data-lucide="calendar" class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5"></i>
                            <div>
                                <strong class="font-semibold">กรอบเวลาโครงการหลัก:</strong> 
                                <?= $parentStartThai ?> ถึง <?= $parentEndThai ?>
                                <div class="text-[11px] text-blue-600 dark:text-blue-400 mt-0.5">
                                    * โครงการย่อยต้องเริ่มต้นตั้งแต่วันที่ <?= $parentStartThai ?> เป็นต้นไป และต้องสิ้นสุดไม่เกินวันที่ <?= $parentEndThai ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">วันที่เริ่มต้น <span class="text-rose-500">*</span></label>
                            <div class="relative" x-data="thaiDatePicker({ name: 'start_date', value: '', align: 'left', placeholder: 'วว/ดด/ปปปป' })" @click.outside="open = false">
                                <input type="hidden" :name="name" :value="value">
                                <button type="button" 
                                        @click="toggle()" 
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white flex items-center justify-between focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors cursor-pointer">
                                    <span x-text="displayLabel" 
                                          :class="{ 'text-slate-400 dark:text-slate-500 font-normal': !value, 'font-medium text-slate-900 dark:text-white': value }"
                                          class="truncate">วว/ดด/ปปปป</span>
                                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                                <div x-show="open" 
                                     x-cloak
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute bottom-full mb-2 left-0 z-50 w-72 bg-white dark:bg-[#1f222e] rounded-2xl shadow-2xl border border-slate-200 dark:border-white/10 p-3.5">
                                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-white/10">
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="prevYear()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition cursor-pointer" title="ปีก่อนหน้า">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                                            </button>
                                            <button type="button" @click="prevMonth()" class="p-1 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition cursor-pointer" title="เดือนก่อนหน้า">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                            </button>
                                        </div>
                                        <div class="text-xs font-bold text-slate-800 dark:text-white" x-text="monthLabel"></div>
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="nextMonth()" class="p-1 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition cursor-pointer" title="เดือนถัดไป">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </button>
                                            <button type="button" @click="nextYear()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition cursor-pointer" title="ปีถัดไป">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-7 gap-1 mb-1 text-center">
                                        <template x-for="day in shortDays">
                                            <div class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 py-1" x-text="day"></div>
                                        </template>
                                    </div>
                                    <div class="grid grid-cols-7 gap-1 text-center">
                                        <template x-for="(item, index) in days" :key="item.dateStr || (item.day + '-' + index)">
                                            <div>
                                                <button type="button" 
                                                        x-show="item.isCurrent"
                                                        @click.stop="selectDate(item)"
                                                        class="w-8 h-8 mx-auto text-xs flex items-center justify-center rounded-xl transition-all cursor-pointer"
                                                        :class="{
                                                            'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30': value === item.date,
                                                            'border border-emerald-500 text-emerald-600 dark:text-emerald-400 font-semibold hover:bg-emerald-50 dark:hover:bg-emerald-500/10': item.isToday && value !== item.date,
                                                            'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/10': value !== item.date && !item.isToday
                                                        }"
                                                        x-text="item.day">
                                                </button>
                                                <div x-show="!item.isCurrent" class="w-8 h-8 mx-auto text-xs flex items-center justify-center text-slate-300 dark:text-slate-600 pointer-events-none" x-text="item.day"></div>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-slate-100 dark:border-white/10 flex items-center justify-between text-xs">
                                        <button type="button" @click="clear()" class="text-slate-400 hover:text-rose-500 transition cursor-pointer font-medium">ล้างค่า</button>
                                        <button type="button" @click="selectToday()" class="text-emerald-600 dark:text-emerald-400 hover:underline transition cursor-pointer font-semibold">วันนี้</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">วันที่สิ้นสุด <span class="text-rose-500">*</span></label>
                            <div class="relative" x-data="thaiDatePicker({ name: 'end_date', value: '', align: 'right', placeholder: 'วว/ดด/ปปปป' })" @click.outside="open = false">
                                <input type="hidden" :name="name" :value="value">
                                <button type="button" 
                                        @click="toggle()" 
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white flex items-center justify-between focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors cursor-pointer">
                                    <span x-text="displayLabel" 
                                          :class="{ 'text-slate-400 dark:text-slate-500 font-normal': !value, 'font-medium text-slate-900 dark:text-white': value }"
                                          class="truncate">วว/ดด/ปปปป</span>
                                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                                <div x-show="open" 
                                     x-cloak
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute bottom-full mb-2 right-0 z-50 w-72 bg-white dark:bg-[#1f222e] rounded-2xl shadow-2xl border border-slate-200 dark:border-white/10 p-3.5">
                                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-white/10">
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="prevYear()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition cursor-pointer" title="ปีก่อนหน้า">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                                            </button>
                                            <button type="button" @click="prevMonth()" class="p-1 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition cursor-pointer" title="เดือนก่อนหน้า">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                            </button>
                                        </div>
                                        <div class="text-xs font-bold text-slate-800 dark:text-white" x-text="monthLabel"></div>
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="nextMonth()" class="p-1 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition cursor-pointer" title="เดือนถัดไป">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </button>
                                            <button type="button" @click="nextYear()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition cursor-pointer" title="ปีถัดไป">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-7 gap-1 mb-1 text-center">
                                        <template x-for="day in shortDays">
                                            <div class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 py-1" x-text="day"></div>
                                        </template>
                                    </div>
                                    <div class="grid grid-cols-7 gap-1 text-center">
                                        <template x-for="(item, index) in days" :key="item.dateStr || (item.day + '-' + index)">
                                            <div>
                                                <button type="button" 
                                                        x-show="item.isCurrent"
                                                        @click.stop="selectDate(item)"
                                                        class="w-8 h-8 mx-auto text-xs flex items-center justify-center rounded-xl transition-all cursor-pointer"
                                                        :class="{
                                                            'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30': value === item.date,
                                                            'border border-emerald-500 text-emerald-600 dark:text-emerald-400 font-semibold hover:bg-emerald-50 dark:hover:bg-emerald-500/10': item.isToday && value !== item.date,
                                                            'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/10': value !== item.date && !item.isToday
                                                        }"
                                                        x-text="item.day">
                                                </button>
                                                <div x-show="!item.isCurrent" class="w-8 h-8 mx-auto text-xs flex items-center justify-center text-slate-300 dark:text-slate-600 pointer-events-none" x-text="item.day"></div>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-slate-100 dark:border-white/10 flex items-center justify-between text-xs">
                                        <button type="button" @click="clear()" class="text-slate-400 hover:text-rose-500 transition cursor-pointer font-medium">ล้างค่า</button>
                                        <button type="button" @click="selectToday()" class="text-emerald-600 dark:text-emerald-400 hover:underline transition cursor-pointer font-semibold">วันนี้</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div x-data="{ subBudget: '', maxBudget: <?= (float)$remainingParentBudget ?> }" class="flex flex-col justify-between">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1 h-5 flex items-center truncate" title="งบประมาณ (บาท)">
                                    งบประมาณ (บาท) <span class="text-rose-500 ml-0.5">*</span>
                                </label>
                                <input type="number" step="0.01" min="0" 
                                       max="<?= (float)$remainingParentBudget ?>"
                                       name="budget" x-model="subBudget" required placeholder="0.00" 
                                       :class="{ 'border-rose-400 dark:border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 text-rose-600': parseFloat(subBudget || 0) > maxBudget }"
                                       class="w-full h-10 px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                            </div>
                            <div class="h-4 mt-1 flex items-center overflow-hidden">
                                <template x-if="parseFloat(subBudget || 0) > maxBudget">
                                    <p class="text-[11px] text-rose-500 font-medium truncate flex items-center gap-1" title="ห้ามเกินงบประมาณคงเหลือ: <?= number_format($remainingParentBudget, 2) ?> บาท">
                                        <i data-lucide="alert-circle" class="w-3 h-3 inline shrink-0"></i> เกินงบจัดสรรได้ (สูงสุด <?= number_format($remainingParentBudget, 2) ?> บ.)
                                    </p>
                                </template>
                                <template x-if="!(parseFloat(subBudget || 0) > maxBudget)">
                                    <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium truncate" title="คงเหลือที่จัดสรรได้: <?= number_format($remainingParentBudget, 2) ?> บาท">
                                        คงเหลือจัดสรรได้: <?= number_format($remainingParentBudget, 2) ?> บ.
                                    </p>
                                </template>
                            </div>
                        </div>
                        <div class="flex flex-col justify-between">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1 h-5 flex items-center truncate" title="จำนวนครั้งที่วางแผน">
                                    จำนวนครั้งที่วางแผน <span class="text-rose-500 ml-0.5">*</span>
                                </label>
                                <input type="number" min="1" name="planned_activity_count" value="4" required 
                                       class="w-full h-10 px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                            </div>
                            <div class="h-4 mt-1 flex items-center overflow-hidden">
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate" title="ใช้คำนวณ Progress ตาม Rule #46">
                                    ใช้คำนวณ Progress ตาม Rule #46
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-col justify-between">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1 h-5 flex items-center truncate" title="โหมดการคำนวณความสำเร็จ">
                                    โหมดการคำนวณความสำเร็จ
                                </label>
                                <select name="progress_mode" 
                                        class="w-full h-10 px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                    <option value="auto">คำนวณอัตโนมัติ (AUTO)</option>
                                    <option value="manual">ระบุเอง (MANUAL)</option>
                                </select>
                            </div>
                            <div class="h-4 mt-1 flex items-center overflow-hidden">
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate" title="ระบบคำนวณอัตโนมัติ หรือ ระบุเอง">
                                    คำนวณอัตโนมัติ / ระบุเอง
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-white/[0.08] flex items-center justify-end gap-3 flex-shrink-0">
                        <button type="button" @click="createSubModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 rounded-xl transition-colors cursor-pointer">
                            ยกเลิก
                        </button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-md shadow-emerald-600/20 cursor-pointer">
                            บันทึกโครงการย่อย
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal: Edit Project -->
    <template x-teleport="body">
        <div x-show="editModal" x-cloak @click.self="editModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop-smooth">
            <div class="bg-white dark:bg-[#161922] w-full max-w-2xl rounded-2xl shadow-2xl border border-slate-200 dark:border-white/10 overflow-hidden max-h-[90vh] flex flex-col modal-box-smooth transform-gpu">
                <div class="p-6 border-b border-slate-100 dark:border-white/[0.08] flex items-center justify-between flex-shrink-0">
                    <div>
                        <h3 class="text-lg font-bold font-heading text-slate-900 dark:text-white">แก้ไขข้อมูลโครงการหลัก</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?= htmlspecialchars($project['name']) ?></p>
                    </div>
                    <button type="button" @click.stop="editModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                        <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                    </button>
                </div>

                <form action="<?= \App\Core\Router::url("/projects/{$project['id']}") ?>" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">ชื่อโครงการหลัก <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="<?= htmlspecialchars($project['name'] ?? '') ?>" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">ปีงบประมาณ <span class="text-rose-500">*</span></label>
                            <input type="number" name="fiscal_year" value="<?= htmlspecialchars((string)($project['fiscal_year'] ?? '')) ?>" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">งบประมาณรวม (บาท) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0" name="budget" value="<?= htmlspecialchars((string)($project['budget'] ?? 0)) ?>" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">สถานะโครงการ</label>
                            <select name="status" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                <option value="not_started" <?= $project['status'] === 'not_started' ? 'selected' : '' ?>>ยังไม่เริ่ม</option>
                                <option value="in_progress" <?= $project['status'] === 'in_progress' ? 'selected' : '' ?>>กำลังดำเนินการ</option>
                                <option value="completed" <?= $project['status'] === 'completed' ? 'selected' : '' ?>>เสร็จสิ้น</option>
                                <option value="has_problem" <?= $project['status'] === 'has_problem' ? 'selected' : '' ?>>มีปัญหา</option>
                                <option value="cancelled" <?= $project['status'] === 'cancelled' ? 'selected' : '' ?>>ยกเลิก</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">คำอธิบายโครงการ</label>
                        <textarea name="description" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">วัตถุประสงค์โครงการ</label>
                        <textarea name="objective" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"><?= htmlspecialchars($project['objective'] ?? '') ?></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-white/[0.08] flex items-center justify-end gap-3 flex-shrink-0">
                        <button type="button" @click="editModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 rounded-xl transition-colors cursor-pointer">
                            ยกเลิก
                        </button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-md shadow-emerald-600/20 cursor-pointer">
                            บันทึกการเปลี่ยนแปลง
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<script>
function projectShowPage() {
    return {
        createSubModal: false,
        editModal: false,
        allSubProjects: Object.freeze(<?= $subProjectsJson ?>),
        subSearch: '',
        subPage: 1,
        subPerPage: 5,

        get filteredSubProjects() {
            if (!this.subSearch.trim()) return this.allSubProjects;
            const q = this.subSearch.toLowerCase().trim();
            return this.allSubProjects.filter(s => s.search_text.includes(q));
        },

        get subTotalPages() {
            if (this.subPerPage === 'all') return 1;
            const per = parseInt(this.subPerPage) || 5;
            return Math.ceil(this.filteredSubProjects.length / per) || 1;
        },

        get paginatedSubIds() {
            if (this.subPerPage === 'all') {
                return new Set(this.filteredSubProjects.map(s => s.id));
            }
            const per = parseInt(this.subPerPage) || 5;
            const start = (this.subPage - 1) * per;
            return new Set(this.filteredSubProjects.slice(start, start + per).map(s => s.id));
        },

        isSubVisible(id) {
            return this.paginatedSubIds.has(id);
        },

        get subStartIndex() {
            if (this.filteredSubProjects.length === 0) return 0;
            if (this.subPerPage === 'all') return 1;
            const per = parseInt(this.subPerPage) || 5;
            return (this.subPage - 1) * per + 1;
        },

        get subEndIndex() {
            if (this.filteredSubProjects.length === 0) return 0;
            if (this.subPerPage === 'all') return this.filteredSubProjects.length;
            const per = parseInt(this.subPerPage) || 5;
            return Math.min(this.subPage * per, this.filteredSubProjects.length);
        },

        get subVisiblePages() {
            const total = this.subTotalPages;
            const current = this.subPage;
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

        setSubPage(p) {
            if (p === '...' || p < 1 || p > this.subTotalPages || p === this.subPage) return;
            this.subPage = p;
            this.$nextTick(() => { window.safeCreateIcons && window.safeCreateIcons(this.$el); });
        },

        prevSubPage() {
            if (this.subPage > 1) this.setSubPage(this.subPage - 1);
        },

        nextSubPage() {
            if (this.subPage < this.subTotalPages) this.setSubPage(this.subPage + 1);
        },

        setSubPerPage(val) {
            this.subPerPage = val === 'all' ? 'all' : parseInt(val);
            this.subPage = 1;
            this.$nextTick(() => { window.safeCreateIcons && window.safeCreateIcons(this.$el); });
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>
