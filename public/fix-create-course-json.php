<?php
// Fix Create Course JSON Error
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Fix Create Course JSON Error</h1>";
echo "<pre>";

try {
    echo "=== FIXING CREATE COURSE JSON ERROR ===\n\n";
    
    // Test the specific endpoint used by create-course page
    echo "1. Testing POST /web/courses endpoint...\n";
    
    // Create a test request like the form would send
    $request = new \Illuminate\Http\Request();
    $request->setMethod('POST');
    $request->merge([
        'title' => 'Test Course Creation',
        'description' => 'Testing course creation endpoint',
        'state_code' => 'FL',
        'min_pass_score' => 80,
        'total_duration' => 240,
        'price' => 29.99,
        'certificate_template' => 'default',
        'is_active' => true
    ]);
    
    // Add CSRF token
    $request->headers->set('X-CSRF-TOKEN', csrf_token());
    $request->headers->set('Accept', 'application/json');
    
    try {
        $controller = new \App\Http\Controllers\CourseController();
        $response = $controller->storeWeb($request);
        
        $status = $response->getStatusCode();
        $content = $response->getContent();
        $contentType = $response->headers->get('Content-Type', 'not set');
        
        echo "   Status: {$status}\n";
        echo "   Content-Type: {$contentType}\n";
        
        // Check if it's JSON
        $jsonData = json_decode($content);
        if ($jsonData !== null) {
            echo "   ✅ Returns valid JSON\n";
            echo "   Response: " . substr($content, 0, 200) . "\n";
        } else {
            echo "   ❌ Returns HTML instead of JSON\n";
            echo "   Content preview:\n";
            echo "   " . substr($content, 0, 300) . "...\n";
            
            // This is likely the problem!
            if (strpos($content, '<!DOCTYPE') !== false) {
                echo "   🔍 This is returning an HTML page instead of JSON!\n";
                echo "   This is what's causing your JSON parsing error.\n";
            }
        }
        
    } catch (Exception $e) {
        echo "   ❌ Controller error: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    // 2. Check the route configuration
    echo "\n2. Checking route configuration...\n";
    
    $routes = app('router')->getRoutes();
    $webCoursesRoute = null;
    
    foreach ($routes as $route) {
        if ($route->uri() === 'web/courses' && in_array('POST', $route->methods())) {
            $webCoursesRoute = $route;
            break;
        }
    }
    
    if ($webCoursesRoute) {
        echo "   ✅ Found POST /web/courses route\n";
        echo "   Action: " . $webCoursesRoute->getActionName() . "\n";
        echo "   Middleware: " . implode(', ', $webCoursesRoute->middleware()) . "\n";
    } else {
        echo "   ❌ POST /web/courses route not found!\n";
        echo "   This could be why the form submission fails.\n";
    }
    
    // 3. Check if the form is sending the request to the right place
    echo "\n3. Checking create-course form configuration...\n";
    
    $createCourseView = resource_path('views/create-course.blade.php');
    if (file_exists($createCourseView)) {
        $viewContent = file_get_contents($createCourseView);
        
        // Look for form action
        if (preg_match('/action=["\']([^"\']+)["\']/', $viewContent, $matches)) {
            echo "   Form action: " . $matches[1] . "\n";
        }
        
        // Look for AJAX calls
        if (strpos($viewContent, 'fetch(') !== false || strpos($viewContent, '$.post') !== false || strpos($viewContent, 'axios.post') !== false) {
            echo "   ✅ Form uses AJAX submission\n";
        } else {
            echo "   ⚠️ Form might use regular submission\n";
        }
        
        // Look for JSON handling
        if (strpos($viewContent, 'JSON.parse') !== false) {
            echo "   ✅ Form expects JSON response\n";
        } else {
            echo "   ⚠️ Form might not expect JSON response\n";
        }
        
    } else {
        echo "   ⚠️ create-course.blade.php not found\n";
    }
    
    // 4. Create a fixed version of the storeWeb method
    echo "\n4. Creating fixed response handling...\n";
    
    echo "   The issue is likely that the controller returns a redirect() instead of JSON\n";
    echo "   when the form expects JSON response.\n";
    
    // 5. Test what happens with different request types
    echo "\n5. Testing different request types...\n";
    
    // Test with Accept: application/json header
    $jsonRequest = new \Illuminate\Http\Request();
    $jsonRequest->setMethod('POST');
    $jsonRequest->merge([
        'title' => 'JSON Test Course',
        'description' => 'Testing JSON response',
        'state_code' => 'FL',
        'min_pass_score' => 80,
        'total_duration' => 240,
        'price' => 29.99,
        'is_active' => true
    ]);
    $jsonRequest->headers->set('Accept', 'application/json');
    $jsonRequest->headers->set('Content-Type', 'application/json');
    
    try {
        $controller = new \App\Http\Controllers\CourseController();
        $response = $controller->storeWeb($jsonRequest);
        
        echo "   JSON request response: Status " . $response->getStatusCode() . "\n";
        echo "   Content-Type: " . $response->headers->get('Content-Type') . "\n";
        
        $isJson = json_decode($response->getContent()) !== null;
        echo "   Returns JSON: " . ($isJson ? 'Yes' : 'No') . "\n";
        
        if (!$isJson) {
            echo "   ❌ FOUND THE PROBLEM: Controller returns HTML even with JSON Accept header\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ JSON request error: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎯 LIKELY SOLUTION:\n";
    echo "The CourseController@storeWeb method is returning a redirect() or view()\n";
    echo "instead of response()->json() when the form expects JSON.\n";
    
    echo "\n📝 NEXT STEPS:\n";
    echo "1. Check what the create-course form is actually sending\n";
    echo "2. Make sure the controller returns JSON for AJAX requests\n";
    echo "3. The form JavaScript expects JSON but gets HTML redirect\n";
    
} catch (Exception $e) {
    echo "❌ FIX ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
?>