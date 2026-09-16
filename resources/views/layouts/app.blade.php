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
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>">
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
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>">
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
    
    <!-- Tailwind CSS (STRICTLY NO Bootstrap) - Self-Hosted Offline-First & SRI-Free -->
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>" src="<?= \App\Core\Router::url('/js/tailwindcss.min.js') ?>"></script>
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>">
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
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>">
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
                placement: config.placement || 'auto',
                actualPlacement: (config.placement && config.placement !== 'auto') ? config.placement : 'bottom',
                actualAlign: config.align || 'left',
                open: false,
                viewYear: viewDate.getFullYear(),
                viewMonth: viewDate.getMonth(),
                days: [],

                init() {
                    this.actualPlacement = (this.placement && this.placement !== 'auto') ? this.placement : 'bottom';
                    this.actualAlign = this.align || 'left';
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
                        this.updatePlacement();
                    }
                },

                updatePlacement() {
                    this.$nextTick(() => {
                        if (!this.$el) return;
                        const trigger = this.$el.querySelector('button');
                        if (!trigger) return;
                        const rect = trigger.getBoundingClientRect();

                        // Find scrollable container or viewport
                        const scrollContainer = this.$el.closest('.overflow-y-auto, [data-teleport-modal], .modal-box-smooth');
                        const containerRect = scrollContainer ? scrollContainer.getBoundingClientRect() : {
                            top: 0,
                            bottom: window.innerHeight,
                            left: 0,
                            right: window.innerWidth
                        };

                        const spaceAbove = rect.top - containerRect.top;
                        const spaceBelow = containerRect.bottom - rect.bottom;
                        const requiredHeight = 340; // Full calendar dropdown height

                        if (this.placement === 'top') {
                            // If forced 'top' but space above is insufficient and space below has more room, auto-flip to 'bottom'
                            this.actualPlacement = (spaceAbove < requiredHeight && spaceBelow > spaceAbove) ? 'bottom' : 'top';
                        } else if (this.placement === 'bottom') {
                            // If forced 'bottom' but space below is insufficient and space above has more room, auto-flip to 'top'
                            this.actualPlacement = (spaceBelow < requiredHeight && spaceAbove > spaceBelow) ? 'top' : 'bottom';
                        } else {
                            // 'auto': prioritize opening where there is sufficient room
                            if (spaceBelow >= requiredHeight) {
                                this.actualPlacement = 'bottom';
                            } else if (spaceAbove >= requiredHeight) {
                                this.actualPlacement = 'top';
                            } else {
                                this.actualPlacement = spaceBelow >= spaceAbove ? 'bottom' : 'top';
                            }
                        }

                        // Horizontal alignment auto-adjustment
                        if (rect.left + 288 > window.innerWidth - 16) {
                            this.actualAlign = 'right';
                        } else if (rect.right - 288 < 16) {
                            this.actualAlign = 'left';
                        } else {
                            this.actualAlign = this.align || 'left';
                        }

                        // Smoothly scroll dropdown into view if partially obscured
                        setTimeout(() => {
                            const dropdown = this.$refs && this.$refs.calendarDropdown;
                            if (dropdown && typeof dropdown.scrollIntoView === 'function') {
                                dropdown.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
                            }
                        }, 50);
                    });
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

        /**
         * Global Unified Custom Select Component for Alpine.js
         * Follows Emerald-Obsidian Design System (Theme-matching, Dark Mode, ARIA, Keyboard support)
         */
        function customSelect(config = {}) {
            return {
                name: config.name || '',
                id: config.id || config.name || '',
                value: String(config.value !== undefined && config.value !== null ? config.value : ''),
                placeholder: config.placeholder || '-- เลือก --',
                options: Array.isArray(config.options) ? config.options : [],
                searchable: !!config.searchable,
                searchPlaceholder: config.searchPlaceholder || 'ค้นหาตัวเลือก...',
                required: !!config.required,
                disabled: !!config.disabled,
                open: false,
                search: '',
                highlightedIndex: -1,
                selectedLabel: '',
                selectedDot: '',
                selectedBadge: '',

                init() {
                    this.syncFromValue();

                    // Two-way watch if parent model is passed
                    if (config.model && typeof this.$watch === 'function') {
                        this.$watch(config.model, (newVal) => {
                            if (String(newVal || '') !== String(this.value)) {
                                this.value = String(newVal || '');
                                this.syncFromValue();
                            }
                        });
                    }

                    // Listen for programmatic value updates
                    if (this.name) {
                        window.addEventListener('set-select-' + this.name, (e) => {
                            this.setValue(e.detail);
                        });
                    }
                    if (this.id && this.id !== this.name) {
                        window.addEventListener('set-select-' + this.id, (e) => {
                            this.setValue(e.detail);
                        });
                    }

                    this.$nextTick(() => {
                        if (window.lucide && typeof window.lucide.createIcons === 'function') {
                            window.lucide.createIcons();
                        }
                    });
                },

                syncFromValue() {
                    const match = this.options.find(opt => String(opt.value) === String(this.value));
                    if (match) {
                        this.selectedLabel = match.label || '';
                        this.selectedDot = match.dot || '';
                        this.selectedBadge = match.badge || '';
                    } else {
                        const emptyOpt = this.options.find(opt => String(opt.value) === '');
                        if (emptyOpt) {
                            this.selectedLabel = emptyOpt.label || this.placeholder;
                            this.selectedDot = emptyOpt.dot || '';
                            this.selectedBadge = emptyOpt.badge || '';
                        } else {
                            this.selectedLabel = this.placeholder;
                            this.selectedDot = '';
                            this.selectedBadge = '';
                        }
                    }
                },

                get filteredOptions() {
                    const q = (this.search || '').trim().toLowerCase();
                    if (!q) return this.options;
                    return this.options.filter(opt => {
                        const l = String(opt.label || '').toLowerCase();
                        const sub = String(opt.subtext || '').toLowerCase();
                        return l.includes(q) || sub.includes(q);
                    });
                },

                toggle() {
                    if (this.disabled) return;
                    this.open = !this.open;
                    if (this.open) {
                        this.search = '';
                        this.highlightedIndex = -1;
                        this.$nextTick(() => {
                            if (this.searchable) {
                                const input = this.$refs && this.$refs.searchInput;
                                if (input) input.focus();
                            }
                            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                                window.lucide.createIcons();
                            }
                        });
                    }
                },

                close() {
                    this.open = false;
                    this.search = '';
                    this.highlightedIndex = -1;
                },

                select(opt) {
                    if (!opt) return;
                    this.value = String(opt.value !== undefined && opt.value !== null ? opt.value : '');
                    this.selectedLabel = opt.label || '';
                    this.selectedDot = opt.dot || '';
                    this.selectedBadge = opt.badge || '';
                    this.close();
                    this.dispatchChange();
                },

                setValue(val) {
                    this.value = String(val !== undefined && val !== null ? val : '');
                    this.syncFromValue();
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
                            this.$el.dispatchEvent(new CustomEvent('select-changed', {
                                bubbles: true,
                                detail: { name: this.name, value: this.value }
                            }));
                        }
                        if (window.lucide && typeof window.lucide.createIcons === 'function') {
                            window.lucide.createIcons();
                        }
                    });
                },

                navigateOptions(dir) {
                    const opts = this.filteredOptions;
                    if (!opts || opts.length === 0) return;
                    if (dir === 'down') {
                        this.highlightedIndex = (this.highlightedIndex + 1) % opts.length;
                    } else if (dir === 'up') {
                        this.highlightedIndex = (this.highlightedIndex - 1 + opts.length) % opts.length;
                    }
                },

                selectHighlighted() {
                    const opts = this.filteredOptions;
                    if (this.highlightedIndex >= 0 && this.highlightedIndex < opts.length) {
                        this.select(opts[this.highlightedIndex]);
                    }
                }
            };
        }
        window.customSelect = customSelect;


        /**
         * Global Thai Million Compact Currency Formatter for Alpine.js & Client Scripts
         * e.g. 1000000 -> short: "1 ล้าน", full: "1,000,000.00 บาท"
         *      40700000 -> short: "40.7 ล้าน", full: "40,700,000.00 บาท"
         *      850000 -> short: "850,000.00", full: "บาท"
         */
        window.formatMillionCompact = function(amount, decimals = 2) {
            const amt = Number(amount || 0);
            const abs = Math.abs(amt);
            if (abs >= 1000000) {
                let m = (amt / 1000000).toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: decimals });
                return {
                    short: m + ' ล้าน',
                    full: amt.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' บาท',
                    is_million: true
                };
            }
            return {
                short: amt.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                full: 'บาท',
                is_million: false
            };
        };

        document.addEventListener('alpine:init', function() {
            if (window.Alpine && typeof Alpine.data === 'function') {
                Alpine.data('thaiDatePicker', thaiDatePicker);
            }
        });
    </script>

    <!-- Alpine.js, Lucide Icons & Chart.js (Local Offline-First with CDN fallback) -->
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>" src="<?= \App\Core\Router::url('/js/chart.umd.min.js') ?>"></script>
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>">
        if (typeof Chart === 'undefined') {
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            s.nonce = '<?= \App\Core\SecurityHeaders::nonce() ?>';
            document.head.appendChild(s);
        }
    </script>
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>" src="<?= \App\Core\Router::url('/js/lucide.min.js') ?>"></script>
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>">
        if (typeof lucide === 'undefined') {
            const s = document.createElement('script');
            s.src = 'https://unpkg.com/lucide@latest';
            s.nonce = '<?= \App\Core\SecurityHeaders::nonce() ?>';
            document.head.appendChild(s);
        }
    </script>
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>" defer src="<?= \App\Core\Router::url('/js/alpine.min.js') ?>"></script>
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>">
        (function() {
            function registerCoreAlpineComponents() {
                if (typeof Alpine === 'undefined' || window._mptAlpineCoreRegistered) return;
                window._mptAlpineCoreRegistered = true;

                // 1. Root Application Layout Component
                Alpine.data('appLayout', () => ({
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
                }));

                // 2. Theme Switcher Dropdown Component
                Alpine.data('themeDropdown', () => ({
                    open: false,
                    mode: localStorage.getItem('theme') || 'system',
                    resolvedDark: document.documentElement.classList.contains('dark'),
                    init() {
                        window.addEventListener('theme-changed', (e) => {
                            this.mode = (e.detail && e.detail.mode) ? e.detail.mode : (localStorage.getItem('theme') || 'system');
                            this.resolvedDark = document.documentElement.classList.contains('dark');
                        });
                        window.addEventListener('close-other-popups', (e) => {
                            if (e.detail && e.detail.source !== 'theme') {
                                this.open = false;
                            }
                        });
                    },
                    toggle() {
                        this.open = !this.open;
                        if (this.open) {
                            window.dispatchEvent(new CustomEvent('close-other-popups', { detail: { source: 'theme' } }));
                        }
                    },
                    close() {
                        this.open = false;
                    }
                }));

                // 3. User Profile Dropdown Component
                Alpine.data('userProfileMenu', () => ({
                    userMenuOpen: false,
                    profileModalOpen: false,
                    profileTab: 'general',
                    init() {
                        this.$watch('profileModalOpen', v => {
                            if (v) setTimeout(() => { if (typeof safeCreateIcons === 'function') safeCreateIcons(); }, 50);
                        });
                        this.$watch('profileTab', () => {
                            setTimeout(() => { if (typeof safeCreateIcons === 'function') safeCreateIcons(); }, 50);
                        });
                        window.addEventListener('close-other-popups', (e) => {
                            if (e.detail && e.detail.source !== 'user') {
                                this.userMenuOpen = false;
                            }
                        });
                    },
                    toggleMenu() {
                        this.userMenuOpen = !this.userMenuOpen;
                        if (this.userMenuOpen) {
                            window.dispatchEvent(new CustomEvent('close-other-popups', { detail: { source: 'user' } }));
                        }
                    },
                    closeMenu() {
                        this.userMenuOpen = false;
                    },
                    openProfile(tab = 'general') {
                        this.userMenuOpen = false;
                        this.profileTab = tab;
                        this.profileModalOpen = true;
                        setTimeout(() => { if (typeof safeCreateIcons === 'function') safeCreateIcons(); }, 50);
                    }
                }));

                // 4. Fiscal Year Dropdown Component
                Alpine.data('fiscalYearDropdown', () => ({
                    open: false,
                    init() {
                        window.addEventListener('close-other-popups', (e) => {
                            if (e.detail && e.detail.source !== 'fiscal') {
                                this.open = false;
                            }
                        });
                    },
                    toggle() {
                        this.open = !this.open;
                        if (this.open) {
                            window.dispatchEvent(new CustomEvent('close-other-popups', { detail: { source: 'fiscal' } }));
                            setTimeout(() => { if (typeof safeCreateIcons === 'function') safeCreateIcons(); }, 30);
                        }
                    },
                    close() {
                        this.open = false;
                    }
                }));
            }

            document.addEventListener('alpine:init', registerCoreAlpineComponents);
            if (typeof Alpine !== 'undefined') {
                registerCoreAlpineComponents();
            }

            window.addEventListener('DOMContentLoaded', function() {
                if (typeof Alpine === 'undefined') {
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
                    s.nonce = '<?= \App\Core\SecurityHeaders::nonce() ?>';
                    s.defer = true;
                    document.head.appendChild(s);
                }
            });
        })();
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

        /* ========================================================= */
        /* International Standard Accessible Toast Design System     */
        /* (WCAG 2.1 AAA Contrast, Dual-Theme, Solid Surface)         */
        /* ========================================================= */
        .mpt-toast {
            background-color: #ffffff !important;
            color: #0f172a !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.16), 0 8px 10px -6px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 1rem !important;
            overflow: hidden !important;
            opacity: 1 !important;
            transition: all 250ms cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        html.dark .mpt-toast {
            background-color: #181d27 !important;
            color: #f8fafc !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 25px 35px -5px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(255, 255, 255, 0.08) !important;
        }

        .mpt-toast-title {
            color: #0f172a !important;
            font-family: 'Prompt', sans-serif !important;
            font-weight: 700 !important;
            font-size: 0.8125rem !important; /* 13px */
            line-height: 1.25rem !important;
        }
        html.dark .mpt-toast-title {
            color: #ffffff !important;
        }

        .mpt-toast-desc {
            color: #334155 !important; /* Slate-700, 9.6:1 contrast against white */
            font-family: 'Sarabun', sans-serif !important;
            font-weight: 400 !important;
            font-size: 0.75rem !important; /* 12px */
            line-height: 1.25rem !important;
        }
        html.dark .mpt-toast-desc {
            color: #cbd5e1 !important; /* Slate-300, 10.2:1 contrast against #181d27 */
        }

        .mpt-toast-close {
            color: #64748b !important;
            border-radius: 0.5rem !important;
            padding: 0.375rem !important;
            transition: color 150ms ease, background-color 150ms ease !important;
            cursor: pointer !important;
        }
        .mpt-toast-close:hover {
            color: #0f172a !important;
            background-color: #f1f5f9 !important;
        }
        html.dark .mpt-toast-close {
            color: #94a3b8 !important;
        }
        html.dark .mpt-toast-close:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
        }

        /* Semantic Left Accent Borders */
        .mpt-toast-warning {
            border-left: 4px solid #f59e0b !important;
        }
        html.dark .mpt-toast-warning {
            border-left: 4px solid #fbbf24 !important;
        }

        .mpt-toast-success {
            border-left: 4px solid #10b981 !important;
        }
        html.dark .mpt-toast-success {
            border-left: 4px solid #34d399 !important;
        }

        .mpt-toast-error {
            border-left: 4px solid #f43f5e !important;
        }
        html.dark .mpt-toast-error {
            border-left: 4px solid #fb7185 !important;
        }

        .mpt-toast-info {
            border-left: 4px solid #0284c7 !important;
        }
        html.dark .mpt-toast-info {
            border-left: 4px solid #38bdf8 !important;
        }


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
        button:focus, button:focus-visible, button:active {
            outline: none !important;
            box-shadow: none !important;
        }

        /* Light table rows hover */
        tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Form Inputs & Selects (Light) - Instant crisp border, smooth glow */
        input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not(:focus):not(:focus-visible),
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
        input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):focus,
        input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):focus-visible,
        select:focus, select:focus-visible,
        textarea:focus, textarea:focus-visible,
        .focus\:border-emerald-500:focus, .focus\:border-emerald-500:focus-visible {
            border: 1px solid #10b981 !important;
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important;
            outline: none !important;
            outline-offset: 0 !important;
        }

        /* Range sliders: Clean tactile interaction without rectangular focus rings */
        input[type="range"],
        input[type="range"]:focus,
        input[type="range"]:focus-visible,
        input[type="range"]:active {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            background-color: transparent;
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
        html.dark .bg-white:not([data-keep-white]) {
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

        /* Dark Mode Alert Banners & Callout Contrast Guards (Prevents white text on light backgrounds) */
        html.dark .bg-rose-50:not([class*="dark:bg-"]) {
            background-color: rgba(244, 63, 94, 0.16) !important;
            border-color: rgba(244, 63, 94, 0.4) !important;
            color: #fecdd3 !important;
        }
        html.dark .bg-rose-50 h1, html.dark .bg-rose-50 h2, html.dark .bg-rose-50 h3, html.dark .bg-rose-50 h4,
        html.dark [class*="bg-rose-"] h1, html.dark [class*="bg-rose-"] h2, html.dark [class*="bg-rose-"] h3, html.dark [class*="bg-rose-"] h4 {
            color: #fecdd3 !important;
        }
        html.dark .bg-rose-50 .text-rose-900, html.dark .bg-rose-50 .text-rose-800, html.dark .bg-rose-50 .text-rose-700 {
            color: #fecdd3 !important;
        }
        html.dark .bg-amber-50:not([class*="dark:bg-"]) {
            background-color: rgba(245, 158, 11, 0.16) !important;
            border-color: rgba(245, 158, 11, 0.4) !important;
            color: #fde68a !important;
        }
        html.dark .bg-amber-50 h1, html.dark .bg-amber-50 h2, html.dark .bg-amber-50 h3, html.dark .bg-amber-50 h4,
        html.dark [class*="bg-amber-"] h1, html.dark [class*="bg-amber-"] h2, html.dark [class*="bg-amber-"] h3, html.dark [class*="bg-amber-"] h4 {
            color: #fde68a !important;
        }
        html.dark .bg-emerald-50:not([class*="dark:bg-"]) {
            background-color: rgba(16, 185, 129, 0.16) !important;
            border-color: rgba(16, 185, 129, 0.4) !important;
            color: #a7f3d0 !important;
        }
        html.dark .bg-emerald-50 h1, html.dark .bg-emerald-50 h2, html.dark .bg-emerald-50 h3, html.dark .bg-emerald-50 h4,
        html.dark [class*="bg-emerald-"] h1, html.dark [class*="bg-emerald-"] h2, html.dark [class*="bg-emerald-"] h3, html.dark [class*="bg-emerald-"] h4 {
            color: #a7f3d0 !important;
        }
        html.dark .bg-blue-50:not([class*="dark:bg-"]) {
            background-color: rgba(59, 130, 246, 0.16) !important;
            border-color: rgba(59, 130, 246, 0.4) !important;
            color: #bfdbfe !important;
        }
        html.dark .bg-blue-50 h1, html.dark .bg-blue-50 h2, html.dark .bg-blue-50 h3, html.dark .bg-blue-50 h4,
        html.dark [class*="bg-blue-"] h1, html.dark [class*="bg-blue-"] h2, html.dark [class*="bg-blue-"] h3, html.dark [class*="bg-blue-"] h4 {
            color: #bfdbfe !important;
        }

        /* Dark table rows hover */
        html.dark tr:hover {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        /* Form Inputs & Selects Dark - Sleek resting border, Vibrant Emerald focus ring matching Light Mode */
        html.dark input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not(:focus):not(:focus-visible),
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
        html.dark input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):focus,
        html.dark input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):focus-visible,
        html.dark select:focus, html.dark select:focus-visible,
        html.dark textarea:focus, html.dark textarea:focus-visible,
        html.dark .focus\:border-emerald-500:focus, html.dark .focus\:border-emerald-500:focus-visible {
            border: 1px solid #10b981 !important;
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.35) !important;
            outline: none !important;
            outline-offset: 0 !important;
        }

        html.dark input[type="range"],
        html.dark input[type="range"]:focus,
        html.dark input[type="range"]:focus-visible,
        html.dark input[type="range"]:active {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            background-color: transparent !important;
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
            height: 100%;
            height: 100dvh;
        }
        *, *::before, *::after {
            box-sizing: border-box;
        }
        #main-content {
            max-width: 100% !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            touch-action: pan-y !important;
            overscroll-behavior-y: contain !important;
        }
        #main-content table,
        #main-content table * {
            max-width: none !important;
        }
        /* Mobile horizontal scroll containers (pills, tables) must allow vertical page scrolling seamlessly */
        .overflow-x-auto, [class*="overflow-x-auto"] {
            -webkit-overflow-scrolling: touch !important;
            overscroll-behavior-x: contain !important;
            touch-action: pan-x pan-y !important;
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
<body class="h-full antialiased font-sans text-slate-800 dark:text-slate-100 bg-[#f8fafc] dark:bg-[#0f1014] flex flex-col transition-colors duration-150 w-full max-w-full overflow-x-hidden" 
      :class="{ 'overflow-hidden': sidebarOpen }" 
      @close-sidebar.window="sidebarOpen = false"
      x-data="appLayout">

    <?php 
        $flashSuccess = \App\Core\Session::flash('success');
        $flashError = \App\Core\Session::flash('error');
        $flashWarning = \App\Core\Session::flash('warning');
        $flashInfo = \App\Core\Session::flash('info');
    ?>

    <!-- SPA Top Progress Bar (Neon Green Glow) -->
    <div id="spa-progress" class="fixed top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 via-teal-300 to-green-500 shadow-[0_0_12px_#10b981] z-[9999] transition-all duration-200 pointer-events-none opacity-0" style="width: 0%;"></div>

    <!-- Floating Toast Notifications Container (W3C WAI-ARIA Alert Region) -->
    <div id="toast-container" 
         class="fixed top-4 right-4 sm:top-6 sm:right-6 z-[999999] pointer-events-none flex flex-col gap-3 max-w-md w-full px-4 sm:px-0"
         role="region" 
         aria-label="การแจ้งเตือนระบบ"></div>

    <!-- Global Accessible Confirmation Modal Container (W3C WAI-ARIA alertdialog) -->
    <div id="mpt-confirm-modal" 
         class="fixed inset-0 z-[100000] hidden items-center justify-center p-4 sm:p-6 bg-slate-950/65 backdrop-blur-sm transition-opacity duration-200 opacity-0 pointer-events-auto"
         role="alertdialog" 
         aria-modal="true" 
         aria-labelledby="mpt-confirm-title" 
         aria-describedby="mpt-confirm-message">
        <div id="mpt-confirm-box" 
             class="relative w-full max-w-md bg-white dark:bg-[#181a20] rounded-3xl shadow-2xl border border-slate-200 dark:border-white/10 p-6 sm:p-7 transform scale-95 transition-all duration-200 flex flex-col gap-5">
            
            <div class="flex items-start gap-4">
                <div id="mpt-confirm-icon-wrap" class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-xs"></div>
                <div class="flex-1 min-w-0 pt-0.5">
                    <h3 id="mpt-confirm-title" class="text-base sm:text-lg font-bold font-heading text-slate-900 dark:text-white leading-snug"></h3>
                    <p id="mpt-confirm-message" class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 font-sans mt-1.5 leading-relaxed break-words"></p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-white/[0.06]">
                <button type="button" 
                        id="mpt-confirm-cancel-btn" 
                        class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 text-xs sm:text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-400">
                    ยกเลิก
                </button>
                <button type="button" 
                        id="mpt-confirm-accept-btn" 
                        class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white shadow-md transition-all cursor-pointer focus:outline-none focus:ring-2">
                    ยืนยัน
                </button>
            </div>
        </div>
    </div>

    <!-- Flash Message Carrier for SPA and initial page load -->
    <div id="flash-message-carrier" 
         style="display: none;" 
         data-success="<?= htmlspecialchars($flashSuccess ?? '', ENT_QUOTES) ?>" 
         data-error="<?= htmlspecialchars($flashError ?? '', ENT_QUOTES) ?>"
         data-warning="<?= htmlspecialchars($flashWarning ?? '', ENT_QUOTES) ?>"
         data-info="<?= htmlspecialchars($flashInfo ?? '', ENT_QUOTES) ?>"></div>

    <!-- Top Navigation Bar -->
    <header class="bg-white/98 dark:bg-[#101115]/98 border-b border-slate-200 dark:border-white/[0.08] sticky top-0 z-30 shadow-sm dark:shadow-md transition-colors duration-150 w-full max-w-full will-change-transform">
        <div class="px-2.5 sm:px-6 lg:px-8 flex items-center justify-between h-13 sm:h-14 w-full max-w-full">
            <!-- Left Logo & Title -->
            <div class="flex items-center gap-1.5 sm:gap-2 min-w-0 flex-1 sm:flex-initial">
                <button type="button" 
                        @click="toggleSidebar()" 
                        class="w-8.5 h-8.5 sm:w-9 sm:h-9 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 hover:text-emerald-600 dark:hover:text-emerald-400 transition shrink-0 cursor-pointer flex items-center justify-center" 
                        :title="(window.innerWidth >= 1024 && !desktopSidebarOpen) ? 'แสดงแถบเมนู' : 'เปิด/ปิดแถบเมนู'"
                        aria-label="เปิด/ปิดแถบเมนู">
                    <i data-lucide="menu" class="w-4.5 h-4.5 sm:w-5 sm:h-5"></i>
                </button>
                <a href="<?= \App\Core\Router::url('/dashboard') ?>" class="shrink-0 flex items-center gap-1.5 sm:gap-2 group" title="ระบบติดตามและบริหารโครงการเทศบาล">
                    <!-- ตราสัญลักษณ์ร่วม (เทศบาล x กปท.) พื้นหลังขาวคมชัดทุกธีม ขนาดกะทัดรัดได้สัดส่วน -->
                    <div data-keep-white 
                         style="background-color: #ffffff !important;" 
                         class="h-8 px-2.5 py-1 rounded-xl bg-white shadow-xs border border-slate-200/90 dark:border-white/30 flex items-center gap-2 shrink-0 group-hover:scale-[1.02] transition-transform">
                        <img src="<?= \App\Core\Router::url('/images/mobile-logo.webp') ?>" 
                             alt="โลโก้เทศบาล" 
                             class="h-6 w-6 object-contain shrink-0"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="display:none;" class="w-6 h-6 rounded-md bg-gradient-to-tr from-emerald-500 to-teal-400 items-center justify-center text-slate-950 font-bold">
                            <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                        </div>
                        <div class="h-4 w-px bg-slate-200 shrink-0"></div>
                        <img src="<?= \App\Core\Router::url('/images/kpth-logo.png') ?>" 
                             alt="โลโก้ กปท. กองทุนหลักประกันสุขภาพท้องถิ่น" 
                             class="h-5 w-auto max-w-[65px] object-contain shrink-0">
                    </div>
                </a>
                <div class="min-w-0 hidden md:block">
                    <a href="<?= \App\Core\Router::url('/dashboard') ?>" class="font-bold font-heading text-slate-900 dark:text-white tracking-tight flex items-center gap-1.5 leading-tight hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                        <span class="text-xs sm:text-sm font-bold whitespace-nowrap">ระบบติดตามและบริหารโครงการเทศบาล</span>
                    </a>
                    <p class="text-[10px] sm:text-[11px] text-emerald-600 dark:text-emerald-400 font-medium truncate leading-tight mt-0.5">คณะอนุกรรมการฝ่ายติดตามและการประเมินผล</p>
                </div>
            </div>

            <!-- Right: Theme Switcher, Role Switcher & User Profile -->
            <div class="flex items-center gap-1 sm:gap-2 shrink-0">

                <!-- Standard 3-State Theme Switcher (Light / Dark / System) -->
                <div class="relative shrink-0" x-data="themeDropdown">
                    
                    <button type="button" 
                            @click="toggle()" 
                            @keydown.escape="close()"
                            id="theme-dropdown-btn"
                            aria-haspopup="menu"
                            :aria-expanded="open.toString()"
                            aria-label="เลือกโหมดการแสดงผล สว่าง มืด หรือตามระบบ"
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
                         @click.outside="close()" 
                         @keydown.escape.window="close()"
                         x-cloak 
                         role="menu"
                         aria-orientation="vertical"
                         aria-labelledby="theme-dropdown-btn"
                         style="display: none;"
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

                        <div class="space-y-0.5" role="none">
                            <!-- 1. โหมดสว่าง (Light) -->
                            <button type="button" 
                                    role="menuitemradio"
                                    :aria-checked="mode === 'light'"
                                    @click="setAppTheme('light'); close()" 
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
                                    role="menuitemradio"
                                    :aria-checked="mode === 'dark'"
                                    @click="setAppTheme('dark'); close()" 
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
                                    role="menuitemradio"
                                    :aria-checked="mode === 'system'"
                                    @click="setAppTheme('system'); close()" 
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
                    'admin'     => ['label' => 'ผู้ดูแลระบบ', 'badge' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60', 'avatar_bg' => 'bg-gradient-to-tr from-purple-600 to-indigo-600 text-white shadow-sm shadow-purple-500/25', 'icon' => 'shield-check'],
                    'executive' => ['label' => 'ผู้บริหาร (ดูอย่างเดียว)', 'badge' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60', 'avatar_bg' => 'bg-gradient-to-tr from-amber-500 to-orange-500 text-white shadow-sm shadow-amber-500/25', 'icon' => 'award'],
                    'staff'     => ['label' => 'เจ้าหน้าที่ (Staff)', 'badge' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60', 'avatar_bg' => 'bg-gradient-to-tr from-emerald-600 to-teal-600 text-white shadow-sm shadow-emerald-500/25', 'icon' => 'user-check'],
                    'officer'   => ['label' => 'เจ้าหน้าที่', 'badge' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60', 'avatar_bg' => 'bg-gradient-to-tr from-blue-600 to-cyan-600 text-white shadow-sm shadow-blue-500/25', 'icon' => 'user-check'],
                ];
                $currentRoleInfo = $roleMeta[$userRole] ?? [
                    'label'     => $currentUser['role_label'] ?? 'ผู้ใช้งาน',
                    'badge'     => 'bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/10',
                    'avatar_bg' => 'bg-gradient-to-tr from-slate-600 to-slate-700 text-white shadow-sm',
                    'icon'      => 'user'
                ];
                $userName = trim($currentUser['name'] ?? 'ผู้ใช้งาน');
                ?>

                <!-- Modern Interactive User Profile Dropdown Pill & Modals -->
                <!-- Modern Interactive User Profile Dropdown Pill & Modals -->
                <div class="relative shrink-0" 
                     x-data="userProfileMenu"
                     @close-profile-modal.window="profileModalOpen = false; userMenuOpen = false"
                     @open-profile-modal.window="openProfile(($event.detail && $event.detail.tab) ? $event.detail.tab : 'general')">
                    
                    <!-- Profile Button Trigger -->
                    <button type="button" 
                            @click.stop="toggleMenu()" 
                            id="user-profile-menu-btn"
                            class="flex items-center gap-2 p-1 sm:py-1 sm:pl-1.5 sm:pr-2.5 rounded-2xl bg-slate-100 dark:bg-[#181a20] hover:bg-slate-200/80 dark:hover:bg-white/5 border border-slate-200 dark:border-white/[0.08] hover:border-slate-300 dark:hover:border-white/20 transition-all duration-150 cursor-pointer shadow-xs group"
                            :class="{ 'ring-2 ring-purple-500/25 border-purple-500/50 bg-purple-50/60 dark:bg-purple-500/10': userMenuOpen }"
                            title="ข้อมูลผู้ใช้งานและเมนูบัญชี">
                        
                        <!-- Refined Circular Avatar with Online Indicator -->
                        <div class="relative shrink-0">
                            <div class="w-8 h-8 sm:w-8.5 sm:h-8.5 rounded-full <?= $currentRoleInfo['avatar_bg'] ?> flex items-center justify-center ring-2 ring-white/80 dark:ring-white/10 shadow-xs">
                                <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                </svg>
                            </div>
                            <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-[#181a20]" title="ออนไลน์"></span>
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
                         @click.outside="closeMenu()" 
                         x-cloak 
                         style="display: none;"
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
                                <div class="relative shrink-0">
                                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full <?= $currentRoleInfo['avatar_bg'] ?> flex items-center justify-center shrink-0 ring-2 ring-white dark:ring-white/10 shadow-sm">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                        </svg>
                                    </div>
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-[#181a20]" title="ออนไลน์"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs sm:text-sm font-bold font-heading text-slate-900 dark:text-white truncate">
                                        <?= htmlspecialchars($currentUser['name'] ?? 'ผู้ใช้งาน') ?>
                                    </div>
                                    <?php if (!empty($currentUser['position'])): ?>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                            <?= htmlspecialchars($currentUser['position']) ?>
                                        </div>
                                    <?php elseif (!empty($currentUser['department_name'])): ?>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                            <?= htmlspecialchars($currentUser['department_name']) ?>
                                        </div>
                                    <?php endif; ?>
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
                                    @click.stop="openProfile('general')"
                                    class="w-full text-left px-3 py-2.5 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 flex items-center gap-2.5 text-slate-700 dark:text-slate-200 transition cursor-pointer font-medium">
                                <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">ข้อมูลส่วนตัว (My Profile)</div>
                                    <div class="text-[10px] text-slate-400">ดูสังกัดและตำแหน่งงาน</div>
                                </div>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                            </button>

                            <!-- 2. Open Password Modal -->
                            <button type="button" 
                                    @click.stop="openProfile('security')"
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
                
                <!-- Mobile Sidebar Brand Header with Municipal & KPTH Logo -->
                <div class="lg:hidden px-4 py-3.5 border-b border-slate-200/80 dark:border-white/[0.08] shrink-0 bg-slate-50/80 dark:bg-white/[0.02]">
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <!-- Unified Emblem Pill (Compact & Crisp White) -->
                        <a href="<?= \App\Core\Router::url('/dashboard') ?>" 
                           data-keep-white 
                           style="background-color: #ffffff !important;" 
                           class="inline-flex items-center gap-2 px-2.5 py-1 rounded-xl bg-white shadow-xs border border-slate-200/90 dark:border-white/30 group hover:scale-[1.02] transition-transform" 
                           title="ระบบติดตามและบริหารโครงการเทศบาล">
                            <img src="<?= \App\Core\Router::url('/images/mobile-logo.webp') ?>" 
                                 alt="โลโก้เทศบาล" 
                                 class="h-6 w-6 object-contain shrink-0"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display:none;" class="w-6 h-6 rounded-md bg-gradient-to-tr from-emerald-500 to-teal-400 items-center justify-center text-slate-950 font-bold">
                                <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                            </div>
                            <div class="h-4 w-px bg-slate-200 shrink-0"></div>
                            <img src="<?= \App\Core\Router::url('/images/kpth-logo.png') ?>" 
                                 alt="กปท." 
                                 class="h-5 w-auto max-w-[65px] object-contain shrink-0">
                        </a>

                        <!-- Close Drawer Button on Mobile -->
                        <button type="button" 
                                @click="sidebarOpen = false" 
                                class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-white/10 transition shrink-0 cursor-pointer flex items-center justify-center"
                                aria-label="ปิดเมนู">
                            <i data-lucide="x" class="w-4.5 h-4.5"></i>
                        </button>
                    </div>

                    <!-- Brand Title & Subtitle (Full-width, Beautifully Typeset, No Truncation) -->
                    <a href="<?= \App\Core\Router::url('/dashboard') ?>" class="block group">
                        <div class="font-bold text-xs sm:text-sm font-heading text-slate-900 dark:text-white leading-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                            ระบบติดตามและบริหารโครงการเทศบาล
                        </div>
                        <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold leading-tight mt-0.5 truncate">
                            คณะอนุกรรมการฝ่ายติดตามและการประเมินผล
                        </p>
                    </a>
                </div>

                <div id="sidebar-nav-items" class="px-3 py-3 space-y-1 overflow-y-auto flex-1">
                    <div class="px-3 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 font-heading select-none">
                        MENU
                    </div>
                    
                    <!-- แดชบอร์ดภาพรวม -->
                    <?php 
                        $currentReqUri = $_SERVER['REQUEST_URI'] ?? '/';
                        $isDashboard = str_contains($currentReqUri, '/dashboard'); 
                    ?>
                    <a href="<?= \App\Core\Router::url('/dashboard') ?>" 
                       @click="sidebarOpen = false"
                       class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all group select-none <?= $isDashboard ? 'bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 font-semibold shadow-2xs before:absolute before:left-0 before:top-2.5 before:bottom-2.5 before:w-1 before:bg-emerald-600 dark:before:bg-emerald-400 before:rounded-r-full' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/80 dark:hover:bg-white/[0.05]' ?>">
                        <i data-lucide="layout-dashboard" class="w-5 h-5 shrink-0 transition-colors <?= $isDashboard ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                        <span class="truncate">แดชบอร์ดภาพรวม</span>
                    </a>

                    <!-- โครงการหลัก & กิจกรรมหลัก -->
                    <?php $isProjects = (str_contains($currentReqUri, '/projects') || str_contains($currentReqUri, '/sub-projects')); ?>
                    <a href="<?= \App\Core\Router::url('/projects') ?>" 
                       @click="sidebarOpen = false"
                       class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all group select-none <?= $isProjects ? 'bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 font-semibold shadow-2xs before:absolute before:left-0 before:top-2.5 before:bottom-2.5 before:w-1 before:bg-emerald-600 dark:before:bg-emerald-400 before:rounded-r-full' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/80 dark:hover:bg-white/[0.05]' ?>">
                        <i data-lucide="folder-kanban" class="w-5 h-5 shrink-0 transition-colors <?= $isProjects ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                        <span class="whitespace-nowrap">โครงการหลัก & กิจกรรมหลัก</span>
                    </a>

                    <!-- งบประมาณ & เบิกจ่าย -->
                    <?php $isBudgets = str_contains($currentReqUri, '/budgets'); ?>
                    <a href="<?= \App\Core\Router::url('/budgets') ?>" 
                       @click="sidebarOpen = false"
                       class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all group select-none <?= $isBudgets ? 'bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 font-semibold shadow-2xs before:absolute before:left-0 before:top-2.5 before:bottom-2.5 before:w-1 before:bg-emerald-600 dark:before:bg-emerald-400 before:rounded-r-full' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/80 dark:hover:bg-white/[0.05]' ?>">
                        <i data-lucide="wallet" class="w-5 h-5 shrink-0 transition-colors <?= $isBudgets ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                        <span class="truncate">งบประมาณ & เบิกจ่าย</span>
                    </a>

                    <!-- รายงาน & ส่งออกข้อมูล -->
                    <?php $isReports = str_contains($currentReqUri, '/reports'); ?>
                    <a href="<?= \App\Core\Router::url('/reports') ?>" 
                       @click="sidebarOpen = false"
                       class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all group select-none <?= $isReports ? 'bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 font-semibold shadow-2xs before:absolute before:left-0 before:top-2.5 before:bottom-2.5 before:w-1 before:bg-emerald-600 dark:before:bg-emerald-400 before:rounded-r-full' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/80 dark:hover:bg-white/[0.05]' ?>">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5 shrink-0 transition-colors <?= $isReports ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                        <span class="truncate">รายงาน & ส่งออกข้อมูล</span>
                    </a>

                    <?php if (\App\Core\Auth::isAdmin()): ?>
                    <div class="pt-4 mt-3 border-t border-slate-200/60 dark:border-white/[0.06] px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 font-heading select-none">
                        ADMINISTRATION
                    </div>

                    <!-- ผู้ใช้งาน & สิทธิ์ -->
                    <?php $isUsers = str_contains($currentReqUri, '/users'); ?>
                    <a href="<?= \App\Core\Router::url('/users') ?>" 
                       @click="sidebarOpen = false"
                       class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all group select-none <?= $isUsers ? 'bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 font-semibold shadow-2xs before:absolute before:left-0 before:top-2.5 before:bottom-2.5 before:w-1 before:bg-emerald-600 dark:before:bg-emerald-400 before:rounded-r-full' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/80 dark:hover:bg-white/[0.05]' ?>">
                        <i data-lucide="users" class="w-5 h-5 shrink-0 transition-colors <?= $isUsers ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                        <span class="truncate">ผู้ใช้งาน & บทบาท</span>
                    </a>

                    <!-- ประเภทโครงการ -->
                    <?php $isCategories = str_contains($currentReqUri, '/categories'); ?>
                    <a href="<?= \App\Core\Router::url('/categories') ?>" 
                       @click="sidebarOpen = false"
                       class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all group select-none <?= $isCategories ? 'bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 font-semibold shadow-2xs before:absolute before:left-0 before:top-2.5 before:bottom-2.5 before:w-1 before:bg-emerald-600 dark:before:bg-emerald-400 before:rounded-r-full' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/80 dark:hover:bg-white/[0.05]' ?>">
                        <i data-lucide="tags" class="w-5 h-5 shrink-0 transition-colors <?= $isCategories ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                        <span class="truncate">ประเภทโครงการ</span>
                    </a>

                    <!-- Audit Log -->
                    <?php $isAudit = str_contains($currentReqUri, '/audit-logs'); ?>
                    <a href="<?= \App\Core\Router::url('/audit-logs') ?>" 
                       @click="sidebarOpen = false"
                       class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all group select-none <?= $isAudit ? 'bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 font-semibold shadow-2xs before:absolute before:left-0 before:top-2.5 before:bottom-2.5 before:w-1 before:bg-emerald-600 dark:before:bg-emerald-400 before:rounded-r-full' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/80 dark:hover:bg-white/[0.05]' ?>">
                        <i data-lucide="history" class="w-5 h-5 shrink-0 transition-colors <?= $isAudit ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                        <span class="truncate">ประวัติการทำงาน</span>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Bottom Collapse Bar -->
                <div class="p-2.5 border-t border-slate-200/80 dark:border-white/[0.08] bg-slate-50/50 dark:bg-white/[0.01] shrink-0 select-none">
                    <button type="button" 
                            @click="toggleSidebar()" 
                            class="w-full inline-flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-white/10 transition cursor-pointer" 
                            title="ซ่อนแถบเมนู">
                        <i data-lucide="panel-left-close" class="w-4 h-4 text-slate-400"></i>
                        <span>ย่อเมนู</span>
                    </button>
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
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>">
        function safeCreateIcons(root) {
            if (window.lucide && typeof lucide.createIcons === 'function') {
                try {
                    const targetRoot = (root instanceof Element || root instanceof Document) ? root : document;
                    lucide.createIcons({ root: targetRoot });
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

    <!-- Mobile Touch Scroll Optimization (Forward touch gestures from sticky header to #main-content) -->
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>">
        (function() {
            let startY = 0;
            let startScrollTop = 0;
            let isHeaderTouch = false;

            document.addEventListener('touchstart', function(e) {
                const header = e.target.closest('header');
                const main = document.getElementById('main-content');
                if (!header || !main) return;
                if (e.touches.length === 1 && !e.target.closest('button, a, input, select, textarea, [role="button"], [x-data]')) {
                    startY = e.touches[0].clientY;
                    startScrollTop = main.scrollTop;
                    isHeaderTouch = true;
                }
            }, { passive: true });

            document.addEventListener('touchmove', function(e) {
                if (isHeaderTouch && e.touches.length === 1) {
                    const main = document.getElementById('main-content');
                    if (main) {
                        const currentY = e.touches[0].clientY;
                        const deltaY = startY - currentY;
                        main.scrollTop = startScrollTop + deltaY;
                    }
                }
            }, { passive: true });

            document.addEventListener('touchend', function() { isHeaderTouch = false; }, { passive: true });
            document.addEventListener('touchcancel', function() { isHeaderTouch = false; }, { passive: true });
        })();
    </script>

    <!-- Seamless SPA Navigation & Mutation Engine (Persistent Sidebar, Header & Zero-Reload Forms) -->
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>">
        window.CSP_NONCE = '<?= \App\Core\SecurityHeaders::nonce() ?>';
        // ==========================================
        // 1. Enterprise Toast Notification Engine (WCAG 2.1 AA & W3C WAI-ARIA)
        // ==========================================
        const MAX_TOASTS = 4;
        let lastToastMsg = '';
        let lastToastType = '';
        let lastToastTime = 0;

        window.notify = {
            show(message, type = 'info', duration = 3800) {
                if (!message) return;

                // Deduplication guard: prevent duplicate alert spam within 1.5s
                const now = Date.now();
                if (message === lastToastMsg && type === lastToastType && (now - lastToastTime < 1500)) {
                    return;
                }
                lastToastMsg = message;
                lastToastType = type;
                lastToastTime = now;

                const container = document.getElementById('toast-container');
                if (!container) return;

                // Evict oldest if limit reached
                while (container.children.length >= MAX_TOASTS) {
                    const oldest = container.firstChild;
                    if (oldest) oldest.remove();
                }

                const toast = document.createElement('div');
                toast.className = 'mpt-toast pointer-events-auto relative flex items-start gap-3 p-4 shadow-2xl transition-all duration-300 transform translate-y-[-10px] opacity-0';
                
                let typeClass = '';
                let iconSvg = '';
                let title = '';
                let progressBg = '';

                if (type === 'success') {
                    typeClass = 'mpt-toast-success';
                    iconSvg = '<div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-xs"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg></div>';
                    title = 'ดำเนินการสำเร็จ';
                    progressBg = 'bg-emerald-500';
                    toast.setAttribute('role', 'status');
                    toast.setAttribute('aria-live', 'polite');
                } else if (type === 'error' || type === 'danger') {
                    typeClass = 'mpt-toast-error';
                    iconSvg = '<div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 shadow-xs"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg></div>';
                    title = 'เกิดข้อผิดพลาด';
                    progressBg = 'bg-rose-500';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                } else if (type === 'warning') {
                    typeClass = 'mpt-toast-warning';
                    iconSvg = '<div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-xs"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>';
                    title = 'แจ้งเตือนความเสี่ยง';
                    progressBg = 'bg-amber-500';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                } else {
                    typeClass = 'mpt-toast-info';
                    iconSvg = '<div class="w-8 h-8 rounded-xl bg-sky-100 dark:bg-sky-950/80 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0 shadow-xs"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>';
                    title = 'ข้อมูลระบบ';
                    progressBg = 'bg-sky-500';
                    toast.setAttribute('role', 'status');
                    toast.setAttribute('aria-live', 'polite');
                }

                toast.classList.add(typeClass);

                // DOM-safe sanitization to prevent XSS (OWASP A03)
                const msgHolder = document.createElement('div');
                msgHolder.textContent = message;

                toast.innerHTML = `
                    ${iconSvg}
                    <div class="flex-1 min-w-0 pr-1.5">
                        <h4 class="mpt-toast-title tracking-tight">${title}</h4>
                        <p class="mpt-toast-desc leading-relaxed break-words">${msgHolder.innerHTML}</p>
                    </div>
                    <button type="button" class="mpt-toast-close shrink-0" aria-label="ปิดการแจ้งเตือน">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <!-- Animated Progress Countdown Bar -->
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-slate-100 dark:bg-white/5 overflow-hidden">
                        <div class="toast-progress h-full ${progressBg} transition-all" style="width: 100%;"></div>
                    </div>
                `;

                const closeBtn = toast.querySelector('button');
                const progressBar = toast.querySelector('.toast-progress');
                let remainingTime = duration;
                let startTime = Date.now();
                let timer = null;
                let isPaused = false;

                const removeToast = () => {
                    if (timer) clearTimeout(timer);
                    toast.classList.remove('translate-y-0', 'opacity-100');
                    toast.classList.add('translate-y-[-10px]', 'opacity-0');
                    setTimeout(() => { if (toast.parentNode) toast.remove(); }, 250);
                };

                if (closeBtn) closeBtn.addEventListener('click', removeToast);

                // Pause auto-dismiss when hovered (WCAG 2.2 Pause, Stop, Hide)
                toast.addEventListener('mouseenter', () => {
                    isPaused = true;
                    clearTimeout(timer);
                    remainingTime -= (Date.now() - startTime);
                    if (progressBar) progressBar.style.transition = 'none';
                });

                toast.addEventListener('mouseleave', () => {
                    if (!isPaused) return;
                    isPaused = false;
                    startTime = Date.now();
                    const safeRemaining = Math.max(remainingTime, 1000);
                    if (progressBar) {
                        progressBar.style.transition = `width ${safeRemaining}ms linear`;
                        progressBar.style.width = '0%';
                    }
                    timer = setTimeout(removeToast, safeRemaining);
                });

                container.appendChild(toast);
                requestAnimationFrame(() => {
                    toast.classList.remove('translate-y-[-10px]', 'opacity-0');
                    toast.classList.add('translate-y-0', 'opacity-100');
                    if (progressBar) {
                        progressBar.style.transition = `width ${duration}ms linear`;
                        requestAnimationFrame(() => {
                            progressBar.style.width = '0%';
                        });
                    }
                });

                timer = setTimeout(removeToast, duration);
            },
            success(msg, duration = 3800) { this.show(msg, 'success', duration); },
            error(msg, duration = 4800) { this.show(msg, 'error', duration); },
            warning(msg, duration = 4800) { this.show(msg, 'warning', duration); },
            info(msg, duration = 3800) { this.show(msg, 'info', duration); }
        };

        // Backward compatibility for existing code calling window.showToast
        window.showToast = function(message, type = 'success', duration = 3800) {
            window.notify.show(message, type, duration);
        };

        // Safe Non-blocking window.alert override (prevents browser freezing)
        window.alert = function(message) {
            window.notify.warning(String(message));
        };

        // ==========================================
        // 2. Global Accessible Confirmation Modal Engine
        // ==========================================
        window.confirmModal = function(options = {}) {
            return new Promise((resolve) => {
                const modal = document.getElementById('mpt-confirm-modal');
                const box = document.getElementById('mpt-confirm-box');
                const titleEl = document.getElementById('mpt-confirm-title');
                const msgEl = document.getElementById('mpt-confirm-message');
                const iconWrap = document.getElementById('mpt-confirm-icon-wrap');
                const acceptBtn = document.getElementById('mpt-confirm-accept-btn');
                const cancelBtn = document.getElementById('mpt-confirm-cancel-btn');

                if (!modal || !box) {
                    // Fallback if modal DOM element is not found
                    resolve(window.confirm(options.message || 'ยืนยันการดำเนินการ?'));
                    return;
                }

                const type = options.type || 'danger';
                const title = options.title || (type === 'danger' ? 'ยืนยันการลบข้อมูล' : 'ยืนยันการดำเนินการ');
                const message = options.message || 'คุณแน่ใจหรือไม่ว่าต้องการดำเนินการนี้? ข้อมูลอาจไม่สามารถกู้คืนได้';
                const confirmText = options.confirmText || (type === 'danger' ? 'ยืนยันการลบ' : 'ยืนยัน');
                const cancelText = options.cancelText || 'ยกเลิก';

                titleEl.textContent = title;
                msgEl.textContent = message;
                acceptBtn.textContent = confirmText;
                cancelBtn.textContent = cancelText;

                // Theme-aware Icon & Button variants
                if (type === 'danger') {
                    iconWrap.className = 'w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-xs bg-rose-500/15 text-rose-600 dark:text-rose-400';
                    iconWrap.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>';
                    acceptBtn.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-600/30 transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-rose-500';
                } else if (type === 'warning') {
                    iconWrap.className = 'w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-xs bg-amber-500/15 text-amber-600 dark:text-amber-400';
                    iconWrap.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';
                    acceptBtn.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-lg shadow-amber-600/30 transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-amber-500';
                } else {
                    iconWrap.className = 'w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-xs bg-emerald-500/15 text-emerald-600 dark:text-emerald-400';
                    iconWrap.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                    acceptBtn.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-600/30 transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500';
                }

                // Smooth Entrance
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                requestAnimationFrame(() => {
                    modal.classList.remove('opacity-0');
                    modal.classList.add('opacity-100');
                    box.classList.remove('scale-95');
                    box.classList.add('scale-100');
                    cancelBtn.focus();
                });

                const closeModal = (confirmed) => {
                    modal.classList.remove('opacity-100');
                    modal.classList.add('opacity-0');
                    box.classList.remove('scale-100');
                    box.classList.add('scale-95');
                    document.removeEventListener('keydown', onKeyDown);
                    modal.removeEventListener('click', onBackdropClick);
                    acceptBtn.removeEventListener('click', onAccept);
                    cancelBtn.removeEventListener('click', onCancel);
                    setTimeout(() => {
                        modal.classList.remove('flex');
                        modal.classList.add('hidden');
                        resolve(confirmed);
                    }, 200);
                };

                const onAccept = () => closeModal(true);
                const onCancel = () => closeModal(false);
                const onBackdropClick = (e) => {
                    if (e.target === modal) closeModal(false);
                };
                const onKeyDown = (e) => {
                    if (e.key === 'Escape') closeModal(false);
                };

                acceptBtn.addEventListener('click', onAccept);
                cancelBtn.addEventListener('click', onCancel);
                modal.addEventListener('click', onBackdropClick);
                document.addEventListener('keydown', onKeyDown);
            });
        };

        // Declarative Event Delegation for [data-confirm] Forms
        document.addEventListener('submit', async function(e) {
            const form = e.target;
            if (!form || !form.hasAttribute('data-confirm')) return;
            if (form._mptConfirmBypassed) {
                delete form._mptConfirmBypassed;
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            const message = form.getAttribute('data-confirm');
            const title = form.getAttribute('data-confirm-title') || 'ยืนยันการดำเนินการ';
            const type = form.getAttribute('data-confirm-type') || 'danger';
            const confirmText = form.getAttribute('data-confirm-btn') || (type === 'danger' ? 'ยืนยันการลบ' : 'ยืนยัน');

            const confirmed = await window.confirmModal({
                title: title,
                message: message,
                type: type,
                confirmText: confirmText
            });

            if (confirmed) {
                form._mptConfirmBypassed = true;
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit(e.submitter);
                } else {
                    form.submit();
                }
            }
        }, true);

        // Helper to check and display flash messages from carrier
        function checkAndDisplayFlash(doc = document) {
            const carrier = doc.getElementById('flash-message-carrier');
            if (!carrier) return;
            const success = carrier.getAttribute('data-success');
            const error = carrier.getAttribute('data-error');
            const warning = carrier.getAttribute('data-warning');
            const info = carrier.getAttribute('data-info');
            if (success) window.notify.success(success);
            if (error) window.notify.error(error);
            if (warning) window.notify.warning(warning);
            if (info) window.notify.info(info);

            // Clear carrier attributes so message isn't shown twice
            carrier.removeAttribute('data-success');
            carrier.removeAttribute('data-error');
            carrier.removeAttribute('data-warning');
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

                    // 7. Re-evaluate script tags inside newMain
                    const scripts = Array.from(currentMain.querySelectorAll('script'));
                    for (const oldScript of scripts) {
                        try {
                            if (oldScript.src) {
                                const newScript = document.createElement('script');
                                for (const attr of oldScript.attributes) {
                                    newScript.setAttribute(attr.name, attr.value);
                                }
                                newScript.nonce = window.CSP_NONCE || '';
                                oldScript.parentNode.replaceChild(newScript, oldScript);
                            } else if (oldScript.text) {
                                const scriptCode = oldScript.text;
                                const newScript = document.createElement('script');
                                for (const attr of oldScript.attributes) {
                                    newScript.setAttribute(attr.name, attr.value);
                                }
                                newScript.nonce = window.CSP_NONCE || '';
                                newScript.text = scriptCode;
                                oldScript.parentNode.replaceChild(newScript, oldScript);
                            }
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

                    // 7. Re-evaluate script tags inside newMain
                    const scripts = Array.from(currentMain.querySelectorAll('script'));
                    for (const oldScript of scripts) {
                        try {
                            if (oldScript.src) {
                                const newScript = document.createElement('script');
                                for (const attr of oldScript.attributes) {
                                    newScript.setAttribute(attr.name, attr.value);
                                }
                                newScript.nonce = window.CSP_NONCE || '';
                                oldScript.parentNode.replaceChild(newScript, oldScript);
                            } else if (oldScript.text) {
                                const scriptCode = oldScript.text;
                                const newScript = document.createElement('script');
                                for (const attr of oldScript.attributes) {
                                    newScript.setAttribute(attr.name, attr.value);
                                }
                                newScript.nonce = window.CSP_NONCE || '';
                                newScript.text = scriptCode;
                                oldScript.parentNode.replaceChild(newScript, oldScript);
                            }
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
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>">
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

        function setAppTheme(mode, persist = true) {
            if (mode !== 'light' && mode !== 'dark' && mode !== 'system') {
                mode = 'system';
            }

            if (persist) {
                try {
                    localStorage.setItem('theme', mode);
                } catch (e) {}
            }

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
                    setAppTheme('system', false);
                }
            };

            if (mediaQuery.addEventListener) {
                mediaQuery.addEventListener('change', handleSystemThemeChange);
            } else if (mediaQuery.addListener) {
                mediaQuery.addListener(handleSystemThemeChange);
            }
        } catch (e) {}

        // Cross-tab Synchronization (Updates instantly when theme changed in another tab)
        window.addEventListener('storage', function(e) {
            if (e.key === 'theme') {
                setAppTheme(e.newValue || 'system', false);
            }
        });
    </script>
</body>
</html>
