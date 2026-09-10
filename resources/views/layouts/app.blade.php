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
    <meta name="color-scheme" id="meta-color-scheme" content="light">

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
    
    <!-- Theme Detection & Anti-Flicker Script (Standard 3-State Tailwind Pattern: Light / Dark / System) -->
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
                const metaScheme = document.getElementById('meta-color-scheme');
                if (metaScheme) metaScheme.content = isDark ? 'dark' : 'light';
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
                        'dark-card': '0 10px 30px -5px rgba(0, 0, 0, 0.5), 0 4px 10px -3px rgba(0, 0, 0, 0.3)',
                    }
                }
            }
        }
    </script>
    
    <!-- Global DOM-based Thai Datepicker Component (Available before Alpine initializes) -->
    <script>
        function thaiDatePicker(config = {}) {
            const today = new Date();
            let viewDate = today;
            if (config.value) {
                const parts = String(config.value).split('-');
                if (parts.length === 3) {
                    const parsed = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                    if (!isNaN(parsed.getTime())) viewDate = parsed;
                }
            }

            return {
                name: config.name || '',
                id: config.id || config.name || '',
                value: config.value || '',
                placeholder: config.placeholder || 'วว/ดด/ปปปป',
                align: config.align || 'left',
                placement: config.placement || 'bottom',
                open: false,
                viewYear: viewDate.getFullYear(),
                viewMonth: viewDate.getMonth(),
                days: [],

                init() {
                    this.syncFromValue();
                    this.refreshDays();

                    // Listen for specific event targeting this datepicker field
                    if (this.name) {
                        window.addEventListener('set-thai-date-' + this.name, (e) => {
                            this.setValue(e.detail);
                        });
                    }
                    if (this.id && this.id !== this.name) {
                        window.addEventListener('set-thai-date-' + this.id, (e) => {
                            this.setValue(e.detail);
                        });
                    }

                    // Two-way watch if parent model is passed
                    if (config.model && typeof this.$watch === 'function') {
                        this.$watch(config.model, (newVal) => {
                            if (newVal !== this.value) {
                                this.setValue(newVal);
                            }
                        });
                    }
                },

                monthNames: [
                    'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน',
                    'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม',
                    'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
                ],
                shortDays: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],

                get displayValue() {
                    return this.displayLabel;
                },

                get displayLabel() {
                    if (!this.value) return this.placeholder;
                    const parts = String(this.value).split('-');
                    if (parts.length !== 3) return this.value;
                    const y = parseInt(parts[0], 10);
                    const m = parseInt(parts[1], 10);
                    const d = parseInt(parts[2], 10);
                    if (isNaN(y) || isNaN(m) || isNaN(d)) return this.placeholder;
                    const thaiYear = y + 543;
                    return `${String(d).padStart(2, '0')}/${String(m).padStart(2, '0')}/${thaiYear}`;
                },

                get monthLabel() {
                    return this.monthNames[this.viewMonth] + ' ' + (this.viewYear + 543);
                },

                syncFromValue() {
                    if (this.value) {
                        const parts = String(this.value).split('-');
                        if (parts.length === 3) {
                            const y = parseInt(parts[0], 10);
                            const m = parseInt(parts[1], 10);
                            if (!isNaN(y) && !isNaN(m)) {
                                this.viewYear = y;
                                this.viewMonth = m - 1;
                            }
                        }
                    }
                },

                refreshDays() {
                    this.days = this.generateDays();
                },

                generateDays() {
                    const list = [];
                    const firstDayIndex = new Date(this.viewYear, this.viewMonth, 1).getDay();
                    const lastDate = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
                    const prevLastDate = new Date(this.viewYear, this.viewMonth, 0).getDate();

                    for (let i = firstDayIndex - 1; i >= 0; i--) {
                        list.push({
                            day: prevLastDate - i,
                            isCurrent: false,
                            isCurrentMonth: false,
                            date: '',
                            dateStr: '',
                            isToday: false,
                            isSelected: false
                        });
                    }

                    const ty = today.getFullYear();
                    const tm = String(today.getMonth() + 1).padStart(2, '0');
                    const td = String(today.getDate()).padStart(2, '0');
                    const todayStr = `${ty}-${tm}-${td}`;

                    for (let i = 1; i <= lastDate; i++) {
                        const mStr = String(this.viewMonth + 1).padStart(2, '0');
                        const dStr = String(i).padStart(2, '0');
                        const dateStr = `${this.viewYear}-${mStr}-${dStr}`;
                        list.push({
                            day: i,
                            isCurrent: true,
                            isCurrentMonth: true,
                            date: dateStr,
                            dateStr: dateStr,
                            isToday: dateStr === todayStr,
                            isSelected: this.value === dateStr
                        });
                    }

                    const total = list.length <= 35 ? 35 : 42;
                    const rem = total - list.length;
                    for (let i = 1; i <= rem; i++) {
                        list.push({
                            day: i,
                            isCurrent: false,
                            isCurrentMonth: false,
                            date: '',
                            dateStr: '',
                            isToday: false,
                            isSelected: false
                        });
                    }
                    return list;
                },

                toggle() {
                    this.open = !this.open;
                    if (this.open) {
                        this.syncFromValue();
                        this.refreshDays();
                    }
                },

                prevYear() {
                    this.viewYear--;
                    this.refreshDays();
                },

                nextYear() {
                    this.viewYear++;
                    this.refreshDays();
                },

                prevMonth() {
                    if (this.viewMonth === 0) {
                        this.viewMonth = 11;
                        this.viewYear--;
                    } else {
                        this.viewMonth--;
                    }
                    this.refreshDays();
                },

                nextMonth() {
                    if (this.viewMonth === 11) {
                        this.viewMonth = 0;
                        this.viewYear++;
                    } else {
                        this.viewMonth++;
                    }
                    this.refreshDays();
                },

                setValue(val) {
                    this.value = val ? String(val).trim() : '';
                    this.syncFromValue();
                    this.refreshDays();
                    this.dispatchChange();
                },

                dispatchChange() {
                    this.$nextTick(() => {
                        if (this.$el) {
                            const hiddenInput = this.$el.querySelector('input[type="hidden"]');
                            if (hiddenInput) {
                                hiddenInput.value = this.value;
                                hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                            this.$el.dispatchEvent(new CustomEvent('date-selected', {
                                bubbles: true,
                                detail: { name: this.name, value: this.value }
                            }));
                        }
                    });
                },

                selectDate(item) {
                    if (!item.isCurrent || !item.date) return;
                    this.value = item.date;
                    this.open = false;
                    this.dispatchChange();
                },

                selectToday() {
                    const ty = today.getFullYear();
                    const tm = String(today.getMonth() + 1).padStart(2, '0');
                    const td = String(today.getDate()).padStart(2, '0');
                    this.value = `${ty}-${tm}-${td}`;
                    this.viewYear = ty;
                    this.viewMonth = today.getMonth();
                    this.refreshDays();
                    this.open = false;
                    this.dispatchChange();
                },

                clear() {
                    this.value = '';
                    this.open = false;
                    this.dispatchChange();
                }
            };
        }
        window.thaiDatePicker = thaiDatePicker;
        document.addEventListener('alpine:init', function() {
            if (window.Alpine && typeof Alpine.data === 'function') {
                Alpine.data('thaiDatePicker', thaiDatePicker);
            }
        });
    </script>

    <!-- Alpine.js, Lucide Icons & Chart.js (Local Offline-First with CDN fallback) -->
    <script src="<?= \App\Core\Router::url('/js/chart.umd.min.js') ?>"></script>
    <script>
        if (typeof Chart === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/chart.js"><\/script>');
        }
    </script>
    <script src="<?= \App\Core\Router::url('/js/lucide.min.js') ?>"></script>
    <script>
        if (typeof lucide === 'undefined') {
            document.write('<script src="https://unpkg.com/lucide@latest"><\/script>');
        }
    </script>
    <script defer src="<?= \App\Core\Router::url('/js/alpine.min.js') ?>"></script>
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            if (typeof Alpine === 'undefined') {
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
                s.defer = true;
                document.head.appendChild(s);
            }
        });
    </script>

    <style>
        :root {
            color-scheme: light;
        }
        html.dark {
            color-scheme: dark;
        }

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

        /* Hardware Accelerated Smooth Modals & Overlays */
        .modal-backdrop-smooth {
            background-color: rgba(10, 15, 29, 0.72) !important;
            -webkit-backdrop-filter: none !important;
            backdrop-filter: none !important;
            will-change: opacity;
        }
        .modal-box-smooth {
            will-change: transform, opacity;
            transform: translateZ(0);
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
        }

        /* ========================================================= */
        /* Zero-Flicker Focus State (Eliminate Native Black Outline) */
        /* ========================================================= */
        input, select, textarea, button {
            outline: none !important;
            -webkit-tap-highlight-color: transparent;
        }
        input:focus, select:focus, textarea:focus, button:focus,
        input:focus-visible, select:focus-visible, textarea:focus-visible, button:focus-visible,
        input:active, select:active, textarea:active, button:active {
            outline: none !important;
            outline-offset: 0 !important;
        }

        /* Light table rows hover */
        tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Form Inputs & Selects (Light) - Instant crisp border, smooth glow */
        input:not([type="checkbox"]):not([type="radio"]):not(:focus):not(:focus-visible),
        select:not(:focus):not(:focus-visible),
        textarea:not(:focus):not(:focus-visible) {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            color-scheme: light;
            outline: none !important;
            transition: box-shadow 0.1s ease-out;
        }
        select option, select optgroup {
            background-color: #ffffff;
            color: #0f172a;
            color-scheme: light;
        }
        input:not([type="checkbox"]):not([type="radio"]):focus,
        input:not([type="checkbox"]):not([type="radio"]):focus-visible,
        select:focus, select:focus-visible,
        textarea:focus, textarea:focus-visible,
        button.cursor-pointer:focus, button.cursor-pointer:focus-visible,
        .focus\:border-emerald-500:focus, .focus\:border-emerald-500:focus-visible {
            border: 1px solid #10b981 !important;
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important;
            outline: none !important;
            outline-offset: 0 !important;
        }

        /* Universal Smooth Scrollbar Styling (Transparent Track - Zero White Lines) */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: transparent !important;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.4);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(16, 185, 129, 0.7);
        }

        /* Mobile & Touch Viewports (< 1024px): Completely hide desktop scrollbars to prevent white/gray vertical edges */
        @media (max-width: 1023px) {
            ::-webkit-scrollbar {
                display: none !important;
                width: 0px !important;
                height: 0px !important;
                background: transparent !important;
            }
            html, body, #main-content, aside, * {
                -ms-overflow-style: none !important;
                scrollbar-width: none !important;
            }
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
        html.dark .bg-slate-200 {
            background-color: rgba(255, 255, 255, 0.08) !important;
        }
        html.dark .border-slate-100:not(:focus):not(:focus-visible),
        html.dark .border-slate-200:not(:focus):not(:focus-visible),
        html.dark .border-slate-300:not(:focus):not(:focus-visible) {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        /* Dark table rows hover */
        html.dark tr:hover {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        /* Form Inputs & Selects Dark - Sleek resting border, Vibrant Emerald focus ring matching Light Mode */
        html.dark input:not([type="checkbox"]):not([type="radio"]):not(:focus):not(:focus-visible),
        html.dark select:not(:focus):not(:focus-visible),
        html.dark textarea:not(:focus):not(:focus-visible) {
            background-color: #12141a !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
            color-scheme: dark;
            outline: none !important;
            transition: box-shadow 0.1s ease-out;
        }
        html.dark select option, html.dark select optgroup {
            background-color: #181a20 !important;
            color: #ffffff !important;
            color-scheme: dark;
        }
        html.dark input:not([type="checkbox"]):not([type="radio"]):focus,
        html.dark input:not([type="checkbox"]):not([type="radio"]):focus-visible,
        html.dark select:focus, html.dark select:focus-visible,
        html.dark textarea:focus, html.dark textarea:focus-visible,
        html.dark button.cursor-pointer:focus, html.dark button.cursor-pointer:focus-visible,
        html.dark .focus\:border-emerald-500:focus, html.dark .focus\:border-emerald-500:focus-visible,
        html.dark .dark\:border-white\/10:focus, html.dark .dark\:border-white\/10:focus-visible {
            border: 1px solid #10b981 !important;
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.35) !important;
            outline: none !important;
            outline-offset: 0 !important;
        }

        /* Dark Scrollbar Styling */
        html.dark ::-webkit-scrollbar-track {
            background: transparent !important;
        }
        html.dark ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 9999px;
        }
        html.dark ::-webkit-scrollbar-thumb:hover {
            background: rgba(16, 185, 129, 0.7);
        }

        /* Strict Anti-Overflow & Responsive Viewport Rules (Zero white edge artifact) */
        html, body {
            overflow-x: hidden !important;
            max-width: 100% !important;
            width: 100% !important;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        *, *::before, *::after {
            box-sizing: border-box;
        }
        #main-content {
            max-width: 100% !important;
            overflow-x: hidden !important;
            -webkit-overflow-scrolling: touch !important;
            touch-action: pan-x pan-y;
        }
        #main-content table,
        #main-content table * {
            max-width: none !important;
        }
        canvas, .chartjs-render-monitor {
            touch-action: pan-y !important;
        }
        /* Mobile Sidebar Anti-FOUC (Prevents initial flash/slide on mobile) */
        @media (max-width: 1023.98px) {
            #main-sidebar:not(.sidebar-open) {
                transform: translateX(-100%) !important;
            }
        }
    </style>
