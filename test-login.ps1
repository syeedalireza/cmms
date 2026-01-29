# Test Login Endpoint
Write-Host "Testing Zagros CMMS Login..." -ForegroundColor Cyan

# Prepare login data
$loginData = @{
    username = "syeedalireza@yahoo.com"
    password = "Admin@2026"
} | ConvertTo-Json

Write-Host "`nSending login request..." -ForegroundColor Yellow
Write-Host "URL: http://localhost/api/auth/login" -ForegroundColor Gray
Write-Host "Data: $loginData" -ForegroundColor Gray

try {
    $response = Invoke-WebRequest -Uri "http://localhost/api/auth/login" `
        -Method POST `
        -ContentType "application/json" `
        -Body $loginData `
        -UseBasicParsing `
        -TimeoutSec 30
    
    Write-Host "`n✅ SUCCESS!" -ForegroundColor Green
    Write-Host "Status Code: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Response:" -ForegroundColor Cyan
    Write-Host $response.Content
    
} catch {
    $statusCode = $_.Exception.Response.StatusCode.value__
    Write-Host "`n❌ Request Failed" -ForegroundColor Red
    Write-Host "Status Code: $statusCode" -ForegroundColor Yellow
    
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "Response:" -ForegroundColor Yellow
        Write-Host $responseBody
    } else {
        Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`n---"
Write-Host "Check nginx logs for details:" -ForegroundColor Gray
Write-Host "docker logs zagros_nginx --tail 5" -ForegroundColor White
