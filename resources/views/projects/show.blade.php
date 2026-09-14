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

<script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>">
window.projectShowPage = function projectShowPage() {
    return {
        createSubModal: false,
        editModal: false,
        evalModal: false,
        evalScore: <?= $project['evaluation_score'] !== null ? json_encode((float)$project['evaluation_score']) : json_encode(round((float)$project['progress'], 2)) ?>,
        evalNotes: <?= json_encode($project['evaluation_notes'] ?? '') ?>,

        openEvalModal() {
            const defaultScore = <?= $project['evaluation_score'] !== null ? json_encode((float)$project['evaluation_score']) : json_encode(round((float)$project['progress'], 2)) ?>;
            if (this.evalScore === null || this.evalScore === '' || isNaN(parseFloat(this.evalScore))) {
                this.evalScore = defaultScore;
            }
            this.evalModal = true;
            this.$nextTick(() => {
                const inp = document.getElementById('evaluation-score-input');
                if (inp) {
                    inp.focus();
                    inp.select();
                }
                if (window.safeCreateIcons) window.safeCreateIcons(this.$el);
            });
        },

        get sliderValue() {
            const s = parseFloat(this.evalScore);
            return isNaN(s) ? 0 : Math.max(0, Math.min(100, s));
        },

        onSliderChange(e) {
            this.evalScore = parseFloat(e.target.value);
        },

        onScoreInput(e) {
            const val = e.target.value;
            if (val === '') {
                this.evalScore = '';
                return;
            }
            const num = parseFloat(val);
            if (!isNaN(num)) {
                if (num > 100) this.evalScore = 100;
                else if (num < 0) this.evalScore = 0;
                else this.evalScore = val;
            }
        },

        get currentGrade() {
            const s = parseFloat(this.evalScore);
            if (isNaN(s) || this.evalScore === '' || this.evalScore === null) {
                return { 
                    grade: '-', 
                    label: 'กรุณาระบุคะแนน (0 - 100)', 
                    cardBg: 'bg-slate-50 dark:bg-white/[0.02]', 
                    cardBorder: 'border-slate-200 dark:border-white/10', 
                    dividerBorder: 'border-slate-200 dark:border-white/10',
                    text: 'text-slate-600 dark:text-slate-400', 
                    bar: 'bg-slate-400' 
                };
            }
            if (s >= 90) return { 
                grade: 'A+', 
                label: 'ระดับ A+ (ดีเยี่ยมมาก)', 
                cardBg: 'bg-emerald-500/[0.06] dark:bg-emerald-500/[0.12]', 
                cardBorder: 'border-emerald-500/40 dark:border-emerald-500/40', 
                dividerBorder: 'border-emerald-500/20 dark:border-emerald-500/25',
                text: 'text-emerald-700 dark:text-emerald-400', 
                bar: 'bg-emerald-600 dark:bg-emerald-500' 
            };
            if (s >= 80) return { 
                grade: 'A', 
                label: 'ระดับ A (ดีเยี่ยม)', 
                cardBg: 'bg-sky-500/[0.06] dark:bg-sky-500/[0.12]', 
                cardBorder: 'border-sky-500/40 dark:border-sky-500/40', 
                dividerBorder: 'border-sky-500/20 dark:border-sky-500/25',
                text: 'text-sky-700 dark:text-sky-400', 
                bar: 'bg-sky-600 dark:bg-sky-500' 
            };
            if (s >= 70) return { 
                grade: 'B', 
                label: 'ระดับ B (ดี)', 
                cardBg: 'bg-indigo-500/[0.06] dark:bg-indigo-500/[0.12]', 
                cardBorder: 'border-indigo-500/40 dark:border-indigo-500/40', 
                dividerBorder: 'border-indigo-500/20 dark:border-indigo-500/25',
                text: 'text-indigo-700 dark:text-indigo-400', 
                bar: 'bg-indigo-600 dark:bg-indigo-500' 
            };
            if (s >= 60) return { 
                grade: 'C', 
                label: 'ระดับ C (พอใช้)', 
                cardBg: 'bg-amber-500/[0.06] dark:bg-amber-500/[0.12]', 
                cardBorder: 'border-amber-500/40 dark:border-amber-500/40', 
                dividerBorder: 'border-amber-500/20 dark:border-amber-500/25',
                text: 'text-amber-700 dark:text-amber-400', 
                bar: 'bg-amber-600 dark:bg-amber-500' 
            };
            return { 
                grade: 'D', 
                label: 'ระดับ D (ต้องปรับปรุง)', 
                cardBg: 'bg-rose-500/[0.06] dark:bg-rose-500/[0.12]', 
                cardBorder: 'border-rose-500/40 dark:border-rose-500/40', 
                dividerBorder: 'border-rose-500/20 dark:border-rose-500/25',
                text: 'text-rose-700 dark:text-rose-400', 
                bar: 'bg-rose-600 dark:bg-rose-500' 
            };
        },

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

<div class="space-y-6 w-full max-w-full min-w-0" x-data="projectShowPage()">
    <!-- Breadcrumb & Back -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="<?= \App\Core\Router::url('/projects') ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> ย้อนกลับไปหน้ารายการโครงการ
        </a>
        <div class="flex items-center gap-2">
            <?php if (\App\Core\Auth::canManageProjects()): ?>
                <button type="button" @click="openEvalModal()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-300 dark:border-emerald-500/30 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-500/25 transition-all shadow-sm cursor-pointer">
                    <i data-lucide="award" class="w-3.5 h-3.5"></i> <?= $project['evaluation_score'] !== null ? 'แก้ไขผลประเมิน' : 'ประเมินผลโครงการ' ?>
                </button>
                <button type="button" @click="editModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-[#181a20] border border-slate-300 dark:border-white/10 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-all shadow-sm cursor-pointer">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> แก้ไขข้อมูล
                </button>
            <?php endif; ?>
            <?php if (\App\Core\Auth::isAdmin()): ?>
                <form action="<?= \App\Core\Router::url("/projects/{$project['id']}/delete") ?>" method="POST" data-confirm="ยืนยันการลบโครงการนี้และกิจกรรมหลักทั้งหมดหรือไม่? ข้อมูลจะไม่สามารถกู้คืนได้">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/15 border border-rose-200 dark:border-rose-500/30 rounded-xl hover:bg-rose-100 dark:hover:bg-rose-500/25 transition-all cursor-pointer">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> ลบโครงการ
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Project Info Card -->
    <?php
        $mStartRaw = $project['start_date'] ?? '';
        $mEndRaw = $project['end_date'] ?? '';
        $mStartThai = !empty($mStartRaw) ? \App\Core\Helper::thaiDate($mStartRaw, false) : 'ไม่ระบุ';
        $mEndThai = !empty($mEndRaw) ? \App\Core\Helper::thaiDate($mEndRaw, false) : 'ไม่ระบุ';
        $mStartNumeric = !empty($mStartRaw) ? date('d/m/', strtotime($mStartRaw)) . (date('Y', strtotime($mStartRaw)) + 543) : '-';
        $mEndNumeric = !empty($mEndRaw) ? date('d/m/', strtotime($mEndRaw)) . (date('Y', strtotime($mEndRaw)) + 543) : '-';
        $mDurationDays = (!empty($mStartRaw) && !empty($mEndRaw)) ? max(1, round((strtotime($mEndRaw) - strtotime($mStartRaw)) / 86400) + 1) : null;
        $evalScore = $project['evaluation_score'] !== null ? (float)$project['evaluation_score'] : null;
        $evalGrade = $evalScore !== null ? \App\Services\ProjectService::calculateEvaluationGrade($evalScore) : null;
        $evalDateThai = !empty($project['evaluated_at']) ? \App\Core\Helper::thaiDate($project['evaluated_at'], true) : null;
    ?>
    <div class="bg-white dark:bg-[#161922] p-6 sm:p-8 rounded-2xl border border-slate-200/80 dark:border-white/[0.08] shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            <?php if (!empty($project['project_code'])): ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-mono font-bold rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-300/80 dark:border-emerald-500/30">
                    <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                    <span><?= htmlspecialchars($project['project_code']) ?></span>
                </span>
            <?php endif; ?>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-50 dark:bg-blue-500/15 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-500/30"><?= htmlspecialchars(!empty($project['responsible_person']) ? $project['responsible_person'] : ($project['department_name'] ?? 'ไม่ระบุหน่วยงาน')) ?></span>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-white/10">ปีงบประมาณ <?= htmlspecialchars((string)($project['fiscal_year'] ?? '-')) ?></span>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-50 dark:bg-purple-500/15 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-500/30"><?= htmlspecialchars($project['category_name'] ?? 'ทั่วไป') ?></span>
            <?php if ($evalGrade): ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full border <?= $evalGrade['bgClass'] ?>">
                    <i data-lucide="award" class="w-3.5 h-3.5"></i>
                    <span>ผลประเมิน: เกรด <?= $evalGrade['grade'] ?> (<?= number_format($evalScore, 1) ?>)</span>
                </span>
            <?php endif; ?>
        </div>

        <h1 class="text-xl sm:text-2xl font-bold font-heading text-slate-900 dark:text-white leading-snug tracking-tight mt-3">
            <?= htmlspecialchars($project['name']) ?>
        </h1>

        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 max-w-4xl leading-relaxed">
            <?= nl2br(htmlspecialchars($project['description'] ?? 'ไม่มีคำอธิบายเพิ่มเติม')) ?>
        </p>

        <?php if (!empty($project['objective'])): ?>
            <div class="mt-4 p-4 rounded-2xl bg-slate-50/80 dark:bg-[#12141a]/60 border border-slate-200/80 dark:border-white/[0.06] text-slate-800 dark:text-slate-200 w-full">
                <div class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-1.5">
                    <div class="w-7 h-7 rounded-xl bg-indigo-100 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                        <i data-lucide="target" class="w-4 h-4"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 font-heading">วัตถุประสงค์โครงการ</span>
                </div>
                <div class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed pl-9">
                    <?= nl2br(htmlspecialchars($project['objective'] ?? '')) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Project Timeline / Duration Info -->
        <div class="mt-4 p-4 rounded-2xl bg-slate-50/80 dark:bg-[#12141a]/60 border border-slate-200/80 dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-xs sm:text-sm">
                <!-- วันที่เริ่มต้น -->
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 block">วันที่เริ่มต้นโครงการ</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">
                            <?= $mStartThai ?>
                            <?php if (!empty($mStartRaw)): ?>
                                <span class="text-xs font-normal text-slate-500 dark:text-slate-400">(<?= $mStartNumeric ?>)</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <div class="hidden sm:block text-slate-300 dark:text-slate-700">|</div>

                <!-- วันที่สิ้นสุด -->
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                        <i data-lucide="calendar-check" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 block">วันที่สิ้นสุดโครงการ</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">
                            <?= $mEndThai ?>
                            <?php if (!empty($mEndRaw)): ?>
                                <span class="text-xs font-normal text-slate-500 dark:text-slate-400">(<?= $mEndNumeric ?>)</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if ($mDurationDays !== null): ?>
                <div class="inline-flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300 bg-white dark:bg-[#1a1d26] px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-white/10 font-medium self-start sm:self-auto shadow-xs">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-emerald-500"></i>
                    <span>ระยะเวลาดำเนินโครงการ: <strong class="text-slate-900 dark:text-white font-bold"><?= number_format($mDurationDays) ?></strong> วัน</span>
                </div>
            <?php endif; ?>
        </div>

        <?php
            $totalSubBudget = array_sum(array_column($project['sub_projects'] ?? [], 'budget'));
            $remainingParentBudget = max(0, (float)($project['budget'] ?? 0) - $totalSubBudget);
        ?>
        <!-- KPI Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100 dark:border-white/[0.08]">
            <div>
                <div class="text-xs text-slate-400 dark:text-slate-500">งบประมาณที่ได้รับจัดสรร</div>
                <div class="mt-0.5">
                    <?= \App\Core\Helper::moneyDisplay($project['budget'], 'card', 'left', 'text-slate-900 dark:text-white') ?>
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-400 dark:text-slate-500">ยอดเบิกจ่ายแล้ว</div>
                <div class="mt-0.5">
                    <?= \App\Core\Helper::moneyDisplay($project['disbursed_amount'], 'card', 'left', 'text-emerald-600 dark:text-emerald-400') ?>
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-400 dark:text-slate-500">คงเหลือ</div>
                <div class="mt-0.5">
                    <?= \App\Core\Helper::moneyDisplay($remainingParentBudget, 'card', 'left', 'text-purple-600 dark:text-purple-400') ?>
                </div>
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

        <!-- Evaluation Section Card -->
        <div class="mt-6 pt-6 border-t border-slate-100 dark:border-white/[0.08]">
            <div class="p-4 sm:p-5 rounded-2xl <?= $evalGrade ? $evalGrade['bgClass'] : 'bg-slate-50/80 dark:bg-[#12141a]/60 border border-slate-200/80 dark:border-white/[0.06]' ?> transition-all">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 font-heading font-black text-xl shadow-xs <?= $evalGrade ? $evalGrade['badgeClass'] : 'bg-slate-200 dark:bg-white/10 text-slate-500 dark:text-slate-400' ?>">
                            <?= $evalGrade ? $evalGrade['grade'] : '?' ?>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">ผลการประเมินโครงการหลัก</span>
                                <?php if ($evalGrade): ?>
                                    <span class="text-xs font-bold font-mono px-2 py-0.5 rounded-full <?= $evalGrade['badgeClass'] ?>">
                                        เกรด <?= $evalGrade['grade'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-slate-200 dark:bg-white/10 text-slate-600 dark:text-slate-400">
                                        ยังไม่ได้ประเมินผล
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mt-0.5 flex flex-wrap items-center gap-2">
                                <?php if ($evalScore !== null): ?>
                                    <span><?= number_format($evalScore, 2) ?> <span class="text-xs font-normal text-slate-500">/ 100 คะแนน</span></span>
                                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">(<?= $evalGrade['desc'] ?>)</span>
                                <?php else: ?>
                                    <span class="text-sm font-medium text-slate-500 dark:text-slate-400">ยังไม่มีการบันทึกคะแนนการประเมินโครงการนี้</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($evalDateThai || !empty($project['evaluator_name'])): ?>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <?php if (!empty($project['evaluator_name'])): ?>
                                        <span>ผู้ประเมิน: <strong class="text-slate-700 dark:text-slate-300"><?= htmlspecialchars($project['evaluator_name']) ?></strong></span>
                                    <?php endif; ?>
                                    <?php if ($evalDateThai): ?>
                                        <span>วันที่ประเมิน: <?= $evalDateThai ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($project['evaluation_notes'])): ?>
                                <div class="text-xs text-slate-600 dark:text-slate-300 mt-2 p-2.5 rounded-xl bg-white/70 dark:bg-black/20 border border-current/10 max-w-2xl">
                                    <span class="font-semibold text-slate-700 dark:text-slate-200">ข้อเสนอแนะ:</span> <?= nl2br(htmlspecialchars($project['evaluation_notes'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (\App\Core\Auth::canManageProjects()): ?>
                        <div class="shrink-0 self-start sm:self-center">
                            <button type="button" 
                                    @click="openEvalModal()"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl transition-all shadow-xs cursor-pointer <?= $evalGrade ? 'bg-white dark:bg-[#1a1d26] hover:bg-slate-50 dark:hover:bg-white/10 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-white/15' : 'bg-emerald-600 hover:bg-emerald-700 text-white' ?>">
                                <i data-lucide="award" class="w-4 h-4"></i>
                                <span><?= $evalGrade ? 'แก้ไขผลการประเมิน' : 'ใส่คะแนนการประเมิน' ?></span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub-projects Section -->
    <div class="bg-white dark:bg-[#161922] p-4 sm:p-6 lg:p-8 rounded-2xl border border-slate-200/80 dark:border-white/[0.08] shadow-sm space-y-4 sm:space-y-6">
        
        <!-- Header & Search Toolbar (Responsive & Polished) -->
        <div class="pb-3 sm:pb-4 border-b border-slate-100 dark:border-white/[0.06] space-y-3">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 sm:gap-4">
                
                <!-- Title, Badge & Subtitle (With Action button aligned on mobile) -->
                <div class="flex items-center justify-between gap-3 w-full md:w-auto">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <i data-lucide="layers" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h2 class="text-base sm:text-lg font-bold font-heading text-slate-900 dark:text-white truncate">
                                    กิจกรรมหลัก
                                </h2>
                                <span class="text-[11px] sm:text-xs font-bold font-mono px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20 whitespace-nowrap shrink-0">
                                    <?= count($project['sub_projects']) ?> กิจกรรมหลัก
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 hidden sm:block truncate mt-0.5">
                                คลิกเพื่อดูรายละเอียดกิจกรรมย่อย งบประมาณ บันทึกปัญหา และอัปเดตความคืบหน้า
                            </p>
                        </div>
                    </div>

                    <!-- Action Button: Visible on mobile aligned with title -->
                    <?php if (\App\Core\Auth::canManageProjects()): ?>
                        <button type="button" 
                                @click="createSubModal = true; $nextTick(() => { window.safeCreateIcons && window.safeCreateIcons($el); });" 
                                class="md:hidden inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-95 rounded-xl transition-all shadow-sm shadow-emerald-600/20 whitespace-nowrap cursor-pointer shrink-0">
                            <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                            <span>เพิ่มกิจกรรมหลัก</span>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Desktop / Tablet Search & Action Button Container -->
                <div class="flex items-center gap-2.5 w-full md:w-auto">
                    <?php if (!empty($project['sub_projects'])): ?>
                        <div class="relative w-full md:w-64">
                            <input type="text" 
                                   x-model="subSearch" 
                                   @input="subPage = 1" 
                                   placeholder="ค้นหาชื่อกิจกรรมหลัก..." 
                                   class="w-full pl-8 pr-8 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#1f222e] text-slate-900 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-[#161922] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            <button type="button" 
                                    x-show="subSearch" 
                                    @click="subSearch = ''; subPage = 1;" 
                                    class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5 cursor-pointer">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Action Button: Visible on desktop side-by-side with search -->
                    <?php if (\App\Core\Auth::canManageProjects()): ?>
                        <button type="button" 
                                @click="createSubModal = true; $nextTick(() => { window.safeCreateIcons && window.safeCreateIcons($el); });" 
                                class="hidden md:inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-95 rounded-xl transition-all shadow-md shadow-emerald-600/20 whitespace-nowrap cursor-pointer shrink-0">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>เพิ่มกิจกรรมหลัก</span>
                        </button>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <?php if (empty($project['sub_projects'])): ?>
            <div class="p-8 text-center text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-[#181a20] rounded-2xl border border-slate-200 dark:border-white/[0.08]">
                ยังไม่มีกิจกรรมหลักภายใต้โครงการหลักนี้<?= \App\Core\Auth::canManageProjects() ? ' กรุณากดปุ่ม "เพิ่มกิจกรรมหลัก"' : '' ?>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[780px]">
                    <thead>
                        <tr class="border-b border-slate-200/80 dark:border-white/[0.08] text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="py-3 px-4 min-w-[240px]">ชื่อกิจกรรมหลัก</th>
                            <th class="py-3 px-4 whitespace-nowrap">งบประมาณ (บาท)</th>
                            <th class="py-3 px-4 whitespace-nowrap text-center">จำนวนครั้งกิจกรรมย่อย</th>
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
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <?= \App\Core\Helper::moneyDisplay($sub['budget'], 'table', 'left') ?>
                                </td>
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
                                        'in_progress' => 'bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-400 border-sky-200/80 dark:border-sky-500/30',
                                        'has_problem' => 'bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-400 border-rose-200/80 dark:border-rose-500/30',
                                        'cancelled' => 'bg-slate-100 dark:bg-slate-500/15 text-slate-700 dark:text-slate-400 border-slate-200/80 dark:border-slate-500/30',
                                        default => 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-400 border-indigo-200/80 dark:border-indigo-500/30'
                                    };
                                    $sLabel = match($sub['status']) {
                                        'completed' => 'เสร็จสิ้น',
                                        'in_progress' => 'กำลังดำเนินการ',
                                        'has_problem' => 'มีปัญหา',
                                        'cancelled' => 'ยกเลิก',
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
                        <tr x-show="filteredSubProjects.length === 0" style="display: none;">
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i data-lucide="search-x" class="w-8 h-8 text-slate-300 dark:text-slate-600 mb-2"></i>
                                    <span class="font-medium text-slate-600 dark:text-slate-400">ไม่พบกิจกรรมหลักที่ค้นหา</span>
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
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono" x-text="filteredSubProjects.length"></span> กิจกรรมหลัก
                    </span>
                    <div class="relative shrink-0 ml-2 border-l border-slate-200 dark:border-white/10 pl-3" x-data="{ openSubPerPage: false }" @click.outside="openSubPerPage = false">
                        <button type="button" 
                                @click="openSubPerPage = !openSubPerPage" 
                                class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-white/[0.04] hover:bg-slate-100 dark:hover:bg-white/[0.08] px-2.5 py-1 rounded-xl border border-slate-200 dark:border-white/10 hover:border-emerald-500/40 transition-all cursor-pointer shadow-2xs">
                            <span class="text-slate-400 dark:text-slate-500 font-normal text-[11px]">แสดงต่อหน้า:</span>
                            <span class="font-bold text-slate-900 dark:text-white font-mono" x-text="subPerPage === 'all' ? 'ทั้งหมด' : subPerPage"></span>
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-150 shrink-0" :class="{ 'rotate-180': openSubPerPage }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Themed Dropdown Flyout (Pops Up) -->
                        <div x-show="openSubPerPage" 
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
                                <template x-for="opt in [5, 10, 20, 'all']" :key="opt">
                                    <button type="button" 
                                            @click="setSubPerPage(opt); openSubPerPage = false" 
                                            class="w-full text-left px-2.5 py-1.5 rounded-xl text-xs flex items-center justify-between transition cursor-pointer"
                                            :class="subPerPage == opt 
                                                ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/20' 
                                                : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5'">
                                        <span x-text="opt === 'all' ? 'ทั้งหมด' : opt + ' รายการ'"></span>
                                        <svg x-show="subPerPage == opt" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination buttons -->
                <template x-if="subTotalPages > 1 && subPerPage !== 'all'">
                    <div class="flex items-center gap-1">
                        <button type="button" @click="setSubPage(1)" :disabled="subPage === 1"
                                class="p-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#181a20] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าแรก">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                            </svg>
                        </button>
                        <button type="button" @click="prevSubPage()" :disabled="subPage === 1"
                                class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#181a20] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span class="hidden sm:inline font-sans">ก่อนหน้า</span>
                        </button>
                        <div class="flex items-center gap-1 px-1">
                            <template x-for="(p, idx) in subVisiblePages" :key="idx">
                                <button type="button" 
                                        @click="setSubPage(p)"
                                        :disabled="p === '...'"
                                        :class="p === subPage ? 'bg-emerald-600 text-white font-bold shadow-sm shadow-emerald-600/30 border border-emerald-600' : (p === '...' ? 'text-slate-400 cursor-default' : 'bg-white dark:bg-[#181a20] border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5')"
                                        class="min-w-[32px] h-8 px-2 rounded-lg text-xs font-mono font-semibold transition cursor-pointer flex items-center justify-center"
                                        x-text="p">
                                </button>
                            </template>
                        </div>
                        <button type="button" @click="nextSubPage()" :disabled="subPage === subTotalPages"
                                class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#181a20] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer flex items-center gap-1">
                            <span class="hidden sm:inline font-sans">ถัดไป</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        <button type="button" @click="setSubPage(subTotalPages)" :disabled="subPage === subTotalPages"
                                class="p-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#181a20] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="หน้าสุดท้าย">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal: Create Sub-project -->
    <?php if (\App\Core\Auth::canManageProjects()): ?>
    <template x-teleport="body">
        <div x-show="createSubModal" x-cloak data-teleport-modal="true" style="display: none;" @click.self="createSubModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto modal-backdrop-smooth">
            <div class="bg-white dark:bg-[#161922] w-full max-w-3xl rounded-2xl shadow-2xl border border-slate-200 dark:border-white/10 overflow-hidden max-h-[90vh] flex flex-col modal-box-smooth transform-gpu">
                <div class="p-6 border-b border-slate-100 dark:border-white/[0.08] flex items-center justify-between flex-shrink-0">
                    <div>
                        <h3 class="text-lg font-bold font-heading text-slate-900 dark:text-white">เพิ่มกิจกรรมหลัก</h3>
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
                            window.notify.warning('กรุณาเลือกวันที่เริ่มต้น');
                            $event.preventDefault();
                            return false;
                        }
                        if (!end) {
                            window.notify.warning('กรุณาเลือกวันที่สิ้นสุด');
                            $event.preventDefault();
                            return false;
                        }
                        if (pStart && start < pStart) {
                            window.notify.warning('วันที่เริ่มต้นของกิจกรรมหลักต้องเท่ากับหรือมากกว่าวันที่เริ่มต้นของโครงการหลัก (<?= $parentStartThai ?>)');
                            $event.preventDefault();
                            return false;
                        }
                        if (pEnd && end > pEnd) {
                            window.notify.warning('วันที่สิ้นสุดของกิจกรรมหลักต้องไม่เกินวันที่สิ้นสุดของโครงการหลัก (<?= $parentEndThai ?>)');
                            $event.preventDefault();
                            return false;
                        }
                        if (start > end) {
                            window.notify.warning('วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุดของกิจกรรมหลัก');
                            $event.preventDefault();
                            return false;
                        }
                        const maxBudget = <?= (float)$remainingParentBudget ?>;
                        const budgetVal = parseFloat($el.querySelector('input[name=budget]')?.value || 0);
                        if (budgetVal > maxBudget) {
                            window.notify.warning('งบประมาณกิจกรรมหลัก (' + budgetVal.toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท) ต้องไม่เกินงบประมาณคงเหลือของโครงการหลักที่จัดสรรได้ (' + maxBudget.toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท)');
                            $event.preventDefault();
                            return false;
                        }
                      "
                      class="p-6 space-y-4 overflow-y-auto flex-1">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="parent_id" value="<?= $project['id'] ?>">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">ชื่อกิจกรรมหลัก <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="ระบุชื่อกิจกรรมหลัก..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">ผู้รับผิดชอบกิจกรรมหลัก <span class="text-rose-500">*</span></label>
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
                                    * กิจกรรมหลักต้องเริ่มต้นตั้งแต่วันที่ <?= $parentStartThai ?> เป็นต้นไป และต้องสิ้นสุดไม่เกินวันที่ <?= $parentEndThai ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <?php \App\Core\View::component('thai-datepicker', [
                                'name' => 'start_date',
                                'label' => 'วันที่เริ่มต้น',
                                'required' => true,
                                'placement' => 'top',
                                'align' => 'left',
                            ]); ?>
                        </div>
                        <div>
                            <?php \App\Core\View::component('thai-datepicker', [
                                'name' => 'end_date',
                                'label' => 'วันที่สิ้นสุด',
                                'required' => true,
                                'placement' => 'top',
                                'align' => 'right',
                            ]); ?>
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
                                <div class="relative" x-data="{
                                    open: false,
                                    mode: 'auto',
                                    modes: {
                                        'auto': { label: 'คำนวณอัตโนมัติ (AUTO)', desc: 'คิด % ตามผลสำเร็จของกิจกรรมย่อย' },
                                        'manual': { label: 'ระบุเอง (MANUAL)', desc: 'ผู้ดูแลระบุ % ตามดุลยพินิจ' }
                                    },
                                    select(val) {
                                        this.mode = val;
                                        this.open = false;
                                    }
                                }" @click.outside="open = false">
                                    <input type="hidden" name="progress_mode" :value="mode">
                                    <button type="button" 
                                            @click="open = !open" 
                                            class="w-full h-10 px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white flex items-center justify-between focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all cursor-pointer text-left shadow-2xs">
                                        <span class="truncate font-medium text-xs sm:text-sm" x-text="modes[mode]?.label || 'เลือกโหมด'"></span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0 ml-1.5" :class="{ 'rotate-180 text-emerald-600 dark:text-emerald-400': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open" 
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute z-50 mt-1.5 w-full bg-white dark:bg-[#1f222e] rounded-2xl shadow-xl border border-slate-200 dark:border-white/10 p-1.5" 
                                         style="display: none;">
                                        <div class="space-y-1">
                                            <template x-for="(info, key) in modes" :key="key">
                                                <div @click="select(key)" 
                                                     class="px-3 py-2 rounded-xl text-xs cursor-pointer flex items-center justify-between transition-colors"
                                                     :class="mode === key ? 'bg-emerald-50/80 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04]'">
                                                    <div>
                                                        <div x-text="info.label"></div>
                                                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-normal" x-text="info.desc"></div>
                                                    </div>
                                                    <svg x-show="mode === key" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
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
                            บันทึกกิจกรรมหลัก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
    <?php endif; ?>

    <!-- Modal: Edit Project -->
    <?php if (\App\Core\Auth::canManageProjects()): ?>
    <template x-teleport="body">
        <div x-show="editModal" x-cloak data-teleport-modal="true" style="display: none;" @click.self="editModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto modal-backdrop-smooth">
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

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                รหัสโครงการหลัก
                            </label>
                            <input type="text" name="project_code" value="<?= htmlspecialchars($project['project_code'] ?? '') ?>" placeholder="เช่น PRJ-2569-001" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white font-mono focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                ชื่อโครงการหลัก <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" value="<?= htmlspecialchars($project['name'] ?? '') ?>" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
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
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                สถานะโครงการ <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative" x-data="{
                                open: false,
                                status: '<?= htmlspecialchars($project['status'] ?? 'in_progress') ?>',
                                statuses: {
                                    'not_started': {
                                        label: 'ยังไม่เริ่ม',
                                        badge: 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border-indigo-200/60 dark:border-indigo-800/40',
                                        dot: 'bg-indigo-500'
                                    },
                                    'in_progress': {
                                        label: 'กำลังดำเนินการ',
                                        badge: 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300 border-sky-200/60 dark:border-sky-800/40',
                                        dot: 'bg-sky-500'
                                    },
                                    'completed': {
                                        label: 'เสร็จสิ้น',
                                        badge: 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800/40',
                                        dot: 'bg-emerald-500'
                                    },
                                    'has_problem': {
                                        label: 'มีปัญหา',
                                        badge: 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border-rose-200/60 dark:border-rose-800/40',
                                        dot: 'bg-rose-500'
                                    },
                                    'cancelled': {
                                        label: 'ยกเลิก',
                                        badge: 'bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300 border-slate-200/80 dark:border-white/10',
                                        dot: 'bg-slate-400'
                                    }
                                },
                                select(val) {
                                    this.status = val;
                                    this.open = false;
                                }
                            }" @click.outside="open = false">
                                <input type="hidden" name="status" :value="status" required>
                                <button type="button" 
                                        @click="open = !open" 
                                        class="w-full h-10 px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white flex items-center justify-between focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all cursor-pointer text-left shadow-2xs">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" :class="statuses[status]?.dot || 'bg-slate-400'"></span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border whitespace-nowrap"
                                              :class="statuses[status]?.badge || 'bg-slate-100 text-slate-700'"
                                              x-text="statuses[status]?.label || 'เลือกสถานะ'"></span>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0 ml-1.5" :class="{ 'rotate-180 text-emerald-600 dark:text-emerald-400': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <!-- Custom Floating Dropdown Menu -->
                                <div x-show="open" 
                                     x-cloak
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute z-50 mt-1.5 w-full bg-white dark:bg-[#1f222e] rounded-2xl shadow-xl border border-slate-200 dark:border-white/10 p-1.5" 
                                     style="display: none;">
                                    <div class="space-y-1">
                                        <template x-for="(info, key) in statuses" :key="key">
                                            <div @click="select(key)" 
                                                 class="px-3 py-2 rounded-xl text-xs sm:text-sm cursor-pointer flex items-center justify-between transition-colors group"
                                                 :class="status === key ? 'bg-slate-100/80 dark:bg-white/[0.08] font-bold text-slate-900 dark:text-white' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04]'">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" :class="info.dot"></span>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border whitespace-nowrap" :class="info.badge" x-text="info.label"></span>
                                                </div>
                                                <svg x-show="status === key" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            ประเภทโครงการ <span class="text-rose-500">*</span>
                        </label>
                        <?php 
                            $currentCatId = (string)($project['category_id'] ?? '');
                            $currentCatName = '-- เลือกประเภทโครงการ --';
                            if (!empty($categories)) {
                                foreach ($categories as $cat) {
                                    if ((string)$cat['id'] === $currentCatId) {
                                        $currentCatName = $cat['name'];
                                        break;
                                    }
                                }
                                if ($currentCatName === '-- เลือกประเภทโครงการ --' && !empty($categories)) {
                                    $currentCatId = (string)$categories[0]['id'];
                                    $currentCatName = $categories[0]['name'];
                                }
                            }
                        ?>
                        <div class="relative" x-data="{
                            open: false,
                            val: '<?= $currentCatId ?>',
                            label: '<?= htmlspecialchars(addslashes($currentCatName)) ?>',
                            select(id, name) {
                                this.val = id;
                                this.label = name;
                                this.open = false;
                            }
                        }" @click.outside="open = false">
                            <input type="hidden" name="category_id" :value="val" required>
                            <button type="button" 
                                    @click="open = !open" 
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white flex items-center justify-between focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors cursor-pointer text-left">
                                <span x-text="label" :class="{ 'text-slate-400 dark:text-slate-500 font-normal': !val, 'text-slate-900 dark:text-white font-medium': val }" class="truncate pr-2"></span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute z-50 mt-1.5 w-full bg-white dark:bg-[#1f222e] rounded-xl shadow-xl border border-slate-200 dark:border-white/10 py-1 max-h-60 overflow-y-auto" 
                                 style="display: none;">
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <div @click="select('<?= $cat['id'] ?>', '<?= htmlspecialchars(addslashes($cat['name'])) ?>')" 
                                             class="px-3 py-2.5 text-xs sm:text-sm text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 hover:text-emerald-700 dark:hover:text-emerald-400 cursor-pointer flex items-center justify-between transition-colors border-b border-slate-100 dark:border-white/5 last:border-0"
                                             :class="{ 'bg-emerald-50/70 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-semibold': val == '<?= $cat['id'] ?>' }">
                                            <span class="leading-relaxed"><?= htmlspecialchars($cat['name']) ?></span>
                                            <svg x-show="val == '<?= $cat['id'] ?>'" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="px-3 py-2 text-xs text-slate-400">ไม่พบประเภทโครงการ</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <?php \App\Core\View::component('thai-datepicker', [
                                'name' => 'start_date',
                                'label' => 'วันที่เริ่มต้นโครงการ',
                                'value' => $project['start_date'] ?? '',
                                'required' => true,
                                'placement' => 'top',
                                'align' => 'left',
                            ]); ?>
                        </div>
                        <div>
                            <?php \App\Core\View::component('thai-datepicker', [
                                'name' => 'end_date',
                                'label' => 'วันที่สิ้นสุดโครงการ',
                                'value' => $project['end_date'] ?? '',
                                'required' => true,
                                'placement' => 'top',
                                'align' => 'right',
                            ]); ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            ผู้รับผิดชอบโครงการ <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="responsible_person" 
                               value="<?= htmlspecialchars(!empty($project['responsible_person']) ? $project['responsible_person'] : ($project['responsible_name'] ?? '')) ?>" 
                               required 
                               maxlength="255" 
                               placeholder="ระบุชื่อผู้รับผิดชอบ เช่น นางสาวสมใจ รักดี หรือ กองสาธารณสุข" 
                               class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
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
    <?php endif; ?>

    <!-- Modal: Evaluate Project -->
    <?php if (\App\Core\Auth::canManageProjects()): ?>
    <template x-teleport="body">
        <div x-show="evalModal" x-cloak data-teleport-modal="true" style="display: none;" @click.self="evalModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto modal-backdrop-smooth">
            <div class="bg-white dark:bg-[#161922] w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200 dark:border-white/10 overflow-hidden flex flex-col modal-box-smooth transform-gpu">
                <div class="p-6 border-b border-slate-100 dark:border-white/[0.08] flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <i data-lucide="award" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-bold font-heading text-slate-900 dark:text-white">ประเมินผลโครงการหลัก</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate max-w-xs sm:max-w-sm"><?= htmlspecialchars($project['name']) ?></p>
                        </div>
                    </div>
                    <button type="button" @click.stop="evalModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                        <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                    </button>
                </div>

                <form action="<?= \App\Core\Router::url("/projects/{$project['id']}/evaluate") ?>" method="POST" class="p-6 space-y-5">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                    <!-- Unified Assessment Card (Grade Badge, Centered Score Input, Full-Width Slider & Presets) -->
                    <div class="p-5 rounded-2xl border-2 transition-all duration-300 space-y-4" :class="currentGrade.cardBorder + ' ' + currentGrade.cardBg">
                        
                        <!-- Header Row: Grade Letter + Criteria (Left) & Prominent Score Input (Right) -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b transition-colors" :class="currentGrade.dividerBorder">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-black text-white shadow-md transition-all shrink-0" :class="currentGrade.bar">
                                    <span x-text="currentGrade.grade"></span>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">ผลการประเมินโครงการ</span>
                                    <div class="text-base sm:text-lg font-bold truncate" :class="currentGrade.text" x-text="currentGrade.label"></div>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">
                                        เกณฑ์: 90-100 <strong class="text-emerald-600 dark:text-emerald-400">A+</strong> | 80-89 <strong class="text-sky-600 dark:text-sky-400">A</strong> | 70-79 <strong class="text-indigo-600 dark:text-indigo-400">B</strong> | 60-69 <strong class="text-amber-600 dark:text-amber-400">C</strong> | &lt;60 <strong class="text-rose-600 dark:text-rose-400">D</strong>
                                    </span>
                                </div>
                            </div>

                            <!-- Standard & Clean Score Input Box -->
                            <div class="shrink-0">
                                <label for="evaluation-score-input" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1 sm:text-right cursor-pointer">
                                    คะแนนที่ได้รับ (0 – 100) <span class="text-rose-500">*</span>
                                </label>
                                <div class="flex items-center rounded-xl border-2 border-slate-300 dark:border-white/20 bg-white dark:bg-[#1a1d26] shadow-xs overflow-hidden focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all h-11">
                                    <input type="number" 
                                           id="evaluation-score-input"
                                           name="evaluation_score" 
                                           min="0" 
                                           max="100" 
                                           step="0.01" 
                                           x-model="evalScore" 
                                           @input="onScoreInput($event)"
                                           placeholder="0.00"
                                           required 
                                           class="w-24 sm:w-28 h-full px-3 text-lg sm:text-xl font-bold font-mono text-center text-slate-900 dark:text-white bg-transparent border-0 focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    <span class="h-full inline-flex items-center px-3 text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-white/10 border-l border-slate-200 dark:border-white/10 select-none">
                                        คะแนน
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Full-Width Range Slider Bar -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                <span class="font-medium flex items-center gap-1.5">
                                    <i data-lucide="sliders" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>เลื่อนสเกลปรับคะแนน</span>
                                </span>
                                <span class="font-mono text-xs font-bold" :class="currentGrade.text">
                                    <span x-text="(parseFloat(evalScore) || 0).toFixed(1)"></span> / 100 คะแนน
                                </span>
                            </div>

                            <div class="py-1">
                                <input type="range" 
                                       min="0" 
                                       max="100" 
                                       step="0.5" 
                                       :value="sliderValue" 
                                       @input="onSliderChange($event)"
                                       class="w-full h-2.5 bg-slate-200 dark:bg-[#1a1d26] rounded-lg appearance-none cursor-pointer accent-emerald-600 focus:outline-none transition-all">
                            </div>

                            <!-- Clickable Tick Marks -->
                            <div class="flex justify-between text-[11px] font-mono text-slate-400 dark:text-slate-500 px-0.5 select-none">
                                <button type="button" @click="evalScore = 0" class="hover:text-slate-700 dark:hover:text-white cursor-pointer font-semibold transition">0</button>
                                <button type="button" @click="evalScore = 25" class="hover:text-slate-700 dark:hover:text-white cursor-pointer font-semibold transition">25</button>
                                <button type="button" @click="evalScore = 50" class="hover:text-slate-700 dark:hover:text-white cursor-pointer font-semibold transition">50</button>
                                <button type="button" @click="evalScore = 75" class="hover:text-slate-700 dark:hover:text-white cursor-pointer font-semibold transition">75</button>
                                <button type="button" @click="evalScore = 100" class="hover:text-slate-700 dark:hover:text-white cursor-pointer font-semibold transition">100</button>
                            </div>
                        </div>

                        <!-- Presets and Progress Sync Buttons -->
                        <div class="flex items-center justify-between gap-2 pt-3 flex-wrap border-t transition-colors" :class="currentGrade.dividerBorder">
                            <div class="flex items-center gap-1.5 flex-wrap text-xs">
                                <span class="text-[11px] font-medium text-slate-400 mr-0.5">เลือกด่วน:</span>
                                <button type="button" @click="evalScore = 95" class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition-all cursor-pointer" :class="evalScore == 95 ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-white dark:bg-white/5 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-white/10 hover:border-emerald-500'">
                                    95 (A+)
                                </button>
                                <button type="button" @click="evalScore = 85" class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition-all cursor-pointer" :class="evalScore == 85 ? 'bg-sky-600 text-white border-sky-600 shadow-xs' : 'bg-white dark:bg-white/5 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-white/10 hover:border-sky-500'">
                                    85 (A)
                                </button>
                                <button type="button" @click="evalScore = 75" class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition-all cursor-pointer" :class="evalScore == 75 ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-white dark:bg-white/5 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-white/10 hover:border-indigo-500'">
                                    75 (B)
                                </button>
                                <button type="button" @click="evalScore = 65" class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition-all cursor-pointer" :class="evalScore == 65 ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white dark:bg-white/5 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-white/10 hover:border-amber-500'">
                                    65 (C)
                                </button>
                                <button type="button" @click="evalScore = 55" class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition-all cursor-pointer" :class="evalScore == 55 ? 'bg-rose-600 text-white border-rose-600 shadow-xs' : 'bg-white dark:bg-white/5 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-white/10 hover:border-rose-500'">
                                    55 (D)
                                </button>
                            </div>

                            <button type="button" 
                                    @click="evalScore = <?= round((float)$project['progress'], 2) ?>" 
                                    class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition-all cursor-pointer flex items-center gap-1 shadow-2xs"
                                    :class="evalScore == <?= round((float)$project['progress'], 2) ?> ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-300 dark:border-emerald-500/30 hover:bg-emerald-100'">
                                <i data-lucide="sparkles" class="w-3 h-3"></i>
                                <span>ตามความก้าวหน้าโครงการ (<?= number_format((float)$project['progress'], 1) ?>%)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Notes / Comments -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            ข้อเสนอแนะ / ความคิดเห็นของผู้ประเมิน (ทางเลือก)
                        </label>
                        <textarea name="evaluation_notes" 
                                  x-model="evalNotes"
                                  rows="3" 
                                  placeholder="ระบุจุดเด่น ข้อสังเกต หรือข้อเสนอแนะเพื่อการพัฒนาโครงการ..." 
                                  class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1f222e] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"></textarea>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 border-t border-slate-100 dark:border-white/[0.08] flex items-center justify-end gap-3 flex-shrink-0">
                        <button type="button" @click="evalModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 rounded-xl transition-colors cursor-pointer">
                            ยกเลิก
                        </button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-md shadow-emerald-600/20 cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>บันทึกผลการประเมิน</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
    <?php endif; ?>
</div>



<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>
