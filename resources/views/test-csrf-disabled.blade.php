<!DOCTYPE html>
<html>
<head>
    <title>CSRF Disabled Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .result-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-top: 10px;
            max-height: 400px;
            overflow-y: auto;
        }
        .success { border-color: #28a745; background-color: #d4edda; }
        .error { border-color: #dc3545; background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>🚀 CSRF Disabled Test</h1>
        <p class="text-muted">Testing with CSRF completely disabled</p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Original Laravel Routes</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select DOCX File:</label>
                            <input type="file" id="docxFile" accept=".docx" class="form-control">
                        </div>
                        <button onclick="testOriginalRoute()" class="btn btn-success">Test /api/import-docx</button>
                        <div id="originalResult" class="result-box" style="display: none;"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Direct PHP Endpoint</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select DOCX File:</label>
                            <input type="file" id="docxFile2" accept=".docx" class="form-control">
                        </div>
                        <button onclick="testDirectRoute()" class="btn btn-primary">Test /docx-import-direct.php</button>
                        <div id="directResult" class="result-box" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Course Management Test</h3>
                    </div>
                    <div class="card-body">
                        <button onclick="testCourses()" class="btn btn-info">Load Courses</button>
                        <button onclick="testChapters()" class="btn btn-warning">Load Chapters (Course 1)</button>
                        <div id="courseResult" class="result-box" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function testOriginalRoute() {
            const fileInput = document.getElementById('docxFile');
            const resultDiv = document.getElementById('originalResult');
            
            if (!fileInput.files[0]) {
                resultDiv.style.display = 'block';
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = '<strong>❌ ERROR:</strong> Please select a DOCX file';
                return;
            }
            
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm"></div> Testing original route...';
            
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
                    resultDiv.innerHTML = `
                        <strong>✅ SUCCESS!</strong><br>
                        Original Laravel route working (CSRF disabled)<br>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                } else {
                    const errorText = await response.text();
                    resultDiv.className = 'result-box error';
                    resultDiv.innerHTML = `<strong>❌ ERROR:</strong> HTTP ${response.status}<br>${errorText}`;
                }
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `<strong>❌ ERROR:</strong> ${error.message}`;
            }
        }
        
        async function testDirectRoute() {
            const fileInput = document.getElementById('docxFile2');
            const resultDiv = document.getElementById('directResult');
            
            if (!fileInput.files[0]) {
                resultDiv.style.display = 'block';
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = '<strong>❌ ERROR:</strong> Please select a DOCX file';
                return;
            }
            
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm"></div> Testing direct PHP...';
            
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
                    resultDiv.innerHTML = `
                        <strong>✅ SUCCESS!</strong><br>
                        Direct PHP endpoint working<br>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                } else {
                    const errorText = await response.text();
                    resultDiv.className = 'result-box error';
                    resultDiv.innerHTML = `<strong>❌ ERROR:</strong> HTTP ${response.status}<br>${errorText}`;
                }
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `<strong>❌ ERROR:</strong> ${error.message}`;
            }
        }
        
        async function testCourses() {
            const resultDiv = document.getElementById('courseResult');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Loading courses...';
            
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
            resultDiv.innerHTML = 'Loading chapters...';
            
            try {
                const response = await fetch('/web/courses/1/chapters', {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    resultDiv.className = 'result-box success';
                    resultDiv.innerHTML = `<strong>✅ SUCCESS!</strong> Found ${data.length} chapters for course 1`;
                } else {
                    resultDiv.className = 'result-box error';
                    resultDiv.innerHTML = `<strong>❌ ERROR:</strong> HTTP ${response.status}`;
                }
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `<strong>❌ ERROR:</strong> ${error.message}`;
            }
        }
    </script>
</body>
</html>