@echo off
echo ========================================
echo   Deploy Salamtak App to OnePlus 7T
echo ========================================
echo.

echo Checking for connected devices...
flutter devices

echo.
echo ========================================
echo If you see your device (HD1900), press any key to deploy...
echo If NOT, connect your phone and run this script again.
echo ========================================
pause

echo.
echo Building and deploying app...
flutter run -d e5bff4de

pause
