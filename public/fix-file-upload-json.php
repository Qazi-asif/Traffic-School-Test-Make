<?php
// Fix File Upload JSON Error
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Fix File Upload JSON Error</h1>";
echo "<pre>";

try {
    echo "=== FIXING FILE UPLOAD JSON ERROR ===\n\n";
    
    // 1. Check file upload endpoints
    echo "1. Checking file upload endpoints...\n";
    
    $uploadEndpoints = [
        '/api/upload-tinymce-image' => 'TinyMCE image upload',
        '/api/import-docx' => 'DOCX file import',
        '/api/import-docx-images' => 'DOCX images import',
        '/api/bulk-upload' => 'Bulk upload (if exists)',
        '/upload' => 'Generic upload (if exists)'
    ];
    
    foreach ($uploadEndpoints as $endpoint => $description) {
        echo "   Checking {$endpoint} ({$description})...\n";
        
        // Check if route exists
        $routes = app('router')->getRoutes();
        $routeExists = false;
        
        foreach ($routes as $route) {
            if ($route->uri() === ltrim($endpoint, '/')) {
                $routeExists = true;
                echo "     ✅ Route exists: " . $route->getActionName() . "\n";
                echo "     Methods: " . implode(', ', $route->methods()) . "\n";
                echo "     Middleware: " . implode(', ', $route->middleware()) . "\n";
                break;
            }
        }
        
        if (!$routeExists) {
            echo "     ⚠️ Route not found\n";
        }
        
        echo "\n";
    }
    
    // 2. Check file upload configuration
    echo "2. Checking PHP file upload configuration...\n";
    
    $uploadMaxFilesize = ini_get('upload_max_filesize');
    $postMaxSize = ini_get('post_max_size');
    $maxExecutionTime = ini_get('max_execution_time');
    $memoryLimit = ini_get('memory_limit');
    
    echo "   upload_max_filesize: {$uploadMaxFilesize}\n";
    echo "   post_max_size: {$postMaxSize}\n";
    echo "   max_execution_time: {$maxExecutionTime} seconds\n";
    echo "   memory_limit: {$memoryLimit}\n";
    
    // Convert to bytes for comparison
    function convertToBytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value)-1]);
        $value = (int) $value;
        switch($last) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        return $value;
    }
    
    $uploadBytes = convertToBytes($uploadMaxFilesize);
    $postBytes = convertToBytes($postMaxSize);
    
    if ($uploadBytes < 10 * 1024 * 1024) { // Less than 10MB
        echo "   ⚠️ upload_max_filesize is quite small ({$uploadMaxFilesize})\n";
    }
    
    if ($postBytes < $uploadBytes) {
        echo "   ❌ post_max_size ({$postMaxSize}) is smaller than upload_max_filesize ({$uploadMaxFilesize})\n";
    }
    
    // 3. Test a simple file upload endpoint
    echo "\n3. Testing file upload endpoints...\n";
    
    // Test TinyMCE image upload endpoint
    try {
        $controller = new \App\Http\Controllers\ChapterController();
        
        // Create a fake uploaded file for testing
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        
        // Simulate the request that would come from file upload
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-CSRF-TOKEN', csrf_token());
        
        echo "   Testing TinyMCE upload endpoint structure...\n";
        
        // Check if the method exists
        if (method_exists($controller, 'uploadTinyMceImage')) {
            echo "     ✅ uploadTinyMceImage method exists\n";
        } else {
            echo "     ❌ uploadTinyMceImage method not found\n";
        }
        
        if (method_exists($controller, 'importDocx')) {
            echo "     ✅ importDocx method exists\n";
        } else {
            echo "     ❌ importDocx method not found\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Controller test error: " . $e->getMessage() . "\n";
    }
    
    // 4. Check for common file upload issues
    echo "\n4. Checking common file upload issues...\n";
    
    // Check storage directory permissions
    $storagePath = storage_path('app/public');
    if (!is_dir($storagePath)) {
        echo "   ❌ Storage directory doesn't exist: {$storagePath}\n";
    } elseif (!is_writable($storagePath)) {
        echo "   ❌ Storage directory not writable: {$storagePath}\n";
    } else {
        echo "   ✅ Storage directory exists and is writable\n";
    }
    
    // Check if storage link exists
    $publicStorageLink = public_path('storage');
    if (!file_exists($publicStorageLink)) {
        echo "   ⚠️ Storage symlink doesn't exist: {$publicStorageLink}\n";
        echo "   Run: php artisan storage:link\n";
    } else {
        echo "   ✅ Storage symlink exists\n";
    }
    
    // 5. Create a test file upload response
    echo "\n5. Creating test file upload response...\n";
    
    try {
        // Simulate successful file upload response
        $successResponse = response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'file_url' => '/storage/uploads/test-file.jpg',
            'file_name' => 'test-file.jpg'
        ]);
        
        echo "   ✅ Success response: " . $successResponse->getContent() . "\n";
        
        // Simulate error response
        $errorResponse = response()->json([
            'success' => false,
            'error' => 'File upload failed',
            'message' => 'Invalid file type or size too large'
        ], 422);
        
        echo "   ✅ Error response: " . $errorResponse->getContent() . "\n";
        
    } catch (Exception $e) {
        echo "   ❌ Response test error: " . $e->getMessage() . "\n";
    }
    
    // 6. Check for CSRF token issues with file uploads
    echo "\n6. Checking CSRF token for file uploads...\n";
    
    $csrfToken = csrf_token();
    echo "   Current CSRF token: " . substr($csrfToken, 0, 20) . "...\n";
    
    // Check if CSRF middleware is applied to upload routes
    $uploadRoutes = ['api/upload-tinymce-image', 'api/import-docx', 'api/import-docx-images'];
    
    foreach ($uploadRoutes as $routeUri) {
        $routes = app('router')->getRoutes();
        foreach ($routes as $route) {
            if ($route->uri() === $routeUri) {
                $middleware = $route->middleware();
                if (in_array('web', $middleware) || in_array('csrf', $middleware)) {
                    echo "   ✅ {$routeUri} has CSRF protection\n";
                } else {
                    echo "   ⚠️ {$routeUri} might not have CSRF protection\n";
                }
                break;
            }
        }
    }
    
    echo "\n🎯 COMMON FILE UPLOAD JSON ERRORS:\n\n";
    echo "1. File too large → PHP returns HTML error page instead of JSON\n";
    echo "2. CSRF token missing → Laravel returns HTML error page\n";
    echo "3. Route not found → 404 HTML page instead of JSON error\n";
    echo "4. Controller exception → HTML error page instead of JSON\n";
    echo "5. Storage permission issue → PHP error as HTML\n";
    
    echo "\n💡 SOLUTIONS:\n\n";
    echo "1. Increase PHP upload limits in php.ini:\n";
    echo "   upload_max_filesize = 50M\n";
    echo "   post_max_size = 50M\n";
    echo "   max_execution_time = 300\n";
    echo "   memory_limit = 256M\n\n";
    
    echo "2. Ensure CSRF token is sent with file uploads:\n";
    echo "   Add X-CSRF-TOKEN header to AJAX requests\n\n";
    
    echo "3. Make sure upload endpoints return JSON:\n";
    echo "   return response()->json(['success' => true]);\n";
    echo "   NOT: return redirect() or return view()\n\n";
    
    echo "4. Handle errors properly:\n";
    echo "   try { ... } catch (Exception \$e) {\n";
    echo "       return response()->json(['error' => \$e->getMessage()], 500);\n";
    echo "   }\n";
    
    echo "\n📝 NEXT STEPS:\n";
    echo "1. Tell me which specific file upload is failing:\n";
    echo "   - Image upload in TinyMCE editor?\n";
    echo "   - DOCX file import?\n";
    echo "   - Bulk file upload?\n";
    echo "   - Other file upload?\n\n";
    
    echo "2. What type of file are you uploading?\n";
    echo "3. How large is the file?\n";
    echo "4. What happens when you try to upload?\n";
    
} catch (Exception $e) {
    echo "❌ FIX ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
?>