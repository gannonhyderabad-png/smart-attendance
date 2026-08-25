<?php

namespace App\Models;

use PDO;

class Holiday extends Model {
    protected static string $table = 'holidays';

    public static function all(array $conditions = [], string $orderBy = 'holiday_date ASC'): array {
        $pdo = self::db();
        $sql = "SELECT * FROM " . static::$table;
        $params = [];

        if (!empty($conditions)) {
            $clauses = [];
            foreach ($conditions as $col => $val) {
                $clauses[] = "$col = ?";
                $params[] = $val;
            }
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }

        $sql .= " ORDER BY $orderBy";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array {
        $pdo = self::db();
        $stmt = $pdo->prepare("SELECT * FROM " . static::$table . " WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findByDate(string $date): ?array {
        $pdo = self::db();
        $stmt = $pdo->prepare("SELECT * FROM " . static::$table . " WHERE holiday_date = ? LIMIT 1");
        $stmt->execute([$date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function getForMonth(string $year, string $month): array {
        $pdo = self::db();
        $startDate = sprintf('%04d-%02d-01', (int)$year, (int)$month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $stmt = $pdo->prepare("SELECT * FROM " . static::$table . " WHERE holiday_date >= ? AND holiday_date <= ? ORDER BY holiday_date ASC");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getMapForMonth(string $year, string $month): array {
        $holidays = self::getForMonth($year, $month);
        $map = [];
        foreach ($holidays as $h) {
            $map[$h['holiday_date']] = $h['title'];
        }
        return $map;
    }

    public static function create(array $data): int {
        $pdo = self::db();
        $stmt = $pdo->prepare("INSERT INTO " . static::$table . " (title, holiday_date, description) VALUES (?, ?, ?)");
        $stmt->execute([
            trim($data['title']),
            $data['holiday_date'],
            trim($data['description'] ?? '')
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $pdo = self::db();
        $stmt = $pdo->prepare("UPDATE " . static::$table . " SET title = ?, holiday_date = ?, description = ? WHERE id = ?");
        return $stmt->execute([
            trim($data['title']),
            $data['holiday_date'],
            trim($data['description'] ?? ''),
            $id
        ]);
    }

    public static function delete(int $id): bool {
        $pdo = self::db();
        $stmt = $pdo->prepare("DELETE FROM " . static::$table . " WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
