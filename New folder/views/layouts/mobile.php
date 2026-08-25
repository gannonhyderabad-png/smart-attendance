<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= e($title ?? 'Employee Attendance Punch') ?> | Smart Attendance</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body.mobile-punch-body {
            background-color: #f1f5f9;
            min-height: 100vh;
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>
<body class="mobile-punch-body">
    <div class="mobile-container d-flex flex-column justify-content-between min-vh-100 py-3 px-3">
        <?= $content ?>
        
        <footer class="text-center text-muted small mt-4 pb-2">
            <div class="d-flex align-items-center justify-content-center gap-1 opacity-75">
                <i class="fa-solid fa-shield-halved text-primary"></i>
                <span>Smart Attendance Server &bull; SSL Secured</span>
            </div>
        </footer>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
