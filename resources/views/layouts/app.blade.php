<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= htmlspecialchars($title ?? 'ระบบติดตามและบริหารโครงการเทศบาล') ?> - เทศบาลตำบล/เมือง</title>
    <link rel="icon" type="image/webp" href="<?= \App\Core\Router::url('/images/mobile-logo.webp') ?>">
    
    <!-- Theme Detection & Anti-Flicker Script (Standard 3-State Tailwind Pattern: Light / Dark / System) -->
    <script>
        (function() {
            try {
                const theme = localStorage.getItem('theme') || 'system';
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (theme === 'dark' || (theme === 'system' && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>

    <!-- Google Fonts: Prompt & Sarabun -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (STRICTLY NO Bootstrap) -->
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
                        // Deep obsidian canvas & elevated dark cards
                        canvas: '#0f1014',
                        surface: '#181a20',
                        surfaceLight: '#22252e',
                        borderDark: 'rgba(255, 255, 255, 0.08)',
                        
                        // Neon Accents from Reference Image
                        neon: {
                            green: '#10b981',   // Electric Emerald
                            mint: '#0df5c4',    // Cyber Mint
                            yellow: '#ffd166',  // Sunshine Yellow
                            pink: '#f472b6',    // Neon Pink
                            purple: '#c084fc',  // Pastel Purple
                            cyan: '#38bdf8',    // Cyan Sky
                            orange: '#fb923c',  // Sunset Orange
                        },
                        // Overriding emerald and teal with vibrant neon green
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',  // Main vibrant neon green
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
                    },
                    boxShadow: {
                        'neon-green': '0 0 20px rgba(16, 185, 129, 0.35)',
                        'neon-yellow': '0 0 20px rgba(255, 209, 102, 0.35)',
                        'neon-pink': '0 0 20px rgba(244, 114, 182, 0.35)',
                        'dark-card': '0 10px 30px -5px rgba(0, 0, 0, 0.5), 0 4px 10px -3px rgba(0, 0, 0, 0.3)',
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js & Lucide Icons -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Base / Light Mode Defaults */
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Prompt', sans-serif;
            color: #0f172a;
        }
        [x-cloak] { display: none !important; }

        /* Form focus transition only (prevent desync during theme switch) */
        input:focus, select:focus, textarea:focus {
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        /* Light table rows hover */
        tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Form Inputs & Selects (Light) */
        input:not([type="checkbox"]):not([type="radio"]), select, textarea {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            color: #0f172a;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        /* Light Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #10b981;
        }

        /* ========================================================= */
        /* Dark Theme Overrides (Standard Web Adaptation)            */
        /* ========================================================= */
        html.dark body {
            background-color: #0f1014;
            color: #f1f5f9;
        }
        html.dark h1, html.dark h2, html.dark h3, html.dark h4, html.dark h5, html.dark h6, html.dark .font-heading {
            color: #ffffff;
        }

        /* Dark Card & Surface Adaptation (Non-destructive to rounded utilities) */
        html.dark .card-dark, html.dark .card-soft {
            background-color: #181a20 !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5) !important;
            color: #f1f5f9;
        }
        html.dark .bg-white {
            background-color: #181a20 !important;
            color: #f1f5f9;
        }

        /* Dark mode text hierarchy for templates using Tailwind slate */
        html.dark .text-slate-900, html.dark .text-slate-800 {
            color: #ffffff !important;
        }
        html.dark .text-slate-700 {
            color: #e2e8f0 !important;
        }
        html.dark .text-slate-600 {
            color: #cbd5e1 !important;
        }
        html.dark .text-slate-500, html.dark .text-slate-400 {
            color: #94a3b8 !important;
        }

        /* Background and border overrides in Dark */
        html.dark .bg-slate-50, html.dark .bg-slate-100 {
            background-color: #121318 !important;
        }
        html.dark [class*="bg-slate-50/"], html.dark [class*="bg-slate-100/"] {
            background-color: #12141a !important;
        }
        html.dark .bg-slate-200 {
            background-color: rgba(255, 255, 255, 0.08) !important;
        }
        html.dark .border-slate-100, html.dark .border-slate-200, html.dark .border-slate-300 {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        /* Dark table rows hover */
        html.dark tr:hover {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        /* Form Inputs & Selects Dark */
        html.dark input:not([type="checkbox"]):not([type="radio"]), html.dark select, html.dark textarea {
            background-color: #12141a !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
        }
        html.dark input:focus, html.dark select:focus, html.dark textarea:focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.3) !important;
        }

        /* Dark Scrollbar Styling */
        html.dark ::-webkit-scrollbar-track {
            background: #0f1014;
        }
        html.dark ::-webkit-scrollbar-thumb {
            background: #272a34;
            border-radius: 3px;
        }
        html.dark ::-webkit-scrollbar-thumb:hover {
            background: #10b981;
        }

        /* Strict Anti-Overflow & Responsive Viewport Rules */
        html, body {
            overflow-x: hidden !important;
            max-width: 100vw !important;
            width: 100% !important;
            box-sizing: border-box;
        }
        *, *::before, *::after {
            box-sizing: border-box;
        }
        #main-content {
            max-width: 100% !important;
            overflow-x: hidden !important;
        }
        #main-content table,
        #main-content table * {
            max-width: none !important;
        }
    </style>
</head>
<body class="h-full antialiased text-slate-800 dark:text-slate-100 bg-[#f8fafc] dark:bg-[#0f1014] flex flex-col transition-colors duration-150 w-full max-w-full overflow-x-hidden" x-data="{ sidebarOpen: false }">

    <!-- SPA Top Progress Bar (Neon Green Glow) -->
    <div id="spa-progress" class="fixed top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 via-teal-300 to-green-500 shadow-[0_0_12px_#10b981] z-[9999] transition-all duration-200 pointer-events-none opacity-0" style="width: 0%;"></div>

    <!-- Top Navigation Bar -->
    <header class="bg-white/95 dark:bg-[#101115]/95 border-b border-slate-200 dark:border-white/[0.08] sticky top-0 z-30 shadow-sm dark:shadow-md backdrop-blur-md transition-colors duration-150 w-full max-w-full">
        <div class="px-2.5 sm:px-6 lg:px-8 flex items-center justify-between h-14 sm:h-16 w-full max-w-full">
            <!-- Left Logo & Title -->
            <div class="flex items-center gap-1.5 sm:gap-3 min-w-0 flex-1 sm:flex-initial">
                <button type="button" @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-1.5 sm:p-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 transition shrink-0" aria-label="เปิดเมนู">
                    <i data-lucide="menu" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </button>
                <a href="<?= \App\Core\Router::url('/dashboard') ?>" class="shrink-0 flex items-center group" title="ระบบติดตามและบริหารโครงการเทศบาล">
                    <img src="<?= \App\Core\Router::url('/images/mobile-logo.webp') ?>" 
                         alt="โลโก้เทศบาล" 
                         class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl object-contain shrink-0 shadow-sm border border-slate-200/80 dark:border-white/10 bg-white dark:bg-white/5 p-0.5 group-hover:scale-105 transition-transform"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div style="display:none;" class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 items-center justify-center text-slate-950 font-bold shadow-neon-green shrink-0">
                        <i data-lucide="activity" class="w-4 h-4 sm:w-6 sm:h-6"></i>
                    </div>
                </a>
                <div class="min-w-0">
                    <a href="<?= \App\Core\Router::url('/dashboard') ?>" class="font-bold font-heading text-slate-900 dark:text-white tracking-tight flex items-center gap-1 sm:gap-2">
                        <span class="hidden md:inline text-base sm:text-lg whitespace-nowrap">ระบบติดตามและบริหารโครงการเทศบาล</span>
                        <span class="md:hidden text-xs sm:text-sm font-bold truncate max-w-[105px] xs:max-w-[160px] whitespace-nowrap">ติดตามโครงการ</span>
                        <span class="hidden sm:inline-block text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-500/15 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 dark:border-emerald-500/40 font-sans shrink-0">ปี 2568</span>
                    </a>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 hidden lg:block font-sans truncate">Municipal Project Tracking & Performance Management System</p>
                </div>
            </div>

            <!-- Right: Theme Switcher, Role Switcher & User Profile -->
            <div class="flex items-center gap-1 sm:gap-2 shrink-0">

                <!-- Standard 3-State Theme Switcher (Light / Dark / System) -->
                <div class="relative shrink-0" x-data="{ 
                    open: false, 
                    mode: localStorage.getItem('theme') || 'system',
                    resolvedDark: document.documentElement.classList.contains('dark')
                }" @theme-changed.window="mode = $event.detail.mode || localStorage.getItem('theme') || 'system'; resolvedDark = document.documentElement.classList.contains('dark')">
                    
                    <button type="button" 
                            @click="open = !open" 
                            id="theme-dropdown-btn"
                            class="flex items-center gap-1 sm:gap-1.5 p-1.5 sm:px-2.5 sm:py-1.5 rounded-xl bg-slate-100 dark:bg-[#181a20] text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-white/[0.08] text-xs font-semibold hover:bg-slate-200/80 dark:hover:bg-white/5 transition cursor-pointer"
                            title="เลือกโหมดการแสดงผล (สว่าง / มืด / ตามระบบ)">
                        
                        <!-- Dynamic active icon -->
                        <span class="flex items-center gap-1.5">
                            <span x-show="mode === 'light'" x-cloak class="inline-flex items-center gap-1.5">
                                <i data-lucide="sun" class="w-4 h-4 text-amber-500"></i>
                                <span class="hidden md:inline text-[11px] font-sans">สว่าง</span>
                            </span>
                            <span x-show="mode === 'dark'" x-cloak class="inline-flex items-center gap-1.5">
                                <i data-lucide="moon" class="w-4 h-4 text-emerald-400"></i>
                                <span class="hidden md:inline text-[11px] font-sans">มืด</span>
                            </span>
                            <span x-show="mode === 'system'" x-cloak class="inline-flex items-center gap-1.5">
                                <i data-lucide="monitor" class="w-4 h-4 text-slate-500 dark:text-slate-400"></i>
                                <span class="hidden md:inline text-[11px] font-sans">ตามระบบ</span>
                            </span>
                        </span>

                        <i data-lucide="chevron-down" class="w-3 h-3 sm:w-3.5 sm:h-3.5 opacity-60 ml-0.5 transition-transform duration-150 hidden xs:inline-block" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <!-- Theme Selector Dropdown Menu -->
                    <div x-show="open" 
                         @click.outside="open = false" 
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
                                    @click="setAppTheme('light'); open = false" 
                                    class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center justify-between transition cursor-pointer hover:bg-slate-100 dark:hover:bg-white/5 text-slate-700 dark:text-slate-200"
                                    :class="{ 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/20': mode === 'light' }">
                                <div class="flex items-center gap-2.5">
                                    <i data-lucide="sun" class="w-4 h-4 text-amber-500"></i>
                                    <span>สว่าง (Light)</span>
                                </div>
                                <i data-lucide="check" x-show="mode === 'light'" x-cloak class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                            </button>

                            <!-- 2. โหมดมืด (Dark) -->
                            <button type="button" 
                                    @click="setAppTheme('dark'); open = false" 
                                    class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center justify-between transition cursor-pointer hover:bg-slate-100 dark:hover:bg-white/5 text-slate-700 dark:text-slate-200"
                                    :class="{ 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/20': mode === 'dark' }">
                                <div class="flex items-center gap-2.5">
                                    <i data-lucide="moon" class="w-4 h-4 text-emerald-400"></i>
                                    <span>มืด (Dark)</span>
                                </div>
                                <i data-lucide="check" x-show="mode === 'dark'" x-cloak class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                            </button>

                            <!-- 3. โหมดตามระบบ (System) -->
                            <button type="button" 
                                    @click="setAppTheme('system'); open = false" 
                                    class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center justify-between transition cursor-pointer hover:bg-slate-100 dark:hover:bg-white/5 text-slate-700 dark:text-slate-200"
                                    :class="{ 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/20': mode === 'system' }">
                                <div class="flex items-center gap-2.5">
                                    <i data-lucide="monitor" class="w-4 h-4 text-slate-400"></i>
                                    <span>ตามระบบ (System)</span>
                                </div>
                                <i data-lucide="check" x-show="mode === 'system'" x-cloak class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <?php
                $userRole = $currentUser['role_name'] ?? 'admin';
                $roleMeta = [
                    'admin'     => ['label' => 'ผู้ดูแลระบบ (Admin)', 'bg' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800/60', 'icon' => 'shield-check'],
                    'executive' => ['label' => 'ผู้บริหาร (Executive)', 'bg' => 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800/60', 'icon' => 'briefcase'],
                ];
                $currentRoleInfo = $roleMeta[$userRole] ?? ['label' => $currentUser['role_label'] ?? 'ผู้ใช้งาน', 'bg' => 'bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700', 'icon' => 'user'];
                $switchDemoUsers = \App\Core\Database::query("SELECT u.id, u.name, r.name as role_name, r.display_name as role_label FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id ASC LIMIT 5");
                ?>

                <!-- Dynamic Role Badge & Quick Switcher Dropdown (Hidden on mobile <640px) -->
                <div class="relative hidden sm:block shrink-0" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="flex items-center gap-1.5 bg-slate-100 dark:bg-[#181a20] text-slate-700 dark:text-slate-200 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/[0.08] text-xs font-bold hover:bg-slate-200 dark:hover:bg-white/5 transition cursor-pointer" title="คลิกเพื่อสลับบทบาททดสอบระบบ">
                        <i data-lucide="<?= $currentRoleInfo['icon'] ?>" class="w-4 h-4 text-emerald-500 dark:text-emerald-400"></i>
                        <span><?= htmlspecialchars($currentRoleInfo['label']) ?></span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-60"></i>
                    </button>

                    <!-- Role Switcher Dropdown Menu -->
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white dark:bg-[#181a20] rounded-2xl shadow-xl dark:shadow-2xl border border-slate-200 dark:border-white/10 p-2 z-50 text-left">
                        <div class="px-3 py-2 border-b border-slate-100 dark:border-white/[0.08] text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider font-heading">
                            สลับบทบาททดสอบระบบ (RBAC)
                        </div>
                        <div class="py-1 space-y-1">
                            <?php foreach ($switchDemoUsers as $su): ?>
                                <form action="<?= \App\Core\Router::url('/auth/switch') ?>" method="POST">
                                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="user_id" value="<?= $su['id'] ?>">
                                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">
                                    <button type="submit" class="w-full text-left px-3 py-2 rounded-xl text-xs hover:bg-slate-100 dark:hover:bg-white/5 flex items-center justify-between transition cursor-pointer <?= ($currentUser['id'] == $su['id']) ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-500/30' : 'text-slate-700 dark:text-slate-300' ?>">
                                        <div>
                                            <div class="font-medium text-slate-900 dark:text-white"><?= htmlspecialchars($su['name']) ?></div>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400"><?= htmlspecialchars($su['role_label']) ?></div>
                                        </div>
                                        <?php if ($currentUser['id'] == $su['id']): ?>
                                             <i data-lucide="check" class="w-4 h-4 text-emerald-500 dark:text-emerald-400"></i>
                                        <?php endif; ?>
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Current User Avatar -->
                <div class="flex items-center gap-1.5 sm:gap-2 pl-1 sm:pl-2 border-l border-slate-200/80 dark:border-white/[0.08] shrink-0">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 flex items-center justify-center font-extrabold text-xs shadow-neon-green ring-1 sm:ring-2 ring-emerald-400/40 shrink-0">
                        <?= mb_substr($currentUser['name'] ?? 'U', 0, 1) ?>
                    </div>
                    <div class="hidden lg:block text-left">
                        <div class="text-xs font-bold text-slate-900 dark:text-white truncate max-w-[130px]"><?= htmlspecialchars($currentUser['name'] ?? 'ผู้ใช้งาน') ?></div>
                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold"><?= htmlspecialchars($currentUser['role_label'] ?? 'เจ้าหน้าที่') ?></div>
                    </div>
                </div>

                <!-- Logout Form -->
                <form action="<?= \App\Core\Router::url('/logout') ?>" method="POST" class="shrink-0 flex items-center">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <button type="submit" title="ออกจากระบบ" class="p-1.5 sm:p-2 text-slate-500 dark:text-slate-400 hover:text-rose-500 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-white/5 rounded-xl transition-colors shrink-0 cursor-pointer">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Global Floating Toast Notification (Light & Dark Mode Compatible) -->
    <div class="fixed top-20 right-4 sm:right-8 z-50 max-w-md w-[calc(100%-2rem)] pointer-events-none space-y-3">
        <?php if (!empty($flashSuccess)): ?>
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-cloak
                 x-init="setTimeout(() => show = false, 4500)"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 -translate-y-2 sm:translate-y-0 sm:translate-x-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="pointer-events-auto p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#181a20] border border-emerald-500/30 dark:border-emerald-500/40 shadow-xl shadow-emerald-500/10 dark:shadow-2xl flex items-center gap-3 text-slate-800 dark:text-slate-100 backdrop-blur-md">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-200/60 dark:border-emerald-500/20">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
                <div class="text-xs sm:text-sm font-semibold flex-1 leading-snug">
                    <?= htmlspecialchars($flashSuccess) ?>
                </div>
                <button type="button" @click="show = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white transition shrink-0 cursor-pointer" aria-label="ปิดการแจ้งเตือน">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-cloak
                 x-init="setTimeout(() => show = false, 5000)"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 -translate-y-2 sm:translate-y-0 sm:translate-x-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="pointer-events-auto p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#181a20] border border-rose-500/30 dark:border-rose-500/40 shadow-xl shadow-rose-500/10 dark:shadow-2xl flex items-center gap-3 text-slate-800 dark:text-slate-100 backdrop-blur-md">
                <div class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 border border-rose-200/60 dark:border-rose-500/20">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <div class="text-xs sm:text-sm font-semibold flex-1 leading-snug">
                    <?= htmlspecialchars($flashError) ?>
                </div>
                <button type="button" @click="show = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white transition shrink-0 cursor-pointer" aria-label="ปิดการแจ้งเตือน">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Container Layout -->
    <div class="flex-1 flex overflow-hidden w-full max-w-full min-w-0">
        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-40 w-64 bg-white dark:bg-[#0b0c0f] border-r border-slate-200 dark:border-white/[0.08] pt-16 lg:pt-0 transform lg:translate-x-0 lg:static transition-all duration-200 ease-in-out flex flex-col justify-between shadow-lg dark:shadow-2xl lg:shadow-none">
            <div id="sidebar-nav-items" class="p-4 space-y-2 overflow-y-auto">
                <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 font-heading">
                    MENU
                </div>
                
                <!-- แดชบอร์ดภาพรวม -->
                <?php $isDashboard = str_contains($_SERVER['REQUEST_URI'], '/dashboard'); ?>
                <a href="<?= \App\Core\Router::url('/dashboard') ?>" 
                   class="flex items-center justify-between px-4 py-3 rounded-2xl text-sm font-medium transition-all <?= $isDashboard ? 'bg-emerald-50 dark:bg-[#181c26] text-emerald-900 dark:text-white font-semibold border border-emerald-500/30 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent' ?>">
                    <div class="flex items-center gap-3">
                        <i data-lucide="layout-dashboard" class="w-5 h-5 <?= $isDashboard ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"></i>
                        <span>แดชบอร์ดภาพรวม</span>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 <?= $isDashboard ? 'text-emerald-600 dark:text-emerald-400' : 'opacity-40 text-slate-400 dark:text-slate-500' ?>"></i>
                </a>

                <!-- โครงการหลัก & ย่อย -->
                <?php $isProjects = (str_contains($_SERVER['REQUEST_URI'], '/projects') || str_contains($_SERVER['REQUEST_URI'], '/sub-projects')); ?>
                <a href="<?= \App\Core\Router::url('/projects') ?>" 
                   class="flex items-center justify-between px-4 py-3 rounded-2xl text-sm font-medium transition-all <?= $isProjects ? 'bg-emerald-50 dark:bg-[#181c26] text-emerald-900 dark:text-white font-semibold border border-emerald-500/30 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent' ?>">
                    <div class="flex items-center gap-3">
                        <i data-lucide="folder-kanban" class="w-5 h-5 <?= $isProjects ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"></i>
                        <span>โครงการหลัก & ย่อย</span>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 <?= $isProjects ? 'text-emerald-600 dark:text-emerald-400' : 'opacity-40 text-slate-400 dark:text-slate-500' ?>"></i>
                </a>

                <!-- งบประมาณ & เบิกจ่าย -->
                <?php $isBudgets = str_contains($_SERVER['REQUEST_URI'], '/budgets'); ?>
                <a href="<?= \App\Core\Router::url('/budgets') ?>" 
                   class="flex items-center justify-between px-4 py-3 rounded-2xl text-sm font-medium transition-all <?= $isBudgets ? 'bg-emerald-50 dark:bg-[#181c26] text-emerald-900 dark:text-white font-semibold border border-emerald-500/30 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent' ?>">
                    <div class="flex items-center gap-3">
                        <i data-lucide="wallet" class="w-5 h-5 <?= $isBudgets ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"></i>
                        <span>งบประมาณ & เบิกจ่าย</span>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 <?= $isBudgets ? 'text-emerald-600 dark:text-emerald-400' : 'opacity-40 text-slate-400 dark:text-slate-500' ?>"></i>
                </a>

                <!-- รายงาน & ส่งออกข้อมูล -->
                <?php $isReports = str_contains($_SERVER['REQUEST_URI'], '/reports'); ?>
                <a href="<?= \App\Core\Router::url('/reports') ?>" 
                   class="flex items-center justify-between px-4 py-3 rounded-2xl text-sm font-medium transition-all <?= $isReports ? 'bg-emerald-50 dark:bg-[#181c26] text-emerald-900 dark:text-white font-semibold border border-emerald-500/30 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent' ?>">
                    <div class="flex items-center gap-3">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5 <?= $isReports ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"></i>
                        <span>รายงาน & ส่งออกข้อมูล</span>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 <?= $isReports ? 'text-emerald-600 dark:text-emerald-400' : 'opacity-40 text-slate-400 dark:text-slate-500' ?>"></i>
                </a>

                <div class="pt-4 px-3 py-2 text-[11px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 font-heading">
                    ADMINISTRATION
                </div>

                <!-- ผู้ใช้งาน & สิทธิ์ -->
                <?php $isUsers = str_contains($_SERVER['REQUEST_URI'], '/users'); ?>
                <a href="<?= \App\Core\Router::url('/users') ?>" 
                   class="flex items-center justify-between px-4 py-3 rounded-2xl text-sm font-medium transition-all <?= $isUsers ? 'bg-emerald-50 dark:bg-[#181c26] text-emerald-900 dark:text-white font-semibold border border-emerald-500/30 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent' ?>">
                    <div class="flex items-center gap-3">
                        <i data-lucide="users" class="w-5 h-5 <?= $isUsers ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"></i>
                        <span>ผู้ใช้งาน & บทบาท</span>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 <?= $isUsers ? 'text-emerald-600 dark:text-emerald-400' : 'opacity-40 text-slate-400 dark:text-slate-500' ?>"></i>
                </a>

                <!-- Audit Log -->
                <?php $isAudit = str_contains($_SERVER['REQUEST_URI'], '/audit-logs'); ?>
                <a href="<?= \App\Core\Router::url('/audit-logs') ?>" 
                   class="flex items-center justify-between px-4 py-3 rounded-2xl text-sm font-medium transition-all <?= $isAudit ? 'bg-emerald-50 dark:bg-[#181c26] text-emerald-900 dark:text-white font-semibold border border-emerald-500/30 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent' ?>">
                    <div class="flex items-center gap-3">
                        <i data-lucide="history" class="w-5 h-5 <?= $isAudit ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"></i>
                        <span>ประวัติการทำงาน</span>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 <?= $isAudit ? 'text-emerald-600 dark:text-emerald-400' : 'opacity-40 text-slate-400 dark:text-slate-500' ?>"></i>
                </a>
            </div>

            <!-- Bottom Icons -->
            <div class="p-4 border-t border-slate-200 dark:border-white/[0.08] flex items-center justify-between text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-2">
                    <button type="button" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-[#181a20] border border-slate-200 dark:border-white/[0.08] flex items-center justify-center hover:text-emerald-600 dark:hover:text-emerald-400 transition" title="ตั้งค่าระบบ">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                    </button>
                    <button type="button" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-[#181a20] border border-slate-200 dark:border-white/[0.08] flex items-center justify-center hover:text-emerald-600 dark:hover:text-emerald-400 transition" title="ช่วยเหลือ">
                        <i data-lucide="help-circle" class="w-4 h-4"></i>
                    </button>
                </div>
                <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400/80 font-mono">MPT V2.0</span>
            </div>
        </aside>

        <!-- Backdrop for mobile sidebar -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden"></div>

        <!-- Main Content Area -->
        <main id="main-content" class="flex-1 min-w-0 w-full max-w-full overflow-y-auto overflow-x-hidden p-3.5 sm:p-6 lg:p-8 bg-[#f8fafc] dark:bg-[#0f1014]">
            <!-- View Specific Content -->
            <?= $content ?? '' ?>
        </main>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        function safeCreateIcons() {
            if (window.lucide && typeof lucide.createIcons === 'function') {
                const unhandled = document.querySelectorAll('i[data-lucide]');
                if (unhandled.length > 0) {
                    lucide.createIcons();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', safeCreateIcons);
        document.addEventListener('alpine:initialized', safeCreateIcons);
        window.addEventListener('load', safeCreateIcons);

        // Only observe when actual <i> tags with data-lucide are added (e.g. dynamic modals), avoiding infinite loops on <svg>
        let lucideDebounce = null;
        const observer = new MutationObserver((mutations) => {
            let hasNewIcons = false;
            for (const m of mutations) {
                for (const node of m.addedNodes) {
                    if (node.nodeType === 1) {
                        if (node.tagName === 'I' && node.hasAttribute('data-lucide')) {
                            hasNewIcons = true;
                            break;
                        }
                        if (node.querySelector && node.querySelector('i[data-lucide]')) {
                            hasNewIcons = true;
                            break;
                        }
                    }
                }
                if (hasNewIcons) break;
            }
            if (hasNewIcons) {
                clearTimeout(lucideDebounce);
                lucideDebounce = setTimeout(safeCreateIcons, 30);
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    </script>

    <!-- Seamless SPA Navigation Router (Persistent Sidebar & Header) -->
    <script>
        window.AppSPA = {
            isNavigating: false,
            progressEl: null,
            progressTimer: null,

            startProgress() {
                if (!this.progressEl) this.progressEl = document.getElementById('spa-progress');
                if (!this.progressEl) return;
                clearTimeout(this.progressTimer);
                this.progressEl.style.transition = 'width 300ms ease, opacity 150ms ease';
                this.progressEl.style.opacity = '1';
                this.progressEl.style.width = '35%';
                this.progressTimer = setTimeout(() => {
                    if (this.progressEl && this.isNavigating) {
                        this.progressEl.style.width = '75%';
                    }
                }, 200);
            },

            finishProgress() {
                if (!this.progressEl) return;
                clearTimeout(this.progressTimer);
                this.progressEl.style.width = '100%';
                setTimeout(() => {
                    if (this.progressEl) {
                        this.progressEl.style.opacity = '0';
                        setTimeout(() => {
                            if (this.progressEl) {
                                this.progressEl.style.transition = 'none';
                                this.progressEl.style.width = '0%';
                            }
                        }, 250);
                    }
                }, 150);
            },

            async navigate(url, pushState = true) {
                if (this.isNavigating) return;
                this.isNavigating = true;
                this.startProgress();

                try {
                    const res = await fetch(url, {
                        cache: 'no-store',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html,application/xhtml+xml,application/xml'
                        }
                    });

                    if (!res.ok) {
                        window.location.href = url;
                        return;
                    }
                    if (res.redirected && res.url && !res.url.includes('/Municipal_Project_Tracker/public/')) {
                        window.location.href = res.url;
                        return;
                    }

                    const html = await res.text();
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, 'text/html');

                    const newMain = newDoc.querySelector('#main-content');
                    if (!newMain) {
                        window.location.href = url;
                        return;
                    }

                    // 1. Update Title
                    if (newDoc.title) {
                        document.title = newDoc.title;
                    }

                    // 2. Update Sidebar Active Links (Keeps Sidebar Mounted Without Reloading)
                    const newSidebar = newDoc.querySelector('#sidebar-nav-items');
                    const currentSidebar = document.querySelector('#sidebar-nav-items');
                    if (newSidebar && currentSidebar) {
                        currentSidebar.innerHTML = newSidebar.innerHTML;
                    }

                    // 3. Update Browser History
                    if (pushState) {
                        window.history.pushState({ spa: true, url: url }, '', url);
                    }

                    // 4. Teardown existing Alpine bindings in main
                    const currentMain = document.querySelector('#main-content');
                    if (window.Alpine && typeof Alpine.destroyTree === 'function') {
                        try {
                            Alpine.destroyTree(currentMain);
                        } catch (e) {
                            console.warn('Alpine destroyTree:', e);
                        }
                    }

                    // 5. Replace Main Content & Scroll to Top
                    currentMain.innerHTML = newMain.innerHTML;
                    currentMain.scrollTop = 0;

                    // 6. Re-evaluate <script> tags inside newMain
                    const scripts = Array.from(currentMain.querySelectorAll('script'));
                    for (const oldScript of scripts) {
                        const newScript = document.createElement('script');
                        for (const attr of oldScript.attributes) {
                            newScript.setAttribute(attr.name, attr.value);
                        }
                        newScript.text = oldScript.text;
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    }

                    // 7. Re-initialize Alpine on currentMain
                    if (window.Alpine && typeof Alpine.initTree === 'function') {
                        try {
                            Alpine.initTree(currentMain);
                        } catch (e) {
                            console.warn('Alpine initTree:', e);
                        }
                    }

                    // 8. Re-render Lucide Icons
                    safeCreateIcons();

                    // 9. Re-initialize charts if on dashboard
                    if (typeof window.initDashboardCharts === 'function' && (document.getElementById('statusDonutChart') || document.getElementById('statusChart') || document.getElementById('budgetTrendChart'))) {
                        window.initDashboardCharts();
                    }

                    // 10. Auto-close mobile sidebar if opened
                    const bodyEl = document.querySelector('body');
                    if (bodyEl && window.Alpine) {
                        const alpineData = bodyEl._x_dataStack ? bodyEl._x_dataStack[0] : null;
                        if (alpineData && alpineData.sidebarOpen) {
                            alpineData.sidebarOpen = false;
                        }
                    }

                } catch (err) {
                    console.error('SPA navigation error, falling back:', err);
                    window.location.href = url;
                } finally {
                    this.isNavigating = false;
                    this.finishProgress();
                }
            }
        };

        // Intercept internal link clicks
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;

            if (link.target === '_blank' || link.hasAttribute('download') || link.getAttribute('rel') === 'external' || link.hasAttribute('data-no-spa')) return;
            if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button !== 0) return;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

            let url;
            try {
                url = new URL(link.href, window.location.origin);
            } catch (err) {
                return;
            }
            if (url.origin !== window.location.origin) return;

            const path = url.pathname.toLowerCase();
            if (path.includes('/print') || path.includes('/export') || path.includes('/logout') || path.endsWith('.pdf') || path.endsWith('.xlsx') || path.endsWith('.csv')) {
                return;
            }

            if (url.href === window.location.href) {
                e.preventDefault();
                return;
            }

            e.preventDefault();
            window.AppSPA.navigate(url.href, true);
        });

        // Intercept GET filter forms (e.g. reports, audit logs)
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form || (form.method && form.method.toUpperCase() !== 'GET')) return;
            if (form.target === '_blank' || form.hasAttribute('data-no-spa')) return;

            let actionUrl;
            try {
                actionUrl = new URL(form.action || window.location.href, window.location.origin);
            } catch (err) {
                return;
            }
            if (actionUrl.origin !== window.location.origin) return;

            const path = actionUrl.pathname.toLowerCase();
            if (path.includes('/export') || path.includes('/print')) return;

            e.preventDefault();
            const formData = new FormData(form);
            const searchParams = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value !== '') {
                    searchParams.append(key, value);
                }
            }
            const query = searchParams.toString();
            const targetUrl = actionUrl.pathname + (query ? '?' + query : '');
            window.AppSPA.navigate(targetUrl, true);
        });

        // Handle Browser History (Back / Forward)
        window.addEventListener('popstate', function(e) {
            window.AppSPA.navigate(window.location.href, false);
        });
    </script>

    <!-- Standard Theme Management JavaScript (Light / Dark / System + OS Live Sync) -->
    <script>
        function applyThemeSynchronously(callback) {
            // Temporarily disable CSS transitions across all elements to prevent desync
            const style = document.createElement('style');
            style.textContent = '*, *::before, *::after { -webkit-transition: none !important; -moz-transition: none !important; -o-transition: none !important; -ms-transition: none !important; transition: none !important; }';
            document.head.appendChild(style);

            try {
                callback();
            } finally {
                // Force synchronous style reflow
                void document.documentElement.offsetHeight;

                // Re-enable transitions on the next animation frame
                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        if (style.parentNode) {
                            style.parentNode.removeChild(style);
                        }
                    });
                });
            }
        }

        function setAppTheme(mode) {
            if (mode !== 'light' && mode !== 'dark' && mode !== 'system') {
                mode = 'system';
            }

            try {
                localStorage.setItem('theme', mode);
            } catch (e) {}

            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = (mode === 'dark') || (mode === 'system' && prefersDark);

            applyThemeSynchronously(function() {
                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }

                // Dispatch event synchronously for Alpine, Chart.js, and components
                window.dispatchEvent(new CustomEvent('theme-changed', {
                    detail: { 
                        theme: isDark ? 'dark' : 'light', 
                        mode: mode,
                        isInstant: true 
                    }
                }));

                // Re-render Lucide icons if any changed
                if (typeof safeCreateIcons === 'function') {
                    safeCreateIcons();
                }
            });
        }

        function toggleAppTheme() {
            const current = localStorage.getItem('theme') || 'system';
            if (current === 'light') {
                setAppTheme('dark');
            } else if (current === 'dark') {
                setAppTheme('system');
            } else {
                setAppTheme('light');
            }
        }

        window.setAppTheme = setAppTheme;
        window.toggleAppTheme = toggleAppTheme;

        // Dynamic OS Preference Listener (Updates immediately when OS switches theme in 'system' mode)
        try {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            const handleSystemThemeChange = function(e) {
                const currentMode = localStorage.getItem('theme') || 'system';
                if (currentMode === 'system') {
                    applyThemeSynchronously(function() {
                        if (e.matches) {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                        window.dispatchEvent(new CustomEvent('theme-changed', {
                            detail: { 
                                theme: e.matches ? 'dark' : 'light', 
                                mode: 'system',
                                isInstant: true
                            }
                        }));
                        if (typeof safeCreateIcons === 'function') safeCreateIcons();
                    });
                }
            };

            if (mediaQuery.addEventListener) {
                mediaQuery.addEventListener('change', handleSystemThemeChange);
            } else if (mediaQuery.addListener) {
                mediaQuery.addListener(handleSystemThemeChange);
            }
        } catch (e) {}
    </script>
</body>
</html>
