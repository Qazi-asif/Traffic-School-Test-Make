<?php

// Check chapters table structure
header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly-elearning', 'root', '');
    
    // Get table structure
    $stmt = $pdo->prepare("DESCRIBE chapters");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get column names only
    $columnNames = array_column($columns, 'Field');
    
    echo json_encode([
        'success' => true,
        'table' => 'chapters',
        'columns' => $columns,
        'column_names' => $columnNames,
        'has_video_url' => in_array('video_url', $columnNames),
        'missing_columns' => array_diff(['video_url', 'required_min_time', 'order_index', 'is_active'], $columnNames)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

?>