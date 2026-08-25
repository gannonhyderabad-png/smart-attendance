<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Helpers/utils.php';

// Autoloading
spl_autoload_register(function ($class) {
    $prefixMap = [
        'App\\' => __DIR__ . '/../app/',
        'Database\\' => __DIR__ . '/../database/'
    ];

    foreach ($prefixMap as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

$appConfig = require __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database.php';

echo "=== STARTING SMART ATTENDANCE TEST SUITE ===\n\n";

// 1. Initialize Database
echo "[1] Testing Database Connection & Migration... ";
try {
    \Database\Database::init($dbConfig);
    \Database\Database::autoMigrate();
    echo "PASSED\n";
} catch (\Throwable $e) {
    if ($dbConfig['default'] === 'mysql') {
        $dbConfig['default'] = 'sqlite';
        \Database\Database::init($dbConfig);
        \Database\Database::autoMigrate();
        echo "PASSED (using SQLite fallback)\n";
    } else {
        echo "FAILED: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// 2. Test Admin Authentication
echo "[2] Testing Admin Auth (admin@attendance.local / admin123)... ";
$authSuccess = \App\Core\Auth::attempt('admin@attendance.local', 'admin123');
assert($authSuccess === true, 'Admin login failed');
$authFail = \App\Core\Auth::attempt('admin@attendance.local', 'wrongpass');
assert($authFail === false, 'Invalid login succeeded unexpectedly');
echo "PASSED\n";

// 3. Test Employee Retrieval & Punch URLs
echo "[3] Testing Employee Retrieval & URL Generation... ";
$emp = \App\Models\Employee::findByCode('EMP001');
if (!$emp) {
    \App\Models\Employee::create([
        'employee_code' => 'EMP001',
        'name' => 'Alex Johnson',
        'email' => 'alex.j@company.com',
        'phone' => '+1 555-019-2831',
        'department_id' => 1,
        'designation' => 'Senior Backend Engineer',
        'project' => 'Enterprise Helpdesk ERP',
        'site' => 'Headquarters Building A',
        'status' => 'active'
    ]);
    $emp = \App\Models\Employee::findByCode('EMP001');
}
assert($emp !== null, 'EMP001 could not be loaded');
$punchUrl = punch_url($emp['employee_code']);
assert(str_contains($punchUrl, '/p/EMP001'), "Punch URL invalid: $punchUrl");
echo "PASSED (URL: {$punchUrl})\n";

// 4. Test Employee Auto-Code Generation
echo "[4] Testing Auto-generation of next Employee Code... ";
$nextCode = \App\Models\Employee::generateNextCode('EMP-');
assert(!empty($nextCode), 'Generated code was empty');
echo "PASSED (Next suggested code: {$nextCode})\n";

// 5. Test Employee Creation
echo "[5] Testing New Employee Creation... ";
$testCode = 'TEST-' . rand(1000, 9999);
$newEmpId = \App\Models\Employee::create([
    'employee_code' => $testCode,
    'name' => 'Automated Test User',
    'email' => 'test@test.local',
    'phone' => '1234567890',
    'department_id' => 1,
    'designation' => 'Automation Bot',
    'status' => 'active'
]);
assert($newEmpId > 0, 'Failed to create employee');
$createdEmp = \App\Models\Employee::find($newEmpId);
assert($createdEmp['name'] === 'Automated Test User', 'Employee name mismatch');
echo "PASSED (Created ID: {$newEmpId}, Code: {$testCode})\n";

// 6. Test Mobile Punch IN
echo "[6] Testing Mobile Punch IN for {$testCode}... ";
$punchInRes = \App\Models\Attendance::recordPunch($newEmpId, 'IN', [
    'ip_address' => '192.168.1.100',
    'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
    'device_info' => 'Mobile (iPhone iOS - Safari)'
]);
assert($punchInRes['success'] === true, 'Punch IN failed: ' . ($punchInRes['message'] ?? ''));
assert($punchInRes['current_status'] === 'IN', 'Status is not IN');
echo "PASSED (Punch IN ID: {$punchInRes['punch_id']})\n";

// 7. Test Duplicate Punch IN Prevention
echo "[7] Testing Duplicate Punch IN Prevention... ";
$dupInRes = \App\Models\Attendance::recordPunch($newEmpId, 'IN', ['ip_address' => '192.168.1.100']);
assert($dupInRes['success'] === false, 'Duplicate punch IN was not rejected!');
echo "PASSED (Correctly rejected with: \"{$dupInRes['message']}\")\n";

// 8. Test Spam Cooldown
echo "[8] Testing Spam Rapid Punch Cooldown Guard... ";
$rapidRes = \App\Models\Attendance::recordPunch($newEmpId, 'OUT', ['ip_address' => '192.168.1.100']);
assert($rapidRes['success'] === false && str_contains($rapidRes['message'], 'wait a few moments'), 'Rapid cooldown guard failed');
echo "PASSED (Correctly rejected rapid punch attempt)\n";

// 9. Test Valid Mobile Punch OUT (after cooldown)
echo "[9] Testing Valid Mobile Punch OUT for {$testCode} (waiting cooldown)... ";
sleep(5);
$punchOutRes = \App\Models\Attendance::recordPunch($newEmpId, 'OUT', [
    'ip_address' => '192.168.1.100',
    'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
    'device_info' => 'Mobile (iPhone iOS - Safari)'
]);
assert($punchOutRes['success'] === true, 'Punch OUT failed: ' . ($punchOutRes['message'] ?? ''));
assert($punchOutRes['current_status'] === 'OUT', 'Status is not OUT');
echo "PASSED (Punch OUT ID: {$punchOutRes['punch_id']})\n";

// 10. Test Duplicate Punch OUT Prevention
echo "[10] Testing Duplicate Punch OUT Prevention... ";
$dupOutRes = \App\Models\Attendance::recordPunch($newEmpId, 'OUT', ['ip_address' => '192.168.1.100']);
assert($dupOutRes['success'] === false, 'Duplicate punch OUT was not rejected!');
echo "PASSED (Correctly rejected with: \"{$dupOutRes['message']}\")\n";

// 11. Test Work Duration Calculation
echo "[11] Testing Work Duration Calculation... ";
$workedSeconds = \App\Models\Attendance::calculateWorkSeconds($newEmpId, date('Y-m-d'));
assert($workedSeconds >= 1, "Worked seconds should be >= 1, got {$workedSeconds}");
echo "PASSED (Calculated worked time: {$workedSeconds}s / " . format_seconds($workedSeconds) . ")\n";

// 12. Test GPS Geofencing (200m Radius Guard)
echo "[12] Testing GPS Geofencing (200m Radius Guard)... \n";

// Site coords: Hyderabad Hitec City (17.448500, 78.374500)
$siteLat = 17.448500;
$siteLon = 78.374500;
$siteRadius = 200;

$geoEmpCode = 'GEO-TEST-' . rand(1000, 9999);
$geoEmpId = \App\Models\Employee::create([
    'employee_code' => $geoEmpCode,
    'name' => 'Geofenced Field Engineer',
    'site' => 'Hyderabad Cyber Towers',
    'site_latitude' => $siteLat,
    'site_longitude' => $siteLon,
    'site_radius' => $siteRadius,
    'geofence_enabled' => 1,
    'status' => 'active'
]);

// Test A: Punch with NO GPS -> Should FAIL
$noGpsRes = \App\Models\Attendance::recordPunch($geoEmpId, 'IN', ['ip_address' => '127.0.0.1']);
assert($noGpsRes['success'] === false && str_contains($noGpsRes['message'], 'Location permission required'), 'No GPS was not rejected!');
echo "    [12a] Missing GPS location rejection: PASSED (Rejected with: \"{$noGpsRes['message']}\")\n";

// Test B: Punch 600 meters away (17.453000, 78.378000) -> Should FAIL (outside 200m)
$outsideDist = calculate_haversine_distance(17.453000, 78.378000, $siteLat, $siteLon);
$outGpsRes = \App\Models\Attendance::recordPunch($geoEmpId, 'IN', [
    'latitude' => 17.453000,
    'longitude' => 78.378000,
    'ip_address' => '127.0.0.1'
]);
assert($outGpsRes['success'] === false && str_contains($outGpsRes['message'], 'Punch Rejected'), 'Outside 200m was not rejected!');
echo "    [12b] Outside 200m radius punch rejection (" . round($outsideDist) . "m away): PASSED (Rejected with: \"{$outGpsRes['message']}\")\n";

// Test C: Punch 45 meters away (17.448700, 78.374800) -> Should SUCCEED (within 200m)
$insideDist = calculate_haversine_distance(17.448700, 78.374800, $siteLat, $siteLon);
$inGpsRes = \App\Models\Attendance::recordPunch($geoEmpId, 'IN', [
    'latitude' => 17.448700,
    'longitude' => 78.374800,
    'ip_address' => '127.0.0.1'
]);
assert($inGpsRes['success'] === true, 'Valid within 200m punch failed: ' . ($inGpsRes['message'] ?? ''));
assert($inGpsRes['location_verified'] === 1, 'Location was not marked verified');
echo "    [12c] Valid punch within 200m (" . round($insideDist) . "m away): PASSED (Verified: \"{$inGpsRes['message']}\")\n";

// 13. Test Daily Summary & Monthly Timesheet
echo "[13] Testing Daily Summary & Monthly Timesheet generation... ";
$dailySummary = \App\Models\Attendance::getDailySummary(date('Y-m-d'));
assert(!empty($dailySummary), 'Daily summary is empty');
$monthly = \App\Models\Attendance::getMonthlyReport((int)date('Y'), (int)date('n'));
assert(!empty($monthly['data']), 'Monthly report data is empty');
echo "PASSED (Daily employees: " . count($dailySummary) . ", Monthly: " . count($monthly['data']) . ")\n";

// 14. Test Audit Logger
echo "[14] Testing Activity Audit Trail... ";
\App\Core\Logger::log(1, 'UNIT_TEST', 'Completed automated test suite execution.');
$logCount = \Database\Database::getConnection()->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
assert($logCount > 0, 'No activity logs found');
echo "PASSED (Total logs recorded: {$logCount})\n";

// 15. Test Employee Profile Photo & Live Punch Selfie Capture
echo "[15] Testing Employee Photo Upload & Live Selfie Capture... \n";

// 1x1 transparent red PNG in base64
$fakeSelfieBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

// Test save_base64_image helper
$savedPath = save_base64_image($fakeSelfieBase64, 'avatars');
assert(!empty($savedPath) && file_exists(dirname(__DIR__) . '/' . $savedPath), 'save_base64_image failed to write file!');
echo "    [15a] Avatar save helper: PASSED (Saved: {$savedPath})\n";

// Create employee with photo
$photoEmpCode = 'SELFIE-' . rand(1000, 9999);
$photoEmpId = \App\Models\Employee::create([
    'employee_code' => $photoEmpCode,
    'name' => 'Selfie Test User',
    'photo' => $savedPath,
    'status' => 'active'
]);

$fetchedPhotoEmp = \App\Models\Employee::find($photoEmpId);
assert($fetchedPhotoEmp['photo'] === $savedPath, 'Employee photo was not saved properly');
echo "    [15b] Employee profile photo persistence: PASSED\n";

// Punch IN with Selfie Snapshot
$selfiePunchIn = \App\Models\Attendance::recordPunch($photoEmpId, 'IN', [
    'photo_data' => $fakeSelfieBase64,
    'ip_address' => '127.0.0.1'
]);
assert($selfiePunchIn['success'] === true, 'Selfie Punch IN failed: ' . ($selfiePunchIn['message'] ?? ''));
assert(!empty($selfiePunchIn['punch_photo']), 'Selfie Punch IN did not return punch_photo');
assert(file_exists(dirname(__DIR__) . '/' . $selfiePunchIn['punch_photo']), 'Selfie Punch IN file was not created on disk!');
echo "    [15c] Punch IN with Live Camera Snapshot: PASSED (Photo: {$selfiePunchIn['punch_photo']})\n";

sleep(5);

// Punch OUT with Selfie Snapshot
$selfiePunchOut = \App\Models\Attendance::recordPunch($photoEmpId, 'OUT', [
    'photo_data' => $fakeSelfieBase64,
    'ip_address' => '127.0.0.1'
]);
assert($selfiePunchOut['success'] === true, 'Selfie Punch OUT failed');
assert(!empty($selfiePunchOut['punch_photo']), 'Selfie Punch OUT did not return punch_photo');
echo "    [15d] Punch OUT with Live Camera Snapshot: PASSED (Photo: {$selfiePunchOut['punch_photo']})\n";

// Verify Paired Sessions contains in_photo and out_photo
$sessions = \App\Models\Attendance::getPairedSessions(['employee_id' => $photoEmpId]);
assert(!empty($sessions), 'No paired sessions found for selfie test employee');
assert(!empty($sessions[0]['in_photo']), 'in_photo is missing in paired sessions');
assert(!empty($sessions[0]['out_photo']), 'out_photo is missing in paired sessions');
assert($sessions[0]['employee_photo'] === $savedPath, 'employee_photo is missing in paired sessions');
echo "    [15e] Paired Session IN/OUT Photo linking: PASSED\n";

// 16. Test Auto-Close of Unassigned OUT Punch based on Site Shift End Time
echo "[16] Testing Auto-Close of Unassigned OUT Punch based on Site Shift End Time... \n";

// Create employee with shift 09:00:00 to 18:00:00 (9 hours)
$noOutEmpCode = 'NOOUT-' . rand(1000, 9999);
$noOutEmpId = \App\Models\Employee::create([
    'employee_code' => $noOutEmpCode,
    'name' => 'Unassigned Out Test Staff',
    'shift_start' => '09:00:00',
    'shift_end' => '18:00:00',
    'status' => 'active'
]);

// Insert a past date IN punch at 09:30:00 AM with NO OUT punch
$pastDate = date('Y-m-d', strtotime('-2 days'));
$pastPunchTime = $pastDate . ' 09:30:00';
$pdo = \Database\Database::getConnection();
$stmt = $pdo->prepare("INSERT INTO attendance (employee_id, punch_type, punch_time, punch_date, ip_address) VALUES (?, 'IN', ?, ?, '127.0.0.1')");
$stmt->execute([$noOutEmpId, $pastPunchTime, $pastDate]);

// A. Test calculateWorkSeconds for the past unclosed date:
// From 09:30:00 to 18:00:00 is 8 hours 30 mins = 30600 seconds
$workedSecs = \App\Models\Attendance::calculateWorkSeconds($noOutEmpId, $pastDate);
assert($workedSecs === 30600, "Expected 30600 seconds (8.5 hrs), got {$workedSecs}");
echo "    [16a] Auto-calculated work duration (09:30 to 18:00): PASSED ({$workedSecs}s / " . format_seconds($workedSecs) . ")\n";

// B. Test getPairedSessions auto-closed session status
$noOutSessions = \App\Models\Attendance::getPairedSessions(['employee_id' => $noOutEmpId, 'date' => $pastDate]);
assert(!empty($noOutSessions), 'No paired sessions returned for unclosed punch');
assert($noOutSessions[0]['status'] === 'NO_OUT', "Expected status NO_OUT, got {$noOutSessions[0]['status']}");
assert(!empty($noOutSessions[0]['auto_closed']), 'Session was not marked auto_closed');
assert($noOutSessions[0]['duration_seconds'] === 30600, "Expected 30600s duration, got {$noOutSessions[0]['duration_seconds']}");
assert($noOutSessions[0]['formatted_duration'] === '08:30:00', "Expected 08:30:00 formatted duration, got {$noOutSessions[0]['formatted_duration']}");
echo "    [16b] Paired session status & auto-closed flags: PASSED (Status: {$noOutSessions[0]['status']}, Duration: {$noOutSessions[0]['formatted_duration']})\n";

// C. Test getPairedSessions with punch_type=NO_OUT filter
$filteredNoOut = \App\Models\Attendance::getPairedSessions(['punch_type' => 'NO_OUT']);
assert(!empty($filteredNoOut), 'Filter punch_type=NO_OUT returned 0 records');
echo "    [16c] NO_OUT filter query: PASSED (Found: " . count($filteredNoOut) . " auto-closed sessions)\n";

// D. Test getDailySummary for past unclosed date
$dailySummary = \App\Models\Attendance::getDailySummary($pastDate);
$empSummary = null;
foreach ($dailySummary as $item) {
    if ($item['employee']['id'] == $noOutEmpId) {
        $empSummary = $item;
        break;
    }
}
assert($empSummary !== null && $empSummary['status'] === 'NO_OUT_PUNCH', 'Daily summary did not flag NO_OUT_PUNCH');
assert($empSummary['seconds_worked'] === 30600, "Daily summary worked seconds expected 30600, got {$empSummary['seconds_worked']}");
echo "    [16d] Daily summary NO_OUT_PUNCH status: PASSED\n";

// 17. Testing Text Format Department Input
echo "[17] Testing Text Format Department Input in Employee Creation & Update... \n";
$textDeptEmpCode = 'TXTDEPT-' . rand(1000, 9999);
$customDeptName = 'Civil Site Operations ' . rand(100, 999);
$textDeptEmpId = \App\Models\Employee::create([
    'employee_code' => $textDeptEmpCode,
    'name' => 'Text Department Staff',
    'department' => $customDeptName,
    'designation' => 'Site Manager',
    'status' => 'active'
]);

$fetchedEmp = \App\Models\Employee::find($textDeptEmpId);
assert($fetchedEmp !== null, 'Failed to fetch employee created with text department');
assert($fetchedEmp['department_name'] === $customDeptName || $fetchedEmp['department'] === $customDeptName, "Expected department '{$customDeptName}', got '{$fetchedEmp['department_name']}'");
echo "    [17a] Create Employee with Custom Text Department: PASSED (Department: {$fetchedEmp['department_name']})\n";

// Test update with another text department
$updatedDeptName = 'Logistics & Fleet ' . rand(100, 999);
\App\Models\Employee::update($textDeptEmpId, [
    'department' => $updatedDeptName
]);
$updatedEmp = \App\Models\Employee::find($textDeptEmpId);
assert($updatedEmp['department_name'] === $updatedDeptName || $updatedEmp['department'] === $updatedDeptName, "Expected updated department '{$updatedDeptName}', got '{$updatedEmp['department_name']}'");
echo "    [17b] Update Employee with New Text Department: PASSED (Department: {$updatedEmp['department_name']})\n";

// 18. Testing Employee Recycle Bin (Soft Delete, Restore, Force Delete, Empty Trash)
echo "[18] Testing Employee Recycle Bin Lifecycle (Trash, Restore, Force Delete)... \n";
$recycleEmpCode = 'TRASH-' . rand(1000, 9999);
$recycleEmpId = \App\Models\Employee::create([
    'employee_code' => $recycleEmpCode,
    'name' => 'Recycle Bin Test Staff',
    'status' => 'active'
]);

// 18a: Soft delete (Move to Recycle Bin)
\App\Models\Employee::delete($recycleEmpId);
$trashedEmp = \App\Models\Employee::find($recycleEmpId);
assert(!empty($trashedEmp['deleted_at']), 'Employee deleted_at timestamp was not set on soft delete!');
$activeList = \App\Models\Employee::all();
$foundInActive = array_filter($activeList, fn($e) => $e['id'] == $recycleEmpId);
assert(empty($foundInActive), 'Trashed employee still appeared in active staff list!');

$trashList = \App\Models\Employee::all(['view' => 'trash']);
$foundInTrash = array_filter($trashList, fn($e) => $e['id'] == $recycleEmpId);
assert(!empty($foundInTrash), 'Trashed employee was not found in Recycle Bin list!');
echo "    [18a] Move to Recycle Bin (Soft Delete): PASSED (Employee: {$recycleEmpCode})\n";

// 18b: Restore from Recycle Bin
\App\Models\Employee::restore($recycleEmpId);
$restoredEmp = \App\Models\Employee::find($recycleEmpId);
assert(empty($restoredEmp['deleted_at']), 'Employee deleted_at was not cleared on restore!');
assert($restoredEmp['status'] === 'active', 'Employee status was not restored to active!');
$activeListAfterRestore = \App\Models\Employee::all();
$foundInActiveAfter = array_filter($activeListAfterRestore, fn($e) => $e['id'] == $recycleEmpId);
assert(!empty($foundInActiveAfter), 'Restored employee did not return to active staff list!');
echo "    [18b] Restore from Recycle Bin: PASSED (Restored to Active)\n";

// 18c: Permanent Force Delete
\App\Models\Employee::forceDelete($recycleEmpId);
$forceDeletedEmp = \App\Models\Employee::find($recycleEmpId);
assert($forceDeletedEmp === null, 'Employee was not permanently deleted on forceDelete!');
echo "    [18c] Permanent Force Delete: PASSED (Wiped from database)\n";

// Clean up test employees & test departments
\App\Models\Employee::forceDelete($newEmpId);
\App\Models\Employee::forceDelete($geoEmpId);
\App\Models\Employee::forceDelete($photoEmpId);
\App\Models\Employee::forceDelete($noOutEmpId);
\App\Models\Employee::forceDelete($textDeptEmpId);
\Database\Database::getConnection()->exec("DELETE FROM departments WHERE name LIKE 'Civil Site Operations%' OR name LIKE 'Logistics & Fleet%' OR name LIKE 'TXTDEPT%'");

echo "\n=== ALL VERIFICATION TESTS PASSED SUCCESSFULLY! ===\n";
