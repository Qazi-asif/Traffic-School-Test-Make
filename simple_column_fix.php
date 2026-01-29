<?php

echo "=== Simple Column Fix for chapter_questions ===\n";

try {
    // Database connection
    $host = '127.0.0.1';
    $database = 'nelly-elearning';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully\n";
    
    // Add question_type column
    try {
        $pdo->exec("ALTER TABLE chapter_questions ADD COLUMN question_type VARCHAR(50) NOT NULL DEFAULT 'multiple_choice' AFTER question_text");
        echo "✅ Added question_type column\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "✅ question_type column already exists\n";
        } else {
            echo "❌ Error adding question_type: " . $e->getMessage() . "\n";
        }
    }
    
    // Add options column
    try {
        $pdo->exec("ALTER TABLE chapter_questions ADD COLUMN options TEXT NULL AFTER question_type");
        echo "✅ Added options column\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "✅ options column already exists\n";
        } else {
            echo "❌ Error adding options: " . $e->getMessage() . "\n";
        }
    }
    
    // Show table structure
    echo "\nFinal table structure:\n";
    $stmt = $pdo->query("DESCRIBE chapter_questions");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['Field']} - {$row['Type']}\n";
    }
    
    echo "\n✅ Column fix completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>