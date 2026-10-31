<?php

/**
 * Database Configuration
 * Supports Cloud PostgreSQL (Neon, Supabase, Render), Cloud MySQL (Aiven, TiDB, Railway),
 * Render Persistent Disk (/var/data), and local SQLite fallback.
 */

$dbUrl = env('DATABASE_URL')
    ?: env('DATABASE_PRIVATE_URL')
    ?: env('DATABASE_PUBLIC_URL')
    ?: env('POSTGRES_URL')
    ?: env('POSTGRES_PRISMA_URL')
    ?: env('MYSQL_URL')
    ?: env('MYSQL_PRIVATE_URL')
    ?: env('MYSQL_PUBLIC_URL')
    ?: env('JAWSDB_URL');

$defaultDriver = env('DB_CONNECTION');
$hasExplicitCloudDb = !empty($dbUrl);

$dbHost = env('DB_HOST') ?: env('PGHOST') ?: env('MYSQLHOST') ?: '127.0.0.1';
$dbPort = env('DB_PORT') ?: env('PGPORT') ?: env('MYSQLPORT') ?: '';
$dbName = env('DB_DATABASE') ?: env('PGDATABASE') ?: env('MYSQLDATABASE') ?: 'attendance_db';
$dbUser = env('DB_USERNAME') ?: env('PGUSER') ?: env('MYSQLUSER') ?: 'root';
$dbPass = env('DB_PASSWORD') ?: env('PGPASSWORD') ?: env('MYSQLPASSWORD') ?: '';
$pgSslMode = env('PGSSLMODE') ?: '';
$pgOptions = '';

if (env('PGHOST') || env('POSTGRES_URL')) {
    $defaultDriver = 'pgsql';
    $hasExplicitCloudDb = true;
} elseif (env('MYSQLHOST') || env('MYSQL_URL')) {
    $defaultDriver = 'mysql';
    $hasExplicitCloudDb = true;
} elseif ($dbHost !== '127.0.0.1' && $dbHost !== 'localhost') {
    $hasExplicitCloudDb = true;
}

if ($dbUrl) {
    $trimmedUrl = trim((string) $dbUrl, " \t\n\r\0\x0B\"'");
    $parsed = parse_url($trimmedUrl);
    if ($parsed && !empty($parsed['host'])) {
        $scheme = strtolower($parsed['scheme'] ?? 'postgresql');
        $defaultDriver = ($scheme === 'postgres' || $scheme === 'postgresql' || $scheme === 'pgsql') ? 'pgsql' : 'mysql';
        $dbHost = $parsed['host'];
        $dbPort = (string) ($parsed['port'] ?? ($defaultDriver === 'pgsql' ? '5432' : '3306'));
        $dbName = ltrim($parsed['path'] ?? 'attendance_db', '/');
        $dbUser = isset($parsed['user']) ? rawurldecode($parsed['user']) : ($defaultDriver === 'pgsql' ? 'postgres' : 'root');
        $dbPass = isset($parsed['pass']) ? rawurldecode($parsed['pass']) : '';

        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $qParams);
            if (!empty($qParams['sslmode'])) {
                $pgSslMode = (string) $qParams['sslmode'];
            }
            if (!empty($qParams['options'])) {
                $pgOptions = (string) $qParams['options'];
            }
        }
    }
}

// Default sslmode=require for remote cloud PostgreSQL (Neon, Supabase, Render external)
if ($defaultDriver === 'pgsql' && empty($pgSslMode) && $dbHost !== '127.0.0.1' && $dbHost !== 'localhost') {
    // Internal Render hostnames (e.g. dpg-xxxx-a) don't always have domain dots; external ones do
    $pgSslMode = str_contains($dbHost, '.') ? 'require' : 'prefer';
}

// Neon.tech older libpq compatibility: pass endpoint=<first-label> if host is *.neon.tech and no options set
if ($defaultDriver === 'pgsql' && empty($pgOptions) && str_contains($dbHost, '.neon.tech') && !str_contains($dbHost, '-pooler.')) {
    $firstLabel = explode('.', $dbHost)[0] ?? '';
    if (str_starts_with($firstLabel, 'ep-')) {
        $pgOptions = 'endpoint=' . $firstLabel;
    }
}

if (!$defaultDriver) {
    $defaultDriver = 'sqlite';
}

// Determine best SQLite path (prefer /var/data persistent volume if mounted)
$sqlitePath = env('DB_SQLITE_PATH');
if (is_dir('/var/data') && is_writable('/var/data') && (!$sqlitePath || $sqlitePath === 'database/attendance.sqlite' || $sqlitePath === '/var/www/html/database/attendance.sqlite')) {
    $sqlitePath = '/var/data/attendance.sqlite';
} elseif (!$sqlitePath) {
    $sqlitePath = __DIR__ . '/../database/attendance.sqlite';
} elseif (!str_starts_with($sqlitePath, '/') && !preg_match('/^[A-Z]:[\\\\\/]/i', $sqlitePath)) {
    $sqlitePath = __DIR__ . '/../' . ltrim($sqlitePath, '/\\');
}

return [
    'default' => $defaultDriver,
    'has_explicit_cloud_db' => $hasExplicitCloudDb,

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => $dbHost,
            'port' => $dbPort !== '' ? (string) $dbPort : '3306',
            'database' => $dbName,
            'username' => $dbUser,
            'password' => $dbPass,
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 15,
            ]
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => $dbHost,
            'port' => $dbPort !== '' ? (string) $dbPort : '5432',
            'database' => $dbName,
            'username' => $dbUser,
            'password' => $dbPass,
            'sslmode' => $pgSslMode,
            'pg_options' => $pgOptions,
            'charset' => 'utf8',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 15,
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
