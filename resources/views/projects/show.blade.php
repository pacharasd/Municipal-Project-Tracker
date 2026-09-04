<?php
ob_start();
$title = htmlspecialchars($project['name']);
?>

<div class="space-y-6" x-data="{ createSubModal: false, editModal: false }">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="<?= \App\Core\Router::url('/projects') ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> ย้อนกลับไปหน้ารายการโครงการ
        </a>
        <div class="flex items-center gap-2">
            <?php if (\App\Core\Auth::canManageProjects()): ?>
                <button type="button" @click="editModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> แก้ไขข้อมูล
                </button>
            <?php endif; ?>
            <?php if (\App\Core\Auth::isAdmin()): ?>
                <form action="<?= \App\Core\Router::url("/projects/{$project['id']}/delete") ?>" method="POST" onsubmit="return confirm('ยืนยันการลบโครงการนี้และโครงการย่อยทั้งหมดหรือไม่? ข้อมูลจะไม่สามารถกู้คืนได้');">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 transition-colors">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> ลบโครงการ
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Project Info Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            <span class="px-2.5 py-1 text-xs font-mono font-bold rounded-lg bg-slate-100 text-slate-800 border border-slate-200"><?= htmlspecialchars($project['project_code']) ?></span>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200"><?= htmlspecialchars($project['department_name']) ?></span>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">ปีงบประมาณ <?= $project['fiscal_year'] ?></span>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-50 text-purple-700 border border-purple-200"><?= htmlspecialchars($project['category_name']) ?></span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-3">
            <?= htmlspecialchars($project['name']) ?>
        </h1>

        <p class="text-sm text-slate-600 mt-2 max-w-4xl leading-relaxed">
            <?= nl2br(htmlspecialchars($project['description'] ?? 'ไม่มีคำอธิบายเพิ่มเติม')) ?>
        </p>

        <!-- KPI Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100">
            <div>
                <div class="text-xs text-slate-400">งบประมาณที่ได้รับจัดสรร</div>
                <div class="text-lg sm:text-xl font-bold text-slate-900 mt-0.5"><?= number_format($project['budget'], 2) ?> <span class="text-xs font-normal text-slate-500">บาท</span></div>
            </div>
            <div>
                <div class="text-xs text-slate-400">ยอดเบิกจ่ายแล้ว</div>
                <div class="text-lg sm:text-xl font-bold text-emerald-600 mt-0.5"><?= number_format($project['disbursed_amount'], 2) ?> <span class="text-xs font-normal text-slate-500">บาท</span></div>
            </div>
            <div>
                <div class="text-xs text-slate-400">คงเหลือ</div>
                <div class="text-lg sm:text-xl font-bold text-purple-600 mt-0.5"><?= number_format($project['budget'] - $project['disbursed_amount'], 2) ?> <span class="text-xs font-normal text-slate-500">บาท</span></div>
            </div>
            <div>
                <div class="text-xs text-slate-400 flex items-center justify-between">
                    <span>ความก้าวหน้าเฉลี่ย (Rule #47)</span>
                    <span class="font-bold text-emerald-600"><?= number_format($project['progress'], 1) ?>%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 mt-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-2 rounded-full" style="width: <?= min(100, $project['progress']) ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub-projects Section -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="layers" class="w-5 h-5 text-emerald-600"></i>
                    โครงการย่อยในความรับผิดชอบ (<?= count($project['sub_projects']) ?> โครงการ)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">คลิกเพื่อดูรายละเอียดกิจกรรม งบประมาณ บันทึกปัญหา และอัปเดตความคืบหน้า</p>
            </div>
            <?php if (\App\Core\Auth::canManageProjects()): ?>
                <button type="button" @click="createSubModal = true" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-600/30">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> เพิ่มโครงการย่อย
                </button>
            <?php endif; ?>
        </div>

        <?php if (empty($project['sub_projects'])): ?>
            <div class="p-8 text-center text-slate-400 bg-slate-50 rounded-xl border border-slate-200">
                ยังไม่มีโครงการย่อยภายใต้โครงการหลักนี้ กรุณากดปุ่ม "เพิ่มโครงการย่อย"
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="py-3 px-4">รหัส</th>
                            <th class="py-3 px-4">ชื่อโครงการย่อย</th>
                            <th class="py-3 px-4">งบประมาณ (บาท)</th>
                            <th class="py-3 px-4">จำนวนครั้งกิจกรรม</th>
                            <th class="py-3 px-4">ความก้าวหน้า</th>
                            <th class="py-3 px-4">สถานะ</th>
                            <th class="py-3 px-4 text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php foreach ($project['sub_projects'] as $sub): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-600 font-bold"><?= htmlspecialchars($sub['project_code']) ?></td>
                                <td class="py-3.5 px-4 font-semibold text-slate-900">
                                    <a href="<?= \App\Core\Router::url("/sub-projects/{$sub['id']}") ?>" class="hover:text-emerald-600 transition-colors">
                                        <?= htmlspecialchars($sub['name']) ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 font-medium"><?= number_format($sub['budget'], 2) ?></td>
                                <td class="py-3.5 px-4 text-slate-600 font-medium">
                                    <?= $sub['actual_activity_count'] ?> / <?= $sub['planned_activity_count'] ?> ครั้ง
                                </td>
                                <td class="py-3.5 px-4 w-44">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="font-bold text-emerald-600"><?= number_format($sub['progress'], 1) ?>%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full <?= $sub['status'] == 'has_problem' ? 'bg-rose-500' : 'bg-emerald-500' ?>" style="width: <?= min(100, $sub['progress']) ?>%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php
                                    $sClass = match($sub['status']) {
                                        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'in_progress' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'has_problem' => 'bg-rose-50 text-rose-700 border-rose-200 animate-pulse',
                                        default => 'bg-slate-100 text-slate-600 border-slate-200'
                                    };
                                    $sLabel = match($sub['status']) {
                                        'completed' => 'เสร็จสิ้น',
                                        'in_progress' => 'กำลังดำเนินการ',
                                        'has_problem' => 'มีปัญหา',
                                        default => 'ยังไม่เริ่ม'
                                    };
                                    ?>
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border <?= $sClass ?>">
                                        <?= $sLabel ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="<?= \App\Core\Router::url("/sub-projects/{$sub['id']}") ?>" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition-colors">
                                        ดูรายละเอียด <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal: Create Sub-project -->
    <template x-teleport="body">
        <div x-show="createSubModal" x-cloak @click.self="createSubModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-3xl rounded-2xl shadow-xl border border-slate-200 overflow-hidden max-h-[90vh] flex flex-col">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">เพิ่มโครงการย่อย</h3>
                        <p class="text-xs text-slate-500 mt-0.5">ภายใต้: <?= htmlspecialchars($project['name']) ?></p>
                    </div>
                    <button type="button" @click.stop="createSubModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer flex items-center justify-center" title="ปิดหน้าต่าง">
                        <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
                    </button>
                </div>

                <form action="<?= \App\Core\Router::url('/sub-projects') ?>" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="parent_id" value="<?= $project['id'] ?>">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">รหัสโครงการย่อย <span class="text-rose-500">*</span></label>
                            <input type="text" name="project_code" required placeholder="เช่น SUB-2568-001-04" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">ผู้รับผิดชอบโครงการ</label>
                            <select name="responsible_user_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['position'] ?? '') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">ชื่อโครงการย่อย <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="ระบุชื่อโครงการย่อย..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">ประเภทกิจกรรม</label>
                            <input type="text" name="activity_type" placeholder="เช่น ตรวจสุขภาพ, งานก่อสร้าง, ฝึกอบรม" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">พื้นที่ดำเนินการ</label>
                            <input type="text" name="location" placeholder="เช่น ชุมชนวัดใหม่, สวนสาธารณะ" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">กลุ่มเป้าหมาย</label>
                            <input type="text" name="target_group" placeholder="เช่น ประชาชนทั่วไป, ผู้สูงอายุ" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">จำนวนกลุ่มเป้าหมาย (คน)</label>
                            <input type="number" min="0" name="target_quantity" placeholder="0" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">วัตถุประสงค์</label>
                        <textarea name="objective" rows="2" placeholder="วัตถุประสงค์ของโครงการ..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">งบประมาณ (บาท) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0" name="budget" required placeholder="0.00" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">จำนวนครั้งที่วางแผน <span class="text-rose-500">*</span></label>
                            <input type="number" min="1" name="planned_activity_count" value="4" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <span class="text-[10px] text-slate-400">ใช้คำนวณ Progress ตาม Rule #46</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">โหมดการคำนวณความสำเร็จ</label>
                            <select name="progress_mode" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="auto">คำนวณอัตโนมัติ (AUTO)</option>
                                <option value="manual">ระบุเอง (MANUAL)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">วันที่เริ่มต้น <span class="text-rose-500">*</span></label>
                            <input type="date" name="start_date" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">วันที่สิ้นสุด <span class="text-rose-500">*</span></label>
                            <input type="date" name="end_date" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3 flex-shrink-0">
                        <button type="button" @click="createSubModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                            ยกเลิก
                        </button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-sm shadow-emerald-600/30">
                            บันทึกโครงการย่อย
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
