<?php
/**
 * Unified Custom Select Component (components/custom-select.blade.php)
 * Follows Emerald-Obsidian Design System
 *
 * Parameters:
 * - name: string (required) - form field name
 * - id: string (optional) - element ID
 * - value: string|int (optional) - currently selected value
 * - label: string (optional) - field label
 * - required: bool (optional) - whether field is required
 * - disabled: bool (optional) - whether field is disabled
 * - placeholder: string (optional) - default '-- เลือก --'
 * - options: array (required) - array of ['value' => ..., 'label' => ..., 'dot' => ..., 'badge' => ...]
 * - searchable: bool (optional) - whether to show quick search box
 * - searchPlaceholder: string (optional) - placeholder for search box
 * - icon: string (optional) - Lucide icon name (e.g. 'calendar', 'building-2', 'activity')
 * - xModel: string (optional) - Alpine.js model expression
 * - class: string (optional) - extra classes for wrapper
 */
$name = $name ?? 'select';
$id = $id ?? $name;
$value = $value ?? '';
$label = $label ?? null;
$required = !empty($required);
$disabled = !empty($disabled);
$placeholder = $placeholder ?? '-- เลือก --';
$options = $options ?? [];
$searchable = isset($searchable) ? (bool)$searchable : (count($options) > 7);
$searchPlaceholder = $searchPlaceholder ?? 'ค้นหา...';
$icon = $icon ?? null;
$xModel = $xModel ?? '';
$class = $class ?? '';

// Normalize options array
$normalizedOptions = [];
$initialLabel = $placeholder;
$initialDot = '';
$initialBadge = '';

foreach ($options as $opt) {
    if (is_array($opt)) {
        $optVal = (string)($opt['value'] ?? '');
        $optLabel = (string)($opt['label'] ?? '');
        $optDot = (string)($opt['dot'] ?? '');
        $optBadge = (string)($opt['badge'] ?? '');
        $optSubtext = (string)($opt['subtext'] ?? '');

        $normalizedOptions[] = [
            'value' => $optVal,
            'label' => $optLabel,
            'dot' => $optDot,
            'badge' => $optBadge,
            'subtext' => $optSubtext,
        ];

        if ((string)$value !== '' && $optVal === (string)$value) {
            $initialLabel = $optLabel ?: $placeholder;
            $initialDot = $optDot;
            $initialBadge = $optBadge;
        }
    } else {
        $optVal = (string)$opt;
        $normalizedOptions[] = [
            'value' => $optVal,
            'label' => $optVal,
            'dot' => '',
            'badge' => '',
            'subtext' => '',
        ];

        if ((string)$value !== '' && $optVal === (string)$value) {
            $initialLabel = $optVal ?: $placeholder;
        }
    }
}

