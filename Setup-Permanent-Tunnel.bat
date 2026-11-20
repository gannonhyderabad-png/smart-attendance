@echo off
title Setup Permanent Cloudflare Fixed Domain Tunnel
color 0B
cls
cd /d "%~dp0"

echo ===================================================================
echo     SETUP PERMANENT FIXED CLOUDFLARE TUNNEL (24/7 WINDOWS SERVICE)
echo ===================================================================
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\setup_permanent_tunnel.ps1"

echo.
pause
