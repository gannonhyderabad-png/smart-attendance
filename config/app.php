<?php

/**
 * Global Application Configuration
 */

// Simple .env Loader (never overwrites real OS / Render container environment variables)
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
            $existingOsVal = getenv($key);
            if ($existingOsVal === false && !array_key_exists($key, $_ENV) && !array_key_exists($key, $_SERVER)) {
                $_ENV[$key] = $val;
                putenv("$key=$val");
            }
        }
    }
}

function env(string $key, mixed $default = null): mixed {
    if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    if (array_key_exists($key, $_SERVER) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return $val;
    }
    return $default;
}

// Set Default Timezone
$tz = env('APP_TIMEZONE', 'Asia/Kolkata');
date_default_timezone_set($tz);

return [
    'name' => env('APP_NAME', 'Smart Attendance Server'),
    'env' => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim((string) env('APP_URL', 'http://localhost:8000'), '/'),
    'timezone' => $tz,
    'session_lifetime' => (int) env('SESSION_LIFETIME', 120),
    'employee_code_prefix' => env('EMPLOYEE_CODE_PREFIX', 'EMP-'),
];
