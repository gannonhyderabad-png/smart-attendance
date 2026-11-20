@echo off
title Starting Smart Attendance 24/7 Background Server...
color 0A
cls
cd /d "%~dp0"

echo ===================================================================
echo     STARTING SMART ATTENDANCE 24/7 BACKGROUND SERVER (ZERO DATA LOSS)
echo ===================================================================
echo.

:: Launch powershell script completely detached and hidden
powershell -WindowStyle Hidden -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\run_server_24x7.ps1"

timeout /t 3 /nobreak > nul

echo [OK] Background server watchdog has been started!
echo.
echo - Web Dashboard : http://localhost:8000/dashboard
echo - Background Log: %~dp0scratch\server_24x7.log
echo.

if exist "%~dp0scratch\cloudflare_url.txt" (
    set /p PUBLIC_URL=<"%~dp0scratch\cloudflare_url.txt"
    echo - Mobile Public Link: !PUBLIC_URL!
    echo.
)

echo You can safely CLOSE this window now.
echo The server will continue running in the background 24/7.
echo To stop it at any time, run: Stop-Server.bat
echo ===================================================================
echo.
start http://localhost:8000/dashboard
pause
