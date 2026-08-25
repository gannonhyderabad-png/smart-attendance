<?php

namespace Database;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;
    private static array $config = [];

    public static function init(array $config): void {
        self::$config = $config;
        self::$instance = null;
    }

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $default = self::$config['default'] ?? 'mysql';
            $connConfig = self::$config['connections'][$default] ?? [];

            try {
                if ($default === 'sqlite') {
                    $dbPath = $connConfig['database'] ?? (__DIR__ . '/attendance.sqlite');
                    $dir = dirname($dbPath);
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0777, true);
                    }
                    self::$instance = new PDO(
                        "sqlite:" . $dbPath,
                        null,
                        null,
                        $connConfig['options'] ?? [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        ]
                    );
                    self::$instance->exec("PRAGMA foreign_keys = ON;");
                } elseif ($default === 'pgsql') {
                    $dsn = sprintf(
                        "pgsql:host=%s;port=%s;dbname=%s",
                        $connConfig['host'] ?? '127.0.0.1',
                        $connConfig['port'] ?? '5432',
                        $connConfig['database'] ?? 'attendance_db'
                    );
                    self::$instance = new PDO(
                        $dsn,
                        $connConfig['username'] ?? 'postgres',
                        $connConfig['password'] ?? '',
                        $connConfig['options'] ?? []
                    );
                } else {
                    $dsn = sprintf(
                        "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                        $connConfig['host'] ?? '127.0.0.1',
                        $connConfig['port'] ?? '3306',
                        $connConfig['database'] ?? 'attendance_db',
                        $connConfig['charset'] ?? 'utf8mb4'
                    );
                    self::$instance = new PDO(
                        $dsn,
                        $connConfig['username'] ?? 'root',
                        $connConfig['password'] ?? '',
                        $connConfig['options'] ?? []
                    );
                }
            } catch (\Throwable $e) {
                // If MySQL database doesn't exist, try connecting without dbname to create it
                if ($default === 'mysql' && str_contains($e->getMessage(), 'Unknown database')) {
                    try {
                        $dsnNoDb = sprintf(
                            "mysql:host=%s;port=%s;charset=%s",
                            $connConfig['host'] ?? '127.0.0.1',
                            $connConfig['port'] ?? '3306',
                            $connConfig['charset'] ?? 'utf8mb4'
                        );
                        $tempPdo = new PDO($dsnNoDb, $connConfig['username'] ?? 'root', $connConfig['password'] ?? '');
                        $dbName = $connConfig['database'] ?? 'attendance_db';
                        $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                        
                        self::$instance = new PDO(
                            $dsn,
                            $connConfig['username'] ?? 'root',
                            $connConfig['password'] ?? '',
                            $connConfig['options'] ?? []
                        );
                        return self::$instance;
                    } catch (\Throwable $e2) {
                        self::$config['default'] = 'sqlite';
                        self::$instance = null;
                        return self::getConnection();
                    }
                }

                // If MySQL is completely down or connection is refused, auto-fallback to SQLite
                if ($default !== 'sqlite') {
                    self::$config['default'] = 'sqlite';
                    self::$instance = null;
                    return self::getConnection();
                }

                throw new PDOException("Database connection error: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Run schema migrations / initial tables setup
     */
    public static function autoMigrate(): void {
        static $hasMigratedInProcess = false;
        if ($hasMigratedInProcess) {
            return;
        }

        $pdo = self::getConnection();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            self::migrateSqlite($pdo);
        } else {
            self::migrateMysql($pdo);
        }

        $hasMigratedInProcess = true;
    }

    private static function migrateMysql(PDO $pdo): void {
        $schemaFile = __DIR__ . '/schema.sql';
        $seedFile = __DIR__ . '/seed.sql';

        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $stmt) {
                if (!empty($stmt)) {
                    try {
                        $pdo->exec($stmt);
                    } catch (\Throwable $e) {}
                }
            }
        }

        // Add project & site columns if missing
        try {
            $pdo->exec("ALTER TABLE `employees` ADD COLUMN `project` TEXT NULL AFTER `designation`;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `employees` ADD COLUMN `site` TEXT NULL AFTER `project`;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `employees` ADD COLUMN `site_latitude` DECIMAL(10,8) NULL AFTER `site`;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `employees` ADD COLUMN `site_longitude` DECIMAL(11,8) NULL AFTER `site_latitude`;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `employees` ADD COLUMN `site_radius` INT DEFAULT 200 AFTER `site_longitude`;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `employees` ADD COLUMN `geofence_enabled` TINYINT(1) DEFAULT 1 AFTER `site_radius`;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `attendance` ADD COLUMN `project` TEXT NULL AFTER `punch_date`;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `attendance` ADD COLUMN `site` TEXT NULL AFTER `project`;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `attendance` ADD COLUMN `distance_meters` DECIMAL(10,2) NULL AFTER `longitude`;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `attendance` ADD COLUMN `location_verified` TINYINT(1) DEFAULT 0 AFTER `distance_meters`;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `employees` ADD COLUMN `photo` VARCHAR(255) NULL AFTER `punch_token`;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `employees` ADD COLUMN `department` VARCHAR(100) NULL AFTER `department_id`;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `attendance` ADD COLUMN `punch_photo` VARCHAR(255) NULL AFTER `location_verified`;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `employees` ADD COLUMN `deleted_at` DATETIME NULL;");
        } catch (\Throwable $e) {}

        // Check if employees table is already populated; ONLY seed if completely empty
        $empCount = 0;
        try {
            $empCount = (int) $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
        } catch (\Throwable $e) {}

        if ($empCount === 0 && file_exists($seedFile)) {
            $seedSql = file_get_contents($seedFile);
            $statements = array_filter(array_map('trim', explode(';', $seedSql)));
            foreach ($statements as $stmt) {
                if (!empty($stmt)) {
                    try {
                        $pdo->exec($stmt);
                    } catch (\Throwable $e) {}
                }
            }
        }

        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `holidays` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(150) NOT NULL,
                `holiday_date` DATE NOT NULL UNIQUE,
                `description` VARCHAR(255) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_holiday_date` (`holiday_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `leaves` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` INT NOT NULL,
                `leave_type` VARCHAR(50) NOT NULL,
                `start_date` DATE NOT NULL,
                `end_date` DATE NOT NULL,
                `days_count` DECIMAL(4, 1) DEFAULT 1.0,
                `reason` TEXT NULL,
                `status` VARCHAR(20) DEFAULT 'APPROVED',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT `fk_leave_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
                INDEX `idx_leave_dates` (`start_date`, `end_date`),
                INDEX `idx_leave_emp` (`employee_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        } catch (\Throwable $e) {}

        // Seed standard public holidays if table is empty
        try {
            $holCount = (int) $pdo->query("SELECT COUNT(*) FROM holidays")->fetchColumn();
            if ($holCount === 0) {
                $defaultHolidays = [
                    ['New Year\'s Day', '2026-01-01', 'Global Celebration'],
                    ['Republic Day', '2026-01-26', 'National Holiday'],
                    ['Maha Shivaratri', '2026-02-16', 'Religious Festival'],
                    ['Holi', '2026-03-04', 'Festival of Colours'],
                    ['Eid al-Fitr (Ramzan Eid)', '2026-03-21', 'Public Holiday'],
                    ['Good Friday', '2026-04-03', 'Public Holiday'],
                    ['Dr. Ambedkar Jayanti', '2026-04-14', 'National Observance'],
                    ['May Day (Labor Day)', '2026-05-01', 'International Workers Day'],
                    ['Independence Day', '2026-08-15', 'National Holiday'],
                    ['Raksha Bandhan', '2026-08-28', 'Festival'],
                    ['Janmashtami', '2026-09-04', 'Religious Festival'],
                    ['Gandhi Jayanti', '2026-10-02', 'National Holiday'],
                    ['Dussehra (Vijayadashami)', '2026-10-20', 'Festival Holiday'],
                    ['Diwali (Deepavali)', '2026-11-08', 'Festival of Lights'],
                    ['Guru Nanak Jayanti', '2026-11-24', 'Religious Holiday'],
                    ['Christmas', '2026-12-25', 'Public Holiday'],
                ];
                $hStmt = $pdo->prepare("INSERT IGNORE INTO holidays (title, holiday_date, description) VALUES (?, ?, ?)");
                foreach ($defaultHolidays as $h) {
                    $hStmt->execute($h);
                }
            }
        } catch (\Throwable $e) {}

        // Ensure default admin has valid password hash
        $adminStmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ? LIMIT 1");
        $adminStmt->execute(['admin@attendance.com']);
        $adminUser = $adminStmt->fetch();
        if (!$adminUser) {
            $newHash = password_hash('admin123', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, 'admin', 'active')");
            $stmt->execute(['System Administrator', 'admin@attendance.com', $newHash]);
        } elseif (!password_verify('admin123', $adminUser['password_hash'])) {
            $newHash = password_hash('admin123', PASSWORD_BCRYPT);
            $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $updateStmt->execute([$newHash, $adminUser['id']]);
        }
    }

    private static function migrateSqlite(PDO $pdo): void {
        // SQLite compatible table creation
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
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS departments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            code TEXT,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_code TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            email TEXT,
            phone TEXT,
            department_id INTEGER,
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
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS attendance (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_id INTEGER NOT NULL,
            punch_type TEXT NOT NULL,
            punch_time DATETIME NOT NULL,
            punch_date DATE NOT NULL,
            project TEXT,
            site TEXT,
            ip_address TEXT,
            user_agent TEXT,
            device_info TEXT,
            latitude REAL,
            longitude REAL,
            distance_meters REAL,
            location_verified INTEGER DEFAULT 0,
            punch_photo TEXT,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        )");

        // Migrate columns if already created without them
        try {
            $pdo->exec("ALTER TABLE employees ADD COLUMN project TEXT;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE employees ADD COLUMN site TEXT;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE employees ADD COLUMN site_latitude REAL;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE employees ADD COLUMN site_longitude REAL;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE employees ADD COLUMN site_radius INTEGER DEFAULT 200;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE employees ADD COLUMN geofence_enabled INTEGER DEFAULT 1;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE employees ADD COLUMN photo TEXT;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE employees ADD COLUMN department TEXT;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE employees ADD COLUMN deleted_at DATETIME NULL;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE attendance ADD COLUMN project TEXT;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE attendance ADD COLUMN site TEXT;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE attendance ADD COLUMN distance_meters REAL;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE attendance ADD COLUMN location_verified INTEGER DEFAULT 0;");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE attendance ADD COLUMN punch_photo TEXT;");
        } catch (\Throwable $e) {}

        $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action TEXT NOT NULL,
            description TEXT,
            ip_address TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            setting_key TEXT PRIMARY KEY,
            setting_value TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS holidays (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            holiday_date DATE NOT NULL UNIQUE,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS leaves (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_id INTEGER NOT NULL,
            leave_type TEXT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            days_count REAL DEFAULT 1.0,
            reason TEXT,
            status TEXT DEFAULT 'APPROVED',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        )");

        // Seed standard public holidays in SQLite if empty
        $holCount = (int) $pdo->query("SELECT COUNT(*) FROM holidays")->fetchColumn();
        if ($holCount === 0) {
            $defaultHolidays = [
                ['New Year\'s Day', '2026-01-01', 'Global Celebration'],
                ['Republic Day', '2026-01-26', 'National Holiday'],
                ['Maha Shivaratri', '2026-02-16', 'Religious Festival'],
                ['Holi', '2026-03-04', 'Festival of Colours'],
                ['Eid al-Fitr (Ramzan Eid)', '2026-03-21', 'Public Holiday'],
                ['Good Friday', '2026-04-03', 'Public Holiday'],
                ['Dr. Ambedkar Jayanti', '2026-04-14', 'National Observance'],
                ['May Day (Labor Day)', '2026-05-01', 'International Workers Day'],
                ['Independence Day', '2026-08-15', 'National Holiday'],
                ['Raksha Bandhan', '2026-08-28', 'Festival'],
                ['Janmashtami', '2026-09-04', 'Religious Festival'],
                ['Gandhi Jayanti', '2026-10-02', 'National Holiday'],
                ['Dussehra (Vijayadashami)', '2026-10-20', 'Festival Holiday'],
                ['Diwali (Deepavali)', '2026-11-08', 'Festival of Lights'],
                ['Guru Nanak Jayanti', '2026-11-24', 'Religious Holiday'],
                ['Christmas', '2026-12-25', 'Public Holiday'],
            ];
            $hStmt = $pdo->prepare("INSERT OR IGNORE INTO holidays (title, holiday_date, description) VALUES (?, ?, ?)");
            foreach ($defaultHolidays as $h) {
                $hStmt->execute($h);
            }
        }

        // Insert default user if empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        if ($stmt->fetchColumn() == 0) {
            $passHash = password_hash('admin123', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, 'admin', 'active')");
            $stmt->execute(['System Administrator', 'admin@attendance.com', $passHash]);

            // Default departments
            $pdo->exec("INSERT INTO departments (name, code, description) VALUES 
                ('Engineering & Tech', 'ENG', 'Software Development & IT Support'),
                ('Human Resources', 'HR', 'People Operations & Talent Management'),
                ('Sales & Marketing', 'MKT', 'Direct Sales, Growth & Client Success'),
                ('Operations', 'OPS', 'Logistics, Support & Administration')");

            // Default settings
            $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES 
                ('company_name', 'TechCorp Solutions Inc.'),
                ('company_email', 'hr@techcorp.com'),
                ('company_phone', '+1 (800) 555-0199'),
                ('work_hours_per_day', '8'),
                ('grace_period_minutes', '15'),
                ('allow_geo_capture', '1'),
                ('auto_calculate_hours', '1'),
                ('site_logo_text', 'SmartAttendance')");
        } else {
            $adminStmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ? LIMIT 1");
            $adminStmt->execute(['admin@attendance.com']);
            $adminUser = $adminStmt->fetch();
            if (!$adminUser) {
                $newHash = password_hash('admin123', PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, 'admin', 'active')");
                $stmt->execute(['System Administrator', 'admin@attendance.com', $newHash]);
            } elseif (!password_verify('admin123', $adminUser['password_hash'])) {
                $newHash = password_hash('admin123', PASSWORD_BCRYPT);
                $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $updateStmt->execute([$newHash, $adminUser['id']]);
            }
        }

        // No sample employees seeded so database is completely clean for user
    }
}
