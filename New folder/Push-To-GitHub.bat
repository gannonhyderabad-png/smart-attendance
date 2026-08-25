@echo off
title Push Smart Attendance to GitHub (Render Deploy)
color 0B
cls
echo ===================================================================
echo        DEPLOY SMART ATTENDANCE TO GITHUB & RENDER
echo ===================================================================
echo.
echo Pushing latest code with Recycle Bin, Excel Export & Sites to GitHub...
echo Repository: https://github.com/gannonhyderabad-png/smart-attendance
echo.

set "GIT_PATH=%USERPROFILE%\MinGit\cmd\git.exe"
if not exist "%GIT_PATH%" (
    set "GIT_PATH=git"
)

"%GIT_PATH%" push -u origin main

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ===================================================================
    echo  SUCCESS! Code pushed to GitHub!
    echo  Render is now automatically deploying your live updates at:
    echo  https://smart-attendance-hw9c.onrender.com/employees
    echo ===================================================================
) else (
    echo.
    echo ===================================================================
    echo  Authentication required.
    echo  Please sign in with your GitHub account when prompted or
    echo  use your GitHub Personal Access Token (PAT) as password.
    echo ===================================================================
)

echo.
pause
