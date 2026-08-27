<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Setting;

class ReportController extends Controller {
    /**
     * Unified Attendance Reports Hub
     */
    public function index(): void {
        Auth::requireAuth();

        $reportType = Request::input('report_type', 'daily');
        $date = Request::input('date', date('Y-m-d'));
        $year = (int) Request::input('year', date('Y'));
        $month = (int) Request::input('month', date('n'));
        $startDate = Request::input('start_date', date('Y-m-d', strtotime('-6 days')));
        $endDate = Request::input('end_date', date('Y-m-d'));
        $employeeId = Request::input('employee_id') ? (int) Request::input('employee_id') : null;
        $departmentId = Request::input('department_id') ? (int) Request::input('department_id') : null;
        $status = Request::input('status');
        $site = Request::input('site');

        $employees = Employee::all(['status' => 'active']);
        $departments = Department::all();
        $departmentList = Employee::getDistinctDepartments();
        $siteList = Employee::getDistinctSites();
        $holidaysMap = Holiday::getMapForMonth((string)$year, (string)$month);
        $holidaysList = Holiday::getForMonth((string)$year, (string)$month);

        // Data containers based on active tab
        $dailySummary = [];
        $weeklyReport = [];
        $monthlyReport = [];
        $employeeReport = [];
        $monthlySummary = [];

        if ($reportType === 'daily') {
            $dailySummary = Attendance::getDailySummary($date, $departmentId, $status, $site);
        } elseif ($reportType === 'weekly') {
            $weeklyReport = Attendance::getWeeklyReport($startDate, $endDate, $departmentId, $site);
        } elseif ($reportType === 'monthly') {
            $monthlyReport = Attendance::getMonthlyReport($year, $month, $departmentId, $site);
            $daysInMonth = $monthlyReport['days_in_month'] ?? 30;
            $totalEmployees = count($monthlyReport['data'] ?? []);
            $totalPresentDays = 0;
            $totalHours = 0;
            foreach ($monthlyReport['data'] ?? [] as $r) {
                $totalPresentDays += (int) ($r['present_days'] ?? 0);
                $totalHours += (float) ($r['total_hours'] ?? 0);
            }
            $totalPossibleDays = $totalEmployees * $daysInMonth;
            $totalAbsentDays = max(0, $totalPossibleDays - $totalPresentDays);
            $attendanceRate = $totalPossibleDays > 0 ? round(($totalPresentDays / $totalPossibleDays) * 100, 1) : 0;
            
            $monthlySummary = [
                'total_employees' => $totalEmployees,
                'total_present_days' => $totalPresentDays,
                'total_absent_days' => $totalAbsentDays,
                'total_hours' => round($totalHours, 2),
                'attendance_rate' => $attendanceRate,
                'days_in_month' => $daysInMonth,
                'total_holidays' => count($holidaysList)
            ];
        } elseif ($reportType === 'employee') {
            if (!$employeeId && !empty($employees)) {
                $employeeId = (int) $employees[0]['id'];
            }
            if ($employeeId) {
                $employeeReport = Attendance::getEmployeeWiseReport($employeeId, $startDate, $endDate);
            }
        }

        $this->view('reports.index', [
            'title' => 'Workforce Attendance Reports & Analytics',
            'reportType' => $reportType,
            'date' => $date,
            'year' => $year,
            'month' => $month,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'employeeId' => $employeeId,
            'departmentId' => $departmentId,
            'status' => $status,
            'site' => $site,
            'employees' => $employees,
            'departments' => $departments,
            'departmentList' => $departmentList,
            'siteList' => $siteList,
            'holidaysMap' => $holidaysMap,
            'holidaysList' => $holidaysList,
            'dailySummary' => $dailySummary,
            'weeklyReport' => $weeklyReport,
            'monthlyReport' => $monthlyReport,
            'monthlySummary' => $monthlySummary,
            'employeeReport' => $employeeReport,
            'companyName' => Setting::get('company_name', 'Gannon Dunkerley & Co. Ltd.'),
            'logoText' => Setting::get('site_logo_text', 'Smart Attendance')
        ], 'admin');
    }

