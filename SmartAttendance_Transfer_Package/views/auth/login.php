<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4" style="max-width: 440px;">
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden" style="animation: gdclTextSlideUp 0.55s ease-out both;">
            <div class="card-header text-white text-center py-4 px-3 border-0 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 52%, #2563eb 100%);">
                <div class="my-2">
                    <div class="gdcl-logo-emblem" title="GDCL HYD">
                        <img src="<?= asset_url('images/gdcl-logo.png') ?>" alt="GDCL HYD Logo" class="gdcl-logo-img">
                    </div>
                </div>
                <h4 class="fw-extrabold mb-1 mt-2" style="font-weight: 800; letter-spacing: -0.3px; animation: gdclTextSlideUp 0.65s ease-out 0.15s both;">
                    Welcome to GDCL HYD
                </h4>
                <p class="small text-white-50 mb-0" style="animation: gdclTextSlideUp 0.65s ease-out 0.28s both;">
                    Web Attendance Management Portal
                </p>
            </div>
            <div class="card-body p-4 p-md-5 bg-white">
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

                <form method="POST" action="<?= base_url('login') ?>" id="gdclLoginForm">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Login Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control bg-light border-start-0 ps-0" placeholder="admin@attendance.local" required autofocus>
                        </div>
                    </div>

                                        <div class="mb-4">
                        <label class="form-label text-muted small fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" class="form-control bg-light border-start-0 ps-0" placeholder="Enter password (e.g. admin123)" required>
                        </div>
                        <div class="form-text small text-muted mt-1 text-center" style="font-size: 0.75rem;">
                            Default: <strong class="text-primary font-monospace">admin@attendance.local</strong> &bull; Pass: <strong class="text-primary font-monospace">admin123</strong>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" id="gdclLoginBtn" class="btn btn-primary py-2 rounded-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-right-to-bracket" id="gdclLoginIcon"></i>
                            <span id="gdclLoginBtnText">Sign In to Web Attendance</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center mt-3 text-white-50 small">
            &copy; <?= date('Y') ?> <strong class="text-white">GDCL HYD</strong> &bull; Web Attendance Portal
        </div>
    </div>
</div>

<script>
document.getElementById('gdclLoginForm')?.addEventListener('submit', function() {
    var btn = document.getElementById('gdclLoginBtn');
    var icon = document.getElementById('gdclLoginIcon');
    var txt = document.getElementById('gdclLoginBtnText');
    if (btn && icon && txt) {
        btn.disabled = true;
        icon.className = 'fa-solid fa-gear fa-spin';
        txt.textContent = 'Signing in to GDCL HYD...';
    }
});
</script>
