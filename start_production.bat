@echo off
cd /d "%~dp0"
title Smart Attendance Production Local Server
color 0B
echo ===================================================
echo   Starting Smart Attendance System (Local Server)
echo ===================================================
echo.

set "PHP_BIN="
where php.exe >nul 2>&1
if %errorlevel% equ 0 set "PHP_BIN=php.exe"
if not defined PHP_BIN if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"
if not defined PHP_BIN if exist "C:\php\php.exe" set "PHP_BIN=C:\php\php.exe"
if not defined PHP_BIN if exist "C:\Program Files\PHP\php.exe" set "PHP_BIN=C:\Program Files\PHP\php.exe"

if not defined PHP_BIN (
    echo PHP not found. Installing via winget...
    winget install PHP.PHP.8.3 --accept-source-agreements --accept-package-agreements
    set "PHP_BIN=php.exe"
)

echo Starting PHP Server on 0.0.0.0:8000...
set PHP_CLI_SERVER_WORKERS=8
start /B "SmartAttendance-PHP" "%PHP_BIN%" -S 0.0.0.0:8000 index.php

if exist "C:\Program Files (x86)\cloudflared\cloudflared.exe" (
    echo Starting Cloudflare Public HTTPS Tunnel for Mobile Punches...
    start "Cloudflare Tunnel" "C:\Program Files (x86)\cloudflared\cloudflared.exe" tunnel --protocol http2 --url http://localhost:8000
)

timeout /t 2 /nobreak > nul
start http://localhost:8000/dashboard

echo.
echo Local Server is running!
echo Local Portal: http://localhost:8000
echo.
pause
