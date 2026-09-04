<?php

$routes = [
    '/dashboard',
    '/projects',
    '/projects/1',
    '/sub-projects/1',
    '/budgets',
    '/reports',
    '/reports/print',
    '/users',
    '/audit-logs',
    '/login'
];

$allOk = true;
foreach ($routes as $r) {
    $url = 'http://localhost/Municipal_Project_Tracker/public' . $r;
    $ctx = stream_context_create(['http' => ['ignore_errors' => true]]);
    $res = @file_get_contents($url, false, $ctx);
    $status = $http_response_header[0] ?? 'NO_HEADER';
    echo "Endpoint {$r} => {$status}\n";
    if (strpos($status, '200') === false && strpos($status, '302') === false) {
        $allOk = false;
    }
}

if ($allOk) {
    echo "\nAll HTTP routes are responding successfully (200/302 OK)!\n";
} else {
    echo "\nSome HTTP routes failed.\n";
    exit(1);
}
