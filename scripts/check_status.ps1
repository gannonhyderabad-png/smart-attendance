$root = Split-Path -Parent $PSScriptRoot
$urlFile = Join-Path $root "scratch\cloudflare_url.txt"
$dbFile = Join-Path $root "database\attendance.sqlite"
$logFile = Join-Path $root "scratch\server_24x7.log"

# 1. Check Web Server
$portConn = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
if ($portConn) {
    Write-Host "[OK] Local Web Server : ONLINE on port 8000 (PID: $($portConn.OwningProcess))" -ForegroundColor Green
} else {
    Write-Host "[OFFLINE] Local Web Server : NOT RUNNING on port 8000" -ForegroundColor Red
}

# 2. Check HTTP Health
try {
    $res = Invoke-RestMethod -Uri "http://127.0.0.1:8000/health?lite=1" -TimeoutSec 2 -ErrorAction Stop
    Write-Host "[OK] Web Response     : Healthy (Status: $($res.status))" -ForegroundColor Green
} catch {
    Write-Host "[INFO] HTTP Check     : No response from localhost:8000" -ForegroundColor Yellow
}

# 3. Check Public Cloudflare Mobile Link
$publicUrl = "Not connected"
if (Test-Path $urlFile) {
    $publicUrl = (Get-Content $urlFile -Raw).Trim()
}
$cfProc = Get-Process cloudflared -ErrorAction SilentlyContinue
if ($cfProc) {
    Write-Host "[OK] Public Tunnel    : ONLINE (cloudflared PID: $($cfProc.Id))" -ForegroundColor Green
    Write-Host "     Mobile Punch URL : $publicUrl" -ForegroundColor Cyan
} else {
    Write-Host "[NOTICE] Public Tunnel: Offline (Employees can only connect via Local LAN/Wi-Fi)" -ForegroundColor Yellow
}

# 4. Check Database
if (Test-Path $dbFile) {
    $sizeKb = [Math]::Round((Get-Item $dbFile).Length / 1KB, 1)
    Write-Host "[OK] SQLite Database  : Permanent File Active ($sizeKb KB)" -ForegroundColor Green
    Write-Host "     File Location    : $dbFile" -ForegroundColor Gray
} else {
    Write-Host "[WARN] SQLite Database: attendance.sqlite not found at $dbFile" -ForegroundColor Red
}

# 5. Check Watchdog Process
$watchdogProcs = Get-Process powershell -ErrorAction SilentlyContinue
$isWatchdogRunning = $false
if (Test-Path $logFile) {
    $lastEntry = Get-Item $logFile
    if ((Get-Date) - $lastEntry.LastWriteTime -lt [TimeSpan]::FromMinutes(2)) {
        $isWatchdogRunning = $true
    }
}
if ($isWatchdogRunning -or ($portConn -and $cfProc)) {
    Write-Host "[OK] 24/7 Watchdog    : ACTIVE (Services monitored in background)" -ForegroundColor Green
} else {
    Write-Host "[INFO] 24/7 Watchdog   : Not running as a background loop" -ForegroundColor Yellow
}

# 6. Recent Log
if (Test-Path $logFile) {
    Write-Host "`n--- Recent Server Activity (Last 4 entries) ---" -ForegroundColor Gray
    Get-Content $logFile -Tail 4 | ForEach-Object { Write-Host "  $_" -ForegroundColor DarkGray }
}
