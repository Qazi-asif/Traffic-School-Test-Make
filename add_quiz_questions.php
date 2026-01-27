<?php
/**
 * Quiz Question Importer
 * 
 * Usage: 
 * - Browser: http://127.0.0.1:8000/add_quiz_questions.php?chapter_id=123
 * - Terminal: php add_quiz_questions.php
 * 
 * Format: Questions with *** marking correct answer
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Get chapter_id from query string or command line
$chapterId = $_GET['chapter_id'] ?? ($argv[1] ?? null);

$isWeb = php_sapi_name() !== 'cli';

// CLI Mode
if (!$isWeb) {
    if (!$chapterId) {
        echo "Usage: php add_quiz_questions.php <chapter_id> [quiz_set]\n";
        echo "Example: php add_quiz_questions.php 58\n";
        echo "Example: php add_quiz_questions.php 58 2  (for Delaware Quiz Set 2)\n";
        exit(1);
    }
    
    $quizSet = isset($argv[2]) ? (int)$argv[2] : 1;
    if ($quizSet < 1 || $quizSet > 2) $quizSet = 1;
    
    // Check if chapter exists
    $chapter = DB::table('chapters')->find($chapterId);
    if (!$chapter) {
        echo "Error: Chapter {$chapterId} not found.\n";
        exit(1);
    }
    
    echo "=== Quiz Question Importer ===\n";
    echo "Chapter: {$chapter->title} (ID: {$chapterId})\n";
    echo "Quiz Set: {$quizSet}" . ($quizSet == 2 ? " (Delaware Rotation)" : "") . "\n\n";
    echo "Paste your quiz text below (use *** after correct answer).\n";
    echo "When done, type 'END' on a new line and press Enter:\n";
    echo "-------------------------------------------\n";
    
    $quizText = '';
    while (($line = fgets(STDIN)) !== false) {
        if (trim($line) === 'END') break;
        $quizText .= $line;
    }
    
    $questions = parseQuizText($quizText);
    
    if (empty($questions)) {
        echo "\nNo questions found. Check your format.\n";
        exit(1);
    }
    
    echo "\n--- Parsed " . count($questions) . " questions (Quiz Set {$quizSet}) ---\n\n";
    
    foreach ($questions as $q) {
        echo "Q{$q['number']}: {$q['question']}\n";
        foreach ($q['options'] as $letter => $opt) {
            $mark = ($letter === $q['correct']) ? ' ✓' : '';
            echo "   {$letter}. {$opt}{$mark}\n";
        }
        echo "\n";
    }
    
    echo "Insert these questions into chapter {$chapterId} (Quiz Set {$quizSet})? (y/n): ";
    $confirm = trim(fgets(STDIN));
    
    if (strtolower($confirm) === 'y') {
        $inserted = 0;
        
        // Check if chapter_questions table exists (new system)
        $useNewTable = \Schema::hasTable('chapter_questions');
        
        foreach ($questions as $index => $q) {
            try {
                if ($useNewTable) {
                    // Use new chapter_questions table (supports quiz_set)
                    DB::table('chapter_questions')->insert([
                        'chapter_id' => $chapterId,
                        'question_text' => $q['question'],
                        'question_type' => 'multiple_choice',
                        'options' => json_encode([
                            ['label' => 'A', 'text' => $q['options']['A'] ?? ''],
                            ['label' => 'B', 'text' => $q['options']['B'] ?? ''],
                            ['label' => 'C', 'text' => $q['options']['C'] ?? ''],
                            ['label' => 'D', 'text' => $q['options']['D'] ?? ''],
                            ['label' => 'E', 'text' => $q['options']['E'] ?? ''],
                        ]),
                        'correct_answer' => $q['correct'],
                        'points' => 1,
                        'order_index' => $index + 1,
                        'quiz_set' => $quizSet,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // Use old questions table
                    DB::table('questions')->insert([
                        'chapter_id' => $chapterId,
                        'question_text' => $q['question'],
                        'option_a' => $q['options']['A'] ?? '',
                        'option_b' => $q['options']['B'] ?? '',
                        'option_c' => $q['options']['C'] ?? '',
                        'option_d' => $q['options']['D'] ?? '',
                        'option_e' => $q['options']['E'] ?? null,
                        'correct_answer' => $q['correct'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $inserted++;
            } catch (\Exception $e) {
                echo "Error Q{$q['number']}: " . $e->getMessage() . "\n";
            }
        }
        echo "\n✓ Inserted {$inserted} questions into chapter {$chapterId} (Quiz Set {$quizSet})\n";
    } else {
        echo "Cancelled.\n";
    }
    
    exit(0);
}

if ($isWeb) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Quiz Question Importer</title>';
    echo '<style>body{font-family:Arial,sans-serif;max-width:900px;margin:20px auto;padding:20px;}';
    echo 'textarea{width:100%;height:400px;font-family:monospace;font-size:14px;}';
    echo '.success{color:green;}.error{color:red;}.btn{padding:10px 20px;background:#007bff;color:white;border:none;cursor:pointer;margin:5px;}';
    echo '.btn:hover{background:#0056b3;} pre{background:#f5f5f5;padding:10px;overflow-x:auto;}';
    echo '</style></head><body>';
    echo '<h1>Quiz Question Importer</h1>';
}

// Handle form submission (web only)
if ($isWeb && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['quiz_text']) && isset($_POST['chapter_id'])) {
    $chapterId = (int)$_POST['chapter_id'];
    $quizText = $_POST['quiz_text'];
    
    $questions = parseQuizText($quizText);
    
    if (empty($questions)) {
        echo '<p class="error">No questions found. Check your format.</p>';
    } else {
        $inserted = 0;
        $errors = [];
        
        foreach ($questions as $q) {
            try {
                DB::table('questions')->insert([
                    'chapter_id' => $chapterId,
                    'question_text' => $q['question'],
                    'option_a' => $q['options']['A'] ?? '',
                    'option_b' => $q['options']['B'] ?? '',
                    'option_c' => $q['options']['C'] ?? '',
                    'option_d' => $q['options']['D'] ?? '',
                    'option_e' => $q['options']['E'] ?? null,
                    'correct_answer' => $q['correct'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $inserted++;
            } catch (\Exception $e) {
                $errors[] = "Q{$q['number']}: " . $e->getMessage();
            }
        }
        
        echo "<p class='success'>✓ Inserted {$inserted} questions into chapter {$chapterId}</p>";
        if (!empty($errors)) {
            echo "<p class='error'>Errors:</p><ul>";
            foreach ($errors as $err) echo "<li>{$err}</li>";
            echo "</ul>";
        }
        
        echo "<h3>Parsed Questions Preview:</h3><pre>" . print_r($questions, true) . "</pre>";
    }
}

// Show form
if ($isWeb) {
    // Get chapters for dropdown
    $chapters = DB::table('chapters')
        ->select('id', 'title', 'course_id')
        ->where('is_active', true)
        ->orderBy('course_id')
        ->orderBy('order_index')
        ->get();
    
    echo '<form method="POST">';
    echo '<label><strong>Select Chapter:</strong></label><br>';
    echo '<select name="chapter_id" style="width:100%;padding:10px;margin:10px 0;">';
    
    $currentCourse = null;
    foreach ($chapters as $ch) {
        if ($currentCourse !== $ch->course_id) {
            if ($currentCourse !== null) echo '</optgroup>';
            echo "<optgroup label='Course {$ch->course_id}'>";
            $currentCourse = $ch->course_id;
        }
        $selected = ($chapterId == $ch->id) ? 'selected' : '';
        echo "<option value='{$ch->id}' {$selected}>{$ch->id} - {$ch->title}</option>";
    }
    if ($currentCourse !== null) echo '</optgroup>';
    echo '</select><br><br>';
    
    echo '<label><strong>Paste Quiz Text:</strong> (use *** after correct answer)</label><br>';
    echo '<textarea name="quiz_text" placeholder="Chapter 9 Quiz:Choose the best answer.
1.) _____ is the most widely used drug in our society.
A. Marijuana
B. Alcohol ***
C. Tobacco
D. Heroin

2.) For drivers under the age of ____, the state takes a zero tolerance policy.
A. 16
B. 18
C. 20
D. All of the above ***"></textarea><br><br>';
    
    echo '<button type="submit" class="btn">Import Questions</button>';
    echo '</form>';
    
    echo '<hr><h3>Format Guide:</h3>';
    echo '<pre>1.) Question text here?
A. Option A
B. Option B ***
C. Option C
D. Option D

2.) Next question...
A. Answer ***
B. Wrong
C. Wrong
D. Wrong</pre>';
    echo '<p>The <code>***</code> marks the correct answer.</p>';
    echo '</body></html>';
}

/**
 * Parse quiz text into structured questions array
 */
