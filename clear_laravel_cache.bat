@echo off
echo Clearing Laravel caches...
echo.

echo Clearing config cache...
php artisan config:clear

echo Clearing route cache...
php artisan route:clear

echo Clearing view cache...
php artisan view:clear

echo Clearing application cache...
php artisan cache:clear

echo Clearing compiled views...
php artisan view:clear

echo.
echo All caches cleared!
echo Please refresh your browser now.
pause