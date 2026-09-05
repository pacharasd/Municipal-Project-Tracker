<?php

$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';

$oldDb = 'municipal_project_tracker';
$newDb = 'behn_project_tracker';

try {
    $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "1. Creating new database `{$newDb}`...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$newDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    echo "2. Copying tables and data from `{$oldDb}` to `{$newDb}`...\n";
    $stmt = $pdo->query("SHOW TABLES FROM `{$oldDb}`");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("USE `{$newDb}`;");

    foreach ($tables as $table) {
        echo "   - Copying table: {$table}...\n";
        $pdo->exec("DROP TABLE IF EXISTS `{$newDb}`.`{$table}`");
        $pdo->exec("CREATE TABLE `{$newDb}`.`{$table}` LIKE `{$oldDb}`.`{$table}`");
        $pdo->exec("INSERT INTO `{$newDb}`.`{$table}` SELECT * FROM `{$oldDb}`.`{$table}`");
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "SUCCESS: All " . count($tables) . " tables copied to `{$newDb}`.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
