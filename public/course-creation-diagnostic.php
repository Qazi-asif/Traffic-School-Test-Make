<?php
// Diagnostic script to identify course creation issues
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<h1>Course Creation Diagnostic</h1>";
echo "<style>
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
</style>";
echo "<pre>";

try {
    echo "=== COURSE CREATION DIAGNOSTIC ===\n\n";
    
    // Check 1: Database connection
    echo "1. Database Connection:\n";
    try {
        $pdo = DB::connection()->getPdo();
        echo "   <span class='success'>✓ Connected successfully</span>\n";
        echo "   Database: " . $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";
    } catch (Exception $e) {
        echo "   <span class='error'>❌ Connection failed: " . $e->getMessage() . "</span>\n";
    }
    
    // Check 2: Table existence and structure
    echo "\n2. Table Structure:\n";
    $tableExists = Schema::hasTable('florida_courses');
    echo "   florida_courses table exists: " . ($tableExists ? "<span class='success'>YES</span>" : "<span class='error'>NO</span>") . "\n";
    
    if ($tableExists) {
        $columns = DB::select("DESCRIBE florida_courses");
        echo "   Columns:\n";
        foreach ($columns as $column) {
            $nullable = $column->Null === 'YES' ? 'NULL' : 'NOT NULL';
            $default = $column->Default !== null ? " DEFAULT '{$column->Default}'" : '';
            echo "     - {$column->Field} ({$column->Type}) {$nullable}{$default}\n";
        }
    }
    
    // Check 3: Model class
    echo "\n3. Model Class:\n";
    try {
        $model = new \App\Models\FloridaCourse();
        echo "   <span class='success'>✓ FloridaCourse model loaded</span>\n";
        echo "   Table name: " . $model->getTable() . "\n";
        echo "   Fillable fields: " . implode(', ', $model->getFillable()) . "\n";
    } catch (Exception $e) {
        echo "   <span class='error'>❌ Model error: " . $e->getMessage() . "</span>\n";
    }
    
    // Check 4: Required vs Available fields
    echo "\n4. Field Mapping Analysis:\n";
    $requiredFields = ['title', 'description', 'state', 'duration', 'price', 'passing_score'];
    $availableColumns = array_column($columns, 'Field');
    
    foreach ($requiredFields as $field) {
        $exists = in_array($field, $availableColumns);
        $status = $exists ? "<span class='success'>✓</span>" : "<span class='error'>❌</span>";
        echo "   {$status} {$field}: " . ($exists ? "Available" : "Missing") . "\n";
    }
    
    // Check 5: Test minimal course creation
    echo "\n5. Minimal Course Creation Test:\n";
    try {
        $testData = [
            'title' => 'Diagnostic Test Course',
            'state' => 'FL',
            'duration' => 240,
            'price' => 29.99,
            'passing_score' => 80,
        ];
        
        echo "   Attempting to create course with minimal data...\n";
        $courseId = DB::table('florida_courses')->insertGetId($testData);
        echo "   <span class='success'>✓ Course created with ID: {$courseId}</span>\n";
        
        // Clean up
        DB::table('florida_courses')->where('id', $courseId)->delete();
        echo "   <span class='success'>✓ Test course deleted</span>\n";
        
    } catch (Exception $e) {
        echo "   <span class='error'>❌ Creation failed: " . $e->getMessage() . "</span>\n";
        echo "   SQL State: " . $e->getCode() . "\n";
    }
    
    // Check 6: Controller classes
    echo "\n6. Controller Classes:\n";
    try {
        $courseController = new \App\Http\Controllers\CourseController();
        echo "   <span class='success'>✓ CourseController loaded</span>\n";
    } catch (Exception $e) {
        echo "   <span class='error'>❌ CourseController error: " . $e->getMessage() . "</span>\n";
    }
    
    try {
        $floridaController = new \App\Http\Controllers\FloridaCourseController();
        echo "   <span class='success'>✓ FloridaCourseController loaded</span>\n";
    } catch (Exception $e) {
        echo "   <span class='error'>❌ FloridaCourseController error: " . $e->getMessage() . "</span>\n";
    }
    
    // Check 7: Routes
    echo "\n7. Route Analysis:\n";
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $courseRoutes = [];
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        if (strpos($uri, 'courses') !== false && in_array('POST', $route->methods())) {
            $courseRoutes[] = [
                'method' => 'POST',
                'uri' => $uri,
                'action' => $route->getActionName()
            ];
        }
    }
    
    if (!empty($courseRoutes)) {
        echo "   Available POST routes for courses:\n";
        foreach ($courseRoutes as $route) {
            echo "     - {$route['method']} /{$route['uri']} → {$route['action']}\n";
        }
    } else {
        echo "   <span class='warning'>⚠️ No course POST routes found</span>\n";
    }
    
    echo "\n=== DIAGNOSTIC COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "<span class='error'>❌ FATAL ERROR: " . $e->getMessage() . "</span>\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
?>