function parseQuizText($text) {
    $questions = [];
    
    // Normalize line endings and clean up
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    
    // Split by question numbers - supports 1.) 1:) 1. 1: formats
    preg_match_all('/(\d+)\s*[:\.\)]+\s*(.+?)(?=\d+\s*[:\.\)]+|$)/s', $text, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $qNum = (int)$match[1];
        $qBlock = trim($match[2]);
        
        // Find options (A., B., C., D., E.) - supports A.) A:) A. A: formats
        $options = [];
        $correct = null;
        $questionText = '';
        
        // Split question text from options
        if (preg_match('/^(.+?)(?=\n\s*A\s*[:\.\)])/s', $qBlock, $qMatch)) {
            $questionText = trim($qMatch[1]);
        } else {
            // Try inline format: question A. opt B. opt
            if (preg_match('/^(.+?)(?=A\s*[:\.\)])/s', $qBlock, $qMatch)) {
                $questionText = trim($qMatch[1]);
            }
        }
        
        // Extract options A-E - supports A.) A:) A. A: formats
        preg_match_all('/([A-E])\s*[:\.\)]+\s*(.+?)(?=\s*[A-E]\s*[:\.\)]+|$)/s', $qBlock, $optMatches, PREG_SET_ORDER);
        
        foreach ($optMatches as $opt) {
            $letter = $opt[1];
            $optText = trim($opt[2]);
            
            // Check for *** correct answer marker
            if (strpos($optText, '***') !== false) {
                $correct = $letter;
                $optText = trim(str_replace('***', '', $optText));
            }
            
            $options[$letter] = $optText;
        }
        
        if (!empty($questionText) && !empty($options)) {
            $questions[] = [
                'number' => $qNum,
                'question' => $questionText,
                'options' => $options,
                'correct' => $correct,
            ];
        }
    }
    
    return $questions;
}
