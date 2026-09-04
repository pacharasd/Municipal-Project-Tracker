<?php
ob_start();
$title = 'ประวัติการใช้งานและการตรวจสอบ (Audit Trail) - Municipal Project Tracker';
?>

<div class="space-y-6" x-data="{
    selectedLog: null,
    searchQuery: '',
    formatJson(jsonStr) {
        if (!jsonStr) return '-';
        try {
            return JSON.stringify(JSON.parse(jsonStr), null, 2);
        } catch (e) {
            return jsonStr;
        }
    }
}">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl">
                    <i data-lucide="shield-alert" class="w-6 h-6"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">บันทึกประวัติการตรวจสอบระบบ (Audit Trail)</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">บันทึกการกระทำทุกขั้นตอนแบบถาวร (Immutable Log) พร้อมข้อมูลก่อนและหลังแก้ไขเพื่อความโปร่งใส</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl text-xs font-semibold border border-emerald-200 dark:border-emerald-800">
                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                <span>เฉพาะผู้ดูแลระบบ (Administrator)</span>
            </span>
        </div>
    </div>

    <!-- Search / Filter Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-80">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            <input type="text" x-model="searchQuery" placeholder="ค้นหาการกระทำ, ผู้ใช้, โมดูล..."
                   class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none dark:text-white">
        </div>
        <div class="text-xs text-slate-500 dark:text-slate-400">
            แสดง 100 รายการล่าสุดจากฐานข้อมูล
        </div>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/70 text-slate-600 dark:text-slate-400 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-3 w-12 text-center">ID</th>
                        <th class="py-3 px-3 w-36">วันเวลา</th>
                        <th class="py-3 px-3">ผู้ดำเนินการ / บทบาท</th>
                        <th class="py-3 px-3 text-center">โมดูล</th>
                        <th class="py-3 px-3 text-center">การกระทำ (Action)</th>
                        <th class="py-3 px-3 text-center">Record ID</th>
                        <th class="py-3 px-3">IP Address</th>
                        <th class="py-3 px-3 text-center">ข้อมูลการเปลี่ยนแปลง (Diff)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                ยังไม่มีบันทึก Audit Log ในระบบ
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): 
                            $hasChanges = !empty($log['old_values']) || !empty($log['new_values']);
                            
                            // Badge color by action
                            $actionColor = match($log['action']) {
                                'CREATE'             => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                                'UPDATE'             => 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-800',
                                'INCREMENT_PROGRESS' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800',
                                'DELETE'             => 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-900',
                                'REPORT_PROBLEM'     => 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-900',
                                'RESOLVE_PROBLEM'    => 'bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 border-teal-200 dark:border-teal-800',
                                'DISBURSE'           => 'bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-800',
                                default              => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700'
                            };
                        ?>
                            <tr class="hover:bg-slate-50/75 dark:hover:bg-slate-800/40 transition-colors"
                                x-show="!searchQuery || '<?= strtolower($log['action'] . ' ' . $log['module'] . ' ' . ($log['user_name'] ?? '') . ' ' . $log['ip_address']) ?>'.includes(searchQuery.toLowerCase())">
                                <td class="py-3 px-3 text-center text-slate-400 font-mono"><?= $log['id'] ?></td>
                                <td class="py-3 px-3 whitespace-nowrap text-slate-600 dark:text-slate-400 font-mono">
                                    <div><?= date('d/m/Y', strtotime($log['created_at'])) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= date('H:i:s น.', strtotime($log['created_at'])) ?></div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-medium text-slate-900 dark:text-white">
                                        <?= htmlspecialchars($log['user_name'] ?? 'ระบบอัตโนมัติ') ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        <?= htmlspecialchars($log['role_label'] ?? 'System') ?>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        <?= htmlspecialchars($log['module']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border <?= $actionColor ?>">
                                        <?= htmlspecialchars($log['action']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center font-mono text-slate-600 dark:text-slate-400">
                                    #<?= htmlspecialchars($log['record_id'] ?? '-') ?>
                                </td>
                                <td class="py-3 px-3 font-mono text-[11px] text-slate-500 dark:text-slate-400">
                                    <?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <?php if ($hasChanges): ?>
                                        <button @click='selectedLog = <?= json_encode($log, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'
                                                class="inline-flex items-center gap-1 px-2 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/60 rounded-lg text-[11px] font-medium transition-colors">
                                            <i data-lucide="eye" class="w-3 h-3"></i>
                                            <span>ดูการเปลี่ยนแปลง</span>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-slate-300 dark:text-slate-600">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: View JSON Diff Detail -->
    <template x-teleport="body">
    <div x-show="selectedLog" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div @click.away="selectedLog = null" 
             class="bg-white dark:bg-slate-900 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="p-2 bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl">
                        <i data-lucide="code" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>รายละเอียดการเปลี่ยนแปลงข้อมูล</span>
                            <span class="text-xs px-2 py-0.5 rounded font-mono bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300" x-text="`ID #${selectedLog?.id}`"></span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-text="`${selectedLog?.module} > ${selectedLog?.action} โดย ${selectedLog?.user_name || 'System'}`"></p>
                    </div>
                </div>
                <button @click="selectedLog = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Old Values -->
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 flex items-center gap-1">
                        <i data-lucide="minus-circle" class="w-3.5 h-3.5"></i>
                        <span>ข้อมูลเดิมก่อนแก้ไข (Old Values)</span>
                    </span>
                    <pre class="p-3 bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/60 rounded-xl text-[11px] font-mono text-slate-800 dark:text-slate-200 overflow-x-auto max-h-60"
                         x-text="formatJson(selectedLog?.old_values)"></pre>
                </div>

                <!-- New Values -->
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                        <span>ข้อมูลใหม่หลังแก้ไข (New Values)</span>
                    </span>
                    <pre class="p-3 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/60 rounded-xl text-[11px] font-mono text-slate-800 dark:text-slate-200 overflow-x-auto max-h-60"
                         x-text="formatJson(selectedLog?.new_values)"></pre>
                </div>
            </div>

            <!-- Client metadata -->
            <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400 space-y-1">
                <div><span class="font-semibold text-slate-700 dark:text-slate-300">IP Address:</span> <span class="font-mono" x-text="selectedLog?.ip_address || '-'"></span></div>
                <div class="truncate"><span class="font-semibold text-slate-700 dark:text-slate-300">User Agent:</span> <span class="font-mono text-[10px]" x-text="selectedLog?.user_agent || '-'"></span></div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" @click="selectedLog = null"
                        class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium rounded-xl text-xs transition-colors">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>
    </template>

</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>

