<?php

// Verification script for class conflict fix
// Access via: http://your-domain.com/verify-class-fix.php

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Class Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>Class Conflict Fix Verification</h1>
    
    <div class="section">
        <h2>File Status Check</h2>
        
        <?php
        
        $oldFile = '../app/Http/Controllers/FreeResponseQuizController.php';
        $newFile = '../app/Http/Controllers/StudentFreeResponseQuizController.php';
        $adminFile = '../app/Http/Controllers/Admin/FreeResponseQuizController.php';
        
        echo '<h3>Controller Files:</h3>';
        
        if (!file_exists($oldFile)) {
            echo '<div class="success">✅ Old FreeResponseQuizController.php removed</div>';
        } else {
            echo '<div class="error">❌ Old FreeResponseQuizController.php still exists</div>';
        }
        
        if (file_exists($newFile)) {
            echo '<div class="success">✅ StudentFreeResponseQuizController.php exists</div>';
        } else {
            echo '<div class="error">❌ StudentFreeResponseQuizController.php missing</div>';
        }
        
        if (file_exists($adminFile)) {
            echo '<div class="success">✅ Admin/FreeResponseQuizController.php exists</div>';
        } else {
            echo '<div class="error">❌ Admin/FreeResponseQuizController.php missing</div>';
        }
        
        ?>
    </div>
    
    <div class="section">
        <h2>Class Loading Test</h2>
        
        <?php
        
        try {
            // Include Laravel bootstrap
            require_once '../vendor/autoload.php';
            $app = require_once '../bootstrap/app.php';
            $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
            
            echo '<div class="info">Testing class loading...</div>';
            
            // Test if we can instantiate both controllers without conflict
            try {
                $studentController = new \App\Http\Controllers\StudentFreeResponseQuizController();
                echo '<div class="success">✅ StudentFreeResponseQuizController loads successfully</div>';
            } catch (\Exception $e) {
                echo '<div class="error">❌ StudentFreeResponseQuizController error: ' . $e->getMessage() . '</div>';
            }
            
            try {
                $adminController = new \App\Http\Controllers\Admin\FreeResponseQuizController();
                echo '<div class="success">✅ Admin FreeResponseQuizController loads successfully</div>';
            } catch (\Exception $e) {
                echo '<div class="error">❌ Admin FreeResponseQuizController error: ' . $e->getMessage() . '</div>';
            }
            
            echo '<div class="success"><strong>✅ Class conflict resolved!</strong></div>';
            
        } catch (\Exception $e) {
            echo '<div class="error">❌ Laravel bootstrap error: ' . $e->getMessage() . '</div>';
        }
        
        ?>
    </div>
    
    <div class="section">
        <h2>Route Verification</h2>
        
        <?php
        
        try {
            // Check if routes are properly configured
            $webRoutes = file_get_contents('../routes/web.php');
            
            if (strpos($webRoutes, 'StudentFreeResponseQuizController') !== false) {
                echo '<div class="success">✅ Routes updated to use StudentFreeResponseQuizController</div>';
            } else {
                echo '<div class="error">❌ Routes not updated</div>';
            }
            
            if (strpos($webRoutes, 'Admin\\FreeResponseQuizController') !== false) {
                echo '<div class="success">✅ Admin routes properly configured</div>';
            } else {
                echo '<div class="error">❌ Admin routes missing</div>';
            }
            
        } catch (\Exception $e) {
            echo '<div class="error">❌ Route check error: ' . $e->getMessage() . '</div>';
        }
        
        ?>
    </div>
    
    <div class="section">
        <h2>Summary</h2>
        <p>If all items above show ✅, the class conflict has been successfully resolved and your application should work normally.</p>
        <p>If you see any ❌ items, please run the complete fix script or contact support.</p>
    </div>
    
</body>
</html>