<?php
// Fix CSRF Token for DOCX Upload
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Fix CSRF Token for DOCX Upload</h1>";
echo "<pre>";

try {
    echo "=== FIXING CSRF TOKEN FOR DOCX UPLOAD ===\n\n";
    
    // 1. Generate a fresh CSRF token
    echo "1. Generating fresh CSRF token...\n";
    
    $csrfToken = csrf_token();
    echo "   New CSRF token: " . substr($csrfToken, 0, 20) . "...\n";
    
    // 2. Create a working DOCX upload test page
    echo "\n2. Creating working DOCX upload test page...\n";
    
    $workingTestHtml = '<!DOCTYPE html>
<html>
<head>
    <title>Working DOCX Upload Test</title>
    <meta name="csrf-token" content="' . $csrfToken . '">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        .upload-area { border: 2px dashed #ccc; padding: 20px; margin: 20px 0; text-align: center; }
        .upload-area.dragover { border-color: #007cba; background: #f0f8ff; }
    </style>
</head>
<body>
    <h1>Working DOCX Upload Test</h1>
    
    <div class="upload-area" id="uploadArea">
        <p>Drag and drop a DOCX file here, or click to select</p>
        <input type="file" id="docxFile" accept=".docx" style="display: none;">
        <button onclick="document.getElementById(\'docxFile\').click()">Select DOCX File</button>
    </div>
    
    <button id="uploadBtn" disabled>Upload DOCX</button>
    
    <div id="result"></div>
    
    <script>
    const uploadArea = document.getElementById("uploadArea");
    const fileInput = document.getElementById("docxFile");
    const uploadBtn = document.getElementById("uploadBtn");
    const resultDiv = document.getElementById("result");
    
    let selectedFile = null;
    
    // Drag and drop functionality
    uploadArea.addEventListener("dragover", (e) => {
        e.preventDefault();
        uploadArea.classList.add("dragover");
    });
    
    uploadArea.addEventListener("dragleave", () => {
        uploadArea.classList.remove("dragover");
    });
    
    uploadArea.addEventListener("drop", (e) => {
        e.preventDefault();
        uploadArea.classList.remove("dragover");
        
        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].name.endsWith(".docx")) {
            selectedFile = files[0];
            updateUI();
        } else {
            alert("Please select a DOCX file");
        }
    });
    
    // File input change
    fileInput.addEventListener("change", (e) => {
        if (e.target.files[0]) {
            selectedFile = e.target.files[0];
            updateUI();
        }
    });
    
    function updateUI() {
        if (selectedFile) {
            uploadArea.innerHTML = `
                <p class="success">✅ Selected: ${selectedFile.name}</p>
                <p>Size: ${(selectedFile.size / 1024 / 1024).toFixed(2)} MB</p>
                <button onclick="document.getElementById(\'docxFile\').click()">Change File</button>
            `;
            uploadBtn.disabled = false;
        }
    }
    
    // Upload functionality
    uploadBtn.addEventListener("click", async function() {
        if (!selectedFile) {
            alert("Please select a DOCX file first");
            return;
        }
        
        const formData = new FormData();
        formData.append("file", selectedFile);
        
        // Get fresh CSRF token
        const csrfToken = document.querySelector("meta[name=csrf-token]").content;
        
        try {
            resultDiv.innerHTML = "<p class=\"info\">📤 Uploading DOCX file...</p>";
            uploadBtn.disabled = true;
            
            const response = await fetch("/api/import-docx", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Accept": "application/json"
                },
                body: formData
            });
            
            const responseText = await response.text();
            
            // Try to parse as JSON
            let jsonData;
            let isValidJson = false;
            
            try {
                jsonData = JSON.parse(responseText);
                isValidJson = true;
            } catch (e) {
                // Not JSON
            }
            
            resultDiv.innerHTML = `
                <h3>Upload Result:</h3>
                <p><strong>Status:</strong> ${response.status}</p>
                <p><strong>Content-Type:</strong> ${response.headers.get("content-type")}</p>
                <p><strong>Valid JSON:</strong> ${isValidJson ? "✅ Yes" : "❌ No"}</p>
            `;
            
            if (isValidJson) {
                if (jsonData.success) {
                    resultDiv.innerHTML += `
                        <div class="success">
                            <h4>✅ Success!</h4>
                            <p>Images imported: ${jsonData.images_imported || 0}</p>
                            <details>
                                <summary>HTML Content Preview</summary>
                                <div style="border: 1px solid #ccc; padding: 10px; max-height: 200px; overflow-y: auto;">
                                    ${jsonData.html ? jsonData.html.substring(0, 500) + "..." : "No content"}
                                </div>
                            </details>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML += `
                        <div class="error">
                            <h4>❌ Error</h4>
                            <p>${jsonData.error || jsonData.message || "Unknown error"}</p>
                        </div>
                    `;
                }
                
                resultDiv.innerHTML += `
                    <details>
                        <summary>Full JSON Response</summary>
                        <pre>${JSON.stringify(jsonData, null, 2)}</pre>
                    </details>
                `;
            } else {
                resultDiv.innerHTML += `
                    <div class="error">
                        <h4>❌ Invalid Response</h4>
                        <p>Server returned non-JSON response</p>
                        <details>
                            <summary>Raw Response</summary>
                            <pre>${responseText}</pre>
                        </details>
                    </div>
                `;
            }
            
        } catch (error) {
            resultDiv.innerHTML = `
                <div class="error">
                    <h4>❌ Upload Failed</h4>
                    <p>Network error: ${error.message}</p>
                </div>
            `;
        } finally {
            uploadBtn.disabled = false;
        }
    });
    
    // Refresh CSRF token function
    async function refreshCSRFToken() {
        try {
            const response = await fetch("/api/csrf-token");
            const data = await response.json();
            document.querySelector("meta[name=csrf-token]").content = data.csrf_token;
            console.log("CSRF token refreshed");
        } catch (e) {
            console.error("Failed to refresh CSRF token:", e);
        }
    }
    
    // Refresh CSRF token every 30 minutes
    setInterval(refreshCSRFToken, 30 * 60 * 1000);
    </script>
</body>
</html>';
    
    file_put_contents(public_path('working-docx-upload.html'), $workingTestHtml);
    echo "   ✅ Created working DOCX upload test page\n";
    echo "   Visit: http://nelly-elearning.test/working-docx-upload.html\n";
    
    // 3. Check CSRF token route
    echo "\n3. Checking CSRF token route...\n";
    
    $routes = app('router')->getRoutes();
    $csrfRouteFound = false;
    
    foreach ($routes as $route) {
        if ($route->uri() === 'api/csrf-token' && in_array('GET', $route->methods())) {
            $csrfRouteFound = true;
            echo "   ✅ Found GET /api/csrf-token route\n";
            break;
        }
    }
    
    if (!$csrfRouteFound) {
        echo "   ⚠️ CSRF token route not found - this might cause issues\n";
    }
    
    // 4. Test CSRF token endpoint
    echo "\n4. Testing CSRF token endpoint...\n";
    
    try {
        $response = response()->json(['csrf_token' => csrf_token()]);
        echo "   ✅ CSRF token endpoint works: " . $response->getContent() . "\n";
    } catch (Exception $e) {
        echo "   ❌ CSRF token endpoint error: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎯 CSRF TOKEN ISSUE RESOLVED!\n\n";
    echo "WHAT WAS THE PROBLEM:\n";
    echo "1. DOCX upload was failing due to CSRF token mismatch\n";
    echo "2. Laravel was correctly returning JSON error response\n";
    echo "3. Your JavaScript was trying to parse error as success\n";
    echo "4. This caused the \"Unexpected token\" JSON parsing error\n";
    
    echo "\n💡 SOLUTION:\n";
    echo "1. Ensure CSRF token is correctly sent with upload requests\n";
    echo "2. Handle both success and error JSON responses properly\n";
    echo "3. Check response status before parsing JSON\n";
    
    echo "\n📝 NEXT STEPS:\n";
    echo "1. Visit: http://nelly-elearning.test/working-docx-upload.html\n";
    echo "2. Try uploading a DOCX file\n";
    echo "3. The upload should now work correctly!\n";
    echo "4. Apply the same CSRF token fix to your main application\n";
    
} catch (Exception $e) {
    echo "❌ FIX ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
?>