# Salamtak Web App Launcher (PowerShell)
# Run this script: .\run-app.ps1

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   Salamtak Web App Launcher" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if Apache and MySQL are running
Write-Host "Checking XAMPP services..." -ForegroundColor Yellow
$apache = Get-Process -Name "httpd" -ErrorAction SilentlyContinue
$mysql = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue

if ($apache) {
    Write-Host "✓ Apache is running" -ForegroundColor Green
} else {
    Write-Host "✗ Apache is NOT running" -ForegroundColor Red
    Write-Host "  Please start XAMPP Apache first!" -ForegroundColor Yellow
}

if ($mysql) {
    Write-Host "✓ MySQL is running" -ForegroundColor Green
} else {
    Write-Host "✗ MySQL is NOT running" -ForegroundColor Red
    Write-Host "  Please start XAMPP MySQL first!" -ForegroundColor Yellow
}

Write-Host ""

# Menu
Write-Host "Select an option:" -ForegroundColor Cyan
Write-Host "1. Open Home Page" -ForegroundColor White
Write-Host "2. Open Admin Dashboard" -ForegroundColor White
Write-Host "3. Open User Products" -ForegroundColor White
Write-Host "4. Open Login Page" -ForegroundColor White
Write-Host "5. Exit" -ForegroundColor White
Write-Host ""

$choice = Read-Host "Enter your choice (1-5)"

switch ($choice) {
    "1" {
        Write-Host "Opening Home Page..." -ForegroundColor Green
        Start-Process "chrome.exe" "http://localhost/salamtak_web/home.php"
    }
    "2" {
        Write-Host "Opening Admin Dashboard..." -ForegroundColor Green
        Start-Process "chrome.exe" "http://localhost/salamtak_web/admin/dashboard.php"
    }
    "3" {
        Write-Host "Opening User Products..." -ForegroundColor Green
        Start-Process "chrome.exe" "http://localhost/salamtak_web/user/products.php"
    }
    "4" {
        Write-Host "Opening Login Page..." -ForegroundColor Green
        Start-Process "chrome.exe" "http://localhost/salamtak_web/login.php"
    }
    "5" {
        Write-Host "Exiting..." -ForegroundColor Yellow
        exit
    }
    default {
        Write-Host "Invalid choice!" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Done!" -ForegroundColor Green
Write-Host ""
