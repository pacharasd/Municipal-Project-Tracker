<?php
/**
 * Modern Accessible Inline Alert / Banner Component
 * Follows Tailwind Design System, WCAG 2.1 AA and W3C WAI-ARIA standards.
 * 
 * Usage:
 * <?php component('alert', [
 *     'type' => 'success', // 'success' | 'error' | 'warning' | 'info'
 *     'title' => 'หัวข้อ',
 *     'message' => 'ข้อความรายละเอียด...',
 *     'dismissible' => true,
 *     'icon' => null // Optional custom Lucide icon name
 * ]); ?>
 */

$type = $type ?? 'info';
if ($type === 'danger') $type = 'error';

$title = $title ?? null;
$message = $message ?? ($content ?? '');
$dismissible = $dismissible ?? false;

// Color maps for Tailwind Design System
$styles = [
    'success' => [
        'container' => 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300/80 dark:border-emerald-800/60 text-emerald-900 dark:text-emerald-200',
        'iconBg'    => 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
        'defaultIcon' => 'check-circle-2',
        'closeBtn'  => 'text-emerald-700/60 hover:text-emerald-900 dark:text-emerald-400/60 dark:hover:text-emerald-200',
    ],
    'error' => [
        'container' => 'bg-rose-50 dark:bg-rose-950/40 border-rose-300/80 dark:border-rose-800/60 text-rose-900 dark:text-rose-200',
        'iconBg'    => 'bg-rose-500/15 text-rose-600 dark:text-rose-400',
        'defaultIcon' => 'alert-circle',
        'closeBtn'  => 'text-rose-700/60 hover:text-rose-900 dark:text-rose-400/60 dark:hover:text-rose-200',
    ],
    'warning' => [
        'container' => 'bg-amber-50 dark:bg-amber-950/40 border-amber-300/80 dark:border-amber-800/60 text-amber-900 dark:text-amber-200',
        'iconBg'    => 'bg-amber-500/15 text-amber-600 dark:text-amber-400',
        'defaultIcon' => 'alert-triangle',
        'closeBtn'  => 'text-amber-700/60 hover:text-amber-900 dark:text-amber-400/60 dark:hover:text-amber-200',
    ],
    'info' => [
        'container' => 'bg-sky-50 dark:bg-sky-950/40 border-sky-300/80 dark:border-sky-800/60 text-sky-900 dark:text-sky-200',
        'iconBg'    => 'bg-sky-500/15 text-sky-600 dark:text-sky-400',
        'defaultIcon' => 'info',
        'closeBtn'  => 'text-sky-700/60 hover:text-sky-900 dark:text-sky-400/60 dark:hover:text-sky-200',
    ]
];

$config = $styles[$type] ?? $styles['info'];
$iconName = $icon ?? $config['defaultIcon'];
$ariaRole = ($type === 'error' || $type === 'warning') ? 'alert' : 'status';
$ariaLive = ($type === 'error') ? 'assertive' : 'polite';
?>

<div x-data="{ open: true }" 
     x-show="open" 
     x-cloak
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 max-h-40"
     x-transition:leave-end="opacity-0 max-h-0"
     role="<?= $ariaRole ?>" 
     aria-live="<?= $ariaLive ?>" 
     class="flex items-start gap-3 p-4 rounded-2xl border shadow-xs overflow-hidden transition-all duration-200 <?= $config['container'] ?> <?= $class ?? '' ?>">
    
    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5 <?= $config['iconBg'] ?>" aria-hidden="true">
        <i data-lucide="<?= $iconName ?>" class="w-4 h-4"></i>
    </div>

    <div class="flex-1 min-w-0 pr-1">
        <?php if (!empty($title)): ?>
            <h4 class="font-heading font-bold text-sm leading-snug mb-1">
                <?= htmlspecialchars($title) ?>
            </h4>
        <?php endif; ?>
        <div class="text-xs leading-relaxed opacity-95">
            <?= $message ?>
        </div>
    </div>

    <?php if ($dismissible): ?>
        <button type="button" 
                @click="open = false" 
                class="p-1 rounded-lg cursor-pointer transition-colors shrink-0 <?= $config['closeBtn'] ?>"
                aria-label="ปิดการแจ้งเตือน">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    <?php endif; ?>
</div>
