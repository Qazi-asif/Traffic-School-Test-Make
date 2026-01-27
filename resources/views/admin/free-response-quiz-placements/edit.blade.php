@extends('layouts.app')

@section('title', 'Edit Quiz Placement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Edit Quiz Placement #{{ $placement->id }}
                        </h4>
                        <a href="{{ route('admin.free-response-quiz-placements.index', ['course_id' => $placement->course_id]) }}" class="btn btn-dark btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to Placements
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.free-response-quiz-placements.update', $placement->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="course_id" class="form-label">Course <span class="text-danger">*</span></label>
                                    <select name="course_id" id="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ (old('course_id', $placement->course_id) == $course->id) ? 'selected' : '' }}>
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
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="after_chapter_id" class="form-label">After Chapter</label>
                                    <select name="after_chapter_id" id="after_chapter_id" class="form-select @error('after_chapter_id') is-invalid @enderror">
                                        <option value="" {{ !old('after_chapter_id', $placement->after_chapter_id) ? 'selected' : '' }}>
                                            End of Course (Before Final Exam)
                                        </option>
                                        @foreach($chapters as $chapter)
                                            <option value="{{ $chapter->id }}" {{ (old('after_chapter_id', $placement->after_chapter_id) == $chapter->id) ? 'selected' : '' }}>
                                                {{ $chapter->order_index }}. {{ $chapter->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('after_chapter_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Select a chapter to place the quiz after, or leave empty to place at the end</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="quiz_title" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                            <input type="text" name="quiz_title" id="quiz_title" 
                                   class="form-control @error('quiz_title') is-invalid @enderror" 
                                   value="{{ old('quiz_title', $placement->quiz_title) }}" required>
                            @error('quiz_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="quiz_description" class="form-label">Quiz Description</label>
                            <textarea name="quiz_description" id="quiz_description" rows="3" 
                                      class="form-control @error('quiz_description') is-invalid @enderror" 
                                      placeholder="Optional description shown to students">{{ old('quiz_description', $placement->quiz_description) }}</textarea>
                            @error('quiz_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order_index" class="form-label">Order Index <span class="text-danger">*</span></label>
                                    <input type="number" name="order_index" id="order_index" 
                                           class="form-control @error('order_index') is-invalid @enderror" 
                                           value="{{ old('order_index', $placement->order_index) }}" min="1" required>
                                    @error('order_index')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Controls the order when multiple placements exist</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" name="is_mandatory" id="is_mandatory" 
                                               value="1" {{ old('is_mandatory', $placement->is_mandatory) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_mandatory">
                                            <strong>Mandatory Quiz</strong>
                                        </label>
                                        <div class="form-text">Students must complete mandatory quizzes to proceed</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Current Status:</h6>
                            <ul class="mb-0">
                                <li><strong>Status:</strong> {{ $placement->is_active ? 'Active' : 'Inactive' }}</li>
                                <li><strong>Created:</strong> {{ $placement->created_at->format('M j, Y g:i A') }}</li>
                                <li><strong>Last Updated:</strong> {{ $placement->updated_at->format('M j, Y g:i A') }}</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.free-response-quiz-placements.index', ['course_id' => $placement->course_id]) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-warning text-dark">
                                <i class="fas fa-save me-1"></i>Update Placement
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
// Update chapters when course changes
document.getElementById('course_id').addEventListener('change', function() {
    const courseId = this.value;
    const chapterSelect = document.getElementById('after_chapter_id');
    const currentChapterId = {{ $placement->after_chapter_id ?? 'null' }};
    
    // Clear existing options except the first one
    chapterSelect.innerHTML = '<option value="">End of Course (Before Final Exam)</option>';
    
    if (courseId) {
        // Fetch chapters for the selected course
        fetch(`/api/courses/${courseId}/chapters`)
            .then(response => response.json())
            .then(chapters => {
                chapters.forEach(chapter => {
                    if (chapter.chapter_type === 'chapters') { // Only regular chapters
                        const option = document.createElement('option');
                        option.value = chapter.id;
                        option.textContent = `${chapter.order_index}. ${chapter.title}`;
                        
                        // Select the current chapter if it matches
                        if (chapter.id == currentChapterId) {
                            option.selected = true;
                        }
                        
                        chapterSelect.appendChild(option);
                    }
                });
            })
            .catch(error => {
                console.error('Error loading chapters:', error);
            });
    }
});
</script>
@endsection