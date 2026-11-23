@echo off
title Smart Attendance - Fix Database Driver (could not find driver)
color 0A
cls
cd /d "%~dp0"

echo ===================================================================
echo     FIXING PHP SQLITE DATABASE DRIVERS FOR SMART ATTENDANCE
echo ===================================================================
echo.

:: 1. Run the powershell php.ini configurator
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\fix_php_ini.ps1"

echo.
echo Restarting local server with enabled database drivers...

:: 2. Stop any old server running without the driver
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :8000 ^| findstr LISTENING') do (
    taskkill /F /PID %%a >nul 2>&1
)

:: 3. Launch server with explicit driver flags
call "%~dp0Start-SmartAttendance.bat"

echo.
pause
