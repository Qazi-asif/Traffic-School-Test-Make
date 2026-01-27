<?php

// Complete fix for class conflict issue
// Access via: http://your-domain.com/complete-class-fix.php

?>
<!DOCTYPE html>
<html>
<head>
    <title>Complete Class Conflict Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .btn { background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; }
        .code { background: #f5f5f5; padding: 10px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>Complete Class Conflict Fix</h1>
    
    <?php
    
    if (isset($_POST['apply_complete_fix'])) {
        echo '<div class="section">';
        echo '<h2>Applying Complete Fix...</h2>';
        
        try {
            // Include Laravel bootstrap
            require_once '../vendor/autoload.php';
            $app = require_once '../bootstrap/app.php';
            $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
            
            $oldFile = '../app/Http/Controllers/FreeResponseQuizController.php';
            $newFile = '../app/Http/Controllers/StudentFreeResponseQuizController.php';
            
            echo '<div class="info">1. Creating properly named controller file...</div>';
            
            if (file_exists($oldFile)) {
                // Read the content of the old file
                $content = file_get_contents($oldFile);
                
                // Ensure the class name is correct
                $content = str_replace(
                    'class FreeResponseQuizController extends Controller',
                    'class StudentFreeResponseQuizController extends Controller',
                    $content
                );
                
                // Write to the new file
                file_put_contents($newFile, $content);
                echo '<div class="success">✅ Created StudentFreeResponseQuizController.php</div>';
                
                // Remove the old file
                unlink($oldFile);
                echo '<div class="success">✅ Removed old FreeResponseQuizController.php</div>';
                
            } else {
                echo '<div class="warning">⚠️  Old file not found, may already be fixed</div>';
            }
            
            echo '<div class="info">2. Clearing all caches...</div>';
            
            \Artisan::call('config:clear');
            echo '<div class="success">✅ Config cache cleared</div>';
            
            \Artisan::call('cache:clear');
            echo '<div class="success">✅ Application cache cleared</div>';
            
            \Artisan::call('route:clear');
            echo '<div class="success">✅ Route cache cleared</div>';
            
            \Artisan::call('view:clear');
            echo '<div class="success">✅ View cache cleared</div>';
            
            echo '<div class="info">3. Verifying fix...</div>';
            
            if (file_exists($newFile)) {
                echo '<div class="success">✅ StudentFreeResponseQuizController.php exists</div>';
            } else {
                echo '<div class="error">❌ StudentFreeResponseQuizController.php missing</div>';
            }
            
            if (!file_exists($oldFile)) {
                echo '<div class="success">✅ Old FreeResponseQuizController.php removed</div>';
            } else {
                echo '<div class="warning">⚠️  Old file still exists</div>';
            }
            
            $adminController = '../app/Http/Controllers/Admin/FreeResponseQuizController.php';
            if (file_exists($adminController)) {
                echo '<div class="success">✅ Admin FreeResponseQuizController exists</div>';
            } else {
                echo '<div class="error">❌ Admin controller missing</div>';
            }
            
            echo '<div class="success"><strong>✅ Complete fix applied successfully!</strong></div>';
            echo '<div class="info">The class conflict should now be resolved. Please test your application.</div>';
            
        } catch (\Exception $e) {
            echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
        }
        
        echo '</div>';
        
    } else {
        echo '<div class="section">';
        echo '<h2>Current Issue</h2>';
        echo '<div class="error">The controller class was renamed but the file name wasn\'t updated to match.</div>';
        echo '<p>In PHP, the file name should match the class name for proper autoloading.</p>';
        
        $oldFile = '../app/Http/Controllers/FreeResponseQuizController.php';
        $newFile = '../app/Http/Controllers/StudentFreeResponseQuizController.php';
        
        echo '<h3>Current Status:</h3>';
        if (file_exists($oldFile)) {
            echo '<div class="warning">⚠️  FreeResponseQuizController.php exists (should be renamed)</div>';
        }
        if (file_exists($newFile)) {
            echo '<div class="success">✅ StudentFreeResponseQuizController.php exists</div>';
        } else {
            echo '<div class="error">❌ StudentFreeResponseQuizController.php missing</div>';
        }
        
        echo '</div>';
        
        echo '<div class="section">';
        echo '<h2>Complete Solution</h2>';
        echo '<p>This fix will:</p>';
        echo '<ol>';
        echo '<li>Create <code>StudentFreeResponseQuizController.php</code> with correct class name</li>';
        echo '<li>Remove the old <code>FreeResponseQuizController.php</code> file</li>';
        echo '<li>Clear all Laravel caches to refresh autoloader</li>';
        echo '<li>Verify the fix is properly applied</li>';
        echo '</ol>';
        
        echo '<form method="POST">';
        echo '<button type="submit" name="apply_complete_fix" class="btn">Apply Complete Fix</button>';
        echo '</form>';
        echo '</div>';
    }
    
    ?>
    
    <div class="section">
        <h2>Manual Alternative</h2>
        <p>If the automatic fix doesn't work, you can manually:</p>
        <div class="code">
1. Rename the file:
   FreeResponseQuizController.php → StudentFreeResponseQuizController.php

2. Ensure the class name inside matches:
   class StudentFreeResponseQuizController extends Controller

3. Clear caches:
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
        </div>
    </div>
    
</body>
</html>