<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-primary text-white text-center py-4 border-0">
                <div class="display-6 mb-2"><i class="fa-solid fa-fingerprint"></i></div>
                <h4 class="fw-bold mb-1">Smart Attendance</h4>
                <p class="small text-white-50 mb-0">Admin Control Portal</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <?php if (!empty($flashSuccess)): ?>
                    <div class="alert alert-success alert-dismissible fade show small" role="alert">
                        <i class="fa-solid fa-circle-check me-1"></i> <?= e($flashSuccess) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($flashError)): ?>
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> <?= e($flashError) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= base_url('login') ?>" id="loginForm">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" name="email" id="loginEmail" class="form-control bg-light border-start-0 ps-0" placeholder="name@company.com" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" id="loginPassword" class="form-control bg-light border-start-0 ps-0" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-2 rounded-3 fw-semibold">
                            <i class="fa-solid fa-right-to-bracket me-2"></i> Sign In to Dashboard
                        </button>
                    </div>
                </form>

                <!-- Quick Login Credentials Card -->
                <div class="mt-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="flex-grow-1 border-top"></div>
                        <small class="text-muted fw-semibold text-uppercase" style="font-size:0.65rem;">Quick Login</small>
                        <div class="flex-grow-1 border-top"></div>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-sm btn-outline-dark rounded-3 text-start px-3 py-2 quick-login-btn" data-email="admin@attendance.local" data-pass="admin123">
                            <i class="fa-solid fa-user-shield me-2 text-danger"></i>
                            <strong>Administrator</strong>
                            <span class="float-end badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size:0.6rem;">Full Access</span>
                            <div class="text-muted" style="font-size:0.7rem;">admin@attendance.local</div>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-3 text-start px-3 py-2 quick-login-btn" data-email="hr@attendance.local" data-pass="admin123">
                            <i class="fa-solid fa-id-badge me-2 text-primary"></i>
                            <strong>HR Manager</strong>
                            <span class="float-end badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size:0.6rem;">Step 2</span>
                            <div class="text-muted" style="font-size:0.7rem;">hr@attendance.local &mdash; Employees &amp; Leave Approvals</div>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-3 text-start px-3 py-2 quick-login-btn" data-email="account@attendance.local" data-pass="admin123">
                            <i class="fa-solid fa-briefcase me-2 text-success"></i>
                            <strong>Accounts / HOD</strong>
                            <span class="float-end badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size:0.6rem;">Step 1</span>
                            <div class="text-muted" style="font-size:0.7rem;">account@attendance.local &mdash; Employees &amp; Leave Approvals</div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-3 text-muted small">
            &copy; <?= date('Y') ?> Smart Attendance Server. All rights reserved.
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.quick-login-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('loginEmail').value = this.dataset.email;
        document.getElementById('loginPassword').value = this.dataset.pass;
        document.getElementById('loginForm').submit();
    });
});
</script>
