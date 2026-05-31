@echo off
REM Flutter App Launcher - Chrome
REM Run Salamtak Flutter App on Chrome Browser

echo.
echo ========================================
echo   Salamtak Flutter App - Chrome
echo ========================================
echo.

cd /d "c:\New folder\htdocs\swebsite\salamtak - Copy"

echo Checking Flutter installation...
flutter --version
echo.

echo Getting dependencies...
flutter pub get
echo.

echo Starting app on Chrome...
echo.
echo Hot Reload Commands:
echo   r - Hot reload
echo   R - Hot restart
echo   q - Quit
echo.

flutter run -d chrome

pause
