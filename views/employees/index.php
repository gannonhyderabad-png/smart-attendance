<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
    <div class="card-body p-4">
        
        <!-- Action Header & Tabs -->
        <div class="row g-3 align-items-center mb-4">
            <div class="col-md-6">
                <h5 class="fw-bold text-dark mb-1">
                    <?= $currentView === 'trash' ? '<i class="fa-solid fa-trash-arrow-up text-danger me-2"></i> Employee Recycle Bin' : '<i class="fa-solid fa-users text-primary me-2"></i> Staff Directory' ?>
                </h5>
                <p class="text-muted small mb-0">
                    <?= $currentView === 'trash' ? 'Manage deleted employee records, restore them back to active directory, or permanently purge.' : 'Manage workforce members, generate their unique punch links, and assign work sites.' ?>
                </p>
            </div>
            <div class="col-md-6 text-md-end d-flex justify-content-md-end gap-2 flex-wrap">
                <?php if ($currentView === 'trash'): ?>
                    <?php if (!empty($employees)): ?>
                        <a href="<?= base_url('employees/empty-trash') ?>" class="btn btn-danger rounded-pill px-3 shadow-sm" onclick="return confirm('Are you sure you want to permanently empty the entire Recycle Bin? This action cannot be undone!')">
                            <i class="fa-solid fa-dumpster-fire me-1"></i> Empty Recycle Bin
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('employees') ?>" class="btn btn-outline-secondary rounded-pill px-3">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Staff Directory
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('employees/create') ?>" class="btn btn-primary rounded-pill px-3 shadow-sm">
                        <i class="fa-solid fa-user-plus me-1"></i> Add New Employee
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Directory / Recycle Bin View Tabs -->
        <ul class="nav nav-pills mb-4 p-1 bg-light rounded-pill d-inline-flex border">
            <li class="nav-item">
                <a class="nav-link rounded-pill px-4 fw-semibold <?= $currentView !== 'trash' ? 'active shadow-sm' : 'text-muted' ?>" href="<?= base_url('employees') ?>">
                    <i class="fa-solid fa-users me-1"></i> Active Staff <span class="badge <?= $currentView !== 'trash' ? 'bg-light text-primary' : 'bg-secondary' ?> ms-1"><?= $activeCount ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-4 fw-semibold <?= $currentView === 'trash' ? 'active bg-danger text-white shadow-sm' : 'text-muted' ?>" href="<?= base_url('employees?view=trash') ?>">
                    <i class="fa-solid fa-trash-can me-1"></i> Recycle Bin
                    <?php if ($trashCount > 0): ?>
                        <span class="badge <?= $currentView === 'trash' ? 'bg-light text-danger' : 'bg-danger' ?> ms-1"><?= $trashCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <?php if ($currentView === 'trash'): ?>
            <div class="alert alert-warning border-0 rounded-4 py-2 px-3 mb-4 d-flex align-items-center">
                <i class="fa-solid fa-triangle-exclamation fs-5 me-2 text-warning"></i>
                <div class="small fw-semibold text-dark">
                    Employees in the Recycle Bin cannot punch attendance. Click <strong>Restore</strong> to reactivate them anytime, or <strong>Wipe Permanently</strong> to remove all logs forever.
                </div>
            </div>
        <?php endif; ?>

        <!-- Filters Form -->
        <form method="GET" action="<?= base_url('employees') ?>" class="row g-2 mb-4">
            <?php if ($currentView === 'trash'): ?>
                <input type="hidden" name="view" value="trash">
            <?php endif; ?>
        <!-- Filters Form -->
        <form method="GET" action="<?= base_url('employees') ?>" class="row g-2 mb-4">
            <?php if ($currentView === 'trash'): ?>
                <input type="hidden" name="view" value="trash">
            <?php endif; ?>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" placeholder="Search staff..." value="<?= e($filters['search'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-building"></i></span>
                    <select name="department" class="form-select bg-light border-start-0 ps-0">
                        <option value="">All Departments</option>
                        <?php foreach ($departmentList as $deptName): ?>
                            <option value="<?= e($deptName) ?>" <?= (($filters['department'] ?? '') === $deptName) ? 'selected' : '' ?>>
                                <?= e($deptName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-location-dot"></i></span>
                    <select name="site" class="form-select bg-light border-start-0 ps-0">
                        <option value="">All Sites</option>
                        <?php foreach ($siteList as $sName): ?>
                            <option value="<?= e($sName) ?>" <?= (($filters['site'] ?? '') === $sName) ? 'selected' : '' ?>>
                                <?= e($sName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select bg-light">
                    <option value="">All Statuses</option>
                    <option value="active" <?= (($filters['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= (($filters['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    <option value="archived" <?= (($filters['status'] ?? '') === 'archived') ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                <a href="<?= base_url($currentView === 'trash' ? 'employees?view=trash' : 'employees') ?>" class="btn btn-light border" title="Reset"><i class="fa-solid fa-rotate-right"></i></a>
            </div>
        </form>

        <!-- Employees Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Code</th>
                        <th>Employee</th>
                        <th>Department & Role</th>
                        <th>Project & Site</th>
                        <?php if ($currentView === 'trash'): ?>
                            <th>Deleted Date</th>
                            <th>Status</th>
                        <?php else: ?>
                            <th>Punch URL</th>
                            <th>Status</th>
                        <?php endif; ?>
                        <th class="pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-<?= $currentView === 'trash' ? 'trash-can' : 'user-slash' ?> fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                <?= $currentView === 'trash' ? 'Recycle Bin is completely empty.' : 'No active employees found matching the filters.' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $emp): 
                            $empPunchUrl = punch_url($emp['employee_code']);
                        ?>
                            <tr class="<?= $currentView === 'trash' ? 'table-light opacity-75' : '' ?>">
                                <td class="ps-3">
                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1 shadow-sm">
                                        <i class="fa-solid fa-id-badge me-1 text-primary"></i><?= e($emp['employee_code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($emp['photo'])): ?>
                                            <img src="<?= uploaded_url($emp['photo']) ?>" class="rounded-circle border object-fit-cover me-2 shadow-sm" style="width: 38px; height: 38px;" alt="<?= e($emp['name']) ?>">
                                        <?php else: ?>
                                            <div class="avatar-sm rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center me-2" style="width: 38px; height: 38px; font-size: 0.85rem;">
                                                <?= strtoupper(substr($emp['name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-dark">
                                                <?= e($emp['name']) ?>
                                            </div>
                                            <div class="text-muted small">
                                                <?= e($emp['email'] ?? 'No email') ?> &bull; <?= e($emp['phone'] ?? 'No phone') ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle px-2 py-1 d-block mb-1 text-truncate" style="max-width: 140px;">
                                        <i class="fa-solid fa-building me-1 text-secondary"></i><?= e($emp['department'] ?: ($emp['department_name'] ?? 'General')) ?>
                                    </span>
                                    <span class="text-muted small d-block"><?= e($emp['designation'] ?? 'Staff') ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($emp['project'])): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 d-block mb-1 text-truncate" style="max-width: 140px;">
                                            <i class="fa-solid fa-diagram-project me-1"></i><?= e($emp['project']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($emp['site'])): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 d-block text-truncate" style="max-width: 140px;">
                                            <i class="fa-solid fa-location-dot me-1"></i><?= e($emp['site']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (empty($emp['project']) && empty($emp['site'])): ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>

                                <?php if ($currentView === 'trash'): ?>
                                    <td>
                                        <span class="text-danger small fw-semibold">
                                            <i class="fa-solid fa-calendar-xmark me-1"></i>
                                            <?= !empty($emp['deleted_at']) ? date('M d, Y h:i A', strtotime($emp['deleted_at'])) : 'Archived' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                            <i class="fa-solid fa-trash-can me-1"></i> In Trash
                                        </span>
                                    </td>
                                    <td class="pe-3 text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('employees/restore/' . $emp['id']) ?>" class="btn btn-success text-white px-3 shadow-sm" title="Restore to Active Directory">
                                                <i class="fa-solid fa-rotate-left me-1"></i> Restore
                                            </a>
                                            <a href="<?= base_url('employees/force-delete/' . $emp['id']) ?>" class="btn btn-outline-danger px-2" onclick="return confirm('Permanently delete <?= e($emp['name']) ?>? All attendance logs will be wiped forever!')" title="Wipe Permanently">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                <?php else: ?>
                                    <td>
                                        <div class="input-group input-group-sm" style="max-width: 240px;">
                                            <input type="text" class="form-control bg-light font-monospace" value="<?= e($empPunchUrl) ?>" id="url-<?= $emp['id'] ?>" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('<?= e($empPunchUrl) ?>', this)" title="Copy Attendance Link">
                                                <i class="fa-regular fa-copy"></i>
                                            </button>
                                            <a href="<?= e($empPunchUrl) ?>" target="_blank" class="btn btn-outline-primary" title="Open Mobile View">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $emp['status'] === 'active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' ?> rounded-pill px-2 py-1">
                                            <i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i>
                                            <?= ucfirst(e($emp['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="pe-3 text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('employees/view/' . $emp['id']) ?>" class="btn btn-primary text-white border-0 px-2" title="View Profile & QR Code">
                                                <i class="fa-solid fa-id-card me-1"></i> Details
                                            </a>
                                            <a href="<?= base_url('employees/edit/' . $emp['id']) ?>" class="btn btn-light border text-secondary" title="Edit Employee">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <a href="<?= base_url('employees/delete/' . $emp['id']) ?>" class="btn btn-light border text-danger" onclick="return confirm('Move <?= e($emp['name']) ?> to Recycle Bin?')" title="Move to Recycle Bin">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
