@echo off
echo Safe Database Import Method
echo ========================

REM Set variables
set MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe
set DB_NAME=check
set SQL_FILE=C:\Users\lenovo\Downloads\wolkeweb56541_elearning (3).sql

echo.
echo Step 1: Creating database if not exists...
"%MYSQL_PATH%" -u root -p -e "CREATE DATABASE IF NOT EXISTS %DB_NAME%;"

echo.
echo Step 2: Setting MySQL session variables...
"%MYSQL_PATH%" -u root -p %DB_NAME% -e "SET SESSION wait_timeout=28800; SET SESSION interactive_timeout=28800; SET SESSION max_allowed_packet=1073741824;"

echo.
echo Step 3: Importing with extended timeout and error handling...
"%MYSQL_PATH%" -u root -p --init-command="SET SESSION SQL_MODE=''; SET autocommit=0; SET unique_checks=0; SET foreign_key_checks=0;" --max_allowed_packet=1024M --net_buffer_length=32K %DB_NAME% < "%SQL_FILE%"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✅ Database imported successfully!
    echo.
    echo Step 4: Re-enabling constraints...
    "%MYSQL_PATH%" -u root -p %DB_NAME% -e "SET foreign_key_checks=1; SET unique_checks=1; COMMIT;"
) else (
    echo.
    echo ❌ Import failed at line 333 or later.
    echo Trying alternative method...
    goto :alternative
)

goto :end

:alternative
echo.
echo Alternative Method: Importing without foreign key checks...
"%MYSQL_PATH%" -u root -p --init-command="SET foreign_key_checks=0; SET autocommit=0;" --single-transaction --routines --triggers %DB_NAME% < "%SQL_FILE%"

:end
echo.
echo Import process completed.
pause