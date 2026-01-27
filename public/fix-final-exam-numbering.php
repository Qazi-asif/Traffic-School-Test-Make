<?php

// Web-based fix for final exam question numbering
// Access via: http://your-domain.com/fix-final-exam-numbering.php

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Final Exam Numbering</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .question-fix { background: #f9f9f9; padding: 10px; margin: 10px 0; border-left: 4px solid #007cba; }
        .before { color: #d00; }
        .after { color: #0a0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        .btn { background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; }
        .btn:hover { background: #005a8b; }
    </style>
</head>
<body>
    <h1>Fix Final Exam Question Numbering</h1>
    
    <?php
    
    // Include Laravel bootstrap
    require_once '../vendor/autoload.php';
    $app = require_once '../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    if (isset($_POST['fix_numbering'])) {
        echo '<div class="section">';
        echo '<h2>Fixing Question Numbering...</h2>';
        
        try {
            // Find all final exam questions with numbers at the beginning
            $questionsWithNumbers = \DB::table('final_exam_questions')
                ->where('question_text', 'REGEXP', '^[0-9]+[\\.\\)]+')
                ->get();
            
            if ($questionsWithNumbers->isEmpty()) {
                echo '<div class="success">✅ No questions with numbering issues found</div>';
            } else {
                echo '<div class="info">Found ' . $questionsWithNumbers->count() . ' questions with numbering issues</div>';
                
                $fixed = 0;
                
                foreach ($questionsWithNumbers as $question) {
                    $originalText = $question->question_text;
                    
                    // Remove various numbering formats
                    $cleanedText = preg_replace('/^[0-9]+[\.\)]+\s*/', '', $originalText);
                    $cleanedText = trim($cleanedText);
                    
                    if ($cleanedText !== $originalText && !empty($cleanedText)) {
                        \DB::table('final_exam_questions')
                            ->where('id', $question->id)
                            ->update(['question_text' => $cleanedText]);
                        
                        echo '<div class="question-fix">';
                        echo '<strong>Fixed Question ID ' . $question->id . '</strong><br>';
                        echo '<div class="before">Before: ' . htmlspecialchars(substr($originalText, 0, 100)) . '...</div>';
                        echo '<div class="after">After: ' . htmlspecialchars(substr($cleanedText, 0, 100)) . '...</div>';
                        echo '</div>';
                        
                        $fixed++;
                    }
                }
                
                echo '<div class="success">✅ Fixed ' . $fixed . ' questions</div>';
                echo '<div class="info">The final exam should now show proper sequential numbering (1, 2, 3, etc.)</div>';
            }
            
        } catch (\Exception $e) {
            echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
        }
        
        echo '</div>';
    } else {
        // Show analysis first
        echo '<div class="section">';
        echo '<h2>Analysis</h2>';
        
        try {
            // Find Florida 4-Hour BDI Course
            $floridaCourse = \DB::table('florida_courses')
                ->where('title', 'LIKE', '%4%Hour%')
                ->orWhere('title', 'LIKE', '%BDI%')
                ->first();
            
            if (!$floridaCourse) {
                echo '<div class="error">❌ Florida 4-Hour BDI Course not found</div>';
            } else {
                echo '<div class="success">✅ Found Florida course: ' . $floridaCourse->title . ' (ID: ' . $floridaCourse->id . ')</div>';
                
                // Check questions
                $questionsWithNumbers = \DB::table('final_exam_questions')
                    ->where('course_id', $floridaCourse->id)
                    ->where('question_text', 'REGEXP', '^[0-9]+[\\.\\)]+')
                    ->count();
                
                $totalQuestions = \DB::table('final_exam_questions')
                    ->where('course_id', $floridaCourse->id)
                    ->count();
                
                echo '<div class="info">Total questions: ' . $totalQuestions . '</div>';
                echo '<div class="info">Questions with numbering issues: ' . $questionsWithNumbers . '</div>';
                
                if ($questionsWithNumbers > 0) {
                    echo '<div class="error">❌ ISSUE FOUND: Questions contain original numbering in question_text</div>';
                    
                    // Show sample questions
                    $sampleQuestions = \DB::table('final_exam_questions')
                        ->where('course_id', $floridaCourse->id)
                        ->where('question_text', 'REGEXP', '^[0-9]+[\\.\\)]+')
                        ->limit(3)
                        ->get();
                    
                    echo '<h3>Sample Questions with Issues:</h3>';
                    foreach ($sampleQuestions as $question) {
                        echo '<div class="question-fix">';
                        echo '<strong>Question ID ' . $question->id . ':</strong><br>';
                        echo htmlspecialchars(substr($question->question_text, 0, 150)) . '...';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="success">✅ No numbering issues found</div>';
                }
            }
            
        } catch (\Exception $e) {
            echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
        }
        
        echo '</div>';
        
        // Show fix button if issues found
        if (isset($questionsWithNumbers) && $questionsWithNumbers > 0) {
            echo '<div class="section">';
            echo '<h2>Fix Issues</h2>';
            echo '<form method="POST">';
            echo '<p>Click the button below to automatically remove the numbering from all final exam questions:</p>';
            echo '<button type="submit" name="fix_numbering" class="btn">Fix Question Numbering</button>';
            echo '</form>';
            echo '</div>';
        }
    }
    
    ?>
    
    <div class="section">
        <h2>What This Fix Does</h2>
        <p>This tool removes the original question numbers from the question text, so instead of:</p>
        <div class="before">❌ "229.) You should inspect your vehicle before a long drive..."</div>
        <p>It becomes:</p>
        <div class="after">✅ "You should inspect your vehicle before a long drive..."</div>
        <p>The final exam will then display proper sequential numbering (1, 2, 3, etc.) automatically.</p>
    </div>
    
</body>
</html>