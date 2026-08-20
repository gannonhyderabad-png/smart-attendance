<?php

/**
 * Database Configuration
 */

// Auto-detect MySQL availability or fallback seamlessly to SQLite
$defaultDriver = env('DB_CONNECTION');
if (!$defaultDriver) {
    $mysqlAvailable = false;
    if (extension_loaded('pdo_mysql')) {
        $socket = @fsockopen(env('DB_HOST', '127.0.0.1'), (int) env('DB_PORT', 3306), $errno, $errstr, 0.4);
        if ($socket) {
            $mysqlAvailable = true;
            @fclose($socket);
        }
    }
    $defaultDriver = $mysqlAvailable ? 'mysql' : 'sqlite';
}

return [
    'default' => $defaultDriver,

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'attendance_db'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../' . env('DB_SQLITE_PATH', 'database/attendance.sqlite'),
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        ]
    ]
];
