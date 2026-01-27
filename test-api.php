<?php

// Simple test to verify the timer API endpoint
$url = 'http://127.0.0.1:8000/api/timer/start';
$data = [
    'chapter_id' => 1,
    'browser_fingerprint' => 'test-fingerprint-' . time()
];

// Get CSRF token first
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/test-timer');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== Timer API Test ===\n";
echo "Test page HTTP code: $httpCode\n";

if ($httpCode == 302) {
    echo "❌ Redirected to login - authentication required\n";
    echo "Please:\n";
    echo "1. Open browser and go to: http://127.0.0.1:8000/login\n";
    echo "2. Login with: test@example.com / password123\n";
    echo "3. Then go to: http://127.0.0.1:8000/test-timer\n";
    echo "4. Test the timer with Chapter ID: 1\n";
} else {
    echo "✅ Test page accessible\n";
}

echo "\n=== Manual Testing Instructions ===\n";
echo "1. Open your browser\n";
echo "2. Go to: http://127.0.0.1:8000/login\n";
echo "3. Login with:\n";
echo "   Email: test@example.com\n";
echo "   Password: password123\n";
echo "4. After login, go to: http://127.0.0.1:8000/test-timer\n";
echo "5. Enter Chapter ID: 1\n";
echo "6. Click 'Start Timer Test'\n";
echo "7. The timer should start with 2-minute requirement\n";
echo "8. Try the violation tests (tab switching, right-click, etc.)\n";
echo "9. Check browser console for detailed logs\n";