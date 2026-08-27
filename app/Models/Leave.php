<?php

namespace App\Models;

use PDO;

class Leave extends Model {
    protected static string $table = 'leaves';

    public static function all(array $conditions = [], string $orderBy = 'l.start_date DESC'): array {
        $pdo = self::db();
        $sql = "SELECT l.*, e.name as employee_name, e.employee_code, e.department_id, e.project, e.site, d.name as department_name
                FROM " . static::$table . " l
                JOIN employees e ON l.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE 1=1";
        $params = [];

        if (!empty($conditions['employee_id'])) {
            $sql .= " AND l.employee_id = ?";
            $params[] = (int) $conditions['employee_id'];
        }
        if (!empty($conditions['department_id'])) {
            $sql .= " AND e.department_id = ?";
            $params[] = (int) $conditions['department_id'];
        }
        if (!empty($conditions['leave_type'])) {
            $sql .= " AND l.leave_type = ?";
            $params[] = $conditions['leave_type'];
        }
        if (!empty($conditions['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $conditions['status'];
        }
        if (!empty($conditions['month_start']) && !empty($conditions['month_end'])) {
            $sql .= " AND ((l.start_date BETWEEN ? AND ?) OR (l.end_date BETWEEN ? AND ?) OR (l.start_date <= ? AND l.end_date >= ?))";
            $params[] = $conditions['month_start'];
            $params[] = $conditions['month_end'];
            $params[] = $conditions['month_start'];
            $params[] = $conditions['month_end'];
            $params[] = $conditions['month_start'];
            $params[] = $conditions['month_end'];
        }

        $sql .= " ORDER BY $orderBy";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array {
        $pdo = self::db();
        $stmt = $pdo->prepare("SELECT l.*, e.name as employee_name, e.employee_code FROM " . static::$table . " l JOIN employees e ON l.employee_id = e.id WHERE l.id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get Leave lookup map for an entire month for all employees
     * Map format: ['empId_YYYY-MM-DD' => ['type' => 'Casual Leave', 'code' => 'CL', 'reason' => 'Doctor Visit']]
     */
    public static function getLeaveMapForMonth(string $year, string $month): array {
        $startDate = sprintf('%04d-%02d-01', (int)$year, (int)$month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $leaves = self::all([
            'month_start' => $startDate,
            'month_end' => $endDate,
            'status' => 'APPROVED'
        ]);

        $map = [];
        foreach ($leaves as $lv) {
            $cur = strtotime($lv['start_date']);
            $end = strtotime($lv['end_date']);
            $mStart = strtotime($startDate);
            $mEnd = strtotime($endDate);

            // Bound by current month range
            if ($cur < $mStart) $cur = $mStart;
            if ($end > $mEnd) $end = $mEnd;

            $code = self::getLeaveCode($lv['leave_type']);

            while ($cur <= $end) {
                $dStr = date('Y-m-d', $cur);
                $key = $lv['employee_id'] . '_' . $dStr;
                $map[$key] = [
                    'leave_id' => $lv['id'],
                    'type' => $lv['leave_type'],
                    'code' => $code,
                    'reason' => $lv['reason'] ?: $lv['leave_type'],
                    'origin_site' => $lv['origin_site'] ?? null,
                    'target_site' => $lv['target_site'] ?? null,
                    'days_count' => $lv['days_count']
                ];
                $cur = strtotime('+1 day', $cur);
            }
        }

        return $map;
    }

    public static function getLeaveCode(string $type): string {
        $t = strtoupper(trim($type));
        if (str_contains($t, 'CASUAL') || $t === 'CL') return 'CL';
        if (str_contains($t, 'SICK') || str_contains($t, 'MEDICAL') || $t === 'SL') return 'SL';
        if (str_contains($t, 'PAID') || str_contains($t, 'PRIVILEGE') || str_contains($t, 'ANNUAL') || $t === 'PL') return 'PL';
        if (str_contains($t, 'DUTY') || str_contains($t, 'OFFICIAL') || str_contains($t, 'OUTDOOR') || $t === 'OD') return 'OD';
        if (str_contains($t, 'HALF') || $t === 'HD') return 'HD';
        if (str_contains($t, 'COMP') || $t === 'CO') return 'CO';
        if (str_contains($t, 'HOLIDAY') || str_contains($t, 'OFF')) return 'HOL';
        return 'L';
    }

    public static function create(array $data): int {
        $pdo = self::db();
        $stmt = $pdo->prepare("INSERT INTO " . static::$table . " (employee_id, leave_type, start_date, end_date, days_count, origin_site, target_site, reason, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $startDate = $data['start_date'];
        $endDate = !empty($data['end_date']) ? $data['end_date'] : $startDate;
        
        $daysCount = 1.0;
        if (!empty($data['days_count'])) {
            $daysCount = (float) $data['days_count'];
        } else {
            $startTs = strtotime($startDate);
            $endTs = strtotime($endDate);
            $daysCount = max(1.0, round(($endTs - $startTs) / 86400) + 1.0);
        }

        $stmt->execute([
            (int) $data['employee_id'],
            trim($data['leave_type']),
            $startDate,
            $endDate,
            $daysCount,
            !empty($data['origin_site']) ? trim($data['origin_site']) : null,
            !empty($data['target_site']) ? trim($data['target_site']) : null,
            trim($data['reason'] ?? ''),
            $data['status'] ?? 'APPROVED'
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function delete(int $id): bool {
        $pdo = self::db();
        $stmt = $pdo->prepare("DELETE FROM " . static::$table . " WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
