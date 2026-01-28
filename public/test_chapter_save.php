<?php

// Test chapter save functionality
header('Content-Type: application/json');

try {
    // Test the chapter save route to see if it's working
    $testData = [
        'title' => 'Test Chapter',
        'content' => 'Test content for chapter save',
        'duration' => 30,
        'video_url' => '',
        'is_active' => true
    ];
    
    // Test different routes
    $routes = [
        '/web/courses/1/chapters' => 'Standard chapter creation route',
        '/api/no-csrf/courses/1/chapters' => 'CSRF-free chapter creation route'
    ];
    
    $results = [];
    
    foreach ($routes as $route => $description) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://nelly-elearning.test$route");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        $results[$route] = [
            'description' => $description,
            'http_code' => $httpCode,
            'response' => $response,
            'error' => $error,
            'working' => $httpCode >= 200 && $httpCode < 300
        ];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Chapter save route testing completed',
        'results' => $results,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

?>