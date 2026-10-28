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

                <form method="POST" action="<?= base_url('login') ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control bg-light border-start-0 ps-0" placeholder="name@company.com" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" class="form-control bg-light border-start-0 ps-0" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-2 rounded-3 fw-semibold">
                            <i class="fa-solid fa-right-to-bracket me-2"></i> Sign In to Dashboard
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center mt-3 text-muted small">
            &copy; <?= date('Y') ?> Smart Attendance Server. All rights reserved.
        </div>
    </div>
</div>
