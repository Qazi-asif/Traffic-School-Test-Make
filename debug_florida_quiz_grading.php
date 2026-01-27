<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FLORIDA 4HR BDI QUIZ GRADING DEBUG ===\n\n";

// Find the Florida 4Hr BDI course
$course = DB::table('florida_courses')
    ->where('title', 'LIKE', '%4%Hr%')
    ->orWhere('title', 'LIKE', '%BDI%')
    ->first();

if (!$course) {
    echo "❌ Florida 4Hr BDI course not found!\n";
    exit(1);
}

echo "✅ Found course: {$course->title} (ID: {$course->id})\n\n";

// Get all chapters for this course
$chapters = DB::table('florida_chapters')
    ->where('course_id', $course->id)
    ->orderBy('order_index')
    ->get();

echo "📚 Total chapters: " . count($chapters) . "\n\n";

// Check questions for each chapter
foreach ($chapters as $chapter) {
    echo "--- Chapter {$chapter->order_index}: {$chapter->title} ---\n";
    
    // Get questions from chapter_questions table
    $questions = DB::table('chapter_questions')
        ->where('chapter_id', $chapter->id)
        ->get();
    
    if ($questions->isEmpty()) {
        echo "  ⚠️  No questions found in chapter_questions table\n";
        
        // Check legacy questions table
        $legacyQuestions = DB::table('questions')
            ->where('chapter_id', $chapter->id)
            ->get();
        
        if (!$legacyQuestions->isEmpty()) {
            echo "  ℹ️  Found {$legacyQuestions->count()} questions in legacy 'questions' table\n";
            $questions = $legacyQuestions;
        }
    } else {
        echo "  ✅ Found {$questions->count()} questions\n";
    }
    
    // Analyze each question
    foreach ($questions as $q) {
        $options = json_decode($q->options, true);
        
        echo "\n  Question ID {$q->id}:\n";
        echo "    Text: " . substr($q->question_text, 0, 60) . "...\n";
        echo "    Type: {$q->question_type}\n";
        echo "    Correct Answer: '{$q->correct_answer}'\n";
        
        if (is_array($options)) {
            echo "    Options (" . count($options) . "):\n";
            foreach ($options as $key => $value) {
                $marker = ($key === $q->correct_answer || $value === $q->correct_answer) ? " ✓" : "";
                echo "      {$key}: " . substr($value, 0, 50) . "...{$marker}\n";
            }
        } else {
            echo "    ⚠️  Options format issue: " . gettype($options) . "\n";
            echo "    Raw options: " . substr($q->options, 0, 100) . "\n";
        }
        
        // Check for potential issues
        $issues = [];
        
        // Issue 1: Correct answer not in options
        if (is_array($options)) {
            $correctAnswerFound = false;
            foreach ($options as $key => $value) {
                if ($key === $q->correct_answer || $value === $q->correct_answer) {
                    $correctAnswerFound = true;
                    break;
                }
            }
            if (!$correctAnswerFound) {
                $issues[] = "Correct answer '{$q->correct_answer}' not found in options!";
            }
        }
        
        // Issue 2: Options not properly formatted
        if (!is_array($options)) {
            $issues[] = "Options are not a valid JSON array";
        }
        
        // Issue 3: Empty correct answer
        if (empty($q->correct_answer)) {
            $issues[] = "Correct answer is empty!";
        }
        
        // Issue 4: Whitespace issues
        if (trim($q->correct_answer) !== $q->correct_answer) {
            $issues[] = "Correct answer has leading/trailing whitespace";
        }
        
        if (!empty($issues)) {
            echo "    🚨 ISSUES FOUND:\n";
            foreach ($issues as $issue) {
                echo "       - {$issue}\n";
            }
        }
    }
    
    echo "\n";
}

echo "\n=== SUMMARY ===\n";
echo "Check the output above for any 🚨 ISSUES FOUND markers\n";
echo "These indicate questions that will fail grading\n";
