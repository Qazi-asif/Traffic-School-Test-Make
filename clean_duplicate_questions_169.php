<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Question;

echo "🧹 Cleaning duplicate questions for Chapter 169:\n\n";

// Get all questions for chapter 169
$questions = Question::where('chapter_id', 169)->orderBy('id')->get();

echo "📋 Current questions:\n";
foreach ($questions as $q) {
    echo "ID: {$q->id} | Order: {$q->order_index} | Question: " . substr($q->question_text, 0, 50) . "...\n";
}

echo "\n🗑️ Deleting duplicate questions (IDs 39-48):\n";

// Delete the duplicate questions (IDs 39-48)
$duplicateIds = [39, 40, 41, 42, 43, 44, 45, 46, 47, 48];
$deletedCount = 0;

foreach ($duplicateIds as $id) {
    $question = Question::find($id);
    if ($question && $question->chapter_id == 169) {
        echo "Deleting ID {$id}: " . substr($question->question_text, 0, 40) . "...\n";
        $question->delete();
        $deletedCount++;
    }
}

echo "\n✅ Deleted {$deletedCount} duplicate questions.\n";

// Show remaining questions
$remainingQuestions = Question::where('chapter_id', 169)->orderBy('order_index')->get();
echo "\n📋 Remaining questions:\n";
foreach ($remainingQuestions as $q) {
    echo "ID: {$q->id} | Order: {$q->order_index} | Question: " . substr($q->question_text, 0, 50) . "...\n";
}
echo "Total remaining: " . $remainingQuestions->count() . "\n";