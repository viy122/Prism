@echo off
title PRISM Stack Launcher
echo ============================================
echo   Starting the full PRISM stack...
echo ============================================

echo [1/4] MySQL (port 3306)...
start "MySQL" /min cmd /c "C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini --standalone"

echo [2/4] Apache (Laravel app)...
start "Apache" /min cmd /c "C:\xampp\apache_start.bat"

echo [3/4] Price API - prism-price-api (port 8000)...
start "Price API :8000" /min cmd /c "cd /d C:\xampp\htdocs\prism-price-api && python -m uvicorn app.main:app --port 8000"

echo [4/4] Matcher microservice (port 5001)...
start "Matcher :5001" /min cmd /c "cd /d C:\xampp\htdocs\Prism\microservice && call start.bat"

echo.
echo All services launching in minimized windows.
echo.
echo   Laravel app :  http://prism.test  (or http://localhost/Prism/prism/public)
echo   Price API   :  http://localhost:8000/docs
echo   Matcher     :  http://localhost:5001/health
echo.
echo Note: http://prism.test requires a one-time hosts file entry.
echo See docs/PROJECT_STRUCTURE_GUIDE.md for setup.
echo.
echo To stop everything: close the minimized windows,
echo or stop Apache/MySQL from the XAMPP Control Panel.
pause
