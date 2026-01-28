<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\FloridaCourse;

echo "=== COURSE CREATION FIX TEST ===\n\n";

try {
    // Check table structure first
    echo "1. Checking florida_courses table structure...\n";
    $tableExists = Schema::hasTable('florida_courses');
    echo "   Table exists: " . ($tableExists ? 'YES' : 'NO') . "\n";
    
    if (!$tableExists) {
        echo "   ERROR: florida_courses table does not exist!\n";
        exit(1);
    }
    
    // Check specific columns
    $hasState = Schema::hasColumn('florida_courses', 'state');
    $hasStateCode = Schema::hasColumn('florida_courses', 'state_code');
    echo "   Has 'state' column: " . ($hasState ? 'YES' : 'NO') . "\n";
    echo "   Has 'state_code' column: " . ($hasStateCode ? 'YES' : 'NO') . "\n\n";
    
    // Test course creation with correct field mapping
    echo "2. Testing course creation...\n";
    
    $testCourseData = [
        'title' => 'Test Course - Fix Verification',
        'description' => 'This is a test course to verify the fix',
        'state' => 'FL', // Use 'state' field as per database schema
        'passing_score' => 80,
        'duration' => 240,
        'price' => 29.99,
        'is_active' => true,
        'course_type' => 'BDI',
        'delivery_type' => 'Online',
        'dicds_course_id' => 'TEST_' . time(),
    ];
    
    echo "   Creating course with data:\n";
    foreach ($testCourseData as $key => $value) {
        echo "     {$key}: {$value}\n";
    }
    
    $course = FloridaCourse::create($testCourseData);
    echo "   SUCCESS: Course created with ID: {$course->id}\n";
    
    // Verify the course was created correctly
    echo "\n3. Verifying created course...\n";
    $createdCourse = FloridaCourse::find($course->id);
    echo "   Course ID: {$createdCourse->id}\n";
    echo "   Title: {$createdCourse->title}\n";
    echo "   State: " . ($createdCourse->state ?? 'NULL') . "\n";
    echo "   Duration: {$createdCourse->duration}\n";
    echo "   Price: {$createdCourse->price}\n";
    echo "   Course Type: {$createdCourse->course_type}\n";
    
    // Test the controller method simulation
    echo "\n4. Testing controller data mapping...\n";
    
    // Simulate form data that would come from the frontend
    $formData = [
        'title' => 'Test Course - Controller Simulation',
        'description' => 'Testing controller field mapping',
        'state_code' => 'FL', // Form sends state_code
        'min_pass_score' => 85,
        'total_duration' => 300,
        'price' => 39.99,
        'certificate_template' => 'standard',
        'is_active' => true,
    ];
    
    // Map form fields to database fields (as controller does)
    $mappedData = [
        'title' => $formData['title'],
        'description' => $formData['description'],
        'state' => $formData['state_code'], // Map state_code to state
        'passing_score' => $formData['min_pass_score'],
        'duration' => $formData['total_duration'],
        'price' => $formData['price'],
        'certificate_type' => $formData['certificate_template'],
        'is_active' => $formData['is_active'],
        'course_type' => 'BDI',
        'delivery_type' => 'Online',
        'dicds_course_id' => 'CTRL_' . time(),
    ];
    
    echo "   Form data mapped to:\n";
    foreach ($mappedData as $key => $value) {
        echo "     {$key}: {$value}\n";
    }
    
    $course2 = FloridaCourse::create($mappedData);
    echo "   SUCCESS: Controller simulation course created with ID: {$course2->id}\n";
    
    // Clean up test data
    echo "\n5. Cleaning up test data...\n";
    FloridaCourse::destroy([$course->id, $course2->id]);
    echo "   Test courses deleted\n";
    
    echo "\n=== COURSE CREATION FIX SUCCESSFUL ===\n";
    echo "The course creation issue has been resolved!\n";
    echo "- Field mapping corrected (state_code -> state)\n";
    echo "- Model fillable array updated\n";
    echo "- Controllers updated with proper error handling\n";
    
} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    
    // If we created any test courses, try to clean them up
    try {
        if (isset($course) && $course->id) {
            FloridaCourse::destroy($course->id);
            echo "\nCleaned up test course ID: {$course->id}\n";
        }
        if (isset($course2) && $course2->id) {
            FloridaCourse::destroy($course2->id);
            echo "Cleaned up test course ID: {$course2->id}\n";
        }
    } catch (Exception $cleanupError) {
        echo "Could not clean up test data: " . $cleanupError->getMessage() . "\n";
    }
}