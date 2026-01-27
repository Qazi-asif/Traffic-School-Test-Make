@extends('layouts.app')

@section('title', 'Edit Free Response Question')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Edit Free Response Question #{{ $question->id }}
                        </h4>
                        <a href="{{ route('admin.free-response-quiz.index', ['course_id' => $question->course_id]) }}" class="btn btn-dark btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to Questions
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.free-response-quiz.update', $question->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="course_id" class="form-label">Course <span class="text-danger">*</span></label>
                                    <select name="course_id" id="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ (old('course_id', $question->course_id) == $course->id) ? 'selected' : '' }}>
                                                {{ $course->title }} ({{ $course->state_code }})
                                                @if(isset($course->table_type))
                                                    - {{ $course->table_type === 'florida_courses' ? 'Florida' : 'Regular' }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('course_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="placement_id" class="form-label">Quiz Placement</label>
                                    <select name="placement_id" id="placement_id" class="form-select @error('placement_id') is-invalid @enderror">
                                        <option value="">Default (End of Course)</option>
                                        <!-- Placements will be loaded via JavaScript -->
                                    </select>
                                    @error('placement_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Select which quiz placement this question belongs to</div>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="points" class="form-label">Points</label>
                                    <input type="number" name="points" id="points" 
                                           class="form-control @error('points') is-invalid @enderror" 
                                           value="{{ old('points', $question->points ?? 5) }}" min="1" max="20">
                                    @error('points')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Points (1-20)</div>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="order_index" class="form-label">Order <span class="text-danger">*</span></label>
                                    <input type="number" name="order_index" id="order_index" class="form-control @error('order_index') is-invalid @enderror" 
                                           value="{{ old('order_index', $question->order_index) }}" min="1" required>
                                    @error('order_index')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="question_text" class="form-label">Question Text <span class="text-danger">*</span></label>
                            <textarea name="question_text" id="question_text" rows="4" 
                                      class="form-control @error('question_text') is-invalid @enderror" 
                                      placeholder="Enter the question that students will answer with a written response..." required>{{ old('question_text', $question->question_text) }}</textarea>
                            @error('question_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> Students will provide a written answer (max 50 words) to this question.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sample_answer" class="form-label">Sample Answer (Optional)</label>
                            <textarea name="sample_answer" id="sample_answer" rows="4" 
                                      class="form-control @error('sample_answer') is-invalid @enderror" 
                                      placeholder="Provide a sample answer to help with grading...">{{ old('sample_answer', $question->sample_answer) }}</textarea>
                            @error('sample_answer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-lightbulb"></i> This helps you and other admins understand what a good answer looks like.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="grading_rubric" class="form-label">Grading Rubric (Optional)</label>
                            <textarea name="grading_rubric" id="grading_rubric" rows="4" 
                                      class="form-control @error('grading_rubric') is-invalid @enderror" 
                                      placeholder="Define criteria for grading this question...">{{ old('grading_rubric', $question->grading_rubric) }}</textarea>
                            @error('grading_rubric')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror>
                            <div class="form-text">
                                <i class="fas fa-clipboard-list"></i> Specify what makes a good answer (key points, criteria, etc.)
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                       value="1" {{ old('is_active', $question->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active Question</strong>
                                </label>
                                <div class="form-text">
                                    <i class="fas fa-toggle-on"></i> Only active questions will be shown to students.
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Student Experience:</h6>
                            <ul class="mb-0">
                                <li><strong>50-word limit:</strong> Students can write up to 50 words</li>
                                <li><strong>No copy/paste:</strong> Copy and paste functionality will be disabled</li>
                                <li><strong>Manual grading:</strong> Admin will review and grade each response</li>
                                <li><strong>Feedback:</strong> Admin can provide feedback on student answers</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.free-response-quiz.index', ['course_id' => $question->course_id]) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-warning text-dark">
                                <i class="fas fa-save me-1"></i>Update Question
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
// Character counter for question text
document.getElementById('question_text').addEventListener('input', function() {
    const maxLength = 2000;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    // Create or update character counter
    let counter = document.getElementById('question_text_counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'question_text_counter';
        counter.className = 'form-text text-end';
        this.parentNode.appendChild(counter);
    }
    
    counter.innerHTML = `<small class="${remaining < 100 ? 'text-warning' : 'text-muted'}">${currentLength}/${maxLength} characters</small>`;
});

// Character counter for sample answer
document.getElementById('sample_answer').addEventListener('input', function() {
    const maxLength = 1000;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    let counter = document.getElementById('sample_answer_counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'sample_answer_counter';
        counter.className = 'form-text text-end';
        this.parentNode.appendChild(counter);
    }
    
    counter.innerHTML = `<small class="${remaining < 50 ? 'text-warning' : 'text-muted'}">${currentLength}/${maxLength} characters</small>`;
});

// Character counter for grading rubric
document.getElementById('grading_rubric').addEventListener('input', function() {
    const maxLength = 2000;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    let counter = document.getElementById('grading_rubric_counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'grading_rubric_counter';
        counter.className = 'form-text text-end';
        this.parentNode.appendChild(counter);
    }
    
    counter.innerHTML = `<small class="${remaining < 100 ? 'text-warning' : 'text-muted'}">${currentLength}/${maxLength} characters</small>`;
});

// Initialize counters on page load
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('question_text').dispatchEvent(new Event('input'));
    document.getElementById('sample_answer').dispatchEvent(new Event('input'));
    document.getElementById('grading_rubric').dispatchEvent(new Event('input'));
});
</script>
@endsection