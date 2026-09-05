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
        }

        return self::$instance;
    }

    private static function ensureSchemaImported(PDO $pdo): void
    {
        try {
            $check = $pdo->query("SHOW TABLES LIKE 'projects'")->fetch();
            if (!$check) {
                $sqlFile = dirname(__DIR__, 2) . '/behn_project_tracker.sql';
                if (file_exists($sqlFile)) {
                    $sql = file_get_contents($sqlFile);
                    if ($sql) {
                        $pdo->exec($sql);
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Auto schema import notice: " . $e->getMessage());
        }
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
