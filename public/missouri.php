<?php
/**
 * Missouri Traffic School - Direct PHP Implementation
 */

header('Content-Type: text/html; charset=UTF-8');
ob_start();

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    
    $dbConnected = true;
    \Illuminate\Support\Facades\DB::connection()->getPdo();
} catch (Exception $e) {
    $app = null;
    $dbConnected = false;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Missouri Traffic School</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #28a745; border-bottom: 3px solid #28a745; padding-bottom: 10px; }
        .success { color: #28a745; font-size: 18px; margin: 20px 0; padding: 15px; background: #d4edda; border-radius: 5px; }
        .info { color: #0c5460; font-size: 16px; margin: 20px 0; padding: 15px; background: #d1ecf1; border-radius: 5px; }
        .nav { margin: 20px 0; }
        .nav a { display: inline-block; margin: 10px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; }
        .nav a:hover { background: #1e7e34; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏛️ Missouri Traffic School</h1>
        
        <div class="success">
            <strong>✅ SUCCESS!</strong> Missouri PHP routing is working!
        </div>
        
        <div class="info">
            <strong>📊 System Status:</strong><br>
            <strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
            <strong>Database:</strong> <?php echo $dbConnected ? '✅ Connected' : '❌ Disconnected'; ?>
        </div>

        <?php if ($dbConnected): ?>
            <?php
            try {
                $missouriCourses = \Illuminate\Support\Facades\DB::table('missouri_courses')->count();
                echo "<div class='info'><strong>Missouri Courses:</strong> $missouriCourses</div>";
            } catch (Exception $e) {
                echo "<div class='info'>Missouri courses table: Not accessible</div>";
            }
            ?>
        <?php endif; ?>

        <div class="nav">
            <h3>🌐 Navigate to Other States:</h3>
            <a href="/florida.php">Florida</a>
            <a href="/texas.php">Texas</a>
            <a href="/delaware.php">Delaware</a>
            <a href="/admin.php">Admin</a>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #d4edda; border-radius: 5px;">
            <h4>🎯 Missouri State - Phase 1 Integration</h4>
            <p><strong>Status:</strong> PHP routing functional</p>
        </div>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>