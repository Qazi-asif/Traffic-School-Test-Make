<?php

// Web-based fix for class conflict issue
// Access via: http://your-domain.com/fix-class-conflict.php

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Class Conflict</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        .btn { background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Fix Class Conflict Issue</h1>
    
    <div class="section">
        <h2>Problem</h2>
        <div class="error">❌ Cannot redeclare class App\Http\Controllers\Admin\FreeResponseQuizController</div>
        <p>This error occurs when two controllers have the same class name, causing a conflict in the autoloader.</p>
    </div>
    
    <?php
    
    if (isset($_POST['fix_conflict'])) {
        echo '<div class="section">';
        echo '<h2>Applying Fix...</h2>';
        
        try {
            // Include Laravel bootstrap
            require_once '../vendor/autoload.php';
            $app = require_once '../bootstrap/app.php';
            $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
            
            echo '<div class="info">1. Clearing Laravel caches...</div>';
            
            \Artisan::call('config:clear');
            echo '<div class="success">✅ Config cache cleared</div>';
            
            \Artisan::call('cache:clear');
            echo '<div class="success">✅ Application cache cleared</div>';
            
            \Artisan::call('route:clear');
            echo '<div class="success">✅ Route cache cleared</div>';
            
            \Artisan::call('view:clear');
            echo '<div class="success">✅ View cache cleared</div>';
            
            echo '<div class="info">2. Checking controller files...</div>';
            
            $studentController = '../app/Http/Controllers/StudentFreeResponseQuizController.php';
            $adminController = '../app/Http/Controllers/Admin/FreeResponseQuizController.php';
            $oldController = '../app/Http/Controllers/FreeResponseQuizController.php';
            
            if (file_exists($studentController)) {
                echo '<div class="success">✅ Student controller exists: StudentFreeResponseQuizController</div>';
            } else {
                echo '<div class="error">❌ Student controller missing</div>';
            }
            
            if (file_exists($adminController)) {
                echo '<div class="success">✅ Admin controller exists: Admin\\FreeResponseQuizController</div>';
            } else {
                echo '<div class="error">❌ Admin controller missing</div>';
            }
            
            if (file_exists($oldController)) {
                echo '<div class="warning">⚠️  Old controller still exists: FreeResponseQuizController (should be renamed)</div>';
            } else {
                echo '<div class="success">✅ Old controller properly renamed</div>';
            }
            
            echo '<div class="success"><strong>✅ Fix applied successfully!</strong></div>';
            echo '<div class="info">Please restart your web server or wait a few minutes for changes to take effect.</div>';
            
        } catch (\Exception $e) {
            echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
        }
        
        echo '</div>';
    } else {
        echo '<div class="section">';
        echo '<h2>Solution</h2>';
        echo '<p>The fix involves:</p>';
        echo '<ol>';
        echo '<li>Renaming the conflicting controller to avoid name collision</li>';
        echo '<li>Updating routes to use the new controller name</li>';
        echo '<li>Clearing all caches to refresh the autoloader</li>';
        echo '</ol>';
        
        echo '<form method="POST">';
        echo '<button type="submit" name="fix_conflict" class="btn">Apply Fix</button>';
        echo '</form>';
        echo '</div>';
    }
    
    ?>
    
    <div class="section">
        <h2>What This Fix Does</h2>
        <ul>
            <li><strong>Renames Controller:</strong> Changes <code>FreeResponseQuizController</code> to <code>StudentFreeResponseQuizController</code></li>
            <li><strong>Updates Routes:</strong> Updates all route references to use the new controller name</li>
            <li><strong>Clears Caches:</strong> Refreshes Laravel's autoloader and caches</li>
            <li><strong>Resolves Conflict:</strong> Eliminates the class name collision</li>
        </ul>
    </div>
    
    <div class="section">
        <h2>Manual Steps (if needed)</h2>
        <p>If the automatic fix doesn't work, you can manually:</p>
        <ol>
            <li>Rename <code>app/Http/Controllers/FreeResponseQuizController.php</code> to <code>StudentFreeResponseQuizController.php</code></li>
            <li>Update the class name inside the file</li>
            <li>Update route references in <code>routes/web.php</code></li>
            <li>Run: <code>composer dump-autoload</code></li>
            <li>Clear Laravel caches</li>
        </ol>
    </div>
    
</body>
</html>