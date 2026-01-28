<?php
/**
 * Check actual chapters table structure
 * This will show us exactly what columns exist
 */

echo "=== CHAPTERS TABLE STRUCTURE CHECK ===\n\n";

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
    
    echo "Connecting to database: {$database} on {$host}\n\n";
    
    // Connect to MySQL
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful\n\n";
    
    // Get table structure
    echo "CHAPTERS TABLE STRUCTURE:\n";
    echo str_repeat("=", 80) . "\n";
    printf("%-20s %-20s %-10s %-15s %-10s\n", "COLUMN", "TYPE", "NULL", "DEFAULT", "EXTRA");
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $pdo->query("DESCRIBE chapters");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $existingColumns = [];
    foreach ($columns as $column) {
        $existingColumns[] = $column['Field'];
        printf("%-20s %-20s %-10s %-15s %-10s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null'], 
            $column['Default'] ?? 'NULL',
            $column['Extra']
        );
    }
    
    echo str_repeat("=", 80) . "\n\n";
    
    // Check for required columns
    $requiredColumns = ['id', 'course_id', 'title', 'content'];
    $optionalColumns = ['duration', 'required_min_time', 'course_table', 'order_index', 'is_active', 'video_url', 'created_at', 'updated_at'];
    
    echo "COLUMN ANALYSIS:\n";
    echo str_repeat("=", 40) . "\n";
    
    echo "✅ REQUIRED COLUMNS:\n";
    foreach ($requiredColumns as $col) {
        $exists = in_array($col, $existingColumns);
        echo ($exists ? "✅" : "❌") . " {$col}\n";
    }
    
    echo "\n📋 OPTIONAL COLUMNS:\n";
    foreach ($optionalColumns as $col) {
        $exists = in_array($col, $existingColumns);
        echo ($exists ? "✅" : "⚠️ ") . " {$col}" . ($exists ? "" : " (missing)") . "\n";
    }
    
    echo "\n🎯 RECOMMENDATION:\n";
    echo "Based on your table structure, I'll create a controller that works with your existing columns.\n";
    
    // Count rows
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chapters");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "\nCurrent chapters in database: {$count}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nPress any key to continue...\n";
if (php_sapi_name() === 'cli') {
    fgets(STDIN);
}