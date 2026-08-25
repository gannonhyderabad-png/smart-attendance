<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Csrf;
use App\Core\Logger;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Leave;
use App\Models\Holiday;

class AttendanceController extends Controller {
    public function index(): void {
        Auth::requireAuth();

        $filters = [
            'employee_id' => Request::input('employee_id', ''),
            'department_id' => Request::input('department_id', ''),
            'project' => Request::input('project', ''),
            'site' => Request::input('site', ''),
            'punch_type' => Request::input('punch_type', ''),
            'start_date' => Request::input('start_date', date('Y-m-d', strtotime('-7 days'))),
            'end_date' => Request::input('end_date', date('Y-m-d')),
            'search' => Request::input('search', '')
        ];

        $page = max(1, (int) Request::input('page', 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $records = Attendance::getPairedSessions($filters, $limit, $offset);
        $totalRecords = Attendance::countPairedSessions($filters);
        $totalPages = max(1, ceil($totalRecords / $limit));

        $employees = Employee::all(['status' => 'active']);
        $departments = Department::all();
        $departmentList = Employee::getDistinctDepartments();
        $siteList = Employee::getDistinctSites();
        $projectList = Employee::getDistinctProjects();

        $this->view('attendance.index', [
            'title' => 'Attendance Records',
            'records' => $records,
            'employees' => $employees,
            'departments' => $departments,
            'departmentList' => $departmentList,
            'siteList' => $siteList,
            'projectList' => $projectList,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalRecords' => $totalRecords
        ], 'admin');
    }

    public function exportCsv(): void {
        Auth::requireAuth();

        $filters = [
            'employee_id' => Request::input('employee_id', ''),
            'department_id' => Request::input('department_id', ''),
            'project' => Request::input('project', ''),
            'site' => Request::input('site', ''),
            'punch_type' => Request::input('punch_type', ''),
            'start_date' => Request::input('start_date', date('Y-m-d', strtotime('-30 days'))),
            'end_date' => Request::input('end_date', date('Y-m-d')),
            'search' => Request::input('search', '')
        ];

        $records = Attendance::getPairedSessions($filters, 5000, 0);

        $filename = "attendance_paired_export_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'Employee Code', 'Employee Name', 'Department', 'Project', 'Site', 'Punch IN Time', 'Punch OUT Time', 'Total Work Duration', 'Status', 'IP Address', 'Device / Browser']);

        foreach ($records as $row) {
            fputcsv($output, [
                $row['punch_date'],
                $row['employee_code'],
                $row['employee_name'],
                $row['department_name'] ?? 'N/A',
                $row['project'] ?? 'N/A',
                $row['site'] ?? 'N/A',
                $row['in_time'] ? date('h:i:s A', strtotime($row['in_time'])) : '—',
                $row['out_time'] ? date('h:i:s A', strtotime($row['out_time'])) : ($row['status'] === 'IN' ? 'Working Now' : '—'),
                $row['formatted_duration'],
                $row['status'] === 'IN' ? 'Working Now' : 'Completed',
                $row['in_ip'] ?: ($row['out_ip'] ?? '127.0.0.1'),
                $row['in_device'] ?: ($row['out_device'] ?? '—')
            ]);
        }

        fclose($output);
        exit;
    }

    public function manualStore(): void {
        Auth::requireAuth();
        Csrf::verify();

        $entryType = trim(Request::input('entry_type', 'punch'));
        $employeeInput = Request::input('employee_id');
        $punchDate = trim(Request::input('punch_date', date('Y-m-d')));
        $endDate = trim(Request::input('end_date', ''));
        $inTime = trim(Request::input('in_time', ''));
        $outTime = trim(Request::input('out_time', ''));
        $project = trim(Request::input('project', ''));
        $site = trim(Request::input('site', ''));
        $leaveType = trim(Request::input('leave_type', 'Casual Leave'));
        $holidayTitle = trim(Request::input('holiday_title', ''));
        $notes = trim(Request::input('notes', ''));

        // --- MODE 1: PUBLIC HOLIDAY ENTRY ---
        if ($entryType === 'holiday') {
            if (empty($holidayTitle) || empty($punchDate)) {
                $_SESSION['flash_error'] = 'Holiday title and date are required.';
                redirect('attendance');
            }

            try {
                Holiday::create([
                    'title' => $holidayTitle,
                    'holiday_date' => $punchDate,
                    'description' => $notes ?: 'Official Public Holiday'
                ]);

                Logger::log(Auth::id(), 'HOLIDAY_CREATED', "Added public holiday '{$holidayTitle}' on {$punchDate} via Manual Portal");
                $_SESSION['flash_success'] = "Public Holiday '{$holidayTitle}' on {$punchDate} created successfully!";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'Failed to add public holiday: ' . $e->getMessage();
            }
            redirect('attendance');
        }

        // Validate employee for Punch and Leave modes
        $employee = Employee::resolveEmployee($employeeInput);
        if (!$employee) {
            $_SESSION['flash_error'] = 'Selected employee was not found. Please type a valid employee code or name.';
            redirect('attendance');
        }
        $employeeId = (int) $employee['id'];

        // --- MODE 2: EMPLOYEE LEAVE ENTRY ---
        if ($entryType === 'leave') {
            if (empty($punchDate)) {
                $_SESSION['flash_error'] = 'Leave date is required.';
                redirect('attendance');
            }
            if (empty($endDate)) {
                $endDate = $punchDate;
            }

            try {
                Leave::create([
                    'employee_id' => $employeeId,
                    'leave_type' => $leaveType,
                    'start_date' => $punchDate,
                    'end_date' => $endDate,
                    'reason' => $notes,
                    'status' => 'APPROVED'
                ]);

                $dateRange = ($punchDate === $endDate) ? $punchDate : "{$punchDate} to {$endDate}";
                Logger::log(Auth::id(), 'LEAVE_RECORDED', "Recorded {$leaveType} for {$employee['name']} ({$employee['employee_code']}) for {$dateRange}");
                $_SESSION['flash_success'] = "Leave ({$leaveType}) for {$employee['name']} for {$dateRange} recorded successfully!";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'Failed to save leave entry: ' . $e->getMessage();
            }
            redirect('attendance');
        }

        // --- MODE 3: STANDARD ATTENDANCE PUNCH (IN & OUT) ---
        if (empty($punchDate) || empty($inTime)) {
            $_SESSION['flash_error'] = 'Punch date and Punch IN time are required.';
            redirect('attendance');
        }

        // Use employee default project/site if not specified
        if (empty($project)) $project = $employee['project'] ?? 'General';
        if (empty($site)) $site = $employee['site'] ?? 'Office';

        $notesFormatted = 'Manual Entry: ' . ($notes ?: 'Admin Correction');

        try {
            // 1. Create Punch IN record
            $fullInDateTime = $punchDate . ' ' . (strlen($inTime) === 5 ? $inTime . ':00' : $inTime);
            Attendance::create([
                'employee_id' => $employeeId,
                'punch_type' => 'IN',
                'punch_time' => $fullInDateTime,
                'punch_date' => $punchDate,
                'project' => $project,
                'site' => $site,
                'latitude' => $employee['site_latitude'] ?? null,
                'longitude' => $employee['site_longitude'] ?? null,
                'distance_meters' => 0,
                'location_verified' => 1,
                'ip_address' => 'Manual Admin',
                'device_info' => 'Admin Manual Portal',
                'notes' => $notesFormatted
            ]);

            // 2. Create Punch OUT record if out_time provided
            if (!empty($outTime)) {
                $fullOutDateTime = $punchDate . ' ' . (strlen($outTime) === 5 ? $outTime . ':00' : $outTime);
                Attendance::create([
                    'employee_id' => $employeeId,
                    'punch_type' => 'OUT',
                    'punch_time' => $fullOutDateTime,
                    'punch_date' => $punchDate,
                    'project' => $project,
                    'site' => $site,
                    'latitude' => $employee['site_latitude'] ?? null,
                    'longitude' => $employee['site_longitude'] ?? null,
                    'distance_meters' => 0,
                    'location_verified' => 1,
                    'ip_address' => 'Manual Admin',
                    'device_info' => 'Admin Manual Portal',
                    'notes' => $notesFormatted
                ]);
            }

            Logger::log(Auth::id(), 'MANUAL_ATTENDANCE', "Added manual attendance for {$employee['name']} ({$employee['employee_code']}) on {$punchDate} (IN: {$inTime}" . ($outTime ? ", OUT: {$outTime}" : "") . ")");
            $_SESSION['flash_success'] = "Manual attendance entry for {$employee['name']} recorded successfully!";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to save manual attendance: ' . $e->getMessage();
        }

        redirect('attendance');
    }

    public function delete(): void {
        Auth::requireAuth();
        Csrf::verify();

        $id = (int) Request::input('id', 0);
        $employeeId = (int) Request::input('employee_id', 0);
        $punchDate = trim(Request::input('punch_date', ''));

        try {
            if ($employeeId > 0 && !empty($punchDate)) {
                // Delete full daily session for that employee and date
                $pdo = \Database\Database::getConnection();
                $stmt = $pdo->prepare("DELETE FROM attendance WHERE employee_id = ? AND punch_date = ?");
                $stmt->execute([$employeeId, $punchDate]);
                Logger::log(Auth::id(), 'ATTENDANCE_DELETED', "Deleted attendance session for employee ID {$employeeId} on {$punchDate}");
                $_SESSION['flash_success'] = "Attendance session deleted successfully.";
            } elseif ($id > 0) {
                Attendance::delete($id);
                Logger::log(Auth::id(), 'ATTENDANCE_DELETED', "Deleted attendance record #{$id}");
                $_SESSION['flash_success'] = "Attendance record deleted successfully.";
            }
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to delete attendance record: ' . $e->getMessage();
        }

        redirect('attendance');
    }
}
