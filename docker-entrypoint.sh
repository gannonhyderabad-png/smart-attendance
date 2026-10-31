#!/bin/sh
# =============================================================================
# Smart Attendance - Docker Entrypoint
# Supports Cloud PostgreSQL/MySQL (DATABASE_URL) and Render Persistent Disk
# (/var/data) without shadowing /var/www/html/database/Database.php.
# =============================================================================

echo "============================================================"
echo "  SmartPunch Attendance - Starting Up"
echo "  $(date)"
echo "============================================================"

# 1. Choose Persistent Data Directory (/var/data if Render Disk mounted, else /var/www/html/database)
if [ -d "/var/data" ] && [ -w "/var/data" ]; then
    DATA_DIR="/var/data"
    export DB_SQLITE_PATH="/var/data/attendance.sqlite"
    echo "[DISK] Persistent Render volume detected at /var/data"
else
    DATA_DIR="/var/www/html/database"
fi

DB_FILE="${DB_SQLITE_PATH:-${DATA_DIR}/attendance.sqlite}"

mkdir -p "${DATA_DIR}" 2>/dev/null || true
chmod 777 "${DATA_DIR}" 2>/dev/null || true

if [ -z "${DATABASE_URL}" ]; then
    if [ ! -f "${DB_FILE}" ]; then
        echo "[INIT] Creating local SQLite database at ${DB_FILE}"
        touch "${DB_FILE}" 2>/dev/null || true
        chmod 666 "${DB_FILE}" 2>/dev/null || true
    else
        chmod 666 "${DB_FILE}" 2>/dev/null || true
        echo "[OK] SQLite database ready: ${DB_FILE}"
    fi
else
    echo "[CLOUD DB] DATABASE_URL configured - using external persistent database"
fi

# 2. Persistent Uploads Directory inside DATA_DIR
PERSISTENT_UPLOADS="${DATA_DIR}/_uploads"
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

echo "  Data Dir  : ${DATA_DIR}"
echo "  Port      : ${PORT:-80}"
echo "============================================================"

cd /var/www/html
export PHP_CLI_SERVER_WORKERS=8
exec php -S "0.0.0.0:${PORT:-80}" index.php
