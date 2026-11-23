# ==============================================================================
# Smart Attendance 24/7 Background Server Watchdog
# Runs PHP Web Server + Cloudflare Public Tunnel + Automated Database Protection
# ==============================================================================

param(
    [switch]$HideWindow
)

$ErrorActionPreference = "SilentlyContinue"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

$scratchDir = Join-Path $root "scratch"
$backupDir = Join-Path $root "database\backups"
$dbFile = Join-Path $root "database\attendance.sqlite"
$logFile = Join-Path $scratchDir "server_24x7.log"
$urlFile = Join-Path $scratchDir "cloudflare_url.txt"
$cfLog = Join-Path $scratchDir "cloudflared.log"

if (-not (Test-Path $scratchDir)) { New-Item -ItemType Directory -Path $scratchDir -Force | Out-Null }
if (-not (Test-Path $backupDir)) { New-Item -ItemType Directory -Path $backupDir -Force | Out-Null }

function Write-Log($msg) {
    $ts = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $line = "[$ts] $msg"
    Write-Host $line
    Add-Content -Path $logFile -Value $line -Force
}

# 1. Locate PHP Engine
$phpBin = ""
$phpCandidates = @(
    (Join-Path $root "php\php.exe"),
    "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe",
    "C:\Program Files\PHP\php.exe",
    "C:\php\php.exe",
    "C:\xampp\php\php.exe"
)
foreach ($cand in $phpCandidates) {
    if (Test-Path $cand) { $phpBin = $cand; break }
}
if (-not $phpBin) {
    $cmd = Get-Command php.exe -ErrorAction SilentlyContinue
    if ($cmd) { $phpBin = $cmd.Source }
}
if (-not $phpBin) {
    Write-Log "[Setup] Attempting auto-install of portable PHP..."
    & powershell -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot "install_portable_php.ps1")
    $localPhp = Join-Path $root "php\php.exe"
    if (Test-Path $localPhp) { $phpBin = $localPhp }
}
if (-not $phpBin) {
    Write-Log "FATAL: PHP 8+ executable not found. Please install PHP or run Start-SmartAttendance.bat once."
    exit 1
}

