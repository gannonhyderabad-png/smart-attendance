# Smart Attendance - Live PC Auto-Sync & Render 24/7 Keep-Alive
# 1. Backs up all punches & selfie photos to PC every 5 seconds
# 2. Pings Render healthcheck to prevent Render from sleeping/closing
# 3. Downloads automated full system ZIP backups periodically
$ErrorActionPreference = "SilentlyContinue"
$hostUrl = "https://smart-attendance-hw9c.onrender.com"
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$punchesDir = Join-Path $scriptDir "public\uploads\punches"
$backupsDir = Join-Path $scriptDir "backups"
$lastIdFile = Join-Path $scriptDir "last_sync_id.txt"

if (-not (Test-Path $punchesDir)) { New-Item -ItemType Directory -Path $punchesDir -Force | Out-Null }
if (-not (Test-Path $backupsDir)) { New-Item -ItemType Directory -Path $backupsDir -Force | Out-Null }

$lastId = 0
if (Test-Path $lastIdFile) {
    $content = Get-Content $lastIdFile -Raw
    if ($content -match '^\d+$') {
        $lastId = [int]$content.Trim()
    }
}

$lastKeepAlive = [DateTime]::MinValue
$lastHourlyBackup = [DateTime]::MinValue

Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host "  SMART ATTENDANCE - 24/7 CLOUD KEEP-ALIVE & LIVE PC AUTO-BACKUP" -ForegroundColor Green
Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host "  Cloud Server       : $hostUrl" -ForegroundColor Yellow
Write-Host "  Local Photos Dir   : $punchesDir" -ForegroundColor Yellow
Write-Host "  Full Backup Dir    : $backupsDir" -ForegroundColor Yellow
Write-Host "  Cloud Keep-Alive   : ACTIVE (Prevents Render from ever sleeping)" -ForegroundColor Green
Write-Host "  Real-Time Sync     : Every 5 Seconds" -ForegroundColor Green
Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host ""

while ($true) {
    $now = Get-Date

    # 1. Cloud Keep-Alive Ping every 60 seconds (prevents Render idle sleep/closing)
    if (($now - $lastKeepAlive).TotalSeconds -ge 60) {
        try {
            $keepAliveUrl = "$hostUrl/health?lite=1"
            Invoke-RestMethod -Uri $keepAliveUrl -Method Get -TimeoutSec 10 | Out-Null
            $timeStr = $now.ToString("HH:mm:ss")
            Write-Host "[$timeStr] [KEEP-ALIVE] Cloud server pinged (Server kept 24/7 awake)" -ForegroundColor Gray
            $lastKeepAlive = $now
        } catch {}
    }

    # 2. Live Punch & Photo Auto-Sync every 5 seconds
    try {
        $syncUrl = "$hostUrl/api/sync?last_id=$lastId"
        $res = Invoke-RestMethod -Uri $syncUrl -Method Get -TimeoutSec 10 -Headers @{'User-Agent'='SmartAttendanceSync/2.0'}

        if ($res -and $res.success -and $res.punches) {
            $newPunches = $res.punches
            if ($newPunches.Count -gt 0) {
                foreach ($p in $newPunches) {
                    $empName = $p.employee_name
                    $empCode = $p.employee_code
                    $punchType = $p.punch_type
                    $photoPath = $p.punch_photo
                    $photoB64 = $p.photo_base64

                    if ($photoB64 -and $photoPath) {
                        $filename = Split-Path -Leaf $photoPath
                        $targetFilePath = Join-Path $punchesDir $filename
                        $bytes = [Convert]::FromBase64String($photoB64)
                        [IO.File]::WriteAllBytes($targetFilePath, $bytes)

                        $tStr = (Get-Date).ToString("HH:mm:ss")
                        Write-Host "[$tStr] [PHOTO BACKED UP] $empName ($empCode) $punchType -> $filename" -ForegroundColor Green
                    } else {
                        $tStr = (Get-Date).ToString("HH:mm:ss")
                        Write-Host "[$tStr] [PUNCH BACKED UP] $empName ($empCode) $punchType recorded" -ForegroundColor Cyan
                    }
                }

                $lastId = [int]$res.latest_id
                Set-Content -Path $lastIdFile -Value $lastId -Force
            }
        }
    } catch {
        # Retry smoothly
    }

    # 3. Automated Periodic Full System ZIP Backup (Every 6 hours)
    if (($now - $lastHourlyBackup).TotalHours -ge 6) {
        try {
            $zipName = "cloud_backup_" + $now.ToString("yyyy-MM-dd_HH-mm") + ".zip"
            $targetZip = Join-Path $backupsDir $zipName
            $backupUrl = "$hostUrl/backup/download"
            Invoke-WebRequest -Uri $backupUrl -OutFile $targetZip -TimeoutSec 30 -UseBasicParsing | Out-Null
            if (Test-Path $targetZip) {
                $tStr = (Get-Date).ToString("HH:mm:ss")
                Write-Host "[$tStr] [FULL BACKUP CREATED] Saved snapshot to $targetZip" -ForegroundColor Yellow
                $lastHourlyBackup = $now
            }
        } catch {}
    }

    Start-Sleep -Seconds 5
}
