<?php
echo "=== Strict Timer System Test ===\n\n";

// Check if server is running
$serverCheck = @file_get_contents('http://127.0.0.1:8000');
if ($serverCheck === false) {
    echo "❌ Laravel server is not running!\n";
    echo "Please run: php artisan serve\n";
    exit(1);
}

echo "✅ Laravel server is running\n";

// Check if timer configuration exists
echo "Checking timer configuration...\n";
$output = shell_exec('php artisan tinker --execute="echo App\Models\CourseTimer::count();"');
$timerCount = (int) trim($output);

if ($timerCount == 0) {
    echo "Creating test timer configuration...\n";
    shell_exec('php artisan tinker --execute="App\Models\CourseTimer::create([\'chapter_id\' => 1, \'chapter_type\' => \'chapters\', \'required_time_minutes\' => 2, \'is_enabled\' => true, \'allow_pause\' => true, \'bypass_for_admin\' => true]);"');
    echo "✅ Test timer created for Chapter 1 (2 minutes)\n";
} else {
    echo "✅ Timer configuration exists ($timerCount timers)\n";
}

// Check if test user exists
echo "Checking test user...\n";
$userCheck = shell_exec('php artisan tinker --execute="echo App\Models\User::where(\'email\', \'admin@example.com\')->count();"');
$userExists = (int) trim($userCheck);

if ($userExists == 0) {
    echo "Creating test user...\n";
    shell_exec('php artisan tinker --execute="App\Models\User::create([\'name\' => \'Test Admin\', \'email\' => \'admin@example.com\', \'password\' => bcrypt(\'password123\'), \'role_id\' => 1]);"');
    echo "✅ Test user created\n";
} else {
    echo "✅ Test user exists\n";
}

echo "\n=== MANUAL TESTING INSTRUCTIONS ===\n";
echo "1. Open browser and go to: http://127.0.0.1:8000/login\n";
echo "2. Login with:\n";
echo "   Email: admin@example.com\n";
echo "   Password: password123\n";
echo "3. After login, go to: http://127.0.0.1:8000/test-timer\n";
echo "4. Enter Chapter ID: 1\n";
echo "5. Click 'Start Timer Test'\n";
echo "6. Try the following to test violations:\n";
echo "   - Switch to another tab (Alt+Tab)\n";
echo "   - Right-click on the page\n";
echo "   - Press F12 to open dev tools\n";
echo "   - Press Ctrl+R to reload\n";
echo "   - Press F5 to refresh\n";
echo "   - Try to open new tab with Ctrl+T\n";
echo "\n✅ All violations should be detected and logged!\n";
echo "\n=== EXPECTED BEHAVIOR ===\n";
echo "- Timer should start and count up to 2 minutes (120 seconds)\n";
echo "- All bypass attempts should be blocked and logged\n";
echo "- Violations should appear in the violation log\n";
echo "- Timer should complete automatically after 2 minutes\n";
echo "- Progress bar should show completion percentage\n";
echo "\n=== DEBUGGING ===\n";
echo "If timer doesn't start, check Laravel logs:\n";
echo "- tail -f storage/logs/laravel.log (Linux/Mac)\n";
echo "- Get-Content storage/logs/laravel.log -Tail 10 -Wait (Windows PowerShell)\n";
echo "\nDatabase foreign key issue should now be fixed!\n";
?>