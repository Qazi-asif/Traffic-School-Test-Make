<?php

/**
 * EMERGENCY DATABASE FIX RUNNER
 * This script executes the SQL fix for the missing user_course_enrollments table
 */

echo "🔧 EMERGENCY DATABASE FIX - Starting...\n";

// Database configuration from .env
$host = '127.0.0.1';
$port = '3306';
$database = 'nelly-elearning';
$username = 'root';
$password = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database: $database\n";
    
    // Read and execute the SQL file
    $sql = file_get_contents('fix_database_emergency.sql');
    
    if (!$sql) {
        throw new Exception("Could not read SQL file");
    }
    
    echo "🔧 Executing database fix SQL...\n";
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            
            // If it's a SELECT statement, show results
            if (stripos($statement, 'SELECT') === 0) {
                $stmt = $pdo->query($statement);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($results as $row) {
                    foreach ($row as $key => $value) {
                        echo "  $key: $value\n";
                    }
                }
            }
        } catch (Exception $e) {
            // Ignore duplicate entry errors and other non-critical errors
            if (strpos($e->getMessage(), 'Duplicate entry') === false && 
                strpos($e->getMessage(), 'already exists') === false) {
                echo "⚠️  Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "✅ SQL execution completed!\n";
    
    // Verify the fix
    echo "\n🔍 Verifying the fix...\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_course_enrollments");
    $count = $stmt->fetch()['count'];
    echo "✅ Total enrollments: $count\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_course_enrollments WHERE status = 'completed'");
    $completedCount = $stmt->fetch()['count'];
    echo "✅ Completed enrollments: $completedCount\n";
    
    // Test the specific failing query
    echo "\n🧪 Testing the failing query...\n";
    $stmt = $pdo->query("SELECT * FROM user_course_enrollments WHERE user_id = 1 AND status = 'completed'");
    $results = $stmt->fetchAll();
    echo "✅ Query executed successfully! Found " . count($results) . " results\n";
    
    echo "\n🎉 DATABASE FIX COMPLETED SUCCESSFULLY!\n";
    echo "The application should now work properly.\n";
    echo "\nTest URLs:\n";
    echo "- Certificate generation: http://nelly-elearning.test/generate-certificates\n";
    echo "- My certificates: http://nelly-elearning.test/my-certificates\n";
    echo "- Course player: http://nelly-elearning.test/course-player\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>