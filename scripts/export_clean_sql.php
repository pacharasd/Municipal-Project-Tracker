<?php

$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';
$db   = 'behn_project_tracker';

$outputFile = __DIR__ . '/../behn_project_tracker.sql';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $out = "-- Database Dump for: {$db}\n";
    $out .= "-- Generated for Plesk / phpMyAdmin import\n";
    $out .= "SET FOREIGN_KEY_CHECKS = 0;\n";
    $out .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
    $out .= "SET NAMES utf8mb4;\n\n";

    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
        $out .= "-- --------------------------------------------------------\n";
        $out .= "-- Table structure for `{$table}`\n";
        $out .= "-- --------------------------------------------------------\n";
        $out .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $out .= $createStmt['Create Table'] . ";\n\n";

        $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $out .= "-- Dumping data for table `{$table}`\n";
            $columns = array_keys($rows[0]);
            $colList = '`' . implode('`, `', $columns) . '`';

            $chunkSize = 100;
            $chunks = array_chunk($rows, $chunkSize);
            foreach ($chunks as $chunk) {
                $valRows = [];
                foreach ($chunk as $row) {
                    $vals = [];
                    foreach ($row as $val) {
                        if ($val === null) {
                            $vals[] = "NULL";
                        } else {
                            $vals[] = $pdo->quote($val);
                        }
                    }
                    $valRows[] = "(" . implode(", ", $vals) . ")";
                }
                $out .= "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $valRows) . ";\n\n";
            }
        }
    }

    $out .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    file_put_contents($outputFile, $out);
    echo "SUCCESS: Exported {$outputFile} (" . strlen($out) . " bytes)\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
