<?php
header('Content-Type: text/html; charset=UTF-8');
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    $dbConnected = true;
} catch (Exception $e) {
    $dbConnected = false;
}
?>
<!DOCTYPE html>
<html>
<head><title>Texas Traffic School</title>
<style>
body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
.container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
h1 { color: #ffc107; border-bottom: 3px solid #ffc107; padding-bottom: 10px; }
.success { color: #856404; background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; }
.nav a { display: inline-block; margin: 10px; padding: 10px 20px; background: #ffc107; color: #212529; text-decoration: none; border-radius: 5px; }
</style></head>
<body>
<div class="container">
<h1>🏛️ Texas Traffic School</h1>
<div class="success"><strong>✅ SUCCESS!</strong> Texas PHP routing working!</div>
<p><strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
<p><strong>Database:</strong> <?php echo $dbConnected ? '✅ Connected' : '❌ Disconnected'; ?></p>
<div class="nav">
<a href="/florida.php">Florida</a>
<a href="/missouri.php">Missouri</a>
<a href="/delaware.php">Delaware</a>
<a href="/admin.php">Admin</a>
</div>
</div>
</body>
</html>