<?php

// List all available chapters
header('Content-Type: application/json');

try {
    // Connect to database
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly-elearning', 'root', '');
    
    // Get all chapters
    $stmt = $pdo->prepare("SELECT id, course_id, title, duration, created_at FROM chapters ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM chapters");
    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'total_chapters' => $total,
        'recent_chapters' => $chapters,
        'message' => 'Chapters listed successfully'
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

?>