    public function daily(): void {
        Request::set('report_type', 'daily');
        $this->index();
    }

    public function weekly(): void {
        Request::set('report_type', 'weekly');
        $this->index();
    }

    public function monthly(): void {
        Request::set('report_type', 'monthly');
        $this->index();
    }

    public function employee(): void {
        Request::set('report_type', 'employee');
        $this->index();
    }

    /**
     * Unified Excel / Table Spreadsheet Export
     */
    public function export(): void {
        Auth::requireAuth();

        $type = Request::input('type', 'daily');
        $format = Request::input('format', 'excel'); // 'excel' or 'csv'

        if ($type === 'daily') {
            $this->exportDaily($format);
        } elseif ($type === 'weekly') {
            $this->exportWeekly($format);
        } elseif ($type === 'monthly_audit') {
            $this->exportMonthlyAudit($format);
        } elseif ($type === 'employee') {
            $this->exportEmployeeWise($format);
        } else {
            $this->exportMonthly($format);
        }
    }

    public function exportDailyCsv(): void {
        $this->exportDaily('excel');
    }

    public function exportMonthlyCsv(): void {
        $this->exportMonthly('excel');
    }

    public function exportMonthlyAuditCsv(): void {
        $this->exportMonthlyAudit('excel');
    }

    private function exportDaily(string $format): void {
        $date = Request::input('date', date('Y-m-d'));
        $departmentId = Request::input('department_id') ? (int) Request::input('department_id') : null;
        $status = Request::input('status');
        $site = Request::input('site');

        $summary = Attendance::getDailySummary($date, $departmentId, $status, $site);
        $headers = [
            'Emp Code', 'Employee Name', 'Department', 'Job Role', 'Assigned Project', 'Work Site',
            'Attendance Status', 'First Check IN', 'Last Check OUT', 'Total Punches', 'Work Duration', 'Hours'
        ];

        $rows = [];
        $totalPresent = 0;
        $totalHours = 0;

        foreach ($summary as $row) {
            $emp = $row['employee'] ?? $row;
            $inTime = !empty($row['first_in']) ? date('h:i:s A', strtotime($row['first_in'])) : '—';
            $outTime = !empty($row['last_out']) ? date('h:i:s A', strtotime($row['last_out'])) : '—';
            
            $statusLabel = 'Absent';
            if ($row['status'] === 'CHECKED_IN' || $row['status'] === 'IN') {
                $statusLabel = 'Working Now (IN)';
                $totalPresent++;
            } elseif ($row['status'] === 'COMPLETED' || $row['status'] === 'OUT') {
                $statusLabel = 'Present (Completed)';
                $totalPresent++;
            } elseif ($row['status'] === 'NO_OUT_PUNCH' || $row['status'] === 'NO_OUT' || !empty($row['is_no_out'])) {
                $statusLabel = 'Auto-Closed (No OUT)';
                $totalPresent++;
            } elseif ($row['status'] === 'LEAVE' || !empty($row['leave_info'])) {
                $lvType = $row['leave_info']['leave_type'] ?? 'Approved Leave';
                $odSite = !empty($row['leave_info']['target_site']) ? " -> " . $row['leave_info']['target_site'] : "";
                $statusLabel = "Leave ({$lvType}{$odSite})";
            }

            $totalHours += (float) ($row['hours_worked'] ?? 0);

            $rows[] = [
                $emp['employee_code'] ?? '',
                $emp['name'] ?? '',
                $emp['department_name'] ?? ($emp['department'] ?? 'General'),
                $emp['designation'] ?? 'Staff',
                $emp['project'] ?? '—',
                $emp['site'] ?? ($site ?: 'Main Site'),
                $statusLabel,
                $inTime,
                $outTime,
                $row['punch_count'] ?? 0,
                $row['formatted_duration'] ?? '00:00:00',
                ($row['hours_worked'] ?? 0) . 'h'
            ];
        }

        $kpis = [
            'Total Staff' => count($summary),
            'Present Today' => $totalPresent,
            'Total Hours Worked' => round($totalHours, 2) . ' hrs',
            'Selected Date' => date('d M Y (l)', strtotime($date)),
            'Site Scope' => $site ?: 'All Work Sites'
        ];

        $filename = "Daily_Attendance_Sheet_{$date}";
        $this->outputFormattedSpreadsheet($filename, "DAILY ATTENDANCE SUMMARY SHEET", "Date: " . date('d M Y', strtotime($date)), $site, $headers, $rows, $kpis, $format);
    }

