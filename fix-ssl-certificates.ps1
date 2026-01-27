# SSL Certificate Fix for Windows PHP
# Run this script as Administrator in PowerShell

Write-Host "🔧 Fixing SSL Certificates for PHP on Windows..." -ForegroundColor Green

# Create SSL directory
$sslDir = "C:\php\extras\ssl"
if (!(Test-Path $sslDir)) {
    New-Item -ItemType Directory -Path $sslDir -Force
    Write-Host "✅ Created directory: $sslDir" -ForegroundColor Green
}

# Download CA certificate bundle
$certUrl = "https://curl.se/ca/cacert.pem"
$certPath = "$sslDir\cacert.pem"

try {
    Write-Host "📥 Downloading CA certificate bundle..." -ForegroundColor Yellow
    Invoke-WebRequest -Uri $certUrl -OutFile $certPath -UseBasicParsing
    Write-Host "✅ Downloaded certificate bundle to: $certPath" -ForegroundColor Green
} catch {
    Write-Host "❌ Failed to download certificate bundle: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "💡 You may need to download manually from: $certUrl" -ForegroundColor Yellow
    exit 1
}

# Find PHP ini file
Write-Host "🔍 Finding PHP configuration..." -ForegroundColor Yellow
try {
    $phpIniPath = php --ini | Select-String "Loaded Configuration File:" | ForEach-Object { $_.ToString().Split(":")[1].Trim() }
    if ($phpIniPath -and (Test-Path $phpIniPath)) {
        Write-Host "✅ Found PHP ini file: $phpIniPath" -ForegroundColor Green
        
        # Read current php.ini content
        $phpIniContent = Get-Content $phpIniPath
        
        # Check if curl.cainfo is already set
        $curlCaInfoExists = $phpIniContent | Select-String "curl.cainfo"
        $opensslCaFileExists = $phpIniContent | Select-String "openssl.cafile"
        
        if (!$curlCaInfoExists) {
            Add-Content $phpIniPath "`ncurl.cainfo = `"$certPath`""
            Write-Host "✅ Added curl.cainfo to php.ini" -ForegroundColor Green
        } else {
            Write-Host "ℹ️  curl.cainfo already exists in php.ini" -ForegroundColor Yellow
        }
        
        if (!$opensslCaFileExists) {
            Add-Content $phpIniPath "`nopenssl.cafile = `"$certPath`""
            Write-Host "✅ Added openssl.cafile to php.ini" -ForegroundColor Green
        } else {
            Write-Host "ℹ️  openssl.cafile already exists in php.ini" -ForegroundColor Yellow
        }
        
    } else {
        Write-Host "❌ Could not find PHP ini file" -ForegroundColor Red
        Write-Host "💡 Manually add these lines to your php.ini:" -ForegroundColor Yellow
        Write-Host "curl.cainfo = `"$certPath`"" -ForegroundColor White
        Write-Host "openssl.cafile = `"$certPath`"" -ForegroundColor White
    }
} catch {
    Write-Host "❌ Error finding PHP configuration: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n🎉 SSL Certificate fix completed!" -ForegroundColor Green
Write-Host "💡 You may need to restart your web server/PHP-FPM" -ForegroundColor Yellow
Write-Host "🧪 Test with: php artisan states:test-all" -ForegroundColor Cyan