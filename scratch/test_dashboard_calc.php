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
$today = date('Y-m-d');

// Test the updated DashboardController Top Departments query:
$stmtDepts = $pdo->prepare("
    SELECT 
        COALESCE(NULLIF(TRIM(d.name), ''), NULLIF(TRIM(e.department), ''), 'General') as dept_name,
        COUNT(DISTINCT e.id) as employee_count,
        COUNT(DISTINCT CASE WHEN a.punch_date = ? AND a.punch_type = 'IN' THEN a.employee_id END) as present_count
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN attendance a ON a.employee_id = e.id AND a.punch_date = ? AND a.punch_type = 'IN'
    WHERE e.status = 'active' AND e.deleted_at IS NULL
    GROUP BY dept_name
    HAVING employee_count > 0
    ORDER BY employee_count DESC, dept_name ASC
");
$stmtDepts->execute([$today, $today]);
$deptRows = $stmtDepts->fetchAll(PDO::FETCH_ASSOC);

echo "DEPARTMENTS SHOWN IN DASHBOARD:\n";
foreach ($deptRows as $r) {
    echo "- " . $r['dept_name'] . ": " . $r['employee_count'] . " staff, " . $r['present_count'] . " present\n";
}

// Test Leave Overview query:
$stmtLeaves = $pdo->query("SELECT TRIM(leave_type) as leave_name, COUNT(*) as c FROM leaves WHERE status != 'REJECTED' GROUP BY leave_name ORDER BY c DESC, leave_name ASC");
$leaveRows = $stmtLeaves ? $stmtLeaves->fetchAll(PDO::FETCH_ASSOC) : [];
echo "\nLEAVE OVERVIEW SHOWN IN DASHBOARD:\n";
if (empty($leaveRows)) {
    echo "- Total: 0 (No leaves recorded)\n";
} else {
    foreach ($leaveRows as $lr) {
        echo "- " . $lr['leave_name'] . ": " . $lr['c'] . "\n";
    }
}
