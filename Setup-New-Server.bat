@echo off
title Smart Attendance - Complete Server System Setup
color 0A
cls
cd /d "%~dp0"

echo ===================================================================
echo     SMART ATTENDANCE ENTERPRISE - COMPLETE SERVER AUTO-SETUP
echo ===================================================================
echo.
echo Running automatic configuration on this server system...
echo Project Folder: %~dp0
echo.

:: 1. Ensure required directories exist
if not exist "database" mkdir database
if not exist "database\backups" mkdir database\backups
if not exist "scratch" mkdir scratch
if not exist "public\uploads\avatars" mkdir public\uploads\avatars
if not exist "public\uploads\punches" mkdir public\uploads\punches
if not exist "public\uploads\leaves" mkdir public\uploads\leaves

:: 2. Check and Setup PHP Engine
echo [1/3] Checking PHP 8+ engine...
set "PHP_BIN="
if exist "%~dp0php\php.exe" set "PHP_BIN=%~dp0php\php.exe"
if not defined PHP_BIN if exist "%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" set "PHP_BIN=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
if not defined PHP_BIN where php.exe >nul 2>&1 && set "PHP_BIN=php.exe"
if not defined PHP_BIN if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"
if not defined PHP_BIN if exist "C:\Program Files\PHP\php.exe" set "PHP_BIN=C:\Program Files\PHP\php.exe"

if not defined PHP_BIN (
    echo [SETUP] Setting up portable PHP 8.3 on this server...
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\install_portable_php.ps1"
    if exist "%~dp0php\php.exe" set "PHP_BIN=%~dp0php\php.exe"
)
echo [OK] PHP Engine: %PHP_BIN%

:: Ensure PHP SQLite and essential drivers are enabled in php.ini
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\fix_php_ini.ps1"
echo.

:: 3. Configure PowerShell Profile with 1-word commands
echo [2/3] Configuring PowerShell profile and execution policy...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\setup_powershell_profile.ps1"
echo.

:: 4. Verify Cloudflare Tunnel
echo [3/3] Checking Cloudflare secure mobile tunnel...
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$cfCandidates = @('%~dp0scripts\cloudflared.exe', 'C:\Program Files (x86)\cloudflared\cloudflared.exe'); ^
   $found = $false; ^
   foreach ($c in $cfCandidates) { if (Test-Path $c) { $found = $true; break } } ^
   if (-not $found -and (Get-Command cloudflared.exe -ErrorAction SilentlyContinue)) { $found = $true } ^
   if (-not $found) { ^
       Write-Host '[SETUP] Auto-downloading portable Cloudflare Tunnel binary...' -ForegroundColor Cyan; ^
       try { ^
           [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; ^
           Invoke-WebRequest -Uri 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe' -OutFile '%~dp0scripts\cloudflared.exe' -TimeoutSec 35; ^
           Write-Host '[OK] Cloudflared downloaded successfully.' -ForegroundColor Green; ^
       } catch { ^
           Write-Host '[INFO] Cloudflared download skipped. Will use local network.' -ForegroundColor Gray; ^
       } ^
   } else { ^
       Write-Host '[OK] Cloudflare Tunnel binary ready.' -ForegroundColor Green; ^
   }"
echo.

:: 5. Open Windows Firewall for Port 8000
echo [4/4] Opening Windows Firewall for Port 8000 (Local Wi-Fi Mobile Access)...
netsh advfirewall firewall add rule name="SmartAttendance Port 8000" dir=in action=allow protocol=TCP localport=8000 profile=any >nul 2>&1
echo [OK] Windows Firewall configured for mobile attendance.
echo.

echo ===================================================================
echo   SERVER SYSTEM CONFIGURATION COMPLETE!
echo ===================================================================
echo.
echo You can now run the system using either:
echo   1. Double-click Start-SmartAttendance.bat (or Start-Background-24x7.bat)
echo   2. Open ANY PowerShell window on this server and type:
echo        start-attendance   -^> Start 24/7 background server
echo        status-attendance  -^> Check live health and public mobile link
echo        stop-attendance    -^> Safely stop the server
echo ===================================================================
echo.
pause
