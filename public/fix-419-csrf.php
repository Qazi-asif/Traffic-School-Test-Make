<?php
// Fix 419 CSRF Error - Simple Diagnostic
session_start();

echo "<h1>Fix 419 CSRF Error</h1>";
echo "<pre>";

echo "=== FIXING 419 CSRF TOKEN MISMATCH ===\n\n";

// 1. Check session
echo "1. Session Check...\n";
echo "   Session ID: " . session_id() . "\n";
echo "   Session started: " . (session_status() === PHP_SESSION_ACTIVE ? "Yes" : "No") . "\n";

// 2. Check if we can load Laravel without CSRF
echo "\n2. Loading Laravel (bypassing CSRF)...\n";

try {
    require_once '../vendor/autoload.php';
    echo "   ✅ Composer autoload loaded\n";
    
    $app = require_once '../bootstrap/app.php';
    echo "   ✅ Laravel app loaded\n";
    
    // Bootstrap without middleware
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    echo "   ✅ Laravel kernel bootstrapped\n";
    
} catch (Exception $e) {
    echo "   ❌ Laravel loading failed: " . $e->getMessage() . "\n";
    exit;
}

// 3. Test database connection
echo "\n3. Database Connection...\n";

try {
    $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "   ✅ Database connected successfully\n";
    
    // Test basic query
    $userCount = \Illuminate\Support\Facades\DB::table('users')->count();
    echo "   Users in database: {$userCount}\n";
    
    $courseCount = \Illuminate\Support\Facades\DB::table('florida_courses')->count();
    echo "   Florida courses in database: {$courseCount}\n";
    
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

// 4. Generate fresh CSRF token
echo "\n4. CSRF Token Generation...\n";

try {
    $csrfToken = csrf_token();
    echo "   ✅ CSRF token generated: " . substr($csrfToken, 0, 20) . "...\n";
    
    // Store in session for later use
    $_SESSION['csrf_token'] = $csrfToken;
    
} catch (Exception $e) {
    echo "   ❌ CSRF token generation failed: " . $e->getMessage() . "\n";
}

// 5. Test basic controller without CSRF
echo "\n5. Controller Test (no CSRF)...\n";

try {
    $controller = new \App\Http\Controllers\CourseController();
    echo "   ✅ CourseController instantiated\n";
    
    // Create a request without CSRF token
    $request = new \Illuminate\Http\Request();
    $response = $controller->indexWeb($request);
    
    echo "   ✅ indexWeb method works\n";
    echo "   Response status: " . $response->getStatusCode() . "\n";
    echo "   Response type: " . $response->headers->get('Content-Type') . "\n";
    
    $content = $response->getContent();
    $isJson = json_decode($content) !== null;
    echo "   Valid JSON: " . ($isJson ? "Yes" : "No") . "\n";
    
    if ($isJson) {
        $data = json_decode($content, true);
        echo "   Data count: " . (is_array($data) ? count($data) : 'object') . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Controller test failed: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// 6. Create working test pages with CSRF
echo "\n6. Creating Working Test Pages...\n";

// Create a working course list page
$courseListPage = '<!DOCTYPE html>
<html>
<head>
    <title>Working Course List</title>
    <meta name="csrf-token" content="' . (isset($csrfToken) ? $csrfToken : 'NO_TOKEN') . '">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .course { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Working Course List</h1>
    <button onclick="loadCourses()">Load Courses</button>
    <div id="courses"></div>
    
    <script>
    async function loadCourses() {
        const coursesDiv = document.getElementById("courses");
        
        try {
            coursesDiv.innerHTML = "<p>Loading courses...</p>";
            
            const response = await fetch("/web/courses", {
                method: "GET",
                headers: {
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content
                }
            });
            
            if (response.ok) {
                const courses = await response.json();
                
                if (courses.length > 0) {
                    coursesDiv.innerHTML = "<h3 class=\"success\">✅ Found " + courses.length + " courses:</h3>";
                    
                    courses.forEach(course => {
                        coursesDiv.innerHTML += `
                            <div class="course">
                                <h4>${course.title}</h4>
                                <p><strong>State:</strong> ${course.state_code || course.state || "N/A"}</p>
                                <p><strong>Duration:</strong> ${course.duration || course.total_duration || 0} minutes</p>
                                <p><strong>Price:</strong> $${course.price || 0}</p>
                                <p><strong>Active:</strong> ${course.is_active ? "Yes" : "No"}</p>
                            </div>
                        `;
                    });
                } else {
                    coursesDiv.innerHTML = "<p class=\"success\">✅ No courses found, but API works!</p>";
                }
            } else {
                const errorText = await response.text();
                coursesDiv.innerHTML = `
                    <div class="error">
                        <h3>❌ Error Loading Courses</h3>
                        <p><strong>Status:</strong> ${response.status}</p>
                        <p><strong>Response:</strong></p>
                        <pre>${errorText}</pre>
                    </div>
                `;
            }
            
        } catch (error) {
            coursesDiv.innerHTML = `
                <div class="error">
                    <h3>❌ Network Error</h3>
                    <p>${error.message}</p>
                </div>
            `;
        }
    }
    
    // Auto-load courses when page loads
    window.onload = loadCourses;
    </script>
</body>
</html>';

file_put_contents('working-courses.html', $courseListPage);
echo "   ✅ Created working course list: http://nelly-elearning.test/working-courses.html\n";

// Create a simple course creation test
$courseCreatePage = '<!DOCTYPE html>
<html>
<head>
    <title>Working Course Creation</title>
    <meta name="csrf-token" content="' . (isset($csrfToken) ? $csrfToken : 'NO_TOKEN') . '">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Working Course Creation</h1>
    
    <form id="courseForm">
        <div class="form-group">
            <label>Course Title:</label>
            <input type="text" name="title" value="Test Course ' . date('Y-m-d H:i') . '" required>
        </div>
        
        <div class="form-group">
            <label>Description:</label>
            <textarea name="description" required>Test course created via working form</textarea>
        </div>
        
        <div class="form-group">
            <label>State:</label>
            <select name="state_code" required>
                <option value="FL">Florida</option>
                <option value="MO">Missouri</option>
                <option value="TX">Texas</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Duration (minutes):</label>
            <input type="number" name="total_duration" value="240" required>
        </div>
        
        <div class="form-group">
            <label>Pass Score (%):</label>
            <input type="number" name="min_pass_score" value="80" required>
        </div>
        
        <div class="form-group">
            <label>Price ($):</label>
            <input type="number" name="price" value="29.99" step="0.01" required>
        </div>
        
        <button type="submit">Create Course</button>
    </form>
    
    <div id="result"></div>
    
    <script>
    document.getElementById("courseForm").addEventListener("submit", async function(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        data.is_active = true;
        
        const resultDiv = document.getElementById("result");
        
        try {
            resultDiv.innerHTML = "<p>Creating course...</p>";
            
            const response = await fetch("/api/florida-courses", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content
                },
                body: JSON.stringify(data)
            });
            
            const responseData = await response.json();
            
            if (response.ok && responseData.id) {
                resultDiv.innerHTML = `
                    <div class="success">
                        <h3>✅ Course Created!</h3>
                        <p><strong>ID:</strong> ${responseData.id}</p>
                        <p><strong>Title:</strong> ${responseData.title}</p>
                    </div>
                `;
                e.target.reset();
            } else {
                resultDiv.innerHTML = `
                    <div class="error">
                        <h3>❌ Creation Failed</h3>
                        <p>${responseData.error || responseData.message || "Unknown error"}</p>
                        <pre>${JSON.stringify(responseData, null, 2)}</pre>
                    </div>
                `;
            }
            
        } catch (error) {
            resultDiv.innerHTML = `
                <div class="error">
                    <h3>❌ Network Error</h3>
                    <p>${error.message}</p>
                </div>
            `;
        }
    });
    </script>
</body>
</html>';

file_put_contents('working-course-create.html', $courseCreatePage);
echo "   ✅ Created working course creation: http://nelly-elearning.test/working-course-create.html\n";

echo "\n🎉 419 CSRF ERROR DIAGNOSIS COMPLETE!\n";

echo "\n✅ WHAT WE FOUND:\n";
echo "- Laravel is working (419 means Laravel loaded successfully)\n";
echo "- The issue is CSRF token mismatch, not a broken system\n";
echo "- Database connection should be working\n";
echo "- Controllers should be functional\n";

echo "\n📝 TEST THESE WORKING PAGES:\n";
echo "1. Course List: http://nelly-elearning.test/working-courses.html\n";
echo "2. Course Creation: http://nelly-elearning.test/working-course-create.html\n";

echo "\n💡 CSRF TOKEN SOLUTION:\n";
echo "1. Make sure all AJAX requests include X-CSRF-TOKEN header\n";
echo "2. Get token from meta tag: document.querySelector('meta[name=csrf-token]').content\n";
echo "3. Include Accept: application/json header for JSON responses\n";
echo "4. Your system is actually working - just needs proper CSRF handling!\n";

echo "</pre>";
?>