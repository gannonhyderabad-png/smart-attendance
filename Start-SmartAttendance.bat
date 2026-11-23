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

if exist "%~dp0php\php.exe" (
    set "PHP_BIN=%~dp0php\php.exe"
    goto :PHP_FOUND
)

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
echo [NOTICE] PHP was not found. Automatically setting up PHP engine...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\install_portable_php.ps1"
if exist "%~dp0php\php.exe" (
    set "PHP_BIN=%~dp0php\php.exe"
    goto :PHP_FOUND
)
if exist "%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" (
    set "PHP_BIN=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
    goto :PHP_FOUND
)
pause
exit /b 1

:PHP_FOUND
echo [OK] PHP Engine: %PHP_BIN%

:: Extract PHP directory
for %%I in ("%PHP_BIN%") do set "PHP_DIR=%%~dpI"
set "PHP_DIR=%PHP_DIR:~0,-1%"

:: Auto-enable pdo_sqlite in php.ini if missing
if exist "%PHP_DIR%\php.ini-production" if not exist "%PHP_DIR%\php.ini" (
    copy "%PHP_DIR%\php.ini-production" "%PHP_DIR%\php.ini" >nul 2>&1
)
powershell -NoProfile -ExecutionPolicy Bypass -Command "if (Test-Path '%PHP_DIR%\php.ini') { $c = Get-Content '%PHP_DIR%\php.ini' -Raw; if ($c -notmatch 'extension=pdo_sqlite') { $c = $c -replace ';extension=pdo_sqlite', 'extension=pdo_sqlite'; $c = $c -replace ';extension_dir = \"ext\"', 'extension_dir = \"ext\"'; Set-Content '%PHP_DIR%\php.ini' $c -Force } }" >nul 2>&1

:: Build Driver Flags to guarantee SQLite works 100% on any PC
set PHP_FLAGS=-d extension_dir="%PHP_DIR%\ext" -d extension=pdo_sqlite -d extension=sqlite3 -d extension=curl -d extension=mbstring -d extension=fileinfo -d extension=gd -d extension=openssl -d extension=pdo_mysql

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

:: 4. Start PHP Built-in Server on port 8000 with explicit database drivers
echo [1/3] Starting Local Web Server on port 8000 (SQLite Driver Active)...
start /b "SmartAttendance-PHP" "%PHP_BIN%" %PHP_FLAGS% -S 0.0.0.0:8000 index.php > nul 2>&1
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
