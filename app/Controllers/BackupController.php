<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use Database\Database;
use ZipArchive;
use PDO;

class BackupController extends Controller {
    /**
     * Generate and download a complete ZIP backup of the database, readable CSV reports, and all upload photos
     */
    public function download(): void {
        Auth::requireAuth();
        if (Auth::isRestricted()) {
            $_SESSION['flash_error'] = 'Access restricted: Your login is limited to Employee Directory and Leave Approvals.';
            redirect('leaves');
        }

        $zipFileName = 'smart_attendance_backup_' . date('Y-m-d_H-i-s') . '.zip';
        $tempZipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            die("Could not create backup zip file.");
        }

        $pdo = null;
        try {
            $pdo = Database::getConnection();
        } catch (\Throwable $e) {}

        $punchesCount = 0;
        $leavesCount = 0;
        $empCount = 0;
        $tempSqlitePath = null;

        if ($pdo) {
            // 1. Generate & Add Employee Attendance Punches Log (CSV)
            $punchesCsv = $this->generatePunchesCsv($pdo, $punchesCount);
            $zip->addFromString('reports/employee_punches_log.csv', $punchesCsv);

            // 2. Generate & Add Leave & OD Attendance Log (CSV)
            $leavesCsv = $this->generateLeavesCsv($pdo, $leavesCount);
            $zip->addFromString('reports/leave_od_attendance_log.csv', $leavesCsv);

            // 3. Generate & Add All Employees Directory (CSV)
            $empCsv = $this->generateEmployeesCsv($pdo, $empCount);
            $zip->addFromString('reports/all_employees_directory.csv', $empCsv);

            // 4. Create a fresh, live SQLite snapshot directly from live PDO
            $tempSqlitePath = $this->createLiveSqliteSnapshot($pdo);
            if ($tempSqlitePath && file_exists($tempSqlitePath) && filesize($tempSqlitePath) > 0) {
                $zip->addFile($tempSqlitePath, 'database/attendance.sqlite');
            } else {
                // Fallback to SQLite database file on disk if live snapshot couldn't be built
                $sqliteCandidates = [
                    '/var/data/attendance.sqlite',
                    __DIR__ . '/../../database/attendance.sqlite'
                ];
                foreach ($sqliteCandidates as $sqlitePath) {
                    if (file_exists($sqlitePath) && filesize($sqlitePath) > 0) {
                        $zip->addFile($sqlitePath, 'database/attendance.sqlite');
                        break;
                    }
                }
            }

            // 5. Generate ANSI SQL Data Dump
            try {
                $sqlDump = "-- Smart Attendance System Complete SQL Backup\n";
                $sqlDump .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";

                $tables = ['users', 'departments', 'employees', 'attendance', 'holidays', 'leaves', 'devices', 'device_logs', 'activity_logs', 'settings'];
                foreach ($tables as $table) {
                    try {
                        $stmt = $pdo->query("SELECT * FROM \"{$table}\"");
                    } catch (\Throwable $quoteErr) {
                        try {
                            $stmt = $pdo->query("SELECT * FROM {$table}");
                        } catch (\Throwable $noQuoteErr) {
                            $stmt = null;
                        }
                    }

                    if ($stmt) {
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if (!empty($rows)) {
                            $sqlDump .= "-- Table: {$table} (" . count($rows) . " rows)\n";
                            foreach ($rows as $row) {
                                $keys = array_keys($row);
                                $values = array_map(function($val) use ($pdo) {
                                    return $val === null ? 'NULL' : $pdo->quote((string)$val);
                                }, array_values($row));
                                $sqlDump .= "INSERT INTO {$table} (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
                            }
                            $sqlDump .= "\n";
                        }
                    }
                }
                $zip->addFromString('database/database_dump.sql', $sqlDump);
            } catch (\Throwable $e) {}
        }

