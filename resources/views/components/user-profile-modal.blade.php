<?php
/**
 * Component: User Profile Modal (Interactive Pop-up Center)
 * ระบบติดตามและบริหารโครงการเทศบาล
 *
 * Props:
 * - $currentUser (array): ข้อมูลผู้ใช้งานปัจจุบัน
 * - $csrfToken (string): CSRF Token
 */

$currentUser = $currentUser ?? \App\Core\Auth::user() ?? [];
$userId = (int)($currentUser['id'] ?? 0);
$userStats = \App\Core\Auth::userStats($userId);

$userRole = $currentUser['role_name'] ?? 'admin';
$roleMeta = [
    'admin'     => [
        'label'       => 'ผู้ดูแลระบบ',
        'badge'       => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60',
        'avatar_bg'   => 'bg-purple-600 text-white',
        'icon'        => 'shield-check',
        'description' => 'สิทธิ์สูงสุดในการจัดการข้อมูลทุกส่วนของระบบ'
    ],
    'executive' => [
        'label'       => 'ผู้บริหาร (ดูอย่างเดียว)',
        'badge'       => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60',
        'avatar_bg'   => 'bg-amber-600 text-white',
        'icon'        => 'eye',
        'description' => 'เข้าถึงรายงาน สรุปข้อมูล และแดชบอร์ดภาพรวม'
    ],
    'officer'   => [
        'label'       => 'เจ้าหน้าที่',
        'badge'       => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60',
        'avatar_bg'   => 'bg-blue-600 text-white',
        'icon'        => 'user-check',
        'description' => 'บันทึกและอัปเดตความคืบหน้าโครงการที่ได้รับมอบหมาย'
    ],
];
$currentRoleInfo = $roleMeta[$userRole] ?? [
    'label'       => $currentUser['role_label'] ?? 'ผู้ใช้งาน',
    'badge'       => 'bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/10',
    'avatar_bg'   => 'bg-slate-700 text-white',
    'icon'        => 'user',
    'description' => 'ผู้ใช้งานระบบเทศบาล'
];

$userName = trim($currentUser['name'] ?? 'ผู้ใช้งาน');
$avatarInitials = mb_substr($userName, 0, 2, 'UTF-8');

// แปลงวันที่สมัครใช้งานเป็นปี พ.ศ.
$thMonths = ['01'=>'ม.ค.','02'=>'ก.พ.','03'=>'มี.ค.','04'=>'เม.ย.','05'=>'พ.ค.','06'=>'มิ.ย.','07'=>'ก.ค.','08'=>'ส.ค.','09'=>'ก.ย.','10'=>'ต.ค.','11'=>'พ.ย.','12'=>'ธ.ค.'];
$createdAtTs = !empty($currentUser['created_at']) ? strtotime($currentUser['created_at']) : time();
$d = date('j', $createdAtTs);
$m = $thMonths[date('m', $createdAtTs)] ?? '';
$y = (int)date('Y', $createdAtTs) + 543;
$memberSinceText = "สมาชิกตั้งแต่ {$d} {$m} {$y}";
?>

