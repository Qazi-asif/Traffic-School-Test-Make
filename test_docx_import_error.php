<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a mock request to bootstrap Laravel
$request = Illuminate\Http\Request::create('/', 'GET');
$response = $kernel->handle($request);

echo "=== DOCX Import Error Diagnosis ===\n\n";

// Test 1: Check if ChapterController exists and has importDocx method
echo "1. Testing ChapterController...\n";
try {
    $controller = new \App\Http\Controllers\ChapterController();
    echo "   ✅ ChapterController instantiated successfully\n";
    
    if (method_exists($controller, 'importDocx')) {
        echo "   ✅ importDocx method exists\n";
    } else {
        echo "   ❌ importDocx method missing\n";
    }
    
    if (method_exists($controller, 'storeWeb')) {
        echo "   ✅ storeWeb method exists\n";
    } else {
        echo "   ❌ storeWeb method missing\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Controller error: " . $e->getMessage() . "\n";
}

// Test 2: Check PHPWord
echo "\n2. Testing PHPWord Library...\n";
try {
    if (class_exists('PhpOffice\PhpWord\IOFactory')) {
        echo "   ✅ PHPWord IOFactory available\n";
        
        // Test creating a simple document
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $section->addText('Test document');
        echo "   ✅ PHPWord can create documents\n";
        
    } else {
        echo "   ❌ PHPWord IOFactory not found\n";
    }
} catch (\Exception $e) {
    echo "   ❌ PHPWord error: " . $e->getMessage() . "\n";
}

// Test 3: Check database connection and chapters table
echo "\n3. Testing Database...\n";
try {
    $columns = \DB::getSchemaBuilder()->getColumnListing('chapters');
    echo "   ✅ Database connected, chapters table exists\n";
    echo "   ✅ Columns: " . implode(', ', $columns) . "\n";
    
} catch (\Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

// Test 4: Check storage directory
echo "\n4. Testing Storage...\n";
$storageDir = storage_path('app/public/course-media');
if (file_exists($storageDir)) {
    echo "   ✅ Course media directory exists\n";
    if (is_writable($storageDir)) {
        echo "   ✅ Directory is writable\n";
    } else {
        echo "   ⚠️  Directory is not writable\n";
    }
} else {
    echo "   ⚠️  Course media directory doesn't exist, will be created on upload\n";
}

// Test 5: Simulate DOCX import request
echo "\n5. Testing DOCX Import Route...\n";
try {
    // Check if route exists
    $routes = \Route::getRoutes();
    $docxRoute = null;
    
    foreach ($routes as $route) {
        if ($route->uri() === 'api/import-docx' && in_array('POST', $route->methods())) {
            $docxRoute = $route;
            break;
        }
    }
    
    if ($docxRoute) {
        echo "   ✅ DOCX import route exists: POST /api/import-docx\n";
        echo "   ✅ Route action: " . $docxRoute->getActionName() . "\n";
    } else {
        echo "   ❌ DOCX import route not found\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Route test error: " . $e->getMessage() . "\n";
}

echo "\n=== Potential Issues ===\n";
echo "If you're getting HTTP 500 errors on DOCX import, check:\n";
echo "1. Laravel logs: storage/logs/laravel.log\n";
echo "2. Web server error logs\n";
echo "3. PHP error logs\n";
echo "4. Make sure PHPWord library is properly installed\n";
echo "5. Verify file upload limits in php.ini\n";

$kernel->terminate($request, $response);