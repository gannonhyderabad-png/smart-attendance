<?php

require_once __DIR__ . '/../app/Helpers/utils.php';

spl_autoload_register(function ($class) {
    $prefixMap = [
        'App\\' => __DIR__ . '/../app/',
        'Database\\' => __DIR__ . '/../database/'
    ];

    foreach ($prefixMap as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
            if (file_exists($file)) require_once $file;
        }
    }
});

$appConfig = require __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database.php';
\Database\Database::init($dbConfig);
\Database\Database::autoMigrate();

// Update sai (ID 26) with real Bengaluru coordinates
$sai = \App\Models\Employee::find(26);
if ($sai) {
    \App\Models\Employee::update($sai['id'], [
        'employee_code' => 'EMP-2448',
        'name' => 'sai',
        'site' => 'Bengaluru IT Park',
        'site_latitude' => 12.971599,
        'site_longitude' => 77.594566,
        'site_radius' => 200,
        'geofence_enabled' => 1
    ]);
    echo "Updated sai (ID 26, Code: EMP-2448) with real Bengaluru coordinates (12.971599, 77.594566).\n";
}

// Test Punch OUT from Hyderabad (17.429335, 78.457624) -> SHOULD FAIL (500 km away)!
echo "Testing Punch OUT for sai from Hyderabad (~500 km away)... \n";
$hydOutPunch = \App\Models\Attendance::recordPunch($sai['id'], 'OUT', [
    'latitude' => 17.429335,
    'longitude' => 78.457624,
    'ip_address' => '183.82.126.42'
]);
echo "Result: " . json_encode($hydOutPunch, JSON_PRETTY_PRINT) . "\n";
assert($hydOutPunch['success'] === false && str_contains($hydOutPunch['message'], 'Punch Rejected'), 'Punch OUT should have failed due to geofence!');
echo "Rejection Assertion: PASSED (Correctly rejected: \"{$hydOutPunch['message']}\")\n";
