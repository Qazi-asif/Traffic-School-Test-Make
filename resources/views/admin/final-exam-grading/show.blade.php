@extends('layouts.app')

@section('title', 'Grade Final Exam - ' . $result->user->first_name . ' ' . $result->user->last_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-clipboard-check me-2"></i>Grade Final Exam</h2>
                    <p class="text-muted mb-0">
                        <strong>{{ $result->user->first_name }} {{ $result->user->last_name }}</strong> - 
                        {{ $courseDetails->title }} ({{ $courseDetails->state_code ?? 'Course' }})
                    </p>
                </div>
                <a href="{{ route('admin.final-exam-grading.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to List
                </a>
            </div>

            <!-- Grading Period Status -->
            @if($result->is_grading_period_active)
                <div class="alert alert-warning">
                    <h5><i class="fas fa-clock me-2"></i>Grading Period Active</h5>
                    <p class="mb-0">{{ $result->remaining_grading_time }} to complete grading for this exam.</p>
                </div>
            @elseif(!$result->grading_completed)
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Grading Period Expired</h5>
                    <p class="mb-0">The 24-hour grading period has expired. Only super admins can modify this result.</p>
                </div>
            @else
                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle me-2"></i>Grading Completed</h5>
                    <p class="mb-0">
                        Graded by {{ $result->gradedBy->first_name ?? 'Admin' }} on 
                        {{ $result->graded_at->format('M j, Y g:i A') }}
                    </p>
                </div>
            @endif

            <div class="row">
                <!-- Student Performance Overview -->
                <div class="col-md-8">
                    <!-- Score Summary -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Performance Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="mb-3">
                                        <div class="display-4 fw-bold text-{{ $result->is_passing ? 'success' : 'danger' }}">
                                            {{ number_format($result->overall_score, 1) }}%
                                        </div>
                                        <div class="h5 mb-2">Overall Score</div>
                                        <span class="badge bg-{{ $result->is_passing ? 'success' : 'danger' }} fs-6">
                                            Grade {{ $result->grade_letter }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-center p-3 border rounded">
                                                <h6>Chapter Quizzes</h6>
                                                <div class="h4 text-info">{{ number_format($componentScores['quiz_average']['score'], 1) }}%</div>
                                                <small class="text-muted">Weight: 30%</small>
                                                <div class="progress mt-2" style="height: 8px;">
                                                    <div class="progress-bar bg-info" style="width: {{ $componentScores['quiz_average']['score'] }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($componentScores['free_response']['score'] !== null)
                                        <div class="col-md-4">
                                            <div class="text-center p-3 border rounded">
                                                <h6>Free Response</h6>
                                                <div class="h4 text-warning">{{ number_format($componentScores['free_response']['score'], 1) }}%</div>
                                                <small class="text-muted">Weight: 20%</small>
                                                <div class="progress mt-2" style="height: 8px;">
                                                    <div class="progress-bar bg-warning" style="width: {{ $componentScores['free_response']['score'] }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="col-md-4">
                                            <div class="text-center p-3 border rounded">
                                                <h6>Final Exam</h6>
                                                <div class="h4 text-success">{{ number_format($result->final_exam_score, 1) }}%</div>
                                                <small class="text-muted">{{ $result->final_exam_correct }}/{{ $result->final_exam_total }} Correct (50%)</small>
                                                <div class="progress mt-2" style="height: 8px;">
                                                    <div class="progress-bar bg-success" style="width: {{ $result->final_exam_score }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Final Exam Question Details -->
                    @if($result->questionResults->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Final Exam Question Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="questionsAccordion">
                                @foreach($result->questionResults as $questionResult)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $questionResult->id }}">
                                            <button class="accordion-button collapsed" type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#collapse{{ $questionResult->id }}">
                                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                    <span>
                                                        <strong>Question {{ $loop->iteration }}</strong>
                                                        @if($questionResult->question)
                                                            - {{ Str::limit($questionResult->question->question_text, 60) }}
                                                        @endif
                                                    </span>
                                                    <span class="badge bg-{{ $questionResult->is_correct ? 'success' : 'danger' }}">
                                                        {{ $questionResult->is_correct ? 'Correct' : 'Incorrect' }}
                                                    </span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $questionResult->id }}" 
                                             class="accordion-collapse collapse" 
                                             data-bs-parent="#questionsAccordion">
                                            <div class="accordion-body">
                                                @if($questionResult->question)
                                                    <div class="mb-3">
                                                        <strong>Question:</strong>
                                                        <p>{{ $questionResult->question->question_text }}</p>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <strong>Student Answer:</strong>
                                                            <div class="p-2 rounded bg-{{ $questionResult->is_correct ? 'success' : 'danger' }} bg-opacity-10">
                                                                {{ $questionResult->student_answer }}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Correct Answer:</strong>
                                                            <div class="p-2 rounded bg-success bg-opacity-10">
                                                                {{ $questionResult->correct_answer }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    @if($questionResult->question->explanation)
                                                        <div class="mt-3">
                                                            <strong>Explanation:</strong>
                                                            <p class="text-muted">{{ $questionResult->question->explanation }}</p>
                                                        </div>
                                                    @endif
                                                    
                                                    <div class="mt-3">
                                                        <small class="text-muted">
                                                            Time spent: {{ $questionResult->formatted_time_spent }} | 
                                                            Points: {{ $questionResult->points_earned }}/{{ $questionResult->points_possible }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Student Feedback -->
                    @if($result->student_feedback)
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-star me-2"></i>Student Feedback</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2 text-center">
                                    <div class="h3 text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $result->student_rating ? '' : '-o' }}"></i>
                                        @endfor
                                    </div>
                                    <div>{{ $result->student_rating }}/5 Stars</div>
                                </div>
                                <div class="col-md-10">
                                    <blockquote class="blockquote">
                                        <p>"{{ $result->student_feedback }}"</p>
                                        <footer class="blockquote-footer">
                                            Submitted on {{ $result->student_feedback_at->format('M j, Y g:i A') }}
                                        </footer>
                                    </blockquote>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Grading Panel -->
                <div class="col-md-4">
                    <!-- Current Status -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Current Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $result->status_color }} ms-2">
                                    {{ ucfirst(str_replace('_', ' ', $result->status)) }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <strong>Passing:</strong>
                                <span class="badge bg-{{ $result->is_passing ? 'success' : 'danger' }} ms-2">
                                    {{ $result->is_passing ? 'Yes' : 'No' }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <strong>Certificate:</strong>
                                @if($result->certificate_generated)
                                    <span class="badge bg-success ms-2">Generated</span>
                                    <br><small class="text-muted">{{ $result->certificate_number }}</small>
                                @else
                                    <span class="badge bg-secondary ms-2">Not Generated</span>
                                @endif
                            </div>
                            <div class="mb-3">
                                <strong>Exam Duration:</strong>
                                <span class="ms-2">{{ $result->formatted_exam_duration }}</span>
                            </div>
                            <div class="mb-0">
                                <strong>Completed:</strong>
                                <span class="ms-2">{{ $result->exam_completed_at->format('M j, Y g:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Grading Form -->
                    @if($result->is_grading_period_active || Auth::user()->hasRole('super-admin'))
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Grade Exam</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.final-exam-grading.update', $result->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="instructor_notes" class="form-label">Instructor Notes</label>
                                    <textarea name="instructor_notes" id="instructor_notes" rows="4" 
                                              class="form-control @error('instructor_notes') is-invalid @enderror"
                                              placeholder="Add notes about this student's performance...">{{ old('instructor_notes', $result->instructor_notes) }}</textarea>
                                    @error('instructor_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="override_score" class="form-label">Override Overall Score (Optional)</label>
                                    <div class="input-group">
                                        <input type="number" name="override_score" id="override_score" 
                                               class="form-control @error('override_score') is-invalid @enderror"
                                               min="0" max="100" step="0.1" 
                                               value="{{ old('override_score') }}"
                                               placeholder="Leave blank to keep calculated score">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    @error('override_score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Current calculated score: {{ number_format($result->overall_score, 1) }}%
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="override_status" class="form-label">Override Status (Optional)</label>
                                    <select name="override_status" id="override_status" class="form-select">
                                        <option value="">Keep current status</option>
                                        <option value="passed" {{ old('override_status') === 'passed' ? 'selected' : '' }}>Passed</option>
                                        <option value="failed" {{ old('override_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                                        <option value="under_review" {{ old('override_status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="generate_certificate" 
                                               id="generate_certificate" value="1" 
                                               {{ old('generate_certificate') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="generate_certificate">
                                            Generate Certificate (if passing)
                                        </label>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-warning text-dark">
                                        <i class="fas fa-save me-1"></i>Complete Grading
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Previous Grading Info -->
                    @if($result->grading_completed)
                    <div class="card mt-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Grading History</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>Graded by:</strong> {{ $result->gradedBy->first_name ?? 'Admin' }}
                            </div>
                            <div class="mb-2">
                                <strong>Graded on:</strong> {{ $result->graded_at->format('M j, Y g:i A') }}
                            </div>
                            @if($result->instructor_notes)
                                <div class="mb-0">
                                    <strong>Notes:</strong>
                                    <p class="text-muted small">{{ $result->instructor_notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-calculate grade letter when override score changes
document.getElementById('override_score').addEventListener('input', function() {
    const score = parseFloat(this.value);
    if (!isNaN(score)) {
        let grade = 'F';
        if (score >= 90) grade = 'A';
        else if (score >= 80) grade = 'B';
        else if (score >= 70) grade = 'C';
        else if (score >= 60) grade = 'D';
        
        // Update form text to show new grade
        const formText = this.parentNode.nextElementSibling.nextElementSibling;
        formText.innerHTML = `Current calculated score: {{ number_format($result->overall_score, 1) }}% | Override grade: ${grade}`;
    }
});

// Auto-check certificate generation for passing overrides
document.getElementById('override_status').addEventListener('change', function() {
    const certificateCheckbox = document.getElementById('generate_certificate');
    if (this.value === 'passed') {
        certificateCheckbox.checked = true;
    }
});
</script>
@endsection