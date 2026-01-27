<?php

// Simple web-based test for Missouri Form 4444 system
// Access via: http://your-domain.com/test-missouri-system.php

?>
<!DOCTYPE html>
<html>
<head>
    <title>Missouri Form 4444 System Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Missouri Form 4444 System Test</h1>
    
    <?php
    
    // Include Laravel bootstrap
    require_once '../vendor/autoload.php';
    $app = require_once '../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo '<div class="section">';
    echo '<h2>1. File Existence Check</h2>';
    
    $requiredFiles = [
        '../app/Services/MissouriForm4444PdfService.php',
        '../resources/views/certificates/missouri-form-4444.blade.php',
        '../resources/views/emails/missouri-form-4444.blade.php',
        '../app/Listeners/GenerateMissouriForm4444.php',
        '../resources/views/admin/missouri-forms.blade.php',
    ];
    
    foreach ($requiredFiles as $file) {
        if (file_exists($file)) {
            echo '<div class="success">✅ ' . basename($file) . '</div>';
        } else {
            echo '<div class="error">❌ ' . basename($file) . ' - MISSING</div>';
        }
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>2. Database Check</h2>';
    
    try {
        // Check if Missouri tables exist
        $tables = ['missouri_form4444s', 'missouri_submission_trackers'];
        foreach ($tables as $table) {
            $exists = \DB::select("SHOW TABLES LIKE '{$table}'");
            if ($exists) {
                echo '<div class="success">✅ Table: ' . $table . '</div>';
            } else {
                echo '<div class="error">❌ Table missing: ' . $table . '</div>';
            }
        }
        
        // Check if we have any Missouri courses
        $missouriCourses = \DB::table('courses')->where('state', 'Missouri')->count();
        echo '<div class="info">📊 Missouri courses found: ' . $missouriCourses . '</div>';
        
    } catch (\Exception $e) {
        echo '<div class="error">❌ Database error: ' . $e->getMessage() . '</div>';
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>3. Class Loading Check</h2>';
    
    try {
        $pdfService = new \App\Services\MissouriForm4444PdfService();
        echo '<div class="success">✅ MissouriForm4444PdfService loads correctly</div>';
    } catch (\Exception $e) {
        echo '<div class="error">❌ MissouriForm4444PdfService error: ' . $e->getMessage() . '</div>';
    }
    
    try {
        $listener = new \App\Listeners\GenerateMissouriForm4444(new \App\Services\MissouriForm4444PdfService());
        echo '<div class="success">✅ GenerateMissouriForm4444 listener loads correctly</div>';
    } catch (\Exception $e) {
        echo '<div class="error">❌ GenerateMissouriForm4444 listener error: ' . $e->getMessage() . '</div>';
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>4. Route Check</h2>';
    
    $webRoutes = file_get_contents('../routes/web.php');
    if (strpos($webRoutes, 'missouri/form4444') !== false) {
        echo '<div class="success">✅ Missouri routes found in web.php</div>';
    } else {
        echo '<div class="error">❌ Missouri routes missing from web.php</div>';
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>5. Test Form Generation</h2>';
    
    try {
        // Find or create a test Missouri course
        $course = \DB::table('courses')->where('state', 'Missouri')->first();
        if (!$course) {
            $courseId = \DB::table('courses')->insertGetId([
                'title' => 'Test Missouri Course',
                'state' => 'Missouri',
                'duration' => 480,
                'price' => 24.95,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo '<div class="info">📝 Created test Missouri course (ID: ' . $courseId . ')</div>';
        } else {
            echo '<div class="success">✅ Found Missouri course: ' . $course->title . '</div>';
        }
        
        // Check if we can create a Form 4444 record
        $testForm = new \App\Models\MissouriForm4444();
        echo '<div class="success">✅ MissouriForm4444 model accessible</div>';
        
    } catch (\Exception $e) {
        echo '<div class="error">❌ Test generation error: ' . $e->getMessage() . '</div>';
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>6. Next Steps</h2>';
    echo '<div class="info">';
    echo '<p><strong>If all checks pass:</strong></p>';
    echo '<ul>';
    echo '<li>✅ System is ready to use</li>';
    echo '<li>🔗 Admin interface: <a href="/admin/missouri-forms">/admin/missouri-forms</a></li>';
    echo '<li>📝 Test by completing a Missouri course</li>';
    echo '</ul>';
    echo '</div>';
    echo '</div>';
    
    ?>
    
    <div class="section">
        <h2>7. Manual Test</h2>
        <p>To manually test the system:</p>
        <ol>
            <li>Create a Missouri course (state = 'Missouri')</li>
            <li>Enroll a student in the course</li>
            <li>Mark the course as completed</li>
            <li>Check if Form 4444 is automatically generated</li>
            <li>Visit the admin interface to manage forms</li>
        </ol>
    </div>
    
</body>
</html>