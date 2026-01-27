<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Finding Florida 4-Hour BDI Course Enrollment ===\n";
    
    // Find the Florida 4-Hour BDI Course
    $floridaCourse = \DB::table('florida_courses')
        ->where('title', 'LIKE', '%4%Hour%')
        ->orWhere('title', 'LIKE', '%4-Hour%')
        ->orWhere('title', 'LIKE', '%BDI%')
        ->first();
    
    if (!$floridaCourse) {
        echo "❌ Florida 4-Hour BDI Course not found\n";
        
        // List all Florida courses
        echo "\n=== All Florida Courses ===\n";
        $allCourses = \DB::table('florida_courses')->get();
        foreach ($allCourses as $course) {
            echo "- ID: {$course->id}, Title: {$course->title}\n";
        }
        exit;
    }
    
    echo "✅ Found Florida Course:\n";
    echo "- ID: {$floridaCourse->id}\n";
    echo "- Title: {$floridaCourse->title}\n";
    
    // Find enrollments for this course
    $enrollments = \DB::table('user_course_enrollments')
        ->where('course_id', $floridaCourse->id)
        ->where('course_table', 'florida_courses')
        ->get();
    
    if ($enrollments->isEmpty()) {
        echo "\n❌ No enrollments found for this course\n";
        echo "Creating a test enrollment...\n";
        
        // Get the first admin user
        $adminUser = \DB::table('users')
            ->where('role_id', 1) // Assuming role_id 1 is admin
            ->orWhere('email', 'LIKE', '%admin%')
            ->first();
        
        if (!$adminUser) {
            $adminUser = \DB::table('users')->first(); // Fallback to first user
        }
        
        if ($adminUser) {
            $enrollmentId = \DB::table('user_course_enrollments')->insertGetId([
                'user_id' => $adminUser->id,
                'course_id' => $floridaCourse->id,
                'course_table' => 'florida_courses',
                'payment_status' => 'paid',
                'amount_paid' => $floridaCourse->price ?? 29.99,
                'status' => 'active',
                'enrolled_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            echo "✅ Created test enrollment with ID: {$enrollmentId}\n";
            echo "You can now access: /course-player/{$enrollmentId}\n";
        } else {
            echo "❌ No users found to create enrollment\n";
        }
    } else {
        echo "\n✅ Found {$enrollments->count()} enrollments:\n";
        foreach ($enrollments as $enrollment) {
            echo "- Enrollment ID: {$enrollment->id}\n";
            echo "  User ID: {$enrollment->user_id}\n";
            echo "  Status: {$enrollment->status}\n";
            echo "  Payment Status: {$enrollment->payment_status}\n";
            echo "  URL: /course-player/{$enrollment->id}\n";
            echo "\n";
        }
    }
    
    // Check chapters
    echo "=== Checking Chapters ===\n";
    $chapters = \DB::table('chapters')
        ->where('course_id', $floridaCourse->id)
        ->where('course_table', 'florida_courses')
        ->orderBy('order_index')
        ->get();
    
    if ($chapters->isEmpty()) {
        echo "❌ No chapters found with course_table filter\n";
        
        // Check without filter
        $chaptersNoFilter = \DB::table('chapters')
            ->where('course_id', $floridaCourse->id)
            ->orderBy('order_index')
            ->get();
        
        if (!$chaptersNoFilter->isEmpty()) {
            echo "⚠️  Found {$chaptersNoFilter->count()} chapters without course_table filter\n";
            echo "Updating chapters to have correct course_table...\n";
            
            \DB::table('chapters')
                ->where('course_id', $floridaCourse->id)
                ->whereNull('course_table')
                ->update(['course_table' => 'florida_courses']);
            
            echo "✅ Updated chapters with course_table = 'florida_courses'\n";
        } else {
            echo "❌ No chapters found at all for this course\n";
        }
    } else {
        echo "✅ Found {$chapters->count()} chapters with correct course_table\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}