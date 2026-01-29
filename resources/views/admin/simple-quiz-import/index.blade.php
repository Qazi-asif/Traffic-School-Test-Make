<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Quiz Import</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-import"></i> Simple Quiz Import System</h3>
                        <p class="mb-0 text-muted">Import quiz questions from text or Word documents</p>
                    </div>
                    <div class="card-body">
                        
                        <!-- Import Methods -->
                        <ul class="nav nav-tabs" id="importTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="text-tab" data-bs-toggle="tab" data-bs-target="#text-import" type="button" role="tab">
                                    <i class="fas fa-keyboard"></i> Text Import
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="file-tab" data-bs-toggle="tab" data-bs-target="#file-import" type="button" role="tab">
                                    <i class="fas fa-file-upload"></i> File Import
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="importTabsContent">
                            
                            <!-- Text Import Tab -->
                            <div class="tab-pane fade show active" id="text-import" role="tabpanel">
                                <form id="textImportForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="textChapterId" class="form-label">Select Chapter</label>
                                                <select class="form-select" id="textChapterId" name="chapter_id" required>
                                                    <option value="">Choose a chapter...</option>
                                                    @foreach($chapters as $chapter)
                                                        <option value="{{ $chapter->id }}">{{ $chapter->course_title }} - {{ $chapter->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="textReplaceExisting" name="replace_existing">
                                                    <label class="form-check-label" for="textReplaceExisting">
                                                        Replace existing questions in this chapter
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload"></i> Import Questions
                                            </button>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="textContent" class="form-label">Quiz Questions</label>
                                                <textarea class="form-control" id="textContent" name="text_content" rows="15" placeholder="Paste your quiz questions here...

Example format:
1. What is the speed limit in a school zone?
A. 15 mph
B. 25 mph **
C. 35 mph
D. 45 mph

2. When should you use turn signals?
A. Only when turning left
B. Only when turning right
C. Before any turn or lane change **
D. Only on highways

Note: Mark correct answers with ** after the option"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- File Import Tab -->
                            <div class="tab-pane fade" id="file-import" role="tabpanel">
                                <form id="fileImportForm" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="fileChapterId" class="form-label">Select Chapter</label>
                                                <select class="form-select" id="fileChapterId" name="chapter_id" required>
                                                    <option value="">Choose a chapter...</option>
                                                    @foreach($chapters as $chapter)
                                                        <option value="{{ $chapter->id }}">{{ $chapter->course_title }} - {{ $chapter->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="quizFile" class="form-label">Quiz File</label>
                                                <input type="file" class="form-control" id="quizFile" name="file" accept=".txt,.docx,.doc" required>
                                                <div class="form-text">Supported formats: TXT, DOCX, DOC (Max: 10MB)</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="fileReplaceExisting" name="replace_existing">
                                                    <label class="form-check-label" for="fileReplaceExisting">
                                                        Replace existing questions in this chapter
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-file-upload"></i> Import from File
                                            </button>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="alert alert-info">
                                                <h6><i class="fas fa-info-circle"></i> File Format Requirements:</h6>
                                                <ul class="mb-0">
                                                    <li>Questions must start with numbers (1., 2., etc.)</li>
                                                    <li>Options must start with letters (A., B., C., D.)</li>
                                                    <li>Mark correct answers with ** after the option</li>
                                                    <li>Each question should be separated by blank lines</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Results Area -->
                        <div id="importResults" class="mt-4" style="display: none;">
                            <div class="alert" id="resultAlert"></div>
                            <div id="questionPreview"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // CSRF token setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Text import form handler
        document.getElementById('textImportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/admin/simple-quiz-import/text', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showResults(data);
            })
            .catch(error => {
                console.error('Error:', error);
                showResults({
                    success: false,
                    error: 'Network error occurred'
                });
            });
        });

        // File import form handler
        document.getElementById('fileImportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/admin/simple-quiz-import/file', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showResults(data);
            })
            .catch(error => {
                console.error('Error:', error);
                showResults({
                    success: false,
                    error: 'Network error occurred'
                });
            });
        });

        // Show results function
        function showResults(data) {
            const resultsDiv = document.getElementById('importResults');
            const alertDiv = document.getElementById('resultAlert');
            const previewDiv = document.getElementById('questionPreview');
            
            resultsDiv.style.display = 'block';
            
            if (data.success) {
                alertDiv.className = 'alert alert-success';
                alertDiv.innerHTML = `
                    <h6><i class="fas fa-check-circle"></i> Import Successful!</h6>
                    <p>${data.message}</p>
                    <small>Imported: ${data.imported} questions | Deleted: ${data.deleted || 0} questions</small>
                `;
                
                // Show question preview if available
                if (data.questions && data.questions.length > 0) {
                    let previewHtml = '<h6>Question Preview:</h6>';
                    data.questions.slice(0, 3).forEach((q, index) => {
                        previewHtml += `
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h6>Question ${index + 1}: ${q.question}</h6>
                                    <ul class="list-unstyled">
                                        ${Object.entries(q.options).map(([key, value]) => 
                                            `<li><strong>${key}.</strong> ${value} ${q.correct_answer === key ? '<span class="badge bg-success">Correct</span>' : ''}</li>`
                                        ).join('')}
                                    </ul>
                                </div>
                            </div>
                        `;
                    });
                    previewDiv.innerHTML = previewHtml;
                } else {
                    previewDiv.innerHTML = '';
                }
                
            } else {
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerHTML = `
                    <h6><i class="fas fa-exclamation-triangle"></i> Import Failed</h6>
                    <p>${data.error}</p>
                    ${data.content_preview ? `<small><strong>Content Preview:</strong><br><pre>${data.content_preview}</pre></small>` : ''}
                `;
                previewDiv.innerHTML = '';
            }
        }
    </script>
</body>
</html>