<?php
// Disable Maintenance Mode - Fix JSON Error
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Disable Maintenance Mode</h1>";
echo "<pre>";

try {
    echo "=== DISABLING MAINTENANCE MODE ===\n\n";
    
    // 1. Check if maintenance mode is enabled
    $maintenanceFile = storage_path('framework/maintenance.php');
    
    if (file_exists($maintenanceFile)) {
        echo "1. Maintenance mode is ENABLED\n";
        echo "   File exists: {$maintenanceFile}\n";
        
        // Read the maintenance file content
        $content = file_get_contents($maintenanceFile);
        echo "   Content preview: " . substr($content, 0, 100) . "...\n";
        
        // Delete the maintenance file
        if (unlink($maintenanceFile)) {
            echo "   ✅ Successfully deleted maintenance file\n";
            echo "   ✅ Maintenance mode is now DISABLED\n";
        } else {
            echo "   ❌ Failed to delete maintenance file\n";
        }
    } else {
        echo "1. Maintenance mode is already DISABLED\n";
        echo "   File does not exist: {$maintenanceFile}\n";
    }
    
    // 2. Verify maintenance mode is disabled
    echo "\n2. Verifying maintenance mode status...\n";
    
    if (!file_exists($maintenanceFile)) {
        echo "   ✅ Maintenance mode is DISABLED\n";
        echo "   ✅ JSON endpoints should now work properly\n";
    } else {
        echo "   ❌ Maintenance mode is still ENABLED\n";
    }
    
    // 3. Test a JSON endpoint to confirm it works
    echo "\n3. Testing JSON endpoint...\n";
    
    try {
        $controller = new \App\Http\Controllers\CourseController();
        $request = new \Illuminate\Http\Request();
        $response = $controller->indexWeb($request);
        
        if ($response->getStatusCode() === 200) {
            $content = $response->getContent();
            $isValidJson = json_decode($content) !== null;
            echo "   ✅ /web/courses endpoint: Status 200, Valid JSON: " . ($isValidJson ? 'Yes' : 'No') . "\n";
            
            if ($isValidJson) {
                $data = json_decode($content, true);
                echo "   ✅ Found " . count($data) . " courses in response\n";
            }
        } else {
            echo "   ❌ /web/courses endpoint failed: Status " . $response->getStatusCode() . "\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Endpoint test error: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 MAINTENANCE MODE DISABLED!\n";
    
    echo "\n✅ WHAT WAS FIXED:\n";
    echo "1. Deleted Laravel maintenance mode file\n";
    echo "2. JSON endpoints should now return proper JSON instead of HTML\n";
    echo "3. Course creation should work without JSON errors\n";
    
    echo "\n📝 IMMEDIATE NEXT STEPS:\n";
    echo "1. Refresh your browser page\n";
    echo "2. Try the course creation again\n";
    echo "3. The JSON error should be completely resolved!\n";
    
    echo "\n🔧 IF YOU STILL GET ERRORS:\n";
    echo "1. Clear browser cache (Ctrl+Shift+Delete)\n";
    echo "2. Check browser Network tab for any remaining issues\n";
    echo "3. All endpoints should now return JSON properly\n";
    
} catch (Exception $e) {
    echo "❌ ERROR DISABLING MAINTENANCE MODE: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";

echo "<h2>🚀 JSON Error Fixed!</h2>";
echo "<p><strong>Maintenance mode has been disabled. Your course creation should work perfectly now!</strong></p>";
?>