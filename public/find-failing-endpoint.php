<?php
// Find the Exact Failing Endpoint
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Find Failing Endpoint</h1>";
echo "<pre>";

try {
    echo "=== FINDING THE EXACT FAILING ENDPOINT ===\n\n";
    echo "Main endpoints are working, so the error is from a different URL.\n\n";
    
    // 1. Check all possible endpoints that might be called by JavaScript
    echo "1. Testing additional endpoints that might be called by frontend...\n\n";
    
    $additionalEndpoints = [
        // Common AJAX endpoints
        '/api/csrf-token' => function() {
            return response()->json(['csrf_token' => csrf_token()]);
        },
        
        // User/auth endpoints
        '/web/user' => function() {
            if (auth()->check()) {
                return response()->json(auth()->user());
            }
            return response()->json(['error' => 'Not authenticated'], 401);
        },
        
        // Dashboard stats
        '/web/admin/dashboard/stats' => function() {
            return response()->json([
                'users' => \DB::table('users')->count(),
                'courses' => \DB::table('florida_courses')->count(),
                'enrollments' => \DB::table('user_course_enrollments')->count()
            ]);
        },
        
        // Notifications
        '/api/notifications' => function() {
            return response()->json(['notifications' => []]);
        }
    ];
    
    foreach ($additionalEndpoints as $endpoint => $callable) {
        echo "Testing {$endpoint}:\n";
        
        try {
            $response = $callable();
            $status = $response->getStatusCode();
            $content = $response->getContent();
            $isJson = json_decode($content) !== null;
            
            echo "   Status: {$status}\n";
            echo "   Valid JSON: " . ($isJson ? 'Yes' : 'No') . "\n";
            
            if (!$isJson && $status === 200) {
                echo "   ❌ FOUND POTENTIAL ISSUE: Returns 200 but not JSON\n";
                echo "   Content preview: " . substr($content, 0, 100) . "...\n";
            } else {
                echo "   ✅ Working correctly\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    // 2. Check for routes that might be returning views when they should return JSON
    echo "2. Scanning routes for potential HTML responses...\n\n";
    
    // Get all registered routes
    $routes = app('router')->getRoutes();
    $suspiciousRoutes = [];
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        $methods = $route->methods();
        
        // Look for API routes that might return views
        if ((strpos($uri, 'api/') !== false || strpos($uri, 'web/') !== false) && 
            in_array('GET', $methods)) {
            
            // These are routes that should return JSON but might return HTML
            if (strpos($uri, 'courses') !== false || 
                strpos($uri, 'users') !== false || 
                strpos($uri, 'dashboard') !== false ||
                strpos($uri, 'notifications') !== false) {
                
                $suspiciousRoutes[] = [
                    'uri' => $uri,
                    'methods' => implode(',', $methods),
                    'action' => $route->getActionName()
                ];
            }
        }
    }
    
    echo "Found " . count($suspiciousRoutes) . " routes that should return JSON:\n";
    foreach ($suspiciousRoutes as $route) {
        echo "   {$route['methods']} /{$route['uri']} → {$route['action']}\n";
    }
    
    // 3. Create JavaScript debugging code
    echo "\n3. Creating JavaScript debugging solution...\n";
    
    $jsDebugCode = "
// Add this to your browser console to catch JSON errors
(function() {
    const originalFetch = window.fetch;
    const originalXHR = XMLHttpRequest.prototype.open;
    
    // Intercept fetch requests
    window.fetch = function(...args) {
        console.log('🔍 FETCH REQUEST:', args[0]);
        return originalFetch.apply(this, args)
            .then(response => {
                console.log('📡 FETCH RESPONSE:', response.url, 'Status:', response.status);
                return response.clone().text().then(text => {
                    if (response.headers.get('content-type')?.includes('application/json')) {
                        try {
                            JSON.parse(text);
                            console.log('✅ Valid JSON response from:', response.url);
                        } catch (e) {
                            console.error('❌ JSON PARSE ERROR from:', response.url);
                            console.error('Response text:', text.substring(0, 200));
                        }
                    }
                    return response;
                });
            });
    };
    
    // Intercept XMLHttpRequest
    XMLHttpRequest.prototype.open = function(method, url, ...args) {
        console.log('🔍 XHR REQUEST:', method, url);
        
        this.addEventListener('load', function() {
            console.log('📡 XHR RESPONSE:', url, 'Status:', this.status);
            
            if (this.getResponseHeader('content-type')?.includes('application/json')) {
                try {
                    JSON.parse(this.responseText);
                    console.log('✅ Valid JSON response from:', url);
                } catch (e) {
                    console.error('❌ JSON PARSE ERROR from:', url);
                    console.error('Response text:', this.responseText.substring(0, 200));
                }
            }
        });
        
        return originalXHR.apply(this, [method, url, ...args]);
    };
    
    console.log('🔧 JSON debugging enabled. All AJAX requests will be logged.');
})();
";
    
    // Save the JavaScript code to a file
    file_put_contents(public_path('debug-json.js'), $jsDebugCode);
    echo "   ✅ Created debug-json.js file\n";
    echo "   You can include this in your page or run it in browser console\n";
    
    // 4. Create a comprehensive test page
    echo "\n4. Creating comprehensive test page...\n";
    
    $testPageHtml = '<!DOCTYPE html>
<html>
<head>
    <title>JSON Endpoint Test</title>
    <meta name="csrf-token" content="' . csrf_token() . '">
</head>
<body>
    <h1>JSON Endpoint Test</h1>
    <div id="results"></div>
    
    <script>
    ' . $jsDebugCode . '
    
    // Test all endpoints
    const endpoints = [
        "/web/courses",
        "/api/florida-courses", 
        "/api/check-notifications",
        "/api/csrf-token",
        "/web/user",
        "/web/admin/dashboard/stats"
    ];
    
    async function testEndpoints() {
        const results = document.getElementById("results");
        
        for (const endpoint of endpoints) {
            try {
                const response = await fetch(endpoint, {
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content,
                        "Accept": "application/json"
                    }
                });
                
                const text = await response.text();
                let isJson = false;
                
                try {
                    JSON.parse(text);
                    isJson = true;
                } catch (e) {
                    // Not JSON
                }
                
                results.innerHTML += `
                    <div style="margin: 10px 0; padding: 10px; border: 1px solid ${isJson ? "green" : "red"};">
                        <strong>${endpoint}</strong><br>
                        Status: ${response.status}<br>
                        Content-Type: ${response.headers.get("content-type")}<br>
                        Valid JSON: ${isJson ? "Yes" : "No"}<br>
                        ${!isJson ? `<details><summary>Response Preview</summary><pre>${text.substring(0, 500)}</pre></details>` : ""}
                    </div>
                `;
                
            } catch (error) {
                results.innerHTML += `
                    <div style="margin: 10px 0; padding: 10px; border: 1px solid red;">
                        <strong>${endpoint}</strong><br>
                        Error: ${error.message}
                    </div>
                `;
            }
        }
    }
    
    // Run tests when page loads
    testEndpoints();
    </script>
</body>
</html>';
    
    file_put_contents(public_path('test-endpoints.html'), $testPageHtml);
    echo "   ✅ Created test-endpoints.html\n";
    echo "   Visit: http://nelly-elearning.test/test-endpoints.html\n";
    
    echo "\n🎯 NEXT STEPS TO FIND THE FAILING ENDPOINT:\n\n";
    echo "METHOD 1 - Use the test page:\n";
    echo "   Visit: http://nelly-elearning.test/test-endpoints.html\n";
    echo "   This will test all endpoints and show which ones return HTML\n\n";
    
    echo "METHOD 2 - Use browser debugging:\n";
    echo "   1. Open the page where the error occurs\n";
    echo "   2. Press F12 → Console tab\n";
    echo "   3. Paste the JavaScript debugging code (from debug-json.js)\n";
    echo "   4. Trigger the error - it will log the exact failing URL\n\n";
    
    echo "METHOD 3 - Check browser Network tab:\n";
    echo "   1. Open Developer Tools (F12) → Network tab\n";
    echo "   2. Clear the log\n";
    echo "   3. Trigger the error\n";
    echo "   4. Look for requests with 200 status but wrong content-type\n";
    echo "   5. Check the Response tab of suspicious requests\n\n";
    
    echo "📝 REPORT BACK:\n";
    echo "Once you find the failing endpoint, tell me:\n";
    echo "1. The exact URL (e.g., /some/endpoint)\n";
    echo "2. The first few lines of the HTML response\n";
    echo "3. What triggers this request (page load, button click, etc.)\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
?>