<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debugging Final Exam Issue ===\n\n";

try {
    // Check enrollment 29
    $enrollment = DB::table('user_course_enrollments')->where('id', 29)->first();
    
    if ($enrollment) {
        echo "Enrollment 29 details:\n";
        echo "- Course ID: {$enrollment->course_id}\n";
        echo "- Course Table: {$enrollment->course_table}\n";
        echo "- User ID: {$enrollment->user_id}\n";
        echo "- Status: {$enrollment->status}\n\n";
        
        $courseId = $enrollment->course_id;
        $courseTable = $enrollment->course_table;
        
        // Check if course exists in the specified table
        if ($courseTable === 'florida_courses') {
            $course = DB::table('florida_courses')->where('id', $courseId)->first();
        } else {
            $course = DB::table('courses')->where('id', $courseId)->first();
        }
        
        if ($course) {
            echo "Course details:\n";
            echo "- Title: {$course->title}\n";
            echo "- Table: {$courseTable}\n\n";
        } else {
            echo "❌ Course {$courseId} not found in {$courseTable} table!\n\n";
        }
        
        // Check final exam questions for this course
        $finalExamQuestions = DB::table('final_exam_questions')
            ->where('course_id', $courseId)
            ->count();
        
        echo "Final exam questions for course {$courseId}: {$finalExamQuestions}\n";
        
        if ($finalExamQuestions == 0) {
            echo "❌ No final exam questions found for course {$courseId}!\n";
            
            // Check if questions exist for course 21 (which you mentioned works)
            $course21Questions = DB::table('final_exam_questions')
                ->where('course_id', 21)
                ->count();
            
            echo "Final exam questions for course 21: {$course21Questions}\n";
            
            if ($course21Questions > 0) {
                echo "✅ Questions exist for course 21 but not for course {$courseId}\n";
                echo "This explains why admin works for course 21 but course player fails for enrollment 29\n\n";
                
                // Check if courses are related (same title, different tables)
                if ($course) {
                    $course21 = DB::table('florida_courses')->where('id', 21)->first();
                    if ($course21) {
                        echo "Course comparison:\n";
                        echo "- Course {$courseId} ({$courseTable}): {$course->title}\n";
                        echo "- Course 21 (florida_courses): {$course21->title}\n";
                        
                        if (stripos($course->title, 'Texas') !== false && stripos($course21->title, 'Texas') !== false) {
                            echo "🔍 Both courses appear to be Texas courses - they might need the same questions!\n";
                        }
                    }
                }
            }
        } else {
            echo "✅ Final exam questions exist for course {$courseId}\n";
        }
        
        // Check what the course player API returns
        echo "\n=== Testing Course Player API ===\n";
        
        // Simulate the course player request
        $request = new Illuminate\Http\Request();
        $request->merge(['enrollmentId' => 29]);
        $request->server->set('REQUEST_URI', "/course-player/{$courseId}");
        app()->instance('request', $request);
        
        $controller = new App\Http\Controllers\ChapterController();
        $response = $controller->indexWeb($courseId);
        $chapters = json_decode($response->getContent(), true);
        
        $finalExamChapter = null;
        foreach ($chapters as $chapter) {
            if ($chapter['id'] === 'final-exam') {
                $finalExamChapter = $chapter;
                break;
            }
        }
        
        if ($finalExamChapter) {
            echo "✅ Final exam chapter found in course player API\n";
            echo "- Title: {$finalExamChapter['title']}\n";
            echo "- Course ID: {$finalExamChapter['course_id']}\n";
        } else {
            echo "❌ Final exam chapter NOT found in course player API\n";
        }
        
    } else {
        echo "❌ Enrollment 29 not found!\n";
    }
    
    echo "\n=== Solution Recommendations ===\n";
    
    if (isset($courseId) && isset($finalExamQuestions) && $finalExamQuestions == 0) {
        echo "1. Copy final exam questions from course 21 to course {$courseId}\n";
        echo "2. Or create new final exam questions for course {$courseId}\n";
        echo "3. Or update enrollment 29 to use course 21 instead\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}