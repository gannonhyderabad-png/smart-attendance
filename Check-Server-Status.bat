@echo off
title Smart Attendance 24/7 Status Check
color 0B
cls
cd /d "%~dp0"

echo ===================================================================
echo             SMART ATTENDANCE 24/7 LIVE STATUS CHECK
echo ===================================================================
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\check_status.ps1"

echo.
echo ===================================================================
pause
