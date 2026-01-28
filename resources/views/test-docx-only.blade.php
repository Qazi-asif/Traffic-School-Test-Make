<!DOCTYPE html>
<html>
<head>
    <title>DOCX Import Test (No CSRF)</title>
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
        <h1>🚀 DOCX Import Test (No CSRF)</h1>
        <p class="text-muted">Testing DOCX import without CSRF tokens</p>
        
        <div class="card">
            <div class="card-header">
                <h3>DOCX Import Test</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Select DOCX File:</label>
                    <input type="file" id="docxFile" accept=".docx" class="form-control">
                </div>
                <button onclick="testDocxImport()" class="btn btn-success">Import DOCX (No CSRF)</button>
                <div id="docxResult" class="result-box" style="display: none;"></div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h3>Alternative Test Routes</h3>
            </div>
            <div class="card-body">
                <button onclick="testOriginalRoute()" class="btn btn-warning">Test Original Route (/api/import-docx)</button>
                <button onclick="testNewRoute()" class="btn btn-primary">Test Direct PHP (/docx-import-direct.php)</button>
                <div id="routeResult" class="result-box" style="display: none;"></div>
            </div>
        </div>
    </div>

    <script>
        async function testDocxImport() {
            const fileInput = document.getElementById('docxFile');
            const resultDiv = document.getElementById('docxResult');
            
            if (!fileInput.files[0]) {
                resultDiv.style.display = 'block';
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = '<strong>❌ ERROR:</strong> Please select a DOCX file';
                return;
            }
            
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm"></div> Importing DOCX...';
            
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            
            try {
                const response = await fetch('/docx-import-direct.php', {
                    method: 'POST',
                    body: formData
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (response.ok) {
                    const data = await response.json();
                    resultDiv.className = 'result-box success';
                    resultDiv.innerHTML = `
                        <strong>✅ SUCCESS!</strong><br>
                        DOCX imported successfully via direct PHP endpoint (no Laravel middleware)<br>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                } else {
                    const errorText = await response.text();
                    throw new Error(`HTTP ${response.status}: ${errorText}`);
                }
            } catch (error) {
                console.error('Import error:', error);
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `<strong>❌ ERROR:</strong> ${error.message}`;
            }
        }
        
        async function testOriginalRoute() {
            const resultDiv = document.getElementById('routeResult');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing original route...';
            
            try {
                const response = await fetch('/api/import-docx', {
                    method: 'POST',
                    body: new FormData() // Empty form data just to test route
                });
                
                resultDiv.className = 'result-box ' + (response.status === 419 ? 'error' : 'success');
                resultDiv.innerHTML = `Original route: HTTP ${response.status} - ${response.status === 419 ? 'CSRF Error (Expected)' : 'Working'}`;
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `Original route error: ${error.message}`;
            }
        }
        
        async function testNewRoute() {
            const resultDiv = document.getElementById('routeResult');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing direct PHP endpoint...';
            
            try {
                const response = await fetch('/docx-import-direct.php', {
                    method: 'POST',
                    body: new FormData() // Empty form data just to test route
                });
                
                resultDiv.className = 'result-box ' + (response.ok ? 'success' : 'error');
                resultDiv.innerHTML = `Direct PHP endpoint: HTTP ${response.status} - ${response.ok ? 'Working (No Laravel Middleware)' : 'Error'}`;
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = `Direct endpoint error: ${error.message}`;
            }
        }
    </script>
</body>
</html>