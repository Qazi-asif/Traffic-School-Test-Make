<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=nelly-elearning", "root", "");
    
    echo "Checking tables...\n";
    
    $tables = [
        'user_course_enrollments',
        'chapters', 
        'questions',
        'missouri_courses',
        'texas_courses', 
        'delaware_courses',
        'nevada_courses'
    ];
    
    foreach ($tables as $table) {
        $exists = $pdo->query("SHOW TABLES LIKE '{$table}'")->rowCount() > 0;
        if ($exists) {
            $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
            echo "{$table}: EXISTS ({$count} records)\n";
        } else {
            echo "{$table}: MISSING\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>