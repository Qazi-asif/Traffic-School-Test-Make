@extends('layouts.app')

@section('title', 'Chapter Quiz Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-list-ol me-2"></i>Chapter Quiz Settings
                        </h4>
                        <div class="text-end">
                            <small>Change how many questions students get on chapter quizzes</small>
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

                    @if($selectedCourse && count($chapterSettings) > 0)
                        <!-- Chapter Settings -->
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-cogs me-2"></i>Chapter Quiz Settings for: {{ $selectedCourse->title }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Example: Florida 4-Hour Course</h6>
                                    <ul class="mb-0">
                                        <li><strong>Total Questions in Pool:</strong> 50 questions per chapter</li>
                                        <li><strong>Questions to Select:</strong> 10 questions per student</li>
                                        <li><strong>Result:</strong> Each student gets 10 different random questions</li>
                                    </ul>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Chapter</th>
                                                <th>Total Questions Available</th>
                                                <th>Random Selection</th>
                                                <th>Questions to Select</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($chapterSettings as $setting)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $setting['chapter']->order_index }}. {{ $setting['chapter']->title }}</strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">{{ $setting['total_questions'] }} questions</span>
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   id="random_{{ $setting['chapter']->id }}"
                                                                   {{ $setting['use_random_selection'] ? 'checked' : '' }}
                                                                   onchange="toggleRandomSelection('{{ $setting['chapter']->id }}')">
                                                            <label class="form-check-label" for="random_{{ $setting['chapter']->id }}">
                                                                {{ $setting['use_random_selection'] ? 'Enabled' : 'Disabled' }}
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="input-group" style="width: 150px;">
                                                            <input type="number" 
                                                                   class="form-control" 
                                                                   id="questions_{{ $setting['chapter']->id }}"
                                                                   value="{{ $setting['questions_to_select'] }}"
                                                                   min="1" 
                                                                   max="{{ $setting['total_questions'] }}"
                                                                   {{ !$setting['use_random_selection'] ? 'disabled' : '' }}>
                                                            <span class="input-group-text">
                                                                / {{ $setting['total_questions'] }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <button type="button" 
                                                                class="btn btn-success btn-sm"
                                                                onclick="saveChapterSettings('{{ $setting['course_id'] }}', '{{ $setting['chapter']->id }}', '{{ $setting['course_table'] }}', '{{ $setting['chapter']->title }}')">
                                                            <i class="fas fa-save me-1"></i>Save
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="alert alert-warning mt-3">
                                    <h6><i class="fas fa-lightbulb me-2"></i>How Random Selection Works:</h6>
                                    <ul class="mb-0">
                                        <li><strong>Disabled:</strong> Students get all questions in order</li>
                                        <li><strong>Enabled:</strong> Students get a random subset of questions</li>
                                        <li><strong>Questions to Select:</strong> How many questions each student receives</li>
                                        <li><strong>Pool Size:</strong> Total questions available to choose from</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @elseif($selectedCourse)
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>No Chapter Quizzes Found</h6>
                            <p class="mb-0">The selected course "{{ $selectedCourse->title }}" doesn't have any chapters with quiz questions.</p>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Getting Started</h6>
                            <p class="mb-0">Select a course from the dropdown above to view and edit its chapter quiz settings.</p>
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
        window.location.href = `{{ route('admin.chapter-quiz-settings.index') }}?course_id=${courseId}`;
    } else {
        window.location.href = `{{ route('admin.chapter-quiz-settings.index') }}`;
    }
}

function toggleRandomSelection(chapterId) {
    const checkbox = document.getElementById(`random_${chapterId}`);
    const questionsInput = document.getElementById(`questions_${chapterId}`);
    const label = checkbox.nextElementSibling;
    
    if (checkbox.checked) {
        questionsInput.disabled = false;
        label.textContent = 'Enabled';
    } else {
        questionsInput.disabled = true;
        label.textContent = 'Disabled';
    }
}

function saveChapterSettings(courseId, chapterId, courseTable, chapterTitle) {
    const useRandom = document.getElementById(`random_${chapterId}`).checked;
    const questionsToSelect = document.getElementById(`questions_${chapterId}`).value;
    
    if (!questionsToSelect || questionsToSelect < 1) {
        showMessage('Please enter a valid number of questions', 'error');
        return;
    }

    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    button.disabled = true;

    fetch('{{ route("admin.chapter-quiz-settings.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            course_id: courseId,
            chapter_id: chapterId,
            course_table: courseTable,
            use_random_selection: useRandom,
            questions_to_select: parseInt(questionsToSelect)
        })
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