# ==============================================================================
# Permanent Cloudflare Tunnel Setup (Named Tunnel with Fixed Domain)
# ==============================================================================

param(
    [string]$TunnelToken
)

$root = Split-Path -Parent $PSScriptRoot

$cfExe = ""
$cfCandidates = @(
    (Join-Path $PSScriptRoot "cloudflared.exe"),
    "C:\Program Files (x86)\cloudflared\cloudflared.exe",
    "C:\Program Files\cloudflared\cloudflared.exe"
)
foreach ($c in $cfCandidates) {
    if (Test-Path $c) { $cfExe = $c; break }
}
if (-not $cfExe -and (Get-Command cloudflared.exe -ErrorAction SilentlyContinue)) {
    $cfExe = (Get-Command cloudflared.exe).Source
}

if (-not $cfExe) {
    Write-Host "[SETUP] Downloading portable Cloudflare binary..." -ForegroundColor Cyan
    $targetCf = Join-Path $PSScriptRoot "cloudflared.exe"
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    Invoke-WebRequest -Uri "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe" -OutFile $targetCf -TimeoutSec 35
    $cfExe = $targetCf
}

if (-not $TunnelToken) {
    Write-Host ""
    Write-Host "===================================================================" -ForegroundColor Green
    Write-Host " HOW TO GET A PERMANENT FIXED CLOUDFLARE URL (FREE FOREVER):" -ForegroundColor Green
    Write-Host "===================================================================" -ForegroundColor Green
    Write-Host "1. Go to https://one.dash.cloudflare.com/ (Free Cloudflare Zero Trust)" -ForegroundColor White
    Write-Host "2. Navigate to: Networks -> Tunnels -> Click 'Add a Tunnel'" -ForegroundColor White
    Write-Host "3. Name your tunnel: smart-attendance" -ForegroundColor White
    Write-Host "4. Choose 'Windows' environment. Cloudflare will show an install command with a TOKEN." -ForegroundColor White
    Write-Host "5. Copy that token and paste it here when prompted." -ForegroundColor White
    Write-Host "===================================================================" -ForegroundColor Green
    Write-Host ""
    $TunnelToken = Read-Host "Paste your Cloudflare Tunnel Token (or press Enter to cancel)"
}

if ($TunnelToken) {
    $TunnelToken = $TunnelToken.Trim()
    if ($TunnelToken -match 'token\s+([A-Za-z0-9_-]+)') {
        $TunnelToken = $matches[1]
    }

    Write-Host "Installing Cloudflare permanent Windows Service with your token..." -ForegroundColor Cyan
    & $cfExe service install $TunnelToken
    Start-Sleep -Seconds 2
    Start-Service cloudflared -ErrorAction SilentlyContinue

    # Save token for persistence
    Set-Content -Path (Join-Path $root "scratch\permanent_tunnel_token.txt") -Value $TunnelToken -Force

    Write-Host "[SUCCESS] Permanent Cloudflare Tunnel Windows Service installed!" -ForegroundColor Green
    Write-Host "It will now run 24/7 as an official Windows Service on boot." -ForegroundColor Green
}
