<?php

// Debug chapter update issue
header('Content-Type: application/json');

try {
    // Test if chapter exists
    $chapterId = 2;
    
    // Check if we can connect to database
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly-elearning', 'root', '');
    
    // Check if chapter exists
    $stmt = $pdo->prepare("SELECT * FROM chapters WHERE id = ?");
    $stmt->execute([$chapterId]);
    $chapter = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$chapter) {
        echo json_encode([
            'success' => false,
            'error' => 'Chapter not found',
            'chapter_id' => $chapterId
        ]);
        exit;
    }
    
    // Test the update directly
    $updateData = [
        'title' => 'Updated Test Chapter',
        'content' => 'Updated test content',
        'duration' => 45
    ];
    
    $updateSql = "UPDATE chapters SET title = ?, content = ?, duration = ?, updated_at = NOW() WHERE id = ?";
    $updateStmt = $pdo->prepare($updateSql);
    $result = $updateStmt->execute([
        $updateData['title'],
        $updateData['content'],
        $updateData['duration'],
        $chapterId
    ]);
    
    if ($result) {
        // Get updated chapter
        $stmt->execute([$chapterId]);
        $updatedChapter = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Chapter updated successfully via direct database',
            'original_chapter' => $chapter,
            'updated_chapter' => $updatedChapter,
            'test_results' => [
                'database_connection' => 'OK',
                'chapter_exists' => 'OK',
                'direct_update' => 'OK'
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Database update failed',
            'pdo_error' => $pdo->errorInfo()
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

?>