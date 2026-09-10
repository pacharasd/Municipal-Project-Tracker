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
    
    <!-- Browser DevTools / Web-Vitals Suppression Guard -->
    <script>
        (function() {
            function isDevToolsError(err) {
                if (!err) return false;
                const str = String((err && (err.message || err.stack)) || err || '').toLowerCase();
                return str.includes('starttime') || str.includes('reportallchanges');
            }

            window.addEventListener('error', function(event) {
                if (isDevToolsError(event.message) || isDevToolsError(event.error)) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    return true;
                }
            }, true);

            window.addEventListener('unhandledrejection', function(event) {
                if (isDevToolsError(event.reason)) {
                    event.preventDefault();
                }
            });

            const origOnError = window.onerror;
            window.onerror = function(message, source, lineno, colno, error) {
                if (isDevToolsError(message) || isDevToolsError(error)) {
                    return true;
                }
                if (typeof origOnError === 'function') {
                    return origOnError.apply(this, arguments);
                }
                return false;
            };

            const origConsoleError = console.error;
            console.error = function(...args) {
                const text = args.map(a => (a && (a.message || a.stack)) ? (a.message + ' ' + (a.stack || '')) : String(a)).join(' ');
                if (isDevToolsError(text)) {
                    return;
                }
                origConsoleError.apply(console, args);
            };
        })();
    </script>

    <!-- Theme Detection & Anti-Flicker Script (Light / Dark / System) -->
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
                        canvas: '#0b0f19',
                        surface: '#161922',
                        surfaceLight: '#202430',
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
        
        /* Subtle glow background without tacky matrix dots */
        .ambient-glow {
            background-radial: radial-gradient(circle at 50% 0%, rgba(16, 185, 129, 0.08), transparent 70%);
        }
    </style>
