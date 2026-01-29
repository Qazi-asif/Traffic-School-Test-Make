@extends('layouts.admin')

@section('title', 'Quiz Import System')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-import"></i>
                        Advanced Quiz Import System
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#formatGuideModal">
                            <i class="fas fa-question-circle"></i> Format Guide
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Import Tabs -->
                    <ul class="nav nav-tabs" id="importTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="single-tab" data-toggle="tab" href="#single" role="tab">
                                <i class="fas fa-file"></i> Single File Import
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="bulk-tab" data-toggle="tab" href="#bulk" role="tab">
                                <i class="fas fa-files"></i> Bulk Import (Up to 20 files)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="text-tab" data-toggle="tab" href="#text" role="tab">
                                <i class="fas fa-paste"></i> Text Paste Import
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="importTabContent">
                        <!-- Single File Import -->
                        <div class="tab-pane fade show active" id="single" role="tabpanel">
                            <form id="singleImportForm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="single_file">Select File</label>
                                            <input type="file" class="form-control-file" id="single_file" name="file" 
                                                   accept=".docx,.doc,.pdf,.txt,.csv" required>
                                            <small class="form-text text-muted">
                                                Supported formats: Word (.docx, .doc), PDF (.pdf), Text (.txt), CSV (.csv)
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="single_chapter">Target Chapter</label>
                                            <select class="form-control" id="single_chapter" name="chapter_id" required>
                                                <option value="">Select Chapter</option>
                                                @foreach($chapters as $chapter)
                                                    <option value="{{ $chapter->id }}">
                                                        {{ $chapter->course_title }} - {{ $chapter->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="single_replace" name="replace_existing">
                                        <label class="custom-control-label" for="single_replace">
                                            Replace existing questions in this chapter
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-info" id="previewSingleBtn">
                                        <i class="fas fa-eye"></i> Preview Questions
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Import Questions
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Bulk Import -->
                        <div class="tab-pane fade" id="bulk" role="tabpanel">
                            <form id="bulkImportForm" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="bulk_files">Select Multiple Files (Max 20)</label>
                                    <input type="file" class="form-control-file" id="bulk_files" name="files[]" 
                                           accept=".docx,.doc,.pdf,.txt,.csv" multiple required>
                                    <small class="form-text text-muted">
                                        Hold Ctrl/Cmd to select multiple files. Max 20 files, 50MB each.
                                    </small>
                                </div>
                                
                                <div id="bulkFileMapping" class="d-none">
                                    <h5>Chapter Mapping</h5>
                                    <p class="text-muted">Assign each file to a chapter:</p>
                                    <div id="fileMappingContainer"></div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="bulk_replace" name="replace_existing">
                                        <label class="custom-control-label" for="bulk_replace">
                                            Replace existing questions in target chapters
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-success" id="bulkImportBtn" disabled>
                                        <i class="fas fa-upload"></i> Import All Files
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Text Paste Import -->
                        <div class="tab-pane fade" id="text" role="tabpanel">
                            <form id="textImportForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="text_content">Paste Quiz Content</label>
                                            <textarea class="form-control" id="text_content" name="text_content" 
                                                      rows="15" placeholder="Paste your quiz questions here...
Example format:
1. What is the speed limit in school zones?
A. 15 mph
B. 25 mph ***
C. 35 mph
D. 45 mph

2. When should you use turn signals?
A. Only when other cars are present
B. Always when turning or changing lanes ***
C. Only on highways
D. Never in parking lots" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="text_chapter">Target Chapter</label>
                                            <select class="form-control" id="text_chapter" name="chapter_id" required>
                                                <option value="">Select Chapter</option>
                                                @foreach($chapters as $chapter)
                                                    <option value="{{ $chapter->id }}">
                                                        {{ $chapter->course_title }} - {{ $chapter->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="text_replace" name="replace_existing">
                                                <label class="custom-control-label" for="text_replace">
                                                    Replace existing questions
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <button type="button" class="btn btn-info" id="previewTextBtn">
                                                <i class="fas fa-eye"></i> Preview
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload"></i> Import
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="row mt-4" id="resultsSection" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Import Results</h3>
                </div>
                <div class="card-body" id="resultsContent">
                    <!-- Results will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Format Guide Modal -->
<div class="modal fade" id="formatGuideModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quiz Import Format Guide</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-file-word"></i> Word/PDF/Text Format</h6>
                        <pre class="bg-light p-3">1. What is the speed limit?
A. 15 mph
B. 25 mph ***
C. 35 mph
D. 45 mph
Explanation: School zones are 25 mph

2. When to signal?
A. Always ***
B. Sometimes
C. Never
D. Only at night</pre>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-file-csv"></i> CSV Format</h6>
                        <pre class="bg-light p-3">Question,Option A,Option B,Option C,Option D,Correct,Explanation
"Speed limit?","15 mph","25 mph","35 mph","45 mph","B","School zones"
"When signal?","Always","Sometimes","Never","Night only","A","Safety rule"</pre>
                    </div>
                </div>
                <div class="mt-3">
                    <h6>Supported Formats:</h6>
                    <ul>
                        <li><strong>Word Documents:</strong> .docx, .doc</li>
                        <li><strong>PDF Files:</strong> .pdf (text-based)</li>
                        <li><strong>Text Files:</strong> .txt</li>
                        <li><strong>CSV Files:</strong> .csv (comma-separated)</li>
                    </ul>
                    <h6>Marking Correct Answers:</h6>
                    <ul>
                        <li>Use <code>***</code> after the correct option</li>
                        <li>Use <code>(correct)</code> after the correct option</li>
                        <li>Use <code>[correct]</code> after the correct option</li>
                        <li>For CSV: Use letter (A, B, C, D) in the Correct column</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Question Preview</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Preview content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Handle bulk file selection
    $('#bulk_files').on('change', function() {
        const files = this.files;
        if (files.length > 20) {
            alert('Maximum 20 files allowed');
            this.value = '';
            return;
        }
        
        if (files.length > 0) {
            showBulkFileMapping(files);
        }
    });

    // Show file mapping for bulk import
    function showBulkFileMapping(files) {
        const container = $('#fileMappingContainer');
        container.empty();
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const row = $(`
                <div class="row mb-2">
                    <div class="col-md-6">
                        <strong>${file.name}</strong>
                        <small class="text-muted d-block">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                    </div>
                    <div class="col-md-6">
                        <select class="form-control chapter-select" name="chapter_mapping[${i}]" required>
                            <option value="">Select Chapter</option>
                            @foreach($chapters as $chapter)
                                <option value="{{ $chapter->id }}">{{ $chapter->course_title }} - {{ $chapter->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            `);
            container.append(row);
        }
        
        $('#bulkFileMapping').removeClass('d-none');
        $('#bulkImportBtn').prop('disabled', false);
    }

    // Single file import
    $('#singleImportForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = $(this).find('button[type="submit"]');
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');
        
        $.ajax({
            url: '{{ route("admin.quiz-import.single") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showResults(response);
                $('#singleImportForm')[0].reset();
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Import failed';
                alert('Error: ' + error);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Import Questions');
            }
        });
    });

    // Bulk import
    $('#bulkImportForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = $('#bulkImportBtn');
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');
        
        $.ajax({
            url: '{{ route("admin.quiz-import.bulk") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showBulkResults(response);
                $('#bulkImportForm')[0].reset();
                $('#bulkFileMapping').addClass('d-none');
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Bulk import failed';
                alert('Error: ' + error);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Import All Files');
            }
        });
    });

    // Text import
    $('#textImportForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = $(this).find('button[type="submit"]');
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');
        
        $.ajax({
            url: '{{ route("admin.quiz-import.text") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showResults(response);
                $('#text_content').val('');
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Text import failed';
                alert('Error: ' + error);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Import');
            }
        });
    });

    // Preview functionality
    $('#previewSingleBtn').on('click', function() {
        const fileInput = $('#single_file')[0];
        if (!fileInput.files.length) {
            alert('Please select a file first');
            return;
        }
        
        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        $.ajax({
            url: '{{ route("admin.quiz-import.preview") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showPreview(response);
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Preview failed';
                alert('Error: ' + error);
            }
        });
    });

    $('#previewTextBtn').on('click', function() {
        const textContent = $('#text_content').val();
        if (!textContent.trim()) {
            alert('Please enter some text first');
            return;
        }
        
        // Simple client-side preview for text
        const lines = textContent.split('\n');
        let questions = [];
        let currentQuestion = null;
        let currentOptions = [];
        
        lines.forEach(line => {
            line = line.trim();
            if (!line) return;
            
            if (/^\d+[\.\)]\s*/.test(line)) {
                if (currentQuestion && currentOptions.length > 0) {
                    questions.push({
                        question: currentQuestion,
                        options: currentOptions
                    });
                }
                currentQuestion = line.replace(/^\d+[\.\)]\s*/, '');
                currentOptions = [];
            } else if (/^[A-E][\.\)]\s*/.test(line)) {
                currentOptions.push(line);
            }
        });
        
        if (currentQuestion && currentOptions.length > 0) {
            questions.push({
                question: currentQuestion,
                options: currentOptions
            });
        }
        
        showPreview({
            success: true,
            questions: questions.slice(0, 5),
            total_questions: questions.length
        });
    });

    function showPreview(response) {
        if (!response.success) {
            alert('Preview failed: ' + response.error);
            return;
        }
        
        let html = `<div class="alert alert-info">Found ${response.total_questions} questions. Showing first 5:</div>`;
        
        response.questions.forEach((q, index) => {
            html += `
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Question ${index + 1}:</h6>
                        <p><strong>${q.question}</strong></p>
                        <ul class="list-unstyled ml-3">
            `;
            
            if (q.options) {
                Object.entries(q.options).forEach(([key, value]) => {
                    const isCorrect = key === q.correct_answer;
                    html += `<li class="${isCorrect ? 'text-success font-weight-bold' : ''}">${key}. ${value} ${isCorrect ? '✓' : ''}</li>`;
                });
            }
            
            html += `</ul>`;
            
            if (q.explanation) {
                html += `<small class="text-muted"><strong>Explanation:</strong> ${q.explanation}</small>`;
            }
            
            html += `</div></div>`;
        });
        
        $('#previewContent').html(html);
        $('#previewModal').modal('show');
    }

    function showResults(response) {
        let html = `
            <div class="alert alert-success">
                <h5><i class="fas fa-check-circle"></i> Import Successful!</h5>
                <p>${response.message}</p>
                <ul>
                    <li>Questions imported: <strong>${response.imported}</strong></li>
                    ${response.deleted > 0 ? `<li>Questions deleted: <strong>${response.deleted}</strong></li>` : ''}
                </ul>
            </div>
        `;
        
        $('#resultsContent').html(html);
        $('#resultsSection').show();
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#resultsSection').offset().top
        }, 500);
    }

    function showBulkResults(response) {
        let html = `
            <div class="alert alert-success">
                <h5><i class="fas fa-check-circle"></i> Bulk Import Successful!</h5>
                <p>${response.message}</p>
                <ul>
                    <li>Total questions imported: <strong>${response.total_imported}</strong></li>
                    ${response.total_deleted > 0 ? `<li>Total questions deleted: <strong>${response.total_deleted}</strong></li>` : ''}
                </ul>
            </div>
            <h6>File Details:</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Questions Imported</th>
                            <th>Questions Deleted</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        response.results.forEach(result => {
            html += `
                <tr>
                    <td>${result.filename}</td>
                    <td><span class="badge badge-success">${result.imported}</span></td>
                    <td><span class="badge badge-warning">${result.deleted}</span></td>
                </tr>
            `;
        });
        
        html += `</tbody></table></div>`;
        
        $('#resultsContent').html(html);
        $('#resultsSection').show();
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#resultsSection').offset().top
        }, 500);
    }
});
</script>
@endsection