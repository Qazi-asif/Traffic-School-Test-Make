@extends('layouts.admin')

@section('title', 'Bulk Upload - Course & Quiz Content')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-upload"></i>
                        Bulk Upload - Course & Quiz Content
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-success">Unlimited File Size</span>
                        <span class="badge badge-info">No Word/Image Limits</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Upload Type Tabs -->
                    <ul class="nav nav-tabs" id="uploadTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="course-content-tab" data-bs-toggle="tab" data-bs-target="#course-content" type="button" role="tab">
                                <i class="fas fa-book"></i> Course Content Upload
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="quiz-content-tab" data-bs-toggle="tab" data-bs-target="#quiz-content" type="button" role="tab">
                                <i class="fas fa-question-circle"></i> Quiz Content Upload
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="bulk-management-tab" data-bs-toggle="tab" data-bs-target="#bulk-management" type="button" role="tab">
                                <i class="fas fa-cogs"></i> Bulk Management
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-4" id="uploadTabContent">
                        <!-- Course Content Upload Tab -->
                        <div class="tab-pane fade show active" id="course-content" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <form id="courseUploadForm" enctype="multipart/form-data">
                                        @csrf
                                        
                                        <!-- Course Selection -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="course_type" class="form-label">Course Type</label>
                                                <select class="form-select" id="course_type" name="course_type" required>
                                                    <option value="">Select Course Type</option>
                                                    <option value="courses">Generic Courses</option>
                                                    <option value="florida_courses">Florida Courses</option>
                                                    <option value="missouri_courses">Missouri Courses</option>
                                                    <option value="texas_courses">Texas Courses</option>
                                                    <option value="delaware_courses">Delaware Courses</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="course_id" class="form-label">Target Course</label>
                                                <select class="form-select" id="course_id" name="course_id" required>
                                                    <option value="">Select Course First</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- File Upload -->
                                        <div class="mb-3">
                                            <label for="files" class="form-label">Upload Files</label>
                                            <input type="file" class="form-control" id="files" name="files[]" multiple accept=".docx,.doc,.txt,.html,.htm,.pdf,.zip" required>
                                            <div class="form-text">
                                                <strong>Supported formats:</strong> Word (.docx, .doc), Text (.txt), HTML (.html, .htm), PDF (.pdf), ZIP archives (.zip)
                                                <br><strong>No file size limits!</strong> Upload as many files as needed.
                                            </div>
                                        </div>

                                        <!-- Upload Options -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="upload_type" class="form-label">Upload Type</label>
                                                <select class="form-select" id="upload_type" name="upload_type">
                                                    <option value="single_file">Single File per Chapter</option>
                                                    <option value="multiple_files">Multiple Files as Chapters</option>
                                                    <option value="zip_archive">ZIP Archive (Auto-extract)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="chapter_duration" class="form-label">Default Chapter Duration (minutes)</label>
                                                <input type="number" class="form-control" id="chapter_duration" name="chapter_duration" value="60" min="1">
                                            </div>
                                        </div>

                                        <!-- Advanced Options -->
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <h6 class="mb-0">
                                                    <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#advancedOptions">
                                                        Advanced Options <i class="fas fa-chevron-down"></i>
                                                    </button>
                                                </h6>
                                            </div>
                                            <div class="collapse" id="advancedOptions">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" id="auto_create_chapters" name="auto_create_chapters" checked>
                                                                <label class="form-check-label" for="auto_create_chapters">
                                                                    Auto-create chapters from content
                                                                </label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" id="extract_images" name="extract_images" checked>
                                                                <label class="form-check-label" for="extract_images">
                                                                    Extract and save images
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" id="preserve_formatting" name="preserve_formatting" checked>
                                                                <label class="form-check-label" for="preserve_formatting">
                                                                    Preserve text formatting
                                                                </label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" id="extract_quiz_questions" name="extract_quiz_questions" checked>
                                                                <label class="form-check-label" for="extract_quiz_questions">
                                                                    Auto-extract quiz questions
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-upload"></i> Upload Course Content
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Upload Status -->
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Upload Status</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="uploadProgress" style="display: none;">
                                                <div class="progress mb-3">
                                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                                </div>
                                                <div id="uploadStatus">Preparing upload...</div>
                                            </div>
                                            <div id="uploadResults" style="display: none;">
                                                <div class="alert alert-success">
                                                    <h6>Upload Complete!</h6>
                                                    <ul id="resultsList" class="mb-0"></ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quiz Content Upload Tab -->
                        <div class="tab-pane fade" id="quiz-content" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <form id="quizUploadForm" enctype="multipart/form-data">
                                        @csrf
                                        
                                        <!-- Chapter Selection -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="quiz_course_type" class="form-label">Course Type</label>
                                                <select class="form-select" id="quiz_course_type" name="course_type" required>
                                                    <option value="">Select Course Type</option>
                                                    <option value="courses">Generic Courses</option>
                                                    <option value="florida_courses">Florida Courses</option>
                                                    <option value="missouri_courses">Missouri Courses</option>
                                                    <option value="texas_courses">Texas Courses</option>
                                                    <option value="delaware_courses">Delaware Courses</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="quiz_course_id" class="form-label">Course</label>
                                                <select class="form-select" id="quiz_course_id" name="course_id" required>
                                                    <option value="">Select Course Type First</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="chapter_id" class="form-label">Target Chapter</label>
                                            <select class="form-select" id="chapter_id" name="chapter_id" required>
                                                <option value="">Select Course First</option>
                                            </select>
                                        </div>

                                        <!-- Quiz File Upload -->
                                        <div class="mb-3">
                                            <label for="quiz_files" class="form-label">Upload Quiz Files</label>
                                            <input type="file" class="form-control" id="quiz_files" name="files[]" multiple accept=".docx,.doc,.txt,.csv,.json" required>
                                            <div class="form-text">
                                                <strong>Supported formats:</strong> Word (.docx, .doc), Text (.txt), CSV (.csv), JSON (.json)
                                                <br><strong>Unlimited questions per file!</strong>
                                            </div>
                                        </div>

                                        <!-- Quiz Options -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="question_format" class="form-label">Question Format</label>
                                                <select class="form-select" id="question_format" name="question_format">
                                                    <option value="auto_detect">Auto-detect</option>
                                                    <option value="multiple_choice">Multiple Choice Only</option>
                                                    <option value="true_false">True/False Only</option>
                                                    <option value="mixed">Mixed Format</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" id="auto_assign_answers" name="auto_assign_answers">
                                                    <label class="form-check-label" for="auto_assign_answers">
                                                        Auto-assign correct answers
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="randomize_options" name="randomize_options">
                                                    <label class="form-check-label" for="randomize_options">
                                                        Randomize answer options
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fas fa-question-circle"></i> Upload Quiz Questions
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Quiz Upload Status -->
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Quiz Upload Status</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="quizUploadProgress" style="display: none;">
                                                <div class="progress mb-3">
                                                    <div class="progress-bar progress-bar-success" role="progressbar" style="width: 0%"></div>
                                                </div>
                                                <div id="quizUploadStatus">Processing questions...</div>
                                            </div>
                                            <div id="quizUploadResults" style="display: none;">
                                                <div class="alert alert-success">
                                                    <h6>Quiz Upload Complete!</h6>
                                                    <ul id="quizResultsList" class="mb-0"></ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Management Tab -->
                        <div class="tab-pane fade" id="bulk-management" role="tabpanel">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <h5><i class="fas fa-info-circle"></i> Bulk Management Tools</h5>
                                        <p>Manage uploaded content in bulk. These tools help you organize and maintain your course content efficiently.</p>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6>Content Statistics</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div id="contentStats">
                                                        <div class="d-flex justify-content-between">
                                                            <span>Total Courses:</span>
                                                            <strong>{{ $courses->count() }}</strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span>Florida Courses:</span>
                                                            <strong>{{ $stateCourses['florida']->count() }}</strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span>Missouri Courses:</span>
                                                            <strong>{{ $stateCourses['missouri']->count() }}</strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span>Texas Courses:</span>
                                                            <strong>{{ $stateCourses['texas']->count() }}</strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span>Delaware Courses:</span>
                                                            <strong>{{ $stateCourses['delaware']->count() }}</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-8">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6>Quick Actions</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <button class="btn btn-outline-primary btn-block mb-2" onclick="refreshContentStats()">
                                                                <i class="fas fa-sync"></i> Refresh Statistics
                                                            </button>
                                                            <button class="btn btn-outline-info btn-block mb-2" onclick="validateAllContent()">
                                                                <i class="fas fa-check-circle"></i> Validate All Content
                                                            </button>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <button class="btn btn-outline-warning btn-block mb-2" onclick="optimizeImages()">
                                                                <i class="fas fa-image"></i> Optimize Images
                                                            </button>
                                                            <button class="btn btn-outline-secondary btn-block mb-2" onclick="exportContent()">
                                                                <i class="fas fa-download"></i> Export Content
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Templates -->
<div class="d-none">
    <!-- Course Upload Template -->
    <div id="courseUploadTemplate">
        <div class="alert alert-info">
            <h6>Supported File Formats:</h6>
            <ul class="mb-0">
                <li><strong>Word Documents:</strong> .docx, .doc - Full content extraction with images</li>
                <li><strong>Text Files:</strong> .txt - Plain text content</li>
                <li><strong>HTML Files:</strong> .html, .htm - Formatted web content</li>
                <li><strong>PDF Files:</strong> .pdf - Text extraction (basic)</li>
                <li><strong>ZIP Archives:</strong> .zip - Bulk file processing</li>
            </ul>
        </div>
    </div>
    
    <!-- Quiz Upload Template -->
    <div id="quizUploadTemplate">
        <div class="alert alert-success">
            <h6>Quiz Question Formats:</h6>
            <ul class="mb-0">
                <li><strong>Multiple Choice:</strong> Question with A) B) C) D) options</li>
                <li><strong>True/False:</strong> Statement with True/False answer</li>
                <li><strong>CSV Format:</strong> Question, Option A, Option B, Option C, Option D, Correct Answer</li>
                <li><strong>JSON Format:</strong> Structured question data</li>
            </ul>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize upload functionality
    initializeBulkUpload();
});

