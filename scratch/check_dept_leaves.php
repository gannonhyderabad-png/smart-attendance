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
echo "--- DEPARTMENTS TABLE ---\n";
$depts = $pdo->query("SELECT * FROM departments")->fetchAll(PDO::FETCH_ASSOC);
foreach ($depts as $d) {
    echo "ID: {$d['id']} | Name: {$d['name']} | Code: " . ($d['code'] ?? '') . "\n";
}

echo "\n--- EMPLOYEES DISTINCT DEPARTMENTS ---\n";
$empDepts = $pdo->query("SELECT DISTINCT department FROM employees WHERE status = 'active' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_COLUMN);
foreach ($empDepts as $ed) {
    echo "- '$ed'\n";
}

echo "\n--- LEAVES DISTINCT TYPES ---\n";
$leaves = $pdo->query("SELECT DISTINCT leave_type FROM leaves")->fetchAll(PDO::FETCH_COLUMN);
foreach ($leaves as $l) {
    echo "- '$l'\n";
}
