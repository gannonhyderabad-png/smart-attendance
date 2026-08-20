<?php
$stats = $stats ?? [];
$stats['total_employees'] = $stats['total_employees'] ?? 0;
$stats['present_today'] = $stats['present_today'] ?? 0;
$stats['currently_in'] = $stats['currently_in'] ?? 0;
$stats['currently_out'] = $stats['currently_out'] ?? 0;
$stats['no_out_count'] = $stats['no_out_count'] ?? 0;
$stats['absent_today'] = $stats['absent_today'] ?? 0;
$stats['attendance_rate'] = $stats['attendance_rate'] ?? 0;
$stats['recent_punches'] = $stats['recent_punches'] ?? [];
$stats['trend_dates'] = $stats['trend_dates'] ?? [];
$stats['trend_counts'] = $stats['trend_counts'] ?? [];
$departments = $departments ?? [];
?>
<style>
.stat-card-link {
    text-decoration: none !important;
    display: block;
    height: 100%;
}
.stat-card-interactive {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}
.stat-card-interactive:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px -6px rgba(0, 0, 0, 0.12) !important;
}
</style>

<!-- Dashboard Page Header -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold text-dark mb-0">Workforce Dashboard</h4>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small fw-semibold" id="dashboardLiveBadge" style="font-size: 0.72rem;">
                <i class="fa-solid fa-circle text-success fa-beat-fade me-1"></i> Live Real-Time
            </span>
        </div>
        <p class="text-muted small mb-0">Overview of active workforce, attendance stats, and real-time punch logs</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('employees') ?>" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
            <i class="fa-solid fa-users me-1"></i> Staff Directory
        </a>
        <a href="<?= base_url('attendance') ?>" class="btn btn-light border rounded-pill px-3 py-2 fw-semibold">
            <i class="fa-solid fa-calendar-check me-1"></i> Attendance Logs
        </a>
    </div>
</div>

<div id="dashboardLiveContent">

<?php if (!empty($stats['no_out_count']) && $stats['no_out_count'] > 0): ?>
    <div class="alert alert-warning border-0 rounded-4 shadow-sm py-2 px-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <span class="badge bg-warning text-dark me-2 px-2 py-1"><i class="fa-solid fa-triangle-exclamation"></i> Alert</span>
            <span class="small fw-semibold text-dark">
                <strong><?= $stats['no_out_count'] ?> active employee(s)</strong> have no OUT punch today. Work duration has been auto-closed at scheduled shift end time.
            </span>
        </div>
        <a href="<?= base_url('attendance?punch_type=NO_OUT') ?>" class="btn btn-warning btn-sm rounded-pill px-3 py-1 text-dark fw-bold" style="font-size: 0.75rem;">
            View Logs <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <!-- Stat 1: Total Employees -->
    <div class="col-6 col-lg-3">
        <a href="<?= base_url('employees') ?>" class="stat-card-link" title="Click to view all employees">
            <div class="card stat-card stat-card-interactive border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-primary border-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Employees</span>
                    <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="fa-solid fa-users fs-6"></i></span>
                </div>
                <h3 class="fw-bold text-dark mb-0"><?= $stats['total_employees'] ?></h3>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <small class="text-muted" style="font-size: 0.75rem;">Active workforce</small>
                    <small class="text-primary fw-semibold" style="font-size: 0.7rem;">View list <i class="fa-solid fa-chevron-right ms-1"></i></small>
                </div>
            </div>
        </a>
    </div>

    <!-- Stat 2: Currently IN -->
    <div class="col-6 col-lg-3">
        <a href="<?= base_url('reports/daily?status=IN&date=' . date('Y-m-d')) ?>" class="stat-card-link" title="Click to view employees currently on duty">
            <div class="card stat-card stat-card-interactive border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Working Now (IN)</span>
                    <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="fa-solid fa-user-check fs-6"></i></span>
                </div>
                <h3 class="fw-bold text-success mb-0"><?= $stats['currently_in'] ?></h3>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <small class="text-muted" style="font-size: 0.75rem;">Currently on duty</small>
                    <small class="text-success fw-semibold" style="font-size: 0.7rem;">View IN <i class="fa-solid fa-chevron-right ms-1"></i></small>
                </div>
            </div>
        </a>
    </div>

    <!-- Stat 3: Punched Out -->
    <div class="col-6 col-lg-3">
        <a href="<?= base_url('reports/daily?status=OUT&date=' . date('Y-m-d')) ?>" class="stat-card-link" title="Click to view employees punched out today">
            <div class="card stat-card stat-card-interactive border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Punched Out</span>
                    <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="fa-solid fa-clock-rotate-left fs-6"></i></span>
                </div>
                <h3 class="fw-bold text-warning mb-0"><?= $stats['currently_out'] ?></h3>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <small class="text-muted" style="font-size: 0.75rem;">Completed shifts / on break</small>
                    <small class="text-warning fw-semibold" style="font-size: 0.7rem;">View OUT <i class="fa-solid fa-chevron-right ms-1"></i></small>
                </div>
            </div>
        </a>
    </div>

    <!-- Stat 4: Absent Today -->
    <div class="col-6 col-lg-3">
        <a href="<?= base_url('reports/daily?status=ABSENT&date=' . date('Y-m-d')) ?>" class="stat-card-link" title="Click to view absent employees for today">
            <div class="card stat-card stat-card-interactive border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-danger border-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Absent Today</span>
                    <span class="badge bg-danger-subtle text-danger rounded-circle p-2"><i class="fa-solid fa-user-xmark fs-6"></i></span>
                </div>
                <h3 class="fw-bold text-danger mb-0"><?= $stats['absent_today'] ?></h3>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <small class="text-muted" style="font-size: 0.75rem;"><?= $stats['attendance_rate'] ?>% present rate</small>
                    <small class="text-danger fw-semibold" style="font-size: 0.7rem;">View Absent <i class="fa-solid fa-chevron-right ms-1"></i></small>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Charts & Highlights Row -->
