@extends('layouts.app')

@section('title', 'Final Exam Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-graduation-cap me-2"></i>Final Exam Settings
                        </h4>
                        <div class="text-end">
                            <small>Change how many questions students get on final exam</small>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if(isset($error))
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Error</h6>
                            <p class="mb-0">{{ $error }}</p>
                        </div>
                    @endif

                    <!-- Course Selection -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label for="course_select" class="form-label">
                                <strong>Select Course</strong>
                            </label>
                            <select id="course_select" class="form-select" onchange="handleCourseChange()">
                                <option value="">Choose a course...</option>
                                @if($courses->count() > 0)
                                    @foreach($courses as $course)
                                        <option value="{{ $course->display_id }}" {{ ($selectedCourse && $selectedCourse->id == $course->id) ? 'selected' : '' }}>
                                            {{ $course->display_title }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No courses found</option>
                                @endif
                            </select>
                            @if($courses->count() == 0)
                                <div class="form-text text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    No courses found in the database.
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($selectedCourse && $examSettings)
                        <!-- Current Settings -->
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-cogs me-2"></i>Final Exam Settings for: {{ $selectedCourse->title }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Current Status</h6>
                                    <ul class="mb-0">
                                        <li><strong>Total Questions Available:</strong> {{ $examSettings['total_questions'] }} questions</li>
                                        <li><strong>Currently Selecting:</strong> {{ $examSettings['questions_to_select'] }} questions per student</li>
                                        <li><strong>Random Selection:</strong> {{ $examSettings['use_random_selection'] ? 'Enabled' : 'Disabled' }}</li>
                                    </ul>
                                </div>

                                <form id="settingsForm">
                                    @csrf
                                    <input type="hidden" name="course_id" value="{{ $examSettings['course_id'] }}">
                                    <input type="hidden" name="course_table" value="{{ $examSettings['course_table'] }}">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="questions_to_select" class="form-label">
                                                    <strong>Questions to Select</strong> <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" 
                                                           name="questions_to_select" 
                                                           id="questions_to_select"
                                                           class="form-control" 
                                                           value="{{ $examSettings['questions_to_select'] }}"
                                                           min="1" 
                                                           max="{{ $examSettings['total_questions'] }}"
                                                           required>
                                                    <span class="input-group-text">
                                                        / {{ $examSettings['total_questions'] }}
                                                    </span>
                                                </div>
                                                <div class="form-text">
                                                    How many questions each student gets on their final exam
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="use_random_selection" 
                                                           id="use_random_selection"
                                                           value="1" 
                                                           {{ $examSettings['use_random_selection'] ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="use_random_selection">
                                                        <strong>Use Random Selection</strong>
                                                    </label>
                                                    <div class="form-text">
                                                        Each student gets different random questions
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <button type="button" class="btn btn-success" onclick="saveSettings()">
                                                <i class="fas fa-save me-1"></i>Save Settings
                                            </button>
                                        </div>
                                        <div class="text-muted">
                                            <small>Changes take effect immediately for new exam attempts</small>
                                        </div>
                                    </div>
                                </form>

                                <div class="alert alert-warning mt-3">
                                    <h6><i class="fas fa-lightbulb me-2"></i>Examples:</h6>
                                    <ul class="mb-0">
                                        <li><strong>Florida 4-Hour:</strong> 40 questions from 500 available</li>
                                        <li><strong>Standard Course:</strong> 25 questions from 100 available</li>
                                        <li><strong>Short Course:</strong> 15 questions from 50 available</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @elseif($selectedCourse)
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>No Final Exam Questions</h6>
                            <p class="mb-0">The selected course "{{ $selectedCourse->title }}" doesn't have any final exam questions yet.</p>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Getting Started</h6>
                            <p class="mb-0">Select a course from the dropdown above to view and edit its final exam settings.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="message-container" style="position: fixed; top: 20px; right: 20px; z-index: 1050;"></div>

<!-- Inline script -->
<script>
function handleCourseChange() {
    const courseId = document.getElementById('course_select').value;
    if (courseId) {
        window.location.href = `{{ route('admin.final-exam-settings.index') }}?course_id=${courseId}`;
    } else {
        window.location.href = `{{ route('admin.final-exam-settings.index') }}`;
    }
}

function saveSettings() {
    const form = document.getElementById('settingsForm');
    const formData = new FormData(form);
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    button.disabled = true;

    fetch('{{ route("admin.final-exam-settings.update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while saving settings', 'error');
    })
    .finally(() => {
        // Restore button state
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function showMessage(message, type) {
    const container = document.getElementById('message-container');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="fas fa-${icon} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    container.appendChild(alert);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}
</script>
@endsection