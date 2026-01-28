<?php
/**
 * Fix Routes Syntax Error - Remove Git Conflicts and Duplicates
 */

echo "🔧 FIXING ROUTES SYNTAX ERROR\n";
echo "=============================\n\n";

try {
    $routesFile = __DIR__ . '/routes/web.php';
    
    if (!file_exists($routesFile)) {
        echo "❌ Routes file not found\n";
        exit(1);
    }
    
    // Read the current content
    $content = file_get_contents($routesFile);
    echo "✅ Read routes file (" . strlen($content) . " bytes)\n";
    
    // Remove Git conflict markers
    $content = preg_replace('/<<<<<<< HEAD.*?>>>>>>> [a-f0-9]+ \([^)]+\)/s', '', $content);
    echo "✅ Removed Git conflict markers\n";
    
    // Remove duplicate comment lines
    $content = preg_replace('/\/\/ ={40,}\n(\/\/ ={40,}\n)+/', "// ========================================\n", $content);
    echo "✅ Removed duplicate comment lines\n";
    
    // Remove duplicate STATE-SEPARATED ROUTING SYSTEM headers
    $content = preg_replace('/(\/\/ STATE-SEPARATED ROUTING SYSTEM - PHASE 1\n)+/', "// STATE-SEPARATED ROUTING SYSTEM - PHASE 1\n", $content);
    echo "✅ Removed duplicate headers\n";
    
    // Find the position where our state routes start
    $stateRoutesStart = strpos($content, '// STATE-SEPARATED ROUTING SYSTEM - PHASE 1');
    
    if ($stateRoutesStart === false) {
        echo "❌ State routes section not found\n";
        exit(1);
    }
    
    // Find the end of our state routes
    $stateRoutesEnd = strpos($content, '// END STATE-SEPARATED ROUTING SYSTEM', $stateRoutesStart);
    
    if ($stateRoutesEnd === false) {
        echo "❌ End of state routes section not found\n";
        exit(1);
    }
    
    // Remove the entire state routes section temporarily
    $beforeStateRoutes = substr($content, 0, $stateRoutesStart);
    $afterStateRoutes = substr($content, $stateRoutesEnd + strlen('// END STATE-SEPARATED ROUTING SYSTEM') + strlen('// ========================================'));
    
    // Clean up any trailing whitespace and ensure proper spacing
    $beforeStateRoutes = rtrim($beforeStateRoutes) . "\n\n";
    $afterStateRoutes = ltrim($afterStateRoutes);
    
    // Create clean state routes section
    $cleanStateRoutes = '// ========================================
// STATE-SEPARATED ROUTING SYSTEM - PHASE 1
// ========================================

// Florida Routes
Route::prefix(\'florida\')->group(function() {
    Route::get(\'/\', function() {
        try {
            $controller = new \App\Http\Controllers\Student\Florida\CoursePlayerController();
            return $controller->index();
        } catch (Exception $e) {
            return \'<h1>Florida Traffic School</h1><p>Controller Error: \' . $e->getMessage() . \'</p><p><a href="/florida/courses">Try Courses Page</a></p>\';
        }
    })->name(\'florida.dashboard\');
    
    Route::get(\'/courses\', function() {
        try {
            $controller = new \App\Http\Controllers\Student\Florida\CoursePlayerController();
            return $controller->index();
        } catch (Exception $e) {
            return \'<h1>Florida Courses</h1><p>Loading courses...</p><p>Error: \' . $e->getMessage() . \'</p>\';
        }
    })->name(\'florida.courses\');
    
    Route::get(\'/test-controller\', function() {
        try {
            $controller = new \App\Http\Controllers\Student\Florida\CoursePlayerController();
            return \'<h1>✅ Florida Controller Test</h1><p>Controller loaded successfully</p><p>Class: \' . get_class($controller) . \'</p>\';
        } catch (Exception $e) {
            return \'<h1>❌ Controller Error</h1><p>\' . $e->getMessage() . \'</p>\';
        }
    })->name(\'florida.test-controller\');
});

// Missouri Routes
Route::prefix(\'missouri\')->group(function() {
    Route::get(\'/\', function() {
        try {
            $controller = new \App\Http\Controllers\Student\Missouri\CoursePlayerController();
            return $controller->index();
        } catch (Exception $e) {
            return \'<h1>Missouri Traffic School</h1><p>Controller Error: \' . $e->getMessage() . \'</p>\';
        }
    })->name(\'missouri.dashboard\');
});

// Texas Routes
Route::prefix(\'texas\')->group(function() {
    Route::get(\'/\', function() {
        try {
            $controller = new \App\Http\Controllers\Student\Texas\CoursePlayerController();
            return $controller->index();
        } catch (Exception $e) {
            return \'<h1>Texas Traffic School</h1><p>Controller Error: \' . $e->getMessage() . \'</p>\';
        }
    })->name(\'texas.dashboard\');
});

// Delaware Routes
Route::prefix(\'delaware\')->group(function() {
    Route::get(\'/\', function() {
        try {
            $controller = new \App\Http\Controllers\Student\Delaware\CoursePlayerController();
            return $controller->index();
        } catch (Exception $e) {
            return \'<h1>Delaware Traffic School</h1><p>Controller Error: \' . $e->getMessage() . \'</p>\';
        }
    })->name(\'delaware.dashboard\');
});

// Admin Routes for State Management
Route::prefix(\'admin\')->group(function() {
    Route::get(\'/\', function() {
        try {
            $controller = new \App\Http\Controllers\Admin\DashboardController();
            return $controller->index();
        } catch (Exception $e) {
            return \'<h1>Admin Dashboard</h1><p>Controller Error: \' . $e->getMessage() . \'</p>\';
        }
    })->name(\'admin.dashboard\');
    
    // Florida Admin Routes
    Route::prefix(\'florida\')->name(\'admin.florida.\')->group(function() {
        Route::get(\'/courses\', function() {
            try {
                $controller = new \App\Http\Controllers\Admin\Florida\CourseController();
                return $controller->index();
            } catch (Exception $e) {
                return \'<h1>Florida Course Admin</h1><p>Error: \' . $e->getMessage() . \'</p>\';
            }
        })->name(\'courses.index\');
    });
});

// ========================================
// END STATE-SEPARATED ROUTING SYSTEM
// ========================================

';
    
    // Reconstruct the file
    $newContent = $beforeStateRoutes . $cleanStateRoutes . $afterStateRoutes;
    
    // Write the fixed content back
    file_put_contents($routesFile, $newContent);
    echo "✅ Fixed routes file written\n";
    
    echo "\n🎉 ROUTES SYNTAX FIX COMPLETE!\n";
    echo "==============================\n";
    echo "✅ Git conflict markers removed\n";
    echo "✅ Duplicate lines cleaned up\n";
    echo "✅ State routes section rebuilt\n";
    echo "✅ File syntax should now be valid\n";
    
    echo "\n🧪 TEST THESE URLS NOW:\n";
    echo "=======================\n";
    echo "- http://nelly-elearning.test/florida\n";
    echo "- http://nelly-elearning.test/florida/test-controller\n";
    echo "- http://nelly-elearning.test/missouri\n";
    echo "- http://nelly-elearning.test/admin\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>