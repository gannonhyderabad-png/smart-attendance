<?php
require_once __DIR__ . '/../app/Helpers/utils.php';
spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');
    $file = __DIR__ . '/../app/' . str_replace(['App\\', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
    $dbFile = __DIR__ . '/../database/' . str_replace(['Database\\', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($dbFile)) {
        require_once $dbFile;
    }
});

$pdo = \Database\Database::getConnection();
$employees = \App\Models\Employee::all();
echo "Found " . count($employees) . " employees.\n";
foreach (array_slice($employees, 0, 5) as $e) {
    echo "- {$e['id']} | {$e['employee_code']} | {$e['name']} | {$e['department']}\n";
}
