<?php

namespace App\Core;

use Database\Database;
use Exception;

class Logger {
    public static function log(?int $userId, string $action, ?string $description = null): void {
        try {
            $pdo = Database::getConnection();
            $ip = Request::getClientIp();
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $action, $description, $ip]);
        } catch (Exception $e) {
            // Silently skip if table is not ready during initial setup
            error_log("Activity log error: " . $e->getMessage());
        }
    }
}
