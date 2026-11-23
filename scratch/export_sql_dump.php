<?php
require_once __DIR__ . '/../app/Helpers/utils.php';
spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');
    $file = __DIR__ . '/../app/' . str_replace(['App\\', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($file)) require_once $file;
    $dbFile = __DIR__ . '/../database/' . str_replace(['Database\\', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($dbFile)) require_once $dbFile;
});

$pdo = \Database\Database::getConnection();
$tables = ['users', 'departments', 'employees', 'attendance', 'leaves', 'holidays', 'settings', 'devices', 'activity_logs'];

$sqlDump = "-- ========================================================\n";
$sqlDump .= "-- Smart Attendance SQL Dump for Visual Studio / Any SQL Server\n";
$sqlDump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$sqlDump .= "-- ========================================================\n\n";

foreach ($tables as $table) {
    $createStmt = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'")->fetchColumn();
    if ($createStmt) {
        $sqlDump .= "-- Table structure for `$table`\n";
        $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n";
        $sqlDump .= $createStmt . ";\n\n";

        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $sqlDump .= "-- Dumping data for `$table` (" . count($rows) . " rows)\n";
            foreach ($rows as $row) {
                $cols = array_keys($row);
                $escapedCols = array_map(function($c) { return "`$c`"; }, $cols);
                $escapedVals = array_map(function($v) use ($pdo) {
                    if ($v === null) return 'NULL';
                    return $pdo->quote((string)$v);
                }, array_values($row));
                $sqlDump .= "INSERT INTO `$table` (" . implode(', ', $escapedCols) . ") VALUES (" . implode(', ', $escapedVals) . ");\n";
            }
            $sqlDump .= "\n";
        }
    }
}

file_put_contents(__DIR__ . '/../database/smart_attendance_full.sql', $sqlDump);
echo "Exported smart_attendance_full.sql successfully (" . strlen($sqlDump) . " bytes)\n";
