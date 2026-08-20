@echo off
title Smart Attendance - Live PC Photo & Data Auto-Sync (Every 5 Seconds)
color 0A
cls
echo ===================================================================
echo     SMART ATTENDANCE - REAL-TIME PC PHOTO & DATA AUTO-SYNC
echo ===================================================================
echo.
echo Connecting to live Render cloud server...
echo Syncing new punch photos directly into: public\uploads\punches\
echo.
powershell -ExecutionPolicy Bypass -NoProfile -File "%~dp0sync_to_pc.ps1"
pause
