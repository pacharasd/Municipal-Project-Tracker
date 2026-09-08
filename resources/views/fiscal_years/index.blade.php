<?php
$title = 'จัดการปีงบประมาณ - Municipal Project Tracker';
$activeYearNum = $activeYear['year'] ?? \App\Services\FiscalYearService::getCurrentFiscalYear();
?>

<div class="space-y-6 w-full max-w-full" x-data="{
    openAddModal: false,
    openEditModal: false,
    editData: { id: '', year: '', start_date: '', end_date: '' },
    openEdit(fy) {
        this.editData = {
            id: fy.id,
            year: fy.year,
            start_date: fy.start_date || '',
            end_date: fy.end_date || ''
        };
        this.openEditModal = true;
    }
}">

    <!-- Flash Messages -->
    <?php if ($success = \App\Core\Session::flash('success')): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 flex items-center justify-between text-emerald-800 dark:text-emerald-300 text-sm shadow-sm animate-fade-in">
            <div class="flex items-center gap-2.5">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                <span class="font-medium"><?= htmlspecialchars($success) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($error = \App\Core\Session::flash('error')): ?>
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 flex items-center justify-between text-rose-800 dark:text-rose-300 text-sm shadow-sm animate-fade-in">
            <div class="flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0"></i>
                <span class="font-medium"><?= htmlspecialchars($error) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- 1. Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400 mb-1">
                <i data-lucide="calendar-range" class="w-4 h-4"></i>
                <span>ADMINISTRATION / ระบบปีงบประมาณ</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 dark:text-white tracking-tight">
                จัดการปีงบประมาณ
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                บริหารจัดการปีงบประมาณ กำหนดปีปัจจุบันสำหรับการติดตามโครงการ และคำนวณสถิติภาพรวม
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 shrink-0">
            <a href="<?= \App\Core\Router::url('/dashboard') ?>" 
               class="px-4 py-2.5 rounded-xl bg-white dark:bg-[#161922] text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/5 text-xs font-bold transition flex items-center gap-2 shadow-sm">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span>ดู Dashboard</span>
            </a>
            <button type="button" 
                    @click="openAddModal = true" 
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition flex items-center gap-2 shadow-sm shadow-emerald-600/20 cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>เพิ่มปีงบประมาณใหม่</span>
            </button>
        </div>
    </div>

    <!-- 2. Summary KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: ปีงบประมาณปัจจุบัน -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400"></div>
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-heading">
                    ปีงบประมาณปัจจุบัน
                </span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-200/60 dark:border-emerald-500/20">
                    <i data-lucide="calendar-check-2" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-black font-heading text-slate-900 dark:text-white">
                    พ.ศ. <?= htmlspecialchars((string)$activeYearNum) ?>
                </div>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                        เปิดใช้งานในระบบหลัก
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 2: จำนวนปีในระบบ -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-heading">
                    ปีงบประมาณทั้งหมด
                </span>
                <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-200/60 dark:border-blue-500/20">
                    <i data-lucide="history" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-black font-heading text-slate-900 dark:text-white">
                    <?= number_format($metrics['total_years']) ?>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    ปีงบประมาณที่พร้อมใช้งานในระบบ
                </p>
            </div>
        </div>

        <!-- Card 3: โครงการทั้งหมด -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-pink-500"></div>
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-heading">
                    โครงการทั้งหมดทุกปี
                </span>
                <div class="w-10 h-10 rounded-2xl bg-purple-50 dark:bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-200/60 dark:border-purple-500/20">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-black font-heading text-slate-900 dark:text-white">
                    <?= number_format($metrics['total_projects']) ?>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    โครงการหลัก (โครงการย่อย <?= number_format($metrics['total_sub_projects']) ?> รายการ)
                </p>
            </div>
        </div>

        <!-- Card 4: งบประมาณสะสม -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500"></div>
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-heading">
                    งบประมาณสะสมทั้งหมด
                </span>
                <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200/60 dark:border-amber-500/20">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-black font-heading text-slate-900 dark:text-white">
                    <?= number_format($metrics['grand_total_budget']) ?>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mt-1">
                    <span>เบิกจ่าย <?= number_format($metrics['grand_total_disbursed']) ?> บาท</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400"><?= $metrics['overall_disbursed_pct'] ?>%</span>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. Table Section -->
    <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-[#161922] border border-slate-200/80 dark:border-white/[0.08] shadow-sm overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100 dark:border-white/[0.06]">
            <div>
                <h2 class="text-base sm:text-lg font-bold font-heading text-slate-900 dark:text-white">
                    รายการปีงบประมาณในระบบ
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    กดปุ่ม <span class="font-semibold text-emerald-600 dark:text-emerald-400">"ตั้งเป็นปีปัจจุบัน"</span> เพื่อเปลี่ยนปีงบประมาณหลักที่แสดงบน Dashboard ทันที
                </p>
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                ทั้งหมด <span class="font-bold text-slate-900 dark:text-white"><?= count($years) ?></span> ปีงบประมาณ
            </div>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left text-xs border-collapse min-w-[700px]">
                <thead>
                    <tr class="border-b border-slate-200/70 dark:border-white/[0.06] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px] bg-slate-50/50 dark:bg-white/[0.02]">
                        <th class="py-3 px-4 w-36">ปีงบประมาณ</th>
                        <th class="py-3 px-4 min-w-[170px]">ช่วงเวลางบประมาณ</th>
                        <th class="py-3 px-4 text-center w-28">โครงการหลัก</th>
                        <th class="py-3 px-4 text-center w-28">โครงการย่อย</th>
                        <th class="py-3 px-4 text-right min-w-[130px]">งบประมาณรวม</th>
                        <th class="py-3 px-4 text-right min-w-[130px]">เบิกจ่ายสะสม</th>
                        <th class="py-3 px-4 text-center w-32">สถานะ</th>
                        <th class="py-3 px-4 text-center min-w-[200px]">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04] text-slate-700 dark:text-slate-300">
                    <?php if (!empty($years)): ?>
                        <?php foreach ($years as $fy): ?>
                            <?php 
                                $isActive = !empty($fy['is_active']);
                                $bAmount = (float)($fy['total_budget'] ?? 0);
                                $dAmount = (float)($fy['total_disbursed'] ?? 0);
                                $pct = $bAmount > 0 ? round(($dAmount / $bAmount) * 100, 1) : 0;
                            ?>
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-white/[0.02] transition <?= $isActive ? 'bg-emerald-50/40 dark:bg-emerald-950/10' : '' ?>">
                                <!-- ปีงบประมาณ -->
                                <td class="py-3.5 px-4 font-bold font-heading text-sm text-slate-900 dark:text-white">
                                    <div class="flex items-center gap-2">
                                        <span>พ.ศ. <?= htmlspecialchars((string)$fy['year']) ?></span>
                                        <?php if ($isActive): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                                ปีปัจจุบัน
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- ช่วงเวลา -->
                                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-mono text-[11px]">
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span><?= !empty($fy['start_date']) ? date('d/m/Y', strtotime($fy['start_date'])) : '-' ?></span>
                                        <span class="text-slate-400">ถึง</span>
                                        <span><?= !empty($fy['end_date']) ? date('d/m/Y', strtotime($fy['end_date'])) : '-' ?></span>
                                    </div>
                                </td>

                                <!-- โครงการหลัก -->
                                <td class="py-3.5 px-4 text-center font-bold">
                                    <span class="px-2.5 py-1 rounded-lg text-xs <?= (int)$fy['project_count'] > 0 ? 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 font-mono' : 'text-slate-400' ?>">
                                        <?= (int)$fy['project_count'] ?>
                                    </span>
                                </td>

                                <!-- โครงการย่อย -->
                                <td class="py-3.5 px-4 text-center font-bold">
                                    <span class="px-2.5 py-1 rounded-lg text-xs <?= (int)$fy['sub_project_count'] > 0 ? 'bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 font-mono' : 'text-slate-400' ?>">
                                        <?= (int)$fy['sub_project_count'] ?>
                                    </span>
                                </td>

                                <!-- งบประมาณรวม -->
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                    <?= number_format($bAmount) ?> บาท
                                </td>

                                <!-- เบิกจ่ายสะสม -->
                                <td class="py-3.5 px-4 text-right font-mono">
                                    <div class="font-bold text-emerald-600 dark:text-emerald-400">
                                        <?= number_format($dAmount) ?> บาท
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        <?= $pct ?>% ของงบประมาณ
                                    </div>
                                </td>

                                <!-- สถานะ -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <?php if ($isActive): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500 text-white shadow-sm shadow-emerald-500/20">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            <span>Active</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400">
                                            ทั่วไป
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- การจัดการ -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- ดู Dashboard ของปีนี้ -->
                                        <a href="<?= \App\Core\Router::url('/dashboard') ?>?fiscal_year_id=<?= $fy['id'] ?>" 
                                           class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition" 
                                           title="เปิดดู Dashboard ของปีนี้">
                                            <i data-lucide="external-link" class="w-4 h-4"></i>
                                        </a>

                                        <!-- ตั้งเป็นปีปัจจุบัน (ถ้ายังไม่ได้เป็น) -->
                                        <?php if (!$isActive): ?>
                                            <form method="POST" action="<?= \App\Core\Router::url("/fiscal-years/{$fy['id']}/set-active") ?>" class="inline" onsubmit="return confirm('ยืนยันการเปลี่ยนปีงบประมาณปัจจุบันเป็น พ.ศ. <?= $fy['year'] ?>?')">
                                                <button type="submit" 
                                                        class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-600 hover:text-white text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition cursor-pointer flex items-center gap-1">
                                                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                                    <span>ตั้งเป็นปีปัจจุบัน</span>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                                                ✓ ปีปัจจุบัน
                                            </span>
                                        <?php endif; ?>

                                        <!-- แก้ไขช่วงเวลา -->
                                        <button type="button" 
                                                @click="openEdit(<?= htmlspecialchars(json_encode($fy)) ?>)"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5 transition cursor-pointer" 
                                                title="แก้ไขช่วงเวลาเริ่มต้น-สิ้นสุด">
                                            <i data-lucide="calendar" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="calendar-x" class="w-8 h-8 opacity-40"></i>
                                    <span>ไม่พบข้อมูลปีงบประมาณในระบบ</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. Modal เพิ่มปีงบประมาณใหม่ -->
    <div x-show="openAddModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
        <div @click.away="openAddModal = false" 
             class="w-full max-w-md bg-white dark:bg-[#181a20] rounded-3xl p-6 shadow-2xl border border-slate-200/80 dark:border-white/10 space-y-5 animate-scale-up">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-white/10 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-200/50">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold font-heading text-slate-900 dark:text-white">
                            เพิ่มปีงบประมาณใหม่
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            กำหนดปี พ.ศ. และช่วงเวลาเริ่มต้น-สิ้นสุด
                        </p>
                    </div>
                </div>
                <button type="button" @click="openAddModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="<?= \App\Core\Router::url('/fiscal-years') ?>" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        ปีงบประมาณ (พ.ศ.) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" 
                           name="year" 
                           min="2500" 
                           max="2650" 
                           placeholder="เช่น 2576" 
                           required 
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#12141a] border border-slate-200 dark:border-white/10 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[11px] text-slate-400 mt-1">
                        ระบุปี พ.ศ. เป็นตัวเลข 4 หลัก
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            วันที่เริ่มต้น
                        </label>
                        <input type="date" 
                               name="start_date" 
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-[#12141a] border border-slate-200 dark:border-white/10 text-xs text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        <p class="text-[10px] text-slate-400 mt-0.5">เว้นว่างไว้จะใช้ 1 ต.ค. ปีก่อนหน้า</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            วันที่สิ้นสุด
                        </label>
                        <input type="date" 
                               name="end_date" 
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-[#12141a] border border-slate-200 dark:border-white/10 text-xs text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        <p class="text-[10px] text-slate-400 mt-0.5">เว้นว่างไว้จะใช้ 30 ก.ย.</p>
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-white/10">
                    <button type="button" 
                            @click="openAddModal = false" 
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 transition">
                        ยกเลิก
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm shadow-emerald-600/20 transition cursor-pointer">
                        บันทึกปีงบประมาณ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 5. Modal แก้ไขช่วงเวลาปีงบประมาณ -->
    <div x-show="openEditModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
        <div @click.away="openEditModal = false" 
             class="w-full max-w-md bg-white dark:bg-[#181a20] rounded-3xl p-6 shadow-2xl border border-slate-200/80 dark:border-white/10 space-y-5 animate-scale-up">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-white/10 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-200/50">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold font-heading text-slate-900 dark:text-white">
                            แก้ไขช่วงเวลาปีงบประมาณ <span x-text="editData.year"></span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            ปรับเปลี่ยนวันเริ่มต้นและวันสิ้นสุดรอบปีงบประมาณ
                        </p>
                    </div>
                </div>
                <button type="button" @click="openEditModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'<?= \App\Core\Router::url('/fiscal-years') ?>/' + editData.id + '/update'" method="POST" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            วันที่เริ่มต้น <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" 
                               name="start_date" 
                               x-model="editData.start_date" 
                               required 
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-[#12141a] border border-slate-200 dark:border-white/10 text-xs text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            วันที่สิ้นสุด <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" 
                               name="end_date" 
                               x-model="editData.end_date" 
                               required 
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-[#12141a] border border-slate-200 dark:border-white/10 text-xs text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-white/10">
                    <button type="button" 
                            @click="openEditModal = false" 
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 transition">
                        ยกเลิก
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-500 text-white shadow-sm shadow-blue-600/20 transition cursor-pointer">
                        บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
