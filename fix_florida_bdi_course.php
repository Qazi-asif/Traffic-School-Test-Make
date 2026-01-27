<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Fixing Florida 4-Hour BDI Course Issues ===\n";
    
    // Step 1: Clear all caches
    echo "\n1. Clearing caches...\n";
    \Artisan::call('cache:clear');
    \Artisan::call('config:clear');
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');
    \Cache::flush();
    echo "✅ All caches cleared\n";
    
    // Step 2: Find the Florida 4-Hour BDI Course
    echo "\n2. Finding Florida 4-Hour BDI Course...\n";
    $floridaCourse = \DB::table('florida_courses')
        ->where('title', 'LIKE', '%4%Hour%')
        ->orWhere('title', 'LIKE', '%4-Hour%')
        ->orWhere('title', 'LIKE', '%BDI%')
        ->first();
    
    if (!$floridaCourse) {
        echo "❌ Course not found. Available Florida courses:\n";
        $allCourses = \DB::table('florida_courses')->get();
        foreach ($allCourses as $course) {
            echo "- ID: {$course->id}, Title: {$course->title}\n";
        }
        exit;
    }
    
    echo "✅ Found course: {$floridaCourse->title} (ID: {$floridaCourse->id})\n";
    
    // Step 3: Fix chapters course_table
    echo "\n3. Fixing chapters course_table...\n";
    $chaptersFixed = \DB::table('chapters')
        ->where('course_id', $floridaCourse->id)
        ->where(function($query) {
            $query->whereNull('course_table')
                  ->orWhere('course_table', '!=', 'florida_courses');
        })
        ->update(['course_table' => 'florida_courses']);
    
    if ($chaptersFixed > 0) {
        echo "✅ Fixed {$chaptersFixed} chapters\n";
    } else {
        echo "✅ Chapters already have correct course_table\n";
    }
    
    // Step 4: Check/create enrollment
    echo "\n4. Checking enrollments...\n";
    $enrollments = \DB::table('user_course_enrollments')
        ->where('course_id', $floridaCourse->id)
        ->where('course_table', 'florida_courses')
        ->get();
    
    if ($enrollments->isEmpty()) {
        echo "⚠️  No enrollments found. Creating test enrollment...\n";
        
        // Get admin user
        $adminUser = \DB::table('users')
            ->where('role_id', 1)
            ->orWhere('email', 'LIKE', '%admin%')
            ->first();
        
        if (!$adminUser) {
            $adminUser = \DB::table('users')->first();
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
            
            echo "✅ Created enrollment ID: {$enrollmentId}\n";
            echo "🔗 Access URL: /course-player/{$enrollmentId}\n";
        }
    } else {
        echo "✅ Found {$enrollments->count()} existing enrollments:\n";
        foreach ($enrollments as $enrollment) {
            echo "- Enrollment ID: {$enrollment->id} (User: {$enrollment->user_id})\n";
            echo "  🔗 Access URL: /course-player/{$enrollment->id}\n";
        }
    }
    
    // Step 5: Fix any enrollments with wrong course_table
    echo "\n5. Fixing enrollment course_table...\n";
    $enrollmentsFixed = \DB::table('user_course_enrollments')
        ->where('course_id', $floridaCourse->id)
        ->where(function($query) {
            $query->whereNull('course_table')
                  ->orWhere('course_table', '!=', 'florida_courses');
        })
        ->update(['course_table' => 'florida_courses']);
    
    if ($enrollmentsFixed > 0) {
        echo "✅ Fixed {$enrollmentsFixed} enrollments\n";
    } else {
        echo "✅ Enrollments already have correct course_table\n";
    }
    
    // Step 6: Test the API endpoint
    echo "\n6. Testing API endpoint...\n";
    $testEnrollment = \DB::table('user_course_enrollments')
        ->where('course_id', $floridaCourse->id)
        ->where('course_table', 'florida_courses')
        ->first();
    
    if ($testEnrollment) {
        try {
            $user = \App\Models\User::find($testEnrollment->user_id);
            if ($user) {
                \Auth::login($user);
                
                $enrollmentModel = \App\Models\UserCourseEnrollment::find($testEnrollment->id);
                $controller = new \App\Http\Controllers\EnrollmentController();
                
                $response = $controller->showWeb($enrollmentModel);
                
                if ($response->getStatusCode() === 200) {
                    echo "✅ API endpoint test successful\n";
                    $data = json_decode($response->getContent(), true);
                    echo "- Course title: " . ($data['course']['title'] ?? 'N/A') . "\n";
                    echo "- Chapters count: " . (count($data['course']['chapters'] ?? [])) . "\n";
                } else {
                    echo "❌ API endpoint test failed: " . $response->getStatusCode() . "\n";
                    echo "Response: " . substr($response->getContent(), 0, 200) . "...\n";
                }
            }
        } catch (\Exception $e) {
            echo "❌ API test error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Fix Complete ===\n";
    echo "The Florida 4-Hour BDI Course should now work properly.\n";
    echo "Try accessing one of the enrollment URLs listed above.\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}