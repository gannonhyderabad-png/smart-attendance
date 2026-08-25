<?php

/**
 * Attendance Management Server - Front Controller
 * PHP 8+ MVC Architecture
 */

declare(strict_types=1);

// PHP Built-in Server router support for static files with accurate MIME types
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $path;
    if ($path !== '/' && file_exists($file) && !is_dir($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mimes = [
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'json' => 'application/json; charset=utf-8',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf'
        ];
        if (isset($mimes[$ext])) {
            header('Content-Type: ' . $mimes[$ext]);
            header('Cache-Control: public, max-age=604800, immutable');
            header('Vary: Accept-Encoding');
            readfile($file);
            exit;
        }
        return false;
    }
}

// Output buffering
if (!ob_get_level() && !headers_sent()) {
    ob_start();
}

// Error reporting configuration
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Autoloading (Resilient PSR-4 style for Linux case-insensitivity)
spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');

    if ($class === 'Database\\Database' || strcasecmp($class, 'Database\\Database') === 0) {
        $dbFile = __DIR__ . '/database/Database.php';
        if (file_exists($dbFile)) {
            require_once $dbFile;
            return;
        }
    }

    $prefixMap = [
        'App\\' => __DIR__ . '/app/',
        'Database\\' => __DIR__ . '/database/'
    ];

    foreach ($prefixMap as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncasecmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $relPath = str_replace('\\', '/', $relativeClass);

            $candidates = [
                $baseDir . $relPath . '.php',
                $baseDir . strtolower($relPath) . '.php',
                $baseDir . ucfirst($relPath) . '.php',
                __DIR__ . '/' . $relPath . '.php',
                __DIR__ . '/' . basename($relPath) . '.php',
                __DIR__ . '/database/' . basename($relPath) . '.php',
                __DIR__ . '/database/' . strtolower(basename($relPath)) . '.php',
            ];

            $parts = explode('/', $relPath);
            if (count($parts) === 2) {
                $candidates[] = $baseDir . strtolower($parts[0]) . '/' . $parts[1] . '.php';
                $candidates[] = $baseDir . ucfirst(strtolower($parts[0])) . '/' . $parts[1] . '.php';
                $candidates[] = $baseDir . strtolower($parts[0]) . '/' . strtolower($parts[1]) . '.php';
                $candidates[] = __DIR__ . '/' . $parts[1] . '.php';
            }

            foreach ($candidates as $file) {
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        }
    }
});

// Explicitly require core database class on startup
if (file_exists(__DIR__ . '/database/Database.php')) {
    require_once __DIR__ . '/database/Database.php';
}

// Load Global Helpers & Configurations
$helperCandidates = [
    __DIR__ . '/app/Helpers/utils.php',
    __DIR__ . '/app/helpers/utils.php',
    __DIR__ . '/Helpers/utils.php',
    __DIR__ . '/helpers/utils.php',
    __DIR__ . '/app/Helpers/Utils.php',
];
foreach ($helperCandidates as $hPath) {
    if (file_exists($hPath)) {
        require_once $hPath;
        break;
    }
}
$appConfig = file_exists(__DIR__ . '/config/app.php') ? require __DIR__ . '/config/app.php' : [
    'name' => 'Smart Attendance Server',
    'env' => 'production',
    'debug' => false,
    'url' => '',
    'timezone' => 'Asia/Kolkata'
];

$dbConfig = file_exists(__DIR__ . '/config/database.php') ? require __DIR__ . '/config/database.php' : [
    'default' => 'sqlite',
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/database/attendance.sqlite',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        ]
    ]
];

// Display errors if debug mode enabled
if ($appConfig['debug'] ?? false) {
    ini_set('display_errors', '1');
}

// Initialize Database & Auto-migration
try {
    \Database\Database::init($dbConfig);
    \Database\Database::autoMigrate();
} catch (\Throwable $e) {
    try {
        $dbConfig['default'] = 'sqlite';
        $dbConfig['connections']['sqlite']['database'] = __DIR__ . '/database/attendance.sqlite';
        \Database\Database::init($dbConfig);
        \Database\Database::autoMigrate();
    } catch (\Throwable $sqliteErr) {
        error_log("Database Init Warning: " . $sqliteErr->getMessage());
    }
}

