<?php

echo "=== Emergency Fix: Adding Missing Columns to chapter_questions ===\n\n";

try {
    // Get database configuration from .env
    $envFile = file_get_contents('.env');
    
    preg_match('/DB_HOST=(.+)/', $envFile, $hostMatch);
    preg_match('/DB_DATABASE=(.+)/', $envFile, $dbMatch);
    preg_match('/DB_USERNAME=(.+)/', $envFile, $userMatch);
    preg_match('/DB_PASSWORD=(.*)/', $envFile, $passMatch);
    
    $host = trim($hostMatch[1] ?? 'localhost');
    $database = trim($dbMatch[1] ?? 'laravel');
    $username = trim($userMatch[1] ?? 'root');
    $password = trim($passMatch[1] ?? '');
    
    echo "Connecting to: {$database}@{$host}\n";
    
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check current table structure
    echo "Checking current table structure...\n";
    $stmt = $pdo->query("DESCRIBE chapter_questions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    echo "Current columns: " . implode(', ', $columnNames) . "\n\n";
    
    // Add missing columns one by one
    $columnsToAdd = [
        'question_type' => [
            'sql' => "ALTER TABLE chapter_questions ADD COLUMN question_type VARCHAR(50) NOT NULL DEFAULT 'multiple_choice' AFTER question_text",
            'check' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'chapter_questions' AND COLUMN_NAME = 'question_type' AND TABLE_SCHEMA = '{$database}'"
        ],
        'options' => [
            'sql' => "ALTER TABLE chapter_questions ADD COLUMN options JSON NULL AFTER question_type",
            'check' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'chapter_questions' AND COLUMN_NAME = 'options' AND TABLE_SCHEMA = '{$database}'"
        ]
    ];
    
    foreach ($columnsToAdd as $columnName => $config) {
        echo "Processing column: {$columnName}\n";
        
        // Check if column exists
        $stmt = $pdo->query($config['check']);
        $exists = $stmt->fetch();
        
        if (!$exists) {
            echo "  Adding column {$columnName}...\n";
            try {
                $pdo->exec($config['sql']);
                echo "  ✅ Column {$columnName} added successfully\n";
            } catch (Exception $e) {
                echo "  ❌ Error adding column {$columnName}: " . $e->getMessage() . "\n";
                
                // Try alternative approach
                if ($columnName === 'options') {
                    echo "  Trying alternative JSON approach...\n";
                    try {
                        $pdo->exec("ALTER TABLE chapter_questions ADD COLUMN options TEXT NULL AFTER question_type");
                        echo "  ✅ Column {$columnName} added as TEXT (JSON compatible)\n";
                    } catch (Exception $e2) {
                        echo "  ❌ Alternative approach failed: " . $e2->getMessage() . "\n";
                    }
                }
            }
        } else {
            echo "  ✅ Column {$columnName} already exists\n";
        }
    }
    
    // Verify final structure
    echo "\nVerifying final table structure...\n";
    $stmt = $pdo->query("DESCRIBE chapter_questions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $requiredColumns = ['question_type', 'options'];
    $finalColumnNames = array_column($columns, 'Field');
    
    echo "Final columns: " . implode(', ', $finalColumnNames) . "\n\n";
    
    foreach ($requiredColumns as $col) {
        $exists = in_array($col, $finalColumnNames);
        echo ($exists ? "✅" : "❌") . " Required column: {$col}\n";
    }
    
    // Test insert to make sure it works
    echo "\nTesting insert functionality...\n";
    try {
        $testSQL = "INSERT INTO chapter_questions (chapter_id, question_text, question_type, options, correct_answer, points, order_index, quiz_set, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $pdo->prepare($testSQL);
        $stmt->execute([
            1, // chapter_id (assuming chapter 1 exists)
            'Emergency test question',
            'multiple_choice',
            json_encode(['A' => 'Test A', 'B' => 'Test B', 'C' => 'Test C', 'D' => 'Test D']),
            'A',
            1,
            999, // high order to avoid conflicts
            1,
            1
        ]);
        
        $insertId = $pdo->lastInsertId();
        echo "✅ Test insert successful (ID: {$insertId})\n";
        
        // Verify the data
        $stmt = $pdo->prepare("SELECT * FROM chapter_questions WHERE id = ?");
        $stmt->execute([$insertId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "✅ Data verification:\n";
        echo "  - Question Type: " . $result['question_type'] . "\n";
        echo "  - Options: " . $result['options'] . "\n";
        echo "  - Correct Answer: " . $result['correct_answer'] . "\n";
        
        // Clean up test record
        $pdo->exec("DELETE FROM chapter_questions WHERE id = {$insertId}");
        echo "✅ Test record cleaned up\n";
        
    } catch (Exception $e) {
        echo "❌ Test insert failed: " . $e->getMessage() . "\n";
        
        // Show detailed error info
        echo "Error details:\n";
        echo "  Code: " . $e->getCode() . "\n";
        echo "  Message: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 Emergency fix completed!\n";
    echo "The quiz import system should now work without column errors.\n";
    
} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

?>