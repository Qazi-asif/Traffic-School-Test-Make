<!-- Quick Quiz Import Component -->
<div class="quick-quiz-import-section mt-3">
    <div class="card border-info">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-bolt"></i>
                Quick Quiz Import
                <small class="float-right">Import quiz questions instantly</small>
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-pills nav-sm mb-3" id="quickImportTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="quick-text-tab" data-toggle="pill" href="#quick-text" role="tab">
                                <i class="fas fa-paste"></i> Paste Text
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="quick-file-tab" data-toggle="pill" href="#quick-file" role="tab">
                                <i class="fas fa-file-upload"></i> Upload File
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="quick-auto-tab" data-toggle="pill" href="#quick-auto" role="tab">
                                <i class="fas fa-magic"></i> Auto-Detect
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="quickImportTabContent">
                        <!-- Text Import -->
                        <div class="tab-pane fade show active" id="quick-text" role="tabpanel">
                            <form id="quickTextImportForm">
                                <input type="hidden" name="chapter_id" value="{{ $chapterId ?? '' }}">
                                <input type="hidden" name="import_type" value="text">
                                <div class="form-group">
                                    <textarea class="form-control" name="text_content" rows="8" 
                                              placeholder="Paste your quiz questions here...

Example:
1. What is the speed limit in school zones?
A. 15 mph
B. 25 mph ***
C. 35 mph
D. 45 mph

2. When should you use turn signals?
A. Always ***
B. Sometimes
C. Never
D. Only at night"></textarea>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="replace_existing" id="quickTextReplace">
                                    <label class="form-check-label" for="quickTextReplace">
                                        Replace existing questions
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-upload"></i> Import Questions
                                </button>
                            </form>
                        </div>

                        <!-- File Import -->
                        <div class="tab-pane fade" id="quick-file" role="tabpanel">
                            <form id="quickFileImportForm" enctype="multipart/form-data">
                                <input type="hidden" name="chapter_id" value="{{ $chapterId ?? '' }}">
                                <input type="hidden" name="import_type" value="file">
                                <div class="form-group">
                                    <label for="quickFile">Select File</label>
                                    <input type="file" class="form-control-file" name="file" id="quickFile"
                                           accept=".docx,.doc,.pdf,.txt" required>
                                    <small class="form-text text-muted">
                                        Supported: Word (.docx, .doc), PDF (.pdf), Text (.txt)
                                    </small>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="replace_existing" id="quickFileReplace">
                                    <label class="form-check-label" for="quickFileReplace">
                                        Replace existing questions
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-upload"></i> Import from File
                                </button>
                            </form>
                        </div>

                        <!-- Auto-Detect -->
                        <div class="tab-pane fade" id="quick-auto" role="tabpanel">
                            <form id="quickAutoImportForm">
                                <input type="hidden" name="chapter_id" value="{{ $chapterId ?? '' }}">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Auto-Detection:</strong> This will scan the current chapter content for quiz questions and automatically import them.
                                </div>
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-magic"></i> Auto-Detect & Import Quiz
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Quick Stats -->
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="card-title">Current Quiz Questions</h6>
                            <h3 class="text-primary" id="currentQuestionCount">
                                <i class="fas fa-spinner fa-spin"></i>
                            </h3>
                            <small class="text-muted">questions in this chapter</small>
                        </div>
                    </div>

                    <!-- Format Guide -->
                    <div class="mt-3">
                        <h6><i class="fas fa-question-circle"></i> Quick Format Guide</h6>
                        <small class="text-muted">
                            <strong>Questions:</strong> Start with number (1. 2. 3.)<br>
                            <strong>Options:</strong> Start with letter (A. B. C. D.)<br>
                            <strong>Correct Answer:</strong> Add *** after correct option<br>
                            <strong>Example:</strong> B. Correct answer ***
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Alert -->
    <div id="quickImportResults" class="mt-3" style="display: none;"></div>
</div>

<script>
$(document).ready(function() {
    // Load current question count
    loadCurrentQuestionCount();

    // Handle text import
    $('#quickTextImportForm').on('submit', function(e) {
        e.preventDefault();
        submitQuickImport($(this), 'text');
    });

    // Handle file import
    $('#quickFileImportForm').on('submit', function(e) {
        e.preventDefault();
        submitQuickImport($(this), 'file');
    });

    // Handle auto-detect import
    $('#quickAutoImportForm').on('submit', function(e) {
        e.preventDefault();
        submitAutoImport($(this));
    });

    function loadCurrentQuestionCount() {
        const chapterId = $('input[name="chapter_id"]').val();
        if (!chapterId) return;

        $.get(`/api/chapters/${chapterId}/questions`)
            .done(function(questions) {
                $('#currentQuestionCount').text(questions.length);
            })
            .fail(function() {
                $('#currentQuestionCount').text('?');
            });
    }

    function submitQuickImport(form, type) {
        const formData = new FormData(form[0]);
        const btn = form.find('button[type="submit"]');
        const originalText = btn.html();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');

        $.ajax({
            url: '{{ route("admin.quick-quiz-import.import") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showQuickImportResults(response, 'success');
                form[0].reset();
                loadCurrentQuestionCount();
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Import failed';
                showQuickImportResults({error: error}, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    }

    function submitAutoImport(form) {
        const chapterId = $('input[name="chapter_id"]').val();
        const chapterContent = $('#chapter-content-editor').val() || $('textarea[name="content"]').val() || '';
        
        if (!chapterContent.trim()) {
            alert('No chapter content found to scan for quiz questions.');
            return;
        }

        const btn = form.find('button[type="submit"]');
        const originalText = btn.html();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Scanning...');

        $.ajax({
            url: '{{ route("admin.quick-quiz-import.auto-import") }}',
            method: 'POST',
            data: {
                chapter_id: chapterId,
                chapter_content: chapterContent,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showQuickImportResults(response, response.success ? 'success' : 'info');
                loadCurrentQuestionCount();
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Auto-detection failed';
                showQuickImportResults({error: error}, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    }

    function showQuickImportResults(response, type) {
        let alertClass = 'alert-info';
        let icon = 'fas fa-info-circle';

        if (type === 'success') {
            alertClass = 'alert-success';
            icon = 'fas fa-check-circle';
        } else if (type === 'error') {
            alertClass = 'alert-danger';
            icon = 'fas fa-exclamation-circle';
        }

        let html = `<div class="alert ${alertClass} alert-dismissible fade show">`;
        html += `<i class="${icon}"></i> `;

        if (response.success) {
            html += `<strong>Success!</strong> ${response.message}`;
            if (response.imported > 0) {
                html += `<br><small>Imported: ${response.imported} questions`;
                if (response.deleted > 0) {
                    html += `, Deleted: ${response.deleted} questions`;
                }
                html += '</small>';
            }
        } else {
            html += `<strong>Error:</strong> ${response.error || response.message}`;
        }

        html += '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>';
        html += '</div>';

        $('#quickImportResults').html(html).show();

        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('#quickImportResults').fadeOut();
        }, 5000);
    }
});
</script>

<style>
.quick-quiz-import-section .nav-pills .nav-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.quick-quiz-import-section .card-body {
    padding: 1rem;
}

.quick-quiz-import-section textarea {
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
}

.quick-quiz-import-section .btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}
</style>