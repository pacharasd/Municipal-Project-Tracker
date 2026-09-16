<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานสรุปผลการดำเนินงานและงบประมาณโครงการ (PDF Export) - ระบบติดตามและบริหารโครงการเทศบาล</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&family=Prompt:wght@500;600;700&display=swap" rel="stylesheet">
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>" src="<?= \App\Core\Router::url('/js/tailwindcss.min.js') ?>"></script>
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>" src="<?= \App\Core\Router::url('/js/lucide.min.js') ?>"></script>

    <style>
        body { 
            font-family: 'Sarabun', Tahoma, 'Angsana New', sans-serif; 
            font-size: 12px; 
            color: #1e293b;
            background-color: #f1f5f9;
        }
        h1, h2, h3, .font-heading { 
            font-family: 'Prompt', 'Sarabun', sans-serif; 
        }
        @media print {
            .no-print { display: none !important; }
            body { 
                padding: 0 !important; 
                margin: 0 !important; 
                background: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .page-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
            tfoot {
                display: table-footer-group;
            }
            @page { 
                size: A4 landscape; 
                margin: 10mm 12mm; 
            }
        }
    </style>
</head>
<body class="p-4 sm:p-8">

    <!-- Action Bar (Hidden when printing) -->
    <div class="no-print max-w-7xl mx-auto mb-6 bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="javascript:window.close(); if(!window.closed){ history.back(); }" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>ย้อนกลับ</span>
            </a>
            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>มุมมองเอกสารราชการ A4 แนวนอน (พร้อมบันทึก PDF)</span>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="<?= \App\Core\Router::url('/reports/export-excel?' . http_build_query($_GET)) ?>"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition cursor-pointer">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                <span>ดาวน์โหลด Excel</span>
            </a>
            <button type="button" 
                    onclick="window.print()" 
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md transition cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>บันทึกเป็น PDF / พิมพ์รายงาน</span>
            </button>
        </div>
    </div>

    <!-- Official Report Sheet (A4 Landscape Formatted) -->
    <div class="page-container max-w-7xl mx-auto bg-white p-8 sm:p-10 rounded-2xl shadow-sm border border-slate-200 print:border-none print:shadow-none print:p-0">
        
        <!-- Header Section with Emblem & Title -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-5 mb-6">
            <div class="flex items-center gap-4">
                <img src="<?= \App\Core\Router::url('/images/mobile-logo.webp') ?>" 
                     alt="ตราสัญลักษณ์เทศบาล" 
                     class="w-16 h-16 object-contain"
                     onerror="this.style.display='none';">
                <div>
                    <h1 class="text-xl font-bold font-heading text-slate-900 leading-tight">
                        ระบบติดตามและบริหารโครงการเทศบาล
                    </h1>
                    <h2 class="text-sm font-semibold text-slate-700 mt-0.5">
                        รายงานสรุปผลการดำเนินงานและงบประมาณโครงการ (Municipal Project Tracker)
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-1">
                        คณะอนุกรรมการฝ่ายติดตามและการประเมินผล | ข้อมูล ณ วันที่ <?= date('d/m/') . (date('Y') + 543) ?> เวลา <?= date('H:i') ?> น.
                    </p>
                </div>
            </div>
            
            <div class="text-right text-[11px] text-slate-600 space-y-0.5">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 font-semibold text-slate-800">
                    <span>เอกสารราชการ A4 แนวนอน</span>
                </div>
                <div>แผ่นที่ <span class="font-mono font-bold">1</span> / <span class="font-mono">1</span></div>
            </div>
        </div>

        <!-- KPI Summary Cards in Report -->
        <?php 
            $totalCount = count($projects);
            $totalBudgetVal = (float)$totalBudget;
            $totalDisbursedVal = (float)$totalDisbursed;
            $totalRemainingVal = $totalBudgetVal - $totalDisbursedVal;
            $avgProgress = $totalCount > 0 ? (array_sum(array_column($projects, 'progress')) / $totalCount) : 0;
        ?>
        <div class="grid grid-cols-4 gap-3 mb-6">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-center">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">จำนวนโครงการทั้งหมด</span>
                <div class="text-lg font-bold font-heading text-slate-900 mt-0.5"><?= number_format($totalCount) ?> <span class="text-xs font-normal">โครงการ</span></div>
            </div>
            <div class="p-3 bg-blue-50/60 border border-blue-200/80 rounded-xl text-center">
                <span class="text-[10px] font-semibold text-blue-700 uppercase tracking-wider">งบประมาณรวม</span>
                <div class="text-lg font-bold font-heading text-blue-900 mt-0.5"><?= number_format($totalBudgetVal, 2) ?> <span class="text-xs font-normal">บาท</span></div>
            </div>
            <div class="p-3 bg-emerald-50/60 border border-emerald-200/80 rounded-xl text-center">
                <span class="text-[10px] font-semibold text-emerald-700 uppercase tracking-wider">ยอดเบิกจ่ายสะสม</span>
                <div class="text-lg font-bold font-heading text-emerald-900 mt-0.5"><?= number_format($totalDisbursedVal, 2) ?> <span class="text-xs font-normal">บาท</span></div>
            </div>
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-center">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">งบประมาณคงเหลือ</span>
                <div class="text-lg font-bold font-heading text-slate-800 mt-0.5"><?= number_format($totalRemainingVal, 2) ?> <span class="text-xs font-normal">บาท</span></div>
            </div>
        </div>

        <!-- Data Table -->
        <table class="w-full text-left border-collapse text-[11px] border border-slate-300">
            <thead>
                <tr class="bg-slate-100 text-slate-900 border-b-2 border-slate-300 font-bold">
                    <th class="p-2 border border-slate-300 text-center w-8">#</th>
                    <th class="p-2 border border-slate-300 w-24 text-center">รหัส</th>
                    <th class="p-2 border border-slate-300">ชื่อโครงการ / กิจกรรม</th>
                    <th class="p-2 border border-slate-300 w-28">สำนัก / กอง</th>
                    <th class="p-2 border border-slate-300 w-16 text-center">ปีงบ</th>
                    <th class="p-2 border border-slate-300 text-right w-28">งบประมาณ (บาท)</th>
                    <th class="p-2 border border-slate-300 text-right w-28">เบิกจ่าย (บาท)</th>
                    <th class="p-2 border border-slate-300 text-right w-28">คงเหลือ (บาท)</th>
                    <th class="p-2 border border-slate-300 text-center w-20">ความก้าวหน้า</th>
                    <th class="p-2 border border-slate-300 text-center w-24">สถานะ</th>
                    <th class="p-2 border border-slate-300 text-center w-14">เกรด</th>
                    <th class="p-2 border border-slate-300 w-28">ผู้รับผิดชอบ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($projects)): ?>
                <tr>
                    <td colspan="12" class="p-8 text-center text-slate-400 font-medium">
                        ไม่พบข้อมูลโครงการตามเงื่อนไขที่กำหนด
                    </td>
                </tr>
                <?php else: ?>
                <?php 
                foreach ($projects as $idx => $p): 
                    $isMain = empty($p['parent_id']);
                    $rowBg = $isMain ? 'bg-slate-50/80 font-semibold' : 'bg-white';
                    $remaining = (float)$p['budget'] - (float)$p['disbursed_amount'];
                ?>
                    <tr class="border-b border-slate-200 <?= $rowBg ?>">
                        <td class="p-2 border border-slate-200 text-center"><?= $idx + 1 ?></td>
                        <td class="p-2 border border-slate-200 text-center font-mono text-[10px]"><?= htmlspecialchars($p['project_code'] ?? '-') ?></td>
                        <td class="p-2 border border-slate-200">
                            <?= $isMain ? '<span class="text-emerald-700">📌</span> ' : '&nbsp;&nbsp;&nbsp;&nbsp;↳ ' ?>
                            <span><?= htmlspecialchars($p['name']) ?></span>
                        </td>
                        <td class="p-2 border border-slate-200 text-slate-700"><?= htmlspecialchars($p['department_name'] ?? '-') ?></td>
                        <td class="p-2 border border-slate-200 text-center font-mono"><?= htmlspecialchars($p['fiscal_year'] ?? '-') ?></td>
                        <td class="p-2 border border-slate-200 text-right font-mono"><?= number_format((float)$p['budget'], 2) ?></td>
                        <td class="p-2 border border-slate-200 text-right font-mono text-emerald-700"><?= number_format((float)$p['disbursed_amount'], 2) ?></td>
                        <td class="p-2 border border-slate-200 text-right font-mono"><?= number_format($remaining, 2) ?></td>
                        <td class="p-2 border border-slate-200 text-center font-mono font-bold"><?= number_format((float)$p['progress'], 1) ?>%</td>
                        <td class="p-2 border border-slate-200 text-center whitespace-nowrap">
                            <?= \App\Enums\ProjectStatus::labelFor($p['status']) ?>
                        </td>
                        <td class="p-2 border border-slate-200 text-center font-mono font-bold"><?= htmlspecialchars($p['evaluation_grade'] ?? '-') ?></td>
                        <td class="p-2 border border-slate-200 text-slate-600 truncate"><?= htmlspecialchars($p['responsible_name'] ?? $p['responsible_person'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>

                <!-- Summary Total Row -->
                <tr class="bg-slate-200/90 font-bold border-t-2 border-slate-400 text-slate-900">
                    <td colspan="5" class="p-2.5 border border-slate-300 text-center">
                        ยอดรวมทั้งสิ้น (<?= number_format($totalCount) ?> รายการ)
                    </td>
                    <td class="p-2.5 border border-slate-300 text-right font-mono"><?= number_format($totalBudgetVal, 2) ?></td>
                    <td class="p-2.5 border border-slate-300 text-right font-mono text-emerald-800"><?= number_format($totalDisbursedVal, 2) ?></td>
                    <td class="p-2.5 border border-slate-300 text-right font-mono"><?= number_format($totalRemainingVal, 2) ?></td>
                    <td class="p-2.5 border border-slate-300 text-center font-mono"><?= number_format($avgProgress, 1) ?>%</td>
                    <td colspan="3" class="p-2.5 border border-slate-300 text-center text-slate-500 font-normal">-</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Official Signatures Block -->
        <div class="grid grid-cols-2 gap-16 mt-12 pt-8 text-center text-xs">
            <div class="space-y-8">
                <p class="text-slate-600">ลงชื่อ.........................................................................</p>
                <p class="font-bold text-slate-900">
                    (.........................................................................)<br>
                    <span class="font-normal text-slate-500 text-[11px]">เจ้าหน้าที่ผู้จัดทำรายงาน</span>
                </p>
            </div>
            <div class="space-y-8">
                <p class="text-slate-600">ลงชื่อ.........................................................................</p>
                <p class="font-bold text-slate-900">
                    (.........................................................................)<br>
                    <span class="font-normal text-slate-500 text-[11px]">ผู้อำนวยการกอง / นายกเทศมนตรี</span>
                </p>
            </div>
        </div>

    </div>

    <!-- Auto-Print / Lucide Icons Initializer -->
    <script nonce="<?= \App\Core\SecurityHeaders::nonce() ?>">
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) {
                lucide.createIcons();
            }
            // Automatically prompt the user to save as PDF / print
            setTimeout(function() {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>
