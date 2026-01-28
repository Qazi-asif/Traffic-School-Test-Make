<?php
// Fix DOCX Import JSON Error
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Fix DOCX Import JSON Error</h1>";
echo "<pre>";

try {
    echo "=== FIXING DOCX IMPORT JSON ERROR ===\n\n";
    
    // 1. Check if the DOCX import route exists
    echo "1. Checking DOCX import route...\n";
    
    $routes = app('router')->getRoutes();
    $docxRouteFound = false;
    
    foreach ($routes as $route) {
        if ($route->uri() === 'api/import-docx' && in_array('POST', $route->methods())) {
            $docxRouteFound = true;
            echo "   ✅ Found POST /api/import-docx route\n";
            echo "   Action: " . $route->getActionName() . "\n";
            echo "   Middleware: " . implode(', ', $route->middleware()) . "\n";
            break;
        }
    }
    
    if (!$docxRouteFound) {
        echo "   ❌ POST /api/import-docx route not found!\n";
        echo "   This could be why the DOCX import fails.\n";
    }
    
    // 2. Check if ChapterController has the importDocx method
    echo "\n2. Checking ChapterController importDocx method...\n";
    
    $controller = new \App\Http\Controllers\ChapterController();
    
    if (method_exists($controller, 'importDocx')) {
        echo "   ✅ importDocx method exists in ChapterController\n";
    } else {
        echo "   ❌ importDocx method not found in ChapterController\n";
    }
    
    // 3. Check PHP extensions required for DOCX processing
    echo "\n3. Checking required PHP extensions...\n";
    
    $requiredExtensions = [
        'zip' => 'Required for reading DOCX files',
        'xml' => 'Required for parsing DOCX content',
        'dom' => 'Required for XML processing',
        'libxml' => 'Required for XML parsing',
        'gd' => 'Required for image processing'
    ];
    
    foreach ($requiredExtensions as $ext => $description) {
        if (extension_loaded($ext)) {
            echo "   ✅ {$ext} extension loaded - {$description}\n";
        } else {
            echo "   ❌ {$ext} extension missing - {$description}\n";
        }
    }
    
    // 4. Check if PHPWord is installed
    echo "\n4. Checking PHPWord installation...\n";
    
    if (class_exists('\PhpOffice\PhpWord\IOFactory')) {
        echo "   ✅ PHPWord is installed and available\n";
    } else {
        echo "   ❌ PHPWord not found - required for DOCX processing\n";
        echo "   Install with: composer require phpoffice/phpword\n";
    }
    
    // 5. Check storage directory for course media
    echo "\n5. Checking storage directories...\n";
    
    $courseMediaPath = storage_path('app/public/course-media');
    
    if (!is_dir($courseMediaPath)) {
        echo "   ⚠️ Course media directory doesn't exist: {$courseMediaPath}\n";
        echo "   Creating directory...\n";
        mkdir($courseMediaPath, 0755, true);
        echo "   ✅ Created course media directory\n";
    } else {
        echo "   ✅ Course media directory exists\n";
    }
    
    if (!is_writable($courseMediaPath)) {
        echo "   ❌ Course media directory not writable: {$courseMediaPath}\n";
    } else {
        echo "   ✅ Course media directory is writable\n";
    }
    
    // 6. Test DOCX import validation
    echo "\n6. Testing DOCX import validation...\n";
    
    try {
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-CSRF-TOKEN', csrf_token());
        
        // Test without file (should return validation error as JSON)
        $response = $controller->importDocx($request);
        $status = $response->getStatusCode();
        $content = $response->getContent();
        $isJson = json_decode($content) !== null;
        
        echo "   Validation test (no file): Status {$status}, JSON: " . ($isJson ? 'Yes' : 'No') . "\n";
        
        if (!$isJson && $status !== 200) {
            echo "   ❌ FOUND ISSUE: Validation errors return HTML instead of JSON\n";
            echo "   Content preview: " . substr($content, 0, 200) . "...\n";
        }
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        echo "   ✅ Validation exception thrown correctly\n";
    } catch (Exception $e) {
        echo "   ❌ Unexpected error: " . $e->getMessage() . "\n";
    }
    
    // 7. Check file upload limits for DOCX files
    echo "\n7. Checking file upload limits...\n";
    
    $uploadMaxFilesize = ini_get('upload_max_filesize');
    $postMaxSize = ini_get('post_max_size');
    
    echo "   upload_max_filesize: {$uploadMaxFilesize}\n";
    echo "   post_max_size: {$postMaxSize}\n";
    
    // Convert to bytes
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
    $maxSizeInMB = $uploadBytes / (1024 * 1024);
    
    if ($maxSizeInMB < 10) {
        echo "   ⚠️ Upload limit is quite small ({$uploadMaxFilesize}) for DOCX files\n";
        echo "   Consider increasing to at least 10M\n";
    } else {
        echo "   ✅ Upload limit should be sufficient for DOCX files\n";
    }
    
    // 8. Create a test response to verify JSON format
    echo "\n8. Testing JSON response format...\n";
    
    $testSuccessResponse = response()->json([
        'success' => true,
        'html' => '<p>Test HTML content from DOCX</p>',
        'images_imported' => 2,
        'has_unsupported_images' => false
    ]);
    
    $testErrorResponse = response()->json([
        'error' => 'Failed to import DOCX: Test error message'
    ], 422);
    
    echo "   ✅ Success response format: " . $testSuccessResponse->getContent() . "\n";
    echo "   ✅ Error response format: " . $testErrorResponse->getContent() . "\n";
    
    // 9. Check for common DOCX import issues
    echo "\n9. Common DOCX import issues...\n";
    
    echo "   Issues that can cause JSON errors:\n";
    echo "   - File too large → PHP timeout → HTML error page\n";
    echo "   - Invalid DOCX file → Exception → HTML error page\n";
    echo "   - Missing PHPWord → Fatal error → HTML error page\n";
    echo "   - Storage permission issue → Exception → HTML error page\n";
    echo "   - CSRF token missing → Laravel error page (HTML)\n";
    
    // 10. Create a simple DOCX import test
    echo "\n10. Creating DOCX import test endpoint...\n";
    
    $testEndpointCode = '<?php
// Simple DOCX Import Test
header("Content-Type: application/json");

try {
    if (!isset($_FILES["file"])) {
        echo json_encode(["error" => "No file uploaded"]);
        exit;
    }
    
    $file = $_FILES["file"];
    
    if ($file["error"] !== UPLOAD_ERR_OK) {
        echo json_encode(["error" => "File upload error: " . $file["error"]]);
        exit;
    }
    
    if (pathinfo($file["name"], PATHINFO_EXTENSION) !== "docx") {
        echo json_encode(["error" => "File must be a DOCX file"]);
        exit;
    }
    
    // Basic success response
    echo json_encode([
        "success" => true,
        "message" => "DOCX file received successfully",
        "filename" => $file["name"],
        "size" => $file["size"]
    ]);
    
} catch (Exception $e) {
    echo json_encode(["error" => "Test error: " . $e->getMessage()]);
}
?>';
    
    file_put_contents(public_path('test-docx-upload.php'), $testEndpointCode);
    echo "   ✅ Created test endpoint: http://nelly-elearning.test/test-docx-upload.php\n";
    
    echo "\n🎯 LIKELY CAUSES OF DOCX IMPORT JSON ERROR:\n\n";
    echo "1. Route not found → 404 HTML page instead of JSON error\n";
    echo "2. File too large → PHP timeout → HTML error page\n";
    echo "3. Validation error → HTML error page instead of JSON\n";
    echo "4. PHPWord exception → HTML error page instead of JSON\n";
    echo "5. CSRF token missing → Laravel HTML error page\n";
    
    echo "\n💡 SOLUTIONS:\n\n";
    echo "1. Ensure route exists: POST /api/import-docx\n";
    echo "2. Increase PHP limits for large DOCX files\n";
    echo "3. Make sure all errors return JSON responses\n";
    echo "4. Include CSRF token in upload request\n";
    echo "5. Handle PHPWord exceptions properly\n";
    
    echo "\n📝 NEXT STEPS:\n";
    echo "1. Try uploading a small DOCX file (< 1MB)\n";
    echo "2. Check browser Network tab for the exact error\n";
    echo "3. Look at the Response tab to see HTML vs JSON\n";
    echo "4. Test with: http://nelly-elearning.test/test-docx-upload.php\n";
    
} catch (Exception $e) {
    echo "❌ FIX ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
?>