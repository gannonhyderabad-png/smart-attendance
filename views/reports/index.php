<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
    <div class="card-body p-4">

        <!-- Header & Action Row -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">
                    <i class="fa-solid fa-chart-column text-primary me-2"></i>Attendance Reports & Analytics
                </h4>
                <p class="text-muted small mb-0">Unified attendance reporting suite with daily summaries, weekly logs, monthly timesheets, and employee dossiers.</p>
            </div>
            
            <!-- Excel / CSV Table Download Buttons -->
            <div class="d-flex align-items-center gap-2">
                <?php
                    $exportParams = [
                        'type' => $reportType,
                        'date' => $date,
                        'year' => $year,
                        'month' => $month,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'employee_id' => $employeeId,
                        'department_id' => $departmentId,
                        'status' => $status,
                        'site' => $site,
                        'format' => 'excel'
                    ];
                    $exportUrlExcel = base_url('reports/export?' . http_build_query($exportParams));
                    $exportParams['format'] = 'csv';
                    $exportUrlCsv = base_url('reports/export?' . http_build_query($exportParams));
                ?>
                <div class="btn-group shadow-sm">
                    <a href="<?= $exportUrlExcel ?>" class="btn btn-success rounded-pill-start px-3 py-2 fw-semibold" title="Download formatted table spreadsheet with company header, site name, and timestamp">
                        <i class="fa-solid fa-file-excel me-1"></i> Download Excel (.xls)
                    </a>
                    <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split rounded-pill-end px-2" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                        <li>
                            <a class="dropdown-item py-2 fw-semibold" href="<?= $exportUrlExcel ?>">
                                <i class="fa-solid fa-file-excel text-success me-2"></i>Excel Table Format (.xls)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= $exportUrlCsv ?>">
                                <i class="fa-solid fa-file-csv text-primary me-2"></i>Plain CSV Sheet (.csv)
                            </a>
                        </li>
                        <?php if ($reportType === 'monthly'): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 small text-secondary" href="<?= base_url('reports/export?type=monthly_audit&' . http_build_query(['year' => $year, 'month' => $month, 'site' => $site, 'department_id' => $departmentId])) ?>">
                                    <i class="fa-solid fa-clock-rotate-left text-warning me-2"></i>Day-by-Day IN/OUT Audit (.xls)
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <button type="button" onclick="window.print()" class="btn btn-light border rounded-pill px-3 py-2" title="Print Current Report">
                    <i class="fa-solid fa-print text-muted"></i>
                </button>
            </div>
        </div>

        <!-- 1. UNIFIED REPORT MODE SELECTOR -->
        <div class="bg-light p-2 rounded-4 border mb-4">
            <div class="nav nav-pills nav-fill gap-2" role="tablist">
                <a href="<?= base_url('reports?report_type=daily&' . http_build_query(['date' => $date, 'department_id' => $departmentId, 'site' => $site])) ?>" 
                   class="nav-link rounded-pill py-2 <?= ($reportType === 'daily') ? 'active shadow-sm fw-bold' : 'text-secondary' ?>">
                    <i class="fa-solid fa-calendar-day me-1"></i> Daily Attendance
                </a>
                <a href="<?= base_url('reports?report_type=weekly&' . http_build_query(['start_date' => $startDate, 'end_date' => $endDate, 'department_id' => $departmentId, 'site' => $site])) ?>" 
                   class="nav-link rounded-pill py-2 <?= ($reportType === 'weekly') ? 'active shadow-sm fw-bold' : 'text-secondary' ?>">
                    <i class="fa-solid fa-calendar-week me-1"></i> Weekly Attendance
                </a>
                <a href="<?= base_url('reports?report_type=monthly&' . http_build_query(['year' => $year, 'month' => $month, 'department_id' => $departmentId, 'site' => $site])) ?>" 
                   class="nav-link rounded-pill py-2 <?= ($reportType === 'monthly') ? 'active shadow-sm fw-bold' : 'text-secondary' ?>">
                    <i class="fa-solid fa-table-cells me-1"></i> Monthly Timesheet
                </a>
                <a href="<?= base_url('reports?report_type=employee&' . http_build_query(['employee_id' => $employeeId, 'start_date' => $startDate, 'end_date' => $endDate])) ?>" 
                   class="nav-link rounded-pill py-2 <?= ($reportType === 'employee') ? 'active shadow-sm fw-bold' : 'text-secondary' ?>">
                    <i class="fa-solid fa-user-clock me-1"></i> Employee-wise Attendance
                </a>
            </div>
        </div>

        <!-- 2. DYNAMIC FILTER FORMS -->
        <div class="card border border-light-subtle rounded-4 bg-light mb-4 shadow-sm">
            <div class="card-body p-3">
                <form method="GET" action="<?= base_url('reports') ?>" class="row g-2 align-items-end">
                    <input type="hidden" name="report_type" value="<?= e($reportType) ?>">

                    <?php if ($reportType === 'daily'): ?>
                        <!-- Daily Filters -->
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-regular fa-calendar me-1 text-primary"></i>Select Date</label>
                            <input type="date" name="date" class="form-control form-control-sm bg-white" value="<?= e($date) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-solid fa-building me-1 text-secondary"></i>Department</label>
                            <select name="department_id" class="form-select form-select-sm bg-white">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= ($departmentId == $dept['id']) ? 'selected' : '' ?>><?= e($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-solid fa-location-dot me-1 text-danger"></i>Work Site</label>
                            <select name="site" class="form-select form-select-sm bg-white">
                                <option value="">All Sites</option>
                                <?php foreach ($siteList as $s): ?>
                                    <option value="<?= e($s) ?>" <?= ($site === $s) ? 'selected' : '' ?>><?= e($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-solid fa-filter me-1"></i>Status</label>
                            <select name="status" class="form-select form-select-sm bg-white">
                                <option value="">All Statuses</option>
                                <option value="IN" <?= ($status === 'IN') ? 'selected' : '' ?>>Working Now (IN)</option>
                                <option value="OUT" <?= ($status === 'OUT') ? 'selected' : '' ?>>Present (Completed)</option>
                                <option value="LEAVE" <?= ($status === 'LEAVE') ? 'selected' : '' ?>>Approved Leave</option>
                                <option value="ABSENT" <?= ($status === 'ABSENT') ? 'selected' : '' ?>>Absent</option>
                            </select>
                        </div>

                    <?php elseif ($reportType === 'weekly'): ?>
                        <!-- Weekly Filters -->
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-regular fa-calendar-plus me-1 text-primary"></i>From Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm bg-white" value="<?= e($startDate) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-regular fa-calendar-check me-1 text-primary"></i>To Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm bg-white" value="<?= e($endDate) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-solid fa-building me-1 text-secondary"></i>Department</label>
                            <select name="department_id" class="form-select form-select-sm bg-white">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= ($departmentId == $dept['id']) ? 'selected' : '' ?>><?= e($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-solid fa-location-dot me-1 text-danger"></i>Work Site</label>
                            <select name="site" class="form-select form-select-sm bg-white">
                                <option value="">All Sites</option>
                                <?php foreach ($siteList as $s): ?>
                                    <option value="<?= e($s) ?>" <?= ($site === $s) ? 'selected' : '' ?>><?= e($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    <?php elseif ($reportType === 'monthly'): ?>
                        <!-- Monthly Filters -->
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-regular fa-calendar me-1 text-primary"></i>Select Month</label>
                            <select name="month" class="form-select form-select-sm bg-white">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= ($month == $m) ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m, 1)) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-solid fa-calendar-days me-1 text-secondary"></i>Year</label>
                            <select name="year" class="form-select form-select-sm bg-white">
                                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                    <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-solid fa-building me-1 text-secondary"></i>Department</label>
                            <select name="department_id" class="form-select form-select-sm bg-white">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= ($departmentId == $dept['id']) ? 'selected' : '' ?>><?= e($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-solid fa-location-dot me-1 text-danger"></i>Work Site</label>
                            <select name="site" class="form-select form-select-sm bg-white">
                                <option value="">All Sites</option>
                                <?php foreach ($siteList as $s): ?>
                                    <option value="<?= e($s) ?>" <?= ($site === $s) ? 'selected' : '' ?>><?= e($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    <?php elseif ($reportType === 'employee'): ?>
                        <!-- Employee-wise Filters -->
                        <div class="col-md-4">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-solid fa-user me-1 text-primary"></i>Select Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select form-select-sm bg-white" required>
                                <option value="">-- Choose Employee --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp['id'] ?>" <?= ($employeeId == $emp['id']) ? 'selected' : '' ?>>
                                        <?= e($emp['employee_code']) ?> — <?= e($emp['name']) ?> (<?= e($emp['department'] ?? 'General') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-regular fa-calendar-plus me-1 text-primary"></i>From Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm bg-white" value="<?= e($startDate) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-semibold mb-1"><i class="fa-regular fa-calendar-check me-1 text-primary"></i>To Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm bg-white" value="<?= e($endDate) ?>">
                        </div>
                    <?php endif; ?>

                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1 shadow-sm"><i class="fa-solid fa-filter me-1"></i> Apply Filter</button>
                        <a href="<?= base_url('reports?report_type=' . $reportType) ?>" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. REPORT CONTENT VIEWS -->

        <!-- ================= MODE A: DAILY ATTENDANCE ================= -->
        <?php if ($reportType === 'daily'): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 border">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Employee</th>
                            <th>Department & Site</th>
                            <th>Attendance Status</th>
                            <th>First Check IN</th>
                            <th>Last Check OUT</th>
                            <th>Punches</th>
                            <th class="text-end pe-3">Worked Duration</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php if (empty($dailySummary)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-calendar-xmark fa-2x mb-2 text-secondary opacity-50 d-block"></i>
                                    No records found for the selected date and filters.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($dailySummary as $item): 
                                $emp = $item['employee'];
                            ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                <?= strtoupper(substr($emp['name'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <a href="<?= base_url('reports?report_type=employee&employee_id=' . $emp['id']) ?>" class="fw-bold text-dark text-decoration-none" title="View complete employee dossier">
                                                    <?= e($emp['name']) ?>
                                                </a>
                                                <div class="text-muted font-monospace small" style="font-size: 0.72rem;">
                                                    <?= e($emp['employee_code']) ?> &bull; <?= e($emp['designation'] ?? 'Staff') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= e($emp['department_name'] ?? ($emp['department'] ?? 'General')) ?></div>
                                        <div class="text-muted small font-monospace" style="font-size: 0.7rem;">
                                            <i class="fa-solid fa-location-dot text-danger me-1"></i><?= e($emp['site'] ?? 'Main Site') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($item['status'] === 'CHECKED_IN'): ?>
                                            <span class="badge bg-success text-white rounded-pill px-2 py-1"><i class="fa-solid fa-circle-play me-1"></i>Working Now (IN)</span>
                                        <?php elseif ($item['status'] === 'COMPLETED'): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1"><i class="fa-solid fa-check-double me-1"></i>Completed (OUT)</span>
                                        <?php elseif ($item['status'] === 'NO_OUT_PUNCH'): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>No OUT Punch</span>
                                        <?php elseif ($item['status'] === 'LEAVE'): 
                                            $lvInfo = $item['leave_info'] ?? [];
                                        ?>
                                            <div class="d-inline-flex flex-column">
                                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1">
                                                    <i class="fa-solid fa-plane-departure me-1"></i><?= e($lvInfo['leave_type'] ?? 'Leave') ?>
                                                </span>
                                                <?php if (!empty($lvInfo['target_site'])): ?>
                                                    <span class="text-primary small mt-1 font-monospace" style="font-size: 0.68rem;">
                                                        <i class="fa-solid fa-location-arrow me-1"></i>Target: <?= e($lvInfo['target_site']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1">Absent</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['first_in']): ?>
                                            <span class="badge bg-light text-dark border font-monospace"><i class="fa-solid fa-clock text-success me-1"></i><?= date('h:i:s A', strtotime($item['first_in'])) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['last_out']): ?>
                                            <span class="badge bg-light text-dark border font-monospace"><i class="fa-solid fa-clock text-danger me-1"></i><?= date('h:i:s A', strtotime($item['last_out'])) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= $item['punch_count'] ?></span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <span class="fw-bold <?= ($item['hours_worked'] >= 8) ? 'text-success' : (($item['hours_worked'] > 0) ? 'text-primary' : 'text-muted') ?>">
                                            <?= $item['formatted_duration'] ?> (<?= $item['hours_worked'] ?>h)
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <!-- ================= MODE B: WEEKLY ATTENDANCE ================= -->
        <?php elseif ($reportType === 'weekly'): 
            $days = $weeklyReport['days'] ?? [];
            $data = $weeklyReport['data'] ?? [];
        ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 text-center">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="text-start ps-3" style="min-width: 200px;">Employee</th>
                            <th>Site</th>
                            <?php foreach ($days as $dStr): ?>
                                <th style="min-width: 80px;">
                                    <div><?= date('d M', strtotime($dStr)) ?></div>
                                    <small class="text-muted fw-normal"><?= date('D', strtotime($dStr)) ?></small>
                                </th>
                            <?php endforeach; ?>
                            <th class="bg-light">Present</th>
                            <th class="bg-light">Leave</th>
                            <th class="bg-light text-end pe-3">Total Hours</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php if (empty($data)): ?>
                            <tr>
                                <td colspan="<?= count($days) + 5 ?>" class="text-center py-5 text-muted">
                                    No records found for the selected week.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data as $r): 
                                $emp = $r['employee'];
                            ?>
                                <tr>
                                    <td class="text-start ps-3">
                                        <a href="<?= base_url('reports?report_type=employee&employee_id=' . $emp['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                            <?= e($emp['name']) ?>
                                        </a>
                                        <div class="text-muted font-monospace" style="font-size: 0.7rem;"><?= e($emp['employee_code']) ?> &bull; <?= e($emp['department_name'] ?? 'General') ?></div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= e($emp['site'] ?? 'Main Site') ?></span></td>
                                    <?php foreach ($days as $dStr): 
                                        $st = $r['daily_stats'][$dStr] ?? ['status' => '-', 'hours' => 0];
                                    ?>
                                        <td>
                                            <?php if ($st['status'] === 'P'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle p-1 font-monospace" title="<?= $st['hours'] ?> hours">
                                                    P (<?= $st['hours'] ?>h)
                                                </span>
                                            <?php elseif ($st['status'] === 'L'): ?>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle p-1" title="<?= e($st['leave_type'] ?? 'Leave') ?>">
                                                    <?= e($st['leave_code'] ?? 'L') ?>
                                                </span>
                                            <?php elseif ($st['status'] === 'W'): ?>
                                                <span class="badge bg-light text-muted border p-1">OFF</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border p-1">A</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="fw-bold text-success"><?= $r['present_days'] ?>d</td>
                                    <td class="fw-bold text-info"><?= $r['leave_days'] ?>d</td>
                                    <td class="text-end pe-3 fw-bold text-primary font-monospace"><?= $r['total_hours'] ?>h</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <!-- ================= MODE C: MONTHLY TIMESHEET ================= -->
        <?php elseif ($reportType === 'monthly'): 
            $daysInMonth = $monthlyReport['days_in_month'] ?? 30;
            $data = $monthlyReport['data'] ?? [];
        ?>
            <!-- Executive Metrics Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 bg-primary-subtle text-primary rounded-4 p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Active Staff</h6>
                                <h3 class="fw-bold mb-0"><?= $monthlySummary['total_employees'] ?? count($data) ?></h3>
                            </div>
                            <i class="fa-solid fa-users fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-success-subtle text-success rounded-4 p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small fw-bold text-uppercase mb-1">Present Work Days</h6>
                                <h3 class="fw-bold mb-0"><?= $monthlySummary['total_present_days'] ?? 0 ?></h3>
                            </div>
                            <i class="fa-solid fa-user-check fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-info-subtle text-info rounded-4 p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small fw-bold text-uppercase mb-1">Productive Hours</h6>
                                <h3 class="fw-bold mb-0"><?= $monthlySummary['total_hours'] ?? 0 ?>h</h3>
                            </div>
                            <i class="fa-solid fa-clock-rotate-left fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-warning-subtle text-warning-emphasis rounded-4 p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small fw-bold text-uppercase mb-1">Attendance Rate</h6>
                                <h3 class="fw-bold mb-0"><?= $monthlySummary['attendance_rate'] ?? 0 ?>%</h3>
                            </div>
                            <i class="fa-solid fa-percent fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timesheet Grid -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 text-center font-monospace" style="font-size: 0.75rem;">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="text-start ps-3" style="min-width: 180px;">Employee</th>
                            <th>Site</th>
                            <?php for ($d = 1; $d <= $daysInMonth; $d++): 
                                $cDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                $isHol = isset($holidaysMap[$cDate]);
                            ?>
                                <th style="min-width: 32px;" class="<?= $isHol ? 'bg-warning-subtle text-dark' : '' ?>" title="<?= $isHol ? e($holidaysMap[$cDate]) : '' ?>">
                                    <?= $d ?>
                                </th>
                            <?php endfor; ?>
                            <th class="bg-light">P</th>
                            <th class="bg-light">L</th>
                            <th class="bg-light">A</th>
                            <th class="bg-light">Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr>
                                <td colspan="<?= $daysInMonth + 6 ?>" class="text-center py-5 text-muted font-sans-serif">
                                    No records recorded for <?= date('F Y', mktime(0,0,0,$month, 1, $year)) ?>.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data as $r): 
                                $emp = $r['employee'];
                                $pDays = (int) $r['present_days'];
                                $lDays = (int) ($r['leave_days'] ?? 0);
                                $aDays = max(0, $daysInMonth - $pDays - $lDays);
                            ?>
                                <tr>
                                    <td class="text-start ps-3 font-sans-serif">
                                        <a href="<?= base_url('reports?report_type=employee&employee_id=' . $emp['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                            <?= e($emp['name']) ?>
                                        </a>
                                        <div class="text-muted small" style="font-size: 0.68rem;"><?= e($emp['employee_code']) ?></div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= e($emp['site'] ?? 'Main') ?></span></td>
                                    <?php for ($d = 1; $d <= $daysInMonth; $d++): 
                                        $cDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                        $st = $r['daily_stats'][$d] ?? ['status' => '-', 'hours' => 0];
                                    ?>
                                        <td>
                                            <?php if ($st['status'] === 'P'): ?>
                                                <span class="badge bg-success text-white p-1" title="<?= $st['hours'] ?> hrs worked">P</span>
                                            <?php elseif ($st['status'] === 'L'): ?>
                                                <span class="badge bg-info text-white p-1" title="<?= e($st['leave_type'] ?? 'Leave') ?>"><?= e($st['leave_code'] ?? 'L') ?></span>
                                            <?php elseif (isset($holidaysMap[$cDate])): ?>
                                                <span class="badge bg-warning text-dark p-1" title="<?= e($holidaysMap[$cDate]) ?>">H</span>
                                            <?php elseif ($st['status'] === 'W'): ?>
                                                <span class="badge bg-light text-muted border p-1">W</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary p-1">A</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                    <td class="fw-bold text-success font-sans-serif"><?= $pDays ?></td>
                                    <td class="fw-bold text-info font-sans-serif"><?= $lDays ?></td>
                                    <td class="fw-bold text-danger font-sans-serif"><?= $aDays ?></td>
                                    <td class="fw-bold text-primary font-sans-serif"><?= $r['total_hours'] ?>h</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <!-- ================= MODE D: EMPLOYEE-WISE ATTENDANCE ================= -->
        <?php elseif ($reportType === 'employee'): 
            $emp = $employeeReport['employee'] ?? null;
            $records = $employeeReport['records'] ?? [];
            $summary = $employeeReport['summary'] ?? [];
        ?>
            <?php if (!$emp): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-user-xmark fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                    Please select an employee above to inspect their attendance history dossier.
                </div>
            <?php else: ?>
                <!-- Employee Profile Card Header -->
                <div class="card border-0 bg-light rounded-4 p-4 mb-4">
                    <div class="row align-items-center g-3">
                        <div class="col-auto">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 64px; height: 64px; font-size: 1.5rem;">
                                <?= strtoupper(substr($emp['name'], 0, 2)) ?>
                            </div>
                        </div>
                        <div class="col">
                            <h4 class="fw-bold text-dark mb-1"><?= e($emp['name']) ?></h4>
                            <div class="d-flex flex-wrap gap-3 text-muted small">
                                <span><i class="fa-solid fa-id-badge text-primary me-1"></i><?= e($emp['employee_code']) ?></span>
                                <span><i class="fa-solid fa-building text-secondary me-1"></i><?= e($emp['department'] ?? 'General') ?></span>
                                <span><i class="fa-solid fa-briefcase text-secondary me-1"></i><?= e($emp['designation'] ?? 'Staff') ?></span>
                                <span><i class="fa-solid fa-location-dot text-danger me-1"></i><?= e($emp['site'] ?? 'Main Site') ?></span>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <div class="d-flex gap-3 text-center">
                                <div class="bg-white p-3 rounded-4 border shadow-sm">
                                    <div class="small text-muted fw-semibold">Present Days</div>
                                    <div class="h4 fw-bold text-success mb-0"><?= $summary['present_days'] ?? 0 ?></div>
                                </div>
                                <div class="bg-white p-3 rounded-4 border shadow-sm">
                                    <div class="small text-muted fw-semibold">Total Hours</div>
                                    <div class="h4 fw-bold text-primary mb-0"><?= $summary['total_hours'] ?? 0 ?>h</div>
                                </div>
                                <div class="bg-white p-3 rounded-4 border shadow-sm">
                                    <div class="small text-muted fw-semibold">Attendance</div>
                                    <div class="h4 fw-bold text-warning-emphasis mb-0"><?= $summary['attendance_rate'] ?? 0 ?>%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chronological Punch & Leave Dossier Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 border">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-3">Date & Day</th>
                                <th>Status</th>
                                <th>First Check IN</th>
                                <th>Last Check OUT</th>
                                <th>Punches</th>
                                <th>Work Duration</th>
                                <th>Hours</th>
                                <th class="pe-3">Notes / OD Destination</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No attendance logs found for this period.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($records as $rec): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark"><?= date('d M Y', strtotime($rec['date'])) ?></div>
                                            <div class="text-muted small"><?= $rec['day_name'] ?></div>
                                        </td>
                                        <td>
                                            <?php if ($rec['status'] === 'OD'): 
                                                $lv = $rec['leave_info'] ?? [];
                                            ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 fw-bold">
                                                    <i class="fa-solid fa-briefcase me-1"></i>OD (Outdoor Duty)
                                                </span>
                                            <?php elseif ($rec['status'] === 'P'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                                    <i class="fa-solid fa-check me-1"></i>Present
                                                </span>
                                            <?php elseif ($rec['status'] === 'L'): 
                                                $lv = $rec['leave_info'] ?? [];
                                            ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fw-bold">
                                                    <i class="fa-solid fa-plane-departure me-1"></i><?= e($lv['leave_type'] ?? 'Leave') ?>
                                                </span>
                                            <?php elseif ($rec['status'] === 'W'): ?>
                                                <span class="badge bg-light text-muted border rounded-pill px-2 py-1">Weekend OFF</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1">Absent</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($rec['first_in']): ?>
                                                <span class="badge bg-light text-dark border font-monospace">
                                                    <i class="fa-solid fa-clock text-success me-1"></i><?= $rec['first_in'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($rec['last_out']): ?>
                                                <span class="badge bg-light text-dark border font-monospace">
                                                    <i class="fa-solid fa-clock text-danger me-1"></i><?= $rec['last_out'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= $rec['punch_count'] ?></span></td>
                                        <td><span class="fw-semibold text-dark"><?= $rec['formatted_duration'] ?></span></td>
                                        <td><span class="fw-bold text-primary"><?= $rec['hours'] ?>h</span></td>
                                        <td class="pe-3">
                                            <?php 
                                            $lvInfo = $rec['leave_info'] ?? [];
                                            ?>
                                            <?php if (!empty($lvInfo['target_site'])): ?>
                                                <div class="d-inline-block">
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                                        <i class="fa-solid fa-location-dot text-danger me-1"></i>Target: <?= e($lvInfo['target_site']) ?>
                                                    </span>
                                                    <?php if (!empty($lvInfo['origin_site'])): ?>
                                                        <span class="small text-muted ms-1">(From: <?= e($lvInfo['origin_site']) ?>)</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($lvInfo['reason'])): ?>
                                                <div class="small text-dark mt-1"><i class="fa-regular fa-comment-dots text-muted me-1"></i><?= e($lvInfo['reason']) ?></div>
                                            <?php endif; ?>

                                            <?php if (!empty($lvInfo['attachment'])): ?>
                                                <div class="mt-1">
                                                    <a href="<?= uploaded_url($lvInfo['attachment']) ?>" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-file-pdf me-1"></i>PDF Sheet
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (empty($lvInfo['target_site']) && empty($lvInfo['reason']) && empty($lvInfo['attachment'])): ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>
