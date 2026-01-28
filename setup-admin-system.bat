@echo off
echo ========================================
echo  TRAFFIC SCHOOL ADMIN SYSTEM SETUP
echo ========================================
echo.

echo [1/4] Running database migrations...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo ERROR: Migration failed!
    pause
    exit /b 1
)

echo [2/4] Seeding admin users...
php artisan db:seed --class=AdminUserSeeder --force
if %errorlevel% neq 0 (
    echo ERROR: Admin user seeding failed!
    pause
    exit /b 1
)

echo [3/4] Seeding system settings...
php artisan db:seed --class=SystemSettingSeeder --force
if %errorlevel% neq 0 (
    echo ERROR: System settings seeding failed!
    pause
    exit /b 1
)

echo [4/4] Seeding sample users...
php artisan db:seed --class=UserDataSeeder --force
if %errorlevel% neq 0 (
    echo ERROR: User data seeding failed!
    pause
    exit /b 1
)

echo.
echo ========================================
echo  ADMIN SYSTEM SETUP COMPLETE!
echo ========================================
echo.
echo ADMIN LOGIN CREDENTIALS:
echo ------------------------
echo Super Admin: admin@dummiestrafficschool.com / admin123
echo Florida Admin: florida@dummiestrafficschool.com / florida123
echo Missouri Admin: missouri@dummiestrafficschool.com / missouri123
echo Texas Admin: texas@dummiestrafficschool.com / texas123
echo Delaware Admin: delaware@dummiestrafficschool.com / delaware123
echo Instructor: instructor@dummiestrafficschool.com / instructor123
echo.
echo ACCESS URLS:
echo -----------
echo Admin Login: /admin/login
echo Admin Dashboard: /admin/dashboard
echo.
echo SAMPLE USERS CREATED:
echo --------------------
echo - 11 sample student users across all states
echo - Sample enrollments and payments
echo - Various user statuses for testing
echo.
echo Ready to use! Visit /admin/login to get started.
echo.
pause