<?php
/**
 * Check Quiz Options Format
 * 
 * This script checks how quiz options are stored in the database
 * and identifies if they have duplicate letter prefixes
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking Quiz Options Format ===\n\n";

try {
    // Get a sample of questions from Delaware courses
    $questions = DB::table('questions')
        ->join('chapters', 'questions.chapter_id', '=', 'chapters.id')
        ->join('courses', 'chapters.course_id', '=', 'courses.id')
        ->where('courses.state_code', 'DE')
        ->select('questions.*', 'courses.title as course_title', 'chapters.title as chapter_title')
        ->limit(5)
        ->get();

    if ($questions->isEmpty()) {
        echo "No Delaware questions found. Checking all questions...\n\n";
        $questions = DB::table('questions')
            ->limit(5)
            ->get();
    }

    foreach ($questions as $question) {
        echo "Question ID: {$question->id}\n";
        echo "Question: " . substr($question->question_text, 0, 60) . "...\n";
        
        // Check individual option columns first
        if (isset($question->option_a)) {
            echo "\nIndividual option columns found:\n";
            echo "  option_a: {$question->option_a}\n";
            echo "  option_b: {$question->option_b}\n";
            echo "  option_c: {$question->option_c}\n";
            if (isset($question->option_d)) echo "  option_d: {$question->option_d}\n";
            if (isset($question->option_e)) echo "  option_e: {$question->option_e}\n";
            
            // Check if options have duplicate prefixes
            if (preg_match('/^[A-E]\.\s*[A-E]\./i', $question->option_a)) {
                echo "  ⚠️  DUPLICATE PREFIX DETECTED in option_a!\n";
            }
        }
        
        // Check options field if it exists
        if (isset($question->options)) {
            echo "\nOptions field:\n";
            // Try to parse options
            $options = json_decode($question->options, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($options)) {
                echo "  Format: JSON Array\n";
                foreach ($options as $key => $value) {
                    if (is_string($value)) {
                        echo "  [{$key}]: \"{$value}\"\n";
                        // Check for duplicate prefixes
                        if (preg_match('/^[A-E]\.\s*[A-E]\./i', $value)) {
                            echo "    ⚠️  DUPLICATE PREFIX DETECTED!\n";
                        }
                    } else {
                        echo "  [{$key}]: " . json_encode($value) . "\n";
                    }
                }
            } else {
                echo "  Format: String/Other\n";
                echo "  Raw: " . substr($question->options, 0, 200) . "\n";
            }
        }
        
        echo "\n" . str_repeat("-", 80) . "\n\n";
    }

    echo "\n=== Analysis ===\n";
    echo "If you see options like 'A. alternative routes', the database has the letter prefix.\n";
    echo "The display code should strip these prefixes before showing them.\n";
    echo "\nIf the fix isn't working, the issue might be:\n";
    echo "1. Browser cache (try Ctrl+Shift+R)\n";
    echo "2. Laravel view cache (run: php artisan view:clear)\n";
    echo "3. The parseOptions() function not working correctly\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
