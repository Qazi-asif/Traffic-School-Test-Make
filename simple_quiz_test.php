<?php

// Simple database test without Laravel
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly_elearning', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Simple Quiz Import Test ===\n\n";
    
    // Check table structure
    $stmt = $pdo->query("DESCRIBE chapter_questions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Table columns:\n";
    $columnNames = [];
    foreach ($columns as $column) {
        $columnNames[] = $column['Field'];
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
    $hasPoints = in_array('points', $columnNames);
    $hasOptions = in_array('options', $columnNames);
    $hasQuestionType = in_array('question_type', $columnNames);
    
    echo "\nColumn check:\n";
    echo "- points: " . ($hasPoints ? "EXISTS" : "MISSING") . "\n";
    echo "- options: " . ($hasOptions ? "EXISTS" : "MISSING") . "\n";
    echo "- question_type: " . ($hasQuestionType ? "EXISTS" : "MISSING") . "\n\n";
    
    // Add missing columns if needed
    if (!$hasPoints) {
        echo "Adding points column...\n";
        $pdo->exec("ALTER TABLE chapter_questions ADD COLUMN points INT DEFAULT 1");
    }
    
    if (!$hasOptions) {
        echo "Adding options column...\n";
        $pdo->exec("ALTER TABLE chapter_questions ADD COLUMN options JSON");
    }
    
    if (!$hasQuestionType) {
        echo "Adding question_type column...\n";
        $pdo->exec("ALTER TABLE chapter_questions ADD COLUMN question_type VARCHAR(50) DEFAULT 'multiple_choice'");
    }
    
    // Test parsing the problematic content
    $testContent = "Chapter 1-Quiz 1. Which of the following is an example of a kind of change traffic laws must respond to?A. Changes car manufacturing methodsB. Changes in climateC. Changes in taxesD. Changes in technology. ***E. None of the above.2. What is an example of a driving technique one might need to learn to safely use the roads?A. ScanningB. Avoiding no-zonesC. 3-second systemD. SignalingE. All of the above ***3. When should you check your mirrors?A. Only when changingB. Every 5-8 secondsC. Only when turningD. Before braking ***E. Never";
    
    echo "Original content length: " . strlen($testContent) . "\n";
    
    // Simple parsing logic
    function parseQuestions($content) {
        $questions = [];
        
        // Split by *** markers first
        $blocks = explode('***', $content);
        
        foreach ($blocks as $block) {
            $block = trim($block);
            if (empty($block)) continue;
            
            // Look for question pattern
            if (preg_match('/(\d+)\.\s*(.+?)([A-E]\..+)/s', $block, $matches)) {
                $questionText = trim($matches[2]);
                $optionsText = $matches[3];
                
                // Parse options
                $options = [];
                $correctAnswer = 'A';
                
                if (preg_match_all('/([A-E])\.\s*([^A-E]+?)(?=[A-E]\.|$)/s', $optionsText, $optionMatches, PREG_SET_ORDER)) {
                    foreach ($optionMatches as $match) {
                        $letter = $match[1];
                        $text = trim($match[2]);
                        $text = rtrim($text, '.');
                        $options[$letter] = $text;
                    }
                }
                
                // Find correct answer (last option before ***)
                $lastOption = array_key_last($options);
                if ($lastOption) {
                    $correctAnswer = $lastOption;
                }
                
                if (!empty($questionText) && !empty($options)) {
                    $questions[] = [
                        'question' => $questionText,
                        'options' => $options,
                        'correct_answer' => $correctAnswer
                    ];
                }
            }
        }
        
        return $questions;
    }
    
    $questions = parseQuestions($testContent);
    echo "Parsed questions: " . count($questions) . "\n\n";
    
    foreach ($questions as $index => $q) {
        echo "Question " . ($index + 1) . ":\n";
        echo "Text: " . substr($q['question'], 0, 80) . "...\n";
        echo "Options: " . count($q['options']) . " (" . implode(', ', array_keys($q['options'])) . ")\n";
        echo "Correct: " . $q['correct_answer'] . "\n";
        echo "---\n";
    }
    
    // Test database insert
    if (count($questions) > 0) {
        echo "\nTesting database insert...\n";
        
        // Get a chapter to use
        $stmt = $pdo->query("SELECT id FROM chapters LIMIT 1");
        $chapter = $stmt->fetch();
        
        if ($chapter) {
            $chapterId = $chapter['id'];
            echo "Using chapter ID: {$chapterId}\n";
            
            // Clear existing
            $pdo->exec("DELETE FROM chapter_questions WHERE chapter_id = {$chapterId}");
            
            // Insert questions
            $inserted = 0;
            foreach ($questions as $index => $q) {
                $stmt = $pdo->prepare("
                    INSERT INTO chapter_questions 
                    (chapter_id, question_text, correct_answer, points, order_index, question_type, options, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                
                $result = $stmt->execute([
                    $chapterId,
                    $q['question'],
                    $q['correct_answer'],
                    1, // points
                    $index + 1,
                    'multiple_choice',
                    json_encode($q['options'])
                ]);
                
                if ($result) {
                    $inserted++;
                    echo "✅ Inserted question " . ($index + 1) . "\n";
                } else {
                    echo "❌ Failed to insert question " . ($index + 1) . "\n";
                }
            }
            
            echo "\nInserted {$inserted} out of " . count($questions) . " questions\n";
            
            // Verify
            $stmt = $pdo->query("SELECT * FROM chapter_questions WHERE chapter_id = {$chapterId} ORDER BY order_index");
            $saved = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "\nVerification - Saved questions: " . count($saved) . "\n";
            foreach ($saved as $s) {
                echo "ID: {$s['id']}, Points: {$s['points']}, Answer: {$s['correct_answer']}\n";
                echo "Question: " . substr($s['question_text'], 0, 60) . "...\n";
                $options = json_decode($s['options'], true);
                echo "Options: " . (is_array($options) ? count($options) : 'Invalid') . "\n";
                echo "---\n";
            }
            
        } else {
            echo "❌ No chapters found in database\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>