<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\FloridaCourseController;

echo "=== TESTING CONTROLLER ENDPOINTS ===\n\n";

try {
    echo "1. Testing CourseController@storeWeb endpoint...\n";
    
    // Create a mock request with the data that would come from the form
    $formData = [
        'title' => 'Test Course via CourseController',
        'description' => 'This is a test course created through the CourseController endpoint',
        'state_code' => 'FL',
        'min_pass_score' => 80,
        'total_duration' => 240,
        'price' => 29.99,
        'certificate_template' => 'standard',
        'is_active' => true,
    ];
    
    echo "   Form data being sent:\n";
    foreach ($formData as $key => $value) {
        echo "     {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
    }
    
    // Create a request object
    $request = new Request();
    $request->merge($formData);
    $request->headers->set('Content-Type', 'application/json');
    $request->headers->set('Accept', 'application/json');
    
    // Instantiate the controller
    $courseController = new CourseController();
    
    try {
        $response = $courseController->storeWeb($request);
        $responseData = $response->getData(true);
        
        if ($response->getStatusCode() === 201) {
            echo "   ✅ CourseController SUCCESS!\n";
            echo "   Response status: {$response->getStatusCode()}\n";
            echo "   Course ID: {$responseData['id']}\n";
            echo "   Course title: {$responseData['title']}\n";
            
            $courseControllerCourseId = $responseData['id'];
            
        } else {
            echo "   ❌ CourseController FAILED!\n";
            echo "   Response status: {$response->getStatusCode()}\n";
            echo "   Response data: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ CourseController EXCEPTION: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    }
    
    echo "\n2. Testing FloridaCourseController@storeWeb endpoint...\n";
    
    // Test the Florida-specific controller
    $floridaFormData = [
        'title' => 'Test Course via FloridaCourseController',
        'description' => 'This is a test course created through the FloridaCourseController endpoint',
        'state_code' => 'FL',
        'min_pass_score' => 85,
        'total_duration' => 300,
        'price' => 39.99,
        'certificate_template' => 'premium',
        'is_active' => true,
        // Optional fields that FloridaCourseController might expect
        'course_type' => 'BDI',
        'delivery_type' => 'Online',
        'dicds_course_id' => 'TEST_FL_' . time(),
    ];
    
    echo "   Florida form data being sent:\n";
    foreach ($floridaFormData as $key => $value) {
        echo "     {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
    }
    
    $floridaRequest = new Request();
    $floridaRequest->merge($floridaFormData);
    $floridaRequest->headers->set('Content-Type', 'application/json');
    $floridaRequest->headers->set('Accept', 'application/json');
    
    $floridaCourseController = new FloridaCourseController();
    
    try {
        $floridaResponse = $floridaCourseController->storeWeb($floridaRequest);
        $floridaResponseData = $floridaResponse->getData(true);
        
        if ($floridaResponse->getStatusCode() === 201) {
            echo "   ✅ FloridaCourseController SUCCESS!\n";
            echo "   Response status: {$floridaResponse->getStatusCode()}\n";
            echo "   Course ID: {$floridaResponseData['id']}\n";
            echo "   Course title: {$floridaResponseData['title']}\n";
            
            $floridaControllerCourseId = $floridaResponseData['id'];
            
        } else {
            echo "   ❌ FloridaCourseController FAILED!\n";
            echo "   Response status: {$floridaResponse->getStatusCode()}\n";
            echo "   Response data: " . json_encode($floridaResponseData, JSON_PRETTY_PRINT) . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ FloridaCourseController EXCEPTION: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    }
    
    echo "\n3. Testing with minimal required data...\n";
    
    // Test with absolute minimum data
    $minimalData = [
        'title' => 'Minimal Test Course',
        'description' => 'Testing with minimal required fields only',
        'state_code' => 'FL',
        'min_pass_score' => 80,
        'total_duration' => 240,
        'price' => 29.99,
    ];
    
    echo "   Minimal data being sent:\n";
    foreach ($minimalData as $key => $value) {
        echo "     {$key}: {$value}\n";
    }
    
    $minimalRequest = new Request();
    $minimalRequest->merge($minimalData);
    $minimalRequest->headers->set('Content-Type', 'application/json');
    $minimalRequest->headers->set('Accept', 'application/json');
    
    try {
        $minimalResponse = $courseController->storeWeb($minimalRequest);
        $minimalResponseData = $minimalResponse->getData(true);
        
        if ($minimalResponse->getStatusCode() === 201) {
            echo "   ✅ Minimal data test SUCCESS!\n";
            echo "   Course ID: {$minimalResponseData['id']}\n";
            
            $minimalCourseId = $minimalResponseData['id'];
            
        } else {
            echo "   ❌ Minimal data test FAILED!\n";
            echo "   Response status: {$minimalResponse->getStatusCode()}\n";
            echo "   Response data: " . json_encode($minimalResponseData, JSON_PRETTY_PRINT) . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Minimal data test EXCEPTION: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    }
    
    echo "\n4. Checking created courses...\n";
    
    $createdCourses = [];
    
    if (isset($courseControllerCourseId)) {
        $course = \App\Models\FloridaCourse::find($courseControllerCourseId);
        if ($course) {
            echo "   ✓ CourseController course exists: ID {$course->id}, Title: '{$course->title}'\n";
            $createdCourses[] = $courseControllerCourseId;
        }
    }
    
    if (isset($floridaControllerCourseId)) {
        $course = \App\Models\FloridaCourse::find($floridaControllerCourseId);
        if ($course) {
            echo "   ✓ FloridaCourseController course exists: ID {$course->id}, Title: '{$course->title}'\n";
            $createdCourses[] = $floridaControllerCourseId;
        }
    }
    
    if (isset($minimalCourseId)) {
        $course = \App\Models\FloridaCourse::find($minimalCourseId);
        if ($course) {
            echo "   ✓ Minimal data course exists: ID {$course->id}, Title: '{$course->title}'\n";
            $createdCourses[] = $minimalCourseId;
        }
    }
    
    echo "\n5. Cleaning up test courses...\n";
    
    foreach ($createdCourses as $courseId) {
        try {
            \App\Models\FloridaCourse::destroy($courseId);
            echo "   ✓ Deleted test course ID: {$courseId}\n";
        } catch (Exception $e) {
            echo "   ⚠️  Could not delete course ID {$courseId}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== CONTROLLER ENDPOINT TEST RESULTS ===\n";
    
    $successCount = 0;
    if (isset($courseControllerCourseId)) $successCount++;
    if (isset($floridaControllerCourseId)) $successCount++;
    if (isset($minimalCourseId)) $successCount++;
    
    if ($successCount > 0) {
        echo "✅ SUCCESS: {$successCount}/3 controller endpoints working!\n";
        echo "✅ Course creation functionality is operational\n";
        echo "\nIf the web interface is still showing 'Error saving course', check:\n";
        echo "- Frontend JavaScript form submission\n";
        echo "- CSRF token handling\n";
        echo "- Route middleware (auth, role permissions)\n";
        echo "- Network requests in browser dev tools\n";
    } else {
        echo "❌ FAILURE: No controller endpoints working\n";
        echo "❌ There are still issues with the course creation logic\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== END OF CONTROLLER TEST ===\n";