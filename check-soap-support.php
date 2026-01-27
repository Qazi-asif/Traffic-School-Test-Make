<?php
/**
 * SOAP Extension Checker
 * 
 * Quick script to check if SOAP extension is available
 * and recommend the appropriate service to use.
 */

echo "=== SOAP Extension Checker ===\n\n";

// Check PHP version
echo "PHP Version: " . PHP_VERSION . "\n";

// Check if SOAP extension is loaded
$soapAvailable = extension_loaded('soap');
echo "SOAP Extension: " . ($soapAvailable ? "✅ Available" : "❌ Not Available") . "\n";

// Check if cURL is available (required for HTTP SOAP)
$curlAvailable = extension_loaded('curl');
echo "cURL Extension: " . ($curlAvailable ? "✅ Available" : "❌ Not Available") . "\n";

// Check if SimpleXML is available (required for parsing responses)
$xmlAvailable = extension_loaded('simplexml');
echo "SimpleXML Extension: " . ($xmlAvailable ? "✅ Available" : "❌ Not Available") . "\n";

echo "\n=== Recommendations ===\n";

if ($soapAvailable) {
    echo "✅ You can use native SOAP services (FlhsmvSoapService)\n";
    echo "✅ HTTP SOAP services are also available as backup\n";
} else {
    echo "⚠️  SOAP extension not available - this is common on shared hosting\n";
    
    if ($curlAvailable && $xmlAvailable) {
        echo "✅ Use HTTP SOAP services (FlhsmvHttpService, CaliforniaTvccHttpService)\n";
        echo "✅ All required extensions for HTTP SOAP are available\n";
    } else {
        echo "❌ Missing required extensions for HTTP SOAP workaround\n";
        if (!$curlAvailable) echo "   - Install cURL extension\n";
        if (!$xmlAvailable) echo "   - Install SimpleXML extension\n";
    }
}

echo "\n=== Configuration ===\n";

if (!$soapAvailable) {
    echo "Add to your .env file:\n";
    echo "HTTP_SOAP_ENABLED=true\n";
    echo "FLORIDA_HTTP_SOAP_ENABLED=true\n";
    echo "CALIFORNIA_TVCC_HTTP_SOAP_ENABLED=true\n";
    echo "\nThen test with: php artisan soap:test-http\n";
} else {
    echo "Your current SOAP configuration should work fine.\n";
    echo "Consider enabling HTTP SOAP as backup:\n";
    echo "HTTP_SOAP_ENABLED=true\n";
}

echo "\n=== Testing ===\n";
echo "Run these commands to test your setup:\n";
echo "php artisan soap:test-http florida\n";
echo "php artisan soap:test-http california\n";
echo "php artisan soap:test-http --verbose\n";

echo "\n=== Support ===\n";
echo "For detailed instructions, see: CPANEL_SOAP_WORKAROUND.md\n";
echo "For hosting-specific help, contact your provider about SOAP extension availability.\n";
?>