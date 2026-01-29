# Restart Zagros CMMS and Test
Write-Host "Stopping containers..." -ForegroundColor Yellow
docker-compose down

Write-Host "`nWaiting 5 seconds..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

Write-Host "`nStarting containers..." -ForegroundColor Yellow
docker-compose up -d

Write-Host "`nWaiting for services to be ready (30 seconds)..." -ForegroundColor Yellow
Start-Sleep -Seconds 30

Write-Host "`nTesting connection..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost/" -UseBasicParsing -TimeoutSec 5
    Write-Host "SUCCESS! Status Code: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Content Length: $($response.Content.Length) bytes" -ForegroundColor Green
} catch {
    Write-Host "FAILED: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "`nTrying to open in browser instead..." -ForegroundColor Yellow
    Start-Process "http://localhost/"
}

Write-Host "`nChecking containers:" -ForegroundColor Yellow
docker ps --filter "name=zagros" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

Write-Host "`nTo test login, open browser and go to: http://localhost/login" -ForegroundColor Cyan
Write-Host "Email: syeedalireza@yahoo.com" -ForegroundColor Cyan
Write-Host "Password: [your password]" -ForegroundColor Cyan
