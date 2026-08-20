<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;

class ReportController extends Controller {
    public function daily(): void {
        Auth::requireAuth();

        $date = Request::input('date', date('Y-m-d'));
        $departmentId = Request::input('department_id') ? (int) Request::input('department_id') : null;
        $status = Request::input('status');
        $site = Request::input('site');

        $summary = Attendance::getDailySummary($date, $departmentId, $status, $site);
        $departments = Department::all();
        $departmentList = Employee::getDistinctDepartments();
        $siteList = Employee::getDistinctSites();

        $this->view('reports.daily', [
            'title' => 'Daily Attendance Summary',
            'date' => $date,
            'departmentId' => $departmentId,
            'status' => $status,
            'site' => $site,
            'departments' => $departments,
            'departmentList' => $departmentList,
            'siteList' => $siteList,
            'summary' => $summary
        ], 'admin');
    }

    public function exportDailyCsv(): void {
        Auth::requireAuth();

        $date = Request::input('date', date('Y-m-d'));
        $departmentId = Request::input('department_id') ? (int) Request::input('department_id') : null;
        $status = Request::input('status');
        $site = Request::input('site');

        $summary = Attendance::getDailySummary($date, $departmentId, $status, $site);
        $filename = "daily_attendance_summary_{$date}.csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // Add UTF-8 BOM so Excel properly opens with unicode
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header Row
        $header = [
            'Employee Code',
            'Employee Name',
            'Email',
            'Phone',
            'Department',
            'Job Role',
            'Assigned Project',
            'Work Site',
            'Attendance Status',
            'First Check IN',
            'Last Check OUT',
            'Total Punches',
            'Total Worked Hours'
        ];
        fputcsv($output, $header);

        // Data Rows
        foreach ($summary as $row) {
            $inTime = !empty($row['first_in']) ? date('h:i:s A', strtotime($row['first_in'])) : '—';
            $outTime = !empty($row['last_out']) ? date('h:i:s A', strtotime($row['last_out'])) : '—';
            
            $statusLabel = 'Absent';
            if ($row['status'] === 'IN') {
                $statusLabel = 'Working Now (IN)';
            } elseif ($row['status'] === 'OUT') {
                $statusLabel = 'Punched Out (Completed)';
            } elseif ($row['status'] === 'NO_OUT_PUNCH' || $row['status'] === 'NO_OUT') {
                $statusLabel = 'Auto-Closed (No OUT)';
            }

            $dataRow = [
                $row['employee_code'],
                $row['employee_name'],
                $row['email'] ?? '—',
                $row['phone'] ?? '—',
                $row['department_name'] ?? ($row['department'] ?? 'General'),
                $row['designation'] ?? 'Staff',
                $row['project'] ?? '—',
                $row['site'] ?? '—',
                $statusLabel,
                $inTime,
                $outTime,
                $row['total_punches'] ?? 0,
                $row['work_duration_formatted'] ?? '00:00:00'
            ];
            fputcsv($output, $dataRow);
        }

        fclose($output);
        exit;
    }

    public function monthly(): void {
        Auth::requireAuth();

        $year = (int) Request::input('year', date('Y'));
        $month = (int) Request::input('month', date('n'));
        $departmentId = Request::input('department_id') ? (int) Request::input('department_id') : null;

        $report = Attendance::getMonthlyReport($year, $month, $departmentId);
        $departments = Department::all();

        $this->view('reports.monthly', [
            'title' => 'Monthly Timesheet Report',
            'year' => $year,
            'month' => $month,
            'departmentId' => $departmentId,
            'departments' => $departments,
            'report' => $report
        ], 'admin');
    }

    public function exportMonthlyCsv(): void {
        Auth::requireAuth();

        $year = (int) Request::input('year', date('Y'));
        $month = (int) Request::input('month', date('n'));
        $departmentId = Request::input('department_id') ? (int) Request::input('department_id') : null;

        $report = Attendance::getMonthlyReport($year, $month, $departmentId);
        $daysInMonth = $report['days_in_month'];

        $filename = "monthly_timesheet_{$year}_{$month}_" . date('His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Header Row
        $header = ['Emp Code', 'Employee Name', 'Department', 'Designation', 'Project', 'Present Days', 'Total Hours'];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $header[] = sprintf('%02d', $d);
        }
        fputcsv($output, $header);

        // Data Rows
        foreach ($report['data'] as $row) {
            $emp = $row['employee'];
            $dataRow = [
                $emp['employee_code'],
                $emp['name'],
                $emp['department_name'] ?? 'N/A',
                $emp['designation'] ?? 'N/A',
                $emp['project'] ?? 'N/A',
                $row['present_days'],
                $row['total_hours'] . 'h'
            ];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $stat = $row['daily_stats'][$d] ?? ['status' => '-', 'hours' => 0];
                if ($stat['status'] === 'P') {
                    $dataRow[] = $stat['hours'] > 0 ? "P ({$stat['hours']}h)" : 'P';
                } elseif ($stat['status'] === 'W') {
                    $dataRow[] = 'OFF';
                } else {
                    $dataRow[] = 'A';
                }
            }

            fputcsv($output, $dataRow);
        }

        fclose($output);
        exit;
    }
}
