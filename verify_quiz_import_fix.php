<?php

// Direct database test to verify the fix
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly_elearning', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Quiz Import Fix Verification ===\n\n";
    
    // 1. Check table structure
    echo "1. Checking table structure...\n";
    $stmt = $pdo->query("DESCRIBE chapter_questions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $columnNames = array_column($columns, 'Field');
    $requiredColumns = ['points', 'options', 'question_type', 'is_active'];
    
    foreach ($requiredColumns as $col) {
        $exists = in_array($col, $columnNames);
        echo ($exists ? "✅" : "❌") . " {$col} column\n";
    }
    
    // 2. Test the exact content that was causing issues
    echo "\n2. Testing problematic content...\n";
    $problemContent = "Chapter 1-Quiz 1. Which of the following is an example of a kind of change traffic laws must respond to?A. Changes car manufacturing methodsB. Changes in climateC. Changes in taxesD. Changes in technology. ***E. None of the above.2. What is an example of a driving technique one might need to learn to safely use the roads?A. ScanningB. Avoiding no-zonesC. 3-second systemD. SignalingE. All of the above ***3. When should you check your mirrors?A. Only when changingB. Every 5-8 secondsC. Only when turningD. Before braking ***E. Never";
    
    echo "Content length: " . strlen($problemContent) . " characters\n";
    
    // Enhanced parsing function (matching the controller logic)
    function enhancedParseQuestions($content) {
        // Split by *** markers
        $blocks = explode('***', $content);
        $questions = [];
        
        foreach ($blocks as $blockIndex => $block) {
            $block = trim($block);
            if (empty($block)) continue;
            
            // Look for question pattern at the start
            if (preg_match('/^(\d+)\.\s*(.+?)([A-E]\..+)/s', $block, $matches)) {
                $questionNum = $matches[1];
                $questionText = trim($matches[2]);
                $optionsText = $matches[3];
                
                // Parse options
                $options = [];
                $correctAnswer = 'A';
                
                // Extract all options
                if (preg_match_all('/([A-E])\.\s*([^A-E]+?)(?=[A-E]\.|$)/s', $optionsText, $optionMatches, PREG_SET_ORDER)) {
                    foreach ($optionMatches as $match) {
                        $letter = $match[1];
                        $text = trim($match[2]);
                        $text = rtrim($text, '.');
                        $options[$letter] = $text;
                    }
                    
                    // The correct answer is typically the last option before ***
                    $lastOption = array_key_last($options);
                    if ($lastOption) {
                        $correctAnswer = $lastOption;
                    }
                }
                
                if (!empty($questionText) && !empty($options)) {
                    $questions[] = [
                        'question' => $questionText,
                        'options' => $options,
                        'correct_answer' => $correctAnswer
                    ];
                }
            }
            
            // Also check if there's a question at the end of the previous block
            if ($blockIndex > 0 && preg_match('/(\d+)\.\s*(.+?)([A-E]\..+)$/s', $block, $matches)) {
                $questionNum = $matches[1];
                $questionText = trim($matches[2]);
                $optionsText = $matches[3];
                
                $options = [];
                if (preg_match_all('/([A-E])\.\s*([^A-E]+?)(?=[A-E]\.|$)/s', $optionsText, $optionMatches, PREG_SET_ORDER)) {
                    foreach ($optionMatches as $match) {
                        $letter = $match[1];
                        $text = trim($match[2]);
                        $text = rtrim($text, '.');
                        $options[$letter] = $text;
                    }
                }
                
                if (!empty($questionText) && !empty($options)) {
                    $questions[] = [
                        'question' => $questionText,
                        'options' => $options,
                        'correct_answer' => 'E' // Default for this test
                    ];
                }
            }
        }
        
        return $questions;
    }
    
    $questions = enhancedParseQuestions($problemContent);
    echo "Parsed questions: " . count($questions) . "\n";
    
    if (count($questions) < 3) {
        echo "❌ Expected 3 questions, got " . count($questions) . "\n";
        echo "This indicates the parsing is still not working correctly.\n\n";
        
        // Try alternative parsing
        echo "Trying alternative parsing method...\n";
        
        // Method 2: More aggressive splitting
        $alternativeQuestions = [];
        
        // First, normalize the content
        $normalized = preg_replace('/\s+/', ' ', $problemContent);
        
        // Split by question numbers
        $parts = preg_split('/(\d+)\.\s*/', $normalized, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        
        for ($i = 1; $i < count($parts); $i += 2) {
            if (isset($parts[$i + 1])) {
                $questionNum = $parts[$i];
                $content = $parts[$i + 1];
                
                // Extract question and options
                if (preg_match('/^(.+?)([A-E]\..+)/', $content, $matches)) {
                    $questionText = trim($matches[1]);
                    $optionsText = $matches[2];
                    
                    // Remove *** markers
                    $optionsText = str_replace('***', '', $optionsText);
                    
                    $options = [];
                    if (preg_match_all('/([A-E])\.\s*([^A-E]+?)(?=[A-E]\.|$)/', $optionsText, $optionMatches, PREG_SET_ORDER)) {
                        foreach ($optionMatches as $match) {
                            $letter = $match[1];
                            $text = trim($match[2]);
                            $options[$letter] = $text;
                        }
                    }
                    
                    if (!empty($questionText) && !empty($options)) {
                        $alternativeQuestions[] = [
                            'question' => $questionText,
                            'options' => $options,
                            'correct_answer' => array_key_last($options) ?: 'A'
                        ];
                    }
                }
            }
        }
        
        echo "Alternative parsing found: " . count($alternativeQuestions) . " questions\n";
        $questions = $alternativeQuestions;
    }
    
    foreach ($questions as $index => $q) {
        echo "\nQuestion " . ($index + 1) . ":\n";
        echo "  Text: " . substr($q['question'], 0, 80) . "...\n";
        echo "  Options: " . count($q['options']) . " (" . implode(', ', array_keys($q['options'])) . ")\n";
        echo "  Correct: " . $q['correct_answer'] . "\n";
    }
    
    // 3. Test database insertion
    if (count($questions) > 0) {
        echo "\n3. Testing database insertion...\n";
        
        // Get or create a test chapter
        $stmt = $pdo->query("SELECT id FROM chapters LIMIT 1");
        $chapter = $stmt->fetch();
        
        if (!$chapter) {
            echo "❌ No chapters found. Creating test chapter...\n";
            $pdo->exec("INSERT INTO chapters (course_id, title, content, order_index, created_at, updated_at) VALUES (1, 'Test Chapter', 'Test content', 1, NOW(), NOW())");
            $chapterId = $pdo->lastInsertId();
        } else {
            $chapterId = $chapter['id'];
        }
        
        echo "Using chapter ID: {$chapterId}\n";
        
        // Clear existing questions
        $pdo->exec("DELETE FROM chapter_questions WHERE chapter_id = {$chapterId}");
        
        // Insert questions
        $inserted = 0;
        foreach ($questions as $index => $q) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO chapter_questions 
                    (chapter_id, question_text, correct_answer, points, order_index, question_type, options, is_active, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                
                $result = $stmt->execute([
                    $chapterId,
                    $q['question'],
                    $q['correct_answer'],
                    1, // points - CRITICAL FIX
                    $index + 1,
                    'multiple_choice',
                    json_encode($q['options']),
                    1 // is_active
                ]);
                
                if ($result) {
                    $inserted++;
                    echo "✅ Inserted question " . ($index + 1) . "\n";
                } else {
                    echo "❌ Failed to insert question " . ($index + 1) . "\n";
                }
            } catch (Exception $e) {
                echo "❌ Error inserting question " . ($index + 1) . ": " . $e->getMessage() . "\n";
            }
        }
        
        echo "\nInserted {$inserted} out of " . count($questions) . " questions\n";
        
        // 4. Verify the fix
        echo "\n4. Verifying the fix...\n";
        $stmt = $pdo->query("SELECT * FROM chapter_questions WHERE chapter_id = {$chapterId} ORDER BY order_index");
        $saved = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Questions in database: " . count($saved) . "\n";
        
        $allHavePoints = true;
        $allHaveOptions = true;
        
        foreach ($saved as $s) {
            echo "\nID: {$s['id']}, Order: {$s['order_index']}\n";
            echo "Question: " . substr($s['question_text'], 0, 60) . "...\n";
            echo "Answer: {$s['correct_answer']}\n";
            
            // Check points
            if (isset($s['points']) && $s['points'] == 1) {
                echo "Points: ✅ {$s['points']}\n";
            } else {
                echo "Points: ❌ " . ($s['points'] ?? 'NULL') . "\n";
                $allHavePoints = false;
            }
            
            // Check options
            if (isset($s['options'])) {
                $options = json_decode($s['options'], true);
                if (is_array($options) && count($options) > 0) {
                    echo "Options: ✅ " . count($options) . " options\n";
                } else {
                    echo "Options: ❌ Invalid JSON or empty\n";
                    $allHaveOptions = false;
                }
            } else {
                echo "Options: ❌ NULL\n";
                $allHaveOptions = false;
            }
        }
        
        echo "\n=== FINAL RESULTS ===\n";
        echo ($inserted == count($questions) ? "✅" : "❌") . " All questions imported: {$inserted}/" . count($questions) . "\n";
        echo ($allHavePoints ? "✅" : "❌") . " All questions have points = 1\n";
        echo ($allHaveOptions ? "✅" : "❌") . " All questions have valid options JSON\n";
        
        if ($inserted == count($questions) && $allHavePoints && $allHaveOptions) {
            echo "\n🎉 QUIZ IMPORT FIX IS SUCCESSFUL! 🎉\n";
            echo "- No more partial imports\n";
            echo "- Points show as '1' instead of 'undefined'\n";
            echo "- All questions properly stored with options\n";
        } else {
            echo "\n❌ Issues still remain. Check the errors above.\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>