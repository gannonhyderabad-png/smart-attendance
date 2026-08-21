<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">Edit Employee - <?= e($employee['name']) ?></h5>
                    <small class="text-muted">Update employee profile and working shift details</small>
                </div>
                <a href="<?= base_url('employees') ?>" class="btn btn-light btn-sm border rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="<?= base_url('employees/update/' . $employee['id']) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <!-- Employee Profile Photo Card -->
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border mb-4">
                        <div class="position-relative">
                            <?php if (!empty($employee['photo'])): ?>
                                <img id="avatarPreviewImg" src="<?= uploaded_url($employee['photo']) ?>" class="rounded-circle border shadow-sm object-fit-cover" style="width: 72px; height: 72px;" alt="Avatar">
                            <?php else: ?>
                                <img id="avatarPreviewImg" src="https://ui-avatars.com/api/?name=<?= urlencode($employee['name']) ?>&background=4f46e5&color=fff&size=128" class="rounded-circle border shadow-sm object-fit-cover" style="width: 72px; height: 72px;" alt="Avatar">
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-label small fw-semibold text-dark mb-1">
                                <i class="fa-solid fa-camera text-primary me-1"></i> Update Employee Profile Photo
                            </label>
                            <input type="file" name="photo" id="photoFileInput" class="form-control form-control-sm bg-white" accept="image/jpeg,image/png,image/webp,image/jpg" onchange="previewEmployeeAvatar(this)">
                            <div class="form-text small" style="font-size: 0.72rem;">Leave empty to keep current photo. Supported formats: JPG, PNG, WEBP.</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Employee Code <span class="text-danger">*</span></label>
                            <input type="text" name="employee_code" class="form-control bg-light font-monospace fw-bold text-primary" value="<?= e($employee['employee_code']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-light" value="<?= e($employee['name']) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control bg-light" value="<?= e($employee['email'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Mobile / WhatsApp Phone</label>
                            <input type="text" name="phone" class="form-control bg-light" value="<?= e($employee['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Department</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-building"></i></span>
                                <input type="text" name="department" class="form-control bg-light border-start-0" list="departmentList" value="<?= e($employee['department_name'] ?? $employee['department'] ?? '') ?>" placeholder="e.g. Engineering, Operations">
                            </div>
                            <datalist id="departmentList">
                                 <?php foreach ($departmentList as $deptName): ?>
                                     <option value="<?= e($deptName) ?>">
                                 <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Job Designation</label>
                            <input type="text" name="designation" class="form-control bg-light" value="<?= e($employee['designation'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Assigned Project</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-diagram-project"></i></span>
                                <input type="text" name="project" class="form-control bg-light border-start-0" list="projectList" value="<?= e($employee['project'] ?? '') ?>" placeholder="e.g. ERP Portal, Site Alpha">
                            </div>
                            <datalist id="projectList">
                                <?php foreach ($projectList as $projName): ?>
                                    <option value="<?= e($projName) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Work Site / Location</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-location-dot"></i></span>
                                <input type="text" name="site" id="siteNameInput" class="form-control bg-light border-start-0" list="siteList" value="<?= e($employee['site'] ?? '') ?>" placeholder="e.g. Bengaluru, Hyderabad, HRO" onchange="autoLookupSiteCoordinates(this.value)">
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
                                <small class="text-muted">Attendance will be strictly blocked if employee is not within 200m of these coordinates</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="geofence_enabled" value="0">
                                <input class="form-check-input" type="checkbox" name="geofence_enabled" value="1" id="geofenceEnabledSwitch" <?= !empty($employee['geofence_enabled']) ? 'checked' : '' ?>>
                                <label class="form-check-label small fw-semibold text-dark" for="geofenceEnabledSwitch">Enable GPS Geofence</label>
                            </div>
                        </div>

                        <!-- Smart Google Maps Address / Link / Coordinates Input Box -->
                        <div class="mb-3 p-3 bg-white rounded-3 border">
                            <label class="form-label small fw-bold text-dark mb-1">
                                <i class="fa-brands fa-google text-danger me-1"></i> Paste Google Maps Full Address, Share Link, or GPS Coordinates
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-danger"><i class="fa-solid fa-location-pin"></i></span>
                                <input type="text" id="gmapsPasteInput" class="form-control" placeholder="e.g. Flat 402, Road No 10, Banjara Hills, Hyderabad 500034 OR https://maps.app.goo.gl/... OR 17.437462, 78.448251" oninput="processGoogleMapsInput(this.value)">
                                <button type="button" class="btn btn-primary px-3" onclick="processGoogleMapsInput(document.getElementById('gmapsPasteInput').value)">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto-Locate
                                </button>
                            </div>
                            <div class="form-text small text-muted">
                                You can copy the full door/street address from Google Maps, a share link, or raw coordinates — the system will automatically extract the GPS location!
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
                                <input type="number" step="any" name="site_latitude" id="siteLatInput" class="form-control bg-white font-monospace" value="<?= e($employee['site_latitude'] ?? '') ?>" placeholder="e.g. 17.437462">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Site Longitude</label>
                                <input type="number" step="any" name="site_longitude" id="siteLonInput" class="form-control bg-white font-monospace" value="<?= e($employee['site_longitude'] ?? '') ?>" placeholder="e.g. 78.448251">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold text-muted">Radius (Meters)</label>
                                <input type="number" name="site_radius" id="siteRadiusInput" class="form-control bg-white font-monospace" value="<?= e($employee['site_radius'] ?? 200) ?>" min="10" max="5000">
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
                            <?php 
                            $hasCoords = !empty($employee['site_latitude']) && !empty($employee['site_longitude']);
                            $mapUrl = $hasCoords ? "https://www.google.com/maps?q=" . $employee['site_latitude'] . "," . $employee['site_longitude'] : "#";
                            ?>
                            <a href="<?= $mapUrl ?>" id="googleMapVerifyBtn" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 <?= $hasCoords ? '' : 'd-none' ?>">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Verify Pin on Google Maps
                            </a>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Shift Start Time</label>
                            <input type="time" name="shift_start" class="form-control bg-light" value="<?= substr($employee['shift_start'] ?? '09:00:00', 0, 5) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Shift End Time</label>
                            <input type="time" name="shift_end" class="form-control bg-light" value="<?= substr($employee['shift_end'] ?? '18:00:00', 0, 5) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Status</label>
                            <select name="status" class="form-select bg-light">
                                <option value="active" <?= ($employee['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($employee['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('employees') ?>" class="btn btn-light border px-4 rounded-pill">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Update Employee
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function acquireAdminLocation(btn) {
        const feedback = document.getElementById('gpsAdminFeedback');
        const latInput = document.getElementById('siteLatInput');
        const lonInput = document.getElementById('siteLonInput');
        const mapVerifyBtn = document.getElementById('googleMapVerifyBtn');

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
                if (feedback) {
                    feedback.className = 'small text-success mt-2 d-block';
                    feedback.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Acquired GPS location: <strong>${lat}, ${lon}</strong> (Accuracy: &plusmn;${Math.round(pos.coords.accuracy)}m)`;
                    feedback.classList.remove('d-none');
                }
                if (mapVerifyBtn) {
                    mapVerifyBtn.href = `https://www.google.com/maps?q=${lat},${lon}`;
                    mapVerifyBtn.classList.remove('d-none');
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
        const mapVerifyBtn = document.getElementById('googleMapVerifyBtn');

        if (siteInput) siteInput.value = name;
        if (latInput) latInput.value = lat;
        if (lonInput) lonInput.value = lon;

        if (feedback) {
            feedback.className = 'small text-success mt-2 d-block';
            feedback.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Loaded <strong>${name}</strong> coordinates: <code>${lat}, ${lon}</code> (200m perimeter active)`;
            feedback.classList.remove('d-none');
        }
        if (mapVerifyBtn) {
            mapVerifyBtn.href = `https://www.google.com/maps?q=${lat},${lon}`;
            mapVerifyBtn.classList.remove('d-none');
        }
    }

    function parseGoogleMapsAddressOrCoords(inputVal) {
        if (!inputVal) return null;
        const str = inputVal.trim();

        // 1. Direct Latitude, Longitude (e.g. "17.437462, 78.448251" or "17.437462 78.448251")
        const coordRegex = /^(-?\d{1,2}\.\d+)[,\s]+(-?\d{1,3}\.\d+)$/;
        const mCoord = str.match(coordRegex);
        if (mCoord) {
            return { lat: parseFloat(mCoord[1]).toFixed(6), lon: parseFloat(mCoord[2]).toFixed(6) };
        }

        // 2. Google Maps URL with @lat,lon (e.g. /@17.437462,78.448251,17z)
        const atCoord = str.match(/@(-?\d{1,2}\.\d+),(-?\d{1,3}\.\d+)/);
        if (atCoord) {
            return { lat: parseFloat(atCoord[1]).toFixed(6), lon: parseFloat(atCoord[2]).toFixed(6) };
        }

        // 3. Google Maps URL with ?q=lat,lon or /place/lat,lon or ll=lat,lon
        const qCoord = str.match(/(?:[?&](?:q|ll)=|\/place\/)(-?\d{1,2}\.\d+)[,\s]+(-?\d{1,3}\.\d+)/);
        if (qCoord) {
            return { lat: parseFloat(qCoord[1]).toFixed(6), lon: parseFloat(qCoord[2]).toFixed(6) };
        }

        // 4. Embed / data URL with !3dlat!4dlon
        const dataCoord = str.match(/!3d(-?\d{1,2}\.\d+)!4d(-?\d{1,3}\.\d+)/);
        if (dataCoord) {
            return { lat: parseFloat(dataCoord[1]).toFixed(6), lon: parseFloat(dataCoord[2]).toFixed(6) };
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
        const mapPreview = document.getElementById('googleMapVerifyBtn');

        // Check if direct coordinates or Google Maps link
        const parsed = parseGoogleMapsAddressOrCoords(raw);
        if (parsed) {
            latInput.value = parsed.lat;
            lonInput.value = parsed.lon;
            
            if (feedback) {
                feedback.className = 'small text-success mt-2 d-block';
                feedback.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Exact Google Maps Coordinates Extracted: <strong>${parsed.lat}, ${parsed.lon}</strong>`;
                feedback.classList.remove('d-none');
            }
            if (mapPreview) {
                mapPreview.href = `https://www.google.com/maps?q=${parsed.lat},${parsed.lon}`;
                mapPreview.classList.remove('d-none');
            }

            // Reverse geocode to get clean address if site name is empty or a URL
            if (!siteInput.value || siteInput.value.includes('http') || siteInput.value.includes('@')) {
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${parsed.lat}&lon=${parsed.lon}`)
                    .then(r => r.json())
                    .then(d => {
                        if (d && d.display_name) {
                            siteInput.value = d.display_name.split(',').slice(0, 4).join(',').trim();
                        }
                    }).catch(() => {});
            }
            return;
        }

        // Full text address (with door numbers, streets, etc.)
        siteInput.value = raw;
        geocodeFullAddress(raw);
    }

    function geocodeFullAddress(fullAddress) {
        const feedback = document.getElementById('gpsAdminFeedback');
        const latInput = document.getElementById('siteLatInput');
        const lonInput = document.getElementById('siteLonInput');
        const mapPreview = document.getElementById('googleMapVerifyBtn');

        if (feedback) {
            feedback.className = 'small text-primary mt-2 d-block';
            feedback.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Searching map coordinates for address...`;
            feedback.classList.remove('d-none');
        }

        // Clean door numbers and flat prefixes to geocode accurately on maps
        let cleaned = fullAddress
            .replace(/^(?:flat|door|d\.?no|plot|h\.?no|#|house|shop|office|unit)\s*[:.\-\s]*[0-9a-z\/\-]+,?\s*/i, '')
            .replace(/,\s*(?:near|opposite|opp|behind|beside)\s+[^,]+/i, '')
            .trim();

        const queries = [fullAddress, cleaned];

        function tryQuery(idx) {
            if (idx >= queries.length) {
                autoLookupSiteCoordinates(fullAddress);
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
                        if (feedback) {
                            feedback.className = 'small text-success mt-2 d-block';
                            feedback.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Address Located: <strong>${data[0].display_name.split(',').slice(0, 3).join(',')}</strong> (<code>${lat}, ${lon}</code>)`;
                            feedback.classList.remove('d-none');
                        }
                        if (mapPreview) {
                            mapPreview.href = `https://www.google.com/maps?q=${lat},${lon}`;
                            mapPreview.classList.remove('d-none');
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
            alert('Please type or paste a site address, city name, or Google Maps link first.');
            return;
        }
        processGoogleMapsInput(query);
    }

    function autoLookupSiteCoordinates(name) {
        const lower = name.toLowerCase();
        if (lower.includes('bengalur') || lower.includes('bangalor')) {
            setSitePreset('Bengaluru', 12.971599, 77.594566);
        } else if (lower.includes('hyderabad') || lower.includes('cyber') || lower.includes('telangana')) {
            setSitePreset('Hyderabad Hitec City', 17.448500, 78.374500);
        } else if (lower.includes('mumbai') || lower.includes('bombay') || lower.includes('bkc')) {
            setSitePreset('Mumbai BKC', 19.065700, 72.868700);
        } else if (lower.includes('chennai') || lower.includes('madras')) {
            setSitePreset('Chennai OMR', 12.979100, 80.220900);
        } else if (lower.includes('delhi') || lower.includes('noida') || lower.includes('gurgaon')) {
            setSitePreset('Delhi NCR', 28.535500, 77.391000);
        } else if (lower.includes('pune')) {
            setSitePreset('Pune', 18.520400, 73.856700);
        } else if (lower.includes('kolkata') || lower.includes('calcutta')) {
            setSitePreset('Kolkata', 22.572600, 88.363900);
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
</script>
