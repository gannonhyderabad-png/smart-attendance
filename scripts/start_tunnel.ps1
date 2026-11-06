# Cloudflare Tunnel Launcher & URL Extractor for Smart Attendance
$ErrorActionPreference = "SilentlyContinue"
$root = Split-Path -Parent $PSScriptRoot
$scratch = Join-Path $root "scratch"
if (-not (Test-Path $scratch)) { New-Item -ItemType Directory -Path $scratch -Force | Out-Null }

$logFile = Join-Path $scratch "cloudflared.log"
$urlFile = Join-Path $scratch "cloudflare_url.txt"
if (Test-Path $urlFile) { Remove-Item $urlFile -Force }
if (Test-Path $logFile) { Remove-Item $logFile -Force }

$cfExe = "C:\Program Files (x86)\cloudflared\cloudflared.exe"
if (-not (Test-Path $cfExe)) {
    $found = Get-Command cloudflared.exe -ErrorAction SilentlyContinue
    if ($found) { $cfExe = $found.Source }
}

if (-not (Test-Path $cfExe)) {
    Write-Host "[NOTICE] Cloudflared not found. Server running on LAN only." -ForegroundColor Yellow
    exit 0
}

Write-Host "Connecting Cloudflare secure public tunnel for mobile punches..." -ForegroundColor Cyan
$p = Start-Process -FilePath $cfExe -ArgumentList "tunnel --protocol http2 --ha-connections 4 --url http://localhost:8000 --logfile `"$logFile`"" -PassThru -WindowStyle Hidden

$tunnelUrl = ""
for ($i = 0; $i -lt 20; $i++) {
    Start-Sleep -Milliseconds 600
    if (Test-Path $logFile) {
        $lines = Get-Content $logFile -Raw
        if ($lines -match '(https://[a-zA-Z0-9-]+\.trycloudflare\.com)') {
            $tunnelUrl = $matches[1]
            Set-Content -Path $urlFile -Value $tunnelUrl -Force
            break
        }
    }
}

if ($tunnelUrl) {
    Write-Host ""
    Write-Host "===================================================================" -ForegroundColor Green
    Write-Host "  MOBILE ATTENDANCE PUBLIC LINK (ONLINE EVERYWHERE):" -ForegroundColor Green
    Write-Host "  $tunnelUrl" -ForegroundColor White
    Write-Host "===================================================================" -ForegroundColor Green
    Write-Host "  Any employee can open this link on their phone from anywhere!" -ForegroundColor Yellow
    Write-Host "  Punches will instantly appear on your PC screen in real-time." -ForegroundColor Yellow
    Write-Host ""
} else {
    Write-Host "[INFO] Cloudflare tunnel started in background. Log: $logFile" -ForegroundColor Gray
}
