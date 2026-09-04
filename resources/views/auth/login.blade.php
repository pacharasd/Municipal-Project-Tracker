<?php
use App\Core\Router;
use App\Core\Session;

$error = Session::flash('error');
$success = Session::flash('success');
?>
<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบติดตามและบริหารโครงการเทศบาล</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Sarabun', 'sans-serif'],
                        prompt: ['Prompt', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js & Lucide -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        h1, h2, h3, h4, .font-heading { font-family: 'Prompt', sans-serif; }
    </style>
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex items-center justify-center p-4 relative overflow-x-hidden">

    <!-- Decorative background elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-500/5 rounded-full blur-3xl"></div>
    </div>

    <!-- Main Login Container -->
    <div class="w-full max-w-4xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden relative z-10 grid grid-cols-1 lg:grid-cols-12"
         x-data="{
            email: 'admin@municipality.go.th',
            password: 'password',
            showPassword: false,
            fillUser(userEmail) {
                this.email = userEmail;
                this.password = 'password';
            }
         }">

        <!-- Left Column: Municipality Branding Banner -->
        <div class="lg:col-span-5 bg-gradient-to-br from-slate-900 via-blue-950 to-indigo-950 p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(#3b82f6_1px,transparent_1px)] [background-size:16px_16px] opacity-10"></div>
            
            <div class="relative z-10 space-y-6">
                <!-- Municipality Emblem / Brand -->
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-500 to-emerald-400 p-0.5 shadow-lg shadow-blue-500/30 flex items-center justify-center">
                        <div class="w-full h-full bg-slate-900 rounded-[14px] flex items-center justify-center">
                            <i data-lucide="building-2" class="w-6 h-6 text-emerald-400"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="font-heading font-bold text-base tracking-wide text-white">เทศบาลเมืองขอนแก่น</h2>
                        <span class="text-xs text-blue-200">ระบบติดตามและบริหารโครงการ</span>
                    </div>
                </div>

                <!-- Main Tagline -->
                <div class="space-y-2 pt-4">
                    <h1 class="font-heading text-2xl sm:text-3xl font-bold leading-tight text-white">
                        ติดตามความก้าวหน้า <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300">
                            โปร่งใส คุ้มค่างบประมาณ
                        </span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed pt-1">
                        ระบบสารสนเทศเพื่อการติดตามโครงการหลัก โครงการย่อย กิจกรรม และการเบิกจ่ายงบประมาณตามระเบียบกระทรวงมหาดไทย
                    </p>
                </div>

                <!-- Feature Highlights -->
                <div class="space-y-2.5 pt-2">
                    <div class="flex items-center gap-2.5 text-xs text-slate-200">
                        <div class="p-1 rounded-lg bg-emerald-500/20 text-emerald-400">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>ลำดับชั้นโครงการหลักและโครงการย่อย (Hierarchy)</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs text-slate-200">
                        <div class="p-1 rounded-lg bg-emerald-500/20 text-emerald-400">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>คำนวณ % ความคืบหน้าอัตโนมัติตาม Rule #46</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs text-slate-200">
                        <div class="p-1 rounded-lg bg-emerald-500/20 text-emerald-400">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>ควบคุมเพดานงบประมาณและบันทึก Audit Log</span>
                    </div>
                </div>
            </div>

            <!-- Footer Badge -->
            <div class="relative z-10 pt-8 border-t border-slate-800/80 text-[11px] text-slate-400 flex items-center justify-between">
                <span>เวอร์ชัน 1.0.0 (Production)</span>
                <span class="flex items-center gap-1 text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    พร้อมใช้งาน
                </span>
            </div>
        </div>

        <!-- Right Column: Login Form & 1-Click Role Switcher -->
        <div class="lg:col-span-7 p-8 sm:p-10 flex flex-col justify-between">
            <div class="space-y-6">

                <!-- Title & Messages -->
                <div>
                    <h2 class="font-heading text-xl font-bold text-slate-900 dark:text-white">เข้าสู่ระบบการทำงาน</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">กรุณาระบุบัญชีผู้ใช้งานเพื่อเข้าสู่ระบบงานตามบทบาทหน้าที่</p>
                </div>

                <!-- Flash Alerts -->
                <?php if ($error): ?>
                    <div class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 rounded-xl text-xs text-rose-600 dark:text-rose-400 flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 rounded-xl text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form action="<?= Router::url('/login') ?>" method="POST" class="space-y-4">
                    <?= Session::csrfField() ?>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            อีเมลผู้ใช้งาน (Email)
                        </label>
                        <div class="relative">
                            <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="email" name="email" x-model="email" required placeholder="admin@example.com"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                รหัสผ่าน (Password)
                            </label>
                            <span class="text-[11px] text-slate-400">ค่าเริ่มต้น: password</span>
                        </div>
                        <div class="relative">
                            <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input :type="showPassword ? 'text' : 'password'" name="password" x-model="password" required
                                   class="w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                            <button type="button" @click="showPassword = !showPassword" 
                                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 absolute right-3.5 top-1/2 -translate-y-1/2">
                                <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full py-2.5 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                        <span>เข้าสู่ระบบ</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <!-- 1-Click Sole Admin Quick Login Section -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-blue-500"></i>
                            บัญชีผู้ดูแลระบบสูงสุด (Administrator)
                        </span>
                    </div>

                    <button type="button" @click="fillUser('admin@municipality.go.th')"
                            class="w-full p-3 rounded-xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/60 dark:bg-blue-950/30 hover:bg-blue-100/70 text-left transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                AD
                            </div>
                            <div>
                                <div class="text-xs font-bold text-blue-900 dark:text-blue-300">
                                    ผู้ดูแลระบบ (Admin) — สิทธิ์เต็มทุกระบบ
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                                    admin@municipality.go.th (รหัสผ่าน: password)
                                </div>
                            </div>
                        </div>
                        <span class="text-[11px] text-blue-600 dark:text-blue-400 font-medium group-hover:underline flex items-center gap-1">
                            คลิกกรอกด่วน
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </span>
                    </button>
                </div>

            </div>

            <div class="text-center pt-6 text-[11px] text-slate-400">
                ระบบสารสนเทศเทศบาล เพื่อการติดตามและประเมินผลโครงการ
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
