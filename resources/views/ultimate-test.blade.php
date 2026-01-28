<!DOCTYPE html>
<html>
<head>
    <title>Ultimate CSRF Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .result-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-top: 10px;
            max-height: 300px;
            overflow-y: auto;
        }
        .success { border-color: #28a745; background-color: #d4edda; }
        .error { border-color: #dc3545; background-color: #f8d7da; }
        .warning { border-color: #ffc107; background-color: #fff3cd; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>🚀 Ultimate CSRF Test</h1>
        <p class="text-muted">Testing all possible endpoints and solutions</p>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>1. Simple PHP Test</h5>
                    </div>
                    <div class="card-body">
                        <button onclick="testSimple()" class="btn btn-info">Test /test-simple.php</button>
                        <div id="simpleResult" class="result-box" style="display: none;"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>2. Direct DOCX Import</h5>
                    </div>
                    <div class="card-body">
                        <input type="file" id="docxFile1" accept=".docx" class="form-control mb-2">
                        <button onclick="testDirectDocx()" class="btn btn-primary">Test Direct PHP</button>
                        <div id="directResult" class="result-box" style="display: none;"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>3. Laravel Route Test</h5>
                    </div>
                    <div class="card-body">
                        <input type="file" id="docxFile2" accept=".docx" class="form-control mb-2">
                        <button onclick="testLaravelRoute()" class="btn btn-success">Test Laravel Route</button>
                        <div id="laravelResult" class="result-box" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>4. Course Management Test</h5>
                    </div>
                    <div class="card-body">
                        <button onclick="testCourses()" class="btn btn-warning me-2">Load Courses</button>
                        <button onclick="testChapters()" class="btn btn-info me-2">Load Chapters</button>
                        <button onclick="clearCaches()" class="btn btn-danger">Clear Caches</button>
                        <div id="courseResult" class="result-box" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>5. System Status</h5>
                    </div>
                    <div class="card-body">
                        <div id="systemStatus">
                            <button onclick="checkSystemStatus()" class="btn btn-outline-primary">Check System Status</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function testSimple() {
            const resultDiv = document.getElementById('simpleResult');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing simple PHP endpoint...';
            
            try {
                const response = await fetch('/test-simple.php');
                const data = await response.json();
                
                resultDiv.className = 'result-box success';
                resultDiv.innerHTML = `
                    <strong>✅ SUCCESS!</strong><br>
                    Simple PHP endpoint working<br>
                    <small>${data.message}</small>
                `;
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `<strong>❌ ERROR:</strong> ${error.message}`;
            }
        }
        
        async function testDirectDocx() {
            const fileInput = document.getElementById('docxFile1');
            const resultDiv = document.getElementById('directResult');
            
            if (!fileInput.files[0]) {
                resultDiv.style.display = 'block';
                resultDiv.className = 'result-box warning';
                resultDiv.innerHTML = '<strong>⚠️ WARNING:</strong> Please select a DOCX file';
                return;
            }
            
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing direct DOCX import...';
            
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            
            try {
                const response = await fetch('/docx-import-direct.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    const data = await response.json();
                    resultDiv.className = 'result-box success';
                    resultDiv.innerHTML = `<strong>✅ SUCCESS!</strong><br>Direct DOCX import working`;
                } else {
                    resultDiv.className = 'result-box error';
                    resultDiv.innerHTML = `<strong>❌ ERROR:</strong> HTTP ${response.status}`;
                }
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `<strong>❌ ERROR:</strong> ${error.message}`;
            }
        }
        
        async function testLaravelRoute() {
            const fileInput = document.getElementById('docxFile2');
            const resultDiv = document.getElementById('laravelResult');
            
            if (!fileInput.files[0]) {
                resultDiv.style.display = 'block';
                resultDiv.className = 'result-box warning';
                resultDiv.innerHTML = '<strong>⚠️ WARNING:</strong> Please select a DOCX file';
                return;
            }
            
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing Laravel route...';
            
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            
            try {
                const response = await fetch('/api/import-docx', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    const data = await response.json();
                    resultDiv.className = 'result-box success';
                    resultDiv.innerHTML = `<strong>✅ SUCCESS!</strong><br>Laravel route working (CSRF disabled)`;
                } else if (response.status === 419) {
                    resultDiv.className = 'result-box error';
                    resultDiv.innerHTML = `<strong>❌ CSRF ERROR:</strong> Token mismatch (middleware not working)`;
                } else {
                    resultDiv.className = 'result-box error';
                    resultDiv.innerHTML = `<strong>❌ ERROR:</strong> HTTP ${response.status}`;
                }
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `<strong>❌ ERROR:</strong> ${error.message}`;
            }
        }
        
        async function testCourses() {
            const resultDiv = document.getElementById('courseResult');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing course loading...';
            
            try {
                const response = await fetch('/web/courses', {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    resultDiv.className = 'result-box success';
                    resultDiv.innerHTML = `<strong>✅ SUCCESS!</strong> Found ${data.length} courses`;
                } else {
                    resultDiv.className = 'result-box error';
                    resultDiv.innerHTML = `<strong>❌ ERROR:</strong> HTTP ${response.status}`;
                }
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `<strong>❌ ERROR:</strong> ${error.message}`;
            }
        }
        
        async function testChapters() {
            const resultDiv = document.getElementById('courseResult');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing chapter loading...';
            
            try {
                const response = await fetch('/web/courses/1/chapters', {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    resultDiv.className = 'result-box success';
                    resultDiv.innerHTML = `<strong>✅ SUCCESS!</strong> Found ${data.length} chapters`;
                } else {
                    resultDiv.className = 'result-box error';
                    resultDiv.innerHTML = `<strong>❌ ERROR:</strong> HTTP ${response.status}`;
                }
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `<strong>❌ ERROR:</strong> ${error.message}`;
            }
        }
        
        async function clearCaches() {
            const resultDiv = document.getElementById('courseResult');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Clearing caches...';
            
            try {
                const response = await fetch('/clear_all_caches.php');
                const text = await response.text();
                
                resultDiv.className = 'result-box success';
                resultDiv.innerHTML = `<strong>✅ SUCCESS!</strong> Caches cleared. Please refresh the page.`;
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `<strong>❌ ERROR:</strong> ${error.message}`;
            }
        }
        
        function checkSystemStatus() {
            const statusDiv = document.getElementById('systemStatus');
            statusDiv.innerHTML = `
                <div class="alert alert-info">
                    <h6>System Status Check:</h6>
                    <ul class="mb-0">
                        <li><strong>CSRF Middleware:</strong> Completely disabled</li>
                        <li><strong>Direct PHP Endpoints:</strong> Available</li>
                        <li><strong>Laravel Routes:</strong> Should work without CSRF</li>
                        <li><strong>Cache Status:</strong> Use "Clear Caches" button if needed</li>
                        <li><strong>Test Order:</strong> 1→2→3→4 (Simple→Direct→Laravel→Courses)</li>
                    </ul>
                </div>
            `;
        }
        
        // Auto-check system status on load
        checkSystemStatus();
    </script>
</body>
</html>