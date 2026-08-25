<?php

namespace App\Models;

use PDO;

class Employee extends Model {
    public static function all(array $filters = []): array {
        $sql = "SELECT e.*, d.name as department_name, d.code as department_code 
                FROM employees e 
                LEFT JOIN departments d ON e.department_id = d.id 
                WHERE 1=1";
        $params = [];

        // Recycle Bin Filter
        if (!empty($filters['view']) && $filters['view'] === 'trash') {
            $sql .= " AND e.deleted_at IS NOT NULL";
        } elseif (empty($filters['include_trashed'])) {
            $sql .= " AND e.deleted_at IS NULL";
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (e.name LIKE ? OR e.employee_code LIKE ? OR e.email LIKE ? OR e.phone LIKE ? OR e.project LIKE ? OR e.site LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = ?";
            $params[] = (int) $filters['department_id'];
        }

        if (!empty($filters['department'])) {
            $sql .= " AND (e.department = ? OR d.name = ?)";
            $params[] = $filters['department'];
            $params[] = $filters['department'];
        }

        if (!empty($filters['site'])) {
            $sql .= " AND e.site = ?";
            $params[] = $filters['site'];
        }

        if (!empty($filters['project'])) {
            $sql .= " AND e.project = ?";
            $params[] = $filters['project'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND e.status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY e.id DESC";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function allActive(): array {
        return self::all(['status' => 'active']);
    }



    public static function find(int $id): ?array {
        $stmt = self::db()->prepare("SELECT e.*, d.name as department_name 
                                    FROM employees e 
                                    LEFT JOIN departments d ON e.department_id = d.id 
                                    WHERE e.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByCode(string $code): ?array {
        $decoded = trim(rawurldecode($code));
        $raw = trim($code);
        $normalized = str_replace([' ', '_'], '-', $decoded);
        $padded6 = is_numeric($decoded) ? str_pad($decoded, 6, '0', STR_PAD_LEFT) : $decoded;
        $unpadded = is_numeric($decoded) ? (string)((int)$decoded) : $decoded;

        $stmt = self::db()->prepare("SELECT e.*, d.name as department_name 
                                    FROM employees e 
                                    LEFT JOIN departments d ON e.department_id = d.id 
                                    WHERE LOWER(TRIM(e.employee_code)) = LOWER(?) 
                                       OR LOWER(TRIM(e.employee_code)) = LOWER(?)
                                       OR LOWER(TRIM(e.employee_code)) = LOWER(?)
                                       OR LOWER(TRIM(e.employee_code)) = LOWER(?)
                                       OR LOWER(TRIM(e.employee_code)) = LOWER(?)
                                       OR e.punch_token = ? 
                                       OR e.punch_token = ?
                                       OR (e.id = ? AND ? > 0)
                                    LIMIT 1");
        $idVal = is_numeric($raw) ? (int)$raw : (is_numeric($decoded) ? (int)$decoded : 0);
        $stmt->execute([$decoded, $raw, $normalized, $padded6, $unpadded, $decoded, $raw, $idVal, $idVal]);
        return $stmt->fetch() ?: null;
    }

    public static function resolveEmployee(mixed $input): ?array {
        if (empty($input)) return null;
        if (is_numeric($input) && (int)$input > 0) {
            $emp = self::find((int)$input);
            if ($emp) return $emp;
        }

        $trimmed = trim((string)$input);
        if (empty($trimmed)) return null;

        // Try direct code match or code before dash/em-dash (e.g. "EMP001 — John Doe")
        $parts = preg_split('/[\—\-]+/', $trimmed);
        $possibleCode = trim($parts[0] ?? '');
        if (!empty($possibleCode)) {
            $emp = self::findByCode($possibleCode);
            if ($emp) return $emp;
        }

        $emp = self::findByCode($trimmed);
        if ($emp) return $emp;

        // Search by name or code pattern
        $pdo = self::db();
        $stmt = $pdo->prepare("SELECT e.*, d.name as department_name 
                               FROM employees e 
                               LEFT JOIN departments d ON e.department_id = d.id 
                               WHERE LOWER(TRIM(e.name)) = LOWER(?) 
                                  OR LOWER(TRIM(e.employee_code)) = LOWER(?) 
                                  OR e.name LIKE ? 
                                  OR e.employee_code LIKE ? 
                               LIMIT 1");
        $stmt->execute([$trimmed, $trimmed, '%' . $trimmed . '%', '%' . $trimmed . '%']);
        return $stmt->fetch() ?: null;
    }

    public static function findByToken(string $token): ?array {
        $stmt = self::db()->prepare("SELECT e.*, d.name as department_name 
                                    FROM employees e 
                                    LEFT JOIN departments d ON e.department_id = d.id 
                                    WHERE e.punch_token = ? LIMIT 1");
        $stmt->execute([trim($token)]);
        return $stmt->fetch() ?: null;
    }

    public static function generateNextCode(string $prefix = ''): string {
        $stmt = self::db()->query("SELECT employee_code FROM employees ORDER BY id DESC LIMIT 1");
        $lastCode = $stmt->fetchColumn();

        if ($lastCode && preg_match('/(\d+)$/', $lastCode, $matches)) {
            $nextNum = ((int) $matches[1]) + 1;
            // Pad to 6 digits by default: 000001 to 999999
            return str_pad((string) $nextNum, 6, '0', STR_PAD_LEFT);
        }

        return '000001';
    }

    public static function generateToken(): string {
        return 'tok_' . bin2hex(random_bytes(16));
    }

    public static function resolveDepartment(?string $deptName, ?int $deptId = null): array {
        $deptName = trim($deptName ?? '');
        if (!empty($deptName)) {
            $stmt = self::db()->prepare("SELECT id, name FROM departments WHERE LOWER(TRIM(name)) = LOWER(?) LIMIT 1");
            $stmt->execute([$deptName]);
            $existing = $stmt->fetch();
            if ($existing) {
                return ['department_id' => (int) $existing['id'], 'department' => $existing['name']];
            } else {
                // Auto create new department
                $words = preg_split('/\s+/', $deptName);
                $code = '';
                foreach ($words as $w) {
                    $code .= strtoupper(substr($w, 0, 1));
                }
                if (strlen($code) < 2) {
                    $code = strtoupper(substr($deptName, 0, 3));
                }
                $insert = self::db()->prepare("INSERT INTO departments (name, code) VALUES (?, ?)");
                try {
                    $insert->execute([$deptName, substr($code, 0, 10)]);
                    $newId = (int) self::db()->lastInsertId();
                    return ['department_id' => $newId, 'department' => $deptName];
                } catch (\Throwable $e) {
                    return ['department_id' => null, 'department' => $deptName];
                }
            }
        } elseif (!empty($deptId)) {
            $stmt = self::db()->prepare("SELECT name FROM departments WHERE id = ?");
            $stmt->execute([$deptId]);
            $name = $stmt->fetchColumn();
            return ['department_id' => (int) $deptId, 'department' => $name ?: null];
        }
        return ['department_id' => null, 'department' => null];
    }

    public static function create(array $data): int {
        $token = self::generateToken();
        $deptInfo = self::resolveDepartment($data['department'] ?? null, !empty($data['department_id']) ? (int)$data['department_id'] : null);

        $stmt = self::db()->prepare("INSERT INTO employees 
            (employee_code, name, email, phone, department_id, department, designation, project, site, site_latitude, site_longitude, site_radius, geofence_enabled, punch_token, photo, shift_start, shift_end, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            trim($data['employee_code']),
            trim($data['name']),
            !empty($data['email']) ? trim($data['email']) : null,
            !empty($data['phone']) ? trim($data['phone']) : null,
            $deptInfo['department_id'],
            $deptInfo['department'],
            !empty($data['designation']) ? trim($data['designation']) : null,
            !empty($data['project']) ? trim($data['project']) : null,
            !empty($data['site']) ? trim($data['site']) : null,
            isset($data['site_latitude']) && $data['site_latitude'] !== '' ? (float) $data['site_latitude'] : null,
            isset($data['site_longitude']) && $data['site_longitude'] !== '' ? (float) $data['site_longitude'] : null,
            isset($data['site_radius']) && $data['site_radius'] !== '' ? (int) $data['site_radius'] : 200,
            isset($data['geofence_enabled']) ? (int) $data['geofence_enabled'] : 1,
            $token,
            !empty($data['photo']) ? trim($data['photo']) : null,
            $data['shift_start'] ?? '09:00:00',
            $data['shift_end'] ?? '18:00:00',
            $data['status'] ?? 'active'
        ]);

        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $existing = self::find($id);
        if (!$existing) return false;
        $data = array_merge($existing, $data);
        $deptInfo = self::resolveDepartment($data['department'] ?? null, !empty($data['department_id']) ? (int)$data['department_id'] : null);

        $stmt = self::db()->prepare("UPDATE employees SET 
            employee_code = ?,
            name = ?,
            email = ?,
            phone = ?,
            department_id = ?,
            department = ?,
            designation = ?,
            project = ?,
            site = ?,
            site_latitude = ?,
            site_longitude = ?,
            site_radius = ?,
            geofence_enabled = ?,
            photo = ?,
            shift_start = ?,
            shift_end = ?,
            status = ?
            WHERE id = ?");

        return $stmt->execute([
            trim($data['employee_code']),
            trim($data['name']),
            !empty($data['email']) ? trim($data['email']) : null,
            !empty($data['phone']) ? trim($data['phone']) : null,
            $deptInfo['department_id'],
            $deptInfo['department'],
            !empty($data['designation']) ? trim($data['designation']) : null,
            !empty($data['project']) ? trim($data['project']) : null,
            !empty($data['site']) ? trim($data['site']) : null,
            isset($data['site_latitude']) && $data['site_latitude'] !== '' ? (float) $data['site_latitude'] : null,
            isset($data['site_longitude']) && $data['site_longitude'] !== '' ? (float) $data['site_longitude'] : null,
            isset($data['site_radius']) && $data['site_radius'] !== '' ? (int) $data['site_radius'] : 200,
            isset($data['geofence_enabled']) ? (int) $data['geofence_enabled'] : 1,
            !empty($data['photo']) ? trim($data['photo']) : null,
            $data['shift_start'] ?? '09:00:00',
            $data['shift_end'] ?? '18:00:00',
            $data['status'] ?? 'active',
            $id
        ]);
    }

    /**
     * Soft delete employee (Move to Recycle Bin)
     */
    public static function delete(int $id): bool {
        $stmt = self::db()->prepare("UPDATE employees SET deleted_at = CURRENT_TIMESTAMP, status = 'archived' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Restore employee from Recycle Bin back to active workforce
     */
    public static function restore(int $id): bool {
        $stmt = self::db()->prepare("UPDATE employees SET deleted_at = NULL, status = 'active' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Permanently delete employee and all related attendance logs from the database
     */
    public static function forceDelete(int $id): bool {
        // Delete attendance logs
        $stmtAtt = self::db()->prepare("DELETE FROM attendance WHERE employee_id = ?");
        $stmtAtt->execute([$id]);

        // Permanently delete employee record
        $stmt = self::db()->prepare("DELETE FROM employees WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Empty entire Recycle Bin permanently
     */
    public static function emptyTrash(): int {
        $stmt = self::db()->query("SELECT id FROM employees WHERE deleted_at IS NOT NULL");
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $count = 0;
        foreach ($ids as $id) {
            if (self::forceDelete((int)$id)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Count employees currently in Recycle Bin
     */
    public static function countTrash(): int {
        return (int) self::db()->query("SELECT COUNT(*) FROM employees WHERE deleted_at IS NOT NULL")->fetchColumn();
    }

    public static function count(): int {
        return (int) self::db()->query("SELECT COUNT(*) FROM employees WHERE status = 'active' AND deleted_at IS NULL")->fetchColumn();
    }

    /**
     * Get list of all distinct department names from active employees
     */
    public static function getDistinctDepartments(): array {
        try {
            $sql = "SELECT DISTINCT name FROM (
                        SELECT d.name FROM departments d 
                        JOIN employees e ON e.department_id = d.id 
                        WHERE e.deleted_at IS NULL AND d.name IS NOT NULL AND TRIM(d.name) != ''
                        UNION
                        SELECT e.department as name FROM employees e 
                        WHERE e.deleted_at IS NULL AND e.department IS NOT NULL AND TRIM(e.department) != ''
                    ) as active_depts ORDER BY name ASC";
            $res = self::db()->query($sql)->fetchAll(PDO::FETCH_COLUMN);
            return !empty($res) ? $res : ['General', 'Engineering', 'Operations', 'IT'];
        } catch (\Throwable $e) {
            return ['General', 'Engineering', 'Operations', 'IT'];
        }
    }

    /**
     * Get list of all distinct site names from active employees
     */
    public static function getDistinctSites(): array {
        try {
            $sql = "SELECT DISTINCT site FROM employees 
                    WHERE deleted_at IS NULL AND site IS NOT NULL AND TRIM(site) != '' 
                    ORDER BY site ASC";
            $res = self::db()->query($sql)->fetchAll(PDO::FETCH_COLUMN);
            return !empty($res) ? $res : ['HRO', 'Headquarters', 'Hyderabad Cyber Towers'];
        } catch (\Throwable $e) {
            return ['HRO', 'Headquarters', 'Hyderabad Cyber Towers'];
        }
    }

    /**
     * Get list of all distinct project names across employees and attendance logs
     */
    public static function getDistinctProjects(): array {
        try {
            $sql = "SELECT DISTINCT project FROM (
                        SELECT project FROM employees WHERE project IS NOT NULL AND TRIM(project) != ''
                        UNION
                        SELECT project FROM attendance WHERE project IS NOT NULL AND TRIM(project) != ''
                    ) as combined_projs ORDER BY project ASC";
            $res = self::db()->query($sql)->fetchAll(PDO::FETCH_COLUMN);
            return !empty($res) ? $res : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function regenerateToken(int $id): string {
        $newToken = self::generateToken();
        $stmt = self::db()->prepare("UPDATE employees SET punch_token = ? WHERE id = ?");
        $stmt->execute([$newToken, $id]);
        return $newToken;
    }
}
