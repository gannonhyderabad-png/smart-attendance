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
}
