<?php

namespace App\Models;

use PDO;

class ActivityLog extends Model {
    public static function all(int $limit = 50, int $offset = 0): array {
        $stmt = self::db()->prepare("SELECT a.*, u.name as user_name, u.email as user_email 
                                    FROM activity_logs a 
                                    LEFT JOIN users u ON a.user_id = u.id 
                                    ORDER BY a.created_at DESC 
                                    LIMIT " . (int)$limit . " OFFSET " . (int)$offset);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count(): int {
        return (int) self::db()->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
    }
}
