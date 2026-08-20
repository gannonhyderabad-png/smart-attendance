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
}
