@echo off
echo ========================================
echo   DEPLOYING FIREBASE STORAGE RULES
echo ========================================
echo.
echo This will deploy the updated storage.rules file
echo to allow reading report images from Firebase Storage.
echo.
echo Project: salmtak-6fffe
echo.
pause

echo.
echo Deploying storage rules...
firebase deploy --only storage

echo.
echo ========================================
if %ERRORLEVEL% EQU 0 (
    echo   DEPLOYMENT SUCCESSFUL!
    echo ========================================
    echo.
    echo Report images should now load correctly.
    echo.
) else (
    echo   DEPLOYMENT FAILED!
    echo ========================================
    echo.
    echo Please check the error message above.
    echo You may need to run: firebase login
    echo.
)

pause
