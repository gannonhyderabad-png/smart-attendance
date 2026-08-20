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
        $stmt = self::db()->prepare("INSERT INTO settings (setting_key, setting_value) 
                                    VALUES (?, ?) 
                                    ON DUPLICATE KEY UPDATE setting_value = ?");
        return $stmt->execute([$key, $value, $value]);
    }
}
