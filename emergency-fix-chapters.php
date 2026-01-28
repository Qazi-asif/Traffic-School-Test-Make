<?php
/**
 * Emergency fix for chapters table
 * Run this PHP script to add missing columns
 */

echo "=== EMERGENCY CHAPTERS TABLE FIX ===\n\n";

try {
    // Database connection using Laravel's .env file
    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) {
        throw new Exception('.env file not found');
    }
    
    $envContent = file_get_contents($envFile);
    
    // Parse database credentials from .env
    preg_match('/DB_HOST=(.+)/', $envContent, $hostMatch);
    preg_match('/DB_DATABASE=(.+)/', $envContent, $dbMatch);
    preg_match('/DB_USERNAME=(.+)/', $envContent, $userMatch);
    preg_match('/DB_PASSWORD=(.*)/', $envContent, $passMatch);
    
    $host = trim($hostMatch[1] ?? '127.0.0.1');
    $database = trim($dbMatch[1] ?? 'nelly_elearning');
    $username = trim($userMatch[1] ?? 'root');
    $password = trim($passMatch[1] ?? '');
    
    echo "Connecting to database: {$database} on {$host}\n";
    
    // Connect to MySQL
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful\n\n";
    
    // Check current table structure
    echo "Checking current chapters table structure...\n";
    $stmt = $pdo->query("DESCRIBE chapters");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current columns: " . implode(', ', $columns) . "\n\n";
    
    // Add missing columns
    $columnsToAdd = [
        'duration' => "ALTER TABLE `chapters` ADD COLUMN `duration` INT(11) NOT NULL DEFAULT 30 AFTER `content`",
        'required_min_time' => "ALTER TABLE `chapters` ADD COLUMN `required_min_time` INT(11) NOT NULL DEFAULT 30 AFTER `duration`",
        'course_table' => "ALTER TABLE `chapters` ADD COLUMN `course_table` VARCHAR(255) NOT NULL DEFAULT 'florida_courses' AFTER `course_id`",
        'order_index' => "ALTER TABLE `chapters` ADD COLUMN `order_index` INT(11) NOT NULL DEFAULT 1 AFTER `course_table`",
        'is_active' => "ALTER TABLE `chapters` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `order_index`"
    ];
    
    foreach ($columnsToAdd as $columnName => $sql) {
        if (!in_array($columnName, $columns)) {
            echo "Adding column: {$columnName}...\n";
            try {
                $pdo->exec($sql);
                echo "✅ Added {$columnName} column\n";
            } catch (Exception $e) {
                echo "⚠️ Could not add {$columnName}: " . $e->getMessage() . "\n";
            }
        } else {
            echo "✅ Column {$columnName} already exists\n";
        }
    }
    
    echo "\n=== FINAL TABLE STRUCTURE ===\n";
    $stmt = $pdo->query("DESCRIBE chapters");
    $finalColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($finalColumns as $column) {
        echo sprintf("%-20s %-15s %-10s %-10s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null'], 
            $column['Default']
        );
    }
    
    echo "\n✅ Chapters table fix completed successfully!\n";
    echo "You can now test your DOCX import functionality.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nAlternative: Run this SQL manually in phpMyAdmin:\n";
    echo file_get_contents(__DIR__ . '/fix-chapters-table.sql');
}

echo "\nPress any key to continue...\n";
if (php_sapi_name() === 'cli') {
    fgets(STDIN);
}