$configJson = json_encode([
    'name' => $name,
    'id' => $id,
    'value' => (string)$value,
    'placeholder' => $placeholder,
    'options' => $normalizedOptions,
    'searchable' => $searchable,
    'searchPlaceholder' => $searchPlaceholder,
    'required' => $required,
    'disabled' => $disabled,
    'model' => $xModel,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
?>

<div class="space-y-1.5 <?= htmlspecialchars($class) ?>">
    <?php if ($label): ?>
        <label for="<?= htmlspecialchars($id) ?>" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
            <?= htmlspecialchars($label) ?>
            <?php if ($required): ?>
                <span class="text-rose-500">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <div class="relative w-full"
         x-data="customSelect(<?= htmlspecialchars($configJson, ENT_QUOTES, 'UTF-8') ?>)"
         @click.outside="close()"
         @keydown.escape.window="close()"
         <?= !empty($xModel) ? "@select-changed=\"{$xModel} = \$event.detail.value\"" : "" ?>>

        <!-- Hidden input for standard GET/POST form submission -->
        <input type="hidden"
               id="<?= htmlspecialchars($id) ?>"
               name="<?= htmlspecialchars($name) ?>"
               value="<?= htmlspecialchars((string)$value) ?>"
               :name="name"
               :value="value"
               <?php if ($required): ?> required <?php endif; ?>
               <?php if ($disabled): ?> disabled <?php endif; ?>>

        <!-- Trigger Button -->
        <button type="button"
                @click="toggle()"
                @keydown.down.prevent="if (!open) { toggle(); } else { navigateOptions('down'); }"
                @keydown.up.prevent="if (open) { navigateOptions('up'); }"
                @keydown.enter.prevent="if (open) { selectHighlighted(); } else { toggle(); }"
                :aria-expanded="open"
                aria-haspopup="listbox"
                <?php if ($disabled): ?> disabled <?php endif; ?>
                class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.04] text-slate-800 dark:text-white flex items-center justify-between gap-2 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 hover:border-emerald-500/40 dark:hover:border-emerald-500/30 transition-all shadow-2xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed text-left">
            
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <?php if ($icon): ?>
                    <i data-lucide="<?= htmlspecialchars($icon) ?>" class="w-4 h-4 text-slate-400 dark:text-slate-500 shrink-0"></i>
                <?php endif; ?>

                <!-- Status Dot indicator if selected item has one -->
                <span x-show="selectedDot" 
                      :class="selectedDot" 
                      class="w-2 h-2 rounded-full shrink-0 shadow-xs <?= htmlspecialchars($initialDot) ?>" 
                      <?php if (empty($initialDot)): ?> style="display: none;" <?php endif; ?>></span>

                <span x-text="selectedLabel"
                      :class="{ 'text-slate-400 dark:text-slate-500 font-normal': !value, 'font-medium text-slate-800 dark:text-white': value }"
                      class="truncate <?= (string)$value !== '' ? 'font-medium text-slate-800 dark:text-white' : 'text-slate-400 dark:text-slate-500 font-normal' ?>">
                    <?= htmlspecialchars($initialLabel) ?>
                </span>

                <span x-show="selectedBadge" 
                      x-text="selectedBadge"
                      class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-emerald-500/15 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 shrink-0" 
                      <?php if (empty($initialBadge)): ?> style="display: none;" <?php endif; ?>>
                    <?= htmlspecialchars($initialBadge) ?>
                </span>
            </div>

            <!-- Chevron Suffix -->
            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0 ml-1"
                 :class="{ 'rotate-180 text-emerald-600 dark:text-emerald-400': open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <!-- Dropdown Popover Menu -->
        <div x-show="open"
             x-cloak
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
             class="absolute left-0 right-0 z-50 mt-1.5 w-full bg-white dark:bg-[#181c26] rounded-2xl shadow-2xl border border-slate-200/90 dark:border-white/10 p-1.5 text-left max-h-64 overflow-y-auto backdrop-blur-md space-y-1"
             role="listbox"
             style="display: none;">

            <!-- Quick Search Input if searchable -->
            <div x-show="searchable" class="p-1 pb-1.5 border-b border-slate-100 dark:border-white/[0.06] sticky top-0 bg-white/95 dark:bg-[#181c26]/95 backdrop-blur-xs z-10">
                <div class="relative">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text"
                           x-ref="searchInput"
                           x-model="search"
                           @keydown.down.prevent="navigateOptions('down')"
                           @keydown.up.prevent="navigateOptions('up')"
                           @keydown.enter.prevent="selectHighlighted()"
                           :placeholder="searchPlaceholder"
                           class="w-full pl-8 pr-7 py-1.5 text-xs bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <button type="button" 
                            x-show="search" 
                            @click="search = ''; $refs.searchInput.focus()" 
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5 cursor-pointer">
                        <i data-lucide="x" class="w-3 h-3"></i>
                    </button>
                </div>
            </div>

            <!-- Empty Search State -->
            <div x-show="filteredOptions.length === 0" class="py-4 text-center text-xs text-slate-400 dark:text-slate-500">
                ไม่พบข้อมูลที่ค้นหา
            </div>

            <!-- Options List -->
            <template x-for="(opt, idx) in filteredOptions" :key="opt.value + '_' + idx">
                <button type="button"
                        @click.stop="select(opt)"
                        @mouseenter="highlightedIndex = idx"
                        role="option"
                        :aria-selected="String(value) === String(opt.value)"
                        :class="{
                            'bg-emerald-50/90 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 font-semibold ring-1 ring-emerald-500/25': String(value) === String(opt.value),
                            'bg-slate-100/70 dark:bg-white/[0.06] text-slate-900 dark:text-white': highlightedIndex === idx && String(value) !== String(opt.value),
                            'text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-500/10': String(value) !== String(opt.value) && highlightedIndex !== idx
                        }"
                        class="w-full text-left px-3 py-2 text-xs sm:text-sm rounded-xl flex items-center justify-between gap-2 transition-colors cursor-pointer select-none">
                    
                    <div class="flex items-center gap-2.5 min-w-0 flex-1 pointer-events-none">
                        <!-- Colored Status Dot if provided -->
                        <span x-show="opt.dot" :class="opt.dot" class="w-2 h-2 rounded-full shrink-0 shadow-xs"></span>

                        <div class="min-w-0 flex-1">
                            <span x-text="opt.label" class="block truncate"></span>
                            <span x-show="opt.subtext" x-text="opt.subtext" class="block text-[10px] text-slate-400 dark:text-slate-500 truncate"></span>
                        </div>

                        <!-- Optional Badge (e.g. ปัจจุบัน) -->
                        <span x-show="opt.badge" 
                              x-text="opt.badge" 
                              class="text-[9px] font-semibold px-1.5 py-0.5 rounded-full bg-emerald-500/15 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 shrink-0"></span>
                    </div>

                    <!-- Selected Checkmark Icon -->
                    <svg x-show="String(value) === String(opt.value)"
                         class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 ml-1.5 pointer-events-none"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                    </svg>
                </button>
            </template>
        </div>
    </div>
</div>