</head>
<body class="min-h-screen bg-[#f8fafc] dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 flex flex-col justify-between transition-colors duration-200 relative selection:bg-emerald-500 selection:text-white"
      x-data="loginPage()">

    <!-- Subtle Premium Gradient Background Mesh -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[450px] bg-gradient-to-b from-emerald-500/10 via-teal-500/5 to-transparent dark:from-emerald-500/[0.07] dark:via-teal-500/[0.02] dark:to-transparent rounded-full blur-3xl"></div>
    </div>

    <!-- Top Navigation Bar -->
    <header class="w-full border-b border-slate-200/80 dark:border-white/[0.06] bg-white/70 dark:bg-[#0f121a]/70 backdrop-blur-md relative z-20 px-4 sm:px-8 py-3 transition-colors">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Left Branding -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white dark:bg-white/10 p-1 shadow-xs border border-slate-200/60 dark:border-white/10 flex items-center justify-center shrink-0">
                    <img src="<?= Router::url('/images/mobile-logo.webp') ?>" 
                         alt="โลโก้เทศบาล" 
                         class="w-full h-full object-contain">
                </div>
                <div class="hidden sm:block">
                    <div class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">
                        ระบบติดตามและบริหารโครงการเทศบาล
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">
                        คณะอนุกรรมการฝ่ายติดตามและประเมินผลโครงการ
                    </div>
                </div>
            </div>

            <!-- Right: Theme Switcher Pill -->
            <div class="relative shrink-0" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" 
                        @click="open = !open"
                        id="theme-dropdown-btn"
                        class="px-3 py-1.5 rounded-xl bg-white dark:bg-[#181a20] border border-slate-200 dark:border-white/10 shadow-xs text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5 transition-all flex items-center gap-2 cursor-pointer"
                        title="เลือกโหมดการแสดงผล (สว่าง / มืด / ตามระบบ)">
                    <span class="flex items-center gap-1.5">
                        <span x-show="themeMode === 'light'" class="inline-flex items-center gap-1.5">
                            <i data-lucide="sun" class="w-4 h-4 text-amber-500"></i>
                            <span class="hidden sm:inline font-sans">สว่าง (Light)</span>
                        </span>
                        <span x-show="themeMode === 'dark'" x-cloak class="inline-flex items-center gap-1.5">
                            <i data-lucide="moon" class="w-4 h-4 text-emerald-400"></i>
                            <span class="hidden sm:inline font-sans">มืด (Dark)</span>
                        </span>
                        <span x-show="themeMode === 'system'" x-cloak class="inline-flex items-center gap-1.5">
                            <i data-lucide="monitor" class="w-4 h-4 text-slate-400"></i>
                            <span class="hidden sm:inline font-sans">ตามระบบ (System)</span>
                        </span>
                    </span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-150 shrink-0" :class="{ 'rotate-180': open }"></i>
                </button>

                <!-- Theme Selector Dropdown Menu -->
                <div x-show="open" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 bg-white dark:bg-[#181a20] rounded-2xl shadow-xl dark:shadow-2xl border border-slate-200 dark:border-white/10 p-1.5 z-50 text-left">
                    
                    <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider font-heading border-b border-slate-100 dark:border-white/[0.06] mb-1">
                        โหมดการแสดงผล (Theme)
                    </div>

                    <div class="space-y-0.5">
                        <!-- 1. โหมดสว่าง (Light) -->
                        <button type="button" 
                                @click="setTheme('light'); open = false" 
                                class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center justify-between text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 cursor-pointer transition" 
                                :class="{ 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/20': themeMode === 'light' }">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="sun" class="w-4 h-4 text-amber-500"></i>
                                <span>สว่าง (Light)</span>
                            </div>
                            <i data-lucide="check" x-show="themeMode === 'light'" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                        </button>

                        <!-- 2. โหมดมืด (Dark) -->
                        <button type="button" 
                                @click="setTheme('dark'); open = false" 
                                class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center justify-between text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 cursor-pointer transition" 
                                :class="{ 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/20': themeMode === 'dark' }">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="moon" class="w-4 h-4 text-emerald-400"></i>
                                <span>มืด (Dark)</span>
                            </div>
                            <i data-lucide="check" x-show="themeMode === 'dark'" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                        </button>

                        <!-- 3. โหมดตามระบบ (System) -->
                        <button type="button" 
                                @click="setTheme('system'); open = false" 
                                class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center justify-between text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 cursor-pointer transition" 
                                :class="{ 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/20': themeMode === 'system' }">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="monitor" class="w-4 h-4 text-slate-400"></i>
                                <span>ตามระบบ (System)</span>
                            </div>
                            <i data-lucide="check" x-show="themeMode === 'system'" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Center Content Area -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 relative z-10 my-4 sm:my-8">
        <div class="w-full max-w-[460px] bg-white dark:bg-[#161922] rounded-3xl shadow-xl shadow-slate-200/50 dark:shadow-2xl dark:shadow-black/50 border border-slate-200/80 dark:border-white/[0.08] p-6 sm:p-9 transition-all">
            
            <!-- Card Header: Official Seal & Titles -->
            <div class="text-center mb-6">
                <!-- Dual Emblem Badges -->
                <div class="inline-flex items-center gap-2.5 p-2 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.06] mb-4 shadow-xs">
                    <img src="<?= Router::url('/images/mobile-logo.webp') ?>" 
                         alt="ตราสัญลักษณ์เทศบาล" 
                         class="w-10 h-10 object-contain">
                    <div class="h-6 w-px bg-slate-200 dark:bg-white/10"></div>
                    <img src="<?= Router::url('/images/kpth-logo.png') ?>" 
                         alt="กปท." 
                         class="h-7 w-auto object-contain">
                </div>

                <div class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40 mb-2 font-heading">
                    เทศบาลตำบล / เมือง • กองทุนหลักประกันสุขภาพ
                </div>

                <h1 class="font-heading text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                    เข้าสู่ระบบการทำงาน
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    ระบบติดตามและบริหารโครงการเทศบาล (MPT)
                </p>
            </div>

            <!-- Flash Alert: Error Message -->
            <?php if ($error): ?>
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 max-h-24"
                     x-transition:leave-end="opacity-0 max-h-0"
                     class="mb-5 p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 rounded-2xl text-xs text-rose-700 dark:text-rose-300 flex items-center justify-between gap-2.5 shadow-xs overflow-hidden">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0"></i>
                        <span class="font-medium truncate"><?= htmlspecialchars($error) ?></span>
                    </div>
                    <button type="button" @click="show = false" class="text-rose-400 hover:text-rose-600 dark:hover:text-rose-200 p-0.5 transition cursor-pointer shrink-0" title="ปิด">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Flash Alert: Success Message (Auto-Dismiss After 3.5s) -->
            <?php if ($success): ?>
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 3500)"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-250"
                     x-transition:leave-start="opacity-100 max-h-24"
                     x-transition:leave-end="opacity-0 max-h-0"
                     class="mb-5 p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 rounded-2xl text-xs text-emerald-700 dark:text-emerald-300 flex items-center justify-between gap-2.5 shadow-xs overflow-hidden">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                        <span class="font-medium truncate"><?= htmlspecialchars($success) ?></span>
                    </div>
                    <button type="button" @click="show = false" class="text-emerald-500/70 hover:text-emerald-700 dark:hover:text-emerald-300 p-0.5 transition cursor-pointer shrink-0" title="ปิด">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- 1-Click Role Quick Switcher (Clear & Elegant Tabs) -->
            <div class="mb-5">
                <!-- 2-Option Segmented Tab Switcher -->
                <div class="p-1 bg-slate-100 dark:bg-white/[0.04] rounded-2xl flex items-center gap-1 border border-slate-200/60 dark:border-white/[0.06]">
                    <!-- Admin Tab -->
                    <button type="button" 
                            @click="fillUser('admin@municipality.go.th', 'admin', 'password')"
                            class="flex-1 py-2 px-3 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition cursor-pointer"
                            :class="selectedRole === 'admin' 
                                ? 'bg-white dark:bg-[#1f2330] text-purple-700 dark:text-purple-300 shadow-sm font-bold border border-slate-200/80 dark:border-white/10' 
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                        <span class="w-2 h-2 rounded-full" :class="selectedRole === 'admin' ? 'bg-purple-600' : 'bg-slate-400'"></span>
                        <span>ผู้ดูแลระบบ (Admin)</span>
                    </button>

                    <!-- Executive Tab -->
                    <button type="button" 
                            @click="fillUser('executive@municipality.go.th', 'executive', 'password')"
                            class="flex-1 py-2 px-3 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition cursor-pointer"
                            :class="selectedRole === 'executive' 
                                ? 'bg-white dark:bg-[#1f2330] text-amber-700 dark:text-amber-300 shadow-sm font-bold border border-slate-200/80 dark:border-white/10' 
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                        <span class="w-2 h-2 rounded-full" :class="selectedRole === 'executive' ? 'bg-amber-500' : 'bg-slate-400'"></span>
                        <span>ผู้บริหาร (Executive)</span>
                    </button>
                </div>
            </div>

            <!-- Login Form -->
            <form action="<?= Router::url('/login') ?>" method="POST" class="space-y-4">
                <?= Session::csrfField() ?>

                <!-- Email Field -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        อีเมลผู้ใช้งาน (Email) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <input type="email" 
                               name="email" 
                               x-model="email" 
                               required 
                               placeholder="admin@municipality.go.th"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-[#11141c] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white font-mono focus:bg-white dark:focus:bg-[#161922] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            รหัสผ่าน (Password) <span class="text-rose-500">*</span>
                        </label>
                    </div>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <input :type="showPassword ? 'text' : 'password'" 
                               name="password" 
                               x-model="password" 
                               required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-[#11141c] border border-slate-200 dark:border-white/10 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white font-mono focus:bg-white dark:focus:bg-[#161922] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none">
                        
                        <!-- Toggle Password Visibility -->
                        <button type="button" 
                                @click="showPassword = !showPassword; $nextTick(() => safeIcons())" 
                                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 absolute right-3.5 top-1/2 -translate-y-1/2 p-0.5 cursor-pointer"
                                :title="showPassword ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน'"
                                tabindex="-1">
                            <i x-show="!showPassword" data-lucide="eye" class="w-4 h-4"></i>
                            <i x-show="showPassword" x-cloak data-lucide="eye-off" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold rounded-xl text-sm shadow-md shadow-emerald-600/20 active:scale-[0.99] transition-all flex items-center justify-center gap-2 cursor-pointer mt-5">
                    <span>เข้าสู่ระบบปฏิบัติงาน</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>

            <!-- Card Footer: Security Reassurance -->
            <div class="mt-6 pt-5 border-t border-slate-100 dark:border-white/[0.06] text-center">
                <div class="flex items-center justify-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                    <span>ระบบรักษาความปลอดภัยสารสนเทศภาครัฐ (Bcrypt & RBAC)</span>
                </div>
            </div>

        </div>
    </main>

    <!-- Page Footer -->
    <footer class="w-full border-t border-slate-200/80 dark:border-white/[0.06] bg-white/50 dark:bg-[#0f121a]/50 py-3 px-4 text-center relative z-20 text-xs text-slate-400 dark:text-slate-500">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                เทศบาลตำบล / เมือง • ระบบติดตามและบริหารโครงการ (Municipal Project Tracker)
            </div>
            <div class="flex items-center gap-3 font-mono text-[11px]">
                <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-sans font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    ระบบพร้อมใช้งาน
                </span>
                <span>v2.0 Official</span>
            </div>
        </div>
    </footer>

    <!-- Alpine.js & Utility Logic -->
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
                    this.$nextTick(() => safeIcons());
                }
            };
        }

        function safeIcons() {
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        }

        document.addEventListener('DOMContentLoaded', safeIcons);
        document.addEventListener('alpine:initialized', safeIcons);
    </script>
</body>
</html>
