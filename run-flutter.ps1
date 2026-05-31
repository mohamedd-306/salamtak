# Salamtak Flutter App Launcher (PowerShell)
# Interactive menu to run Flutter app on different platforms

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   Salamtak Flutter App Launcher" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Change to project directory
Set-Location "c:\New folder\htdocs\swebsite\salamtak - Copy"

# Check Flutter installation
Write-Host "Checking Flutter installation..." -ForegroundColor Yellow
flutter --version
Write-Host ""

# Check available devices
Write-Host "Available devices:" -ForegroundColor Yellow
flutter devices
Write-Host ""

# Menu
Write-Host "Select platform to run:" -ForegroundColor Cyan
Write-Host "1. Chrome (Web)" -ForegroundColor White
Write-Host "2. Windows Desktop" -ForegroundColor White
Write-Host "3. Edge (Web)" -ForegroundColor White
Write-Host "4. Get Dependencies Only" -ForegroundColor White
Write-Host "5. Clean & Get Dependencies" -ForegroundColor White
Write-Host "6. Build for Web (Release)" -ForegroundColor White
Write-Host "7. Exit" -ForegroundColor White
Write-Host ""

$choice = Read-Host "Enter your choice (1-7)"

switch ($choice) {
    "1" {
        Write-Host ""
        Write-Host "Getting dependencies..." -ForegroundColor Green
        flutter pub get
        Write-Host ""
        Write-Host "Starting app on Chrome..." -ForegroundColor Green
        Write-Host ""
        Write-Host "Hot Reload Commands:" -ForegroundColor Yellow
        Write-Host "  r - Hot reload" -ForegroundColor White
        Write-Host "  R - Hot restart" -ForegroundColor White
        Write-Host "  q - Quit" -ForegroundColor White
        Write-Host ""
        flutter run -d chrome
    }
    "2" {
        Write-Host ""
        Write-Host "Getting dependencies..." -ForegroundColor Green
        flutter pub get
        Write-Host ""
        Write-Host "Starting app on Windows Desktop..." -ForegroundColor Green
        Write-Host ""
        Write-Host "Hot Reload Commands:" -ForegroundColor Yellow
        Write-Host "  r - Hot reload" -ForegroundColor White
        Write-Host "  R - Hot restart" -ForegroundColor White
        Write-Host "  q - Quit" -ForegroundColor White
        Write-Host ""
        flutter run -d windows
    }
    "3" {
        Write-Host ""
        Write-Host "Getting dependencies..." -ForegroundColor Green
        flutter pub get
        Write-Host ""
        Write-Host "Starting app on Edge..." -ForegroundColor Green
        Write-Host ""
        Write-Host "Hot Reload Commands:" -ForegroundColor Yellow
        Write-Host "  r - Hot reload" -ForegroundColor White
        Write-Host "  R - Hot restart" -ForegroundColor White
        Write-Host "  q - Quit" -ForegroundColor White
        Write-Host ""
        flutter run -d edge
    }
    "4" {
        Write-Host ""
        Write-Host "Getting dependencies..." -ForegroundColor Green
        flutter pub get
        Write-Host ""
        Write-Host "Dependencies installed successfully!" -ForegroundColor Green
    }
    "5" {
        Write-Host ""
        Write-Host "Cleaning build..." -ForegroundColor Yellow
        flutter clean
        Write-Host ""
        Write-Host "Getting dependencies..." -ForegroundColor Green
        flutter pub get
        Write-Host ""
        Write-Host "Clean and dependencies completed!" -ForegroundColor Green
    }
    "6" {
        Write-Host ""
        Write-Host "Building for web (release mode)..." -ForegroundColor Green
        flutter build web --release
        Write-Host ""
        Write-Host "Build completed! Output in: build\web\" -ForegroundColor Green
    }
    "7" {
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
