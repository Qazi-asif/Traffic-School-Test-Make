<?php

// Simple database check without Laravel
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly_elearning', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Database Structure Check ===\n";
    
    // Check table structure
    $stmt = $pdo->query("DESCRIBE chapter_questions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Chapter Questions Table Columns:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Default']}\n";
    }
    
    // Count records
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chapter_questions");
    $count = $stmt->fetch()['count'];
    echo "\nTotal questions: {$count}\n";
    
    // Show recent records
    if ($count > 0) {
        echo "\nRecent questions:\n";
        $stmt = $pdo->query("SELECT * FROM chapter_questions ORDER BY created_at DESC LIMIT 5");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID: {$row['id']}, Chapter: {$row['chapter_id']}\n";
            echo "Question: " . substr($row['question_text'], 0, 80) . "...\n";
            echo "Answer: {$row['correct_answer']}\n";
            if (isset($row['points'])) {
                echo "Points: {$row['points']}\n";
            }
            if (isset($row['options'])) {
                echo "Options: " . substr($row['options'], 0, 100) . "...\n";
            }
            echo "---\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>