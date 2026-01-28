<?php

// Debug chapter saving issue
header('Content-Type: application/json');

try {
    // Test chapter creation with detailed logging
    $testData = [
        'title' => 'Debug Test Chapter',
        'content' => 'This is a test chapter to debug saving issues',
        'duration' => 30,
        'video_url' => '',
        'is_active' => true
    ];
    
    // Test the bypass route directly
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
    
    // Also test direct database connection
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly-elearning', 'root', '');
    
    // Check if course exists
    $courseStmt = $pdo->prepare("SELECT id, title FROM courses WHERE id = 1 UNION SELECT id, title FROM florida_courses WHERE id = 1");
    $courseStmt->execute();
    $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'bypass_route_test' => [
            'http_code' => $httpCode,
            'response' => $response,
            'error' => $error,
            'working' => $httpCode >= 200 && $httpCode < 300
        ],
        'database_test' => [
            'connection' => 'OK',
            'course_exists' => $course ? 'Yes' : 'No',
            'course_data' => $course
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

?>