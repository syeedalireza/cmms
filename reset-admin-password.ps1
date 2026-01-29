# Reset Admin Password for Zagros CMMS
param(
    [string]$NewPassword = "Admin@2026"
)

Write-Host "Resetting admin password..." -ForegroundColor Yellow
Write-Host "New Password: $NewPassword" -ForegroundColor Cyan

# Generate password hash
$hashCommand = "docker exec zagros_backend php bin/console security:hash-password `"$NewPassword`" --no-interaction"
Write-Host "`nGenerating password hash..." -ForegroundColor Yellow
$output = Invoke-Expression $hashCommand

# Extract the hash (it's usually after "Password hash")
$hash = ($output | Select-String "Hashed password\s+(.+)" -AllMatches).Matches[0].Groups[1].Value.Trim()

if ([string]::IsNullOrEmpty($hash)) {
    # Try alternative pattern
    $hash = ($output -split "`n" | Where-Object { $_ -match '^\s+\$' } | Select-Object -First 1).Trim()
}

Write-Host "Hash: $hash" -ForegroundColor Gray

if ([string]::IsNullOrEmpty($hash)) {
    Write-Host "`nERROR: Could not generate password hash" -ForegroundColor Red
    Write-Host "Please run manually:" -ForegroundColor Yellow
    Write-Host "  docker exec zagros_backend php bin/console app:create-admin" -ForegroundColor Cyan
    exit 1
}

# Update password in database
$updateSQL = "UPDATE users SET password = '$hash' WHERE email = 'syeedalireza@yahoo.com';"
$sqlCommand = "docker exec zagros_postgres psql -U cmms -d zagros_cmms -c `"$updateSQL`""

Write-Host "`nUpdating password in database..." -ForegroundColor Yellow
Invoke-Expression $sqlCommand

Write-Host "`n✅ Password updated successfully!" -ForegroundColor Green
Write-Host "`nLogin Details:" -ForegroundColor Cyan
Write-Host "  URL: http://localhost/login" -ForegroundColor White
Write-Host "  Email: syeedalireza@yahoo.com" -ForegroundColor White
Write-Host "  Password: $NewPassword" -ForegroundColor White
