<?php
// Fix DOCX Import Duplicate Routes
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Fix DOCX Import Duplicate Routes</h1>";
echo "<pre>";

try {
    echo "=== FIXING DOCX IMPORT DUPLICATE ROUTES ===\n\n";
    
    // 1. Check for duplicate routes
    echo "1. Checking for duplicate DOCX import routes...\n";
    
    $routes = app('router')->getRoutes();
    $docxRoutes = [];
    
    foreach ($routes as $route) {
        if ($route->uri() === 'api/import-docx' && in_array('POST', $route->methods())) {
            $docxRoutes[] = [
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'action' => $route->getActionName(),
                'middleware' => $route->middleware()
            ];
        }
    }
    
    echo "   Found " . count($docxRoutes) . " routes for POST /api/import-docx:\n";
    foreach ($docxRoutes as $index => $route) {
        echo "   Route " . ($index + 1) . ": " . $route['action'] . "\n";
        echo "     Middleware: " . implode(', ', $route['middleware']) . "\n";
    }
    
    if (count($docxRoutes) > 1) {
        echo "   ❌ FOUND DUPLICATE ROUTES! This can cause routing conflicts.\n";
    } else {
        echo "   ✅ No duplicate routes found\n";
    }
    
    // 2. Test the DOCX import endpoint directly
    echo "\n2. Testing DOCX import endpoint...\n";
    
    try {
        $controller = new \App\Http\Controllers\ChapterController();
        
        // Test with empty request (should return validation error as JSON)
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-CSRF-TOKEN', csrf_token());
        
        $response = $controller->importDocx($request);
        $status = $response->getStatusCode();
        $content = $response->getContent();
        $contentType = $response->headers->get('Content-Type');
        $isJson = json_decode($content) !== null;
        
        echo "   Response Status: {$status}\n";
        echo "   Content-Type: {$contentType}\n";
        echo "   Valid JSON: " . ($isJson ? 'Yes' : 'No') . "\n";
        
        if ($isJson) {
            $data = json_decode($content, true);
            echo "   Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "   ❌ NOT JSON - Content preview:\n";
            echo "   " . substr($content, 0, 300) . "...\n";
        }
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        echo "   ✅ Validation exception caught (this is expected)\n";
        echo "   Errors: " . json_encode($e->errors()) . "\n";
    } catch (Exception $e) {
        echo "   ❌ Unexpected error: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    // 3. Check middleware that might interfere
    echo "\n3. Checking middleware configuration...\n";
    
    if (!empty($docxRoutes)) {
        $middleware = $docxRoutes[0]['middleware'];
        echo "   DOCX route middleware: " . implode(', ', $middleware) . "\n";
        
        // Check for problematic middleware
        $problematicMiddleware = ['guest', 'throttle', 'verified'];
        foreach ($problematicMiddleware as $mw) {
            if (in_array($mw, $middleware)) {
                echo "   ⚠️ Found potentially problematic middleware: {$mw}\n";
            }
        }
        
        // Check for required middleware
        $requiredMiddleware = ['auth'];
        foreach ($requiredMiddleware as $mw) {
            if (in_array($mw, $middleware)) {
                echo "   ✅ Required middleware present: {$mw}\n";
            } else {
                echo "   ⚠️ Required middleware missing: {$mw}\n";
            }
        }
    }
    
    // 4. Create a simple test for DOCX upload
    echo "\n4. Creating DOCX upload test...\n";
    
    $testHtml = '<!DOCTYPE html>
<html>
<head>
    <title>DOCX Upload Test</title>
    <meta name="csrf-token" content="' . csrf_token() . '">
</head>
<body>
    <h1>DOCX Upload Test</h1>
    <form id="docxForm" enctype="multipart/form-data">
        <input type="file" id="docxFile" accept=".docx" required>
        <button type="submit">Upload DOCX</button>
    </form>
    
    <div id="result"></div>
    
    <script>
    document.getElementById("docxForm").addEventListener("submit", async function(e) {
        e.preventDefault();
        
        const fileInput = document.getElementById("docxFile");
        const resultDiv = document.getElementById("result");
        
        if (!fileInput.files[0]) {
            resultDiv.innerHTML = "<p style=\"color: red;\">Please select a DOCX file</p>";
            return;
        }
        
        const formData = new FormData();
        formData.append("file", fileInput.files[0]);
        
        try {
            resultDiv.innerHTML = "<p>Uploading...</p>";
            
            const response = await fetch("/api/import-docx", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content,
                    "Accept": "application/json"
                },
                body: formData
            });
            
            const responseText = await response.text();
            
            resultDiv.innerHTML = `
                <h3>Response:</h3>
                <p><strong>Status:</strong> ${response.status}</p>
                <p><strong>Content-Type:</strong> ${response.headers.get("content-type")}</p>
                <p><strong>Response:</strong></p>
                <pre>${responseText}</pre>
            `;
            
            // Try to parse as JSON
            try {
                const jsonData = JSON.parse(responseText);
                resultDiv.innerHTML += "<p style=\"color: green;\">✅ Valid JSON response</p>";
            } catch (e) {
                resultDiv.innerHTML += "<p style=\"color: red;\">❌ NOT JSON - This is the problem!</p>";
            }
            
        } catch (error) {
            resultDiv.innerHTML = `<p style="color: red;">Error: ${error.message}</p>`;
        }
    });
    </script>
</body>
</html>';
    
    file_put_contents(public_path('test-docx-import.html'), $testHtml);
    echo "   ✅ Created DOCX upload test page\n";
    echo "   Visit: http://nelly-elearning.test/test-docx-import.html\n";
    
    echo "\n🎯 DOCX IMPORT JSON ERROR DIAGNOSIS:\n\n";
    echo "LIKELY CAUSES:\n";
    echo "1. Duplicate routes causing conflicts\n";
    echo "2. Validation errors returning HTML instead of JSON\n";
    echo "3. File upload limits exceeded → PHP error page\n";
    echo "4. PHPWord exceptions not caught → HTML error page\n";
    echo "5. CSRF token issues → Laravel error page\n";
    
    echo "\n💡 IMMEDIATE FIXES:\n\n";
    echo "1. Remove duplicate routes from routes/web.php\n";
    echo "2. Ensure all exceptions return JSON responses\n";
    echo "3. Test with small DOCX files first\n";
    echo "4. Check browser Network tab for exact error\n";
    
    echo "\n📝 TESTING STEPS:\n";
    echo "1. Visit: http://nelly-elearning.test/test-docx-import.html\n";
    echo "2. Upload a small DOCX file (< 1MB)\n";
    echo "3. Check if response is JSON or HTML\n";
    echo "4. Report back what you see in the response\n";
    
} catch (Exception $e) {
    echo "❌ FIX ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
?>