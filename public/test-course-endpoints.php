<?php
// Test Course Endpoints
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>Test Course Endpoints</h1>";
echo "<pre>";

try {
    echo "=== TESTING COURSE ENDPOINTS ===\n\n";
    
    // 1. Test database connection
    echo "1. Testing database connection...\n";
    $connection = DB::connection()->getPdo();
    echo "   ✅ Database connection works\n";
    
    // 2. Test florida_courses table
    echo "\n2. Testing florida_courses table...\n";
    $courseCount = DB::table('florida_courses')->count();
    echo "   ✅ Found {$courseCount} courses in florida_courses table\n";
    
    // 3. Test CourseController queryAllStateCourses method
    echo "\n3. Testing CourseController queryAllStateCourses method...\n";
    
    $controller = new \App\Http\Controllers\CourseController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('queryAllStateCourses');
    $method->setAccessible(true);
    
    $request = new \Illuminate\Http\Request();
    $result = $method->invoke($controller, $request);
    
    echo "   ✅ queryAllStateCourses works! Found " . $result->count() . " total courses\n";
    
    // 4. Test CourseController indexWeb method
    echo "\n4. Testing CourseController indexWeb method...\n";
    
    $response = $controller->indexWeb($request);
    $statusCode = $response->getStatusCode();
    
    if ($statusCode === 200) {
        $data = json_decode($response->getContent(), true);
        echo "   ✅ indexWeb works! Status: {$statusCode}, Courses: " . count($data) . "\n";
    } else {
        echo "   ❌ indexWeb failed! Status: {$statusCode}\n";
        echo "   Response: " . $response->getContent() . "\n";
    }
    
    // 5. Test FloridaCourseController indexWeb method
    echo "\n5. Testing FloridaCourseController indexWeb method...\n";
    
    $floridaController = new \App\Http\Controllers\FloridaCourseController();
    $response = $floridaController->indexWeb();
    $statusCode = $response->getStatusCode();
    
    if ($statusCode === 200) {
        $data = json_decode($response->getContent(), true);
        echo "   ✅ FloridaCourseController indexWeb works! Status: {$statusCode}, Courses: " . count($data) . "\n";
    } else {
        echo "   ❌ FloridaCourseController indexWeb failed! Status: {$statusCode}\n";
        echo "   Response: " . $response->getContent() . "\n";
    }
    
    // 6. Test course creation with FloridaCourseController
    echo "\n6. Testing course creation with FloridaCourseController...\n";
    
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'title' => 'Endpoint Test Course',
        'description' => 'Testing course creation endpoint',
        'state_code' => 'FL',
        'total_duration' => 240,
        'min_pass_score' => 80,
        'price' => 29.99,
        'is_active' => true
    ]);
    
    $response = $floridaController->storeWeb($request);
    $statusCode = $response->getStatusCode();
    
    if ($statusCode === 201) {
        $data = json_decode($response->getContent(), true);
        echo "   ✅ Course creation works! Status: {$statusCode}, Course ID: " . $data['id'] . "\n";
        
        // Clean up test course
        DB::table('florida_courses')->where('id', $data['id'])->delete();
        echo "   ✅ Test course cleaned up\n";
    } else {
        echo "   ❌ Course creation failed! Status: {$statusCode}\n";
        echo "   Response: " . $response->getContent() . "\n";
    }
    
    echo "\n🎉 ENDPOINT TESTING COMPLETE!\n";
    
} catch (Exception $e) {
    echo "❌ ENDPOINT TEST ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>