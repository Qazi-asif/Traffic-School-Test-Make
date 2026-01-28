@echo off
echo Starting Laravel Development Server...
echo =====================================

REM Check if we're in the right directory
if not exist "artisan" (
    echo Error: artisan file not found. Make sure you're in the Laravel project directory.
    pause
    exit /b 1
)

echo Clearing Laravel caches...
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo Starting server on http://127.0.0.1:8000
echo Press Ctrl+C to stop the server
echo.
echo Login URLs:
echo - Florida: http://127.0.0.1:8000/florida/login
echo - Missouri: http://127.0.0.1:8000/missouri/login  
echo - Texas: http://127.0.0.1:8000/texas/login
echo - Delaware: http://127.0.0.1:8000/delaware/login
echo.
echo Test Credentials:
echo - florida@test.com / password123
echo - missouri@test.com / password123
echo - texas@test.com / password123
echo - delaware@test.com / password123
echo - admin@test.com / admin123
echo.

php artisan serve --host=127.0.0.1 --port=8000