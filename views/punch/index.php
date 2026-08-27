<style>
/* Inline Mobile Punch Core Styles */
.mobile-punch-wrap {
    max-width: 460px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
.punch-card {
    border-radius: 24px !important;
    border: none !important;
    box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.08), 0 0 1px 1px rgba(0,0,0,0.05);
    background: #ffffff;
    overflow: hidden;
}
.punch-header-gradient {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%) !important;
    color: #ffffff !important;
    padding: 1.75rem 1.25rem 1.5rem !important;
    text-align: center;
    position: relative;
}
.avatar-badge {
    width: 68px;
    height: 68px;
    margin: 0 auto 0.75rem;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.25);
    border: 3px solid rgba(255, 255, 255, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    font-weight: 800;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.employee-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #ffffff !important;
    margin-bottom: 0.35rem;
    letter-spacing: -0.3px;
}
.clock-display-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 1rem;
    margin-bottom: 1rem;
    text-align: center;
}
.clock-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #64748b;
    margin-bottom: 0.25rem;
}
.clock-digits {
    font-size: 2.15rem;
    font-weight: 800;
    color: #0f172a;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    line-height: 1.1;
}
.clock-date {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.25rem;
}
.status-pill-box {
    border-radius: 16px;
    padding: 0.85rem 1rem;
    margin-bottom: 1.25rem;
    text-align: center;
    border: 1.5px solid transparent;
    transition: all 0.3s ease;
}
.status-pill-box.status-in {
    background-color: #ecfdf5;
    border-color: #a7f3d0;
    color: #065f46;
}
.status-pill-box.status-out {
    background-color: #f8fafc;
    border-color: #e2e8f0;
    color: #475569;
}
.pulse-indicator {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 6px;
    vertical-align: middle;
}
.status-in .pulse-indicator {
    background-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3);
    animation: pulse-dot 1.8s infinite;
}
.status-out .pulse-indicator {
    background-color: #94a3b8;
}
@keyframes pulse-dot {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
    70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
.btn-punch-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1rem 1.25rem;
    border-radius: 18px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
    user-select: none;
}
.btn-punch-action:active {
    transform: scale(0.97);
}
.btn-punch-in-action {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
}
.btn-punch-in-action:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: #ffffff;
}
.btn-punch-out-action {
    background: #ffffff;
    color: #ef4444;
    border: 2px solid #fee2e2;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}
.btn-punch-out-action:hover {
    background: #fef2f2;
    border-color: #fca5a5;
    color: #dc2626;
}
.btn-punch-action.disabled-action {
    opacity: 0.45 !important;
    pointer-events: none !important;
    box-shadow: none !important;
}
</style>

<div class="mobile-punch-wrap">
    
    <!-- Top Branding -->
    <div class="text-center mb-3">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow-sm mb-1" style="width: 46px; height: 46px;">
            <i class="fa-solid fa-fingerprint fs-4"></i>
        </div>
        <h5 class="fw-bold text-dark mb-0">Smart Attendance</h5>
        <span class="text-muted" style="font-size: 0.8rem;">Touch Mobile Punch Portal</span>
    </div>

    <!-- Main Punch Card -->
    <div class="card punch-card mb-3">
        
        <!-- Employee Header Banner -->
        <div class="punch-header-gradient">
            <div class="avatar-badge overflow-hidden p-0">
                <?php if (!empty($employee['photo'])): ?>
                    <img src="<?= uploaded_url($employee['photo']) ?>" class="w-100 h-100 object-fit-cover rounded-circle" alt="<?= e($employee['name']) ?>">
                <?php else: ?>
                    <span><?= strtoupper(substr($employee['name'], 0, 1)) ?></span>
                <?php endif; ?>
            </div>
            <div class="employee-title"><?= e($employee['name']) ?></div>
            
            <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap mt-1">
                <span class="badge bg-white text-primary fw-bold font-monospace px-2 py-1"><?= e($employee['employee_code']) ?></span>
                <span class="badge bg-black bg-opacity-25 text-white"><?= e($employee['department_name'] ?? 'General') ?></span>
                <?php if (!empty($employee['project'])): ?>
                    <span class="badge bg-warning text-dark fw-bold"><i class="fa-solid fa-diagram-project me-1"></i><?= e($employee['project']) ?></span>
                <?php endif; ?>
            </div>

            <?php if (!empty($employee['designation'])): ?>
                <div class="small text-white-50 mt-1" style="font-size: 0.78rem;"><?= e($employee['designation']) ?></div>
            <?php endif; ?>
        </div>

        <div class="card-body p-4">
            
            <!-- Live Digital Clock -->
            <div class="clock-display-box">
                <div class="clock-label">Current Time</div>
                <div class="clock-digits" id="liveClockDisplay">--:--:-- --</div>
                <div class="clock-date" id="liveDateDisplay"><?= date('l, d F Y') ?></div>
            </div>

            <!-- Live Selfie Camera Module -->
            <div class="card border rounded-4 bg-light mb-3 p-3 overflow-hidden text-center position-relative">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="small fw-bold text-dark">
                        <i class="fa-solid fa-camera text-primary me-1"></i> Live Face Verification
                    </div>
                    <span id="cameraBadgeStatus" class="badge bg-warning-subtle text-warning border border-warning-subtle py-1 px-2" style="font-size: 0.7rem;">
                        <span class="spinner-grow spinner-grow-sm me-1" style="width: 8px; height: 8px;"></span> Starting Camera...
                    </span>
                </div>

                <div class="position-relative mx-auto rounded-3 overflow-hidden bg-black shadow-sm" style="max-width: 320px; width: 100%; aspect-ratio: 4/3;">
                    <video id="punchCameraVideo" autoplay playsinline muted class="w-100 h-100 object-fit-cover" style="transform: scaleX(-1);"></video>
                    <canvas id="punchPhotoCanvas" class="d-none"></canvas>
                    <div id="cameraFlashOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white" style="opacity: 0; pointer-events: none; transition: opacity 0.25s ease; z-index: 10;"></div>
                    
                    <!-- Face Target Guide Oval -->
                    <div class="position-absolute top-50 start-50 translate-middle border border-2 border-white rounded-pill opacity-50" style="width: 130px; height: 165px; pointer-events: none; box-shadow: 0 0 0 9999px rgba(0,0,0,0.25);"></div>

                    <!-- Live Snapshot Preview Thumbnail (Shown after successful punch) -->
                    <img id="lastSnapPreview" src="" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover d-none" style="z-index: 5;" alt="Punch Selfie Snapshot">
                </div>

                <div class="d-flex align-items-center justify-content-between mt-2 pt-1">
                    <small class="text-muted" style="font-size: 0.72rem;">
                        <i class="fa-solid fa-shield-halved text-success me-1"></i> Selfie is taken automatically on punch
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" onclick="window.flipCamera()" id="flipCameraBtn" title="Switch Camera" style="font-size: 0.7rem;">
                        <i class="fa-solid fa-camera-rotate me-1"></i> Flip
                    </button>
                </div>
            </div>

            <!-- Site & Geofence GPS Location Card -->
            <?php if (!empty($employee['site']) || !empty($employee['site_latitude'])): ?>
                <div class="p-3 mb-3 rounded-4 border bg-light text-start small">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="fw-bold text-dark text-truncate" style="max-width: 65%;">
                            <i class="fa-solid fa-location-dot text-danger me-1"></i> <?= e($employee['site'] ?? 'Assigned Work Site') ?>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" style="font-size: 0.72rem;">
                            Radius: <?= e($employee['site_radius'] ?? 200) ?>m
                        </span>
                    </div>
                    
                    <!-- Live GPS Indicator Pill -->
                    <div id="gpsStatusContainer" class="d-flex align-items-center justify-content-between p-2 rounded-3 mt-2 bg-white border">
                        <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 80%;">
                            <span id="gpsDotIndicator" class="spinner-grow spinner-grow-sm text-warning flex-shrink-0" style="width: 10px; height: 10px;"></span>
                            <span id="gpsStatusText" class="small fw-semibold text-muted text-truncate">Acquiring GPS location...</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 flex-shrink-0" onclick="window.refreshGpsLocation()" title="Refresh GPS Location">
                            <i class="fa-solid fa-location-crosshairs text-primary"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Active Leave or OD (Outdoor Duty) Notification Banner -->
            <?php if (!empty($todayLeave)): 
                $isOdToday = str_contains(strtoupper($todayLeave['leave_type'] ?? ''), 'OD') || str_contains(strtoupper($todayLeave['leave_type'] ?? ''), 'OUTDOOR') || str_contains(strtoupper($todayLeave['leave_type'] ?? ''), 'DUTY');
            ?>
                <div class="alert <?= $isOdToday ? 'alert-primary border-primary' : 'alert-warning border-warning' ?> rounded-4 text-start p-3 mb-3 shadow-sm">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="<?= $isOdToday ? 'fa-solid fa-briefcase text-primary' : 'fa-solid fa-plane-departure text-warning' ?> fs-5"></i>
                        <h6 class="fw-bold mb-0 text-dark">
                            <?= $isOdToday ? 'Active Outdoor Duty (OD)' : 'Approved Leave Active Today' ?>
                        </h6>
                    </div>
                    <div class="small text-muted mt-1">
                        <strong>Type:</strong> <span class="badge <?= $isOdToday ? 'bg-primary text-white' : 'bg-warning text-dark' ?> ms-1"><?= e($todayLeave['leave_type']) ?></span>
                        <?php if (!empty($todayLeave['target_site'])): ?>
                            <div class="mt-1 text-dark"><i class="fa-solid fa-location-dot text-danger me-1"></i><strong>Assigned Target Site:</strong> <?= e($todayLeave['target_site']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($todayLeave['reason'])): ?>
                            <div class="mt-1"><i class="fa-regular fa-comment-dots me-1"></i><strong>Notes:</strong> <?= e($todayLeave['reason']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Status Banner -->
            <div id="statusBannerBox" class="status-pill-box <?= $currentStatus === 'IN' ? 'status-in' : 'status-out' ?>">
                <div class="d-flex align-items-center justify-content-center gap-1">
                    <span class="pulse-indicator"></span>
                    <span class="fw-bold text-uppercase" id="statusLabelText" style="letter-spacing: 0.5px; font-size: 0.88rem;">
                        <?= $currentStatus === 'IN' ? 'Currently Checked IN' : 'Currently Checked OUT' ?>
                    </span>
                </div>

                <div class="small mt-1 opacity-75" id="lastPunchSubtext" style="font-size: 0.75rem;">
                    <?php if ($latestPunch): ?>
                        Last Punch: <strong><?= e($latestPunch['punch_type']) ?></strong> at <?= date('h:i:s A', strtotime($latestPunch['punch_time'])) ?>
                    <?php else: ?>
                        No punches recorded today yet.
                    <?php endif; ?>
                </div>

                <?php if (!empty($isNoOutAutoClosed)): ?>
                    <div class="mt-2 py-1 px-2 bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-3 small text-center" style="font-size: 0.72rem;">
                        <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> Note: Work session was auto-closed at scheduled site shift end (<?= date('h:i A', strtotime($employee['shift_end'] ?? '18:00:00')) ?>) due to unassigned OUT punch.
                    </div>
                <?php endif; ?>

                <!-- Live Duration Timer (When IN) -->
                <div class="mt-2 pt-2 border-top <?= $currentStatus === 'IN' ? '' : 'd-none' ?>" id="workingDurationBox" style="border-color: rgba(0,0,0,0.08) !important;">
                    <span class="small text-muted" style="font-size: 0.72rem;">Working Duration Today:</span>
                    <div class="h5 fw-bold text-success font-monospace mb-0" id="liveDurationTimer">
                        <?= format_seconds($workedSeconds) ?>
                    </div>
                </div>
            </div>

            <!-- Punch Action Buttons -->
            <div class="d-grid gap-3 mb-3">
                
                <!-- PUNCH IN BUTTON -->
                <button type="button" 
                        id="punchInBtn" 
                        class="btn-punch-action btn-punch-in-action <?= $currentStatus === 'IN' ? 'disabled-action' : '' ?>"
                        onclick="executePunch('IN')">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-right-to-bracket fs-5"></i>
                        <span class="fs-5">PUNCH IN</span>
                    </div>
                    <small class="fw-normal opacity-90" style="font-size: 0.75rem;">Start Work Session</small>
                </button>

                <!-- PUNCH OUT BUTTON -->
                <button type="button" 
                        id="punchOutBtn" 
                        class="btn-punch-action btn-punch-out-action <?= $currentStatus === 'OUT' ? 'disabled-action' : '' ?>"
                        onclick="executePunch('OUT')">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-right-from-bracket fs-5"></i>
                        <span class="fs-5">PUNCH OUT</span>
                    </div>
                    <small class="fw-normal opacity-90" style="font-size: 0.75rem;">End Work Session / Break</small>
                </button>
            </div>

            <!-- Toast / Alert Feedback Message -->
            <div id="punchFeedbackAlert" class="alert d-none small text-start rounded-3 mb-3" role="alert"></div>

            <!-- Today's Punch History Dropdown -->
            <div class="accordion accordion-flush text-start border rounded-3 mt-3" id="historyAccordion">
                <div class="accordion-item rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed py-2 px-3 small fw-semibold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#historyCollapse">
                            <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Today's History (<span id="historyCount"><?= count($todayPunches) ?></span>)
                        </button>
                    </h2>
                    <div id="historyCollapse" class="accordion-collapse collapse" data-bs-parent="#historyAccordion">
                        <div class="accordion-body p-2" id="historyLogsContainer">
                            <?php if (empty($todayPunches)): ?>
                                <div class="text-center text-muted small py-3" id="emptyHistoryMsg">No punches recorded today.</div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($todayPunches as $p): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-2 py-2">
                                            <div class="d-flex align-items-center">
                                                <span class="badge <?= $p['punch_type'] === 'IN' ? 'bg-success' : 'bg-danger' ?> me-2">
                                                    <?= e($p['punch_type']) ?>
                                                </span>
                                                <span class="small font-monospace"><?= date('h:i:s A', strtotime($p['punch_time'])) ?></span>
                                            </div>
                                            <span class="badge bg-light text-muted border small" style="font-size: 0.7rem;">
                                                <?= e($p['ip_address'] ?? 'Local') ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client Device Metadata -->
            <div class="mt-3 text-muted text-center" style="font-size: 0.72rem;">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <span><i class="fa-solid fa-location-dot me-1 text-secondary"></i> IP: <?= e($clientIp) ?></span>
                    <span>&bull;</span>
                    <span class="text-truncate" style="max-width: 150px;"><i class="fa-solid fa-mobile-screen me-1 text-secondary"></i> <?= e($deviceInfo) ?></span>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Geofence Outside Location Dialog Box Modal -->
<div class="modal fade" id="geofenceErrorModal" tabindex="-1" aria-labelledby="geofenceErrorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content rounded-4 border-0 shadow-lg text-center p-4">
            <div class="mb-3">
                <div class="avatar-lg rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 76px; height: 76px; font-size: 2.2rem;">
                    <i class="fa-solid fa-location-crosshairs"></i>
                </div>
            </div>
            <h5 class="fw-bold text-dark mb-1" id="geofenceModalTitle">You Are Not in the Site Location!</h5>
            <p class="text-danger fw-semibold small mb-3" id="geofenceModalSubtitle">Punch IN & OUT is strictly prohibited outside the designated work site.</p>
            
            <div class="p-3 bg-light rounded-4 border text-start small mb-3">
                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-muted fw-semibold">Assigned Work Site:</span>
                    <span class="text-dark fw-bold" id="geoSiteName"><?= e($employee['site'] ?? 'Assigned Work Site') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-muted fw-semibold">Allowed Geofence Radius:</span>
                    <span class="badge bg-primary-subtle text-primary font-monospace"><?= e($employee['site_radius'] ?? 200) ?> meters</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted fw-semibold">Your Current Distance:</span>
                    <span class="text-danger fw-bold font-monospace" id="geoCurrentDist">—</span>
                </div>
            </div>

            <div class="alert alert-warning small text-start rounded-3 mb-4 py-2 px-3">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> Please move within <strong><?= e($employee['site_radius'] ?? 200) ?>m</strong> of your assigned site location to punch your attendance.
            </div>

            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary rounded-pill py-2 fw-bold" onclick="window.refreshGpsLocation(); const m = bootstrap.Modal.getInstance(document.getElementById('geofenceErrorModal')); if (m) m.hide();">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh My GPS Location
                </button>
                <button type="button" class="btn btn-light border rounded-pill py-2" data-bs-dismiss="modal">
                    Close Dialog
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    // State Variables
    let currentStatus = <?= json_encode($currentStatus) ?>;
    let workedSeconds = <?= (int)$workedSeconds ?>;
    const recordUrl = <?= json_encode(public_url('p/' . rawurlencode($employee['employee_code']))) ?>;
    let durationInterval = null;

    // Geofencing State
    const siteLat = <?= json_encode(!empty($employee['site_latitude']) ? (float)$employee['site_latitude'] : null) ?>;
    const siteLon = <?= json_encode(!empty($employee['site_longitude']) ? (float)$employee['site_longitude'] : null) ?>;
    const siteRadius = <?= (int)($employee['site_radius'] ?? 200) ?>;
    const geofenceEnabled = <?= (!empty($employee['geofence_enabled']) && !empty($employee['site_latitude']) && !empty($employee['site_longitude'])) ? 'true' : 'false' ?>;

    let userLat = null;
    let userLon = null;
    let userAccuracy = null;
    let userDistance = null;
    let isWithinGeofence = true;

    // --- Camera Selfie Controller ---
    let mediaStream = null;
    let currentFacingMode = 'user'; // 'user' (front camera) or 'environment' (back)
    const videoEl = document.getElementById('punchCameraVideo');
    const canvasEl = document.getElementById('punchPhotoCanvas');
    const cameraBadge = document.getElementById('cameraBadgeStatus');
    const flashEl = document.getElementById('cameraFlashOverlay');
    const lastSnapEl = document.getElementById('lastSnapPreview');

    function initCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (cameraBadge) {
                cameraBadge.className = 'badge bg-secondary-subtle text-secondary border py-1 px-2';
                cameraBadge.innerHTML = '<i class="fa-solid fa-video-slash me-1"></i> Camera Unavailable';
            }
            return;
        }

        if (mediaStream) {
            mediaStream.getTracks().forEach(t => t.stop());
            mediaStream = null;
        }

        navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: currentFacingMode,
                width: { ideal: 640 },
                height: { ideal: 480 }
            },
            audio: false
        })
        .then(function(stream) {
            mediaStream = stream;
            if (videoEl) {
                videoEl.srcObject = stream;
                videoEl.play().catch(() => {});
                videoEl.style.transform = (currentFacingMode === 'user') ? 'scaleX(-1)' : 'none';
            }
            if (cameraBadge) {
                cameraBadge.className = 'badge bg-success-subtle text-success border border-success-subtle py-1 px-2';
                cameraBadge.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Camera Ready';
            }
        })
        .catch(function(err) {
            if (cameraBadge) {
                cameraBadge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle py-1 px-2';
                cameraBadge.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Camera Access Optional';
            }
        });
    }

    window.flipCamera = function() {
        currentFacingMode = (currentFacingMode === 'user') ? 'environment' : 'user';
        initCamera();
    };

    function snapSelfiePhoto() {
        if (!videoEl || !canvasEl || !mediaStream) return null;
        try {
            const width = videoEl.videoWidth || 480;
            const height = videoEl.videoHeight || 360;
            canvasEl.width = width;
            canvasEl.height = height;
            const ctx = canvasEl.getContext('2d');

            if (currentFacingMode === 'user') {
                ctx.translate(width, 0);
                ctx.scale(-1, 1);
            }

            ctx.drawImage(videoEl, 0, 0, width, height);

            // Shutter Flash Animation
            if (flashEl) {
                flashEl.style.opacity = '0.85';
                setTimeout(() => { flashEl.style.opacity = '0'; }, 200);
            }

            return canvasEl.toDataURL('image/jpeg', 0.82);
        } catch (e) {
            return null;
        }
    }

    // 1. Live Clock Function
    function tickClock() {
        const clockEl = document.getElementById('liveClockDisplay');
        if (!clockEl) return;
        const now = new Date();
        clockEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    }

    // 2. Format Seconds into HH:MM:SS
    function formatTimerSeconds(sec) {
        const h = Math.floor(sec / 3600);
        const m = Math.floor((sec % 3600) / 60);
        const s = sec % 60;
        return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    // 3. Duration Timer Controller
    function startTimer() {
        if (durationInterval) clearInterval(durationInterval);
        const timerWrapper = document.getElementById('workingDurationBox');
        const timerEl = document.getElementById('liveDurationTimer');
        if (timerWrapper) timerWrapper.classList.remove('d-none');

        durationInterval = setInterval(function() {
            workedSeconds++;
            if (timerEl) {
                timerEl.textContent = formatTimerSeconds(workedSeconds);
            }
        }, 1000);
    }

    function stopTimer() {
        if (durationInterval) clearInterval(durationInterval);
        durationInterval = null;
        const timerWrapper = document.getElementById('workingDurationBox');
        if (timerWrapper) timerWrapper.classList.add('d-none');
    }

    // 4. Play Audio Tone Feedback
    function playAudio(isSuccess) {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = isSuccess ? 'sine' : 'sawtooth';
            osc.frequency.setValueAtTime(isSuccess ? 600 : 200, ctx.currentTime);
            if (isSuccess) osc.frequency.exponentialRampToValueAtTime(900, ctx.currentTime + 0.15);
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.3);
        } catch(e) {}
    }

    // 5. Show Alert Toast
    function displayAlert(type, message) {
        const alertBox = document.getElementById('punchFeedbackAlert');
        if (!alertBox) return;
        alertBox.className = `alert alert-${type} small text-start rounded-3 mb-3`;
        alertBox.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'} me-2"></i> ${message}`;
        alertBox.classList.remove('d-none');
        setTimeout(() => {
            alertBox.classList.add('d-none');
        }, 7000);
    }

    // 6. Update Button Disabled States
    function refreshButtons() {
        const inBtn = document.getElementById('punchInBtn');
        const outBtn = document.getElementById('punchOutBtn');
        const statusBox = document.getElementById('statusBannerBox');
        const statusText = document.getElementById('statusLabelText');

        if (currentStatus === 'IN') {
            if (inBtn) inBtn.classList.add('disabled-action');
            if (outBtn) outBtn.classList.remove('disabled-action');
            if (statusBox) {
                statusBox.className = 'status-pill-box status-in';
            }
            if (statusText) statusText.textContent = 'Currently Checked IN';
            startTimer();
        } else {
            if (inBtn) inBtn.classList.remove('disabled-action');
            if (outBtn) outBtn.classList.add('disabled-action');
            if (statusBox) {
                statusBox.className = 'status-pill-box status-out';
            }
            if (statusText) statusText.textContent = 'Currently Checked OUT';
            stopTimer();
        }
    }

    // Haversine Distance in JS (Meters)
    function calcDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return Math.round(R * c);
    }

    function updateGpsUI() {
        const dot = document.getElementById('gpsDotIndicator');
        const text = document.getElementById('gpsStatusText');
        if (!dot || !text) return;

        if (!geofenceEnabled) {
            dot.className = 'fa-solid fa-location-dot text-success flex-shrink-0';
            text.className = 'small fw-semibold text-success text-truncate';
            text.textContent = userLat ? `GPS Active (${userLat.toFixed(4)}, ${userLon.toFixed(4)})` : 'GPS Location Ready';
            return;
        }

        if (userLat !== null && userLon !== null && siteLat !== null && siteLon !== null) {
            userDistance = calcDistance(userLat, userLon, siteLat, siteLon);
            isWithinGeofence = (userDistance <= siteRadius);

            if (isWithinGeofence) {
                dot.className = 'fa-solid fa-circle-check text-success flex-shrink-0';
                text.className = 'small fw-semibold text-success text-truncate';
                text.innerHTML = `<span>Within Site (~${userDistance}m away)</span> &bull; <span class="badge bg-success py-0" style="font-size: 0.65rem;">Ready to Punch</span>`;
            } else {
                dot.className = 'fa-solid fa-circle-xmark text-danger flex-shrink-0';
                text.className = 'small fw-semibold text-danger text-truncate';
                text.innerHTML = `<span>Outside Site (~${userDistance}m away)</span> &bull; <span class="badge bg-danger py-0" style="font-size: 0.65rem;">Must be &le; ${siteRadius}m</span>`;
            }
        }
    }

    window.refreshGpsLocation = function() {
        const text = document.getElementById('gpsStatusText');
        const dot = document.getElementById('gpsDotIndicator');
        if (text) text.textContent = 'Locating GPS position...';
        if (dot) dot.className = 'spinner-grow spinner-grow-sm text-warning flex-shrink-0';

        if (!navigator.geolocation) {
            if (text) text.textContent = 'GPS not supported on this browser';
            if (dot) dot.className = 'fa-solid fa-triangle-exclamation text-warning flex-shrink-0';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                userLat = pos.coords.latitude;
                userLon = pos.coords.longitude;
                userAccuracy = pos.coords.accuracy;
                updateGpsUI();
            },
            function(err) {
                if (text) {
                    if (err.code === 1) {
                        text.innerHTML = '<span class="text-danger">Location denied. Please allow GPS.</span>';
                    } else {
                        text.innerHTML = '<span class="text-muted">GPS signal weak. Retrying...</span>';
                    }
                }
                if (dot) dot.className = 'fa-solid fa-triangle-exclamation text-danger flex-shrink-0';
            },
            { enableHighAccuracy: true, timeout: 8000, maximumAge: 10000 }
        );
    };

    // Auto-track GPS on page load
    if (navigator.geolocation) {
        window.refreshGpsLocation();
        try {
            navigator.geolocation.watchPosition(
                function(pos) {
                    userLat = pos.coords.latitude;
                    userLon = pos.coords.longitude;
                    userAccuracy = pos.coords.accuracy;
                    updateGpsUI();
                },
                function(err) {},
                { enableHighAccuracy: true, maximumAge: 15000 }
            );
        } catch(e) {}
    }

    function showGeofenceModal(title, subtitle, distText, siteName) {
        const modalEl = document.getElementById('geofenceErrorModal');
        if (!modalEl || typeof bootstrap === 'undefined') return;

        if (title) document.getElementById('geofenceModalTitle').textContent = title;
        if (subtitle) document.getElementById('geofenceModalSubtitle').textContent = subtitle;
        if (distText) document.getElementById('geoCurrentDist').textContent = distText;
        if (siteName) document.getElementById('geoSiteName').textContent = siteName;

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    // 7. Execute Punch Action with Selfie Capture
    window.executePunch = function(type) {
        if (type === currentStatus) {
            displayAlert('warning', `You are already punched ${type}.`);
            return;
        }

        const btn = (type === 'IN') ? document.getElementById('punchInBtn') : document.getElementById('punchOutBtn');
        const origContent = btn ? btn.innerHTML : '';
        if (btn) {
            btn.innerHTML = '<div class="d-flex align-items-center gap-2"><span class="spinner-border spinner-border-sm"></span> <span>Capturing Face & GPS...</span></div>';
            btn.classList.add('disabled-action');
        }

        // Snap selfie photo snapshot from camera
        const selfiePhoto = snapSelfiePhoto();

        // Fast GPS check and send request
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    userLat = pos.coords.latitude;
                    userLon = pos.coords.longitude;
                    userAccuracy = pos.coords.accuracy;
                    updateGpsUI();

                    // Client-Side Geofence Verification Check
                    if (geofenceEnabled && siteLat !== null && siteLon !== null) {
                        userDistance = calcDistance(userLat, userLon, siteLat, siteLon);
                        if (userDistance > siteRadius) {
                            const distStr = userDistance >= 1000 ? (userDistance / 1000).toFixed(2) + ' km away' : userDistance + ' meters away';
                            showGeofenceModal(
                                'You Are Not in the Site Location!',
                                `Punch ${type} is blocked because you are outside your assigned work site.`,
                                distStr
                            );
                            displayAlert('danger', `You are not in the site location (${distStr}). Punch is strictly allowed only within ${siteRadius}m.`);
                            if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
                            playAudio(false);
                            if (btn) {
                                btn.innerHTML = origContent;
                                btn.classList.remove('disabled-action');
                            }
                            return;
                        }
                    }

                    sendRequest(type, userLat, userLon, selfiePhoto, btn, origContent);
                },
                function(err) {
                    if (geofenceEnabled) {
                        showGeofenceModal(
                            'GPS Location Access Required',
                            'Please allow GPS location permission on your phone so we can verify you are at the work site.',
                            'GPS Not Granted'
                        );
                        displayAlert('danger', 'Location permission required to verify work site presence.');
                        if (btn) {
                            btn.innerHTML = origContent;
                            btn.classList.remove('disabled-action');
                        }
                        return;
                    }
                    sendRequest(type, userLat, userLon, selfiePhoto, btn, origContent);
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 3000 }
            );
        } else {
            if (geofenceEnabled) {
                showGeofenceModal(
                    'GPS Not Supported',
                    'Your browser does not support GPS location services.',
                    'Unavailable'
                );
                displayAlert('danger', 'GPS location is required to punch attendance at this site.');
                if (btn) {
                    btn.innerHTML = origContent;
                    btn.classList.remove('disabled-action');
                }
                return;
            }
            sendRequest(type, userLat, userLon, selfiePhoto, btn, origContent);
        }
    };

    function sendRequest(type, lat, lng, photoData, btn, origContent) {
        const formData = new FormData();
        formData.append('punch_type', type);
        if (lat !== null && lat !== undefined) formData.append('latitude', lat);
        if (lng !== null && lng !== undefined) formData.append('longitude', lng);
        if (photoData) formData.append('photo_data', photoData);

        fetch(recordUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (btn) {
                btn.innerHTML = origContent;
                btn.classList.remove('disabled-action');
            }

            if (data.success) {
                currentStatus = type;
                if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                playAudio(true);
                displayAlert('success', data.message);

                // Show captured snapshot preview on screen
                if (photoData && lastSnapEl) {
                    lastSnapEl.src = photoData;
                    lastSnapEl.classList.remove('d-none');
                    setTimeout(() => {
                        lastSnapEl.classList.add('d-none');
                    }, 4000);
                }

                // Update subtext
                const subtext = document.getElementById('lastPunchSubtext');
                if (subtext) {
                    const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                    subtext.innerHTML = `Last Punch: <strong>${type}</strong> at ${timeStr}`;
                }

                if (data.worked_seconds !== undefined) {
                    workedSeconds = data.worked_seconds;
                }

                refreshButtons();

                // Append to history list with thumbnail
                const logsCont = document.getElementById('historyLogsContainer');
                const countBadge = document.getElementById('historyCount');
                const emptyMsg = document.getElementById('emptyHistoryMsg');
                if (emptyMsg) emptyMsg.remove();

                if (logsCont) {
                    let listGroup = logsCont.querySelector('.list-group');
                    if (!listGroup) {
                        listGroup = document.createElement('div');
                        listGroup.className = 'list-group list-group-flush';
                        logsCont.appendChild(listGroup);
                    }
                    const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                    const item = document.createElement('div');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center px-2 py-2';
                    const photoThumb = photoData ? `<img src="${photoData}" class="rounded-circle object-fit-cover me-2 border shadow-sm" style="width: 26px; height: 26px;">` : '';
                    item.innerHTML = `<div class="d-flex align-items-center">
                        ${photoThumb}
                        <span class="badge ${type === 'IN' ? 'bg-success' : 'bg-danger'} me-2">${type}</span>
                        <span class="small font-monospace">${timeStr}</span>
                    </div>
                    <span class="badge bg-light text-muted border small" style="font-size: 0.7rem;">Recent</span>`;
                    listGroup.insertBefore(item, listGroup.firstChild);
                }

                if (countBadge) {
                    countBadge.textContent = parseInt(countBadge.textContent || '0') + 1;
                }
            } else {
                if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
                playAudio(false);
                displayAlert('danger', data.message || 'Unable to record punch.');

                // If backend rejected due to geofence or location:
                if (data.message && (data.message.includes('Punch Rejected') || data.message.includes('away from') || data.message.includes('Location permission'))) {
                    const distStr = data.distance ? (data.distance >= 1000 ? (data.distance/1000).toFixed(2) + ' km away' : Math.round(data.distance) + ' m away') : (userDistance ? (userDistance >= 1000 ? (userDistance/1000).toFixed(2) + ' km away' : userDistance + ' m away') : 'Outside perimeter');
                    showGeofenceModal(
                        'You Are Not in the Site Location!',
                        data.message,
                        distStr
                    );
                }

                refreshButtons();
            }
        })
        .catch(function(err) {
            if (btn) {
                btn.innerHTML = origContent;
                btn.classList.remove('disabled-action');
            }
            displayAlert('danger', 'Network error. Please check connection and try again.');
        });
    }

    // Initialize immediately
    tickClock();
    setInterval(tickClock, 1000);
    initCamera();
    refreshButtons();
    if (currentStatus === 'IN') {
        startTimer();
    }
})();
</script>
