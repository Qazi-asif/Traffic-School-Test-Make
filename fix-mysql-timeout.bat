@echo off
echo Fixing MySQL timeout issues...

echo.
echo Step 1: Stop MySQL service
net stop mysql80

echo.
echo Step 2: Backup current my.ini
copy "C:\xampp\mysql\bin\my.ini" "C:\xampp\mysql\bin\my.ini.backup"

echo.
echo Step 3: Update MySQL configuration
echo Adding timeout and packet size settings...

REM Add configuration to my.ini
echo. >> "C:\xampp\mysql\bin\my.ini"
echo [mysql] >> "C:\xampp\mysql\bin\my.ini"
echo max_allowed_packet = 1024M >> "C:\xampp\mysql\bin\my.ini"
echo wait_timeout = 28800 >> "C:\xampp\mysql\bin\my.ini"
echo interactive_timeout = 28800 >> "C:\xampp\mysql\bin\my.ini"
echo. >> "C:\xampp\mysql\bin\my.ini"
echo [mysqld] >> "C:\xampp\mysql\bin\my.ini"
echo max_allowed_packet = 1024M >> "C:\xampp\mysql\bin\my.ini"
echo wait_timeout = 28800 >> "C:\xampp\mysql\bin\my.ini"
echo interactive_timeout = 28800 >> "C:\xampp\mysql\bin\my.ini"
echo innodb_buffer_pool_size = 512M >> "C:\xampp\mysql\bin\my.ini"
echo innodb_log_file_size = 128M >> "C:\xampp\mysql\bin\my.ini"
echo net_read_timeout = 600 >> "C:\xampp\mysql\bin\my.ini"
echo net_write_timeout = 600 >> "C:\xampp\mysql\bin\my.ini"

echo.
echo Step 4: Start MySQL service
net start mysql80

echo.
echo MySQL configuration updated successfully!
echo You can now try importing the database again.

pause