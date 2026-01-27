@extends('layouts.app')

@section('title', 'Quiz Random Selection Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-random me-2"></i>Quiz Random Selection Settings
                        </h4>
                        <div class="text-end">
                            <small>Manage random question selection for normal quizzes</small>
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
                        <div class="col-md-6">
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
                                    No courses found in the database. Please add some courses first.
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div id="course-stats" class="mt-4" style="display: none;">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Course Statistics</h6>
                                    <div id="stats-content"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($selectedCourse && count($quizData) > 0)
                        <!-- Quiz Settings -->
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-cogs me-2"></i>Quiz Settings for: {{ $selectedCourse->title }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Example: Florida 4-Hour Course</h6>
                                    <ul class="mb-0">
                                        <li><strong>Total Questions in Pool:</strong> 500 questions</li>
                                        <li><strong>Questions to Select:</strong> 40 questions per student</li>
                                        <li><strong>Result:</strong> Each student gets 40 different random questions</li>
                                    </ul>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Quiz/Chapter</th>
                                                <th>Total Questions Available</th>
                                                <th>Random Selection</th>
                                                <th>Questions to Select</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($quizData as $quiz)
                                                <tr id="quiz-row-{{ $quiz['chapter']->id ?? 'final' }}">
                                                    <td>
                                                        <strong>
                                                            @if($quiz['chapter']->id)
                                                                {{ $quiz['chapter']->order_index }}. {{ $quiz['chapter']->title }}
                                                            @else
                                                                <i class="fas fa-graduation-cap me-1"></i>Final Exam
                                                            @endif
                                                        </strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">{{ $quiz['total_questions'] }} questions</span>
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   id="random_{{ $quiz['chapter']->id ?? 'final' }}"
                                                                   {{ $quiz['use_random_selection'] ? 'checked' : '' }}
                                                                   onchange="toggleRandomSelection('{{ $quiz['chapter']->id ?? 'final' }}')">
                                                            <label class="form-check-label" for="random_{{ $quiz['chapter']->id ?? 'final' }}">
                                                                {{ $quiz['use_random_selection'] ? 'Enabled' : 'Disabled' }}
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="input-group" style="width: 150px;">
                                                            <input type="number" 
                                                                   class="form-control" 
                                                                   id="questions_{{ $quiz['chapter']->id ?? 'final' }}"
                                                                   value="{{ $quiz['questions_to_select'] }}"
                                                                   min="1" 
                                                                   max="{{ $quiz['total_questions'] }}"
                                                                   {{ !$quiz['use_random_selection'] ? 'disabled' : '' }}>
                                                            <span class="input-group-text">
                                                                / {{ $quiz['total_questions'] }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <button type="button" 
                                                                class="btn btn-success btn-sm"
                                                                onclick="saveQuizSettings('{{ $quiz['chapter']->id ?? '' }}', '{{ $quiz['chapter']->title ?? 'Final Exam' }}')">
                                                            <i class="fas fa-save me-1"></i>Save
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="alert alert-info mt-3">
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
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>No Quizzes Found</h6>
                            <p class="mb-0">The selected course "{{ $selectedCourse->title }}" doesn't have any chapters with quiz questions.</p>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Getting Started</h6>
                            <p class="mb-0">Select a course from the dropdown above to view and manage its quiz random selection settings.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="message-container" style="position: fixed; top: 20px; right: 20px; z-index: 1050;"></div>

<!-- Inline script to ensure functions are available immediately -->
<script>
function handleCourseChange() {
    const courseId = document.getElementById('course_select').value;
    if (courseId) {
        window.location.href = `{{ route('admin.quiz-random-selection.index') }}?course_id=${courseId}`;
    } else {
        window.location.href = `{{ route('admin.quiz-random-selection.index') }}`;
    }
}

// Alias for backward compatibility
function loadCourseQuizzes() {
    handleCourseChange();
}
</script>
@endsection

@section('scripts')
<script>
// Define functions immediately to avoid reference errors
function handleCourseChange() {
    const courseId = document.getElementById('course_select').value;
    if (courseId) {
        window.location.href = `{{ route('admin.quiz-random-selection.index') }}?course_id=${courseId}`;
    } else {
        window.location.href = `{{ route('admin.quiz-random-selection.index') }}`;
    }
}

function loadCourseQuizzes() {
    handleCourseChange(); // Alias for backward compatibility
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

function saveQuizSettings(chapterId, chapterName) {
    const courseId = document.getElementById('course_select').value;
    const useRandom = document.getElementById(`random_${chapterId || 'final'}`).checked;
    const questionsToSelect = document.getElementById(`questions_${chapterId || 'final'}`).value;
    
    if (!courseId) {
        showMessage('Please select a course first', 'error');
        return;
    }

    if (!questionsToSelect || questionsToSelect < 1) {
        showMessage('Please enter a valid number of questions', 'error');
        return;
    }

    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    button.disabled = true;

    fetch('{{ route("admin.quiz-random-selection.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            course_id: courseId,
            chapter_id: chapterId || null,
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

// Load course stats when course is selected
document.addEventListener('DOMContentLoaded', function() {
    const courseSelect = document.getElementById('course_select');
    if (courseSelect.value) {
        loadCourseStats(courseSelect.value);
    }
});

function loadCourseStats(courseId) {
    if (!courseId) return;
    
    fetch(`{{ route('admin.quiz-random-selection.stats') }}?course_id=${courseId}`)
        .then(response => response.json())
        .then(data => {
            const statsContainer = document.getElementById('course-stats');
            const statsContent = document.getElementById('stats-content');
            
            statsContent.innerHTML = `
                <ul class="mb-0">
                    <li><strong>Chapters with Quizzes:</strong> ${data.total_chapters_with_quizzes}</li>
                    <li><strong>Total Questions:</strong> ${data.total_questions}</li>
                    <li><strong>Random Selection Enabled:</strong> ${data.chapters_with_random_selection} chapters</li>
                </ul>
            `;
            
            statsContainer.style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading stats:', error);
        });
}
</script>
@endsection