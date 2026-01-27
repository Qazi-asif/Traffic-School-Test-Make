<?php
/**
 * Quick Test Script - Verify Quiz Fix Deployment
 * 
 * Upload this file to your hosting and visit it in a browser to verify:
 * 1. Files are uploaded correctly
 * 2. Database connection works
 * 3. Routes are accessible
 * 
 * DELETE THIS FILE AFTER TESTING!
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quiz Fix Deployment Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .test { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #ccc; }
        .pass { border-left-color: #28a745; }
        .fail { border-left-color: #dc3545; }
        .warning { border-left-color: #ffc107; }
        h1 { color: #333; }
        .status { font-weight: bold; }
        .pass .status { color: #28a745; }
        .fail .status { color: #dc3545; }
        .warning .status { color: #ffc107; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        .delete-warning { background: #fff3cd; border: 2px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔧 Quiz Fix Deployment Test</h1>
    <p>This page verifies that all components are properly deployed.</p>
    
    <div class="delete-warning">
        <strong>⚠️ SECURITY WARNING:</strong> Delete this file after testing!<br>
        <code>rm test_quiz_fix_deployment.php</code>
    </div>

    <?php
    $allPassed = true;

    // Test 1: Database Connection
    echo '<div class="test ';
    try {
        DB::connection()->getPdo();
        echo 'pass">';
        echo '<div class="status">✅ PASS</div>';
        echo '<strong>Test 1: Database Connection</strong><br>';
        echo 'Successfully connected to database.';
    } catch (Exception $e) {
        echo 'fail">';
        echo '<div class="status">❌ FAIL</div>';
        echo '<strong>Test 1: Database Connection</strong><br>';
        echo 'Error: ' . $e->getMessage();
        $allPassed = false;
    }
    echo '</div>';

    // Test 2: Controller File Exists
    echo '<div class="test ';
    $controllerPath = app_path('Http/Controllers/Admin/QuizMaintenanceController.php');
    if (file_exists($controllerPath)) {
        echo 'pass">';
        echo '<div class="status">✅ PASS</div>';
        echo '<strong>Test 2: Controller File</strong><br>';
        echo 'QuizMaintenanceController.php exists.';
    } else {
        echo 'fail">';
        echo '<div class="status">❌ FAIL</div>';
        echo '<strong>Test 2: Controller File</strong><br>';
        echo 'QuizMaintenanceController.php not found at: ' . $controllerPath;
        $allPassed = false;
    }
    echo '</div>';

    // Test 3: View File Exists
    echo '<div class="test ';
    $viewPath = resource_path('views/admin/quiz-maintenance/index.blade.php');
    if (file_exists($viewPath)) {
        echo 'pass">';
        echo '<div class="status">✅ PASS</div>';
        echo '<strong>Test 3: View File</strong><br>';
        echo 'Quiz maintenance view exists.';
    } else {
        echo 'fail">';
        echo '<div class="status">❌ FAIL</div>';
        echo '<strong>Test 3: View File</strong><br>';
        echo 'View file not found at: ' . $viewPath;
        $allPassed = false;
    }
    echo '</div>';

    // Test 4: Artisan Commands Exist
    echo '<div class="test ';
    $diagnoseCmd = app_path('Console/Commands/DiagnoseBrokenQuizzes.php');
    $fixCmd = app_path('Console/Commands/FixQuizAnswerFormats.php');
    if (file_exists($diagnoseCmd) && file_exists($fixCmd)) {
        echo 'pass">';
        echo '<div class="status">✅ PASS</div>';
        echo '<strong>Test 4: Artisan Commands</strong><br>';
        echo 'Both artisan commands exist.';
    } else {
        echo 'warning">';
        echo '<div class="status">⚠️ WARNING</div>';
        echo '<strong>Test 4: Artisan Commands</strong><br>';
        echo 'Artisan commands not found (optional if using web tool).';
    }
    echo '</div>';

    // Test 5: Question Tables Exist
    echo '<div class="test ';
    try {
        $tables = ['chapter_questions', 'questions', 'final_exam_questions'];
        $existingTables = [];
        foreach ($tables as $table) {
            try {
                DB::table($table)->limit(1)->get();
                $existingTables[] = $table;
            } catch (Exception $e) {
                // Table doesn't exist
            }
        }
        
        if (count($existingTables) > 0) {
            echo 'pass">';
            echo '<div class="status">✅ PASS</div>';
            echo '<strong>Test 5: Question Tables</strong><br>';
            echo 'Found tables: ' . implode(', ', $existingTables);
        } else {
            echo 'fail">';
            echo '<div class="status">❌ FAIL</div>';
            echo '<strong>Test 5: Question Tables</strong><br>';
            echo 'No question tables found in database.';
            $allPassed = false;
        }
    } catch (Exception $e) {
        echo 'fail">';
        echo '<div class="status">❌ FAIL</div>';
        echo '<strong>Test 5: Question Tables</strong><br>';
        echo 'Error: ' . $e->getMessage();
        $allPassed = false;
    }
    echo '</div>';

    // Test 6: Sample Question Data
    echo '<div class="test ';
    try {
        $sampleQuestion = DB::table('chapter_questions')->first();
        if ($sampleQuestion) {
            $options = json_decode($sampleQuestion->options, true);
            echo 'pass">';
            echo '<div class="status">✅ PASS</div>';
            echo '<strong>Test 6: Sample Question Data</strong><br>';
            echo 'Found sample question (ID: ' . $sampleQuestion->id . ')<br>';
            echo 'Correct Answer: ' . $sampleQuestion->correct_answer . '<br>';
            echo 'Options Format: ' . (is_array($options) ? 'Valid JSON Array' : 'Invalid');
        } else {
            echo 'warning">';
            echo '<div class="status">⚠️ WARNING</div>';
            echo '<strong>Test 6: Sample Question Data</strong><br>';
            echo 'No questions found in chapter_questions table.';
        }
    } catch (Exception $e) {
        echo 'fail">';
        echo '<div class="status">❌ FAIL</div>';
        echo '<strong>Test 6: Sample Question Data</strong><br>';
        echo 'Error: ' . $e->getMessage();
        $allPassed = false;
    }
    echo '</div>';

    // Test 7: Route Accessibility
    echo '<div class="test ';
    try {
        $routes = \Illuminate\Support\Facades\Route::getRoutes();
        $maintenanceRouteExists = false;
        foreach ($routes as $route) {
            if (strpos($route->uri(), 'admin/quiz-maintenance') !== false) {
                $maintenanceRouteExists = true;
                break;
            }
        }
        
        if ($maintenanceRouteExists) {
            echo 'pass">';
            echo '<div class="status">✅ PASS</div>';
            echo '<strong>Test 7: Routes</strong><br>';
            echo 'Quiz maintenance routes are registered.<br>';
            echo 'Access at: <a href="/admin/quiz-maintenance">/admin/quiz-maintenance</a>';
        } else {
            echo 'fail">';
            echo '<div class="status">❌ FAIL</div>';
            echo '<strong>Test 7: Routes</strong><br>';
            echo 'Quiz maintenance routes not found. Run: <code>php artisan route:clear</code>';
            $allPassed = false;
        }
    } catch (Exception $e) {
        echo 'fail">';
        echo '<div class="status">❌ FAIL</div>';
        echo '<strong>Test 7: Routes</strong><br>';
        echo 'Error: ' . $e->getMessage();
        $allPassed = false;
    }
    echo '</div>';

    // Final Summary
    echo '<div class="test ' . ($allPassed ? 'pass' : 'fail') . '">';
    echo '<h2>' . ($allPassed ? '✅ All Tests Passed!' : '❌ Some Tests Failed') . '</h2>';
    if ($allPassed) {
        echo '<p>Your deployment is ready! You can now:</p>';
        echo '<ol>';
        echo '<li>Visit <a href="/admin/quiz-maintenance">/admin/quiz-maintenance</a> to use the web tool</li>';
        echo '<li>Or run <code>php artisan quiz:diagnose</code> via SSH</li>';
        echo '<li><strong>Delete this test file!</strong></li>';
        echo '</ol>';
    } else {
        echo '<p>Please fix the failed tests before proceeding:</p>';
        echo '<ol>';
        echo '<li>Verify all files are uploaded</li>';
        echo '<li>Run <code>php artisan config:clear</code></li>';
        echo '<li>Run <code>php artisan route:clear</code></li>';
        echo '<li>Check file permissions</li>';
        echo '<li>Refresh this page</li>';
        echo '</ol>';
    }
    echo '</div>';
    ?>

    <div class="delete-warning">
        <strong>🔒 IMPORTANT:</strong> Delete this test file now!<br>
        <code>rm test_quiz_fix_deployment.php</code><br>
        Or via FTP/File Manager: Delete <code>test_quiz_fix_deployment.php</code>
    </div>
</body>
</html>
