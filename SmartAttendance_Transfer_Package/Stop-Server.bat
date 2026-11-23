@echo off
title Stopping Smart Attendance Server...
color 0C
cls
cd /d "%~dp0"

echo ===================================================================
echo             STOPPING SMART ATTENDANCE BACKGROUND SERVICES
echo ===================================================================
echo.

:: 1. Terminate PHP servers listening on 8000
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :8000 ^| findstr LISTENING') do (
    taskkill /F /PID %%a >nul 2>&1
)

:: 2. Terminate Cloudflared processes
taskkill /F /IM cloudflared.exe >nul 2>&1

:: 3. Terminate running run_server_24x7 powershell instances
powershell -NoProfile -ExecutionPolicy Bypass -Command "Get-Process powershell -ErrorAction SilentlyContinue | Where-Object { $_.MainWindowTitle -like '*run_server_24x7*' } | Stop-Process -Force" >nul 2>&1

echo [OK] All Smart Attendance background server processes stopped safely.
echo Database file (attendance.sqlite) is preserved on your hard drive with zero data loss.
echo.
pause
