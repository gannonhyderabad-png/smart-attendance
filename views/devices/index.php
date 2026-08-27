<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
    <div class="card-body p-4">

        <!-- Page Header -->
        <div class="row g-3 align-items-center mb-4">
            <div class="col-md-7">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h5 class="fw-bold text-dark mb-0">Biometric & FRM Face Devices (eSSL / ZKTeco)</h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 small fw-semibold">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> ADMS Cloud Push Active
                    </span>
                </div>
                <p class="text-muted small mb-0">Connect Facial Recognition Terminals & Fingerprint machines for instant real-time cloud attendance synchronization.</p>
            </div>
            <div class="col-md-5 text-md-end d-flex gap-2 justify-content-md-end flex-wrap">
                <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                    <i class="fa-solid fa-plus me-1"></i> Add Device Manually
                </button>
                <button type="button" class="btn btn-outline-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#setupGuideModal">
                    <i class="fa-solid fa-book-open-reader me-1"></i> Setup Guide &amp; Diagnostics
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

        <!-- Quick Connection Credentials Card -->
        <div class="p-4 bg-primary bg-opacity-10 border border-primary-subtle rounded-4 mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h6 class="fw-bold text-primary mb-1"><i class="fa-solid fa-server me-2"></i>eSSL / ZKTeco Cloud Server Parameters</h6>
                    <small class="text-muted">Enter these exact parameters in your eSSL FRM device under <strong>Menu &gt; Comm. &gt; Cloud Server / ADMS</strong>:</small>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap font-monospace">
                    <div class="bg-white border rounded-3 px-3 py-2 text-center shadow-sm">
                        <small class="text-muted d-block" style="font-size: 0.68rem;">SERVER DOMAIN / IP</small>
                        <strong class="text-dark font-monospace" id="serverDomainCopy">smart-attendance-hw9c.onrender.com</strong>
                        <button class="btn btn-sm btn-link p-0 ms-1 text-primary" onclick="navigator.clipboard.writeText('smart-attendance-hw9c.onrender.com'); alert('Domain copied!');" title="Copy Domain">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                    <div class="bg-white border rounded-3 px-3 py-2 text-center shadow-sm">
                        <small class="text-muted d-block" style="font-size: 0.68rem;">PORT</small>
                        <strong class="text-dark font-monospace">443 <span class="text-muted fw-normal">(or 80)</span></strong>
                    </div>
                    <div class="bg-white border rounded-3 px-3 py-2 text-center shadow-sm">
                        <small class="text-muted d-block" style="font-size: 0.68rem;">HTTPS / SSL</small>
                        <strong class="text-success font-monospace">ENABLE (or Port 80)</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Devices List Table -->
        <div class="border rounded-4 overflow-hidden shadow-sm mb-4">
            <div class="bg-light py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-network-wired me-2 text-primary"></i>Connected FRM Devices (<?= count($devices) ?>)</h6>
                <span class="badge bg-secondary text-white rounded-pill">Auto-Discovered Terminals</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted text-uppercase" style="font-size: 0.75rem;">
                        <tr>
                            <th class="ps-4">Device Name &amp; Model</th>
                            <th>Serial Number (SN)</th>
                            <th>IP Address</th>
                            <th>Assigned Site / Project</th>
                            <th>Firmware / Push</th>
                            <th>Heartbeat Status</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php if (empty($devices)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-fingerprint fa-3x mb-3 d-block text-secondary opacity-50"></i>
                                    <h6 class="fw-bold text-dark mb-1">No FRM Devices Connected Yet</h6>
                                    <p class="small text-muted mb-3">Once you enter the Cloud Server parameters into your eSSL device, it will automatically register here within seconds.</p>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#setupGuideModal">
                                        <i class="fa-solid fa-book-open-reader me-1"></i> View Setup Instructions
                                    </button>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($devices as $d): ?>
                                <?php 
                                $isOnline = false;
                                if (!empty($d['last_heartbeat'])) {
                                    $diffSec = time() - strtotime($d['last_heartbeat']);
                                    $isOnline = ($diffSec < 300); // seen within last 5 minutes
                                }
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="p-2 rounded-3 <?= $isOnline ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>">
                                                <i class="fa-solid fa-fingerprint fs-5"></i>
                                            </div>
                                            <div>
                                                <strong class="text-dark d-block"><?= e($d['device_name'] ?: 'eSSL Face Terminal') ?></strong>
                                                <small class="text-muted"><?= e($d['device_model'] ?: 'Biometric Machine') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border font-monospace px-2 py-1" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-barcode me-1"></i><?= e($d['serial_number']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-monospace text-muted"><?= e($d['ip_address'] ?: '—') ?></span>
                                    </td>
                                    <td>
                                        <div><strong><?= e($d['site'] ?: 'Main Office') ?></strong></div>
                                        <small class="text-muted"><?= e($d['project'] ?: 'General') ?></small>
                                    </td>
                                    <td>
                                        <div class="font-monospace small text-muted"><?= e($d['firmware_version'] ?: 'Ver 2.4') ?></div>
                                        <small class="text-muted font-monospace" style="font-size: 0.68rem;">Push: <?= e($d['push_version'] ?: 'ADMS') ?></small>
                                    </td>
                                    <td>
                                        <?php if ($isOnline): ?>
                                            <span class="badge bg-success px-2 py-1 rounded-pill">
                                                <i class="fa-solid fa-circle fa-beat-fade me-1" style="font-size: 6px;"></i> ONLINE
                                            </span>
                                            <div class="text-muted font-monospace" style="font-size: 0.68rem;">
                                                <?= date('h:i:s A', strtotime($d['last_heartbeat'])) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill">
                                                <i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i> OFFLINE
                                            </span>
                                            <div class="text-muted font-monospace" style="font-size: 0.68rem;">
                                                <?= !empty($d['last_heartbeat']) ? date('d M, h:i A', strtotime($d['last_heartbeat'])) : 'Never' ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-light border text-primary" onclick="openEditDeviceModal(<?= htmlspecialchars(json_encode($d), ENT_QUOTES, 'UTF-8') ?>)" title="Edit Device Details">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <form action="<?= base_url('devices/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Remove device <?= e($d['serial_number']) ?>?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger border-start-0" title="Delete Device">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
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
</div>

<!-- eSSL Setup Instructions Modal -->
<div class="modal fade" id="setupGuideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom bg-light py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary text-white p-2 rounded-3"><i class="fa-solid fa-book-open-reader fs-6"></i></span>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">eSSL / ZKTeco FRM Cloud Setup Guide</h5>
                        <small class="text-muted">Step-by-step instructions to connect your physical face recognition machine</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-start gap-3 mb-4 p-3 bg-light rounded-3 border">
                    <span class="badge bg-primary rounded-circle p-2 fs-6">1</span>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Open Device Communication Menu</h6>
                        <p class="text-muted small mb-0">On your eSSL / ZKTeco machine screen, press <strong>M/OK &gt; Comm. (Communication) &gt; Cloud Server / ADMS / Web Server Settings</strong>.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 mb-4 p-3 bg-light rounded-3 border">
                    <span class="badge bg-primary rounded-circle p-2 fs-6">2</span>
                    <div class="w-100">
                        <h6 class="fw-bold text-dark mb-1">Enter Cloud Server Parameters</h6>
                        <div class="table-responsive mt-2">
                            <table class="table table-bordered table-sm mb-0 small">
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold text-muted bg-white" style="width: 35%;">Server Mode / Type</td>
                                        <td class="font-monospace fw-bold text-primary bg-white">Domain Name (or Cloud Server)</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted bg-white">Server Address / Domain</td>
                                        <td class="font-monospace fw-bold text-dark bg-white">smart-attendance-hw9c.onrender.com</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted bg-white">Server Port</td>
                                        <td class="font-monospace fw-bold text-dark bg-white">443 <span class="text-muted fw-normal">(or 80)</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted bg-white">HTTPS / SSL</td>
                                        <td class="font-monospace fw-bold text-success bg-white">ON / Enable (If using Port 443)</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted bg-white">Enable Proxy / Push</td>
                                        <td class="font-monospace fw-bold text-success bg-white">ON / Enable</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 mb-4 p-3 bg-light rounded-3 border">
                    <span class="badge bg-primary rounded-circle p-2 fs-6">3</span>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Device Network &amp; DNS Checklist (Crucial)</h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Go to <strong>Comm. &gt; Ethernet (or Wi-Fi)</strong> on your machine.</li>
                            <li>Make sure <strong>DNS Server</strong> is set to <code class="text-primary fw-bold">8.8.8.8</code> (Google DNS) or your router's Gateway IP. If DNS is <code>0.0.0.0</code>, the machine cannot resolve the domain!</li>
                            <li>Do <strong>NOT</strong> type <code>http://</code> or <code>https://</code> in the Server Address field — type only <code>smart-attendance-hw9c.onrender.com</code>.</li>
                        </ul>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 border">
                    <span class="badge bg-primary rounded-circle p-2 fs-6">4</span>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Save and Check Connection Status</h6>
                        <p class="text-muted small mb-0">Press <strong>Save / OK</strong>. The cloud/globe icon on the device screen will turn <strong>Green (Connected)</strong>, and the device will instantly appear on this page.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-3 px-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Got It</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Device Manually Modal -->
<div class="modal fade" id="addDeviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('devices/create') ?>" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
            <?= csrf_field() ?>
            <div class="modal-header border-bottom bg-light py-3 px-4">
                <h5 class="modal-title fw-bold text-dark mb-0"><i class="fa-solid fa-plus me-2 text-primary"></i>Add FRM Device Manually</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Device Serial Number (SN) <span class="text-danger">*</span></label>
                    <input type="text" name="serial_number" class="form-control font-monospace" placeholder="e.g. ABCD123456789 (from device back sticker/menu)" required>
                    <small class="text-muted" style="font-size: 0.7rem;">Found in Menu &gt; System Info &gt; Device Info &gt; Serial Number</small>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Device Friendly Name</label>
                    <input type="text" name="device_name" class="form-control" placeholder="e.g. Main Gate Face Machine">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Device Model / Brand</label>
                    <input type="text" name="device_model" class="form-control" placeholder="e.g. eSSL AI Face Pro / SilkBio-100TC">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Assigned Work Site</label>
                        <input type="text" name="site" class="form-control" placeholder="e.g. Hyderabad Office">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Project Name</label>
                        <input type="text" name="project" class="form-control" placeholder="e.g. Metro Line 1">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-3 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-check me-1"></i> Register Device</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Device Modal -->
<div class="modal fade" id="editDeviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('devices/update') ?>" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="editDeviceId">
            <div class="modal-header border-bottom bg-light py-3 px-4">
                <h5 class="modal-title fw-bold text-dark mb-0">Edit FRM Device Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Device Serial Number</label>
                    <input type="text" id="editDeviceSN" class="form-control bg-light font-monospace" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Device Name</label>
                    <input type="text" name="device_name" id="editDeviceName" class="form-control" placeholder="e.g. Main Gate Face Terminal" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Device Model</label>
                    <input type="text" name="device_model" id="editDeviceModel" class="form-control" placeholder="e.g. eSSL AI Face Pro">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Assigned Work Site</label>
                        <input type="text" name="site" id="editDeviceSite" class="form-control" placeholder="e.g. Hyderabad Office">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Project Name</label>
                        <input type="text" name="project" id="editDeviceProject" class="form-control" placeholder="e.g. Metro Line 1">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-3 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditDeviceModal(d) {
    document.getElementById('editDeviceId').value = d.id;
    document.getElementById('editDeviceSN').value = d.serial_number;
    document.getElementById('editDeviceName').value = d.device_name || '';
    document.getElementById('editDeviceModel').value = d.device_model || '';
    document.getElementById('editDeviceSite').value = d.site || '';
    document.getElementById('editDeviceProject').value = d.project || '';
    const modal = new bootstrap.Modal(document.getElementById('editDeviceModal'));
    modal.show();
}
</script>
