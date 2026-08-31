<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Employee;
use App\Models\Attendance;

class ApiController extends Controller {
    /**
     * REST API Punch Endpoint
     * POST /api/punch
     * JSON payload: { "employee_code": "EMP001", "punch_type": "IN", "latitude": 0, "longitude": 0, "notes": "" }
     */
    public function punch(): void {
        $code = trim(Request::input('employee_code', ''));
        $type = strtoupper(trim(Request::input('punch_type', '')));

        if (empty($code) || empty($type)) {
            $this->json([
                'success' => false,
                'message' => 'Missing required fields: employee_code, punch_type'
            ], 400);
            return;
        }

        $employee = Employee::findByCode($code);
        if (!$employee) {
            $this->json(['success' => false, 'message' => 'Employee not found'], 404);
            return;
        }

        if ($employee['status'] !== 'active') {
            $this->json(['success' => false, 'message' => 'Employee account is inactive'], 403);
            return;
        }

        $ip = Request::getClientIp();
        $ua = Request::getUserAgent();

        $result = Attendance::recordPunch($employee['id'], $type, [
            'ip_address' => $ip,
            'user_agent' => $ua,
            'device_info' => parse_user_agent_details($ua),
            'latitude' => Request::input('latitude'),
            'longitude' => Request::input('longitude'),
            'notes' => Request::input('notes')
        ]);

        $this->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * GET /api/employee/{code}
     */
    public function employeeStatus(string $code): void {
        $employee = Employee::findByCode($code);
        if (!$employee) {
            $this->json(['success' => false, 'message' => 'Employee not found'], 404);
            return;
        }

        $today = date('Y-m-d');
        $latest = Attendance::getLatestPunch($employee['id'], $today);
        $workedSeconds = Attendance::calculateWorkSeconds($employee['id'], $today);

        $this->json([
            'success' => true,
            'employee' => [
                'id' => $employee['id'],
                'employee_code' => $employee['employee_code'],
                'name' => $employee['name'],
                'email' => $employee['email'],
                'department' => $employee['department_name'] ?? 'General',
                'designation' => $employee['designation'],
                'status' => $employee['status'],
                'punch_url' => punch_url($employee['employee_code'])
            ],
            'attendance' => [
                'current_status' => $latest ? $latest['punch_type'] : 'OUT',
                'latest_punch_time' => $latest ? $latest['punch_time'] : null,
                'seconds_worked_today' => $workedSeconds,
                'formatted_work_today' => format_seconds($workedSeconds)
            ]
        ]);
    }

    /**
     * GET /api/attendance/summary
     */
    public function summary(): void {
        $stats = Attendance::getDashboardStats();
        $this->json(['success' => true, 'data' => $stats]);
    }

    /**
     * GET /api/sync
     * Real-time sync endpoint for local PC auto-sync tool
     */
    public function sync(): void {
        $lastId = (int) Request::input('last_id', 0);
        $pdo = \Database\Database::getConnection();

        $stmt = $pdo->prepare("SELECT a.*, e.name as employee_name, e.employee_code 
                               FROM attendance a 
                               JOIN employees e ON a.employee_id = e.id 
                               WHERE a.id > ? 
                               ORDER BY a.id ASC LIMIT 100");
        $stmt->execute([$lastId]);
        $punches = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $items = [];
        foreach ($punches as $p) {
            $photoPath = $p['punch_photo'] ?? null;
            $photoBase64 = null;
            if (!empty($photoPath)) {
                $fullPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($photoPath, '/\\'));
                if (file_exists($fullPath)) {
                    $photoBase64 = base64_encode(file_get_contents($fullPath));
                }
            }
            $p['photo_base64'] = $photoBase64;
            $items[] = $p;
        }

        $this->json([
            'success' => true,
            'count' => count($items),
            'latest_id' => !empty($items) ? end($items)['id'] : $lastId,
            'punches' => $items,
            'server_time' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Resolve Google Maps shortlinks, share links, coordinates, or addresses
     * POST or GET /api/resolve-location
     */
    public function resolveLocation(): void {
        $input = trim(Request::input('query', Request::input('url', '')));
        if (empty($input)) {
            $this->json(['success' => false, 'message' => 'Please provide a location query, link, or coordinates.'], 400);
            return;
        }

        // 1. Direct Decimal Coordinates check: e.g. "17.437462, 78.448251"
        if (preg_match('/(-?\d{1,2}\.\d+)[,\s]+(-?\d{1,3}\.\d+)/', $input, $m)) {
            $lat = (float) $m[1];
            $lon = (float) $m[2];
            if ($lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180) {
                $this->json([
                    'success' => true,
                    'lat' => round($lat, 6),
                    'lon' => round($lon, 6),
                    'formatted_address' => "Coordinates ({$lat}, {$lon})",
                    'type' => 'coordinates'
                ]);
                return;
            }
        }

        // 2. Google Maps URL or Shortlink Resolver (maps.app.goo.gl, goo.gl/maps, google.com/maps)
        if (stripos($input, 'http://') === 0 || stripos($input, 'https://') === 0 || stripos($input, 'maps.app.goo.gl') !== false || stripos($input, 'goo.gl/maps') !== false || stripos($input, 'google.com/maps') !== false || stripos($input, 'maps.google.com') !== false) {
            $url = $input;
            if (stripos($url, 'http') !== 0) {
                $url = 'https://' . $url;
            }

            $effectiveUrl = $url;
            $response = '';

            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
                curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch) ?: '';
                $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: $url;
                curl_close($ch);
            }

            // Check effective resolved URL for coordinates
            $combined = $effectiveUrl . ' ' . $response;

            // Pattern A: @lat,lon
            if (preg_match('/@(-?\d{1,2}\.\d+),(-?\d{1,3}\.\d+)/', $combined, $m)) {
                $this->json([
                    'success' => true,
                    'lat' => round((float) $m[1], 6),
                    'lon' => round((float) $m[2], 6),
                    'formatted_address' => 'Google Maps Location',
                    'type' => 'google_maps_link',
                    'resolved_url' => $effectiveUrl
                ]);
                return;
            }

            // Pattern B: !3dlat!4dlon
            if (preg_match('/!3d(-?\d{1,2}\.\d+)!4d(-?\d{1,3}\.\d+)/', $combined, $m)) {
                $this->json([
                    'success' => true,
                    'lat' => round((float) $m[1], 6),
                    'lon' => round((float) $m[2], 6),
                    'formatted_address' => 'Google Maps Place Pin',
                    'type' => 'google_maps_link',
                    'resolved_url' => $effectiveUrl
                ]);
                return;
            }

            // Pattern C: ?q=lat,lon or /search/lat,lon or /place/lat,lon
            if (preg_match('/(?:[?&](?:q|ll|query)=|\/place\/|\/search\/)(-?\d{1,2}\.\d+)[,\s]+(-?\d{1,3}\.\d+)/', $combined, $m)) {
                $this->json([
                    'success' => true,
                    'lat' => round((float) $m[1], 6),
                    'lon' => round((float) $m[2], 6),
                    'formatted_address' => 'Google Maps Location',
                    'type' => 'google_maps_link',
                    'resolved_url' => $effectiveUrl
                ]);
                return;
            }
        }

        // 3. Search / Geocode Address via OpenStreetMap Nominatim
        $geoUrl = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($input) . '&limit=1';
        $geoRes = '';
        if (function_exists('curl_init')) {
            $ch = curl_init($geoUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'SmartAttendanceApp/2.0 (admin@attendance.local)');
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $geoRes = curl_exec($ch) ?: '';
            curl_close($ch);
        } else {
            $geoRes = @file_get_contents($geoUrl);
        }

        if ($geoRes) {
            $data = json_decode($geoRes, true);
            if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                $this->json([
                    'success' => true,
                    'lat' => round((float) $data[0]['lat'], 6),
                    'lon' => round((float) $data[0]['lon'], 6),
                    'formatted_address' => $data[0]['display_name'],
                    'type' => 'geocoded_address'
                ]);
                return;
            }
        }

        $this->json([
            'success' => false,
            'message' => 'Could not determine exact GPS coordinates from this input. Please copy and paste the decimal coordinates directly from Google Maps (e.g. 17.437462, 78.448251) or drag the pin on the map.'
        ], 422);
    }

    /**
     * Bulk Biometric Push from Local eSSL Sync Service / Python Bridge
     * POST /api/biometric/push
     */
    public function pushBiometric(): void {
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (empty($payload)) {
            $payload = $_POST;
        }

        $sn = trim($payload['serial_number'] ?? ($payload['sn'] ?? ''));
        $ip = trim($payload['device_ip'] ?? ($payload['ip'] ?? Request::getClientIp()));
        $punches = $payload['punches'] ?? [];

        // If single punch sent
        if (empty($punches) && !empty($payload['employee_code'])) {
            $punches = [$payload];
        }

        if (empty($punches)) {
            $this->json(['success' => false, 'message' => 'No punches provided'], 400);
            return;
        }

        if (!empty($sn) || !empty($ip)) {
            \App\Models\Device::registerOrHeartbeat($sn ?: 'DEV-' . str_replace('.', '', $ip), [
                'ip_address' => $ip,
                'device_name' => "eSSL Device ({$ip})"
            ]);
        }

        $device = !empty($sn) ? \App\Models\Device::findBySN($sn) : null;
        $site = $device['site'] ?? 'Head Office';
        $project = $device['project'] ?? 'General';

        $saved = 0;
        $pdo = \Database\Database::getConnection();

        foreach ($punches as $p) {
            $code = trim($p['employee_code'] ?? ($p['user_id'] ?? ($p['pin'] ?? '')));
            $time = trim($p['punch_time'] ?? ($p['timestamp'] ?? date('Y-m-d H:i:s')));
            $type = strtoupper(trim($p['punch_type'] ?? ''));

            if (empty($code)) continue;

            $emp = Employee::findByCode($code);
            if (!$emp && is_numeric($code)) {
                $emp = Employee::find((int)$code);
            }
            if (!$emp) {
                $unpadded = ltrim($code, '0');
                if (!empty($unpadded)) {
                    $emp = Employee::findByCode($unpadded);
                }
            }

            if (!$emp) continue;

            $date = date('Y-m-d', strtotime($time));

            if (empty($type) || !in_array($type, ['IN', 'OUT'])) {
                $latest = Attendance::getLatestPunch($emp['id'], $date);
                $type = ($latest && $latest['punch_type'] === 'IN') ? 'OUT' : 'IN';
            }

            // Check duplicate
            $chk = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND punch_type = ? AND punch_time = ? LIMIT 1");
            $chk->execute([$emp['id'], $type, $time]);
            if ($chk->fetch()) {
                $saved++;
                continue;
            }

            Attendance::create([
                'employee_id' => $emp['id'],
                'punch_type' => $type,
                'punch_time' => $time,
                'punch_date' => $date,
                'project' => $emp['project'] ?? $project,
                'site' => $emp['site'] ?? $site,
                'latitude' => $emp['site_latitude'] ?? null,
                'longitude' => $emp['site_longitude'] ?? null,
                'distance_meters' => 0,
                'location_verified' => 1,
                'ip_address' => $ip,
                'device_info' => "eSSL Biometric [{$ip}]",
                'notes' => "eSSL LAN Sync (Device: {$ip})"
            ]);
            $saved++;
        }

        $this->json([
            'success' => true,
            'message' => "Successfully processed {$saved} biometric punches",
            'saved_count' => $saved
        ]);
    }
}
