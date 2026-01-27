<?php

// Simple web-based route test for Missouri Form 4444 system
// Access via: http://your-domain.com/test-missouri-routes.php

?>
<!DOCTYPE html>
<html>
<head>
    <title>Missouri Routes Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .route { margin: 5px 0; padding: 5px; background: #f9f9f9; }
        .test-btn { background: #007cba; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; margin-left: 10px; }
    </style>
</head>
<body>
    <h1>Missouri Form 4444 Routes Test</h1>
    
    <?php
    
    // Include Laravel bootstrap
    require_once '../vendor/autoload.php';
    $app = require_once '../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo '<div class="section">';
    echo '<h2>Route Registration Check</h2>';
    
    // Get all registered routes
    $router = app('router');
    $routes = $router->getRoutes();
    
    $expectedRoutes = [
        'GET' => [
            'admin/missouri-forms' => 'Admin Interface',
            'missouri/form4444/{formId}/download' => 'Download PDF',
            'missouri/user/{userId}/forms' => 'User Forms',
            'missouri/submission-status/{userId}' => 'Submission Status',
            'api/missouri/forms/all' => 'All Forms API',
            'api/missouri/expiring-forms' => 'Expiring Forms API',
        ],
        'POST' => [
            'missouri/form4444/generate' => 'Generate Form',
            'missouri/form4444/{formId}/email' => 'Email Form',
            'missouri/form4444/{formId}/submit-dor' => 'Submit to DOR',
        ]
    ];
    
    foreach ($expectedRoutes as $method => $routeList) {
        echo "<h3>{$method} Routes:</h3>";
        foreach ($routeList as $uri => $description) {
            $found = false;
            $actualUri = '';
            
            foreach ($routes as $route) {
                $routeMethods = $route->methods();
                $routeUri = $route->uri();
                
                // Normalize URIs for comparison
                $normalizedExpected = str_replace(['{formId}', '{userId}'], ['{', '{'], $uri);
                $normalizedActual = str_replace(['formId}', 'userId}'], ['}', '}'], $routeUri);
                
                if (in_array($method, $routeMethods) && 
                    (strpos($normalizedActual, str_replace('{', '', str_replace('}', '', $normalizedExpected))) !== false ||
                     $routeUri === $uri)) {
                    $found = true;
                    $actualUri = $routeUri;
                    break;
                }
            }
            
            echo '<div class="route">';
            if ($found) {
                echo '<span class="success">✅</span> ';
                echo "<strong>{$uri}</strong> → {$description}";
                if ($actualUri !== $uri) {
                    echo " <small>(actual: {$actualUri})</small>";
                }
                
                // Add test links for GET routes
                if ($method === 'GET' && !strpos($uri, '{')) {
                    echo " <a href='/{$uri}' class='test-btn' target='_blank'>Test</a>";
                }
            } else {
                echo '<span class="error">❌</span> ';
                echo "<strong>{$uri}</strong> → {$description} <em>(MISSING)</em>";
            }
            echo '</div>';
        }
    }
    
    echo '</div>';
    
    // Test controller methods
    echo '<div class="section">';
    echo '<h2>Controller Method Check</h2>';
    
    try {
        $controller = new \App\Http\Controllers\MissouriController(new \App\Services\MissouriForm4444PdfService());
        
        $methods = [
            'generateForm4444' => 'Generate Form 4444',
            'downloadForm4444' => 'Download Form PDF',
            'emailForm4444' => 'Email Form',
            'getSubmissionStatus' => 'Get Submission Status',
            'submitToDOR' => 'Submit to DOR',
            'getUserForms' => 'Get User Forms',
            'getAllForms' => 'Get All Forms',
            'getExpiringForms' => 'Get Expiring Forms',
        ];
        
        foreach ($methods as $method => $description) {
            if (method_exists($controller, $method)) {
                echo '<div class="success">✅ ' . $method . '() → ' . $description . '</div>';
            } else {
                echo '<div class="error">❌ ' . $method . '() → ' . $description . ' (MISSING)</div>';
            }
        }
        
    } catch (\Exception $e) {
        echo '<div class="error">❌ Controller error: ' . $e->getMessage() . '</div>';
    }
    
    echo '</div>';
    
    // Test links
    echo '<div class="section">';
    echo '<h2>Quick Test Links</h2>';
    echo '<div class="info">';
    echo '<p><strong>Admin Interface:</strong> <a href="/admin/missouri-forms" target="_blank">/admin/missouri-forms</a></p>';
    echo '<p><strong>System Test:</strong> <a href="/test-missouri-system.php" target="_blank">/test-missouri-system.php</a></p>';
    echo '</div>';
    echo '</div>';
    
    ?>
    
    <div class="section">
        <h2>Status Summary</h2>
        <div class="info">
            <p>✅ = Route/Method exists and is properly registered</p>
            <p>❌ = Route/Method is missing or not registered</p>
            <p><strong>All items should show ✅ for the system to work correctly.</strong></p>
        </div>
    </div>
    
</body>
</html>