<div class="row g-3 mb-4">
    <!-- 7-Day Attendance Trend Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-dark mb-0">Attendance Trend (Last 7 Days)</h6>
                    <small class="text-muted">Unique employee check-ins per day</small>
                </div>
                <span class="badge bg-light text-secondary border">7-Day Matrix</span>
            </div>
            <div class="card-body px-4 pb-4">
                <div style="position: relative; height: 260px;">
                    <canvas id="attendanceTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action / Department Summary -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h6 class="fw-bold text-dark mb-0">Departments Overview</h6>
                <small class="text-muted">Active workforce allocation</small>
            </div>
            <div class="card-body p-4">
                <div class="list-group list-group-flush">
                    <?php foreach ($departments as $dept): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-light">
                            <div>
                                <span class="fw-semibold text-dark small d-block"><?= e($dept['name']) ?></span>
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.65rem;"><?= e($dept['code'] ?? 'GEN') ?></span>
                            </div>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1 small">
                                <?= $dept['employee_count'] ?> Employees
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-grid mt-3">
                    <a href="<?= base_url('employees') ?>" class="btn btn-outline-secondary btn-sm rounded-3">
                        <i class="fa-solid fa-users me-1"></i> View Staff Directory
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Punch Feed Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-bolt text-warning me-2"></i>Live Real-Time Punch Activity</h6>
            <small class="text-muted">Latest attendance actions recorded by the server</small>
        </div>
        <a href="<?= base_url('attendance') ?>" class="btn btn-light btn-sm rounded-pill border px-3">
            View All Logs <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Type</th>
                        <th>Punch Time</th>
                        <th>Department</th>
                        <th>Site</th>
                        <th>IP & Device</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (empty($stats['recent_punches'])): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fa-regular fa-clock fs-4 d-block mb-2 text-secondary"></i>
                                No attendance punches recorded today yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($stats['recent_punches'] as $punch): 
                            $siteVal = $punch['site'] ?? $punch['employee_site'] ?? null;
                            $projVal = $punch['project'] ?? $punch['employee_project'] ?? null;
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($punch['employee_photo'])): ?>
                                            <img src="<?= uploaded_url($punch['employee_photo']) ?>" class="rounded-circle border object-fit-cover me-2 shadow-sm" style="width: 32px; height: 32px;" alt="<?= e($punch['employee_name']) ?>">
                                        <?php else: ?>
                                            <div class="avatar-sm rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                <?= strtoupper(substr($punch['employee_name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <a href="<?= base_url('employees/view/' . $punch['employee_id']) ?>" class="fw-semibold text-dark text-decoration-none">
                                                <?= e($punch['employee_name']) ?>
                                            </a>
                                            <span class="badge bg-light text-secondary border font-monospace ms-1" style="font-size: 0.65rem;">
                                                <?= e($punch['employee_code']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge <?= $punch['punch_type'] === 'IN' ? 'bg-success' : 'bg-danger' ?> px-2 py-1">
                                            <i class="fa-solid <?= $punch['punch_type'] === 'IN' ? 'fa-arrow-right-to-bracket' : 'fa-arrow-right-from-bracket' ?> me-1"></i>
                                            <?= e($punch['punch_type']) ?>
                                        </span>
                                        <?php if (!empty($punch['punch_photo'])): ?>
                                            <a href="javascript:void(0)" onclick="viewPunchPhoto('<?= uploaded_url($punch['punch_photo']) ?>', '<?= e($punch['employee_name']) ?> - <?= e($punch['punch_type']) ?> Selfie', '<?= date('d M Y, h:i:s A', strtotime($punch['punch_time'])) ?>')" class="btn btn-sm btn-light border p-0 rounded-circle shadow-sm" title="View Punch Selfie">
                                                <img src="<?= uploaded_url($punch['punch_photo']) ?>" class="rounded-circle object-fit-cover border" style="width: 22px; height: 22px;" alt="Selfie">
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= date('h:i:s A', strtotime($punch['punch_time'])) ?></div>
                                    <small class="text-muted"><?= date('d M Y', strtotime($punch['punch_date'])) ?></small>
                                </td>
                                <td><?= e($punch['department_name'] ?? 'General') ?></td>
                                <td>
                                    <?php if (!empty($siteVal)): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <i class="fa-solid fa-location-dot me-1"></i><?= e($siteVal) ?>
                                        </span>
                                    <?php elseif (!empty($projVal)): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                            <i class="fa-solid fa-diagram-project me-1"></i><?= e($projVal) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 220px;" title="<?= e($punch['device_info']) ?>">
                                        <i class="fa-solid fa-network-wired text-muted me-1"></i><?= e($punch['ip_address'] ?? '127.0.0.1') ?>
                                        <div class="text-muted small text-truncate"><?= e($punch['device_info']) ?></div>
                                    </div>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('employees/view/' . $punch['employee_id']) ?>" class="btn btn-light border text-primary" title="View Employee Details">
                                            <i class="fa-solid fa-id-card"></i>
                                        </a>
                                        <a href="<?= punch_url($punch['employee_code']) ?>" target="_blank" class="btn btn-outline-primary" title="Open Mobile Punch URL">
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
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('attendanceTrendChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($stats['trend_dates']) ?>,
                    datasets: [{
                        label: 'Present Employees',
                        data: <?= json_encode($stats['trend_counts']) ?>,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#0d6efd',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    });

    function viewPunchPhoto(url, title, time) {
        if (!url) return;
        document.getElementById('modalPunchPhotoImg').src = url;
        document.getElementById('modalPunchPhotoTitle').textContent = title || 'Punch Photo';
        document.getElementById('modalPunchPhotoTime').textContent = time || '';
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('punchPhotoModal'));
        modal.show();
    }

    // Real-time background auto-sync without full page reload
    let isDashboardSyncing = false;
    let isDashboardModalOpen = false;
    const dashModalEl = document.getElementById('punchPhotoModal');
    if (dashModalEl) {
        dashModalEl.addEventListener('show.bs.modal', () => { isDashboardModalOpen = true; });
        dashModalEl.addEventListener('hidden.bs.modal', () => { isDashboardModalOpen = false; });
    }

    async function syncDashboardData() {
        if (isDashboardSyncing || isDashboardModalOpen) return;

        try {
            isDashboardSyncing = true;
            const res = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const newContent = doc.getElementById('dashboardLiveContent');
                const currentContent = document.getElementById('dashboardLiveContent');
                
                if (newContent && currentContent && newContent.innerHTML.trim() !== currentContent.innerHTML.trim()) {
                    currentContent.innerHTML = newContent.innerHTML;
                    
                    // Flash live badge briefly
                    const badge = document.getElementById('dashboardLiveBadge');
                    if (badge) {
                        badge.classList.add('bg-success', 'text-white');
                        setTimeout(() => {
                            badge.classList.remove('bg-success', 'text-white');
                        }, 1200);
                    }
                }
            }
        } catch (e) {
            console.debug('Dashboard sync:', e);
        } finally {
            isDashboardSyncing = false;
        }
    }

    // Poll every 3 seconds for instant updates when employee punches IN/OUT
    setInterval(syncDashboardData, 3000);
</script>

</div> <!-- /#dashboardLiveContent -->

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
