@echo off
echo Starting database import...

REM Set MySQL path (adjust if needed)
set MYSQL_PATH="C:\xampp\mysql\bin\mysql.exe"

REM Database credentials
set DB_HOST=127.0.0.1
set DB_USER=root
set DB_PASS=
set DB_NAME=your_database_name

REM SQL file path
set SQL_FILE="C:\Users\lenovo\Downloads\wolkeweb56541_elearning (3).sql"

echo Importing database...
%MYSQL_PATH% -h %DB_HOST% -u %DB_USER% -p%DB_PASS% --max_allowed_packet=1024M %DB_NAME% < %SQL_FILE%

if %ERRORLEVEL% EQU 0 (
    echo Database imported successfully!
) else (
    echo Import failed. Check the error messages above.
)

pause