@echo off
title Configure PowerShell for Smart Attendance 24/7
color 0B
cls
cd /d "%~dp0"

echo ===================================================================
echo     CONFIGURING POWERSHELL FOR SMART ATTENDANCE 24/7 SERVER
echo ===================================================================
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\setup_powershell_profile.ps1"

echo.
pause
