@echo off
title Gannon Dunkerley eSSL Biometric Auto-Sync Bridge
echo ================================================================
echo  GANNON DUNKERLEY & CO. LTD. - BIOMETRIC AUTO-SYNC AGENT
echo ================================================================
python --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Python is not installed on this PC.
    echo Please install Python from https://www.python.org/downloads/
    pause
    exit /b
)
python scripts\sync_essl_device.py
pause
