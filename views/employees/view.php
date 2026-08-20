<div class="row g-4">
    <!-- Left Column: Employee ID Card & QR Code -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 bg-white text-center p-4">
            
            <div class="position-relative mx-auto mb-3" style="width: 80px; height: 80px;">
                <?php if (!empty($employee['photo'])): ?>
                    <img src="<?= uploaded_url($employee['photo']) ?>" class="rounded-circle border shadow object-fit-cover w-100 h-100" alt="<?= e($employee['name']) ?>">
                <?php else: ?>
                    <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center w-100 h-100 shadow" style="font-size: 2rem;">
                        <?= strtoupper(substr($employee['name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>

            <h4 class="fw-bold text-dark mb-1"><?= e($employee['name']) ?></h4>
            <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                <span class="badge bg-primary-subtle text-primary font-monospace px-2 py-1 fs-6"><?= e($employee['employee_code']) ?></span>
                <span class="badge bg-light text-dark border"><?= e($employee['department_name'] ?? 'General') ?></span>
            </div>

            <div class="p-3 bg-light rounded-4 border text-start small mb-4">
                <div class="row g-2">
                    <div class="col-4 text-muted fw-semibold">Designation:</div>
                    <div class="col-8 text-dark"><?= e($employee['designation'] ?? 'Staff') ?></div>

                    <div class="col-4 text-muted fw-semibold">Assigned Project:</div>
                    <div class="col-8 text-primary fw-bold"><?= e($employee['project'] ?? 'General Operations') ?></div>

                    <div class="col-4 text-muted fw-semibold">Work Site / Location:</div>
                    <div class="col-8 text-dark fw-semibold"><i class="fa-solid fa-location-dot text-danger me-1"></i><?= e($employee['site'] ?? 'Main Office') ?></div>

                    <div class="col-4 text-muted fw-semibold">GPS Geofence:</div>
                    <div class="col-8 text-dark">
                        <?php if (!empty($employee['geofence_enabled']) && !empty($employee['site_latitude']) && !empty($employee['site_longitude'])): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="fa-solid fa-map-pin me-1"></i> Active (Radius: <?= e($employee['site_radius'] ?? 200) ?>m)
                            </span>
                            <div class="font-monospace text-muted small mt-1">
                                <?= round((float)$employee['site_latitude'], 5) ?>, <?= round((float)$employee['site_longitude'], 5) ?>
                            </div>
                        <?php else: ?>
                            <span class="badge bg-light text-muted border">Unrestricted</span>
                        <?php endif; ?>
                    </div>

                    <div class="col-4 text-muted fw-semibold">Email:</div>
                    <div class="col-8 text-dark"><?= e($employee['email'] ?? '—') ?></div>

                    <div class="col-4 text-muted fw-semibold">Phone:</div>
                    <div class="col-8 text-dark"><?= e($employee['phone'] ?? '—') ?></div>

                    <div class="col-4 text-muted fw-semibold">Shift Hours:</div>
                    <div class="col-8 text-dark"><?= date('h:i A', strtotime($employee['shift_start'])) ?> - <?= date('h:i A', strtotime($employee['shift_end'])) ?></div>

                    <div class="col-4 text-muted fw-semibold">Today Worked:</div>
                    <div class="col-8 text-success fw-bold font-monospace"><?= format_seconds($todayWorkSeconds) ?></div>
                </div>
            </div>

            <!-- QR Code Generator Section -->
            <div class="qr-container p-4 bg-light rounded-4 border mb-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-qrcode text-primary me-1"></i> Attendance Punch QR</h6>
                    <span class="badge bg-success text-white small"><i class="fa-solid fa-circle-check me-1"></i> Ready to Scan</span>
                </div>
                <p class="text-muted small mb-2">Scan with any mobile phone to open attendance punch portal</p>
                <div id="qrcodeBox" class="d-flex justify-content-center my-3 p-2 bg-white rounded-3 shadow-sm border mx-auto" style="width: 180px; height: 180px;"></div>
                <div class="small font-monospace text-primary text-truncate p-1 bg-white rounded border"><?= e($punchUrl) ?></div>
            </div>

            <!-- URL Copy & Share Tools -->
            <div class="mb-3 text-start">
                <label class="form-label small fw-semibold text-muted mb-1">Direct Attendance URL:</label>
                <div class="input-group mb-2">
                    <input type="text" class="form-control bg-light font-monospace small" id="punchUrlInput" value="<?= e($punchUrl) ?>" readonly>
                    <button class="btn btn-primary" type="button" onclick="copyToClipboard('<?= e($punchUrl) ?>', this)">
                        <i class="fa-regular fa-copy me-1"></i> Copy URL
                    </button>
                </div>
            </div>

            <!-- Sharing Buttons -->
            <div class="d-flex gap-2 justify-content-center mt-3">
                <?php 
                $waText = "Hello " . $employee['name'] . ", here is your daily attendance punch link: " . $punchUrl;
                $waUrl = "https://api.whatsapp.com/send?text=" . urlencode($waText);
                ?>
                <a href="<?= $waUrl ?>" target="_blank" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fa-brands fa-whatsapp me-1"></i> Send on WhatsApp
                </a>
                <a href="<?= e($punchUrl) ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Punch Page
                </a>
            </div>

        </div>
    </div>

    <!-- Right Column: Recent Punches & Stats -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">Recent Attendance History</h5>
                    <small class="text-muted">Last 10 punch entries with live selfie verification</small>
                </div>
                <a href="<?= base_url('attendance?employee_id=' . $employee['id']) ?>" class="btn btn-light btn-sm border rounded-pill px-3">
                    Full Log History <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Punch IN</th>
                                <th>Punch OUT</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th class="pe-4">Location / Device</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if (empty($recentAttendance)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fa-regular fa-calendar-xmark fs-4 d-block mb-2 text-secondary"></i>
                                        No attendance logs found for this employee.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentAttendance as $att): ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold"><?= date('d M Y', strtotime($att['punch_date'])) ?></td>
                                        <td>
                                            <?php if (!empty($att['in_time'])): ?>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace">
                                                        <i class="fa-solid fa-arrow-right-to-bracket me-1"></i><?= date('h:i:s A', strtotime($att['in_time'])) ?>
                                                    </span>
                                                    <?php if (!empty($att['in_photo'])): ?>
                                                        <a href="javascript:void(0)" onclick="viewPunchPhoto('<?= uploaded_url($att['in_photo']) ?>', '<?= e($employee['name']) ?> - Punch IN', '<?= date('d M Y, h:i:s A', strtotime($att['in_time'])) ?>')" class="btn btn-sm btn-light border p-0 rounded-circle" title="View IN Selfie Photo">
                                                            <img src="<?= uploaded_url($att['in_photo']) ?>" class="rounded-circle object-fit-cover border" style="width: 24px; height: 24px;" alt="IN Selfie">
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($att['out_time'])): ?>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 font-monospace">
                                                        <i class="fa-solid fa-arrow-right-from-bracket me-1"></i><?= date('h:i:s A', strtotime($att['out_time'])) ?>
                                                    </span>
                                                    <?php if (!empty($att['out_photo'])): ?>
                                                        <a href="javascript:void(0)" onclick="viewPunchPhoto('<?= uploaded_url($att['out_photo']) ?>', '<?= e($employee['name']) ?> - Punch OUT', '<?= date('d M Y, h:i:s A', strtotime($att['out_time'])) ?>')" class="btn btn-sm btn-light border p-0 rounded-circle" title="View OUT Selfie Photo">
                                                            <img src="<?= uploaded_url($att['out_photo']) ?>" class="rounded-circle object-fit-cover border" style="width: 24px; height: 24px;" alt="OUT Selfie">
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php elseif ($att['status'] === 'IN'): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                                    <i class="fa-solid fa-clock me-1"></i> Working Now
                                                </span>
                                            <?php elseif ($att['status'] === 'NO_OUT' || !empty($att['auto_closed'])): ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" title="Employee did not punch OUT. Auto-closed at scheduled shift end.">
                                                    <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> No OUT Punch
                                                </span>
                                                <div class="small text-muted font-monospace" style="font-size: 0.68rem;">
                                                    <i class="fa-regular fa-clock me-1"></i>Auto-closed: <?= date('h:i A', strtotime($employee['shift_end'] ?? '18:00:00')) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="fw-bold font-monospace <?= $att['duration_seconds'] > 0 ? 'text-primary' : 'text-muted' ?>">
                                                <?= $att['formatted_duration'] ?>
                                            </span>
                                            <?php if ($att['status'] === 'NO_OUT' || !empty($att['auto_closed'])): ?>
                                                <div class="small text-muted" style="font-size: 0.65rem;" title="Auto-calculated from Punch IN to site shift end time">
                                                    <i class="fa-solid fa-calculator me-1"></i>Shift Time
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($att['status'] === 'IN'): ?>
                                                <span class="badge bg-success px-2 py-1">
                                                    <i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i> Working Now
                                                </span>
                                            <?php elseif ($att['status'] === 'NO_OUT' || !empty($att['auto_closed'])): ?>
                                                <span class="badge bg-warning text-dark border border-warning px-2 py-1" title="No Out Punch - Auto closed at shift end">
                                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> No OUT Punch
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                                    <i class="fa-solid fa-check me-1"></i> Completed
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-muted small">
                                            <?php 
                                             $devIp = $att['in_ip'] ?: ($att['out_ip'] ?? '127.0.0.1');
                                             $devInfo = $att['in_device'] ?: ($att['out_device'] ?? '—');
                                            ?>
                                            <div class="text-truncate" style="max-width: 170px;" title="<?= e($devInfo) ?>">
                                                <code><?= e($devIp) ?></code>
                                                <div class="text-muted small text-truncate"><?= e($devInfo) ?></div>
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

        <div class="d-flex justify-content-between align-items-center">
            <a href="<?= base_url('employees') ?>" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Employee List
            </a>
            <div class="d-flex gap-2">
                <a href="<?= base_url('employees/edit/' . $employee['id']) ?>" class="btn btn-outline-primary rounded-pill px-3">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit Profile
                </a>
            </div>
        </div>

    </div>
</div>

<script>
    function renderEmployeeQr() {
        const qrContainer = document.getElementById('qrcodeBox');
        if (!qrContainer) return;
        if (typeof QRCode !== 'undefined') {
            qrContainer.innerHTML = '';
            new QRCode(qrContainer, {
                text: <?= json_encode($punchUrl) ?>,
                width: 160,
                height: 160,
                colorDark: "#111827",
                colorLight: "#ffffff",
                correctLevel: (typeof QRCode.CorrectLevel !== 'undefined') ? QRCode.CorrectLevel.M : 0
            });
        } else {
            // Dynamic fallback image if library is delayed
            qrContainer.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=${encodeURIComponent(<?= json_encode($punchUrl) ?>)}" style="width:160px;height:160px;" alt="QR Code">`;
        }
    }
    document.addEventListener('DOMContentLoaded', renderEmployeeQr);
    window.addEventListener('load', renderEmployeeQr);
    setTimeout(renderEmployeeQr, 500);
</script>

    function viewPunchPhoto(url, title, time) {
        if (!url) return;
        document.getElementById('modalPunchPhotoImg').src = url;
        document.getElementById('modalPunchPhotoTitle').textContent = title || 'Punch Photo';
        document.getElementById('modalPunchPhotoTime').textContent = time || '';
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('punchPhotoModal'));
        modal.show();
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