function initializeBulkUpload() {
    // Course type change handler
    $('#course_type, #quiz_course_type').change(function() {
        const courseType = $(this).val();
        const targetSelect = $(this).attr('id') === 'course_type' ? '#course_id' : '#quiz_course_id';
        
        loadCourses(courseType, targetSelect);
    });
    
    // Course selection change handler for quiz
    $('#quiz_course_id').change(function() {
        const courseId = $(this).val();
        const courseType = $('#quiz_course_type').val();
        
        if (courseId && courseType) {
            loadChapters(courseId, courseType);
        }
    });
    
    // Course upload form handler
    $('#courseUploadForm').submit(function(e) {
        e.preventDefault();
        uploadCourseContent();
    });
    
    // Quiz upload form handler
    $('#quizUploadForm').submit(function(e) {
        e.preventDefault();
        uploadQuizContent();
    });
}

function loadCourses(courseType, targetSelect) {
    if (!courseType) {
        $(targetSelect).html('<option value="">Select Course Type First</option>');
        return;
    }
    
    $(targetSelect).html('<option value="">Loading...</option>');
    
    // Load courses based on type
    $.get(`/admin/api/courses/${courseType}`)
        .done(function(data) {
            let options = '<option value="">Select Course</option>';
            data.forEach(function(course) {
                options += `<option value="${course.id}">${course.title}</option>`;
            });
            $(targetSelect).html(options);
        })
        .fail(function() {
            $(targetSelect).html('<option value="">Error loading courses</option>');
        });
}