# 2. Locate Cloudflare Tunnel
$cfBin = ""
$cfCandidates = @(
    (Join-Path $PSScriptRoot "cloudflared.exe"),
    (Join-Path $root "scripts\cloudflared.exe"),
    "C:\Program Files (x86)\cloudflared\cloudflared.exe",
    "C:\Program Files\cloudflared\cloudflared.exe"
)
foreach ($cand in $cfCandidates) {
    if (Test-Path $cand) { $cfBin = $cand; break }
}
if (-not $cfBin) {
    $cfCmd = Get-Command cloudflared.exe -ErrorAction SilentlyContinue
    if ($cfCmd) { $cfBin = $cfCmd.Source }
}
if (-not $cfBin) {
    Write-Log "[Setup] Auto-downloading portable Cloudflare Tunnel binary..."
    $targetCf = Join-Path $PSScriptRoot "cloudflared.exe"
    try {
        [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
        Invoke-WebRequest -Uri "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe" -OutFile $targetCf -TimeoutSec 35
        if (Test-Path $targetCf) { $cfBin = $targetCf }
    } catch {
        Write-Log "[Notice] Could not auto-download cloudflared. Server will run on LAN."
    }
}

Write-Log "Starting Smart Attendance 24/7 Watchdog Server..."
Write-Log "PHP Binary: $phpBin"
Write-Log "Cloudflare Tunnel: $(if ($cfBin -and (Test-Path $cfBin)) { $cfBin } else { 'Disabled (Local LAN Only)' })"

# 3. Kill existing lingering processes on port 8000
try {
    $activeConns = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
    foreach ($c in $activeConns) {
        Stop-Process -Id $c.OwningProcess -Force -ErrorAction SilentlyContinue
    }
} catch {}

$phpProc = $null
$cfProc = $null
$lastBackupDate = ""

function Start-PhpServer {
    param($bin, $dir)
    Write-Log "[Watchdog] Launching PHP Server on port 8000 with explicit SQLite drivers..."
    $phpDir = Split-Path -Parent $bin
    $extDir = Join-Path $phpDir "ext"

    $psi = New-Object System.Diagnostics.ProcessStartInfo
    $psi.FileName = $bin
    $psi.Arguments = "-d extension_dir=`"$extDir`" -d extension=pdo_sqlite -d extension=sqlite3 -d extension=curl -d extension=mbstring -d extension=fileinfo -d extension=gd -d extension=openssl -d extension=pdo_mysql -S 0.0.0.0:8000 index.php"
    $psi.WorkingDirectory = $dir
    $psi.UseShellExecute = $false
    $psi.CreateNoWindow = $true
    return [System.Diagnostics.Process]::Start($psi)
}

function Start-CloudflareTunnel {
    param($bin, $logPath)
    if (-not $bin -or -not (Test-Path $bin)) { return $null }
    Write-Log "[Watchdog] Launching Cloudflare Tunnel for secure mobile punching..."
    if (Test-Path $logPath) { Remove-Item $logPath -Force }
    $psi = New-Object System.Diagnostics.ProcessStartInfo
    $psi.FileName = $bin
    $psi.Arguments = "tunnel --protocol http2 --ha-connections 4 --url http://localhost:8000 --logfile `"$logPath`""
    $psi.UseShellExecute = $false
    $psi.CreateNoWindow = $true
    return [System.Diagnostics.Process]::Start($psi)
}

function Do-DailyDatabaseBackup {
    $todayStr = Get-Date -Format "yyyy-MM-dd"
    global:lastBackupDate
    if ($lastBackupDate -ne $todayStr -and (Test-Path $dbFile)) {
        try {
            $backupTarget = Join-Path $backupDir "attendance_$todayStr.sqlite"
            Copy-Item -Path $dbFile -Destination $backupTarget -Force
            Write-Log "[Zero Data Loss] Created daily database snapshot: $backupTarget"
            $global:lastBackupDate = $todayStr

            # Prune backups older than 30 days to save disk space
            Get-ChildItem -Path $backupDir -Filter "attendance_*.sqlite" | Where-Object { $_.CreationTime -lt (Get-Date).AddDays(-30) } | Remove-Item -Force
        } catch {
            Write-Log "[Backup Warning] Could not create snapshot: $_"
        }
    }
}

# Initial start
$phpProc = Start-PhpServer -bin $phpBin -dir $root
Start-Sleep -Seconds 2
$cfProc = Start-CloudflareTunnel -bin $cfBin -logPath $cfLog

# Extract Tunnel URL
for ($i = 0; $i -lt 15; $i++) {
    Start-Sleep -Milliseconds 800
    if (Test-Path $cfLog) {
        $logTxt = Get-Content $cfLog -Raw -ErrorAction SilentlyContinue
        if ($logTxt -match '(https://[a-zA-Z0-9-]+\.trycloudflare\.com)') {
            $tunnelUrl = $matches[1]
            Set-Content -Path $urlFile -Value $tunnelUrl -Force
            Write-Log ">>> PUBLIC MOBILE LINK (ACTIVE): $tunnelUrl"
            break
        }
    }
}

# Watchdog 24/7 Loop
Write-Log "Watchdog monitor is now active. Server will run 24/7 with zero data loss."

while ($true) {
    Start-Sleep -Seconds 12

    # 1. Automated zero-data-loss snapshot check
    Do-DailyDatabaseBackup

    # 2. Check PHP Server
    $phpHealthy = $false
    if ($phpProc -and -not $phpProc.HasExited) {
        try {
            $res = Invoke-RestMethod -Uri "http://127.0.0.1:8000/health?lite=1" -TimeoutSec 3 -ErrorAction SilentlyContinue
            if ($res.status -eq "ok") { $phpHealthy = $true }
        } catch {}
    }

    if (-not $phpHealthy) {
        Write-Log "[Alert] PHP server unresponsive or closed. Auto-recovering..."
        if ($phpProc -and -not $phpProc.HasExited) {
            try { $phpProc.Kill() } catch {}
        }
        $phpProc = Start-PhpServer -bin $phpBin -dir $root
    }

    # 3. Check Cloudflare Tunnel
    if ($cfBin -and (Test-Path $cfBin)) {
        if (-not $cfProc -or $cfProc.HasExited) {
            Write-Log "[Alert] Cloudflare Tunnel disconnected. Auto-reconnecting..."
            $cfProc = Start-CloudflareTunnel -bin $cfBin -logPath $cfLog
            Start-Sleep -Seconds 3
            if (Test-Path $cfLog) {
                $logTxt = Get-Content $cfLog -Raw -ErrorAction SilentlyContinue
                if ($logTxt -match '(https://[a-zA-Z0-9-]+\.trycloudflare\.com)') {
                    Set-Content -Path $urlFile -Value $matches[1] -Force
                    Write-Log ">>> Reconnected Public Tunnel: $($matches[1])"
                }
            }
        }
    }
}
