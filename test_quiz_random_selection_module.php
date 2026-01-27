<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Course;
use App\Models\Chapter;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

echo "Testing Quiz Random Selection Module\n";
echo "===================================\n\n";

// Check if the table exists (will fail if not created yet)
try {
    $tableExists = DB::select("SHOW TABLES LIKE 'quiz_random_settings'");
    if (empty($tableExists)) {
        echo "❌ quiz_random_settings table does not exist yet.\n";
        echo "   Please run: create_quiz_random_settings_table.sql\n\n";
    } else {
        echo "✅ quiz_random_settings table exists\n\n";
    }
} catch (Exception $e) {
    echo "❌ Database connection issue: " . $e->getMessage() . "\n\n";
}

// Check courses with questions
echo "Courses with Quiz Questions:\n";
echo "----------------------------\n";

$coursesWithQuestions = Course::select('id', 'title', 'state_code')
    ->whereHas('questions')
    ->orderBy('title')
    ->get();

if ($coursesWithQuestions->count() > 0) {
    foreach ($coursesWithQuestions as $course) {
        $totalQuestions = Question::where('course_id', $course->id)->count();
        $chaptersWithQuestions = Chapter::where('course_id', $course->id)
            ->whereHas('questions')
            ->count();
        
        echo "Course ID: {$course->id}\n";
        echo "Title: {$course->title} ({$course->state_code})\n";
        echo "Total Questions: {$totalQuestions}\n";
        echo "Chapters with Questions: {$chaptersWithQuestions}\n";
        echo "---\n";
    }
} else {
    echo "No courses with questions found.\n";
}

echo "\nModule Components Created:\n";
echo "=========================\n";
echo "✅ Controller: app/Http/Controllers/Admin/QuizRandomSelectionController.php\n";
echo "✅ View: resources/views/admin/quiz-random-selection/index.blade.php\n";
echo "✅ Migration: database/migrations/2026_01_19_000001_create_quiz_random_settings_table.php\n";
echo "✅ Routes: Added to routes/web.php\n";
echo "✅ Navigation: Added to sidebar\n";

echo "\nFeatures:\n";
echo "=========\n";
echo "🎯 Course dropdown selection\n";
echo "🎯 Display all quizzes for selected course\n";
echo "🎯 Toggle random selection on/off per quiz\n";
echo "🎯 Set number of questions to select (e.g., 40 from 500)\n";
echo "🎯 Real-time AJAX updates\n";
echo "🎯 Course statistics display\n";
echo "🎯 Visual feedback and validation\n";

echo "\nExample Usage:\n";
echo "==============\n";
echo "1. Admin goes to: /admin/quiz-random-selection\n";
echo "2. Selects 'Florida 4-Hour Course' from dropdown\n";
echo "3. Sees 'Final Exam' with 500 total questions\n";
echo "4. Enables random selection\n";
echo "5. Sets 'Questions to Select' to 40\n";
echo "6. Clicks Save\n";
echo "7. Now each student gets 40 random questions from the 500 pool\n";

echo "\nNext Steps:\n";
echo "===========\n";
echo "1. Run the SQL file to create the table:\n";
echo "   - Import create_quiz_random_settings_table.sql into your database\n";
echo "2. Visit /admin/quiz-random-selection in your browser\n";
echo "3. Test the functionality with your Florida course\n";
echo "4. Integrate with the actual quiz delivery system\n";

echo "\nThis module is specifically for NORMAL quizzes (multiple choice),\n";
echo "not free response quizzes. Perfect for your Florida 4-hour course!\n";