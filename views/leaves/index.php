<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-plane-departure text-warning me-2"></i>Employee Leave Management
        </h4>
        <p class="text-muted small mb-0">Record and manage approved leaves, sick days, on-duty visits, and custom time-offs for employees.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <button type="button" class="btn btn-primary rounded-pill shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#addLeaveModal">
            <i class="fa-solid fa-plus me-1"></i> Add Leave Entry
        </button>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?= e($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i><?= e($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Quick Leave KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Total Leave Entries</div>
                    <h3 class="fw-bold mb-0 mt-1 text-dark"><?= count($leaves) ?></h3>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                    <i class="fa-solid fa-folder-open fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 border-start border-4 border-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Casual Leave (CL)</div>
                    <h3 class="fw-bold mb-0 mt-1 text-info"><?= $stats['cl'] ?> <span class="fs-6 text-muted fw-normal">Days</span></h3>
                </div>
                <div class="bg-info bg-opacity-10 text-info rounded-circle p-3">
                    <i class="fa-solid fa-user-clock fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Sick Leave (SL)</div>
                    <h3 class="fw-bold mb-0 mt-1 text-warning"><?= $stats['sl'] ?> <span class="fs-6 text-muted fw-normal">Days</span></h3>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                    <i class="fa-solid fa-notes-medical fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">On Duty / Visit (OD)</div>
                    <h3 class="fw-bold mb-0 mt-1 text-success"><?= $stats['od'] ?> <span class="fs-6 text-muted fw-normal">Days</span></h3>
                </div>
                <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                    <i class="fa-solid fa-briefcase fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<form method="GET" action="<?= base_url('leaves') ?>" class="row g-2 mb-4 p-3 bg-light rounded-4 border">
    <div class="col-md-4">
        <label class="form-label small text-muted fw-semibold mb-1">Staff Member</label>
        <select name="employee_id" class="form-select form-select-sm bg-white">
            <option value="">All Employees</option>
            <?php foreach ($employees as $emp): ?>
                <option value="<?= $emp['id'] ?>" <?= (($filters['employee_id'] ?? '') == $emp['id']) ? 'selected' : '' ?>>
                    <?= e($emp['name']) ?> (<?= e($emp['employee_code']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label small text-muted fw-semibold mb-1">Leave Type</label>
        <select name="leave_type" class="form-select form-select-sm bg-white">
            <option value="">All Leave Types</option>
            <option value="Casual Leave" <?= (($filters['leave_type'] ?? '') === 'Casual Leave') ? 'selected' : '' ?>>Casual Leave (CL)</option>
            <option value="Sick Leave" <?= (($filters['leave_type'] ?? '') === 'Sick Leave') ? 'selected' : '' ?>>Sick / Medical Leave (SL)</option>
            <option value="Paid Leave" <?= (($filters['leave_type'] ?? '') === 'Paid Leave') ? 'selected' : '' ?>>Paid Leave (PL)</option>
            <option value="On Duty" <?= (($filters['leave_type'] ?? '') === 'On Duty') ? 'selected' : '' ?>>On Duty / Official Visit (OD)</option>
            <option value="Half Day Leave" <?= (($filters['leave_type'] ?? '') === 'Half Day Leave') ? 'selected' : '' ?>>Half Day Leave (HD)</option>
            <option value="Comp Off" <?= (($filters['leave_type'] ?? '') === 'Comp Off') ? 'selected' : '' ?>>Compensatory Off (CO)</option>
            <option value="Special Holiday" <?= (($filters['leave_type'] ?? '') === 'Special Holiday') ? 'selected' : '' ?>>Special Holiday / Restricted Off</option>
        </select>
    </div>
    <div class="col-md-4 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-dark btn-sm rounded-pill px-4"><i class="fa-solid fa-filter me-1"></i> Filter</button>
        <a href="<?= base_url('leaves') ?>" class="btn btn-light btn-sm rounded-pill px-3 border"><i class="fa-solid fa-arrow-rotate-left me-1"></i> Reset</a>
    </div>
</form>

<!-- Leaves Table -->
<div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden mb-4">
    <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-list-check text-primary me-2"></i>Employee Leave Records
        </h6>
        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill"><?= count($leaves) ?> Records</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4">Employee</th>
                    <th>Leave Type</th>
                    <th>Date Period</th>
                    <th>Days</th>
                    <th>Reason / Notes</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leaves)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-regular fa-calendar-xmark fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                            No leave entries recorded yet. Click <strong>Add Leave Entry</strong> to record employee leave.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($leaves as $lv): 
                        $code = \App\Models\Leave::getLeaveCode($lv['leave_type']);
                        $badgeClass = match($code) {
                            'CL' => 'bg-info-subtle text-info border border-info-subtle',
                            'SL' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                            'PL' => 'bg-success-subtle text-success border border-success-subtle',
                            'OD' => 'bg-primary-subtle text-primary border border-primary-subtle',
                            'HOL' => 'bg-purple-subtle text-purple border',
                            default => 'bg-secondary-subtle text-secondary border'
                        };
                    ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        <?= strtoupper(substr($lv['employee_name'], 0, 2)) ?>
                                    </div>
                                    <div>
                                        <a href="<?= base_url('employees/view/' . $lv['employee_id']) ?>" class="fw-bold text-dark text-decoration-none">
                                            <?= e($lv['employee_name']) ?>
                                        </a>
                                        <div class="text-muted font-monospace small" style="font-size: 0.72rem;">
                                            <?= e($lv['employee_code']) ?> &bull; <?= e($lv['department_name'] ?? 'General') ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $badgeClass ?> px-2 py-1 rounded-pill">
                                    <i class="fa-solid fa-tag me-1"></i><?= e($lv['leave_type']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    <i class="fa-regular fa-calendar text-primary me-1"></i>
                                    <?= date('d M Y', strtotime($lv['start_date'])) ?>
                                    <?php if ($lv['start_date'] !== $lv['end_date']): ?>
                                        <span class="text-muted">to</span> <?= date('d M Y', strtotime($lv['end_date'])) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1"><?= $lv['days_count'] ?> Day(s)</span>
                            </td>
                            <td class="text-muted small">
                                <?= e($lv['reason'] ?: 'Approved Leave') ?>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                    <i class="fa-solid fa-check me-1"></i> Approved
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <form action="<?= base_url('leaves/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this leave entry?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $lv['id'] ?>">
                                    <input type="hidden" name="return_url" value="leaves">
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Delete Leave">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Leave Modal -->
<div class="modal fade" id="addLeaveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-plane-departure text-warning me-2"></i>Record Employee Leave
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('leaves/create') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="return_url" value="leaves">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Employee (Name or Code) <span class="text-danger">*</span></label>
                        <input type="text" name="employee_id" list="leaveEmployeeDatalist" class="form-control bg-light" placeholder="Type Employee Name or Code (e.g. EMP001)..." required autocomplete="off">
                        <datalist id="leaveEmployeeDatalist">
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= e($emp['employee_code']) ?> — <?= e($emp['name']) ?>">
                                    <?= e($emp['name']) ?> (<?= e($emp['department_name'] ?? ($emp['department'] ?? 'General')) ?>)
                                </option>
                            <?php endforeach; ?>
                        </datalist>
                        <small class="text-muted" style="font-size: 0.72rem;">Type employee code or name to search and select.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type" class="form-select bg-light" required>
                            <option value="Casual Leave">Casual Leave (CL)</option>
                            <option value="Sick Leave">Sick / Medical Leave (SL)</option>
                            <option value="Paid Leave">Paid Leave (PL)</option>
                            <option value="On Duty">On Duty / Official Visit (OD)</option>
                            <option value="Half Day Leave">Half Day Leave (HD)</option>
                            <option value="Comp Off">Compensatory Off (CO)</option>
                            <option value="Special Holiday">Special Holiday / Restricted Off</option>
                            <option value="Unpaid Leave">Unpaid Leave / LOP</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">From Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="leaveFromDate" class="form-control bg-light" value="<?= date('Y-m-d') ?>" required onchange="syncToDate(this.value)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">To Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="leaveToDate" class="form-control bg-light" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Reason / Approval Notes</label>
                        <input type="text" name="reason" class="form-control bg-light" placeholder="e.g. Doctor appointment, Family function, Approved on-duty site visit">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-check me-1"></i> Save Leave Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function syncToDate(val) {
    const toInput = document.getElementById('leaveToDate');
    if (toInput && (!toInput.value || toInput.value < val)) {
        toInput.value = val;
    }
}
</script>
