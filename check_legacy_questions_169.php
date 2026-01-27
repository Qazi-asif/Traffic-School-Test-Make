<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Question;
use App\Models\ChapterQuestion;

echo "🔍 Checking all questions for Chapter 169:\n\n";

// Check questions table
echo "📊 Questions table (legacy):\n";
$questionsTable = Question::where('chapter_id', 169)->get();
foreach ($questionsTable as $q) {
    echo "ID: {$q->id} | Order: {$q->order_index} | Question: " . substr($q->question_text, 0, 50) . "...\n";
}
echo "Total: " . $questionsTable->count() . "\n\n";

// Check chapter_questions table
echo "📊 Chapter Questions table (new):\n";
$chapterQuestionsTable = ChapterQuestion::where('chapter_id', 169)->get();
foreach ($chapterQuestionsTable as $q) {
    echo "ID: {$q->id} | Order: {$q->order_index} | Question: " . substr($q->question_text, 0, 50) . "...\n";
}
echo "Total: " . $chapterQuestionsTable->count() . "\n\n";

// Check what the API returns (simulating the controller logic)
echo "🔍 What the API returns (merged):\n";
$questionsFromNew = Question::where('chapter_id', 169)->orderBy('order_index')->get();
$questionsFromLegacy = ChapterQuestion::where('chapter_id', 169)->orderBy('order_index')->get();
$merged = $questionsFromNew->merge($questionsFromLegacy);

foreach ($merged as $q) {
    $table = ($q instanceof Question) ? 'questions' : 'chapter_questions';
    echo "ID: {$q->id} | Table: {$table} | Order: {$q->order_index} | Question: " . substr($q->question_text, 0, 50) . "...\n";
}
echo "Total merged: " . $merged->count() . "\n";