function loadChapters(courseId, courseType) {
    $('#chapter_id').html('<option value="">Loading...</option>');
    
    $.get(`/admin/api/courses/${courseType}/${courseId}/chapters`)
        .done(function(data) {
            let options = '<option value="">Select Chapter</option>';
            data.forEach(function(chapter) {
                options += `<option value="${chapter.id}">${chapter.title}</option>`;
            });
            $('#chapter_id').html(options);
        })
        .fail(function() {
            $('#chapter_id').html('<option value="">Error loading chapters</option>');
        });
}

function uploadCourseContent() {
    const formData = new FormData($('#courseUploadForm')[0]);
    
    // Show progress
    $('#uploadProgress').show();
    $('#uploadResults').hide();
    updateProgress(0, 'Preparing upload...');
    
    // Disable form
    $('#courseUploadForm button[type="submit"]').prop('disabled', true);
    
    $.ajax({
        url: '/admin/bulk-upload/course-content',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    const percentComplete = (evt.loaded / evt.total) * 100;
                    updateProgress(percentComplete, 'Uploading files...');
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            updateProgress(100, 'Processing complete!');
            showUploadResults(response);
            
            // Reset form
            $('#courseUploadForm')[0].reset();
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.message || 'Upload failed';
            showUploadError(error);
        },
        complete: function() {
            $('#courseUploadForm button[type="submit"]').prop('disabled', false);
        }
    });
}

