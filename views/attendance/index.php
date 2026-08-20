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
            <div class="col-md-6 text-md-end">
                <?php 
                $exportQuery = http_build_query($filters);
                ?>
                <a href="<?= base_url('attendance/export?' . $exportQuery) ?>" class="btn btn-success rounded-pill px-3 shadow-sm">
                    <i class="fa-solid fa-file-csv me-1"></i> Export Filtered CSV
                </a>
            </div>
        </div>

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
        <!-- Attendance Records Table (Paired IN & OUT in one row) -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Employee</th>
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
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-regular fa-calendar-xmark fs-3 d-block mb-2 text-secondary"></i>
                                No attendance sessions match the selected criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <a href="<?= base_url('employees/view/' . $row['employee_id']) ?>" class="text-decoration-none">
                                            <?php if (!empty($row['employee_photo'])): ?>
                                                <img src="<?= uploaded_url($row['employee_photo']) ?>" class="rounded-circle border object-fit-cover me-2 shadow-sm" style="width: 36px; height: 36px;" alt="<?= e($row['employee_name']) ?>">
                                            <?php else: ?>
                                                <div class="avatar-sm rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; font-size: 0.85rem;" title="View <?= e($row['employee_name']) ?> profile">
                                                    <?= strtoupper(substr($row['employee_name'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                        <div>
                                            <a href="<?= base_url('employees/view/' . $row['employee_id']) ?>" class="fw-bold text-dark text-decoration-none" title="Click to view details for <?= e($row['employee_name']) ?>">
                                                <?= e($row['employee_name']) ?>
                                            </a>
                                            <div>
                                                <a href="<?= base_url('employees/view/' . $row['employee_id']) ?>" class="badge bg-light text-primary border font-monospace text-decoration-none" style="font-size: 0.7rem;" title="View Employee Details">
                                                    <?= e($row['employee_code']) ?>
                                                </a>
                                                <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle px-1 ms-1" style="font-size: 0.68rem;"><i class="fa-solid fa-building me-1 text-secondary"></i><?= e($row['department_name'] ?? 'General') ?></span>
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
                                        <?php if (!empty($row['in_distance']) !== null && $row['in_distance'] !== ''): ?>
                                            <div class="small">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.65rem;" title="GPS Verified within <?= round($row['in_distance']) ?>m of site">
                                                    <i class="fa-solid fa-location-dot me-1"></i><?= round($row['in_distance']) ?>m away
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
                                        <?php if (!empty($row['out_distance']) !== null && $row['out_distance'] !== ''): ?>
                                            <div class="small">
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.65rem;" title="GPS Verified within <?= round($row['out_distance']) ?>m of site">
                                                    <i class="fa-solid fa-location-dot me-1"></i><?= round($row['out_distance']) ?>m away
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
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('employees/view/' . $row['employee_id']) ?>" class="btn btn-light border text-primary" title="View Employee Details & QR Code">
                                            <i class="fa-solid fa-id-card"></i>
                                        </a>
                                        <a href="<?= punch_url($row['employee_code']) ?>" target="_blank" class="btn btn-outline-primary" title="Open Mobile Punch URL">
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
</script>