    private function exportWeekly(string $format): void {
        $startDate = Request::input('start_date', date('Y-m-d', strtotime('-6 days')));
        $endDate = Request::input('end_date', date('Y-m-d'));
        $departmentId = Request::input('department_id') ? (int) Request::input('department_id') : null;
        $site = Request::input('site');

        $report = Attendance::getWeeklyReport($startDate, $endDate, $departmentId, $site);
        $days = $report['days'] ?? [];

        $headers = ['Emp Code', 'Employee Name', 'Department', 'Designation', 'Work Site', 'Present (Days)', 'Leave (Days)', 'Total Hours'];
        foreach ($days as $dStr) {
            $headers[] = date('d M (D)', strtotime($dStr));
        }

        $rows = [];
        $totalPresentAll = 0;
        $totalHoursAll = 0;

        foreach ($report['data'] ?? [] as $row) {
            $emp = $row['employee'];
            $totalPresentAll += (int) $row['present_days'];
            $totalHoursAll += (float) $row['total_hours'];

            $r = [
                $emp['employee_code'],
                $emp['name'],
                $emp['department_name'] ?? 'General',
                $emp['designation'] ?? 'Staff',
                $emp['site'] ?? 'Main Site',
                $row['present_days'],
                $row['leave_days'],
                $row['total_hours'] . 'h'
            ];

            foreach ($days as $dStr) {
                $stat = $row['daily_stats'][$dStr] ?? ['status' => '-', 'hours' => 0];
                if ($stat['status'] === 'P') {
                    $r[] = "P ({$stat['hours']}h)";
                } elseif ($stat['status'] === 'L') {
                    $od = !empty($stat['target_site']) ? " -> " . $stat['target_site'] : "";
                    $r[] = ($stat['leave_code'] ?? 'L') . $od;
                } elseif ($stat['status'] === 'W') {
                    $r[] = 'OFF';
                } else {
                    $r[] = 'A';
                }
            }
            $rows[] = $r;
        }

        $kpis = [
            'Total Staff' => count($report['data'] ?? []),
            'Date Range' => date('d M Y', strtotime($startDate)) . ' to ' . date('d M Y', strtotime($endDate)),
            'Total Present Days' => $totalPresentAll,
            'Total Work Hours' => round($totalHoursAll, 2) . ' hrs',
            'Work Site' => $site ?: 'All Sites'
        ];

        $filename = "Weekly_Attendance_Sheet_{$startDate}_to_{$endDate}";
        $this->outputFormattedSpreadsheet($filename, "WEEKLY WORKFORCE ATTENDANCE REPORT", "Period: " . date('d M Y', strtotime($startDate)) . " to " . date('d M Y', strtotime($endDate)), $site, $headers, $rows, $kpis, $format);
    }

    private function exportMonthly(string $format): void {
        $year = (int) Request::input('year', date('Y'));
        $month = (int) Request::input('month', date('n'));
        $departmentId = Request::input('department_id') ? (int) Request::input('department_id') : null;
        $site = Request::input('site');

        $report = Attendance::getMonthlyReport($year, $month, $departmentId, $site);
        $daysInMonth = $report['days_in_month'];
        $holidaysMap = Holiday::getMapForMonth((string)$year, (string)$month);

        $headers = ['Emp Code', 'Employee Name', 'Department', 'Designation', 'Work Site', 'Present (Days)', 'Leave (Days)', 'Absent (Days)', 'Total Hours'];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $headers[] = sprintf('Day %02d', $d);
        }

        $rows = [];
        $totalPresentDays = 0;
        $totalHours = 0;

