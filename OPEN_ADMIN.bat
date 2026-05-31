@echo off
REM Salamtak Admin Dashboard Launcher
REM Double-click this file to open the admin dashboard in Chrome

echo.
echo ========================================
echo   Salamtak Admin Dashboard
echo ========================================
echo.
echo Opening Admin Dashboard in Chrome...
echo.

REM Open the admin dashboard in Chrome
start chrome "http://localhost/salamtak_web/admin/dashboard.php"

echo.
echo Admin Dashboard opened!
echo.
echo Press any key to exit...
pause >nul
