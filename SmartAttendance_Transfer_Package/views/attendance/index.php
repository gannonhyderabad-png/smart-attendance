<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
    <div class="card-body p-4">

        <!-- Header -->
        <div class="row g-3 align-items-center mb-4">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h5 class="fw-bold text-dark mb-0">Attendance Log Records</h5>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small fw-semibold" id="liveSyncStatus" style="font-size: 0.72rem;">
                        <i class="fa-solid fa-circle text-success fa-beat-fade me-1"></i> Live Auto-Sync (Realtime)
                    </span>
                </div>
                <p class="text-muted small mb-0">Total of <?= number_format($totalRecords) ?> employee attendance sessions recorded (IN & OUT paired side-by-side)</p>
            </div>
            <div class="col-md-6 text-md-end d-flex gap-2 justify-content-md-end flex-wrap">
                <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#manualEntryModal">
                    <i class="fa-solid fa-user-clock me-1"></i> + Manual Entry
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

        <!-- Filter Form -->
        <form method="GET" action="<?= base_url('attendance') ?>" class="row g-2 mb-4 p-3 bg-light rounded-4 border">
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">Staff Member</label>
                <select name="employee_id" class="form-select form-select-sm bg-white">
                    <option value="">All Employees</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= ($filters['employee_id'] == $emp['id']) ? 'selected' : '' ?>>
                            <?= e($emp['name']) ?> (<?= e($emp['employee_code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">Department</label>
                <select name="department_id" class="form-select form-select-sm bg-white">
                    <option value="">All Departments</option>
                    <?php foreach ($departmentList as $deptName): ?>
                        <option value="<?= e($deptName) ?>" <?= (($filters['department_id'] ?? '') === $deptName) ? 'selected' : '' ?>>
                            <?= e($deptName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">Project</label>
                <input type="text" name="project" class="form-control form-control-sm bg-white" placeholder="Filter by Project..." value="<?= e($filters['project'] ?? '') ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">Work Site</label>
                <select name="site" class="form-select form-select-sm bg-white">
                    <option value="">All Sites</option>
                    <?php foreach ($siteList as $sName): ?>
                        <option value="<?= e($sName) ?>" <?= (($filters['site'] ?? '') === $sName) ? 'selected' : '' ?>>
                            <?= e($sName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">Status</label>
                <select name="punch_type" class="form-select form-select-sm bg-white">
                    <option value="">All Statuses</option>
                    <option value="IN" <?= ($filters['punch_type'] === 'IN') ? 'selected' : '' ?>>Working Now (IN)</option>
                    <option value="OUT" <?= ($filters['punch_type'] === 'OUT') ? 'selected' : '' ?>>Completed / Shift Over (OUT)</option>
                    <option value="NO_OUT" <?= ($filters['punch_type'] === 'NO_OUT') ? 'selected' : '' ?>>No OUT Punch (Auto-Closed)</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end gap-1">
                <button type="submit" class="btn btn-dark btn-sm w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                <a href="<?= base_url('attendance') ?>" class="btn btn-light btn-sm border" title="Reset Filters"><i class="fa-solid fa-rotate-right"></i></a>
            </div>
        </form>

        <div id="attendanceLiveContent">
        <!-- Filter Count Summary Indicator -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 bg-light rounded-3 px-3 py-2 border">
            <div class="small text-dark d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-bold"><i class="fa-solid fa-list-ol text-primary me-1"></i> Total Sessions: <span class="badge bg-primary text-white rounded-pill px-2"><?= $totalRecords ?></span></span>
                <?php if (!empty($filters['site'])): ?>
                    <span class="badge bg-info-subtle text-dark border border-info px-2 py-1"><i class="fa-solid fa-location-dot text-primary me-1"></i> Site: <strong><?= e($filters['site']) ?></strong></span>
                <?php endif; ?>
                <?php if (!empty($filters['department_id'])): ?>
                    <span class="badge bg-secondary-subtle text-dark border border-secondary px-2 py-1"><i class="fa-solid fa-building text-secondary me-1"></i> Department Filtered</span>
                <?php endif; ?>
                <?php if (!empty($filters['search'])): ?>
                    <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1"><i class="fa-solid fa-magnifying-glass me-1"></i> Search: <strong><?= e($filters['search']) ?></strong></span>
                <?php endif; ?>
            </div>
            <small class="text-muted font-monospace">Showing <?= count($records) ?> logs on this page (updates with site filter)</small>
        </div>

        <!-- Attendance Records Table (Paired IN & OUT in one row) -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3 text-center" style="width: 50px;">S.No</th>
                        <th>Employee</th>
                        <th>Project</th>
                        <th>Site</th>
                        <th>Date</th>
                        <th>Punch IN</th>
                        <th>Punch OUT</th>
                        <th>Total Worked</th>
                        <th>Status</th>
                        <th class="pe-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fa-regular fa-calendar-xmark fs-3 d-block mb-2 text-secondary"></i>
                                No attendance sessions match the selected criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $pageVal = max(1, (int)($page ?? 1));
                        $serialNum = ($pageVal - 1) * 50 + 1;
                        foreach ($records as $row): 
                        ?>
                            <tr>
                                <td class="ps-3 text-center fw-bold text-muted" style="width: 50px;">
                                    <span class="badge bg-light text-secondary border font-monospace"><?= $serialNum++ ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0)" onclick="openEmployeeAttendanceSummary(<?= $row['employee_id'] ?>)" class="text-decoration-none">
                                            <?php if (!empty($row['employee_photo'])): ?>
                                                <img src="<?= uploaded_url($row['employee_photo']) ?>" class="rounded-circle border object-fit-cover me-2 shadow-sm" style="width: 36px; height: 36px;" alt="<?= e($row['employee_name']) ?>">
                                            <?php else: ?>
                                                <div class="avatar-sm rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; font-size: 0.85rem;" title="View <?= e($row['employee_name']) ?> Log Sheet">
                                                    <?= strtoupper(substr($row['employee_name'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                        <div>
                                            <a href="javascript:void(0)" onclick="openEmployeeAttendanceSummary(<?= $row['employee_id'] ?>)" class="fw-bold text-dark text-decoration-none" title="Click to view Attendance Log Sheet for <?= e($row['employee_name']) ?>">
                                                <?= e($row['employee_name']) ?>
                                            </a>
                                            <div>
                                                <a href="javascript:void(0)" onclick="openEmployeeAttendanceSummary(<?= $row['employee_id'] ?>)" class="badge bg-light text-primary border font-monospace text-decoration-none" style="font-size: 0.7rem;" title="View Attendance Log Sheet">
                                                    <?= e($row['employee_code']) ?>
                                                </a>
                                                <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle px-1 ms-1" style="font-size: 0.68rem;"><i class="fa-solid fa-building me-1 text-secondary"></i><?= e($row['department_name'] ?? 'General') ?></span>
                                                <a href="javascript:void(0)" onclick="openEmployeeAttendanceSummary(<?= $row['employee_id'] ?>)" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill text-decoration-none ms-1 px-2 py-0" style="font-size: 0.65rem;" title="View Attendance Log Sheet">
                                                    <i class="fa-solid fa-file-waveform me-1"></i>Log Sheet
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($row['project'])): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                            <i class="fa-solid fa-diagram-project me-1"></i><?= e($row['project']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['site'])): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <i class="fa-solid fa-location-dot me-1"></i><?= e($row['site']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark"><?= date('d M Y', strtotime($row['punch_date'])) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($row['in_time'])): ?>
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace">
                                                <i class="fa-solid fa-arrow-right-to-bracket me-1"></i><?= date('h:i:s A', strtotime($row['in_time'])) ?>
                                            </span>
                                            <?php if (!empty($row['in_photo'])): ?>
                                                <a href="javascript:void(0)" onclick="viewPunchPhoto('<?= uploaded_url($row['in_photo']) ?>', '<?= e($row['employee_name']) ?> - Punch IN Selfie', '<?= date('d M Y, h:i:s A', strtotime($row['in_time'])) ?>')" class="btn btn-sm btn-light border p-0 rounded-circle shadow-sm" title="View IN Selfie">
                                                    <img src="<?= uploaded_url($row['in_photo']) ?>" class="rounded-circle object-fit-cover border" style="width: 24px; height: 24px;" alt="IN Selfie">
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (isset($row['in_distance']) && $row['in_distance'] !== '' && $row['in_distance'] !== null): ?>
                                            <div class="small">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.65rem;" title="GPS Verified within <?= round((float)$row['in_distance']) ?>m of site">
                                                    <i class="fa-solid fa-location-dot me-1"></i><?= round((float)$row['in_distance']) ?>m away
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($row['in_ip'])): ?>
                                            <div class="text-muted small text-truncate" style="max-width: 140px;" title="<?= e($row['in_device'] ?? '') ?>">
                                                <i class="fa-solid fa-network-wired text-secondary me-1"></i><?= e($row['in_ip']) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['out_time'])): ?>
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 font-monospace">
                                                <i class="fa-solid fa-arrow-right-from-bracket me-1"></i><?= date('h:i:s A', strtotime($row['out_time'])) ?>
                                            </span>
                                            <?php if (!empty($row['out_photo'])): ?>
                                                <a href="javascript:void(0)" onclick="viewPunchPhoto('<?= uploaded_url($row['out_photo']) ?>', '<?= e($row['employee_name']) ?> - Punch OUT Selfie', '<?= date('d M Y, h:i:s A', strtotime($row['out_time'])) ?>')" class="btn btn-sm btn-light border p-0 rounded-circle shadow-sm" title="View OUT Selfie">
                                                    <img src="<?= uploaded_url($row['out_photo']) ?>" class="rounded-circle object-fit-cover border" style="width: 24px; height: 24px;" alt="OUT Selfie">
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (isset($row['out_distance']) && $row['out_distance'] !== '' && $row['out_distance'] !== null): ?>
                                            <div class="small">
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.65rem;" title="GPS Verified within <?= round((float)$row['out_distance']) ?>m of site">
                                                    <i class="fa-solid fa-location-dot me-1"></i><?= round((float)$row['out_distance']) ?>m away
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($row['out_ip'])): ?>
                                            <div class="text-muted small text-truncate" style="max-width: 140px;" title="<?= e($row['out_device'] ?? '') ?>">
                                                <i class="fa-solid fa-network-wired text-secondary me-1"></i><?= e($row['out_ip']) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($row['status'] === 'IN'): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <i class="fa-solid fa-clock me-1"></i> Working Now
                                        </span>
                                    <?php elseif ($row['status'] === 'NO_OUT' || !empty($row['auto_closed'])): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" title="Employee did not punch OUT. Auto-closed based on site shift hours.">
                                            <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> No OUT Punch
                                        </span>
                                        <div class="small text-muted font-monospace" style="font-size: 0.68rem;" title="Auto-closed at scheduled shift end">
                                            <i class="fa-regular fa-clock me-1"></i>Auto-closed: <?= date('h:i A', strtotime($row['shift_end'] ?? '18:00:00')) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-bold font-monospace <?= $row['duration_seconds'] > 0 ? 'text-primary' : 'text-muted' ?>">
                                        <?= $row['formatted_duration'] ?>
                                    </span>
                                    <?php if ($row['status'] === 'NO_OUT' || !empty($row['auto_closed'])): ?>
                                        <div class="small text-muted" style="font-size: 0.65rem;" title="Auto-calculated from Punch IN to site shift end time">
                                            <i class="fa-solid fa-calculator me-1"></i>Shift Time
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'IN'): ?>
                                        <span class="badge bg-success px-2 py-1">
                                            <i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i> Working Now
                                        </span>
                                    <?php elseif ($row['status'] === 'NO_OUT' || !empty($row['auto_closed'])): ?>
                                        <span class="badge bg-warning text-dark border border-warning px-2 py-1" title="No Out Punch - Auto closed at shift end">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> No OUT Punch
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                            <i class="fa-solid fa-check me-1"></i> Completed
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3 text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2 py-1" onclick="openEmployeeAttendanceSummary(<?= $row['employee_id'] ?>)" title="View Attendance Log Sheet & Timesheet Summary">
                                            <i class="fa-solid fa-file-waveform me-1"></i> Log Sheet
                                        </button>
                                        <a href="<?= punch_url($row['employee_code']) ?>" target="_blank" class="btn btn-sm btn-light border rounded-pill px-2 py-1 text-primary" title="Open Mobile Punch URL">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <span class="text-muted small">Showing page <?= $page ?> of <?= $totalPages ?></span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($p = 1; $p <= $totalPages; $p++): 
                            $pageFilters = $filters;
                            $pageFilters['page'] = $p;
                            $pageUrl = base_url('attendance?' . http_build_query($pageFilters));
                        ?>
                            <li class="page-item <?= ($page == $p) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $pageUrl ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
        </div> <!-- /#attendanceLiveContent -->

    </div>
</div>

<!-- Manual Attendance & Leave Entry Modal -->
<div class="modal fade" id="manualEntryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Manual Entry Portal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('attendance/manual') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body py-3">
                    
                    <!-- Entry Type Selector Tabs -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-2">Select Entry Type</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="entry_type" id="typePunch" value="punch" checked onchange="toggleManualFormMode('punch')">
                            <label class="btn btn-outline-primary btn-sm py-2" for="typePunch">
                                <i class="fa-solid fa-clock me-1"></i> Attendance Punch
                            </label>

                            <input type="radio" class="btn-check" name="entry_type" id="typeLeave" value="leave" onchange="toggleManualFormMode('leave')">
                            <label class="btn btn-outline-warning text-dark btn-sm py-2" for="typeLeave">
                                <i class="fa-solid fa-plane-departure me-1"></i> Employee Leave
                            </label>

                            <input type="radio" class="btn-check" name="entry_type" id="typeHoliday" value="holiday" onchange="toggleManualFormMode('holiday')">
                            <label class="btn btn-outline-info text-dark btn-sm py-2" for="typeHoliday">
                                <i class="fa-solid fa-calendar-star me-1"></i> Public Holiday
                            </label>
                        </div>
                    </div>

                    <!-- Employee Select (for Punch & Leave) -->
                    <div class="mb-3" id="empSelectGroup">
                        <label class="form-label small fw-semibold text-muted">Employee (Name or Code) <span class="text-danger">*</span></label>
                        <input type="text" name="employee_id" id="manualEmpInput" list="manualEmpDatalist" class="form-control bg-light" placeholder="Type Employee Name or Code (e.g. EMP001)..." required autocomplete="off" oninput="handleManualEmpInput(this.value)">
                        <datalist id="manualEmpDatalist">
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= e($emp['employee_code']) ?> — <?= e($emp['name']) ?>"
                                        data-id="<?= $emp['id'] ?>"
                                        data-project="<?= e($emp['project'] ?? '') ?>" 
                                        data-site="<?= e($emp['site'] ?? '') ?>">
                                    <?= e($emp['name']) ?> (<?= e($emp['department'] ?? 'General') ?>)
                                </option>
                            <?php endforeach; ?>
                        </datalist>
                        <small class="text-muted" style="font-size: 0.72rem;">Type employee code or name to search.</small>
                    </div>

                    <!-- LEAVE TYPE SELECT (Visible in Leave Mode) -->
                    <div class="mb-3 d-none" id="leaveTypeGroup">
                        <label class="form-label small fw-semibold text-muted">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type" id="manualLeaveType" class="form-select bg-light" onchange="handleManualLeaveTypeChange(this.value)">
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

                    <!-- ON DUTY (OD) TARGET SITE INPUT (Visible when Leave Type is OD) -->
                    <div class="p-3 bg-light rounded-4 border border-primary-subtle mb-3 d-none" id="manualOdSiteGroup">
                        <label class="form-label small fw-semibold text-primary mb-1"><i class="fa-solid fa-location-dot text-danger me-1"></i>Where to Go / Target Site (OD Visit) <span class="text-danger">*</span></label>
                        <input type="text" name="target_site" id="manualOdTargetSite" class="form-control bg-white" placeholder="e.g. Hyderabad Metro Site, Client Office, Site-B">
                        <div class="row g-2 mt-1">
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-muted mb-1">From / Base Site</label>
                                <input type="text" name="origin_site" id="manualOdOriginSite" class="form-control bg-white" placeholder="e.g. Head Office / Primary Site">
                            </div>
                        </div>
                    </div>

                    <!-- ATTACH PDF / DOCUMENT (Visible in Leave Mode) -->
                    <div class="mb-3 d-none" id="manualLeaveAttachmentGroup">
                        <label class="form-label small fw-semibold text-muted">
                            <i class="fa-solid fa-paperclip text-primary me-1"></i>Attach PDF / Document Sheet <small class="text-muted">(Optional)</small>
                        </label>
                        <input type="file" name="attachment" class="form-control bg-light" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.xlsx,.csv">
                    </div>

                    <!-- PUBLIC HOLIDAY TITLE (Visible in Holiday Mode) -->
                    <div class="mb-3 d-none" id="holidayTitleGroup">
                        <label class="form-label small fw-semibold text-muted">Holiday Title <span class="text-danger">*</span></label>
                        <input type="text" name="holiday_title" id="manualHolidayTitle" class="form-control bg-light" placeholder="e.g. Independence Day, Diwali, Eid">
                    </div>

                    <!-- DATE FIELDS -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-6" id="fromDateCol">
                            <label class="form-label small fw-semibold text-muted" id="dateLabel">Date <span class="text-danger">*</span></label>
                            <input type="date" name="punch_date" id="manualPunchDate" class="form-control bg-light" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6 d-none" id="toDateCol">
                            <label class="form-label small fw-semibold text-muted">To Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="manualEndDate" class="form-control bg-light" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <!-- PUNCH IN & OUT TIMES (Visible only in Punch Mode) -->
                    <div class="row g-2 mb-3" id="punchTimesGroup">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Punch IN Time <span class="text-danger">*</span></label>
                            <input type="time" name="in_time" class="form-control bg-light" value="09:00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Punch OUT Time <small class="text-muted">(Optional)</small></label>
                            <input type="time" name="out_time" class="form-control bg-light" value="18:00">
                        </div>
                    </div>

                    <!-- PROJECT & SITE (Visible only in Punch Mode) -->
                    <div class="row g-2 mb-3" id="punchLocationGroup">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Project Name</label>
                            <input type="text" name="project" id="manualProjectInput" class="form-control bg-light" placeholder="e.g. ERP Project">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Work Site / Location</label>
                            <input type="text" name="site" id="manualSiteInput" class="form-control bg-light" placeholder="e.g. Hyderabad Office">
                        </div>
                    </div>

                    <!-- REASON / NOTES -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted" id="reasonLabel">Reason / Notes</label>
                        <input type="text" name="notes" class="form-control bg-light" placeholder="e.g. Approved leave / On-duty site visit / device glitch">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="manualSubmitBtn">
                        <i class="fa-solid fa-check me-1"></i> Save Manual Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleManualFormMode(mode) {
    const empGroup = document.getElementById('empSelectGroup');
    const leaveGroup = document.getElementById('leaveTypeGroup');
    const holGroup = document.getElementById('holidayTitleGroup');
    const punchTimes = document.getElementById('punchTimesGroup');
    const punchLoc = document.getElementById('punchLocationGroup');
    const toDateCol = document.getElementById('toDateCol');
    const fromDateCol = document.getElementById('fromDateCol');
    const dateLabel = document.getElementById('dateLabel');
    const submitBtn = document.getElementById('manualSubmitBtn');
    const manualEmpInput = document.getElementById('manualEmpInput');
    const odSiteGroup = document.getElementById('manualOdSiteGroup');
    const attGroup = document.getElementById('manualLeaveAttachmentGroup');

    if (mode === 'punch') {
        empGroup.classList.remove('d-none');
        leaveGroup.classList.add('d-none');
        holGroup.classList.add('d-none');
        if (attGroup) attGroup.classList.add('d-none');
        if (odSiteGroup) odSiteGroup.classList.add('d-none');
        punchTimes.classList.remove('d-none');
        punchLoc.classList.remove('d-none');
        toDateCol.classList.add('d-none');
        fromDateCol.className = 'col-md-12';
        dateLabel.innerHTML = 'Attendance Date <span class="text-danger">*</span>';
        submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save Punch Entry';
        if (manualEmpInput) manualEmpInput.required = true;
    } else if (mode === 'leave') {
        empGroup.classList.remove('d-none');
        leaveGroup.classList.remove('d-none');
        holGroup.classList.add('d-none');
        if (attGroup) attGroup.classList.remove('d-none');
        punchTimes.classList.add('d-none');
        punchLoc.classList.add('d-none');
        toDateCol.classList.remove('d-none');
        fromDateCol.className = 'col-md-6';
        dateLabel.innerHTML = 'From Date <span class="text-danger">*</span>';
        submitBtn.innerHTML = '<i class="fa-solid fa-plane-departure me-1"></i> Save Leave Entry';
        if (manualEmpInput) manualEmpInput.required = true;
        const curLeaveType = document.getElementById('manualLeaveType')?.value || '';
        handleManualLeaveTypeChange(curLeaveType);
    } else if (mode === 'holiday') {
        empGroup.classList.add('d-none');
        leaveGroup.classList.add('d-none');
        holGroup.classList.remove('d-none');
        if (attGroup) attGroup.classList.add('d-none');
        if (odSiteGroup) odSiteGroup.classList.add('d-none');
        punchTimes.classList.add('d-none');
        punchLoc.classList.add('d-none');
        toDateCol.classList.add('d-none');
        fromDateCol.className = 'col-md-12';
        dateLabel.innerHTML = 'Holiday Date <span class="text-danger">*</span>';
        submitBtn.innerHTML = '<i class="fa-solid fa-calendar-star me-1"></i> Save Public Holiday';
        if (manualEmpInput) manualEmpInput.required = false;
    }
}

function handleManualLeaveTypeChange(val) {
    const odGroup = document.getElementById('manualOdSiteGroup');
    const targetInput = document.getElementById('manualOdTargetSite');
    if (!odGroup) return;
    const isOd = (val === 'On Duty' || val.includes('Duty') || val.includes('OD') || val.includes('Official') || val.includes('Outdoor'));
    if (isOd) {
        odGroup.classList.remove('d-none');
        if (targetInput) targetInput.required = true;
    } else {
        odGroup.classList.add('d-none');
        if (targetInput) {
            targetInput.required = false;
            targetInput.value = '';
        }
    }
}

function handleManualEmpInput(val) {
    const list = document.getElementById('manualEmpDatalist');
    if (!list) return;
    for (let opt of list.options) {
        if (opt.value === val) {
            const proj = opt.getAttribute('data-project') || '';
            const site = opt.getAttribute('data-site') || '';
            if (proj) document.getElementById('manualProjectInput').value = proj;
            if (site) document.getElementById('manualSiteInput').value = site;
            break;
        }
    }
}
</script>

<!-- Punch Selfie Photo Modal -->
<div class="modal fade" id="punchPhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content rounded-4 border-0 shadow-lg p-3 text-center">
            <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                <div class="text-start">
                    <h6 class="fw-bold mb-0 text-dark" id="modalPunchPhotoTitle">Punch Photo</h6>
                    <small class="text-muted" id="modalPunchPhotoTime"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="p-2 bg-dark rounded-3 overflow-hidden shadow-inner mb-2">
                <img id="modalPunchPhotoImg" src="" class="img-fluid rounded-3 object-fit-contain" style="max-height: 380px; width: 100%;" alt="Punch Snapshot">
            </div>
            <button type="button" class="btn btn-light border rounded-pill w-100 py-2" data-bs-dismiss="modal">Close Preview</button>
        </div>
    </div>
</div>

<script>
    let isModalOpen = false;
    const photoModalEl = document.getElementById('punchPhotoModal');
    if (photoModalEl) {
        photoModalEl.addEventListener('show.bs.modal', () => { isModalOpen = true; });
        photoModalEl.addEventListener('hidden.bs.modal', () => { isModalOpen = false; });
    }

    const attSummaryModalEl = document.getElementById('employeeAttendanceSummaryModal');
    if (attSummaryModalEl) {
        attSummaryModalEl.addEventListener('show.bs.modal', () => { isModalOpen = true; });
        attSummaryModalEl.addEventListener('hidden.bs.modal', () => { isModalOpen = false; });
    }

    function viewPunchPhoto(url, title, time) {
        if (!url) return;
        document.getElementById('modalPunchPhotoImg').src = url;
        document.getElementById('modalPunchPhotoTitle').textContent = title || 'Punch Photo';
        document.getElementById('modalPunchPhotoTime').textContent = time || '';
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('punchPhotoModal'));
        modal.show();
    }

    // Real-time background auto-sync without full page reload
    let isSyncing = false;
    async function syncAttendanceLogs() {
        if (isSyncing || isModalOpen) return;
        
        // Don't auto-refresh while user is typing in filter inputs
        const activeEl = document.activeElement;
        if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'SELECT')) {
            return;
        }

        try {
            isSyncing = true;
            const res = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const newTable = doc.getElementById('attendanceLiveContent');
                const currentTable = document.getElementById('attendanceLiveContent');
                
                if (newTable && currentTable && newTable.innerHTML.trim() !== currentTable.innerHTML.trim()) {
                    currentTable.innerHTML = newTable.innerHTML;
                    
                    // Flash live badge briefly to give visual feedback
                    const statusBadge = document.getElementById('liveSyncStatus');
                    if (statusBadge) {
                        statusBadge.classList.add('bg-success', 'text-white');
                        setTimeout(() => {
                            statusBadge.classList.remove('bg-success', 'text-white');
                        }, 1200);
                    }
                }
            }
        } catch (e) {
            console.debug('Live sync:', e);
        } finally {
            isSyncing = false;
        }
    }

    // Poll every 3 seconds for instant updates when employee punches IN/OUT
    setInterval(syncAttendanceLogs, 3000);

    let currentSummaryEmpId = null;

    async function openEmployeeAttendanceSummary(empId, month, year) {
        if (!empId) return;
        currentSummaryEmpId = empId;

        const m = month || document.getElementById('attSummaryMonthSelect')?.value || '<?= date('m') ?>';
        const y = year || document.getElementById('attSummaryYearSelect')?.value || '<?= date('Y') ?>';

        const modalEl = document.getElementById('employeeAttendanceSummaryModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        document.getElementById('attSummaryLoadingBox').classList.remove('d-none');
        document.getElementById('attSummaryContentBox').classList.add('d-none');

        try {
            const url = `<?= base_url('attendance/employee-summary') ?>?employee_id=${empId}&month=${m}&year=${y}`;
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();

            if (!data.success) {
                alert(data.message || 'Failed to load attendance summary.');
                modal.hide();
                return;
            }

            const emp = data.employee;
            const stats = data.stats;
            const sessions = data.sessions || [];
            const leaves = data.leaves || [];
            const leaveBal = data.leave_balance || {};

            // Populate Employee Info
            document.getElementById('attSummaryEmpName').textContent = emp.name || '—';
            document.getElementById('attSummaryEmpCode').textContent = emp.employee_code || '—';
            document.getElementById('attSummaryEmpDept').textContent = emp.department_name || emp.department || 'General';
            document.getElementById('attSummaryEmpSite').textContent = emp.site || 'Main Site';
            document.getElementById('attSummaryEmpRole').textContent = emp.designation || emp.role || 'Staff Member';
            document.getElementById('attSummaryPeriodBadge').textContent = stats.month_name;

            // Populate KPIs
            document.getElementById('attSummaryTotalDays').textContent = `${stats.total_sessions} Days`;
            document.getElementById('attSummaryTotalHours').textContent = `${stats.total_hours} hrs`;
            document.getElementById('attSummaryAvgHours').textContent = `${stats.avg_hours_per_day} hrs/day`;
            document.getElementById('attSummaryCompleted').textContent = `${stats.completed}`;
            document.getElementById('attSummaryNoOut').textContent = `${stats.no_out}`;
            
            const totalLeavesTaken = (leaveBal.taken ? leaveBal.taken.total : 0);
            const totalOdVisits = (leaveBal.taken ? leaveBal.taken.OD : 0);
            document.getElementById('attSummaryLeaves').textContent = `${totalLeavesTaken} Leaves • ${totalOdVisits} OD`;

            document.getElementById('attSummaryDossierLink').href = `<?= base_url('reports') ?>?report_type=employee&employee_id=${empId}`;

            // Populate Timesheet Table
            const tbody = document.getElementById('attSummaryTableBody');
            tbody.innerHTML = '';

            if (sessions.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted"><i class="fa-regular fa-calendar-xmark fa-2x mb-2 d-block opacity-50"></i>No attendance sessions clocked in ${stats.month_name}.</td></tr>`;
            } else {
                sessions.forEach((s, idx) => {
                    const tr = document.createElement('tr');

                    // Punch In formatting
                    let inCell = '—';
                    if (s.in_time) {
                        const inTime = formatTimeStr(s.in_time);
                        let selfie = '';
                        if (s.in_photo) {
                            const photoUrl = `<?= base_url('uploads/') ?>${encodeURIComponent(s.in_photo.replace(/^uploads\//, ''))}`;
                            selfie = `<img src="${photoUrl}" class="rounded-circle border object-fit-cover shadow-sm ms-1" style="width: 24px; height: 24px; cursor: pointer;" onclick="viewPunchPhoto('${photoUrl}', '${escapeHtml(emp.name)} - Punch IN Selfie', '${formatDateStr(s.punch_date)}, ${inTime}')" alt="IN Selfie">`;
                        }
                        let dist = '';
                        if (s.in_distance !== null && s.in_distance !== undefined && s.in_distance !== '') {
                            dist = `<div class="text-success small" style="font-size: 0.68rem;"><i class="fa-solid fa-location-dot me-1"></i>${Math.round(parseFloat(s.in_distance))}m away</div>`;
                        }
                        inCell = `<div class="d-flex align-items-center gap-1"><span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">${inTime}</span>${selfie}</div>${dist}`;
                    }

                    // Punch Out formatting
                    let outCell = '—';
                    if (s.out_time) {
                        const outTime = formatTimeStr(s.out_time);
                        let selfie = '';
                        if (s.out_photo) {
                            const photoUrl = `<?= base_url('uploads/') ?>${encodeURIComponent(s.out_photo.replace(/^uploads\//, ''))}`;
                            selfie = `<img src="${photoUrl}" class="rounded-circle border object-fit-cover shadow-sm ms-1" style="width: 24px; height: 24px; cursor: pointer;" onclick="viewPunchPhoto('${photoUrl}', '${escapeHtml(emp.name)} - Punch OUT Selfie', '${formatDateStr(s.punch_date)}, ${outTime}')" alt="OUT Selfie">`;
                        }
                        let dist = '';
                        if (s.out_distance !== null && s.out_distance !== undefined && s.out_distance !== '') {
                            dist = `<div class="text-danger small" style="font-size: 0.68rem;"><i class="fa-solid fa-location-dot me-1"></i>${Math.round(parseFloat(s.out_distance))}m away</div>`;
                        }
                        outCell = `<div class="d-flex align-items-center gap-1"><span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace">${outTime}</span>${selfie}</div>${dist}`;
                    } else if (s.status === 'IN') {
                        outCell = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="fa-solid fa-clock me-1"></i>Working Now</span>';
                    } else if (s.status === 'NO_OUT' || s.auto_closed) {
                        outCell = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>No OUT Punch</span>';
                    }

                    // Status Badge
                    let statusBadge = '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1"><i class="fa-solid fa-check me-1"></i>Completed</span>';
                    if (s.status === 'IN') {
                        statusBadge = '<span class="badge bg-success px-2 py-1"><i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i>Working Now</span>';
                    } else if (s.status === 'NO_OUT' || s.auto_closed) {
                        statusBadge = '<span class="badge bg-warning text-dark border border-warning px-2 py-1">No OUT Punch</span>';
                    }

                    tr.innerHTML = `
                        <td class="ps-3 text-muted">${idx + 1}</td>
                        <td><strong>${formatDateStr(s.punch_date)}</strong></td>
                        <td>${inCell}</td>
                        <td>${outCell}</td>
                        <td><span class="fw-bold font-monospace ${s.duration_seconds > 0 ? 'text-primary' : 'text-muted'}">${s.formatted_duration || '0m'}</span></td>
                        <td><span class="badge bg-light text-dark border">${escapeHtml(s.site || s.project || 'Main Site')}</span></td>
                        <td class="pe-3 text-end">${statusBadge}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            document.getElementById('attSummaryLoadingBox').classList.add('d-none');
            document.getElementById('attSummaryContentBox').classList.remove('d-none');

        } catch (err) {
            console.error(err);
            alert('Error loading employee attendance summary.');
            modal.hide();
        }
    }

    function changeSummaryPeriod() {
        if (currentSummaryEmpId) {
            const m = document.getElementById('attSummaryMonthSelect').value;
            const y = document.getElementById('attSummaryYearSelect').value;
            openEmployeeAttendanceSummary(currentSummaryEmpId, m, y);
        }
    }

    function formatTimeStr(timeStr) {
        if (!timeStr) return '';
        const d = new Date(timeStr);
        if (!isNaN(d.getTime())) {
            return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        }
        return timeStr;
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
        return String(text).replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    function printAttendanceSummarySheet() {
        const content = document.getElementById('printableAttendanceSummaryArea');
        if (!content) return;

        const empName = document.getElementById('attSummaryEmpName')?.textContent || 'Employee';
        const empCode = document.getElementById('attSummaryEmpCode')?.textContent || '';
        const period = document.getElementById('attSummaryPeriodBadge')?.textContent || '';
        const title = `Attendance Log Sheet - ${empName} (${empCode}) - ${period}`;

        const iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);

        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>${title}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
                <style>
                    @page { size: A4 portrait; margin: 10mm 15mm; }
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background: #fff !important; color: #111827; margin: 0; padding: 15px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
                    .table { width: 100% !important; border-collapse: collapse !important; }
                    .table th, .table td { padding: 8px 10px !important; border: 1px solid #e5e7eb !important; font-size: 13px !important; }
                    .bg-light { background-color: #f8fafc !important; }
                    .bg-primary { background-color: #2563eb !important; color: #fff !important; }
                    .bg-success { background-color: #16a34a !important; color: #fff !important; }
                    .badge { border: 1px solid #ddd !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; font-size: 11px !important; }
                    .rounded-4 { border-radius: 10px !important; }
                    .border { border: 1px solid #e5e7eb !important; }
                </style>
            </head>
            <body>
                <div>
                    ${content.innerHTML}
                </div>
            </body>
            </html>
        `);
        doc.close();

        setTimeout(() => {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            setTimeout(() => {
                document.body.removeChild(iframe);
            }, 1000);
        }, 600);
    }
</script>

<!-- Complete Employee Attendance Log Sheet & Summary Modal -->
<div class="modal fade" id="employeeAttendanceSummaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <!-- Modal Actions Bar (hidden when printing) -->
            <div class="modal-header border-bottom bg-light d-print-none py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary text-white p-2 rounded-3"><i class="fa-solid fa-file-waveform fs-6"></i></span>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Employee Attendance Log Sheet & Summary</h5>
                        <small class="text-muted">Detailed punches, selfies, work hours, and monthly attendance statement</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center gap-1 bg-white border rounded-pill px-2 py-1">
                        <select id="attSummaryMonthSelect" class="form-select form-select-sm border-0 bg-transparent fw-semibold" onchange="changeSummaryPeriod()" style="width: auto;">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= sprintf('%02d', $m) ?>" <?= (int)date('m') === $m ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <select id="attSummaryYearSelect" class="form-select form-select-sm border-0 bg-transparent fw-semibold" onchange="changeSummaryPeriod()" style="width: auto;">
                            <?php for ($yr = (int)date('Y'); $yr >= (int)date('Y') - 2; $yr--): ?>
                                <option value="<?= $yr ?>" <?= (int)date('Y') === $yr ? 'selected' : '' ?>><?= $yr ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="button" class="btn btn-outline-dark rounded-pill px-3 shadow-sm" onclick="printAttendanceSummarySheet()">
                        <i class="fa-solid fa-print me-1"></i> Print Log Sheet
                    </button>
                    <button type="button" class="btn btn-light rounded-circle shadow-sm" data-bs-dismiss="modal" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-4 p-md-5" id="printableAttendanceSummaryArea">
                <!-- Loading State -->
                <div id="attSummaryLoadingBox" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <h6 class="text-muted fw-bold">Loading Employee Attendance Log Sheet...</h6>
                </div>

                <!-- Printable Content Box -->
                <div id="attSummaryContentBox" class="d-none">
                    <!-- Company Official Printable Header -->
                    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-4 bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-fingerprint fs-2"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-dark mb-1 font-monospace" style="letter-spacing: -0.5px;">Gannon Dunkerley & Co. Ltd.</h4>
                                <div class="text-uppercase small fw-bold text-primary tracking-wider" style="font-size: 0.75rem; letter-spacing: 1px;">
                                    Employee Attendance Log Sheet & Timesheet Summary
                                </div>
                            </div>
                        </div>
                        <div class="text-end text-muted small">
                            <div><strong>Statement Period:</strong> <span id="attSummaryPeriodBadge" class="fw-bold text-dark">—</span></div>
                            <div><strong>Printed:</strong> <?= date('d M Y, h:i A') ?></div>
                            <div class="badge bg-success-subtle text-success border border-success-subtle rounded-pill mt-1">Official Attendance Audit Sheet</div>
                        </div>
                    </div>

                    <!-- Employee Profile Card -->
                    <div class="row g-3 mb-4 p-3 bg-light rounded-4 border">
                        <div class="col-md-3">
                            <small class="text-muted fw-semibold d-block">Employee Name</small>
                            <span class="fs-6 fw-bold text-dark" id="attSummaryEmpName">—</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted fw-semibold d-block">Employee Code</small>
                            <span class="fs-6 fw-bold text-primary font-monospace" id="attSummaryEmpCode">—</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted fw-semibold d-block">Department</small>
                            <span class="fs-6 fw-semibold text-dark" id="attSummaryEmpDept">—</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted fw-semibold d-block">Base Work Site</small>
                            <span class="fs-6 fw-semibold text-dark" id="attSummaryEmpSite">—</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted fw-semibold d-block">Designation / Role</small>
                            <span class="fs-6 fw-semibold text-dark" id="attSummaryEmpRole">—</span>
                        </div>
                    </div>

                    <!-- Attendance KPI Grid -->
                    <div class="row g-3 mb-4 text-center">
                        <div class="col-6 col-md-2">
                            <div class="p-3 bg-light border rounded-4">
                                <small class="text-muted fw-semibold d-block mb-1">Days Clocked</small>
                                <h5 class="fw-bold text-dark mb-0 font-monospace" id="attSummaryTotalDays">0</h5>
                                <small class="text-muted" style="font-size: 0.7rem;">Attendance Days</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-3 bg-primary bg-opacity-10 border border-primary-subtle rounded-4">
                                <small class="text-primary-emphasis fw-semibold d-block mb-1">Total Hours</small>
                                <h5 class="fw-bold text-primary mb-0 font-monospace" id="attSummaryTotalHours">0 hrs</h5>
                                <small class="text-muted" style="font-size: 0.7rem;">Work Clock</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-3 bg-info bg-opacity-10 border border-info-subtle rounded-4">
                                <small class="text-info-emphasis fw-semibold d-block mb-1">Avg Hours / Day</small>
                                <h5 class="fw-bold text-info mb-0 font-monospace" id="attSummaryAvgHours">0 hrs</h5>
                                <small class="text-muted" style="font-size: 0.7rem;">Daily Average</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-3 bg-success bg-opacity-10 border border-success-subtle rounded-4">
                                <small class="text-success-emphasis fw-semibold d-block mb-1">Completed</small>
                                <h5 class="fw-bold text-success mb-0 font-monospace" id="attSummaryCompleted">0</h5>
                                <small class="text-muted" style="font-size: 0.7rem;">IN & OUT Paired</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-3 bg-warning bg-opacity-10 border border-warning-subtle rounded-4">
                                <small class="text-warning-emphasis fw-semibold d-block mb-1">No Out Punches</small>
                                <h5 class="fw-bold text-warning-emphasis mb-0 font-monospace" id="attSummaryNoOut">0</h5>
                                <small class="text-muted" style="font-size: 0.7rem;">Auto-closed</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-3 bg-dark text-white rounded-4 shadow-sm">
                                <small class="text-white text-opacity-75 fw-semibold d-block mb-1">Leaves / OD</small>
                                <h5 class="fw-bold text-white mb-0 font-monospace" id="attSummaryLeaves" style="font-size: 0.95rem;">0 / 0</h5>
                                <small class="text-white text-opacity-75" style="font-size: 0.7rem;">Official Record</small>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Attendance Timesheet Table -->
                    <div class="border rounded-4 overflow-hidden mb-4">
                        <div class="bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark small"><i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i>Daily Punch Sessions & Geo Logs</h6>
                            <span class="badge bg-secondary text-white rounded-pill">Verified Biometric Timesheet</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-muted text-uppercase" style="font-size: 0.72rem;">
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>Date</th>
                                        <th>Punch IN (Time & GPS)</th>
                                        <th>Punch OUT (Time & GPS)</th>
                                        <th>Hours Clocked</th>
                                        <th>Project / Site</th>
                                        <th class="pe-3 text-end">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="attSummaryTableBody" class="small">
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
                            <span>Site / Project Engineer</span>
                        </div>
                        <div class="col-4 text-center">
                            <div style="border-bottom: 1px dashed #ccc; height: 35px; width: 80%; margin: 0 auto 5px;"></div>
                            <span>HR / Timekeeper Verified</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top bg-light py-3 px-4 d-print-none">
                <a id="attSummaryDossierLink" href="#" class="btn btn-outline-primary rounded-pill px-3 me-auto">
                    <i class="fa-solid fa-chart-pie me-1"></i> Full Employee Attendance Dossier
                </a>
                <button type="button" class="btn btn-dark rounded-pill px-4" onclick="printAttendanceSummarySheet()">
                    <i class="fa-solid fa-print me-1"></i> Print Log Sheet
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
    #printableAttendanceSummaryArea, #printableAttendanceSummaryArea * {
        visibility: visible !important;
    }
    #printableAttendanceSummaryArea {
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
