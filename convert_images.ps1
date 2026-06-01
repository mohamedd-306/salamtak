# Product Image Conversion Script
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Converting Product Images to Base64" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$url = "http://localhost/swebsite/salamtak%20-%20Copy/salamtak_web/auto_fix_products.php"

Write-Host "Fetching products from Firestore..." -ForegroundColor Yellow
Write-Host "URL: $url" -ForegroundColor Gray
Write-Host ""

try {
    $response = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 60
    
    # Save the HTML response to a file for viewing
    $outputFile = "conversion_report.html"
    $response.Content | Out-File -FilePath $outputFile -Encoding UTF8
    
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "  Conversion Complete!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Report saved to: $outputFile" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Opening report in browser..." -ForegroundColor Yellow
    Start-Process $outputFile
    
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Cyan
    Write-Host "1. Check the report in your browser" -ForegroundColor White
    Write-Host "2. Refresh your website to see images" -ForegroundColor White
    Write-Host "3. Hot restart Flutter app (press R in terminal)" -ForegroundColor White
    Write-Host ""
    
} catch {
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "  Error!" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "Error message: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please check:" -ForegroundColor Yellow
    Write-Host "1. XAMPP Apache is running" -ForegroundColor White
    Write-Host "2. PHP GD extension is enabled" -ForegroundColor White
    Write-Host "3. Internet connection is active (for Firestore)" -ForegroundColor White
    Write-Host ""
}

Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
