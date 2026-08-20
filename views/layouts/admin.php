<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Dashboard') ?> | Smart Attendance Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js for Dashboard visual analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom Admin Styles -->
    <link rel="stylesheet" href="<?= asset_url('css/admin.css') ?>">
</head>
<body class="bg-body-tertiary">
    <div class="wrapper d-flex">
        <!-- Sidebar Navigation -->
        <nav id="sidebar" class="sidebar bg-dark text-white d-flex flex-column flex-shrink-0 p-3">
            <a href="<?= base_url('dashboard') ?>" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none px-2 py-2">
                <div class="sidebar-brand-icon me-2 text-primary fs-3">
                    <i class="fa-solid fa-fingerprint"></i>
                </div>
                <div class="sidebar-brand-text">
                    <span class="fs-5 fw-bold tracking-tight">Smart<span class="text-primary">Punch</span></span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle d-block font-monospace" style="font-size: 0.65rem;">ADMIN v2.0</span>
                </div>
            </a>
            <hr class="text-secondary my-3">

            <ul class="nav nav-pills flex-column mb-auto gap-1">
                <li class="nav-item">
                    <a href="<?= base_url('dashboard') ?>" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'dashboard') ? 'active' : 'text-white-50' ?>">
                        <i class="fa-solid fa-gauge-high me-2 fa-fw"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('employees') ?>" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'employees') ? 'active' : 'text-white-50' ?>">
                        <i class="fa-solid fa-users me-2 fa-fw"></i> Employees
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('attendance') ?>" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'attendance') ? 'active' : 'text-white-50' ?>">
                        <i class="fa-solid fa-calendar-check me-2 fa-fw"></i> Attendance Logs
                    </a>
                </li>
                
                <li class="nav-header text-uppercase text-muted fw-bold small px-3 mt-3 mb-1" style="font-size: 0.75rem;">Reports & Analytics</li>
                <li class="nav-item">
                    <a href="<?= base_url('reports/daily') ?>" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'reports/daily') ? 'active' : 'text-white-50' ?>">
                        <i class="fa-solid fa-chart-pie me-2 fa-fw"></i> Daily Summary
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('reports/monthly') ?>" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'reports/monthly') ? 'active' : 'text-white-50' ?>">
                        <i class="fa-solid fa-table-cells me-2 fa-fw"></i> Monthly Timesheet
                    </a>
                </li>

                <li class="nav-header text-uppercase text-muted fw-bold small px-3 mt-3 mb-1" style="font-size: 0.75rem;">System</li>
                <li class="nav-item">
                    <a href="<?= base_url('logs') ?>" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'logs') ? 'active' : 'text-white-50' ?>">
                        <i class="fa-solid fa-shield-halved me-2 fa-fw"></i> Activity Logs
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('profile') ?>" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'profile') ? 'active' : 'text-white-50' ?>">
                        <i class="fa-solid fa-user-gear me-2 fa-fw"></i> Admin Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('backup/download') ?>" class="nav-link text-white-50">
                        <i class="fa-solid fa-cloud-arrow-down me-2 fa-fw text-success"></i> Backup Data & Photos
                    </a>
                </li>
            </ul>

            <hr class="text-secondary">
            <div class="user-panel d-flex align-items-center justify-content-between px-2">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <div class="user-info">
                        <div class="fw-semibold text-white small text-truncate" style="max-width: 110px;"><?= e($user['name'] ?? 'Admin') ?></div>
                        <div class="text-muted small text-truncate" style="font-size: 0.7rem;"><?= e($user['email'] ?? '') ?></div>
                    </div>
                </div>
                <a href="<?= base_url('logout') ?>" class="btn btn-outline-danger btn-sm" title="Logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </nav>

        <!-- Main Content Area -->
        <div class="main-content flex-grow-1 d-flex flex-column">
            <!-- Top Navbar -->
            <header class="navbar navbar-expand-lg bg-white border-bottom shadow-sm px-3 py-2 sticky-top">
                <div class="container-fluid">
                    <button class="btn btn-outline-secondary d-md-none me-2" id="sidebarToggle">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0 fw-bold text-dark"><?= e($title ?? 'Dashboard') ?></h5>
                    </div>
                </div>
            </header>

            <!-- Page Body -->
            <main class="p-3 p-md-4 flex-grow-1">
                <!-- Toast Alerts / Flash -->
                <?php if (!empty($flashSuccess)): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                        <i class="fa-solid fa-circle-check fs-5 me-2"></i>
                        <div><?= e($flashSuccess) ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($flashError)): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                        <i class="fa-solid fa-circle-exclamation fs-5 me-2"></i>
                        <div><?= e($flashError) ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </main>

            <!-- Admin Footer -->
            <footer class="bg-white border-top py-3 px-4 text-center text-muted small">
                <div class="row align-items-center">
                    <div class="col-md-6 text-md-start">
                        <strong>Smart Attendance Server</strong> &copy; <?= date('Y') ?> &bull; PHP & MySQL Core Engine
                    </div>
                    <div class="col-md-6 text-md-end mt-1 mt-md-0">
                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i> System Live</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Global Punch Selfie Photo Modal (Maximized View) -->
    <div class="modal fade" id="punchPhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 600px;">
            <div class="modal-content rounded-4 border-0 shadow-24 p-3 text-center bg-white">
                <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                    <div class="text-start">
                        <h5 class="fw-bold mb-0 text-dark" id="modalPunchPhotoTitle"><i class="fa-solid fa-camera text-primary me-2"></i>Punch Selfie Snapshot</h5>
                        <small class="text-muted font-monospace" id="modalPunchPhotoTime"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="p-2 bg-dark rounded-4 overflow-hidden shadow-sm mb-3 position-relative d-flex align-items-center justify-content-center" style="min-height: 320px; background: #0f172a !important;">
                    <img id="modalPunchPhotoImg" src="" class="img-fluid rounded-3 object-fit-contain" style="max-height: 520px; width: 100%; transition: transform 0.2s;" alt="Punch Snapshot" onerror="this.src='https://placehold.co/500x400?text=Photo+Not+Found'">
                </div>
                <div class="d-flex gap-2">
                    <a id="modalPunchPhotoOpenBtn" href="#" target="_blank" class="btn btn-primary rounded-pill flex-grow-1 py-2 fw-bold shadow-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Full Screen
                    </a>
                    <a id="modalPunchPhotoDownloadBtn" href="#" download class="btn btn-outline-success rounded-pill px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-download me-1"></i> Save
                    </a>
                    <button type="button" class="btn btn-light border rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- QRCode JS Library (CDN + Local Fallback) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>if (typeof QRCode === 'undefined') { document.write('<script src="<?= asset_url('js/qrcode.min.js') ?>"><\/script>'); }</script>
    <script src="<?= asset_url('js/admin.js') ?>"></script>
</body>
</html>
