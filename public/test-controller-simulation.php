<?php
// Test page to simulate controller endpoints
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\FloridaCourseController;

echo "<h1>Controller Endpoint Test</h1>";
echo "<pre>";

try {
    echo "=== TESTING CONTROLLER ENDPOINTS ===\n\n";
    
    // Test CourseController@storeWeb
    echo "1. Testing CourseController@storeWeb...\n";
    
    $formData = [
        'title' => 'Controller Test Course',
        'description' => 'Testing CourseController endpoint',
        'state_code' => 'FL',
        'min_pass_score' => 80,
        'total_duration' => 240,
        'price' => 29.99,
        'is_active' => true,
    ];
    
    $request = new Request();
    $request->merge($formData);
    $request->headers->set('Accept', 'application/json');
    
    $controller = new CourseController();
    $response = $controller->storeWeb($request);
    
    if ($response->getStatusCode() === 201) {
        $data = $response->getData(true);
        echo "   ✅ SUCCESS! Course created with ID: {$data['id']}\n";
        $courseId1 = $data['id'];
    } else {
        echo "   ❌ FAILED! Status: {$response->getStatusCode()}\n";
        echo "   Response: " . $response->getContent() . "\n";
    }
    
    echo "\n2. Testing FloridaCourseController@storeWeb...\n";
    
    $floridaData = [
        'title' => 'Florida Controller Test Course',
        'description' => 'Testing FloridaCourseController endpoint',
        'state_code' => 'FL',
        'min_pass_score' => 85,
        'total_duration' => 300,
        'price' => 39.99,
        'is_active' => true,
    ];
    
    $floridaRequest = new Request();
    $floridaRequest->merge($floridaData);
    $floridaRequest->headers->set('Accept', 'application/json');
    
    $floridaController = new FloridaCourseController();
    $floridaResponse = $floridaController->storeWeb($floridaRequest);
    
    if ($floridaResponse->getStatusCode() === 201) {
        $floridaData = $floridaResponse->getData(true);
        echo "   ✅ SUCCESS! Course created with ID: {$floridaData['id']}\n";
        $courseId2 = $floridaData['id'];
    } else {
        echo "   ❌ FAILED! Status: {$floridaResponse->getStatusCode()}\n";
        echo "   Response: " . $floridaResponse->getContent() . "\n";
    }
    
    // Clean up
    echo "\n3. Cleaning up test courses...\n";
    if (isset($courseId1)) {
        \App\Models\FloridaCourse::destroy($courseId1);
        echo "   ✓ Deleted course ID: {$courseId1}\n";
    }
    if (isset($courseId2)) {
        \App\Models\FloridaCourse::destroy($courseId2);
        echo "   ✓ Deleted course ID: {$courseId2}\n";
    }
    
    echo "\n=== CONTROLLER TESTS COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
?>