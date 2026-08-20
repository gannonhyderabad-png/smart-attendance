<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Models\ActivityLog;

class LogController extends Controller {
    public function index(): void {
        Auth::requireAuth();

        $page = max(1, (int) Request::input('page', 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $logs = ActivityLog::all($limit, $offset);
        $total = ActivityLog::count();
        $totalPages = ceil($total / $limit);

        $this->view('logs.index', [
            'title' => 'System & Audit Logs',
            'logs' => $logs,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ], 'admin');
    }
}
