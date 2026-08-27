<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-plane-departure text-warning me-2"></i>Employee Leave Management
        </h4>
        <p class="text-muted small mb-0">Record and manage approved leaves, sick days, on-duty visits, and custom time-offs for employees.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <button type="button" class="btn btn-outline-primary rounded-pill shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#editCompanyQuotaModal">
            <i class="fa-solid fa-sliders me-1"></i> Edit Company Leave Quota
        </button>
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
    <div class="col-6 col-lg-2">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 border-start border-4 border-secondary h-100">
            <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">Company Assigned</div>
            <h4 class="fw-bold mb-0 mt-1 text-dark"><?= $companyQuotas['total'] ?? 37 ?> <span class="fs-6 text-muted fw-normal">Days/Yr</span></h4>
            <div class="text-muted small mt-1" style="font-size: 0.68rem;">12 CL &bull; 10 SL &bull; 15 PL</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 border-start border-4 border-primary h-100">
            <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">Total Entries</div>
            <h4 class="fw-bold mb-0 mt-1 text-primary"><?= count($leaves) ?></h4>
            <div class="text-muted small mt-1" style="font-size: 0.68rem;">Recorded leaves</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 border-start border-4 border-info h-100">
            <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">Casual Leave (CL)</div>
            <h4 class="fw-bold mb-0 mt-1 text-info"><?= $stats['cl'] ?> <span class="fs-6 text-muted fw-normal">Days</span></h4>
            <div class="text-muted small mt-1" style="font-size: 0.68rem;">Assigned: <?= $companyQuotas['CL'] ?? 12 ?> Days</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 border-start border-4 border-warning h-100">
            <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">Sick Leave (SL)</div>
            <h4 class="fw-bold mb-0 mt-1 text-warning"><?= $stats['sl'] ?> <span class="fs-6 text-muted fw-normal">Days</span></h4>
            <div class="text-muted small mt-1" style="font-size: 0.68rem;">Assigned: <?= $companyQuotas['SL'] ?? 10 ?> Days</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 border-start border-4 border-success h-100">
            <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">Paid Leave (PL)</div>
            <h4 class="fw-bold mb-0 mt-1 text-success"><?= $stats['pl'] ?> <span class="fs-6 text-muted fw-normal">Days</span></h4>
            <div class="text-muted small mt-1" style="font-size: 0.68rem;">Assigned: <?= $companyQuotas['PL'] ?? 15 ?> Days</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 border-start border-4 border-dark h-100">
            <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">Outdoor Duty (OD)</div>
            <h4 class="fw-bold mb-0 mt-1 text-dark"><?= $stats['od'] ?> <span class="fs-6 text-muted fw-normal">Visits</span></h4>
            <div class="text-muted small mt-1" style="font-size: 0.68rem;">On-Duty site visits</div>
        </div>
    </div>
</div>

