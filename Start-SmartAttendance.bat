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

:PHP_NOT_FOUND
echo [NOTICE] PHP was not found. Installing via winget...
winget install PHP.PHP.8.3 --accept-source-agreements --accept-package-agreements
if exist "%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" (
    set "PHP_BIN=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
    goto :PHP_FOUND
)
pause
exit /b 1

:PHP_FOUND
echo [OK] PHP Engine: %PHP_BIN%

:: 2. Ensure directories exist
if not exist "database" mkdir database
if not exist "scratch" mkdir scratch
if not exist "public\uploads\avatars" mkdir public\uploads\avatars
if not exist "public\uploads\punches" mkdir public\uploads\punches
if not exist "public\uploads\leaves" mkdir public\uploads\leaves

:: 3. Stop any previously hanging servers on port 8000
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :8000 ^| findstr LISTENING') do (
    taskkill /F /PID %%a >nul 2>&1
)

:: 4. Start PHP Built-in Server on port 8000
echo [1/3] Starting Multi-Threaded Local Web Server on port 8000...
set PHP_CLI_SERVER_WORKERS=8
start /b "SmartAttendance-PHP" "%PHP_BIN%" -S 0.0.0.0:8000 index.php > nul 2>&1
timeout /t 2 /nobreak > nul

:: 5. Start Cloudflare Tunnel for Mobile Punches
echo [2/3] Connecting Cloudflare Tunnel for Mobile Punches...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\start_tunnel.ps1"

:: 6. Open Local Admin Dashboard
echo [3/3] Opening Local Admin Portal in browser...
start http://localhost:8000/dashboard

echo.
echo ===================================================================
echo   LOCAL SERVER IS ONLINE AND RUNNING!
echo.
echo   - Local Admin Dashboard : http://localhost:8000/dashboard
echo   - Database Storage      : 100%% Permanent on your local hard drive!
echo   - Mobile Punch Sync     : Punches taken on phones reflect HERE instantly!
echo.
echo   Keep this window open while using the application.
echo   To stop the server, simply close this window.
echo ===================================================================
echo.
pause
