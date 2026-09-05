<?php
$endpoints = [
    'http://localhost/Municipal_Project_Tracker/public/dashboard',
    'http://localhost/Municipal_Project_Tracker/public/projects',
    'http://localhost/Municipal_Project_Tracker/public/budgets',
    'http://localhost/Municipal_Project_Tracker/public/reports',
];

$allPassed = true;
foreach ($endpoints as $url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true,
        ]
    ]);
    $html = @file_get_contents($url, false, $context);
    if (!$html) {
        echo "[FAIL] Could not fetch $url\n";
        $allPassed = false;
        continue;
    }
    
    $hasSidebar = strpos($html, 'id="sidebar-nav-items"') !== false;
    $hasMain = strpos($html, 'id="main-content"') !== false;
    $hasSpaProgress = strpos($html, 'id="spa-progress"') !== false;
    $hasSpaScript = strpos($html, 'window.AppSPA') !== false;

    if ($hasSidebar && $hasMain && $hasSpaProgress && $hasSpaScript) {
        echo "[PASS] $url (bytes: " . strlen($html) . ", all SPA IDs present)\n";
    } else {
        echo "[WARN] $url: sidebar=$hasSidebar, main=$hasMain, progress=$hasSpaProgress, spaScript=$hasSpaScript\n";
        $allPassed = false;
    }
}

if ($allPassed) {
    echo "\n==> ALL SPA ENDPOINTS VALIDATED SUCCESSFULLY! <==\n";
}
