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
                            <label class="form-label small fw-semibold text-muted">Work Site / Location Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-location-dot"></i></span>
                                <input type="text" name="site" id="siteNameInput" class="form-control bg-light border-start-0" list="siteList" value="<?= e($employee['site'] ?? '') ?>" placeholder="e.g. Hyderabad Office, Site Alpha">
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
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">
                                    <i class="fa-solid fa-map-location-dot text-primary me-2"></i>Site GPS Geofencing & Perimeter Guard
                                </h6>
                                <small class="text-muted">Attendance will be strictly verified within this perimeter</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="geofence_enabled" value="0">
                                <input class="form-check-input" type="checkbox" name="geofence_enabled" value="1" id="geofenceEnabledSwitch" <?= !empty($employee['geofence_enabled']) ? 'checked' : '' ?>>
                                <label class="form-check-label small fw-semibold text-dark" for="geofenceEnabledSwitch">Enable GPS Geofence</label>
                            </div>
                        </div>

                        <!-- Smart Google Maps Address / Link / Coordinates Input Box -->
                        <div class="mb-3 p-3 bg-white rounded-3 border shadow-sm">
                            <label class="form-label small fw-bold text-dark mb-1">
                                <i class="fa-brands fa-google text-danger me-1"></i> Paste Google Maps Link, Coordinates, or Full Site Address
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-danger"><i class="fa-solid fa-location-dot"></i></span>
                                <input type="text" id="gmapsPasteInput" class="form-control" placeholder="Paste Google Maps link (e.g. maps.app.goo.gl/...), coordinates (17.42899, 78.454556), or Address" onkeydown="if(event.key==='Enter'){event.preventDefault();processGoogleMapsInput(this.value);}">
                                <button type="button" class="btn btn-primary px-3 fw-semibold" id="resolveLocationBtn" onclick="processGoogleMapsInput(document.getElementById('gmapsPasteInput').value)">
                                    <i class="fa-solid fa-location-crosshairs me-1"></i> Set Exact Location
                                </button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-1">
                                <div class="form-text small text-muted">
                                    💡 Supports Google Maps share links (<code>maps.app.goo.gl</code>), <code>@17.42899,78.454556</code>, or click on the map below.
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill py-0 px-2" style="font-size: 0.75rem;" onclick="acquireAdminLocation(this)">
                                    <i class="fa-solid fa-crosshairs me-1 text-primary"></i> Locate My GPS
                                </button>
                            </div>
                        </div>

                        <!-- Interactive Leaflet Map Picker -->
                        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-dark mb-0">
                                    <i class="fa-solid fa-map me-1 text-primary"></i> Interactive Site Map Picker <small class="text-muted">(Click or drag the red marker to set exact location)</small>
                                </label>
                                <span class="badge bg-light text-muted border" id="mapCoordsBadge">17.428990, 78.454556</span>
                            </div>
                            <div id="geofenceMap" style="height: 250px; width: 100%; border-radius: 12px; z-index: 1;" class="border shadow-sm"></div>
                        </div>

                        <!-- Quick City & Hub Presets -->
                        <div class="mb-3 d-flex align-items-center gap-1 flex-wrap">
                            <span class="small text-muted me-1" style="font-size: 0.72rem;">Quick Presets:</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setSitePreset('Hyderabad Hitec City', 17.448500, 78.374500)">Hyderabad</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setSitePreset('Bengaluru', 12.971599, 77.594566)">Bengaluru</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setSitePreset('Mumbai BKC', 19.065700, 72.868700)">Mumbai</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setSitePreset('Chennai OMR', 12.979100, 80.220900)">Chennai</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setSitePreset('Delhi NCR (Noida)', 28.535500, 77.391000)">Delhi/NCR</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setSitePreset('Pune Hinjawadi', 18.591300, 73.738900)">Pune</button>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Site Latitude <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="site_latitude" id="siteLatInput" class="form-control bg-white font-monospace fw-bold text-dark" value="<?= e($employee['site_latitude'] ?? '') ?>" placeholder="e.g. 17.428990" onpaste="handleDirectCoordPaste(event)" oninput="handleManualCoordChange()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Site Longitude <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="site_longitude" id="siteLonInput" class="form-control bg-white font-monospace fw-bold text-dark" value="<?= e($employee['site_longitude'] ?? '') ?>" placeholder="e.g. 78.454556" onpaste="handleDirectCoordPaste(event)" oninput="handleManualCoordChange()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Allowed Radius (Meters)</label>
                                <div class="input-group">
                                    <input type="number" name="site_radius" id="siteRadiusInput" class="form-control bg-white font-monospace fw-bold" value="<?= e($employee['site_radius'] ?? 200) ?>" min="10" max="5000" oninput="updateMapCircleRadius(this.value)">
                                    <span class="input-group-text bg-light small">meters</span>
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
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Locked Pin on Google Maps
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
    let mapInstance = null;
    let mapMarker = null;
    let mapCircle = null;

    function initGeofenceMap() {
        const latInput = document.getElementById('siteLatInput');
        const lonInput = document.getElementById('siteLonInput');
        const radiusInput = document.getElementById('siteRadiusInput');

        let defaultLat = parseFloat(latInput.value) || 17.428990;
        let defaultLon = parseFloat(lonInput.value) || 78.454556;
        let radius = parseInt(radiusInput.value) || 200;

        const mapContainer = document.getElementById('geofenceMap');
        if (!mapContainer || typeof L === 'undefined') return;

        if (mapInstance) {
            mapInstance.remove();
        }

        mapInstance = L.map('geofenceMap').setView([defaultLat, defaultLon], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>'
        }).addTo(mapInstance);

        // Marker
        mapMarker = L.marker([defaultLat, defaultLon], {
            draggable: true,
            autoPan: true
        }).addTo(mapInstance);

        // Circle perimeter
        mapCircle = L.circle([defaultLat, defaultLon], {
            color: '#2563eb',
            fillColor: '#3b82f6',
            fillOpacity: 0.18,
            radius: radius
        }).addTo(mapInstance);

        updateCoordsDisplay(defaultLat, defaultLon);

        // Drag marker event
        mapMarker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            setMapCoordinates(pos.lat, pos.lng, false);
        });

        // Click map event
        mapInstance.on('click', function(e) {
            setMapCoordinates(e.latlng.lat, e.latlng.lng, false);
        });

        // Invalidate size in case of modal or tab rendering
        setTimeout(() => mapInstance.invalidateSize(), 300);
    }

    function setMapCoordinates(lat, lon, panMap = true) {
        lat = parseFloat(lat);
        lon = parseFloat(lon);
        if (isNaN(lat) || isNaN(lon)) return;

        const latFixed = lat.toFixed(6);
        const lonFixed = lon.toFixed(6);

        document.getElementById('siteLatInput').value = latFixed;
        document.getElementById('siteLonInput').value = lonFixed;

        if (mapMarker) mapMarker.setLatLng([lat, lon]);
        if (mapCircle) mapCircle.setLatLng([lat, lon]);
        if (panMap && mapInstance) {
            mapInstance.setView([lat, lon], Math.max(mapInstance.getZoom(), 16));
        }

        updateCoordsDisplay(latFixed, lonFixed);
        updateMapPreviewLink();
    }

    function updateCoordsDisplay(lat, lon) {
        const badge = document.getElementById('mapCoordsBadge');
        if (badge) badge.innerText = `${lat}, ${lon}`;
    }

    function updateMapCircleRadius(radiusVal) {
        const rad = parseInt(radiusVal) || 200;
        if (mapCircle) {
            mapCircle.setRadius(rad);
        }
    }

    function handleManualCoordChange() {
        const lat = parseFloat(document.getElementById('siteLatInput').value);
        const lon = parseFloat(document.getElementById('siteLonInput').value);
        if (!isNaN(lat) && !isNaN(lon)) {
            setMapCoordinates(lat, lon, true);
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
            processGoogleMapsInput(pastedText);
        }
    }

    function processGoogleMapsInput(val) {
        if (!val || !val.trim()) {
            alert('Please paste a Google Maps link, coordinates (e.g. 17.42899, 78.454556), or site address.');
            return;
        }

        const raw = val.trim();
        const feedback = document.getElementById('gpsAdminFeedback');
        const resolveBtn = document.getElementById('resolveLocationBtn');

        if (resolveBtn) {
            resolveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Locating...';
            resolveBtn.disabled = true;
        }

        if (feedback) {
            feedback.className = 'small text-primary mt-2 d-block';
            feedback.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Resolving Google Maps location & GPS coordinates...`;
            feedback.classList.remove('d-none');
        }

        // Call backend location resolver
        fetch('<?= base_url("api/resolve-location") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ query: raw })
        })
        .then(res => res.json())
        .then(data => {
            if (resolveBtn) {
                resolveBtn.innerHTML = '<i class="fa-solid fa-location-crosshairs me-1"></i> Set Exact Location';
                resolveBtn.disabled = false;
            }

            if (data.success && data.lat && data.lon) {
                setMapCoordinates(data.lat, data.lon, true);

                if (feedback) {
                    feedback.className = 'small text-success mt-2 d-block';
                    feedback.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Exact Location Locked: <strong>${data.lat}, ${data.lon}</strong> ${data.formatted_address ? '— ' + data.formatted_address : ''}`;
                }
            } else {
                if (feedback) {
                    feedback.className = 'small text-danger mt-2 d-block';
                    feedback.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i> ${data.message || 'Could not resolve coordinates. Please click on the map to pin location.'}`;
                }
            }
        })
        .catch(err => {
            if (resolveBtn) {
                resolveBtn.innerHTML = '<i class="fa-solid fa-location-crosshairs me-1"></i> Set Exact Location';
                resolveBtn.disabled = false;
            }
            if (feedback) {
                feedback.className = 'small text-danger mt-2 d-block';
                feedback.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i> Location lookup error: ${err.message}. You can click the map directly to drop the pin.`;
            }
        });
    }

    function acquireAdminLocation(btn) {
        const feedback = document.getElementById('gpsAdminFeedback');
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        const origHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                btn.innerHTML = origHtml;
                btn.disabled = false;
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;
                setMapCoordinates(lat, lon, true);
                if (feedback) {
                    feedback.className = 'small text-success mt-2 d-block';
                    feedback.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Acquired GPS location: <strong>${lat.toFixed(6)}, ${lon.toFixed(6)}</strong> (Accuracy: &plusmn;${Math.round(pos.coords.accuracy)}m)`;
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
        const feedback = document.getElementById('gpsAdminFeedback');
        if (siteInput && !siteInput.value) siteInput.value = name;
        setMapCoordinates(lat, lon, true);
        if (feedback) {
            feedback.className = 'small text-success mt-2 d-block';
            feedback.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Loaded <strong>${name}</strong> coordinates: <code>${lat}, ${lon}</code>`;
            feedback.classList.remove('d-none');
        }
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
        initGeofenceMap();
    });
</script>
