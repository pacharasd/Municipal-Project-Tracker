<?php

namespace App\Core;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            // Auto-load .env if not loaded yet
            if (empty($_ENV['DB_DATABASE'])) {
                $envFile = dirname(__DIR__, 2) . '/.env';
                if (file_exists($envFile)) {
                    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($lines as $line) {
                        if (str_starts_with(trim($line), '#')) continue;
                        if (str_contains($line, '=')) {
                            [$k, $v] = explode('=', $line, 2);
                            $k = trim($k);
                            $v = trim(trim($v), '"\'');
                            $_ENV[$k] = $v;
                            putenv("{$k}={$v}");
                        }
                    }
                }
            }

            $host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? (getenv('DB_HOST') ?: 'localhost');
            $port = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? (getenv('DB_PORT') ?: '3306');
            $db   = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? (getenv('DB_DATABASE') ?: 'project_tracker');
            $user = $_ENV['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? (getenv('DB_USERNAME') ?: 'project_tracker');
            $pass = $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');

            $hostCandidates = array_values(array_unique([$host, 'localhost', '127.0.0.1']));
            $dbCandidates = array_values(array_unique([$db, 'behn_' . $db, str_replace('behn_', '', $db), 'behn_project_tracker', 'project_tracker']));
            $userCandidates = array_values(array_unique([$user, 'behn_' . $user, str_replace('behn_', '', $user), 'behn_project_tracker', 'project_tracker']));
            $passCandidates = array_values(array_unique([$pass, trim($pass, "\"'")]));

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
            ];

            $lastException = null;
            $connected = false;

            // 1. Try host candidates (TCP & Localhost)
            foreach ($hostCandidates as $h) {
                foreach ($dbCandidates as $d) {
                    foreach ($userCandidates as $u) {
                        foreach ($passCandidates as $p) {
                            try {
                                $dsn = "mysql:host={$h};port={$port};dbname={$d};charset=utf8mb4";
                                $pdo = new PDO($dsn, $u, $p, $options);
                                self::$instance = $pdo;
                                $connected = true;
                                $_ENV['DB_DATABASE'] = $d;
                                $_ENV['DB_USERNAME'] = $u;
                                $_ENV['DB_HOST'] = $h;
                                break 4;
                            } catch (PDOException $ex) {
                                $lastException = $ex;
                            }
                        }
                    }
                }
            }

            // 2. Try Unix sockets on Linux Plesk environment
            if (!$connected && PHP_OS_FAMILY !== 'Windows') {
                $sockets = ['/var/run/mysqld/mysqld.sock', '/var/lib/mysql/mysql.sock', '/tmp/mysql.sock'];
                foreach ($sockets as $sock) {
                    if (file_exists($sock)) {
                        foreach ($dbCandidates as $d) {
                            foreach ($userCandidates as $u) {
                                foreach ($passCandidates as $p) {
                                    try {
                                        $dsn = "mysql:unix_socket={$sock};dbname={$d};charset=utf8mb4";
                                        $pdo = new PDO($dsn, $u, $p, $options);
                                        self::$instance = $pdo;
                                        $connected = true;
                                        $_ENV['DB_DATABASE'] = $d;
                                        $_ENV['DB_USERNAME'] = $u;
                                        break 4;
                                    } catch (PDOException $ex) {
                                        $lastException = $ex;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!$connected) {
                error_log("Database connection failed: " . ($lastException ? $lastException->getMessage() : 'Unknown error'));
                self::renderConnectionError($lastException ?? new PDOException("ไม่สามารถเชื่อมต่อฐานข้อมูลได้"), $host, $port, $db, $user);
                exit;
            }

            // Check and auto-import schema if tables do not exist yet
            self::ensureSchemaImported(self::$instance);

            // Handle manual action trigger from setup screen
            if (isset($_GET['action']) && in_array($_GET['action'], ['install_db', 'repair_db'])) {
                self::importSchema(self::$instance);
                $cleanUrl = strtok($_SERVER["REQUEST_URI"] ?? '/dashboard', '?');
                header("Location: " . ($cleanUrl ?: '/dashboard'));
                exit;
            }
        }

        return self::$instance;
    }

    public static function importSchema(PDO $pdo): bool
    {
        $sqlFile = dirname(__DIR__, 2) . '/behn_project_tracker.sql';
        if (!file_exists($sqlFile)) {
            error_log("Database SQL file not found at: {$sqlFile}");
            return false;
        }

        $sql = file_get_contents($sqlFile);
        if (!$sql) {
            return false;
        }

        // Auto-detect UTF-16LE and convert
        if (str_starts_with($sql, "\xFF\xFE")) {
            $sql = mb_convert_encoding($sql, 'UTF-8', 'UTF-16LE');
        }
        // Strip UTF-8 BOM if present
        if (str_starts_with($sql, "\xEF\xBB\xBF")) {
            $sql = substr($sql, 3);
        }

        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $pdo->exec($sql);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            return true;
        } catch (\Throwable $e) {
            error_log("Direct SQL dump execution error: " . $e->getMessage() . ", falling back to query splitter...");
            
            // Fallback: Split and execute statements one by one
            $queries = self::splitSqlQueries($sql);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    try {
                        $pdo->exec($query);
                    } catch (\Throwable $subE) {
                        error_log("Statement execution error: " . substr($query, 0, 80) . "... - " . $subE->getMessage());
                    }
                }
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            
            $check = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
            return !empty($check);
        }
    }

    private static function splitSqlQueries(string $sql): array
    {
        $queries = [];
        $buffer = '';
        $inString = false;
        $stringChar = '';
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $nextChar = $i + 1 < $length ? $sql[$i + 1] : '';

            // Handle comments outside of strings
            if (!$inString) {
                if ($char === '-' && $nextChar === '-') {
                    $endLine = strpos($sql, "\n", $i);
                    $i = $endLine === false ? $length : $endLine;
                    continue;
                }
                if ($char === '/' && $nextChar === '*') {
                    $endBlock = strpos($sql, '*/', $i + 2);
                    $i = $endBlock === false ? $length : $endBlock + 1;
                    continue;
                }
            }

            // Handle string literals
            if (($char === "'" || $char === '"' || $char === '`') && ($i === 0 || $sql[$i - 1] !== '\\')) {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($stringChar === $char) {
                    $inString = false;
                }
            }

            if ($char === ';' && !$inString) {
                $trimmed = trim($buffer);
                if (!empty($trimmed)) {
                    $queries[] = $trimmed;
                }
                $buffer = '';
            } else {
                $buffer .= $char;
            }
        }

        $trimmed = trim($buffer);
        if (!empty($trimmed)) {
            $queries[] = $trimmed;
        }

        return $queries;
    }

    private static function ensureSchemaImported(PDO $pdo): void
    {
        try {
            $hasUsers = false;
            $hasProjects = false;
            try {
                $hasUsers = !empty($pdo->query("SHOW TABLES LIKE 'users'")->fetch());
                $hasProjects = !empty($pdo->query("SHOW TABLES LIKE 'projects'")->fetch());
            } catch (\Throwable $e) {
                // Ignore check error
            }

            if (!$hasUsers || !$hasProjects) {
                self::importSchema($pdo);
            }

            // Check if users exists now
            $hasUsersNow = !empty($pdo->query("SHOW TABLES LIKE 'users'")->fetch());
            if (!$hasUsersNow) {
                self::renderMissingTablesError();
                exit;
            }

            // Auto-migrate newly added columns seamlessly
            $colCheck = $pdo->query("SHOW COLUMNS FROM `projects` LIKE 'responsible_person'")->fetch();
            if (!$colCheck) {
                $pdo->exec("ALTER TABLE `projects` ADD COLUMN `responsible_person` VARCHAR(255) NULL AFTER `responsible_user_id`");
                $pdo->exec("
                    UPDATE `projects` p
                    LEFT JOIN `users` u ON p.responsible_user_id = u.id
                    LEFT JOIN `departments` d ON p.department_id = d.id
                    SET p.responsible_person = COALESCE(u.name, d.name)
                    WHERE p.responsible_person IS NULL OR p.responsible_person = ''
                ");
            }

            // Ensure category_id column exists in projects
            $catColCheck = $pdo->query("SHOW COLUMNS FROM `projects` LIKE 'category_id'")->fetch();
            if (!$catColCheck) {
                $pdo->exec("ALTER TABLE `projects` ADD COLUMN `category_id` BIGINT UNSIGNED NULL AFTER `department_id`");
            }

            // Ensure project_categories table exists with proper schema
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `project_categories` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(100) NOT NULL UNIQUE,
                    `description` TEXT NULL,
                    `icon` VARCHAR(50) DEFAULT 'folder',
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (\Throwable $e) {
            error_log("Auto schema migration notice: " . $e->getMessage());
        }
    }

    public static function renderMissingTablesError(?\Throwable $e = null): void
    {
        $isJson = (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');

        $db = $_ENV['DB_DATABASE'] ?? 'behn_project_tracker';
        $user = $_ENV['DB_USERNAME'] ?? 'behn_project_tracker';
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';

        if ($isJson) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => 'Database tables not found',
                'message' => $e ? $e->getMessage() : 'Tables users and projects do not exist yet',
                'install_url' => '?action=install_db',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            return;
        }

        http_response_code(503);
        header('Content-Type: text/html; charset=utf-8');
        ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เริ่มต้นติดตั้งฐานข้อมูล - ระบบติดตามและบริหารโครงการเทศบาล</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Sarabun', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6">
    <div class="max-w-3xl w-full bg-slate-900/95 border border-slate-700/80 rounded-2xl shadow-2xl overflow-hidden backdrop-blur-xl">
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-700 via-teal-800 to-slate-900 p-6 sm:p-8 flex items-center gap-4 sm:gap-6 border-b border-slate-700/50">
            <img src="/images/mobile-logo.webp" onerror="this.src='/mobile-logo.webp'; this.onerror=null;" alt="Logo" class="w-16 h-16 sm:w-20 sm:h-20 object-contain drop-shadow-md bg-white/10 rounded-2xl p-2 border border-white/20">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    เชื่อมต่อฐานข้อมูลสำเร็จ &bull; รอการสร้างตารางข้อมูล
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">ระบบติดตามและบริหารโครงการ</h1>
                <p class="text-sm sm:text-base text-emerald-200/90 font-medium">เทศบาลตำบลบ้านแฮด จังหวัดขอนแก่น</p>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
            <!-- Notice Banner -->
            <div class="p-5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-200 space-y-2">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">⚡</span>
                    <div class="font-bold text-amber-300 text-base">ฐานข้อมูล MySQL เชื่อมต่อได้เรียบร้อย แต่ยังไม่พบตารางข้อมูลในระบบ</div>
                </div>
                <p class="text-xs text-amber-200/80 leading-relaxed pl-9">
                    ระบบตรวจพบว่าเชื่อมต่อกับฐานข้อมูล <code class="bg-amber-950/80 px-2 py-0.5 rounded text-amber-300 font-mono"><?= htmlspecialchars($db) ?></code> สำเร็จแล้ว แต่ยังไม่มีการนำเข้าตารางข้อมูลเริ่มต้น (เช่น users, projects, budgets)
                </p>
            </div>

            <!-- Database Info Details -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800">
                    <span class="text-slate-400 block mb-1">DB Host / Port</span>
                    <span class="font-mono font-bold text-white"><?= htmlspecialchars($host . ':' . $port) ?></span>
                </div>
                <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800">
                    <span class="text-slate-400 block mb-1">Database Name</span>
                    <span class="font-mono font-bold text-emerald-400"><?= htmlspecialchars($db) ?></span>
                </div>
                <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800">
                    <span class="text-slate-400 block mb-1">Database User</span>
                    <span class="font-mono font-bold text-amber-300"><?= htmlspecialchars($user) ?></span>
                </div>
                <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800">
                    <span class="text-slate-400 block mb-1">ไฟล์ Schema</span>
                    <span class="font-bold text-emerald-400 flex items-center gap-1">✓ behn_project_tracker.sql</span>
                </div>
            </div>

            <!-- Action: 1-Click Install Button -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-950/60 via-slate-900/90 to-slate-900 border border-emerald-500/30 text-center space-y-3">
                <div class="text-sm font-bold text-emerald-300">วิธีที่ 1: ติดตั้งตารางข้อมูลอัตโนมัติ (แนะนำ)</div>
                <p class="text-xs text-slate-400 max-w-lg mx-auto">
                    กดปุ่มด้านล่างเพื่อให้ระบบสร้างตารางทั้งหมด 13 ตาราง พร้อมนำเข้าข้อมูลโครงการ งบประมาณ และผู้ใช้งานระบบเริ่มต้นทันที
                </p>
                <div class="pt-2 flex justify-center">
                    <a href="?action=install_db" class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold text-sm shadow-xl shadow-emerald-500/25 transition duration-150 transform hover:scale-[1.02] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        🚀 ติดตั้งตารางข้อมูลอัตโนมัติเดี๋ยวนี้ (Initialize Database)
                    </a>
                </div>
            </div>

            <!-- Step by Step Guide for Plesk phpMyAdmin -->
            <div class="space-y-3 pt-2">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <span>🛠️</span> หรือวิธีที่ 2: นำเข้าผ่าน phpMyAdmin ใน Plesk Control Panel
                </div>
                <div class="text-xs space-y-2 text-slate-300 bg-slate-900/50 p-4 rounded-xl border border-slate-800 leading-relaxed">
                    <div>1. เข้าสู่ <strong>Plesk Control Panel</strong> &rarr; ไปที่เมนู <strong class="text-white">Databases</strong></div>
                    <div>2. ที่ฐานข้อมูล <code class="text-emerald-300 bg-slate-800 px-1.5 py-0.5 rounded"><?= htmlspecialchars($db) ?></code> กดเปิด <strong class="text-amber-300">phpMyAdmin</strong></div>
                    <div>3. กดแท็บเมนูด้านบน <strong class="text-white">Import</strong> &rarr; เลือกไฟล์ <code class="text-emerald-300 bg-slate-800 px-1.5 py-0.5 rounded">behn_project_tracker.sql</code></div>
                    <div>4. กดปุ่ม <strong class="text-emerald-400">Import</strong> ด้านล่างสุด &rarr; เมื่อเสร็จแล้วกลับมากดปุ่มรีเฟรชด้านล่างนี้</div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-800">
                <div class="text-xs text-slate-400">
                    เมื่อติดตั้งเรียบร้อยแล้ว ระบบจะเปิดหน้าแดชบอร์ดโครงการให้ทันที
                </div>
                <button onclick="window.location.reload()" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs border border-slate-700 transition duration-150 flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    รีเฟรชตรวจสอบอีกครั้ง (Refresh)
                </button>
            </div>
        </div>
    </div>
</body>
</html>
        <?php
    }

    private static function renderConnectionError(PDOException $e, string $host, string $port, string $db, string $user): void
    {
        $isJson = (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');

        if ($isJson) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => 'Database connection failed',
                'message' => $e->getMessage(),
                'config' => [
                    'host' => $host,
                    'database' => $db,
                    'user' => $user,
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            return;
        }

        $envPath = dirname(__DIR__, 2) . '/.env';
        $hasEnv = file_exists($envPath);
        $errorMsg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');

        http_response_code(503);
        header('Content-Type: text/html; charset=utf-8');
        ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งค่าฐานข้อมูล - ระบบติดตามและบริหารโครงการเทศบาล</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Sarabun', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6">
    <div class="max-w-3xl w-full bg-slate-800/95 border border-slate-700/80 rounded-2xl shadow-2xl overflow-hidden backdrop-blur-xl">
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-700 via-teal-800 to-slate-900 p-6 sm:p-8 flex items-center gap-4 sm:gap-6 border-b border-slate-700/50">
            <img src="/images/mobile-logo.webp" onerror="this.src='/mobile-logo.webp'; this.onerror=null;" alt="Logo" class="w-16 h-16 sm:w-20 sm:h-20 object-contain drop-shadow-md bg-white/10 rounded-2xl p-2 border border-white/20">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30 mb-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    รอการตั้งค่าฐานข้อมูล MySQL (Database Setup Required)
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">ระบบติดตามและบริหารโครงการ</h1>
                <p class="text-sm sm:text-base text-emerald-200/90 font-medium">เทศบาลตำบลบ้านแฮด จังหวัดขอนแก่น</p>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
            <!-- Error Banner -->
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-200">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">⚠️</span>
                    <div class="space-y-1">
                        <div class="font-bold text-rose-300">ไม่สามารถเชื่อมต่อฐานข้อมูล MySQL ได้</div>
                        <div class="text-xs font-mono bg-rose-950/60 p-2.5 rounded-lg border border-rose-900/60 text-rose-200 break-all leading-relaxed">
                            <?= $errorMsg ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Detection -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-700/60">
                    <span class="text-slate-400 block mb-1">DB Host / Port</span>
                    <span class="font-mono font-bold text-white"><?= htmlspecialchars($host . ':' . $port) ?></span>
                </div>
                <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-700/60">
                    <span class="text-slate-400 block mb-1">Database Name</span>
                    <span class="font-mono font-bold text-emerald-400"><?= htmlspecialchars($db) ?></span>
                </div>
                <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-700/60">
                    <span class="text-slate-400 block mb-1">Database User</span>
                    <span class="font-mono font-bold text-amber-300"><?= htmlspecialchars($user) ?></span>
                </div>
                <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-700/60">
                    <span class="text-slate-400 block mb-1">สถานะไฟล์ .env</span>
                    <?php if ($hasEnv): ?>
                        <span class="font-bold text-emerald-400 flex items-center gap-1">✓ พบไฟล์ .env</span>
                    <?php else: ?>
                        <span class="font-bold text-rose-400 flex items-center gap-1">✗ ยังไม่มี .env</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Setup Steps for Plesk -->
            <div class="space-y-3 pt-2">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <span>🛠️</span> วิธีแก้ไขบน Plesk Hosting (HostAtom) ใน 3 ขั้นตอน:
                </h2>
                
                <div class="space-y-3 text-sm">
                    <!-- Step 1 -->
                    <div class="p-4 rounded-xl bg-slate-900/70 border border-slate-700/70 flex gap-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center flex-shrink-0 text-sm border border-emerald-500/30">1</div>
                        <div class="space-y-1">
                            <div class="font-bold text-slate-200">สร้างฐานข้อมูลใน Plesk</div>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                เข้าหน้าจัดการโดเมนใน Plesk &rarr; เลือกเมนู <strong class="text-white">Databases</strong> &rarr; กดปุ่ม <strong class="text-emerald-400">Add Database</strong><br>
                                ตั้งชื่อ Database เช่น <code class="bg-slate-800 px-1.5 py-0.5 rounded text-emerald-300">behn_project_tracker</code> และสร้าง User พร้อมกำหนดรหัสผ่าน (จดรหัสผ่านไว้)
                            </p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="p-4 rounded-xl bg-slate-900/70 border border-slate-700/70 flex gap-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center flex-shrink-0 text-sm border border-emerald-500/30">2</div>
                        <div class="space-y-1">
                            <div class="font-bold text-slate-200">Import ฐานข้อมูลเริ่มต้น</div>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                กดเปิด <strong class="text-amber-300">phpMyAdmin</strong> ของฐานข้อมูลที่สร้าง &rarr; ไปที่แท็บ <strong class="text-white">Import</strong> &rarr; เลือกไฟล์ <code class="bg-slate-800 px-1.5 py-0.5 rounded text-white">behn_project_tracker.sql</code> (อยู่ในโฟลเดอร์โปรเจกต์) แล้วกดปุ่ม Import
                            </p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="p-4 rounded-xl bg-slate-900/70 border border-slate-700/70 flex gap-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center flex-shrink-0 text-sm border border-emerald-500/30">3</div>
                        <div class="space-y-2">
                            <div class="font-bold text-slate-200">สร้างไฟล์ .env ในโฟลเดอร์เว็บไซต์</div>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                ไปที่เมนู <strong class="text-white">Files</strong> (File Manager) &rarr; เข้าโฟลเดอร์ <code class="bg-slate-800 px-1.5 py-0.5 rounded text-white">project-tracker.behn.go.th</code> &rarr; คัดลอกไฟล์ <code class="bg-slate-800 px-1.5 py-0.5 rounded text-white">.env.example</code> เป็น <code class="bg-slate-800 px-1.5 py-0.5 rounded text-emerald-300 font-bold">.env</code> แล้วแก้ไขค่าให้ตรงกับฐานข้อมูลที่สร้าง:
                            </p>
                            <pre class="bg-slate-950 p-3 rounded-lg text-xs font-mono text-emerald-300 border border-slate-800 overflow-x-auto leading-relaxed"><code>DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ชื่อฐานข้อมูลที่สร้างในPlesk
DB_USERNAME=ชื่อผู้ใช้ที่สร้างในPlesk
DB_PASSWORD=รหัสผ่านที่ตั้งไว้</code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-700/60">
                <div class="text-xs text-slate-400 text-center sm:text-left">
                    เมื่อตั้งค่าเรียบร้อยแล้ว กดปุ่มเพื่อตรวจสอบการเชื่อมต่อทันที
                </div>
                <button onclick="window.location.reload()" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold text-sm shadow-lg shadow-emerald-500/25 transition duration-150 flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    ลองเชื่อมต่อใหม่อีกครั้ง (Retry)
                </button>
            </div>
        </div>
    </div>
</body>
</html>
        <?php
    }

    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function fetchColumn(string $sql, array $params = []): mixed
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
        
        $pdo = self::connect();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return (int) $pdo->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $fields = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
        $sql = "UPDATE `{$table}` SET {$fields} WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        return self::execute($sql, $params);
    }

    public static function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        return self::execute($sql, $whereParams);
    }

    public static function transaction(callable $callback): mixed
    {
        $pdo = self::connect();
        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
