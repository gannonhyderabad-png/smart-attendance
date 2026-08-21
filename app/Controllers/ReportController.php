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
            $emp = $row['employee'] ?? $row;
            $inTime = !empty($row['first_in']) ? date('h:i:s A', strtotime($row['first_in'])) : '—';
            $outTime = !empty($row['last_out']) ? date('h:i:s A', strtotime($row['last_out'])) : '—';
            
            $statusLabel = 'Absent';
            if ($row['status'] === 'CHECKED_IN' || $row['status'] === 'IN') {
                $statusLabel = 'Working Now (IN)';
            } elseif ($row['status'] === 'COMPLETED' || $row['status'] === 'OUT') {
                $statusLabel = 'Punched Out (Completed)';
            } elseif ($row['status'] === 'NO_OUT_PUNCH' || $row['status'] === 'NO_OUT' || !empty($row['is_no_out'])) {
                $statusLabel = 'Auto-Closed (No OUT)';
            }

            $dataRow = [
                $emp['employee_code'] ?? '',
                $emp['name'] ?? '',
                $emp['email'] ?? '—',
                $emp['phone'] ?? '—',
                $emp['department_name'] ?? ($emp['department'] ?? 'General'),
                $emp['designation'] ?? 'Staff',
                $emp['project'] ?? '—',
                $emp['site'] ?? '—',
                $statusLabel,
                $inTime,
                $outTime,
                $row['punch_count'] ?? ($row['total_punches'] ?? 0),
                $row['formatted_duration'] ?? ($row['work_duration_formatted'] ?? '00:00:00')
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
        $site = Request::input('site');

        $report = Attendance::getMonthlyReport($year, $month, $departmentId, $site);
        $departments = Department::all();
        $siteList = Employee::getDistinctSites();

        // Calculate Executive Summary Metrics
        $totalEmployees = count($report['data']);
        $totalPresentDays = 0;
        $totalHours = 0;
        $daysInMonth = $report['days_in_month'];

        foreach ($report['data'] as $r) {
            $totalPresentDays += (int) ($r['present_days'] ?? 0);
            $totalHours += (float) ($r['total_hours'] ?? 0);
        }

        $totalPossibleDays = $totalEmployees * $daysInMonth;
        $totalAbsentDays = max(0, $totalPossibleDays - $totalPresentDays);
        $attendanceRate = $totalPossibleDays > 0 ? round(($totalPresentDays / $totalPossibleDays) * 100, 1) : 0;

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);
        $totalPunchesStmt = \Database\Database::getConnection()->prepare("SELECT COUNT(*) FROM attendance WHERE punch_date >= ? AND punch_date <= ?");
        $totalPunchesStmt->execute([$startDate, $endDate]);
        $totalPunches = (int) $totalPunchesStmt->fetchColumn();

        $summary = [
            'total_employees' => $totalEmployees,
            'total_present_days' => $totalPresentDays,
            'total_absent_days' => $totalAbsentDays,
            'total_hours' => round($totalHours, 2),
            'total_punches' => $totalPunches,
            'attendance_rate' => $attendanceRate,
            'days_in_month' => $daysInMonth
        ];

        $this->view('reports.monthly', [
            'title' => 'Monthly Timesheet Report',
            'year' => $year,
            'month' => $month,
            'departmentId' => $departmentId,
            'site' => $site,
            'departments' => $departments,
            'siteList' => $siteList,
            'report' => $report,
            'summary' => $summary
        ], 'admin');
    }

    public function exportMonthlyCsv(): void {
        Auth::requireAuth();

        $year = (int) Request::input('year', date('Y'));
        $month = (int) Request::input('month', date('n'));
        $departmentId = Request::input('department_id') ? (int) Request::input('department_id') : null;
        $site = Request::input('site');

        $report = Attendance::getMonthlyReport($year, $month, $departmentId, $site);
        $daysInMonth = $report['days_in_month'];

        // Calculate Summary Metrics
        $totalEmployees = count($report['data']);
        $totalPresentDays = 0;
        $totalHours = 0;

        foreach ($report['data'] as $r) {
            $totalPresentDays += (int) ($r['present_days'] ?? 0);
            $totalHours += (float) ($r['total_hours'] ?? 0);
        }

        $totalPossibleDays = $totalEmployees * $daysInMonth;
        $totalAbsentDays = max(0, $totalPossibleDays - $totalPresentDays);
        $attendanceRate = $totalPossibleDays > 0 ? round(($totalPresentDays / $totalPossibleDays) * 100, 1) : 0;

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);
        $totalPunchesStmt = \Database\Database::getConnection()->prepare("SELECT COUNT(*) FROM attendance WHERE punch_date >= ? AND punch_date <= ?");
        $totalPunchesStmt->execute([$startDate, $endDate]);
        $totalPunches = (int) $totalPunchesStmt->fetchColumn();

        $filename = "monthly_timesheet_summary_{$year}_{$month}_" . date('His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // 1. Executive Summary Header Block
        fputcsv($output, ['========================================================================================================']);
        fputcsv($output, ['MONTHLY WORKFORCE ATTENDANCE SUMMARY & TIMESHEET REPORT']);
        fputcsv($output, ['Month Period', date('F Y', mktime(0, 0, 0, $month, 1, $year)), 'Generated At', date('d M Y, h:i:s A')]);
        fputcsv($output, ['Department Scope', $departmentId ? 'Filtered Department' : 'All Departments', 'Work Site Scope', $site ? $site : 'All Sites']);
        fputcsv($output, ['========================================================================================================']);
        fputcsv($output, []);

        // 2. KPI Summary Section (Flow / Stage Wise)
        fputcsv($output, ['--- WORKFORCE KEY PERFORMANCE SUMMARY ---']);
        fputcsv($output, ['Metric / Flow Indicator', 'Total Count', 'Unit / Details']);
        fputcsv($output, ['Total Registered Staff', $totalEmployees, 'Active Employees']);
        fputcsv($output, ['Total Present Days (P)', $totalPresentDays, 'Cumulative Present Days']);
        fputcsv($output, ['Total Absent Days (A)', $totalAbsentDays, 'Cumulative Absent Days']);
        fputcsv($output, ['Total Punches Recorded', $totalPunches, 'IN & OUT Verified Punches']);
        fputcsv($output, ['Total Workforce Work Hours', round($totalHours, 2) . ' hrs', 'Cumulative Productive Hours']);
        fputcsv($output, ['Overall Monthly Attendance Rate', $attendanceRate . '%', 'Attendance Ratio']);
        fputcsv($output, []);

        // 3. Flow Chart Process Breakdown
        fputcsv($output, ['--- ATTENDANCE FLOW CHART PROCESS SUMMARY ---']);
        fputcsv($output, ['Stage 1: PUNCH IN', '-> [Mobile/QR Scan] -> Employee Punches IN -> Live Camera Selfie Verified -> GPS Geofence Checked']);
        fputcsv($output, ['Stage 2: WORKING SESSION', '-> Live Session Duration Active -> Timer Recorded in Database']);
        fputcsv($output, ['Stage 3: PUNCH OUT', '-> Employee Punches OUT -> Session Closed -> Total Hours Computed -> Tagged Present (P)']);
        fputcsv($output, ['Stage 4: SHIFT AUDIT', '-> Auto-Calculation -> Daily & Monthly Summary Tables Aggregated']);
        fputcsv($output, []);

        // 4. Employee Detailed Matrix
        fputcsv($output, ['--- EMPLOYEE-WISE DAY-BY-DAY ATTENDANCE TIMESHEET ---']);
        $header = ['Emp Code', 'Employee Name', 'Department', 'Designation', 'Project', 'Work Site', 'Present (Days)', 'Absent (Days)', 'Total Hours'];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $header[] = sprintf('Day %02d', $d);
        }
        fputcsv($output, $header);

        // Data Rows
        foreach ($report['data'] as $row) {
            $emp = $row['employee'];
            $empPresent = (int) $row['present_days'];
            $empAbsent = max(0, $daysInMonth - $empPresent);

            $dataRow = [
                $emp['employee_code'],
                $emp['name'],
                $emp['department_name'] ?? 'N/A',
                $emp['designation'] ?? 'N/A',
                $emp['project'] ?? 'N/A',
                $emp['site'] ?? 'Main Office',
                $empPresent,
                $empAbsent,
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

    /**
     * Export Detailed Day-by-Day Employee Punch IN/OUT Time Audit Report
     */
    public function exportMonthlyAuditCsv(): void {
        Auth::requireAuth();

        $year = (int) Request::input('year', date('Y'));
        $month = (int) Request::input('month', date('n'));
        $departmentId = Request::input('department_id') ? (int) Request::input('department_id') : null;
        $site = Request::input('site');

        $report = Attendance::getMonthlyReport($year, $month, $departmentId, $site);
        $daysInMonth = $report['days_in_month'];

        $filename = "monthly_punch_in_out_audit_{$year}_{$month}_" . date('His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // 1. Audit Header Block
        fputcsv($output, ['========================================================================================================']);
        fputcsv($output, ['MONTHLY EMPLOYEE-WISE PUNCH IN/OUT & TIME AUDIT REPORT']);
        fputcsv($output, ['Month Period', date('F Y', mktime(0, 0, 0, $month, 1, $year)), 'Generated At', date('d M Y, h:i:s A')]);
        fputcsv($output, ['Department Scope', $departmentId ? 'Filtered' : 'All Departments', 'Work Site Scope', $site ? $site : 'All Sites']);
        fputcsv($output, ['========================================================================================================']);
        fputcsv($output, []);

        // 2. Audit Table Header
        $header = ['Date', 'Day', 'Employee Code', 'Employee Name', 'Department', 'Designation', 'Work Site', 'Punch IN Time', 'Punch OUT Time', 'Total Work Duration', 'Total Hours', 'Punch Count', 'Time Audit Status'];
        fputcsv($output, $header);

        // 3. Detailed Data Rows (Day-by-Day, Employee-by-Employee)
        foreach ($report['data'] as $row) {
            $emp = $row['employee'];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $stat = $row['daily_stats'][$d] ?? [];
                $curDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
                $dayName = date('D', strtotime($curDate));

                $auditLabel = match($stat['audit_status'] ?? 'ABSENT') {
                    'COMPLETED' => 'Completed (Verified IN & OUT)',
                    'CHECKED_IN' => 'Checked IN (Working)',
                    'NO_OUT' => 'Missing OUT Punch (Auto-Closed)',
                    'WEEKEND' => 'Weekend OFF',
                    default => 'Absent'
                };

                $dataRow = [
                    $curDate,
                    $dayName,
                    $emp['employee_code'],
                    $emp['name'],
                    $emp['department_name'] ?? 'N/A',
                    $emp['designation'] ?? 'N/A',
                    $emp['site'] ?? 'Main Office',
                    $stat['first_in'] ?? '—',
                    $stat['last_out'] ?? '—',
                    $stat['formatted_duration'] ?? '00:00:00',
                    ($stat['hours'] ?? 0) . 'h',
                    $stat['punch_count'] ?? 0,
                    $auditLabel
                ];

                fputcsv($output, $dataRow);
            }
        }

        fclose($output);
        exit;
    }
}
