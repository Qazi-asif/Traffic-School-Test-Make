<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixQuizAnswerFormats extends Command
{
    protected $signature = 'quiz:fix {--dry-run : Show what would be fixed without making changes}';
    protected $description = 'Fix quiz answer format inconsistencies';

    private $fixed = 0;
    private $errors = 0;

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('=== DRY RUN MODE - No changes will be made ===');
        } else {
            $this->info('=== FIXING QUIZ ANSWER FORMATS ===');
        }
        $this->newLine();

        // Fix chapter_questions table
        $this->info('Checking chapter_questions table...');
        $this->fixTable('chapter_questions', $dryRun);

        // Fix legacy questions table
        $this->newLine();
        $this->info('Checking questions table...');
        $this->fixTable('questions', $dryRun);

        // Fix final exam questions
        $this->newLine();
        $this->info('Checking final_exam_questions table...');
        $this->fixTable('final_exam_questions', $dryRun);

        $this->newLine();
        $this->info('=== SUMMARY ===');
        
        if ($dryRun) {
            $this->line("Would fix: {$this->fixed} questions");
        } else {
            $this->line("✅ Fixed: {$this->fixed} questions");
        }
        
        $this->line("⚠️  Errors: {$this->errors} questions");
        
        if (!$dryRun) {
            $this->newLine();
            $this->info("All questions now use letter format (A-E) for correct_answer");
            $this->info("All options are normalized to associative arrays with letter keys");
            $this->newLine();
            $this->warn("Run 'php artisan quiz:diagnose' to verify the fix");
        }

        return 0;
    }

    private function fixTable($tableName, $dryRun)
    {
        try {
            $questions = DB::table($tableName)->get();
            
            foreach ($questions as $q) {
                $options = json_decode($q->options, true);
                $correctAnswer = trim($q->correct_answer);
                
                if (!is_array($options) || empty($options)) {
                    $this->warn("  ⚠️  Question {$q->id}: Invalid options format");
                    $this->errors++;
                    continue;
                }
                
                $needsUpdate = false;
                $newCorrectAnswer = $correctAnswer;
                
                // Normalize options to associative array with letter keys (A, B, C, D, E)
                $normalizedOptions = [];
                $optionIndex = 0;
                foreach ($options as $key => $value) {
                    $letter = chr(65 + $optionIndex); // A, B, C, D, E
                    $normalizedOptions[$letter] = is_string($value) ? trim($value) : $value;
                    $optionIndex++;
                }
                
                // Check if correct answer needs normalization
                // If correct answer is full text, convert to letter
                if (!preg_match('/^[A-E]$/i', $correctAnswer)) {
                    // Find which option matches the correct answer text
                    foreach ($normalizedOptions as $letter => $optionText) {
                        $cleanOption = preg_replace('/^[A-E]\.\s*/i', '', $optionText);
                        $cleanCorrect = preg_replace('/^[A-E]\.\s*/i', '', $correctAnswer);
                        
                        if (strcasecmp(trim($cleanOption), trim($cleanCorrect)) === 0) {
                            $newCorrectAnswer = $letter;
                            $needsUpdate = true;
                            break;
                        }
                    }
                }
                
                // Trim whitespace from correct answer
                if ($newCorrectAnswer !== $correctAnswer) {
                    $needsUpdate = true;
                }
                
                // Update if needed
                if ($needsUpdate) {
                    if (!$dryRun) {
                        DB::table($tableName)
                            ->where('id', $q->id)
                            ->update([
                                'correct_answer' => $newCorrectAnswer,
                                'options' => json_encode($normalizedOptions),
                                'updated_at' => now()
                            ]);
                    }
                    
                    $this->line("  ✅ " . ($dryRun ? "Would fix" : "Fixed") . " Question {$q->id}: '{$correctAnswer}' → '{$newCorrectAnswer}'");
                    $this->fixed++;
                }
            }
        } catch (\Exception $e) {
            $this->error("Error processing {$tableName}: " . $e->getMessage());
        }
    }
}