        foreach ($report['data'] as $row) {
            $emp = $row['employee'];
            $empPresent = (int) $row['present_days'];
            $empLeaves = (int) ($row['leave_days'] ?? 0);
            $empAbsent = max(0, $daysInMonth - $empPresent - $empLeaves);

            $totalPresentDays += $empPresent;
            $totalHours += (float) $row['total_hours'];

            $dataRow = [
                $emp['employee_code'],
                $emp['name'],
                $emp['department_name'] ?? 'General',
                $emp['designation'] ?? 'Staff',
                $emp['site'] ?? 'Main Site',
                $empPresent,
                $empLeaves,
                $empAbsent,
                $row['total_hours'] . 'h'
            ];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $curDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
                $stat = $row['daily_stats'][$d] ?? ['status' => '-', 'hours' => 0];
                if ($stat['status'] === 'P') {
                    $dataRow[] = $stat['hours'] > 0 ? "P ({$stat['hours']}h)" : 'P';
                } elseif ($stat['status'] === 'L') {
                    $odExtra = !empty($stat['target_site']) ? " -> " . $stat['target_site'] : '';
                    $dataRow[] = ($stat['leave_code'] ?? 'L') . " (" . ($stat['leave_type'] ?? 'Leave') . $odExtra . ")";
                } elseif (isset($holidaysMap[$curDate])) {
                    $dataRow[] = "H (" . $holidaysMap[$curDate] . ")";
                } elseif ($stat['status'] === 'W') {
                    $dataRow[] = 'OFF';
                } else {
                    $dataRow[] = 'A';
                }
            }

