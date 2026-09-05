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
    <link rel="icon" type="image/webp" href="<?= \App\Core\Router::url('/images/mobile-logo.webp') ?>">
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
                    },
                    colors: {
                        slate: {
                            50: '#edf5f4',
                            100: '#e2efee',
                            200: '#d1e5e3',
                            300: '#b2d3d0',
                            400: '#759e9a',
                            500: '#527b77',
                            600: '#3e625f',
                            700: '#2b4745',
                            800: '#1b3230',
                            900: '#0f201f',
                        },
                        teal: {
                            50: '#eef8f7',
                            100: '#d7f2ef',
                            200: '#b2e5e1',
                            300: '#7ed0cb',
                            400: '#3db2ab',
                            500: '#20c997',
                            600: '#007a78',
                            700: '#006664',
                            800: '#025250',
                            900: '#044240',
                        }
                    },
                    boxShadow: {
                        'soft': '4px 6px 20px rgba(160, 190, 190, 0.18), -3px -3px 14px rgba(255, 255, 255, 0.95)',
                        'soft-lg': '8px 12px 28px rgba(160, 190, 190, 0.22), -5px -5px 20px rgba(255, 255, 255, 0.98)',
                        'teal-glow': '0 6px 18px -2px rgba(0, 122, 120, 0.35)',
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js & Lucide -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: #edf5f4;
        }
        h1, h2, h3, h4, .font-heading { font-family: 'Prompt', sans-serif; }
    </style>
