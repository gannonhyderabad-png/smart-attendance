<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Logger;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Setting;

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

        // Check if employee has an active or pending leave/OD entry today & collect all applied leave ranges + punched dates
        $todayLeave = null;
        $appliedLeaveRanges = [];
        $punchedDates = [];
        try {
            $pdo = \Database\Database::getConnection();
            $stmtLeave = $pdo->prepare("SELECT * FROM leaves WHERE employee_id = ? AND status != 'REJECTED' ORDER BY id DESC");
            $stmtLeave->execute([$employee['id']]);
            $leavesList = $stmtLeave->fetchAll(\PDO::FETCH_ASSOC);
            $todayTs = strtotime($today);
            foreach ($leavesList as $l) {
                $rawS = trim((string)($l['start_date'] ?? ''));
                $rawE = trim((string)($l['end_date'] ?? ''));
                $sTs = preg_match('/^(\d{1,2})[- \/](\d{1,2})[- \/](\d{4})/', $rawS, $m)
                    ? strtotime(sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]))
                    : strtotime(substr($rawS, 0, 10));
                $eTs = preg_match('/^(\d{1,2})[- \/](\d{1,2})[- \/](\d{4})/', $rawE, $m)
                    ? strtotime(sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]))
                    : strtotime(substr($rawE, 0, 10));

                if ($sTs && $eTs) {
                    $appliedLeaveRanges[] = [
                        'start' => date('Y-m-d', $sTs),
                        'end' => date('Y-m-d', $eTs),
                        'type' => $l['leave_type'] ?: 'Leave',
                        'status' => $l['status'] ?? 'PENDING'
                    ];
                    if ($todayLeave === null && $todayTs >= $sTs && $todayTs <= $eTs) {
                        $todayLeave = $l;
                    }
                }
            }

            $stmtPunches = $pdo->prepare("SELECT punch_time FROM attendance WHERE employee_id = ? ORDER BY punch_time DESC");
            $stmtPunches->execute([$employee['id']]);
            $pRows = $stmtPunches->fetchAll(\PDO::FETCH_ASSOC);
            $seenDates = [];
            foreach ($pRows as $pr) {
                $dStr = substr((string)($pr['punch_time'] ?? ''), 0, 10);
                $pTs = strtotime($dStr);
                if ($pTs) {
                    $normD = date('Y-m-d', $pTs);
                    $seenDates[$normD] = true;
                }
            }
            $punchedDates = array_keys($seenDates);
        } catch (\Throwable $e) {}

        // Fetch recent leaves submitted by employee (last 5)
        $myLeaves = \App\Models\Leave::all([
            'employee_id' => $employee['id']
        ], 'l.id DESC LIMIT 5');

        $currentStatus = $latestPunch ? strtoupper($latestPunch['punch_type'] ?? 'OUT') : 'OUT';
        $isNoOutAutoClosed = false;

        // Build employee's own Daily & Monthly Attendance Status + Summary
        $selfReport = $this->buildEmployeeSelfReport($employee, 'both', $today, date('Y-m'));

        $this->view('punch.index', [
            'employee' => $employee,
            'latestPunch' => $latestPunch,
            'todayPunches' => $todayPunches,
            'workedSeconds' => $workedSeconds,
            'currentStatus' => $currentStatus,
            'isNoOutAutoClosed' => $isNoOutAutoClosed,
            'todayLeave' => $todayLeave,
            'appliedLeaveRanges' => $appliedLeaveRanges,
            'punchedDates' => $punchedDates,
            'myLeaves' => $myLeaves,
            'selfReport' => $selfReport,
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

    /**
     * AJAX JSON endpoint for Employee's Own Daily & Monthly Attendance Status + Summary
     */
    public function attendanceReport(string $code): void {
        $employee = Employee::findByCode($code);
        if (!$employee || $employee['status'] !== 'active') {
            $this->json(['success' => false, 'message' => 'Employee not found or inactive'], 404);
            return;
        }

        $mode = strtolower(trim((string) Request::input('mode', 'both')));
        $date = trim((string) Request::input('date', date('Y-m-d')));
        $month = trim((string) Request::input('month', date('Y-m')));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $report = $this->buildEmployeeSelfReport($employee, $mode, $date, $month);
        $this->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Download Employee's Own Daily or Monthly Attendance Report (Excel .xls or .csv)
     */
    public function downloadReport(string $code): void {
        $employee = Employee::findByCode($code);
        if (!$employee || $employee['status'] !== 'active') {
            http_response_code(404);
            echo 'Employee not found or inactive.';
            exit;
        }

        $mode = strtolower(trim((string) Request::input('mode', 'monthly')));
        $format = strtolower(trim((string) Request::input('format', 'excel')));
        $date = trim((string) Request::input('date', date('Y-m-d')));
        $month = trim((string) Request::input('month', date('Y-m')));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $report = $this->buildEmployeeSelfReport($employee, $mode, $date, $month);

        if ($mode === 'daily') {
            $d = $report['daily'];
            $headers = ['#', 'Date', 'Day', 'Punch Type', 'Punch Time', 'Location / Site', 'Verification'];
            $rows = [];
            if (!empty($d['punches'])) {
                foreach ($d['punches'] as $idx => $p) {
                    $rows[] = [
                        $idx + 1,
                        $d['date_formatted'],
                        $d['day_name'],
                        $p['type'],
                        $p['time'],
                        $p['site'] ?: ($employee['site'] ?? 'Main Site'),
                        !empty($p['location_verified']) ? 'GPS Verified' : 'Recorded'
                    ];
                }
            } else {
                $rows[] = [
                    1,
                    $d['date_formatted'],
                    $d['day_name'],
                    '—',
                    '—',
                    $employee['site'] ?? 'Main Site',
                    $d['status_label'] . (!empty($d['note']) ? ' (' . $d['note'] . ')' : '')
                ];
            }

            $kpis = [
                'Emp Code' => $employee['employee_code'],
                'Employee Name' => $employee['name'],
                'Date' => $d['date_formatted'] . ' (' . $d['day_name'] . ')',
                'Status' => $d['status_label'],
                'First IN' => $d['first_in'] ?: '—',
                'Last OUT' => $d['last_out'] ?: '—',
                'Worked Duration' => $d['formatted_duration'] . ' (' . $d['hours'] . 'h)'
            ];

            $filename = "My_Daily_Attendance_{$employee['employee_code']}_{$date}";
            $this->streamEmployeeSpreadsheet(
                $filename,
                'EMPLOYEE DAILY ATTENDANCE STATUS & SUMMARY',
                "Date: {$d['date_formatted']} ({$d['day_name']}) | Employee: {$employee['name']} ({$employee['employee_code']})",
                $employee['site'] ?? 'Main Site',
                $headers,
                $rows,
                $kpis,
                $format
            );
        } else {
            $m = $report['monthly'];
            $s = $m['summary'];
            $headers = ['Date', 'Day', 'Attendance Status', 'First IN', 'Last OUT', 'Punches', 'Worked Duration', 'Hours', 'Remarks / Leave'];
            $rows = [];
            foreach ($m['records'] as $rec) {
                $rows[] = [
                    $rec['date_short'],
                    $rec['day_name'],
                    $rec['status_label'],
                    $rec['first_in'] ?: '—',
                    $rec['last_out'] ?: '—',
                    $rec['punch_count'],
                    $rec['formatted_duration'],
                    $rec['hours'] . 'h',
                    $rec['note'] ?: '—'
                ];
            }

            $kpis = [
                'Emp Code' => $employee['employee_code'],
                'Employee Name' => $employee['name'],
                'Month' => $m['month_label'],
                'Present Days' => $s['present_days'],
                'Absent Days' => $s['absent_days'],
                'Leave / OD' => ($s['leave_days'] + $s['od_days']) . ' d',
                'Total Worked' => $s['formatted_total_duration'] . ' (' . $s['total_hours'] . 'h)',
                'Attendance %' => $s['attendance_rate'] . '%'
            ];

            $filename = "My_Monthly_Attendance_{$employee['employee_code']}_{$month}";
            $this->streamEmployeeSpreadsheet(
                $filename,
                'EMPLOYEE MONTHLY ATTENDANCE SUMMARY & LOG',
                "Month: {$m['month_label']} | Employee: {$employee['name']} ({$employee['employee_code']})",
                $employee['site'] ?? 'Main Site',
                $headers,
                $rows,
                $kpis,
                $format
            );
        }
    }

    /**
     * Compute Daily & Monthly Attendance Status and Summary strictly for the given Employee
     */
    private function buildEmployeeSelfReport(array $employee, string $mode, string $selectedDate, string $selectedMonth): array {
        $empId = (int) $employee['id'];
        $today = date('Y-m-d');
        $nowTs = time();
        $shiftEnd = !empty($employee['shift_end']) ? $employee['shift_end'] : '18:00:00';

        // Load all non-rejected leaves for this employee so both Approved and Pending leaves are reflected
        $empLeaveMap = [];
        try {
            $pdo = \Database\Database::getConnection();
            $stmtL = $pdo->prepare("SELECT * FROM leaves WHERE employee_id = ? AND status != 'REJECTED' ORDER BY id ASC");
            $stmtL->execute([$empId]);
            $leaveRows = $stmtL->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($leaveRows as $lv) {
                $rawS = trim((string)($lv['start_date'] ?? ''));
                $rawE = trim((string)($lv['end_date'] ?? ''));
                $sTs = preg_match('/^(\d{1,2})[- \/](\d{1,2})[- \/](\d{4})/', $rawS, $m)
                    ? strtotime(sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]))
                    : strtotime(substr($rawS, 0, 10));
                $eTs = preg_match('/^(\d{1,2})[- \/](\d{1,2})[- \/](\d{4})/', $rawE, $m)
                    ? strtotime(sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]))
                    : strtotime(substr($rawE, 0, 10));
                if (!$sTs || !$eTs) continue;
                if ($eTs < $sTs) $eTs = $sTs;

                $cur = $sTs;
                $guard = 0;
                while ($cur <= $eTs && $guard < 370) {
                    $empLeaveMap[date('Y-m-d', $cur)] = $lv;
                    $cur = strtotime('+1 day', $cur);
                    $guard++;
                }
            }
        } catch (\Throwable $e) {}

        $computeSingleDay = function(string $dStr, array $holidaysMap) use ($empId, $today, $nowTs, $shiftEnd, $empLeaveMap): array {
            $punches = Attendance::getDayPunches($empId, $dStr);
            $punchCount = count($punches);
            $firstIn = null;
            $lastOut = null;
            $punchItems = [];

            foreach ($punches as $p) {
                if ($p['punch_type'] === 'IN' && $firstIn === null) {
                    $firstIn = date('h:i A', strtotime($p['punch_time']));
                }
                if ($p['punch_type'] === 'OUT') {
                    $lastOut = date('h:i A', strtotime($p['punch_time']));
                }
                $punchItems[] = [
                    'type' => $p['punch_type'],
                    'time' => date('h:i:s A', strtotime($p['punch_time'])),
                    'site' => $p['site'] ?? null,
                    'location_verified' => !empty($p['location_verified'])
                ];
            }

            $seconds = $punchCount > 0 ? Attendance::calculateWorkSeconds($empId, $dStr) : 0;
            $lv = $empLeaveMap[$dStr] ?? null;
            $holidayTitle = $holidaysMap[$dStr] ?? null;
            $dayNum = (int) date('N', strtotime($dStr));
            $isWeekend = ($dayNum === 6 || $dayNum === 7);

            $statusCode = 'ABSENT';
            $statusLabel = 'Absent';
            $badgeClass = 'bg-danger-subtle text-danger border-danger-subtle';
            $note = '';

            if ($punchCount > 0) {
                $latest = end($punches);
                $shiftEndTs = strtotime($dStr . ' ' . $shiftEnd);
                if (($latest['punch_type'] ?? '') === 'IN') {
                    if ($dStr === $today && $nowTs <= $shiftEndTs) {
                        $statusCode = 'WORKING';
                        $statusLabel = 'Working Now (IN)';
                        $badgeClass = 'bg-success text-white';
                    } else {
                        $statusCode = 'NO_OUT';
                        $statusLabel = 'Present (Auto-Closed)';
                        $badgeClass = 'bg-warning-subtle text-dark border-warning-subtle';
                    }
                } else {
                    $statusCode = 'PRESENT';
                    $statusLabel = 'Present';
                    $badgeClass = 'bg-success-subtle text-success border-success-subtle';
                }
            } elseif ($lv !== null) {
                $lvType = $lv['leave_type'] ?: 'Leave';
                $lvStatus = strtoupper((string)($lv['status'] ?? 'PENDING'));
                $isOd = str_contains(strtoupper($lvType), 'OD') || str_contains(strtoupper($lvType), 'OUTDOOR') || str_contains(strtoupper($lvType), 'DUTY');
                $stateSuffix = ($lvStatus === 'APPROVED') ? 'Approved' : 'Pending';
                if ($isOd) {
                    $statusCode = ($lvStatus === 'APPROVED') ? 'OD' : 'LEAVE_PENDING';
                    $statusLabel = "OD ({$stateSuffix})";
                    $badgeClass = 'bg-info-subtle text-info-emphasis border-info-subtle';
                    $note = !empty($lv['target_site']) ? ('Site: ' . $lv['target_site']) : $lvType;
                } else {
                    $statusCode = ($lvStatus === 'APPROVED') ? 'LEAVE' : 'LEAVE_PENDING';
                    $statusLabel = "Leave ({$stateSuffix})";
                    $badgeClass = ($lvStatus === 'APPROVED')
                        ? 'bg-primary-subtle text-primary border-primary-subtle'
                        : 'bg-warning-subtle text-dark border-warning-subtle';
                    $note = $lvType . (!empty($lv['reason']) ? ' - ' . $lv['reason'] : '');
                }
            } elseif ($holidayTitle !== null) {
                $statusCode = 'HOLIDAY';
                $statusLabel = 'Holiday';
                $badgeClass = 'bg-info-subtle text-primary border-info-subtle';
                $note = $holidayTitle;
            } elseif ($isWeekend) {
                $statusCode = 'WEEKEND';
                $statusLabel = 'Weekend';
                $badgeClass = 'bg-light text-secondary border';
                $note = date('l', strtotime($dStr));
            } elseif ($dStr > $today) {
                $statusCode = 'UPCOMING';
                $statusLabel = 'Upcoming';
                $badgeClass = 'bg-light text-muted border';
            } elseif ($dStr === $today) {
                $statusCode = 'NOT_PUNCHED';
                $statusLabel = 'Not Punched Yet';
                $badgeClass = 'bg-secondary-subtle text-secondary border-secondary-subtle';
            }

            return [
                'date' => $dStr,
                'date_short' => date('d M', strtotime($dStr)),
                'date_formatted' => date('d M Y', strtotime($dStr)),
                'day_name' => date('D', strtotime($dStr)),
                'status_code' => $statusCode,
                'status_label' => $statusLabel,
                'badge_class' => $badgeClass,
                'first_in' => $firstIn,
                'last_out' => $lastOut,
                'punch_count' => $punchCount,
                'seconds' => $seconds,
                'formatted_duration' => format_seconds($seconds),
                'hours' => round($seconds / 3600, 2),
                'note' => $note,
                'punches' => $punchItems
            ];
        };

        $out = [];

        if ($mode === 'daily' || $mode === 'both') {
            $dYear = substr($selectedDate, 0, 4);
            $dMonth = substr($selectedDate, 5, 2);
            $holMapD = Holiday::getMapForMonth($dYear, $dMonth);
            $out['daily'] = $computeSingleDay($selectedDate, $holMapD);
        }

        if ($mode === 'monthly' || $mode === 'both') {
            [$mYearStr, $mMonthStr] = explode('-', $selectedMonth);
            $mYear = (int) $mYearStr;
            $mMonth = (int) $mMonthStr;
            $startOfMonth = sprintf('%04d-%02d-01', $mYear, $mMonth);
            $daysInMonth = (int) date('t', strtotime($startOfMonth));
            $holMapM = Holiday::getMapForMonth((string) $mYear, (string) $mMonth);

            $records = [];
            $presentDays = 0;
            $absentDays = 0;
            $leaveDays = 0;
            $odDays = 0;
            $holidayWeekendDays = 0;
            $elapsedWorkingDays = 0;
            $totalSeconds = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $curDate = sprintf('%04d-%02d-%02d', $mYear, $mMonth, $d);
                $dayRec = $computeSingleDay($curDate, $holMapM);
                // Omit nested punches array in monthly list to keep payload compact
                unset($dayRec['punches']);
                $records[] = $dayRec;

                $sc = $dayRec['status_code'];
                $totalSeconds += $dayRec['seconds'];

                if ($sc === 'PRESENT' || $sc === 'WORKING' || $sc === 'NO_OUT') {
                    $presentDays++;
                    $elapsedWorkingDays++;
                } elseif ($sc === 'OD') {
                    $odDays++;
                    $presentDays++;
                    $elapsedWorkingDays++;
                } elseif ($sc === 'LEAVE' || $sc === 'LEAVE_PENDING') {
                    $leaveDays++;
                    if ($curDate <= $today) {
                        $elapsedWorkingDays++;
                    }
                } elseif ($sc === 'HOLIDAY' || $sc === 'WEEKEND') {
                    $holidayWeekendDays++;
                } elseif ($sc === 'ABSENT' || $sc === 'NOT_PUNCHED') {
                    if ($sc === 'ABSENT') {
                        $absentDays++;
                    }
                    if ($curDate <= $today) {
                        $elapsedWorkingDays++;
                    }
                }
            }

            $attendanceRate = $elapsedWorkingDays > 0
                ? round(($presentDays / $elapsedWorkingDays) * 100, 1)
                : 0.0;

            $out['monthly'] = [
                'month' => $selectedMonth,
                'month_label' => date('F Y', strtotime($startOfMonth)),
                'days_in_month' => $daysInMonth,
                'summary' => [
                    'present_days' => $presentDays,
                    'absent_days' => $absentDays,
                    'leave_days' => $leaveDays,
                    'od_days' => $odDays,
                    'holiday_weekend_days' => $holidayWeekendDays,
                    'total_seconds' => $totalSeconds,
                    'formatted_total_duration' => format_seconds($totalSeconds),
                    'total_hours' => round($totalSeconds / 3600, 2),
                    'attendance_rate' => $attendanceRate
                ],
                'records' => $records
            ];
        }

        return $out;
    }

    /**
     * Stream Excel (.xls) or CSV (.csv) download for the Employee
     */
    private function streamEmployeeSpreadsheet(
        string $filename,
        string $reportTitle,
        string $periodLabel,
        string $siteName,
        array $headers,
        array $rows,
        array $kpis,
        string $format
    ): void {
        $rawName = Setting::get('company_name', 'Gannon Dunkerley & Co. Ltd.');
        $companyName = (!empty($rawName) && stripos($rawName, 'TechCorp') === false) ? $rawName : 'Gannon Dunkerley & Co. Ltd.';
        $downloadTime = date('d M Y, h:i:s A');
        $colsCount = max(count($headers), count($kpis), 6);

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($output, [strtoupper($companyName)]);
            fputcsv($output, [$reportTitle]);
            fputcsv($output, ['Work Site:', $siteName, 'Downloaded At:', $downloadTime]);
            fputcsv($output, ['Period:', $periodLabel]);
            fputcsv($output, []);
            if (!empty($kpis)) {
                fputcsv($output, array_keys($kpis));
                fputcsv($output, array_values($kpis));
                fputcsv($output, []);
            }
            fputcsv($output, $headers);
            foreach ($rows as $r) {
                fputcsv($output, $r);
            }
            fclose($output);
            exit;
        }

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><style>
            body { font-family: "Segoe UI", Calibri, Arial, sans-serif; font-size: 10.5pt; color: #1f2937; }
            table { border-collapse: collapse; width: 100%; }
            .banner-logo { background-color: #1e3a8a; color: #ffffff; font-size: 15pt; font-weight: bold; text-align: center; padding: 10px; }
            .banner-title { background-color: #2563eb; color: #ffffff; font-size: 12pt; font-weight: bold; text-align: center; padding: 6px; }
            .meta-bar { background-color: #f1f5f9; color: #334155; font-size: 9.5pt; font-weight: 600; padding: 6px 10px; border: 1px solid #cbd5e1; }
            .kpi-title { background-color: #e2e8f0; font-weight: bold; font-size: 9.5pt; color: #0f172a; text-align: center; border: 1px solid #94a3b8; padding: 5px; }
            .kpi-val { background-color: #f8fafc; font-weight: bold; font-size: 10pt; color: #1e40af; text-align: center; border: 1px solid #cbd5e1; padding: 6px; }
            th.tbl-head { background-color: #0f172a; color: #ffffff; font-size: 9.5pt; font-weight: bold; border: 1px solid #0f172a; text-align: center; padding: 7px 6px; }
            td.cell { border: 1px solid #cbd5e1; font-size: 9.5pt; padding: 5px 8px; }
            td.cell-center { border: 1px solid #cbd5e1; font-size: 9.5pt; padding: 5px 8px; text-align: center; }
            .row-even { background-color: #ffffff; }
            .row-odd { background-color: #f8fafc; }
        </style></head><body><table>';

        echo '<tr><td colspan="' . $colsCount . '" class="banner-logo">' . htmlspecialchars(strtoupper($companyName)) . '</td></tr>';
        echo '<tr><td colspan="' . $colsCount . '" class="banner-title">' . htmlspecialchars($reportTitle) . '</td></tr>';
        echo '<tr>';
        echo '<td colspan="' . (int) ceil($colsCount / 2) . '" class="meta-bar"><strong>Work Site:</strong> ' . htmlspecialchars($siteName) . '</td>';
        echo '<td colspan="' . (int) floor($colsCount / 2) . '" class="meta-bar" style="text-align:right;"><strong>Downloaded At:</strong> ' . htmlspecialchars($downloadTime) . '</td>';
        echo '</tr>';
        echo '<tr><td colspan="' . $colsCount . '" class="meta-bar"><strong>Report Details:</strong> ' . htmlspecialchars($periodLabel) . '</td></tr>';
        echo '<tr><td colspan="' . $colsCount . '" style="height:8px;"></td></tr>';

        if (!empty($kpis)) {
            echo '<tr>';
            foreach ($kpis as $kLabel => $kVal) {
                echo '<td class="kpi-title">' . htmlspecialchars((string) $kLabel) . '</td>';
            }
            echo '</tr><tr>';
            foreach ($kpis as $kLabel => $kVal) {
                echo '<td class="kpi-val">' . htmlspecialchars((string) $kVal) . '</td>';
            }
            echo '</tr>';
            echo '<tr><td colspan="' . $colsCount . '" style="height:10px;"></td></tr>';
        }

        echo '<thead><tr>';
        foreach ($headers as $h) {
            echo '<th class="tbl-head">' . htmlspecialchars($h) . '</th>';
        }
        echo '</tr></thead><tbody>';

        foreach ($rows as $i => $r) {
            $cls = ($i % 2 === 0) ? 'row-even' : 'row-odd';
            echo '<tr class="' . $cls . '">';
            foreach ($r as $ci => $val) {
                $align = ($ci <= 5) ? 'cell-center' : 'cell';
                echo '<td class="' . $align . '">' . htmlspecialchars((string) $val) . '</td>';
            }
            echo '</tr>';
        }

        echo '</tbody></table></body></html>';
        exit;
    }
}
