<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

echo "=== COMPREHENSIVE COURSE CREATION FIX ===\n\n";

try {
    // Step 1: Verify database structure
    echo "1. Verifying database structure...\n";
    
    if (!Schema::hasTable('florida_courses')) {
        echo "   Creating florida_courses table...\n";
        Schema::create('florida_courses', function ($table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('state', 50)->nullable();
            $table->string('state_code', 10)->nullable(); // Add both fields
            $table->integer('duration')->default(0);
            $table->integer('total_duration')->nullable(); // Add for compatibility
            $table->decimal('price', 8, 2)->default(0);
            $table->integer('passing_score')->default(80);
            $table->integer('min_pass_score')->nullable(); // Add for compatibility
            $table->boolean('is_active')->default(true);
            $table->string('course_type')->nullable();
            $table->string('delivery_type')->nullable();
            $table->string('certificate_type')->nullable();
            $table->string('certificate_template')->nullable(); // Add for compatibility
            $table->string('dicds_course_id')->nullable();
            $table->boolean('copyright_protected')->default(false);
            $table->timestamps();
        });
        echo "   ✓ florida_courses table created\n";
    } else {
        echo "   ✓ florida_courses table exists\n";
        
        // Add missing columns if they don't exist
        $columnsToAdd = [
            'state_code' => 'string',
            'total_duration' => 'integer',
            'min_pass_score' => 'integer',
            'certificate_template' => 'string',
            'delivery_type' => 'string',
            'dicds_course_id' => 'string',
            'copyright_protected' => 'boolean'
        ];
        
        foreach ($columnsToAdd as $column => $type) {
            if (!Schema::hasColumn('florida_courses', $column)) {
                Schema::table('florida_courses', function ($table) use ($column, $type) {
                    switch ($type) {
                        case 'string':
                            $table->string($column)->nullable();
                            break;
                        case 'integer':
                            $table->integer($column)->nullable();
                            break;
                        case 'boolean':
                            $table->boolean($column)->default(false);
                            break;
                    }
                });
                echo "   ✓ Added missing column: {$column}\n";
            }
        }
    }
    
    // Step 2: Test direct course creation
    echo "\n2. Testing direct course creation...\n";
    
    $testCourseData = [
        'title' => 'Comprehensive Fix Test Course',
        'description' => 'Test course created during comprehensive fix',
        'state' => 'FL',
        'state_code' => 'FL',
        'duration' => 240,
        'total_duration' => 240,
        'price' => 29.99,
        'passing_score' => 80,
        'min_pass_score' => 80,
        'is_active' => true,
        'course_type' => 'BDI',
        'delivery_type' => 'Online',
        'dicds_course_id' => 'FIX_TEST_' . time(),
        'certificate_type' => 'standard',
        'certificate_template' => 'standard',
    ];
    
    $courseId = DB::table('florida_courses')->insertGetId($testCourseData);
    echo "   ✓ Direct course creation successful (ID: {$courseId})\n";
    
    // Step 3: Test Eloquent model
    echo "\n3. Testing Eloquent model...\n";
    
    $eloquentCourse = \App\Models\FloridaCourse::create([
        'title' => 'Eloquent Test Course',
        'description' => 'Test course via Eloquent model',
        'state' => 'FL',
        'duration' => 300,
        'price' => 39.99,
        'passing_score' => 85,
        'is_active' => true,
        'course_type' => 'BDI',
        'delivery_type' => 'Online',
        'dicds_course_id' => 'ELOQUENT_' . time(),
    ]);
    
    echo "   ✓ Eloquent model creation successful (ID: {$eloquentCourse->id})\n";
    
    // Step 4: Test controller endpoints
    echo "\n4. Testing controller endpoints...\n";
    
    // Test CourseController@storeWeb
    $request1 = new \Illuminate\Http\Request();
    $request1->merge([
        'title' => 'Controller Test Course 1',
        'description' => 'Test via CourseController',
        'state_code' => 'FL',
        'min_pass_score' => 80,
        'total_duration' => 240,
        'price' => 29.99,
        'is_active' => true,
    ]);
    $request1->headers->set('Accept', 'application/json');
    
    $courseController = new \App\Http\Controllers\CourseController();
    $response1 = $courseController->storeWeb($request1);
    
    if ($response1->getStatusCode() === 201) {
        $data1 = $response1->getData(true);
        echo "   ✓ CourseController@storeWeb successful (ID: {$data1['id']})\n";
        $controllerCourseId1 = $data1['id'];
    } else {
        echo "   ❌ CourseController@storeWeb failed: " . $response1->getContent() . "\n";
    }
    
    // Test FloridaCourseController@storeWeb
    $request2 = new \Illuminate\Http\Request();
    $request2->merge([
        'title' => 'Controller Test Course 2',
        'description' => 'Test via FloridaCourseController',
        'state_code' => 'FL',
        'min_pass_score' => 85,
        'total_duration' => 300,
        'price' => 39.99,
        'is_active' => true,
        'course_type' => 'BDI',
        'delivery_type' => 'Online',
        'dicds_course_id' => 'FLORIDA_CTRL_' . time(),
    ]);
    $request2->headers->set('Accept', 'application/json');
    
    $floridaController = new \App\Http\Controllers\FloridaCourseController();
    $response2 = $floridaController->storeWeb($request2);
    
    if ($response2->getStatusCode() === 201) {
        $data2 = $response2->getData(true);
        echo "   ✓ FloridaCourseController@storeWeb successful (ID: {$data2['id']})\n";
        $controllerCourseId2 = $data2['id'];
    } else {
        echo "   ❌ FloridaCourseController@storeWeb failed: " . $response2->getContent() . "\n";
    }
    
    // Step 5: Verify all created courses
    echo "\n5. Verifying created courses...\n";
    
    $createdCourses = [];
    if (isset($courseId)) $createdCourses[] = $courseId;
    if (isset($eloquentCourse)) $createdCourses[] = $eloquentCourse->id;
    if (isset($controllerCourseId1)) $createdCourses[] = $controllerCourseId1;
    if (isset($controllerCourseId2)) $createdCourses[] = $controllerCourseId2;
    
    foreach ($createdCourses as $id) {
        $course = DB::table('florida_courses')->where('id', $id)->first();
        if ($course) {
            echo "   ✓ Course ID {$id}: '{$course->title}' - State: {$course->state}\n";
        }
    }
    
    // Step 6: Clean up test courses
    echo "\n6. Cleaning up test courses...\n";
    
    foreach ($createdCourses as $id) {
        DB::table('florida_courses')->where('id', $id)->delete();
        echo "   ✓ Deleted test course ID: {$id}\n";
    }
    
    echo "\n=== COMPREHENSIVE FIX RESULTS ===\n";
    echo "✅ Database structure: Fixed and verified\n";
    echo "✅ Direct SQL insertion: Working\n";
    echo "✅ Eloquent model: Working\n";
    
    $controllerResults = 0;
    if (isset($controllerCourseId1)) $controllerResults++;
    if (isset($controllerCourseId2)) $controllerResults++;
    
    echo "✅ Controller endpoints: {$controllerResults}/2 working\n";
    
    if ($controllerResults === 2) {
        echo "\n🎉 ALL SYSTEMS WORKING!\n";
        echo "Course creation should now work in both:\n";
        echo "- Main course management (/create-course)\n";
        echo "- Florida courses admin (/admin/florida-courses)\n";
    } else {
        echo "\n⚠️ PARTIAL SUCCESS\n";
        echo "Database and models work, but some controller endpoints need attention.\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== END OF COMPREHENSIVE FIX ===\n";