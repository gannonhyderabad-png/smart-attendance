#!/bin/sh
# =============================================================================
# Smart Attendance - Docker Entrypoint
# Handles Render Persistent Disk setup so data NEVER gets erased on redeploy.
# =============================================================================

set -e

echo "============================================================"
echo "  SmartPunch Attendance - Starting Up"
echo "  $(date)"
echo "============================================================"

# ─── 1. Persistent Database Directory ────────────────────────────────────────
# On Render, /var/www/html/database is mounted as a Persistent Disk.
# This directory survives redeploys and restarts.
DB_DIR="/var/www/html/database"
DB_FILE="${DB_DIR}/attendance.sqlite"

mkdir -p "${DB_DIR}"
chmod 777 "${DB_DIR}"

if [ ! -f "${DB_FILE}" ]; then
    echo "[INIT] First boot detected — creating fresh SQLite database at ${DB_FILE}"
    touch "${DB_FILE}"
    chmod 666 "${DB_FILE}"
else
    echo "[OK] Persistent database found: ${DB_FILE} ($(du -sh ${DB_FILE} | cut -f1) on disk)"
fi

# ─── 2. Persistent Uploads Directory ─────────────────────────────────────────
# Render Persistent Disk is mounted at /var/www/html/database
# We store uploads INSIDE the same persistent disk under a sibling path.
# The symlink trick: keep code in /var/www/html/public/uploads but redirect
# actual storage to /var/www/html/database/_uploads (on the persistent disk).

PERSISTENT_UPLOADS="${DB_DIR}/_uploads"
APP_UPLOADS="/var/www/html/public/uploads"

mkdir -p "${PERSISTENT_UPLOADS}/avatars"
mkdir -p "${PERSISTENT_UPLOADS}/punches"
mkdir -p "${PERSISTENT_UPLOADS}/leaves"
chmod -R 777 "${PERSISTENT_UPLOADS}"

# Replace the build-time uploads folder with a symlink to the persistent volume
if [ -L "${APP_UPLOADS}" ]; then
    echo "[OK] Uploads symlink already exists: ${APP_UPLOADS} -> ${PERSISTENT_UPLOADS}"
elif [ -d "${APP_UPLOADS}" ]; then
    # Move any files that were baked into the image (e.g. default avatars)
    cp -rn "${APP_UPLOADS}/." "${PERSISTENT_UPLOADS}/" 2>/dev/null || true
    rm -rf "${APP_UPLOADS}"
    ln -s "${PERSISTENT_UPLOADS}" "${APP_UPLOADS}"
    echo "[INIT] Created uploads symlink: ${APP_UPLOADS} -> ${PERSISTENT_UPLOADS}"
else
    ln -s "${PERSISTENT_UPLOADS}" "${APP_UPLOADS}"
    echo "[INIT] Created uploads symlink: ${APP_UPLOADS} -> ${PERSISTENT_UPLOADS}"
fi

# ─── 3. Environment Summary ───────────────────────────────────────────────────
echo ""
echo "  DB Driver : ${DB_CONNECTION:-sqlite}"
echo "  DB File   : ${DB_FILE}"
echo "  Uploads   : ${APP_UPLOADS} -> ${PERSISTENT_UPLOADS}"
echo "  Port      : ${PORT:-80}"
echo ""
echo "============================================================"
echo "  Starting PHP web server..."
echo "============================================================"

# ─── 4. Start Server ─────────────────────────────────────────────────────────
exec PHP_CLI_SERVER_WORKERS=8 php -S "0.0.0.0:${PORT:-80}" /var/www/html/index.php