function uploadQuizContent() {
    const formData = new FormData($('#quizUploadForm')[0]);
    
    // Show progress
    $('#quizUploadProgress').show();
    $('#quizUploadResults').hide();
    updateQuizProgress(0, 'Processing questions...');
    
    // Disable form
    $('#quizUploadForm button[type="submit"]').prop('disabled', true);
    
    $.ajax({
        url: '/admin/bulk-upload/quiz-content',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            updateQuizProgress(100, 'Questions processed!');
            showQuizUploadResults(response);
            
            // Reset form
            $('#quizUploadForm')[0].reset();
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.message || 'Quiz upload failed';
            showQuizUploadError(error);
        },
        complete: function() {
            $('#quizUploadForm button[type="submit"]').prop('disabled', false);
        }
    });
}

function updateProgress(percent, status) {
    $('#uploadProgress .progress-bar').css('width', percent + '%');
    $('#uploadStatus').text(status);
}

function updateQuizProgress(percent, status) {
    $('#quizUploadProgress .progress-bar').css('width', percent + '%');
    $('#quizUploadStatus').text(status);
}

function showUploadResults(response) {
    $('#uploadProgress').hide();
    
    let results = [];
    if (response.chapters_created > 0) {
        results.push(`${response.chapters_created} chapters created`);
    }
    if (response.questions_created > 0) {
        results.push(`${response.questions_created} quiz questions extracted`);
    }
    if (response.images_extracted > 0) {
        results.push(`${response.images_extracted} images saved`);
    }
    if (response.files_processed > 0) {
        results.push(`${response.files_processed} files processed`);
    }
    
    let html = '';
    results.forEach(function(result) {
        html += `<li>${result}</li>`;
    });
    
    if (response.errors && response.errors.length > 0) {
        html += '<li class="text-warning">Errors: ' + response.errors.join(', ') + '</li>';
    }
    
    $('#resultsList').html(html);
    $('#uploadResults').show();
}

function showQuizUploadResults(response) {
    $('#quizUploadProgress').hide();
    
    let results = [];
    if (response.questions_created > 0) {
        results.push(`${response.questions_created} questions created`);
    }
    if (response.files_processed > 0) {
        results.push(`${response.files_processed} files processed`);
    }
    
    let html = '';
    results.forEach(function(result) {
        html += `<li>${result}</li>`;
    });
    
    if (response.errors && response.errors.length > 0) {
        html += '<li class="text-warning">Errors: ' + response.errors.join(', ') + '</li>';
    }
    
    $('#quizResultsList').html(html);
    $('#quizUploadResults').show();
}

function showUploadError(error) {
    $('#uploadProgress').hide();
    $('#uploadResults').html(`
        <div class="alert alert-danger">
            <h6>Upload Failed</h6>
            <p>${error}</p>
        </div>
    `).show();
}

function showQuizUploadError(error) {
    $('#quizUploadProgress').hide();
    $('#quizUploadResults').html(`
        <div class="alert alert-danger">
            <h6>Quiz Upload Failed</h6>
            <p>${error}</p>
        </div>
    `).show();
}

// Bulk management functions
function refreshContentStats() {
    // Refresh content statistics
    $.get('/admin/bulk-upload/stats')
        .done(function(data) {
            // Update stats display
            console.log('Stats refreshed', data);
        });
}

function validateAllContent() {
    // Validate all content
    $.post('/admin/bulk-upload/validate')
        .done(function(data) {
            alert('Content validation complete');
        });
}

function optimizeImages() {
    // Optimize images
    $.post('/admin/bulk-upload/optimize-images')
        .done(function(data) {
            alert('Image optimization complete');
        });
}

function exportContent() {
    // Export content
    window.location.href = '/admin/bulk-upload/export';
}
</script>
@endsection

@section('styles')
<style>
.card-tools .badge {
    margin-left: 5px;
}

.progress {
    height: 20px;
}

.form-check {
    margin-bottom: 10px;
}

.nav-tabs .nav-link {
    color: #495057;
}

.nav-tabs .nav-link.active {
    color: #007bff;
    font-weight: 600;
}

.alert ul {
    padding-left: 20px;
}

#contentStats .d-flex {
    margin-bottom: 8px;
    padding: 4px 0;
    border-bottom: 1px solid #eee;
}

.btn-block {
    width: 100%;
}
</style>
@endsection