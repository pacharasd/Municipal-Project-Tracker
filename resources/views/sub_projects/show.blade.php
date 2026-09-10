<?php
ob_start();
$title = htmlspecialchars($project['name'] ?? 'โครงการย่อย');

// Prepare JSON summaries for Activities and Disbursements pagination & search
$activitiesSummary = [];
foreach ($project['activities'] ?? [] as $act) {
    $activitiesSummary[] = [
        'id' => (int)$act['id'],
        'name' => (string)$act['name'],
        'status' => (string)($act['status'] ?? 'not_started'),
        'search_text' => mb_strtolower(($act['name'] ?? '') . ' ' . ($act['description'] ?? '') . ' ' . ($act['location'] ?? ''), 'UTF-8'),
    ];
}
$activitiesJson = json_encode($activitiesSummary, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

$disbursementsSummary = [];
foreach ($project['disbursements'] ?? [] as $disb) {
    $disbursementsSummary[] = [
        'id' => (int)$disb['id'],
        'description' => (string)$disb['description'],
        'recipient' => (string)($disb['recipient'] ?? ''),
        'search_text' => mb_strtolower(($disb['description'] ?? '') . ' ' . ($disb['recipient'] ?? ''), 'UTF-8'),
    ];
}
$disbursementsJson = json_encode($disbursementsSummary, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
?>

<div class="space-y-6" x-data="subProjectShowPage()">
    <!-- Breadcrumb & Back Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="<?= \App\Core\Router::url("/projects/{$project['parent_id']}") ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> ย้อนกลับไป: <?= htmlspecialchars($project['parent_name'] ?? 'โครงการหลัก') ?>
        </a>
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 w-full sm:w-auto">
            <?php if (\App\Core\Auth::canManageProjects()): ?>
                <!-- Upload photos/docs button -->
                <button type="button" @click="uploadModal = true" class="w-full sm:w-auto justify-center inline-flex items-center gap-1.5 px-3 py-2 sm:py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                    <i data-lucide="paperclip" class="w-3.5 h-3.5 text-slate-500 shrink-0"></i> <span class="truncate">แนบเอกสาร/รูปภาพ</span>
                </button>

                <!-- Edit Subproject button -->
                <button type="button" @click="editSubModal = true" class="w-full sm:w-auto justify-center inline-flex items-center gap-1.5 px-3 py-2 sm:py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition-colors shadow-sm">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5 shrink-0"></i> <span class="truncate">แก้ไขโครงการ</span>
                </button>

                <!-- Delete Subproject button -->
                <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/delete") ?>" method="POST" class="w-full sm:w-auto"
                      onsubmit="return confirm('ยืนยันการลบโครงการย่อย <?= htmlspecialchars(addslashes($project['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?> ? กิจกรรมและการเบิกจ่ายทั้งหมดจะถูกลบด้วย');">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <button type="submit" class="w-full sm:w-auto justify-center inline-flex items-center gap-1.5 px-3 py-2 sm:py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 transition-colors shadow-sm">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5 shrink-0"></i> <span>ลบ</span>
                    </button>
                </form>
            <?php endif; ?>

            <?php if (\App\Core\Auth::canManageProjects()): ?>
                <?php if ($project['status'] === 'has_problem'): ?>
                    <button type="button" @click="resolveModal = true" class="w-full sm:w-auto justify-center inline-flex items-center gap-1.5 px-3 py-2 sm:py-1.5 text-xs font-semibold text-emerald-800 bg-emerald-100 rounded-xl hover:bg-emerald-200 transition-colors">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0"></i> <span class="truncate">แก้ปัญหาแล้ว</span>
                    </button>
                <?php else: ?>
                    <button type="button" @click="problemModal = true" class="w-full sm:w-auto justify-center inline-flex items-center gap-1.5 px-3 py-2 sm:py-1.5 text-xs font-semibold text-rose-800 bg-rose-100 rounded-xl hover:bg-rose-200 transition-colors">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0"></i> <span class="truncate">แจ้งปัญหา</span>
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Red Problem Alert if project has issue -->
    <?php if ($project['status'] === 'has_problem'): ?>
        <div class="p-5 rounded-2xl bg-rose-50 border-2 border-rose-300 text-rose-900 shadow-sm flex items-start gap-4">
            <div class="p-2.5 rounded-xl bg-rose-100 text-rose-700 flex-shrink-0">
                <i data-lucide="alert-octagon" class="w-6 h-6"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-rose-900">โครงการนี้มีปัญหาหรืออุปสรรคในการดำเนินงาน</h3>
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-rose-200 text-rose-900">อยู่ในบัญชีเฝ้าระวัง</span>
                </div>
                <p class="text-sm text-rose-800 mt-1 font-medium leading-relaxed">
                    <b>รายละเอียดปัญหา:</b> <?= nl2br(htmlspecialchars($project['problem_description'] ?? '')) ?>
                </p>
                <?php if (\App\Core\Auth::canManageProjects()): ?>
                    <div class="mt-3 flex items-center gap-2">
                        <button type="button" @click="resolveModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-white text-rose-900 border border-rose-300 hover:bg-rose-100 transition-colors">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i> แก้ไขปัญหาเรียบร้อยแล้ว
                        </button>
                        <button type="button" @click="statusModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-rose-100 text-rose-900 hover:bg-rose-200 transition-colors">
                            <i data-lucide="sliders" class="w-3.5 h-3.5"></i> ปรับเปลี่ยนสถานะ
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Sub-project Title & Main Card -->
    <div class="bg-white dark:bg-[#181a20] p-4 sm:p-8 rounded-2xl border border-slate-200/80 dark:border-white/[0.08] shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200"><?= htmlspecialchars($project['department_name'] ?? 'ไม่ระบุหน่วยงาน') ?></span>
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">ปีงบประมาณ <?= htmlspecialchars((string)($project['fiscal_year'] ?? '-')) ?></span>
                <?php if (!empty($project['category_name'])): ?>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-50 dark:bg-purple-500/15 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-500/30" title="<?= htmlspecialchars($project['category_name']) ?>"><?= htmlspecialchars($project['category_name']) ?></span>
                <?php endif; ?>
            </div>

            <?php
            $stClass = match($project['status']) {
                'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/30',
                'in_progress' => 'bg-sky-50 text-sky-700 border-sky-300 hover:bg-sky-100 dark:bg-sky-500/10 dark:text-sky-300 dark:border-sky-500/30',
                'has_problem' => 'bg-rose-50 text-rose-700 border-rose-300 hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:border-rose-500/30',
                'cancelled' => 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:border-slate-500/30',
                default => 'bg-indigo-50 text-indigo-700 border-indigo-300 hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-300 dark:border-indigo-500/30'
            };
            $stLabel = match($project['status']) {
                'completed' => 'เสร็จสิ้นสมบูรณ์',
                'in_progress' => 'กำลังดำเนินการ',
                'has_problem' => 'มีปัญหา / อุปสรรค',
                'cancelled' => 'ยกเลิกโครงการ',
                default => 'ยังไม่เริ่ม'
            };
            $stDotColor = match($project['status']) {
                'completed' => 'bg-[#10b981]',
                'in_progress' => 'bg-[#0ea5e9]',
                'has_problem' => 'bg-[#f43f5e]',
                'cancelled' => 'bg-[#64748b]',
                default => 'bg-[#6366f1]'
            };
            ?>

            <!-- Interactive Status Button for Admin/Managers -->
            <?php if (\App\Core\Auth::canManageProjects()): ?>
                <button type="button" @click="statusModal = true" 
                        class="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-bold rounded-full border <?= $stClass ?> hover:ring-2 hover:ring-offset-1 hover:ring-indigo-400 transition-all cursor-pointer shadow-sm group"
                        title="คลิกเพื่อปรับเปลี่ยนสถานะโครงการและเปอร์เซ็นต์">
                    <span class="w-2 h-2 rounded-full <?= $stDotColor ?>"></span>
                    <span><?= $stLabel ?></span>
                    <i data-lucide="edit-3" class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100 transition-opacity"></i>
                </button>
            <?php else: ?>
                <span class="px-3.5 py-1 text-xs font-bold rounded-full border <?= $stClass ?>">
                    <?= $stLabel ?>
                </span>
            <?php endif; ?>
        </div>

        <h1 class="text-base sm:text-xl md:text-2xl font-semibold text-slate-900 dark:text-white leading-relaxed tracking-normal mt-3">
            <?= htmlspecialchars($project['name'] ?? '') ?>
        </h1>

        <div class="text-xs text-slate-500 dark:text-slate-400 mt-2 flex flex-wrap items-baseline gap-1.5 leading-relaxed">
            <span class="text-slate-400 dark:text-slate-500 font-medium">โครงการหลัก:</span>
            <a href="<?= \App\Core\Router::url("/projects/{$project['parent_id']}") ?>" class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline">
                <?= htmlspecialchars($project['parent_name'] ?? 'โครงการหลัก') ?>
            </a>
        </div>

        <!-- Progress & Status Management Widget -->
        <div class="mt-5 p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-slate-50 to-indigo-50/30 dark:from-white/[0.02] dark:to-indigo-950/20 border border-slate-200 dark:border-white/[0.08] shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 flex items-center gap-1.5">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> ความคืบหน้าของโครงการ
                    </div>
                    <div class="mt-1.5 flex flex-wrap items-baseline gap-2.5">
                        <span class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white"><?= number_format($project['progress'], 1) ?>%</span>
                        <span class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">ความสำเร็จโครงการ</span>
                        <?php 
                            $completedActs = count(array_filter($project['activities'] ?? [], fn($a) => ($a['status'] ?? '') === 'completed'));
                            $plannedActs = max(1, (int)($project['planned_activity_count'] ?? count($project['activities'] ?? [])));
                        ?>
                        <span class="text-[11px] px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 font-medium">
                            กิจกรรม: <?= $completedActs ?> / <?= $plannedActs ?> ครั้ง
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex flex-wrap items-center gap-2">
                        <span>สถานะปัจจุบัน: <b class="text-slate-800 dark:text-slate-200 font-semibold"><?= $stLabel ?></b></span>
                        <span>•</span>
                        <span>โหมด: <span class="font-medium text-emerald-700 dark:text-emerald-400">คำนวณอัตโนมัติตามความสำเร็จของกิจกรรม</span></span>
                    </div>
                </div>

                <!-- Action Buttons: Status -->
                <div class="flex flex-wrap items-center gap-2">
                    <?php if (\App\Core\Auth::canManageProjects()): ?>
                        <button type="button" @click="statusModal = true"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-600/20 transition-all cursor-pointer">
                             <i data-lucide="tag" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i> ปรับสถานะโครงการ
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Big Progress Bar -->
            <?php $pTier = \App\Services\ProgressService::getProgressTier((float)$project['progress'], $project['status'] ?? null); ?>
            <div class="w-full bg-slate-200/80 dark:bg-white/[0.08] rounded-full h-3.5 mt-4 overflow-hidden shadow-inner p-0.5">
                <div class="h-2.5 rounded-full transition-all duration-700 flex items-center justify-end pr-2 text-[10px] font-bold text-white bg-gradient-to-r <?= $pTier['gradient'] ?>" style="width: <?= min(100, (float)$project['progress']) ?>%">
                    <?= $project['progress'] > 10 ? number_format($project['progress'], 0) . '%' : '' ?>
                </div>
            </div>


        </div>

        <?php
            $totalActualParticipants = 0;
            $totalTargetParticipants = 0;
            if (!empty($project['activities'])) {
                foreach ($project['activities'] as $actItem) {
                    $totalActualParticipants += (int)($actItem['actual_participant_count'] ?? $actItem['participant_count'] ?? 0);
                    $totalTargetParticipants += (int)($actItem['target_participant_count'] ?? 0);
                }
            }
            $targetQty = (int)($project['target_quantity'] ?? 0);
            $participantRatioPct = $targetQty > 0 ? round(($totalActualParticipants / $targetQty) * 100, 1) : 0;
        ?>
        <!-- 4 Grid Project Info (2x2 on mobile, 4 columns on lg) -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mt-6 pt-6 border-t border-slate-100 dark:border-white/[0.08] text-xs sm:text-sm">
            <div class="p-3 sm:p-0 rounded-xl sm:rounded-none bg-slate-50/70 dark:bg-white/[0.02] sm:bg-transparent sm:dark:bg-transparent border border-slate-200/60 dark:border-white/[0.06] sm:border-0 flex flex-col justify-between">
                <div>
                    <div class="text-[11px] sm:text-xs text-slate-400">ผู้รับผิดชอบโครงการ</div>
                    <div class="font-semibold text-slate-800 dark:text-slate-200 mt-1 truncate" title="<?= htmlspecialchars(!empty($project['responsible_person']) ? $project['responsible_person'] : ($project['responsible_name'] ?? 'ไม่ระบุ')) ?>">
                        <?= htmlspecialchars(!empty($project['responsible_person']) ? $project['responsible_person'] : ($project['responsible_name'] ?? 'ไม่ระบุ')) ?>
                    </div>
                </div>
                <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5"><?= htmlspecialchars($project['responsible_position'] ?? '') ?></div>
            </div>
            <div class="p-3 sm:p-0 rounded-xl sm:rounded-none bg-slate-50/70 dark:bg-white/[0.02] sm:bg-transparent sm:dark:bg-transparent border border-slate-200/60 dark:border-white/[0.06] sm:border-0 flex flex-col justify-between">
                <div>
                    <div class="text-[11px] sm:text-xs text-slate-400">พื้นที่ดำเนินการ</div>
                    <div class="font-semibold text-slate-800 dark:text-slate-200 mt-1 truncate" title="<?= htmlspecialchars($project['location'] ?: 'ในเขตเทศบาล') ?>">
                        <?= htmlspecialchars($project['location'] ?: 'ในเขตเทศบาล') ?>
                    </div>
                </div>
                <div class="text-[11px] sm:text-xs text-slate-400 mt-0.5">พิกัด/สถานที่จัด</div>
            </div>
            <div class="p-3 sm:p-0 rounded-xl sm:rounded-none bg-slate-50/70 dark:bg-white/[0.02] sm:bg-transparent sm:dark:bg-transparent border border-slate-200/60 dark:border-white/[0.06] sm:border-0 flex flex-col justify-between">
                <div>
                    <div class="text-[11px] sm:text-xs text-slate-400 flex items-center justify-between">
                        <span>กลุ่มเป้าหมาย</span>
                        <?php if ($targetQty > 0 && $totalActualParticipants > 0): ?>
                            <span class="text-[9px] sm:text-[10px] font-bold px-1.5 py-0.2 rounded-md <?= $participantRatioPct >= 100 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40' : 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800/40' ?>">
                                <?= $participantRatioPct ?>%
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="font-semibold text-slate-800 dark:text-white mt-1 truncate" title="<?= htmlspecialchars($project['target_group'] ?: 'ประชาชนทั่วไป') ?>">
                        <?= htmlspecialchars($project['target_group'] ?: 'ประชาชนทั่วไป') ?>
                    </div>
                </div>
                <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1 space-y-0.5">
                    <div>เป้าหมาย: <span class="font-semibold text-slate-700 dark:text-slate-300"><?= number_format($targetQty) ?></span> คน</div>
                    <div>จริง: <span class="font-bold <?= $totalActualParticipants > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-400' ?>"><?= number_format($totalActualParticipants) ?></span> คน</div>
                </div>
            </div>
            <div class="p-3 sm:p-0 rounded-xl sm:rounded-none bg-slate-50/70 dark:bg-white/[0.02] sm:bg-transparent sm:dark:bg-transparent border border-slate-200/60 dark:border-white/[0.06] sm:border-0 flex flex-col justify-between">
                <div>
                    <div class="text-[11px] sm:text-xs text-slate-400">ระยะเวลาดำเนินการ</div>
                    <div class="font-semibold text-slate-800 dark:text-slate-200 mt-1 leading-snug">
                        <?= date('d/m/Y', strtotime($project['start_date'])) ?> - <?= date('d/m/Y', strtotime($project['end_date'])) ?>
                    </div>
                </div>
                <?php if ($project['completion_date']): ?>
                    <div class="text-[11px] sm:text-xs text-emerald-600 dark:text-emerald-400 font-medium mt-1">เสร็จสิ้น: <?= date('d/m/Y', strtotime($project['completion_date'])) ?></div>
                <?php else: ?>
                    <div class="text-[11px] sm:text-xs text-slate-400 mt-1">ตามกำหนดเวลา</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- วัตถุประสงค์ & รายละเอียดโครงการ -->
        <div class="mt-6 pt-6 border-t border-slate-100 space-y-3">
            <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/25 border border-indigo-100 dark:border-indigo-800/30">
                <div class="text-xs font-bold text-indigo-900 dark:text-indigo-300 flex items-center gap-2 mb-1.5">
                    <span class="p-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-lg">
                        <i data-lucide="target" class="w-4 h-4"></i>
                    </span>
                    <span>วัตถุประสงค์โครงการ</span>
                </div>
                <div class="text-sm leading-relaxed text-slate-700 dark:text-slate-200">
                    <?= !empty($project['objective']) ? nl2br(htmlspecialchars($project['objective'])) : '<span class="text-slate-400 dark:text-slate-500 italic">ไม่ได้ระบุวัตถุประสงค์โครงการ</span>' ?>
                </div>
            </div>

            <?php if (!empty($project['description'])): ?>
                <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-[#12141a] border border-slate-200 dark:border-white/[0.08]">
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-1.5">
                        <span class="p-1 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg border border-blue-100 dark:border-blue-900/40">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </span>
                        <span>รายละเอียด / คำอธิบายโครงการ</span>
                    </div>
                    <div class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        <?= nl2br(htmlspecialchars($project['description'] ?? '')) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($project['methodology'])): ?>
                <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-[#12141a] border border-slate-200 dark:border-white/[0.08]">
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-1.5">
                        <span class="p-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-lg border border-emerald-100 dark:border-emerald-900/40">
                            <i data-lucide="list-checks" class="w-4 h-4"></i>
                        </span>
                        <span>วิธีการดำเนินการ</span>
                    </div>
                    <div class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        <?= nl2br(htmlspecialchars($project['methodology'] ?? '')) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Activities & Budget Two Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Activities Column -->
        <div class="bg-white dark:bg-[#181a20] p-4 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-white/[0.08] shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="calendar-check" class="w-5 h-5 text-blue-600 shrink-0"></i>
                        <span class="truncate">กิจกรรมที่กำหนดในโครงการ</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5 ml-7">
                        (<?= count($project['activities'] ?? []) ?> รายการ)
                    </p>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                    <?php if (!empty($project['activities'])): ?>
                        <div class="relative flex-1 sm:w-36">
                            <input type="text" x-model="actSearch" @input="actPage = 1" placeholder="ค้นหากิจกรรม..." 
                                   class="w-full pl-7 pr-6 py-1.5 sm:py-1 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#12141a] text-slate-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            <button type="button" x-show="actSearch" @click="actSearch = ''; actPage = 1;" class="absolute right-1.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5 cursor-pointer">
                                <i data-lucide="x" class="w-3 h-3"></i>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (\App\Core\Auth::canManageProjects()): ?>
                        <button type="button" @click="activityModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-sm shrink-0 whitespace-nowrap cursor-pointer">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>เพิ่มกิจกรรม</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (empty($project['activities'])): ?>
                <div class="p-6 text-center text-xs text-slate-400 bg-slate-50 dark:bg-[#12141a] rounded-xl border border-slate-200 dark:border-white/[0.08]">
                    ยังไม่มีรายการกิจกรรมในโครงการนี้
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($project['activities'] as $act): ?>
                        <div x-show="isActVisible(<?= $act['id'] ?>)" 
                             class="p-4 rounded-xl border border-slate-200 dark:border-white/[0.08] hover:border-blue-300 dark:hover:border-blue-500/40 transition-all bg-white dark:bg-[#12141a] flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($act['name'] ?? '') ?></h4>
                                    <?php if ($act['status'] === 'completed'): ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full whitespace-nowrap bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40">เสร็จแล้ว</span>
                                    <?php elseif ($act['status'] === 'in_progress'): ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full whitespace-nowrap bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800/40">ดำเนินการอยู่</span>
                                    <?php elseif ($act['status'] === 'has_problem'): ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full whitespace-nowrap bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40">มีปัญหา</span>
                                    <?php elseif ($act['status'] === 'cancelled'): ?>
                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full whitespace-nowrap bg-slate-100 dark:bg-slate-900/40 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800/40">ยกเลิก</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full whitespace-nowrap bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/40">ยังไม่เริ่ม</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($act['description'] ?? 'ไม่มีรายละเอียด') ?></p>
                                <?php 
                                    $actTarget = (int)($act['target_participant_count'] ?? 0);
                                    $actActual = (int)($act['actual_participant_count'] ?? $act['participant_count'] ?? 0);
                                ?>
                                <div class="mt-2 text-[11px] text-slate-400 flex flex-wrap items-center gap-y-1.5 gap-x-3">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <?= date('d/m/Y', strtotime($act['activity_date'])) ?>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="banknote" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <?= number_format($act['budget'], 2) ?> บ.
                                    </span>
                                    <span class="flex items-center gap-1 text-slate-600 dark:text-slate-300 font-medium">
                                        <i data-lucide="users" class="w-3.5 h-3.5 text-indigo-500"></i>
                                        ผู้เข้าร่วม: <strong class="text-slate-800 dark:text-white"><?= number_format($actActual) ?></strong>
                                        <?php if ($actTarget > 0): ?>
                                            <span class="text-slate-400">/ <?= number_format($actTarget) ?> คน</span>
                                            <?php $actPct = round(($actActual / $actTarget) * 100); ?>
                                            <span class="px-1.5 py-0.2 text-[10px] font-bold rounded-md <?= $actPct >= 100 ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40' : 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/40' ?>">
                                                <?= $actPct ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-400">คน</span>
                                        <?php endif; ?>
                                    </span>
                                    <?php if (!empty($act['location'])): ?>
                                        <span class="flex items-center gap-1 text-slate-500">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                            <?= htmlspecialchars($act['location'] ?? '') ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 flex-shrink-0">
                                <?php if (\App\Core\Auth::canManageProjects() && $act['status'] !== 'completed'): ?>
                                    <form action="<?= \App\Core\Router::url("/activities/{$act['id']}/status") ?>" method="POST">
                                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer" title="บันทึกว่าเสร็จสิ้นแล้ว">
                                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                <?php elseif (\App\Core\Auth::canManageProjects() && $act['status'] === 'completed'): ?>
                                    <form action="<?= \App\Core\Router::url("/activities/{$act['id']}/status") ?>" method="POST"
                                          onsubmit="return confirm('ต้องการยกเลิกสถานะเสร็จสิ้น และเปลี่ยนกลับเป็นกำลังดำเนินการ ใช่หรือไม่?');">
                                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit" class="p-1.5 text-amber-500 hover:text-amber-700 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-lg transition-colors cursor-pointer" title="เปลี่ยนกลับเป็นกำลังดำเนินการ (ยกเลิกเสร็จสิ้น)">
                                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if (\App\Core\Auth::canManageProjects()): ?>
                                    <form action="<?= \App\Core\Router::url("/activities/{$act['id']}/delete") ?>" method="POST"
                                          onsubmit="return confirm('ยืนยันการลบกิจกรรม <?= htmlspecialchars(addslashes($act['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?> ?');">
                                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                        <?php $actJson = htmlspecialchars(json_encode([
                                            'id'                       => $act['id'],
                                            'name'                     => $act['name'],
                                            'description'              => $act['description'] ?? '',
                                            'activity_date'            => $act['activity_date'],
                                            'location'                 => $act['location'] ?? '',
                                            'target_participant_count' => (int)($act['target_participant_count'] ?? 0),
                                            'actual_participant_count' => (int)($act['actual_participant_count'] ?? $act['participant_count'] ?? 0),
                                            'participant_count'        => (int)($act['actual_participant_count'] ?? $act['participant_count'] ?? 0),
                                            'budget'                   => $act['budget'],
                                            'status'                   => $act['status'],
                                            'progress'                 => $act['progress'] ?? 0,
                                            'notes'                    => $act['notes'] ?? '',
                                        ]), ENT_QUOTES, 'UTF-8'); ?>
                                        <button type="button" @click="openEditAct(<?= $actJson ?>)"
                                                class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="แก้ไขกิจกรรม">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="ลบกิจกรรม">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Empty state when search returns 0 -->
                    <div x-show="filteredActivities.length === 0" class="p-6 text-center text-xs text-slate-400 bg-slate-50 dark:bg-[#12141a] rounded-xl border border-slate-200 dark:border-white/[0.08]">
                        ไม่พบกิจกรรมที่ค้นหา
                    </div>
                </div>

                <!-- Activities Pagination Footer -->
                <div x-show="filteredActivities.length > 0" class="pt-3 border-t border-slate-100 dark:border-white/[0.06] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                        <span>แสดง <strong class="text-slate-800 dark:text-white" x-text="actStartIndex"></strong>-<strong class="text-slate-800 dark:text-white" x-text="actEndIndex"></strong> จาก <strong class="text-emerald-600 dark:text-emerald-400" x-text="filteredActivities.length"></strong></span>
                        <div class="relative shrink-0 border-l border-slate-200 dark:border-white/10 pl-2" x-data="{ openActPerPage: false }" @click.outside="openActPerPage = false">
                            <button type="button" 
                                    @click="openActPerPage = !openActPerPage" 
                                    class="flex items-center gap-1 text-[11px] font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-white/[0.04] hover:bg-slate-100 dark:hover:bg-white/[0.08] px-2 py-0.5 rounded-lg border border-slate-200 dark:border-white/10 hover:border-emerald-500/40 transition-all cursor-pointer">
                                <span class="text-slate-400 font-normal">ต่อหน้า:</span>
                                <span class="font-bold text-slate-900 dark:text-white font-mono" x-text="actPerPage === 'all' ? 'ทั้งหมด' : actPerPage"></span>
                                <svg class="w-3 h-3 text-slate-400 transition-transform duration-150 shrink-0" :class="{ 'rotate-180': openActPerPage }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Themed Dropdown Flyout (Pops Up) -->
                            <div x-show="openActPerPage" 
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute left-2 bottom-full mb-1 w-28 bg-white dark:bg-[#181a20] rounded-xl shadow-xl border border-slate-200 dark:border-white/10 p-1 z-50 text-xs text-left">
                                <template x-for="opt in [5, 10, 'all']" :key="opt">
                                    <button type="button" 
                                            @click="setActPerPage(opt); openActPerPage = false" 
                                            class="w-full text-left px-2 py-1 rounded-lg text-xs flex items-center justify-between transition cursor-pointer"
                                            :class="actPerPage == opt 
                                                ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold' 
                                                : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5'">
                                        <span x-text="opt === 'all' ? 'ทั้งหมด' : opt + ' รายการ'"></span>
                                        <svg x-show="actPerPage == opt" class="w-3 h-3 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination buttons -->
                    <template x-if="actTotalPages > 1 && actPerPage !== 'all'">
                        <div class="flex items-center gap-1">
                            <button type="button" @click="prevActPage()" :disabled="actPage === 1"
                                    class="p-1 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="ก่อนหน้า">
                                <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                            </button>
                            <div class="flex items-center gap-1">
                                <template x-for="(p, idx) in actVisiblePages" :key="idx">
                                    <button type="button" 
                                            @click="setActPage(p)"
                                            :disabled="p === '...'"
                                            :class="p === actPage ? 'bg-emerald-600 text-white font-bold shadow-sm shadow-emerald-600/30 border-emerald-600' : (p === '...' ? 'text-slate-400 cursor-default' : 'bg-white dark:bg-[#12141a] border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5')"
                                            class="min-w-[26px] h-6 px-1.5 rounded text-[11px] font-mono font-semibold transition cursor-pointer flex items-center justify-center"
                                            x-text="p">
                                    </button>
                                </template>
                            </div>
                            <button type="button" @click="nextActPage()" :disabled="actPage === actTotalPages"
                                    class="p-1 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="ถัดไป">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </template>
                </div>
            <?php endif; ?>
        </div>

        <!-- Budget Column -->
        <div class="bg-white dark:bg-[#181a20] p-4 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-white/[0.08] shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="wallet" class="w-5 h-5 text-purple-600 shrink-0"></i>
                        <span class="truncate">การเบิกจ่ายงบประมาณ</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5 ml-7">
                        (<?= number_format($project['disbursed_amount'], 2) ?> / <?= number_format($project['budget'], 2) ?> บาท)
                    </p>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                    <?php if (!empty($project['disbursements'])): ?>
                        <div class="relative flex-1 sm:w-36">
                            <input type="text" x-model="disbSearch" @input="disbPage = 1" placeholder="ค้นหารายการ..." 
                                   class="w-full pl-7 pr-6 py-1.5 sm:py-1 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#12141a] text-slate-900 dark:text-white focus:ring-1 focus:ring-purple-500 focus:outline-none">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            <button type="button" x-show="disbSearch" @click="disbSearch = ''; disbPage = 1;" class="absolute right-1.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5 cursor-pointer">
                                <i data-lucide="x" class="w-3 h-3"></i>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (\App\Core\Auth::canManageProjects()): ?>
                        <button type="button" @click="disburseModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-xl transition-colors shadow-sm shrink-0 whitespace-nowrap cursor-pointer">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>เบิกจ่ายงบ</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Budget bar -->
            <?php 
            $pctDisb = $project['budget'] > 0 ? round(($project['disbursed_amount'] / $project['budget']) * 100, 1) : 0;
            ?>
            <div class="p-4 rounded-xl bg-purple-50/60 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-800/30">
                <div class="flex items-center justify-between text-xs font-semibold text-purple-900 dark:text-purple-300 mb-1.5">
                    <span>เบิกจ่ายไปแล้ว <strong class="font-bold text-purple-700 dark:text-purple-200"><?= $pctDisb ?>%</strong></span>
                    <span>คงเหลือ <strong class="font-bold text-slate-800 dark:text-slate-200"><?= number_format($project['budget'] - $project['disbursed_amount'], 2) ?></strong> บาท</span>
                </div>
                <div class="w-full bg-purple-100 dark:bg-white/[0.08] rounded-full h-2 overflow-hidden">
                    <div class="bg-purple-600 dark:bg-purple-500 h-2 rounded-full transition-all" style="width: <?= min(100, $pctDisb) ?>%"></div>
                </div>
            </div>

            <!-- Disbursement history list -->
            <?php if (empty($project['disbursements'])): ?>
                <div class="p-6 text-center text-xs text-slate-400 bg-slate-50 rounded-xl border border-slate-200">
                    ยังไม่มีประวัติการเบิกจ่ายในโครงการนี้
                </div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($project['disbursements'] as $disb): ?>
                        <div x-show="isDisbVisible(<?= $disb['id'] ?>)" 
                             class="p-3.5 rounded-xl border border-slate-100 dark:border-white/[0.06] bg-slate-50/80 dark:bg-white/[0.03] flex items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($disb['description'] ?? '') ?></div>
                                <div class="text-slate-400 text-[11px] mt-0.5">
                                    ผู้รับ: <?= htmlspecialchars($disb['recipient'] ?? 'ไม่ระบุ') ?> | วันที่: <?= date('d/m/Y', strtotime($disb['disbursement_date'])) ?>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-sm font-bold text-purple-600 dark:text-purple-400"><?= number_format($disb['amount'], 2) ?> บ.</span>
                                <?php if (!empty($disb['evidence_file'])): ?>
                                    <a href="<?= \App\Core\Router::url('/uploads/' . $disb['evidence_file']) ?>" target="_blank"
                                       class="p-1 text-slate-400 hover:text-blue-600 rounded transition" title="ดูหลักฐานการเบิกจ่าย">
                                        <i data-lucide="paperclip" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (\App\Core\Auth::isAdmin()): ?>
                                    <form action="<?= \App\Core\Router::url("/budgets/disbursements/{$disb['id']}/delete") ?>" method="POST"
                                          onsubmit="return confirm('ยืนยันยกเลิกรายการเบิกจ่ายจำนวน <?= number_format($disb['amount'], 2) ?> บาท? (ยอดเงินจะคืนกลับเข้างบโครงการ)');">
                                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                        <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 rounded transition" title="ยกเลิกการเบิกจ่าย">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Empty state when search returns 0 -->
                    <div x-show="filteredDisbursements.length === 0" class="p-6 text-center text-xs text-slate-400 bg-slate-50 dark:bg-[#12141a] rounded-xl border border-slate-200 dark:border-white/[0.08]">
                        ไม่พบรายการเบิกจ่ายที่ค้นหา
                    </div>
                </div>

                <!-- Disbursements Pagination Footer -->
                <div x-show="filteredDisbursements.length > 0" class="pt-3 border-t border-slate-100 dark:border-white/[0.06] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                        <span>แสดง <strong class="text-slate-800 dark:text-white" x-text="disbStartIndex"></strong>-<strong class="text-slate-800 dark:text-white" x-text="disbEndIndex"></strong> จาก <strong class="text-emerald-600 dark:text-emerald-400" x-text="filteredDisbursements.length"></strong></span>
                        <div class="relative shrink-0 border-l border-slate-200 dark:border-white/10 pl-2" x-data="{ openDisbPerPage: false }" @click.outside="openDisbPerPage = false">
                            <button type="button" 
                                    @click="openDisbPerPage = !openDisbPerPage" 
                                    class="flex items-center gap-1 text-[11px] font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-white/[0.04] hover:bg-slate-100 dark:hover:bg-white/[0.08] px-2 py-0.5 rounded-lg border border-slate-200 dark:border-white/10 hover:border-emerald-500/40 transition-all cursor-pointer">
                                <span class="text-slate-400 font-normal">ต่อหน้า:</span>
                                <span class="font-bold text-slate-900 dark:text-white font-mono" x-text="disbPerPage === 'all' ? 'ทั้งหมด' : disbPerPage"></span>
                                <svg class="w-3 h-3 text-slate-400 transition-transform duration-150 shrink-0" :class="{ 'rotate-180': openDisbPerPage }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Themed Dropdown Flyout (Pops Up) -->
                            <div x-show="openDisbPerPage" 
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute left-2 bottom-full mb-1 w-28 bg-white dark:bg-[#181a20] rounded-xl shadow-xl border border-slate-200 dark:border-white/10 p-1 z-50 text-xs text-left">
                                <template x-for="opt in [5, 10, 'all']" :key="opt">
                                    <button type="button" 
                                            @click="setDisbPerPage(opt); openDisbPerPage = false" 
                                            class="w-full text-left px-2 py-1 rounded-lg text-xs flex items-center justify-between transition cursor-pointer"
                                            :class="disbPerPage == opt 
                                                ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold' 
                                                : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5'">
                                        <span x-text="opt === 'all' ? 'ทั้งหมด' : opt + ' รายการ'"></span>
                                        <svg x-show="disbPerPage == opt" class="w-3 h-3 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination buttons -->
                    <template x-if="disbTotalPages > 1 && disbPerPage !== 'all'">
                        <div class="flex items-center gap-1">
                            <button type="button" @click="prevDisbPage()" :disabled="disbPage === 1"
                                    class="p-1 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="ก่อนหน้า">
                                <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                            </button>
                            <div class="flex items-center gap-1">
                                <template x-for="(p, idx) in disbVisiblePages" :key="idx">
                                    <button type="button" 
                                            @click="setDisbPage(p)"
                                            :disabled="p === '...'"
                                            :class="p === disbPage ? 'bg-emerald-600 text-white font-bold shadow-sm shadow-emerald-600/30 border-emerald-600' : (p === '...' ? 'text-slate-400 cursor-default' : 'bg-white dark:bg-[#12141a] border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5')"
                                            class="min-w-[26px] h-6 px-1.5 rounded text-[11px] font-mono font-semibold transition cursor-pointer flex items-center justify-center"
                                            x-text="p">
                                    </button>
                                </template>
                            </div>
                            <button type="button" @click="nextDisbPage()" :disabled="disbPage === disbTotalPages"
                                    class="p-1 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer" title="ถัดไป">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </template>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Attachments & Media Gallery Section -->
    <div class="bg-white dark:bg-[#181a20] p-4 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-white/[0.08] shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="image" class="w-5 h-5 text-emerald-600"></i>
                    เอกสารแนบและภาพถ่ายกิจกรรม (<?= count($project['attachments'] ?? []) ?> รายการ)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">ภาพถ่ายการดำเนินกิจกรรม เอกสารอนุมัติ และหลักฐานเชิงประจักษ์</p>
            </div>
            <?php if (\App\Core\Auth::canManageProjects()): ?>
                <button type="button" @click="uploadModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
                    <i data-lucide="upload" class="w-3.5 h-3.5"></i> อัปโหลดไฟล์/รูปภาพ
                </button>
            <?php endif; ?>
        </div>

        <?php if (empty($project['attachments'])): ?>
            <div class="p-8 text-center bg-slate-50/70 dark:bg-white/[0.02] rounded-xl border border-dashed border-slate-300 dark:border-white/[0.1]">
                <i data-lucide="image" class="w-8 h-8 text-slate-400 mx-auto mb-2 opacity-50"></i>
                <p class="text-xs text-slate-500 font-medium">ยังไม่มีรูปภาพหรือเอกสารแนบในโครงการนี้</p>
                <?php if (\App\Core\Auth::canManageProjects()): ?>
                    <button type="button" @click="uploadModal = true" class="mt-3 text-xs font-bold text-emerald-600 hover:underline">
                        + คลิกเพื่อแนบรูปภาพหรือเอกสารแรก
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php foreach ($project['attachments'] as $att): 
                    $fileUrl = \App\Core\Router::url('/uploads/' . $att['file_path']);
                    $isImg = ($att['file_type'] === 'image');
                ?>
                    <div class="group relative rounded-xl border border-slate-200 overflow-hidden bg-white hover:shadow-md transition">
                        <?php if ($isImg): ?>
                            <a href="<?= $fileUrl ?>" target="_blank" class="block aspect-video bg-slate-100 overflow-hidden">
                                <img src="<?= $fileUrl ?>" alt="<?= htmlspecialchars($att['caption'] ?? '') ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </a>
                        <?php else: ?>
                            <a href="<?= $fileUrl ?>" target="_blank" class="block aspect-video bg-slate-50 flex flex-col items-center justify-center p-3 text-slate-600 hover:text-blue-600">
                                <i data-lucide="file-text" class="w-8 h-8 mb-1 text-slate-400"></i>
                                <span class="text-[11px] font-mono text-center truncate max-w-full"><?= htmlspecialchars($att['file_name'] ?? '') ?></span>
                            </a>
                        <?php endif; ?>
                        <div class="p-2.5 flex items-center justify-between gap-1 border-t border-slate-100 text-xs">
                            <span class="truncate text-slate-700 font-medium" title="<?= htmlspecialchars($att['caption'] ?? $att['file_name'] ?? '') ?>">
                                <?= htmlspecialchars($att['caption'] ?: ($att['file_name'] ?? '')) ?>
                            </span>
                            <?php if (\App\Core\Auth::canManageProjects()): ?>
                                <form action="<?= \App\Core\Router::url("/attachments/{$att['id']}/delete") ?>" method="POST"
                                      onsubmit="return confirm('ยืนยันการลบไฟล์แนบ <?= htmlspecialchars(addslashes($att['file_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?> ?');">
                                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                    <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition" title="ลบไฟล์">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal: Report Problem -->
    <template x-teleport="body">
        <div x-show="problemModal" x-cloak data-teleport-modal="true" style="display: none;" @click.self="problemModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto modal-backdrop-smooth">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden modal-box-smooth transform-gpu">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-rose-50/50">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-rose-100 text-rose-700">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-rose-900">แจ้งปัญหาและอุปสรรค</h3>
                            <p class="text-xs text-rose-700">โครงการจะถูกจัดเข้าสู่บัญชีเฝ้าระวัง (Watchlist) ทันที</p>
                        </div>
                    </div>
                    <button type="button" @click.stop="problemModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-rose-100 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                        <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                    </button>
                </div>

                <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/report-problem") ?>" method="POST" class="p-6 space-y-4">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">รายละเอียดปัญหาและอุปสรรคที่พบ <span class="text-rose-500">*</span></label>
                        <textarea name="problem_description" rows="4" required placeholder="อธิบายสาเหตุ ปัญหา หรือความล่าช้า เช่น ผู้รับจ้างส่งงานล่าช้า, ขาดแคลนวัสดุ..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="problemModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl">ยกเลิก</button>
                        <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm shadow-rose-600/30">
                            บันทึกแจ้งปัญหา
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal: Resolve Problem -->
    <template x-teleport="body">
        <div x-show="resolveModal" x-cloak data-teleport-modal="true" style="display: none;" @click.self="resolveModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto modal-backdrop-smooth">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden modal-box-smooth transform-gpu">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-emerald-50/50">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-emerald-100 text-emerald-700">
                            <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-emerald-900">บันทึกการแก้ไขปัญหาเรียบร้อย</h3>
                            <p class="text-xs text-emerald-700">ปลดโครงการออกจากสถานะ "มีปัญหา"</p>
                        </div>
                    </div>
                    <button type="button" @click.stop="resolveModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-emerald-100 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                        <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                    </button>
                </div>

                <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/resolve-problem") ?>" method="POST" class="p-6 space-y-4">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">บันทึกแนวทางและผลการแก้ไขปัญหา (ถ้ามี)</label>
                        <textarea name="resolution_note" rows="3" placeholder="ระบุสิ่งที่ดำเนินการแก้ไข เช่น ได้รับวัสดุอุปกรณ์ครบถ้วนแล้ว..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="resolveModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl">ยกเลิก</button>
                        <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm shadow-emerald-600/30">
                            ยืนยันการแก้ไขเสร็จสิ้น
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal: Add Activity -->
    <template x-teleport="body">
        <div x-show="activityModal" x-cloak data-teleport-modal="true" style="display: none;" @click.self="activityModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto modal-backdrop-smooth">
            <div class="bg-white dark:bg-[#181a20] w-full max-w-xl rounded-2xl shadow-xl border border-slate-200 dark:border-white/10 relative modal-box-smooth transform-gpu">
                <div class="p-6 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">เพิ่มกิจกรรมใหม่</h3>
                    <button type="button" @click.stop="activityModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                        <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                    </button>
                </div>

                <form action="<?= \App\Core\Router::url('/activities') ?>" method="POST" 
                      @submit="
                        const actDate = $el.querySelector('input[name=activity_date]')?.value;
                        if (!actDate) {
                            alert('กรุณาเลือกวันที่จัดกิจกรรม');
                            $event.preventDefault();
                            return false;
                        }
                      "
                      class="p-6 space-y-4">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">ชื่อกิจกรรม <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="เช่น อบรมให้ความรู้..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">รายละเอียดกิจกรรม</label>
                        <textarea name="description" rows="2" placeholder="รายละเอียดเนื้อหา..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <?php \App\Core\View::component('thai-datepicker', [
                                'name' => 'activity_date',
                                'label' => 'วันที่จัดกิจกรรม',
                                'required' => true,
                                'placement' => 'top',
                                'align' => 'left',
                            ]); ?>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">งบประมาณกิจกรรม (บาท)</label>
                            <input type="number" step="0.01" min="0" name="budget" value="0.00" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">สถานที่จัด</label>
                        <input type="text" name="location" placeholder="เช่น หอประชุมเทศบาล" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">จำนวนเป้าหมาย (คน)</label>
                            <input type="number" min="0" name="target_participant_count" value="0" placeholder="0" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">จำนวนผู้มาเข้าร่วมจริง (คน)</label>
                            <input type="number" min="0" name="actual_participant_count" value="0" placeholder="0" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-mono">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-white/10 flex items-center justify-end gap-3">
                        <button type="button" @click="activityModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 rounded-xl cursor-pointer">ยกเลิก</button>
                        <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm shadow-blue-600/30 cursor-pointer">บันทึกกิจกรรม</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal: Disburse Budget -->
    <template x-teleport="body">
        <div x-show="disburseModal" x-cloak data-teleport-modal="true" style="display: none;" @click.self="disburseModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto modal-backdrop-smooth">
            <div class="bg-white dark:bg-[#181a20] w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 dark:border-white/10 relative modal-box-smooth transform-gpu">
                <div class="p-6 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">บันทึกการเบิกจ่ายงบประมาณ</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">คงเหลือเบิกจ่ายได้: <?= number_format($project['budget'] - $project['disbursed_amount'], 2) ?> บาท</p>
                    </div>
                    <button type="button" @click.stop="disburseModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                        <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                    </button>
                </div>

                <form action="<?= \App\Core\Router::url('/budgets/disburse') ?>" method="POST" enctype="multipart/form-data" 
                      @submit="
                        const disDate = $el.querySelector('input[name=disbursement_date]')?.value;
                        if (!disDate) {
                            alert('กรุณาเลือกวันที่เบิกจ่าย');
                            $event.preventDefault();
                            return false;
                        }
                      "
                      class="p-6 space-y-4">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">จำนวนเงินที่เบิกจ่าย (บาท) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="1" max="<?= $project['budget'] - $project['disbursed_amount'] ?>" name="amount" required placeholder="0.00" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-900 dark:text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 font-bold">
                        <span class="text-[10px] text-slate-400 dark:text-slate-500">ห้ามเบิกจ่ายเกินงบประมาณที่ได้รับอนุมัติ (Rule #8 & #17)</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">รายการ / รายละเอียดการเบิกจ่าย <span class="text-rose-500">*</span></label>
                        <input type="text" name="description" required placeholder="เช่น ค่าวัสดุอุปกรณ์, ค่าจ้างเหมา..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-900 dark:text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <?php \App\Core\View::component('thai-datepicker', [
                                'name' => 'disbursement_date',
                                'label' => 'วันที่เบิกจ่าย',
                                'value' => date('Y-m-d'),
                                'required' => true,
                                'placement' => 'top',
                                'align' => 'left',
                            ]); ?>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">ผู้รับเงิน / บริษัทคู่สัญญา</label>
                            <input type="text" name="recipient" placeholder="เช่น หจก. การช่าง..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-900 dark:text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">เอกสารแนบหลักฐาน (PDF/JPG/PNG)</label>
                        <input type="file" name="evidence_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="disburseModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl">ยกเลิก</button>
                        <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-sm shadow-purple-600/30">บันทึกการเบิกจ่าย</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal: Manual Progress Override -->
    <template x-teleport="body">
        <div x-show="manualModal" x-cloak data-teleport-modal="true" style="display: none;" @click.self="manualModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto modal-backdrop-smooth">
            <div class="bg-white dark:bg-[#181a20] w-full max-w-sm rounded-2xl shadow-xl border border-slate-200 dark:border-white/10 overflow-hidden modal-box-smooth transform-gpu">
                <div class="p-5 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">กำหนดเปอร์เซ็นต์ความคืบหน้าเอง</h3>
                    <button type="button" @click.stop="manualModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                        <i data-lucide="x" class="w-4 h-4 pointer-events-none"></i>
                    </button>
                </div>
                <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/manual-progress") ?>" method="POST" class="p-5 space-y-4">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">ความสำเร็จ (%) ระหว่าง 0-100</label>
                        <input type="number" step="0.1" min="0" max="100" name="manual_progress" value="<?= $project['progress'] ?>" required class="w-full px-3 py-2 text-base font-bold text-center rounded-xl bg-white dark:bg-[#12141a] text-slate-900 dark:text-white border border-slate-300 dark:border-white/10 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="manualModal = false" class="px-3 py-1.5 text-xs text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 rounded-lg cursor-pointer">ยกเลิก</button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 cursor-pointer">อัปเดต</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal: Update Status and Progress Directly -->
    <template x-teleport="body">
        <div x-show="statusModal" x-cloak data-teleport-modal="true" style="display: none;" @click.self="statusModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto modal-backdrop-smooth"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white dark:bg-[#181a20] w-full max-w-lg rounded-3xl shadow-2xl border border-slate-200 dark:border-white/10 overflow-hidden space-y-5 p-6 modal-box-smooth transform-gpu">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/10">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-xl">
                            <i data-lucide="sliders" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">ปรับสถานะโครงการ</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">แอดมินและผู้ดูแลโครงการปรับสถานะการดำเนินงานของโครงการย่อย</p>
                        </div>
                    </div>
                    <button type="button" @click.stop="statusModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                        <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                    </button>
                </div>

                <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/status") ?>" method="POST" class="space-y-4">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                    <!-- 1. Status Selection -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            เลือกสถานะโครงการ <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <!-- 1. ยังไม่เริ่มดำเนินการ (not_started: Tech Indigo #6366f1) -->
                            <button type="button" @click="setStatus('not_started')"
                                    :class="selectedStatus === 'not_started' 
                                        ? 'ring-2 ring-indigo-500 dark:ring-indigo-400 bg-indigo-50/80 dark:bg-indigo-600/20 border-indigo-500 dark:border-indigo-400 shadow-sm shadow-indigo-500/20' 
                                        : 'bg-slate-50/70 dark:bg-[#12141a] border-indigo-200/60 dark:border-indigo-500/30 hover:bg-indigo-50/40 dark:hover:bg-indigo-500/10 hover:border-indigo-300 dark:hover:border-indigo-500/50'"
                                    class="p-3 rounded-2xl border text-left flex items-center justify-between transition-all cursor-pointer group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                         :class="selectedStatus === 'not_started' ? 'bg-[#6366f1] text-white shadow-sm shadow-indigo-500/30' : 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-500/25'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                            <polyline points="12 6 12 12 16 14" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold transition-colors truncate"
                                             :class="selectedStatus === 'not_started' ? 'text-indigo-950 dark:text-white' : 'text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400'">
                                            ยังไม่เริ่มดำเนินการ
                                        </div>
                                        <div class="text-[11px] font-medium transition-colors"
                                             :class="selectedStatus === 'not_started' ? 'text-indigo-700 dark:text-indigo-300' : 'text-indigo-600 dark:text-indigo-400'">
                                            ยังไม่มีการจัดกิจกรรม
                                        </div>
                                    </div>
                                </div>
                                <div x-show="selectedStatus === 'not_started'" class="w-5 h-5 rounded-full bg-[#6366f1] text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </button>

                            <!-- 2. กำลังดำเนินการ (in_progress: Electric Sky #0ea5e9) -->
                            <button type="button" @click="setStatus('in_progress')"
                                    :class="selectedStatus === 'in_progress' 
                                        ? 'ring-2 ring-sky-500 dark:ring-sky-400 bg-sky-50/80 dark:bg-sky-600/20 border-sky-500 dark:border-sky-400 shadow-sm shadow-sky-500/20' 
                                        : 'bg-slate-50/70 dark:bg-[#12141a] border-sky-200/60 dark:border-sky-500/30 hover:bg-sky-50/40 dark:hover:bg-sky-500/10 hover:border-sky-300 dark:hover:border-sky-500/50'"
                                    class="p-3 rounded-2xl border text-left flex items-center justify-between transition-all cursor-pointer group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                         :class="selectedStatus === 'in_progress' ? 'bg-[#0ea5e9] text-white shadow-sm shadow-sky-500/30' : 'bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 group-hover:bg-sky-100 dark:group-hover:bg-sky-500/25'">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <polygon points="5 3 19 12 5 21 5 3"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold transition-colors truncate"
                                             :class="selectedStatus === 'in_progress' ? 'text-sky-950 dark:text-white' : 'text-slate-800 dark:text-slate-200 group-hover:text-sky-600 dark:group-hover:text-sky-400'">
                                            กำลังดำเนินการ
                                        </div>
                                        <div class="text-[11px] font-medium transition-colors"
                                             :class="selectedStatus === 'in_progress' ? 'text-sky-700 dark:text-sky-300' : 'text-sky-600 dark:text-sky-400'">
                                            ดำเนินกิจกรรมตามแผนงาน
                                        </div>
                                    </div>
                                </div>
                                <div x-show="selectedStatus === 'in_progress'" class="w-5 h-5 rounded-full bg-[#0ea5e9] text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </button>

                            <!-- 3. เสร็จสิ้นสมบูรณ์ (completed: Luminous Emerald #10b981) -->
                            <button type="button" @click="setStatus('completed')"
                                    :class="selectedStatus === 'completed' 
                                        ? 'ring-2 ring-emerald-500 dark:ring-emerald-400 bg-emerald-50/80 dark:bg-emerald-600/20 border-emerald-500 dark:border-emerald-400 shadow-sm shadow-emerald-500/20' 
                                        : 'bg-slate-50/70 dark:bg-[#12141a] border-emerald-200/60 dark:border-emerald-500/30 hover:bg-emerald-50/40 dark:hover:bg-emerald-500/10 hover:border-emerald-300 dark:hover:border-emerald-500/50'"
                                    class="p-3 rounded-2xl border text-left flex items-center justify-between transition-all cursor-pointer group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                         :class="selectedStatus === 'completed' ? 'bg-[#10b981] text-white shadow-sm shadow-emerald-500/30' : 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-500/25'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold transition-colors truncate"
                                             :class="selectedStatus === 'completed' ? 'text-emerald-950 dark:text-white' : 'text-slate-800 dark:text-slate-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400'">
                                            เสร็จสิ้นสมบูรณ์
                                        </div>
                                        <div class="text-[11px] font-medium transition-colors"
                                             :class="selectedStatus === 'completed' ? 'text-emerald-700 dark:text-emerald-300' : 'text-emerald-600 dark:text-emerald-400'">
                                            ดำเนินกิจกรรมครบถ้วนตามแผน
                                        </div>
                                    </div>
                                </div>
                                <div x-show="selectedStatus === 'completed'" class="w-5 h-5 rounded-full bg-[#10b981] text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </button>

                            <!-- 4. มีปัญหา / อุปสรรค (has_problem: Radiant Coral #f43f5e) -->
                            <button type="button" @click="setStatus('has_problem')"
                                    :class="selectedStatus === 'has_problem' 
                                        ? 'ring-2 ring-rose-500 dark:ring-rose-400 bg-rose-50/80 dark:bg-rose-600/20 border-rose-500 dark:border-rose-400 shadow-sm shadow-rose-500/20' 
                                        : 'bg-slate-50/70 dark:bg-[#12141a] border-rose-200/60 dark:border-rose-500/30 hover:bg-rose-50/40 dark:hover:bg-rose-500/10 hover:border-rose-300 dark:hover:border-rose-500/50'"
                                    class="p-3 rounded-2xl border text-left flex items-center justify-between transition-all cursor-pointer group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                         :class="selectedStatus === 'has_problem' ? 'bg-[#f43f5e] text-white shadow-sm shadow-rose-500/30' : 'bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 group-hover:bg-rose-100 dark:group-hover:bg-rose-500/25'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold transition-colors truncate"
                                             :class="selectedStatus === 'has_problem' ? 'text-rose-950 dark:text-white' : 'text-slate-800 dark:text-slate-200 group-hover:text-rose-600 dark:group-hover:text-rose-400'">
                                            มีปัญหา / อุปสรรค
                                        </div>
                                        <div class="text-[11px] font-medium transition-colors"
                                             :class="selectedStatus === 'has_problem' ? 'text-rose-700 dark:text-rose-300' : 'text-rose-600 dark:text-rose-400'">
                                            เข้าสู่บัญชีเฝ้าระวัง
                                        </div>
                                    </div>
                                </div>
                                <div x-show="selectedStatus === 'has_problem'" class="w-5 h-5 rounded-full bg-[#f43f5e] text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </button>

                            <!-- 5. ยกเลิกโครงการ (cancelled: Deep Slate #64748b) -->
                            <button type="button" @click="setStatus('cancelled')"
                                    :class="selectedStatus === 'cancelled' 
                                        ? 'ring-2 ring-slate-500 dark:ring-slate-400 bg-slate-100/80 dark:bg-slate-500/20 border-slate-500 dark:border-slate-400 shadow-sm shadow-slate-500/20' 
                                        : 'bg-slate-50/70 dark:bg-[#12141a] border-slate-200/80 dark:border-slate-500/30 hover:bg-slate-100/80 dark:hover:bg-slate-500/10 hover:border-slate-300 dark:hover:border-slate-500/50'"
                                    class="p-3 rounded-2xl border text-left flex items-center justify-between transition-all cursor-pointer group sm:col-span-2">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                         :class="selectedStatus === 'cancelled' ? 'bg-[#64748b] text-white shadow-sm shadow-slate-500/30' : 'bg-slate-100 dark:bg-slate-500/15 text-slate-500 dark:text-slate-400 group-hover:bg-slate-200 dark:group-hover:bg-slate-500/25'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" stroke-width="2"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold transition-colors truncate"
                                             :class="selectedStatus === 'cancelled' ? 'text-slate-900 dark:text-white' : 'text-slate-800 dark:text-slate-200 group-hover:text-slate-600 dark:group-hover:text-slate-300'">
                                            ยกเลิกโครงการ
                                        </div>
                                        <div class="text-[11px] font-medium transition-colors"
                                             :class="selectedStatus === 'cancelled' ? 'text-slate-700 dark:text-slate-300' : 'text-slate-500 dark:text-slate-400'">
                                            ระงับการดำเนินงาน
                                        </div>
                                    </div>
                                </div>
                                <div x-show="selectedStatus === 'cancelled'" class="w-5 h-5 rounded-full bg-[#64748b] text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </button>
                        </div>
                        <input type="hidden" name="status" :value="selectedStatus">
                    </div>

                    <!-- Problem note if has_problem -->
                    <div x-show="selectedStatus === 'has_problem'" class="pt-2">
                        <label class="block text-xs font-semibold text-rose-600 dark:text-rose-400 mb-1">
                            ระบุรายละเอียดปัญหา / อุปสรรคที่พบ <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="problem_description" rows="2" x-model="statusNote" placeholder="เช่น ผู้รับเหมาทิ้งงาน, รอย้ายจุดเสาไฟฟ้า, รอการอนุมัติ..."
                                  class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-[#12141a] text-slate-900 dark:text-white border border-rose-300 dark:border-rose-500/40 focus:outline-none focus:ring-2 focus:ring-rose-500 placeholder-slate-400"></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-white/10 flex items-center justify-end gap-2.5">
                        <button type="button" @click="statusModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5 rounded-xl transition cursor-pointer">
                            ยกเลิก
                        </button>
                        <button type="submit" 
                                :class="selectedStatus === 'completed' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/25' : (selectedStatus === 'not_started' ? 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/25' : (selectedStatus === 'has_problem' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/25' : (selectedStatus === 'cancelled' ? 'bg-slate-600 hover:bg-slate-700 shadow-slate-600/25' : 'bg-sky-600 hover:bg-sky-700 shadow-sky-600/25')))"
                                class="px-5 py-2 text-xs font-bold text-white rounded-xl shadow-md hover:shadow-lg transition-all cursor-pointer">
                            บันทึกสถานะโครงการ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal: Edit Sub-Project -->
    <template x-teleport="body">
    <div x-show="editSubModal" x-cloak data-teleport-modal="true" style="display: none;" @click.self="editSubModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto modal-backdrop-smooth">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-2xl w-full overflow-hidden max-h-[90vh] flex flex-col modal-box-smooth transform-gpu">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-blue-100 dark:bg-blue-950/50 text-blue-600">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-slate-900 dark:text-white">แก้ไขข้อมูลโครงการย่อย</h3>
                        <p class="text-xs text-slate-400"><?= htmlspecialchars($project['name'] ?? '') ?></p>
                    </div>
                </div>
                <button type="button" @click.stop="editSubModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                    <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                </button>
            </div>

            <?php 
                $maxAllowedSubBudget = (float)($project['parent_remaining_budget'] ?? $project['parent_budget'] ?? 0); 
                $parentStartRaw = $project['parent_start_date'] ?? '';
                $parentEndRaw = $project['parent_end_date'] ?? '';
                $parentStartThai = !empty($parentStartRaw) ? date('d/m/', strtotime($parentStartRaw)) . (date('Y', strtotime($parentStartRaw)) + 543) : 'ไม่ระบุ';
                $parentEndThai = !empty($parentEndRaw) ? date('d/m/', strtotime($parentEndRaw)) . (date('Y', strtotime($parentEndRaw)) + 543) : 'ไม่ระบุ';
            ?>
            <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/update") ?>" method="POST" 
                  @submit="
                    const maxB = <?= $maxAllowedSubBudget ?>;
                    const bVal = parseFloat($el.querySelector('input[name=budget]')?.value || 0);
                    if (maxB > 0 && bVal > maxB) {
                        alert('งบประมาณโครงการย่อย (' + bVal.toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท) ต้องไม่เกินงบประมาณคงเหลือของโครงการหลักที่จัดสรรได้ (' + maxB.toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท)');
                        $event.preventDefault();
                        return false;
                    }
                    const pStart = '<?= $parentStartRaw ?>';
                    const pEnd = '<?= $parentEndRaw ?>';
                    const sStart = $el.querySelector('input[name=start_date]')?.value || '';
                    const sEnd = $el.querySelector('input[name=end_date]')?.value || '';
                    if (!sStart || !sEnd) {
                        alert('กรุณาระบุวันที่เริ่มต้นและวันที่สิ้นสุดของโครงการย่อย');
                        $event.preventDefault();
                        return false;
                    }
                    if (pStart && sStart < pStart) {
                        alert('วันที่เริ่มต้นของโครงการย่อยต้องเท่ากับหรือมากกว่าวันที่เริ่มต้นของโครงการหลัก (<?= $parentStartThai ?>)');
                        $event.preventDefault();
                        return false;
                    }
                    if (pEnd && sEnd > pEnd) {
                        alert('วันที่สิ้นสุดของโครงการย่อยต้องไม่เกินวันที่สิ้นสุดของโครงการหลัก (<?= $parentEndThai ?>)');
                        $event.preventDefault();
                        return false;
                    }
                    if (sStart > sEnd) {
                        alert('วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุดของโครงการย่อย');
                        $event.preventDefault();
                        return false;
                    }
                  "
                  class="p-6 space-y-4 overflow-y-auto">
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        ชื่อโครงการย่อย <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="<?= htmlspecialchars($project['name'] ?? '') ?>" required
                           class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div x-data="{ editBudget: '<?= (float)($project['budget'] ?? 0) ?>', maxParentBudget: <?= $maxAllowedSubBudget ?> }">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1 h-5 flex items-center justify-between">
                            <span>งบประมาณโครงการ (บาท) <span class="text-rose-500">*</span></span>
                            <?php if ($maxAllowedSubBudget > 0): ?>
                                <span class="text-[11px] text-slate-400 font-normal truncate" title="คงเหลือที่จัดสรรได้: <?= number_format($maxAllowedSubBudget, 2) ?> บาท">(คงเหลือ: <?= number_format($maxAllowedSubBudget, 2) ?> บ.)</span>
                            <?php endif; ?>
                        </label>
                        <input type="number" step="0.01" min="0" 
                               <?php if ($maxAllowedSubBudget > 0): ?>
                                   max="<?= $maxAllowedSubBudget ?>"
                                <?php endif; ?>
                               name="budget" x-model="editBudget" required
                               :class="{ 'border-rose-400 dark:border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 text-rose-600': maxParentBudget > 0 && parseFloat(editBudget || 0) > maxParentBudget }"
                               class="w-full h-10 px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                        <template x-if="maxParentBudget > 0 && parseFloat(editBudget || 0) > maxParentBudget">
                            <p class="text-[11px] text-rose-500 mt-1 font-medium flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3 h-3 inline"></i> ห้ามเกินงบประมาณคงเหลือของโครงการหลัก (จัดสรรได้สูงสุด: <?= number_format($maxAllowedSubBudget, 2) ?> บาท)
                            </p>
                        </template>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1 h-5 flex items-center">
                            จำนวนครั้งที่วางแผนกิจกรรม
                        </label>
                        <input type="number" min="1" name="planned_activity_count" value="<?= htmlspecialchars($project['planned_activity_count'] ?? 1) ?>"
                               class="w-full h-10 px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                    </div>
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <?php \App\Core\View::component('thai-datepicker', [
                            'name' => 'start_date',
                            'label' => 'วันที่เริ่มต้น',
                            'value' => $project['start_date'] ?? '',
                            'required' => true,
                            'placement' => 'top',
                            'align' => 'left',
                        ]); ?>
                    </div>
                    <div>
                        <?php \App\Core\View::component('thai-datepicker', [
                            'name' => 'end_date',
                            'label' => 'วันที่สิ้นสุด',
                            'value' => $project['end_date'] ?? '',
                            'required' => true,
                            'placement' => 'top',
                            'align' => 'right',
                        ]); ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            ผู้รับผิดชอบโครงการ <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="responsible_person" required maxlength="255"
                               value="<?= htmlspecialchars(!empty($project['responsible_person']) ? $project['responsible_person'] : ($project['responsible_name'] ?? '')) ?>"
                               placeholder="ระบุชื่อผู้รับผิดชอบ เช่น นางสาวสมใจ รักดี หรือ กองสาธารณสุข"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            พื้นที่ดำเนินการ
                        </label>
                        <input type="text" name="location" value="<?= htmlspecialchars($project['location'] ?? '') ?>" placeholder="เช่น ชุมชนวัดใหม่, ตลาดสด..."
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            กลุ่มเป้าหมาย
                        </label>
                        <input type="text" name="target_group" value="<?= htmlspecialchars($project['target_group'] ?? '') ?>" placeholder="เช่น ผู้สูงอายุ, ประชาชนทั่วไป..."
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            จำนวนกลุ่มเป้าหมาย (คน)
                        </label>
                        <input type="number" min="0" name="target_quantity" value="<?= htmlspecialchars($project['target_quantity'] ?? 0) ?>"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        วัตถุประสงค์
                    </label>
                    <textarea name="objective" rows="2" placeholder="วัตถุประสงค์ของโครงการ..."
                              class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white"><?= htmlspecialchars($project['objective'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        รายละเอียด / คำอธิบายเพิ่มเติม
                    </label>
                    <textarea name="description" rows="2" placeholder="รายละเอียด..."
                              class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" @click="editSubModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl">
                        ยกเลิก
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition">
                        บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    <!-- Modal: Edit Activity -->
    <template x-teleport="body">
    <div x-show="editActModal" x-cloak data-teleport-modal="true" style="display: none;" @click.self="editActModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto modal-backdrop-smooth">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-lg w-full overflow-hidden modal-box-smooth transform-gpu">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-blue-100 dark:bg-blue-950/50 text-blue-600">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-slate-900 dark:text-white">แก้ไขข้อมูลกิจกรรม</h3>
                        <p class="text-xs text-slate-400">อัปเดตรายละเอียด งบประมาณ และความคืบหน้ากิจกรรม</p>
                    </div>
                </div>
                <button type="button" @click.stop="editActModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                    <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                </button>
            </div>

            <form :action="'<?= \App\Core\Router::url('/activities') ?>/' + (selectedAct.id || '') + '/update'" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        ชื่อกิจกรรม <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" x-model="selectedAct.name" required
                           class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <?php \App\Core\View::component('thai-datepicker', [
                            'name' => 'activity_date',
                            'id' => 'edit_activity_date',
                            'label' => 'วันที่จัดกิจกรรม',
                            'required' => true,
                            'xModel' => 'selectedAct.activity_date',
                            'placement' => 'top',
                            'align' => 'left',
                        ]); ?>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            งบประมาณกิจกรรม (บาท) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" name="budget" x-model="selectedAct.budget" required
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        สถานที่จัดกิจกรรม
                    </label>
                    <input type="text" name="location" x-model="selectedAct.location" placeholder="เช่น อาคารอเนกประสงค์"
                           class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            จำนวนเป้าหมาย (คน)
                        </label>
                        <input type="number" min="0" name="target_participant_count" x-model="selectedAct.target_participant_count"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            จำนวนผู้มาเข้าร่วมจริง (คน)
                        </label>
                        <input type="number" min="0" name="actual_participant_count" x-model="selectedAct.actual_participant_count"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            สถานะกิจกรรม
                        </label>
                        <select name="status" x-model="selectedAct.status" @change="onActStatusChange()"
                                class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                            <option value="not_started">ยังไม่เริ่ม</option>
                            <option value="in_progress">กำลังดำเนินการ</option>
                            <option value="completed">เสร็จสิ้น</option>
                            <option value="has_problem">มีปัญหา</option>
                            <option value="cancelled">ยกเลิก</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            ความคืบหน้ากิจกรรม (%)
                        </label>
                        <input type="number" min="0" max="100" step="1" name="progress" x-model="selectedAct.progress" @input="onActProgressInput()"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        รายละเอียด / หมายเหตุ
                    </label>
                    <textarea name="notes" rows="2" x-model="selectedAct.notes" placeholder="หมายเหตุ..."
                              class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" @click="editActModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl">
                        ยกเลิก
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition">
                        บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    <!-- Modal: Upload Attachment or Photo -->
    <template x-teleport="body">
    <div x-show="uploadModal" x-cloak data-teleport-modal="true" style="display: none;" @click.self="uploadModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto modal-backdrop-smooth">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-md w-full overflow-hidden modal-box-smooth transform-gpu">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600">
                        <i data-lucide="upload" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-slate-900 dark:text-white">แนบเอกสาร / รูปภาพกิจกรรม</h3>
                        <p class="text-xs text-slate-400">อัปโหลดหลักฐานเชิงประจักษ์ประกอบโครงการ</p>
                    </div>
                </div>
                <button type="button" @click.stop="uploadModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                    <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                </button>
            </div>

            <form action="<?= \App\Core\Router::url('/attachments/upload') ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        เลือกไฟล์รูปภาพหรือเอกสาร <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" name="file" required accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                    <p class="text-[11px] text-slate-400 mt-1">รองรับ: รูปภาพ (JPG, PNG), PDF, Word, Excel (ขนาดไม่เกิน 20MB)</p>
                </div>

                <?php if (!empty($project['activities'])): ?>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            เชื่อมโยงกับกิจกรรม (ถ้ามี)
                        </label>
                        <select name="activity_id"
                                class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white">
                            <option value="">-- ภาพรวมของโครงการ --</option>
                            <?php foreach ($project['activities'] as $act): ?>
                                <option value="<?= $act['id'] ?>"><?= htmlspecialchars($act['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        คำบรรยาย / ชื่อเอกสาร
                    </label>
                    <input type="text" name="caption" placeholder="เช่น ภาพถ่ายพิธีเปิดโครงการ, สัญญาจ้าง..."
                           class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white">
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" @click="uploadModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl">
                        ยกเลิก
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition">
                        อัปโหลดไฟล์
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>
</div>

<script>
function subProjectShowPage() {
    return {
        problemModal: false, 
        resolveModal: false, 
        disburseModal: false, 
        activityModal: false, 
        manualModal: false,
        statusModal: false,
        editSubModal: false,
        editActModal: false,
        uploadModal: false,
        selectedAct: { id: '', name: '', description: '', activity_date: '', location: '', budget: '', target_participant_count: 0, actual_participant_count: 0, participant_count: 0, status: '', progress: '', notes: '' },
        openEditAct(act) {
            this.selectedAct = Object.assign({}, act);
            if (this.selectedAct.progress !== undefined && this.selectedAct.progress !== null) {
                this.selectedAct.progress = parseFloat(this.selectedAct.progress);
            }
            this.editActModal = true;
            this.$nextTick(() => { 
                window.safeCreateIcons && window.safeCreateIcons(); 
                window.dispatchEvent(new CustomEvent('set-thai-date-edit_activity_date', { detail: act.activity_date || '' }));
            });
        },
        onActStatusChange() {
            const s = this.selectedAct.status;
            let p = parseFloat(this.selectedAct.progress);
            if (isNaN(p)) p = 0;

            if (s === 'completed') {
                this.selectedAct.progress = 100;
            } else if (s === 'not_started' || s === 'cancelled') {
                this.selectedAct.progress = 0;
            } else if (s === 'in_progress' || s === 'has_problem') {
                if (p >= 100) {
                    this.selectedAct.progress = 0;
                }
            }
        },
        onActProgressInput() {
            let p = parseFloat(this.selectedAct.progress);
            if (isNaN(p)) return;

            if (p >= 100) {
                this.selectedAct.progress = 100;
                this.selectedAct.status = 'completed';
            } else if (p <= 0) {
                if (this.selectedAct.status === 'completed') {
                    this.selectedAct.status = 'not_started';
                }
            } else {
                if (this.selectedAct.status === 'completed' || this.selectedAct.status === 'not_started') {
                    this.selectedAct.status = 'in_progress';
                }
            }
        },
        selectedStatus: '<?= $project['status'] ?>',
        statusNote: <?= json_encode((string)($project['problem_description'] ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>,
        setStatus(s) {
            this.selectedStatus = s;
        },

        // Activities pagination & search
        allActivities: Object.freeze(<?= $activitiesJson ?>),
        actSearch: '',
        actPage: 1,
        actPerPage: 5,

        get filteredActivities() {
            if (!this.actSearch.trim()) return this.allActivities;
            const q = this.actSearch.toLowerCase().trim();
            return this.allActivities.filter(a => a.search_text.includes(q));
        },
        get actTotalPages() {
            if (this.actPerPage === 'all') return 1;
            const per = parseInt(this.actPerPage) || 5;
            return Math.ceil(this.filteredActivities.length / per) || 1;
        },
        get paginatedActIds() {
            if (this.actPerPage === 'all') {
                return new Set(this.filteredActivities.map(a => a.id));
            }
            const per = parseInt(this.actPerPage) || 5;
            const start = (this.actPage - 1) * per;
            return new Set(this.filteredActivities.slice(start, start + per).map(a => a.id));
        },
        isActVisible(id) {
            return this.paginatedActIds.has(id);
        },
        get actStartIndex() {
            if (this.filteredActivities.length === 0) return 0;
            if (this.actPerPage === 'all') return 1;
            const per = parseInt(this.actPerPage) || 5;
            return (this.actPage - 1) * per + 1;
        },
        get actEndIndex() {
            if (this.filteredActivities.length === 0) return 0;
            if (this.actPerPage === 'all') return this.filteredActivities.length;
            const per = parseInt(this.actPerPage) || 5;
            return Math.min(this.actPage * per, this.filteredActivities.length);
        },
        get actVisiblePages() {
            return this.getVisiblePages(this.actTotalPages, this.actPage);
        },
        setActPage(p) {
            if (p === '...' || p < 1 || p > this.actTotalPages || p === this.actPage) return;
            this.actPage = p;
            this.$nextTick(() => { window.safeCreateIcons && window.safeCreateIcons(this.$el); });
        },
        prevActPage() {
            if (this.actPage > 1) this.setActPage(this.actPage - 1);
        },
        nextActPage() {
            if (this.actPage < this.actTotalPages) this.setActPage(this.actPage + 1);
        },
        setActPerPage(val) {
            this.actPerPage = val === 'all' ? 'all' : parseInt(val);
            this.actPage = 1;
            this.$nextTick(() => { window.safeCreateIcons && window.safeCreateIcons(this.$el); });
        },

        // Disbursements pagination & search
        allDisbursements: Object.freeze(<?= $disbursementsJson ?>),
        disbSearch: '',
        disbPage: 1,
        disbPerPage: 5,

        get filteredDisbursements() {
            if (!this.disbSearch.trim()) return this.allDisbursements;
            const q = this.disbSearch.toLowerCase().trim();
            return this.allDisbursements.filter(d => d.search_text.includes(q));
        },
        get disbTotalPages() {
            if (this.disbPerPage === 'all') return 1;
            const per = parseInt(this.disbPerPage) || 5;
            return Math.ceil(this.filteredDisbursements.length / per) || 1;
        },
        get paginatedDisbIds() {
            if (this.disbPerPage === 'all') {
                return new Set(this.filteredDisbursements.map(d => d.id));
            }
            const per = parseInt(this.disbPerPage) || 5;
            const start = (this.disbPage - 1) * per;
            return new Set(this.filteredDisbursements.slice(start, start + per).map(d => d.id));
        },
        isDisbVisible(id) {
            return this.paginatedDisbIds.has(id);
        },
        get disbStartIndex() {
            if (this.filteredDisbursements.length === 0) return 0;
            if (this.disbPerPage === 'all') return 1;
            const per = parseInt(this.disbPerPage) || 5;
            return (this.disbPage - 1) * per + 1;
        },
        get disbEndIndex() {
            if (this.filteredDisbursements.length === 0) return 0;
            if (this.disbPerPage === 'all') return this.filteredDisbursements.length;
            const per = parseInt(this.disbPerPage) || 5;
            return Math.min(this.disbPage * per, this.filteredDisbursements.length);
        },
        get disbVisiblePages() {
            return this.getVisiblePages(this.disbTotalPages, this.disbPage);
        },
        setDisbPage(p) {
            if (p === '...' || p < 1 || p > this.disbTotalPages || p === this.disbPage) return;
            this.disbPage = p;
            this.$nextTick(() => { window.safeCreateIcons && window.safeCreateIcons(this.$el); });
        },
        prevDisbPage() {
            if (this.disbPage > 1) this.setDisbPage(this.disbPage - 1);
        },
        nextDisbPage() {
            if (this.disbPage < this.disbTotalPages) this.setDisbPage(this.disbPage + 1);
        },
        setDisbPerPage(val) {
            this.disbPerPage = val === 'all' ? 'all' : parseInt(val);
            this.disbPage = 1;
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
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>
