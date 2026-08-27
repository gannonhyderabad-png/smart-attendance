<?php

namespace App\Models;

use PDO;
use Exception;

class Attendance extends Model {
    /**
     * Record a new Punch IN or OUT
     */
    public static function recordPunch(int $employeeId, string $type, array $meta = []): array {
        $type = strtoupper(trim($type));
        if (!in_array($type, ['IN', 'OUT'])) {
            return ['success' => false, 'message' => 'Invalid punch type. Allowed: IN, OUT'];
        }

        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');

        // Check latest punch for employee
        $latest = self::getLatestPunch($employeeId);

        // Validation 1: Prevent duplicate punch type (e.g. IN when already IN, OUT when already OUT)
        if ($latest) {
            if ($latest['punch_type'] === $type) {
                $statusText = $type === 'IN' ? 'already punched IN' : 'already punched OUT';
                return [
                    'success' => false,
                    'message' => "You are {$statusText}. Your last punch was at " . date('h:i A', strtotime($latest['punch_time'])),
                    'current_status' => $latest['punch_type'],
                    'last_punch' => $latest
                ];
            }

            // Rapid spam prevention: prevent punching within 5 seconds
            $timeDiff = strtotime($now) - strtotime($latest['punch_time']);
            if ($timeDiff < 5) {
                return [
                    'success' => false,
                    'message' => 'Please wait a few moments before punching again.',
                    'current_status' => $latest['punch_type'],
                    'last_punch' => $latest
                ];
            }
        } else {
            // First punch ever or today: cannot punch OUT if never punched IN
            if ($type === 'OUT') {
                return [
                    'success' => false,
                    'message' => 'Cannot punch OUT without punching IN first.',
                    'current_status' => 'OUT',
                    'last_punch' => null
                ];
            }
        }

        // --- GEOFENCING & GPS VALIDATION ---
        $employee = Employee::find($employeeId);
        $distance = null;
        $locationVerified = 0;

        if ($employee && !empty($employee['geofence_enabled']) && !empty($employee['site_latitude']) && !empty($employee['site_longitude'])) {
            $siteLat = (float) $employee['site_latitude'];
            $siteLon = (float) $employee['site_longitude'];
            $allowedRadius = !empty($employee['site_radius']) ? (int) $employee['site_radius'] : 200;

            $punchLat = isset($meta['latitude']) && $meta['latitude'] !== null && $meta['latitude'] !== '' ? (float) $meta['latitude'] : null;
            $punchLon = isset($meta['longitude']) && $meta['longitude'] !== null && $meta['longitude'] !== '' ? (float) $meta['longitude'] : null;

            if ($punchLat === null || $punchLon === null) {
                return [
                    'success' => false,
                    'message' => 'Location permission required. Please allow GPS location access on your phone to punch attendance.',
                    'current_status' => $latest ? $latest['punch_type'] : 'OUT',
                    'last_punch' => $latest
                ];
            }

            $distance = calculate_haversine_distance($punchLat, $punchLon, $siteLat, $siteLon);

            if ($distance > $allowedRadius) {
                $siteLabel = !empty($employee['site']) ? $employee['site'] : 'your assigned site';
                return [
                    'success' => false,
                    'message' => "Punch Rejected: You are " . round($distance) . "m away from {$siteLabel}. Punch is strictly allowed only within {$allowedRadius} meters.",
                    'distance' => $distance,
                    'allowed_radius' => $allowedRadius,
                    'current_status' => $latest ? $latest['punch_type'] : 'OUT',
                    'last_punch' => $latest
                ];
            }

            $locationVerified = 1;
        }

        // Save punch selfie photo if provided
        $punchPhoto = null;
        if (!empty($meta['photo_data'])) {
            $punchPhoto = save_base64_image($meta['photo_data'], 'punches');
        } elseif (!empty($meta['punch_photo'])) {
            $punchPhoto = $meta['punch_photo'];
        }

        // Insert punch record
        $stmt = self::db()->prepare("INSERT INTO attendance 
            (employee_id, punch_type, punch_time, punch_date, project, site, ip_address, user_agent, device_info, latitude, longitude, distance_meters, location_verified, punch_photo, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $employeeId,
            $type,
            $now,
            $today,
            $meta['project'] ?? null,
            $meta['site'] ?? null,
            $meta['ip_address'] ?? null,
            $meta['user_agent'] ?? null,
            $meta['device_info'] ?? null,
            $meta['latitude'] ?? null,
            $meta['longitude'] ?? null,
            $distance,
            $locationVerified,
            $punchPhoto,
            $meta['notes'] ?? null
        ]);

        $punchId = (int) self::db()->lastInsertId();

        $successMsg = $type === 'IN' ? 'Punch IN recorded successfully! Have a productive day.' : 'Punch OUT recorded successfully! Have a great evening.';
        if ($locationVerified && $distance !== null) {
            $successMsg .= " (📍 Verified within " . round($distance) . "m)";
        }

        return [
            'success' => true,
            'message' => $successMsg,
            'punch_id' => $punchId,
            'punch_type' => $type,
            'punch_time' => $now,
            'distance' => $distance,
            'location_verified' => $locationVerified,
            'punch_photo' => $punchPhoto,
            'current_status' => $type
        ];
    }