        // 6. Backup Avatars, Punch Selfies & Leave Attachments (from disk + stored_files DB table)
        $addedUploadPaths = [];
        $uploadsDir = __DIR__ . '/../../public/uploads';
        if (is_dir($uploadsDir)) {
            $this->addFolderToZip($zip, $uploadsDir, 'public/uploads', $addedUploadPaths);
        }
        if ($pdo) {
            try {
                $sfStmt = $pdo->query("SELECT file_path, file_data FROM stored_files");
                if ($sfStmt) {
                    while ($sfRow = $sfStmt->fetch(PDO::FETCH_ASSOC)) {
                        $fp = ltrim(str_replace('\\', '/', $sfRow['file_path'] ?? ''), '/');
                        if ($fp !== '' && empty($addedUploadPaths[$fp]) && !empty($sfRow['file_data'])) {
                            $raw = base64_decode($sfRow['file_data'], true);
                            if ($raw !== false) {
                                $zip->addFromString($fp, $raw);
                                $addedUploadPaths[$fp] = true;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 7. Add Backup Metadata
        $meta = [
            'backup_date' => date('Y-m-d H:i:s'),
            'system' => 'Smart Attendance System',
            'version' => '2.2',
            'db_driver' => Database::getDriverName(),
            'created_by' => Auth::user()['name'] ?? 'Admin',
            'records_exported' => [
                'employee_punches' => $punchesCount,
                'leaves_and_od' => $leavesCount,
                'employees' => $empCount,
            ],
            'files_included' => [
                'reports/employee_punches_log.csv',
                'reports/leave_od_attendance_log.csv',
                'reports/all_employees_directory.csv',
                'database/attendance.sqlite',
                'database/database_dump.sql',
                'public/uploads/punches/',
                'public/uploads/avatars/'
            ]
        ];
        $zip->addFromString('backup_info.json', json_encode($meta, JSON_PRETTY_PRINT));

        $zip->close();

        // Clean up temporary live SQLite file if created
        if (!empty($tempSqlitePath) && file_exists($tempSqlitePath)) {
            @unlink($tempSqlitePath);
        }

        // Send ZIP download to browser
        if (file_exists($tempZipPath)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
            header('Content-Length: ' . filesize($tempZipPath));
            header('Pragma: no-cache');
            header('Expires: 0');
            readfile($tempZipPath);
            @unlink($tempZipPath);
            exit;
        } else {
            die("Backup file generation failed.");
        }
    }

    /**
     * Download Employee Attendance Punches as standalone CSV
     */
    public function exportPunchesCsv(): void {
        Auth::requireAuth();
        if (Auth::isRestricted() && !Auth::isSiteUser()) {
            $_SESSION['flash_error'] = 'Access restricted: Your login is limited.';
            redirect('leaves');
        }
        $pdo = Database::getConnection();
        $count = 0;
        $csv = $this->generatePunchesCsv($pdo, $count);
        $fileName = 'employee_punches_log_' . date('Y-m-d_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $csv;
        exit;
    }

    /**
     * Download Leave & OD Attendance Log as standalone CSV
     */
    public function exportLeavesCsv(): void {
        Auth::requireAuth();
        if (Auth::isRestricted() && !Auth::isSiteUser()) {
            $_SESSION['flash_error'] = 'Access restricted: Your login is limited.';
            redirect('leaves');
        }
        $pdo = Database::getConnection();
        $count = 0;
        $csv = $this->generateLeavesCsv($pdo, $count);
        $fileName = 'leave_od_attendance_log_' . date('Y-m-d_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $csv;
        exit;
    }

    /**
     * Generate complete CSV string for all Employee Attendance Punches
     */
    private function generatePunchesCsv(PDO $pdo, int &$rowCount = 0): string {
        $out = fopen('php://temp', 'r+');
        // UTF-8 BOM for Microsoft Excel compatibility
        fputs($out, "\xEF\xBB\xBF");
        
        $headers = [
            'Punch ID',
            'Date',
            'Time',
            'Punch Type',
            'Employee Code',
            'Employee Name',
            'Department',
            'Designation',
            'Project',
            'Site',
            'Location Verified',
            'Distance (Meters)',
            'Latitude',
            'Longitude',
            'Device Info',
            'IP Address',
            'Selfie Photo',
            'Notes',
            'Recorded At'
        ];
        fputcsv($out, $headers);

        try {
            $sql = "SELECT a.*, e.name as employee_name, e.employee_code, e.designation, e.project as employee_project, e.site as employee_site, d.name as department_name 
                    FROM attendance a 
                    JOIN employees e ON a.employee_id = e.id 
                    LEFT JOIN departments d ON e.department_id = d.id 
                    ORDER BY a.punch_time DESC, a.id DESC";
            $stmt = $pdo->query($sql);
            if ($stmt) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $rowCount++;
                    $punchTime = !empty($row['punch_time']) ? $row['punch_time'] : '';
                    $timeOnly = $punchTime ? date('h:i:s A', strtotime($punchTime)) : '';
                    $dateOnly = !empty($row['punch_date']) ? $row['punch_date'] : ($punchTime ? date('Y-m-d', strtotime($punchTime)) : '');
                    $locVerified = !empty($row['location_verified']) ? 'YES' : 'NO';
                    
                    fputcsv($out, [
                        $row['id'] ?? '',
                        $dateOnly,
                        $timeOnly,
                        strtoupper($row['punch_type'] ?? 'IN'),
                        $row['employee_code'] ?? '',
                        $row['employee_name'] ?? '',
                        $row['department_name'] ?? 'General',
                        $row['designation'] ?? '',
                        $row['project'] ?: ($row['employee_project'] ?? ''),
                        $row['site'] ?: ($row['employee_site'] ?? ''),
                        $locVerified,
                        $row['distance_meters'] ?? '0',
                        $row['latitude'] ?? '',
                        $row['longitude'] ?? '',
                        $row['device_info'] ?? '',
                        $row['ip_address'] ?? '',
                        $row['punch_photo'] ?? '',
                        $row['notes'] ?? '',
                        $row['created_at'] ?? $punchTime
                    ]);
                }
            }
        } catch (\Throwable $e) {}

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        return $csv ?: '';
    }

    /**
     * Generate complete CSV string for all Leaves and On-Duty (OD) records
     */
    private function generateLeavesCsv(PDO $pdo, int &$rowCount = 0): string {
        $out = fopen('php://temp', 'r+');
        // UTF-8 BOM for Microsoft Excel compatibility
        fputs($out, "\xEF\xBB\xBF");
        
        $headers = [
            'Leave ID',
            'Employee Code',
            'Employee Name',
            'Department',
            'Designation',
            'Leave / OD Type',
            'Start Date',
            'End Date',
            'Days Count',
            'Origin Site',
            'Target Site / Project',
            'Reason / Purpose',
            'Status',
            'Approved By',
            'Attachment',
            'Applied Date'
        ];
        fputcsv($out, $headers);

        try {
            $sql = "SELECT l.*, e.name as employee_name, e.employee_code, e.designation as employee_designation, e.project as employee_project, e.site as employee_site, 
                           d.name as department_name, u.name as approver_name 
                    FROM leaves l 
                    JOIN employees e ON l.employee_id = e.id 
                    LEFT JOIN departments d ON e.department_id = d.id 
                    LEFT JOIN users u ON l.approved_by = u.id 
                    ORDER BY l.start_date DESC, l.id DESC";
            $stmt = $pdo->query($sql);
            if ($stmt) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $rowCount++;
                    $type = trim((string)($row['leave_type'] ?? ''));
                    $typeLabel = $type;
                    if (strtoupper($type) === 'OD' || stripos($type, 'on-duty') !== false || stripos($type, 'on duty') !== false) {
                        $typeLabel = 'On-Duty (OD)';
                    } elseif (strtoupper($type) === 'CL' || stripos($type, 'casual') !== false) {
                        $typeLabel = 'Casual Leave (CL)';
                    } elseif (strtoupper($type) === 'SL' || stripos($type, 'sick') !== false) {
                        $typeLabel = 'Sick Leave (SL)';
                    } elseif (strtoupper($type) === 'PL' || stripos($type, 'paid') !== false || stripos($type, 'earned') !== false) {
                        $typeLabel = 'Paid Leave (PL)';
                    }

                    fputcsv($out, [
                        $row['id'] ?? '',
                        $row['employee_code'] ?? '',
                        $row['employee_name'] ?? '',
                        $row['department_name'] ?? 'General',
                        $row['employee_designation'] ?? '',
                        $typeLabel,
                        $row['start_date'] ?? '',
                        $row['end_date'] ?? '',
                        $row['days_count'] ?? '1.0',
                        $row['origin_site'] ?: ($row['site'] ?? ''),
                        $row['target_site'] ?: ($row['project'] ?? ''),
                        $row['reason'] ?? '',
                        strtoupper($row['status'] ?? 'APPROVED'),
                        $row['approver_name'] ?? 'Admin',
                        $row['attachment'] ?? '',
                        $row['created_at'] ?? ($row['start_date'] ?? '')
                    ]);
                }
            }
        } catch (\Throwable $e) {}

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        return $csv ?: '';
    }

    /**
     * Generate complete CSV string for all Employees Directory
     */
    private function generateEmployeesCsv(PDO $pdo, int &$rowCount = 0): string {
        $out = fopen('php://temp', 'r+');
        fputs($out, "\xEF\xBB\xBF");
        
        $headers = [
            'Employee ID',
            'Employee Code',
            'Name',
            'Email',
            'Phone',
            'Department',
            'Designation',
            'Project',
            'Site',
            'Shift Start',
            'Shift End',
            'Status',
            'Created At'
        ];
        fputcsv($out, $headers);

        try {
            $sql = "SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id ORDER BY e.id ASC";
            $stmt = $pdo->query($sql);
            if ($stmt) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $rowCount++;
                    fputcsv($out, [
                        $row['id'] ?? '',
                        $row['employee_code'] ?? '',
                        $row['name'] ?? '',
                        $row['email'] ?? '',
                        $row['phone'] ?? '',
                        $row['department_name'] ?? 'General',
                        $row['designation'] ?? '',
                        $row['project'] ?? '',
                        $row['site'] ?? '',
                        $row['shift_start'] ?? '09:00:00',
                        $row['shift_end'] ?? '18:00:00',
                        strtoupper($row['status'] ?? 'ACTIVE'),
                        $row['created_at'] ?? ''
                    ]);
                }
            }
        } catch (\Throwable $e) {}

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        return $csv ?: '';
    }

    /**
     * Dynamically create a complete, fresh SQLite database from the live PDO connection
     */
    private function createLiveSqliteSnapshot(PDO $srcPdo): ?string {
        try {
            $tmpSqlite = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'live_attendance_' . uniqid() . '.sqlite';
            if (file_exists($tmpSqlite)) @unlink($tmpSqlite);

            $sqlite = new PDO('sqlite:' . $tmpSqlite, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            $sqlite->exec("PRAGMA foreign_keys = OFF;");
            
            $tableSchemas = [
                'users' => "CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT NOT NULL UNIQUE, password_hash TEXT NOT NULL, role TEXT DEFAULT 'admin', status TEXT DEFAULT 'active', last_login DATETIME, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);",
                'departments' => "CREATE TABLE departments (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE, code TEXT, description TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);",
                'employees' => "CREATE TABLE employees (id INTEGER PRIMARY KEY AUTOINCREMENT, employee_code TEXT NOT NULL UNIQUE, name TEXT NOT NULL, email TEXT, phone TEXT, department_id INTEGER, designation TEXT, project TEXT, site TEXT, site_latitude NUMERIC, site_longitude NUMERIC, site_radius INTEGER DEFAULT 200, geofence_enabled INTEGER DEFAULT 1, punch_token TEXT NOT NULL UNIQUE, photo TEXT, shift_start TIME DEFAULT '09:00:00', shift_end TIME DEFAULT '18:00:00', status TEXT DEFAULT 'active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);",
                'attendance' => "CREATE TABLE attendance (id INTEGER PRIMARY KEY AUTOINCREMENT, employee_id INTEGER NOT NULL, punch_type TEXT NOT NULL, punch_time DATETIME NOT NULL, punch_date DATE NOT NULL, project TEXT, site TEXT, ip_address TEXT, user_agent TEXT, device_info TEXT, latitude NUMERIC, longitude NUMERIC, distance_meters NUMERIC, location_verified INTEGER DEFAULT 0, punch_photo TEXT, notes TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);",
                'leaves' => "CREATE TABLE leaves (id INTEGER PRIMARY KEY AUTOINCREMENT, employee_id INTEGER NOT NULL, leave_type TEXT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, days_count NUMERIC DEFAULT 1.0, origin_site TEXT, target_site TEXT, attachment TEXT, reason TEXT, status TEXT DEFAULT 'APPROVED', approval_step INTEGER DEFAULT 1, approved_by INTEGER, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);",
                'holidays' => "CREATE TABLE holidays (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, holiday_date DATE NOT NULL UNIQUE, description TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);",
                'devices' => "CREATE TABLE devices (id INTEGER PRIMARY KEY AUTOINCREMENT, device_name TEXT NOT NULL, serial_number TEXT NOT NULL UNIQUE, ip_address TEXT, port INTEGER DEFAULT 4370, protocol TEXT DEFAULT 'standalone', status TEXT DEFAULT 'ONLINE', last_seen DATETIME, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);",
                'device_logs' => "CREATE TABLE device_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, device_id INTEGER, log_type TEXT DEFAULT 'INFO', message TEXT NOT NULL, raw_payload TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);",
                'activity_logs' => "CREATE TABLE activity_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, action TEXT NOT NULL, description TEXT, ip_address TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);",
                'settings' => "CREATE TABLE settings (setting_key TEXT PRIMARY KEY, setting_value TEXT, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);"
            ];

            foreach ($tableSchemas as $tbl => $schemaSql) {
                try {
                    $sqlite->exec($schemaSql);
                } catch (\Throwable $e) {}
            }

            $tablesToCopy = ['users', 'departments', 'employees', 'attendance', 'leaves', 'holidays', 'devices', 'device_logs', 'activity_logs', 'settings'];
            foreach ($tablesToCopy as $table) {
                try {
                    $stmt = null;
                    try {
                        $stmt = $srcPdo->query("SELECT * FROM \"{$table}\"");
                    } catch (\Throwable $qe) {
                        $stmt = $srcPdo->query("SELECT * FROM {$table}");
                    }
                    if (!$stmt) continue;
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($rows)) continue;

                    $cols = array_keys($rows[0]);
                    $colList = implode(', ', $cols);
                    $placeholders = implode(', ', array_fill(0, count($cols), '?'));
                    $insertStmt = $sqlite->prepare("INSERT INTO {$table} ({$colList}) VALUES ({$placeholders})");

                    $sqlite->beginTransaction();
                    foreach ($rows as $row) {
                        $insertStmt->execute(array_values($row));
                    }
                    $sqlite->commit();
                } catch (\Throwable $tblErr) {
                    if ($sqlite->inTransaction()) {
                        $sqlite->rollBack();
                    }
                }
            }

            return $tmpSqlite;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function addFolderToZip(ZipArchive $zip, string $folderPath, string $zipPath, array &$added = []): void {
        if (!is_dir($folderPath)) return;
        $files = scandir($folderPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = $folderPath . DIRECTORY_SEPARATOR . $file;
            $localZipPath = $zipPath . '/' . $file;
            if (is_dir($fullPath)) {
                $this->addFolderToZip($zip, $fullPath, $localZipPath, $added);
            } elseif (is_file($fullPath)) {
                $zip->addFile($fullPath, $localZipPath);
                $added[$localZipPath] = true;
            }
        }
    }

    /**
     * Upload and restore a complete system backup ZIP, SQLite, or SQL file
     * Supports restoring into SQLite as well as migrating backup data directly into Cloud PostgreSQL/MySQL!
     */
    public function restore(): void {
        Auth::requireAuth();
        if (Auth::isRestricted()) {
            $_SESSION['flash_error'] = 'Access restricted: Your login is limited to Employee Directory and Leave Approvals.';
            redirect('leaves');
        }

        if (empty($_FILES['backup_file']['tmp_name']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Please select a valid backup (.zip) file to upload and restore.');
            redirect('profile');
        }

        $tmpFile = $_FILES['backup_file']['tmp_name'];
        $origName = $_FILES['backup_file']['name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if ($ext !== 'zip' && $ext !== 'sqlite' && $ext !== 'sql') {
            $this->setFlash('error', 'Only .zip, .sqlite, or .sql backup files are supported for restoration.');
            redirect('profile');
        }

        $restoredPhotos = 0;
        $restoredDb = false;
        $liveDriver = Database::getDriverName();

        if ($ext === 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($tmpFile) === true) {
                $baseDir = dirname(__DIR__, 2);

                // 1. Extract and restore uploads (selfie photos, avatars, leave attachments) + persist in stored_files DB
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entryName = str_replace('\\', '/', $zip->getNameIndex($i));

                    if (str_starts_with($entryName, 'public/uploads/') && !str_ends_with($entryName, '/')) {
                        $rawBytes = $zip->getFromIndex($i);
                        if ($rawBytes !== false) {
                            $targetPath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $entryName);
                            $targetDir = dirname($targetPath);
                            if (!is_dir($targetDir)) {
                                @mkdir($targetDir, 0777, true);
                            }
                            @file_put_contents($targetPath, $rawBytes);
                            if (function_exists('store_file_in_db')) {
                                store_file_in_db($entryName, $rawBytes);
                            }
                            $restoredPhotos++;
                        }
                    }
                }

                // 2. Restore Database
                $sqliteContent = $zip->getFromName('database/attendance.sqlite');
                if ($sqliteContent !== false && strlen($sqliteContent) > 100) {
                    if ($liveDriver === 'sqlite') {
                        $dbTarget = (is_dir('/var/data') && is_writable('/var/data'))
                            ? '/var/data/attendance.sqlite'
                            : ($baseDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'attendance.sqlite');
                        $dbDir = dirname($dbTarget);
                        if (!is_dir($dbDir)) @mkdir($dbDir, 0777, true);
                        file_put_contents($dbTarget, $sqliteContent);
                        $restoredDb = true;
                    } else {
                        // Live DB is Cloud PostgreSQL or MySQL: import rows from the SQLite snapshot directly into Cloud DB!
                        $tmpSqlite = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'restore_' . uniqid() . '.sqlite';
                        file_put_contents($tmpSqlite, $sqliteContent);
                        $restoredDb = $this->importSqliteIntoCloudDb($tmpSqlite);
                        @unlink($tmpSqlite);
                    }
                }

                // 3. Alternatively execute SQL dump if database.sqlite wasn't present
                if (!$restoredDb) {
                    $sqlContent = $zip->getFromName('database/database_dump.sql');
                    if ($sqlContent !== false && !empty(trim($sqlContent))) {
                        try {
                            $pdo = Database::getConnection();
                            $cleanSql = str_replace('`', '', $sqlContent);
                            $pdo->exec($cleanSql);
                            $restoredDb = true;
                        } catch (\Throwable $e) {
                            // SQL error
                        }
                    }
                }

                $zip->close();
            } else {
                $this->setFlash('error', 'Unable to extract the selected ZIP archive.');
                redirect('profile');
            }
        } elseif ($ext === 'sqlite') {
            if ($liveDriver === 'sqlite') {
                $baseDir = dirname(__DIR__, 2);
                $dbTarget = (is_dir('/var/data') && is_writable('/var/data'))
                    ? '/var/data/attendance.sqlite'
                    : ($baseDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'attendance.sqlite');
                copy($tmpFile, $dbTarget);
                $restoredDb = true;
            } else {
                $restoredDb = $this->importSqliteIntoCloudDb($tmpFile);
            }
        } elseif ($ext === 'sql') {
            try {
                $sqlContent = (string) file_get_contents($tmpFile);
                $pdo = Database::getConnection();
                $pdo->exec(str_replace('`', '', $sqlContent));
                $restoredDb = true;
            } catch (\Throwable $e) {}
        }

        \App\Core\Logger::log(Auth::id(), 'BACKUP_RESTORED', "Restored system backup from file: {$origName} (Driver: {$liveDriver})");
        $this->setFlash('success', "Backup restored successfully into {$liveDriver} database along with {$restoredPhotos} uploaded file(s).");
        redirect('profile');
    }

    /**
     * Import tables from an uploaded SQLite database directly into Cloud PostgreSQL or MySQL
     */
    private function importSqliteIntoCloudDb(string $sqliteFile): bool {
        try {
            $src = new PDO('sqlite:' . $sqliteFile, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $dst = Database::getConnection();
            $driver = $dst->getAttribute(PDO::ATTR_DRIVER_NAME);

            $tables = ['departments', 'users', 'employees', 'attendance', 'holidays', 'leaves', 'devices', 'settings'];
            foreach ($tables as $table) {
                try {
                    $rows = $src->query("SELECT * FROM {$table}")->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($rows)) continue;

                    // Get destination columns so extra/obsolete columns don't fail
                    $dstCols = $this->getTableColumns($dst, $driver, $table);

                    foreach ($rows as $row) {
                        if (!empty($dstCols)) {
                            $row = array_intersect_key($row, $dstCols);
                        }
                        if ($table === 'settings' && $driver === 'pgsql') {
                            unset($row['id'], $row['description']);
                        }
                        if (empty($row)) continue;

                        $cols = array_keys($row);
                        $placeholders = implode(', ', array_fill(0, count($cols), '?'));
                        $colList = implode(', ', $cols);

                        try {
                            if ($driver === 'pgsql') {
                                $pk = ($table === 'settings') ? 'setting_key' : 'id';
                                $updates = [];
                                foreach ($cols as $c) {
                                    if ($c !== $pk) {
                                        $updates[] = "{$c} = EXCLUDED.{$c}";
                                    }
                                }
                                $conflictClause = !empty($updates)
                                    ? "ON CONFLICT ({$pk}) DO UPDATE SET " . implode(', ', $updates)
                                    : "ON CONFLICT DO NOTHING";
                                $stmt = $dst->prepare("INSERT INTO {$table} ({$colList}) VALUES ({$placeholders}) {$conflictClause}");
                            } else {
                                $updates = [];
                                foreach ($cols as $c) {
                                    if ($c !== 'id' && $c !== 'setting_key') {
                                        $updates[] = "{$c} = VALUES({$c})";
                                    }
                                }
                                $dupClause = !empty($updates) ? "ON DUPLICATE KEY UPDATE " . implode(', ', $updates) : "";
                                $stmt = $dst->prepare("INSERT INTO {$table} ({$colList}) VALUES ({$placeholders}) {$dupClause}");
                            }
                            $stmt->execute(array_values($row));
                        } catch (\Throwable $rowErr) {
                            // Skip conflicting individual row
                        }
                    }

                    // Sync PostgreSQL SERIAL sequence after explicit ID inserts
                    if ($driver === 'pgsql' && $table !== 'settings') {
                        try {
                            $dst->exec("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1), true)");
                        } catch (\Throwable $seqErr) {}
                    }
                } catch (\Throwable $tblErr) {
                    // Table might not exist in older SQLite backup
                }
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function getTableColumns(PDO $pdo, string $driver, string $table): array {
        $cols = [];
        try {
            if ($driver === 'pgsql') {
                $stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = ?");
                $stmt->execute([$table]);
                foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $c) {
                    $cols[$c] = true;
                }
            } elseif ($driver === 'mysql') {
                $stmt = $pdo->query("SHOW COLUMNS FROM {$table}");
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $cols[$r['Field']] = true;
                }
            }
        } catch (\Throwable $e) {}
        return $cols;
    }

    /**
     * Clean old punch photos older than specified days to free up disk storage
     */
    public function cleanOldPhotos(): void {
        Auth::requireAuth();
        if (Auth::isRestricted()) {
            $_SESSION['flash_error'] = 'Access restricted: Your login is limited to Employee Directory and Leave Approvals.';
            redirect('leaves');
        }
        $days = (int) \App\Core\Request::input('days', 60);
        if ($days < 7) $days = 7;

        $cutoffTime = time() - ($days * 86400);
        $punchesDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'punches';
        $deletedFiles = 0;
        $freedBytes = 0;

        if (is_dir($punchesDir)) {
            $files = scandir($punchesDir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $filePath = $punchesDir . DIRECTORY_SEPARATOR . $file;
                if (is_file($filePath) && filemtime($filePath) < $cutoffTime) {
                    $freedBytes += filesize($filePath);
                    @unlink($filePath);
                    $deletedFiles++;
                }
            }
        }

        try {
            $cutoffDate = date('Y-m-d H:i:s', $cutoffTime);
            $pdo = Database::getConnection();
            $delStmt = $pdo->prepare("DELETE FROM stored_files WHERE file_path LIKE 'public/uploads/punches/%' AND created_at < ?");
            $delStmt->execute([$cutoffDate]);
        } catch (\Throwable $e) {}

        $freedMb = round($freedBytes / (1024 * 1024), 2);
        \App\Core\Logger::log(Auth::id(), 'STORAGE_CLEANED', "Cleaned {$deletedFiles} punch photos older than {$days} days ({$freedMb} MB freed)");
        $this->setFlash('success', "Storage cleanup complete! Removed {$deletedFiles} old photos older than {$days} days and freed {$freedMb} MB of disk space.");
        redirect('profile');
    }
}
