<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Csrf;
use App\Core\Logger;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Attendance;

class EmployeeController extends Controller {
    public function index(): void {
        Auth::requireAuth();

        $view = Request::input('view', 'active');
        $search = Request::input('search', '');
        $department = Request::input('department', Request::input('department_id', ''));
        $site = Request::input('site', '');
        $status = Request::input('status', '');

        $employees = Employee::all([
            'view' => $view,
            'search' => $search,
            'department' => $department,
            'site' => $site,
            'status' => $status
        ]);

        $departments = Department::all();
        $departmentList = Employee::getDistinctDepartments();
        $siteList = Employee::getDistinctSites();
        $projectList = Employee::getDistinctProjects();
        $trashCount = Employee::countTrash();
        $activeCount = Employee::count();

        $this->view('employees.index', [
            'title' => $view === 'trash' ? 'Employee Recycle Bin' : 'Employee Directory',
            'employees' => $employees,
            'departments' => $departments,
            'departmentList' => $departmentList,
            'siteList' => $siteList,
            'projectList' => $projectList,
            'currentView' => $view,
            'trashCount' => $trashCount,
            'activeCount' => $activeCount,
            'filters' => [
                'view' => $view,
                'search' => $search,
                'department' => $department,
                'site' => $site,
                'status' => $status
            ]
        ], 'admin');
    }

    public function create(): void {
        Auth::requireAuth();

        global $appConfig;
        $suggestedCode = Employee::generateNextCode('');
        $departments = Department::all();
        $departmentList = Employee::getDistinctDepartments();
        $siteList = Employee::getDistinctSites();
        $projectList = Employee::getDistinctProjects();

        $this->view('employees.create', [
            'title' => 'Add New Employee',
            'suggestedCode' => $suggestedCode,
            'departments' => $departments,
            'departmentList' => $departmentList,
            'siteList' => $siteList,
            'projectList' => $projectList
        ], 'admin');
    }

    public function store(): void {
        Auth::requireAuth();

        if (!Csrf::validate(Request::input('_csrf_token'))) {
            $this->setFlash('error', 'Invalid security token.');
            redirect('employees/create');
        }

        $code = trim(Request::input('employee_code', ''));
        $name = trim(Request::input('name', ''));
        $email = trim(Request::input('email', ''));
        $phone = trim(Request::input('phone', ''));
        $department = trim(Request::input('department', Request::input('department_name', '')));
        $departmentId = Request::input('department_id');
        $designation = trim(Request::input('designation', ''));
        $project = trim(Request::input('project', ''));
        $site = trim(Request::input('site', ''));
        $siteLatitude = Request::input('site_latitude');
        $siteLongitude = Request::input('site_longitude');
        $siteRadius = Request::input('site_radius', '200');
        $geofenceEnabled = Request::input('geofence_enabled', '1');
        $shiftStart = Request::input('shift_start', '09:00:00');
        $shiftEnd = Request::input('shift_end', '18:00:00');
        $status = Request::input('status', 'active');

        if (empty($code) || empty($name)) {
            $this->setFlash('error', 'Employee Code and Full Name are required.');
            redirect('employees/create');
        }

        // Check if code already exists
        if (Employee::findByCode($code)) {
            $this->setFlash('error', "Employee Code '{$code}' already exists. Please choose another code.");
            redirect('employees/create');
        }

        // Handle photo upload
        $photoPath = null;
        if (!empty($_FILES['photo']['name'])) {
            $photoPath = upload_employee_avatar($_FILES['photo']);
        }

        $empId = Employee::create([
            'employee_code' => $code,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'department' => $department,
            'department_id' => $departmentId,
            'designation' => $designation,
            'project' => $project,
            'site' => $site,
            'site_latitude' => ($siteLatitude !== '' && $siteLatitude !== null) ? $siteLatitude : null,
            'site_longitude' => ($siteLongitude !== '' && $siteLongitude !== null) ? $siteLongitude : null,
            'site_radius' => ($siteRadius !== '' && $siteRadius !== null) ? (int) $siteRadius : 200,
            'geofence_enabled' => (int) $geofenceEnabled,
            'photo' => $photoPath,
            'shift_start' => $shiftStart,
            'shift_end' => $shiftEnd,
            'status' => $status
        ]);

        Logger::log(Auth::id(), 'EMPLOYEE_CREATED', "Created employee {$name} (Code: {$code}, Project: {$project}, Site: {$site})");
        $this->setFlash('success', "Employee '{$name}' created successfully with Attendance URL ready!");
        redirect('employees/view/' . $empId);
    }

    public function edit(string $id): void {
        Auth::requireAuth();

        $employee = Employee::find((int) $id);
        if (!$employee) {
            $this->setFlash('error', 'Employee not found.');
            redirect('employees');
        }

        $departments = Department::all();
        $departmentList = Employee::getDistinctDepartments();
        $siteList = Employee::getDistinctSites();
        $projectList = Employee::getDistinctProjects();

        $this->view('employees.edit', [
            'title' => 'Edit Employee - ' . $employee['name'],
            'employee' => $employee,
            'departments' => $departments,
            'departmentList' => $departmentList,
            'siteList' => $siteList,
            'projectList' => $projectList
        ], 'admin');
    }

