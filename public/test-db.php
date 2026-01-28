<?php
echo "<h1>Database Connection Test</h1>";

try {
    // Test direct PDO connection
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly-elearning', 'root', '');
    echo "<p>✅ Direct PDO connection successful</p>";
    
    // Show tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h2>Tables in database:</h2><ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Check if user_course_enrollments exists
    if (in_array('user_course_enrollments', $tables)) {
        $count = $pdo->query("SELECT COUNT(*) FROM user_course_enrollments")->fetchColumn();
        echo "<p>✅ user_course_enrollments table exists with $count records</p>";
    } else {
        echo "<p>❌ user_course_enrollments table does NOT exist</p>";
        
        // Create the table
        echo "<p>Creating table...</p>";
        $sql = "CREATE TABLE user_course_enrollments (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            course_id bigint(20) unsigned NOT NULL,
            course_table varchar(255) DEFAULT 'florida_courses',
            payment_status enum('pending','paid','failed','refunded') DEFAULT 'pending',
            status enum('pending','active','completed','expired','cancelled') DEFAULT 'pending',
            progress_percentage decimal(5,2) DEFAULT 0.00,
            enrolled_at timestamp NULL DEFAULT NULL,
            completed_at timestamp NULL DEFAULT NULL,
            certificate_generated_at timestamp NULL DEFAULT NULL,
            certificate_number varchar(255) DEFAULT NULL,
            certificate_path varchar(500) DEFAULT NULL,
            created_at timestamp NULL DEFAULT NULL,
            updated_at timestamp NULL DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $pdo->exec($sql);
        echo "<p>✅ Table created</p>";
        
        // Insert test data
        $pdo->exec("INSERT INTO user_course_enrollments (user_id, course_id, status, progress_percentage, completed_at, certificate_generated_at, certificate_number, certificate_path, created_at, updated_at) VALUES (1, 1, 'completed', 100.00, NOW(), NOW(), 'CERT-2026-000001', 'certificates/cert-1.html', NOW(), NOW())");
        echo "<p>✅ Test data inserted</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

// Test Laravel connection
echo "<h2>Laravel Connection Test</h2>";
try {
    require_once '../vendor/autoload.php';
    $app = require_once '../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $count = DB::table('user_course_enrollments')->count();
    echo "<p>✅ Laravel can see $count enrollments</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Laravel Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='/generate-certificates'>Test Generate Certificates Page</a></p>";
?>