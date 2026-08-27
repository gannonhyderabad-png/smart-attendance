<?php

namespace App\Core;

abstract class Controller {
    protected function view(string $viewPath, array $data = [], string $layout = 'admin'): void {
        extract($data);
        $user = Auth::user();

        // Flash message handling
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $viewFile = __DIR__ . '/../../views/' . str_replace('.', '/', $viewPath) . '.php';
        
        if (!file_exists($viewFile)) {
            die("View [{$viewPath}] not found at {$viewFile}");
        }

        // Buffer the view content
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Render layout
        if ($layout) {
            $candidates = [
                __DIR__ . '/../../views/layouts/' . $layout . '.php',
                __DIR__ . '/../../views/Layouts/' . $layout . '.php',
                __DIR__ . '/../../views/' . $layout . '.php',
                __DIR__ . '/../views/layouts/' . $layout . '.php',
                __DIR__ . '/../../layouts/' . $layout . '.php',
            ];
            $found = false;
            foreach ($candidates as $layoutFile) {
                if (file_exists($layoutFile)) {
                    include $layoutFile;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                // Self-contained high-fidelity fallback layout with Bootstrap 5 CDN & FontAwesome
                ?>
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title><?= htmlspecialchars($title ?? 'Dashboard') ?> | Smart Attendance</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
                    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <style>
                        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #1e293b; }
                        .navbar-brand { font-weight: 700; }
                    </style>
                </head>
                <body>
                    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3">
                        <div class="container-fluid px-4">
                            <a class="navbar-brand d-flex align-items-center" href="<?= base_url('dashboard') ?>">
                                <i class="fa-solid fa-fingerprint text-primary fs-4 me-2"></i>
                                <span>Smart<span class="text-primary">Punch</span></span>
                            </a>
                            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse" id="topNav">
                                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                                    <li class="nav-item"><a class="nav-link fw-semibold text-white px-3" href="<?= base_url('dashboard') ?>"><i class="fa-solid fa-gauge-high me-1"></i> Dashboard</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50 px-3" href="<?= base_url('employees') ?>"><i class="fa-solid fa-users me-1"></i> Staff Directory</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50 px-3" href="<?= base_url('attendance') ?>"><i class="fa-solid fa-calendar-check me-1"></i> Attendance Logs</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50 px-3" href="<?= base_url('reports/daily') ?>"><i class="fa-solid fa-chart-pie me-1"></i> Daily Reports</a></li>
                                </ul>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="<?= base_url('logout') ?>" class="btn btn-outline-light btn-sm rounded-pill px-3"><i class="fa-solid fa-right-from-bracket me-1"></i> Sign Out</a>
                                </div>
                            </div>
                        </div>
                    </nav>
                    <main class="container-fluid p-4">
                        <?= $content ?>
                    </main>
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
                </body>
                </html>
                <?php
            }
        } else {
            echo $content;
        }
    }

    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function setFlash(string $type, string $message): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_' . $type] = $message;
    }

    protected function render(string $viewPath, array $data = [], string $layout = 'admin'): void {
        $this->view($viewPath, $data, $layout);
    }
}
