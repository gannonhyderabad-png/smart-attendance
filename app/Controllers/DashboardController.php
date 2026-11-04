<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Leave;
use Database\Database;

class DashboardController extends Controller {
    public function index(): void {
        Auth::requireAuth();

        if (Auth::isSiteUser()) { redirect('employees'); }
        if (Auth::isRestricted()) {
            redirect('leaves');
        }

        $stats = Attendance::getDashboardStats();
        $pdo = Database::getConnection();
        $today = date('Y-m-d');

        // On leave today
        $onLeaveToday = 0;
        try {
            $stmtL = $pdo->prepare("SELECT COUNT(DISTINCT employee_id) FROM leaves WHERE ? BETWEEN start_date AND end_date AND status != 'REJECTED'");
            $stmtL->execute([$today]);
            $onLeaveToday = (int) $stmtL->fetchColumn();
        } catch (\Throwable $e) {}

        $totalEmployees = (int)($stats['total_employees'] ?? 0);
        $totalEmpSafe = max(1, $totalEmployees);
        $stats['on_leave_today'] = $onLeaveToday;
        $stats['leave_rate'] = round(($onLeaveToday / $totalEmpSafe) * 100, 1);
        $stats['duty_rate'] = round((($stats['currently_in'] ?? 0) / $totalEmpSafe) * 100, 1);

        // 1. LEAVE OVERVIEW: Show strictly actual recorded leaves given in the system
        $leavePalette = ['#00b894', '#0984e3', '#f39c12', '#e74c3c', '#6c5ce7', '#00cec9', '#fdcb6e', '#e84393'];
        $leaveItems = [];
        $leaveTotal = 0;
        $chartLabels = [];
        $chartData = [];
        $chartColors = [];

        try {
            $stmtLeaves = $pdo->query("SELECT TRIM(leave_type) as leave_name, COUNT(*) as c FROM leaves WHERE status != 'REJECTED' GROUP BY leave_name ORDER BY c DESC, leave_name ASC");
            $rows = $stmtLeaves ? $stmtLeaves->fetchAll(\PDO::FETCH_ASSOC) : [];
            $colorIdx = 0;
            foreach ($rows as $r) {
                $name = trim((string)($r['leave_name'] ?? ''));
                if ($name === '') continue;
                $cnt = (int)($r['c'] ?? 0);
                $color = $leavePalette[$colorIdx % count($leavePalette)];
                $leaveItems[] = [
                    'label' => $name,
                    'count' => $cnt,
                    'color' => $color
                ];
                $chartLabels[] = $name;
                $chartData[] = $cnt;
                $chartColors[] = $color;
                $leaveTotal += $cnt;
                $colorIdx++;
            }
        } catch (\Throwable $e) {}

        // If no leaves recorded yet, show company assigned types with count 0 (never fake numbers)
        if (empty($leaveItems)) {
            $leaveItems = [
                ['label' => 'Casual Leave (CL)', 'count' => 0, 'color' => '#00b894'],
                ['label' => 'Sick Leave (SL)', 'count' => 0, 'color' => '#f39c12'],
                ['label' => 'Paid Leave (PL)', 'count' => 0, 'color' => '#e74c3c']
            ];
            $leaveTotal = 0;
            $chartLabels = ['No Leaves Recorded'];
            $chartData = [0];
            $chartColors = ['#e2e8f0'];
        }

        $leaveBreakdown = [
            'total' => $leaveTotal,
            'items' => $leaveItems,
            'chart_labels' => $chartLabels,
            'chart_data' => $chartData,
            'chart_colors' => $chartColors
        ];

        // 2. TOP DEPARTMENTS: Strictly departments where active workforce is assigned (no empty seed depts)
        $enrichedDepts = [];
        $deptColors = ['#00b894', '#0984e3', '#6c5ce7', '#e17055', '#fdcb6e', '#00cec9', '#e84393', '#2d3436'];

        try {
            $stmtDepts = $pdo->prepare("
                SELECT 
                    COALESCE(NULLIF(TRIM(d.name), ''), NULLIF(TRIM(e.department), ''), 'General') as dept_name,
                    COUNT(DISTINCT e.id) as employee_count,
                    COUNT(DISTINCT CASE WHEN a.punch_date = ? AND a.punch_type = 'IN' THEN a.employee_id END) as present_count
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN attendance a ON a.employee_id = e.id AND a.punch_date = ? AND a.punch_type = 'IN'
                WHERE e.status = 'active' AND e.deleted_at IS NULL
                GROUP BY COALESCE(NULLIF(TRIM(d.name), ''), NULLIF(TRIM(e.department), ''), 'General')
                ORDER BY employee_count DESC, dept_name ASC
            ");
            $stmtDepts->execute([$today, $today]);
            $deptRows = $stmtDepts->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($deptRows as $idx => $r) {
                $name = trim((string)($r['dept_name'] ?? 'General'));
                $totalInDept = (int)($r['employee_count'] ?? 0);
                $presentInDept = (int)($r['present_count'] ?? 0);
                $rate = ($totalInDept > 0) ? round(($presentInDept / $totalInDept) * 100) : 0;

                $enrichedDepts[] = [
                    'name' => $name,
                    'employee_count' => $totalInDept,
                    'present_count' => $presentInDept,
                    'attendance_rate' => $rate,
                    'bar_color' => $deptColors[$idx % count($deptColors)]
                ];
            }
        } catch (\Throwable $e) {}

        // 3. Live Locations
        $sites = [];
        try {
            $stmtSites = $pdo->query("SELECT TRIM(COALESCE(site, 'Main Site')) as site_name, COUNT(*) as count FROM employees WHERE status = 'active' AND deleted_at IS NULL GROUP BY site_name ORDER BY count DESC");
            $sites = $stmtSites ? $stmtSites->fetchAll(\PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {}

        // 4. Recent Activities feed
        $activities = [];
        try {
            $stmtActP = $pdo->query("SELECT a.punch_time, a.punch_type, e.name as emp_name FROM attendance a JOIN employees e ON a.employee_id = e.id ORDER BY a.punch_time DESC LIMIT 6");
            $pRows = $stmtActP ? $stmtActP->fetchAll(\PDO::FETCH_ASSOC) : [];
            foreach ($pRows as $pr) {
                $isIn = ($pr['punch_type'] === 'IN');
                $activities[] = [
                    'text' => $pr['emp_name'] . ' ' . ($isIn ? 'punched IN (Duty)' : 'punched OUT (Shift End)'),
                    'time_ago' => time_ago($pr['punch_time']),
                    'color' => $isIn ? 'success' : 'danger',
                    'icon' => $isIn ? 'fa-check' : 'fa-arrow-right-from-bracket'
                ];
            }

            $stmtActL = $pdo->query("SELECT l.created_at, l.leave_type, l.status, e.name as emp_name FROM leaves l JOIN employees e ON l.employee_id = e.id ORDER BY l.id DESC LIMIT 4");
            $lRows = $stmtActL ? $stmtActL->fetchAll(\PDO::FETCH_ASSOC) : [];
            foreach ($lRows as $lr) {
                $st = strtoupper($lr['status'] ?? 'PENDING');
                $activities[] = [
                    'text' => $lr['emp_name'] . ' applied ' . strtolower($lr['leave_type']) . ' (' . strtolower($st) . ')',
                    'time_ago' => time_ago($lr['created_at'] ?? 'now'),
                    'color' => ($st === 'APPROVED') ? 'success' : 'warning',
                    'icon' => ($st === 'APPROVED') ? 'fa-circle-check' : 'fa-clock'
                ];
            }
        } catch (\Throwable $e) {}

        if (empty($activities)) {
            $activities[] = [
                'text' => 'Workforce active & monitoring live punches',
                'time_ago' => 'Just now',
                'color' => 'success',
                'icon' => 'fa-circle-check'
            ];
        }

        $this->view('dashboard.index', [
            'title' => 'Dashboard Overview',
            'stats' => $stats,
            'departments' => $enrichedDepts,
            'leaveBreakdown' => $leaveBreakdown,
            'sites' => $sites,
            'activities' => $activities
        ], 'admin');
    }
}
