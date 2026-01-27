<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Course;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

echo "Debugging Courses Dropdown Issue\n";
echo "================================\n\n";

// Check if courses table exists
try {
    $tableExists = DB::select("SHOW TABLES LIKE 'courses'");
    if (empty($tableExists)) {
        echo "❌ 'courses' table does not exist!\n";
        exit;
    } else {
        echo "✅ 'courses' table exists\n";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit;
}

// Check total courses
$totalCourses = Course::count();
echo "Total courses in database: {$totalCourses}\n\n";

if ($totalCourses == 0) {
    echo "❌ No courses found in the database!\n";
    echo "This is why the dropdown is empty.\n\n";
    
    echo "To fix this, you need to:\n";
    echo "1. Add some courses to the database\n";
    echo "2. Or check if courses are in a different table (florida_courses, etc.)\n\n";
    
    // Check other course tables
    $otherTables = ['florida_courses', 'nevada_courses'];
    foreach ($otherTables as $table) {
        try {
            $count = DB::table($table)->count();
            echo "Courses in {$table}: {$count}\n";
        } catch (Exception $e) {
            echo "Table {$table} doesn't exist or error: " . $e->getMessage() . "\n";
        }
    }
    
} else {
    echo "✅ Found {$totalCourses} courses\n\n";
    
    // Show first 5 courses
    $courses = Course::select('id', 'title', 'state')->limit(5)->get();
    echo "Sample courses:\n";
    foreach ($courses as $course) {
        echo "- ID: {$course->id}, Title: {$course->title}, State: " . ($course->state ?? 'NULL') . "\n";
    }
    
    echo "\n";
    
    // Check which courses have questions
    $coursesWithQuestions = Course::select('id', 'title', 'state')
        ->whereHas('questions')
        ->get();
    
    echo "Courses with questions: " . $coursesWithQuestions->count() . "\n";
    foreach ($coursesWithQuestions as $course) {
        $questionCount = Question::where('course_id', $course->id)->count();
        echo "- {$course->title}: {$questionCount} questions\n";
    }
}

echo "\nController Query Test:\n";
echo "=====================\n";

// Test the exact query from the controller
try {
    $controllerCourses = Course::select('id', 'title', 'state')
        ->orderBy('title')
        ->get();
    
    echo "Controller query returned: " . $controllerCourses->count() . " courses\n";
    
    if ($controllerCourses->count() > 0) {
        echo "✅ Controller query works fine!\n";
        echo "The issue might be in the view rendering or route access.\n\n";
        
        echo "Check:\n";
        echo "1. Are you accessing /admin/quiz-random-selection?\n";
        echo "2. Are you logged in as admin?\n";
        echo "3. Check browser console for JavaScript errors\n";
        echo "4. Check Laravel logs for any errors\n";
    } else {
        echo "❌ Controller query returns empty results\n";
    }
    
} catch (Exception $e) {
    echo "❌ Controller query failed: " . $e->getMessage() . "\n";
}

echo "\nNext Steps:\n";
echo "===========\n";
if ($totalCourses == 0) {
    echo "1. Add some test courses to the database\n";
    echo "2. Or check if your courses are in florida_courses table instead\n";
} else {
    echo "1. Clear Laravel cache: php artisan cache:clear\n";
    echo "2. Check if you're accessing the correct URL\n";
    echo "3. Check browser developer tools for errors\n";
    echo "4. Verify admin authentication is working\n";
}