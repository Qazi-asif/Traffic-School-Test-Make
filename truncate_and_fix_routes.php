<?php
/**
 * Truncate routes file at good part and add clean state routes
 */

echo "🔧 TRUNCATING AND FIXING ROUTES\n";
echo "===============================\n\n";

try {
    $routesFile = __DIR__ . '/routes/web.php';
    
    // Read the file
    $lines = file($routesFile);
    echo "✅ Read " . count($lines) . " lines from routes file\n";
    
    // Find the line with "Admin Routes - Include the complete admin system"
    $truncateAt = -1;
    for ($i = 0; $i < count($lines); $i++) {
        if (strpos($lines[$i], 'Admin Routes - Include the complete admin system') !== false) {
            $truncateAt = $i + 2; // Keep this line and the next route line
            break;
        }
    }
    
    if ($truncateAt === -1) {
        echo "❌ Could not find truncation point\n";
        exit(1);
    }
    
    echo "✅ Found truncation point at line " . ($truncateAt + 1) . "\n";
    
    // Keep only the good part
    $goodLines = array_slice($lines, 0, $truncateAt);
    
    // Add clean state routes
    $stateRoutes = [
        "",
        "// ========================================",
        "// STATE-SEPARATED ROUTING SYSTEM - PHASE 1", 
        "// ========================================",
        "",
        "// Florida Routes",
        "Route::prefix('florida')->group(function() {",
        "    Route::get('/', function() {",
        "        try {",
        "            \$controller = new \\App\\Http\\Controllers\\Student\\Florida\\CoursePlayerController();",
        "            return \$controller->index();",
        "        } catch (Exception \$e) {",
        "            return '<h1>Florida Traffic School</h1><p>Controller Error: ' . \$e->getMessage() . '</p>';",
        "        }",
        "    })->name('florida.dashboard');",
        "    ",
        "    Route::get('/test-controller', function() {",
        "        try {",
        "            \$controller = new \\App\\Http\\Controllers\\Student\\Florida\\CoursePlayerController();",
        "            return '<h1>✅ Florida Controller Test</h1><p>Controller loaded successfully</p><p>Class: ' . get_class(\$controller) . '</p>';",
        "        } catch (Exception \$e) {",
        "            return '<h1>❌ Controller Error</h1><p>' . \$e->getMessage() . '</p>';",
        "        }",
        "    })->name('florida.test-controller');",
        "});",
        "",
        "// Missouri Routes", 
        "Route::prefix('missouri')->group(function() {",
        "    Route::get('/', function() {",
        "        return '<h1>Missouri Traffic School</h1><p>Coming soon...</p>';",
        "    })->name('missouri.dashboard');",
        "});",
        "",
        "// Texas Routes",
        "Route::prefix('texas')->group(function() {",
        "    Route::get('/', function() {",
        "        return '<h1>Texas Traffic School</h1><p>Coming soon...</p>';",
        "    })->name('texas.dashboard');",
        "});",
        "",
        "// Delaware Routes", 
        "Route::prefix('delaware')->group(function() {",
        "    Route::get('/', function() {",
        "        return '<h1>Delaware Traffic School</h1><p>Coming soon...</p>';",
        "    })->name('delaware.dashboard');",
        "});",
        "",
        "// ========================================",
        "// END STATE-SEPARATED ROUTING SYSTEM",
        "// ========================================",
        ""
    ];
    
    // Combine good lines with state routes
    $newContent = implode("", $goodLines) . implode("\n", $stateRoutes);
    
    // Write the fixed file
    file_put_contents($routesFile, $newContent);
    echo "✅ Fixed routes file written\n";
    
    echo "\n🎉 ROUTES FIX COMPLETE!\n";
    echo "=======================\n";
    echo "✅ File truncated at good point\n";
    echo "✅ Clean state routes added\n";
    echo "✅ Syntax should now be valid\n";
    
    echo "\n🧪 TEST THESE URLS NOW:\n";
    echo "=======================\n";
    echo "- http://nelly-elearning.test/florida\n";
    echo "- http://nelly-elearning.test/florida/test-controller\n";
    echo "- http://nelly-elearning.test/missouri\n";
    echo "- http://nelly-elearning.test/texas\n";
    echo "- http://nelly-elearning.test/delaware\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>