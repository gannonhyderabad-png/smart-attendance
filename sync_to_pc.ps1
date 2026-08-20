# Smart Attendance - Live PC Auto-Sync Script (Every 5 Seconds)
$ErrorActionPreference = "SilentlyContinue"
$hostUrl = "https://smart-attendance-hw9c.onrender.com"
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$punchesDir = Join-Path $scriptDir "public\uploads\punches"
$lastIdFile = Join-Path $scriptDir "last_sync_id.txt"

if (-not (Test-Path $punchesDir)) {
    New-Item -ItemType Directory -Path $punchesDir -Force | Out-Null
}

$lastId = 0
if (Test-Path $lastIdFile) {
    $content = Get-Content $lastIdFile -Raw
    if ($content -match '^\d+$') {
        $lastId = [int]$content.Trim()
    }
}

Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host "  SMART ATTENDANCE - REAL-TIME PC PHOTO & DATA AUTO-SYNC" -ForegroundColor Green
Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host "  Server: $hostUrl" -ForegroundColor Yellow
Write-Host "  Local Photos Folder: $punchesDir" -ForegroundColor Yellow
Write-Host "  Sync Interval: Every 5 Seconds" -ForegroundColor Yellow
Write-Host "  Status: LIVE & MONITORING..." -ForegroundColor Green
Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host ""

while ($true) {
    try {
        $syncUrl = "$hostUrl/api/sync?last_id=$lastId"
        $res = Invoke-RestMethod -Uri $syncUrl -Method Get -TimeoutSec 8 -Headers @{'User-Agent'='SmartAttendanceSync/2.0'}

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

                        $now = Get-Date -Format "HH:mm:ss"
                        Write-Host "[$now] [PHOTO SYNCED] $empName ($empCode) $punchType -> $filename" -ForegroundColor Green
                    } else {
                        $now = Get-Date -Format "HH:mm:ss"
                        Write-Host "[$now] [PUNCH SYNCED] $empName ($empCode) $punchType recorded" -ForegroundColor Cyan
                    }
                }

                $lastId = [int]$res.latest_id
                Set-Content -Path $lastIdFile -Value $lastId
            }
        }
    } catch {
        # Silent retry
    }

    Start-Sleep -Seconds 5
}
