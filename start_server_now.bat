@echo off
title Laravel Development Server
color 0A

echo.
echo ========================================
echo    LARAVEL DEVELOPMENT SERVER
echo ========================================
echo.
echo Starting server on http://127.0.0.1:8000
echo.
echo Login URLs:
echo - Florida: http://127.0.0.1:8000/florida/login
echo - Missouri: http://127.0.0.1:8000/missouri/login
echo - Texas: http://127.0.0.1:8000/texas/login
echo - Delaware: http://127.0.0.1:8000/delaware/login
echo.
echo Test Credentials:
echo Email: florida@test.com
echo Password: password123
echo.
echo Press Ctrl+C to stop the server
echo ========================================
echo.

php artisan serve --host=127.0.0.1 --port=8000

pause