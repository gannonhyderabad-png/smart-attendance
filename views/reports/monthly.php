<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
    <div class="card-body p-4">

        <!-- Header -->
        <div class="row g-3 align-items-center mb-4">
            <div class="col-md-6">
                <h5 class="fw-bold text-dark mb-1">Monthly Timesheet Matrix</h5>
                <p class="text-muted small mb-0">Detailed day-by-day attendance grid for <?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <?php 
                $exportQuery = http_build_query([
                    'year' => $year,
                    'month' => $month,
                    'department_id' => $departmentId,
                    'site' => $site ?? null
                ]);
                ?>
                <a href="<?= base_url('reports/monthly/export?' . $exportQuery) ?>" class="btn btn-success rounded-pill px-3 shadow-sm">
                    <i class="fa-solid fa-file-excel me-1"></i> Download Excel (.csv)
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
                    <div class="p-3 bg-primary-subtle bg-opacity-25 rounded-4 border border-primary-subtle">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small fw-semibold text-primary">Workforce Hours</span>
                            <span class="badge bg-primary"><i class="fa-solid fa-clock me-1"></i> Hours</span>
                        </div>
                        <h4 class="fw-bold text-primary font-monospace mb-0"><?= $summary['total_hours'] ?>h</h4>
                        <small class="text-muted" style="font-size: 0.7rem;">Across <?= $summary['total_punches'] ?> total punches</small>
                    </div>
                </div>
            </div>

            <!-- Attendance Workflow Process -->
            <div class="p-3 bg-white rounded-4 border shadow-sm mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-bold text-dark"><i class="fa-solid fa-diagram-project text-primary me-2"></i>Attendance Process Flow</span>
                    <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">Standard Operational Procedure</span>
                </div>
                <div class="row g-2 text-center small">
                    <div class="col-md-3">
                        <div class="p-2 bg-light rounded-3 border">
                            <div class="fw-bold text-primary mb-1"><i class="fa-solid fa-camera me-1"></i> 1. Punch IN</div>
                            <span class="text-muted" style="font-size: 0.72rem;">Selfie snapshot + GPS Geofence Check</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2 bg-light rounded-3 border">
                            <div class="fw-bold text-info mb-1"><i class="fa-solid fa-stopwatch me-1"></i> 2. Working Session</div>
                            <span class="text-muted" style="font-size: 0.72rem;">Live duration timer recorded in DB</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2 bg-light rounded-3 border">
                            <div class="fw-bold text-success mb-1"><i class="fa-solid fa-right-from-bracket me-1"></i> 3. Punch OUT</div>
                            <span class="text-muted" style="font-size: 0.72rem;">Session completed & Total hours computed</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2 bg-light rounded-3 border">
                            <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-file-invoice me-1"></i> 4. Timesheet Matrix</div>
                            <span class="text-muted" style="font-size: 0.72rem;">Daily & Monthly aggregated timesheet</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Legend -->
        <div class="d-flex align-items-center gap-3 mb-3 small text-muted">
            <span class="fw-bold">Legend:</span>
            <span><span class="badge bg-success">P</span> Present (Hours)</span>
            <span><span class="badge bg-danger">A</span> Absent</span>
            <span><span class="badge bg-secondary">OFF</span> Weekend</span>
        </div>

        <!-- Timesheet Grid -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 timesheet-table text-center" style="font-size: 0.75rem;">
                <thead class="table-dark">
                    <tr>
                        <th class="text-start ps-3" style="min-width: 140px;">Employee</th>
                        <th class="text-start" style="min-width: 110px;">Work Site</th>
                        <th style="min-width: 55px;" title="Total Present Days">Present</th>
                        <th style="min-width: 55px;" title="Total Absent Days">Absent</th>
                        <th style="min-width: 65px;">Total Hrs</th>
                        <?php for ($d = 1; $d <= $report['days_in_month']; $d++): 
                            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                            $dayName = date('D', strtotime($dateStr));
                            $isWeekend = ($dayName === 'Sat' || $dayName === 'Sun');
                        ?>
                            <th class="<?= $isWeekend ? 'bg-secondary bg-opacity-25' : '' ?>" style="min-width: 38px;">
                                <div><?= $d ?></div>
                                <div style="font-size: 0.65rem; opacity: 0.75;"><?= substr($dayName, 0, 1) ?></div>
                            </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($report['data'])): ?>
                        <tr>
                            <td colspan="<?= $report['days_in_month'] + 5 ?>" class="text-center py-4 text-muted">
                                No active employees found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($report['data'] as $row): 
                            $emp = $row['employee'];
                            $empPresent = (int) $row['present_days'];
                            $empAbsent = max(0, $report['days_in_month'] - $empPresent);
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
                                <td class="fw-bold text-danger"><?= $empAbsent ?>d</td>
                                <td class="fw-bold font-monospace text-primary"><?= $row['total_hours'] ?>h</td>

                                <?php for ($d = 1; $d <= $report['days_in_month']; $d++): 
                                    $stat = $row['daily_stats'][$d] ?? ['status' => 'A', 'hours' => 0];
                                ?>
                                    <td class="p-1">
                                        <?php if ($stat['status'] === 'P'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle p-1 d-block" title="<?= $stat['hours'] ?> hours worked">
                                                <?= $stat['hours'] > 0 ? $stat['hours'] . 'h' : 'P' ?>
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
