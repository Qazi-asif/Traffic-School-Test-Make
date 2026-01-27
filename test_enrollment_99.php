<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Testing Enrollment 99 ===\n";
    
    // Check if enrollment 99 exists
    $enrollment = \DB::table('user_course_enrollments')->where('id', 99)->first();
    
    if (!$enrollment) {
        echo "❌ Enrollment 99 not found\n";
        exit;
    }
    
    echo "✅ Enrollment 99 found:\n";
    echo "- ID: {$enrollment->id}\n";
    echo "- User ID: {$enrollment->user_id}\n";
    echo "- Course ID: {$enrollment->course_id}\n";
    echo "- Course Table: " . ($enrollment->course_table ?? 'null') . "\n";
    echo "- Status: {$enrollment->status}\n";
    echo "- Payment Status: {$enrollment->payment_status}\n";
    
    // Check if the course exists in the appropriate table
    $courseTable = $enrollment->course_table ?? 'courses';
    echo "\n=== Checking Course in {$courseTable} ===\n";
    
    $course = \DB::table($courseTable)->where('id', $enrollment->course_id)->first();
    
    if (!$course) {
        echo "❌ Course {$enrollment->course_id} not found in {$courseTable}\n";
        
        // Try the other table
        $fallbackTable = $courseTable === 'courses' ? 'florida_courses' : 'courses';
        echo "Trying {$fallbackTable}...\n";
        
        $course = \DB::table($fallbackTable)->where('id', $enrollment->course_id)->first();
        
        if ($course) {
            echo "✅ Course found in {$fallbackTable}!\n";
            echo "- Title: {$course->title}\n";
            echo "- State: " . ($course->state ?? 'N/A') . "\n";
        } else {
            echo "❌ Course not found in either table\n";
        }
    } else {
        echo "✅ Course found in {$courseTable}:\n";
        echo "- Title: {$course->title}\n";
        echo "- State: " . ($course->state ?? 'N/A') . "\n";
    }
    
    // Test the API endpoint directly
    echo "\n=== Testing API Endpoint ===\n";
    
    // Simulate the request
    $user = \App\Models\User::find($enrollment->user_id);
    if ($user) {
        echo "✅ User found: {$user->email}\n";
        
        // Authenticate as this user for testing
        \Auth::login($user);
        
        // Test the showWeb method
        try {
            $enrollmentModel = \App\Models\UserCourseEnrollment::find(99);
            $controller = new \App\Http\Controllers\EnrollmentController();
            
            echo "Calling showWeb method...\n";
            $response = $controller->showWeb($enrollmentModel);
            
            if ($response->getStatusCode() === 200) {
                echo "✅ showWeb method successful\n";
                $data = json_decode($response->getContent(), true);
                echo "- Course title: " . ($data['course']['title'] ?? 'N/A') . "\n";
                echo "- Chapters count: " . (count($data['course']['chapters'] ?? [])) . "\n";
            } else {
                echo "❌ showWeb method failed with status: " . $response->getStatusCode() . "\n";
                echo "Response: " . $response->getContent() . "\n";
            }
            
        } catch (\Exception $e) {
            echo "❌ Error calling showWeb: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
        
    } else {
        echo "❌ User not found for enrollment\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}