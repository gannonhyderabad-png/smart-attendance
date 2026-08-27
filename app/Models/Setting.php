<?php

namespace App\Models;

use PDO;

class Setting extends Model {
    public static function get(string $key, ?string $default = null): ?string {
        $stmt = self::db()->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    }

    public static function all(): array {
        $stmt = self::db()->query("SELECT * FROM settings");
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public static function set(string $key, ?string $value): bool {
        $pdo = self::db();
        try {
            // Check if key already exists (Universal ANSI SQL across SQLite, MySQL, PostgreSQL)
            $check = $pdo->prepare("SELECT id FROM settings WHERE setting_key = ? LIMIT 1");
            $check->execute([$key]);
            if ($check->fetch()) {
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                return $stmt->execute([$value, $key]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                return $stmt->execute([$key, $value]);
            }
        } catch (\Throwable $e) {
            // Fallback for driver-specific conflict handling
            try {
                $stmt = $pdo->prepare("INSERT OR REPLACE INTO settings (setting_key, setting_value) VALUES (?, ?)");
                return $stmt->execute([$key, $value]);
            } catch (\Throwable $e2) {
                return false;
            }
        }
    }
}
