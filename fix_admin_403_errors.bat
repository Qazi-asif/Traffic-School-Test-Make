@echo off
echo ========================================
echo FIXING 403 ADMIN ERRORS
echo ========================================
echo.

echo Looking for PHP and running Laravel artisan command...
echo.

REM Try to find PHP and run the artisan command
if exist "D:\laragon\bin\php\php-8.2.28-Win32-vs16-x64\php.exe" (
    echo Found PHP at D:\laragon\bin\php\php-8.2.28-Win32-vs16-x64\php.exe
    "D:\laragon\bin\php\php-8.2.28-Win32-vs16-x64\php.exe" artisan fix:roles
    goto :success
)

if exist "C:\laragon\bin\php\php-8.2.28-Win32-vs16-x64\php.exe" (
    echo Found PHP at C:\laragon\bin\php\php-8.2.28-Win32-vs16-x64\php.exe
    "C:\laragon\bin\php\php-8.2.28-Win32-vs16-x64\php.exe" artisan fix:roles
    goto :success
)

REM Try to find PHP in PATH
php -v >nul 2>&1
if %errorlevel% == 0 (
    echo Found PHP in PATH
    php artisan fix:roles
    goto :success
)

echo.
echo ========================================
echo PHP NOT FOUND - MANUAL INSTRUCTIONS
echo ========================================
echo.
echo Since PHP is not available, please run these SQL commands manually:
echo.
echo 1. Open your database management tool (phpMyAdmin, MySQL Workbench, etc.)
echo 2. Connect to the 'nelly-elearning' database
echo 3. Run these SQL commands:
echo.
echo UPDATE roles SET slug = 'temp-super-admin' WHERE id = 1;
echo UPDATE roles SET slug = 'temp-admin' WHERE id = 2;
echo UPDATE roles SET slug = 'temp-user' WHERE id = 3;
echo.
echo UPDATE roles SET name = 'Super Admin', slug = 'super-admin', description = 'Full system access', updated_at = NOW() WHERE id = 1;
echo UPDATE roles SET name = 'Admin', slug = 'admin', description = 'Administrative access', updated_at = NOW() WHERE id = 2;
echo UPDATE roles SET name = 'User', slug = 'user', description = 'Regular user access', updated_at = NOW() WHERE id = 3;
echo.
echo 4. Ensure you have an admin user by checking:
echo SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.role_id IN (1, 2);
echo.
echo 5. If no admin users exist, promote a user:
echo UPDATE users SET role_id = 1 WHERE id = 1;
echo.
goto :end

:success
echo.
echo ========================================
echo SUCCESS! 403 ERRORS SHOULD BE FIXED
echo ========================================
echo.
echo Next steps:
echo 1. Clear your browser cache and cookies
echo 2. Log out and log back in
echo 3. Test these admin routes:
echo    - http://nelly-elearning.test/admin/state-transmissions
echo    - http://nelly-elearning.test/admin/certificates
echo    - http://nelly-elearning.test/admin/users
echo    - http://nelly-elearning.test/admin/dashboard
echo    - http://nelly-elearning.test/booklets
echo.

:end
pause