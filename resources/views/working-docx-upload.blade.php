<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Working DOCX Upload</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .upload-area {
            border: 3px dashed #ddd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
            transition: all 0.3s;
            cursor: pointer;
        }
        .upload-area:hover {
            border-color: #007cba;
            background-color: #f8f9fa;
        }
        .upload-area.dragover {
            border-color: #007cba;
            background-color: #e3f2fd;
            transform: scale(1.02);
        }
        .upload-area.has-file {
            border-color: #28a745;
            background-color: #d4edda;
        }
        .upload-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .file-input {
            display: none;
        }
        button {
            background: linear-gradient(135deg, #007cba, #005a87);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin: 10px;
        }
        button:hover:not(:disabled) {
            background: linear-gradient(135deg, #005a87, #004066);
            transform: translateY(-2px);
        }
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        .file-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            display: none;
        }
        .file-info.show {
            display: block;
        }
        .result {
            margin-top: 30px;
            padding: 20px;
            border-radius: 6px;
            display: none;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .loading {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #007cba, #28a745);
            width: 0%;
            transition: width 0.3s;
        }
        .content-preview {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 6px;
            background: white;
            font-size: 14px;
            line-height: 1.5;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #007cba;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📄 Working DOCX Upload & Import</h1>
        
        <div class="upload-area" id="uploadArea">
            <div class="upload-icon">📁</div>
            <h3>Drop your DOCX file here</h3>
            <p>or click to browse and select a file</p>
            <button type="button" onclick="document.getElementById('docxFile').click()">
                Choose DOCX File
            </button>
            <input type="file" id="docxFile" class="file-input" accept=".docx">
        </div>
        
        <div id="fileInfo" class="file-info">
            <h4>📋 Selected File Information</h4>
            <div id="fileDetails"></div>
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-value" id="fileSize">0 MB</div>
                    <div class="stat-label">File Size</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="fileType">DOCX</div>
                    <div class="stat-label">File Type</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="uploadStatus">Ready</div>
                    <div class="stat-label">Status</div>
                </div>
            </div>
            <button id="uploadBtn" disabled>
                🚀 Upload & Import DOCX
            </button>
            <button onclick="clearFile()">
                🗑️ Clear Selection
            </button>
        </div>
        
        <div id="result" class="result"></div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('docxFile');
        const fileInfo = document.getElementById('fileInfo');
        const uploadBtn = document.getElementById('uploadBtn');
        const resultDiv = document.getElementById('result');
        
        let selectedFile = null;
        
        // Drag and drop functionality
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelection(files[0]);
            }
        });
        
        // Click to upload
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });
        
        // File input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files[0]) {
                handleFileSelection(e.target.files[0]);
            }
        });
        
        function handleFileSelection(file) {
            if (!file.name.toLowerCase().endsWith('.docx')) {
                alert('Please select a DOCX file only.');
                return;
            }
            
            selectedFile = file;
            updateUI();
        }
        
        function updateUI() {
            if (selectedFile) {
                uploadArea.classList.add('has-file');
                uploadArea.innerHTML = `
                    <div class="upload-icon">✅</div>
                    <h3>File Selected Successfully!</h3>
                    <p><strong>${selectedFile.name}</strong></p>
                    <p>Click here to select a different file</p>
                `;
                
                // Show file info
                fileInfo.classList.add('show');
                document.getElementById('fileDetails').innerHTML = `
                    <p><strong>Filename:</strong> ${selectedFile.name}</p>
                    <p><strong>Last Modified:</strong> ${new Date(selectedFile.lastModified).toLocaleString()}</p>
                `;
                
                const sizeInMB = (selectedFile.size / 1024 / 1024).toFixed(2);
                document.getElementById('fileSize').textContent = sizeInMB + ' MB';
                document.getElementById('uploadStatus').textContent = 'Ready';
                
                uploadBtn.disabled = false;
            }
        }
        
        function clearFile() {
            selectedFile = null;
            fileInput.value = '';
            uploadArea.classList.remove('has-file');
            uploadArea.innerHTML = `
                <div class="upload-icon">📁</div>
                <h3>Drop your DOCX file here</h3>
                <p>or click to browse and select a file</p>
                <button type="button" onclick="document.getElementById('docxFile').click()">
                    Choose DOCX File
                </button>
            `;
            fileInfo.classList.remove('show');
            uploadBtn.disabled = true;
            resultDiv.style.display = 'none';
        }
        
        // Upload functionality
        uploadBtn.addEventListener('click', async function() {
            if (!selectedFile) {
                alert('Please select a DOCX file first.');
                return;
            }
            
            const formData = new FormData();
            formData.append('file', selectedFile);
            
            try {
                // Show loading state
                resultDiv.className = 'result loading';
                resultDiv.style.display = 'block';
                resultDiv.innerHTML = `
                    <h3>🔄 Processing DOCX File...</h3>
                    <p>Uploading and importing your document. This may take a moment.</p>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <p id="progressText">Initializing...</p>
                `;
                
                uploadBtn.disabled = true;
                document.getElementById('uploadStatus').textContent = 'Uploading';
                
                // Simulate progress
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 90) progress = 90;
                    document.getElementById('progressFill').style.width = progress + '%';
                    
                    if (progress < 30) {
                        document.getElementById('progressText').textContent = 'Uploading file...';
                    } else if (progress < 60) {
                        document.getElementById('progressText').textContent = 'Processing DOCX content...';
                    } else if (progress < 90) {
                        document.getElementById('progressText').textContent = 'Extracting images and formatting...';
                    }
                }, 200);
                
                const response = await fetch('/api/import-docx', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                clearInterval(progressInterval);
                document.getElementById('progressFill').style.width = '100%';
                document.getElementById('progressText').textContent = 'Processing complete!';
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Success
                    resultDiv.className = 'result success';
                    resultDiv.innerHTML = `
                        <h3>✅ DOCX Import Successful!</h3>
                        <div class="stats">
                            <div class="stat-item">
                                <div class="stat-value">${data.images_imported || 0}</div>
                                <div class="stat-label">Images Imported</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">${data.html ? (data.html.length / 1000).toFixed(1) + 'K' : '0'}</div>
                                <div class="stat-label">Characters</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">${data.has_unsupported_images ? 'Yes' : 'No'}</div>
                                <div class="stat-label">Unsupported Images</div>
                            </div>
                        </div>
                        ${data.has_unsupported_images ? `
                            <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 6px; margin: 15px 0;">
                                <h4>⚠️ Some Images Were Skipped</h4>
                                <p>Some images in your document were in unsupported formats (WMF, EMF, etc.) and were skipped during import.</p>
                            </div>
                        ` : ''}
                        ${data.html ? `
                            <details>
                                <summary><strong>📄 Preview Imported Content</strong></summary>
                                <div class="content-preview">
                                    ${data.html.substring(0, 2000)}${data.html.length > 2000 ? '...' : ''}
                                </div>
                            </details>
                        ` : ''}
                        <details>
                            <summary>View Full Response Data</summary>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </details>
                    `;
                    
                    document.getElementById('uploadStatus').textContent = 'Success';
                    
                } else {
                    // Error
                    resultDiv.className = 'result error';
                    resultDiv.innerHTML = `
                        <h3>❌ DOCX Import Failed</h3>
                        <p><strong>Status:</strong> ${response.status}</p>
                        <p><strong>Error:</strong> ${data.error || data.message || 'Unknown error occurred'}</p>
                        ${data.validation_errors ? `
                            <h4>Validation Errors:</h4>
                            <ul>
                                ${Object.entries(data.validation_errors).map(([field, errors]) => 
                                    `<li><strong>${field}:</strong> ${Array.isArray(errors) ? errors.join(', ') : errors}</li>`
                                ).join('')}
                            </ul>
                        ` : ''}
                        <details>
                            <summary>View Full Response</summary>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </details>
                        <h4>💡 Common Solutions:</h4>
                        <ul>
                            <li>Make sure the file is a valid DOCX document</li>
                            <li>Try with a smaller file (under 10MB)</li>
                            <li>Ensure the document doesn't contain corrupted images</li>
                            <li>Check that the file isn't password protected</li>
                        </ul>
                    `;
                    
                    document.getElementById('uploadStatus').textContent = 'Failed';
                }
                
            } catch (error) {
                // Network error
                resultDiv.className = 'result error';
                resultDiv.innerHTML = `
                    <h3>❌ Upload Failed</h3>
                    <p><strong>Network Error:</strong> ${error.message}</p>
                    <p>Please check your internet connection and try again.</p>
                    <h4>💡 Troubleshooting:</h4>
                    <ul>
                        <li>Check your internet connection</li>
                        <li>Try refreshing the page</li>
                        <li>Make sure the server is running</li>
                        <li>Try with a smaller file</li>
                    </ul>
                `;
                
                document.getElementById('uploadStatus').textContent = 'Error';
            } finally {
                uploadBtn.disabled = false;
            }
        });
    </script>
</body>
</html>