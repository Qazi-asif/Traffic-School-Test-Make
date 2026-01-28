@echo off
echo Looking for PHP executable...

REM Try common PHP paths
if exist "D:\laragon\bin\php\php-8.2.28-Win32-vs16-x64\php.exe" (
    echo Found PHP at D:\laragon\bin\php\php-8.2.28-Win32-vs16-x64\php.exe
    "D:\laragon\bin\php\php-8.2.28-Win32-vs16-x64\php.exe" execute_role_fix.php
    goto :end
)

if exist "C:\laragon\bin\php\php-8.2.28-Win32-vs16-x64\php.exe" (
    echo Found PHP at C:\laragon\bin\php\php-8.2.28-Win32-vs16-x64\php.exe
    "C:\laragon\bin\php\php-8.2.28-Win32-vs16-x64\php.exe" execute_role_fix.php
    goto :end
)

REM Try to find PHP in PATH
php -v >nul 2>&1
if %errorlevel% == 0 (
    echo Found PHP in PATH
    php execute_role_fix.php
    goto :end
)

echo PHP not found. Please run the SQL manually or install PHP.
echo.
echo SQL to run:
echo UPDATE roles SET slug = 'temp-super-admin' WHERE id = 1;
echo UPDATE roles SET slug = 'temp-admin' WHERE id = 2;
echo UPDATE roles SET slug = 'temp-user' WHERE id = 3;
echo UPDATE roles SET name = 'Super Admin', slug = 'super-admin', description = 'Full system access', updated_at = NOW() WHERE id = 1;
echo UPDATE roles SET name = 'Admin', slug = 'admin', description = 'Administrative access', updated_at = NOW() WHERE id = 2;
echo UPDATE roles SET name = 'User', slug = 'user', description = 'Regular user access', updated_at = NOW() WHERE id = 3;

:end
pause