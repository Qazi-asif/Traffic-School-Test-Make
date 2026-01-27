@extends('layouts.app')

@section('title', 'Student Review - ' . $enrollment->first_name . ' ' . $enrollment->last_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Student Header -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-user-graduate me-2"></i>
                                {{ $enrollment->first_name }} {{ $enrollment->last_name }}
                            </h4>
                            <p class="mb-0">{{ $enrollment->email }} • {{ $enrollment->course_title }} ({{ $enrollment->course_state_code }})</p>
                        </div>
                        <a href="{{ route('admin.student-feedback.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6>Course Progress</h6>
                                <div class="progress mb-2" style="height: 25px;">
                                    <div class="progress-bar" style="width: {{ $enrollment->progress_percentage ?? 0 }}%">
                                        {{ round($enrollment->progress_percentage ?? 0) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6>Quiz Average</h6>
                                <span class="badge bg-{{ ($enrollment->quiz_average ?? 0) >= 80 ? 'success' : 'warning' }} fs-5">
                                    {{ round($enrollment->quiz_average ?? 0) }}%
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6>Enrollment Date</h6>
                                <span class="text-muted">{{ date('M j, Y', strtotime($enrollment->created_at)) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6>Final Exam Status</h6>
                                @if($feedback && $feedback->status === 'approved')
                                    <span class="badge bg-success"><i class="fas fa-unlock me-1"></i>Allowed</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-lock me-1"></i>Blocked</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Quiz Results -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Chapter Quiz Results</h5>
                        </div>
                        <div class="card-body">
                            @if($chapterQuizzes->count() > 0)
                                @foreach($chapterQuizzes as $quiz)
                                    <div class="border rounded p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">{{ $quiz->chapter_title ?? 'Chapter ' . $quiz->chapter_id }}</h6>
                                            <div class="d-flex gap-2">
                                                <span class="badge bg-{{ $quiz->percentage >= 80 ? 'success' : 'warning' }}">
                                                    {{ $quiz->percentage }}% ({{ $quiz->correct_answers }}/{{ $quiz->total_questions }})
                                                </span>
                                                @if($quiz->feedback_status)
                                                    <span class="badge bg-info">{{ ucfirst($quiz->feedback_status) }}</span>
                                                @else
                                                    <span class="badge bg-secondary">No Feedback</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Quiz Details -->
                                        @if(isset($quizDetails[$quiz->chapter_id]) && $quizDetails[$quiz->chapter_id]->count() > 0)
                                            <div class="mt-3">
                                                <button class="btn btn-sm btn-outline-primary" type="button" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#quiz-details-{{ $quiz->chapter_id }}">
                                                    <i class="fas fa-eye me-1"></i>View Question Details
                                                </button>
                                                
                                                <div class="collapse mt-2" id="quiz-details-{{ $quiz->chapter_id }}">
                                                    <div class="bg-light p-3 rounded">
                                                        @foreach($quizDetails[$quiz->chapter_id] as $attempt)
                                                            <div class="border rounded p-3 mb-3 bg-white">
                                                                <div class="mb-3">
                                                                    <h6 class="text-primary mb-2">
                                                                        <i class="fas fa-question-circle me-1"></i>
                                                                        Question {{ $loop->iteration }}
                                                                        <span class="badge bg-{{ $attempt->is_correct ? 'success' : 'danger' }} ms-2">
                                                                            {{ $attempt->is_correct ? 'Correct' : 'Incorrect' }}
                                                                        </span>
                                                                    </h6>
                                                                    <p class="mb-3"><strong>{{ $attempt->question_text }}</strong></p>
                                                                </div>
                                                                
                                                                <!-- Answer Options -->
                                                                <div class="row mb-3">
                                                                    <div class="col-md-6">
                                                                        <h6 class="text-muted mb-2">Answer Options:</h6>
                                                                        <div class="options-list">
                                                                            @if($attempt->option_a)
                                                                                <div class="option-item mb-1 p-2 rounded {{ $attempt->student_answer === 'A' ? ($attempt->is_correct ? 'bg-success text-white' : 'bg-danger text-white') : ($attempt->correct_answer === 'A' ? 'bg-success bg-opacity-25' : 'bg-light') }}">
                                                                                    <strong>A:</strong> {{ $attempt->option_a }}
                                                                                    @if($attempt->student_answer === 'A')
                                                                                        <i class="fas fa-arrow-left ms-2"></i> <small>Student's Answer</small>
                                                                                    @endif
                                                                                    @if($attempt->correct_answer === 'A')
                                                                                        <i class="fas fa-check ms-2"></i> <small>Correct Answer</small>
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                            @if($attempt->option_b)
                                                                                <div class="option-item mb-1 p-2 rounded {{ $attempt->student_answer === 'B' ? ($attempt->is_correct ? 'bg-success text-white' : 'bg-danger text-white') : ($attempt->correct_answer === 'B' ? 'bg-success bg-opacity-25' : 'bg-light') }}">
                                                                                    <strong>B:</strong> {{ $attempt->option_b }}
                                                                                    @if($attempt->student_answer === 'B')
                                                                                        <i class="fas fa-arrow-left ms-2"></i> <small>Student's Answer</small>
                                                                                    @endif
                                                                                    @if($attempt->correct_answer === 'B')
                                                                                        <i class="fas fa-check ms-2"></i> <small>Correct Answer</small>
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                            @if($attempt->option_c)
                                                                                <div class="option-item mb-1 p-2 rounded {{ $attempt->student_answer === 'C' ? ($attempt->is_correct ? 'bg-success text-white' : 'bg-danger text-white') : ($attempt->correct_answer === 'C' ? 'bg-success bg-opacity-25' : 'bg-light') }}">
                                                                                    <strong>C:</strong> {{ $attempt->option_c }}
                                                                                    @if($attempt->student_answer === 'C')
                                                                                        <i class="fas fa-arrow-left ms-2"></i> <small>Student's Answer</small>
                                                                                    @endif
                                                                                    @if($attempt->correct_answer === 'C')
                                                                                        <i class="fas fa-check ms-2"></i> <small>Correct Answer</small>
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                            @if($attempt->option_d)
                                                                                <div class="option-item mb-1 p-2 rounded {{ $attempt->student_answer === 'D' ? ($attempt->is_correct ? 'bg-success text-white' : 'bg-danger text-white') : ($attempt->correct_answer === 'D' ? 'bg-success bg-opacity-25' : 'bg-light') }}">
                                                                                    <strong>D:</strong> {{ $attempt->option_d }}
                                                                                    @if($attempt->student_answer === 'D')
                                                                                        <i class="fas fa-arrow-left ms-2"></i> <small>Student's Answer</small>
                                                                                    @endif
                                                                                    @if($attempt->correct_answer === 'D')
                                                                                        <i class="fas fa-check ms-2"></i> <small>Correct Answer</small>
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                            @if($attempt->option_e)
                                                                                <div class="option-item mb-1 p-2 rounded {{ $attempt->student_answer === 'E' ? ($attempt->is_correct ? 'bg-success text-white' : 'bg-danger text-white') : ($attempt->correct_answer === 'E' ? 'bg-success bg-opacity-25' : 'bg-light') }}">
                                                                                    <strong>E:</strong> {{ $attempt->option_e }}
                                                                                    @if($attempt->student_answer === 'E')
                                                                                        <i class="fas fa-arrow-left ms-2"></i> <small>Student's Answer</small>
                                                                                    @endif
                                                                                    @if($attempt->correct_answer === 'E')
                                                                                        <i class="fas fa-check ms-2"></i> <small>Correct Answer</small>
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <h6 class="text-muted mb-2">Question Feedback:</h6>
                                                                        <textarea class="form-control form-control-sm" 
                                                                                  id="question_feedback_{{ $attempt->id }}" 
                                                                                  rows="3" 
                                                                                  placeholder="Provide specific feedback for this question..."></textarea>
                                                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                                                                onclick="saveQuestionFeedback({{ $attempt->id }})">
                                                                            <i class="fas fa-save me-1"></i>Save Question Feedback
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                
                                                                @if($attempt->explanation)
                                                                    <div class="mt-3 p-2 bg-info bg-opacity-10 rounded">
                                                                        <h6 class="text-info mb-1">
                                                                            <i class="fas fa-lightbulb me-1"></i>Explanation:
                                                                        </h6>
                                                                        <p class="mb-0 small">{{ $attempt->explanation }}</p>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="mt-3">
                                                <div class="text-center text-muted py-3">
                                                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                                                    <p class="mb-0">Individual question details not available for this quiz.</p>
                                                    <small>You can still provide overall quiz feedback below.</small>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- Quiz Feedback Section -->
                                        <div class="mt-3 border-top pt-3">
                                            @if($quiz->quiz_feedback)
                                                <div class="alert alert-info mb-2">
                                                    <strong>Previous Feedback:</strong> {{ $quiz->quiz_feedback }}
                                                </div>
                                            @endif
                                            
                                            <form onsubmit="submitQuizFeedback(event, {{ $enrollment->id }}, {{ $quiz->chapter_id }})">
                                                <div class="mb-2">
                                                    <label class="form-label small">Quiz Feedback:</label>
                                                    <textarea class="form-control form-control-sm" 
                                                              name="quiz_feedback" 
                                                              rows="2" 
                                                              placeholder="Provide feedback on this quiz performance...">{{ $quiz->quiz_feedback ?? '' }}</textarea>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <select class="form-select form-select-sm" name="status" style="width: auto;">
                                                            <option value="reviewed" {{ $quiz->feedback_status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                                            <option value="needs_improvement" {{ $quiz->feedback_status === 'needs_improvement' ? 'selected' : '' }}>Needs Improvement</option>
                                                            <option value="approved" {{ $quiz->feedback_status === 'approved' ? 'selected' : '' }}>Approved</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-save me-1"></i>Save Quiz Feedback
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-clipboard fa-2x mb-2"></i>
                                    <p>No chapter quiz results found.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Free Response Answers -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Free Response Answers</h5>
                        </div>
                        <div class="card-body">
                            @if($freeResponseAnswers->count() > 0)
                                @foreach($freeResponseAnswers as $answer)
                                    <div class="border rounded p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-1">Question {{ $loop->iteration }}</h6>
                                            <div class="d-flex gap-2">
                                                <span class="badge bg-info">{{ $answer->word_count }} words</span>
                                                <span class="badge bg-warning">{{ $answer->points }} pts</span>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <strong>Question:</strong>
                                            <p class="text-muted small mb-2">{{ $answer->question_text }}</p>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <strong>Student Answer:</strong>
                                            <div class="bg-light p-2 rounded">
                                                {{ $answer->answer_text }}
                                            </div>
                                        </div>
                                        
                                        @if($answer->sample_answer)
                                            <div class="mb-2">
                                                <strong>Sample Answer:</strong>
                                                <div class="bg-info bg-opacity-10 p-2 rounded small">
                                                    {{ $answer->sample_answer }}
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- Grading Section -->
                                        <div class="border-top pt-2">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label small">Score (0-{{ $answer->points }}):</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           id="score_{{ $answer->id }}" 
                                                           value="{{ $answer->score ?? '' }}"
                                                           min="0" max="{{ $answer->points }}" step="0.5">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Status:</label>
                                                    <span class="badge bg-{{ $answer->status === 'graded' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($answer->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <label class="form-label small">Feedback:</label>
                                                <textarea class="form-control form-control-sm" 
                                                          id="feedback_{{ $answer->id }}" 
                                                          rows="2" 
                                                          placeholder="Provide feedback on this answer...">{{ $answer->feedback ?? '' }}</textarea>
                                            </div>
                                            <div class="mt-2">
                                                <button class="btn btn-sm btn-success" 
                                                        onclick="gradeAnswer({{ $answer->id }})">
                                                    <i class="fas fa-save me-1"></i>Save Grade
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-edit fa-2x mb-2"></i>
                                    <p>No free response answers found.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructor Feedback Section -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Overall Instructor Feedback</h5>
                </div>
                <div class="card-body">
                    @if($feedback)
                        <div class="alert alert-info mb-4">
                            <h6><i class="fas fa-info-circle me-2"></i>Previous Feedback</h6>
                            <p class="mb-2"><strong>Status:</strong> 
                                <span class="badge bg-{{ $feedback->status === 'approved' ? 'success' : 'warning' }}">
                                    {{ ucfirst($feedback->status) }}
                                </span>
                            </p>
                            <p class="mb-2"><strong>Given:</strong> {{ date('M j, Y g:i A', strtotime($feedback->feedback_given_at)) }}</p>
                            <p class="mb-0"><strong>Feedback:</strong> {{ $feedback->instructor_feedback }}</p>
                        </div>
                    @endif

                    <form action="{{ route('admin.student-feedback.store', $enrollment->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="instructor_feedback" class="form-label">Overall Instructor Feedback <span class="text-danger">*</span></label>
                            <textarea name="instructor_feedback" id="instructor_feedback" rows="6" 
                                      class="form-control @error('instructor_feedback') is-invalid @enderror" 
                                      placeholder="Provide detailed feedback on the student's overall performance, areas for improvement, and recommendations..."
                                      required>{{ old('instructor_feedback', $feedback->instructor_feedback ?? '') }}</textarea>
                            @error('instructor_feedback')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> This feedback will be shown to the student before they can take the final exam.
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allow_final_exam" id="allow_final_exam" 
                                           value="1" {{ old('allow_final_exam', $feedback && $feedback->status === 'approved') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_final_exam">
                                        <strong class="text-success">Allow Final Exam</strong>
                                    </label>
                                    <div class="form-text">
                                        <i class="fas fa-unlock"></i> Student can proceed to take the final exam.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="requires_improvement" id="requires_improvement" 
                                           value="1" {{ old('requires_improvement', $feedback && $feedback->status === 'needs_improvement') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_improvement">
                                        <strong class="text-warning">Requires Improvement</strong>
                                    </label>
                                    <div class="form-text">
                                        <i class="fas fa-exclamation-triangle"></i> Student needs to improve before final exam.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.student-feedback.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to List
                            </a>
                            <button type="submit" class="btn btn-warning text-dark">
                                <i class="fas fa-paper-plane me-1"></i>Submit Overall Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function saveQuestionFeedback(questionId) {
    const feedback = document.getElementById(`question_feedback_${questionId}`).value;
    
    if (!feedback.trim()) {
        alert('Please enter feedback before saving.');
        return;
    }
    
    fetch(`/admin/student-feedback/question-feedback/${questionId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            feedback: feedback
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Question feedback saved successfully!', 'success');
        } else {
            showNotification('Error: ' + (data.error || 'Failed to save question feedback'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to save question feedback', 'error');
    });
}

function gradeAnswer(answerId) {
    const score = document.getElementById(`score_${answerId}`).value;
    const feedback = document.getElementById(`feedback_${answerId}`).value;
    
    if (!score) {
        alert('Please enter a score before saving.');
        return;
    }
    
    fetch(`/admin/student-feedback/grade-answer/${answerId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            score: parseFloat(score),
            feedback: feedback
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Answer graded successfully!', 'success');
        } else {
            showNotification('Error: ' + (data.error || 'Failed to grade answer'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to grade answer', 'error');
    });
}

function submitQuizFeedback(event, enrollmentId, chapterId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    fetch(`/admin/student-feedback/quiz-feedback/${enrollmentId}/${chapterId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Quiz feedback saved successfully!', 'success');
        } else {
            showNotification('Error: ' + (data.error || 'Failed to save quiz feedback'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to save quiz feedback', 'error');
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Character counter for feedback
document.getElementById('instructor_feedback').addEventListener('input', function() {
    const maxLength = 5000;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    let counter = document.getElementById('feedback_counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'feedback_counter';
        counter.className = 'form-text text-end';
        this.parentNode.appendChild(counter);
    }
    
    counter.innerHTML = `<small class="${remaining < 200 ? 'text-warning' : 'text-muted'}">${currentLength}/${maxLength} characters</small>`;
});

// Initialize counter on page load
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('instructor_feedback').dispatchEvent(new Event('input'));
});

// Checkbox logic
document.getElementById('allow_final_exam').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('requires_improvement').checked = false;
    }
});

document.getElementById('requires_improvement').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('allow_final_exam').checked = false;
    }
});
</script>
@endsection