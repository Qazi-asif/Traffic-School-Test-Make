<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly-elearning', 'root', '');
    $stmt = $pdo->prepare('SELECT module_name, enabled FROM system_modules');
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    echo "Current module status:\n";
    foreach($results as $row) {
        echo $row['module_name'] . ': ' . ($row['enabled'] ? 'ENABLED' : 'DISABLED') . "\n";
    }
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>