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

        $allLeaves = Leave::all([], 'l.start_date DESC');
        $leavesByEmployee = [];
        foreach ($allLeaves as $l) {
            $leavesByEmployee[$l['employee_id']][] = $l;
        }

        // Fetch all pending leave applications for Dept Heads & HR approval
        $pendingLeaves = Leave::all(['status' => 'PENDING'], 'l.id DESC');

        $selectedDepartment = null;
        if (!empty($departmentId)) {
            $selectedDepartment = Department::find((int) $departmentId);
        }

        $this->view('leaves.index', [
            'title' => 'Employee Leave Management',
            'leaves' => $leaves,
            'pendingLeaves' => $pendingLeaves,
            'employees' => $employees,
            'departments' => $departments,
            'selectedDepartment' => $selectedDepartment,
            'siteList' => $siteList,
            'companyQuotas' => $companyQuotas,
            'employeeBalances' => $employeeBalances,
            'leavesByEmployee' => $leavesByEmployee,
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
                'od' => $odCount,
                'pending' => count($pendingLeaves)
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

    /**
     * 3-Step Hierarchical Leave Approval
     * Step 1: Department Head -> Step 2: HR Manager -> Step 3: Final Management
     */
    public function approve(): void {
        Auth::requireAuth();
        Csrf::verify();

        $id = (int) Request::input('id', 0);
        $directApprove = (int) Request::input('direct_approve', 0);

        if ($id <= 0) {
            redirect('leaves');
        }

        $leave = Leave::find($id);
        if (!$leave) {
            redirect('leaves');
        }

        $pdo = \Database\Database::getConnection();
        $nowStr = date('Y-m-d H:i:s');
        $userId = Auth::id();
        $user = Auth::user();
        $userName = $user['name'] ?? 'Administrator';

        if ($directApprove === 1) {
            // Fast-track / Direct approve all 3 steps at once (Super Admin privilege)
            $stmt = $pdo->prepare("UPDATE leaves SET 
                status = 'APPROVED', 
                approval_step = 4, 
                step1_approved_by = COALESCE(step1_approved_by, ?),
                step1_approved_by_name = COALESCE(step1_approved_by_name, ?),
                step1_approved_at = COALESCE(step1_approved_at, ?),
                step2_approved_by = COALESCE(step2_approved_by, ?),
                step2_approved_by_name = COALESCE(step2_approved_by_name, ?),
                step2_approved_at = COALESCE(step2_approved_at, ?),
                step3_approved_by = COALESCE(step3_approved_by, ?),
                step3_approved_by_name = COALESCE(step3_approved_by_name, ?),
                step3_approved_at = COALESCE(step3_approved_at, ?),
                approved_by = ?, 
                approved_at = ? 
                WHERE id = ?");
            $stmt->execute([
                $userId, $userName, $nowStr,
                $userId, $userName, $nowStr,
                $userId, $userName, $nowStr,
                $userId, $nowStr, $id
            ]);

            Logger::log($userId, 'LEAVE_DIRECT_APPROVED', "Directly approved all steps for leave #{$id} ({$leave['employee_name']})");
            $_SESSION['flash_success'] = "Leave for {$leave['employee_name']} has been Direct Approved (All 3 Steps Cleared)!";
            try { \App\Services\MailService::sendLeaveDecision($leave, 'APPROVED', $userName); } catch (\Throwable $e) {}
        } else {
            $currentStep = (int) ($leave['approval_step'] ?? 1);
            if ($currentStep <= 1) {
                // Step 1: Department Head Review & Approval -> Moves to Step 2 (HR)
                $stmt = $pdo->prepare("UPDATE leaves SET 
                    approval_step = 2, 
                    status = 'PENDING_LEVEL_2',
                    step1_approved_by = ?,
                    step1_approved_by_name = ?,
                    step1_approved_at = ?
                    WHERE id = ?");
                $stmt->execute([$userId, $userName, $nowStr, $id]);

                Logger::log($userId, 'LEAVE_STEP1_APPROVED', "Department Head ({$userName}) approved Step 1 for leave #{$id} ({$leave['employee_name']}). Now forwarded to HR.");
                $_SESSION['flash_success'] = "✓ Step 1 Approved by Department Head ({$userName})! Request forwarded to Step 2: HR Review.";
                try { \App\Services\MailService::sendStepApproved($leave, 1, $userName); } catch (\Throwable $e) {}
            } elseif ($currentStep === 2) {
                // Step 2: HR Manager Approval -> Moves to Step 3 (Final Management)
                $stmt = $pdo->prepare("UPDATE leaves SET 
                    approval_step = 3, 
                    status = 'PENDING_LEVEL_3',
                    step2_approved_by = ?,
                    step2_approved_by_name = ?,
                    step2_approved_at = ?
                    WHERE id = ?");
                $stmt->execute([$userId, $userName, $nowStr, $id]);

                Logger::log($userId, 'LEAVE_STEP2_APPROVED', "HR ({$userName}) approved Step 2 for leave #{$id} ({$leave['employee_name']}). Now forwarded to Management.");
                $_SESSION['flash_success'] = "✓ Step 2 Approved by HR ({$userName})! Request forwarded to Step 3: Final Management Sign-off.";
                try { \App\Services\MailService::sendStepApproved($leave, 2, $userName); } catch (\Throwable $e) {}
            } else {
                // Step 3: Final Management Sign-off -> Status becomes fully APPROVED
                $stmt = $pdo->prepare("UPDATE leaves SET 
                    approval_step = 4, 
                    status = 'APPROVED',
                    step3_approved_by = ?,
                    step3_approved_by_name = ?,
                    step3_approved_at = ?,
                    approved_by = ?,
                    approved_at = ?
                    WHERE id = ?");
                $stmt->execute([$userId, $userName, $nowStr, $userId, $nowStr, $id]);

                Logger::log($userId, 'LEAVE_STEP3_APPROVED', "Final Authority ({$userName}) approved Step 3 for leave #{$id} ({$leave['employee_name']}). Fully Approved.");
                $_SESSION['flash_success'] = "✓ Final Step 3 Approved! Leave request for {$leave['employee_name']} is now Fully Approved and recorded.";
                try { \App\Services\MailService::sendLeaveDecision($leave, 'APPROVED', $userName); } catch (\Throwable $e) {}
            }
        }
        redirect('leaves');
    }

    /**
     * Reject a pending leave request at the current approval level
     */
    public function reject(): void {
        Auth::requireAuth();
        Csrf::verify();

        $id = (int) Request::input('id', 0);
        $rejectReason = trim((string) Request::input('reject_reason', 'Not approved'));

        if ($id > 0) {
            $leave = Leave::find($id);
            if ($leave) {
                $pdo = \Database\Database::getConnection();
                $nowStr = date('Y-m-d H:i:s');
                $userId = Auth::id();
                $currentStep = (int) ($leave['approval_step'] ?? 1);

                $stmt = $pdo->prepare("UPDATE leaves SET 
                    status = 'REJECTED', 
                    reject_reason = ?, 
                    rejected_at_step = ?, 
                    approved_by = ?, 
                    approved_at = ? 
                    WHERE id = ?");
                $stmt->execute([$rejectReason, $currentStep, $userId, $nowStr, $id]);

                Logger::log($userId, 'LEAVE_REJECTED', "Rejected leave #{$id} at Step {$currentStep} for {$leave['employee_name']}: {$rejectReason}");
                $_SESSION['flash_error'] = "Leave request for {$leave['employee_name']} has been Rejected at Step {$currentStep}.";
                try { \App\Services\MailService::sendLeaveDecision($leave, 'REJECTED', Auth::user()['name'] ?? 'Admin', $rejectReason); } catch (\Throwable $e) {}
            }
        }
        redirect('leaves');
    }

    /**
     * Employee self-service leave application (from mobile punch page)
     * Starts at Level 1 (Step 1 / 3: Awaiting Department Head)
     */
    public function applyLeave(string $code = ''): void {
        header('Content-Type: application/json; charset=utf-8');

        // This is a public endpoint — no Auth required, validate employee code from URL or body
        $code = trim($code ?: (string) Request::input('employee_code', ''));
        $emp = Employee::findByCode($code);

        if (!$emp) {
            echo json_encode(['success' => false, 'message' => 'Employee code not recognized.']);
            exit;
        }

        $leaveType = trim((string) Request::input('leave_type', 'Casual Leave'));
        $startDate = trim((string) Request::input('start_date', date('Y-m-d')));
        $endDate = trim((string) Request::input('end_date', $startDate));
        $reason = trim((string) Request::input('reason', ''));
        $targetSite = trim((string) Request::input('target_site', ''));

        if (empty($startDate)) {
            echo json_encode(['success' => false, 'message' => 'Start date is required.']);
            exit;
        }
        if ($endDate < $startDate) {
            $endDate = $startDate;
        }

        $startTs = strtotime($startDate);
        $endTs = strtotime($endDate);
        $daysCount = max(1.0, round(($endTs - $startTs) / 86400) + 1.0);

        try {
            $leaveId = Leave::create([
                'employee_id' => $emp['id'],
                'leave_type' => $leaveType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days_count' => $daysCount,
                'origin_site' => $emp['site'] ?? '',
                'target_site' => $targetSite,
                'attachment' => null,
                'reason' => $reason,
                'status' => 'PENDING_LEVEL_1',
                'approval_step' => 1
            ]);

            // Mark applied_by_employee
            try {
                $pdo = \Database\Database::getConnection();
                $pdo->prepare("UPDATE leaves SET applied_by_employee = 1 WHERE id = ?")->execute([$leaveId]);
            } catch (\Throwable $e) {}

            Logger::log(null, 'EMPLOYEE_LEAVE_APPLIED', "Employee {$emp['name']} ({$emp['employee_code']}) applied for {$leaveType} ({$daysCount}d) from {$startDate} to {$endDate}. Level 1 approval pending.");

            // Dispatch Email Notification to Department Head & HR
            try {
                $fullLeave = Leave::find($leaveId);
                if ($fullLeave) {
                    \App\Services\MailService::sendLeaveApplied($fullLeave);
                }
            } catch (\Throwable $e) {
                Logger::log(null, 'MAIL_DISPATCH_FAIL', 'Failed to dispatch leave applied email: ' . $e->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => "Your {$leaveType} request for " . ($daysCount == 1 ? "1 day" : "{$daysCount} days") . " has been submitted successfully!\n\nStatus: ⏳ Step 1 / 3: Awaiting Department Head Approval."
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to submit request: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Save / Update Department details and Approving Authority
     */
    public function updateDepartmentHead(): void {
        Auth::requireAuth();
        Csrf::verify();

        $deptId = (int) Request::input('department_id', 0);
        $name = trim((string) Request::input('name', ''));
        $code = trim((string) Request::input('code', ''));
        $description = trim((string) Request::input('description', ''));
        $headName = trim((string) Request::input('head_name', ''));
        $headEmail = trim((string) Request::input('head_email', ''));

        if ($deptId > 0) {
            $existing = Department::find($deptId);
            if ($existing) {
                $deptName = !empty($name) ? $name : $existing['name'];
                $deptCode = !empty($code) ? $code : $existing['code'];
                $deptDesc = $description !== '' ? $description : ($existing['description'] ?? '');
                $head = !empty($headName) ? $headName : ($existing['head_name'] ?? '');
                $email = $headEmail !== '' ? $headEmail : ($existing['head_email'] ?? '');

                Department::update($deptId, $deptName, $deptCode, $deptDesc, $head, $email);
                Logger::log(Auth::id(), 'DEPARTMENT_UPDATED', "Updated department #{$deptId} '{$deptName}' (Head: {$head})");
                $_SESSION['flash_success'] = "Department '{$deptName}' & Approving Authority updated successfully!";
            }
        } else {
            // New department creation
            if (!empty($name)) {
                $newId = Department::create($name, $code ?: null, $description ?: null, $headName ?: null, $headEmail ?: null);
                Logger::log(Auth::id(), 'DEPARTMENT_CREATED', "Created new department '{$name}' (Head: {$headName})");
                $_SESSION['flash_success'] = "New Department '{$name}' created successfully!";
                $deptId = $newId;
            } else {
                $_SESSION['flash_error'] = "Department Name is required.";
            }
        }
        redirect('leaves' . ($deptId > 0 ? '?department_id=' . $deptId : ''));
    }

    /**
     * Delete an unused department
     */
    public function deleteDepartment(): void {
        Auth::requireAuth();
        Csrf::verify();

        $deptId = (int) Request::input('department_id', 0);
        if ($deptId > 0) {
            $dept = Department::find($deptId);
            if ($dept) {
                $pdo = \Database\Database::getConnection();
                $empCount = (int) $pdo->query("SELECT COUNT(*) FROM employees WHERE department_id = {$deptId}")->fetchColumn();
                if ($empCount > 0) {
                    $_SESSION['flash_error'] = "Cannot delete department '{$dept['name']}': {$empCount} employee(s) are assigned to it.";
                } else {
                    Department::delete($deptId);
                    Logger::log(Auth::id(), 'DEPARTMENT_DELETED', "Deleted department #{$deptId} ({$dept['name']})");
                    $_SESSION['flash_success'] = "Department '{$dept['name']}' deleted successfully.";
                }
            }
        }
        redirect('leaves');
    }
}