    /**
     * Get the single latest punch record for an employee
     */
    public static function getLatestPunch(int $employeeId, ?string $date = null): ?array {
        $sql = "SELECT * FROM attendance WHERE employee_id = ?";
        $params = [$employeeId];

        if ($date) {
            $sql .= " AND punch_date = ?";
            $params[] = $date;
        }

        $sql .= " ORDER BY punch_time DESC LIMIT 1";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get all punches for an employee for a specific date
     */
    public static function getDayPunches(int $employeeId, string $date): array {
        $stmt = self::db()->prepare("SELECT * FROM attendance WHERE employee_id = ? AND punch_date = ? ORDER BY punch_time ASC");
        $stmt->execute([$employeeId, $date]);
        return $stmt->fetchAll();
    }

    /**
     * Calculate worked seconds for an employee on a given date by pairing IN and OUT.
     * When there is no OUT punch, auto-closes work duration based on the employee's site shift_end time.
     */
    public static function calculateWorkSeconds(int $employeeId, string $date): int {
        $punches = self::getDayPunches($employeeId, $date);
        $totalSeconds = 0;
        $inTime = null;

        foreach ($punches as $punch) {
            if ($punch['punch_type'] === 'IN') {
                $inTime = strtotime($punch['punch_time']);
            } elseif ($punch['punch_type'] === 'OUT' && $inTime !== null) {
                $outTime = strtotime($punch['punch_time']);
                if ($outTime > $inTime) {
                    $totalSeconds += ($outTime - $inTime);
                }
                $inTime = null;
            }
        }

        // If still unclosed IN punch without OUT punch:
        if ($inTime !== null) {
            $employee = Employee::find($employeeId);
            $shiftEnd = !empty($employee['shift_end']) ? $employee['shift_end'] : '18:00:00';
            $shiftEndTs = strtotime($date . ' ' . $shiftEnd);
            $today = date('Y-m-d');
            $nowTime = time();

            if ($date === $today) {
                if ($nowTime <= $shiftEndTs) {
                    // Shift currently in progress: calculate live running worked time
                    $totalSeconds += max(0, $nowTime - $inTime);
                } else {
                    // Past shift end time today: auto-close at shift_end!
                    $totalSeconds += max(0, $shiftEndTs - $inTime);
                }
            } else {
                // Past date: auto-close at shift_end of that date
                if ($shiftEndTs > $inTime) {
                    $totalSeconds += max(0, $shiftEndTs - $inTime);
                } else {
                    // Fallback to 8 hours (28800s) if shift spans midnight or invalid
                    $totalSeconds += 28800;
                }
            }
        }

        return $totalSeconds;
    }

    /**
     * List attendance records with comprehensive filtering
     */
    public static function all(array $filters = [], int $limit = 50, int $offset = 0): array {
        $sql = "SELECT a.*, e.name as employee_name, e.employee_code, e.photo as employee_photo, e.designation, e.project as employee_project, e.site as employee_site, d.name as department_name 
                FROM attendance a 
                JOIN employees e ON a.employee_id = e.id 
                LEFT JOIN departments d ON e.department_id = d.id 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['employee_id'])) {
            $sql .= " AND a.employee_id = ?";
            $params[] = (int) $filters['employee_id'];
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = ?";
            $params[] = (int) $filters['department_id'];
        }

