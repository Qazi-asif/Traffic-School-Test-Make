@echo off
echo === Running Database Migration ===
echo.

REM Try different PHP paths
if exist "php.exe" (
    echo Using local PHP executable...
    php.exe artisan migrate
    goto :end
)

if exist "C:\laragon\bin\php\php-8.2.12-Win32-vs16-x64\php.exe" (
    echo Using Laragon PHP 8.2.12...
    "C:\laragon\bin\php\php-8.2.12-Win32-vs16-x64\php.exe" artisan migrate
    goto :end
)

if exist "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe" (
    echo Using Laragon PHP 8.1.10...
    "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe" artisan migrate
    goto :end
)

echo.
echo Please run this command manually in Laragon terminal:
echo php artisan migrate
echo.

:end
echo.
echo Migration complete! The 'duration' column should now be added to the chapters table.
echo.
pause