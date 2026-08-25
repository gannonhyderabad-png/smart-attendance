<?php

namespace App\Models;

use PDO;

class Department extends Model {
    public static function all(): array {
        $stmt = self::db()->query("SELECT d.*, COUNT(e.id) as employee_count FROM departments d LEFT JOIN employees e ON d.id = e.department_id GROUP BY d.id ORDER BY d.name ASC");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $stmt = self::db()->prepare("SELECT * FROM departments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $name, ?string $code = null, ?string $description = null): int {
        $stmt = self::db()->prepare("INSERT INTO departments (name, code, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $code, $description]);
        return (int) self::db()->lastInsertId();
    }
}
