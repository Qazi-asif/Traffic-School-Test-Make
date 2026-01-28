<?php
// Simple Test Endpoint
header("Content-Type: application/json");

try {
    echo json_encode([
        "status" => "success",
        "message" => "Simple test endpoint works",
        "timestamp" => date("Y-m-d H:i:s"),
        "php_version" => PHP_VERSION
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>