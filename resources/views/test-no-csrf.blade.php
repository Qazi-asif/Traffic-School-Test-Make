<!DOCTYPE html>
<html>
<head>
    <title>Course Management Test (No CSRF)</title>
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
        <h1>🚀 Course Management Test (No CSRF)</h1>
        <p class="text-muted">Testing course management functionality without CSRF tokens</p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Test Chapter Loading</h3>
                    </div>
                    <div class="card-body">
                        <button onclick="testChapters()" class="btn btn-primary">Load Chapters for Course 1</button>
                        <div id="result" class="result-box"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Test DOCX Import</h3>
                    </div>
                    <div class="card-body">
                        <input type="file" id="docxFile" accept=".docx" class="form-control mb-2">
                        <button onclick="testDocxImport()" class="btn btn-success">Import DOCX</button>
                        <div id="docxResult" class="result-box"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Test All Courses</h3>
                    </div>
                    <div class="card-body">
                        <button onclick="testCourses()" class="btn btn-info">Load All Courses</button>
                        <div id="coursesResult" class="result-box"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function testChapters() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="spinner-border" role="status"></div> Loading...';
            
            try {
                const response = await fetch('/web/courses/1/chapters', {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    resultDiv.className = 'result-box success';
                    resultDiv.innerHTML = `
                        <strong>✅ SUCCESS!</strong><br>
                        Found ${Array.isArray(data) ? data.length : 'unknown'} chapters<br>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                } else {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `<strong>❌ ERROR:</strong> ${error.message}`;
            }
        }
        
        async function testDocxImport() {
            const fileInput = document.getElementById('docxFile');
            const resultDiv = document.getElementById('docxResult');
            
            if (!fileInput.files[0]) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = '<strong>❌ ERROR:</strong> Please select a DOCX file';
                return;
            }
            
            resultDiv.innerHTML = '<div class="spinner-border" role="status"></div> Importing...';
            
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
                        DOCX imported successfully<br>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                } else {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `<strong>❌ ERROR:</strong> ${error.message}`;
            }
        }
        
        async function testCourses() {
            const resultDiv = document.getElementById('coursesResult');
            resultDiv.innerHTML = '<div class="spinner-border" role="status"></div> Loading...';
            
            try {
                const response = await fetch('/web/courses', {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    resultDiv.className = 'result-box success';
                    resultDiv.innerHTML = `
                        <strong>✅ SUCCESS!</strong><br>
                        Found ${Array.isArray(data) ? data.length : 'unknown'} courses<br>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                } else {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `<strong>❌ ERROR:</strong> ${error.message}`;
            }
        }
    </script>
</body>
</html>