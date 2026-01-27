@echo off
REM Deployment script for timer fix (Windows)
REM This script clears all caches and ensures the updated files are deployed

echo.
echo 🚀 Deploying Timer Fix...
echo.

REM Step 1: Clear Laravel caches
echo 📦 Clearing Laravel caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo ✅ Laravel caches cleared
echo.

REM Step 2: Touch files to update modification time
echo 📝 Updating file timestamps...
copy /b public\js\strict-timer.js +,,
copy /b resources\views\course-player.blade.php +,,
echo ✅ File timestamps updated
echo.

REM Step 3: Optimize for production
echo ⚡ Optimizing for production...
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo ✅ Production optimization complete
echo.

echo ✨ Deployment complete!
echo.
echo 📋 Next steps:
echo 1. Clear your browser cache (Ctrl+Shift+Delete)
echo 2. Do a hard refresh (Ctrl+F5)
echo 3. Test the timer functionality
echo.
echo If issues persist, check:
echo - Browser console for JavaScript errors (F12)
echo - Server error logs: storage/logs/laravel.log
echo - Verify files were uploaded correctly
echo.
pause
