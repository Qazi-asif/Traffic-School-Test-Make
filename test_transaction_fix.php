<?php

// Simple test to verify transaction handling
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly_elearning', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Testing Transaction Fix ===\n\n";
    
    // Check if table exists and has required columns
    $stmt = $pdo->query("DESCRIBE chapter_questions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current table structure:\n";
    $columnNames = [];
    foreach ($columns as $column) {
        $columnNames[] = $column['Field'];
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
    $requiredColumns = ['points', 'options', 'question_type', 'is_active'];
    echo "\nRequired columns check:\n";
    foreach ($requiredColumns as $col) {
        $exists = in_array($col, $columnNames);
        echo ($exists ? "✅" : "❌") . " {$col}\n";
    }
    
    // Test a simple transaction
    echo "\nTesting transaction handling...\n";
    
    try {
        $pdo->beginTransaction();
        
        // Simple test insert
        $stmt = $pdo->prepare("
            INSERT INTO chapter_questions 
            (chapter_id, question_text, correct_answer, order_index, created_at, updated_at, points) 
            VALUES (?, ?, ?, ?, NOW(), NOW(), ?)
        ");
        
        $result = $stmt->execute([1, 'Test question for transaction', 'A', 999, 1]);
        
        if ($result) {
            echo "✅ Test insert successful\n";
            
            // Clean up test data
            $pdo->exec("DELETE FROM chapter_questions WHERE order_index = 999");
            echo "✅ Test data cleaned up\n";
        }
        
        $pdo->commit();
        echo "✅ Transaction committed successfully\n";
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
            echo "⚠️ Transaction rolled back\n";
        }
        echo "❌ Transaction test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Transaction handling test completed\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>