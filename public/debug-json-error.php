<?php
// Debug JSON Error - Check what's actually being returned
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>Debug JSON Error</h1>";
echo "<pre>";

try {
    echo "=== DEBUGGING JSON ERROR ===\n\n";
    
    // 1. Test database connection
    echo "1. Testing database connection...\n";
    try {
        $connection = DB::connection()->getPdo();
        echo "   ✅ Database connection works\n";
    } catch (Exception $e) {
        echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
        echo "   This could cause 500 errors that return HTML instead of JSON\n";
    }
    
    // 2. Test florida_courses table
    echo "\n2. Testing florida_courses table...\n";
    try {
        $courseCount = DB::table('florida_courses')->count();
        echo "   ✅ florida_courses table accessible, found {$courseCount} courses\n";
    } catch (Exception $e) {
        echo "   ❌ florida_courses table error: " . $e->getMessage() . "\n";
        echo "   This could cause 500 errors in course endpoints\n";
    }
    
    // 3. Test users table and roles
    echo "\n3. Testing users table and roles...\n";
    try {
        $userCount = DB::table('users')->count();
        $adminCount = DB::table('users')->whereIn('role', ['admin', 'super-admin'])->count();
        echo "   ✅ users table accessible, found {$userCount} users, {$adminCount} admins\n";
    } catch (Exception $e) {
        echo "   ❌ users table error: " . $e->getMessage() . "\n";
        echo "   This could cause authentication/authorization errors\n";
    }
    
    // 4. Test specific endpoints that commonly cause JSON errors
    echo "\n4. Testing common endpoints...\n";
    
    $endpoints = [
        '/web/courses' => 'CourseController@indexWeb',
        '/api/florida-courses' => 'FloridaCourseController@indexWeb',
        '/api/check-notifications' => 'NotificationController (if exists)'
    ];
    
    foreach ($endpoints as $endpoint => $controller) {
        echo "   Testing {$endpoint} ({$controller})...\n";
        
        try {
            if ($endpoint === '/web/courses') {
                $controller = new \App\Http\Controllers\CourseController();
                $request = new \Illuminate\Http\Request();
                $response = $controller->indexWeb($request);
                $status = $response->getStatusCode();
                
                if ($status === 200) {
                    $content = $response->getContent();
                    $isJson = json_decode($content) !== null;
                    echo "     ✅ Status: {$status}, Valid JSON: " . ($isJson ? 'Yes' : 'No') . "\n";
                    if (!$isJson) {
                        echo "     ⚠️ Response content preview: " . substr($content, 0, 100) . "...\n";
                    }
                } else {
                    echo "     ❌ Status: {$status}\n";
                    echo "     Response: " . substr($response->getContent(), 0, 200) . "...\n";
                }
            } elseif ($endpoint === '/api/florida-courses') {
                $controller = new \App\Http\Controllers\FloridaCourseController();
                $response = $controller->indexWeb();
                $status = $response->getStatusCode();
                
                if ($status === 200) {
                    $content = $response->getContent();
                    $isJson = json_decode($content) !== null;
                    echo "     ✅ Status: {$status}, Valid JSON: " . ($isJson ? 'Yes' : 'No') . "\n";
                } else {
                    echo "     ❌ Status: {$status}\n";
                    echo "     Response: " . substr($response->getContent(), 0, 200) . "...\n";
                }
            } else {
                echo "     ⚠️ Endpoint not testable directly\n";
            }
        } catch (Exception $e) {
            echo "     ❌ Error: " . $e->getMessage() . "\n";
            echo "     File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
    }
    
    // 5. Check for common Laravel error conditions
    echo "\n5. Checking Laravel configuration...\n";
    
    // Check if APP_DEBUG is enabled
    $debug = env('APP_DEBUG', false);
    echo "   APP_DEBUG: " . ($debug ? 'true (shows detailed errors)' : 'false (shows generic errors)') . "\n";
    
    // Check if maintenance mode is enabled
    if (file_exists(storage_path('framework/maintenance.php'))) {
        echo "   ⚠️ Maintenance mode is ENABLED - this returns HTML instead of JSON\n";
    } else {
        echo "   ✅ Maintenance mode is disabled\n";
    }
    
    echo "\n🔍 COMMON CAUSES OF JSON ERROR:\n";
    echo "1. 500 Internal Server Error returning Laravel error page (HTML)\n";
    echo "2. 403 Forbidden returning login redirect (HTML)\n";
    echo "3. Maintenance mode returning maintenance page (HTML)\n";
    echo "4. Missing route returning 404 page (HTML)\n";
    echo "5. CSRF token mismatch returning error page (HTML)\n";
    
    echo "\n💡 SOLUTIONS:\n";
    echo "1. Check browser Network tab to see actual HTTP status and response\n";
    echo "2. Check Laravel logs: storage/logs/laravel.log\n";
    echo "3. Enable APP_DEBUG=true in .env to see detailed errors\n";
    echo "4. Ensure user is logged in and has proper role\n";
    echo "5. Check CSRF token is being sent with AJAX requests\n";
    
} catch (Exception $e) {
    echo "❌ DEBUG ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";

echo "<h2>Next Steps</h2>";
echo "<p>1. Check your browser's Network tab to see which endpoint is failing</p>";
echo "<p>2. Look at the actual HTTP response - it's probably HTML instead of JSON</p>";
echo "<p>3. Check the Laravel logs for the specific error</p>";
?>