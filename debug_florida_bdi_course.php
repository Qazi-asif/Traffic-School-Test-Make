<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Debugging Florida 4-Hour BDI Course ===\n";
    
    // Find the Florida 4-Hour BDI Course
    $floridaCourse = \DB::table('florida_courses')
        ->where('title', 'LIKE', '%4-Hour%BDI%')
        ->orWhere('title', 'LIKE', '%4 Hour%BDI%')
        ->orWhere('title', 'LIKE', '%Florida%BDI%')
        ->first();
    
    if (!$floridaCourse) {
        echo "❌ Florida 4-Hour BDI Course not found in florida_courses table\n";
        
        // Try regular courses table
        $regularCourse = \DB::table('courses')
            ->where('title', 'LIKE', '%4-Hour%BDI%')
            ->orWhere('title', 'LIKE', '%4 Hour%BDI%')
            ->orWhere('title', 'LIKE', '%Florida%BDI%')
            ->first();
            
        if ($regularCourse) {
            echo "✅ Found in regular courses table:\n";
            echo "- ID: {$regularCourse->id}\n";
            echo "- Title: {$regularCourse->title}\n";
            $floridaCourse = $regularCourse;
            $courseTable = 'courses';
        } else {
            echo "❌ Course not found in either table\n";
            
            // List all Florida courses
            echo "\n=== All Florida Courses ===\n";
            $allFloridaCourses = \DB::table('florida_courses')->get();
            foreach ($allFloridaCourses as $course) {
                echo "- ID: {$course->id}, Title: {$course->title}\n";
            }
            exit;
        }
    } else {
        echo "✅ Found Florida 4-Hour BDI Course:\n";
        echo "- ID: {$floridaCourse->id}\n";
        echo "- Title: {$floridaCourse->title}\n";
        $courseTable = 'florida_courses';
    }
    
    // Check for enrollments in this course
    echo "\n=== Checking Enrollments ===\n";
    $enrollments = \DB::table('user_course_enrollments')
        ->where('course_id', $floridaCourse->id)
        ->where('course_table', $courseTable)
        ->get();
    
    if ($enrollments->isEmpty()) {
        echo "❌ No enrollments found for this course\n";
    } else {
        echo "✅ Found {$enrollments->count()} enrollments:\n";
        foreach ($enrollments as $enrollment) {
            echo "- Enrollment ID: {$enrollment->id}\n";
            echo "  User ID: {$enrollment->user_id}\n";
            echo "  Status: {$enrollment->status}\n";
            echo "  Payment Status: {$enrollment->payment_status}\n";
            echo "  Course Table: " . ($enrollment->course_table ?? 'null') . "\n";
            
            // Test the API endpoint for this enrollment
            echo "  Testing API endpoint...\n";
            
            try {
                $user = \App\Models\User::find($enrollment->user_id);
                if ($user) {
                    \Auth::login($user);
                    
                    $enrollmentModel = \App\Models\UserCourseEnrollment::find($enrollment->id);
                    $controller = new \App\Http\Controllers\EnrollmentController();
                    
                    $response = $controller->showWeb($enrollmentModel);
                    
                    if ($response->getStatusCode() === 200) {
                        echo "  ✅ API endpoint works\n";
                        $data = json_decode($response->getContent(), true);
                        echo "  Course title: " . ($data['course']['title'] ?? 'N/A') . "\n";
                        echo "  Chapters count: " . (count($data['course']['chapters'] ?? [])) . "\n";
                    } else {
                        echo "  ❌ API endpoint failed: " . $response->getStatusCode() . "\n";
                        echo "  Response: " . $response->getContent() . "\n";
                    }
                } else {
                    echo "  ❌ User not found\n";
                }
            } catch (\Exception $e) {
                echo "  ❌ API test failed: " . $e->getMessage() . "\n";
            }
            
            echo "\n";
        }
    }
    
    // Check chapters for this course
    echo "=== Checking Chapters ===\n";
    $chapters = \DB::table('chapters')
        ->where('course_id', $floridaCourse->id)
        ->where('course_table', $courseTable)
        ->orderBy('order_index')
        ->get();
    
    if ($chapters->isEmpty()) {
        echo "❌ No chapters found for this course\n";
        
        // Check if chapters exist without course_table filter
        $chaptersNoFilter = \DB::table('chapters')
            ->where('course_id', $floridaCourse->id)
            ->orderBy('order_index')
            ->get();
            
        if ($chaptersNoFilter->isEmpty()) {
            echo "❌ No chapters found even without course_table filter\n";
        } else {
            echo "⚠️  Found {$chaptersNoFilter->count()} chapters without course_table filter:\n";
            foreach ($chaptersNoFilter as $chapter) {
                echo "- Chapter ID: {$chapter->id}\n";
                echo "  Title: {$chapter->title}\n";
                echo "  Course Table: " . ($chapter->course_table ?? 'null') . "\n";
                echo "  Order Index: {$chapter->order_index}\n";
                echo "\n";
            }
        }
    } else {
        echo "✅ Found {$chapters->count()} chapters:\n";
        foreach ($chapters as $chapter) {
            echo "- Chapter ID: {$chapter->id}, Title: {$chapter->title}, Order: {$chapter->order_index}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}