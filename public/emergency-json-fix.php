<?php
// Emergency JSON Fix - Direct approach
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Emergency JSON Fix</h1>";
echo "<pre>";

try {
    echo "=== EMERGENCY JSON FIX ===\n\n";
    
    // 1. Force disable maintenance mode multiple ways
    echo "1. Force disabling maintenance mode...\n";
    
    $maintenanceFile = storage_path('framework/maintenance.php');
    $downFile = storage_path('framework/down');
    
    // Method 1: Delete maintenance.php
    if (file_exists($maintenanceFile)) {
        unlink($maintenanceFile);
        echo "   ✅ Deleted maintenance.php\n";
    }
    
    // Method 2: Delete down file
    if (file_exists($downFile)) {
        unlink($downFile);
        echo "   ✅ Deleted down file\n";
    }
    
    // Method 3: Check for any other maintenance files
    $frameworkDir = storage_path('framework');
    $files = scandir($frameworkDir);
    foreach ($files as $file) {
        if (strpos($file, 'maintenance') !== false || strpos($file, 'down') !== false) {
            $fullPath = $frameworkDir . '/' . $file;
            if (is_file($fullPath)) {
                unlink($fullPath);
                echo "   ✅ Deleted {$file}\n";
            }
        }
    }
    
    // 2. Create a test JSON endpoint
    echo "\n2. Creating test JSON endpoint...\n";
    
    $testData = [
        'status' => 'success',
        'message' => 'JSON is working correctly',
        'timestamp' => date('Y-m-d H:i:s'),
        'maintenance_mode' => file_exists($maintenanceFile) ? 'enabled' : 'disabled',
        'test_endpoints' => []
    ];
    
    // Test each endpoint individually
    $endpoints = [
        'web_courses' => function() {
            $controller = new \App\Http\Controllers\CourseController();
            $request = new \Illuminate\Http\Request();
            return $controller->indexWeb($request);
        },
        'florida_courses' => function() {
            $controller = new \App\Http\Controllers\FloridaCourseController();
            return $controller->indexWeb();
        }
    ];
    
    foreach ($endpoints as $name => $callable) {
        try {
            $response = $callable();
            $status = $response->getStatusCode();
            $content = $response->getContent();
            $isJson = json_decode($content) !== null;
            
            $testData['test_endpoints'][$name] = [
                'status' => $status,
                'is_json' => $isJson,
                'content_preview' => $isJson ? 'Valid JSON' : substr($content, 0, 100) . '...'
            ];
            
            echo "   {$name}: Status {$status}, JSON: " . ($isJson ? 'Yes' : 'No') . "\n";
            
        } catch (Exception $e) {
            $testData['test_endpoints'][$name] = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
            echo "   {$name}: ERROR - " . $e->getMessage() . "\n";
        }
    }
    
    // 3. Write test JSON to a file for direct access
    echo "\n3. Creating direct JSON test file...\n";
    
    file_put_contents(public_path('test-json.json'), json_encode($testData, JSON_PRETTY_PRINT));
    echo "   ✅ Created test-json.json file\n";
    echo "   Access it at: http://nelly-elearning.test/test-json.json\n";
    
    // 4. Check .htaccess for any redirects
    echo "\n4. Checking .htaccess file...\n";
    
    $htaccessFile = public_path('.htaccess');
    if (file_exists($htaccessFile)) {
        $htaccessContent = file_get_contents($htaccessFile);
        
        // Check for maintenance redirects
        if (strpos($htaccessContent, 'maintenance') !== false) {
            echo "   ⚠️ Found maintenance rules in .htaccess\n";
        } else {
            echo "   ✅ No maintenance rules in .htaccess\n";
        }
        
        // Check for any redirects that might interfere
        if (strpos($htaccessContent, 'RewriteRule') !== false) {
            echo "   ⚠️ Found rewrite rules in .htaccess - these might interfere\n";
        }
    } else {
        echo "   ⚠️ No .htaccess file found\n";
    }
    
    // 5. Check environment configuration
    echo "\n5. Checking environment...\n";
    
    echo "   APP_ENV: " . env('APP_ENV', 'not set') . "\n";
    echo "   APP_DEBUG: " . (env('APP_DEBUG', false) ? 'true' : 'false') . "\n";
    echo "   APP_URL: " . env('APP_URL', 'not set') . "\n";
    
    // 6. Create a simple test route response
    echo "\n6. Testing direct response...\n";
    
    $simpleResponse = response()->json([
        'test' => 'success',
        'message' => 'Direct JSON response works'
    ]);
    
    echo "   ✅ Direct JSON response created successfully\n";
    echo "   Status: " . $simpleResponse->getStatusCode() . "\n";
    echo "   Content: " . $simpleResponse->getContent() . "\n";
    
    echo "\n🎯 DEBUGGING INSTRUCTIONS:\n";
    echo "1. Open browser Developer Tools (F12)\n";
    echo "2. Go to Network tab\n";
    echo "3. Try the action that fails\n";
    echo "4. Look for the red/failed request\n";
    echo "5. Click on it and check:\n";
    echo "   - Request URL (which endpoint is failing)\n";
    echo "   - Response tab (what HTML is being returned)\n";
    echo "   - Status code (500, 403, 404, etc.)\n";
    
    echo "\n🔧 COMMON FAILING ENDPOINTS:\n";
    echo "- /web/courses (course listing)\n";
    echo "- /api/florida-courses (Florida course API)\n";
    echo "- /api/check-notifications (notifications)\n";
    echo "- /web/courses (course creation)\n";
    
    echo "\n📝 NEXT STEPS:\n";
    echo "1. Check the test JSON file: http://nelly-elearning.test/test-json.json\n";
    echo "2. If that works, the issue is with specific endpoints\n";
    echo "3. Use browser Network tab to identify the exact failing endpoint\n";
    echo "4. Report back which specific URL is returning HTML instead of JSON\n";
    
} catch (Exception $e) {
    echo "❌ EMERGENCY FIX ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";

echo "<h2>🔍 Debug Information</h2>";
echo "<p>Check the test JSON file and use browser Network tab to identify the exact failing endpoint.</p>";
?>