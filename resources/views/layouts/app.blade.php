<!DOCTYPE html>
<html lang="th" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'ระบบติดตามและบริหารโครงการเทศบาล') ?> - เทศบาลตำบล/เมือง</title>
    
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
                        muni: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Prompt', sans-serif;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full antialiased text-slate-800 bg-slate-50 flex flex-col" x-data="{ sidebarOpen: false }">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <!-- Left Logo & Title -->
            <div class="flex items-center space-x-3">
                <button type="button" @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white shadow-md shadow-emerald-500/20">
                    <i data-lucide="landmark" class="w-6 h-6"></i>
                </div>
                <div>
                    <a href="<?= \App\Core\Router::url('/dashboard') ?>" class="text-base sm:text-lg font-bold font-heading text-slate-900 tracking-tight flex items-center gap-2">
                        ระบบติดตามและบริหารโครงการเทศบาล
                        <span class="hidden sm:inline-block text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 font-sans">ปี 2568</span>
                    </a>
                    <p class="text-xs text-slate-500 hidden sm:block font-sans">Municipal Project Tracking & Budget Disbursement System</p>
                </div>
            </div>

            <!-- Right: Role Switcher, Current User & Logout -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                <?php
                $userRole = $currentUser['role_name'] ?? 'officer';
                $roleMeta = [
                    'admin'           => ['label' => 'ผู้ดูแลระบบ (Admin)', 'bg' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon' => 'shield-check'],
                    'executive'       => ['label' => 'ผู้บริหาร (Executive)', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'icon' => 'briefcase'],
                    'officer'         => ['label' => 'เจ้าหน้าที่ (Officer)', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon' => 'clipboard-check'],
                    'project_manager' => ['label' => 'ผู้ดูแลโครงการ (PM)', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'icon' => 'user-check'],
                ];
                $currentRoleInfo = $roleMeta[$userRole] ?? ['label' => $currentUser['role_label'] ?? 'ผู้ใช้งาน', 'bg' => 'bg-slate-50 text-slate-700 border-slate-200', 'icon' => 'user'];
                $switchDemoUsers = \App\Core\Database::query("SELECT u.id, u.name, r.name as role_name, r.display_name as role_label FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id ASC LIMIT 5");
                ?>

                <!-- Dynamic Role Badge & Quick Switcher Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="hidden sm:flex items-center gap-1.5 <?= $currentRoleInfo['bg'] ?> px-3 py-1.5 rounded-xl border text-xs font-bold hover:shadow-sm transition cursor-pointer" title="คลิกเพื่อสลับบทบาททดสอบระบบ">
                        <i data-lucide="<?= $currentRoleInfo['icon'] ?>" class="w-4 h-4"></i>
                        <span><?= htmlspecialchars($currentRoleInfo['label']) ?></span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-60"></i>
                    </button>

                    <!-- Role Switcher Dropdown Menu -->
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-200 p-2 z-50 text-left">
                        <div class="px-3 py-2 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider font-heading">
                            สลับบทบาททดสอบระบบ (RBAC)
                        </div>
                        <div class="py-1 space-y-1">
                            <?php foreach ($switchDemoUsers as $su): ?>
                                <form action="<?= \App\Core\Router::url('/auth/switch') ?>" method="POST">
                                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="user_id" value="<?= $su['id'] ?>">
                                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">
                                    <button type="submit" class="w-full text-left px-3 py-2 rounded-xl text-xs hover:bg-slate-50 flex items-center justify-between transition cursor-pointer <?= ($currentUser['id'] == $su['id']) ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-700' ?>">
                                        <div>
                                            <div class="font-medium"><?= htmlspecialchars($su['name']) ?></div>
                                            <div class="text-[10px] text-slate-400"><?= htmlspecialchars($su['role_label']) ?></div>
                                        </div>
                                        <?php if ($currentUser['id'] == $su['id']): ?>
                                            <i data-lucide="check" class="w-4 h-4 text-emerald-600"></i>
                                        <?php endif; ?>
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Current User Badge -->
                <div class="flex items-center gap-2 pl-2 sm:border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs border border-emerald-300">
                        <?= mb_substr($currentUser['name'] ?? 'U', 0, 1) ?>
                    </div>
                    <div class="hidden lg:block text-left">
                        <div class="text-xs font-semibold text-slate-800 truncate max-w-[140px]"><?= htmlspecialchars($currentUser['name'] ?? 'ผู้ใช้งาน') ?></div>
                        <div class="text-[10px] text-emerald-600 font-medium"><?= htmlspecialchars($currentUser['role_label'] ?? 'เจ้าหน้าที่') ?></div>
                    </div>
                </div>

                <!-- Logout Form -->
                <form action="<?= \App\Core\Router::url('/logout') ?>" method="POST">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <button type="submit" title="ออกจากระบบ" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Container Layout -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 pt-16 lg:pt-0 transform lg:translate-x-0 lg:static transition-transform duration-200 ease-in-out flex flex-col justify-between shadow-lg lg:shadow-none">
            <div class="p-4 space-y-1.5 overflow-y-auto">
                <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">ระบบงานหลัก</div>
                
                <a href="<?= \App\Core\Router::url('/dashboard') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= str_contains($_SERVER['REQUEST_URI'], '/dashboard') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span>แดชบอร์ดภาพรวม</span>
                </a>

                <a href="<?= \App\Core\Router::url('/projects') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= (str_contains($_SERVER['REQUEST_URI'], '/projects') || str_contains($_SERVER['REQUEST_URI'], '/sub-projects')) ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                    <i data-lucide="folder-kanban" class="w-5 h-5"></i>
                    <span>โครงการหลัก & ย่อย</span>
                </a>

                <a href="<?= \App\Core\Router::url('/budgets') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= str_contains($_SERVER['REQUEST_URI'], '/budgets') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                    <span>งบประมาณ & เบิกจ่าย</span>
                </a>

                <a href="<?= \App\Core\Router::url('/reports') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= str_contains($_SERVER['REQUEST_URI'], '/reports') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                    <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                    <span>รายงาน & ส่งออกข้อมูล</span>
                </a>

                <div class="pt-4 px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">การกำกับดูแล & สิทธิ์</div>

                <a href="<?= \App\Core\Router::url('/users') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= str_contains($_SERVER['REQUEST_URI'], '/users') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span>ผู้ใช้งาน & บทบาท (RBAC)</span>
                </a>

                <a href="<?= \App\Core\Router::url('/audit-logs') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= str_contains($_SERVER['REQUEST_URI'], '/audit-logs') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                    <i data-lucide="history" class="w-5 h-5"></i>
                    <span>ประวัติการทำงาน (Audit Log)</span>
                </a>
            </div>
        </aside>

        <!-- Backdrop for mobile sidebar -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            <!-- Flash Message: Success -->
            <?php if (!empty($flashSuccess)): ?>
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm" x-data="{ show: true }" x-show="show">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
                    <div class="text-sm font-medium flex-1"><?= htmlspecialchars($flashSuccess) ?></div>
                    <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Flash Message: Error -->
            <?php if (!empty($flashError)): ?>
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-3 shadow-sm" x-data="{ show: true }" x-show="show">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 flex-shrink-0"></i>
                    <div class="text-sm font-medium flex-1"><?= htmlspecialchars($flashError) ?></div>
                    <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-700">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            <?php endif; ?>

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
</body>
</html>
