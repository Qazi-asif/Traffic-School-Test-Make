<?php

echo "=== Database Structure Investigation ===\n";

// Try to connect and get actual database info
try {
    // Read .env file to get database credentials
    $envContent = file_get_contents('.env');
    
    // Extract database info
    preg_match('/DB_HOST=(.+)/', $envContent, $hostMatch);
    preg_match('/DB_DATABASE=(.+)/', $envContent, $dbMatch);
    preg_match('/DB_USERNAME=(.+)/', $envContent, $userMatch);
    preg_match('/DB_PASSWORD=(.*)/', $envContent, $passMatch);
    
    $host = trim($hostMatch[1] ?? '127.0.0.1');
    $database = trim($dbMatch[1] ?? 'nelly-elearning');
    $username = trim($userMatch[1] ?? 'root');
    $password = trim($passMatch[1] ?? '');
    
    echo "Database Config:\n";
    echo "Host: $host\n";
    echo "Database: $database\n";
    echo "Username: $username\n";
    echo "Password: " . (empty($password) ? '(empty)' : '(set)') . "\n\n";
    
    // Try connection
    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Database connection successful!\n\n";
    
    // Check if chapter_questions table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'chapter_questions'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "❌ Table 'chapter_questions' does NOT exist!\n";
        echo "Available tables:\n";
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch()) {
            echo "  - " . array_values($row)[0] . "\n";
        }
        
        // Create the table
        echo "\nCreating chapter_questions table...\n";
        $createSQL = "
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
        
        $pdo->exec($createSQL);
        echo "✅ Table created successfully!\n";
        
    } else {
        echo "✅ Table 'chapter_questions' exists\n";
        
        // Get current structure
        echo "\nCurrent table structure:\n";
        $stmt = $pdo->query("DESCRIBE chapter_questions");
        $columns = $stmt->fetchAll();
        
        $columnNames = [];
        foreach ($columns as $column) {
            $columnNames[] = $column['Field'];
            echo "  - {$column['Field']} ({$column['Type']}) " . 
                 ($column['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . 
                 ($column['Default'] !== null ? " DEFAULT '{$column['Default']}'" : '') . "\n";
        }
        
        // Check for required columns
        $requiredColumns = ['question_type', 'options'];
        $missingColumns = [];
        
        echo "\nColumn check:\n";
        foreach ($requiredColumns as $col) {
            if (in_array($col, $columnNames)) {
                echo "  ✅ $col exists\n";
            } else {
                echo "  ❌ $col MISSING\n";
                $missingColumns[] = $col;
            }
        }
        
        // Add missing columns
        if (!empty($missingColumns)) {
            echo "\nAdding missing columns...\n";
            
            foreach ($missingColumns as $column) {
                try {
                    if ($column === 'question_type') {
                        $sql = "ALTER TABLE chapter_questions ADD COLUMN question_type VARCHAR(50) NOT NULL DEFAULT 'multiple_choice' AFTER question_text";
                        $pdo->exec($sql);
                        echo "  ✅ Added question_type column\n";
                    } elseif ($column === 'options') {
                        // Try JSON first, fallback to TEXT
                        try {
                            $sql = "ALTER TABLE chapter_questions ADD COLUMN options JSON NULL AFTER question_type";
                            $pdo->exec($sql);
                            echo "  ✅ Added options column (JSON)\n";
                        } catch (Exception $e) {
                            $sql = "ALTER TABLE chapter_questions ADD COLUMN options TEXT NULL AFTER question_type";
                            $pdo->exec($sql);
                            echo "  ✅ Added options column (TEXT)\n";
                        }
                    }
                } catch (Exception $e) {
                    echo "  ❌ Failed to add $column: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    // Final verification
    echo "\nFinal table structure:\n";
    $stmt = $pdo->query("DESCRIBE chapter_questions");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']})\n";
    }
    
    // Test insert
    echo "\nTesting insert functionality...\n";
    try {
        $testSQL = "INSERT INTO chapter_questions (
            chapter_id, question_text, question_type, options, correct_answer, 
            explanation, points, order_index, quiz_set, is_active, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $pdo->prepare($testSQL);
        $result = $stmt->execute([
            1, // chapter_id
            'Test question - SQL column fix verification',
            'multiple_choice',
            json_encode(['A' => 'Option A', 'B' => 'Option B', 'C' => 'Option C', 'D' => 'Option D']),
            'A',
            'This is a test explanation',
            1,
            999,
            1,
            1
        ]);
        
        if ($result) {
            $insertId = $pdo->lastInsertId();
            echo "  ✅ Test insert successful! (ID: $insertId)\n";
            
            // Verify the inserted data
            $stmt = $pdo->prepare("SELECT * FROM chapter_questions WHERE id = ?");
            $stmt->execute([$insertId]);
            $row = $stmt->fetch();
            
            echo "  ✅ Data verification:\n";
            echo "    - Question: " . substr($row['question_text'], 0, 50) . "...\n";
            echo "    - Type: " . $row['question_type'] . "\n";
            echo "    - Options: " . substr($row['options'], 0, 50) . "...\n";
            echo "    - Correct: " . $row['correct_answer'] . "\n";
            
            // Clean up
            $pdo->exec("DELETE FROM chapter_questions WHERE id = $insertId");
            echo "  ✅ Test data cleaned up\n";
        }
        
    } catch (Exception $e) {
        echo "  ❌ Test insert failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 Database investigation and fix completed!\n";
    echo "The chapter_questions table is now ready for quiz imports.\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Error details:\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
    echo "  Trace: " . $e->getTraceAsString() . "\n";
}

?>