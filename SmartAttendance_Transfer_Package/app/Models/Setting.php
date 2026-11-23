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
            // Universal ANSI SQL across SQLite, MySQL, and PostgreSQL (settings PK may be setting_key)
            $check = $pdo->prepare("SELECT setting_key FROM settings WHERE setting_key = ? LIMIT 1");
            $check->execute([$key]);
            if ($check->fetchColumn() !== false) {
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?");
                return $stmt->execute([$value, $key]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                return $stmt->execute([$key, $value]);
            }
        } catch (\Throwable $e) {
            return false;
        }
    }
}
