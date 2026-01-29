<?php

require_once 'vendor/autoload.php';

echo "=== Fixing chapter_questions Table Columns ===\n\n";

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
    
    echo "Connecting to MySQL database: {$database}@{$host}\n";
    
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'chapter_questions'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "❌ Table 'chapter_questions' does not exist!\n";
        echo "Creating table...\n";
        
        $createTableSQL = "
        CREATE TABLE `chapter_questions` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `chapter_id` bigint(20) unsigned NOT NULL,
            `question_text` text NOT NULL,
            `question_type` varchar(50) NOT NULL DEFAULT 'multiple_choice',
            `options` json DEFAULT NULL,
            `correct_answer` varchar(10) DEFAULT NULL,
            `explanation` text DEFAULT NULL,
            `points` int(11) NOT NULL DEFAULT 1,
            `order_index` int(11) NOT NULL DEFAULT 0,
            `quiz_set` int(11) NOT NULL DEFAULT 1,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `chapter_questions_chapter_id_foreign` (`chapter_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createTableSQL);
        echo "✅ Table created successfully\n";
        
    } else {
        echo "✅ Table 'chapter_questions' exists\n";
        
        // Check current structure
        $stmt = $pdo->query("DESCRIBE chapter_questions");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        echo "Current columns: " . implode(', ', $columnNames) . "\n\n";
        
        // Add missing columns
        $columnsToAdd = [
            'question_type' => "ALTER TABLE chapter_questions ADD COLUMN question_type VARCHAR(50) NOT NULL DEFAULT 'multiple_choice' AFTER question_text",
            'options' => "ALTER TABLE chapter_questions ADD COLUMN options JSON NULL AFTER question_type"
        ];
        
        foreach ($columnsToAdd as $column => $sql) {
            if (!in_array($column, $columnNames)) {
                echo "Adding column: {$column}\n";
                try {
                    $pdo->exec($sql);
                    echo "✅ Column {$column} added successfully\n";
                } catch (Exception $e) {
                    echo "❌ Error adding column {$column}: " . $e->getMessage() . "\n";
                }
            } else {
                echo "✅ Column {$column} already exists\n";
            }
        }
    }
    
    // Verify final structure
    echo "\nFinal table structure:\n";
    $stmt = $pdo->query("DESCRIBE chapter_questions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']}) " . 
             ($column['Null'] === 'NO' ? "NOT NULL" : "NULL") . 
             ($column['Default'] ? " DEFAULT {$column['Default']}" : "") . "\n";
    }
    
    // Test insert to verify it works
    echo "\nTesting insert functionality...\n";
    try {
        $testSQL = "INSERT INTO chapter_questions (chapter_id, question_text, question_type, options, correct_answer, points, order_index, quiz_set, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $pdo->prepare($testSQL);
        $stmt->execute([
            1, // chapter_id
            'Test question for verification',
            'multiple_choice',
            json_encode(['A' => 'Option A', 'B' => 'Option B', 'C' => 'Option C', 'D' => 'Option D']),
            'A',
            1,
            1,
            1,
            1
        ]);
        
        $insertId = $pdo->lastInsertId();
        echo "✅ Test insert successful (ID: {$insertId})\n";
        
        // Clean up test record
        $pdo->exec("DELETE FROM chapter_questions WHERE id = {$insertId}");
        echo "✅ Test record cleaned up\n";
        
    } catch (Exception $e) {
        echo "❌ Test insert failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 chapter_questions table is ready for quiz import system!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>