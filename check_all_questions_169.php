<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Question;
use App\Models\ChapterQuestion;

echo "🔍 Checking ALL questions for Chapter 169 in both tables:\n\n";

// Check questions table
echo "📊 Questions table:\n";
$questionsTable = Question::where('chapter_id', 169)->orderBy('id')->get();
foreach ($questionsTable as $q) {
    echo "ID: {$q->id} | Order: {$q->order_index} | Question: " . substr($q->question_text, 0, 50) . "...\n";
}
echo "Total in questions table: " . $questionsTable->count() . "\n\n";

// Check chapter_questions table
echo "📊 Chapter Questions table:\n";
$chapterQuestionsTable = ChapterQuestion::where('chapter_id', 169)->orderBy('id')->get();
foreach ($chapterQuestionsTable as $q) {
    echo "ID: {$q->id} | Order: {$q->order_index} | Question: " . substr($q->question_text, 0, 50) . "...\n";
}
echo "Total in chapter_questions table: " . $chapterQuestionsTable->count() . "\n\n";

// Check if questions 29-38 still exist anywhere
echo "🔍 Searching for original questions (IDs 29-38):\n";
for ($i = 29; $i <= 38; $i++) {
    $question = Question::find($i);
    if ($question) {
        echo "Found ID {$i} in questions table: Chapter {$question->chapter_id} - " . substr($question->question_text, 0, 40) . "...\n";
    }
    
    $chapterQuestion = ChapterQuestion::find($i);
    if ($chapterQuestion) {
        echo "Found ID {$i} in chapter_questions table: Chapter {$chapterQuestion->chapter_id} - " . substr($chapterQuestion->question_text, 0, 40) . "...\n";
    }
}