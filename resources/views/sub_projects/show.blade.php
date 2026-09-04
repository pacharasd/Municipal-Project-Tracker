<?php
ob_start();
$title = htmlspecialchars($project['name']);
?>

<div class="space-y-6" x-data="{ 
    problemModal: false, 
    resolveModal: false, 
    disburseModal: false, 
    activityModal: false, 
    manualModal: false,
    statusModal: false,
    editSubModal: false,
    editActModal: false,
    uploadModal: false,
    selectedAct: { id: '', name: '', description: '', activity_date: '', location: '', budget: '', participant_count: '', status: '', progress: '', notes: '' },
    openEditAct(act) {
        this.selectedAct = Object.assign({}, act);
        this.editActModal = true;
    },
    selectedStatus: '<?= $project['status'] ?>',
    currentProgress: <?= (float)$project['progress'] ?>,
    statusNote: '<?= addslashes($project['problem_description'] ?? '') ?>',
    setStatus(s) {
        this.selectedStatus = s;
        if (s === 'completed') {
            this.currentProgress = 100;
        } else if (s === 'not_started') {
            this.currentProgress = 0;
        } else if (s === 'in_progress' && (this.currentProgress == 0 || this.currentProgress == 100)) {
            this.currentProgress = 50;
        }
    },
    setProgress(p) {
        this.currentProgress = p;
        if (p >= 100) {
            this.selectedStatus = 'completed';
        } else if (p == 0) {
            this.selectedStatus = 'not_started';
        } else if (this.selectedStatus === 'not_started' || this.selectedStatus === 'completed') {
            this.selectedStatus = 'in_progress';
        }
    }
}">
    <!-- Breadcrumb & Back Navigation -->
    <div class="flex items-center justify-between">
        <a href="<?= \App\Core\Router::url("/projects/{$project['parent_id']}") ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> ย้อนกลับไป: <?= htmlspecialchars($project['parent_name'] ?? 'โครงการหลัก') ?>
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <?php if (\App\Core\Auth::canManageProjects()): ?>
                <!-- Upload photos/docs button -->
                <button type="button" @click="uploadModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                    <i data-lucide="paperclip" class="w-3.5 h-3.5 text-slate-500"></i> แนบเอกสาร/รูปภาพ
                </button>

                <!-- Edit Subproject button -->
                <button type="button" @click="editSubModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition-colors shadow-sm">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> แก้ไขโครงการย่อย
                </button>

                <!-- Delete Subproject button -->
                <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/delete") ?>" method="POST"
                      onsubmit="return confirm('ยืนยันการลบโครงการย่อย <?= addslashes($project['name']) ?> ? กิจกรรมและการเบิกจ่ายทั้งหมดจะถูกลบด้วย');">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 transition-colors shadow-sm">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> ลบ
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($project['status'] === 'has_problem'): ?>
                <button type="button" @click="resolveModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-800 bg-emerald-100 rounded-xl hover:bg-emerald-200 transition-colors">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i> แก้ไขปัญหาแล้ว
                </button>
            <?php else: ?>
                <button type="button" @click="problemModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-800 bg-rose-100 rounded-xl hover:bg-rose-200 transition-colors">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i> แจ้งปัญหา
                </button>
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
                    <b>รายละเอียดปัญหา:</b> <?= nl2br(htmlspecialchars($project['problem_description'])) ?>
                </p>
                <div class="mt-3 flex items-center gap-2">
                    <button type="button" @click="resolveModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-white text-rose-900 border border-rose-300 hover:bg-rose-100 transition-colors">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i> แก้ไขปัญหาเรียบร้อยแล้ว
                    </button>
                    <?php if (\App\Core\Auth::canManageProjects()): ?>
                        <button type="button" @click="statusModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-rose-100 text-rose-900 hover:bg-rose-200 transition-colors">
                            <i data-lucide="sliders" class="w-3.5 h-3.5"></i> ปรับเปลี่ยนสถานะ
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Sub-project Title & Main Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-1 text-xs font-mono font-bold rounded-lg bg-slate-100 text-slate-800 border border-slate-200"><?= htmlspecialchars($project['project_code']) ?></span>
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200"><?= htmlspecialchars($project['department_name']) ?></span>
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">ปีงบประมาณ <?= $project['fiscal_year'] ?></span>
            </div>

            <?php
            $stClass = match($project['status']) {
                'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100',
                'in_progress' => 'bg-blue-50 text-blue-700 border-blue-300 hover:bg-blue-100',
                'has_problem' => 'bg-rose-50 text-rose-700 border-rose-300 animate-pulse hover:bg-rose-100',
                'cancelled' => 'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200',
                default => 'bg-slate-100 text-slate-600 border-slate-300 hover:bg-slate-200'
            };
            $stLabel = match($project['status']) {
                'completed' => 'เสร็จสิ้นสมบูรณ์',
                'in_progress' => 'กำลังดำเนินการ',
                'has_problem' => 'มีปัญหา / อุปสรรค',
                'cancelled' => 'ยกเลิกโครงการ',
                default => 'ยังไม่เริ่มดำเนินการ'
            };
            ?>

            <!-- Interactive Status Button for Admin/Managers -->
            <?php if (\App\Core\Auth::canManageProjects()): ?>
                <button type="button" @click="statusModal = true" 
                        class="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-bold rounded-full border <?= $stClass ?> hover:ring-2 hover:ring-offset-1 hover:ring-indigo-400 transition-all cursor-pointer shadow-sm group"
                        title="คลิกเพื่อปรับเปลี่ยนสถานะโครงการและเปอร์เซ็นต์">
                    <span class="w-2 h-2 rounded-full <?= $project['status'] === 'completed' ? 'bg-emerald-500' : ($project['status'] === 'has_problem' ? 'bg-rose-500' : 'bg-blue-500') ?>"></span>
                    <span><?= $stLabel ?></span>
                    <i data-lucide="edit-3" class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100 transition-opacity"></i>
                </button>
            <?php else: ?>
                <span class="px-3.5 py-1 text-xs font-bold rounded-full border <?= $stClass ?>">
                    <?= $stLabel ?>
                </span>
            <?php endif; ?>
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-3">
            <?= htmlspecialchars($project['name']) ?>
        </h1>

        <div class="text-xs text-slate-500 mt-1 flex items-center gap-2">
            <span>โครงการหลัก:</span>
            <a href="<?= \App\Core\Router::url("/projects/{$project['parent_id']}") ?>" class="text-emerald-600 font-semibold hover:underline">
                <?= htmlspecialchars($project['parent_name']) ?>
            </a>
        </div>

        <!-- Progress & Status Management Widget -->
        <div class="mt-6 p-6 rounded-2xl bg-gradient-to-br from-slate-50 to-indigo-50/30 border border-slate-200 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-indigo-700 flex items-center gap-1.5">
                        <i data-lucide="gauge" class="w-4 h-4"></i> ความคืบหน้าของโครงการ
                    </div>
                    <div class="mt-2 flex items-baseline gap-3">
                        <span class="text-3xl sm:text-4xl font-extrabold text-slate-900"><?= number_format($project['progress'], 1) ?>%</span>
                        <span class="text-sm font-semibold text-slate-500">ความสำเร็จโครงการ</span>
                        <?php if ($project['planned_activity_count'] > 1): ?>
                            <span class="text-xs px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 font-mono font-medium">
                                กิจกรรม: <?= $project['actual_activity_count'] ?> / <?= $project['planned_activity_count'] ?> ครั้ง
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-2">
                        <span>สถานะปัจจุบัน: <b class="text-slate-800"><?= $stLabel ?></b></span>
                        <span>•</span>
                        <span>โหมด: <span class="font-medium text-indigo-700"><?= $project['progress_mode'] === 'manual' ? 'ปรับตามสถานะ / ผู้ดูแลกำหนดเอง' : 'คำนวณอัตโนมัติจากกิจกรรม' ?></span></span>
                    </div>
                </div>

                <!-- Action Buttons: Status & Progress -->
                <div class="flex flex-wrap items-center gap-2">
                    <?php if (\App\Core\Auth::canManageProjects()): ?>
                        <button type="button" @click="statusModal = true"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-600/20 transition-all">
                            <i data-lucide="sliders" class="w-4 h-4"></i> ปรับสถานะ & ความคืบหน้า
                        </button>

                        <?php if ($project['planned_activity_count'] > 1): ?>
                            <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/increment") ?>" method="POST" class="inline">
                                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                <button type="submit" 
                                        <?= $project['actual_activity_count'] >= $project['planned_activity_count'] ? 'disabled' : '' ?>
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-all shadow-sm <?= $project['actual_activity_count'] >= $project['planned_activity_count'] ? 'opacity-50 cursor-not-allowed' : '' ?>"
                                        title="+1 ครั้งกิจกรรม">
                                    <i data-lucide="plus" class="w-3.5 h-3.5 text-emerald-600"></i> +1 กิจกรรม
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Big Progress Bar -->
            <div class="w-full bg-slate-200/80 rounded-full h-3.5 mt-4 overflow-hidden shadow-inner">
                <div class="h-3.5 rounded-full transition-all duration-500 flex items-center justify-end pr-2 text-[10px] font-bold text-white <?= $project['status'] === 'has_problem' ? 'bg-rose-500' : ($project['status'] === 'completed' ? 'bg-emerald-500' : 'bg-gradient-to-r from-indigo-500 to-teal-500') ?>" style="width: <?= min(100, (float)$project['progress']) ?>%">
                    <?= $project['progress'] > 10 ? number_format($project['progress'], 0) . '%' : '' ?>
                </div>
            </div>

            <!-- Quick One-Click Progress & Status Presets -->
            <?php if (\App\Core\Auth::canManageProjects()): ?>
                <div class="mt-4 pt-3 border-t border-slate-200/60 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <span class="text-slate-500 flex items-center gap-1 font-medium">
                        <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-500"></i> ปรับด่วน:
                    </span>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/status") ?>" method="POST" class="inline">
                            <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="status" value="not_started">
                            <input type="hidden" name="progress" value="0">
                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 font-medium transition-colors shadow-xs">
                                0% (ยังไม่เริ่ม)
                            </button>
                        </form>
                        <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/status") ?>" method="POST" class="inline">
                            <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="status" value="in_progress">
                            <input type="hidden" name="progress" value="25">
                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-medium transition-colors shadow-xs">
                                25%
                            </button>
                        </form>
                        <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/status") ?>" method="POST" class="inline">
                            <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="status" value="in_progress">
                            <input type="hidden" name="progress" value="50">
                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-medium transition-colors shadow-xs">
                                50%
                            </button>
                        </form>
                        <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/status") ?>" method="POST" class="inline">
                            <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="status" value="in_progress">
                            <input type="hidden" name="progress" value="75">
                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-medium transition-colors shadow-xs">
                                75%
                            </button>
                        </form>
                        <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/status") ?>" method="POST" class="inline">
                            <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="status" value="completed">
                            <input type="hidden" name="progress" value="100">
                            <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-100 border border-emerald-300 hover:bg-emerald-200 text-emerald-800 font-bold transition-colors shadow-xs">
                                100% (เสร็จสิ้น)
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- 4 Grid Project Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-6 pt-6 border-t border-slate-100 text-sm">
            <div>
                <div class="text-xs text-slate-400">ผู้รับผิดชอบโครงการ</div>
                <div class="font-semibold text-slate-800 mt-1"><?= htmlspecialchars($project['responsible_name'] ?? 'ไม่ระบุ') ?></div>
                <div class="text-xs text-slate-500"><?= htmlspecialchars($project['responsible_position'] ?? '') ?></div>
            </div>
            <div>
                <div class="text-xs text-slate-400">พื้นที่ดำเนินการ</div>
                <div class="font-semibold text-slate-800 mt-1"><?= htmlspecialchars($project['location'] ?: 'ในเขตเทศบาล') ?></div>
            </div>
            <div>
                <div class="text-xs text-slate-400">กลุ่มเป้าหมาย</div>
                <div class="font-semibold text-slate-800 mt-1"><?= htmlspecialchars($project['target_group'] ?: 'ประชาชนทั่วไป') ?></div>
                <div class="text-xs text-slate-500">จำนวน: <?= number_format($project['target_quantity'] ?? 0) ?> คน</div>
            </div>
            <div>
                <div class="text-xs text-slate-400">ระยะเวลาดำเนินการ</div>
                <div class="font-semibold text-slate-800 mt-1"><?= date('d/m/Y', strtotime($project['start_date'])) ?> - <?= date('d/m/Y', strtotime($project['end_date'])) ?></div>
                <?php if ($project['completion_date']): ?>
                    <div class="text-xs text-emerald-600 font-medium">เสร็จสิ้นเมื่อ: <?= date('d/m/Y', strtotime($project['completion_date'])) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Activities & Budget Two Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Activities Column -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="calendar-check" class="w-5 h-5 text-blue-600"></i>
                        กิจกรรมที่กำหนดในโครงการ (<?= count($project['activities'] ?? []) ?> รายการ)
                    </h2>
                </div>
                <?php if (\App\Core\Auth::canManageProjects()): ?>
                    <button type="button" @click="activityModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> เพิ่มกิจกรรม
                    </button>
                <?php endif; ?>
            </div>

            <?php if (empty($project['activities'])): ?>
                <div class="p-6 text-center text-xs text-slate-400 bg-slate-50 rounded-xl border border-slate-200">
                    ยังไม่มีรายการกิจกรรมในโครงการนี้
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($project['activities'] as $act): ?>
                        <div class="p-4 rounded-xl border border-slate-200 hover:border-blue-300 transition-all bg-white flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-slate-900"><?= htmlspecialchars($act['name']) ?></h4>
                                    <?php if ($act['status'] === 'completed'): ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">เสร็จแล้ว</span>
                                    <?php elseif ($act['status'] === 'in_progress'): ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-200">ดำเนินการอยู่</span>
                                    <?php elseif ($act['status'] === 'has_problem'): ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200">มีปัญหา</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-slate-100 text-slate-600">ยังไม่เริ่ม</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($act['description'] ?: 'ไม่มีรายละเอียด') ?></p>
                                <div class="mt-2 text-[11px] text-slate-400 flex flex-wrap gap-3">
                                    <span>วันที่: <?= date('d/m/Y', strtotime($act['activity_date'])) ?></span>
                                    <span>งบ: <?= number_format($act['budget'], 2) ?> บ.</span>
                                    <span>ผู้เข้าร่วม: <?= number_format($act['participant_count']) ?> คน</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 flex-shrink-0">
                                <?php if (\App\Core\Auth::canManageProjects() && $act['status'] !== 'completed'): ?>
                                    <form action="<?= \App\Core\Router::url("/activities/{$act['id']}/status") ?>" method="POST">
                                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="บันทึกว่าเสร็จสิ้นแล้ว">
                                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if (\App\Core\Auth::canManageProjects()): ?>
                                    <?php $actJson = htmlspecialchars(json_encode([
                                        'id'                => $act['id'],
                                        'name'              => $act['name'],
                                        'description'       => $act['description'] ?? '',
                                        'activity_date'     => $act['activity_date'],
                                        'location'          => $act['location'] ?? '',
                                        'participant_count' => $act['participant_count'] ?? 0,
                                        'budget'            => $act['budget'],
                                        'status'            => $act['status'],
                                        'progress'          => $act['progress'] ?? 0,
                                        'notes'             => $act['notes'] ?? '',
                                    ]), ENT_QUOTES, 'UTF-8'); ?>
                                    <button type="button" @click="openEditAct(<?= $actJson ?>)"
                                            class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="แก้ไขกิจกรรม">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <form action="<?= \App\Core\Router::url("/activities/{$act['id']}/delete") ?>" method="POST"
                                          onsubmit="return confirm('ยืนยันการลบกิจกรรม <?= addslashes($act['name']) ?> ?');">
                                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="ลบกิจกรรม">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Budget & Disbursement Column -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="wallet" class="w-5 h-5 text-purple-600"></i>
                        การเบิกจ่ายงบประมาณ (<?= number_format($project['disbursed_amount'], 2) ?> / <?= number_format($project['budget'], 2) ?> บ.)
                    </h2>
                </div>
                <?php if (\App\Core\Auth::canManageProjects()): ?>
                    <button type="button" @click="disburseModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-purple-600 rounded-xl hover:bg-purple-700 transition-colors shadow-sm">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> เบิกจ่ายงบประมาณ
                    </button>
                <?php endif; ?>
            </div>

            <!-- Budget bar -->
            <?php 
            $pctDisb = $project['budget'] > 0 ? round(($project['disbursed_amount'] / $project['budget']) * 100, 1) : 0;
            ?>
            <div class="p-4 rounded-xl bg-purple-50/50 border border-purple-100">
                <div class="flex items-center justify-between text-xs font-semibold text-purple-900 mb-1.5">
                    <span>เบิกจ่ายไปแล้ว <?= $pctDisb ?>%</span>
                    <span>คงเหลือ <?= number_format($project['budget'] - $project['disbursed_amount'], 2) ?> บาท</span>
                </div>
                <div class="w-full bg-purple-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-purple-600 h-2 rounded-full transition-all" style="width: <?= min(100, $pctDisb) ?>%"></div>
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
                        <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="font-semibold text-slate-900"><?= htmlspecialchars($disb['description']) ?></div>
                                <div class="text-slate-400 text-[11px] mt-0.5">
                                    ผู้รับ: <?= htmlspecialchars($disb['recipient'] ?: 'ไม่ระบุ') ?> | วันที่: <?= date('d/m/Y', strtotime($disb['disbursement_date'])) ?>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-sm font-bold text-purple-600"><?= number_format($disb['amount'], 2) ?> บ.</span>
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
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Attachments & Media Gallery Section -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
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
            <div class="p-8 text-center bg-slate-50/70 rounded-xl border border-dashed border-slate-300">
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
                                <span class="text-[11px] font-mono text-center truncate max-w-full"><?= htmlspecialchars($att['file_name']) ?></span>
                            </a>
                        <?php endif; ?>
                        <div class="p-2.5 flex items-center justify-between gap-1 border-t border-slate-100 text-xs">
                            <span class="truncate text-slate-700 font-medium" title="<?= htmlspecialchars($att['caption'] ?? $att['file_name']) ?>">
                                <?= htmlspecialchars($att['caption'] ?: $att['file_name']) ?>
                            </span>
                            <?php if (\App\Core\Auth::canManageProjects()): ?>
                                <form action="<?= \App\Core\Router::url("/attachments/{$att['id']}/delete") ?>" method="POST"
                                      onsubmit="return confirm('ยืนยันการลบไฟล์แนบ <?= addslashes($att['file_name']) ?> ?');">
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
        <div x-show="problemModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div @click.away="problemModal = false" class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
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
                    <button type="button" @click="problemModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
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
        <div x-show="resolveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div @click.away="resolveModal = false" class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
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
                    <button type="button" @click="resolveModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
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
        <div x-show="activityModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div @click.away="activityModal = false" class="bg-white w-full max-w-xl rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">เพิ่มกิจกรรมใหม่</h3>
                    <button type="button" @click="activityModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="<?= \App\Core\Router::url('/activities') ?>" method="POST" class="p-6 space-y-4">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">ชื่อกิจกรรม <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="เช่น อบรมให้ความรู้..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">รายละเอียดกิจกรรม</label>
                        <textarea name="description" rows="2" placeholder="รายละเอียดเนื้อหา..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">วันที่จัดกิจกรรม <span class="text-rose-500">*</span></label>
                            <input type="date" name="activity_date" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">งบประมาณกิจกรรม (บาท)</label>
                            <input type="number" step="0.01" min="0" name="budget" value="0.00" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">สถานที่จัด</label>
                            <input type="text" name="location" placeholder="เช่น หอประชุมเทศบาล" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">จำนวนผู้เข้าร่วม (คน)</label>
                            <input type="number" min="0" name="participant_count" value="0" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="activityModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl">ยกเลิก</button>
                        <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm shadow-blue-600/30">บันทึกกิจกรรม</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal: Disburse Budget -->
    <template x-teleport="body">
        <div x-show="disburseModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div @click.away="disburseModal = false" class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">บันทึกการเบิกจ่ายงบประมาณ</h3>
                        <p class="text-xs text-slate-500 mt-0.5">คงเหลือเบิกจ่ายได้: <?= number_format($project['budget'] - $project['disbursed_amount'], 2) ?> บาท</p>
                    </div>
                    <button type="button" @click="disburseModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="<?= \App\Core\Router::url('/budgets/disburse') ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">จำนวนเงินที่เบิกจ่าย (บาท) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="1" max="<?= $project['budget'] - $project['disbursed_amount'] ?>" name="amount" required placeholder="0.00" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-purple-500 font-bold">
                        <span class="text-[10px] text-slate-400">ห้ามเบิกจ่ายเกินงบประมาณที่ได้รับอนุมัติ (Rule #8 & #17)</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">รายการ / รายละเอียดการเบิกจ่าย <span class="text-rose-500">*</span></label>
                        <input type="text" name="description" required placeholder="เช่น ค่าวัสดุอุปกรณ์, ค่าจ้างเหมา..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">วันที่เบิกจ่าย <span class="text-rose-500">*</span></label>
                            <input type="date" name="disbursement_date" required value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">ผู้รับเงิน / บริษัทคู่สัญญา</label>
                            <input type="text" name="recipient" placeholder="เช่น หจก. การช่าง..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
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
        <div x-show="manualModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div @click.away="manualModal = false" class="bg-white w-full max-w-sm rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900">กำหนดเปอร์เซ็นต์ความคืบหน้าเอง</h3>
                    <button type="button" @click="manualModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/manual-progress") ?>" method="POST" class="p-5 space-y-4">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">ความสำเร็จ (%) ระหว่าง 0-100</label>
                        <input type="number" step="0.1" min="0" max="100" name="manual_progress" value="<?= $project['progress'] ?>" required class="w-full px-3 py-2 text-base font-bold text-center rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="manualModal = false" class="px-3 py-1.5 text-xs text-slate-500 hover:bg-slate-100 rounded-lg">ยกเลิก</button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">อัปเดต</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal: Update Status and Progress Directly -->
    <template x-teleport="body">
        <div x-show="statusModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="statusModal = false" class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 overflow-hidden space-y-5 p-6">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 bg-indigo-100 text-indigo-600 rounded-xl">
                            <i data-lucide="sliders" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">ปรับสถานะและความคืบหน้าโครงการ</h3>
                            <p class="text-xs text-slate-500">แอดมินและผู้ดูแลโครงการปรับสถานะและเปอร์เซ็นต์ได้โดยตรง</p>
                        </div>
                    </div>
                    <button type="button" @click="statusModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/status") ?>" method="POST" class="space-y-4">
                    <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">

                    <!-- 1. Status Selection -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">
                            เลือกสถานะโครงการ <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <!-- Not Started -->
                            <button type="button" @click="setStatus('not_started')"
                                    :class="selectedStatus === 'not_started' ? 'ring-2 ring-slate-500 bg-slate-100 border-slate-400' : 'bg-white border-slate-200 hover:bg-slate-50'"
                                    class="p-2.5 rounded-xl border text-left flex items-center gap-2.5 transition-all">
                                <span class="w-3 h-3 rounded-full bg-slate-400"></span>
                                <div>
                                    <div class="text-xs font-bold text-slate-800">ยังไม่เริ่มดำเนินการ</div>
                                    <div class="text-[10px] text-slate-400">ความคืบหน้า 0%</div>
                                </div>
                            </button>

                            <!-- In Progress -->
                            <button type="button" @click="setStatus('in_progress')"
                                    :class="selectedStatus === 'in_progress' ? 'ring-2 ring-blue-500 bg-blue-50/70 border-blue-400' : 'bg-white border-slate-200 hover:bg-slate-50'"
                                    class="p-2.5 rounded-xl border text-left flex items-center gap-2.5 transition-all">
                                <span class="w-3 h-3 rounded-full bg-blue-500 animate-pulse"></span>
                                <div>
                                    <div class="text-xs font-bold text-blue-900">กำลังดำเนินการ</div>
                                    <div class="text-[10px] text-blue-500">ปรับความคืบหน้าได้อิสระ</div>
                                </div>
                            </button>

                            <!-- Completed -->
                            <button type="button" @click="setStatus('completed')"
                                    :class="selectedStatus === 'completed' ? 'ring-2 ring-emerald-500 bg-emerald-50/70 border-emerald-400' : 'bg-white border-slate-200 hover:bg-slate-50'"
                                    class="p-2.5 rounded-xl border text-left flex items-center gap-2.5 transition-all">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                <div>
                                    <div class="text-xs font-bold text-emerald-900">เสร็จสิ้นสมบูรณ์</div>
                                    <div class="text-[10px] text-emerald-600">ความคืบหน้า 100%</div>
                                </div>
                            </button>

                            <!-- Has Problem -->
                            <button type="button" @click="setStatus('has_problem')"
                                    :class="selectedStatus === 'has_problem' ? 'ring-2 ring-rose-500 bg-rose-50/70 border-rose-400' : 'bg-white border-slate-200 hover:bg-slate-50'"
                                    class="p-2.5 rounded-xl border text-left flex items-center gap-2.5 transition-all">
                                <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                                <div>
                                    <div class="text-xs font-bold text-rose-900">มีปัญหา / อุปสรรค</div>
                                    <div class="text-[10px] text-rose-500">เข้าสู่บัญชีเฝ้าระวัง</div>
                                </div>
                            </button>

                            <!-- Cancelled -->
                            <button type="button" @click="setStatus('cancelled')"
                                    :class="selectedStatus === 'cancelled' ? 'ring-2 ring-gray-600 bg-gray-100 border-gray-400' : 'bg-white border-slate-200 hover:bg-slate-50'"
                                    class="p-2.5 rounded-xl border text-left flex items-center gap-2.5 transition-all sm:col-span-2">
                                <span class="w-3 h-3 rounded-full bg-gray-600"></span>
                                <div>
                                    <div class="text-xs font-bold text-gray-800">ยกเลิกโครงการ</div>
                                    <div class="text-[10px] text-gray-500">ระงับการดำเนินงาน</div>
                                </div>
                            </button>
                        </div>
                        <input type="hidden" name="status" :value="selectedStatus">
                    </div>

                    <!-- 2. Progress Percentage (%) Slider & Number -->
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-slate-700">เปอร์เซ็นต์ความสำเร็จของโครงการ (%)</label>
                            <span class="text-lg font-extrabold text-indigo-600" x-text="`${currentProgress}%`"></span>
                        </div>

                        <!-- Range Slider -->
                        <input type="range" min="0" max="100" step="1" x-model="currentProgress" @input="setProgress(Number($event.target.value))"
                               class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">

                        <!-- Direct input and presets -->
                        <div class="flex items-center justify-between mt-3 gap-2">
                            <div class="flex flex-wrap items-center gap-1 text-xs">
                                <button type="button" @click="setProgress(0)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded font-medium text-slate-600">0%</button>
                                <button type="button" @click="setProgress(25)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded font-medium text-slate-600">25%</button>
                                <button type="button" @click="setProgress(50)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded font-medium text-slate-600">50%</button>
                                <button type="button" @click="setProgress(75)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded font-medium text-slate-600">75%</button>
                                <button type="button" @click="setProgress(100)" class="px-2 py-1 bg-emerald-100 hover:bg-emerald-200 rounded font-bold text-emerald-700">100%</button>
                            </div>
                            <div class="flex items-center gap-1">
                                <input type="number" min="0" max="100" step="0.1" name="progress" x-model="currentProgress" @input="setProgress(Number($event.target.value))" required
                                       class="w-20 px-2.5 py-1 text-sm font-bold text-center border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <span class="text-xs font-bold text-slate-600">%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Problem note if has_problem -->
                    <div x-show="selectedStatus === 'has_problem'" class="pt-2">
                        <label class="block text-xs font-semibold text-rose-700 mb-1">
                            ระบุรายละเอียดปัญหา / อุปสรรคที่พบ <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="problem_description" rows="2" x-model="statusNote" placeholder="เช่น ผู้รับเหมาทิ้งงาน, รอย้ายจุดเสาไฟฟ้า, รอการอนุมัติ..."
                                  class="w-full px-3 py-2 text-xs rounded-xl border border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="statusModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl">
                            ยกเลิก
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-600/20 transition-all">
                            บันทึกสถานะและความคืบหน้า
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal: Edit Sub-Project -->
    <template x-teleport="body">
    <div x-show="editSubModal" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-2xl w-full overflow-hidden max-h-[90vh] flex flex-col"
             @click.away="editSubModal = false">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-blue-100 dark:bg-blue-950/50 text-blue-600">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-slate-900 dark:text-white">แก้ไขข้อมูลโครงการย่อย</h3>
                        <p class="text-xs text-slate-400">รหัส: <?= htmlspecialchars($project['project_code']) ?></p>
                    </div>
                </div>
                <button type="button" @click="editSubModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="<?= \App\Core\Router::url("/sub-projects/{$project['id']}/update") ?>" method="POST" class="p-6 space-y-4 overflow-y-auto">
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        ชื่อโครงการย่อย <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="<?= htmlspecialchars($project['name']) ?>" required
                           class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            งบประมาณโครงการ (บาท) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" name="budget" value="<?= htmlspecialchars($project['budget']) ?>" required
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            จำนวนครั้งที่วางแผนกิจกรรม
                        </label>
                        <input type="number" min="1" name="planned_activity_count" value="<?= htmlspecialchars($project['planned_activity_count'] ?? 1) ?>"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            วันที่เริ่มต้น <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($project['start_date']) ?>" required
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            วันที่สิ้นสุด <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($project['end_date']) ?>" required
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            ผู้รับผิดชอบโครงการ
                        </label>
                        <select name="responsible_user_id"
                                class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($project['responsible_user_id'] == $u['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['position'] ?: 'เจ้าหน้าที่') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
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
    <div x-show="editActModal" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-lg w-full overflow-hidden"
             @click.away="editActModal = false">
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
                <button type="button" @click="editActModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'<?= \App\Core\Router::url('/activities/') ?>' + selectedAct.id + '/update'" method="POST" class="p-6 space-y-4">
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
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            วันที่จัดกิจกรรม <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="activity_date" x-model="selectedAct.activity_date" required
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            งบประมาณกิจกรรม (บาท) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" name="budget" x-model="selectedAct.budget" required
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            สถานที่จัดกิจกรรม
                        </label>
                        <input type="text" name="location" x-model="selectedAct.location" placeholder="เช่น อาคารอเนกประสงค์"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            จำนวนผู้เข้าร่วม (คน)
                        </label>
                        <input type="number" min="0" name="participant_count" x-model="selectedAct.participant_count"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            สถานะกิจกรรม
                        </label>
                        <select name="status" x-model="selectedAct.status"
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
                        <input type="number" min="0" max="100" step="1" name="progress" x-model="selectedAct.progress"
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
    <div x-show="uploadModal" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-md w-full overflow-hidden"
             @click.away="uploadModal = false">
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
                <button type="button" @click="uploadModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
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
                                <option value="<?= $act['id'] ?>"><?= htmlspecialchars($act['name']) ?></option>
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

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>
