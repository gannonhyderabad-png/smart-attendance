<?php

/**
 * Database Configuration
 */

$dbUrl = env('DATABASE_URL') ?: env('MYSQL_URL') ?: env('POSTGRES_URL') ?: env('JAWSDB_URL');
$defaultDriver = env('DB_CONNECTION');
$mysqlConfig = [
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
];

if ($dbUrl) {
    $parsed = parse_url($dbUrl);
    if ($parsed) {
        $scheme = $parsed['scheme'] ?? 'mysql';
        $defaultDriver = ($scheme === 'postgres' || $scheme === 'postgresql') ? 'pgsql' : 'mysql';
        $mysqlConfig['host'] = $parsed['host'] ?? '127.0.0.1';
        $mysqlConfig['port'] = (string)($parsed['port'] ?? ($defaultDriver === 'pgsql' ? '5432' : '3306'));
        $mysqlConfig['database'] = ltrim($parsed['path'] ?? 'attendance_db', '/');
        $mysqlConfig['username'] = $parsed['user'] ?? 'root';
        $mysqlConfig['password'] = $parsed['pass'] ?? '';
    }
}

if (!$defaultDriver) {
    $mysqlAvailable = false;
    if (extension_loaded('pdo_mysql')) {
        $socket = @fsockopen($mysqlConfig['host'], (int) $mysqlConfig['port'], $errno, $errstr, 0.4);
        if ($socket) {
            $mysqlAvailable = true;
            @fclose($socket);
        }
    }
    $defaultDriver = $mysqlAvailable ? 'mysql' : 'sqlite';
}

$sqlitePath = env('DB_SQLITE_PATH');
if (!$sqlitePath) {
    $sqlitePath = __DIR__ . '/../database/attendance.sqlite';
} elseif (!str_starts_with($sqlitePath, '/') && !preg_match('/^[A-Z]:[\\\\\/]/i', $sqlitePath)) {
    $sqlitePath = __DIR__ . '/../' . ltrim($sqlitePath, '/\\');
}

return [
    'default' => $defaultDriver,

    'connections' => [
        'mysql' => $mysqlConfig,

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => $mysqlConfig['host'],
            'port' => $mysqlConfig['port'] ?? '5432',
            'database' => $mysqlConfig['database'],
            'username' => $mysqlConfig['username'],
            'password' => $mysqlConfig['password'],
            'charset' => 'utf8',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => $sqlitePath,
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        ]
    ]
];

