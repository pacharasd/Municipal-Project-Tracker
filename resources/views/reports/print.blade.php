<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานสรุปผลการดำเนินงานและงบประมาณโครงการ - เทศบาล</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&family=Prompt:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Sarabun', sans-serif; font-size: 13px; }
        h1, h2, h3, .font-heading { font-family: 'Prompt', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            @page { size: A4 landscape; margin: 12mm; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8">

    <!-- Action Bar (Hidden when printing) -->
    <div class="no-print max-w-6xl mx-auto mb-6 bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="javascript:history.back()" class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                ← กลับ
            </a>
            <span class="text-xs text-slate-500">ดูตัวอย่างก่อนพิมพ์รายงานราชการ</span>
        </div>
        <button onclick="window.print()" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            พิมพ์รายงาน / บันทึก PDF (Print)
        </button>
    </div>

    <!-- Official Report Sheet -->
    <div class="max-w-6xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-slate-200 print:border-none print:shadow-none print:p-0">
        <!-- Municipal Emblem & Header -->
        <div class="text-center space-y-1 pb-6 border-b border-slate-300">
            <h1 class="text-lg font-bold text-slate-900">เทศบาลเมืองขอนแก่น</h1>
            <h2 class="text-base font-semibold text-slate-800">รายงานติดตามและประเมินผลการดำเนินงานโครงการและงบประมาณ</h2>
            <p class="text-xs text-slate-600">
                ข้อมูล ณ วันที่ <?= date('d/m/') . (date('Y') + 543) ?> เวลา <?= date('H:i') ?> น. | ระบบติดตามและบริหารโครงการเทศบาล
            </p>
        </div>

        <!-- Summary Cards in Report -->
        <div class="grid grid-cols-4 gap-4 my-6 text-center">
            <div class="p-3 border border-slate-200 rounded-lg">
                <div class="text-[11px] text-slate-500">โครงการทั้งหมด</div>
                <div class="text-lg font-bold text-slate-900"><?= count($projects) ?> โครงการ</div>
            </div>
            <div class="p-3 border border-slate-200 rounded-lg">
                <div class="text-[11px] text-slate-500">งบประมาณรวม</div>
                <div class="text-lg font-bold text-blue-700"><?= number_format($totalBudget, 2) ?> บาท</div>
            </div>
            <div class="p-3 border border-slate-200 rounded-lg">
                <div class="text-[11px] text-slate-500">เบิกจ่ายแล้ว</div>
                <div class="text-lg font-bold text-emerald-700"><?= number_format($totalDisbursed, 2) ?> บาท</div>
            </div>
            <div class="p-3 border border-slate-200 rounded-lg">
                <div class="text-[11px] text-slate-500">งบประมาณคงเหลือ</div>
                <div class="text-lg font-bold text-slate-700"><?= number_format($totalBudget - $totalDisbursed, 2) ?> บาท</div>
            </div>
        </div>

        <!-- Data Table -->
        <table class="w-full text-left border-collapse text-[11px] border border-slate-300 mt-4">
            <thead>
                <tr class="bg-slate-100 text-slate-800 border-b border-slate-300 font-bold">
                    <th class="p-2 border border-slate-300 text-center w-8">#</th>
                    <th class="p-2 border border-slate-300 w-24">รหัสโครงการ</th>
                    <th class="p-2 border border-slate-300">ชื่อโครงการ</th>
                    <th class="p-2 border border-slate-300 w-24">สังกัด/กอง</th>
                    <th class="p-2 border border-slate-300 text-right w-24">งบประมาณ (บาท)</th>
                    <th class="p-2 border border-slate-300 text-right w-24">เบิกจ่าย (บาท)</th>
                    <th class="p-2 border border-slate-300 text-right w-24">คงเหลือ (บาท)</th>
                    <th class="p-2 border border-slate-300 text-center w-16">ความก้าวหน้า</th>
                    <th class="p-2 border border-slate-300 text-center w-24">สถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ($projects as $idx => $p): 
                    $isMain = empty($p['parent_id']);
                    $rowBg = $isMain ? 'bg-slate-50/70 font-semibold' : '';
                ?>
                    <tr class="border-b border-slate-200 <?= $rowBg ?>">
                        <td class="p-2 border border-slate-200 text-center"><?= $idx + 1 ?></td>
                        <td class="p-2 border border-slate-200 font-mono"><?= htmlspecialchars($p['project_code']) ?></td>
                        <td class="p-2 border border-slate-200">
                            <?= $isMain ? '📌 ' : '&nbsp;&nbsp;&nbsp;↳ ' ?>
                            <?= htmlspecialchars($p['name']) ?>
                        </td>
                        <td class="p-2 border border-slate-200"><?= htmlspecialchars($p['department_name'] ?? '-') ?></td>
                        <td class="p-2 border border-slate-200 text-right font-mono"><?= number_format($p['budget'], 2) ?></td>
                        <td class="p-2 border border-slate-200 text-right font-mono"><?= number_format($p['disbursed_amount'], 2) ?></td>
                        <td class="p-2 border border-slate-200 text-right font-mono"><?= number_format($p['budget'] - $p['disbursed_amount'], 2) ?></td>
                        <td class="p-2 border border-slate-200 text-center font-mono font-bold"><?= number_format($p['progress'], 1) ?>%</td>
                        <td class="p-2 border border-slate-200 text-center">
                            <?= \App\Enums\ProjectStatus::labelFor($p['status']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Signature Blocks -->
        <div class="grid grid-cols-2 gap-12 mt-12 pt-8 text-center text-xs">
            <div class="space-y-8">
                <p>ลงชื่อ.........................................................................</p>
                <p class="font-bold">(.........................................................................)<br>
                <span class="font-normal text-slate-600">เจ้าหน้าที่ผู้จัดทำรายงาน</span></p>
            </div>
            <div class="space-y-8">
                <p>ลงชื่อ.........................................................................</p>
                <p class="font-bold">(.........................................................................)<br>
                <span class="font-normal text-slate-600">ผู้อำนวยการกอง / นายกเทศมนตรี</span></p>
            </div>
        </div>
    </div>

</body>
</html>
