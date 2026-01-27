<?php
// Test timer API directly
$url = 'http://127.0.0.1:8000/api/timer/start';

// Get CSRF token first
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/test-timer');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
$response = curl_exec($ch);

// Extract CSRF token
preg_match('/<meta name="csrf-token" content="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? '';

echo "CSRF Token: " . $csrfToken . "\n";

// Now test the timer API
$data = [
    'chapter_id' => 1,
    'browser_fingerprint' => 'test-fingerprint'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-CSRF-TOKEN: ' . $csrfToken,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

curl_close($ch);

// Clean up
if (file_exists('cookies.txt')) {
    unlink('cookies.txt');
}
?>