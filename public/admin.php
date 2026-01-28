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
<head><title>Admin Dashboard</title>
<style>
body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
.container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
h1 { color: #6c757d; border-bottom: 3px solid #6c757d; padding-bottom: 10px; }
.success { color: #495057; background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
.nav a { display: inline-block; margin: 10px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; }
.states { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 20px 0; }
.state-card { padding: 20px; border-radius: 5px; text-align: center; }
</style></head>
<body>
<div class="container">
<h1>🏛️ Admin Dashboard</h1>
<div class="success"><strong>✅ SUCCESS!</strong> Admin PHP routing working!</div>
<p><strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
<p><strong>Database:</strong> <?php echo $dbConnected ? '✅ Connected' : '❌ Disconnected'; ?></p>
<div class="states">
<div class="state-card" style="background: #e7f3ff;"><h3>Florida</h3><a href="/florida.php">Manage</a></div>
<div class="state-card" style="background: #d4edda;"><h3>Missouri</h3><a href="/missouri.php">Manage</a></div>
<div class="state-card" style="background: #fff3cd;"><h3>Texas</h3><a href="/texas.php">Manage</a></div>
<div class="state-card" style="background: #d1ecf1;"><h3>Delaware</h3><a href="/delaware.php">Manage</a></div>
</div>
</div>
</body>
</html>