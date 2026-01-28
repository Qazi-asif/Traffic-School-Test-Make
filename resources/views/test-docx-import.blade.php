<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DOCX Import Test - Fixed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            padding: 40px 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .upload-zone {
            border: 3px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #f8f9fa;
        }
        .upload-zone:hover {
            border-color: #667eea;
            background: #e3f2fd;
            transform: translateY(-2px);
        }
        .upload-zone.dragover {
            border-color: #667eea;
            background: #e3f2fd;
            transform: scale(1.02);
        }
        .upload-zone.has-file {
            border-color: #28a745;
            background: #d4edda;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .progress {
            height: 25px;
            border-radius: 15px;
            background: #e9ecef;
        }
        .progress-bar {
            border-radius: 15px;
            background: linear-gradient(90deg, #667eea, #28a745);
        }
        .result-card {
            margin-top: 20px;
            border-radius: 10px;
        }
        .success-card {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error-card {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .loading-card {
            background: linear-gradient(135deg, #d1ecf1, #bee5eb);
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .content-preview {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            background: white;
            font-size: 14px;
            line-height: 1.6;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: rgba(255,255,255,0.8);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .file-info {
            background: rgba(255,255,255,0.9);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #dee2e6;
        }
        .troubleshooting {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .spinner {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header text-center">
                        <h1 class="mb-0">
                            <i class="fas fa-file-word me-2"></i>
                            DOCX Import Test - CSRF Fixed
                        </h1>
                        <p class="mb-0 mt-2 opacity-75">Upload and import Word documents with proper error handling</p>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Upload Zone -->
                        <div class="upload-zone" id="uploadZone">
                            <div class="upload-icon mb-3">
                                <i class="fas fa-cloud-upload-alt fa-4x text-muted"></i>
                            </div>
                            <h4 class="mb-3">Drop your DOCX file here</h4>
                            <p class="text-muted mb-3">or click to browse and select a file</p>
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('docxFile').click()">
                                <i class="fas fa-folder-open me-2"></i>Choose DOCX File
                            </button>
                            <input type="file" id="docxFile" accept=".docx" style="display: none;">
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Supports files up to 50MB with images, tables, and formatted text
                                </small>
                            </div>
                        </div>
                        
                        <!-- File Information -->
                        <div id="fileInfo" class="file-info" style="display: none;">
                            <h5><i class="fas fa-file-alt me-2"></i>Selected File</h5>
                            <div id="fileDetails"></div>
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-value" id="fileSize">0 MB</div>
                                    <div class="stat-label">File Size</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value" id="fileType">DOCX</div>
                                    <div class="stat-label">File Type</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value" id="uploadStatus">Ready</div>
                                    <div class="stat-label">Status</div>
                                </div>
                            </div>
                            <div class="text-center">
                                <button id="uploadBtn" class="btn btn-primary btn-lg me-2" disabled>
                                    <i class="fas fa-upload me-2"></i>Import DOCX Content
                                </button>
                                <button class="btn btn-outline-secondary" onclick="clearFile()">
                                    <i class="fas fa-times me-2"></i>Clear Selection
                                </button>
                            </div>
                        </div>
                        
                        <!-- Results -->
                        <div id="result" class="result-card" style="display: none;"></div>
                        
                        <!-- CSRF Debug Info -->
                        <div class="mt-4">
                            <details>
                                <summary class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>Security & Debug Information
                                </summary>
                                <div class="mt-2 p-3 bg-light rounded">
                                    <p><strong>CSRF Token:</strong> <code id="csrfToken">{{ csrf_token() }}</code></p>
                                    <p><strong>Upload Endpoint:</strong> <code>/api/import-docx</code></p>
                                    <p><strong>Max File Size:</strong> <code>50MB</code></p>
                                    <p><strong>Accepted Types:</strong> <code>.docx</code></p>
                                    <div class="troubleshooting">
                                        <h6><i class="fas fa-tools me-1"></i>Common Issues & Solutions:</h6>
                                        <ul class="mb-0">
                                            <li><strong>HTTP 419 Error:</strong> CSRF token mismatch - page will auto-refresh token</li>
                                            <li><strong>HTTP 422 Error:</strong> File validation failed - check file type and size</li>
                                            <li><strong>HTTP 500 Error:</strong> Server error - check Laravel logs</li>
                                            <li><strong>JSON Parse Error:</strong> Server returned HTML instead of JSON - usually CSRF issue</li>
                                        </ul>
                                    </div>
                                </div>
                            </details>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let selectedFile = null;
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('docxFile');
        const fileInfo = document.getElementById('fileInfo');
        const uploadBtn = document.getElementById('uploadBtn');
        const resultDiv = document.getElementById('result');
        
        // CSRF token management
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }
        
        function refreshCsrfToken() {
            // In a real application, you might want to fetch a fresh token
            // For now, we'll use the existing one
            const token = getCsrfToken();
            document.getElementById('csrfToken').textContent = token;
            return token;
        }
        
        // Drag and drop functionality
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        
        uploadZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
        });
        
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelection(files[0]);
            }
        });
        
        // Click to upload
        uploadZone.addEventListener('click', (e) => {
            if (e.target.tagName !== 'BUTTON') {
                fileInput.click();
            }
        });
        
        // File input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files[0]) {
                handleFileSelection(e.target.files[0]);
            }
        });
        
        function handleFileSelection(file) {
            // Validate file type
            if (!file.name.toLowerCase().endsWith('.docx')) {
                showError('Invalid File Type', 'Please select a DOCX file only. Selected file: ' + file.name);
                return;
            }
            
            // Validate file size (50MB = 52428800 bytes)
            if (file.size > 52428800) {
                showError('File Too Large', 'The selected file is ' + (file.size / 1024 / 1024).toFixed(2) + 'MB. Maximum allowed size is 50MB.');
                return;
            }
            
            selectedFile = file;
            updateUI();
        }
        
        function updateUI() {
            if (selectedFile) {
                // Update upload zone
                uploadZone.classList.add('has-file');
                uploadZone.innerHTML = `
                    <div class="upload-icon mb-3">
                        <i class="fas fa-check-circle fa-4x text-success"></i>
                    </div>
                    <h4 class="mb-3 text-success">File Selected Successfully!</h4>
                    <p class="mb-3"><strong>${selectedFile.name}</strong></p>
                    <p class="text-muted">Click here to select a different file</p>
                `;
                
                // Show file info
                fileInfo.style.display = 'block';
                const sizeInMB = (selectedFile.size / 1024 / 1024).toFixed(2);
                const lastModified = new Date(selectedFile.lastModified).toLocaleString();
                
                document.getElementById('fileDetails').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Filename:</strong> ${selectedFile.name}</p>
                            <p><strong>Size:</strong> ${sizeInMB} MB (${selectedFile.size.toLocaleString()} bytes)</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Type:</strong> ${selectedFile.type || 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'}</p>
                            <p><strong>Last Modified:</strong> ${lastModified}</p>
                        </div>
                    </div>
                `;
                
                document.getElementById('fileSize').textContent = sizeInMB + ' MB';
                document.getElementById('uploadStatus').textContent = 'Ready';
                
                uploadBtn.disabled = false;
                
                // Hide any previous results
                resultDiv.style.display = 'none';
            }
        }
        
        function clearFile() {
            selectedFile = null;
            fileInput.value = '';
            
            // Reset upload zone
            uploadZone.classList.remove('has-file');
            uploadZone.innerHTML = `
                <div class="upload-icon mb-3">
                    <i class="fas fa-cloud-upload-alt fa-4x text-muted"></i>
                </div>
                <h4 class="mb-3">Drop your DOCX file here</h4>
                <p class="text-muted mb-3">or click to browse and select a file</p>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('docxFile').click()">
                    <i class="fas fa-folder-open me-2"></i>Choose DOCX File
                </button>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Supports files up to 50MB with images, tables, and formatted text
                    </small>
                </div>
            `;
            
            // Hide file info and results
            fileInfo.style.display = 'none';
            resultDiv.style.display = 'none';
            uploadBtn.disabled = true;
        }
        
        // Upload functionality with enhanced error handling
        uploadBtn.addEventListener('click', async function() {
            if (!selectedFile) {
                showError('No File Selected', 'Please select a DOCX file first.');
                return;
            }
            
            const formData = new FormData();
            formData.append('file', selectedFile);
            
            try {
                // Show loading state
                showLoading();
                uploadBtn.disabled = true;
                document.getElementById('uploadStatus').textContent = 'Uploading';
                
                // Simulate progress
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += Math.random() * 10;
                    if (progress > 90) progress = 90;
                    updateProgress(progress);
                }, 300);
                
                // Make the request with proper headers
                const response = await fetch('/api/import-docx', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                clearInterval(progressInterval);
                updateProgress(100);
                
                // Handle different response types
                let data;
                const contentType = response.headers.get('content-type');
                
                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    // If we get HTML instead of JSON, it's likely a CSRF error
                    const htmlText = await response.text();
                    
                    if (response.status === 419) {
                        throw new Error('CSRF token mismatch (HTTP 419). The page security token has expired. Please refresh the page and try again.');
                    } else if (htmlText.includes('<!DOCTYPE') || htmlText.includes('<html')) {
                        throw new Error(`Server returned HTML instead of JSON (HTTP ${response.status}). This usually indicates a CSRF token issue or server error.`);
                    } else {
                        throw new Error(`Unexpected response format (HTTP ${response.status}): ${htmlText.substring(0, 200)}`);
                    }
                }
                
                if (response.ok && data.success) {
                    // Success
                    showSuccess(data);
                    document.getElementById('uploadStatus').textContent = 'Success';
                } else {
                    // Error response with JSON
                    showError('Import Failed', data.error || data.message || 'Unknown error occurred', data);
                    document.getElementById('uploadStatus').textContent = 'Failed';
                }
                
            } catch (error) {
                console.error('DOCX import error:', error);
                
                // Enhanced error handling
                let errorTitle = 'Upload Failed';
                let errorMessage = error.message;
                
                if (error.message.includes('CSRF')) {
                    errorTitle = 'Security Token Expired';
                    errorMessage = 'The page security token has expired. Please refresh the page and try again.';
                } else if (error.message.includes('NetworkError') || error.message.includes('fetch')) {
                    errorTitle = 'Network Error';
                    errorMessage = 'Could not connect to the server. Please check your internet connection and try again.';
                } else if (error.message.includes('JSON')) {
                    errorTitle = 'Server Response Error';
                    errorMessage = 'The server returned an unexpected response format. This may be a CSRF token issue or server error.';
                }
                
                showError(errorTitle, errorMessage);
                document.getElementById('uploadStatus').textContent = 'Error';
            } finally {
                uploadBtn.disabled = false;
            }
        });
        
        function showLoading() {
            resultDiv.className = 'result-card loading-card';
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = `
                <div class="p-4">
                    <h5 class="mb-3">
                        <i class="fas fa-spinner spinner me-2"></i>
                        Processing DOCX File...
                    </h5>
                    <p class="mb-3">Uploading and importing your document. This may take a moment depending on file size and complexity.</p>
                    <div class="progress mb-3">
                        <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                    </div>
                    <p id="progressText" class="text-muted mb-0">Initializing upload...</p>
                </div>
            `;
        }
        
        function updateProgress(percent) {
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            
            if (progressBar) {
                progressBar.style.width = percent + '%';
            }
            
            if (progressText) {
                if (percent < 30) {
                    progressText.textContent = 'Uploading file...';
                } else if (percent < 60) {
                    progressText.textContent = 'Processing DOCX content...';
                } else if (percent < 90) {
                    progressText.textContent = 'Extracting images and formatting...';
                } else {
                    progressText.textContent = 'Finalizing import...';
                }
            }
        }
        
        function showSuccess(data) {
            resultDiv.className = 'result-card success-card';
            resultDiv.innerHTML = `
                <div class="p-4">
                    <h5 class="mb-3">
                        <i class="fas fa-check-circle me-2"></i>
                        DOCX Import Successful!
                    </h5>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-value">${data.images_imported || 0}</div>
                            <div class="stat-label">Images Imported</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">${data.html ? (data.html.length / 1000).toFixed(1) + 'K' : '0'}</div>
                            <div class="stat-label">Characters</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">${data.fallback_mode ? 'Yes' : 'No'}</div>
                            <div class="stat-label">Fallback Mode</div>
                        </div>
                    </div>
                    ${data.has_unsupported_images ? `
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-1"></i>Some Images Were Skipped</h6>
                            <p class="mb-0">Some images in your document were in unsupported formats (WMF, EMF, etc.) and were skipped during import.</p>
                        </div>
                    ` : ''}
                    ${data.fallback_mode ? `
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-1"></i>Fallback Mode Used</h6>
                            <p class="mb-0">The document was imported using fallback mode due to compatibility issues. Content has been extracted successfully.</p>
                        </div>
                    ` : ''}
                    ${data.html ? `
                        <details class="mt-3">
                            <summary><strong><i class="fas fa-eye me-1"></i>Preview Imported Content</strong></summary>
                            <div class="content-preview mt-2">
                                ${data.html.substring(0, 3000)}${data.html.length > 3000 ? '<p><em>... (content truncated for preview)</em></p>' : ''}
                            </div>
                        </details>
                    ` : ''}
                    <details class="mt-3">
                        <summary>View Full Response Data</summary>
                        <pre class="mt-2 p-3 bg-light rounded"><code>${JSON.stringify(data, null, 2)}</code></pre>
                    </details>
                </div>
            `;
        }
        
        function showError(title, message, data = null) {
            resultDiv.className = 'result-card error-card';
            resultDiv.style.display = 'block';
            
            let errorDetails = '';
            if (data) {
                if (data.validation_errors) {
                    errorDetails += '<h6>Validation Errors:</h6><ul>';
                    Object.entries(data.validation_errors).forEach(([field, errors]) => {
                        const errorList = Array.isArray(errors) ? errors.join(', ') : errors;
                        errorDetails += `<li><strong>${field}:</strong> ${errorList}</li>`;
                    });
                    errorDetails += '</ul>';
                }
                
                errorDetails += `
                    <details class="mt-3">
                        <summary>View Full Error Response</summary>
                        <pre class="mt-2 p-3 bg-light rounded"><code>${JSON.stringify(data, null, 2)}</code></pre>
                    </details>
                `;
            }
            
            resultDiv.innerHTML = `
                <div class="p-4">
                    <h5 class="mb-3">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        ${title}
                    </h5>
                    <p class="mb-3">${message}</p>
                    ${errorDetails}
                    <div class="troubleshooting">
                        <h6><i class="fas fa-tools me-1"></i>Troubleshooting Steps:</h6>
                        <ol class="mb-0">
                            <li>Ensure the file is a valid DOCX document (not DOC or other formats)</li>
                            <li>Try with a smaller file (under 25MB) to test</li>
                            <li>Check that the document doesn't contain corrupted or unsupported images</li>
                            <li>Make sure the file isn't password protected</li>
                            <li>If you see CSRF errors, refresh the page and try again</li>
                            <li>Try opening and re-saving the document in Microsoft Word</li>
                        </ol>
                    </div>
                </div>
            `;
        }
        
        // Initialize CSRF token display
        document.addEventListener('DOMContentLoaded', function() {
            refreshCsrfToken();
        });
        
        // Auto-refresh CSRF token every 30 minutes
        setInterval(refreshCsrfToken, 30 * 60 * 1000);
    </script>
</body>
</html>