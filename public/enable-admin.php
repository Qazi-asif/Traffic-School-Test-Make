<?php
// Quick fix to enable admin_panel module
$config = [
    'db_host' => '127.0.0.1',
    'db_port' => '3306',
    'db_name' => 'nelly-elearning',
    'db_user' => 'root',
    'db_pass' => '',
];

try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    // Create table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_modules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        module_name VARCHAR(255) UNIQUE,
        enabled BOOLEAN DEFAULT TRUE,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by VARCHAR(255) DEFAULT 'system'
    )");
    
    // Enable admin_panel module
    $stmt = $pdo->prepare("INSERT INTO system_modules (module_name, enabled, updated_by) 
                         VALUES ('admin_panel', TRUE, 'quick_fix') 
                         ON DUPLICATE KEY UPDATE enabled = TRUE, updated_by = 'quick_fix'");
    $stmt->execute();
    
    echo "<h2>✅ Admin Panel Module ENABLED</h2>";
    echo "<p>The admin_panel module has been enabled in the database.</p>";
    echo "<p><a href='/dashboard'>Test Dashboard Now</a></p>";
    echo "<p><a href='/test-dashboard.php'>Check Database Status</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}
?>
