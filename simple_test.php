<?php
echo "PHP is working!\n";
echo "Testing database connection...\n";

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=nelly-elearning", "root", "");
    echo "Database connected successfully!\n";
    
    // Check florida_courses table
    $count = $pdo->query("SELECT COUNT(*) FROM florida_courses")->fetchColumn();
    echo "Florida courses count: " . $count . "\n";
    
    // Check if new tables exist
    $tables = ['missouri_courses', 'texas_courses', 'delaware_courses', 'nevada_courses'];
    foreach ($tables as $table) {
        $exists = $pdo->query("SHOW TABLES LIKE '{$table}'")->rowCount() > 0;
        echo "{$table}: " . ($exists ? "EXISTS" : "NOT FOUND") . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>