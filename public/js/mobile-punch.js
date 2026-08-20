/**
 * Mobile Punch Client Logic
 */

let currentWorkedSeconds = window.PUNCH_CONFIG ? window.PUNCH_CONFIG.workedSeconds : 0;
let currentStatus = window.PUNCH_CONFIG ? window.PUNCH_CONFIG.currentStatus : 'OUT';
let timerInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    // 1. Start live clock
    updateClock();
    setInterval(updateClock, 1000);

    // 2. Start duration timer if currently IN
    if (currentStatus === 'IN') {
        startDurationTimer();
    }
});

function updateClock() {
    const clockEl = document.getElementById('liveClock');
    if (!clockEl) return;
    const now = new Date();
    clockEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
}

function startDurationTimer() {
    if (timerInterval) clearInterval(timerInterval);
    const counterEl = document.getElementById('durationCounter');
    const wrapper = document.getElementById('workingTimerWrapper');
    if (wrapper) wrapper.classList.remove('d-none');

    timerInterval = setInterval(function() {
        currentWorkedSeconds++;
        if (counterEl) {
            counterEl.textContent = formatDuration(currentWorkedSeconds);
        }
    }, 1000);
}

function stopDurationTimer() {
    if (timerInterval) clearInterval(timerInterval);
    const wrapper = document.getElementById('workingTimerWrapper');
    if (wrapper) wrapper.classList.add('d-none');
}

function formatDuration(totalSeconds) {
    const hours = Math.floor(totalSeconds / 3600);
    const remainder = totalSeconds % 3600;
    const minutes = Math.floor(remainder / 60);
    const seconds = remainder % 60;
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

// Play modern audio feedback tone
function playChime(isSuccess = true) {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = isSuccess ? 'sine' : 'sawtooth';
        osc.frequency.setValueAtTime(isSuccess ? 587.33 : 220, ctx.currentTime); // D5 or A3
        if (isSuccess) {
            osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15); // A5
        }

        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);

        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.3);
    } catch (e) {
        // AudioContext not allowed or unsupported
    }
}

// Handle Touch Punch IN / OUT
function handlePunch(type) {
    const btnIn = document.getElementById('btnPunchIn');
    const btnOut = document.getElementById('btnPunchOut');
    const alertBox = document.getElementById('punchAlert');

    // Prevent clicking if disabled
    if (type === currentStatus) {
        showAlert('warning', `You are already punched ${type}.`);
        return;
    }

    const activeBtn = (type === 'IN') ? btnIn : btnOut;
    const origHtml = activeBtn.innerHTML;
    activeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Recording...';
    activeBtn.classList.add('disabled');

    // Attempt to acquire Geolocation if supported
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(pos) {
                sendPunchRequest(type, pos.coords.latitude, pos.coords.longitude, activeBtn, origHtml);
            },
            function(err) {
                // Denied or timeout, proceed without coordinates
                sendPunchRequest(type, null, null, activeBtn, origHtml);
            },
            { timeout: 3000 }
        );
    } else {
        sendPunchRequest(type, null, null, activeBtn, origHtml);
    }
}

function sendPunchRequest(type, lat, lng, activeBtn, origHtml) {
    const config = window.PUNCH_CONFIG;

    fetch(config.recordUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            punch_type: type,
            latitude: lat,
            longitude: lng
        })
    })
    .then(response => response.json())
    .then(data => {
        activeBtn.innerHTML = origHtml;

        if (data.success) {
            // Haptic vibration feedback
            if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
            playChime(true);

            currentStatus = type;
            showAlert('success', data.message);
            updateUIState(type, data);
        } else {
            if (navigator.vibrate) navigator.vibrate(200);
            playChime(false);
            showAlert('danger', data.message);
            updateButtonStates();
        }
    })
    .catch(err => {
        activeBtn.innerHTML = origHtml;
        showAlert('danger', 'Network connection error. Please check your internet connection.');
        updateButtonStates();
    });
}

function updateUIState(type, data) {
    const statusBanner = document.getElementById('statusBanner');
    const statusText = document.getElementById('statusText');
    const lastPunchInfo = document.getElementById('lastPunchInfo');

    if (type === 'IN') {
        statusBanner.className = 'status-banner mb-4 p-3 rounded-4 status-in';
        statusText.textContent = 'Currently Checked IN';
        startDurationTimer();
    } else {
        statusBanner.className = 'status-banner mb-4 p-3 rounded-4 status-out';
        statusText.textContent = 'Currently Checked OUT';
        stopDurationTimer();
    }

    if (data.punch_time) {
        const timeStr = new Date(data.punch_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        lastPunchInfo.innerHTML = `Last Punch: <strong>${type}</strong> at ${timeStr}`;
    }

    updateButtonStates();

    // Reload punches history list if present
    if (data.today_punches) {
        renderPunchesList(data.today_punches);
    }
}

function updateButtonStates() {
    const btnIn = document.getElementById('btnPunchIn');
    const btnOut = document.getElementById('btnPunchOut');

    if (currentStatus === 'IN') {
        btnIn.classList.add('disabled', 'opacity-50');
        btnOut.classList.remove('disabled', 'opacity-50');
    } else {
        btnOut.classList.add('disabled', 'opacity-50');
        btnIn.classList.remove('disabled', 'opacity-50');
    }
}

function renderPunchesList(punches) {
    const container = document.getElementById('punchesList');
    const badge = document.getElementById('punchCountBadge');
    if (badge) badge.textContent = punches.length;

    if (!container) return;

    if (punches.length === 0) {
        container.innerHTML = '<div class="text-center text-muted small py-3">No punches recorded today.</div>';
        return;
    }

    let html = '<div class="list-group list-group-flush">';
    punches.forEach(p => {
        const typeBadge = p.punch_type === 'IN' ? 'bg-success' : 'bg-danger';
        const timeStr = new Date(p.punch_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        html += `
            <div class="list-group-item d-flex justify-content-between align-items-center px-2 py-2">
                <div class="d-flex align-items-center">
                    <span class="badge ${typeBadge} me-2">${p.punch_type}</span>
                    <span class="small font-monospace">${timeStr}</span>
                </div>
                <span class="badge bg-light text-muted border small" style="font-size: 0.7rem;">
                    <i class="fa-solid fa-network-wired me-1"></i>${p.ip_address || 'Local'}
                </span>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

function showAlert(type, message) {
    const alertBox = document.getElementById('punchAlert');
    if (!alertBox) return;

    alertBox.className = `alert alert-${type} small text-start rounded-3 mt-3 shadow-sm`;
    alertBox.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'} me-1"></i> ${message}`;
    alertBox.classList.remove('d-none');
}
