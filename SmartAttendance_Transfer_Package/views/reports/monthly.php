<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
    <div class="card-body p-4">

        <!-- Header -->
        <div class="row g-3 align-items-center mb-4">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h5 class="fw-bold text-dark mb-0">Monthly Attendance Timesheet & Audit</h5>
                    <span class="badge bg-primary rounded-pill px-2 py-1 small"><?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></span>
                </div>
                <p class="text-muted small mb-0">Comprehensive employee attendance, daily punches, working hours, and time audit matrix</p>
            </div>
            <div class="col-md-6 text-md-end d-flex gap-2 justify-content-md-end flex-wrap">
                <?php 
                $exportQuery = http_build_query([
                    'year' => $year,
                    'month' => $month,
                    'department_id' => $departmentId,
                    'site' => $site
                ]);
                ?>
                <a href="<?= base_url('reports/monthly/audit-export?' . $exportQuery) ?>" class="btn btn-outline-primary rounded-pill px-3 shadow-sm">
                    <i class="fa-solid fa-file-invoice me-1"></i> Download Time Audit (.csv)
                </a>
                <a href="<?= base_url('reports/monthly/export?' . $exportQuery) ?>" class="btn btn-outline-success rounded-pill px-3 shadow-sm">
                    <i class="fa-solid fa-file-excel me-1"></i> Download Matrix (.csv)
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="<?= base_url('reports/monthly') ?>" class="row g-2 mb-4 p-3 bg-light rounded-4 border">
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">Month</label>
                <select name="month" class="form-select form-select-sm bg-white">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= ($month == $m) ? 'selected' : '' ?>>
                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">Year</label>
                <select name="year" class="form-select form-select-sm bg-white">
                    <?php 
                    $curYear = (int) date('Y');
                    for ($y = $curYear - 2; $y <= $curYear + 1; $y++): ?>
                        <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small text-muted fw-semibold mb-1">Department</label>
                <select name="department_id" class="form-select form-select-sm bg-white">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= ($departmentId == $dept['id']) ? 'selected' : '' ?>>
                            <?= e($dept['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small text-muted fw-semibold mb-1">Work Site</label>
                <select name="site" class="form-select form-select-sm bg-white">
                    <option value="">All Work Sites</option>
                    <?php foreach ($siteList ?? [] as $s): ?>
                        <option value="<?= e($s) ?>" <?= (($site ?? '') === $s) ? 'selected' : '' ?>><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-dark btn-sm w-100"><i class="fa-solid fa-arrows-rotate me-1"></i> Generate</button>
            </div>
        </form>

        <!-- Monthly Executive KPI Summary & Flow Cards -->
        <?php if (!empty($summary)): ?>
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-4 border">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small fw-semibold text-muted">Active Staff</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="fa-solid fa-users me-1"></i> Staff</span>
                        </div>
                        <h4 class="fw-bold text-dark mb-0"><?= $summary['total_employees'] ?></h4>
                        <small class="text-muted" style="font-size: 0.7rem;">In selected filters</small>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="p-3 bg-success-subtle bg-opacity-25 rounded-4 border border-success-subtle">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small fw-semibold text-success">Total Present</span>
                            <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> Present</span>
                        </div>
                        <h4 class="fw-bold text-success mb-0"><?= $summary['total_present_days'] ?> <span class="fs-6 fw-normal text-muted">Days</span></h4>
                        <small class="text-success" style="font-size: 0.7rem;"><strong><?= $summary['attendance_rate'] ?>%</strong> overall attendance</small>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="p-3 bg-danger-subtle bg-opacity-25 rounded-4 border border-danger-subtle">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small fw-semibold text-danger">Total Absent</span>
                            <span class="badge bg-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Absent</span>
                        </div>
                        <h4 class="fw-bold text-danger mb-0"><?= $summary['total_absent_days'] ?> <span class="fs-6 fw-normal text-muted">Days</span></h4>
                        <small class="text-muted" style="font-size: 0.7rem;">Unattended business days</small>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="p-3 bg-info-subtle bg-opacity-25 rounded-4 border border-info-subtle">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small fw-semibold text-info-emphasis">Public Holidays</span>
                            <span class="badge bg-info text-white"><i class="fa-solid fa-calendar-star me-1"></i> Holidays</span>
                        </div>
                        <h4 class="fw-bold text-info-emphasis mb-0"><?= $summary['total_holidays'] ?? 0 ?> <span class="fs-6 fw-normal text-muted">Days</span></h4>
                        <small class="text-muted" style="font-size: 0.7rem;">Official holidays this month</small>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Report Mode Tabs Switcher -->
        <ul class="nav nav-pills mb-3 p-1 bg-light rounded-pill border" id="monthlyReportTabs" role="tablist" style="width: fit-content;">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 py-2 small fw-bold" id="tab-audit-btn" data-bs-toggle="pill" data-bs-target="#tab-audit-pane" type="button" role="tab">
                    <i class="fa-solid fa-list-check me-2"></i> Employee Punch IN / OUT Audit Log
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2 small fw-bold" id="tab-grid-btn" data-bs-toggle="pill" data-bs-target="#tab-grid-pane" type="button" role="tab">
                    <i class="fa-solid fa-table-cells me-2"></i> 30-Day Matrix Summary Grid
                </button>
            </li>
        </ul>

        <div class="tab-content" id="monthlyReportTabsContent">

            <!-- TAB 1: EMPLOYEE-WISE DAY-BY-DAY PUNCH IN/OUT AUDIT LOG -->
            <div class="tab-pane fade show active" id="tab-audit-pane" role="tabpanel">
                <?php if (empty($report['data'])): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-regular fa-calendar-xmark fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                        No employee records found matching the selected filters.
                    </div>
                <?php else: ?>
                    <div class="accordion accordion-flush" id="employeeAuditAccordion">
                        <?php foreach ($report['data'] as $idx => $row): 
                            $emp = $row['employee'];
                            $collapseId = "collapseEmp" . $emp['id'];
                            $headingId = "headingEmp" . $emp['id'];
                        ?>
                            <div class="accordion-item border rounded-4 mb-3 overflow-hidden shadow-sm">
                                <h2 class="accordion-header" id="<?= $headingId ?>">
                                    <button class="accordion-button <?= $idx > 0 ? 'collapsed' : '' ?> bg-light px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>">
                                        <div class="d-flex align-items-center justify-content-between w-100 me-3 flex-wrap gap-2">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                                    <?= strtoupper(substr($emp['name'], 0, 2)) ?>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold text-dark mb-0"><?= e($emp['name']) ?></h6>
                                                    <div class="small text-muted font-monospace">
                                                        <span><?= e($emp['employee_code']) ?></span> &bull; 
                                                        <span><?= e($emp['department_name'] ?? 'General') ?></span>
                                                        <?php if (!empty($emp['site'])): ?>
                                                            &bull; <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1"><i class="fa-solid fa-location-dot me-1"></i><?= e($emp['site']) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                                    <i class="fa-solid fa-circle-check me-1"></i> <?= $row['present_days'] ?> Present Days
                                                </span>
                                                <?php if (!empty($row['leave_days'])): ?>
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill">
                                                        <i class="fa-solid fa-plane-departure me-1"></i> <?= $row['leave_days'] ?> Leaves
                                                    </span>
                                                <?php endif; ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill font-monospace">
                                                    <i class="fa-solid fa-clock me-1"></i> <?= $row['total_hours'] ?>h Total
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>" data-bs-parent="#employeeAuditAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.8rem;">
                                                <thead class="bg-light text-muted small text-uppercase">
                                                    <tr>
                                                        <th class="text-start ps-3" style="min-width: 120px;">Date & Day</th>
                                                        <th style="min-width: 130px;">Punch IN</th>
                                                        <th style="min-width: 130px;">Punch OUT</th>
                                                        <th style="min-width: 110px;">Total Worked</th>
                                                        <th style="min-width: 80px;">Punches</th>
                                                        <th style="min-width: 130px;">Time Audit Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php for ($d = 1; $d <= $report['days_in_month']; $d++): 
                                                        $stat = $row['daily_stats'][$d] ?? [];
                                                        $curDateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                                        $auditStatus = $stat['audit_status'] ?? 'ABSENT';
                                                        $isHoliday = isset($holidaysMap[$curDateStr]);
                                                        $holidayTitle = $isHoliday ? $holidaysMap[$curDateStr] : '';
                                                    ?>
                                                        <tr class="<?= $isHoliday ? 'bg-info bg-opacity-10' : ($auditStatus === 'WEEKEND' ? 'bg-light bg-opacity-50 text-muted' : '') ?>">
                                                            <td class="text-start ps-3 fw-semibold">
                                                                <span><?= date('d M Y', strtotime($stat['date'])) ?></span>
                                                                <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.68rem;"><?= $stat['day_name'] ?></span>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($stat['first_in'])): ?>
                                                                    <div class="d-inline-flex align-items-center gap-1">
                                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace">
                                                                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i><?= $stat['first_in'] ?>
                                                                        </span>
                                                                        <?php if (!empty($stat['in_photo'])): ?>
                                                                            <a href="javascript:void(0)" onclick="viewPunchPhoto('<?= uploaded_url($stat['in_photo']) ?>', '<?= e($emp['name']) ?> - Punch IN Selfie', '<?= date('d M Y', strtotime($stat['date'])) ?> <?= $stat['first_in'] ?>')" class="btn btn-sm btn-light border p-0 rounded-circle shadow-sm overflow-hidden" title="Click to View IN Selfie" style="width: 26px; height: 26px;">
                                                                                <img src="<?= uploaded_url($stat['in_photo']) ?>" class="w-100 h-100 object-fit-cover" alt="IN Selfie">
                                                                            </a>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <span class="text-muted">—</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($stat['last_out'])): ?>
                                                                    <div class="d-inline-flex align-items-center gap-1">
                                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 font-monospace">
                                                                            <i class="fa-solid fa-arrow-right-from-bracket me-1"></i><?= $stat['last_out'] ?>
                                                                        </span>
                                                                        <?php if (!empty($stat['out_photo'])): ?>
                                                                            <a href="javascript:void(0)" onclick="viewPunchPhoto('<?= uploaded_url($stat['out_photo']) ?>', '<?= e($emp['name']) ?> - Punch OUT Selfie', '<?= date('d M Y', strtotime($stat['date'])) ?> <?= $stat['last_out'] ?>')" class="btn btn-sm btn-light border p-0 rounded-circle shadow-sm overflow-hidden" title="Click to View OUT Selfie" style="width: 26px; height: 26px;">
                                                                                <img src="<?= uploaded_url($stat['out_photo']) ?>" class="w-100 h-100 object-fit-cover" alt="OUT Selfie">
                                                                            </a>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php elseif ($auditStatus === 'CHECKED_IN'): ?>
                                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                                                        <i class="fa-solid fa-clock me-1"></i> Working Now
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">—</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($stat['seconds'] > 0): ?>
                                                                    <span class="fw-bold font-monospace text-primary"><?= $stat['formatted_duration'] ?></span>
                                                                    <small class="text-muted d-block" style="font-size: 0.68rem;">(<?= $stat['hours'] ?>h)</small>
                                                                <?php else: ?>
                                                                    <span class="text-muted">00:00:00</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-light text-dark border"><?= $stat['punch_count'] ?></span>
                                                            </td>
                                                            <td>
                                                                <?php if ($auditStatus === 'COMPLETED'): ?>
                                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                                        <i class="fa-solid fa-circle-check me-1"></i> Completed
                                                                    </span>
                                                                <?php elseif ($auditStatus === 'CHECKED_IN'): ?>
                                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                                                        <i class="fa-solid fa-spinner fa-spin me-1"></i> Checked IN
                                                                    </span>
                                                                <?php elseif ($auditStatus === 'NO_OUT'): ?>
                                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" title="Shift ended with unrecorded OUT punch">
                                                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Missing OUT
                                                                    </span>
                                                                <?php elseif ($auditStatus === 'LEAVE'): ?>
                                                                    <?php if (!empty($stat['target_site'])): ?>
                                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" title="On Duty / OD Visit: <?= e($stat['target_site']) ?><?= !empty($stat['leave_reason']) ? ' - ' . e($stat['leave_reason']) : '' ?>">
                                                                            <i class="fa-solid fa-location-dot text-danger me-1"></i> OD: <?= e($stat['target_site']) ?>
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" title="<?= e($stat['leave_reason'] ?? '') ?>">
                                                                            <i class="fa-solid fa-plane-departure me-1 text-warning"></i> <?= e($stat['leave_type'] ?? 'Leave') ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                <?php elseif ($isHoliday): ?>
                                                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1" title="Public Holiday: <?= e($holidayTitle) ?>">
                                                                        <i class="fa-solid fa-calendar-star me-1 text-info"></i> Holiday (<?= e($holidayTitle) ?>)
                                                                    </span>
                                                                <?php elseif ($auditStatus === 'WEEKEND'): ?>
                                                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                                                        <i class="fa-solid fa-bed me-1"></i> Weekend OFF
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                                        <i class="fa-solid fa-xmark me-1"></i> Absent
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endfor; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAB 2: 30-DAY COMPACT MATRIX GRID -->
            <div class="tab-pane fade" id="tab-grid-pane" role="tabpanel">
                <div class="d-flex align-items-center gap-3 mb-3 small text-muted flex-wrap">
                    <span class="fw-bold text-dark">Legend:</span>
                    <span><span class="badge bg-success">P</span> Present (Hours)</span>
                    <span><span class="badge bg-info text-dark">H</span> Public Holiday</span>
                    <span><span class="badge bg-warning-subtle text-warning-emphasis border">CL/SL/PL/OD</span> Leave</span>
                    <span><span class="badge bg-secondary">OFF</span> Weekend</span>
                    <span><span class="badge bg-danger">A</span> Absent</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 timesheet-table text-center" style="font-size: 0.75rem;">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-start ps-3" style="min-width: 140px;">Employee</th>
                                <th class="text-start" style="min-width: 110px;">Work Site</th>
                                <th style="min-width: 50px;" title="Total Present Days">Present</th>
                                <th style="min-width: 50px;" title="Total Leave Days">Leave</th>
                                <th style="min-width: 50px;" title="Total Absent Days">Absent</th>
                                <th style="min-width: 60px;">Total Hrs</th>
                                <?php for ($d = 1; $d <= $report['days_in_month']; $d++): 
                                    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                    $dayName = date('D', strtotime($dateStr));
                                    $isWeekend = ($dayName === 'Sat' || $dayName === 'Sun');
                                    $isHolDay = isset($holidaysMap[$dateStr]);
                                ?>
                                    <th class="<?= $isHolDay ? 'bg-info bg-opacity-50 text-dark' : ($isWeekend ? 'bg-secondary bg-opacity-25' : '') ?>" style="min-width: 38px;" title="<?= $isHolDay ? e($holidaysMap[$dateStr]) : '' ?>">
                                        <div><?= $d ?></div>
                                        <div style="font-size: 0.65rem; opacity: 0.75;"><?= substr($dayName, 0, 1) ?></div>
                                    </th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($report['data'])): ?>
                                <tr>
                                    <td colspan="<?= $report['days_in_month'] + 6 ?>" class="text-center py-4 text-muted">
                                        No active employees found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($report['data'] as $row): 
                                    $emp = $row['employee'];
                                    $empPresent = (int) $row['present_days'];
                                    $empLeaves = (int) ($row['leave_days'] ?? 0);
                                    $empAbsent = max(0, $report['days_in_month'] - $empPresent - $empLeaves);
                                ?>
                                    <tr>
                                        <td class="text-start ps-3 fw-semibold text-truncate" style="max-width: 160px;">
                                            <a href="<?= base_url('employees/view/' . $emp['id']) ?>" class="text-dark text-decoration-none">
                                                <?= e($emp['name']) ?>
                                            </a>
                                            <div class="text-muted font-monospace" style="font-size: 0.65rem;"><?= e($emp['employee_code']) ?></div>
                                        </td>
                                        <td class="text-start">
                                            <?php if (!empty($emp['site'])): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                                    <i class="fa-solid fa-location-dot me-1 text-danger"></i><?= e($emp['site']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold text-success"><?= $empPresent ?>d</td>
                                        <td class="fw-bold text-warning-emphasis"><?= $empLeaves ?>d</td>
                                        <td class="fw-bold text-danger"><?= $empAbsent ?>d</td>
                                        <td class="fw-bold font-monospace text-primary"><?= $row['total_hours'] ?>h</td>

                                        <?php for ($d = 1; $d <= $report['days_in_month']; $d++): 
                                            $curDateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                            $stat = $row['daily_stats'][$d] ?? ['status' => 'A', 'hours' => 0];
                                            $isHol = isset($holidaysMap[$curDateStr]);
                                        ?>
                                            <td class="p-1">
                                                <?php if ($stat['status'] === 'P'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle p-1 d-block" title="<?= $stat['hours'] ?> hours worked">
                                                        <?= $stat['hours'] > 0 ? $stat['hours'] . 'h' : 'P' ?>
                                                    </span>
                                                <?php elseif ($stat['status'] === 'L'): 
                                                    $lCode = $stat['leave_code'] ?? 'L';
                                                    $lBadgeClass = match($lCode) {
                                                        'CL' => 'bg-info-subtle text-info border border-info-subtle',
                                                        'SL' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                                        'PL' => 'bg-success-subtle text-success border border-success-subtle',
                                                        'OD' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                                        default => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                    };
                                                ?>
                                                    <span class="badge <?= $lBadgeClass ?> p-1 d-block" title="Leave: <?= e($stat['leave_type'] ?? 'Leave') ?><?= !empty($stat['leave_reason']) ? ' (' . e($stat['leave_reason']) . ')' : '' ?>">
                                                        <?= $lCode ?>
                                                    </span>
                                                <?php elseif ($isHol): ?>
                                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle p-1 d-block" title="Public Holiday: <?= e($holidaysMap[$curDateStr]) ?>">
                                                        H
                                                    </span>
                                                <?php elseif ($stat['status'] === 'W'): ?>
                                                    <span class="badge bg-light text-muted border p-1 d-block">OFF</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle p-1 d-block">A</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endfor; ?>

                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>