        if (!empty($filters['punch_type'])) {
            $sql .= " AND a.punch_type = ?";
            $params[] = $filters['punch_type'];
        }

        if (!empty($filters['project'])) {
            $searchProj = '%' . $filters['project'] . '%';
            $sql .= " AND (a.project LIKE ? OR e.project LIKE ?)";
            $params[] = $searchProj;
            $params[] = $searchProj;
        }

        if (!empty($filters['site'])) {
            $searchSite = '%' . $filters['site'] . '%';
            $sql .= " AND (a.site LIKE ? OR e.site LIKE ?)";
            $params[] = $searchSite;
            $params[] = $searchSite;
        }

        if (!empty($filters['date'])) {
            $sql .= " AND a.punch_date = ?";
            $params[] = $filters['date'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND a.punch_date >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND a.punch_date <= ?";
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (e.name LIKE ? OR e.employee_code LIKE ? OR a.ip_address LIKE ? OR a.device_info LIKE ? OR a.project LIKE ? OR e.project LIKE ? OR a.site LIKE ? OR e.site LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY a.punch_time DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get attendance records paired side-by-side (IN & OUT in one unified row per session).
     * If an employee does not punch OUT, automatically closes the work duration at scheduled site shift_end and flags NO_OUT.
     */
    public static function getPairedSessions(array $filters = [], int $limit = 50, int $offset = 0): array {
        $sql = "SELECT a.*, e.name as employee_name, e.employee_code, e.photo as employee_photo, e.designation, e.project as employee_project, e.site as employee_site, e.shift_start, e.shift_end, d.name as department_name 
                FROM attendance a 
                JOIN employees e ON a.employee_id = e.id 
                LEFT JOIN departments d ON e.department_id = d.id 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['employee_id'])) {
            $sql .= " AND a.employee_id = ?";
            $params[] = (int) $filters['employee_id'];
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = ?";
            $params[] = (int) $filters['department_id'];
        }

        if (!empty($filters['project'])) {
            $searchProj = '%' . $filters['project'] . '%';
            $sql .= " AND (a.project LIKE ? OR e.project LIKE ?)";
            $params[] = $searchProj;
            $params[] = $searchProj;
        }

        if (!empty($filters['site'])) {
            $searchSite = '%' . $filters['site'] . '%';
            $sql .= " AND (a.site LIKE ? OR e.site LIKE ?)";
            $params[] = $searchSite;
            $params[] = $searchSite;
        }

        if (!empty($filters['date'])) {
            $sql .= " AND a.punch_date = ?";
            $params[] = $filters['date'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND a.punch_date >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND a.punch_date <= ?";
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (e.name LIKE ? OR e.employee_code LIKE ? OR a.ip_address LIKE ? OR a.device_info LIKE ? OR a.project LIKE ? OR e.project LIKE ? OR a.site LIKE ? OR e.site LIKE ?)";
            for ($i = 0; $i < 8; $i++) $params[] = $search;
        }

        $sql .= " ORDER BY a.punch_date DESC, a.employee_id ASC, a.punch_time ASC";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        $allPunches = $stmt->fetchAll();

        // Group punches by employee and date
        $grouped = [];
        foreach ($allPunches as $p) {
            $key = $p['punch_date'] . '_' . $p['employee_id'];
            $grouped[$key][] = $p;
        }

        $sessions = [];
        $today = date('Y-m-d');
        $nowTs = time();

        foreach ($grouped as $key => $punches) {
            $currentSession = null;
            foreach ($punches as $p) {
                if ($p['punch_type'] === 'IN') {
                    if ($currentSession !== null) {
                        // Previous session had no OUT punch -> auto-close at shift_end or next punch
                        $prevInTs = strtotime($currentSession['in_time']);
                        $shiftEnd = !empty($currentSession['shift_end']) ? $currentSession['shift_end'] : '18:00:00';
                        $shiftEndTs = strtotime($currentSession['punch_date'] . ' ' . $shiftEnd);
                        $closedTs = ($shiftEndTs > $prevInTs) ? $shiftEndTs : strtotime($p['punch_time']);
                        $diff = max(0, $closedTs - $prevInTs);
                        $currentSession['duration_seconds'] = $diff;
                        $currentSession['formatted_duration'] = format_seconds($diff);
                        $currentSession['status'] = 'NO_OUT';
                        $currentSession['auto_closed'] = true;
                        $sessions[] = $currentSession;
                    }
                    $currentSession = [
                        'employee_id' => $p['employee_id'],
                        'employee_name' => $p['employee_name'],
                        'employee_code' => $p['employee_code'],
                        'employee_photo' => $p['employee_photo'] ?? null,
                        'designation' => $p['designation'],
                        'department_name' => $p['department_name'] ?? 'General',
                        'project' => $p['project'] ?: ($p['employee_project'] ?? null),
                        'site' => $p['site'] ?: ($p['employee_site'] ?? null),
                        'shift_start' => $p['shift_start'] ?? '09:00:00',
                        'shift_end' => $p['shift_end'] ?? '18:00:00',
                        'punch_date' => $p['punch_date'],
                        'in_id' => $p['id'],
                        'in_time' => $p['punch_time'],
                        'in_ip' => $p['ip_address'],
                        'in_device' => $p['device_info'],
                        'in_lat' => $p['latitude'],
                        'in_lon' => $p['longitude'],
                        'in_distance' => $p['distance_meters'],
                        'in_photo' => $p['punch_photo'] ?? null,
                        'location_verified' => $p['location_verified'],
                        'out_id' => null,
                        'out_time' => null,
                        'out_ip' => null,
                        'out_device' => null,
                        'out_lat' => null,
                        'out_lon' => null,
                        'out_distance' => null,
                        'out_photo' => null,
                        'duration_seconds' => 0,
                        'formatted_duration' => '00:00:00',
                        'status' => 'IN',
                        'auto_closed' => false
                    ];
                } elseif ($p['punch_type'] === 'OUT') {
                    if ($currentSession !== null) {
                        $currentSession['out_id'] = $p['id'];
                        $currentSession['out_time'] = $p['punch_time'];
                        $currentSession['out_ip'] = $p['ip_address'];
                        $currentSession['out_device'] = $p['device_info'];
                        $currentSession['out_lat'] = $p['latitude'];
                        $currentSession['out_lon'] = $p['longitude'];
                        $currentSession['out_distance'] = $p['distance_meters'];
                        $currentSession['out_photo'] = $p['punch_photo'] ?? null;
                        if (!empty($p['location_verified'])) {
                            $currentSession['location_verified'] = 1;
                        }
                        $inTs = strtotime($currentSession['in_time']);
                        $outTs = strtotime($p['punch_time']);
                        $diff = max(0, $outTs - $inTs);
                        $currentSession['duration_seconds'] = $diff;
                        $currentSession['formatted_duration'] = format_seconds($diff);
                        $currentSession['status'] = 'COMPLETED';
                        $currentSession['auto_closed'] = false;
                        $sessions[] = $currentSession;
                        $currentSession = null;
                    } else {
                        // Standalone OUT
                        $sessions[] = [
                            'employee_id' => $p['employee_id'],
                            'employee_name' => $p['employee_name'],
                            'employee_code' => $p['employee_code'],
                            'employee_photo' => $p['employee_photo'] ?? null,
                            'designation' => $p['designation'],
                            'department_name' => $p['department_name'] ?? 'General',
                            'project' => $p['project'] ?: ($p['employee_project'] ?? null),
                            'site' => $p['site'] ?: ($p['employee_site'] ?? null),
                            'shift_start' => $p['shift_start'] ?? '09:00:00',
                            'shift_end' => $p['shift_end'] ?? '18:00:00',
                            'punch_date' => $p['punch_date'],
                            'in_id' => null,
                            'in_time' => null,
                            'in_ip' => null,
                            'in_device' => null,
                            'in_lat' => null,
                            'in_lon' => null,
                            'in_distance' => null,
                            'in_photo' => null,
                            'location_verified' => $p['location_verified'],
                            'out_id' => $p['id'],
                            'out_time' => $p['punch_time'],
                            'out_ip' => $p['ip_address'],
                            'out_device' => $p['device_info'],
                            'out_lat' => $p['latitude'],
                            'out_lon' => $p['longitude'],
                            'out_distance' => $p['distance_meters'],
                            'out_photo' => $p['punch_photo'] ?? null,
                            'duration_seconds' => 0,
                            'formatted_duration' => '—',
                            'status' => 'COMPLETED',
                            'auto_closed' => false
                        ];
                    }
                }
            }

            if ($currentSession !== null) {
                $inTs = strtotime($currentSession['in_time']);
                $shiftEnd = !empty($currentSession['shift_end']) ? $currentSession['shift_end'] : '18:00:00';
                $shiftEndTs = strtotime($currentSession['punch_date'] . ' ' . $shiftEnd);

                if ($currentSession['punch_date'] === $today) {
                    if ($nowTs <= $shiftEndTs) {
                        // Actively working right now
                        $diff = max(0, $nowTs - $inTs);
                        $currentSession['duration_seconds'] = $diff;
                        $currentSession['formatted_duration'] = format_seconds($diff);
                        $currentSession['status'] = 'IN';
                        $currentSession['auto_closed'] = false;
                    } else {
                        // Today, but past scheduled shift_end with no OUT punch -> Auto-close!
                        $diff = max(0, $shiftEndTs - $inTs);
                        $currentSession['duration_seconds'] = $diff;
                        $currentSession['formatted_duration'] = format_seconds($diff);
                        $currentSession['status'] = 'NO_OUT';
                        $currentSession['auto_closed'] = true;
                    }
                } else {
                    // Past date with no OUT punch -> Auto-close based on shift_end of that day
                    $diff = ($shiftEndTs > $inTs) ? ($shiftEndTs - $inTs) : 28800;
                    $diff = max(0, $diff);
                    $currentSession['duration_seconds'] = $diff;
                    $currentSession['formatted_duration'] = format_seconds($diff);
                    $currentSession['status'] = 'NO_OUT';
                    $currentSession['auto_closed'] = true;
                }
                $sessions[] = $currentSession;
            }
        }

        // Sort sessions newest first by punch date and timestamp
        usort($sessions, function($a, $b) {
            $timeA = $a['in_time'] ?? $a['out_time'] ?? $a['punch_date'];
            $timeB = $b['in_time'] ?? $b['out_time'] ?? $b['punch_date'];
            return strcmp($timeB, $timeA);
        });

        // Optional punch_type filter
        if (!empty($filters['punch_type'])) {
            $pt = strtoupper($filters['punch_type']);
            $sessions = array_values(array_filter($sessions, function($s) use ($pt) {
                if ($pt === 'IN') return $s['status'] === 'IN';
                if ($pt === 'OUT') return $s['status'] === 'COMPLETED';
                if ($pt === 'NO_OUT' || $pt === 'MISSING_OUT') return $s['status'] === 'NO_OUT' || !empty($s['auto_closed']);
                return true;
            }));
        }

        return array_slice($sessions, $offset, $limit);
    }

    public static function countPairedSessions(array $filters = []): int {
        $sessions = self::getPairedSessions($filters, 100000, 0);
        return count($sessions);
    }

    public static function count(array $filters = []): int {
        $sql = "SELECT COUNT(*) 
                FROM attendance a 
                JOIN employees e ON a.employee_id = e.id 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['employee_id'])) {
            $sql .= " AND a.employee_id = ?";
            $params[] = (int) $filters['employee_id'];
        }
        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = ?";
            $params[] = (int) $filters['department_id'];
        }
        if (!empty($filters['punch_type'])) {
            $sql .= " AND a.punch_type = ?";
            $params[] = $filters['punch_type'];
        }
        if (!empty($filters['date'])) {
            $sql .= " AND a.punch_date = ?";
            $params[] = $filters['date'];
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND a.punch_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND a.punch_date <= ?";
            $params[] = $filters['end_date'];
        }

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Dashboard statistics aggregation
     */
    public static function getDashboardStats(): array {
        $today = date('Y-m-d');
        
        $totalEmployees = (int) self::db()->query("SELECT COUNT(*) FROM employees WHERE status = 'active'")->fetchColumn();

        // Employees who punched IN today
        $stmt = self::db()->prepare("SELECT DISTINCT employee_id FROM attendance WHERE punch_date = ? AND punch_type = 'IN'");
        $stmt->execute([$today]);
        $punchedInEmployeeIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $presentCount = count($punchedInEmployeeIds);

        // Current status for each employee today (last punch today)
        $currentlyInCount = 0;
        $currentlyOutCount = 0;
        $noOutCount = 0;
        $nowTs = time();

        foreach ($punchedInEmployeeIds as $empId) {
            $lastPunch = self::getLatestPunch($empId, $today);
            if ($lastPunch && $lastPunch['punch_type'] === 'IN') {
                $emp = Employee::find($empId);
                $shiftEnd = !empty($emp['shift_end']) ? $emp['shift_end'] : '18:00:00';
                $shiftEndTs = strtotime($today . ' ' . $shiftEnd);
                if ($nowTs <= $shiftEndTs) {
                    $currentlyInCount++;
                } else {
                    $noOutCount++;
                }
            } else {
                $currentlyOutCount++;
            }
        }

        $absentCount = max(0, $totalEmployees - $presentCount);

        // Recent 10 live punches
        $recentPunches = self::all([], 10, 0);

        // Last 7 days attendance trend
        $trendDates = [];
        $trendCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $trendDates[] = date('D (d M)', strtotime($d));
            $stmt = self::db()->prepare("SELECT COUNT(DISTINCT employee_id) FROM attendance WHERE punch_date = ? AND punch_type = 'IN'");
            $stmt->execute([$d]);
            $trendCounts[] = (int) $stmt->fetchColumn();
        }

