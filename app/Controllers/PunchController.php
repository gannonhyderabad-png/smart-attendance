<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Logger;
use App\Models\Employee;
use App\Models\Attendance;

class PunchController extends Controller {
    /**
     * Mobile Punch Page View
     */
    public function show(string $code): void {
        $employee = Employee::findByCode($code);

        if (!$employee) {
            $this->view('punch.invalid', [
                'title' => 'Employee Not Found',
                'message' => "The employee code '{$code}' was not found in the system. Please verify the URL provided by your administrator."
            ], 'mobile');
            return;
        }

        if ($employee['status'] !== 'active') {
            $this->view('punch.invalid', [
                'title' => 'Account Inactive',
                'message' => "The account for {$employee['name']} ({$employee['employee_code']}) is currently marked inactive. Please contact HR or your manager."
            ], 'mobile');
            return;
        }

        $today = date('Y-m-d');
        $latestPunch = Attendance::getLatestPunch($employee['id'], $today);
        $todayPunches = Attendance::getDayPunches($employee['id'], $today);
        $workedSeconds = Attendance::calculateWorkSeconds($employee['id'], $today);

        // Check if employee has an active leave or OD entry today
        $todayLeave = null;
        $activeLeaves = \App\Models\Leave::all([
            'employee_id' => $employee['id'],
            'month_start' => $today,
            'month_end' => $today,
            'status' => 'APPROVED'
        ]);
        if (!empty($activeLeaves)) {
            $todayLeave = $activeLeaves[0];
        }

        $this->view('punch.index', [
            'employee' => $employee,
            'latestPunch' => $latestPunch,
            'todayPunches' => $todayPunches,
            'workedSeconds' => $workedSeconds,
            'currentStatus' => $currentStatus,
            'isNoOutAutoClosed' => $isNoOutAutoClosed,
            'todayLeave' => $todayLeave,
            'clientIp' => Request::getClientIp(),
            'deviceInfo' => parse_user_agent_details(Request::getUserAgent())
        ], 'mobile');
    }

    /**
     * AJAX/Form Punch Action (IN or OUT)
     */
    public function record(string $code): void {
        $employee = Employee::findByCode($code);

        if (!$employee) {
            $this->json(['success' => false, 'message' => 'Employee not found'], 404);
            return;
        }

        if ($employee['status'] !== 'active') {
            $this->json(['success' => false, 'message' => 'Employee account is inactive'], 403);
            return;
        }

        $type = strtoupper(Request::input('punch_type', ''));
        $latitude = Request::input('latitude');
        $longitude = Request::input('longitude');
        $photoData = Request::input('photo_data');
        $notes = Request::input('notes');

        $ip = Request::getClientIp();
        $ua = Request::getUserAgent();
        $deviceInfo = parse_user_agent_details($ua);

        $result = Attendance::recordPunch($employee['id'], $type, [
            'project' => $employee['project'] ?? null,
            'site' => $employee['site'] ?? null,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'device_info' => $deviceInfo,
            'latitude' => !empty($latitude) ? (float) $latitude : null,
            'longitude' => !empty($longitude) ? (float) $longitude : null,
            'photo_data' => !empty($photoData) ? $photoData : null,
            'notes' => !empty($notes) ? trim($notes) : null
        ]);

        if ($result['success']) {
            Logger::log(null, 'EMPLOYEE_PUNCH_' . $type, "Employee {$employee['name']} ({$employee['employee_code']}) punched {$type} from {$ip}");
            
            // Recalculate today's stats
            $today = date('Y-m-d');
            $workedSeconds = Attendance::calculateWorkSeconds($employee['id'], $today);
            $result['worked_seconds'] = $workedSeconds;
            $result['formatted_duration'] = format_seconds($workedSeconds);
            $result['today_punches'] = Attendance::getDayPunches($employee['id'], $today);
        }

        if (Request::isAjax()) {
            $this->json($result, $result['success'] ? 200 : 422);
        } else {
            if ($result['success']) {
                $this->setFlash('success', $result['message']);
            } else {
                $this->setFlash('error', $result['message']);
            }
            redirect('p/' . rawurlencode($code));
        }
    }

    /**
     * Real-time status polling for mobile view
     */
    public function status(string $code): void {
        $employee = Employee::findByCode($code);

        if (!$employee) {
            $this->json(['success' => false, 'message' => 'Employee not found'], 404);
            return;
        }

        $today = date('Y-m-d');
        $latestPunch = Attendance::getLatestPunch($employee['id'], $today);
        $workedSeconds = Attendance::calculateWorkSeconds($employee['id'], $today);

        $this->json([
            'success' => true,
            'employee' => [
                'name' => $employee['name'],
                'code' => $employee['employee_code'],
                'department' => $employee['department_name'] ?? 'General'
            ],
            'current_status' => $latestPunch ? $latestPunch['punch_type'] : 'OUT',
            'latest_punch_time' => $latestPunch ? $latestPunch['punch_time'] : null,
            'worked_seconds' => $workedSeconds,
            'formatted_duration' => format_seconds($workedSeconds),
            'server_time' => date('Y-m-d H:i:s')
        ]);
    }
}
