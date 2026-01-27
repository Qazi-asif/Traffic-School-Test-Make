<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly-elearning', 'root', '');
    
    // Force disable admin_panel
    $stmt = $pdo->prepare("UPDATE system_modules SET enabled = FALSE WHERE module_name = 'admin_panel'");
    $stmt->execute();
    
    echo "Admin panel forcefully disabled!\n";
    
    // Verify the change
    $stmt = $pdo->prepare("SELECT enabled FROM system_modules WHERE module_name = 'admin_panel'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    echo "Admin panel status: " . ($result['enabled'] ? 'ENABLED' : 'DISABLED') . "\n";
    
    // Clear cache files manually
    $cacheFiles = glob('storage/framework/cache/data/*');
    $cleared = 0;
    foreach ($cacheFiles as $file) {
        if (is_file($file)) {
            unlink($file);
            $cleared++;
        }
    }
    
    echo "Cleared {$cleared} cache files\n";
    echo "\nNow try accessing /dashboard - it should be blocked!\n";
    
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>