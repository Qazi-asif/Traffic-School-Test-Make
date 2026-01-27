<?php
// Simple admin maintenance mode control
// Access: http://127.0.0.1:8000/maintenance-admin.php

$downFile = __DIR__ . '/../storage/framework/down';
$action = $_GET['action'] ?? null;

// Handle enable/disable
if ($action === 'enable') {
    file_put_contents($downFile, json_encode(['time' => time(), 'message' => 'Site under maintenance']));
    header('Location: maintenancecbfbvib4767436667gdgdggdgfgfdfghdgh.php?status=enabled');
    exit;
}

if ($action === 'disable') {
    if (file_exists($downFile)) {
        unlink($downFile);
    }
    header('Location: maintenancecbfbvib4767436667gdgdggdgfgfdfghdgh.php?status=disabled');
    exit;
}

$isEnabled = file_exists($downFile);
$status = $_GET['status'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Maintenance Mode Control</title>
    <style>
        body { font-family: Arial; margin: 50px; }
        .status { padding: 20px; border-radius: 5px; margin: 20px 0; }
        .enabled { background: #ffcccc; color: #cc0000; }
        .disabled { background: #ccffcc; color: #00cc00; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔧 Maintenance Mode Control</h1>
    
    <div class="status <?php echo $isEnabled ? 'enabled' : 'disabled'; ?>">
        <strong>Status:</strong> <?php echo $isEnabled ? '🔴 MAINTENANCE MODE ON' : '🟢 SITE ONLINE'; ?>
    </div>

    <?php if ($status === 'enabled'): ?>
        <p class="success">✓ Maintenance mode ENABLED - Normal users see 503 page</p>
    <?php elseif ($status === 'disabled'): ?>
        <p class="success">✓ Maintenance mode DISABLED - Site is online</p>
    <?php endif; ?>

    <div>
        <?php if ($isEnabled): ?>
            <a href="?action=disable"><button style="background: #00cc00; color: white;">🟢 DISABLE Maintenance Mode</button></a>
        <?php else: ?>
            <a href="?action=enable"><button style="background: #cc0000; color: white;">🔴 ENABLE Maintenance Mode</button></a>
        <?php endif; ?>
    </div>

    <hr>
    <p><small>Admin only - Keep this URL secret!</small></p>
</body>
</html>