        return [
            'total_employees' => $totalEmployees,
            'present_today' => $presentCount,
            'currently_in' => $currentlyInCount,
            'currently_out' => $currentlyOutCount,
            'no_out_count' => $noOutCount,
            'absent_today' => $absentCount,
            'attendance_rate' => $totalEmployees > 0 ? round(($presentCount / $totalEmployees) * 100, 1) : 0,
            'recent_punches' => $recentPunches,
            'trend_dates' => $trendDates,
            'trend_counts' => $trendCounts
        ];
    }

    public static function getDailySummary(string $date, ?int $departmentId = null, ?string $statusFilter = null, ?string $site = null): array {
        $empSql = "SELECT e.*, d.name as department_name 
                   FROM employees e 
                   LEFT JOIN departments d ON e.department_id = d.id 
                   WHERE e.status = 'active'";
        $empParams = [];
        if ($departmentId) {
            $empSql .= " AND e.department_id = ?";
            $empParams[] = $departmentId;
        }
        if ($site) {
            $empSql .= " AND e.site LIKE ?";
            $empParams[] = '%' . $site . '%';
        }
        $empSql .= " ORDER BY e.name ASC";

        $stmt = self::db()->prepare($empSql);
        $stmt->execute($empParams);
        $employees = $stmt->fetchAll();

        $results = [];
        $statusFilterUpper = !empty($statusFilter) ? strtoupper(trim($statusFilter)) : null;
        $today = date('Y-m-d');
        $nowTs = time();

        foreach ($employees as $emp) {
            $punches = self::getDayPunches($emp['id'], $date);
            $firstIn = null;
            $lastOut = null;
            $totalPunches = count($punches);

            if ($totalPunches > 0) {
                foreach ($punches as $p) {
                    if ($p['punch_type'] === 'IN' && $firstIn === null) {
                        $firstIn = $p['punch_time'];
                    }
                    if ($p['punch_type'] === 'OUT') {
                        $lastOut = $p['punch_time'];
                    }
                }
            }

            $secondsWorked = self::calculateWorkSeconds($emp['id'], $date);
            $hoursWorked = round($secondsWorked / 3600, 2);

            $latestPunch = self::getLatestPunch($emp['id'], $date);
            $currentStatus = $latestPunch ? $latestPunch['punch_type'] : 'ABSENT';
            $shiftEnd = !empty($emp['shift_end']) ? $emp['shift_end'] : '18:00:00';
            $shiftEndTs = strtotime($date . ' ' . $shiftEnd);
            $isNoOut = false;
            $leaveInfo = null;

            // Check if employee is on leave today
            try {
                $pdo = self::db();
                $lStmt = $pdo->prepare("SELECT * FROM leaves WHERE employee_id = ? AND ? BETWEEN start_date AND end_date AND status = 'APPROVED' LIMIT 1");
                $lStmt->execute([$emp['id'], $date]);
                $leaveInfo = $lStmt->fetch(PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {}

            if ($totalPunches === 0) {
                if ($leaveInfo) {
                    $computedStatus = 'LEAVE';
                } else {
                    $computedStatus = 'ABSENT';
                }
            } elseif ($currentStatus === 'IN') {
                if ($date === $today && $nowTs <= $shiftEndTs) {
                    $computedStatus = 'CHECKED_IN';
                } else {
                    $computedStatus = 'NO_OUT_PUNCH';
                    $isNoOut = true;
                }
            } else {
                $computedStatus = 'COMPLETED';
            }

            // Filter check
            if ($statusFilterUpper) {
                if ($statusFilterUpper === 'IN' && $computedStatus !== 'CHECKED_IN') continue;
                if ($statusFilterUpper === 'CHECKED_IN' && $computedStatus !== 'CHECKED_IN') continue;
                if ($statusFilterUpper === 'OUT' && $computedStatus !== 'COMPLETED') continue;
                if ($statusFilterUpper === 'COMPLETED' && $computedStatus !== 'COMPLETED') continue;
                if (($statusFilterUpper === 'NO_OUT' || $statusFilterUpper === 'NO_OUT_PUNCH') && $computedStatus !== 'NO_OUT_PUNCH') continue;
                if ($statusFilterUpper === 'LEAVE' && $computedStatus !== 'LEAVE') continue;
                if ($statusFilterUpper === 'ABSENT' && $computedStatus !== 'ABSENT') continue;
            }

            $results[] = [
                'employee' => $emp,
                'status' => $computedStatus,
                'is_no_out' => $isNoOut,
                'leave_info' => $leaveInfo,
                'first_in' => $firstIn,
                'last_out' => $lastOut,
                'shift_end' => $shiftEnd,
                'punch_count' => $totalPunches,
                'seconds_worked' => $secondsWorked,
                'hours_worked' => $hoursWorked,
                'formatted_duration' => format_seconds($secondsWorked)
            ];
        }

        return $results;
    }

    /**
     * Monthly timesheet report for employees
     */
    public static function getMonthlyReport(int $year, int $month, ?int $departmentId = null, ?string $site = null): array {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $daysInMonth = (int) date('t', strtotime($startDate));
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        $empSql = "SELECT e.*, d.name as department_name 
                   FROM employees e 
                   LEFT JOIN departments d ON e.department_id = d.id 
                   WHERE e.status = 'active'";
        $empParams = [];
        if ($departmentId) {
            $empSql .= " AND e.department_id = ?";
            $empParams[] = $departmentId;
        }
        if (!empty($site)) {
            $empSql .= " AND e.site = ?";
            $empParams[] = $site;
        }
        $empSql .= " ORDER BY e.name ASC";

        $stmt = self::db()->prepare($empSql);
        $stmt->execute($empParams);
        $employees = $stmt->fetchAll();

        // Get leaves map for the month
        $leaveMap = [];
        try {
            $leaveMap = Leave::getLeaveMapForMonth((string)$year, (string)$month);
        } catch (\Throwable $e) {}

        $report = [];
        foreach ($employees as $emp) {
            $presentDays = 0;
            $leaveDays = 0;
            $totalWorkedSeconds = 0;
            $dailyStats = [];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $curDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
                $leaveKey = $emp['id'] . '_' . $curDate;
                $punches = self::getDayPunches($emp['id'], $curDate);

                if (count($punches) > 0) {
                    $seconds = self::calculateWorkSeconds($emp['id'], $curDate);
                    $firstIn = null;
                    $lastOut = null;
                    $inPhoto = null;
                    $outPhoto = null;

                    foreach ($punches as $p) {
                        if ($p['punch_type'] === 'IN' && $firstIn === null) {
                            $firstIn = $p['punch_time'];
                            $inPhoto = $p['punch_photo'] ?? null;
                        }
                        if ($p['punch_type'] === 'OUT') {
                            $lastOut = $p['punch_time'];
                            $outPhoto = $p['punch_photo'] ?? null;
                        }
                    }

                    $presentDays++;
                    $totalWorkedSeconds += $seconds;
                    
                    $latestPunch = self::getLatestPunch($emp['id'], $curDate);
                    $currentStatus = $latestPunch ? $latestPunch['punch_type'] : 'OUT';
                    $shiftEnd = !empty($emp['shift_end']) ? $emp['shift_end'] : '18:00:00';
                    $shiftEndTs = strtotime($curDate . ' ' . $shiftEnd);
                    $todayStr = date('Y-m-d');
                    $nowTs = time();

                    if ($currentStatus === 'IN') {
                        if ($curDate === $todayStr && $nowTs <= $shiftEndTs) {
                            $auditStatus = 'CHECKED_IN';
                        } else {
                            $auditStatus = 'NO_OUT';
                        }
                    } else {
                        $auditStatus = 'COMPLETED';
                    }

                    $dailyStats[$d] = [
                        'date' => $curDate,
                        'day_name' => date('D', strtotime($curDate)),
                        'status' => 'P',
                        'audit_status' => $auditStatus,
                        'first_in' => $firstIn ? date('h:i:s A', strtotime($firstIn)) : null,
                        'last_out' => $lastOut ? date('h:i:s A', strtotime($lastOut)) : null,
                        'in_photo' => $inPhoto,
                        'out_photo' => $outPhoto,
                        'punch_count' => count($punches),
                        'seconds' => $seconds,
                        'formatted_duration' => format_seconds($seconds),
                        'hours' => round($seconds / 3600, 2)
                    ];
                } elseif (isset($leaveMap[$leaveKey])) {
                    $lv = $leaveMap[$leaveKey];
                    $leaveDays++;
                    $dailyStats[$d] = [
                        'date' => $curDate,
                        'day_name' => date('D', strtotime($curDate)),
                        'status' => 'L',
                        'leave_code' => $lv['code'],
                        'leave_type' => $lv['type'],
                        'leave_reason' => $lv['reason'],
                        'origin_site' => $lv['origin_site'] ?? null,
                        'target_site' => $lv['target_site'] ?? null,
                        'audit_status' => 'LEAVE',
                        'first_in' => null,
                        'last_out' => null,
                        'in_photo' => null,
                        'out_photo' => null,
                        'punch_count' => 0,
                        'seconds' => 0,
                        'formatted_duration' => '00:00:00',
                        'hours' => 0
                    ];
                } else {
                    $dayOfWeek = date('N', strtotime($curDate));
                    $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);

                    $dailyStats[$d] = [
                        'date' => $curDate,
                        'day_name' => date('D', strtotime($curDate)),
                        'status' => $isWeekend ? 'W' : 'A',
                        'audit_status' => $isWeekend ? 'WEEKEND' : 'ABSENT',
                        'first_in' => null,
                        'last_out' => null,
                        'in_photo' => null,
                        'out_photo' => null,
                        'punch_count' => 0,
                        'seconds' => 0,
                        'formatted_duration' => '00:00:00',
                        'hours' => 0
                    ];
                }
            }

            $report[] = [
                'employee' => $emp,
                'present_days' => $presentDays,
                'leave_days' => $leaveDays,
                'total_worked_seconds' => $totalWorkedSeconds,
                'total_hours' => round($totalWorkedSeconds / 3600, 2),
                'formatted_total' => format_seconds($totalWorkedSeconds),
                'daily_stats' => $dailyStats
            ];
        }

        return [
            'year' => $year,
            'month' => $month,
            'days_in_month' => $daysInMonth,
            'data' => $report
        ];
    }
}
