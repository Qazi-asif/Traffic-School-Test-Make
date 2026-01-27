<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Question;

// Get all questions for chapter 169
$questions = Question::where('chapter_id', 169)
    ->orderBy('order_index')
    ->get();

echo "🔍 Questions in Chapter 169:\n\n";

foreach ($questions as $question) {
    echo "ID: {$question->id} | Order: {$question->order_index}\n";
    echo "Question: " . substr($question->question_text, 0, 60) . "...\n";
    echo "Correct Answer: {$question->correct_answer}\n";
    echo "---\n";
}

echo "\nTotal questions: " . $questions->count() . "\n";

// Also check if there are any other questions with similar text
echo "\n🔍 Searching for the 'drugs with alcohol' question:\n";
$drugQuestion = Question::where('question_text', 'LIKE', '%drugs with alcohol%')->first();
if ($drugQuestion) {
    echo "Found: ID {$drugQuestion->id} in Chapter {$drugQuestion->chapter_id}\n";
    echo "Question: {$drugQuestion->question_text}\n";
} else {
    echo "No 'drugs with alcohol' question found.\n";
}