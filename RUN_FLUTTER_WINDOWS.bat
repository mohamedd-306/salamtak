@echo off
REM Flutter App Launcher - Windows Desktop
REM Run Salamtak Flutter App as Windows Desktop Application

echo.
echo ========================================
echo   Salamtak Flutter App - Windows
echo ========================================
echo.

cd /d "c:\New folder\htdocs\swebsite\salamtak - Copy"

echo Checking Flutter installation...
flutter --version
echo.

echo Getting dependencies...
flutter pub get
echo.

echo Starting app on Windows Desktop...
echo.
echo Hot Reload Commands:
echo   r - Hot reload
echo   R - Hot restart
echo   q - Quit
echo.

flutter run -d windows

pause
