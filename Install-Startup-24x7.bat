@echo off
title Install Smart Attendance 24/7 Windows Auto-Startup
color 0B
cls
cd /d "%~dp0"

echo ===================================================================
echo   ENABLE 24/7 AUTO-STARTUP ON WINDOWS BOOT (ZERO DATA LOSS)
echo ===================================================================
echo.
echo This will configure Smart Attendance to start automatically in the
echo background whenever your computer turns on or reboots.
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$ws = New-Object -ComObject WScript.Shell; ^
   $startup = [System.Environment]::GetFolderPath('Startup'); ^
   $shortcutPath = Join-Path $startup 'SmartAttendance24x7.lnk'; ^
   $s = $ws.CreateShortcut($shortcutPath); ^
   $s.TargetPath = 'powershell.exe'; ^
   $s.Arguments = '-WindowStyle Hidden -NoProfile -ExecutionPolicy Bypass -File \"\"%~dp0scripts\run_server_24x7.ps1\"\"'; ^
   $s.WorkingDirectory = '%~dp0'; ^
   $s.Description = 'Smart Attendance 24x7 Background Server Watchdog'; ^
   $s.Save(); ^
   Write-Host '[SUCCESS] Auto-start shortcut installed into Windows Startup folder:' -ForegroundColor Green; ^
   Write-Host $shortcutPath -ForegroundColor Cyan"

echo.
echo ===================================================================
echo [OK] Smart Attendance will now start 24/7 automatically when Windows boots!
echo To remove auto-start in the future, delete SmartAttendance24x7.lnk
echo from your Windows Startup folder (Win + R -> shell:startup).
echo ===================================================================
echo.
pause
