@echo off
echo === Fixing Composer Autoload ===
echo.

REM Try different composer paths
if exist "composer.phar" (
    echo Using local composer.phar...
    php composer.phar dump-autoload
    goto :end
)

if exist "D:\laragon\bin\composer\composer.phar" (
    echo Using Laragon composer...
    php "D:\laragon\bin\composer\composer.phar" dump-autoload
    goto :end
)

REM Try system PATH
composer dump-autoload 2>nul
if %errorlevel% equ 0 goto :end

echo.
echo ERROR: Composer not found!
echo Please run this command manually in Laragon terminal:
echo composer dump-autoload
echo.

:end
echo.
echo Done! Now test your DOCX import functionality.
echo Visit: http://localhost/nelly-elearning/docx-status
echo.
pause