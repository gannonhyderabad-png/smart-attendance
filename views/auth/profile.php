<div class="row g-4 justify-content-center">
    <!-- Left Column: Admin Profile Settings -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-user-gear text-primary me-2"></i>Admin Profile & Security</h5>
                <small class="text-muted">Manage your administrator account credentials and password</small>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="<?= base_url('profile/update') ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Administrator Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                            <input type="text" name="name" class="form-control bg-light border-start-0" value="<?= e($currentUser['name']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted">Administrator Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control bg-light border-start-0" value="<?= e($currentUser['email']) ?>" required>
                        </div>
                    </div>

                    <hr class="my-4 text-secondary opacity-25">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-lock text-secondary me-2"></i>Change Admin Password</h6>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">New Password (leave blank to keep current)</label>
                        <input type="password" name="new_password" class="form-control bg-light" placeholder="••••••••">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control bg-light" placeholder="••••••••">
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Profile
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Full System Backup (Database + Selfie Photos) -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-box-archive text-success me-2"></i>System Backup & Photos</h5>
                <small class="text-muted">Download all live punch images and the complete database</small>
            </div>

            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="alert alert-success border-0 rounded-4 p-3 mb-4">
                        <div class="d-flex align-items-center mb-1">
                            <i class="fa-solid fa-circle-check text-success fs-5 me-2"></i>
                            <strong class="text-success">Complete Package Backup</strong>
                        </div>
                        <p class="small text-muted mb-0">
                            The backup engine bundles everything into a single <code>.zip</code> file including:
                        </p>
                        <ul class="small text-dark mb-0 mt-2 ps-3">
                            <li><strong>All Selfie Punch Pictures</strong> (<code>public/uploads/punches/</code>)</li>
                            <li><strong>All Employee Avatar Photos</strong> (<code>public/uploads/avatars/</code>)</li>
                            <li><strong>SQLite Database File</strong> (<code>database/attendance.sqlite</code>)</li>
                            <li><strong>SQL Database Dump</strong> (<code>database/database_dump.sql</code>)</li>
                        </ul>
                    </div>

                    <div class="p-3 bg-light rounded-4 border mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-semibold text-muted">Cloud Host:</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">Render Production</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-semibold text-muted">Storage Format:</span>
                            <span class="badge bg-secondary-subtle text-dark border">ZIP Archive (.zip)</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small fw-semibold text-muted">Includes:</span>
                            <span class="small text-dark fw-bold">DB + All Photos + Logs</span>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <a href="<?= base_url('backup/download') ?>" class="btn btn-success rounded-pill shadow-sm py-2 fw-bold">
                        <i class="fa-solid fa-download me-2"></i> Download Full Backup (.ZIP)
                    </a>
                </div>

                <hr class="my-3 text-secondary opacity-25">

                <!-- Restore Backup Form -->
                <div>
                    <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i>Restore / Upload Backup</h6>
                    <p class="text-muted small mb-2">Upload a previously downloaded <code>.zip</code> file to restore all photos and attendance logs.</p>
                    <form method="POST" action="<?= base_url('backup/restore') ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="input-group input-group-sm mb-1">
                            <input type="file" name="backup_file" class="form-control" accept=".zip,.sqlite,.sql" required>
                            <button type="submit" class="btn btn-primary px-3 fw-semibold" onclick="return confirm('Restore this backup? This will update the database and restore all included photos.')">
                                <i class="fa-solid fa-rotate-left me-1"></i> Restore Data
                            </button>
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">Supports: <code>.zip</code> (Full bundle), <code>.sqlite</code>, <code>.sql</code></small>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
