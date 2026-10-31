#!/bin/sh
# =============================================================================
# Smart Attendance - Docker Entrypoint
# Handles Render Persistent Disk setup so data NEVER gets erased on redeploy.
# =============================================================================

echo "============================================================"
echo "  SmartPunch Attendance - Starting Up"
echo "  $(date)"
echo "============================================================"

# 1. Persistent Database Directory (/var/www/html/database on Render Disk)
DB_DIR="/var/www/html/database"
DB_FILE="${DB_DIR}/attendance.sqlite"

mkdir -p "${DB_DIR}" 2>/dev/null || true
chmod 777 "${DB_DIR}" 2>/dev/null || true

if [ ! -f "${DB_FILE}" ]; then
    echo "[INIT] First boot detected - creating SQLite database at ${DB_FILE}"
    touch "${DB_FILE}" 2>/dev/null || true
    chmod 666 "${DB_FILE}" 2>/dev/null || true
else
    chmod 666 "${DB_FILE}" 2>/dev/null || true
    echo "[OK] Persistent database found: ${DB_FILE}"
fi

# 2. Persistent Uploads Directory inside persistent volume
PERSISTENT_UPLOADS="${DB_DIR}/_uploads"
APP_UPLOADS="/var/www/html/public/uploads"

mkdir -p "${PERSISTENT_UPLOADS}/avatars" \
         "${PERSISTENT_UPLOADS}/punches" \
         "${PERSISTENT_UPLOADS}/leaves" 2>/dev/null || true
chmod -R 777 "${PERSISTENT_UPLOADS}" 2>/dev/null || true

if [ -L "${APP_UPLOADS}" ]; then
    echo "[OK] Uploads symlink already exists: ${APP_UPLOADS} -> ${PERSISTENT_UPLOADS}"
elif [ -d "${APP_UPLOADS}" ]; then
    cp -rn "${APP_UPLOADS}/." "${PERSISTENT_UPLOADS}/" 2>/dev/null || true
    rm -rf "${APP_UPLOADS}" 2>/dev/null || true
    ln -s "${PERSISTENT_UPLOADS}" "${APP_UPLOADS}" 2>/dev/null || true
    echo "[INIT] Created uploads symlink: ${APP_UPLOADS} -> ${PERSISTENT_UPLOADS}"
else
    ln -s "${PERSISTENT_UPLOADS}" "${APP_UPLOADS}" 2>/dev/null || true
    echo "[INIT] Created uploads symlink: ${APP_UPLOADS} -> ${PERSISTENT_UPLOADS}"
fi

echo "  DB Driver : ${DB_CONNECTION:-sqlite}"
echo "  DB File   : ${DB_FILE}"
echo "  Port      : ${PORT:-80}"
echo "============================================================"

cd /var/www/html
export PHP_CLI_SERVER_WORKERS=8
exec php -S "0.0.0.0:${PORT:-80}" index.php
