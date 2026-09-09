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
    <link rel="icon" type="image/webp" href="<?= Router::url('/images/mobile-logo.webp') ?>">
    
    <!-- Theme Detection & Anti-Flicker Script -->
    <script>
        (function() {
            try {
                const theme = localStorage.getItem('theme') || 'system';
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = (theme === 'dark' || (theme === 'system' && prefersDark));
                if (isDark) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.style.colorScheme = 'dark';
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.style.colorScheme = 'light';
                }
            } catch (e) {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.style.colorScheme = 'dark';
                }
            }
        })();
    </script>

    <!-- Google Fonts: Prompt & Sarabun -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Sarabun', 'sans-serif'],
                        heading: ['Prompt', 'sans-serif'],
                    },
                    colors: {
                        canvas: '#0f1014',
                        surface: '#181a20',
                        surfaceLight: '#22252e',
                        borderDark: 'rgba(255, 255, 255, 0.08)',
                        neon: {
                            green: '#10b981',
                            mint: '#0df5c4',
                            yellow: '#ffd166',
                            cyan: '#38bdf8',
                        },
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                        teal: {
                            400: '#0df5c4',
                            500: '#10b981',
                            600: '#059669',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js & Lucide Icons -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        h1, h2, h3, h4, .font-heading { font-family: 'Prompt', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-[#f8fafc] dark:bg-[#0f1014] text-slate-800 dark:text-slate-100 flex flex-col justify-center items-center p-4 sm:p-6 relative overflow-x-hidden transition-colors duration-200"
      x-data="loginPage()">

    <!-- Ambient Mesh Glow Background (Light & Dark Theme) -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <!-- Dot Pattern Background -->
        <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] dark:bg-[radial-gradient(#334155_1px,transparent_1px)] [background-size:24px_24px] opacity-40 dark:opacity-20"></div>
        
        <!-- Glowing Ambient Orbs -->
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-500/15 dark:bg-emerald-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-teal-400/15 dark:bg-teal-400/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-emerald-600/5 dark:bg-emerald-500/5 rounded-full blur-3xl"></div>
    </div>

    <!-- Floating Top Bar: Theme Switcher -->
    <div class="w-full max-w-4xl flex items-center justify-between mb-4 relative z-20 px-1">
        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="font-medium">ระบบสารสนเทศเทศบาล</span>
        </div>

        <!-- Light / Dark / System Mode Toggle -->
        <div class="relative shrink-0" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" 
                    @click="open = !open"
                    id="theme-dropdown-btn"
                    class="px-3 py-1.5 rounded-xl bg-white dark:bg-[#181a20] border border-slate-200 dark:border-white/10 shadow-sm text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-emerald-500/40 transition-all flex items-center gap-2 cursor-pointer"
                    title="เลือกโหมดการแสดงผล (สว่าง / มืด / ตามระบบ)">
                
                <!-- Dynamic active icon (Single clean icon, no duplicates) -->
                <span class="flex items-center gap-1.5">
                    <span x-show="themeMode === 'light'" class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                        <span>สว่าง (Light)</span>
                    </span>
                    <span x-show="themeMode === 'dark'" x-cloak class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                        <span>มืด (Dark)</span>
                    </span>
                    <span x-show="themeMode === 'system'" x-cloak class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                        <span>ตามระบบ (System)</span>
                    </span>
                </span>

                <svg class="w-3 h-3 text-slate-400 transition-transform duration-150 shrink-0" :class="{ 'rotate-180': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-1.5 w-44 bg-white dark:bg-[#181a20] rounded-2xl shadow-xl dark:shadow-2xl border border-slate-200 dark:border-white/10 p-1.5 z-50 text-xs">
                
                <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider font-heading border-b border-slate-100 dark:border-white/[0.06] mb-1">
                    โหมดการแสดงผล (Theme)
                </div>

                <div class="space-y-0.5">
                    <!-- 1. Light Mode -->
                    <button type="button" 
                            @click="setTheme('light'); open = false" 
                            class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center justify-between text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 cursor-pointer transition" 
                            :class="{ 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/20': themeMode === 'light' }">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                            <span>สว่าง (Light)</span>
                        </div>
                        <svg x-show="themeMode === 'light'" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>

                    <!-- 2. Dark Mode -->
                    <button type="button" 
                            @click="setTheme('dark'); open = false" 
                            class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center justify-between text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 cursor-pointer transition" 
                            :class="{ 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/20': themeMode === 'dark' }">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                            <span>มืด (Dark)</span>
                        </div>
                        <svg x-show="themeMode === 'dark'" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>

                    <!-- 3. System Mode -->
                    <button type="button" 
                            @click="setTheme('system'); open = false" 
                            class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center justify-between text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 cursor-pointer transition" 
                            :class="{ 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/20': themeMode === 'system' }">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                            <span>ตามระบบ (System)</span>
                        </div>
                        <svg x-show="themeMode === 'system'" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Login Card Container -->
    <div class="w-full max-w-4xl bg-white dark:bg-[#161922] rounded-3xl shadow-2xl shadow-slate-300/40 dark:shadow-none border border-slate-200/80 dark:border-white/[0.08] overflow-hidden relative z-10 grid grid-cols-1 lg:grid-cols-12 transition-all">

        <!-- Left Column: Municipality Branding Showcase (5 Columns) -->
        <div class="lg:col-span-5 bg-gradient-to-br from-[#064e3b] via-[#047857] to-[#0f172a] p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
            <!-- Subtle Radial Dot Overlay -->
            <div class="absolute inset-0 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:18px_18px] opacity-15"></div>
            
            <div class="relative z-10 space-y-6">
                <!-- Municipality Emblem / Brand Dual-Logo -->
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-white p-1.5 shadow-md border border-white/20 flex items-center justify-center shrink-0">
                        <img src="<?= Router::url('/images/mobile-logo.webp') ?>" 
                             alt="โลโก้เทศบาล" 
                             class="w-full h-full object-contain"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="display:none;" class="w-full h-full rounded-xl bg-emerald-500 text-white items-center justify-center font-bold">
                            <i data-lucide="building-2" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <div class="h-12 px-2.5 rounded-2xl bg-white shadow-md border border-white/20 flex items-center justify-center shrink-0">
                        <img src="<?= Router::url('/images/kpth-logo.png') ?>" 
                             alt="กปท." 
                             class="h-7 w-auto object-contain">
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-heading font-bold text-sm sm:text-base tracking-wide text-white truncate">เทศบาลตำบล / เมือง</h2>
                        <span class="text-[11px] text-emerald-200/90 font-medium block truncate">ระบบติดตามและบริหารโครงการ</span>
                    </div>
                </div>

                <!-- Hero Tagline -->
                <div class="space-y-2 pt-2">
                    <h1 class="font-heading text-2xl sm:text-3xl font-bold leading-tight text-white">
                        ติดตามความก้าวหน้า <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-300 via-teal-200 to-cyan-200">
                            โปร่งใส คุ้มค่างบประมาณ
                        </span>
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-100/80 leading-relaxed pt-1 font-light">
                        ระบบสารสนเทศเพื่อการติดตามโครงการหลัก โครงการย่อย กิจกรรม และการเบิกจ่ายงบประมาณตามระเบียบกระทรวงมหาดไทย
                    </p>
                </div>

                <!-- Feature Highlights Pills -->
                <div class="space-y-2.5 pt-1">
                    <div class="flex items-center gap-2.5 text-xs text-emerald-100/90">
                        <div class="w-6 h-6 rounded-lg bg-emerald-500/25 border border-emerald-400/30 flex items-center justify-center text-emerald-300 shrink-0">
                            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>ลำดับชั้นโครงการหลักและโครงการย่อย (Hierarchy)</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs text-emerald-100/90">
                        <div class="w-6 h-6 rounded-lg bg-emerald-500/25 border border-emerald-400/30 flex items-center justify-center text-emerald-300 shrink-0">
                            <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>คำนวณ % ความก้าวหน้าอัตโนมัติตามกิจกรรม</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs text-emerald-100/90">
                        <div class="w-6 h-6 rounded-lg bg-emerald-500/25 border border-emerald-400/30 flex items-center justify-center text-emerald-300 shrink-0">
                            <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>ควบคุมเพดานงบประมาณและการเบิกจ่ายเงิน</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs text-emerald-100/90">
                        <div class="w-6 h-6 rounded-lg bg-emerald-500/25 border border-emerald-400/30 flex items-center justify-center text-emerald-300 shrink-0">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>ความปลอดภัยสูงสุดพร้อมระบบบันทึก Audit Log</span>
                    </div>
                </div>
            </div>

            <!-- Footer Badge -->
            <div class="relative z-10 pt-6 mt-6 border-t border-emerald-500/30 text-[11px] text-emerald-200/80 flex items-center justify-between font-mono">
                <span>MPT V2.0 (Official)</span>
                <span class="flex items-center gap-1.5 text-emerald-300 font-semibold font-sans">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    ระบบพร้อมใช้งาน
                </span>
            </div>
        </div>

        <!-- Right Column: Login Form & 1-Click Access (7 Columns) -->
        <div class="lg:col-span-7 p-6 sm:p-10 flex flex-col justify-between bg-white dark:bg-[#161922] transition-colors">
            <div class="space-y-5">

                <!-- Title & Header -->
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <i data-lucide="log-in" class="w-4 h-4"></i>
                            </div>
                            <h2 class="font-heading text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">เข้าสู่ระบบการทำงาน</h2>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 ml-10">กรุณาระบุบัญชีผู้ใช้งานเพื่อเข้าสู่ระบบงานตามบทบาทหน้าที่</p>
                    </div>
                </div>

                <!-- Flash Alert Messages -->
                <?php if ($error): ?>
                    <div class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 rounded-2xl text-xs text-rose-700 dark:text-rose-300 flex items-center gap-2.5 shadow-sm">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0"></i>
                        <span class="font-medium"><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 rounded-2xl text-xs text-emerald-700 dark:text-emerald-300 flex items-center gap-2.5 shadow-sm">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                        <span class="font-medium"><?= htmlspecialchars($success) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form action="<?= Router::url('/login') ?>" method="POST" class="space-y-4">
                    <?= Session::csrfField() ?>

                    <!-- Email Field -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            อีเมลผู้ใช้งาน (Email)
                        </label>
                        <div class="relative">
                            <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            <input type="email" 
                                   name="email" 
                                   x-model="email" 
                                   required 
                                   placeholder="admin@municipality.go.th"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-[#12141a] border border-slate-200 dark:border-white/10 rounded-xl text-sm text-slate-900 dark:text-white font-mono focus:bg-white dark:focus:bg-[#161922] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                รหัสผ่าน (Password)
                            </label>
                            <span class="text-[11px] text-slate-400 dark:text-slate-500 font-mono" x-text="selectedRole === 'executive' ? 'รหัส: 12345678' : 'รหัส: password'"></span>
                        </div>
                        <div class="relative">
                            <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            <input :type="showPassword ? 'text' : 'password'" 
                                   name="password" 
                                   x-model="password" 
                                   required
                                   class="w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-[#12141a] border border-slate-200 dark:border-white/10 rounded-xl text-sm text-slate-900 dark:text-white font-mono focus:bg-white dark:focus:bg-[#161922] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none">
                            <button type="button" 
                                    @click="showPassword = !showPassword; $nextTick(() => safeIcons())" 
                                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 absolute right-3.5 top-1/2 -translate-y-1/2 p-0.5 cursor-pointer"
                                    :title="showPassword ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน'">
                                <i x-show="!showPassword" data-lucide="eye" class="w-4 h-4"></i>
                                <i x-show="showPassword" x-cloak data-lucide="eye-off" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full py-3 px-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-emerald-600/25 hover:shadow-lg hover:shadow-emerald-600/35 active:scale-[0.99] transition-all flex items-center justify-center gap-2 cursor-pointer mt-2">
                        <span>เข้าสู่ระบบปฏิบัติงาน</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <!-- 1-Click Quick Fill Demo Accounts -->
                <div class="pt-4 border-t border-slate-100 dark:border-white/[0.08] space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5 font-heading">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-emerald-500"></i>
                            เลือกเข้าสู่ระบบด่วนตามบทบาท (1-Click Login)
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <!-- Admin Quick Login -->
                        <button type="button" 
                                @click="fillUser('admin@municipality.go.th', 'admin', 'password')"
                                class="p-2.5 rounded-xl border text-left transition-all flex items-center justify-between group cursor-pointer"
                                :class="selectedRole === 'admin' 
                                    ? 'bg-purple-50/80 dark:bg-purple-950/30 border-purple-300 dark:border-purple-600/40 ring-1 ring-purple-400/30' 
                                    : 'bg-slate-50/70 dark:bg-white/[0.03] border-slate-200 dark:border-white/10 hover:bg-purple-50/50 dark:hover:bg-purple-950/20 hover:border-purple-200'">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                    AD
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-purple-600 dark:group-hover:text-purple-400">
                                        ผู้ดูแลระบบ (Admin)
                                    </div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 truncate font-mono">
                                        สิทธิ์เต็มทุกระบบ
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400 shrink-0 ml-1.5">
                                คลิกกรอก
                            </span>
                        </button>

                        <!-- Executive Quick Login -->
                        <button type="button" 
                                @click="fillUser('630108020008@dpu.ac.th', 'executive', '12345678')"
                                class="p-2.5 rounded-xl border text-left transition-all flex items-center justify-between group cursor-pointer"
                                :class="selectedRole === 'executive' 
                                    ? 'bg-amber-50/80 dark:bg-amber-950/30 border-amber-300 dark:border-amber-600/40 ring-1 ring-amber-400/30' 
                                    : 'bg-slate-50/70 dark:bg-white/[0.03] border-slate-200 dark:border-white/10 hover:bg-amber-50/50 dark:hover:bg-amber-950/20 hover:border-amber-200'">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                    EX
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-amber-600 dark:group-hover:text-amber-400">
                                        ผู้บริหาร (Executive)
                                    </div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 truncate font-mono">
                                        ดูอย่างเดียว (Read-Only)
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 shrink-0 ml-1.5">
                                คลิกกรอก
                            </span>
                        </button>
                    </div>
                </div>

            </div>

            <div class="text-center pt-6 text-[11px] text-slate-400 dark:text-slate-500 font-sans">
                ระบบสารสนเทศเทศบาล เพื่อการติดตามและประเมินผลโครงการ
            </div>
        </div>

    </div>

    <script>
        function loginPage() {
            return {
                email: 'admin@municipality.go.th',
                password: 'password',
                showPassword: false,
                selectedRole: 'admin',
                themeMode: localStorage.getItem('theme') || 'system',

                init() {
                    try {
                        const mq = window.matchMedia('(prefers-color-scheme: dark)');
                        const listener = (e) => {
                            if (this.themeMode === 'system') {
                                if (e.matches) {
                                    document.documentElement.classList.add('dark');
                                    document.documentElement.style.colorScheme = 'dark';
                                } else {
                                    document.documentElement.classList.remove('dark');
                                    document.documentElement.style.colorScheme = 'light';
                                }
                            }
                        };
                        if (mq.addEventListener) mq.addEventListener('change', listener);
                        else if (mq.addListener) mq.addListener(listener);
                    } catch (e) {}
                },

                fillUser(userEmail, role, pass = 'password') {
                    this.email = userEmail;
                    this.password = pass;
                    this.selectedRole = role;
                },

                setTheme(mode) {
                    this.themeMode = mode;
                    localStorage.setItem('theme', mode);
                    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    const isDark = (mode === 'dark' || (mode === 'system' && prefersDark));
                    if (isDark) {
                        document.documentElement.classList.add('dark');
                        document.documentElement.style.colorScheme = 'dark';
                    } else {
                        document.documentElement.classList.remove('dark');
                        document.documentElement.style.colorScheme = 'light';
                    }
                }
            };
        }

        function safeIcons() {
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                const unhandled = document.querySelectorAll('i[data-lucide]');
                if (unhandled.length > 0) {
                    lucide.createIcons();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', safeIcons);
        document.addEventListener('alpine:initialized', safeIcons);
    </script>
</body>
</html>
