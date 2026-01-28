<?php
// Debug 200 Status JSON Error
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Debug 200 Status JSON Error</h1>";
echo "<pre>";

try {
    echo "=== DEBUGGING 200 STATUS JSON ERROR ===\n\n";
    echo "This means the endpoint returns HTTP 200 but HTML content instead of JSON.\n\n";
    
    // Test all possible endpoints that might be causing the issue
    $endpoints = [
        '/web/courses' => function() {
            $controller = new \App\Http\Controllers\CourseController();
            $request = new \Illuminate\Http\Request();
            return $controller->indexWeb($request);
        },
        '/api/florida-courses' => function() {
            $controller = new \App\Http\Controllers\FloridaCourseController();
            return $controller->indexWeb();
        },
        '/api/check-notifications' => function() {
            // This endpoint might not exist, causing a 200 with HTML error page
            try {
                $response = response()->json(['notifications' => []]);
                return $response;
            } catch (Exception $e) {
                return response('Endpoint not found', 404);
            }
        }
    ];
    
    foreach ($endpoints as $endpoint => $callable) {
        echo "Testing {$endpoint}:\n";
        
        try {
            $response = $callable();
            $status = $response->getStatusCode();
            $content = $response->getContent();
            $contentType = $response->headers->get('Content-Type', 'not set');
            
            echo "   Status: {$status}\n";
            echo "   Content-Type: {$contentType}\n";
            
            // Check if it's JSON
            $jsonData = json_decode($content);
            if ($jsonData !== null) {
                echo "   ✅ Valid JSON response\n";
                echo "   Data count: " . (is_array($jsonData) ? count($jsonData) : 'object') . "\n";
            } else {
                echo "   ❌ NOT JSON - returning HTML/text\n";
                echo "   Content preview (first 200 chars):\n";
                echo "   " . substr($content, 0, 200) . "...\n";
                
                // Check if it's a Laravel view/HTML
                if (strpos($content, '<!DOCTYPE') !== false) {
                    echo "   🔍 This is an HTML page (probably a Laravel view)\n";
                } elseif (strpos($content, '<html') !== false) {
                    echo "   🔍 This is HTML content\n";
                } else {
                    echo "   🔍 This is plain text or other content\n";
                }
            }
            
        } catch (Exception $e) {
            echo "   ❌ Exception: " . $e->getMessage() . "\n";
            echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
        
        echo "\n";
    }
    
    // Check for common issues that cause HTML responses with 200 status
    echo "🔍 COMMON CAUSES OF 200 STATUS WITH HTML:\n\n";
    
    // 1. Check if routes are returning views instead of JSON
    echo "1. Checking route definitions...\n";
    
    $routeFile = base_path('routes/web.php');
    if (file_exists($routeFile)) {
        $routeContent = file_get_contents($routeFile);
        
        // Look for routes that might return views instead of JSON
        if (strpos($routeContent, "return view(") !== false) {
            echo "   ⚠️ Found routes returning views - this could cause HTML responses\n";
        }
        
        // Check for specific problematic routes
        $problematicPatterns = [
            '/web/courses.*return view',
            '/api/.*return view',
            'Route::get.*function.*return view'
        ];
        
        foreach ($problematicPatterns as $pattern) {
            if (preg_match("/{$pattern}/", $routeContent)) {
                echo "   ⚠️ Found potentially problematic route pattern: {$pattern}\n";
            }
        }
    }
    
    // 2. Check for middleware that might be interfering
    echo "\n2. Checking middleware issues...\n";
    
    // Test if CSRF middleware is causing issues
    echo "   Testing CSRF token...\n";
    $csrfToken = csrf_token();
    echo "   CSRF token: " . substr($csrfToken, 0, 20) . "...\n";
    
    // 3. Create a simple test route response
    echo "\n3. Creating test responses...\n";
    
    // Test direct JSON response
    $testJson = response()->json(['test' => 'success', 'timestamp' => now()]);
    echo "   Direct JSON response: Status " . $testJson->getStatusCode() . "\n";
    echo "   Content-Type: " . $testJson->headers->get('Content-Type') . "\n";
    
    // Test if the issue is with specific data
    try {
        $courses = \Illuminate\Support\Facades\DB::table('florida_courses')->get();
        $coursesJson = response()->json($courses);
        echo "   Courses JSON response: Status " . $coursesJson->getStatusCode() . "\n";
        echo "   Courses count: " . $courses->count() . "\n";
    } catch (Exception $e) {
        echo "   ❌ Courses JSON error: " . $e->getMessage() . "\n";
    }
    
    echo "\n💡 LIKELY SOLUTIONS:\n";
    echo "1. A route is returning a view() instead of response()->json()\n";
    echo "2. An exception is being caught and returning an error view\n";
    echo "3. Middleware is redirecting to a page instead of returning JSON\n";
    echo "4. The endpoint exists but returns HTML content\n";
    
    echo "\n📝 NEXT STEPS:\n";
    echo "1. In browser Network tab, find the exact URL that's failing\n";
    echo "2. Copy the full response content from the Response tab\n";
    echo "3. Look for Laravel blade template content or error pages\n";
    echo "4. Check if the route is defined to return JSON or a view\n";
    
} catch (Exception $e) {
    echo "❌ DEBUG ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";

echo "<h2>🎯 Action Required</h2>";
echo "<p><strong>Please check the browser Network tab and tell me:</strong></p>";
echo "<ol>";
echo "<li>The exact URL that's returning HTML instead of JSON</li>";
echo "<li>Copy the first few lines of the Response content</li>";
echo "<li>What action triggers this error (loading page, clicking button, etc.)</li>";
echo "</ol>";
?>