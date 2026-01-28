@echo off
echo === DOCX Import Test Runner ===
echo.

REM Try different PHP paths
if exist "php.exe" (
    echo Using local PHP executable...
    php.exe quick-docx-test.php
    goto :end
)

if exist "C:\laragon\bin\php\php-8.2.12-Win32-vs16-x64\php.exe" (
    echo Using Laragon PHP 8.2.12...
    "C:\laragon\bin\php\php-8.2.12-Win32-vs16-x64\php.exe" quick-docx-test.php
    goto :end
)

if exist "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe" (
    echo Using Laragon PHP 8.1.10...
    "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe" quick-docx-test.php
    goto :end
)

if exist "C:\laragon\bin\php\php-8.0.23-Win32-vs16-x64\php.exe" (
    echo Using Laragon PHP 8.0.23...
    "C:\laragon\bin\php\php-8.0.23-Win32-vs16-x64\php.exe" quick-docx-test.php
    goto :end
)

REM Try system PATH
php quick-docx-test.php 2>nul
if %errorlevel% equ 0 goto :end

echo.
echo ERROR: PHP not found!
echo.
echo Please ensure PHP is installed and accessible.
echo Common locations:
echo - C:\laragon\bin\php\[version]\php.exe
echo - C:\xampp\php\php.exe
echo - C:\wamp\bin\php\[version]\php.exe
echo.
echo Or add PHP to your system PATH.
echo.

:end
echo.
echo Press any key to continue...
pause >nul