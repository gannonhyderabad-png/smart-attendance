<?php

$pdo = new PDO('sqlite:c:/Users/rampr/.gemini/antigravity/scratch/Helpdesk/database/attendance.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Delete all auto-generated test departments
$pdo->exec("DELETE FROM departments WHERE name LIKE 'Civil Site Operations%' OR name LIKE 'Logistics & Fleet%' OR name LIKE 'TXTDEPT%'");

// 2. See what departments exist on active employees
$stmt = $pdo->query("SELECT id, name, department_id, department FROM employees WHERE deleted_at IS NULL");
$activeEmps = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "ACTIVE EMPLOYEES:\n";
foreach ($activeEmps as $emp) {
    echo "  - {$emp['name']} : '{$emp['department']}'\n";
}

// 3. Clean departments table: keep only departments that are assigned to employees or standard ones
$pdo->exec("DELETE FROM departments WHERE name NOT IN (SELECT DISTINCT department FROM employees WHERE deleted_at IS NULL AND department IS NOT NULL) AND name NOT IN ('General', 'IT', 'Engineering', 'Operations', 'Management')");

$finalDepts = $pdo->query("SELECT DISTINCT name FROM (SELECT name FROM departments UNION SELECT department as name FROM employees WHERE deleted_at IS NULL AND department IS NOT NULL AND TRIM(department) != '') ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);

echo "\nFINAL CLEAN DEPARTMENTS:\n";
foreach ($finalDepts as $d) {
    echo "  * " . $d . "\n";
}
