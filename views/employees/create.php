<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">Register New Staff Member</h5>
                    <small class="text-muted">Fill out the employee profile. The unique attendance URL is generated automatically.</small>
                </div>
                <a href="<?= base_url('employees') ?>" class="btn btn-light btn-sm border rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="<?= base_url('employees/store') ?>" id="createEmployeeForm" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <!-- Employee Profile Photo Card -->
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border mb-4">
                        <div class="position-relative">
                            <img id="avatarPreviewImg" src="<?= asset_url('img/default-avatar.png') ?>" onerror="this.src='https://ui-avatars.com/api/?name=New+Staff&background=4f46e5&color=fff&size=128'" class="rounded-circle border shadow-sm object-fit-cover" style="width: 72px; height: 72px;" alt="Avatar Preview">
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-label small fw-semibold text-dark mb-1">
                                <i class="fa-solid fa-camera text-primary me-1"></i> Employee Profile Photo
                            </label>
                            <input type="file" name="photo" id="photoFileInput" class="form-control form-control-sm bg-white" accept="image/jpeg,image/png,image/webp,image/jpg" onchange="previewEmployeeAvatar(this)">
                            <div class="form-text small" style="font-size: 0.72rem;">Supported formats: JPG, PNG, WEBP. Max size: 5MB.</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Employee Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-id-card"></i></span>
                                <input type="text" name="employee_code" id="employeeCodeInput" class="form-control bg-light border-start-0 font-monospace fw-bold text-primary" placeholder="000001" value="<?= e($suggestedCode) ?>" required>
                            </div>
                            <div class="form-text small">Unique numeric code in range <code>000001</code> to <code>999999</code>.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                                <input type="text" name="name" class="form-control bg-light border-start-0" placeholder="e.g. Jessica Alba" required>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control bg-light border-start-0" placeholder="jessica@company.com">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Mobile / WhatsApp Phone</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-phone"></i></span>
                                <input type="text" name="phone" class="form-control bg-light border-start-0" placeholder="+1 (555) 000-0000">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Department</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-building"></i></span>
                                <input type="text" name="department" class="form-control bg-light border-start-0" list="departmentList" placeholder="e.g. Engineering, Operations">
                            </div>
                            <datalist id="departmentList">
                                <?php foreach ($departmentList as $deptName): ?>
                                    <option value="<?= e($deptName) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Job Designation</label>
                            <input type="text" name="designation" class="form-control bg-light" placeholder="e.g. QA Engineer">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Assigned Project</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-diagram-project"></i></span>
                                <input type="text" name="project" class="form-control bg-light border-start-0" list="projectList" placeholder="e.g. ERP Portal, Site Alpha">
                            </div>
                            <datalist id="projectList">
                                <?php foreach ($projectList as $projName): ?>
                                    <option value="<?= e($projName) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Work Site / Location Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-location-dot"></i></span>
                                <input type="text" name="site" id="siteNameInput" class="form-control bg-light border-start-0" list="siteList" placeholder="e.g. Hyderabad Office, Site Alpha">
                            </div>
                            <datalist id="siteList">
                                <?php foreach ($siteList as $sName): ?>
                                    <option value="<?= e($sName) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>

                    <!-- Site GPS Geofencing Settings (200m Radius Guard) -->
                    <div class="card border rounded-4 bg-light mb-4 p-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">
                                    <i class="fa-solid fa-map-location-dot text-primary me-2"></i>Site GPS Geofencing (200m Radius Guard)
                                </h6>
                                <small class="text-muted">Attendance will be strictly verified within this perimeter</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="geofence_enabled" value="0">
                                <input class="form-check-input" type="checkbox" name="geofence_enabled" value="1" id="geofenceEnabledSwitch" checked>
                                <label class="form-check-label small fw-semibold text-dark" for="geofenceEnabledSwitch">Enable GPS Geofence</label>
                            </div>
                        </div>

                        <!-- Smart Google Maps Address / Link / Coordinates Input Box -->
                        <div class="mb-3 p-3 bg-white rounded-3 border">
                            <label class="form-label small fw-bold text-dark mb-1">
                                <i class="fa-brands fa-google text-danger me-1"></i> Paste Google Maps Coordinates, Link, or Full Address
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-danger"><i class="fa-solid fa-location-pin"></i></span>
                                <input type="text" id="gmapsPasteInput" class="form-control" placeholder="Paste exact coordinates: 17.437462, 78.448251 or Google Maps link or Full Address" oninput="processGoogleMapsInput(this.value)">
                                <button type="button" class="btn btn-primary px-3" onclick="processGoogleMapsInput(document.getElementById('gmapsPasteInput').value)">
                                    <i class="fa-solid fa-location-crosshairs me-1"></i> Set Exact Location
                                </button>
                            </div>
                            <div class="form-text small text-muted">
                                💡 Tip: You can copy coordinates from Google Maps (e.g. <code>17.437462, 78.448251</code>) and paste them here or directly in the Latitude box!
                            </div>
                        </div>

                        <!-- Quick City & Hub Presets -->
                        <div class="mb-3 d-flex align-items-center gap-1 flex-wrap">
                            <span class="small text-muted me-1" style="font-size: 0.72rem;">Quick Presets:</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setSitePreset('Bengaluru', 12.971599, 77.594566)">Bengaluru</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setSitePreset('Hyderabad Hitec City', 17.448500, 78.374500)">Hyderabad</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setSitePreset('Mumbai BKC', 19.065700, 72.868700)">Mumbai</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setSitePreset('Chennai OMR', 12.979100, 80.220900)">Chennai</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setSitePreset('Delhi NCR (Noida)', 28.535500, 77.391000)">Delhi/NCR</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setSitePreset('Pune Hinjawadi', 18.591300, 73.738900)">Pune</button>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Site Latitude</label>
                                <input type="number" step="any" name="site_latitude" id="siteLatInput" class="form-control bg-white font-monospace fw-bold text-dark" placeholder="e.g. 17.437462" onpaste="handleDirectCoordPaste(event)" oninput="updateMapPreviewLink()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Site Longitude</label>
                                <input type="number" step="any" name="site_longitude" id="siteLonInput" class="form-control bg-white font-monospace fw-bold text-dark" placeholder="e.g. 78.448251" onpaste="handleDirectCoordPaste(event)" oninput="updateMapPreviewLink()">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold text-muted">Radius (Meters)</label>
                                <input type="number" name="site_radius" id="siteRadiusInput" class="form-control bg-white font-monospace" value="200" min="10" max="5000">
                            </div>
                            <div class="col-md-2">
                                <div class="btn-group w-100">
                                    <button type="button" class="btn btn-outline-primary btn-sm px-2" onclick="searchSiteLocation(this)" title="Search coordinates for site name">
                                        <i class="fa-solid fa-magnifying-glass-location me-1"></i> Search
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-2" onclick="acquireAdminLocation(this)" title="Use current device GPS">
                                        <i class="fa-solid fa-crosshairs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="gpsAdminFeedback" class="small text-muted mt-2 d-none"></div>
                        <div class="mt-2">
                            <a href="#" id="googleMapVerifyBtn" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 d-none">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Locked Pin on Google Maps
                            </a>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Shift Start Time</label>
                            <input type="time" name="shift_start" class="form-control bg-light" value="09:00">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Shift End Time</label>
                            <input type="time" name="shift_end" class="form-control bg-light" value="18:00">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Status</label>
                            <select name="status" class="form-select bg-light">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Punch URL Live Preview Box -->
                    <div class="p-3 bg-light rounded-4 border mb-4">
                        <div class="d-flex align-items-center mb-1">
                            <i class="fa-solid fa-qrcode text-primary me-2"></i>
                            <span class="fw-semibold text-dark">Auto-Generated Mobile Punch URL</span>
                        </div>
                        <p class="text-muted small mb-2">Each employee gets a permanent direct mobile check-in link:</p>
                        <div class="input-group">
                            <input type="text" id="previewUrl" class="form-control bg-white font-monospace text-primary" value="<?= punch_url($suggestedCode) ?>" readonly>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('employees') ?>" class="btn btn-light border px-4 rounded-pill">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm">
                            <i class="fa-solid fa-user-plus me-1"></i> Save & Generate Punch Link
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function updateUrlPreview(code) {
        const preview = document.getElementById('previewUrl');
        const trimmed = code.trim();
        if (trimmed) {
            preview.value = "<?= punch_url('') ?>" + encodeURIComponent(trimmed);
        } else {
            preview.value = '—';
        }
    }

    function updateMapPreviewLink() {
        const lat = document.getElementById('siteLatInput').value.trim();
        const lon = document.getElementById('siteLonInput').value.trim();
        const mapVerifyBtn = document.getElementById('googleMapVerifyBtn');
        if (lat && lon && mapVerifyBtn) {
            mapVerifyBtn.href = `https://www.google.com/maps?q=${lat},${lon}`;
            mapVerifyBtn.classList.remove('d-none');
        }
    }

    function handleDirectCoordPaste(e) {
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        if (pastedText) {
            const parsed = extractExactCoordinates(pastedText);
            if (parsed) {
                e.preventDefault();
                document.getElementById('siteLatInput').value = parsed.lat;
                document.getElementById('siteLonInput').value = parsed.lon;
                updateMapPreviewLink();
                const feedback = document.getElementById('gpsAdminFeedback');
                if (feedback) {
                    feedback.className = 'small text-success mt-2 d-block';
                    feedback.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Exact Google Maps Coordinates Locked: <strong>${parsed.lat}, ${parsed.lon}</strong>`;
                }
            }
        }
    }

    function acquireAdminLocation(btn) {
        const feedback = document.getElementById('gpsAdminFeedback');
        const latInput = document.getElementById('siteLatInput');
        const lonInput = document.getElementById('siteLonInput');

        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        const origHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Locating...';
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                btn.innerHTML = origHtml;
                btn.disabled = false;
                const lat = pos.coords.latitude.toFixed(6);
                const lon = pos.coords.longitude.toFixed(6);
                latInput.value = lat;
                lonInput.value = lon;
                updateMapPreviewLink();
                if (feedback) {
                    feedback.className = 'small text-success mt-2 d-block';
                    feedback.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Acquired GPS location: <strong>${lat}, ${lon}</strong> (Accuracy: &plusmn;${Math.round(pos.coords.accuracy)}m)`;
                    feedback.classList.remove('d-none');
                }
            },
            function(err) {
                btn.innerHTML = origHtml;
                btn.disabled = false;
                alert('Unable to retrieve GPS location: ' + err.message + '. Please ensure location permission is allowed.');
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    function setSitePreset(name, lat, lon) {
        const siteInput = document.getElementById('siteNameInput');
        const latInput = document.getElementById('siteLatInput');
        const lonInput = document.getElementById('siteLonInput');
        const feedback = document.getElementById('gpsAdminFeedback');

        if (siteInput && !siteInput.value) siteInput.value = name;
        if (latInput) latInput.value = lat;
        if (lonInput) lonInput.value = lon;
        updateMapPreviewLink();

        if (feedback) {
            feedback.className = 'small text-success mt-2 d-block';
            feedback.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Loaded <strong>${name}</strong> coordinates: <code>${lat}, ${lon}</code> (200m perimeter active)`;
            feedback.classList.remove('d-none');
        }
    }

    function dmsToDecimal(degrees, minutes, seconds, direction) {
        let dd = parseFloat(degrees) + (parseFloat(minutes) / 60) + (parseFloat(seconds) / (60 * 60));
        if (direction === 'S' || direction === 'W') {
            dd = dd * -1;
        }
        return dd;
    }

    function extractExactCoordinates(str) {
        if (!str) return null;
        str = str.trim();

        // 1. Direct Decimal: "17.437462, 78.448251" or "17.437462,78.448251" or "17.437462 78.448251"
        const decRegex = /(-?\d{1,2}\.\d+)[,\s]+(-?\d{1,3}\.\d+)/;
        const mDec = str.match(decRegex);
        if (mDec) {
            const lat = parseFloat(mDec[1]);
            const lon = parseFloat(mDec[2]);
            if (lat >= -90 && lat <= 90 && lon >= -180 && lon <= 180) {
                return { lat: lat.toFixed(6), lon: lon.toFixed(6) };
            }
        }

        // 2. DMS Coordinates: 17°26'14.9"N 78°26'53.7"E or 17°26'14.9" N, 78°26'53.7" E
        const dmsRegex = /(\d{1,2})[°\s]+(\d{1,2})['\s]+([\d.]+)["]?\s*([NSns])[,\s]+(\d{1,3})[°\s]+(\d{1,2})['\s]+([\d.]+)["]?\s*([EWew])/;
        const mDms = str.match(dmsRegex);
        if (mDms) {
            const lat = dmsToDecimal(mDms[1], mDms[2], mDms[3], mDms[4].toUpperCase());
            const lon = dmsToDecimal(mDms[5], mDms[6], mDms[7], mDms[8].toUpperCase());
            return { lat: lat.toFixed(6), lon: lon.toFixed(6) };
        }

        // 3. Direction suffixed decimals: 17.437462 N, 78.448251 E
        const dirDecRegex = /(\d{1,2}\.\d+)\s*([NSns])[,\s]+(\d{1,3}\.\d+)\s*([EWew])/;
        const mDir = str.match(dirDecRegex);
        if (mDir) {
            let lat = parseFloat(mDir[1]);
            if (mDir[2].toUpperCase() === 'S') lat = -lat;
            let lon = parseFloat(mDir[3]);
            if (mDir[4].toUpperCase() === 'W') lon = -lon;
            return { lat: lat.toFixed(6), lon: lon.toFixed(6) };
        }

        // 4. Google Maps URL @lat,lon
        const atMatch = str.match(/@(-?\d{1,2}\.\d+),(-?\d{1,3}\.\d+)/);
        if (atMatch) {
            return { lat: parseFloat(atMatch[1]).toFixed(6), lon: parseFloat(atMatch[2]).toFixed(6) };
        }

        // 5. Google Maps URL ?q=lat,lon or /place/lat,lon or ll=lat,lon
        const qMatch = str.match(/(?:[?&](?:q|ll)=|\/place\/)(-?\d{1,2}\.\d+)[,\s]+(-?\d{1,3}\.\d+)/);
        if (qMatch) {
            return { lat: parseFloat(qMatch[1]).toFixed(6), lon: parseFloat(qMatch[2]).toFixed(6) };
        }

        // 6. Google Maps data string !3dlat!4dlon
        const dataMatch = str.match(/!3d(-?\d{1,2}\.\d+)!4d(-?\d{1,3}\.\d+)/);
        if (dataMatch) {
            return { lat: parseFloat(dataMatch[1]).toFixed(6), lon: parseFloat(dataMatch[2]).toFixed(6) };
        }

        return null;
    }

    function processGoogleMapsInput(val) {
        if (!val || !val.trim()) return;
        const raw = val.trim();
        const siteInput = document.getElementById('siteNameInput');
        const latInput = document.getElementById('siteLatInput');
        const lonInput = document.getElementById('siteLonInput');
        const feedback = document.getElementById('gpsAdminFeedback');

        // Check if direct coordinates or Google Maps link
        const parsed = extractExactCoordinates(raw);
        if (parsed) {
            latInput.value = parsed.lat;
            lonInput.value = parsed.lon;
            updateMapPreviewLink();
            
            if (feedback) {
                feedback.className = 'small text-success mt-2 d-block';
                feedback.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Exact Google Maps Coordinates Locked: <strong>${parsed.lat}, ${parsed.lon}</strong> (Location preserved)`;
                feedback.classList.remove('d-none');
            }
            return;
        }

        // If user typed a full address, save as site name and geocode
        siteInput.value = raw;
        geocodeFullAddress(raw);
    }

    function geocodeFullAddress(fullAddress) {
        const feedback = document.getElementById('gpsAdminFeedback');
        const latInput = document.getElementById('siteLatInput');
        const lonInput = document.getElementById('siteLonInput');

        if (feedback) {
            feedback.className = 'small text-primary mt-2 d-block';
            feedback.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Searching map coordinates for address...`;
            feedback.classList.remove('d-none');
        }

        let cleaned = fullAddress
            .replace(/^(?:flat|door|d\.?no|plot|h\.?no|#|house|shop|office|unit)\s*[:.\-\s]*[0-9a-z\/\-]+,?\s*/i, '')
            .replace(/,\s*(?:near|opposite|opp|behind|beside)\s+[^,]+/i, '')
            .trim();

        const queries = [fullAddress, cleaned];

        function tryQuery(idx) {
            if (idx >= queries.length) {
                if (feedback) {
                    feedback.className = 'small text-muted mt-2 d-block';
                    feedback.innerHTML = `<i class="fa-solid fa-circle-info me-1"></i> Please paste exact Google Maps coordinates (e.g. <code>17.437462, 78.448251</code>) to lock the exact location.`;
                }
                return;
            }

            const q = queries[idx];
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=1`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat).toFixed(6);
                        const lon = parseFloat(data[0].lon).toFixed(6);
                        latInput.value = lat;
                        lonInput.value = lon;
                        updateMapPreviewLink();
                        if (feedback) {
                            feedback.className = 'small text-success mt-2 d-block';
                            feedback.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Address Located: <strong>${data[0].display_name.split(',').slice(0, 3).join(',')}</strong> (<code>${lat}, ${lon}</code>)`;
                            feedback.classList.remove('d-none');
                        }
                    } else {
                        tryQuery(idx + 1);
                    }
                })
                .catch(() => tryQuery(idx + 1));
        }

        tryQuery(0);
    }

    function searchSiteLocation(btn) {
        const siteInput = document.getElementById('siteNameInput');
        const query = siteInput ? siteInput.value.trim() : '';
        if (!query) {
            alert('Please type or paste a site address, city name, or Google Maps coordinates first.');
            return;
        }
        processGoogleMapsInput(query);
    }

    function previewEmployeeAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('avatarPreviewImg');
                if (img) img.src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const codeInput = document.getElementById('employeeCodeInput');
        if (codeInput) {
            codeInput.addEventListener('input', function() {
                updateUrlPreview(this.value);
            });
        }
        updateMapPreviewLink();
    });
</script>
