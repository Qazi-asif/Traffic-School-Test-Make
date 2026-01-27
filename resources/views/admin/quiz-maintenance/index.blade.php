<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quiz Maintenance - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        .card {
            background-color: var(--bg-secondary);
            border-color: var(--border);
        }
        .result-box {
            max-height: 500px;
            overflow-y: auto;
            background-color: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 15px;
        }
        .broken-question {
            background-color: rgba(220, 53, 69, 0.1);
            border-left: 4px solid #dc3545;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .fixed-question {
            background-color: rgba(25, 135, 84, 0.1);
            border-left: 4px solid #198754;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-tools me-2"></i>Quiz Maintenance Tool
                </h2>
                <p class="text-muted">Diagnose and fix quiz grading issues caused by data inconsistencies</p>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Diagnose Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-search me-2"></i>Step 1: Diagnose
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>Scan all quiz questions to identify data inconsistencies that cause grading failures.</p>
                        <button id="diagnoseBtn" class="btn btn-info w-100">
                            <i class="fas fa-search me-2"></i>Run Diagnosis
                        </button>
                        
                        <div id="diagnoseResults" class="mt-3" style="display: none;">
                            <h6>Results:</h6>
                            <div class="result-box" id="diagnoseResultsContent"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fix Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-wrench me-2"></i>Step 2: Fix
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>Automatically fix all identified issues by normalizing question data formats.</p>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="dryRunCheck" checked>
                            <label class="form-check-label" for="dryRunCheck">
                                <strong>Dry Run</strong> (preview changes without applying them)
                            </label>
                        </div>
                        
                        <button id="fixBtn" class="btn btn-success w-100" disabled>
                            <i class="fas fa-wrench me-2"></i>Fix Issues
                        </button>
                        
                        <div id="fixResults" class="mt-3" style="display: none;">
                            <h6>Results:</h6>
                            <div class="result-box" id="fixResultsContent"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>What This Tool Does
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Common Issues Fixed:</h6>
                        <ul>
                            <li><strong>Inconsistent Answer Format:</strong> Some questions use "A", others use full text</li>
                            <li><strong>Whitespace Problems:</strong> Leading/trailing spaces in answers</li>
                            <li><strong>Option Format Variations:</strong> Mixed array formats (indexed vs associative)</li>
                            <li><strong>Missing Correct Answers:</strong> Answer not found in available options</li>
                        </ul>
                        
                        <h6 class="mt-3">After Fixing:</h6>
                        <ul>
                            <li>All correct answers will use letter format (A-E)</li>
                            <li>All options will be normalized to <code>{"A": "text", "B": "text"}</code> format</li>
                            <li>Whitespace will be trimmed</li>
                            <li>Quiz grading will work consistently</li>
                        </ul>
                        
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Recommendation:</strong> Always run with "Dry Run" enabled first to preview changes before applying them.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const diagnoseBtn = document.getElementById('diagnoseBtn');
        const fixBtn = document.getElementById('fixBtn');
        const dryRunCheck = document.getElementById('dryRunCheck');
        const diagnoseResults = document.getElementById('diagnoseResults');
        const diagnoseResultsContent = document.getElementById('diagnoseResultsContent');
        const fixResults = document.getElementById('fixResults');
        const fixResultsContent = document.getElementById('fixResultsContent');

        diagnoseBtn.addEventListener('click', async () => {
            diagnoseBtn.disabled = true;
            diagnoseBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Scanning...';
            diagnoseResults.style.display = 'none';
            
            try {
                const response = await fetch('/admin/quiz-maintenance/diagnose', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({})
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Response error:', errorText);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    const results = data.results;
                    let html = `
                        <div class="alert alert-info">
                            <strong>Total Questions Checked:</strong> ${results.total_checked}<br>
                            <strong>Broken Questions Found:</strong> ${results.total_broken}
                        </div>
                    `;
                    
                    if (results.total_broken > 0) {
                        html += '<h6 class="text-danger">Broken Questions:</h6>';
                        results.broken_questions.forEach(q => {
                            html += `
                                <div class="broken-question">
                                    <strong>Question ID ${q.id}</strong> (${q.table})<br>
                                    <small>${q.question_text}...</small><br>
                                    <strong>Issues:</strong>
                                    <ul class="mb-0 mt-1">
                                        ${q.issues.map(issue => `<li>${issue}</li>`).join('')}
                                    </ul>
                                </div>
                            `;
                        });
                        
                        fixBtn.disabled = false;
                    } else {
                        html += '<div class="alert alert-success">✅ All questions are correctly formatted!</div>';
                    }
                    
                    diagnoseResultsContent.innerHTML = html;
                    diagnoseResults.style.display = 'block';
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error running diagnosis: ' + error.message);
            } finally {
                diagnoseBtn.disabled = false;
                diagnoseBtn.innerHTML = '<i class="fas fa-search me-2"></i>Run Diagnosis';
            }
        });

        fixBtn.addEventListener('click', async () => {
            const dryRun = dryRunCheck.checked;
            
            if (!dryRun && !confirm('Are you sure you want to apply these fixes? This will modify your database.')) {
                return;
            }
            
            fixBtn.disabled = true;
            fixBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            fixResults.style.display = 'none';
            
            try {
                const response = await fetch('/admin/quiz-maintenance/fix', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ dry_run: dryRun })
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Response error:', errorText);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    const results = data.results;
                    let html = `
                        <div class="alert ${dryRun ? 'alert-warning' : 'alert-success'}">
                            ${dryRun ? '<strong>DRY RUN - No changes made</strong><br>' : ''}
                            <strong>${dryRun ? 'Would Fix' : 'Fixed'}:</strong> ${results.fixed} questions<br>
                            <strong>Errors:</strong> ${results.errors} questions
                        </div>
                    `;
                    
                    if (results.details.length > 0) {
                        html += `<h6>${dryRun ? 'Would Fix' : 'Fixed'} Questions:</h6>`;
                        results.details.slice(0, 50).forEach(detail => {
                            html += `
                                <div class="fixed-question">
                                    <strong>${detail.table} - ID ${detail.id}</strong><br>
                                    <small>Changed: '${detail.old}' → '${detail.new}'</small>
                                </div>
                            `;
                        });
                        
                        if (results.details.length > 50) {
                            html += `<p class="text-muted">... and ${results.details.length - 50} more</p>`;
                        }
                    }
                    
                    if (!dryRun && results.fixed > 0) {
                        html += '<div class="alert alert-success mt-3">✅ All fixes applied successfully! Run diagnosis again to verify.</div>';
                    }
                    
                    fixResultsContent.innerHTML = html;
                    fixResults.style.display = 'block';
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error applying fixes: ' + error.message);
            } finally {
                fixBtn.disabled = false;
                fixBtn.innerHTML = '<i class="fas fa-wrench me-2"></i>Fix Issues';
            }
        });
    </script>
</body>
</html>
