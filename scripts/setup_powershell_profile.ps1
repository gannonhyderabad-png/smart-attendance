# ==============================================================================
# Configure PowerShell Shortcuts for Smart Attendance 24/7 Server
# ==============================================================================

Write-Host "Configuring PowerShell for Smart Attendance..." -ForegroundColor Cyan

# 1. Ensure ExecutionPolicy allows running local scripts
try {
    Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned -Force -ErrorAction SilentlyContinue
    Write-Host "[OK] Execution Policy set to RemoteSigned (CurrentUser)" -ForegroundColor Green
} catch {
    Write-Host "[INFO] Execution policy check complete" -ForegroundColor Gray
}

# 2. Locate or create PowerShell Profile
$profilePath = $PROFILE.CurrentUserCurrentHost
if (-not $profilePath) {
    $profilePath = Join-Path ([Environment]::GetFolderPath("MyDocuments")) "WindowsPowerShell\Microsoft.PowerShell_profile.ps1"
}
$profileDir = Split-Path -Parent $profilePath
if (-not (Test-Path $profileDir)) {
    New-Item -ItemType Directory -Path $profileDir -Force | Out-Null
}

$root = Split-Path -Parent $PSScriptRoot

# 3. Create function definitions to append into Profile
$customFunctions = @"

# >>> Smart Attendance 24/7 Management Commands >>>
function global:start-attendance {
    Write-Host "Starting Smart Attendance 24/7 Background Server..." -ForegroundColor Cyan
    Start-Process powershell -WindowStyle Hidden -ArgumentList "-NoProfile -ExecutionPolicy Bypass -File '$root\scripts\run_server_24x7.ps1'"
    Start-Sleep -Seconds 2
    & '$root\scripts\check_status.ps1'
    Start-Process "http://localhost:8000/dashboard"
}

function global:status-attendance {
    & '$root\scripts\check_status.ps1'
}

function global:stop-attendance {
    Write-Host "Stopping Smart Attendance Server..." -ForegroundColor Yellow
    Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue | ForEach-Object { Stop-Process -Id `$_.OwningProcess -Force -ErrorAction SilentlyContinue }
    Stop-Process -Name cloudflared -Force -ErrorAction SilentlyContinue
    Write-Host "Smart Attendance server stopped." -ForegroundColor Green
}

function global:open-attendance {
    Start-Process "http://localhost:8000/dashboard"
}
# <<< Smart Attendance Management Commands <<<
"@

# 4. Append to Profile if not already present
$existingProfile = ""
if (Test-Path $profilePath) {
    $existingProfile = Get-Content $profilePath -Raw -ErrorAction SilentlyContinue
}

if ($existingProfile -notlike "*Smart Attendance 24/7 Management Commands*") {
    Add-Content -Path $profilePath -Value $customFunctions -Force
    Write-Host "[OK] Custom commands added to PowerShell Profile:" -ForegroundColor Green
    Write-Host "     Profile file: $profilePath" -ForegroundColor Gray
} else {
    Write-Host "[OK] PowerShell Profile already contains Smart Attendance commands." -ForegroundColor Green
}

Write-Host ""
Write-Host "===================================================================" -ForegroundColor Green
Write-Host " POWERSHELL CONFIGURATION COMPLETE!" -ForegroundColor Green
Write-Host "===================================================================" -ForegroundColor Green
Write-Host " You can now open any PowerShell window and use these 4 commands:" -ForegroundColor White
Write-Host "   start-attendance   -> Starts the 24/7 server & tunnel in background" -ForegroundColor Cyan
Write-Host "   status-attendance  -> Shows live server health & mobile punch link" -ForegroundColor Cyan
Write-Host "   open-attendance    -> Opens admin dashboard in browser" -ForegroundColor Cyan
Write-Host "   stop-attendance    -> Safely stops background server" -ForegroundColor Cyan
Write-Host "===================================================================" -ForegroundColor Green
Write-Host ""
