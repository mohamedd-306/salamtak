@echo off
echo ========================================
echo  ADMIN PRODUCT MANAGEMENT DEPLOYMENT
echo ========================================
echo.

cd /d "%~dp0"

echo Step 1: Logging in to Firebase...
echo (This will open your browser)
echo.
firebase login
if errorlevel 1 (
    echo ERROR: Firebase login failed!
    pause
    exit /b 1
)

echo.
echo Step 2: Setting active project...
echo.
firebase use --add
if errorlevel 1 (
    echo ERROR: Failed to set project!
    pause
    exit /b 1
)

echo.
echo Step 3: Deploying Firebase rules and indexes...
echo.
firebase deploy --only firestore:rules,storage,firestore:indexes
if errorlevel 1 (
    echo ERROR: Deployment failed!
    pause
    exit /b 1
)

echo.
echo ========================================
echo  DEPLOYMENT COMPLETE!
echo ========================================
echo.
echo Next steps:
echo 1. Verify your admin user has userType: 'admin' in Firestore
echo 2. Run: flutter run
echo 3. Test the feature!
echo.
pause
