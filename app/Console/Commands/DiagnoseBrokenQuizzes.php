<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagnoseBrokenQuizzes extends Command
{
    protected $signature = 'quiz:diagnose';
    protected $description = 'Diagnose broken quiz questions with data inconsistencies';

    public function handle()
    {
        $this->info('=== DIAGNOSING BROKEN QUIZZES ===');
        $this->newLine();

        $totalBroken = 0;
        $totalChecked = 0;

        // Check all question tables
        $tables = [
            'chapter_questions' => 'chapter_id',
            'questions' => 'chapter_id',
            'final_exam_questions' => 'course_id'
        ];

        foreach ($tables as $tableName => $foreignKey) {
            $this->info("=== Checking {$tableName} ===");
            
            try {
                $questions = DB::table($tableName)->get();
                $this->line("Total questions: " . count($questions));
                $this->newLine();
                
                foreach ($questions as $q) {
                    $totalChecked++;
                    $issues = $this->checkQuestion($q, $tableName);
                    
                    if (!empty($issues)) {
                        $totalBroken++;
                        $this->error("🚨 BROKEN - Question ID {$q->id} ({$foreignKey}: {$q->$foreignKey})");
                        $this->line("   Text: " . substr($q->question_text, 0, 60) . "...");
                        foreach ($issues as $issue) {
                            $this->line("   - {$issue}");
                        }
                        $this->newLine();
                    }
                }
            } catch (\Exception $e) {
                $this->warn("⚠️  Error checking {$tableName}: " . $e->getMessage());
                $this->newLine();
            }
        }

        $this->newLine();
        $this->info('=== SUMMARY ===');
        $this->line("Total questions checked: {$totalChecked}");
        $this->line("Broken questions found: {$totalBroken}");

        if ($totalBroken > 0) {
            $this->newLine();
            $this->warn("🔧 To fix these issues, run: php artisan quiz:fix");
        } else {
            $this->newLine();
            $this->info("✅ All questions appear to be correctly formatted!");
        }

        return 0;
    }

    private function checkQuestion($q, $tableName)
    {
        $issues = [];
        $options = json_decode($q->options, true);
        $correctAnswer = $q->correct_answer;
        
        // Issue 1: Options not an array
        if (!is_array($options)) {
            $issues[] = "Options is not an array (type: " . gettype($options) . ")";
            return $issues;
        }
        
        // Issue 2: Empty options
        if (empty($options)) {
            $issues[] = "Options array is empty";
        }
        
        // Issue 3: Empty correct answer
        if (empty(trim($correctAnswer))) {
            $issues[] = "Correct answer is empty";
        }
        
        // Issue 4: Whitespace in correct answer
        if ($correctAnswer !== trim($correctAnswer)) {
            $issues[] = "Correct answer has whitespace: '" . $correctAnswer . "'";
        }
        
        // Issue 5: Correct answer not found in options
        $correctAnswerFound = false;
        $correctAnswerNorm = trim($correctAnswer);
        
        foreach ($options as $key => $value) {
            $keyNorm = trim($key);
            $valueNorm = trim($value);
            
            // Check if correct answer matches key or value
            if (strcasecmp($keyNorm, $correctAnswerNorm) === 0 || 
                strcasecmp($valueNorm, $correctAnswerNorm) === 0) {
                $correctAnswerFound = true;
                break;
            }
            
            // Check without letter prefix (e.g., "A. Text" → "Text")
            $valueClean = preg_replace('/^[A-E]\.\s*/i', '', $valueNorm);
            $correctClean = preg_replace('/^[A-E]\.\s*/i', '', $correctAnswerNorm);
            
            if (strcasecmp($valueClean, $correctClean) === 0) {
                $correctAnswerFound = true;
                break;
            }
        }
        
        if (!$correctAnswerFound) {
            $issues[] = "Correct answer '{$correctAnswer}' not found in options";
            $issues[] = "  Available options: " . implode(', ', array_keys($options));
        }
        
        return $issues;
    }
}
