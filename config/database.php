<?php

/**
 * Database Configuration
 */

$dbUrl = env('DATABASE_URL') ?: env('DATABASE_PRIVATE_URL') ?: env('DATABASE_PUBLIC_URL') ?: env('MYSQL_URL') ?: env('MYSQL_PRIVATE_URL') ?: env('MYSQL_PUBLIC_URL') ?: env('POSTGRES_URL') ?: env('JAWSDB_URL');
$defaultDriver = env('DB_CONNECTION');

$mysqlHost = env('DB_HOST') ?: env('MYSQLHOST') ?: env('PGHOST') ?: '127.0.0.1';
$mysqlPort = env('DB_PORT') ?: env('MYSQLPORT') ?: env('PGPORT') ?: '3306';
$mysqlDb   = env('DB_DATABASE') ?: env('MYSQLDATABASE') ?: env('PGDATABASE') ?: 'attendance_db';
$mysqlUser = env('DB_USERNAME') ?: env('MYSQLUSER') ?: env('PGUSER') ?: 'root';
$mysqlPass = env('DB_PASSWORD') ?: env('MYSQLPASSWORD') ?: env('PGPASSWORD') ?: '';

$mysqlConfig = [
    'driver' => 'mysql',
    'host' => $mysqlHost,
    'port' => (string) $mysqlPort,
    'database' => $mysqlDb,
    'username' => $mysqlUser,
    'password' => $mysqlPass,
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

if (env('PGHOST') || env('POSTGRES_URL')) {
    $defaultDriver = 'pgsql';
} elseif (env('MYSQLHOST') || env('MYSQL_URL')) {
    $defaultDriver = 'mysql';
}

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
    if (extension_loaded('pdo_mysql') && $mysqlConfig['host'] !== '127.0.0.1') {
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

