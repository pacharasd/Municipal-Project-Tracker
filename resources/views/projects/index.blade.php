<?php
ob_start();
$title = "จัดการโครงการหลักและโครงการย่อย";
?>

<div class="space-y-6" x-data="{ createModal: false }">
    <!-- Header with Action -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 mb-1">
                <a href="<?= \App\Core\Router::url('/dashboard') ?>" class="hover:underline">หน้าหลัก</a>
                <span>/</span>
                <span>โครงการหลักและย่อย</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">โครงการหลักและโครงการย่อย</h1>
            <p class="text-sm text-slate-500 mt-1">บริหารจัดการโครงการตามแผนพัฒนาเทศบาล ติดตามความก้าวหน้าและงบประมาณแบบลำดับชั้น</p>
        </div>
        <div class="flex items-center gap-3">
            <?php if (\App\Core\Auth::canManageProjects()): ?>
                <button type="button" @click="createModal = true" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-600/30">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> สร้างโครงการหลักใหม่
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters Section -->
    <form action="<?= \App\Core\Router::url('/projects') ?>" method="GET" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- Search Keyword -->
        <div class="lg:col-span-2 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </div>
            <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="ค้นหาชื่อ หรือรหัสโครงการ..." class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        </div>

        <!-- Fiscal Year Filter -->
        <div>
            <select name="fiscal_year_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">-- ทุกปีงบประมาณ --</option>
                <?php foreach ($fiscalYears as $fy): ?>
                    <option value="<?= $fy['id'] ?>" <?= $filters['fiscal_year_id'] == $fy['id'] ? 'selected' : '' ?>>ปี <?= $fy['year'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Department Filter -->
        <div>
            <select name="department_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">-- ทุกสำนัก/กอง --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>" <?= $filters['department_id'] == $dept['id'] ? 'selected' : '' ?>><?= htmlspecialchars($dept['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Submit & Clear Buttons -->
        <div class="flex items-center gap-2">
            <button type="submit" class="w-full py-2 px-4 text-sm font-medium text-white bg-slate-800 rounded-xl hover:bg-slate-900 transition-colors">
                ค้นหา
            </button>
            <a href="<?= \App\Core\Router::url('/projects') ?>" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl" title="ล้างตัวกรอง">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
            </a>
        </div>
    </form>

    <!-- Main Projects List (Hierarchical Structure) -->
    <div class="space-y-4">
        <?php if (empty($projects)): ?>
            <div class="bg-white p-12 text-center rounded-2xl border border-slate-200">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="folder-search" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">ไม่พบโครงการตามเงื่อนไขที่ระบุ</h3>
                <p class="text-xs text-slate-500 mt-1">กรุณาลองปรับเปลี่ยนคำค้นหาหรือตัวกรองใหม่อีกครั้ง</p>
            </div>
        <?php endif; ?>

        <?php foreach ($projects as $p): ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ expanded: true }">
                <!-- Main Project Bar -->
                <div class="p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100">
                    <div class="flex items-start gap-4">
                        <button type="button" @click="expanded = !expanded" class="mt-1 p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                            <i data-lucide="chevron-down" class="w-5 h-5 transform transition-transform" :class="expanded ? 'rotate-0' : '-rotate-90'"></i>
                        </button>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 text-xs font-mono font-bold rounded bg-slate-100 text-slate-700 border border-slate-200"><?= htmlspecialchars($p['project_code']) ?></span>
                                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-blue-50 text-blue-700 border border-blue-200"><?= htmlspecialchars($p['department_name']) ?></span>
                                <span class="px-2 py-0.5 text-xs text-slate-500">ปีงบ <?= $p['fiscal_year'] ?></span>
                            </div>
                            <h2 class="text-lg font-bold text-slate-900 mt-1">
                                <a href="<?= \App\Core\Router::url("/projects/{$p['id']}") ?>" class="hover:text-emerald-600 transition-colors">
                                    <?= htmlspecialchars($p['name']) ?>
                                </a>
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-1"><?= htmlspecialchars($p['description']) ?></p>
                        </div>
                    </div>

                    <!-- Right KPIs for Main Project -->
                    <div class="flex items-center gap-6 lg:justify-end">
                        <div class="text-right">
                            <div class="text-xs text-slate-400">งบประมาณรวม</div>
                            <div class="text-sm font-bold text-slate-900"><?= number_format($p['budget'], 2) ?> <span class="text-[10px] font-normal text-slate-500">บาท</span></div>
                        </div>

                        <!-- Progress indicator -->
                        <div class="w-36">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-slate-500">ความก้าวหน้า</span>
                                <span class="font-bold text-emerald-600"><?= number_format($p['progress'], 1) ?>%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-2 rounded-full" style="width: <?= min(100, $p['progress']) ?>%"></div>
                            </div>
                        </div>

                        <!-- View detail button -->
                        <a href="<?= \App\Core\Router::url("/projects/{$p['id']}") ?>" class="p-2 rounded-xl text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" title="ดูรายละเอียดโครงการ">
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>

                <!-- Sub-projects accordion table -->
                <div x-show="expanded" class="bg-slate-50/50 p-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center justify-between">
                        <span>โครงการย่อยในความรับผิดชอบ (<?= count($p['sub_projects']) ?> โครงการ)</span>
                        <a href="<?= \App\Core\Router::url("/projects/{$p['id']}") ?>" class="text-emerald-600 hover:underline normal-case font-semibold text-xs flex items-center gap-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> เพิ่มโครงการย่อย
                        </a>
                    </div>

                    <?php if (empty($p['sub_projects'])): ?>
                        <div class="p-4 text-center text-xs text-slate-400 bg-white rounded-xl border border-slate-200">
                            ยังไม่มีโครงการย่อยภายใต้โครงการหลักนี้
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <?php foreach ($p['sub_projects'] as $sub): ?>
                                <a href="<?= \App\Core\Router::url("/sub-projects/{$sub['id']}") ?>" class="block p-4 rounded-xl bg-white border border-slate-200 hover:border-emerald-300 hover:shadow-md transition-all">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[11px] font-mono text-slate-400"><?= htmlspecialchars($sub['project_code']) ?></span>
                                        <?php
                                        $statusClass = match($sub['status']) {
                                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'in_progress' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'has_problem' => 'bg-rose-50 text-rose-700 border-rose-200 animate-pulse',
                                            default => 'bg-slate-100 text-slate-600 border-slate-200'
                                        };
                                        $statusLabel = match($sub['status']) {
                                            'completed' => 'เสร็จสิ้น',
                                            'in_progress' => 'กำลังดำเนินการ',
                                            'has_problem' => 'มีปัญหา',
                                            default => 'ยังไม่เริ่ม'
                                        };
                                        ?>
                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full border <?= $statusClass ?>">
                                            <?= $statusLabel ?>
                                        </span>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800 mt-2 line-clamp-1"><?= htmlspecialchars($sub['name']) ?></h3>
                                    
                                    <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                                        <span>กิจกรรม: <b><?= $sub['actual_activity_count'] ?></b> / <?= $sub['planned_activity_count'] ?> ครั้ง</span>
                                        <span class="font-bold text-emerald-600"><?= number_format($sub['progress'], 1) ?>%</span>
                                    </div>

                                    <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full <?= $sub['status'] == 'has_problem' ? 'bg-rose-500' : 'bg-emerald-500' ?>" style="width: <?= min(100, $sub['progress']) ?>%"></div>
                                    </div>

                                    <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                                        <span>งบ: <?= number_format($sub['budget'], 0) ?> บ.</span>
                                        <span class="truncate max-w-[100px]"><?= htmlspecialchars($sub['responsible_name'] ?? 'ผู้รับผิดชอบ') ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal: Create Main Project -->
    <template x-teleport="body">
        <div x-show="createModal" x-cloak @click.self="createModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">สร้างโครงการหลักใหม่</h3>
                        <p class="text-xs text-slate-500 mt-0.5">เพิ่มโครงการหลักประจำปีงบประมาณตามแผนพัฒนาเทศบาล</p>
                    </div>
                    <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="<?= \App\Core\Router::url('/projects') ?>" method="POST" class="p-6 space-y-4">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">รหัสโครงการ <span class="text-rose-500">*</span></label>
                            <input type="text" name="project_code" required placeholder="เช่น PRJ-2568-004" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">ปีงบประมาณ <span class="text-rose-500">*</span></label>
                            <select name="fiscal_year_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <?php foreach ($fiscalYears as $fy): ?>
                                    <option value="<?= $fy['id'] ?>" <?= $fy['is_active'] ? 'selected' : '' ?>>ปี <?= $fy['year'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">ชื่อโครงการหลัก <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="ระบุชื่อโครงการหลัก..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">คำอธิบายและวัตถุประสงค์</label>
                        <textarea name="description" rows="2" placeholder="รายละเอียดของโครงการ..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">หมวดหมู่โครงการ <span class="text-rose-500">*</span></label>
                            <select name="category_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">หน่วยงาน / สำนัก / กอง <span class="text-rose-500">*</span></label>
                            <select name="department_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">งบประมาณรวม (บาท) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0" name="budget" required placeholder="0.00" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">วันที่เริ่มต้น <span class="text-rose-500">*</span></label>
                            <input type="date" name="start_date" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">วันที่สิ้นสุด <span class="text-rose-500">*</span></label>
                            <input type="date" name="end_date" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="createModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                            ยกเลิก
                        </button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-sm shadow-emerald-600/30">
                            บันทึกโครงการหลัก
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
