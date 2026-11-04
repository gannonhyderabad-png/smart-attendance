<?php
$employee = $employee ?? [];
$latestPunch = $latestPunch ?? null;
$todayPunches = $todayPunches ?? [];
$workedSeconds = $workedSeconds ?? 0;
$currentStatus = $currentStatus ?? 'OUT';
$todayLeave = $todayLeave ?? null;
$appliedLeaveRanges = $appliedLeaveRanges ?? [];
$punchedDates = $punchedDates ?? [];
$myLeaves = $myLeaves ?? [];
$selfReport = $selfReport ?? [];

$hasActiveLeaveToday = !empty($todayLeave);
$hasPunchesToday = !empty($todayPunches);

// Calculate Today's Summary values for display matching Image 1
$firstInTime = null;
$lastOutTime = null;
foreach ($todayPunches as $p) {
    if ($p['punch_type'] === 'IN' && $firstInTime === null) {
        $firstInTime = $p['punch_time'];
    }
    if ($p['punch_type'] === 'OUT') {
        $lastOutTime = $p['punch_time'];
    }
}

$firstInFormatted = $firstInTime ? date('h:i A', strtotime($firstInTime)) : '--';
$lastOutFormatted = $lastOutTime ? date('h:i A', strtotime($lastOutTime)) : '--';

$durH = floor($workedSeconds / 3600);
$durM = floor(($workedSeconds % 3600) / 60);
$durationFormatted = sprintf('%02dh %02dm', $durH, $durM);

$initDaily = $selfReport['daily'] ?? [];
$initMonthly = $selfReport['monthly'] ?? [];
$initMSum = $initMonthly['summary'] ?? [
    'present_days' => 0,
    'absent_days' => 0,
    'leave_days' => 0,
    'od_days' => 0,
    'formatted_total_duration' => '00:00:00',
    'total_hours' => 0,
    'attendance_rate' => 0
];
$empCodeUrlEnc = rawurlencode($employee['employee_code'] ?? '');
$initMonthVal = $initMonthly['month'] ?? date('Y-m');
$initDateVal = $initDaily['date'] ?? date('Y-m-d');
?>

<style>
/* Modern Smartphone App UI Styles (Image 1 Theme) */
.mobile-profile-wrap {
    max-width: 440px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, "Plus Jakarta Sans", "Segoe UI", Roboto, sans-serif;
}
.mobile-profile-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 28px;
    box-shadow: 0 20px 45px -15px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}
.profile-top-bar {
    padding: 1.25rem 1.25rem 0.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.profile-bar-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #334155;
    text-decoration: none;
    transition: all 0.2s ease;
}
.profile-bar-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.hero-avatar-wrap {
    width: 86px;
    height: 86px;
    margin: 0.5rem auto 0.75rem;
    border-radius: 50%;
    padding: 3px;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    position: relative;
}
.hero-avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}
.hero-avatar-initial {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    color: #ffffff;
    font-size: 2rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
}
.hero-name {
    font-size: 1.35rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.3px;
    margin-bottom: 0.15rem;
}
.hero-designation {
    font-size: 0.85rem;
    font-weight: 500;
    color: #64748b;
    margin-bottom: 0.45rem;
}
.hero-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
}
.hero-status-online {
    background-color: #ecfdf5;
    color: #059669;
}
.hero-status-offline {
    background-color: #f8fafc;
    color: #64748b;
    border: 1px solid #e2e8f0;
}
.hero-status-leave {
    background-color: #fffbeb;
    color: #d97706;
}
.status-dot-pulse {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: currentColor;
    display: inline-block;
}

