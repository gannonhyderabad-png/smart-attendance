<?php

/**
 * Global Application Configuration
 */

// Simple .env Loader
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            $val = trim($val, '"\'');
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $val;
                putenv("$key=$val");
            }
        }
    }
}

function env(string $key, mixed $default = null): mixed {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// Set Default Timezone
$tz = env('APP_TIMEZONE', 'Asia/Kolkata');
date_default_timezone_set($tz);

return [
    'name' => env('APP_NAME', 'Smart Attendance Server'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => rtrim(env('APP_URL', 'http://localhost:8000'), '/'),
    'timezone' => $tz,
    'session_lifetime' => (int) env('SESSION_LIFETIME', 120),
    'employee_code_prefix' => env('EMPLOYEE_CODE_PREFIX', 'EMP-'),
];
