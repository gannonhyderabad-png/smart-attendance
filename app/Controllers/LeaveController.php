<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Csrf;
use App\Core\Logger;
use App\Models\Leave;
use App\Models\Employee;
use App\Models\Department;

class LeaveController extends Controller {
    public function index(): void {
        Auth::requireAuth();

        $employeeId = Request::input('employee_id');
        $leaveType = Request::input('leave_type');
        $departmentId = Request::input('department_id');

        $conditions = [];
        if ($employeeId) $conditions['employee_id'] = (int) $employeeId;
        if ($leaveType) $conditions['leave_type'] = $leaveType;
        if ($departmentId) $conditions['department_id'] = (int) $departmentId;

        $leaves = Leave::all($conditions, 'l.start_date DESC');
        $employees = Employee::allActive();
        $departments = Department::all();

        // Calculate summary counters
        $totalLeaves = count($leaves);
        $clCount = 0;
        $slCount = 0;
        $plCount = 0;
        $odCount = 0;

        foreach ($leaves as $lv) {
            $code = Leave::getLeaveCode($lv['leave_type']);
            if ($code === 'CL') $clCount += (float)$lv['days_count'];
            elseif ($code === 'SL') $slCount += (float)$lv['days_count'];
            elseif ($code === 'PL') $plCount += (float)$lv['days_count'];
            elseif ($code === 'OD') $odCount += (float)$lv['days_count'];
        }

        $this->view('leaves.index', [
            'title' => 'Employee Leave Management',
            'leaves' => $leaves,
            'employees' => $employees,
            'departments' => $departments,
            'filters' => [
                'employee_id' => $employeeId,
                'leave_type' => $leaveType,
                'department_id' => $departmentId
            ],
            'stats' => [
                'total' => $totalLeaves,
                'cl' => $clCount,
                'sl' => $slCount,
                'pl' => $plCount,
                'od' => $odCount
            ]
        ], 'admin');
    }

    public function store(): void {
        Auth::requireAuth();
        Csrf::verify();

        $employeeId = (int) Request::input('employee_id', 0);
        $leaveType = trim(Request::input('leave_type', 'Casual Leave'));
        $startDate = trim(Request::input('start_date', ''));
        $endDate = trim(Request::input('end_date', ''));
        $reason = trim(Request::input('reason', ''));
        $returnUrl = Request::input('return_url', 'leaves');

        if ($employeeId <= 0 || empty($startDate)) {
            $_SESSION['flash_error'] = 'Employee and Start Date are required.';
            redirect($returnUrl);
        }

        if (empty($endDate)) {
            $endDate = $startDate;
        }

        if ($endDate < $startDate) {
            $_SESSION['flash_error'] = 'End Date cannot be earlier than Start Date.';
            redirect($returnUrl);
        }

        $emp = Employee::find($employeeId);
        if (!$emp) {
            $_SESSION['flash_error'] = 'Selected employee does not exist.';
            redirect($returnUrl);
        }

        try {
            Leave::create([
                'employee_id' => $employeeId,
                'leave_type' => $leaveType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'reason' => $reason,
                'status' => 'APPROVED'
            ]);

            $dateLabel = ($startDate === $endDate) ? $startDate : "{$startDate} to {$endDate}";
            Logger::log(Auth::id(), 'LEAVE_RECORDED', "Recorded {$leaveType} for {$emp['name']} ({$emp['employee_code']}) for {$dateLabel}");
            $_SESSION['flash_success'] = "Leave entry for {$emp['name']} ({$leaveType}, {$dateLabel}) recorded successfully!";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to record leave: ' . $e->getMessage();
        }

        redirect($returnUrl);
    }

    public function delete(): void {
        Auth::requireAuth();
        Csrf::verify();

        $id = (int) Request::input('id', 0);
        $returnUrl = Request::input('return_url', 'leaves');

        if ($id > 0) {
            $leave = Leave::find($id);
            if ($leave) {
                Leave::delete($id);
                Logger::log(Auth::id(), 'LEAVE_DELETED', "Deleted leave #{$id} for {$leave['employee_name']}");
                $_SESSION['flash_success'] = "Leave record deleted successfully.";
            }
        }

        redirect($returnUrl);
    }
}
