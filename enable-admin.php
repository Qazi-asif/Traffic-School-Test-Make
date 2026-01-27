<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly-elearning', 'root', '');
    
    // Enable admin_panel
    $stmt = $pdo->prepare("UPDATE system_modules SET enabled = TRUE WHERE module_name = 'admin_panel'");
    $stmt->execute();
    
    echo "Admin panel enabled!\n";
    
    // Verify the change
    $stmt = $pdo->prepare("SELECT enabled FROM system_modules WHERE module_name = 'admin_panel'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    echo "Admin panel status: " . ($result['enabled'] ? 'ENABLED' : 'DISABLED') . "\n";
    
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>