    public function update(string $id): void {
        Auth::requireAuth();

        if (!Csrf::validate(Request::input('_csrf_token'))) {
            $this->setFlash('error', 'Invalid security token.');
            redirect('employees/edit/' . $id);
        }

        $empId = (int) $id;
        $employee = Employee::find($empId);
        if (!$employee) {
            $this->setFlash('error', 'Employee not found.');
            redirect('employees');
        }

        $code = trim(Request::input('employee_code', ''));
        $name = trim(Request::input('name', ''));
        $email = trim(Request::input('email', ''));
        $phone = trim(Request::input('phone', ''));
        $department = trim(Request::input('department', Request::input('department_name', '')));
        $departmentId = Request::input('department_id');
        $designation = trim(Request::input('designation', ''));
        $project = trim(Request::input('project', ''));
        $site = trim(Request::input('site', ''));
        $siteLatitude = Request::input('site_latitude');
        $siteLongitude = Request::input('site_longitude');
        $siteRadius = Request::input('site_radius', '200');
        $geofenceEnabled = Request::input('geofence_enabled', '1');
        $shiftStart = Request::input('shift_start', '09:00:00');
        $shiftEnd = Request::input('shift_end', '18:00:00');
        $status = Request::input('status', 'active');

        if (empty($code) || empty($name)) {
            $this->setFlash('error', 'Employee Code and Full Name are required.');
            redirect('employees/edit/' . $id);
        }

        // Check if new code conflicts with another employee
        $existing = Employee::findByCode($code);
        if ($existing && $existing['id'] !== $empId) {
            $this->setFlash('error', "Employee Code '{$code}' is already used by another employee.");
            redirect('employees/edit/' . $id);
        }

        // Handle photo upload
        $photoPath = $employee['photo'] ?? null;
        if (!empty($_FILES['photo']['name'])) {
            $uploaded = upload_employee_avatar($_FILES['photo']);
            if ($uploaded) {
                $photoPath = $uploaded;
            }
        }

        Employee::update($empId, [
            'employee_code' => $code,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'department' => $department,
            'department_id' => $departmentId,
            'designation' => $designation,
            'project' => $project,
            'site' => $site,
            'site_latitude' => ($siteLatitude !== '' && $siteLatitude !== null) ? $siteLatitude : null,
            'site_longitude' => ($siteLongitude !== '' && $siteLongitude !== null) ? $siteLongitude : null,
            'site_radius' => ($siteRadius !== '' && $siteRadius !== null) ? (int) $siteRadius : 200,
            'geofence_enabled' => (int) $geofenceEnabled,
            'photo' => $photoPath,
            'shift_start' => $shiftStart,
            'shift_end' => $shiftEnd,
            'status' => $status
        ]);

        Logger::log(Auth::id(), 'EMPLOYEE_UPDATED', "Updated employee {$name} (Code: {$code}, Site: {$site})");
        $this->setFlash('success', 'Employee details updated successfully.');
        redirect('employees');
    }

    public function viewDetails(string $id): void {
        Auth::requireAuth();

        $employee = Employee::find((int) $id);
        if (!$employee) {
            $this->setFlash('error', 'Employee not found.');
            redirect('employees');
        }

        $punchUrl = punch_url($employee['employee_code']);
        $recentAttendance = Attendance::getPairedSessions(['employee_id' => $employee['id']], 10, 0);
        $todayWorkSeconds = Attendance::calculateWorkSeconds($employee['id'], date('Y-m-d'));

        $this->view('employees.view', [
            'title' => 'Employee Details - ' . $employee['name'],
            'employee' => $employee,
            'punchUrl' => $punchUrl,
            'recentAttendance' => $recentAttendance,
            'todayWorkSeconds' => $todayWorkSeconds
        ], 'admin');
    }

    /**
     * Move employee to Recycle Bin (Soft Delete)
     */
    public function delete(string $id): void {
        Auth::requireAuth();

        $empId = (int) $id;
        $employee = Employee::find($empId);
        if ($employee) {
            Employee::delete($empId);
            Logger::log(Auth::id(), 'EMPLOYEE_TRASHED', "Moved employee {$employee['name']} ({$employee['employee_code']}) to Recycle Bin");
            $this->setFlash('success', "Employee '{$employee['name']}' moved to Recycle Bin.");
        }

        redirect('employees');
    }

    /**
     * Restore employee from Recycle Bin
     */
    public function restore(string $id): void {
        Auth::requireAuth();

        $empId = (int) $id;
        $employee = Employee::find($empId);
        if ($employee) {
            Employee::restore($empId);
            Logger::log(Auth::id(), 'EMPLOYEE_RESTORED', "Restored employee {$employee['name']} ({$employee['employee_code']}) from Recycle Bin");
            $this->setFlash('success', "Employee '{$employee['name']}' restored back to active staff directory.");
        }

        redirect('employees?view=trash');
    }

    /**
     * Permanently delete employee from database
     */
    public function forceDelete(string $id): void {
        Auth::requireAuth();

        $empId = (int) $id;
        $employee = Employee::find($empId);
        if ($employee) {
            Employee::forceDelete($empId);
            Logger::log(Auth::id(), 'EMPLOYEE_FORCE_DELETED', "Permanently deleted employee {$employee['name']} ({$employee['employee_code']}) and attendance logs");
            $this->setFlash('success', "Employee '{$employee['name']}' permanently wiped from system.");
        }

        redirect('employees?view=trash');
    }

    /**
     * Empty entire Recycle Bin
     */
    public function emptyTrash(): void {
        Auth::requireAuth();

        $count = Employee::emptyTrash();
        Logger::log(Auth::id(), 'TRASH_EMPTIED', "Emptied Recycle Bin, purged {$count} employee record(s)");
        $this->setFlash('success', "Recycle Bin emptied successfully ({$count} employee record(s) removed).");

        redirect('employees');
    }
}
