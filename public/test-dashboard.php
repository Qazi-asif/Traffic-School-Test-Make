<?php
// Simple test to check database connection and module status
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
    
    echo "<h2>Database Connection: SUCCESS</h2>";
    
    // Check if system_modules table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'system_modules'");
    if ($stmt->rowCount() > 0) {
        echo "<h3>system_modules table exists</h3>";
        
        // Get all modules
        $stmt = $pdo->query("SELECT * FROM system_modules ORDER BY module_name");
        $modules = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Module Name</th><th>Enabled</th><th>Updated At</th><th>Updated By</th></tr>";
        
        foreach ($modules as $module) {
            $status = $module['enabled'] ? 'YES' : 'NO';
            $color = $module['enabled'] ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$module['module_name']}</td>";
            echo "<td style='color: {$color}; font-weight: bold;'>{$status}</td>";
            echo "<td>{$module['updated_at']}</td>";
            echo "<td>{$module['updated_by']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check admin_panel specifically
        $stmt = $pdo->prepare("SELECT * FROM system_modules WHERE module_name = 'admin_panel'");
        $stmt->execute();
        $admin_panel = $stmt->fetch();
        
        if ($admin_panel) {
            echo "<h3>Admin Panel Status:</h3>";
            echo "<p><strong>Enabled:</strong> " . ($admin_panel['enabled'] ? 'YES' : 'NO') . "</p>";
            echo "<p><strong>Updated:</strong> {$admin_panel['updated_at']}</p>";
            echo "<p><strong>Updated By:</strong> {$admin_panel['updated_by']}</p>";
        } else {
            echo "<h3>Admin Panel Status: NOT FOUND (defaults to enabled)</h3>";
        }
        
    } else {
        echo "<h3>system_modules table does NOT exist</h3>";
    }
    
} catch (PDOException $e) {
    echo "<h2>Database Connection: FAILED</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Test Links:</h3>";
echo "<a href='/dashboard'>Test Dashboard</a><br>";
echo "<a href='/system-control-panel-standalone.php?token=scp_2025_secure_admin_panel_xyz789'>System Control Panel</a><br>";
?>
