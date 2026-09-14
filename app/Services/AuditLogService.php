<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Auth;

class AuditLogService
{
    public static function log(string $action, string $module, ?int $recordId = null, ?array $oldValues = null, ?array $newValues = null, ?int $userId = null): void
    {
        $effectiveUserId = $userId !== null ? $userId : Auth::id();
        $ip = substr($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', 0, 50);
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'CLI/Browser', 0, 255);

        Database::insert('audit_logs', [
            'user_id'    => $effectiveUserId,
            'action'     => $action,
            'module'     => $module,
            'record_id'  => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            'new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }
}
