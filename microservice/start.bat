@echo off
title PRISM Market Scoping Microservice
cd /d "%~dp0"

echo Checking Python...
python --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python is not installed or not in PATH.
    echo Download from https://python.org
    pause
    exit /b 1
)

echo Installing / updating dependencies...
pip install -r requirements.txt --quiet --disable-pip-version-check

echo.
python app.py
pause
