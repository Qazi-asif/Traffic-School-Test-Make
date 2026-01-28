@echo off
echo 🔄 Restarting Laragon Services...
echo ================================

echo.
echo 1. Stopping Laragon services...
taskkill /f /im "laragon.exe" 2>nul
timeout /t 2 /nobreak >nul

echo 2. Starting Laragon...
start "" "C:\laragon\laragon.exe"
timeout /t 3 /nobreak >nul

echo 3. Waiting for services to start...
timeout /t 5 /nobreak >nul

echo.
echo ✅ Laragon restart completed!
echo.
echo 🌐 Now test these URLs:
echo =====================
echo 1. http://nelly-elearning.test/welcome.html
echo 2. http://nelly-elearning.test/status.php  
echo 3. http://nelly-elearning.test/florida/login
echo.
echo 🔑 Login: florida@test.com / password123
echo.
pause