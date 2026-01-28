<?php
/**
 * Simple Test Page
 * Direct access test to verify Laravel is working
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laravel Test Page</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        .test-item { margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Laravel Application Test</h1>
        <p>This page tests if your Laravel application is working correctly.</p>
        
        <div class="test-item">
            <strong>✅ PHP Version:</strong> <?php echo phpversion(); ?>
        </div>
        
        <div class="test-item">
            <strong>✅ Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
        </div>
        
        <div class="test-item">
            <strong>✅ Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?>
        </div>
        
        <div class="test-item">
            <strong>✅ Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?>
        </div>
        
        <div class="test-item">
            <strong>✅ Laravel Directory:</strong> 
            <?php 
            if (file_exists('../bootstrap/app.php')) {
                echo '<span class="success">Found</span>';
            } else {
                echo '<span class="error">Not Found</span>';
            }
            ?>
        </div>
        
        <div class="test-item">
            <strong>✅ Routes File:</strong> 
            <?php 
            if (file_exists('../routes/web.php')) {
                echo '<span class="success">Found</span>';
            } else {
                echo '<span class="error">Not Found</span>';
            }
            ?>
        </div>
        
        <h2>🔗 Test Links</h2>
        <div class="test-item">
            <p>If Laravel is working, these links should work:</p>
            <ul>
                <li><a href="/florida/login">Florida Login</a></li>
                <li><a href="/missouri/login">Missouri Login</a></li>
                <li><a href="/texas/login">Texas Login</a></li>
                <li><a href="/delaware/login">Delaware Login</a></li>
            </ul>
        </div>
        
        <h2>🔑 Test Credentials</h2>
        <div class="test-item">
            <p><strong>Email:</strong> florida@test.com</p>
            <p><strong>Password:</strong> password123</p>
        </div>
        
        <h2>🎯 What's Ready</h2>
        <div class="test-item">
            <ul>
                <li>✅ Multi-state authentication system</li>
                <li>✅ Course progress tracking</li>
                <li>✅ Certificate generation</li>
                <li>✅ State-specific dashboards</li>
                <li>✅ Progress monitoring APIs</li>
            </ul>
        </div>
        
        <div class="test-item">
            <p class="info">
                <strong>Next Step:</strong> Try clicking one of the login links above to test the Laravel application!
            </p>
        </div>
    </div>
</body>
</html>