/* 4 Quick App-Style Navigation Tabs (Exact Image 1) */
.app-tabs-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    padding: 1.25rem 1.25rem 1rem;
}
.app-tab-btn {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 0.85rem 0.4rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}
.app-tab-btn:active {
    transform: scale(0.96);
}
.app-tab-btn.active {
    background: #ffffff;
    border-color: #00a884;
    box-shadow: 0 4px 14px rgba(0, 168, 132, 0.15);
}
.app-tab-btn.active .app-tab-icon {
    color: #00a884;
    transform: translateY(-1px);
}
.app-tab-btn.active .app-tab-label {
    color: #0f172a;
    font-weight: 700;
}
.app-tab-icon {
    font-size: 1.2rem;
    color: #64748b;
    margin-bottom: 0.35rem;
    transition: all 0.2s;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.app-tab-label {
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748b;
    display: block;
}

/* Today's Summary Card (Exact Image 1) */
.today-summary-card {
    background: #ffffff;
    border: 1px solid #edf2f7;
    border-radius: 20px;
    padding: 1.15rem 1.25rem;
    margin: 0 1.25rem 1.25rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}
.summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.65rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.summary-row:last-child {
    border-bottom: none;
    padding-bottom: 0.2rem;
}
.summary-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #334155;
}
.summary-val {
    font-size: 0.88rem;
    font-weight: 700;
    color: #0f172a;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

/* Big Action Button (Exact Image 1: Check Out / Check In in emerald/teal) */
.btn-primary-punch {
    background: #00a884 !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 18px !important;
    font-weight: 700 !important;
    font-size: 1.08rem !important;
    padding: 1rem 1.5rem !important;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 10px 22px -5px rgba(0, 168, 132, 0.42);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    width: 100%;
}
.btn-primary-punch:hover {
    background: #009373 !important;
    color: #ffffff !important;
}
.btn-primary-punch:active {
    transform: scale(0.98);
}
.btn-primary-punch.disabled-action {
    opacity: 0.55 !important;
    pointer-events: none !important;
    box-shadow: none !important;
}
</style>

<div class="mobile-profile-wrap">
    
    <!-- Outer Card Shell -->
    <div class="mobile-profile-card">
        
        <!-- Top App Bar (Back Arrow, Title, Options) -->
        <div class="profile-top-bar">
            <a href="javascript:void(0)" class="profile-bar-btn" onclick="history.back()" title="Go Back">
                <i class="fa-solid fa-arrow-left" style="font-size: 0.85rem;"></i>
            </a>
            <h6 class="fw-bold text-dark mb-0 font-sans" style="font-size: 0.95rem;">Employee Profile</h6>
            <button type="button" class="profile-bar-btn" onclick="location.reload()" title="Refresh Attendance">
                <i class="fa-solid fa-ellipsis" style="font-size: 0.85rem;"></i>
            </button>
        </div>

        <!-- Hero Profile Section -->
        <div class="text-center px-3 pt-1 pb-2">
            <div class="hero-avatar-wrap">
                <?php if (!empty($employee['photo'])): ?>
                    <img src="<?= uploaded_url($employee['photo']) ?>" class="hero-avatar-img" alt="<?= e($employee['name']) ?>">
                <?php else: ?>
                    <div class="hero-avatar-initial"><?= strtoupper(substr($employee['name'], 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            
            <h4 class="hero-name"><?= e($employee['name']) ?></h4>
            <div class="hero-designation"><?= e($employee['designation'] ?? 'Staff Member') ?></div>
            
            <!-- Live Status Indicator Pill (Exact Image 1: • Online) -->
            <div id="heroLiveStatusBadge">
                <?php if ($hasActiveLeaveToday): ?>
                    <span class="hero-status-pill hero-status-leave">
                        <span class="status-dot-pulse"></span> On Leave Today
                    </span>
                <?php elseif ($currentStatus === 'IN'): ?>
                    <span class="hero-status-pill hero-status-online">
                        <span class="status-dot-pulse"></span> Online
                    </span>
                <?php else: ?>
                    <span class="hero-status-pill hero-status-offline">
                        <span class="status-dot-pulse"></span> Offline
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- 4 Quick App Tabs Navigation (Exact Image 1) -->
        <div class="app-tabs-grid">
            <!-- 1. Profile Tab -->
            <div class="app-tab-btn" id="appTabBtnProfile" onclick="switchAppTab('profile')">
                <div class="app-tab-icon"><i class="fa-regular fa-user"></i></div>
                <span class="app-tab-label">Profile</span>
            </div>

            <!-- 2. Attendance Tab (Active by default) -->
            <div class="app-tab-btn active" id="appTabBtnAttendance" onclick="switchAppTab('attendance')">
                <div class="app-tab-icon"><i class="fa-regular fa-clock"></i></div>
                <span class="app-tab-label">Attendance</span>
            </div>

            <!-- 3. Leave Entry Tab -->
            <div class="app-tab-btn" id="appTabBtnLeave" onclick="switchAppTab('leave')">
                <div class="app-tab-icon"><i class="fa-regular fa-file-lines"></i></div>
                <span class="app-tab-label">Leave Entry</span>
            </div>

            <!-- 4. Summary Tab -->
            <div class="app-tab-btn" id="appTabBtnSummary" onclick="switchAppTab('summary')">
                <div class="app-tab-icon"><i class="fa-solid fa-bars-staggered"></i></div>
                <span class="app-tab-label">Summary</span>
            </div>
        </div>

        <!-- ================= TAB CONTENT PANELS ================= -->

        <!-- 1. ATTENDANCE PANEL (Default View) -->
        <div id="tabPanelAttendance">
            
            <!-- TODAY'S SUMMARY CARD (Exact Image 1) -->
            <div class="today-summary-card">
                <h6 class="fw-bold text-dark mb-3" style="font-size: 0.95rem;">Today's Summary</h6>

                <!-- Row 1: Check In -->
                <div class="summary-row">
                    <div class="summary-label">
                        <i class="fa-solid fa-circle-check text-success" style="font-size: 1.05rem;"></i>
                        <span>Check In</span>
                    </div>
                    <span class="summary-val" id="summaryCheckInDisplay"><?= $firstInFormatted ?></span>
                </div>

                <!-- Row 2: Check Out -->
                <div class="summary-row">
                    <div class="summary-label">
                        <i class="fa-regular fa-circle-dot text-primary" style="font-size: 1.05rem;"></i>
                        <span>Check Out</span>
                    </div>
                    <span class="summary-val" id="summaryCheckOutDisplay"><?= $lastOutFormatted ?></span>
                </div>

                <!-- Row 3: Work Duration -->
                <div class="summary-row">
                    <div class="summary-label">
                        <i class="fa-regular fa-clock text-primary" style="font-size: 1.05rem;"></i>
                        <span>Work Duration</span>
                    </div>
                    <span class="summary-val" id="summaryDurationDisplay"><?= $durationFormatted ?></span>
                </div>

                <!-- Row 4: Location -->
                <div class="summary-row">
                    <div class="summary-label">
                        <i class="fa-solid fa-location-dot text-primary" style="font-size: 1.05rem;"></i>
                        <span>Location</span>
                    </div>
                    <span class="summary-val text-truncate" style="max-width: 170px;" title="<?= e($employee['site'] ?? 'Hyderabad') ?>">
                        <?= e($employee['site'] ?? 'Hyderabad') ?>
                    </span>
                </div>
            </div>

            <!-- PRIMARY PUNCH ACTION BUTTON (Exact Image 1) -->
            <div class="px-4 mb-3">
                <?php if ($hasActiveLeaveToday): ?>
                    <button type="button" 
                            id="punchInBtn" 
                            class="btn-primary-punch"
                            style="background: #94a3b8 !important; cursor: not-allowed;"
                            onclick="window.showConflictModal('Punch Disabled: Leave Active', 'Cannot punch attendance while leave is applied for today.', 'You have already applied for <?= addslashes(e($todayLeave['leave_type'])) ?> on today\'s date (<?= date('d M Y') ?>).')">
                        <span>On Leave Today</span> <i class="fa-solid fa-ban ms-1"></i>
                    </button>
                    <button type="button" id="punchOutBtn" class="d-none"></button>
                <?php elseif ($currentStatus === 'IN'): ?>
                    <!-- Check Out Button (Exact Image 1: Check Out [->]) -->
                    <button type="button" 
                            id="punchOutBtn" 
                            class="btn-primary-punch"
                            onclick="executePunch('OUT')">
                        <span>Check Out</span> <i class="fa-solid fa-arrow-right-from-bracket ms-1"></i>
                    </button>
                    <button type="button" id="punchInBtn" class="d-none" onclick="executePunch('IN')"></button>
                <?php else: ?>
                    <!-- Check In Button (Exact Image 1 Style) -->
                    <button type="button" 
                            id="punchInBtn" 
                            class="btn-primary-punch"
                            onclick="executePunch('IN')">
                        <span>Check In</span> <i class="fa-solid fa-arrow-right-to-bracket ms-1"></i>
                    </button>
                    <button type="button" id="punchOutBtn" class="d-none" onclick="executePunch('OUT')"></button>
                <?php endif; ?>
            </div>

            <!-- Feedback Alert Toast -->
            <div class="px-4">
                <div id="punchFeedbackAlert" class="alert d-none small text-start rounded-3 mb-3" role="alert"></div>
            </div>

            <!-- Live Camera Face Verification Card -->
            <div class="px-4 mb-3">
                <div class="card border rounded-4 bg-light p-3 overflow-hidden text-center position-relative">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="small fw-bold text-dark">
                            <i class="fa-solid fa-camera text-primary me-1"></i> Face Verification
                        </div>
                        <span id="cameraBadgeStatus" class="badge bg-warning-subtle text-warning border border-warning-subtle py-1 px-2" style="font-size: 0.68rem;">
                            <span class="spinner-grow spinner-grow-sm me-1" style="width: 8px; height: 8px;"></span> Initializing...
                        </span>
                    </div>

                    <div class="position-relative mx-auto rounded-3 overflow-hidden bg-black shadow-sm" style="max-width: 280px; width: 100%; aspect-ratio: 4/3;">
                        <video id="punchCameraVideo" autoplay playsinline muted class="w-100 h-100 object-fit-cover" style="transform: scaleX(-1);"></video>
                        <canvas id="punchPhotoCanvas" class="d-none"></canvas>
                        <div id="cameraFlashOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white" style="opacity: 0; pointer-events: none; transition: opacity 0.25s ease; z-index: 10;"></div>
                        
                        <!-- Face Target Guide Oval -->
                        <div class="position-absolute top-50 start-50 translate-middle border border-2 border-white rounded-pill opacity-50" style="width: 120px; height: 150px; pointer-events: none; box-shadow: 0 0 0 9999px rgba(0,0,0,0.25);"></div>

                        <!-- Snapshot Preview -->
                        <img id="lastSnapPreview" src="" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover d-none" style="z-index: 5;" alt="Punch Selfie">
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-2 pt-1">
                        <small class="text-muted" style="font-size: 0.7rem;">
                            <i class="fa-solid fa-shield-halved text-success me-1"></i> Auto-snap selfie on punch
                        </small>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" onclick="window.flipCamera()" id="flipCameraBtn" title="Switch Camera" style="font-size: 0.68rem;">
                            <i class="fa-solid fa-camera-rotate me-1"></i> Flip
                        </button>
                    </div>
                </div>
            </div>

            <!-- Site & GPS Geofencing Status -->
            <div class="px-4 mb-3">
                <div class="p-2 px-3 rounded-3 border bg-light small text-start">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 80%;">
                            <span id="gpsDotIndicator" class="spinner-grow spinner-grow-sm text-warning flex-shrink-0" style="width: 8px; height: 8px;"></span>
                            <span id="gpsStatusText" class="small fw-semibold text-muted text-truncate" style="font-size: 0.74rem;">Acquiring GPS location...</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 flex-shrink-0" onclick="window.refreshGpsLocation()" title="Refresh GPS">
                            <i class="fa-solid fa-location-crosshairs text-primary" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Today's Punch History Accordion -->
            <div class="px-4 pb-4">
                <div class="accordion accordion-flush text-start border rounded-3" id="historyAccordion">
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
                                                <span class="badge bg-light text-muted border small" style="font-size: 0.68rem;">
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
            </div>

            <!-- Hidden digital clock and timer hooks for JS compatibility -->
            <div class="d-none">
                <span id="liveClockDisplay"></span>
                <span id="liveDateDisplay"></span>
                <div id="workingDurationBox">
                    <span id="liveDurationTimer"></span>
                </div>
                <div id="statusBannerBox">
                    <span id="statusLabelText"></span>
                    <span id="lastPunchSubtext"></span>
                </div>
            </div>
        </div>

        <!-- 2. LEAVE ENTRY PANEL -->
        <div id="tabPanelLeave" class="d-none px-4 pb-4">
            <div class="card border rounded-4 p-3 bg-white shadow-sm text-start mb-3">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                        <i class="fa-solid fa-calendar-plus fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Apply Leave / OD</h6>
                        <small class="text-muted" style="font-size: 0.72rem;">Submit for Dept Head & HR Approval</small>
                    </div>
                </div>

                <div id="leaveFormAlert" class="alert d-none py-2 px-3 small rounded-3 mb-3"></div>

                <div id="punchedTodayLeaveBanner" class="alert alert-warning border border-warning-subtle rounded-3 p-2 small mb-3 text-start <?= $hasPunchesToday ? '' : 'd-none' ?>">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fa-solid fa-clock-rotate-left text-warning"></i>
                        <span class="fw-bold text-dark">Attendance Punched Today</span>
                    </div>
                    <div class="text-muted" style="font-size: 0.72rem;">
                        You have already recorded attendance punches today (<?= date('d M Y') ?>). Same-day Punch and Leave are not allowed.
                    </div>
                </div>

                <form id="mobileLeaveForm" onsubmit="window.submitMobileLeave(event)" enctype="multipart/form-data">
                    <input type="hidden" name="employee_code" value="<?= e($employee['employee_code'] ?? '') ?>">
                    
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted mb-1">Application Type <span class="text-danger">*</span></label>
                        <select name="leave_type" id="mLeaveType" class="form-select form-select-sm rounded-3 fw-semibold text-dark" required onchange="window.toggleTargetSiteField()">
                            <option value="Casual Leave">Casual Leave (CL)</option>
                            <option value="Sick Leave">Sick Leave (SL)</option>
                            <option value="Privilege Leave">Privilege / Paid Leave (PL)</option>
                            <option value="On Duty">On Duty / Outdoor Duty (OD)</option>
                        </select>
                    </div>

                    <!-- Target Site Field (Shown for OD) -->
                    <div class="mb-2 d-none" id="mTargetSiteGroup">
                        <label class="form-label small fw-bold text-muted mb-1">Target Work Site / Client <span class="text-danger">*</span></label>
                        <input type="text" name="target_site" id="mTargetSite" class="form-control form-control-sm rounded-3" placeholder="e.g. Hyderabad Metro Site, Client Office">
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted mb-1">From Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="mStartDate" class="form-control form-control-sm rounded-3" required onchange="window.onLeaveStartDateChange()">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted mb-1">To Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="mEndDate" class="form-control form-control-sm rounded-3" required onchange="window.onLeaveEndDateChange()">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted mb-1">Reason / Notes <span class="text-danger">*</span></label>
                        <textarea name="reason" id="mReason" rows="2" class="form-control form-control-sm rounded-3" placeholder="Brief reason for leave application..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Attachment (Optional)</label>
                        <input type="file" name="attachment" id="mAttachment" class="form-control form-control-sm rounded-3" accept=".pdf,image/*">
                        <div id="attachmentError" class="text-danger small mt-1 d-none" style="font-size: 0.7rem;"></div>
                    </div>

                    <button type="submit" id="btnSubmitLeave" class="btn btn-primary btn-sm rounded-pill w-100 py-2 fw-bold">
                        <i class="fa-solid fa-paper-plane me-1"></i> Submit Leave Application
                    </button>
                </form>
            </div>

            <!-- Recent Submitted Leaves by Employee -->
            <div class="card border rounded-4 p-3 bg-white shadow-sm text-start">
                <h6 class="fw-bold text-dark mb-2" style="font-size: 0.85rem;">My Recent Leaves (<?= count($myLeaves) ?>)</h6>
                <?php if (empty($myLeaves)): ?>
                    <div class="text-muted small py-2 text-center">No past leave applications.</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($myLeaves as $lv): ?>
                            <div class="list-group-item px-0 py-2 border-bottom border-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-light text-dark border fw-bold"><?= e($lv['leave_type']) ?></span>
                                    <span class="badge <?= ($lv['status'] ?? '') === 'APPROVED' ? 'bg-success' : (($lv['status'] ?? '') === 'REJECTED' ? 'bg-danger' : 'bg-warning text-dark') ?> rounded-pill" style="font-size: 0.65rem;">
                                        <?= e($lv['status'] ?? 'PENDING') ?>
                                    </span>
                                </div>
                                <div class="small text-muted mt-1" style="font-size: 0.72rem;">
                                    <?= date('d M Y', strtotime($lv['start_date'])) ?> to <?= date('d M Y', strtotime($lv['end_date'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3. SUMMARY PANEL (Daily & Monthly Attendance Summary) -->
        <div id="tabPanelSummary" class="d-none px-4 pb-4 text-start">
            <!-- Mode Switcher: Monthly vs Daily -->
            <div class="btn-group w-100 mb-3 p-1 bg-light rounded-pill border" role="group">
                <button type="button" id="tabBtnMonthly" class="btn btn-sm btn-primary rounded-pill fw-bold py-1" onclick="window.switchSelfReportTab('monthly')">
                    <i class="fa-solid fa-calendar-days me-1"></i> Monthly Summary
                </button>
                <button type="button" id="tabBtnDaily" class="btn btn-sm btn-light rounded-pill fw-semibold text-muted py-1" onclick="window.switchSelfReportTab('daily')">
                    <i class="fa-solid fa-calendar-day me-1"></i> Daily Status
                </button>
            </div>

            <!-- Monthly Panel -->
            <div id="selfMonthlyPanel">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <div class="flex-grow-1">
                        <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.7rem;">Select Month</label>
                        <input type="month" id="selfMonthInput" class="form-control form-control-sm rounded-3 fw-semibold"
                               value="<?= e($initMonthVal) ?>" max="<?= date('Y-m') ?>"
                               onchange="window.loadSelfAttendanceData('monthly')">
                    </div>
                    <div class="text-end">
                        <label class="form-label small fw-bold text-muted mb-1 d-block" style="font-size: 0.7rem;">Download</label>
                        <div class="btn-group btn-group-sm">
                            <a id="btnDownloadMonthlyExcel"
                               href="<?= e(public_url('p/' . $empCodeUrlEnc . '/download-report?mode=monthly&month=' . $initMonthVal . '&format=excel')) ?>"
                               class="btn btn-success fw-semibold px-2 rounded-start-3" title="Download Excel">
                                <i class="fa-solid fa-file-excel me-1"></i>Excel
                            </a>
                            <a id="btnDownloadMonthlyCsv"
                               href="<?= e(public_url('p/' . $empCodeUrlEnc . '/download-report?mode=monthly&month=' . $initMonthVal . '&format=csv')) ?>"
                               class="btn btn-outline-secondary fw-semibold px-2 rounded-end-3" title="Download CSV">
                                <i class="fa-solid fa-file-csv me-1"></i>CSV
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Monthly KPI Summary Cards -->
                <div class="row g-2 mb-3 text-center">
                    <div class="col-3">
                        <div class="p-2 rounded-3 bg-success-subtle border border-success-subtle h-100">
                            <div class="fw-bold text-success fs-6" id="mSumPresent"><?= (int)($initMSum['present_days'] ?? 0) ?></div>
                            <div class="text-muted" style="font-size: 0.65rem;">Present</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded-3 bg-danger-subtle border border-danger-subtle h-100">
                            <div class="fw-bold text-danger fs-6" id="mSumAbsent"><?= (int)($initMSum['absent_days'] ?? 0) ?></div>
                            <div class="text-muted" style="font-size: 0.65rem;">Absent</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded-3 bg-primary-subtle border border-primary-subtle h-100">
                            <div class="fw-bold text-primary fs-6" id="mSumLeave"><?= (int)(($initMSum['leave_days'] ?? 0) + ($initMSum['od_days'] ?? 0)) ?></div>
                            <div class="text-muted" style="font-size: 0.65rem;">Leave/OD</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded-3 bg-light border h-100">
                            <div class="fw-bold text-dark fs-6" id="mSumHours"><?= e((string)($initMSum['total_hours'] ?? 0)) ?>h</div>
                            <div class="text-muted" style="font-size: 0.65rem;"><span id="mSumRate"><?= e((string)($initMSum['attendance_rate'] ?? 0)) ?></span>%</div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Day-by-Day Status Table -->
                <div class="border rounded-3 overflow-hidden">
                    <div class="bg-light px-2 py-1 border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-muted text-uppercase" style="font-size: 0.68rem;" id="mTableTitle">
                            <?= e($initMonthly['month_label'] ?? date('F Y')) ?> Log
                        </span>
                        <span class="text-muted font-monospace" style="font-size: 0.68rem;" id="mTotalDurationLabel">
                            Total: <?= e($initMSum['formatted_total_duration'] ?? '00:00:00') ?>
                        </span>
                    </div>
                    <div style="max-height: 220px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.74rem;">
                            <thead class="table-light sticky-top">
                                <tr class="text-muted" style="font-size: 0.66rem;">
                                    <th class="ps-2">Date</th>
                                    <th>Status</th>
                                    <th>IN / OUT</th>
                                    <th class="pe-2 text-end">Hrs</th>
                                </tr>
                            </thead>
                            <tbody id="selfMonthlyTableBody">
                                <?php foreach (($initMonthly['records'] ?? []) as $rec): ?>
                                    <tr>
                                        <td class="ps-2 text-nowrap">
                                            <div class="fw-semibold text-dark"><?= e($rec['date_short']) ?></div>
                                            <div class="text-muted" style="font-size: 0.64rem;"><?= e($rec['day_name']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?= e($rec['badge_class']) ?> border rounded-pill px-2 py-0" style="font-size: 0.64rem;">
                                                <?= e($rec['status_label']) ?>
                                            </span>
                                        </td>
                                        <td class="font-monospace text-nowrap" style="font-size: 0.66rem;">
                                            <?php if (!empty($rec['first_in']) || !empty($rec['last_out'])): ?>
                                                <span class="text-success"><?= e($rec['first_in'] ?? '—') ?></span>
                                                <span class="text-muted">/</span>
                                                <span class="text-danger"><?= e($rec['last_out'] ?? '—') ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-2 text-end font-monospace fw-semibold">
                                            <?= ($rec['hours'] ?? 0) > 0 ? e((string)$rec['hours']) . 'h' : '—' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Daily Panel -->
            <div id="selfDailyPanel" class="d-none">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <div class="flex-grow-1">
                        <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.7rem;">Select Date</label>
                        <input type="date" id="selfDateInput" class="form-control form-control-sm rounded-3 fw-semibold"
                               value="<?= e($initDateVal) ?>" max="<?= date('Y-m-d') ?>"
                               onchange="window.loadSelfAttendanceData('daily')">
                    </div>
                    <div class="text-end">
                        <label class="form-label small fw-bold text-muted mb-1 d-block" style="font-size: 0.7rem;">Download</label>
                        <div class="btn-group btn-group-sm">
                            <a id="btnDownloadDailyExcel"
                               href="<?= e(public_url('p/' . $empCodeUrlEnc . '/download-report?mode=daily&date=' . $initDateVal . '&format=excel')) ?>"
                               class="btn btn-success fw-semibold px-2 rounded-start-3" title="Download Excel">
                                <i class="fa-solid fa-file-excel me-1"></i>Excel
                            </a>
                            <a id="btnDownloadDailyCsv"
                               href="<?= e(public_url('p/' . $empCodeUrlEnc . '/download-report?mode=daily&date=' . $initDateVal . '&format=csv')) ?>"
                               class="btn btn-outline-secondary fw-semibold px-2 rounded-end-3" title="Download CSV">
                                <i class="fa-solid fa-file-csv me-1"></i>CSV
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Selected Day Summary Card -->
                <div class="p-3 rounded-3 bg-light border mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <div class="fw-bold text-dark small" id="dSumDateLabel">
                                <?= e(($initDaily['date_formatted'] ?? date('d M Y')) . ' (' . ($initDaily['day_name'] ?? date('D')) . ')') ?>
                            </div>
                            <div class="text-muted" style="font-size: 0.68rem;" id="dSumNote">
                                <?= !empty($initDaily['note']) ? e($initDaily['note']) : 'Daily Attendance Summary' ?>
                            </div>
                        </div>
                        <span id="dSumStatusBadge" class="badge <?= e($initDaily['badge_class'] ?? 'bg-secondary-subtle text-secondary') ?> border rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                            <?= e($initDaily['status_label'] ?? 'Not Punched Yet') ?>
                        </span>
                    </div>

                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="p-2 bg-white rounded-3 border">
                                <div class="text-muted" style="font-size: 0.64rem;">First IN</div>
                                <div class="fw-bold text-success font-monospace small" id="dSumFirstIn"><?= e($initDaily['first_in'] ?? '—') ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-white rounded-3 border">
                                <div class="text-muted" style="font-size: 0.64rem;">Last OUT</div>
                                <div class="fw-bold text-danger font-monospace small" id="dSumLastOut"><?= e($initDaily['last_out'] ?? '—') ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-white rounded-3 border">
                                <div class="text-muted" style="font-size: 0.64rem;">Worked</div>
                                <div class="fw-bold text-primary font-monospace small" id="dSumDuration"><?= e($initDaily['formatted_duration'] ?? '00:00:00') ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected Day Punch Items -->
                <div class="border rounded-3 overflow-hidden">
                    <div class="bg-light px-2 py-1 border-bottom d-flex justify-content-between">
                        <span class="fw-bold text-muted text-uppercase" style="font-size: 0.68rem;">Day Punch Log</span>
                        <span class="badge bg-white text-dark border font-monospace" style="font-size: 0.65rem;" id="dSumPunchCount">
                            <?= (int)($initDaily['punch_count'] ?? 0) ?> punch(es)
                        </span>
                    </div>
                    <div id="selfDailyPunchList" class="p-2" style="max-height: 150px; overflow-y: auto;">
                        <?php if (empty($initDaily['punches'])): ?>
                            <div class="text-center text-muted small py-2">No punches recorded on this date.</div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($initDaily['punches'] as $dp): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-1 py-1 border-0 border-bottom">
                                        <div>
                                            <span class="badge <?= ($dp['type'] ?? '') === 'IN' ? 'bg-success' : 'bg-danger' ?> me-1" style="font-size: 0.65rem;">
                                                <?= e($dp['type'] ?? '') ?>
                                            </span>
                                            <span class="font-monospace small"><?= e($dp['time'] ?? '') ?></span>
                                        </div>
                                        <span class="text-muted" style="font-size: 0.66rem;">
                                            <?= !empty($dp['location_verified']) ? 'GPS Verified' : 'Recorded' ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Hidden quick badge hook for JS -->
            <span id="selfHeaderQuickBadge" class="d-none"></span>
        </div>

        <!-- 4. PROFILE PANEL -->
        <div id="tabPanelProfile" class="d-none px-4 pb-4 text-start">
            <div class="card border rounded-4 p-3 bg-white shadow-sm">
                <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom" style="font-size: 0.95rem;">
                    <i class="fa-regular fa-address-card text-primary me-2"></i> Employee Dossier
                </h6>

                <div class="d-flex flex-column gap-2 small">
                    <div class="d-flex justify-content-between py-1 border-bottom border-light">
                        <span class="text-muted"><i class="fa-solid fa-id-card text-secondary me-2"></i>Employee Code</span>
                        <span class="fw-bold font-monospace text-primary"><?= e($employee['employee_code'] ?? '—') ?></span>
                    </div>

                    <div class="d-flex justify-content-between py-1 border-bottom border-light">
                        <span class="text-muted"><i class="fa-solid fa-sitemap text-secondary me-2"></i>Department</span>
                        <span class="fw-semibold text-dark"><?= e($employee['department_name'] ?? 'General') ?></span>
                    </div>

                    <div class="d-flex justify-content-between py-1 border-bottom border-light">
                        <span class="text-muted"><i class="fa-solid fa-location-dot text-danger me-2"></i>Assigned Work Site</span>
                        <span class="fw-semibold text-dark"><?= e($employee['site'] ?? 'Main Site') ?></span>
                    </div>

                    <div class="d-flex justify-content-between py-1 border-bottom border-light">
                        <span class="text-muted"><i class="fa-solid fa-diagram-project text-secondary me-2"></i>Assigned Project</span>
                        <span class="fw-semibold text-dark"><?= e($employee['project'] ?? 'Standard Operations') ?></span>
                    </div>

                    <div class="d-flex justify-content-between py-1 border-bottom border-light">
                        <span class="text-muted"><i class="fa-regular fa-clock text-secondary me-2"></i>Shift Schedule</span>
                        <span class="font-monospace text-dark">
                            <?= date('h:i A', strtotime($employee['shift_start'] ?? '09:00:00')) ?> - <?= date('h:i A', strtotime($employee['shift_end'] ?? '18:00:00')) ?>
                        </span>
                    </div>

                    <div class="d-flex justify-content-between py-1 border-bottom border-light">
                        <span class="text-muted"><i class="fa-solid fa-phone text-secondary me-2"></i>Mobile / Phone</span>
                        <span class="font-monospace text-dark"><?= e($employee['phone'] ?? '—') ?></span>
                    </div>

                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted"><i class="fa-solid fa-circle-check text-success me-2"></i>Account Status</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace">Active</span>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- /.mobile-profile-card -->

</div> <!-- /.mobile-profile-wrap -->

<!-- Geofence Error Modal -->
<div class="modal fade" id="geofenceErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content rounded-4 border-0 shadow-lg text-center p-4">
            <div class="mb-3">
                <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 70px; height: 70px; font-size: 2rem;">
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
                    <span class="text-muted fw-semibold">Allowed Radius:</span>
                    <span class="badge bg-primary-subtle text-primary font-monospace"><?= e($employee['site_radius'] ?? 200) ?> meters</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted fw-semibold">Your Current Distance:</span>
                    <span class="text-danger fw-bold font-monospace" id="geoCurrentDist">—</span>
                </div>
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

<!-- Mutual Exclusion Conflict Dialog Modal (Punch vs Leave) -->
<div class="modal fade" id="conflictAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content rounded-4 border-0 shadow-lg text-center p-4">
            <div class="mb-3">
                <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 70px; height: 70px; font-size: 2rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <h5 class="fw-bold text-dark mb-1" id="conflictModalTitle">Action Not Permitted</h5>
            <p class="text-danger fw-semibold small mb-3" id="conflictModalSubtitle">Punch & Leave cannot be taken on the same date.</p>
            
            <div class="p-3 bg-light rounded-4 border text-start small mb-3">
                <div id="conflictDetailText" class="text-secondary small">
                    You can take either Attendance Punch OR Leave on any given date, not both.
                </div>
            </div>

            <div class="d-grid">
                <button type="button" class="btn btn-dark rounded-pill py-2 fw-bold" data-bs-dismiss="modal">
                    Understood
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Interactive App Tab Switcher (Profile, Attendance, Leave, Summary)
window.switchAppTab = function(tabName) {
    const tabs = ['profile', 'attendance', 'leave', 'summary'];
    tabs.forEach(t => {
        const btn = document.getElementById('appTabBtn' + t.charAt(0).toUpperCase() + t.slice(1));
        const panel = document.getElementById('tabPanel' + t.charAt(0).toUpperCase() + t.slice(1));
        if (btn) {
            if (t === tabName) btn.classList.add('active');
            else btn.classList.remove('active');
        }
        if (panel) {
            if (t === tabName) panel.classList.remove('d-none');
            else panel.classList.add('d-none');
        }
    });
};
</script>

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

    // Mutual Exclusion & Duplicate Leave State
    const todayIsoStr = <?= json_encode(date('Y-m-d')) ?>;
    let hasPunchesTodayJs = <?= !empty($todayPunches) ? 'true' : 'false' ?>;
    const appliedLeaveRanges = <?= json_encode($appliedLeaveRanges ?? []) ?>;
    const punchedDatesList = <?= json_encode($punchedDates ?? []) ?>;

    function formatDisplayDate(iso) {
        if (!iso) return '';
        const parts = iso.split('-');
        if (parts.length !== 3) return iso;
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const mIdx = parseInt(parts[1], 10) - 1;
        return parts[2] + ' ' + (months[mIdx] || parts[1]) + ' ' + parts[0];
    }

    function findNextFreeLeaveDate(baseIso) {
        const startMs = Date.parse((baseIso || todayIsoStr) + 'T00:00:00');
        for (let i = 0; i < 90; i++) {
            const dt = new Date(startMs + i * 86400000);
            const y = dt.getFullYear();
            const m = String(dt.getMonth() + 1).padStart(2, '0');
            const d = String(dt.getDate()).padStart(2, '0');
            const cand = y + '-' + m + '-' + d;
            if (cand === todayIsoStr && hasPunchesTodayJs) continue;
            if (punchedDatesList.indexOf(cand) !== -1) continue;
            let inLeave = false;
            for (let k = 0; k < appliedLeaveRanges.length; k++) {
                const rng = appliedLeaveRanges[k];
                if (cand >= rng.start && cand <= rng.end) {
                    inLeave = true;
                    break;
                }
            }
            if (!inLeave) return cand;
        }
        return baseIso || todayIsoStr;
    }

    function validateLeaveDateSelection(sDate, eDate, showModal) {
        const alertBox = document.getElementById('leaveFormAlert');
        if (!sDate) return true;
        const endCheck = eDate && eDate >= sDate ? eDate : sDate;

        // 1. Check same-day / recorded attendance punch conflict
        if (hasPunchesTodayJs && sDate <= todayIsoStr && endCheck >= todayIsoStr) {
            const msg = 'Attendance punch is already recorded today (' + formatDisplayDate(todayIsoStr) + '). Same-day Punch and Leave are not allowed — take any one only. Please change the date.';
            if (alertBox) {
                alertBox.className = 'alert alert-danger py-2 px-3 small rounded-3 mb-3 d-block';
                alertBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> ' + msg;
            }
            if (showModal) {
                window.showConflictModal(
                    'Same-Day Punch & Leave Not Allowed',
                    'Take any one only — please change the date.',
                    'You have already recorded attendance punches on <strong>' + formatDisplayDate(todayIsoStr) + '</strong>.<br><br>The system allows <strong>either Attendance Punch OR Leave</strong> on any given day, not both. Please change the date.'
                );
            }
            return false;
        }

        for (let i = 0; i < punchedDatesList.length; i++) {
            const pDate = punchedDatesList[i];
            if (pDate >= sDate && pDate <= endCheck) {
                const msg = 'Attendance punch is already recorded on ' + formatDisplayDate(pDate) + '. Same-day Punch and Leave are not allowed — take any one only. Please change the date.';
                if (alertBox) {
                    alertBox.className = 'alert alert-danger py-2 px-3 small rounded-3 mb-3 d-block';
                    alertBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> ' + msg;
                }
                if (showModal) {
                    window.showConflictModal(
                        'Same-Day Punch & Leave Not Allowed',
                        'Take any one only — please change the date.',
                        'Attendance punch is already recorded on <strong>' + formatDisplayDate(pDate) + '</strong>.<br><br>You cannot take both Punch and Leave on the same day. Please change the date.'
                    );
                }
                return false;
            }
        }

        // 2. Check duplicate / already-applied leave on the same date
        for (let j = 0; j < appliedLeaveRanges.length; j++) {
            const lv = appliedLeaveRanges[j];
            if (!(endCheck < lv.start || sDate > lv.end)) {
                const rangeLabel = (lv.start === lv.end)
                    ? formatDisplayDate(lv.start)
                    : (formatDisplayDate(lv.start) + ' to ' + formatDisplayDate(lv.end));
                const msg = 'This day (' + rangeLabel + ') is already applied for ' + lv.type + '. Please change the date.';
                if (alertBox) {
                    alertBox.className = 'alert alert-danger py-2 px-3 small rounded-3 mb-3 d-block';
                    alertBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> ' + msg;
                }
                if (showModal) {
                    window.showConflictModal(
                        'This Day Already Applied!',
                        'Please change the date.',
                        'This day (<strong>' + rangeLabel + '</strong>) is already applied for <strong>' + lv.type + '</strong>.<br><br>Duplicate leave on the same day is not allowed. Please change the date.'
                    );
                }
                return false;
            }
        }

        return true;
    }

    // --- Mobile Leave / OD Application Handlers ---
    window.toggleTargetSiteField = function() {
        const type = document.getElementById('mLeaveType').value;
        const group = document.getElementById('mTargetSiteGroup');
        if (group) {
            if (type.includes('OD') || type.includes('Outdoor') || type.includes('Duty')) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        }
    };

    window.validateLeaveAttachment = function(input) {
        const errBox = document.getElementById('attachmentError');
        const btn = document.getElementById('btnSubmitLeave');
        if (!errBox) return;
        errBox.className = 'text-danger small d-none mt-1';
        errBox.textContent = '';
        if (btn) btn.disabled = false;

        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            const maxSize = 5 * 1024 * 1024; // 5 MB
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg'];
            const ext = file.name.split('.').pop().toLowerCase();
            const allowedExts = ['pdf', 'jpg', 'jpeg'];

            if (!allowedTypes.includes(file.type) && !allowedExts.includes(ext)) {
                errBox.className = 'text-danger small d-block mt-1';
                errBox.innerHTML = '<i class="fa-solid fa-circle-exclamation me-1"></i> Invalid file type! Only PDF and JPEG files are allowed.';
                input.value = '';
                if (btn) btn.disabled = true;
                return;
            }
            if (file.size > maxSize) {
                errBox.className = 'text-danger small d-block mt-1';
                errBox.innerHTML = '<i class="fa-solid fa-circle-exclamation me-1"></i> File too large! Maximum size is 5 MB. Your file: ' + (file.size / (1024*1024)).toFixed(2) + ' MB';
                input.value = '';
                if (btn) btn.disabled = true;
                return;
            }
            // Valid file — show green info
            errBox.className = 'text-success small d-block mt-1';
            errBox.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
        }
    };

    window.submitMobileLeave = function(e) {
        e.preventDefault();
        const form = document.getElementById('mobileLeaveForm');
        const alertBox = document.getElementById('leaveFormAlert');
        const btn = document.getElementById('btnSubmitLeave');
        if (!form || !btn) return;
        const origText = btn.innerHTML;

        // Re-validate attachment before submit
        const fileInput = document.getElementById('mAttachment');
        if (fileInput && fileInput.files && fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const maxSize = 5 * 1024 * 1024;
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['pdf','jpg','jpeg'].includes(ext)) {
                if (alertBox) {
                    alertBox.className = 'alert alert-danger py-2 px-3 small rounded-3 mb-3 d-block';
                    alertBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Invalid file type! Only PDF and JPEG files are allowed.';
                }
                return;
            }
            if (file.size > maxSize) {
                if (alertBox) {
                    alertBox.className = 'alert alert-danger py-2 px-3 small rounded-3 mb-3 d-block';
                    alertBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> File too large! Max 5 MB.';
                }
                return;
            }
        }

        const sDate = document.getElementById('mStartDate') ? document.getElementById('mStartDate').value : '';
        const eDate = document.getElementById('mEndDate') ? document.getElementById('mEndDate').value : '';

        if (!validateLeaveDateSelection(sDate, eDate, true)) {
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';
        if (alertBox) {
            alertBox.className = 'alert d-none';
            alertBox.innerHTML = '';
        }

        const formData = new FormData(form);
        const url = <?= json_encode(public_url('p/' . rawurlencode($employee['employee_code']) . '/leave-apply')) ?>;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) {
            return res.text().then(function(text) {
                try {
                    return JSON.parse(text);
                } catch(parseErr) {
                    console.error('Server response:', text);
                    return { success: false, message: 'Server error: ' + (text.substring(0, 120) || 'Empty response') };
                }
            });
        })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = origText;
            if (data.is_punch_conflict) {
                window.showConflictModal(
                    'Same-Day Punch & Leave Not Allowed',
                    'Take any one only — please change the date.',
                    data.message
                );
            } else if (data.is_leave_conflict) {
                window.showConflictModal(
                    'This Day Already Applied!',
                    'Please change the date.',
                    data.message
                );
            }

            if (alertBox) {
                if (data.success) {
                    alertBox.className = 'alert alert-success py-2 px-3 small rounded-3 mb-3 d-block';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ' + (data.message || 'Submitted successfully!').replace(/\n/g, '<br>');
                    form.reset();
                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    alertBox.className = 'alert alert-danger py-2 px-3 small rounded-3 mb-3 d-block';
                    alertBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> ' + (data.message || 'Submission failed.');
                }
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = origText;
            if (alertBox) {
                alertBox.className = 'alert alert-danger py-2 px-3 small rounded-3 mb-3 d-block';
                alertBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Request failed: ' + (err.message || 'Network error');
            }
        });
    };

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
        const hasLeaveToday = <?= !empty($todayLeave) ? 'true' : 'false' ?>;

        if (hasLeaveToday) {
            const conflictHandler = function() {
                window.showConflictModal(
                    'Punch Blocked: Leave Active Today',
                    'Cannot punch attendance while leave is applied for today.',
                    'You have already applied for <strong><?= addslashes(e($todayLeave['leave_type'] ?? 'Leave')) ?></strong> on today\'s date (<?= date('d M Y') ?>).<br><br>The system strictly allows <strong>either Attendance Punch OR Leave</strong> on any given date, not both.'
                );
            };

            if (inBtn) {
                inBtn.classList.add('disabled-action');
                inBtn.style.pointerEvents = 'auto';
                inBtn.style.cursor = 'not-allowed';
                inBtn.onclick = conflictHandler;
            }
            if (outBtn) {
                outBtn.classList.add('disabled-action');
                outBtn.style.pointerEvents = 'auto';
                outBtn.style.cursor = 'not-allowed';
                outBtn.onclick = conflictHandler;
            }
            if (statusBox) {
                statusBox.className = 'status-pill-box bg-warning-subtle text-warning-emphasis border border-warning-subtle';
            }
            if (statusText) {
                statusText.innerHTML = '<i class="fa-solid fa-calendar-xmark me-1 text-warning"></i> ON LEAVE TODAY (<?= addslashes(e($todayLeave['leave_type'] ?? 'Leave')) ?>)';
            }
            stopTimer();
            return;
        }

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

    // Modal for Punch vs Leave Conflict
    window.showConflictModal = function(title, subtitle, detail) {
        const modalEl = document.getElementById('conflictAlertModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            alert((title ? title + "\n\n" : "") + (subtitle ? subtitle + "\n\n" : "") + (detail ? detail.replace(/<[^>]*>/g, '') : ""));
            return;
        }
        if (title) document.getElementById('conflictModalTitle').textContent = title;
        if (subtitle) document.getElementById('conflictModalSubtitle').textContent = subtitle;
        if (detail) document.getElementById('conflictDetailText').innerHTML = detail;
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    // 7. Execute Punch Action with Selfie Capture
    window.executePunch = function(type) {
        const hasLeaveToday = <?= !empty($todayLeave) ? 'true' : 'false' ?>;
        if (hasLeaveToday) {
            window.showConflictModal(
                'Punch Blocked: Leave Active Today',
                'Cannot punch attendance while leave is applied for today.',
                'You have already applied for <strong><?= addslashes(e($todayLeave['leave_type'] ?? 'Leave')) ?></strong> on today\'s date (<?= date('d M Y') ?>).<br><br>The system allows <strong>either Attendance Punch OR Leave</strong> on any given date, not both.'
            );
            return;
        }

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
                // Update Today's Summary Card & Hero live status matching Image 1
                const summaryIn = document.getElementById('summaryCheckInDisplay');
                const summaryOut = document.getElementById('summaryCheckOutDisplay');
                const heroBadge = document.getElementById('heroLiveStatusBadge');
                const nowTimeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });

                if (type === 'IN') {
                    if (summaryIn) summaryIn.textContent = nowTimeStr;
                    if (heroBadge) {
                        heroBadge.innerHTML = '<span class="hero-status-pill hero-status-online"><span class="status-dot-pulse"></span> Online</span>';
                    }
                } else {
                    if (summaryOut) summaryOut.textContent = nowTimeStr;
                    if (heroBadge) {
                        heroBadge.innerHTML = '<span class="hero-status-pill hero-status-offline"><span class="status-dot-pulse"></span> Offline</span>';
                    }
                }


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

                // Dynamically lock today's leave selection immediately after punch without requiring page reload
                hasPunchesTodayJs = true;
                if (punchedDatesList.indexOf(todayIsoStr) === -1) {
                    punchedDatesList.push(todayIsoStr);
                }
                const punchedBanner = document.getElementById('punchedTodayLeaveBanner');
                if (typeof window.refreshSelfAttendanceLive === 'function') {
                    window.refreshSelfAttendanceLive();
                }
                if (punchedBanner) punchedBanner.classList.remove('d-none');
                const sInputLive = document.getElementById('mStartDate');
                const eInputLive = document.getElementById('mEndDate');
                const nextFree = findNextFreeLeaveDate(<?= json_encode(date('Y-m-d', strtotime('+1 day'))) ?>);
                if (sInputLive) {
                    sInputLive.min = <?= json_encode(date('Y-m-d', strtotime('+1 day'))) ?>;
                    if (sInputLive.value <= todayIsoStr) sInputLive.value = nextFree;
                }
                if (eInputLive) {
                    eInputLive.min = sInputLive ? sInputLive.value : nextFree;
                    if (eInputLive.value < eInputLive.min) eInputLive.value = eInputLive.min;
                }
            } else {
                if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
                playAudio(false);
                displayAlert('danger', data.message || 'Unable to record punch.');

                // If backend rejected due to geofence or location:
                if (data.is_leave_conflict) {
                    window.showConflictModal(
                        'Punch Blocked: Leave Active',
                        'Cannot punch attendance on a leave date.',
                        data.message
                    );
                    return;
                }

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

    // Intercept date selection on From Date & To Date for BOTH punched dates and already-applied leave dates
    const sDateInput = document.getElementById('mStartDate');
    const eDateInput = document.getElementById('mEndDate');

    if (sDateInput) {
        sDateInput.addEventListener('change', function() {
            if (eDateInput) {
                eDateInput.min = this.value;
                if (eDateInput.value < this.value) {
                    eDateInput.value = this.value;
                }
            }
            const curS = this.value;
            const curE = eDateInput ? eDateInput.value : curS;
            if (!validateLeaveDateSelection(curS, curE, true)) {
                const fallbackDate = findNextFreeLeaveDate(<?= json_encode(date('Y-m-d', strtotime('+1 day'))) ?>);
                this.value = fallbackDate;
                if (eDateInput) {
                    eDateInput.min = fallbackDate;
                    eDateInput.value = fallbackDate;
                }
            }
        });
    }

    if (eDateInput) {
        eDateInput.addEventListener('change', function() {
            const curS = sDateInput ? sDateInput.value : this.value;
            const curE = this.value;
            if (!validateLeaveDateSelection(curS, curE, true)) {
                this.value = curS;
            }
        });
    }

    // --- Employee Self-Service Daily & Monthly Status, Summary & Download Controller ---
    const selfReportApiBase = <?= json_encode(public_url('p/' . rawurlencode($employee['employee_code']) . '/attendance-report')) ?>;
    const selfDownloadBase = <?= json_encode(public_url('p/' . rawurlencode($employee['employee_code']) . '/download-report')) ?>;

    function escHtml(val) {
        return String(val === null || val === undefined ? '' : val)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    window.switchSelfReportTab = function(tab) {
        const mPanel = document.getElementById('selfMonthlyPanel');
        const dPanel = document.getElementById('selfDailyPanel');
        const btnM = document.getElementById('tabBtnMonthly');
        const btnD = document.getElementById('tabBtnDaily');
        if (!mPanel || !dPanel || !btnM || !btnD) return;

        if (tab === 'daily') {
            mPanel.classList.add('d-none');
            dPanel.classList.remove('d-none');
            btnD.className = 'btn btn-sm btn-primary rounded-pill fw-bold py-1';
            btnM.className = 'btn btn-sm btn-light rounded-pill fw-semibold text-muted py-1';
        } else {
            dPanel.classList.add('d-none');
            mPanel.classList.remove('d-none');
            btnM.className = 'btn btn-sm btn-primary rounded-pill fw-bold py-1';
            btnD.className = 'btn btn-sm btn-light rounded-pill fw-semibold text-muted py-1';
        }
    };

    function updateSelfDownloadLinks() {
        const mVal = (document.getElementById('selfMonthInput') || {}).value || '';
        const dVal = (document.getElementById('selfDateInput') || {}).value || '';
        const mExcel = document.getElementById('btnDownloadMonthlyExcel');
        const mCsv = document.getElementById('btnDownloadMonthlyCsv');
        const dExcel = document.getElementById('btnDownloadDailyExcel');
        const dCsv = document.getElementById('btnDownloadDailyCsv');

        if (mExcel && mVal) mExcel.href = selfDownloadBase + '?mode=monthly&month=' + encodeURIComponent(mVal) + '&format=excel';
        if (mCsv && mVal) mCsv.href = selfDownloadBase + '?mode=monthly&month=' + encodeURIComponent(mVal) + '&format=csv';
        if (dExcel && dVal) dExcel.href = selfDownloadBase + '?mode=daily&date=' + encodeURIComponent(dVal) + '&format=excel';
        if (dCsv && dVal) dCsv.href = selfDownloadBase + '?mode=daily&date=' + encodeURIComponent(dVal) + '&format=csv';
    }

    function renderSelfMonthly(m) {
        if (!m || !m.summary) return;
        const s = m.summary;
        const elP = document.getElementById('mSumPresent');
        const elA = document.getElementById('mSumAbsent');
        const elL = document.getElementById('mSumLeave');
        const elH = document.getElementById('mSumHours');
        const elR = document.getElementById('mSumRate');
        const elTitle = document.getElementById('mTableTitle');
        const elDur = document.getElementById('mTotalDurationLabel');
        const quickBadge = document.getElementById('selfHeaderQuickBadge');
        const tbody = document.getElementById('selfMonthlyTableBody');

        if (elP) elP.textContent = s.present_days;
        if (elA) elA.textContent = s.absent_days;
        if (elL) elL.textContent = (parseInt(s.leave_days || 0) + parseInt(s.od_days || 0));
        if (elH) elH.textContent = s.total_hours + 'h';
        if (elR) elR.textContent = s.attendance_rate;
        if (elTitle) elTitle.textContent = (m.month_label || m.month) + ' Log';
        if (elDur) elDur.textContent = 'Total: ' + (s.formatted_total_duration || '00:00:00');
        if (quickBadge) quickBadge.innerHTML = s.present_days + 'd &bull; ' + s.total_hours + 'h';

        if (tbody && Array.isArray(m.records)) {
            let html = '';
            for (let i = 0; i < m.records.length; i++) {
                const r = m.records[i];
                const inOut = (r.first_in || r.last_out)
                    ? ('<span class="text-success">' + escHtml(r.first_in || '—') + '</span> <span class="text-muted">/</span> <span class="text-danger">' + escHtml(r.last_out || '—') + '</span>')
                    : '<span class="text-muted">—</span>';
                const noteHtml = r.note
                    ? '<div class="text-muted text-truncate" style="font-size: 0.62rem; max-width: 105px;" title="' + escHtml(r.note) + '">' + escHtml(r.note) + '</div>'
                    : '';
                html += '<tr>'
                    + '<td class="ps-2 text-nowrap"><div class="fw-semibold text-dark">' + escHtml(r.date_short) + '</div><div class="text-muted" style="font-size: 0.64rem;">' + escHtml(r.day_name) + '</div></td>'
                    + '<td><span class="badge ' + escHtml(r.badge_class) + ' border rounded-pill px-2 py-0" style="font-size: 0.64rem;">' + escHtml(r.status_label) + '</span>' + noteHtml + '</td>'
                    + '<td class="font-monospace text-nowrap" style="font-size: 0.66rem;">' + inOut + '</td>'
                    + '<td class="pe-2 text-end font-monospace fw-semibold">' + (r.hours > 0 ? escHtml(r.hours) + 'h' : '—') + '</td>'
                    + '</tr>';
            }
            tbody.innerHTML = html;
        }
    }

    function renderSelfDaily(d) {
        if (!d) return;
        const elDate = document.getElementById('dSumDateLabel');
        const elNote = document.getElementById('dSumNote');
        const elBadge = document.getElementById('dSumStatusBadge');
        const elIn = document.getElementById('dSumFirstIn');
        const elOut = document.getElementById('dSumLastOut');
        const elDur = document.getElementById('dSumDuration');
        const elCnt = document.getElementById('dSumPunchCount');
        const listEl = document.getElementById('selfDailyPunchList');

        if (elDate) elDate.textContent = (d.date_formatted || d.date) + ' (' + (d.day_name || '') + ')';
        if (elNote) elNote.textContent = d.note ? d.note : 'Daily Attendance Summary';
        if (elBadge) {
            elBadge.className = 'badge ' + (d.badge_class || 'bg-secondary-subtle text-secondary') + ' border rounded-pill px-2 py-1';
            elBadge.textContent = d.status_label || '—';
        }
        if (elIn) elIn.textContent = d.first_in || '—';
        if (elOut) elOut.textContent = d.last_out || '—';
        if (elDur) elDur.textContent = d.formatted_duration || '00:00:00';
        if (elCnt) elCnt.textContent = (d.punch_count || 0) + ' punch(es)';

        if (listEl) {
            if (!Array.isArray(d.punches) || d.punches.length === 0) {
                listEl.innerHTML = '<div class="text-center text-muted small py-2">No punches recorded on this date.</div>';
            } else {
                let html = '<div class="list-group list-group-flush">';
                for (let i = 0; i < d.punches.length; i++) {
                    const p = d.punches[i];
                    const bCls = p.type === 'IN' ? 'bg-success' : 'bg-danger';
                    html += '<div class="list-group-item d-flex justify-content-between align-items-center px-1 py-1 border-0 border-bottom">'
                        + '<div><span class="badge ' + bCls + ' me-1" style="font-size: 0.65rem;">' + escHtml(p.type) + '</span><span class="font-monospace small">' + escHtml(p.time) + '</span></div>'
                        + '<span class="text-muted" style="font-size: 0.66rem;">' + (p.location_verified ? 'GPS Verified' : 'Recorded') + '</span>'
                        + '</div>';
                }
                html += '</div>';
                listEl.innerHTML = html;
            }
        }
    }

    window.loadSelfAttendanceData = function(mode) {
        updateSelfDownloadLinks();
        const mVal = (document.getElementById('selfMonthInput') || {}).value || '';
        const dVal = (document.getElementById('selfDateInput') || {}).value || '';
        const qs = '?mode=' + encodeURIComponent(mode || 'both')
            + '&month=' + encodeURIComponent(mVal)
            + '&date=' + encodeURIComponent(dVal);

        fetch(selfReportApiBase + qs, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            cache: 'no-store'
        })
        .then(function(res) { return res.json(); })
        .then(function(resp) {
            if (resp && resp.success && resp.data) {
                if (resp.data.monthly) renderSelfMonthly(resp.data.monthly);
                if (resp.data.daily) renderSelfDaily(resp.data.daily);
            }
        })
        .catch(function() {});
    };

    window.refreshSelfAttendanceLive = function() {
        window.loadSelfAttendanceData('both');
    };

    // Keep Render instance warm while employee punch page is open (every 4 minutes)
    setInterval(function() {
        fetch(<?= json_encode(public_url('health?lite=1')) ?>, { cache: 'no-store', keepalive: true }).catch(function() {});
    }, 240000);
})();
</script>