// 100% Inline Table & Admin Guarantee for SQLite (Never relies on external files)
try {
    $pdo = \Database\Database::getConnection();
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT DEFAULT 'admin',
            status TEXT DEFAULT 'active',
            last_login DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS departments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            code TEXT,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_code TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            email TEXT,
            phone TEXT,
            department_id INTEGER,
            department TEXT,
            designation TEXT,
            project TEXT,
            site TEXT,
            site_latitude REAL,
            site_longitude REAL,
            site_radius INTEGER DEFAULT 200,
            geofence_enabled INTEGER DEFAULT 1,
            punch_token TEXT NOT NULL UNIQUE,
            photo TEXT,
            shift_start TIME DEFAULT '09:00:00',
            shift_end TIME DEFAULT '18:00:00',
            status TEXT DEFAULT 'active',
            deleted_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS attendance (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_id INTEGER NOT NULL,
            punch_type TEXT NOT NULL,
            punch_date DATE NOT NULL,
            punch_time TIME NOT NULL,
            latitude REAL,
            longitude REAL,
            distance_meters INTEGER,
            location_verified INTEGER DEFAULT 0,
            punch_photo TEXT,
            project TEXT,
            site TEXT,
            ip_address TEXT,
            device_info TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action TEXT NOT NULL,
            description TEXT,
            ip_address TEXT,
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key TEXT NOT NULL UNIQUE,
            setting_value TEXT,
            description TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        try {
            $pdo->exec("ALTER TABLE employees ADD COLUMN deleted_at DATETIME NULL;");
        } catch (\Throwable $e) {}

        // Ensure default admin exists
        $adminStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $adminStmt->execute(['admin@attendance.local']);
        if (!$adminStmt->fetch()) {
            $adminHash = password_hash('admin123', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, 'admin', 'active')");
            $stmt->execute(['System Administrator', 'admin@attendance.local', $adminHash]);
        }
    }
} catch (\Throwable $guaranteeErr) {
    error_log("Schema guarantee note: " . $guaranteeErr->getMessage());
}

// Start Session
\App\Core\Auth::init();

// Initialize Router
$router = new \App\Core\Router();

// -------------------------------------------------------------
// Public Routes
// -------------------------------------------------------------
$router->get('/', function() {
    if (\App\Core\Auth::check()) {
        redirect('dashboard');
    } else {
        redirect('login');
    }
});

$router->get('/login', [\App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login']);
$router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);

// Public Mobile Employee Punch URL (/p/{code})
$router->get('/p/{code}', [\App\Controllers\PunchController::class, 'show']);
$router->post('/p/{code}', [\App\Controllers\PunchController::class, 'record']);
$router->get('/p/{code}/status', [\App\Controllers\PunchController::class, 'status']);

// REST API Endpoints
$router->post('/api/punch', [\App\Controllers\ApiController::class, 'punch']);
$router->get('/api/employee/{code}', [\App\Controllers\ApiController::class, 'employeeStatus']);
$router->get('/api/attendance/summary', [\App\Controllers\ApiController::class, 'summary']);
$router->get('/api/sync', [\App\Controllers\ApiController::class, 'sync']);

// -------------------------------------------------------------
// Protected Admin Routes
// -------------------------------------------------------------
$router->get('/dashboard', [\App\Controllers\DashboardController::class, 'index']);

// Employees
$router->get('/employees', [\App\Controllers\EmployeeController::class, 'index']);
$router->get('/employees/create', [\App\Controllers\EmployeeController::class, 'create']);
$router->post('/employees/store', [\App\Controllers\EmployeeController::class, 'store']);
$router->get('/employees/view/{id}', [\App\Controllers\EmployeeController::class, 'viewDetails']);
$router->get('/employees/edit/{id}', [\App\Controllers\EmployeeController::class, 'edit']);
$router->post('/employees/update/{id}', [\App\Controllers\EmployeeController::class, 'update']);
$router->get('/employees/delete/{id}', [\App\Controllers\EmployeeController::class, 'delete']);
$router->get('/employees/restore/{id}', [\App\Controllers\EmployeeController::class, 'restore']);
$router->get('/employees/force-delete/{id}', [\App\Controllers\EmployeeController::class, 'forceDelete']);
$router->get('/employees/empty-trash', [\App\Controllers\EmployeeController::class, 'emptyTrash']);

// Attendance Logs
$router->get('/attendance', [\App\Controllers\AttendanceController::class, 'index']);
$router->get('/attendance/export', [\App\Controllers\AttendanceController::class, 'exportCsv']);

// Reports
$router->get('/reports/daily', [\App\Controllers\ReportController::class, 'daily']);
$router->get('/reports/daily/export', [\App\Controllers\ReportController::class, 'exportDailyCsv']);
$router->get('/reports/monthly', [\App\Controllers\ReportController::class, 'monthly']);
$router->get('/reports/monthly/export', [\App\Controllers\ReportController::class, 'exportMonthlyCsv']);
$router->get('/reports/monthly/audit-export', [\App\Controllers\ReportController::class, 'exportMonthlyAuditCsv']);

// System Logs, Backups & Admin Settings
$router->get('/logs', [\App\Controllers\LogController::class, 'index']);
$router->get('/profile', [\App\Controllers\AuthController::class, 'profile']);
$router->post('/profile/update', [\App\Controllers\AuthController::class, 'updateProfile']);
$router->get('/backup/download', [\App\Controllers\BackupController::class, 'download']);
$router->post('/backup/restore', [\App\Controllers\BackupController::class, 'restore']);
$router->post('/backup/clean-photos', [\App\Controllers\BackupController::class, 'cleanOldPhotos']);

// Dispatch Current Request
try {
    $router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');
} catch (\Throwable $e) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    echo '<div style="font-family:sans-serif;padding:30px;max-width:800px;margin:40px auto;border:1px solid #f87171;background:#fef2f2;border-radius:12px;">';
    echo '<h3 style="color:#b91c1c;margin-top:0;">Application Notice</h3>';
    echo '<p style="color:#374151;">' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre style="background:#fff;padding:15px;border-radius:8px;overflow-x:auto;font-size:12px;color:#4b5563;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</div>';
}
