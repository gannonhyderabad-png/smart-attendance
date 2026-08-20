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
}
