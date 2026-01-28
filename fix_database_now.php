<?php
echo "Creating database table...\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly-elearning', 'root', '');
    echo "Connected to database\n";
    
    $pdo->exec('DROP TABLE IF EXISTS user_course_enrollments');
    echo "Dropped existing table\n";
    
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
    echo "Table created successfully\n";
    
    $pdo->exec("INSERT INTO user_course_enrollments (user_id, course_id, status, progress_percentage, completed_at, certificate_generated_at, certificate_number, certificate_path, created_at, updated_at) VALUES (1, 1, 'completed', 100.00, NOW(), NOW(), 'CERT-2026-000001', 'certificates/cert-1.html', NOW(), NOW())");
    
    $count = $pdo->query('SELECT COUNT(*) FROM user_course_enrollments')->fetchColumn();
    echo "Test data inserted: $count records\n";
    
    echo "SUCCESS: Database table created and ready!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>