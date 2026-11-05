@echo off
title Smart Attendance System - Local Server
color 0A
cls
cd /d "%~dp0"

echo ===================================================================
echo             SMART ATTENDANCE ENTERPRISE LOCAL SERVER
echo ===================================================================
echo.

:: 1. Locate PHP Executable
set "PHP_BIN="

if exist "%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" (
    set "PHP_BIN=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
    goto :PHP_FOUND
)

where php.exe >nul 2>&1
if %errorlevel% equ 0 (
    set "PHP_BIN=php.exe"
    goto :PHP_FOUND
)

if exist "C:\xampp\php\php.exe" (
    set "PHP_BIN=C:\xampp\php\php.exe"
    goto :PHP_FOUND
)

if exist "C:\php\php.exe" (
    set "PHP_BIN=C:\php\php.exe"
    goto :PHP_FOUND
)

if exist "C:\Program Files\PHP\php.exe" (
    set "PHP_BIN=C:\Program Files\PHP\php.exe"
    goto :PHP_FOUND
)

for /d %%D in ("C:\laragon\bin\php\php-*") do (
    if exist "%%D\php.exe" (
        set "PHP_BIN=%%D\php.exe"
        goto :PHP_FOUND
    )
)

:PHP_NOT_FOUND
echo [NOTICE] PHP was not found on your system.
echo Installing PHP 8.3 automatically via Windows winget...
winget install PHP.PHP.8.3 --accept-source-agreements --accept-package-agreements
timeout /t 3 /nobreak > nul

where php.exe >nul 2>&1
if %errorlevel% equ 0 (
    set "PHP_BIN=php.exe"
    goto :PHP_FOUND
)
echo.
echo [ERROR] Could not find or install PHP automatically.
echo Please install XAMPP (https://www.apachefriends.org) or run:
echo    winget install PHP.PHP.8.3
echo Then run this file again.
pause
exit /b 1

:PHP_FOUND
echo [OK] Using PHP: %PHP_BIN%

:: 2. Ensure directories exist
if not exist "database" mkdir database
if not exist "public\uploads\avatars" mkdir public\uploads\avatars
if not exist "public\uploads\punches" mkdir public\uploads\punches
if not exist "public\uploads\leaves" mkdir public\uploads\leaves

:: 3. Start PHP Built-in Server on port 8000
echo [1/3] Starting Multi-Threaded Local Web Server on port 8000...
set PHP_CLI_SERVER_WORKERS=8
start /b "SmartAttendance-PHP" "%PHP_BIN%" -S 0.0.0.0:8000 index.php > nul 2>&1
timeout /t 2 /nobreak > nul

:: 4. Cloudflare Public Tunnel (Allows employees to punch from mobile anywhere)
set "CF_BIN="
if exist "C:\Program Files (x86)\cloudflared\cloudflared.exe" (
    set "CF_BIN=C:\Program Files (x86)\cloudflared\cloudflared.exe"
) else (
    where cloudflared.exe >nul 2>&1
    if %errorlevel% equ 0 set "CF_BIN=cloudflared.exe"
)

if defined CF_BIN (
    echo [2/3] Starting Cloudflare Public HTTPS Tunnel for Mobile Attendance...
    start "Cloudflare Mobile Tunnel" "%CF_BIN%" tunnel --protocol http2 --ha-connections 4 --url http://localhost:8000
) else (
    echo [2/3] Cloudflare Tunnel not found (Server will run for local network only).
)

:: 5. Open Local Admin Dashboard
echo [3/3] Opening Local Admin Portal in browser...
start http://localhost:8000/dashboard

echo.
echo ===================================================================
echo   LOCAL SERVER IS ONLINE AND RUNNING!
echo.
echo   - Local Admin Dashboard : http://localhost:8000/dashboard
echo   - Local Network Access  : Available to all PCs/tablets on your Wi-Fi/LAN
echo   - Cloudflare Window     : Shows your public HTTPS URL for Mobile Attendance
echo   - Database Storage      : 100%% Permanent on your local hard drive!
echo   - Data Reset Risk       : ZERO - Data is never erased on local server!
echo.
echo   Keep this window open while using the application.
echo ===================================================================
echo.
pause
