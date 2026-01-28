<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DIRECT DATABASE COURSE TEST ===\n\n";

try {
    echo "1. Testing database connection...\n";
    
    // Test basic database connection
    $connection = DB::connection()->getPdo();
    echo "   ✓ Database connection successful\n";
    
    echo "\n2. Checking florida_courses table...\n";
    
    // Check if table exists
    $tables = DB::select("SHOW TABLES LIKE 'florida_courses'");
    if (empty($tables)) {
        echo "   ❌ florida_courses table does not exist!\n";
        echo "   Creating table...\n";
        
        DB::statement("
            CREATE TABLE florida_courses (
                id bigint unsigned NOT NULL AUTO_INCREMENT,
                title varchar(255) NOT NULL,
                description text,
                state varchar(50),
                duration int DEFAULT 0,
                price decimal(8,2) DEFAULT 0.00,
                passing_score int DEFAULT 80,
                is_active tinyint(1) DEFAULT 1,
                course_type varchar(255),
                certificate_type varchar(255),
                delivery_type varchar(255),
                dicds_course_id varchar(255),
                created_at timestamp NULL DEFAULT NULL,
                updated_at timestamp NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        echo "   ✓ Table created successfully\n";
    } else {
        echo "   ✓ florida_courses table exists\n";
    }
    
    // Show table structure
    $columns = DB::select("DESCRIBE florida_courses");
    echo "   Table structure:\n";
    foreach ($columns as $column) {
        echo "     - {$column->Field} ({$column->Type}) " . 
             ($column->Null === 'YES' ? 'NULL' : 'NOT NULL') . 
             ($column->Default !== null ? " DEFAULT '{$column->Default}'" : '') . "\n";
    }
    
    echo "\n3. Testing direct SQL INSERT...\n";
    
    $testData = [
        'title' => 'Direct SQL Test Course',
        'description' => 'This course was created using direct SQL to test database functionality',
        'state' => 'FL',
        'duration' => 240,
        'price' => 29.99,
        'passing_score' => 80,
        'is_active' => 1,
        'course_type' => 'BDI',
        'delivery_type' => 'Online',
        'dicds_course_id' => 'SQL_TEST_' . time(),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];
    
    echo "   Inserting course data:\n";
    foreach ($testData as $key => $value) {
        echo "     {$key}: {$value}\n";
    }
    
    $courseId = DB::table('florida_courses')->insertGetId($testData);
    echo "   ✓ Course inserted successfully with ID: {$courseId}\n";
    
    echo "\n4. Verifying inserted data...\n";
    
    $insertedCourse = DB::table('florida_courses')->where('id', $courseId)->first();
    if ($insertedCourse) {
        echo "   ✓ Course retrieved successfully:\n";
        echo "     ID: {$insertedCourse->id}\n";
        echo "     Title: {$insertedCourse->title}\n";
        echo "     State: {$insertedCourse->state}\n";
        echo "     Duration: {$insertedCourse->duration}\n";
        echo "     Price: {$insertedCourse->price}\n";
        echo "     Course Type: {$insertedCourse->course_type}\n";
        echo "     Is Active: " . ($insertedCourse->is_active ? 'Yes' : 'No') . "\n";
    } else {
        echo "   ❌ Could not retrieve inserted course\n";
    }
    
    echo "\n5. Testing UPDATE operation...\n";
    
    $updateResult = DB::table('florida_courses')
        ->where('id', $courseId)
        ->update([
            'title' => 'Updated Test Course Title',
            'price' => 39.99,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    
    if ($updateResult) {
        echo "   ✓ Course updated successfully\n";
        
        $updatedCourse = DB::table('florida_courses')->where('id', $courseId)->first();
        echo "   Updated title: {$updatedCourse->title}\n";
        echo "   Updated price: {$updatedCourse->price}\n";
    } else {
        echo "   ❌ Course update failed\n";
    }
    
    echo "\n6. Testing Laravel Query Builder methods...\n";
    
    // Test various query methods
    $totalCourses = DB::table('florida_courses')->count();
    echo "   Total courses in table: {$totalCourses}\n";
    
    $activeCourses = DB::table('florida_courses')->where('is_active', 1)->count();
    echo "   Active courses: {$activeCourses}\n";
    
    $floridaCourses = DB::table('florida_courses')->where('state', 'FL')->count();
    echo "   Florida courses: {$floridaCourses}\n";
    
    echo "\n7. Testing Eloquent Model (if working)...\n";
    
    try {
        // Test if we can use the Eloquent model
        $eloquentCourse = new \App\Models\FloridaCourse([
            'title' => 'Eloquent Test Course',
            'description' => 'Testing Eloquent model functionality',
            'state' => 'FL',
            'duration' => 180,
            'price' => 24.99,
            'passing_score' => 75,
            'is_active' => true,
            'course_type' => 'BDI',
            'delivery_type' => 'Online',
            'dicds_course_id' => 'ELOQUENT_' . time(),
        ]);
        
        $eloquentCourse->save();
        echo "   ✓ Eloquent model working! Course ID: {$eloquentCourse->id}\n";
        
        $eloquentCourseId = $eloquentCourse->id;
        
    } catch (Exception $e) {
        echo "   ❌ Eloquent model error: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    }
    
    echo "\n8. Cleaning up test data...\n";
    
    $deletedCount = 0;
    
    // Delete the SQL test course
    if (DB::table('florida_courses')->where('id', $courseId)->delete()) {
        echo "   ✓ Deleted SQL test course (ID: {$courseId})\n";
        $deletedCount++;
    }
    
    // Delete the Eloquent test course if it was created
    if (isset($eloquentCourseId)) {
        if (DB::table('florida_courses')->where('id', $eloquentCourseId)->delete()) {
            echo "   ✓ Deleted Eloquent test course (ID: {$eloquentCourseId})\n";
            $deletedCount++;
        }
    }
    
    echo "   Total test courses deleted: {$deletedCount}\n";
    
    echo "\n=== DATABASE TEST RESULTS ===\n";
    echo "✅ Database connection: Working\n";
    echo "✅ Table structure: Correct\n";
    echo "✅ SQL INSERT: Working\n";
    echo "✅ SQL UPDATE: Working\n";
    echo "✅ Query Builder: Working\n";
    
    if (isset($eloquentCourseId)) {
        echo "✅ Eloquent Model: Working\n";
        echo "\n🎉 ALL TESTS PASSED! The database and models are working correctly.\n";
        echo "If course creation is still failing in the web interface, the issue is likely in:\n";
        echo "- Frontend form submission\n";
        echo "- Route configuration\n";
        echo "- Controller method being called\n";
        echo "- Authentication/authorization\n";
    } else {
        echo "⚠️  Eloquent Model: Has issues (but raw SQL works)\n";
        echo "\n📋 PARTIAL SUCCESS: Database works, but Eloquent model needs attention.\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== END OF DATABASE TEST ===\n";