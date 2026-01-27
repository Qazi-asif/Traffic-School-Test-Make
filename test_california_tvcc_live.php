<?php
/**
 * California TVCC Live API Test Script
 * 
 * This script tests the live California TVCC API endpoint with dummy data
 * and captures detailed error responses for debugging.
 */

// Configuration
$config = [
    'wsdl_path' => __DIR__ . '/resources/wsdl/TvccServiceImplService.wsdl',
    'endpoint' => 'https://xsg.dmv.ca.gov/tvcc/tvccservice',
    'username' => 'Support@dummiestrafficschool.com',
    'password' => 'Traffic24',
    'timeout' => 30,
];

// Dummy test data
$testData = [
    'ccDate' => date('c'), // Current date in ISO 8601 format
    'classCity' => 'Los Angeles',
    'classCntyCd' => 'LA',
    'courtCd' => 'ABC123',
    'dateOfBirth' => '1990-05-20T00:00:00',
    'dlNbr' => 'D1234567890123',
    'firstName' => 'John',
    'instructorLicNbr' => 'INS123456',
    'instructorName' => 'Test Instructor',
    'lastName' => 'Doe',
    'modality' => '4T',
    'refNbr' => 'TEST123456',
    'userDto' => [
        'userId' => $config['username'],
        'password' => $config['password'],
    ],
];

echo "=== California TVCC Live API Test ===\n";
echo "Endpoint: {$config['endpoint']}\n";
echo "Username: {$config['username']}\n";
echo "Test Date: " . date('Y-m-d H:i:s') . "\n";
echo "=====================================\n\n";

// Test 1: Check WSDL file exists
echo "1. Checking WSDL file...\n";
if (!file_exists($config['wsdl_path'])) {
    echo "❌ ERROR: WSDL file not found at: {$config['wsdl_path']}\n";
    echo "Please ensure the WSDL files are copied to resources/wsdl/\n";
    exit(1);
}
echo "✅ WSDL file found\n\n";

// Test 2: Initialize SOAP client
echo "2. Initializing SOAP client...\n";
try {
    $soapOptions = [
        'trace' => true,
        'exceptions' => true,
        'connection_timeout' => $config['timeout'],
        'cache_wsdl' => WSDL_CACHE_NONE,
        'soap_version' => SOAP_1_1,
        'encoding' => 'UTF-8',
        'location' => $config['endpoint'], // Override endpoint
        'stream_context' => stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
            'http' => [
                'timeout' => $config['timeout'],
                'user_agent' => 'California-TVCC-Test-Client/1.0',
            ],
        ]),
    ];

    $soapClient = new SoapClient($config['wsdl_path'], $soapOptions);
    echo "✅ SOAP client initialized successfully\n";
    
    // Display available methods
    echo "\nAvailable SOAP methods:\n";
    $functions = $soapClient->__getFunctions();
    foreach ($functions as $function) {
        echo "  - $function\n";
    }
    echo "\n";

} catch (Exception $e) {
    echo "❌ ERROR: Failed to initialize SOAP client\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Prepare request
echo "3. Preparing TVCC request...\n";
$request = ['arg0' => $testData];

echo "Request data:\n";
echo json_encode($request, JSON_PRETTY_PRINT) . "\n\n";

// Test 4: Send request to live API
echo "4. Sending request to live TVCC API...\n";
echo "⚠️  WARNING: This will attempt to connect to the live California DMV endpoint\n";
echo "Expected result: Network error or authentication failure (unless on authorized network)\n\n";

$startTime = microtime(true);

try {
    // Call the TVCC API
    $response = $soapClient->addCourseCompletion($request);
    
    $endTime = microtime(true);
    $duration = round(($endTime - $startTime) * 1000, 2);
    
    echo "🎉 SUCCESS: API call completed!\n";
    echo "Response time: {$duration}ms\n";
    echo "Response:\n";
    print_r($response);
    
    // Log successful response
    file_put_contents('tvcc_success_log.txt', date('Y-m-d H:i:s') . " - SUCCESS\n" . print_r($response, true) . "\n\n", FILE_APPEND);

} catch (SoapFault $e) {
    $endTime = microtime(true);
    $duration = round(($endTime - $startTime) * 1000, 2);
    
    echo "❌ SOAP FAULT: API call failed\n";
    echo "Response time: {$duration}ms\n";
    echo "Fault Code: " . ($e->faultcode ?? 'N/A') . "\n";
    echo "Fault String: " . ($e->faultstring ?? 'N/A') . "\n";
    echo "Error Message: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    
    // Get detailed SOAP information
    echo "\n--- SOAP Request ---\n";
    echo $soapClient->__getLastRequest() . "\n";
    
    echo "\n--- SOAP Response ---\n";
    echo $soapClient->__getLastResponse() . "\n";
    
    echo "\n--- SOAP Request Headers ---\n";
    echo $soapClient->__getLastRequestHeaders() . "\n";
    
    echo "\n--- SOAP Response Headers ---\n";
    echo $soapClient->__getLastResponseHeaders() . "\n";
    
    // Log error details
    $errorLog = [
        'timestamp' => date('Y-m-d H:i:s'),
        'duration_ms' => $duration,
        'fault_code' => $e->faultcode ?? null,
        'fault_string' => $e->faultstring ?? null,
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'request' => $soapClient->__getLastRequest(),
        'response' => $soapClient->__getLastResponse(),
        'request_headers' => $soapClient->__getLastRequestHeaders(),
        'response_headers' => $soapClient->__getLastResponseHeaders(),
    ];
    
    file_put_contents('tvcc_error_log.json', json_encode($errorLog, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
    
} catch (Exception $e) {
    $endTime = microtime(true);
    $duration = round(($endTime - $startTime) * 1000, 2);
    
    echo "❌ GENERAL ERROR: API call failed\n";
    echo "Response time: {$duration}ms\n";
    echo "Error Type: " . get_class($e) . "\n";
    echo "Error Message: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    
    // Log general error
    $errorLog = [
        'timestamp' => date('Y-m-d H:i:s'),
        'duration_ms' => $duration,
        'type' => get_class($e),
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ];
    
    file_put_contents('tvcc_general_error_log.json', json_encode($errorLog, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
}

echo "\n=== Test Complete ===\n";
echo "Check the following log files for detailed information:\n";
echo "- tvcc_success_log.txt (if successful)\n";
echo "- tvcc_error_log.json (SOAP faults)\n";
echo "- tvcc_general_error_log.json (general errors)\n";

// Test 5: Network connectivity test
echo "\n5. Testing basic network connectivity...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $config['endpoint']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Network Error: $error\n";
} else {
    echo "✅ Network connectivity: HTTP $httpCode\n";
    if ($httpCode == 200 || $httpCode == 405) {
        echo "✅ Endpoint is reachable\n";
    } else {
        echo "⚠️  Endpoint returned HTTP $httpCode\n";
    }
}

echo "\n=== Summary ===\n";
echo "This test helps identify:\n";
echo "1. WSDL file availability\n";
echo "2. SOAP client configuration\n";
echo "3. Network connectivity to California DMV\n";
echo "4. Authentication/authorization issues\n";
echo "5. API response format and error codes\n";
echo "\nExpected results when NOT on authorized network:\n";
echo "- Network timeout or connection refused\n";
echo "- HTTP 403 Forbidden\n";
echo "- SOAP fault with authentication error\n";
echo "\nExpected results when ON authorized network:\n";
echo "- Successful connection\n";
echo "- Proper SOAP response (success or validation error)\n";
echo "- Certificate sequence number returned\n";
?>