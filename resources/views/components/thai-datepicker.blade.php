<?php
/**
 * Thai Buddhist Era Datepicker Component (components/thai-datepicker.blade.php)
 *
 * Parameters:
 * - name: string (required) - form field name
 * - id: string (optional) - element ID
 * - value: string (optional) - date string in YYYY-MM-DD
 * - label: string (optional) - field label text
 * - required: bool (optional) - whether field is required
 * - placeholder: string (optional) - default 'วว/ดด/ปปปป'
 * - align: string (optional) - 'left' or 'right', default 'left'
 * - placement: string (optional) - 'bottom' or 'top', default 'bottom'
 * - xModel: string (optional) - Alpine.js model expression (e.g. 'selectedAct.activity_date')
 * - helpText: string (optional) - small help text under field
 */
$name = $name ?? 'date';
$id = $id ?? $name;
$value = $value ?? '';
$label = $label ?? null;
$required = !empty($required);
$placeholder = $placeholder ?? 'วว/ดด/ปปปป';
$align = $align ?? 'left';
$placement = $placement ?? 'bottom';
$xModel = $xModel ?? '';
$helpText = $helpText ?? null;
$disabled = !empty($disabled);

// Safe JSON encoding for config
$configJson = json_encode([
    'name' => $name,
    'id' => $id,
    'value' => (string)$value,
    'placeholder' => $placeholder,
    'align' => $align,
    'placement' => $placement,
    'model' => $xModel,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
?>

<div class="space-y-1">
    <?php if ($label): ?>
        <label for="<?= htmlspecialchars($id) ?>" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
            <?= htmlspecialchars($label) ?>
            <?php if ($required): ?>
                <span class="text-rose-500">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <div class="relative w-full" 
         x-data="thaiDatePicker(<?= htmlspecialchars($configJson, ENT_QUOTES, 'UTF-8') ?>)" 
         @click.outside="open = false"
         <?= !empty($xModel) ? "@date-selected=\"{$xModel} = \$event.detail.value\"" : "" ?>>
        
        <!-- Hidden input for standard YYYY-MM-DD form submission -->
        <input type="hidden" 
               id="<?= htmlspecialchars($id) ?>" 
               name="<?= htmlspecialchars($name) ?>" 
               :name="name" 
               :value="value"
               <?php if ($required): ?> required <?php endif; ?>
               <?php if ($disabled): ?> disabled <?php endif; ?>>

        <!-- Clickable Input trigger button -->
        <button type="button" 
                @click="toggle()"
                <?php if ($disabled): ?> disabled <?php endif; ?>
                class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#12141a] text-slate-900 dark:text-white flex items-center justify-between focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-text="displayLabel" 
                  :class="{ 'text-slate-400 dark:text-slate-500 font-normal': !value, 'font-medium text-slate-800 dark:text-white': value }"
                  class="truncate">
                <?= htmlspecialchars($placeholder) ?>
            </span>
            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </button>

        <!-- Dropdown Calendar Panel (Buddhist Era พ.ศ.) -->
        <div x-show="open" 
             x-cloak
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             :class="{
                 'bottom-full mb-2': placement === 'top',
                 'top-full mt-2': placement !== 'top',
                 'right-0': align === 'right',
                 'left-0': align !== 'right'
             }"
             class="absolute z-50 w-72 max-w-[calc(100vw-2rem)] bg-white dark:bg-[#1f222e] rounded-2xl shadow-2xl border border-slate-200 dark:border-white/10 p-3.5 select-none"
             style="display: none;">
            
            <!-- Calendar Navigation Header -->
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-white/10">
                <div class="flex items-center gap-0.5">
                    <button type="button" @click="prevYear()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition cursor-pointer" title="ปีก่อนหน้า (พ.ศ.)">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                    </button>
                    <button type="button" @click="prevMonth()" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition cursor-pointer" title="เดือนก่อนหน้า">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                </div>
                
                <!-- Month & Thai Year Display (พ.ศ.) -->
                <div class="text-xs font-bold text-slate-800 dark:text-white font-heading" x-text="monthLabel"></div>
                
                <div class="flex items-center gap-0.5">
                    <button type="button" @click="nextMonth()" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition cursor-pointer" title="เดือนถัดไป">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                    <button type="button" @click="nextYear()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition cursor-pointer" title="ปีถัดไป (พ.ศ.)">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Days of Week (Thai short names) -->
            <div class="grid grid-cols-7 gap-1 mb-1 text-center">
                <template x-for="(day, dIdx) in shortDays" :key="dIdx">
                    <div class="text-[11px] font-semibold py-1" 
                         :class="dIdx === 0 ? 'text-rose-500/80 dark:text-rose-400/80' : 'text-slate-400 dark:text-slate-500'" 
                         x-text="day"></div>
                </template>
            </div>

            <!-- Calendar Days Grid -->
            <div class="grid grid-cols-7 gap-1 text-center">
                <template x-for="(item, index) in days" :key="item.dateStr || (item.day + '-' + index)">
                    <div>
                        <button type="button" 
                                x-show="item.isCurrent"
                                @click.stop="selectDate(item)"
                                class="w-8 h-8 mx-auto text-xs flex items-center justify-center rounded-xl transition-all cursor-pointer"
                                :class="{
                                    'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30': value === item.date,
                                    'border border-emerald-500 text-emerald-600 dark:text-emerald-400 font-semibold hover:bg-emerald-50 dark:hover:bg-emerald-500/10': item.isToday && value !== item.date,
                                    'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/10': value !== item.date && !item.isToday
                                }"
                                x-text="item.day">
                        </button>
                        <div x-show="!item.isCurrent" class="w-8 h-8 mx-auto text-xs flex items-center justify-center text-slate-300 dark:text-slate-600 pointer-events-none" x-text="item.day"></div>
                    </div>
                </template>
            </div>

            <!-- Footer: Clear & Today Shortcuts -->
            <div class="mt-3 pt-2 border-t border-slate-100 dark:border-white/10 flex items-center justify-between text-xs">
                <button type="button" @click="clear()" class="text-slate-400 hover:text-rose-500 transition cursor-pointer font-medium px-1 py-0.5 rounded">ล้างค่า</button>
                <button type="button" @click="selectToday()" class="text-emerald-600 dark:text-emerald-400 hover:underline transition cursor-pointer font-semibold px-1 py-0.5 rounded">วันนี้</button>
            </div>
        </div>
    </div>

    <?php if ($helpText): ?>
        <p class="text-[11px] text-slate-400 dark:text-slate-500"><?= htmlspecialchars($helpText) ?></p>
    <?php endif; ?>
</div>
