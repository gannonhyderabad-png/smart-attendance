@echo off
cd /d "%~dp0"
title Smart Attendance Production Server
echo ===================================================
echo   Starting Smart Attendance System
echo ===================================================
echo.
echo Starting PHP Server on port 8000...
start /B "PHP Server" "C:\xampp\php\php.exe" -S 0.0.0.0:8000 index.php
echo Starting Cloudflare Public HTTPS Tunnel...
start /B "Cloudflared" "C:\Program Files (x86)\cloudflared\cloudflared.exe" tunnel --protocol http2 --url http://localhost:8000
echo.
echo System is now online!
echo Local URL: http://localhost:8000
echo Cloudflare tunnel is running in the background.
echo.
pause
