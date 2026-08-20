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

        $zipFileName = 'smart_attendance_backup_' . date('Y-m-d_H-i-s') . '.zip';
        $tempZipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            die("Could not create backup zip file.");
        }

        // 1. Backup SQLite Database file if exists
        $sqlitePath = __DIR__ . '/../../database/attendance.sqlite';
        if (file_exists($sqlitePath)) {
            $zip->addFile($sqlitePath, 'database/attendance.sqlite');
        }

        // 2. Backup SQL Data Dump
        try {
            $pdo = Database::getConnection();
            $sqlDump = "-- Smart Attendance System Complete SQL Backup\n";
            $sqlDump .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";

            $tables = ['users', 'departments', 'employees', 'attendance', 'activity_logs', 'settings'];
            foreach ($tables as $table) {
                try {
                    $stmt = $pdo->query("SELECT * FROM `$table`");
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (!empty($rows)) {
                        $sqlDump .= "-- Table: $table (" . count($rows) . " rows)\n";
                        foreach ($rows as $row) {
                            $keys = array_keys($row);
                            $values = array_map(function($val) use ($pdo) {
                                return $val === null ? 'NULL' : $pdo->quote((string)$val);
                            }, array_values($row));
                            $sqlDump .= "INSERT INTO `$table` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $values) . ");\n";
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

        // 3. Backup Avatars & Punch Selfies
        $uploadsDir = __DIR__ . '/../../public/uploads';
        if (is_dir($uploadsDir)) {
            $this->addFolderToZip($zip, $uploadsDir, 'public/uploads');
        }

        // 4. Add Backup Metadata
        $meta = [
            'backup_date' => date('Y-m-d H:i:s'),
            'system' => 'Smart Attendance System',
            'version' => '2.0',
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

    private function addFolderToZip(ZipArchive $zip, string $folderPath, string $zipPath): void {
        if (!is_dir($folderPath)) return;
        $files = scandir($folderPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = $folderPath . DIRECTORY_SEPARATOR . $file;
            $localZipPath = $zipPath . '/' . $file;
            if (is_dir($fullPath)) {
                $this->addFolderToZip($zip, $fullPath, $localZipPath);
            } elseif (is_file($fullPath)) {
                $zip->addFile($fullPath, $localZipPath);
            }
        }
    }

    /**
     * Upload and restore a complete system backup ZIP or SQL file
     */
    public function restore(): void {
        Auth::requireAuth();

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

        if ($ext === 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($tmpFile) === true) {
                $baseDir = dirname(__DIR__, 2);

                // 1. Extract and restore uploads (selfie photos and avatars)
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entryName = $zip->getNameIndex($i);

                    if (str_starts_with($entryName, 'public/uploads/')) {
                        $targetPath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $entryName);
                        $targetDir = dirname($targetPath);
                        if (!is_dir($targetDir)) {
                            mkdir($targetDir, 0777, true);
                        }
                        if (!str_ends_with($entryName, '/')) {
                            file_put_contents($targetPath, $zip->getFromIndex($i));
                            $restoredPhotos++;
                        }
                    }
                }

                // 2. Restore SQLite database file if present
                $sqliteContent = $zip->getFromName('database/attendance.sqlite');
                if ($sqliteContent !== false) {
                    $dbTarget = $baseDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'attendance.sqlite';
                    $dbDir = dirname($dbTarget);
                    if (!is_dir($dbDir)) mkdir($dbDir, 0777, true);
                    file_put_contents($dbTarget, $sqliteContent);
                    $restoredDb = true;
                }

                // 3. Alternatively execute SQL dump if database.sqlite wasn't present
                if (!$restoredDb) {
                    $sqlContent = $zip->getFromName('database/database_dump.sql');
                    if ($sqlContent !== false && !empty(trim($sqlContent))) {
                        try {
                            $pdo = Database::getConnection();
                            $pdo->exec($sqlContent);
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
            $baseDir = dirname(__DIR__, 2);
            $dbTarget = $baseDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'attendance.sqlite';
            copy($tmpFile, $dbTarget);
            $restoredDb = true;
        }

        \App\Core\Logger::log(Auth::id(), 'BACKUP_RESTORED', "Restored system backup from file: {$origName}");
        $this->setFlash('success', "Backup restored successfully! Restored database and {$restoredPhotos} uploaded photos.");
        redirect('profile');
    }

    /**
     * Clean old punch photos older than specified days to free up disk storage
     */
    public function cleanOldPhotos(): void {
        Auth::requireAuth();
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

        $freedMb = round($freedBytes / (1024 * 1024), 2);
        \App\Core\Logger::log(Auth::id(), 'STORAGE_CLEANED', "Cleaned {$deletedFiles} punch photos older than {$days} days ({$freedMb} MB freed)");
        $this->setFlash('success', "Storage cleanup complete! Removed {$deletedFiles} old photos older than {$days} days and freed {$freedMb} MB of disk space.");
        redirect('profile');
    }
}
