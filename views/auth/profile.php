<div class="row g-4 justify-content-center mb-4">
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
                <div class="mb-3">
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

                <hr class="my-3 text-secondary opacity-25">

                <!-- Clean Old Photos Form -->
                <div>
                    <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-broom text-warning me-2"></i>Storage Auto-Cleaner</h6>
                    <p class="text-muted small mb-2">Delete old selfie pictures to free up disk space while preserving all timestamps and work hours in the database.</p>
                    <form method="POST" action="<?= base_url('backup/clean-photos') ?>" class="row g-2 align-items-center">
                        <?= csrf_field() ?>
                        <div class="col-8">
                            <select name="days" class="form-select form-select-sm bg-light">
                                <option value="30">Delete photos older than 30 days</option>
                                <option value="60" selected>Delete photos older than 60 days</option>
                                <option value="90">Delete photos older than 90 days</option>
                                <option value="180">Delete photos older than 6 months</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100 fw-semibold" onclick="return confirm('Clean old punch photos? Make sure you have downloaded a backup ZIP first.')">
                                <i class="fa-solid fa-trash-can me-1"></i> Clean
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($allUsers)): ?>
<!-- Row 2: System User Logins & Roles Management (Admin Only) -->
<div class="row g-4 justify-content-center">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="fa-solid fa-users-gear text-primary me-2"></i>System Logins &amp; Role Management
                    </h5>
                    <p class="text-muted small mb-0">Create and manage role-based login credentials for 3-Step Leave Approvals and Employee Directory access.</p>
                </div>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    <i class="fa-solid fa-user-plus me-1"></i> Create New Login
                </button>
            </div>

            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">User / Name</th>
                                <th>Login &amp; Company Mail</th>
                                <th>Assigned Role &amp; Permissions</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $u): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 38px; height: 38px; background: <?= $u['role'] === 'admin' ? '#dc3545' : ($u['role'] === 'hr' ? '#0d6efd' : '#198754') ?>;">
                                                <?= strtoupper(substr($u['name'] ?? 'U', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= e($u['name']) ?></div>
                                                <small class="text-muted" style="font-size:0.7rem;">ID #<?= $u['id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><code class="text-dark bg-light px-2 py-1 rounded font-monospace small" title="System Login Username"><?= e($u['email']) ?></code></div>
                                        <?php if (!empty($u['company_mail'])): ?>
                                            <div class="mt-1 small" style="font-size: 0.73rem;">
                                                <span class="text-success fw-semibold" title="Company Notification Mailbox"><i class="fa-solid fa-envelope-open-text me-1"></i><?= e($u['company_mail']) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted small mt-1" style="font-size: 0.68rem;" title="Notifications sent to login email">
                                                <i class="fa-solid fa-arrow-turn-down-right me-1 text-secondary"></i>Mailbox: Uses login mail
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($u['role'] === 'admin' || $u['role'] === 'superadmin'): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-user-shield me-1"></i> System Administrator (Step 3)
                                            </span>
                                            <div class="text-muted small mt-1" style="font-size:0.68rem;">All modules, Step 3 &amp; Direct Fast-Track</div>
                                        <?php elseif ($u['role'] === 'hr'): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-id-badge me-1"></i> HR &amp; Admin (Step 2 Authority)
                                            </span>
                                            <div class="text-muted small mt-1" style="font-size:0.68rem;">Employees &amp; Step 2 Leave Approvals</div>
                                        <?php elseif ($u['role'] === 'account'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-calculator me-1"></i> Accounts Head (Step 1)
                                            </span>
                                            <div class="text-muted small mt-1" style="font-size:0.68rem;">Step 1 Approvals for Accounts Employees</div>
                                        <?php elseif ($u['role'] === 'incharge'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-user-check me-1"></i> Incharge (Step 1)
                                            </span>
                                            <div class="text-muted small mt-1" style="font-size:0.68rem;">Step 1 Approvals for Incharge &amp; General Staff</div>
                                        <?php elseif ($u['role'] === 'coordinator'): ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-diagram-project me-1"></i> Coordinator (Step 1)
                                            </span>
                                            <div class="text-muted small mt-1" style="font-size:0.68rem;">Step 1 Approvals for Coordinator Staff</div>
                                        <?php elseif ($u['role'] === 'tender'): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-file-contract me-1"></i> Tender Head (Step 1)
                                            </span>
                                            <div class="text-muted small mt-1" style="font-size:0.68rem;">Step 1 Approvals for Tender Staff</div>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-user-shield text-primary me-1"></i> <?= e(ucwords(str_replace(['_', '-'], ' ', $u['role']))) ?> (Step 1 Authority)
                                            </span>
                                            <div class="text-muted small mt-1" style="font-size:0.68rem;">Step 1 Approvals for <?= e(ucwords(str_replace(['_', '-'], ' ', $u['role']))) ?> Staff</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (($u['status'] ?? 'active') === 'active'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                                <i class="fa-solid fa-circle me-1" style="font-size:6px;"></i> Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-pill px-2 py-1">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= !empty($u['last_login']) ? date('M d, Y h:i A', strtotime($u['last_login'])) : 'Never logged in' ?>
                                        </small>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-2 py-1 me-1" onclick="openEditUserModal(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)" title="Edit User">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </button>
                                            <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                                                <form action="<?= base_url('users/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete login for <?= addslashes($u['name']) ?> (<?= addslashes($u['email']) ?>)?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger rounded-pill px-2 py-1" title="Delete Login">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border px-2 py-1 small align-self-center">Current Account</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SMTP Mail Server Configuration Card -->
<div class="row g-4 justify-content-center mt-0 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="fa-solid fa-paper-plane text-info me-2"></i>Outbound SMTP Mail Server Configuration
                    </h5>
                    <p class="text-muted small mb-0">Configure your SMTP server so leave applications push real email notifications to authority mailboxes (e.g. <code>hyd.itserv@gdcl.in</code>).</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge <?= !empty($smtpSettings['host']) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' ?> rounded-pill px-3">
                        <i class="fa-solid <?= !empty($smtpSettings['host']) ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> me-1"></i>
                        <?= !empty($smtpSettings['host']) ? 'SMTP Configured' : 'Not Configured (using system mail)' ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- SMTP Settings Form -->
                    <div class="col-lg-7">
                        <form method="POST" action="<?= base_url('settings/smtp') ?>">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" name="smtp_enabled" id="smtpEnabled" value="1" <?= ($smtpSettings['enabled'] ?? '1') !== '0' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold small text-dark" for="smtpEnabled">
                                            Enable Outbound SMTP Delivery
                                        </label>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.72rem;">When enabled, leave notifications are sent directly via authenticated SMTP to authority mailboxes.</small>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-semibold text-muted">SMTP Host / Server <span class="text-danger">*</span></label>
                                    <input type="text" name="smtp_host" class="form-control bg-light" placeholder="e.g. smtp.office365.com or smtp.gmail.com" value="<?= e($smtpSettings['host'] ?? '') ?>">
                                    <small class="text-muted" style="font-size: 0.7rem;">For Microsoft 365: <code>smtp.office365.com</code> &bull; Gmail: <code>smtp.gmail.com</code></small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-muted">SMTP Port</label>
                                    <select name="smtp_port" class="form-select bg-light">
                                        <option value="587" <?= ($smtpSettings['port'] ?? '587') === '587' ? 'selected' : '' ?>>587 (STARTTLS — Recommended)</option>
                                        <option value="465" <?= ($smtpSettings['port'] ?? '') === '465' ? 'selected' : '' ?>>465 (SSL)</option>
                                        <option value="25" <?= ($smtpSettings['port'] ?? '') === '25' ? 'selected' : '' ?>>25 (Plain)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-muted">Encryption</label>
                                    <select name="smtp_encryption" class="form-select bg-light">
                                        <option value="tls" <?= ($smtpSettings['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>STARTTLS (Port 587)</option>
                                        <option value="ssl" <?= ($smtpSettings['encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                                        <option value="none" <?= ($smtpSettings['encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None (Port 25)</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-semibold text-muted">SMTP Username / Login Email</label>
                                    <input type="email" name="smtp_username" class="form-control bg-light" placeholder="e.g. sender@gdcl.in" value="<?= e($smtpSettings['username'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">SMTP Password / App Password</label>
                                    <input type="password" name="smtp_password" class="form-control bg-light" placeholder="Leave blank to keep saved password">
                                    <small class="text-muted" style="font-size: 0.7rem;">For Microsoft 365 / Gmail with MFA: use an App Password.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">From Email Address</label>
                                    <input type="email" name="smtp_from_email" class="form-control bg-light" placeholder="e.g. attendance@gdcl.in" value="<?= e($smtpSettings['from_email'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">From Name (displayed to recipient)</label>
                                    <input type="text" name="smtp_from_name" class="form-control bg-light" placeholder="e.g. SmartPunch Leave System" value="<?= e($smtpSettings['from_name'] ?? 'SmartPunch Attendance') ?>">
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-light border rounded-3 p-2 small text-muted mb-0">
                                        <i class="fa-solid fa-circle-info text-primary me-1"></i>
                                        <strong>Microsoft 365 users:</strong> Use <code>smtp.office365.com</code> port <code>587</code> with STARTTLS. Your login email is your M365 username. Enable <em>Authenticated SMTP</em> in the Exchange admin center if needed.
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-info text-white rounded-pill px-4 shadow-sm fw-bold">
                                        <i class="fa-solid fa-floppy-disk me-1"></i> Save SMTP Settings
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- Test Email Panel -->
                    <div class="col-lg-5">
                        <div class="card border-0 bg-light rounded-4 h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-dark mb-1">
                                    <i class="fa-solid fa-flask text-success me-2"></i>Live Delivery Test
                                </h6>
                                <p class="text-muted small mb-3">Send a test email to verify your SMTP configuration is working and can deliver to your authority mailboxes.</p>
                                <form method="POST" action="<?= base_url('settings/smtp-test') ?>">
                                    <?= csrf_field() ?>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold text-muted">Test Recipient Email</label>
                                        <input type="email" name="test_email" class="form-control" placeholder="e.g. hyd.itserv@gdcl.in" value="<?= e($smtpSettings['username'] ?? '') ?>" required>
                                        <small class="text-muted" style="font-size: 0.7rem;">Enter the authority / company mailbox to test delivery.</small>
                                    </div>
                                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm w-100 fw-bold">
                                        <i class="fa-solid fa-paper-plane me-1"></i> Send Test Email Now
                                    </button>
                                </form>
                                <hr class="my-3 opacity-25">
                                <div class="p-3 bg-white rounded-3 border">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small fw-semibold text-muted">SMTP Status:</span>
                                        <span class="badge <?= !empty($smtpSettings['host']) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border' ?> rounded-pill">
                                            <?= !empty($smtpSettings['host']) ? '🟢 Ready' : '⚪ Not Set' ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small fw-semibold text-muted">Host:</span>
                                        <code class="small"><?= !empty($smtpSettings['host']) ? e($smtpSettings['host'] . ':' . ($smtpSettings['port'] ?? '587')) : '—' ?></code>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small fw-semibold text-muted">Auth User:</span>
                                        <code class="small"><?= !empty($smtpSettings['username']) ? e($smtpSettings['username']) : '—' ?></code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- Modal: Create New System Login -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold" id="createUserModalLabel">
                    <i class="fa-solid fa-user-plus me-2"></i>Create New System Login
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= base_url('users/create') ?>">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control bg-light" placeholder="e.g. Accounts Incharge, HR Reviewer" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Login Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control bg-light" placeholder="e.g. account@attendance.local" required>
                        <small class="text-muted" style="font-size:0.7rem;">Will be used as the username to sign into the system.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">
                            <i class="fa-solid fa-envelope-open-text text-primary me-1"></i>Company Mail / Official Mailbox
                        </label>
                        <input type="email" name="company_mail" id="createCompanyMail" class="form-control bg-light" placeholder="e.g. employee.name@company.com or dept@gammon.com">
                        <small class="text-muted" style="font-size:0.7rem;">
                            When employees in this department apply for leave, email notifications with direct action links will reflect to this company mailbox.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">System Role &amp; Permissions <span class="text-danger">*</span></label>
                        <select name="role_preset" id="createRolePreset" class="form-select bg-light" onchange="handleRolePresetChange(this, 'createCustomRoleBox', 'createRoleInput')">
                            <option value="custom" selected>-- Blank Entry: Custom Authority / Enter Below --</option>
                            <option value="account">Accounts Head (Step 1 Approvals for Accounts)</option>
                            <option value="incharge">Incharge (Step 1 Approvals for Incharge &amp; General Staff)</option>
                            <option value="coordinator">Coordinator (Step 1 Approvals for Coordinator Staff)</option>
                            <option value="tender">Tender Head (Step 1 Approvals for Tender Staff)</option>
                            <option value="hr">HR &amp; Admin (Step 2 Authority &amp; Employee Directory)</option>
                            <option value="admin">System Administrator (Full System Access &amp; Step 3 Sign-off)</option>
                        </select>
                        <div id="createCustomRoleBox" class="mt-2">
                            <label class="form-label small fw-semibold text-primary mb-1">
                                <i class="fa-solid fa-pen-nib me-1"></i> Custom Authority Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="role" id="createRoleInput" class="form-control bg-light border-primary" placeholder="Type custom authority (e.g. Technical, Engineering, Operations, Store...)" required>
                            <div class="text-muted small mt-1" style="font-size:0.7rem;">
                                <i class="fa-solid fa-circle-check text-success me-1"></i> This custom entry is automatically taken as the <strong>Step 1 Approving Authority</strong> for employees in this department/designation.
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Login Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control bg-light" placeholder="Minimum 6 characters" required minlength="6">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted">Account Status</label>
                        <select name="status" class="form-select bg-light">
                            <option value="active" selected>Active (Can login immediately)</option>
                            <option value="inactive">Inactive (Access disabled)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-check me-1"></i> Create Login
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit System Login -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold" id="editUserModalLabel">
                    <i class="fa-solid fa-user-pen me-2"></i>Edit System Login
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= base_url('users/update') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="editUserId" value="">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editUserName" class="form-control bg-light" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Login Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="editUserEmail" class="form-control bg-light" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">
                            <i class="fa-solid fa-envelope-open-text text-primary me-1"></i>Company Mail / Official Mailbox
                        </label>
                        <input type="email" name="company_mail" id="editUserCompanyMail" class="form-control bg-light" placeholder="e.g. employee.name@company.com or dept@gammon.com">
                        <small class="text-muted" style="font-size:0.7rem;">
                            When employees in this department apply for leave, email notifications with direct action links will reflect to this company mailbox.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">System Role &amp; Permissions <span class="text-danger">*</span></label>
                        <select name="role_preset" id="editRolePreset" class="form-select bg-light" onchange="handleRolePresetChange(this, 'editCustomRoleBox', 'editRoleInput')">
                            <option value="custom">-- Blank Entry: Custom Authority / Enter Below --</option>
                            <option value="account">Accounts Head (Step 1 Approvals for Accounts)</option>
                            <option value="incharge">Incharge (Step 1 Approvals for Incharge &amp; General Staff)</option>
                            <option value="coordinator">Coordinator (Step 1 Approvals for Coordinator Staff)</option>
                            <option value="tender">Tender Head (Step 1 Approvals for Tender Staff)</option>
                            <option value="hr">HR &amp; Admin (Step 2 Authority &amp; Employee Directory)</option>
                            <option value="admin">System Administrator (Full System Access &amp; Step 3 Sign-off)</option>
                        </select>
                        <div id="editCustomRoleBox" class="mt-2" style="display:none;">
                            <label class="form-label small fw-semibold text-primary mb-1">
                                <i class="fa-solid fa-pen-nib me-1"></i> Custom Authority Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="role" id="editRoleInput" class="form-control bg-light border-primary" placeholder="Type custom authority name..." required>
                            <div class="text-muted small mt-1" style="font-size:0.7rem;">
                                <i class="fa-solid fa-circle-check text-success me-1"></i> This custom entry is taken as the Step 1 Approving Authority.
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Reset Password <small class="text-muted">(leave blank to keep current password)</small></label>
                        <input type="password" name="password" id="editUserPassword" class="form-control bg-light" placeholder="New password or blank" minlength="6">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted">Account Status</label>
                        <select name="status" id="editUserStatus" class="form-select bg-light">
                            <option value="active">Active (Can login immediately)</option>
                            <option value="inactive">Inactive (Access disabled)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleRolePresetChange(sel, boxId, inputId) {
    const box = document.getElementById(boxId);
    const input = document.getElementById(inputId);
    if (sel.value === 'custom') {
        box.style.display = 'block';
        input.value = '';
        input.placeholder = 'Type custom authority (e.g. Technical, Engineering, Operations...)';
        input.focus();
    } else {
        box.style.display = 'none';
        input.value = sel.value;
    }
}

function openEditUserModal(user) {
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editUserName').value = user.name || '';
    document.getElementById('editUserEmail').value = user.email || '';
    document.getElementById('editUserCompanyMail').value = user.company_mail || '';
    document.getElementById('editUserStatus').value = user.status || 'active';
    document.getElementById('editUserPassword').value = '';

    const presetSel = document.getElementById('editRolePreset');
    const roleInput = document.getElementById('editRoleInput');
    const customBox = document.getElementById('editCustomRoleBox');
    const standardRoles = ['account', 'incharge', 'coordinator', 'tender', 'hr', 'admin'];

    if (standardRoles.includes(user.role)) {
        presetSel.value = user.role;
        roleInput.value = user.role;
        customBox.style.display = 'none';
    } else {
        presetSel.value = 'custom';
        roleInput.value = user.role || '';
        customBox.style.display = 'block';
    }

    const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
}
</script>
<?php endif; ?>
