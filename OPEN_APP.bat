@echo off
REM Salamtak Web App Launcher
REM Double-click this file to open the app in Chrome

echo.
echo ========================================
echo   Salamtak Web App Launcher
echo ========================================
echo.
echo Opening Salamtak in Chrome...
echo.

REM Open the home page in Chrome
start chrome "http://localhost/salamtak_web/home.php"

echo.
echo App opened in Chrome!
echo.
echo Quick Links:
echo - Home: http://localhost/salamtak_web/home.php
echo - Login: http://localhost/salamtak_web/login.php
echo - Admin: http://localhost/salamtak_web/admin/dashboard.php
echo.
echo Press any key to exit...
pause >nul
