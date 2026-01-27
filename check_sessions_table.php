<?php

// Check if sessions table exists and is working
echo "=== CHECKING SESSIONS TABLE ===\n\n";

try {
    // Database connection details from .env
    $host = 'localhost';
    $dbname = 'nelly-elearning';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if sessions table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'sessions'");
    $tableExists = $stmt->rowCount() > 0;
    
    echo "1. SESSIONS TABLE CHECK:\n";
    echo "Sessions table exists: " . ($tableExists ? 'YES' : 'NO') . "\n\n";
    
    if ($tableExists) {
        // Check table structure
        echo "2. TABLE STRUCTURE:\n";
        $stmt = $pdo->query("DESCRIBE sessions");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']})\n";
        }
        
        // Check for records
        echo "\n3. RECORD COUNT:\n";
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM sessions");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "Total session records: {$count}\n\n";
        
        if ($count > 0) {
            echo "4. RECENT SESSIONS:\n";
            $stmt = $pdo->query("SELECT id, user_id, last_activity FROM sessions ORDER BY last_activity DESC LIMIT 5");
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($sessions as $session) {
                $lastActivity = date('Y-m-d H:i:s', $session['last_activity']);
                echo "ID: {$session['id']}, User: {$session['user_id']}, Last Activity: {$lastActivity}\n";
            }
        }
    } else {
        echo "❌ Sessions table is missing! This will cause 'Page Expired' errors.\n";
        echo "Run: php artisan session:table\n";
        echo "Then: php artisan migrate\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";