            $rows[] = $dataRow;
        }

        $kpis = [
            'Month Period' => date('F Y', mktime(0, 0, 0, $month, 1, $year)),
            'Total Staff' => count($report['data']),
            'Total Present Days' => $totalPresentDays,
            'Total Productive Hours' => round($totalHours, 2) . ' hrs',
            'Work Site' => $site ?: 'All Sites'
        ];

        $filename = "Monthly_Timesheet_Matrix_{$year}_{$month}";
        $this->outputFormattedSpreadsheet($filename, "MONTHLY ATTENDANCE TIMESHEET MATRIX", "Month: " . date('F Y', mktime(0, 0, 0, $month, 1, $year)), $site, $headers, $rows, $kpis, $format);
    }

    private function exportMonthlyAudit(string $format): void {
        $year = (int) Request::input('year', date('Y'));
        $month = (int) Request::input('month', date('n'));
        $departmentId = Request::input('department_id') ? (int) Request::input('department_id') : null;
        $site = Request::input('site');

        $report = Attendance::getMonthlyReport($year, $month, $departmentId, $site);
        $daysInMonth = $report['days_in_month'];
        $holidaysMap = Holiday::getMapForMonth((string)$year, (string)$month);

        $headers = ['Date', 'Day', 'Employee Code', 'Employee Name', 'Department', 'Designation', 'Work Site', 'Punch IN Time', 'Punch OUT Time', 'Total Work Duration', 'Total Hours', 'Punch Count', 'Time Audit Status'];
        $rows = [];

        foreach ($report['data'] as $row) {
            $emp = $row['employee'];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $stat = $row['daily_stats'][$d] ?? [];
                $curDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
                $dayName = date('D', strtotime($curDate));

                $auditStatus = $stat['audit_status'] ?? 'ABSENT';
                if ($auditStatus === 'LEAVE') {
                    $siteDetail = !empty($stat['target_site']) ? " [Target Site: {$stat['target_site']}]" : '';
                    $auditLabel = 'Leave: ' . ($stat['leave_type'] ?? 'Approved') . $siteDetail . (!empty($stat['leave_reason']) ? ' (' . $stat['leave_reason'] . ')' : '');
                } elseif ($auditStatus === 'ABSENT' && isset($holidaysMap[$curDate])) {
                    $auditLabel = 'Holiday (' . $holidaysMap[$curDate] . ')';
                } else {
                    $auditLabel = match($auditStatus) {
                        'COMPLETED' => 'Completed (Verified IN & OUT)',
                        'CHECKED_IN' => 'Checked IN (Working)',
                        'NO_OUT' => 'Missing OUT Punch (Auto-Closed)',
                        'WEEKEND' => 'Weekend OFF',
                        default => 'Absent'
                    };
                }

                $rows[] = [
                    $curDate,
                    $dayName,
                    $emp['employee_code'],
                    $emp['name'],
                    $emp['department_name'] ?? 'General',
                    $emp['designation'] ?? 'Staff',
                    $emp['site'] ?? 'Main Site',
                    $stat['first_in'] ?? '—',
                    $stat['last_out'] ?? '—',
                    $stat['formatted_duration'] ?? '00:00:00',
                    ($stat['hours'] ?? 0) . 'h',
                    $stat['punch_count'] ?? 0,
                    $auditLabel
                ];
            }
        }

        $kpis = [
            'Month Period' => date('F Y', mktime(0, 0, 0, $month, 1, $year)),
            'Total Staff' => count($report['data']),
            'Audit Records Count' => count($rows),
            'Work Site' => $site ?: 'All Sites'
        ];

        $filename = "Monthly_IN_OUT_Audit_Report_{$year}_{$month}";
        $this->outputFormattedSpreadsheet($filename, "EMPLOYEE PUNCH IN/OUT TIME AUDIT SHEET", "Month: " . date('F Y', mktime(0, 0, 0, $month, 1, $year)), $site, $headers, $rows, $kpis, $format);
    }

    private function exportEmployeeWise(string $format): void {
        $employeeId = (int) Request::input('employee_id');
        $startDate = Request::input('start_date', date('Y-m-01'));
        $endDate = Request::input('end_date', date('Y-m-d'));

        $report = Attendance::getEmployeeWiseReport($employeeId, $startDate, $endDate);
        $emp = $report['employee'];
        if (!$emp) {
            redirect('reports');
        }

        $headers = ['Date', 'Day', 'Attendance Status', 'First Check IN', 'Last Check OUT', 'Total Punches', 'Work Duration', 'Hours', 'Notes / OD Target Site'];
        $rows = [];

        foreach ($report['records'] as $rec) {
            $statusText = match($rec['audit_status']) {
                'OD_DUTY' => 'OD (Outdoor Duty - Punched)',
                'OD_OFFSITE' => 'OD (Outdoor Duty - Field Visit)',
                'COMPLETED' => 'Present (Completed)',
                'CHECKED_IN' => 'Working Now',
                'NO_OUT' => 'Auto-Closed (No OUT)',
                'LEAVE' => 'Leave (' . ($rec['leave_info']['leave_type'] ?? 'Approved') . ')',
                'WEEKEND' => 'Weekend OFF',
                default => 'Absent'
            };

            $notes = '';
            if (!empty($rec['leave_info'])) {
                $lv = $rec['leave_info'];
                $isOd = str_contains(strtoupper($lv['leave_type'] ?? ''), 'OD') || str_contains(strtoupper($lv['leave_type'] ?? ''), 'OUTDOOR') || str_contains(strtoupper($lv['leave_type'] ?? ''), 'DUTY');
                if ($isOd) {
                    $notes .= "OD Target Site: " . ($lv['target_site'] ?? 'Field Site');
                    if (!empty($lv['origin_site'])) $notes .= " (From: " . $lv['origin_site'] . ")";
                    if (!empty($lv['reason'])) $notes .= " | Notes: " . $lv['reason'];
                    if (!empty($lv['attachment'])) $notes .= " [PDF Attached]";
                } else {
                    $notes .= "Leave Reason: " . ($lv['reason'] ?? $lv['leave_type']);
                    if (!empty($lv['attachment'])) $notes .= " [PDF Attached]";
                }
            }

            $rows[] = [
                $rec['date'],
                $rec['day_name'],
                $statusText,
                $rec['first_in'] ?? '—',
                $rec['last_out'] ?? '—',
                $rec['punch_count'],
                $rec['formatted_duration'],
                $rec['hours'] . 'h',
                $notes ?: '—'
            ];
        }

        $kpis = [
            'Employee Code' => $emp['employee_code'],
            'Employee Name' => $emp['name'],
            'Department' => $emp['department'] ?? 'General',
            'Work Site' => $emp['site'] ?? 'Main Site',
            'Present Days' => $report['summary']['present_days'],
            'Total Hours Worked' => $report['summary']['total_hours'] . ' hrs',
            'Period' => date('d M Y', strtotime($startDate)) . ' to ' . date('d M Y', strtotime($endDate))
        ];

        $filename = "Employee_Attendance_{$emp['employee_code']}_{$startDate}_to_{$endDate}";
        $this->outputFormattedSpreadsheet($filename, "INDIVIDUAL EMPLOYEE ATTENDANCE DOSSIER", "Employee: {$emp['name']} ({$emp['employee_code']})", $emp['site'] ?? 'Main Site', $headers, $rows, $kpis, $format);
    }

    /**
     * Professional Table Spreadsheet Generator with Company Banner, Logo, Site Name, Download Date/Time
     */
    private function outputFormattedSpreadsheet(string $filename, string $reportTitle, string $periodLabel, ?string $siteName, array $headers, array $rows, array $kpis, string $format = 'excel'): void {
        $rawName = Setting::get('company_name', 'Gannon Dunkerley & Co. Ltd.');
        $companyName = (!empty($rawName) && stripos($rawName, 'TechCorp') === false) ? $rawName : 'Gannon Dunkerley & Co. Ltd.';
        $logoText = Setting::get('site_logo_text', 'Smart Attendance');
        $downloadTime = date('d M Y, h:i:s A');
        $siteDisplay = $siteName ?: 'All Operational Work Sites / Headquarters';
        $colsCount = max(count($headers), 6);

        if ($format === 'excel') {
            // High-grade XML/HTML Excel Table Spreadsheet (opens with styling in Excel, Google Sheets, Numbers)
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            echo '<style>
                body { font-family: "Segoe UI", Calibri, Arial, sans-serif; font-size: 11pt; color: #1f2937; }
                table { border-collapse: collapse; width: 100%; }
                .banner-logo { background-color: #1e3a8a; color: #ffffff; font-size: 16pt; font-weight: bold; text-align: center; padding: 12px; height: 40px; letter-spacing: 0.5px; }
                .banner-title { background-color: #2563eb; color: #ffffff; font-size: 13pt; font-weight: bold; text-align: center; height: 30px; }
                .meta-bar { background-color: #f1f5f9; color: #334155; font-size: 9.5pt; font-weight: 600; padding: 6px 10px; border: 1px solid #cbd5e1; }
                .kpi-title { background-color: #e2e8f0; font-weight: bold; font-size: 10pt; color: #0f172a; text-align: center; border: 1px solid #94a3b8; }
                .kpi-val { background-color: #f8fafc; font-weight: bold; font-size: 10.5pt; color: #1e40af; text-align: center; border: 1px solid #cbd5e1; }
                th.tbl-head { background-color: #0f172a; color: #ffffff; font-size: 10pt; font-weight: bold; border: 1px solid #0f172a; text-align: center; padding: 8px 6px; }
                td.cell { border: 1px solid #cbd5e1; font-size: 9.5pt; padding: 6px 8px; }
                td.cell-center { border: 1px solid #cbd5e1; font-size: 9.5pt; padding: 6px 8px; text-align: center; }
                td.cell-bold { border: 1px solid #cbd5e1; font-size: 9.5pt; padding: 6px 8px; font-weight: bold; }
                .row-even { background-color: #ffffff; }
                .row-odd { background-color: #f8fafc; }
            </style></head><body>';

            echo '<table>';

            // 1. TOP HEADER: COMPANY NAME BANNER
            echo '<tr><td colspan="' . $colsCount . '" class="banner-logo">' . htmlspecialchars(strtoupper($companyName)) . '</td></tr>';
            echo '<tr><td colspan="' . $colsCount . '" class="banner-title">' . htmlspecialchars($reportTitle) . '</td></tr>';

            // 2. SITE NAME, PERIOD & DOWNLOAD DATE / TIME
            echo '<tr>';
            echo '<td colspan="' . ceil($colsCount / 2) . '" class="meta-bar"><strong>🏢 WORK SITE:</strong> ' . htmlspecialchars($siteDisplay) . '</td>';
            echo '<td colspan="' . floor($colsCount / 2) . '" class="meta-bar" style="text-align:right;"><strong>🕒 DOWNLOADED AT:</strong> ' . htmlspecialchars($downloadTime) . '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td colspan="' . ceil($colsCount / 2) . '" class="meta-bar"><strong>📅 REPORT PERIOD:</strong> ' . htmlspecialchars($periodLabel) . '</td>';
            echo '<td colspan="' . floor($colsCount / 2) . '" class="meta-bar" style="text-align:right;"><strong>🛡️ STATUS:</strong> Official Verified Attendance Document</td>';
            echo '</tr>';
            echo '<tr><td colspan="' . $colsCount . '" style="height:10px;"></td></tr>';

            // 3. KPI METRIC HIGHLIGHTS
            if (!empty($kpis)) {
                echo '<tr>';
                foreach ($kpis as $kLabel => $kVal) {
                    echo '<td class="kpi-title">' . htmlspecialchars((string)$kLabel) . '</td>';
                }
                echo '</tr>';
                echo '<tr>';
                foreach ($kpis as $kLabel => $kVal) {
                    echo '<td class="kpi-val">' . htmlspecialchars((string)$kVal) . '</td>';
                }
                echo '</tr>';
                echo '<tr><td colspan="' . $colsCount . '" style="height:12px;"></td></tr>';
            }

            // 4. MAIN TABLE HEADERS
            echo '<thead><tr>';
            foreach ($headers as $h) {
                echo '<th class="tbl-head">' . htmlspecialchars($h) . '</th>';
            }
            echo '</tr></thead>';

            // 5. TABLE DATA ROWS
            echo '<tbody>';
            foreach ($rows as $i => $row) {
                $rowClass = ($i % 2 === 0) ? 'row-even' : 'row-odd';
                echo '<tr class="' . $rowClass . '">';
                foreach ($row as $ci => $cell) {
                    $isCenter = ($ci === 0 || $ci === 1 || str_contains($headers[$ci] ?? '', 'Time') || str_contains($headers[$ci] ?? '', 'Date') || str_contains($headers[$ci] ?? '', 'Status') || str_contains($headers[$ci] ?? '', 'Day'));
                    $alignClass = $isCenter ? 'cell-center' : 'cell';
                    echo '<td class="' . $alignClass . '">' . htmlspecialchars((string)$cell) . '</td>';
                }
                echo '</tr>';
            }
            echo '</tbody>';

            // 6. FOOTER
            echo '<tfoot><tr><td colspan="' . $colsCount . '" style="border-top:2px solid #0f172a; padding:10px; font-size:9pt; color:#64748b; text-align:center;">';
            echo 'Generated automatically by ' . htmlspecialchars($companyName) . ' Digital Attendance Cloud Server &bull; ' . htmlspecialchars($downloadTime);
            echo '</td></tr></tfoot>';

            echo '</table></body></html>';
            exit;
        } else {
            // Standard Formatted CSV with UTF-8 BOM and Header Blocks
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, ['========================================================================================================']);
            fputcsv($output, [strtoupper($companyName)]);
            fputcsv($output, [$reportTitle]);
            fputcsv($output, ['Work Site:', $siteDisplay, 'Downloaded At:', $downloadTime]);
            fputcsv($output, ['Report Period:', $periodLabel, 'Document Status:', 'Official Verified Attendance Document']);
            fputcsv($output, ['========================================================================================================']);
            fputcsv($output, []);

            if (!empty($kpis)) {
                fputcsv($output, ['--- KEY PERFORMANCE SUMMARY ---']);
                fputcsv($output, array_keys($kpis));
                fputcsv($output, array_values($kpis));
                fputcsv($output, []);
            }

            fputcsv($output, ['--- ATTENDANCE DATA TABLE ---']);
            fputcsv($output, $headers);
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
            exit;
        }
    }
}
