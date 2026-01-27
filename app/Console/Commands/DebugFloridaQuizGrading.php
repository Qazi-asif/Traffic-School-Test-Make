<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugFloridaQuizGrading extends Command
{
    protected $signature = 'debug:florida-quiz';
    protected $description = 'Debug Florida 4Hr BDI quiz grading issues';

    public function handle()
    {
        $this->info('=== FLORIDA 4HR BDI QUIZ GRADING DEBUG ===');
        $this->newLine();

        // Find the Florida 4Hr BDI course
        $course = DB::table('florida_courses')
            ->where('title', 'LIKE', '%4%Hr%')
            ->orWhere('title', 'LIKE', '%BDI%')
            ->first();

        if (!$course) {
            $this->error('❌ Florida 4Hr BDI course not found!');
            return 1;
        }

        $this->info("✅ Found course: {$course->title} (ID: {$course->id})");
        $this->newLine();

        // Get all chapters for this course
        $chapters = DB::table('florida_chapters')
            ->where('course_id', $course->id)
            ->orderBy('order_index')
            ->get();

        $this->info("📚 Total chapters: " . count($chapters));
        $this->newLine();

        $totalIssues = 0;

        // Check questions for each chapter
        foreach ($chapters as $chapter) {
            $this->line("--- Chapter {$chapter->order_index}: {$chapter->title} ---");
            
            // Get questions from chapter_questions table
            $questions = DB::table('chapter_questions')
                ->where('chapter_id', $chapter->id)
                ->get();
            
            if ($questions->isEmpty()) {
                $this->warn("  ⚠️  No questions found in chapter_questions table");
                
                // Check legacy questions table
                $legacyQuestions = DB::table('questions')
                    ->where('chapter_id', $chapter->id)
                    ->get();
                
                if (!$legacyQuestions->isEmpty()) {
                    $this->info("  ℹ️  Found {$legacyQuestions->count()} questions in legacy 'questions' table");
                    $questions = $legacyQuestions;
                }
            } else {
                $this->info("  ✅ Found {$questions->count()} questions");
            }
            
            // Analyze each question
            foreach ($questions as $q) {
                $options = json_decode($q->options, true);
                
                $this->newLine();
                $this->line("  Question ID {$q->id}:");
                $this->line("    Text: " . substr($q->question_text, 0, 60) . "...");
                $this->line("    Type: {$q->question_type}");
                $this->line("    Correct Answer: '{$q->correct_answer}'");
                
                if (is_array($options)) {
                    $this->line("    Options (" . count($options) . "):");
                    foreach ($options as $key => $value) {
                        $marker = ($key === $q->correct_answer || $value === $q->correct_answer) ? " ✓" : "";
                        $this->line("      {$key}: " . substr($value, 0, 50) . "...{$marker}");
                    }
                } else {
                    $this->warn("    ⚠️  Options format issue: " . gettype($options));
                    $this->line("    Raw options: " . substr($q->options, 0, 100));
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
                    $this->error("    🚨 ISSUES FOUND:");
                    foreach ($issues as $issue) {
                        $this->error("       - {$issue}");
                        $totalIssues++;
                    }
                }
            }
            
            $this->newLine();
        }

        $this->newLine();
        $this->info('=== SUMMARY ===');
        if ($totalIssues > 0) {
            $this->error("Found {$totalIssues} issues that will cause grading failures!");
        } else {
            $this->info("No issues found - all questions should grade correctly");
        }

        return 0;
    }
}
