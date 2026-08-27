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
        $employees = Employee::all(['status' => 'active']);
        $departments = Department::all();
        $siteList = Employee::getDistinctSites();

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

        $companyQuotas = Leave::getCompanyAssignedLeaves();
        $employeeBalances = Leave::getAllEmployeesLeaveBalances();

        $this->view('leaves.index', [
            'title' => 'Employee Leave Management',
            'leaves' => $leaves,
            'employees' => $employees,
            'departments' => $departments,
            'siteList' => $siteList,
            'companyQuotas' => $companyQuotas,
            'employeeBalances' => $employeeBalances,
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

        $employeeInput = Request::input('employee_id');
        $leaveType = trim(Request::input('leave_type', 'Casual Leave'));
        $startDate = trim(Request::input('start_date', ''));
        $endDate = trim(Request::input('end_date', ''));
        $originSite = trim(Request::input('origin_site', ''));
        $targetSite = trim(Request::input('target_site', ''));
        $reason = trim(Request::input('reason', ''));
        $returnUrl = Request::input('return_url', 'leaves');

        $emp = Employee::resolveEmployee($employeeInput);
        if (!$emp) {
            $_SESSION['flash_error'] = 'Selected employee was not found. Please type a valid employee code or name.';
            redirect($returnUrl);
        }
        $employeeId = (int) $emp['id'];

        if (empty($startDate)) {
            $_SESSION['flash_error'] = 'Start Date is required.';
            redirect($returnUrl);
        }

        if (empty($endDate)) {
            $endDate = $startDate;
        }

        if ($endDate < $startDate) {
            $_SESSION['flash_error'] = 'End Date cannot be earlier than Start Date.';
            redirect($returnUrl);
        }

        // If Origin site is empty and employee has a home site, default to it
        if (empty($originSite) && !empty($emp['site'])) {
            $originSite = $emp['site'];
        }

        // Handle PDF/Document attachment upload
        $attachmentPath = null;
        if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $attachmentPath = upload_leave_attachment($_FILES['attachment']);
        }

        try {
            Leave::create([
                'employee_id' => $employeeId,
                'leave_type' => $leaveType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'origin_site' => $originSite,
                'target_site' => $targetSite,
                'attachment' => $attachmentPath,
                'reason' => $reason,
                'status' => 'APPROVED'
            ]);

            $dateLabel = ($startDate === $endDate) ? $startDate : "{$startDate} to {$endDate}";
            $siteDetails = !empty($targetSite) ? " (Target: {$targetSite})" : "";
            $docDetails = !empty($attachmentPath) ? " [Document Attached]" : "";
            Logger::log(Auth::id(), 'LEAVE_RECORDED', "Recorded {$leaveType}{$siteDetails}{$docDetails} for {$emp['name']} ({$emp['employee_code']}) for {$dateLabel}");
            $_SESSION['flash_success'] = "Leave entry for {$emp['name']} ({$leaveType}{$siteDetails}, {$dateLabel}) recorded successfully!";
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

    /**
     * Update Company Standard Leave Quota (Admin Permission)
     */
    public function updateCompanyQuota(): void {
        Auth::requireAuth();
        Csrf::verify();

        $cl = max(0, (float) Request::input('cl_quota', 12));
        $sl = max(0, (float) Request::input('sl_quota', 10));
        $pl = max(0, (float) Request::input('pl_quota', 15));
        $total = $cl + $sl + $pl;

        \App\Models\Setting::set('company_assigned_cl', (string)$cl);
        \App\Models\Setting::set('company_assigned_sl', (string)$sl);
        \App\Models\Setting::set('company_assigned_pl', (string)$pl);
        \App\Models\Setting::set('company_assigned_total', (string)$total);

        Logger::log(Auth::id(), 'COMPANY_LEAVE_QUOTA_UPDATED', "Updated company standard leave quotas: CL={$cl}, SL={$sl}, PL={$pl}, Total={$total} Days");
        $_SESSION['flash_success'] = "Company Assigned Leave Quota updated successfully! (CL: {$cl}, SL: {$sl}, PL: {$pl} = Total {$total} Days/Year)";
        redirect('leaves');
    }

    /**
     * Update Individual Employee Custom Leave Quota (Admin Permission)
     */
    public function updateEmployeeQuota(): void {
        Auth::requireAuth();
        Csrf::verify();

        $employeeId = (int) Request::input('employee_id', 0);
        $emp = Employee::find($employeeId);
        if (!$emp) {
            $_SESSION['flash_error'] = 'Employee not found.';
            redirect('leaves');
        }

        $useDefault = Request::input('use_company_default');
        $pdo = \Database\Database::getConnection();
        if ($useDefault) {
            $stmt = $pdo->prepare("UPDATE employees SET cl_quota = NULL, sl_quota = NULL, pl_quota = NULL WHERE id = ?");
            $stmt->execute([$employeeId]);
            Logger::log(Auth::id(), 'EMPLOYEE_LEAVE_QUOTA_RESET', "Reset {$emp['name']} ({$emp['employee_code']}) leave quota to Company Standard");
            $_SESSION['flash_success'] = "{$emp['name']}'s leave quota reset to company standard.";
        } else {
            $cl = max(0, (float) Request::input('cl_quota', 12));
            $sl = max(0, (float) Request::input('sl_quota', 10));
            $pl = max(0, (float) Request::input('pl_quota', 15));

            $stmt = $pdo->prepare("UPDATE employees SET cl_quota = ?, sl_quota = ?, pl_quota = ? WHERE id = ?");
            $stmt->execute([$cl, $sl, $pl, $employeeId]);

            $total = $cl + $sl + $pl;
            Logger::log(Auth::id(), 'EMPLOYEE_LEAVE_QUOTA_UPDATED', "Updated {$emp['name']} ({$emp['employee_code']}) custom leave quota: CL={$cl}, SL={$sl}, PL={$pl}, Total={$total} Days");
            $_SESSION['flash_success'] = "Custom leave quota for {$emp['name']} updated: {$cl} CL, {$sl} SL, {$pl} PL (Total {$total} Days).";
        }

        redirect('leaves');
    }
}
