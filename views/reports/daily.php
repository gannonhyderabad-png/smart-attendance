<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
    <div class="card-body p-4">

        <!-- Header -->
        <div class="row g-3 align-items-center mb-4">
            <div class="col-md-6">
                <h5 class="fw-bold text-dark mb-1">Daily Attendance Summary</h5>
                <p class="text-muted small mb-0">Overview of employee presence, work hours, and punch timelines for <?= date('l, d F Y', strtotime($date)) ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?= base_url('reports/daily/export?' . http_build_query(['date' => $date, 'department_id' => $departmentId, 'status' => $status, 'site' => $site])) ?>" class="btn btn-success rounded-pill px-3 shadow-sm">
                    <i class="fa-solid fa-file-excel me-1"></i> Download Excel (.csv)
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="<?= base_url('reports/daily') ?>" class="row g-2 mb-4 p-3 bg-light rounded-4 border">
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">Select Date</label>
                <input type="date" name="date" class="form-control form-control-sm bg-white" value="<?= e($date) ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label small text-muted fw-semibold mb-1">Department</label>
                <select name="department_id" class="form-select form-select-sm bg-white">
                    <option value="">All Departments</option>
                    <?php foreach ($departmentList as $deptName): ?>
                        <option value="<?= e($deptName) ?>" <?= ($departmentId == $deptName) ? 'selected' : '' ?>>
                            <?= e($deptName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small text-muted fw-semibold mb-1">Work Site</label>
                <select name="site" class="form-select form-select-sm bg-white">
                    <option value="">All Sites</option>
                    <?php foreach ($siteList as $sName): ?>
                        <option value="<?= e($sName) ?>" <?= (($site ?? '') === $sName) ? 'selected' : '' ?>>
                            <?= e($sName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">Attendance Status</label>
                <select name="status" class="form-select form-select-sm bg-white">
                    <option value="">All Statuses</option>
                    <option value="IN" <?= (isset($status) && strtoupper($status) === 'IN') ? 'selected' : '' ?>>Working Now (IN)</option>
                    <option value="OUT" <?= (isset($status) && strtoupper($status) === 'OUT') ? 'selected' : '' ?>>Punched Out</option>
                    <option value="NO_OUT" <?= (isset($status) && (strtoupper($status) === 'NO_OUT' || strtoupper($status) === 'NO_OUT_PUNCH')) ? 'selected' : '' ?>>No OUT Punch (Auto-Closed)</option>
                    <option value="ABSENT" <?= (isset($status) && strtoupper($status) === 'ABSENT') ? 'selected' : '' ?>>Absent Today</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end gap-1">
                <button type="submit" class="btn btn-dark btn-sm flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                <a href="<?= base_url('reports/daily?date=' . $date) ?>" class="btn btn-outline-secondary btn-sm" title="Reset Filters"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>

        <!-- Summary Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Code</th>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Project</th>
                        <th>Site</th>
                        <th>Status</th>
                        <th>First Check IN</th>
                        <th>Last Check OUT</th>
                        <th>Punches</th>
                        <th class="pe-3 text-end">Total Work Hours</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (empty($summary)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-filter-circle-xmark fs-4 d-block mb-2 text-secondary"></i>
                                No active employees found matching the filters.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($summary as $item): 
                            $emp = $item['employee'];
                        ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-light text-dark border font-monospace"><?= e($emp['employee_code']) ?></span>
                                </td>
                                <td>
                                    <a href="<?= base_url('employees/view/' . $emp['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= e($emp['name']) ?>
                                    </a>
                                    <div class="text-muted small"><?= e($emp['designation'] ?? 'Staff') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle px-2 py-1">
                                        <i class="fa-solid fa-building me-1 text-secondary"></i><?= e($emp['department_name'] ?? ($emp['department'] ?? 'General')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($emp['project'])): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                            <i class="fa-solid fa-diagram-project me-1"></i><?= e($emp['project']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($emp['site'])): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <i class="fa-solid fa-location-dot me-1"></i><?= e($emp['site']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item['status'] === 'CHECKED_IN'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i> Currently Working
                                        </span>
                                    <?php elseif ($item['status'] === 'NO_OUT_PUNCH' || !empty($item['is_no_out'])): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" title="Employee did not punch OUT. Work duration auto-closed at scheduled shift end.">
                                            <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> No OUT Punch
                                        </span>
                                    <?php elseif ($item['status'] === 'COMPLETED'): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> Present / Out
                                        </span>
                                    <?php elseif ($item['status'] === 'LEAVE' || !empty($item['leave_info'])): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" title="<?= e($item['leave_info']['reason'] ?? '') ?>">
                                            <i class="fa-solid fa-plane-departure text-warning me-1"></i> On Leave (<?= e($item['leave_info']['leave_type'] ?? 'Approved') ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                            <i class="fa-solid fa-circle-xmark me-1"></i> Absent
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $item['first_in'] ? '<span class="font-monospace text-success fw-bold">' . date('h:i A', strtotime($item['first_in'])) . '</span>' : '—' ?>
                                </td>
                                <td>
                                    <?php if ($item['last_out']): ?>
                                        <span class="font-monospace text-danger fw-bold"><?= date('h:i A', strtotime($item['last_out'])) ?></span>
                                    <?php elseif ($item['status'] === 'NO_OUT_PUNCH' || !empty($item['is_no_out'])): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace small" title="Auto-closed at scheduled shift end">
                                            <i class="fa-regular fa-clock me-1"></i>Auto: <?= date('h:i A', strtotime($item['shift_end'] ?? '18:00:00')) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-muted border"><?= $item['punch_count'] ?> punches</span>
                                </td>
                                <td class="pe-3 text-end">
                                    <span class="fw-bold font-monospace <?= $item['seconds_worked'] > 0 ? 'text-primary' : 'text-muted' ?>">
                                        <?= $item['formatted_duration'] ?>
                                    </span>
                                    <?php if ($item['status'] === 'NO_OUT_PUNCH' || !empty($item['is_no_out'])): ?>
                                        <div class="small text-muted" style="font-size: 0.65rem;">
                                            <i class="fa-solid fa-calculator me-1"></i>Shift Time
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
