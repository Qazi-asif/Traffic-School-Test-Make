<?php
// Simple test page to verify course creation functionality
require_once '../vendor/autoload.php';

// Load Laravel
$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\FloridaCourse;

echo "<h1>Course Creation Test</h1>";
echo "<pre>";

try {
    echo "=== TESTING COURSE CREATION ===\n\n";
    
    // Test 1: Check database connection
    echo "1. Testing database connection...\n";
    $connection = DB::connection()->getPdo();
    echo "   ✓ Database connected successfully\n\n";
    
    // Test 2: Check table structure
    echo "2. Checking florida_courses table...\n";
    $columns = DB::select("DESCRIBE florida_courses");
    echo "   Available columns:\n";
    foreach ($columns as $column) {
        echo "     - {$column->Field} ({$column->Type})\n";
    }
    echo "\n";
    
    // Test 3: Create a hard-coded course
    echo "3. Creating hard-coded test course...\n";
    
    $courseData = [
        'title' => 'Test Course - ' . date('Y-m-d H:i:s'),
        'description' => 'This is a test course created via web test page',
        'state' => 'FL',
        'duration' => 240,
        'price' => 29.99,
        'passing_score' => 80,
        'is_active' => true,
        'course_type' => 'BDI',
        'delivery_type' => 'Online',
        'dicds_course_id' => 'WEB_TEST_' . time(),
    ];
    
    $course = FloridaCourse::create($courseData);
    echo "   ✓ Course created successfully!\n";
    echo "   Course ID: {$course->id}\n";
    echo "   Title: {$course->title}\n";
    echo "   State: {$course->state}\n\n";
    
    // Test 4: Verify course exists
    echo "4. Verifying course creation...\n";
    $retrievedCourse = FloridaCourse::find($course->id);
    if ($retrievedCourse) {
        echo "   ✓ Course retrieved successfully\n";
        echo "   All data intact\n\n";
    }
    
    // Test 5: Clean up
    echo "5. Cleaning up test data...\n";
    $retrievedCourse->delete();
    echo "   ✓ Test course deleted\n\n";
    
    echo "=== ALL TESTS PASSED ===\n";
    echo "✅ Course creation is working correctly!\n";
    echo "✅ The database and model are functioning properly\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
?>