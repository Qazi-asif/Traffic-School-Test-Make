<?php
// Simple JSON Test
header('Content-Type: application/json');

$response = [
    'status' => 'success',
    'message' => 'JSON is working correctly',
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>