</head>
<body class="h-full antialiased text-slate-800 dark:text-slate-100 bg-[#f8fafc] dark:bg-[#0f1014] flex flex-col transition-colors duration-150 w-full max-w-full overflow-x-hidden" 
      :class="{ 'overflow-hidden': sidebarOpen }" 
      @close-sidebar.window="sidebarOpen = false"
      x-data="{ 
          sidebarOpen: false, 
          desktopSidebarOpen: (localStorage.getItem('mpt_desktop_sidebar') !== 'false'),
          resizeTimer: null,
          closeSidebar() {
              this.sidebarOpen = false;
          },
          toggleSidebar() {
              if (window.innerWidth >= 1024) {
                  this.desktopSidebarOpen = !this.desktopSidebarOpen;
                  try {
                      localStorage.setItem('mpt_desktop_sidebar', this.desktopSidebarOpen ? 'true' : 'false');
                  } catch (e) {}
              } else {
                  this.sidebarOpen = !this.sidebarOpen;
              }
              clearTimeout(this.resizeTimer);
              this.resizeTimer = setTimeout(() => { 
                  window.dispatchEvent(new Event('resize')); 
              }, 220);
          }
      }">

    <?php 
        $flashSuccess = \App\Core\Session::flash('success');
        $flashError = \App\Core\Session::flash('error');
        $flashInfo = \App\Core\Session::flash('info');
    ?>

    <!-- SPA Top Progress Bar (Neon Green Glow) -->
    <div id="spa-progress" class="fixed top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 via-teal-300 to-green-500 shadow-[0_0_12px_#10b981] z-[9999] transition-all duration-200 pointer-events-none opacity-0" style="width: 0%;"></div>

    <!-- Floating Toast Notifications Container -->
    <div id="toast-container" class="fixed top-4 right-4 sm:top-6 sm:right-6 z-[99999] pointer-events-none flex flex-col gap-2.5 max-w-sm w-full px-4 sm:px-0"></div>

    <!-- Flash Message Carrier for SPA and initial page load -->
    <div id="flash-message-carrier" 
         style="display: none;" 
         data-success="<?= htmlspecialchars($flashSuccess ?? '', ENT_QUOTES) ?>" 
         data-error="<?= htmlspecialchars($flashError ?? '', ENT_QUOTES) ?>"
         data-info="<?= htmlspecialchars($flashInfo ?? '', ENT_QUOTES) ?>"></div>

    <!-- Top Navigation Bar -->
    <header class="bg-white/98 dark:bg-[#101115]/98 border-b border-slate-200 dark:border-white/[0.08] sticky top-0 z-30 shadow-sm dark:shadow-md transition-colors duration-150 w-full max-w-full will-change-transform">
        <div class="px-2.5 sm:px-6 lg:px-8 flex items-center justify-between h-14 sm:h-16 w-full max-w-full">
            <!-- Left Logo & Title -->
            <div class="flex items-center gap-1.5 sm:gap-3 min-w-0 flex-1 sm:flex-initial">
                <button type="button" 
                        @click="toggleSidebar()" 
                        class="p-1.5 sm:p-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 hover:text-emerald-600 dark:hover:text-emerald-400 transition shrink-0 cursor-pointer flex items-center justify-center" 
                        :title="(window.innerWidth >= 1024 && !desktopSidebarOpen) ? 'แสดงแถบเมนู' : 'เปิด/ปิดแถบเมนู'"
                        aria-label="เปิด/ปิดแถบเมนู">
                    <i data-lucide="menu" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </button>
                <a href="<?= \App\Core\Router::url('/dashboard') ?>" class="shrink-0 flex items-center gap-1.5 sm:gap-2 group" title="ระบบติดตามและบริหารโครงการเทศบาล">
                    <!-- โลโก้เทศบาล (พื้นหลังขาวคมชัดทุกธีม) -->
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-white p-1 shadow-sm border border-slate-200/80 dark:border-white/20 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <img src="<?= \App\Core\Router::url('/images/mobile-logo.webp') ?>" 
                             alt="โลโก้เทศบาล" 
                             class="w-full h-full object-contain"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="display:none;" class="w-full h-full rounded-lg bg-gradient-to-tr from-emerald-500 to-teal-400 items-center justify-center text-slate-950 font-bold shadow-neon-green">
                            <i data-lucide="activity" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                    </div>

                    <!-- โลโก้ กปท. (กองทุนหลักประกันสุขภาพท้องถิ่น - พื้นหลังขาวคมชัดทุกธีม) -->
                    <div class="h-8 sm:h-10 px-2 sm:px-2.5 py-1 rounded-xl sm:rounded-2xl border border-slate-200/80 dark:border-white/20 bg-white shadow-sm flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <img src="<?= \App\Core\Router::url('/images/kpth-logo.png') ?>" 
                             alt="โลโก้ กปท. กองทุนหลักประกันสุขภาพท้องถิ่น" 
                             class="h-5 sm:h-7 w-auto max-w-[70px] sm:max-w-[105px] object-contain shrink-0">
                    </div>
                </a>
                <div class="min-w-0 hidden md:block">
                    <a href="<?= \App\Core\Router::url('/dashboard') ?>" class="font-bold font-heading text-slate-900 dark:text-white tracking-tight flex items-center gap-1 sm:gap-2">
                        <span class="text-base sm:text-lg whitespace-nowrap">ระบบติดตามและบริหารโครงการเทศบาล</span>
                        <?php 
                            $navActiveFy = \App\Services\FiscalYearService::getActiveYear(); 
                            $navActiveYearStr = $navActiveFy ? $navActiveFy['year'] : \App\Services\FiscalYearService::getCurrentFiscalYear();
                        ?>
                        <span class="inline-block text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-500/15 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 dark:border-emerald-500/40 font-sans shrink-0">ปี <?= htmlspecialchars((string)$navActiveYearStr) ?></span>
                    </a>
                    <p class="text-[11px] sm:text-xs text-emerald-600 dark:text-emerald-400 font-semibold truncate">คณะอนุกรรมการฝ่ายติดตามและการประเมินผล</p>
                </div>
            </div>

            <!-- Right: Theme Switcher, Role Switcher & User Profile -->
            <div class="flex items-center gap-1 sm:gap-2 shrink-0">

                <!-- Standard 3-State Theme Switcher (Light / Dark / System) -->
                <div class="relative shrink-0" x-data="{ 
                    open: false, 
                    mode: localStorage.getItem('theme') || 'system',
                    resolvedDark: document.documentElement.classList.contains('dark')
                }" @theme-changed.window="mode = ($event.detail && $event.detail.mode) ? $event.detail.mode : (localStorage.getItem('theme') || 'system'); resolvedDark = document.documentElement.classList.contains('dark')">
                    
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
                $currentUser = $currentUser ?? \App\Core\Auth::user() ?? [];
                $userRole = $currentUser['role_name'] ?? 'admin';
                $roleMeta = [
                    'admin'     => ['label' => 'ผู้ดูแลระบบ', 'badge' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60', 'avatar_bg' => 'bg-purple-600 text-white', 'icon' => 'shield-check'],
                    'executive' => ['label' => 'ผู้บริหาร (ดูอย่างเดียว)', 'badge' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60', 'avatar_bg' => 'bg-amber-600 text-white', 'icon' => 'eye'],
                    'officer'   => ['label' => 'เจ้าหน้าที่', 'badge' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60', 'avatar_bg' => 'bg-blue-600 text-white', 'icon' => 'user-check'],
                ];
                $currentRoleInfo = $roleMeta[$userRole] ?? [
                    'label'     => $currentUser['role_label'] ?? 'ผู้ใช้งาน',
                    'badge'     => 'bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/10',
                    'avatar_bg' => 'bg-slate-700 text-white',
                    'icon'      => 'user'
                ];
                $userName = trim($currentUser['name'] ?? 'ผู้ใช้งาน');
                $avatarInitials = mb_substr($userName, 0, 2, 'UTF-8');
                ?>

                <!-- Modern Interactive User Profile Dropdown Pill & Modals -->
                <div class="relative shrink-0" 
                     x-data="{ 
                         userMenuOpen: false, 
                         profileModalOpen: false, 
                         profileTab: 'general' 
                     }"
                     @close-profile-modal.window="profileModalOpen = false; userMenuOpen = false"
                     @open-profile-modal.window="userMenuOpen = false; profileTab = ($event.detail && $event.detail.tab) ? $event.detail.tab : 'general'; profileModalOpen = true"
                     x-init="$watch('profileModalOpen', v => { if(v) setTimeout(() => { if (typeof safeCreateIcons === 'function') safeCreateIcons(); }, 50); }); $watch('profileTab', () => setTimeout(() => { if (typeof safeCreateIcons === 'function') safeCreateIcons(); }, 50));">
                    
                    <!-- Profile Button Trigger -->
                    <button type="button" 
                            @click.stop="userMenuOpen = !userMenuOpen" 
                            id="user-profile-menu-btn"
                            class="flex items-center gap-2 p-1 sm:py-1 sm:pl-1.5 sm:pr-2.5 rounded-2xl bg-slate-100 dark:bg-[#181a20] hover:bg-slate-200/80 dark:hover:bg-white/5 border border-slate-200 dark:border-white/[0.08] hover:border-slate-300 dark:hover:border-white/20 transition-all duration-150 cursor-pointer shadow-xs group"
                            :class="{ 'ring-2 ring-purple-500/25 border-purple-500/50 bg-purple-50/60 dark:bg-purple-500/10': userMenuOpen }"
                            title="ข้อมูลผู้ใช้งานและเมนูบัญชี">
                        
                        <!-- Refined Solid Avatar with Online Indicator -->
                        <div class="relative shrink-0">
                            <div class="w-8 h-8 sm:w-8.5 sm:h-8.5 rounded-xl <?= $currentRoleInfo['avatar_bg'] ?> font-heading font-semibold text-xs flex items-center justify-center shadow-sm">
                                <?= htmlspecialchars($avatarInitials) ?>
                            </div>
                            <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-[#181a20]"></span>
                        </div>

                        <!-- User Name & Role (Hidden on mobile <640px) -->
                        <div class="hidden sm:block text-left pr-0.5 max-w-[140px]">
                            <div class="text-xs font-bold font-heading text-slate-800 dark:text-slate-100 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors truncate">
                                <?= htmlspecialchars($userName) ?>
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium truncate flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block shrink-0"></span>
                                <span class="truncate"><?= htmlspecialchars($currentRoleInfo['label']) ?></span>
                            </div>
                        </div>

                        <!-- Subtle Rotating Chevron -->
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': userMenuOpen }"></i>
                    </button>

                    <!-- Dropdown Flyout Card -->
                    <div x-show="userMenuOpen" 
                         @click.outside="userMenuOpen = false" 
                         x-cloak 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="transform opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="transform opacity-0 scale-95 -translate-y-1"
                         class="absolute right-0 mt-2 w-72 sm:w-80 max-w-[calc(100vw-1.5rem)] bg-white dark:bg-[#181a20] rounded-2xl shadow-2xl border border-slate-200 dark:border-white/10 p-2 z-50 text-left backdrop-blur-xl">
                        
                        <!-- Header User Info Card (Clean Solid Surface) -->
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.06] mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl <?= $currentRoleInfo['avatar_bg'] ?> font-heading font-bold text-sm flex items-center justify-center shadow-md shrink-0">
                                    <?= htmlspecialchars($avatarInitials) ?>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs sm:text-sm font-bold font-heading text-slate-900 dark:text-white truncate">
                                        <?= htmlspecialchars($currentUser['name'] ?? 'ผู้ใช้งาน') ?>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                        <?= htmlspecialchars($currentUser['email'] ?? 'user@municipal.go.th') ?>
                                    </div>
                                    <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold <?= $currentRoleInfo['badge'] ?>">
                                            <i data-lucide="<?= $currentRoleInfo['icon'] ?>" class="w-3 h-3"></i>
                                            <?= htmlspecialchars($currentRoleInfo['label']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Items -->
                        <div class="space-y-0.5 text-xs">
                            <!-- 1. Open Profile Modal -->
                            <button type="button" 
                                    @click.stop="userMenuOpen = false; profileTab = 'general'; profileModalOpen = true"
                                    class="w-full text-left px-3 py-2.5 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 flex items-center gap-2.5 text-slate-700 dark:text-slate-200 transition cursor-pointer font-medium">
                                <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">ข้อมูลส่วนตัว (My Profile)</div>
                                    <div class="text-[10px] text-slate-400">ดูสังกัด ข้อมูลติดต่อ และตำแหน่ง</div>
                                </div>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                            </button>

                            <!-- 2. Open Password Modal -->
                            <button type="button" 
                                    @click.stop="userMenuOpen = false; profileTab = 'security'; profileModalOpen = true"
                                    class="w-full text-left px-3 py-2.5 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 flex items-center gap-2.5 text-slate-700 dark:text-slate-200 transition cursor-pointer font-medium">
                                <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="key-round" class="w-4 h-4"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">เปลี่ยนรหัสผ่าน (Change Password)</div>
                                    <div class="text-[10px] text-slate-400">ความปลอดภัยของบัญชีผู้ใช้งาน</div>
                                </div>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                            </button>

                            <?php if (\App\Core\Auth::isAdmin()): ?>
                                <!-- 3. User Management (Admin Only) -->
                                <a href="<?= \App\Core\Router::url('/users') ?>" 
                                   class="w-full text-left px-3 py-2.5 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 flex items-center gap-2.5 text-slate-700 dark:text-slate-200 transition font-medium">
                                    <div class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                                        <i data-lucide="users" class="w-4 h-4"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold text-slate-800 dark:text-slate-200">จัดการผู้ใช้งาน (User Management)</div>
                                        <div class="text-[10px] text-slate-400">กำหนดสิทธิ์และเพิ่มบัญชีเจ้าหน้าที่</div>
                                    </div>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Clean Logout Button in Menu -->
                        <div class="mt-1 pt-1 border-t border-slate-100 dark:border-white/[0.06]">
                            <form action="<?= \App\Core\Router::url('/logout') ?>" method="POST" data-no-spa>
                                <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                <button type="submit" 
                                        class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-between transition cursor-pointer">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                        <span>ออกจากระบบ (Sign Out)</span>
                                    </div>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-60"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Modern Interactive User Profile Pop-up Modal Component -->
                    <?php \App\Core\View::component('user-profile-modal', [
                        'currentUser' => $currentUser,
                        'csrfToken'   => $csrfToken,
                    ]); ?>
                </div>
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
        <!-- Sidebar Navigation (Elevated z-50 above backdrop) -->
        <aside id="main-sidebar"
               :class="{
                   'translate-x-0 sidebar-open': sidebarOpen,
                   '-translate-x-full': !sidebarOpen,
                   'lg:ml-0': desktopSidebarOpen,
                   'lg:-ml-64 lg:pointer-events-none': !desktopSidebarOpen
               }" 
               class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-[#0b0c0f] border-r border-slate-200 dark:border-white/[0.08] pt-0 transform -translate-x-full lg:translate-x-0 lg:ml-0 lg:static transition-[margin-left,transform] duration-200 ease-out flex flex-col justify-between shadow-lg dark:shadow-2xl lg:shadow-none overflow-hidden shrink-0 will-change-[margin-left,transform]">
            <div class="w-64 h-full flex flex-col justify-between overflow-hidden">
                
                <!-- Mobile Sidebar Brand Header with Municipal Logo (Replaces empty top space) -->
                <div class="lg:hidden h-14 sm:h-16 px-3.5 flex items-center justify-between border-b border-slate-200/80 dark:border-white/[0.08] shrink-0 bg-slate-50/70 dark:bg-white/[0.02]">
                    <a href="<?= \App\Core\Router::url('/dashboard') ?>" class="flex items-center gap-2 min-w-0 group" title="ระบบติดตามและบริหารโครงการเทศบาล">
                        <!-- โลโก้เทศบาล (พื้นหลังขาวคมชัดทุกธีม) -->
                        <div class="w-8 h-8 rounded-xl bg-white p-1 shadow-sm border border-slate-200/80 dark:border-white/20 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <img src="<?= \App\Core\Router::url('/images/mobile-logo.webp') ?>" 
                                 alt="โลโก้เทศบาล" 
                                 class="w-full h-full object-contain"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display:none;" class="w-full h-full rounded-lg bg-gradient-to-tr from-emerald-500 to-teal-400 items-center justify-center text-slate-950 font-bold">
                                <i data-lucide="activity" class="w-4 h-4"></i>
                            </div>
                        </div>

                        <!-- โลโก้ กปท. -->
                        <div class="h-8 px-1.5 py-0.5 rounded-xl border border-slate-200/80 dark:border-white/20 bg-white shadow-sm flex items-center justify-center shrink-0">
                            <img src="<?= \App\Core\Router::url('/images/kpth-logo.png') ?>" 
                                 alt="กปท." 
                                 class="h-5 w-auto max-w-[60px] object-contain shrink-0">
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-xs font-heading text-slate-900 dark:text-white truncate">
                                ติดตามโครงการ
                            </div>
                            <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold truncate" title="คณะอนุกรรมการฝ่ายติดตามและการประเมินผล">
                                คณะอนุกรรมการฝ่ายติดตามและการประเมินผล
                            </div>
                        </div>
                    </a>

                    <!-- Close Drawer Button on Mobile -->
                    <button type="button" 
                            @click="sidebarOpen = false" 
                            class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-white/10 transition shrink-0 cursor-pointer ml-1"
                            aria-label="ปิดเมนู">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div id="sidebar-nav-items" class="p-4 space-y-2 overflow-y-auto flex-1">
                    <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 font-heading">
                        MENU
                    </div>
                    
                    <!-- แดชบอร์ดภาพรวม -->
                    <?php 
                        $currentReqUri = $_SERVER['REQUEST_URI'] ?? '/';
                        $isDashboard = str_contains($currentReqUri, '/dashboard'); 
                    ?>
                    <a href="<?= \App\Core\Router::url('/dashboard') ?>" 
                       @click="sidebarOpen = false"
                       class="flex items-center justify-between px-4 py-3 rounded-2xl text-sm font-medium transition-all <?= $isDashboard ? 'bg-emerald-50 dark:bg-[#181c26] text-emerald-900 dark:text-white font-semibold border border-emerald-500/30 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent' ?>">
                        <div class="flex items-center gap-3">
                            <i data-lucide="layout-dashboard" class="w-5 h-5 <?= $isDashboard ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"></i>
                            <span>แดชบอร์ดภาพรวม</span>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 <?= $isDashboard ? 'text-emerald-600 dark:text-emerald-400' : 'opacity-40 text-slate-400 dark:text-slate-500' ?>"></i>
                    </a>

                    <!-- โครงการหลัก & ย่อย -->
                    <?php $isProjects = (str_contains($currentReqUri, '/projects') || str_contains($currentReqUri, '/sub-projects')); ?>
                    <a href="<?= \App\Core\Router::url('/projects') ?>" 
                       @click="sidebarOpen = false"
                       class="flex items-center justify-between px-4 py-3 rounded-2xl text-sm font-medium transition-all <?= $isProjects ? 'bg-emerald-50 dark:bg-[#181c26] text-emerald-900 dark:text-white font-semibold border border-emerald-500/30 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent' ?>">
                        <div class="flex items-center gap-3">
                            <i data-lucide="folder-kanban" class="w-5 h-5 <?= $isProjects ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"></i>
                            <span>โครงการหลัก & ย่อย</span>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 <?= $isProjects ? 'text-emerald-600 dark:text-emerald-400' : 'opacity-40 text-slate-400 dark:text-slate-500' ?>"></i>
                    </a>

                    <!-- งบประมาณ & เบิกจ่าย -->
                    <?php $isBudgets = str_contains($currentReqUri, '/budgets'); ?>
                    <a href="<?= \App\Core\Router::url('/budgets') ?>" 
                       @click="sidebarOpen = false"
                       class="flex items-center justify-between px-4 py-3 rounded-2xl text-sm font-medium transition-all <?= $isBudgets ? 'bg-emerald-50 dark:bg-[#181c26] text-emerald-900 dark:text-white font-semibold border border-emerald-500/30 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent' ?>">
                        <div class="flex items-center gap-3">
                            <i data-lucide="wallet" class="w-5 h-5 <?= $isBudgets ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"></i>
                            <span>งบประมาณ & เบิกจ่าย</span>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 <?= $isBudgets ? 'text-emerald-600 dark:text-emerald-400' : 'opacity-40 text-slate-400 dark:text-slate-500' ?>"></i>
                    </a>

                    <!-- รายงาน & ส่งออกข้อมูล -->
                    <?php $isReports = str_contains($currentReqUri, '/reports'); ?>
                    <a href="<?= \App\Core\Router::url('/reports') ?>" 
                       @click="sidebarOpen = false"
                       class="flex items-center justify-between px-4 py-3 rounded-2xl text-sm font-medium transition-all <?= $isReports ? 'bg-emerald-50 dark:bg-[#181c26] text-emerald-900 dark:text-white font-semibold border border-emerald-500/30 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent' ?>">
                        <div class="flex items-center gap-3">
                            <i data-lucide="file-spreadsheet" class="w-5 h-5 <?= $isReports ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"></i>
                            <span>รายงาน & ส่งออกข้อมูล</span>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 <?= $isReports ? 'text-emerald-600 dark:text-emerald-400' : 'opacity-40 text-slate-400 dark:text-slate-500' ?>"></i>
                    </a>

                    <?php if (\App\Core\Auth::isAdmin()): ?>
                    <div class="pt-4 px-3 py-2 text-[11px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 font-heading">
                        ADMINISTRATION
                    </div>

                    <!-- ผู้ใช้งาน & สิทธิ์ -->
                    <?php $isUsers = str_contains($currentReqUri, '/users'); ?>
                    <a href="<?= \App\Core\Router::url('/users') ?>" 
                       @click="sidebarOpen = false"
                       class="flex items-center justify-between px-4 py-3 rounded-2xl text-sm font-medium transition-all <?= $isUsers ? 'bg-emerald-50 dark:bg-[#181c26] text-emerald-900 dark:text-white font-semibold border border-emerald-500/30 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent' ?>">
                        <div class="flex items-center gap-3">
                            <i data-lucide="users" class="w-5 h-5 <?= $isUsers ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"></i>
                            <span>ผู้ใช้งาน & บทบาท</span>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 <?= $isUsers ? 'text-emerald-600 dark:text-emerald-400' : 'opacity-40 text-slate-400 dark:text-slate-500' ?>"></i>
                    </a>

                    <!-- ประเภทโครงการ -->
                    <?php $isCategories = str_contains($currentReqUri, '/categories'); ?>
                    <a href="<?= \App\Core\Router::url('/categories') ?>" 
                       @click="sidebarOpen = false"
                       class="flex items-center justify-between px-4 py-3 rounded-2xl text-sm font-medium transition-all <?= $isCategories ? 'bg-emerald-50 dark:bg-[#181c26] text-emerald-900 dark:text-white font-semibold border border-emerald-500/30 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent' ?>">
                        <div class="flex items-center gap-3">
                            <i data-lucide="tags" class="w-5 h-5 <?= $isCategories ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"></i>
                            <span>ประเภทโครงการ</span>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 <?= $isCategories ? 'text-emerald-600 dark:text-emerald-400' : 'opacity-40 text-slate-400 dark:text-slate-500' ?>"></i>
                    </a>

                    <!-- Audit Log -->
                    <?php $isAudit = str_contains($currentReqUri, '/audit-logs'); ?>
                    <a href="<?= \App\Core\Router::url('/audit-logs') ?>" 
                       @click="sidebarOpen = false"
                       class="flex items-center justify-between px-4 py-3 rounded-2xl text-sm font-medium transition-all <?= $isAudit ? 'bg-emerald-50 dark:bg-[#181c26] text-emerald-900 dark:text-white font-semibold border border-emerald-500/30 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent' ?>">
                        <div class="flex items-center gap-3">
                            <i data-lucide="history" class="w-5 h-5 <?= $isAudit ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"></i>
                            <span>ประวัติการทำงาน</span>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 <?= $isAudit ? 'text-emerald-600 dark:text-emerald-400' : 'opacity-40 text-slate-400 dark:text-slate-500' ?>"></i>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Bottom Icons -->
                <div class="p-4 border-t border-slate-200 dark:border-white/[0.08] flex items-center justify-between text-slate-500 dark:text-slate-400 shrink-0">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="toggleSidebar()" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-[#181a20] border border-slate-200 dark:border-white/[0.08] flex items-center justify-center hover:text-emerald-600 dark:hover:text-emerald-400 transition cursor-pointer" title="ซ่อน/ย่อแถบเมนู">
                            <i data-lucide="panel-left-close" class="w-4 h-4"></i>
                        </button>
                        <button type="button" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-[#181a20] border border-slate-200 dark:border-white/[0.08] flex items-center justify-center hover:text-emerald-600 dark:hover:text-emerald-400 transition" title="ช่วยเหลือ">
                            <i data-lucide="help-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400/80 font-mono">MPT V2.0</span>
                </div>
            </div>
        </aside>

        <!-- Backdrop for mobile sidebar (Covers entire screen smoothly with no right-edge white lines) -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false" 
             x-cloak 
             style="display: none;"
             class="fixed inset-0 z-40 modal-backdrop-smooth lg:hidden w-screen h-screen"
             :class="{ 'pointer-events-none': !sidebarOpen }"></div>

        <!-- Main Content Area -->
        <main id="main-content" class="flex-1 min-w-0 w-full max-w-full overflow-y-auto overflow-x-hidden p-3.5 sm:p-6 lg:p-8 bg-[#f8fafc] dark:bg-[#0f1014]">
            <!-- View Specific Content -->
            <?= $content ?? '' ?>
        </main>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        function safeCreateIcons(root) {
            if (window.lucide && typeof lucide.createIcons === 'function') {
                try {
                    const targetRoot = (root instanceof Element || root instanceof Document) ? root : document;
                    const unhandled = targetRoot.querySelectorAll('i[data-lucide]');
                    if (unhandled.length > 0) {
                        lucide.createIcons({ root: targetRoot });
                    }
                } catch (e) {
                    try { lucide.createIcons(); } catch (err) {}
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => safeCreateIcons());
        document.addEventListener('alpine:initialized', () => safeCreateIcons());
        window.addEventListener('load', () => safeCreateIcons());

        window.safeCreateIcons = safeCreateIcons;
        window.refreshIcons = safeCreateIcons;
        window.addEventListener('icons:refresh', safeCreateIcons);
    </script>

    <!-- Seamless SPA Navigation & Mutation Engine (Persistent Sidebar, Header & Zero-Reload Forms) -->
    <script>
        // Floating Toast Notification System
        window.showToast = function(message, type = 'success', duration = 3500) {
            if (!message) return;
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto flex items-start gap-3 p-4 rounded-2xl shadow-xl backdrop-blur-md border transition-all duration-300 transform translate-y-[-10px] opacity-0';
            
            let colorClasses = '';
            let iconSvg = '';
            let title = '';

            if (type === 'success') {
                colorClasses = 'bg-white/95 dark:bg-[#161a22]/95 border-emerald-500/30 dark:border-emerald-500/40 text-slate-800 dark:text-white shadow-emerald-500/10';
                iconSvg = '<div class="w-7 h-7 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg></div>';
                title = 'ดำเนินการสำเร็จ';
            } else if (type === 'error') {
                colorClasses = 'bg-white/95 dark:bg-[#161a22]/95 border-rose-500/30 dark:border-rose-500/40 text-slate-800 dark:text-white shadow-rose-500/10';
                iconSvg = '<div class="w-7 h-7 rounded-xl bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg></div>';
                title = 'เกิดข้อผิดพลาด';
            } else {
                colorClasses = 'bg-white/95 dark:bg-[#161a22]/95 border-blue-500/30 dark:border-blue-500/40 text-slate-800 dark:text-white shadow-blue-500/10';
                iconSvg = '<div class="w-7 h-7 rounded-xl bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>';
                title = 'แจ้งเตือนระบบ';
            }

            toast.className += ' ' + colorClasses;
            toast.innerHTML = `
                ${iconSvg}
                <div class="flex-1 min-w-0 pr-2">
                    <h4 class="text-xs font-bold font-heading mb-0.5">${title}</h4>
                    <p class="text-xs text-slate-600 dark:text-slate-300 font-sans leading-relaxed break-words">${message}</p>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition-colors cursor-pointer shrink-0" aria-label="ปิดการแจ้งเตือน">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            `;

            const closeBtn = toast.querySelector('button');
            const removeToast = () => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-[-10px]', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            };
            if (closeBtn) closeBtn.addEventListener('click', removeToast);

            container.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-[-10px]', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
            });

            setTimeout(removeToast, duration);
        };

        // Helper to check and display flash messages from carrier
        function checkAndDisplayFlash(doc = document) {
            const carrier = doc.getElementById('flash-message-carrier');
            if (!carrier) return;
            const success = carrier.getAttribute('data-success');
            const error = carrier.getAttribute('data-error');
            const info = carrier.getAttribute('data-info');
            if (success) window.showToast(success, 'success');
            if (error) window.showToast(error, 'error');
            if (info) window.showToast(info, 'info');

            // Clear carrier attributes so message isn't shown twice
            carrier.removeAttribute('data-success');
            carrier.removeAttribute('data-error');
            carrier.removeAttribute('data-info');
        }

        // Initialize flash check on first page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => checkAndDisplayFlash());
        } else {
            checkAndDisplayFlash();
        }

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

                    // 5. Clean up any teleported modal overlays attached to body that might linger (except persistent global modals)
                    document.querySelectorAll('body > [data-teleport-modal]:not([data-persistent-modal]), body > .modal-backdrop-smooth:not([data-persistent-modal]), body > [data-teleport-target]:not([data-persistent-modal]), body > [x-teleport-target]:not([data-persistent-modal]), body > [data-teleport-overlay]:not([data-persistent-modal])').forEach(el => {
                        try {
                            if (window.Alpine && typeof Alpine.destroyTree === 'function') {
                                Alpine.destroyTree(el);
                            }
                        } catch (e) {}
                        el.remove();
                    });

                    // Gracefully close any persistent global modals (e.g. user profile) on page navigation
                    try {
                        window.dispatchEvent(new CustomEvent('close-profile-modal'));
                    } catch (e) {}

                    // Unlock any stuck overflow-hidden on body/html
                    document.body.classList.remove('overflow-hidden');
                    document.documentElement.classList.remove('overflow-hidden');

                    // 6. Replace Main Content & Scroll to Top
                    currentMain.innerHTML = newMain.innerHTML;
                    currentMain.scrollTop = 0;

                    // 7. Re-evaluate <script> tags inside newMain
                    const scripts = Array.from(currentMain.querySelectorAll('script'));
                    for (const oldScript of scripts) {
                        try {
                            const newScript = document.createElement('script');
                            for (const attr of oldScript.attributes) {
                                newScript.setAttribute(attr.name, attr.value);
                            }
                            newScript.text = oldScript.text;
                            oldScript.parentNode.replaceChild(newScript, oldScript);
                        } catch (scriptErr) {
                            console.warn('MPT: Script re-evaluation warning:', scriptErr);
                        }
                    }

                    // 8. Re-initialize Alpine on currentMain
                    if (window.Alpine && typeof Alpine.initTree === 'function') {
                        try {
                            Alpine.initTree(currentMain);
                        } catch (e) {
                            console.warn('Alpine initTree:', e);
                        }
                    }

                    // 9. Re-render Lucide Icons
                    safeCreateIcons();

                    // 10. Re-initialize charts if on dashboard
                    if (typeof window.initDashboardCharts === 'function' && (document.getElementById('statusDonutChart') || document.getElementById('budgetComparisonChart') || document.getElementById('projectSuccessChart') || document.getElementById('categoryBarChart'))) {
                        window.initDashboardCharts();
                    }

                    // 11. Auto-close mobile sidebar if opened and unlock body
                    window.dispatchEvent(new CustomEvent('close-sidebar'));
                    const bodyEl = document.querySelector('body');
                    if (bodyEl) {
                        bodyEl.classList.remove('overflow-hidden');
                        if (window.Alpine) {
                            try {
                                if (typeof Alpine.$data === 'function') {
                                    const data = Alpine.$data(bodyEl);
                                    if (data && data.sidebarOpen) data.sidebarOpen = false;
                                } else if (bodyEl._x_dataStack) {
                                    bodyEl._x_dataStack.forEach(d => { if (d && d.sidebarOpen) d.sidebarOpen = false; });
                                }
                            } catch (e) {}
                        }
                    }

                    // 12. Check and display any flash message from destination
                    checkAndDisplayFlash(newDoc);

                } catch (err) {
                    console.error('SPA navigation error, falling back:', err);
                    window.location.href = url;
                } finally {
                    this.isNavigating = false;
                    this.finishProgress();
                }
            },

            // Asynchronous form submit handler for zero-reload Add/Edit/Delete
            async submitForm(form, submitter) {
                if (this.isNavigating) return;

                const method = (form.method || 'GET').toUpperCase();
                if (method !== 'POST') return;

                let actionUrl;
                try {
                    actionUrl = new URL(form.action || window.location.href, window.location.origin);
                } catch (err) {
                    form.submit();
                    return;
                }

                if (actionUrl.origin !== window.location.origin) {
                    form.submit();
                    return;
                }

                const path = actionUrl.pathname.toLowerCase();
                // Exclude file export / download / print / logout
                if (form.hasAttribute('data-no-spa') || path.includes('/export') || path.includes('/print') || path.includes('/logout') || path.endsWith('.pdf') || path.endsWith('.xlsx') || path.endsWith('.csv')) {
                    form.submit();
                    return;
                }

                this.isNavigating = true;
                this.startProgress();

                // Disable submit button & visual feedback
                const submitBtn = submitter || form.querySelector('button[type="submit"], input[type="submit"]');
                const originalBtnText = submitBtn ? submitBtn.innerHTML : null;
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.7';
                }

                // Smooth optimistic feedback for row deletion
                let targetRowOrCard = null;
                if (path.includes('/delete')) {
                    targetRowOrCard = form.closest('tr, .p-4.rounded-xl, .activity-card');
                    if (targetRowOrCard) {
                        targetRowOrCard.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                        targetRowOrCard.style.opacity = '0.35';
                        targetRowOrCard.style.transform = 'scale(0.98)';
                    }
                }

                try {
                    const formData = new FormData(form);
                    if (submitter && submitter.name) {
                        formData.append(submitter.name, submitter.value);
                    }

                    const res = await fetch(actionUrl.href, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html,application/xhtml+xml,application/xml'
                        }
                    });

                    if (!res.ok) {
                        console.warn('Form submit HTTP status ' + res.status);
                        try {
                            const errJson = await res.json();
                            if (errJson && errJson.message) {
                                window.showToast(errJson.message, 'error');
                                return;
                            }
                        } catch (e) {}
                        window.showToast('เกิดข้อผิดพลาดในการประมวลผลข้อมูล (รหัส ' + res.status + ')', 'error');
                        return;
                    }

                    const targetUrl = res.url || window.location.href;
                    const html = await res.text();
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, 'text/html');

                    const newMain = newDoc.querySelector('#main-content');
                    if (!newMain) {
                        try {
                            const json = JSON.parse(html);
                            if (json) {
                                if (json.message) {
                                    window.showToast(json.message, json.success === false ? 'error' : 'success');
                                }
                                this.navigate(window.location.href, false);
                                return;
                            }
                        } catch (e) {}
                        window.location.reload();
                        return;
                    }

                    // 1. Update Title
                    if (newDoc.title) {
                        document.title = newDoc.title;
                    }

                    // 2. Update Sidebar Active Links & Counts
                    const newSidebar = newDoc.querySelector('#sidebar-nav-items');
                    const currentSidebar = document.querySelector('#sidebar-nav-items');
                    if (newSidebar && currentSidebar) {
                        currentSidebar.innerHTML = newSidebar.innerHTML;
                    }

                    // 3. Update Browser History if destination URL changed
                    if (targetUrl !== window.location.href) {
                        window.history.pushState({ spa: true, url: targetUrl }, '', targetUrl);
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

                    // 5. Clean up any teleported modal overlays attached to body that might linger (except persistent global modals)
                    document.querySelectorAll('body > [data-teleport-modal]:not([data-persistent-modal]), body > .modal-backdrop-smooth:not([data-persistent-modal]), body > [data-teleport-target]:not([data-persistent-modal]), body > [x-teleport-target]:not([data-persistent-modal]), body > [data-teleport-overlay]:not([data-persistent-modal])').forEach(el => {
                        try {
                            if (window.Alpine && typeof Alpine.destroyTree === 'function') {
                                Alpine.destroyTree(el);
                            }
                        } catch (e) {}
                        el.remove();
                    });

                    // Gracefully close any persistent global modals (e.g. user profile) on form submission
                    try {
                        window.dispatchEvent(new CustomEvent('close-profile-modal'));
                    } catch (e) {}

                    // Unlock any stuck overflow-hidden on body/html
                    document.body.classList.remove('overflow-hidden');
                    document.documentElement.classList.remove('overflow-hidden');

                    // 6. Replace Main Content & Scroll to Top
                    currentMain.innerHTML = newMain.innerHTML;
                    currentMain.scrollTop = 0;

                    // 7. Re-evaluate <script> tags inside newMain
                    const scripts = Array.from(currentMain.querySelectorAll('script'));
                    for (const oldScript of scripts) {
                        try {
                            const newScript = document.createElement('script');
                            for (const attr of oldScript.attributes) {
                                newScript.setAttribute(attr.name, attr.value);
                            }
                            newScript.text = oldScript.text;
                            oldScript.parentNode.replaceChild(newScript, oldScript);
                        } catch (scriptErr) {
                            console.warn('MPT: Form submit script re-evaluation warning:', scriptErr);
                        }
                    }

                    // 8. Re-initialize Alpine on currentMain
                    if (window.Alpine && typeof Alpine.initTree === 'function') {
                        try {
                            Alpine.initTree(currentMain);
                        } catch (e) {
                            console.warn('Alpine initTree:', e);
                        }
                    }

                    // 9. Re-render Lucide Icons
                    safeCreateIcons();

                    // 10. Re-initialize charts if on dashboard
                    if (typeof window.initDashboardCharts === 'function' && (document.getElementById('statusDonutChart') || document.getElementById('budgetComparisonChart') || document.getElementById('projectSuccessChart') || document.getElementById('categoryBarChart'))) {
                        window.initDashboardCharts();
                    }

                    // 11. Extract and display Flash Message as Toast
                    checkAndDisplayFlash(newDoc);

                } catch (err) {
                    console.error('SPA form submit error, falling back:', err);
                    form.submit();
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.style.opacity = '1';
                        if (originalBtnText) submitBtn.innerHTML = originalBtnText;
                    }
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

        // Intercept both GET filter forms and POST mutation forms (Zero-Reload Add/Edit/Delete)
        document.addEventListener('submit', function(e) {
            if (e.defaultPrevented) return; // Respect client-side validation / confirmation cancellations

            const form = e.target;
            if (!form || !form.tagName || form.tagName.toLowerCase() !== 'form') return;
            if (form.target === '_blank' || form.hasAttribute('data-no-spa')) return;

            let actionUrl;
            try {
                actionUrl = new URL(form.action || window.location.href, window.location.origin);
            } catch (err) {
                return;
            }
            if (actionUrl.origin !== window.location.origin) return;

            const path = actionUrl.pathname.toLowerCase();
            if (path.includes('/export') || path.includes('/print') || path.includes('/logout') || path.endsWith('.pdf') || path.endsWith('.xlsx') || path.endsWith('.csv')) {
                return; // Let normal browser download / export proceed
            }

            const method = (form.method || 'GET').toUpperCase();

            if (method === 'GET') {
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
            } else if (method === 'POST') {
                e.preventDefault();
                window.AppSPA.submitForm(form, e.submitter);
            }
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
                    document.documentElement.style.colorScheme = 'dark';
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.style.colorScheme = 'light';
                }

                const metaScheme = document.getElementById('meta-color-scheme');
                if (metaScheme) metaScheme.content = isDark ? 'dark' : 'light';

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
