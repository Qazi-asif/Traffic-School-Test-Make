<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Working Course Creation</title>
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #007cba;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
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
            width: 100%;
        }
        button:hover {
            background: linear-gradient(135deg, #005a87, #004066);
            transform: translateY(-2px);
        }
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
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
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 14px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 Working Course Creation</h1>
        
        <form id="courseForm">
            <div class="form-group">
                <label for="title">Course Title *</label>
                <input type="text" id="title" name="title" required 
                       placeholder="Enter course title (e.g., Florida Traffic School)">
            </div>
            
            <div class="form-group">
                <label for="description">Course Description *</label>
                <textarea id="description" name="description" required 
                          placeholder="Enter detailed course description..."></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="state_code">State *</label>
                    <select id="state_code" name="state_code" required>
                        <option value="">Select State</option>
                        <option value="FL">Florida</option>
                        <option value="MO">Missouri</option>
                        <option value="TX">Texas</option>
                        <option value="DE">Delaware</option>
                        <option value="NV">Nevada</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="course_type">Course Type</label>
                    <select id="course_type" name="course_type">
                        <option value="BDI">Basic Driver Improvement (BDI)</option>
                        <option value="ADI">Advanced Driver Improvement (ADI)</option>
                        <option value="Defensive">Defensive Driving</option>
                        <option value="Traffic">Traffic School</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="total_duration">Duration (minutes) *</label>
                    <input type="number" id="total_duration" name="total_duration" 
                           value="240" min="1" max="1440" required>
                </div>
                
                <div class="form-group">
                    <label for="min_pass_score">Minimum Pass Score (%) *</label>
                    <input type="number" id="min_pass_score" name="min_pass_score" 
                           value="80" min="0" max="100" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Course Price ($) *</label>
                    <input type="number" id="price" name="price" 
                           value="29.99" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="certificate_template">Certificate Template</label>
                    <select id="certificate_template" name="certificate_template">
                        <option value="">Default Template</option>
                        <option value="florida">Florida Template</option>
                        <option value="missouri">Missouri Template</option>
                        <option value="texas">Texas Template</option>
                        <option value="delaware">Delaware Template</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="is_active" name="is_active" checked>
                    <label for="is_active">Course is Active and Available for Enrollment</label>
                </div>
            </div>
            
            <button type="submit" id="submitBtn">
                🚀 Create Course
            </button>
        </form>
        
        <div id="result" class="result"></div>
    </div>

    <script>
        document.getElementById('courseForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            // Handle checkbox
            data.is_active = document.getElementById('is_active').checked;
            
            const resultDiv = document.getElementById('result');
            const submitBtn = document.getElementById('submitBtn');
            
            try {
                // Show loading state
                resultDiv.className = 'result loading';
                resultDiv.style.display = 'block';
                resultDiv.innerHTML = '<h3>🔄 Creating Course...</h3><p>Please wait while we create your course.</p>';
                submitBtn.disabled = true;
                submitBtn.textContent = 'Creating...';
                
                const response = await fetch('/api/florida-courses', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });
                
                const responseData = await response.json();
                
                if (response.ok && responseData.id) {
                    // Success
                    resultDiv.className = 'result success';
                    resultDiv.innerHTML = `
                        <h3>✅ Course Created Successfully!</h3>
                        <p><strong>Course ID:</strong> ${responseData.id}</p>
                        <p><strong>Title:</strong> ${responseData.title}</p>
                        <p><strong>State:</strong> ${responseData.state || responseData.state_code}</p>
                        <p><strong>Duration:</strong> ${responseData.duration || responseData.total_duration} minutes</p>
                        <p><strong>Price:</strong> $${responseData.price}</p>
                        <details>
                            <summary>View Full Response</summary>
                            <pre>${JSON.stringify(responseData, null, 2)}</pre>
                        </details>
                    `;
                    
                    // Reset form
                    e.target.reset();
                    document.getElementById('is_active').checked = true;
                    
                } else {
                    // Error
                    resultDiv.className = 'result error';
                    resultDiv.innerHTML = `
                        <h3>❌ Course Creation Failed</h3>
                        <p><strong>Status:</strong> ${response.status}</p>
                        <p><strong>Error:</strong> ${responseData.error || responseData.message || 'Unknown error occurred'}</p>
                        ${responseData.validation_errors ? `
                            <h4>Validation Errors:</h4>
                            <ul>
                                ${Object.entries(responseData.validation_errors).map(([field, errors]) => 
                                    `<li><strong>${field}:</strong> ${Array.isArray(errors) ? errors.join(', ') : errors}</li>`
                                ).join('')}
                            </ul>
                        ` : ''}
                        <details>
                            <summary>View Full Response</summary>
                            <pre>${JSON.stringify(responseData, null, 2)}</pre>
                        </details>
                    `;
                }
                
            } catch (error) {
                // Network error
                resultDiv.className = 'result error';
                resultDiv.innerHTML = `
                    <h3>❌ Network Error</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <p>Please check your internet connection and try again.</p>
                `;
            } finally {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.textContent = '🚀 Create Course';
            }
        });
        
        // Auto-fill form with sample data
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const timestamp = now.toISOString().slice(0, 16).replace('T', ' ');
            
            document.getElementById('title').value = `Sample Course ${timestamp}`;
            document.getElementById('description').value = `This is a sample course created on ${timestamp} to test the course creation functionality.`;
            document.getElementById('state_code').value = 'FL';
        });
    </script>
</body>
</html>