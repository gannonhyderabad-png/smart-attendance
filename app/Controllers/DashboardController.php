<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Department;

class DashboardController extends Controller {
    public function index(): void {
        Auth::requireAuth();

        $stats = Attendance::getDashboardStats();
        $departments = Department::all();

        $this->view('dashboard.index', [
            'title' => 'Dashboard Overview',
            'stats' => $stats,
            'departments' => $departments
        ], 'admin');
    }
}
