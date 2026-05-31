@echo off
echo ========================================
echo   Push Salamtak to GitHub
echo ========================================
echo.

REM Check if remote exists
git remote -v > nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Git remote not configured!
    echo.
    echo Please follow these steps:
    echo 1. Create a new repository on GitHub
    echo 2. Run this command with YOUR GitHub username:
    echo.
    echo    git remote add origin https://github.com/YOUR_USERNAME/salamtak.git
    echo.
    pause
    exit /b 1
)

echo Current Git Status:
echo -------------------
git status
echo.

echo Current Remote:
echo ---------------
git remote -v
echo.

set /p confirm="Do you want to push to GitHub? (y/n): "
if /i not "%confirm%"=="y" (
    echo Push cancelled.
    pause
    exit /b 0
)

echo.
echo Pushing to GitHub...
echo.

git push -u origin master

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo   SUCCESS! Project pushed to GitHub!
    echo ========================================
    echo.
    echo Your repository is now available at:
    echo https://github.com/YOUR_USERNAME/salamtak
    echo.
) else (
    echo.
    echo ========================================
    echo   ERROR: Push failed!
    echo ========================================
    echo.
    echo Common issues:
    echo 1. Remote not configured - Run: git remote add origin URL
    echo 2. Authentication failed - Use personal access token
    echo 3. Branch name mismatch - Check branch name
    echo.
)

pause
