<?php

require_once 'vendor/autoload.php';

echo "=== Checking chapter_questions Table Structure ===\n\n";

try {
    // Check if we're using SQLite or MySQL
    $envFile = file_get_contents('.env');
    $dbConnection = 'sqlite'; // default
    
    if (preg_match('/DB_CONNECTION=(.+)/', $envFile, $matches)) {
        $dbConnection = trim($matches[1]);
    }
    
    echo "Database Connection: {$dbConnection}\n\n";
    
    if ($dbConnection === 'sqlite') {
        $pdo = new PDO('sqlite:database/database.sqlite');
    } else {
        // Parse MySQL connection details from .env
        preg_match('/DB_HOST=(.+)/', $envFile, $hostMatch);
        preg_match('/DB_DATABASE=(.+)/', $envFile, $dbMatch);
        preg_match('/DB_USERNAME=(.+)/', $envFile, $userMatch);
        preg_match('/DB_PASSWORD=(.+)/', $envFile, $passMatch);
        
        $host = trim($hostMatch[1] ?? 'localhost');
        $database = trim($dbMatch[1] ?? 'laravel');
        $username = trim($userMatch[1] ?? 'root');
        $password = trim($passMatch[1] ?? '');
        
        $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if table exists
    if ($dbConnection === 'sqlite') {
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='chapter_questions'");
    } else {
        $stmt = $pdo->query("SHOW TABLES LIKE 'chapter_questions'");
    }
    
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "❌ Table 'chapter_questions' does not exist!\n";
        echo "Available tables:\n";
        
        if ($dbConnection === 'sqlite') {
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
        } else {
            $stmt = $pdo->query("SHOW TABLES");
        }
        
        while ($row = $stmt->fetch()) {
            echo "  - " . (is_array($row) ? $row[0] : $row['name']) . "\n";
        }
        exit;
    }
    
    echo "✅ Table 'chapter_questions' exists\n\n";
    
    // Get table structure
    if ($dbConnection === 'sqlite') {
        $stmt = $pdo->query("PRAGMA table_info(chapter_questions)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Table Structure:\n";
        foreach ($columns as $column) {
            echo "  - {$column['name']} ({$column['type']}) " . 
                 ($column['notnull'] ? "NOT NULL" : "NULL") . 
                 ($column['dflt_value'] ? " DEFAULT {$column['dflt_value']}" : "") . "\n";
        }
    } else {
        $stmt = $pdo->query("DESCRIBE chapter_questions");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Table Structure:\n";
        foreach ($columns as $column) {
            echo "  - {$column['Field']} ({$column['Type']}) " . 
                 ($column['Null'] === 'NO' ? "NOT NULL" : "NULL") . 
                 ($column['Default'] ? " DEFAULT {$column['Default']}" : "") . "\n";
        }
    }
    
    // Check for required columns
    $columnNames = array_column($columns, $dbConnection === 'sqlite' ? 'name' : 'Field');
    $requiredColumns = ['question_type', 'options'];
    
    echo "\nRequired Columns Check:\n";
    foreach ($requiredColumns as $col) {
        $exists = in_array($col, $columnNames);
        echo ($exists ? "✅" : "❌") . " {$col}\n";
    }
    
    // Count existing records
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chapter_questions");
    $count = $stmt->fetch()['count'];
    echo "\nExisting records: {$count}\n";
    
    if ($count > 0) {
        echo "\nSample records:\n";
        $stmt = $pdo->query("SELECT * FROM chapter_questions LIMIT 3");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  ID: {$row['id']}, Chapter: {$row['chapter_id']}, Question: " . substr($row['question_text'], 0, 50) . "...\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>