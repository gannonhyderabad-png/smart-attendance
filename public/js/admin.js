/**
 * Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle for mobile
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }

    // Live Server Clock in Admin header
    const clockEl = document.getElementById('liveServerClock');
    if (clockEl) {
        setInterval(function() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
            const dateStr = now.toLocaleDateString([], { day: '2-digit', month: 'short', year: 'numeric' });
            clockEl.textContent = `${dateStr} | ${timeStr}`;
        }, 1000);
    }
});

// Copy link to clipboard with visual tooltip/feedback
function copyToClipboard(text, btnElement) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(onCopied).catch(fallbackCopy);
    } else {
        fallbackCopy();
    }

    function fallbackCopy() {
        const temp = document.createElement('input');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        onCopied();
    }

    function onCopied() {
        if (btnElement) {
            const origHtml = btnElement.innerHTML;
            btnElement.innerHTML = '<i class="fa-solid fa-check text-success"></i> Copied!';
            btnElement.classList.add('btn-success', 'text-white');
            setTimeout(function() {
                btnElement.innerHTML = origHtml;
                btnElement.classList.remove('btn-success', 'text-white');
            }, 2000);
        }
    }
}
