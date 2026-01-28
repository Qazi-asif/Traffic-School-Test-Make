<?php
echo "Testing server...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode === 200) {
    echo "✅ SERVER IS RUNNING!\n";
    echo "Visit: http://127.0.0.1:8000/florida/login\n";
    echo "Login: florida@test.com / password123\n";
} else {
    echo "❌ Server not yet accessible\n";
    echo "Error: " . ($error ?: "HTTP {$httpCode}") . "\n";
    echo "Please wait a moment for server to start...\n";
}
?>