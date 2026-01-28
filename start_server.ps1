#!/usr/bin/env pwsh

Write-Host "🚀 Starting Laravel Development Server" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
Write-Host ""

# Check if we're in the right directory
if (-not (Test-Path "artisan")) {
    Write-Host "❌ Error: artisan file not found" -ForegroundColor Red
    Write-Host "Make sure you're in the Laravel project directory" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "✅ Laravel project detected" -ForegroundColor Green

# Clear caches
Write-Host "🧹 Clearing Laravel caches..." -ForegroundColor Yellow
try {
    & php artisan config:clear
    & php artisan route:clear  
    & php artisan view:clear
    Write-Host "✅ Caches cleared successfully" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Cache clearing failed, continuing anyway..." -ForegroundColor Yellow
}

# Check if port 8000 is available
Write-Host "🔍 Checking port availability..." -ForegroundColor Yellow
try {
    $connection = New-Object System.Net.Sockets.TcpClient
    $connection.Connect("127.0.0.1", 8000)
    $connection.Close()
    Write-Host "⚠️  Port 8000 is already in use" -ForegroundColor Yellow
    Write-Host "Trying to start on port 8001 instead..." -ForegroundColor Yellow
    $port = 8001
} catch {
    Write-Host "✅ Port 8000 is available" -ForegroundColor Green
    $port = 8000
}

Write-Host ""
Write-Host "🌐 Server will be available at:" -ForegroundColor Cyan
Write-Host "   http://127.0.0.1:$port" -ForegroundColor White
Write-Host ""
Write-Host "🔑 Login URLs:" -ForegroundColor Cyan
Write-Host "   Florida:  http://127.0.0.1:$port/florida/login" -ForegroundColor White
Write-Host "   Missouri: http://127.0.0.1:$port/missouri/login" -ForegroundColor White
Write-Host "   Texas:    http://127.0.0.1:$port/texas/login" -ForegroundColor White
Write-Host "   Delaware: http://127.0.0.1:$port/delaware/login" -ForegroundColor White
Write-Host ""
Write-Host "👤 Test Credentials:" -ForegroundColor Cyan
Write-Host "   Email:    florida@test.com" -ForegroundColor White
Write-Host "   Password: password123" -ForegroundColor White
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""

# Start the server
try {
    & php artisan serve --host=127.0.0.1 --port=$port
} catch {
    Write-Host "❌ Failed to start with artisan serve" -ForegroundColor Red
    Write-Host "Trying alternative method..." -ForegroundColor Yellow
    
    try {
        & php -S "127.0.0.1:$port" -t public
    } catch {
        Write-Host "❌ All server methods failed" -ForegroundColor Red
        Write-Host "Please check if PHP is properly installed" -ForegroundColor Yellow
        Read-Host "Press Enter to exit"
    }
}