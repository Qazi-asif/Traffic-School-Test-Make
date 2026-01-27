<?php
/**
 * TEST SCRIPT: Court API Endpoints
 * 
 * This script tests if the court API endpoints are working properly
 */

echo "🧪 TESTING COURT API ENDPOINTS\n";
echo "==============================\n\n";

// Test the court API endpoints that the registration form uses
$baseUrl = 'http://nelly-elearning.test'; // Update this to match your local URL

function testEndpoint($url, $description) {
    echo "Testing: {$description}\n";
    echo "URL: {$url}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ CURL Error: {$error}\n";
        return false;
    }
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "✅ HTTP {$httpCode} - Success\n";
        
        $data = json_decode($response, true);
        if ($data) {
            if (is_array($data)) {
                echo "📊 Response: Array with " . count($data) . " items\n";
                if (count($data) > 0) {
                    echo "📝 Sample: " . (is_string($data[0]) ? $data[0] : json_encode($data[0])) . "\n";
                }
            } else {
                echo "📊 Response: " . json_encode($data) . "\n";
            }
        } else {
            echo "📊 Response: " . substr($response, 0, 100) . "...\n";
        }
        return true;
    } else {
        echo "❌ HTTP {$httpCode} - Error\n";
        echo "📊 Response: " . substr($response, 0, 200) . "...\n";
        return false;
    }
    
    echo "\n";
}

// Test endpoints
echo "1. Testing States Endpoint\n";
echo str_repeat("-", 30) . "\n";
testEndpoint($baseUrl . '/api/courts/states', 'Get all states');

echo "\n2. Testing Counties Endpoint\n";
echo str_repeat("-", 30) . "\n";
testEndpoint($baseUrl . '/api/courts/by-state/FL', 'Get counties for Florida');
testEndpoint($baseUrl . '/api/courts/by-state/CA', 'Get counties for California');
testEndpoint($baseUrl . '/api/courts/by-state/TX', 'Get counties for Texas');

echo "\n3. Testing Courts Endpoint\n";
echo str_repeat("-", 30) . "\n";
testEndpoint($baseUrl . '/api/courts/by-county/FL/Miami-Dade', 'Get courts for Miami-Dade, FL');
testEndpoint($baseUrl . '/api/courts/by-county/CA/Los Angeles', 'Get courts for Los Angeles, CA');

echo "\n4. Testing Registration Page\n";
echo str_repeat("-", 30) . "\n";
testEndpoint($baseUrl . '/register/2', 'Registration Step 2 Page');

echo "\n✅ Court API testing completed!\n";
echo "\nIf any endpoints failed, check:\n";
echo "1. Routes are properly defined in routes/web.php or routes/api.php\n";
echo "2. Controllers exist and methods are implemented\n";
echo "3. Database has court data populated\n";
echo "4. Web server is running\n";
?>