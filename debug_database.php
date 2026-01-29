<?php

echo "=== Database Debug ===\n";

// Try different connection methods
$connections = [
    ['host' => '127.0.0.1', 'db' => 'nelly-elearning', 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'db' => 'nelly-elearning', 'user' => 'root', 'pass' => ''],
    ['host' => '127.0.0.1', 'db' => 'nelly_elearning', 'user' => 'root', 'pass' => ''],
];

foreach ($connections as $i => $config) {
    echo "\nTrying connection " . ($i + 1) . ": {$config['db']}@{$config['host']}\n";
    
    try {
        $pdo = new PDO("mysql:host={$config['host']};dbname={$config['db']}", $config['user'], $config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "✅ Connected successfully!\n";
        
        // Check if chapter_questions table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'chapter_questions'");
        if ($stmt->fetch()) {
            echo "✅ chapter_questions table exists\n";
            
            // Show current structure
            echo "Current structure:\n";
            $stmt = $pdo->query("DESCRIBE chapter_questions");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "  - {$row['Field']} ({$row['Type']})\n";
            }
            
            // Try to add columns
            echo "\nAttempting to add missing columns...\n";
            
            // Add question_type
            try {
                $pdo->exec("ALTER TABLE chapter_questions ADD COLUMN question_type VARCHAR(50) NOT NULL DEFAULT 'multiple_choice'");
                echo "✅ Added question_type column\n";
            } catch (Exception $e) {
                echo "Info: " . $e->getMessage() . "\n";
            }
            
            // Add options
            try {
                $pdo->exec("ALTER TABLE chapter_questions ADD COLUMN options TEXT NULL");
                echo "✅ Added options column\n";
            } catch (Exception $e) {
                echo "Info: " . $e->getMessage() . "\n";
            }
            
            // Show final structure
            echo "\nFinal structure:\n";
            $stmt = $pdo->query("DESCRIBE chapter_questions");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "  - {$row['Field']} ({$row['Type']})\n";
            }
            
            break; // Success, exit loop
            
        } else {
            echo "❌ chapter_questions table does not exist\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Connection failed: " . $e->getMessage() . "\n";
    }
}

?>