<!-- Collapsible Company Assigned Staff Leave Balances Hub -->
<div class="card border-0 rounded-4 shadow-sm bg-white mb-4">
    <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center cursor-pointer" data-bs-toggle="collapse" data-bs-target="#staffLeaveQuotaCollapse">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-scale-balanced text-primary fs-5"></i>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Company Assigned Leave Balances per Staff</h6>
                <small class="text-muted">Company standard annual allowance: <strong><?= $companyQuotas['total'] ?? 37 ?> Days</strong> (12 CL &bull; 10 SL &bull; 15 PL)</small>
            </div>
        </div>
        <button class="btn btn-sm btn-outline-primary rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#staffLeaveQuotaCollapse">
            <i class="fa-solid fa-table-list me-1"></i> Toggle Quota Matrix
        </button>
    </div>
    <div class="collapse show" id="staffLeaveQuotaCollapse">
        <div class="card-body p-0 border-top">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase" style="font-size: 0.72rem;">
                        <tr>
                            <th class="ps-4">Staff Member</th>
                            <th>Department & Site</th>
                            <th>Company Assigned Quota</th>
                            <th>Casual Leave (CL)</th>
                            <th>Sick Leave (SL)</th>
                            <th>Paid Leave (PL)</th>
                            <th>Outdoor Duty (OD)</th>
                            <th>Available Balance</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php foreach ($employees as $emp): 
                            $bal = $employeeBalances[$emp['id']] ?? null;
                            if (!$bal) continue;
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold cursor-pointer" style="width: 32px; height: 32px; font-size: 0.8rem;" onclick="openEmployeeLeaveSheet(<?= $emp['id'] ?>)">
                                            <?= strtoupper(substr($emp['name'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0)" onclick="openEmployeeLeaveSheet(<?= $emp['id'] ?>)" class="fw-bold text-dark text-decoration-none" title="Click to view full Employee Leave Sheet">
                                                <?= e($emp['name']) ?>
                                            </a>
                                            <div class="text-muted font-monospace small" style="font-size: 0.72rem;">
                                                <?= e($emp['employee_code']) ?>
                                                <a href="javascript:void(0)" onclick="openEmployeeLeaveSheet(<?= $emp['id'] ?>)" class="text-primary text-decoration-none ms-1 font-sans small fw-semibold" style="font-size: 0.7rem;">
                                                    <i class="fa-solid fa-file-lines me-1"></i>View Sheet
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><?= e($emp['department_name'] ?? ($emp['department'] ?? 'General')) ?></div>
                                    <div class="text-muted small"><i class="fa-solid fa-location-dot text-danger me-1"></i><?= e($emp['site'] ?? 'Main Site') ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-light text-dark border font-monospace">
                                            <?= $bal['assigned']['total'] ?> Days
                                        </span>
                                        <?php if (!empty($bal['is_custom'])): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-1" style="font-size: 0.65rem;">Custom</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small mt-1" style="font-size: 0.68rem;"><?= $bal['assigned']['CL'] ?> CL &bull; <?= $bal['assigned']['SL'] ?> SL &bull; <?= $bal['assigned']['PL'] ?> PL</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-info"><?= $bal['taken']['CL'] ?> used</div>
                                    <div class="text-muted small"><?= $bal['balance']['CL'] ?> left of <?= $bal['assigned']['CL'] ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-warning"><?= $bal['taken']['SL'] ?> used</div>
                                    <div class="text-muted small"><?= $bal['balance']['SL'] ?> left of <?= $bal['assigned']['SL'] ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-success"><?= $bal['taken']['PL'] ?> used</div>
                                    <div class="text-muted small"><?= $bal['balance']['PL'] ?> left of <?= $bal['assigned']['PL'] ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2">
                                        <?= $bal['taken']['OD'] ?> Visits
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $bal['balance']['total'] > 5 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' ?> rounded-pill px-3 py-1 fw-bold fs-6 font-monospace">
                                        <?= $bal['balance']['total'] ?> / <?= $bal['assigned']['total'] ?>
                                    </span>
                                    <div class="text-muted small mt-1">Days Remaining</div>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2 py-1" onclick="openEmployeeLeaveSheet(<?= $emp['id'] ?>)" title="View Complete Leave Sheet">
                                            <i class="fa-solid fa-file-lines me-1"></i> Sheet
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" onclick="openEditEmpQuota(<?= htmlspecialchars(json_encode([
                                            'id' => $emp['id'],
                                            'name' => $emp['name'],
                                            'code' => $emp['employee_code'],
                                            'cl' => $bal['assigned']['CL'],
                                            'sl' => $bal['assigned']['SL'],
                                            'pl' => $bal['assigned']['PL'],
                                            'is_custom' => $bal['is_custom'] ?? false
                                        ])) ?>)" title="Edit Leave Quota for this employee">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Quota
                                        </button>
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
                    <th>Company Assigned & Balance</th>
                    <th>Target / Visit Site</th>
                    <th>Date Period</th>
                    <th>Days</th>
                    <th>Attached PDF / Sheet</th>
                    <th>Reason / Notes</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leaves)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
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
                        $empBal = $employeeBalances[$lv['employee_id']] ?? null;
                    ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold cursor-pointer" style="width: 32px; height: 32px; font-size: 0.8rem;" onclick="openEmployeeLeaveSheet(<?= $lv['employee_id'] ?>)" title="Click to view Leave Sheet">
                                        <?= strtoupper(substr($lv['employee_name'], 0, 2)) ?>
                                    </div>
                                    <div>
                                        <a href="javascript:void(0)" onclick="openEmployeeLeaveSheet(<?= $lv['employee_id'] ?>)" class="fw-bold text-dark text-decoration-none" title="Click to view full Employee Leave Sheet & Dossier">
                                            <?= e($lv['employee_name']) ?>
                                        </a>
                                        <div class="text-muted font-monospace small" style="font-size: 0.72rem;">
                                            <?= e($lv['employee_code']) ?> &bull; <?= e($lv['department_name'] ?? 'General') ?>
                                            <a href="javascript:void(0)" onclick="openEmployeeLeaveSheet(<?= $lv['employee_id'] ?>)" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill text-decoration-none ms-1 px-2 py-0" style="font-size: 0.65rem;" title="View Complete Leave Sheet">
                                                <i class="fa-solid fa-file-lines me-1"></i>Leave Sheet
                                            </a>
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
                                <?php if ($empBal): ?>
                                    <div class="small fw-semibold text-dark">
                                        <span class="text-info" title="Casual Leave Balance"><strong><?= $empBal['balance']['CL'] ?></strong>/<?= $empBal['assigned']['CL'] ?> CL</span> &bull;
                                        <span class="text-warning" title="Sick Leave Balance"><strong><?= $empBal['balance']['SL'] ?></strong>/<?= $empBal['assigned']['SL'] ?> SL</span> &bull;
                                        <span class="text-success" title="Paid Leave Balance"><strong><?= $empBal['balance']['PL'] ?></strong>/<?= $empBal['assigned']['PL'] ?> PL</span>
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-shield-halved me-1"></i><?= $empBal['balance']['total'] ?> / <?= $empBal['assigned']['total'] ?> Left
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($lv['target_site'])): ?>
                                    <div class="d-inline-flex flex-column">
                                        <span class="badge bg-light text-dark border">
                                            <i class="fa-solid fa-location-crosshairs text-primary me-1"></i><?= e($lv['target_site']) ?>
                                        </span>
                                        <?php if (!empty($lv['origin_site'])): ?>
                                            <small class="text-muted" style="font-size: 0.7rem;">From: <?= e($lv['origin_site']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="text-dark fw-semibold">
                                        <i class="fa-regular fa-calendar me-1 text-primary"></i><?= date('d M Y', strtotime($lv['start_date'])) ?>
                                    </span>
                                    <?php if ($lv['start_date'] !== $lv['end_date']): ?>
                                        <div class="text-muted" style="font-size: 0.75rem;">to <?= date('d M Y', strtotime($lv['end_date'])) ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1"><?= $lv['days_count'] ?> Day(s)</span>
                            </td>
                            <td>
                                <?php if (!empty($lv['attachment'])): 
                                    $attExt = strtolower(pathinfo($lv['attachment'], PATHINFO_EXTENSION));
                                    $attUrl = uploaded_url($lv['attachment']);
                                ?>
                                    <?php if ($attExt === 'pdf'): ?>
                                        <a href="<?= $attUrl ?>" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 shadow-sm font-monospace" style="font-size: 0.75rem;" title="View Attached PDF Sheet">
                                            <i class="fa-solid fa-file-pdf me-1"></i> PDF Sheet
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= $attUrl ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 shadow-sm" style="font-size: 0.75rem;">
                                            <i class="fa-solid fa-file-arrow-down me-1"></i> Attachment
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
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
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2 py-1" onclick="openEmployeeLeaveSheet(<?= $lv['employee_id'] ?>)" title="View Complete Leave Sheet">
                                        <i class="fa-solid fa-file-lines"></i>
                                    </button>
                                    <form action="<?= base_url('leaves/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this leave entry?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $lv['id'] ?>">
                                        <input type="hidden" name="return_url" value="leaves">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" title="Delete Leave">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
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
                    <i class="fa-solid fa-plane-departure text-warning me-2"></i>Record Employee Leave / OD
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('leaves/create') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="return_url" value="leaves">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Employee (Name or Code) <span class="text-danger">*</span></label>
                        <input type="text" name="employee_id" id="modalEmpInput" list="leaveEmployeeDatalist" class="form-control bg-light" placeholder="Type Employee Name or Code (e.g. EMP001)..." required autocomplete="off" oninput="updateModalEmpQuota(this.value)">
                        <datalist id="leaveEmployeeDatalist">
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= e($emp['employee_code']) ?> — <?= e($emp['name']) ?>" data-empid="<?= $emp['id'] ?>" data-empcode="<?= e($emp['employee_code']) ?>">
                                    <?= e($emp['name']) ?> (<?= e($emp['department_name'] ?? ($emp['department'] ?? 'General')) ?>)
                                </option>
                            <?php endforeach; ?>
                        </datalist>
                        <small class="text-muted" style="font-size: 0.72rem;">Type employee code or name to search and select.</small>
                    </div>

                    <!-- Live Employee Leave Quota Preview in Modal -->
                    <div id="modalEmpQuotaBox" class="p-3 bg-light rounded-4 border mb-3 d-none">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small fw-bold text-dark"><i class="fa-solid fa-scale-balanced text-primary me-1"></i>Company Assigned Quota:</span>
                            <span id="modalEmpTotalBalBadge" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace"></span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 small mt-2">
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1" id="modalClBal"></span>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1" id="modalSlBal"></span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" id="modalPlBal"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type" id="modalLeaveTypeSelect" class="form-select bg-light" required onchange="handleLeaveTypeChange(this.value)">
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

                    <!-- ON DUTY (OD) WHERE TO GO / TARGET SITE FIELDS -->
                    <div class="p-3 bg-light rounded-4 border border-primary-subtle mb-3 d-none" id="odSiteFields">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary text-white"><i class="fa-solid fa-location-crosshairs me-1"></i> Outdoor / OD Visit Details</span>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Where to Go Site / Target Site <span class="text-danger">*</span></label>
                                <input type="text" name="target_site" id="odTargetSite" list="allSitesList" class="form-control bg-white" placeholder="e.g. Hyderabad Metro Site, Client Office, Site-B">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">From / Base Site</label>
                                <input type="text" name="origin_site" id="odOriginSite" list="allSitesList" class="form-control bg-white" placeholder="e.g. Head Office / Primary Site">
                            </div>
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">Specify the destination client or project site where the employee is visiting.</small>
                    </div>

                    <!-- Available Work Sites Datalist -->
                    <datalist id="allSitesList">
                        <?php foreach ($siteList ?? [] as $s): ?>
                            <option value="<?= e($s) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>

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

                    <!-- ATTACH PDF / DOCUMENT SHEET -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">
                            <i class="fa-solid fa-paperclip text-primary me-1"></i>Attach PDF / Document Sheet <small class="text-muted">(Optional)</small>
                        </label>
                        <input type="file" name="attachment" class="form-control bg-light" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.xlsx,.csv">
                        <small class="text-muted" style="font-size: 0.72rem;">Upload approval slip, medical certificate, OD tour sheet, or permission form (PDF / Images / Docs).</small>
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

<!-- Edit Company Standard Leave Quota Modal -->
<div class="modal fade" id="editCompanyQuotaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-sliders text-primary me-2"></i>Edit Company Leave Quota
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('leaves/update-company-quota') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <p class="text-muted small mb-3">Set standard annual leave allowances assigned by the company to all employees.</p>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Casual Leave (CL) Quota (Days/Year) <span class="text-danger">*</span></label>
                        <input type="number" step="0.5" min="0" max="365" name="cl_quota" id="compClQuota" class="form-control bg-light font-monospace fw-bold" value="<?= $companyQuotas['CL'] ?? 12 ?>" required oninput="calcCompTotal()">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Sick Leave (SL) Quota (Days/Year) <span class="text-danger">*</span></label>
                        <input type="number" step="0.5" min="0" max="365" name="sl_quota" id="compSlQuota" class="form-control bg-light font-monospace fw-bold" value="<?= $companyQuotas['SL'] ?? 10 ?>" required oninput="calcCompTotal()">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Paid / Annual Leave (PL) Quota (Days/Year) <span class="text-danger">*</span></label>
                        <input type="number" step="0.5" min="0" max="365" name="pl_quota" id="compPlQuota" class="form-control bg-light font-monospace fw-bold" value="<?= $companyQuotas['PL'] ?? 15 ?>" required oninput="calcCompTotal()">
                    </div>

                    <div class="p-3 bg-primary bg-opacity-10 rounded-4 border border-primary-subtle d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-dark">Total Company Annual Allowance:</span>
                        <span id="compTotalDisplay" class="badge bg-primary text-white fs-6 font-monospace px-3 py-2 rounded-pill"><?= $companyQuotas['total'] ?? 37 ?> Days</span>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-check me-1"></i> Update Company Quota
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Individual Employee Leave Quota Modal -->
<div class="modal fade" id="editEmpQuotaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-user-pen text-primary me-2"></i>Edit Employee Leave Quota
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('leaves/update-employee-quota') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="employee_id" id="editEmpId">
                <div class="modal-body py-4">
                    <div class="p-3 bg-light rounded-4 border mb-3">
                        <div class="fw-bold text-dark" id="editEmpName">—</div>
                        <div class="text-muted font-monospace small" id="editEmpCode">—</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="use_company_default" id="useCompanyDefaultCheck" value="1" onchange="toggleCustomQuotaInputs(this.checked)">
                        <label class="form-check-label small fw-semibold text-dark" for="useCompanyDefaultCheck">
                            Use Standard Company Quota (<?= $companyQuotas['total'] ?? 37 ?> Days)
                        </label>
                    </div>

                    <div id="customQuotaInputsContainer">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Custom Casual Leave (CL) Quota</label>
                            <input type="number" step="0.5" min="0" max="365" name="cl_quota" id="editEmpCl" class="form-control bg-light font-monospace fw-bold" oninput="calcEmpTotal()">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Custom Sick Leave (SL) Quota</label>
                            <input type="number" step="0.5" min="0" max="365" name="sl_quota" id="editEmpSl" class="form-control bg-light font-monospace fw-bold" oninput="calcEmpTotal()">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Custom Paid Leave (PL) Quota</label>
                            <input type="number" step="0.5" min="0" max="365" name="pl_quota" id="editEmpPl" class="form-control bg-light font-monospace fw-bold" oninput="calcEmpTotal()">
                        </div>

                        <div class="p-3 bg-primary bg-opacity-10 rounded-4 border border-primary-subtle d-flex justify-content-between align-items-center">
                            <span class="small fw-bold text-dark">Total Assigned to this Employee:</span>
                            <span id="editEmpTotalDisplay" class="badge bg-primary text-white fs-6 font-monospace px-3 py-2 rounded-pill">0 Days</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-check me-1"></i> Save Employee Quota
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Employee Leave Statement & Dossier Sheet Modal -->
<div class="modal fade" id="employeeLeaveSheetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <!-- Modal Actions Bar (hidden when printing) -->
            <div class="modal-header border-bottom bg-light d-print-none py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary text-white p-2 rounded-3"><i class="fa-solid fa-file-invoice fs-6"></i></span>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Employee Leave Statement & Dossier Sheet</h5>
                        <small class="text-muted">Official company leave record & balance report</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-dark rounded-pill px-3 shadow-sm" onclick="printLeaveSheet()">
                        <i class="fa-solid fa-print me-1"></i> Print Leave Sheet
                    </button>
                    <button type="button" class="btn btn-light rounded-circle shadow-sm" data-bs-dismiss="modal" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-4 p-md-5" id="printableLeaveSheetArea">
                <!-- Company Official Printable Header -->
                <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-4 bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-building-circle-check fs-2"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold text-dark mb-1 font-monospace" style="letter-spacing: -0.5px;">Gannon Dunkerley & Co. Ltd.</h4>
                            <div class="text-uppercase small fw-bold text-primary tracking-wider" style="font-size: 0.75rem; letter-spacing: 1px;">
                                Employee Leave Statement & Dossier Sheet
                            </div>
                        </div>
                    </div>
                    <div class="text-end text-muted small">
                        <div><strong>Report Date:</strong> <?= date('d M Y, h:i A') ?></div>
                        <div><strong>Calendar Year:</strong> <?= date('Y') ?></div>
                        <div class="badge bg-success-subtle text-success border border-success-subtle rounded-pill mt-1">Official Company Record</div>
                    </div>
                </div>

                <!-- Employee Profile Card -->
                <div class="row g-3 mb-4 p-3 bg-light rounded-4 border">
                    <div class="col-md-3">
                        <small class="text-muted fw-semibold d-block">Employee Name</small>
                        <span class="fs-6 fw-bold text-dark" id="sheetEmpName">—</span>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted fw-semibold d-block">Employee Code</small>
                        <span class="fs-6 fw-bold text-primary font-monospace" id="sheetEmpCode">—</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted fw-semibold d-block">Department</small>
                        <span class="fs-6 fw-semibold text-dark" id="sheetEmpDept">—</span>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted fw-semibold d-block">Base Work Site</small>
                        <span class="fs-6 fw-semibold text-dark" id="sheetEmpSite">—</span>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted fw-semibold d-block">Designation / Role</small>
                        <span class="fs-6 fw-semibold text-dark" id="sheetEmpRole">—</span>
                    </div>
                </div>

                <!-- Leave Quota & Balance KPI Grid -->
                <div class="row g-3 mb-4 text-center">
                    <div class="col-6 col-md-2">
                        <div class="p-3 bg-light border rounded-4">
                            <small class="text-muted fw-semibold d-block mb-1">Total Quota</small>
                            <h5 class="fw-bold text-dark mb-0 font-monospace" id="sheetTotalAssigned">0</h5>
                            <small class="text-muted" style="font-size: 0.7rem;">Assigned / Year</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="p-3 bg-info bg-opacity-10 border border-info-subtle rounded-4">
                            <small class="text-info-emphasis fw-semibold d-block mb-1">Casual Leave (CL)</small>
                            <h5 class="fw-bold text-info mb-0 font-monospace" id="sheetClBalance">0 / 0</h5>
                            <small class="text-muted" style="font-size: 0.7rem;" id="sheetClTaken">0 Used</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="p-3 bg-warning bg-opacity-10 border border-warning-subtle rounded-4">
                            <small class="text-warning-emphasis fw-semibold d-block mb-1">Sick Leave (SL)</small>
                            <h5 class="fw-bold text-warning-emphasis mb-0 font-monospace" id="sheetSlBalance">0 / 0</h5>
                            <small class="text-muted" style="font-size: 0.7rem;" id="sheetSlTaken">0 Used</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="p-3 bg-success bg-opacity-10 border border-success-subtle rounded-4">
                            <small class="text-success-emphasis fw-semibold d-block mb-1">Paid Leave (PL)</small>
                            <h5 class="fw-bold text-success mb-0 font-monospace" id="sheetPlBalance">0 / 0</h5>
                            <small class="text-muted" style="font-size: 0.7rem;" id="sheetPlTaken">0 Used</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="p-3 bg-primary bg-opacity-10 border border-primary-subtle rounded-4">
                            <small class="text-primary-emphasis fw-semibold d-block mb-1">Outdoor Duty (OD)</small>
                            <h5 class="fw-bold text-primary mb-0 font-monospace" id="sheetOdVisits">0</h5>
                            <small class="text-muted" style="font-size: 0.7rem;">Site Visits</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="p-3 bg-success text-white rounded-4 shadow-sm">
                            <small class="text-white text-opacity-75 fw-semibold d-block mb-1">Remaining Balance</small>
                            <h5 class="fw-bold text-white mb-0 font-monospace" id="sheetTotalRemaining">0</h5>
                            <small class="text-white text-opacity-75" style="font-size: 0.7rem;">Available Days</small>
                        </div>
                    </div>
                </div>

                <!-- Detailed Leave Records Table -->
                <div class="border rounded-4 overflow-hidden mb-4">
                    <div class="bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark small"><i class="fa-solid fa-list me-1 text-primary"></i>Recorded Leave & Outdoor Duty (OD) History</h6>
                        <span class="badge bg-secondary text-white rounded-pill" id="sheetTotalRecordsCount">0 Entries</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted text-uppercase" style="font-size: 0.72rem;">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Leave Type</th>
                                    <th>Date Period</th>
                                    <th>Days</th>
                                    <th>Origin / Target Site</th>
                                    <th>Reason / Notes</th>
                                    <th>Attached PDF / Sheet</th>
                                    <th class="pe-3 text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody id="sheetTableBody" class="small">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Signature & Authorization Section for Print -->
                <div class="row pt-4 mt-4 border-top text-muted small">
                    <div class="col-4 text-center">
                        <div style="border-bottom: 1px dashed #ccc; height: 35px; width: 80%; margin: 0 auto 5px;"></div>
                        <span>Employee Signature</span>
                    </div>
                    <div class="col-4 text-center">
                        <div style="border-bottom: 1px dashed #ccc; height: 35px; width: 80%; margin: 0 auto 5px;"></div>
                        <span>Site / Project Manager</span>
                    </div>
                    <div class="col-4 text-center">
                        <div style="border-bottom: 1px dashed #ccc; height: 35px; width: 80%; margin: 0 auto 5px;"></div>
                        <span>HR & Admin Department</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top bg-light py-3 px-4 d-print-none">
                <a id="sheetViewDossierLink" href="#" class="btn btn-outline-primary rounded-pill px-3 me-auto">
                    <i class="fa-solid fa-chart-pie me-1"></i> View Full Attendance Dossier
                </a>
                <button type="button" class="btn btn-dark rounded-pill px-4" onclick="printLeaveSheet()">
                    <i class="fa-solid fa-print me-1"></i> Print Leave Sheet
                </button>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #printableLeaveSheetArea, #printableLeaveSheetArea * {
        visibility: visible !important;
    }
    #printableLeaveSheetArea {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 20px !important;
        background: #fff !important;
    }
    .modal {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
    }
    .modal-dialog {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>

<script>
const allEmployeeBalances = <?= json_encode($employeeBalances ?? []) ?>;
const defaultCompanyQuotas = <?= json_encode($companyQuotas ?? ['CL' => 12, 'SL' => 10, 'PL' => 15, 'total' => 37]) ?>;
const leavesByEmployee = <?= json_encode($leavesByEmployee ?? []) ?>;
const employeesMap = {
    <?php foreach ($employees as $emp): ?>
        "<?= (int)$emp['id'] ?>": <?= json_encode($emp) ?>,
    <?php endforeach; ?>
};
const employeesLookup = {
    <?php foreach ($employees as $emp): ?>
        "<?= addslashes(strtolower($emp['employee_code'])) ?>": <?= (int)$emp['id'] ?>,
        "<?= addslashes(strtolower($emp['name'])) ?>": <?= (int)$emp['id'] ?>,
        "<?= (int)$emp['id'] ?>": <?= (int)$emp['id'] ?>,
    <?php endforeach; ?>
};

function openEmployeeLeaveSheet(empId) {
    const emp = employeesMap[empId];
    if (!emp) return;

    const bal = allEmployeeBalances[empId] || {
        assigned: { total: 37, CL: 12, SL: 10, PL: 15 },
        taken: { total: 0, CL: 0, SL: 0, PL: 0, OD: 0 },
        balance: { total: 37, CL: 12, SL: 10, PL: 15 }
    };

    const empLeaves = leavesByEmployee[empId] || [];

    // Populate profile card
    document.getElementById('sheetEmpName').textContent = emp.name || '—';
    document.getElementById('sheetEmpCode').textContent = emp.employee_code || '—';
    document.getElementById('sheetEmpDept').textContent = emp.department_name || emp.department || 'General';
    document.getElementById('sheetEmpSite').textContent = emp.site || 'Main Site';
    document.getElementById('sheetEmpRole').textContent = emp.designation || emp.role || 'Staff Member';

    // Populate balances
    document.getElementById('sheetTotalAssigned').textContent = `${bal.assigned.total} Days`;
    document.getElementById('sheetClBalance').textContent = `${bal.balance.CL} / ${bal.assigned.CL} Left`;
    document.getElementById('sheetClTaken').textContent = `${bal.taken.CL} Days Used`;

    document.getElementById('sheetSlBalance').textContent = `${bal.balance.SL} / ${bal.assigned.SL} Left`;
    document.getElementById('sheetSlTaken').textContent = `${bal.taken.SL} Days Used`;

    document.getElementById('sheetPlBalance').textContent = `${bal.balance.PL} / ${bal.assigned.PL} Left`;
    document.getElementById('sheetPlTaken').textContent = `${bal.taken.PL} Days Used`;

    document.getElementById('sheetOdVisits').textContent = `${bal.taken.OD} Visits`;
    document.getElementById('sheetTotalRemaining').textContent = `${bal.balance.total} / ${bal.assigned.total} Days`;

    document.getElementById('sheetTotalRecordsCount').textContent = `${empLeaves.length} Recorded Entries`;
    document.getElementById('sheetViewDossierLink').href = `<?= base_url('reports') ?>?report_type=employee&employee_id=${empId}`;

    // Populate table
    const tbody = document.getElementById('sheetTableBody');
    tbody.innerHTML = '';

    if (empLeaves.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fa-regular fa-folder-open fa-2x mb-2 d-block opacity-50"></i>No leaves or outdoor duties recorded yet for this employee in <?= date('Y') ?>.</td></tr>`;
    } else {
        empLeaves.forEach((lv, index) => {
            const isOd = lv.leave_type === 'On Duty' || lv.leave_type.includes('OD') || lv.leave_type.includes('Duty');
            const badgeBg = isOd ? 'bg-primary-subtle text-primary border border-primary-subtle' : 
                            (lv.leave_type.includes('Casual') ? 'bg-info-subtle text-info border border-info-subtle' : 
                            (lv.leave_type.includes('Sick') ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-success-subtle text-success border border-success-subtle'));

            const datePeriod = (lv.start_date === lv.end_date) ? 
                `<strong>${formatDateStr(lv.start_date)}</strong>` : 
                `<strong>${formatDateStr(lv.start_date)}</strong> to <strong>${formatDateStr(lv.end_date)}</strong>`;

            let siteInfo = '—';
            if (lv.target_site) {
                siteInfo = `<span class="badge bg-light text-dark border"><i class="fa-solid fa-location-dot text-danger me-1"></i>${escapeHtml(lv.target_site)}</span>`;
                if (lv.origin_site) {
                    siteInfo += `<div class="text-muted small" style="font-size: 0.7rem;">From: ${escapeHtml(lv.origin_site)}</div>`;
                }
            }

            let docLink = '<span class="text-muted">—</span>';
            if (lv.attachment) {
                const cleanAtt = lv.attachment.replace(/^uploads\//, '');
                const isPdf = cleanAtt.toLowerCase().endsWith('.pdf');
                docLink = `<a href="<?= base_url('uploads/') ?>${encodeURIComponent(cleanAtt)}" target="_blank" class="btn btn-sm ${isPdf ? 'btn-outline-danger' : 'btn-outline-primary'} rounded-pill px-2 py-0" style="font-size: 0.72rem;">
                    <i class="fa-solid ${isPdf ? 'fa-file-pdf' : 'fa-paperclip'} me-1"></i>${isPdf ? 'PDF Sheet' : 'Attachment'}
                </a>`;
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-3 text-muted">${index + 1}</td>
                <td><span class="badge ${badgeBg} rounded-pill px-2 py-1">${escapeHtml(lv.leave_type)}</span></td>
                <td>${datePeriod}</td>
                <td><span class="badge bg-light text-dark border">${lv.days_count} Day(s)</span></td>
                <td>${siteInfo}</td>
                <td class="text-muted">${escapeHtml(lv.reason || 'Approved Leave')}</td>
                <td>${docLink}</td>
                <td class="pe-3 text-end"><span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1"><i class="fa-solid fa-check me-1"></i>Approved</span></td>
            `;
            tbody.appendChild(tr);
        });
    }

    const modal = new bootstrap.Modal(document.getElementById('employeeLeaveSheetModal'));
    modal.show();
}

function formatDateStr(str) {
    if (!str) return '';
    const parts = str.split('-');
    if (parts.length === 3) {
        const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    return str;
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

function printLeaveSheet() {
    window.print();
}

function calcCompTotal() {
    const cl = parseFloat(document.getElementById('compClQuota').value) || 0;
    const sl = parseFloat(document.getElementById('compSlQuota').value) || 0;
    const pl = parseFloat(document.getElementById('compPlQuota').value) || 0;
    document.getElementById('compTotalDisplay').textContent = `${(cl + sl + pl)} Days`;
}

function openEditEmpQuota(data) {
    document.getElementById('editEmpId').value = data.id;
    document.getElementById('editEmpName').textContent = data.name;
    document.getElementById('editEmpCode').textContent = data.code;
    
    const isCustom = data.is_custom;
    const check = document.getElementById('useCompanyDefaultCheck');
    check.checked = !isCustom;

    document.getElementById('editEmpCl').value = data.cl;
    document.getElementById('editEmpSl').value = data.sl;
    document.getElementById('editEmpPl').value = data.pl;

    toggleCustomQuotaInputs(!isCustom);
    calcEmpTotal();

    const modal = new bootstrap.Modal(document.getElementById('editEmpQuotaModal'));
    modal.show();
}

function toggleCustomQuotaInputs(useDefault) {
    const container = document.getElementById('customQuotaInputsContainer');
    if (useDefault) {
        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';
        document.getElementById('editEmpCl').value = defaultCompanyQuotas.CL;
        document.getElementById('editEmpSl').value = defaultCompanyQuotas.SL;
        document.getElementById('editEmpPl').value = defaultCompanyQuotas.PL;
    } else {
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
    }
    calcEmpTotal();
}

function calcEmpTotal() {
    const cl = parseFloat(document.getElementById('editEmpCl').value) || 0;
    const sl = parseFloat(document.getElementById('editEmpSl').value) || 0;
    const pl = parseFloat(document.getElementById('editEmpPl').value) || 0;
    document.getElementById('editEmpTotalDisplay').textContent = `${(cl + sl + pl)} Days`;
}

function updateModalEmpQuota(val) {
    const box = document.getElementById('modalEmpQuotaBox');
    if (!val || !box) {
        if (box) box.classList.add('d-none');
        return;
    }

    let empId = null;
    const cleanVal = val.toLowerCase().trim();
    
    // Check if format is "CODE — Name"
    if (cleanVal.includes('—')) {
        const parts = cleanVal.split('—');
        const code = parts[0].trim();
        if (employeesLookup[code]) empId = employeesLookup[code];
    } else if (cleanVal.includes('-')) {
        const parts = cleanVal.split('-');
        const code = parts[0].trim();
        if (employeesLookup[code]) empId = employeesLookup[code];
    } else if (employeesLookup[cleanVal]) {
        empId = employeesLookup[cleanVal];
    }

    if (!empId) {
        for (const [key, id] of Object.entries(employeesLookup)) {
            if (cleanVal.includes(key) || key.includes(cleanVal)) {
                empId = id;
                break;
            }
        }
    }

    if (empId && allEmployeeBalances[empId]) {
        const b = allEmployeeBalances[empId];
        box.classList.remove('d-none');
        document.getElementById('modalEmpTotalBalBadge').textContent = `${b.balance.total} / ${b.assigned.total} Days Left`;
        document.getElementById('modalClBal').textContent = `CL: ${b.balance.CL} / ${b.assigned.CL} Left`;
        document.getElementById('modalSlBal').textContent = `SL: ${b.balance.SL} / ${b.assigned.SL} Left`;
        document.getElementById('modalPlBal').textContent = `PL: ${b.balance.PL} / ${b.assigned.PL} Left`;
    } else {
        box.classList.add('d-none');
    }
}

function syncToDate(val) {
    const toInput = document.getElementById('leaveToDate');
    if (toInput && (!toInput.value || toInput.value < val)) {
        toInput.value = val;
    }
}

function handleLeaveTypeChange(val) {
    const odBlock = document.getElementById('odSiteFields');
    const targetInput = document.getElementById('odTargetSite');
    if (!odBlock) return;
    const isOd = (val === 'On Duty' || val.includes('Duty') || val.includes('OD') || val.includes('Official') || val.includes('Outdoor'));
    if (isOd) {
        odBlock.classList.remove('d-none');
        if (targetInput) targetInput.required = true;
    } else {
        odBlock.classList.add('d-none');
        if (targetInput) {
            targetInput.required = false;
            targetInput.value = '';
        }
    }
}
</script>
