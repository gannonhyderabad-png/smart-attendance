@echo off
title Smart Attendance System - Server Launcher
color 0A
cls
echo ===================================================================
echo             SMART ATTENDANCE ENTERPRISE SERVER
echo ===================================================================
echo.
echo [1/3] Starting Multi-Threaded PHP Web Server on port 8000...
set PHP_CLI_SERVER_WORKERS=8
start /b "" "C:\xampp\php\php.exe" -S 0.0.0.0:8000 index.php > nul 2>&1
timeout /t 2 /nobreak > nul

echo [2/3] Connecting High-Speed Cloudflare Public Tunnel...
start "" "C:\Program Files (x86)\cloudflared\cloudflared.exe" tunnel --protocol http2 --ha-connections 4 --url http://localhost:8000

echo [3/3] Opening Local Admin Portal...
start http://localhost:8000/dashboard

echo.
echo ===================================================================
echo   SERVER IS RUNNING!
echo   - Local Admin Dashboard: http://localhost:8000/dashboard
echo   - The Cloudflare Tunnel window will show your public HTTPS link!
echo.
echo   Keep this window open while using the application.
echo ===================================================================
echo.
pause
