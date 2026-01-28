<?php

// Fix saving issues and implement true bulk import
header('Content-Type: application/json');

try {
    // Test chapter saving first
    $testData = [
        'title' => 'Bulk Import Test Chapter',
        'content' => 'This is a test chapter for bulk import functionality',
        'duration' => 30,
        'video_url' => '',
        'is_active' => true
    ];
    
    // Test the bypass route
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://nelly-elearning.test/api/chapter-save-bypass/1");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Check database connection separately
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly-elearning', 'root', '');
    
    // Check courses table
    $courseStmt = $pdo->prepare("SELECT id, title FROM courses WHERE id = 1 LIMIT 1");
    $courseStmt->execute();
    $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
    
    // Check florida_courses table if regular courses doesn't have it
    if (!$course) {
        $floridaStmt = $pdo->prepare("SELECT id, title FROM florida_courses WHERE id = 1 LIMIT 1");
        $floridaStmt->execute();
        $course = $floridaStmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'chapter_save_test' => [
            'http_code' => $httpCode,
            'response' => $response ? json_decode($response, true) : null,
            'error' => $error,
            'working' => $httpCode >= 200 && $httpCode < 300
        ],
        'database_status' => [
            'connection' => 'OK',
            'course_found' => $course ? 'Yes' : 'No',
            'course_data' => $course
        ],
        'next_steps' => [
            'implement_bulk_import' => 'Create multi-file upload system',
            'fix_saving_if_needed' => $httpCode >= 200 && $httpCode < 300 ? 'Saving works' : 'Need to fix saving first'
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

?>