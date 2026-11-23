@echo off
title Configure PowerShell & Command Line for Smart Attendance
color 0B
cls
cd /d "%~dp0"

echo ===================================================================
echo   CONFIGURING POWERSHELL FOR SMART ATTENDANCE ON THIS SERVER
echo ===================================================================
echo.
echo Server Project Path: %~dp0
echo.

:: 1. Add project path to Current User PATH so commands work from ANY folder
echo [1/2] Adding commands to User Environment PATH...
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$projPath = '%~dp0'.TrimEnd('\'); ^
   $current = [System.Environment]::GetEnvironmentVariable('Path', 'User'); ^
   if (-not $current) { $current = '' }; ^
   if ($current -notlike ('*' + $projPath + '*')) { ^
       $newPath = ($current.TrimEnd(';') + ';' + $projPath).TrimStart(';'); ^
       [System.Environment]::SetEnvironmentVariable('Path', $newPath, 'User'); ^
       Write-Host '[OK] Added project folder to User PATH successfully!' -ForegroundColor Green; ^
   } else { ^
       Write-Host '[OK] Project folder already in User PATH.' -ForegroundColor Green; ^
   }"

:: 2. Try registering PowerShell profile functions (optional)
echo [2/2] Configuring PowerShell profile shortcuts...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\setup_powershell_profile.ps1" 2>nul

echo.
echo ===================================================================
echo   POWERSHELL CONFIGURATION COMPLETE ON THIS SERVER!
echo ===================================================================
echo.
echo You can now open ANY PowerShell or Command Prompt window and type:
echo.
echo   start-attendance   -> Starts server & tunnel 24/7 in background
echo   status-attendance  -> Shows live server health & mobile punch URL
echo   open-attendance    -> Opens admin dashboard in browser
echo   stop-attendance    -> Stops background server
echo.
echo ===================================================================
echo (Note: If you have an existing PowerShell window open, close and
echo  re-open it once so Windows reloads the new PATH commands).
echo ===================================================================
echo.
pause
