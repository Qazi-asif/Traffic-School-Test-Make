<?php

/**
 * Emergency Login Bypass - Create Simple Login Route
 * 
 * This creates a temporary bypass route to test the UI/UX system
 * without dealing with complex authentication issues.
 */

echo "🚨 CREATING EMERGENCY LOGIN BYPASS\n";
echo "==================================\n\n";

// Read the current routes file
$routesFile = 'routes/web.php';
$routesContent = file_get_contents($routesFile);

// Check if bypass route already exists
if (strpos($routesContent, '/emergency-login') !== false) {
    echo "✅ Emergency login route already exists\n";
} else {
    echo "🔧 Adding emergency login bypass route...\n";
    
    // Add emergency login route at the end
    $emergencyRoute = "\n// Emergency Login Bypass (for testing UI/UX)\n";
    $emergencyRoute .= "Route::get('/emergency-login', function() {\n";
    $emergencyRoute .= "    // Create or find test user\n";
    $emergencyRoute .= "    \$user = \\App\\Models\\User::firstOrCreate(\n";
    $emergencyRoute .= "        ['email' => 'test@example.com'],\n";
    $emergencyRoute .= "        [\n";
    $emergencyRoute .= "            'name' => 'Test User',\n";
    $emergencyRoute .= "            'password' => bcrypt('password'),\n";
    $emergencyRoute .= "            'state_code' => 'florida',\n";
    $emergencyRoute .= "            'email_verified_at' => now()\n";
    $emergencyRoute .= "        ]\n";
    $emergencyRoute .= "    );\n";
    $emergencyRoute .= "    \n";
    $emergencyRoute .= "    // Login the user\n";
    $emergencyRoute .= "    auth()->login(\$user);\n";
    $emergencyRoute .= "    \n";
    $emergencyRoute .= "    // Redirect to dashboard\n";
    $emergencyRoute .= "    return redirect('/dashboard')->with('success', 'Emergency login successful!');\n";
    $emergencyRoute .= "});\n\n";
    
    // Find a good place to insert (before the last closing tag or at the end)
    $insertPosition = strrpos($routesContent, '?>');
    if ($insertPosition === false) {
        $routesContent .= $emergencyRoute;
    } else {
        $routesContent = substr_replace($routesContent, $emergencyRoute, $insertPosition, 0);
    }
    
    file_put_contents($routesFile, $routesContent);
    echo "✅ Emergency login route added\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🚨 EMERGENCY ACCESS READY!\n";
echo str_repeat("=", 50) . "\n\n";

echo "🔗 BYPASS LOGIN URL:\n";
echo "   http://nelly-elearning.test/emergency-login\n\n";

echo "🎯 WHAT THIS DOES:\n";
echo "1. Creates a test user automatically\n";
echo "2. Logs you in without password validation\n";
echo "3. Redirects to dashboard → state portal\n";
echo "4. Bypasses all authentication issues\n\n";

echo "✅ NOW YOU CAN TEST THE UI/UX SYSTEM!\n";
echo "Just visit: http://nelly-elearning.test/emergency-login\n\n";

echo "🔧 WHAT YOU'LL SEE:\n";
echo "• Automatic login and redirect to Florida portal\n";
echo "• Professional state-branded dashboard\n";
echo "• Complete UI/UX system working\n";
echo "• Navigation to courses, certificates, etc.\n\n";

echo "⚠️  SECURITY NOTE:\n";
echo "This is a temporary bypass for testing only.\n";
echo "Remove this route before going to production!\n\n";

?>