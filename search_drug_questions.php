<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Question;
use App\Models\ChapterQuestion;

echo "🔍 Searching for questions containing 'drug' or 'alcohol':\n\n";

// Search in questions table
$questionsTable = Question::where('question_text', 'LIKE', '%drug%')
    ->orWhere('question_text', 'LIKE', '%alcohol%')
    ->get();

echo "📊 Questions table:\n";
foreach ($questionsTable as $q) {
    echo "ID: {$q->id} | Chapter: {$q->chapter_id}\n";
    echo "Question: {$q->question_text}\n";
    echo "---\n";
}

// Search in chapter_questions table
$chapterQuestionsTable = ChapterQuestion::where('question_text', 'LIKE', '%drug%')
    ->orWhere('question_text', 'LIKE', '%alcohol%')
    ->get();

echo "\n📊 Chapter Questions table:\n";
foreach ($chapterQuestionsTable as $q) {
    echo "ID: {$q->id} | Chapter: {$q->chapter_id}\n";
    echo "Question: {$q->question_text}\n";
    echo "---\n";
}

// Search in final_exam_questions table
$finalExamQuestions = DB::table('final_exam_questions')
    ->where('question_text', 'LIKE', '%drug%')
    ->orWhere('question_text', 'LIKE', '%alcohol%')
    ->get();

echo "\n📊 Final Exam Questions table:\n";
foreach ($finalExamQuestions as $q) {
    echo "ID: {$q->id} | Course: {$q->course_id}\n";
    echo "Question: {$q->question_text}\n";
    echo "---\n";
}

// Check what question ID 30 returns from the API
echo "\n🔍 Testing API call for question ID 30:\n";
try {
    $question30 = Question::find(30);
    if ($question30) {
        echo "Found in questions table:\n";
        echo "ID: {$question30->id}\n";
        echo "Chapter: {$question30->chapter_id}\n";
        echo "Question: {$question30->question_text}\n";
    }
    
    $chapterQuestion30 = ChapterQuestion::find(30);
    if ($chapterQuestion30) {
        echo "Found in chapter_questions table:\n";
        echo "ID: {$chapterQuestion30->id}\n";
        echo "Chapter: {$chapterQuestion30->chapter_id}\n";
        echo "Question: {$chapterQuestion30->question_text}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}