<template x-teleport="body">
    <div x-show="profileModalOpen" 
         x-cloak 
         data-teleport-modal="true"
         data-persistent-modal="true"
         style="display: none;"
         @click.self="profileModalOpen = false" 
         class="fixed inset-0 z-[999] flex items-center justify-center p-3 sm:p-6 modal-backdrop-smooth overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white dark:bg-[#181a20] rounded-3xl shadow-2xl border border-slate-200 dark:border-white/10 max-w-2xl w-full overflow-hidden max-h-[92vh] flex flex-col modal-box-smooth transform-gpu text-left my-auto transition-all"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <!-- Hero Header Banner (Clean Solid Surface - Zero Green Gradient) -->
            <div class="relative px-6 pt-6 pb-5 border-b border-slate-200/80 dark:border-white/[0.08] bg-slate-50/60 dark:bg-white/[0.02] shrink-0">
                <!-- Close Button -->
                <button type="button" 
                        @click="profileModalOpen = false" 
                        class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/10 rounded-xl transition cursor-pointer z-10"
                        title="ปิดหน้าต่าง">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>

                <!-- Profile User Details -->
                <div class="flex items-start sm:items-center gap-4">
                    <!-- Avatar with Status Dot (Solid Role-Matched Color) -->
                    <div class="relative shrink-0">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl <?= $currentRoleInfo['avatar_bg'] ?> font-heading font-extrabold text-xl sm:text-2xl flex items-center justify-center shadow-md border-2 border-white dark:border-[#181a20]">
                            <?= htmlspecialchars($avatarInitials) ?>
                        </div>
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 border-2 border-white dark:border-[#181a20] rounded-full flex items-center justify-center" title="ออนไลน์ (Active)">
                            <span class="w-2 h-2 bg-white rounded-full"></span>
                        </span>
                    </div>

                    <!-- User Identity & Badges -->
                    <div class="min-w-0 flex-1 pr-6">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="font-heading font-bold text-slate-900 dark:text-white text-lg sm:text-xl truncate">
                                <?= htmlspecialchars($currentUser['name'] ?? 'ผู้ใช้งาน') ?>
                            </h3>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $currentRoleInfo['badge'] ?>">
                                <i data-lucide="<?= $currentRoleInfo['icon'] ?>" class="w-3 h-3"></i>
                                <?= htmlspecialchars($currentRoleInfo['label']) ?>
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-y-1 gap-x-3">
                            <span class="inline-flex items-center gap-1">
                                <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span class="truncate"><?= htmlspecialchars($currentUser['email'] ?? '-') ?></span>
                            </span>
                            <?php if (!empty($currentUser['department_name'])): ?>
                                <span class="inline-flex items-center gap-1 text-slate-700 dark:text-slate-300 font-medium">
                                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400"></i>
                                    <span><?= htmlspecialchars($currentUser['department_name']) ?></span>
                                </span>
                            <?php endif; ?>
                            <span class="inline-flex items-center gap-1 text-slate-400 text-[11px]">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                <span><?= $memberSinceText ?></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Segmented Control Tabs Navigation -->
                <div class="mt-5 p-1 bg-slate-100/90 dark:bg-white/[0.04] rounded-2xl flex items-center gap-1 overflow-x-auto text-xs font-semibold text-slate-600 dark:text-slate-300">
                    <!-- Tab 1: ข้อมูลทั่วไป -->
                    <button type="button" 
                            @click="profileTab = 'general'"
                            class="flex-1 min-w-[95px] py-2 px-3 rounded-xl flex items-center justify-center gap-1.5 transition cursor-pointer"
                            :class="profileTab === 'general' ? 'bg-white dark:bg-[#181a20] text-slate-900 dark:text-white shadow-sm border border-slate-200/80 dark:border-white/10 font-bold' : 'hover:text-slate-900 dark:hover:text-white'">
                        <i data-lucide="user" class="w-3.5 h-3.5"></i>
                        <span>ข้อมูลทั่วไป</span>
                    </button>

                    <!-- Tab 2: ความปลอดภัย -->
                    <button type="button" 
                            @click="profileTab = 'security'"
                            class="flex-1 min-w-[95px] py-2 px-3 rounded-xl flex items-center justify-center gap-1.5 transition cursor-pointer"
                            :class="profileTab === 'security' ? 'bg-white dark:bg-[#181a20] text-slate-900 dark:text-white shadow-sm border border-slate-200/80 dark:border-white/10 font-bold' : 'hover:text-slate-900 dark:hover:text-white'">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        <span>ความปลอดภัย</span>
                    </button>

                    <!-- Tab 3: โครงการที่ดูแล -->
                    <button type="button" 
                            @click="profileTab = 'projects'"
                            class="flex-1 min-w-[95px] py-2 px-3 rounded-xl flex items-center justify-center gap-1.5 transition cursor-pointer relative"
                            :class="profileTab === 'projects' ? 'bg-white dark:bg-[#181a20] text-slate-900 dark:text-white shadow-sm border border-slate-200/80 dark:border-white/10 font-bold' : 'hover:text-slate-900 dark:hover:text-white'">
                        <i data-lucide="folder-kanban" class="w-3.5 h-3.5"></i>
                        <span>โครงการ (<?= $userStats['project_count'] ?>)</span>
                    </button>

                    <!-- Tab 4: ประวัติการใช้งาน -->
                    <button type="button" 
                            @click="profileTab = 'activity'"
                            class="flex-1 min-w-[95px] py-2 px-3 rounded-xl flex items-center justify-center gap-1.5 transition cursor-pointer"
                            :class="profileTab === 'activity' ? 'bg-white dark:bg-[#181a20] text-slate-900 dark:text-white shadow-sm border border-slate-200/80 dark:border-white/10 font-bold' : 'hover:text-slate-900 dark:hover:text-white'">
                        <i data-lucide="history" class="w-3.5 h-3.5"></i>
                        <span>ประวัติการใช้</span>
                    </button>
                </div>
            </div>

            <!-- Modal Content Body (Scrollable) -->
            <div class="overflow-y-auto flex-1 p-6">
                
                <!-- TAB 1: ข้อมูลทั่วไป (General Info) -->
                <div x-show="profileTab === 'general'" x-cloak class="space-y-5">
                    <form action="<?= \App\Core\Router::url('/profile/update') ?>" method="POST" data-no-spa class="space-y-4">
                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">

                        <!-- Account Status Card -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/[0.08] flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="shield" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($currentRoleInfo['label']) ?></div>
                                    <div class="text-[11px] text-slate-400"><?= htmlspecialchars($currentRoleInfo['description']) ?></div>
                                </div>
                            </div>
                            <span class="px-2 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300/60 dark:border-emerald-800/40">
                                บัญชีเปิดใช้งาน
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Name Field -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    ชื่อ-นามสกุล <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>" 
                                       required
                                       minlength="2"
                                       maxlength="150"
                                       placeholder="เช่น นายสมคิด สถิตมั่นคง"
                                       class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-white dark:bg-[#121316] border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none text-slate-900 dark:text-white transition">
                            </div>

                            <!-- Email Field (Read Only for Security) -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
                                    <span>อีเมลเข้าสู่ระบบ</span>
                                    <span class="text-[10px] text-slate-400 flex items-center gap-0.5">
                                        <i data-lucide="lock" class="w-2.5 h-2.5"></i> ล็อคความปลอดภัย
                                    </span>
                                </label>
                                <input type="email" 
                                       value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" 
                                       disabled
                                       class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-100/80 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.06] rounded-xl text-slate-500 dark:text-slate-400 cursor-not-allowed">
                            </div>

                            <!-- Position Field -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    ตำแหน่งงาน
                                </label>
                                <input type="text" 
                                       name="position" 
                                       value="<?= htmlspecialchars($currentUser['position'] ?? '') ?>" 
                                       maxlength="100"
                                       placeholder="เช่น นักวิเคราะห์นโยบายและแผนชำนาญการ"
                                       class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-white dark:bg-[#121316] border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none text-slate-900 dark:text-white transition">
                            </div>

                            <!-- Phone Field -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    เบอร์โทรศัพท์ติดต่อ
                                </label>
                                <input type="text" 
                                       name="phone" 
                                       value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" 
                                       maxlength="20"
                                       placeholder="เช่น 081-234-5678"
                                       class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-white dark:bg-[#121316] border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none text-slate-900 dark:text-white transition">
                            </div>
                        </div>

                        <!-- Department Info Card -->
                        <div class="p-3 rounded-xl bg-blue-50/70 dark:bg-blue-950/20 border border-blue-200/60 dark:border-blue-800/30 flex items-center justify-between text-xs text-blue-900 dark:text-blue-300">
                            <div class="flex items-center gap-2">
                                <i data-lucide="building" class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0"></i>
                                <div>
                                    <span class="text-[11px] text-blue-700 dark:text-blue-400">หน่วยงาน / สังกัด:</span>
                                    <span class="font-bold ml-1"><?= htmlspecialchars($currentUser['department_name'] ?? 'ไม่ระบุสังกัด (ส่วนกลาง)') ?></span>
                                    <?php if (!empty($currentUser['department_code'])): ?>
                                        <span class="text-[10px] text-blue-500">([<?= htmlspecialchars($currentUser['department_code']) ?>])</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="text-[10px] text-blue-600/80 dark:text-blue-400/80">แก้ไขโดยผู้ดูแลระบบ</span>
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="pt-4 border-t border-slate-100 dark:border-white/[0.08] flex items-center justify-between shrink-0">
                            <button type="button" 
                                    @click.stop="profileTab = 'security'"
                                    class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1 cursor-pointer">
                                <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
                                <span>ต้องการเปลี่ยนรหัสผ่าน?</span>
                            </button>
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        @click="profileModalOpen = false"
                                        class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 rounded-xl cursor-pointer transition">
                                    ยกเลิก
                                </button>
                                <button type="submit" 
                                        class="px-5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600 rounded-xl transition shadow-md shadow-emerald-500/20 cursor-pointer flex items-center gap-1.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    <span>บันทึกข้อมูลส่วนตัว</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: ความปลอดภัยและรหัสผ่าน (Security & Password) -->
                <div x-show="profileTab === 'security'" x-cloak class="space-y-4">
                    <form action="<?= \App\Core\Router::url('/profile/password') ?>" 
                          method="POST" 
                          data-no-spa
                          x-data="{ 
                              showCurrent: false, 
                              showNew: false, 
                              showConfirm: false, 
                              newPass: '', 
                              confirmPass: '' 
                          }"
                          @submit="
                              if (newPass.length < 8) { 
                                  alert('รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 8 ตัวอักษร'); 
                                  $event.preventDefault(); 
                                  return false; 
                              } 
                              if (newPass !== confirmPass) { 
                                  alert('รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน'); 
                                  $event.preventDefault(); 
                                  return false; 
                              }
                          "
                          class="space-y-4">
                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">

                        <!-- Security Notice Card -->
                        <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200/80 dark:border-amber-500/20 text-xs text-amber-900 dark:text-amber-300 flex items-start gap-3">
                            <div class="w-7 h-7 rounded-xl bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="shield-alert" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="font-bold">นโยบายความปลอดภัยของบัญชีผู้ใช้งาน</div>
                                <div class="text-[11px] text-amber-800/90 dark:text-amber-300/90 mt-0.5 leading-relaxed">
                                    รหัสผ่านต้องมีความยาวไม่น้อยกว่า 8 ตัวอักษร และจะถูกเข้ารหัสระดับสูงด้วยมาตรฐาน Bcrypt การเปลี่ยนรหัสผ่านจะบันทึกประวัติความปลอดภัยใน Audit Log ทันที
                                </div>
                            </div>
                        </div>

                        <!-- Current Password -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                รหัสผ่านปัจจุบัน <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showCurrent ? 'text' : 'password'" 
                                       name="current_password" 
                                       required
                                       placeholder="••••••••"
                                       class="w-full px-3.5 py-2.5 pr-10 text-xs sm:text-sm bg-white dark:bg-[#121316] border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none text-slate-900 dark:text-white transition">
                                <button type="button" 
                                        @click="showCurrent = !showCurrent" 
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 cursor-pointer"
                                        tabindex="-1"
                                        title="แสดง/ซ่อนรหัสผ่าน">
                                    <svg x-show="!showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showCurrent" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                รหัสผ่านใหม่ <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showNew ? 'text' : 'password'" 
                                       name="new_password" 
                                       x-model="newPass"
                                       required
                                       minlength="8"
                                       placeholder="ความยาวอย่างน้อย 8 ตัวอักษร"
                                       class="w-full px-3.5 py-2.5 pr-10 text-xs sm:text-sm bg-white dark:bg-[#121316] border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none text-slate-900 dark:text-white transition">
                                <button type="button" 
                                        @click="showNew = !showNew" 
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 cursor-pointer"
                                        tabindex="-1"
                                        title="แสดง/ซ่อนรหัสผ่าน">
                                    <svg x-show="!showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showNew" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                </button>
                            </div>
                            <div class="mt-1 flex items-center gap-1.5 text-[11px]" :class="newPass.length >= 8 ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-slate-400'">
                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                <span x-text="newPass.length >= 8 ? 'ความยาวรหัสผ่านถูกต้อง (' + newPass.length + ' ตัวอักษร)' : 'ความยาวต้องไม่น้อยกว่า 8 ตัวอักษร'"></span>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                ยืนยันรหัสผ่านใหม่ <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'" 
                                       name="new_password_confirmation" 
                                       x-model="confirmPass"
                                       required
                                       minlength="8"
                                       placeholder="กรอกรหัสผ่านใหม่อีกครั้ง"
                                       class="w-full px-3.5 py-2.5 pr-10 text-xs sm:text-sm bg-white dark:bg-[#121316] border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none text-slate-900 dark:text-white transition"
                                       :class="{ 'border-rose-400 dark:border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 text-rose-600': confirmPass && newPass !== confirmPass }">
                                <button type="button" 
                                        @click="showConfirm = !showConfirm" 
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 cursor-pointer"
                                        tabindex="-1"
                                        title="แสดง/ซ่อนรหัสผ่าน">
                                    <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showConfirm" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                </button>
                            </div>
                            <div x-show="confirmPass && newPass !== confirmPass" x-cloak class="text-[11px] text-rose-500 font-medium mt-1">
                                รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="pt-4 border-t border-slate-100 dark:border-white/[0.08] flex items-center justify-between shrink-0">
                            <button type="button" 
                                    @click.stop="profileTab = 'general'"
                                    class="text-xs font-semibold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 flex items-center gap-1 cursor-pointer">
                                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                                <span>กลับไปหน้าข้อมูลทั่วไป</span>
                            </button>
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        @click="profileModalOpen = false" 
                                        class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 rounded-xl cursor-pointer transition">
                                    ยกเลิก
                                </button>
                                <button type="submit" 
                                        class="px-5 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 rounded-xl transition shadow-md shadow-amber-500/20 cursor-pointer flex items-center gap-1.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    <span>บันทึกรหัสผ่านใหม่</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- TAB 3: โครงการที่ดูแล (My Assigned Projects & KPI) -->
                <div x-show="profileTab === 'projects'" x-cloak class="space-y-4">
                    <!-- KPI Cards Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 rounded-2xl bg-blue-50/70 dark:bg-blue-950/20 border border-blue-200/60 dark:border-blue-800/30 text-center">
                            <div class="text-[11px] font-semibold text-blue-700 dark:text-blue-400">โครงการทั้งหมด</div>
                            <div class="text-xl font-heading font-extrabold text-blue-900 dark:text-blue-200 mt-0.5">
                                <?= number_format($userStats['project_count']) ?>
                            </div>
                        </div>
                        <div class="p-3 rounded-2xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/30 text-center">
                            <div class="text-[11px] font-semibold text-amber-700 dark:text-amber-400">กำลังดำเนินการ</div>
                            <div class="text-xl font-heading font-extrabold text-amber-900 dark:text-amber-200 mt-0.5">
                                <?= number_format($userStats['in_progress_count']) ?>
                            </div>
                        </div>
                        <div class="p-3 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-800/30 text-center">
                            <div class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">เสร็จสิ้นแล้ว</div>
                            <div class="text-xl font-heading font-extrabold text-emerald-900 dark:text-emerald-200 mt-0.5">
                                <?= number_format($userStats['completed_count']) ?>
                            </div>
                        </div>
                        <div class="p-3 rounded-2xl bg-purple-50/70 dark:bg-purple-950/20 border border-purple-200/60 dark:border-purple-800/30 text-center flex flex-col justify-between">
                            <div class="text-[11px] font-semibold text-purple-700 dark:text-purple-400">งบประมาณรวม</div>
                            <div class="mt-1">
                                <?= \App\Core\Helper::moneyDisplay($userStats['total_budget'], 'card', 'center', 'text-purple-900 dark:text-purple-200') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Projects List -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <i data-lucide="list-todo" class="w-3.5 h-3.5 text-blue-500"></i>
                                รายการโครงการที่รับผิดชอบล่าสุด
                            </span>
                            <a href="<?= \App\Core\Router::url('/projects') ?>" class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">
                                ดูทั้งหมดในระบบ →
                            </a>
                        </div>

                        <?php if (!empty($userStats['assigned_projects'])): ?>
                            <div class="space-y-2.5">
                                <?php foreach ($userStats['assigned_projects'] as $proj): ?>
                                    <?php 
                                        $isSub = !empty($proj['parent_id']);
                                        $targetUrl = $isSub 
                                            ? \App\Core\Router::url("/sub-projects/{$proj['id']}")
                                            : \App\Core\Router::url("/projects/{$proj['id']}");
                                        
                                        $statusClass = match($proj['status']) {
                                            'completed'   => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/40',
                                            'in_progress' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/40',
                                            'has_problem' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/40',
                                            default       => 'bg-slate-50 dark:bg-white/5 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-white/10'
                                        };
                                        $statusLabel = match($proj['status']) {
                                            'completed'   => 'เสร็จสิ้น',
                                            'in_progress' => 'กำลังดำเนินการ',
                                            'has_problem' => 'มีปัญหา',
                                            default       => 'ยังไม่เริ่ม'
                                        };
                                        $progVal = min(100, max(0, (float)($proj['progress'] ?? 0)));
                                    ?>
                                    <div class="p-3 rounded-2xl bg-white dark:bg-[#121316] border border-slate-200 dark:border-white/10 hover:border-emerald-500/40 transition">
                                        <div class="flex items-start justify-between gap-2 mb-1.5">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <?php if ($isSub): ?>
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">โครงการย่อย</span>
                                                    <?php else: ?>
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">โครงการหลัก</span>
                                                    <?php endif; ?>
                                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate"><?= htmlspecialchars($proj['name']) ?></span>
                                                </div>
                                            </div>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full border <?= $statusClass ?> shrink-0">
                                                <?= $statusLabel ?>
                                            </span>
                                        </div>

                                        <!-- Progress Bar & Budget -->
                                        <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 mb-1">
                                            <span>ความคืบหน้า: <strong class="text-slate-700 dark:text-slate-300"><?= $progVal ?>%</strong></span>
                                            <span>งบประมาณ: <?= \App\Core\Helper::moneyDisplay((float)$proj['budget'], 'inline') ?></span>
                                        </div>
                                        <div class="w-full h-1.5 bg-slate-100 dark:bg-white/10 rounded-full overflow-hidden mb-2">
                                            <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all duration-300" style="width: <?= $progVal ?>%"></div>
                                        </div>

                                        <div class="flex justify-end">
                                            <a href="<?= $targetUrl ?>" class="inline-flex items-center gap-1 text-[11px] text-emerald-600 dark:text-emerald-400 hover:underline font-semibold">
                                                <span>เข้าดูรายละเอียดโครงการ</span>
                                                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="py-8 text-center bg-slate-50 dark:bg-white/[0.02] rounded-2xl border border-dashed border-slate-200 dark:border-white/10">
                                <i data-lucide="folder-open" class="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-2"></i>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-300">ยังไม่มีโครงการที่รับผิดชอบโดยตรง</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">เมื่อได้รับมอบหมายเป็นผู้รับผิดชอบโครงการ โครงการจะแสดงในหน้านี้</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TAB 4: ประวัติการใช้งาน (Recent Activity & Audit Log) -->
                <div x-show="profileTab === 'activity'" x-cloak class="space-y-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-purple-500"></i>
                            ประวัติกิจกรรมล่าสุดในระบบ (Audit Logs)
                        </span>
                        <?php if (\App\Core\Auth::isAdmin()): ?>
                            <a href="<?= \App\Core\Router::url('/audit-logs') ?>" class="text-[11px] text-purple-600 dark:text-purple-400 font-semibold hover:underline">
                                ดู Audit Log ทั้งหมด →
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($userStats['recent_activities'])): ?>
                        <div class="space-y-2">
                            <?php foreach ($userStats['recent_activities'] as $log): ?>
                                <?php
                                    $logTs = strtotime($log['created_at'] ?? 'now');
                                    $logDateStr = date('j', $logTs) . ' ' . ($thMonths[date('m', $logTs)] ?? '') . ' ' . ((int)date('Y', $logTs) + 543) . ' เวลา ' . date('H:i', $logTs) . ' น.';
                                    
                                    $actionMeta = match($log['action']) {
                                        'LOGIN'           => ['label' => 'เข้าสู่ระบบ', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/40', 'icon' => 'log-in'],
                                        'UPDATE_PROFILE'  => ['label' => 'แก้ไขข้อมูลส่วนตัว', 'badge' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/40', 'icon' => 'user-pen'],
                                        'CHANGE_PASSWORD' => ['label' => 'เปลี่ยนรหัสผ่าน', 'badge' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/40', 'icon' => 'key'],
                                        'CREATE'          => ['label' => 'สร้างรายการใหม่', 'badge' => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800/40', 'icon' => 'plus'],
                                        'UPDATE'          => ['label' => 'แก้ไขข้อมูล', 'badge' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-white/10 dark:text-slate-300 dark:border-white/10', 'icon' => 'pencil'],
                                        'DELETE'          => ['label' => 'ลบรายการ', 'badge' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/40', 'icon' => 'trash-2'],
                                        default           => ['label' => $log['action'], 'badge' => 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-white/5 dark:text-slate-400 dark:border-white/10', 'icon' => 'activity'],
                                    };
                                ?>
                                <div class="p-3 rounded-2xl bg-white dark:bg-[#121316] border border-slate-200 dark:border-white/10 flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 border <?= $actionMeta['badge'] ?>">
                                            <i data-lucide="<?= $actionMeta['icon'] ?>" class="w-4 h-4"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-800 dark:text-slate-200 truncate">
                                                <?= $actionMeta['label'] ?> 
                                                <span class="text-slate-400 font-normal">ในระบบ <?= htmlspecialchars($log['module'] ?? 'ทั่วไป') ?></span>
                                            </div>
                                            <div class="text-[11px] text-slate-400"><?= $logDateStr ?></div>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono text-slate-400 bg-slate-50 dark:bg-white/5 shrink-0">
                                        #<?= htmlspecialchars($log['record_id'] ?? '-') ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="py-8 text-center bg-slate-50 dark:bg-white/[0.02] rounded-2xl border border-dashed border-slate-200 dark:border-white/10">
                            <i data-lucide="clock" class="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-2"></i>
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300">ยังไม่มีประวัติกิจกรรมที่บันทึกไว้</div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Modal Global Footer -->
            <div class="px-6 py-3 border-t border-slate-100 dark:border-white/[0.08] bg-slate-50/50 dark:bg-white/[0.02] flex items-center justify-between shrink-0 text-xs text-slate-400">
                <div class="flex items-center gap-1.5 text-[11px]">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                    <span>ระบบรักษาความปลอดภัยสารสนเทศเทศบาล</span>
                </div>
                <button type="button" 
                        @click="profileModalOpen = false" 
                        class="px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/10 transition cursor-pointer font-medium">
                    ปิด
                </button>
            </div>

        </div>
    </div>
</template>
