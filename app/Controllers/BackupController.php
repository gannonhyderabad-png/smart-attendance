<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use Database\Database;
use ZipArchive;
use PDO;

class BackupController extends Controller {
    /**
     * Generate and download a complete ZIP backup of the database and all upload photos
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

        // 1. Backup SQLite Database file if exists
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

        // 2. Backup ANSI SQL Data Dump (works across SQLite, PostgreSQL, and MySQL)
        try {
            $pdo = Database::getConnection();
            $sqlDump = "-- Smart Attendance System Complete SQL Backup\n";
            $sqlDump .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";

            $tables = ['users', 'departments', 'employees', 'attendance', 'holidays', 'leaves', 'devices', 'device_logs', 'activity_logs', 'settings'];
            foreach ($tables as $table) {
                try {
                    $stmt = $pdo->query("SELECT * FROM {$table}");
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
                } catch (\Throwable $tblErr) {
                    // Skip if table doesn't exist
                }
            }
            $zip->addFromString('database/database_dump.sql', $sqlDump);
        } catch (\Throwable $e) {
            // SQL dump fallback
        }

        // 3. Backup Avatars, Punch Selfies & Leave Attachments (from disk + stored_files DB table)
        $addedUploadPaths = [];
        $uploadsDir = __DIR__ . '/../../public/uploads';
        if (is_dir($uploadsDir)) {
            $this->addFolderToZip($zip, $uploadsDir, 'public/uploads', $addedUploadPaths);
        }
        try {
            $pdo = Database::getConnection();
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

        // 4. Add Backup Metadata
        $meta = [
            'backup_date' => date('Y-m-d H:i:s'),
            'system' => 'Smart Attendance System',
            'version' => '2.1',
            'db_driver' => Database::getDriverName(),
            'created_by' => Auth::user()['name'] ?? 'Admin',
        ];
        $zip->addFromString('backup_info.json', json_encode($meta, JSON_PRETTY_PRINT));

        $zip->close();

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