</head>
<body class="h-full bg-slate-50 text-slate-800 flex items-center justify-center p-4 relative overflow-x-hidden">

    <!-- Decorative background elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-[#20c997]/15 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-teal-600/5 rounded-full blur-3xl"></div>
    </div>

    <!-- Main Login Container -->
    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-soft-lg border border-slate-200/80 overflow-hidden relative z-10 grid grid-cols-1 lg:grid-cols-12"
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
        <div class="lg:col-span-5 bg-gradient-to-br from-[#024a48] via-[#007a78] to-[#044240] p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(#20c997_1px,transparent_1px)] [background-size:16px_16px] opacity-15"></div>
            
            <div class="relative z-10 space-y-6">
                <!-- Municipality Emblem / Brand -->
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/20 p-1 shadow-lg shadow-teal-900/40 flex items-center justify-center shrink-0 backdrop-blur-sm">
                        <img src="<?= \App\Core\Router::url('/images/mobile-logo.webp') ?>" 
                             alt="โลโก้เทศบาล" 
                             class="w-full h-full object-contain rounded-xl"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <i data-lucide="building-2" style="display:none;" class="w-6 h-6 text-[#20c997]"></i>
                    </div>
                    <div>
                        <h2 class="font-heading font-bold text-base tracking-wide text-white">เทศบาลตำบล / เมือง</h2>
                        <span class="text-xs text-teal-200">ระบบติดตามและบริหารโครงการ</span>
                    </div>
                </div>

                <!-- Main Tagline -->
                <div class="space-y-2 pt-4">
                    <h1 class="font-heading text-2xl sm:text-3xl font-bold leading-tight text-white">
                        ติดตามความก้าวหน้า <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#20c997] to-teal-200">
                            โปร่งใส คุ้มค่างบประมาณ
                        </span>
                    </h1>
                    <p class="text-xs sm:text-sm text-teal-100/90 leading-relaxed pt-1">
                        ระบบสารสนเทศเพื่อการติดตามโครงการหลัก โครงการย่อย กิจกรรม และการเบิกจ่ายงบประมาณตามระเบียบกระทรวงมหาดไทย
                    </p>
                </div>

                <!-- Feature Highlights -->
                <div class="space-y-2.5 pt-2">
                    <div class="flex items-center gap-2.5 text-xs text-teal-100">
                        <div class="p-1 rounded-lg bg-white/20 text-[#20c997]">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>ลำดับชั้นโครงการหลักและโครงการย่อย (Hierarchy)</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs text-teal-100">
                        <div class="p-1 rounded-lg bg-white/20 text-[#20c997]">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>คำนวณ % ความคืบหน้าอัตโนมัติตาม Rule #46</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs text-teal-100">
                        <div class="p-1 rounded-lg bg-white/20 text-[#20c997]">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>ควบคุมเพดานงบประมาณและบันทึก Audit Log</span>
                    </div>
                </div>
            </div>

            <!-- Footer Badge -->
            <div class="relative z-10 pt-8 border-t border-teal-600/50 text-[11px] text-teal-200 flex items-center justify-between">
                <span>เวอร์ชัน 2.5 (Soft UI)</span>
                <span class="flex items-center gap-1 text-[#20c997] font-semibold">
                    <span class="w-2 h-2 rounded-full bg-[#20c997] animate-pulse"></span>
                    พร้อมใช้งาน
                </span>
            </div>
        </div>

        <!-- Right Column: Login Form & 1-Click Role Switcher -->
        <div class="lg:col-span-7 p-8 sm:p-10 flex flex-col justify-between bg-[#f8fcfc]">
            <div class="space-y-6">

                <!-- Title & Messages -->
                <div>
                    <h2 class="font-heading text-xl font-bold text-slate-800">เข้าสู่ระบบการทำงาน</h2>
                    <p class="text-xs text-slate-500 mt-1">กรุณาระบุบัญชีผู้ใช้งานเพื่อเข้าสู่ระบบงานตามบทบาทหน้าที่</p>
                </div>

                <!-- Flash Alerts -->
                <?php if ($error): ?>
                    <div class="p-3.5 bg-coral-50 border border-coral-200 rounded-2xl text-xs text-coral-800 flex items-center gap-2 shadow-soft-sm">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-coral-600 flex-shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="p-3.5 bg-teal-50 border border-teal-200 rounded-2xl text-xs text-teal-800 flex items-center gap-2 shadow-soft-sm">
                        <i data-lucide="check-circle" class="w-4 h-4 text-teal-600 flex-shrink-0"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form action="<?= Router::url('/login') ?>" method="POST" class="space-y-4">
                    <?= Session::csrfField() ?>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            อีเมลผู้ใช้งาน (Email)
                        </label>
                        <div class="relative">
                            <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="email" name="email" x-model="email" required placeholder="admin@municipality.go.th"
                                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none text-slate-800 shadow-soft-sm font-mono">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">
                                รหัสผ่าน (Password)
                            </label>
                            <span class="text-[11px] text-slate-400 font-mono">ค่าเริ่มต้น: password</span>
                        </div>
                        <div class="relative">
                            <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input :type="showPassword ? 'text' : 'password'" name="password" x-model="password" required
                                   class="w-full pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none text-slate-800 shadow-soft-sm font-mono">
                            <button type="button" @click="showPassword = !showPassword" 
                                    class="text-slate-400 hover:text-slate-600 absolute right-3.5 top-1/2 -translate-y-1/2">
                                <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full py-3 px-4 bg-[#007a78] hover:bg-teal-700 text-white font-semibold rounded-2xl text-sm shadow-teal-glow hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span>เข้าสู่ระบบ</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <!-- 1-Click Sole Admin Quick Login Section -->
                <div class="pt-4 border-t border-slate-200/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-teal-600"></i>
                            บัญชีผู้ดูแลระบบสูงสุด (Administrator)
                        </span>
                    </div>

                    <button type="button" @click="fillUser('admin@municipality.go.th')"
                            class="w-full p-3 rounded-2xl border border-teal-200/90 bg-white hover:bg-teal-50/60 text-left transition-all flex items-center justify-between group shadow-soft-sm cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-[#007a78] text-white flex items-center justify-center font-bold text-xs shadow-soft-sm">
                                AD
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-800 group-hover:text-teal-700 transition">
                                    ผู้ดูแลระบบ (Admin) — สิทธิ์เต็มทุกระบบ
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono">
                                    admin@municipality.go.th (รหัสผ่าน: password)
                                </div>
                            </div>
                        </div>
                        <span class="text-[11px] text-teal-700 font-semibold group-hover:underline flex items-center gap-1">
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
