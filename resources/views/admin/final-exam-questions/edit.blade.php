@extends('layouts.app')

@section('title', 'Edit Final Exam Question')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Edit Final Exam Question #{{ $question->id }}
                        </h4>
                        <a href="{{ route('admin.final-exam-questions.index', ['course_id' => $question->course_id]) }}" class="btn btn-dark btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to Questions
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.final-exam-questions.update', $question->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
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
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="question_type" class="form-label">Question Type <span class="text-danger">*</span></label>
                                    <select name="question_type" id="question_type" class="form-select @error('question_type') is-invalid @enderror" required onchange="toggleQuestionType()">
                                        <option value="multiple_choice" {{ old('question_type', $question->question_type) === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                        <option value="true_false" {{ old('question_type', $question->question_type) === 'true_false' ? 'selected' : '' }}>True/False</option>
                                    </select>
                                    @error('question_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
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
                                      placeholder="Enter the question text..." required>{{ old('question_text', $question->question_text) }}</textarea>
                            @error('question_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Multiple Choice Options -->
                        <div id="multiple_choice_options">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="option_a" class="form-label">Option A <span class="text-danger">*</span></label>
                                        <input type="text" name="option_a" id="option_a" 
                                               class="form-control @error('option_a') is-invalid @enderror" 
                                               value="{{ old('option_a', $options['A'] ?? '') }}" placeholder="Enter option A">
                                        @error('option_a')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="option_b" class="form-label">Option B <span class="text-danger">*</span></label>
                                        <input type="text" name="option_b" id="option_b" 
                                               class="form-control @error('option_b') is-invalid @enderror" 
                                               value="{{ old('option_b', $options['B'] ?? '') }}" placeholder="Enter option B">
                                        @error('option_b')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="option_c" class="form-label">Option C</label>
                                        <input type="text" name="option_c" id="option_c" 
                                               class="form-control @error('option_c') is-invalid @enderror" 
                                               value="{{ old('option_c', $options['C'] ?? '') }}" placeholder="Enter option C (optional)">
                                        @error('option_c')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="option_d" class="form-label">Option D</label>
                                        <input type="text" name="option_d" id="option_d" 
                                               class="form-control @error('option_d') is-invalid @enderror" 
                                               value="{{ old('option_d', $options['D'] ?? '') }}" placeholder="Enter option D (optional)">
                                        @error('option_d')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="correct_answer" class="form-label">Correct Answer <span class="text-danger">*</span></label>
                                    <select name="correct_answer" id="correct_answer" class="form-select @error('correct_answer') is-invalid @enderror" required>
                                        <option value="">Select correct answer</option>
                                        <option value="A" {{ old('correct_answer', $question->correct_answer) === 'A' ? 'selected' : '' }}>A</option>
                                        <option value="B" {{ old('correct_answer', $question->correct_answer) === 'B' ? 'selected' : '' }}>B</option>
                                        <option value="C" {{ old('correct_answer', $question->correct_answer) === 'C' ? 'selected' : '' }}>C</option>
                                        <option value="D" {{ old('correct_answer', $question->correct_answer) === 'D' ? 'selected' : '' }}>D</option>
                                    </select>
                                    @error('correct_answer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="points" class="form-label">Points</label>
                                    <input type="number" name="points" id="points" 
                                           class="form-control @error('points') is-invalid @enderror" 
                                           value="{{ old('points', $question->points ?? 1) }}" min="1" max="10">
                                    @error('points')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="explanation" class="form-label">Explanation (Optional)</label>
                            <textarea name="explanation" id="explanation" rows="3" 
                                      class="form-control @error('explanation') is-invalid @enderror" 
                                      placeholder="Provide an explanation for the correct answer (optional)">{{ old('explanation', $question->explanation) }}</textarea>
                            @error('explanation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.final-exam-questions.index', ['course_id' => $question->course_id]) }}" class="btn btn-secondary">
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
function toggleQuestionType() {
    const questionType = document.getElementById('question_type').value;
    const multipleChoiceOptions = document.getElementById('multiple_choice_options');
    const correctAnswerSelect = document.getElementById('correct_answer');
    const currentCorrectAnswer = correctAnswerSelect.value;
    
    if (questionType === 'true_false') {
        multipleChoiceOptions.style.display = 'none';
        
        // Update correct answer options for True/False
        correctAnswerSelect.innerHTML = `
            <option value="">Select correct answer</option>
            <option value="A" ${currentCorrectAnswer === 'A' ? 'selected' : ''}>True</option>
            <option value="B" ${currentCorrectAnswer === 'B' ? 'selected' : ''}>False</option>
        `;
        
        // Remove required attribute from options
        document.getElementById('option_a').removeAttribute('required');
        document.getElementById('option_b').removeAttribute('required');
        
    } else {
        multipleChoiceOptions.style.display = 'block';
        
        // Update correct answer options for Multiple Choice
        correctAnswerSelect.innerHTML = `
            <option value="">Select correct answer</option>
            <option value="A" ${currentCorrectAnswer === 'A' ? 'selected' : ''}>A</option>
            <option value="B" ${currentCorrectAnswer === 'B' ? 'selected' : ''}>B</option>
            <option value="C" ${currentCorrectAnswer === 'C' ? 'selected' : ''}>C</option>
            <option value="D" ${currentCorrectAnswer === 'D' ? 'selected' : ''}>D</option>
        `;
        
        // Add required attribute to options A and B
        document.getElementById('option_a').setAttribute('required', 'required');
        document.getElementById('option_b').setAttribute('required', 'required');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleQuestionType();
});
</script>
@endsection