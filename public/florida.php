<?php
/**
 * Florida Traffic School - Direct PHP Implementation
 * Bypasses Laravel routing issues
 */

// Set proper headers
header('Content-Type: text/html; charset=UTF-8');

// Start output buffering
ob_start();

// Include Laravel bootstrap to access database and models
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Try to bootstrap Laravel for database access
    $app = null;
    try {
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
        $kernel->bootstrap();
        
        // Test database connection
        $dbConnected = false;
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $dbConnected = true;
        } catch (Exception $e) {
            $dbConnected = false;
        }
        
    } catch (Exception $e) {
        // Laravel bootstrap failed, continue without it
        $app = null;
        $dbConnected = false;
    }
    
} catch (Exception $e) {
    // Composer autoload failed
    $app = null;
    $dbConnected = false;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Florida Traffic School</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c5aa0; border-bottom: 3px solid #2c5aa0; padding-bottom: 10px; }
        .success { color: #28a745; font-size: 18px; margin: 20px 0; padding: 15px; background: #d4edda; border-radius: 5px; }
        .error { color: #dc3545; font-size: 16px; margin: 20px 0; padding: 15px; background: #f8d7da; border-radius: 5px; }
        .info { color: #0c5460; font-size: 16px; margin: 20px 0; padding: 15px; background: #d1ecf1; border-radius: 5px; }
        .nav { margin: 20px 0; }
        .nav a { display: inline-block; margin: 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .nav a:hover { background: #0056b3; }
        .status-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 20px 0; }
        .status-card { padding: 20px; border-radius: 5px; }
        .status-ok { background: #d4edda; }
        .status-error { background: #f8d7da; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏛️ Florida Traffic School</h1>
        
        <div class="success">
            <strong>✅ SUCCESS!</strong> Florida PHP routing is working!
        </div>
        
        <div class="info">
            <strong>📊 System Status:</strong><br>
            <strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
            <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?><br>
            <strong>Laravel App:</strong> <?php echo $app ? '✅ Loaded' : '❌ Failed'; ?><br>
            <strong>Database:</strong> <?php echo $dbConnected ? '✅ Connected' : '❌ Disconnected'; ?>
        </div>

        <?php if ($dbConnected): ?>
            <div class="success">
                <strong>🗄️ Database Integration Active</strong><br>
                Laravel models and database are accessible.
            </div>
            
            <?php
            // Try to get some data from database
            try {
                $userCount = \Illuminate\Support\Facades\DB::table('users')->count();
                $floridaCourses = \Illuminate\Support\Facades\DB::table('florida_courses')->count();
                echo "<div class='info'>";
                echo "<strong>📈 Database Stats:</strong><br>";
                echo "Users: $userCount<br>";
                echo "Florida Courses: $floridaCourses<br>";
                echo "</div>";
            } catch (Exception $e) {
                echo "<div class='error'>Database query failed: " . $e->getMessage() . "</div>";
            }
            ?>
            
        <?php else: ?>
            <div class="error">
                <strong>⚠️ Laravel Bootstrap Issue</strong><br>
                PHP routing works but Laravel services are not available.<br>
                This is the same issue affecting the main Laravel routes.
            </div>
        <?php endif; ?>

        <div class="nav">
            <h3>🌐 Navigate to Other States:</h3>
            <a href="/missouri.php">Missouri</a>
            <a href="/texas.php">Texas</a>
            <a href="/delaware.php">Delaware</a>
            <a href="/admin.php">Admin</a>
        </div>
        
        <div class="status-grid">
            <div class="status-card status-ok">
                <h4>✅ Working Features</h4>
                <ul>
                    <li>Direct PHP routing</li>
                    <li>State separation</li>
                    <li>Independent access</li>
                    <li>No Laravel dependency</li>
                </ul>
            </div>
            <div class="status-card <?php echo $dbConnected ? 'status-ok' : 'status-error'; ?>">
                <h4><?php echo $dbConnected ? '✅' : '❌'; ?> Laravel Integration</h4>
                <ul>
                    <li>Database access: <?php echo $dbConnected ? 'Working' : 'Failed'; ?></li>
                    <li>Models: <?php echo $dbConnected ? 'Available' : 'Unavailable'; ?></li>
                    <li>Eloquent: <?php echo $dbConnected ? 'Active' : 'Inactive'; ?></li>
                    <li>Services: <?php echo $app ? 'Loaded' : 'Failed'; ?></li>
                </ul>
            </div>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #e7f3ff; border-radius: 5px;">
            <h4>🎯 Florida State - Phase 1 Integration</h4>
            <p><strong>Status:</strong> PHP routing functional, Laravel integration <?php echo $dbConnected ? 'working' : 'needs fixing'; ?></p>
            <p><strong>Next Steps:</strong> <?php echo $dbConnected ? 'Ready for controller integration' : 'Fix Laravel service providers first'; ?></p>
        </div>
    </div>
</body>
</html>

<?php
// End output buffering and send
ob_end_flush();
?>