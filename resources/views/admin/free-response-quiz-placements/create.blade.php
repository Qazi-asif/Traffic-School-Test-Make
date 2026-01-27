@extends('layouts.app')

@section('title', 'Add Quiz Placement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-plus me-2"></i>Add Quiz Placement
                        </h4>
                        <a href="{{ route('admin.free-response-quiz-placements.index', ['course_id' => $courseId]) }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to Placements
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.free-response-quiz-placements.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="course_id" class="form-label">Course <span class="text-danger">*</span></label>
                                    <select name="course_id" id="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                        <option value="">Select Course</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ $courseId == $course->id ? 'selected' : '' }}>
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
                                        <option value="">End of Course (Before Final Exam)</option>
                                        @foreach($chapters as $chapter)
                                            <option value="{{ $chapter->id }}">
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
                                   value="{{ old('quiz_title', 'Free Response Questions') }}" required>
                            @error('quiz_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="quiz_description" class="form-label">Quiz Description</label>
                            <textarea name="quiz_description" id="quiz_description" rows="3" 
                                      class="form-control @error('quiz_description') is-invalid @enderror" 
                                      placeholder="Optional description shown to students">{{ old('quiz_description') }}</textarea>
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
                                           value="{{ old('order_index', 1) }}" min="1" required>
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
                                               value="1" {{ old('is_mandatory', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_mandatory">
                                            <strong>Mandatory Quiz</strong>
                                        </label>
                                        <div class="form-text">Students must complete mandatory quizzes to proceed</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Random Selection Settings -->
                        <div class="card mb-3 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-random me-2"></i>Random Question Selection
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="use_random_selection" id="use_random_selection" 
                                           value="1" {{ old('use_random_selection', false) ? 'checked' : '' }} onchange="toggleRandomSettings()">
                                    <label class="form-check-label" for="use_random_selection">
                                        <strong>Enable Random Selection</strong>
                                    </label>
                                    <div class="form-text">Randomly select questions from a pool for each student</div>
                                    
                                    <!-- Debug button -->
        
                                </div>

                                <div id="random-settings" style="display: block;"><!-- Changed to block for debugging -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="questions_to_select" class="form-label">
                                                    Questions to Select <span class="text-danger">*</span>
                                                </label>
                                                <input type="number" name="questions_to_select" id="questions_to_select" 
                                                       class="form-control @error('questions_to_select') is-invalid @enderror" 
                                                       value="{{ old('questions_to_select', 10) }}" min="1" max="100">
                                                @error('questions_to_select')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">Number of questions each student will receive</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="total_questions_in_pool" class="form-label">
                                                    Total Pool Size (Reference)
                                                </label>
                                                <input type="number" name="total_questions_in_pool" id="total_questions_in_pool" 
                                                       class="form-control @error('total_questions_in_pool') is-invalid @enderror" 
                                                       value="{{ old('total_questions_in_pool', 50) }}" min="1" readonly>
                                                @error('total_questions_in_pool')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">Total questions available (auto-calculated)</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning">
                                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Example: Florida 12 Hour</h6>
                                        <ul class="mb-0">
                                            <li><strong>Pool Size:</strong> 50 questions</li>
                                            <li><strong>Questions to Select:</strong> 10 questions</li>
                                            <li><strong>Result:</strong> Each student gets 10 random questions from the pool of 50</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grading Settings -->
                        <div class="card mb-3 border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-clock me-2"></i>Grading & Review Settings
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="enforce_24hour_grading" id="enforce_24hour_grading" 
                                           value="1" {{ old('enforce_24hour_grading', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enforce_24hour_grading">
                                        <strong>Enforce 24-Hour Grading Period</strong>
                                    </label>
                                    <div class="form-text">Show "under review" message and delay results for 24 hours</div>
                                </div>

                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>24-Hour Grading Period:</h6>
                                    <ul class="mb-0">
                                        <li><strong>Enabled:</strong> Students see "under instructor review" message for 24 hours</li>
                                        <li><strong>Disabled:</strong> Students see results immediately after submission</li>
                                        <li><strong>Use Case:</strong> Enable for courses requiring manual review, disable for automated grading</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>How Quiz Placements Work:</h6>
                            <ul class="mb-0">
                                <li><strong>After Chapter:</strong> Quiz appears after the selected chapter</li>
                                <li><strong>End of Course:</strong> Quiz appears before the final exam</li>
                                <li><strong>Order Index:</strong> Controls sequence when multiple quizzes exist</li>
                                <li><strong>Mandatory:</strong> Students must complete to unlock next content</li>
                                <li><strong>Random Selection:</strong> Each student gets different questions from the pool</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.free-response-quiz-placements.index', ['course_id' => $courseId]) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Create Placement
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
// Toggle random selection settings
function toggleRandomSettings() {
    console.log('toggleRandomSettings called');
    
    const checkbox = document.getElementById('use_random_selection');
    const settings = document.getElementById('random-settings');
    const questionsInput = document.getElementById('questions_to_select');
    
    console.log('Checkbox:', checkbox);
    console.log('Settings div:', settings);
    console.log('Checkbox checked:', checkbox ? checkbox.checked : 'not found');
    
    if (checkbox && settings) {
        if (checkbox.checked) {
            console.log('Showing random settings');
            settings.style.display = 'block';
            if (questionsInput) {
                questionsInput.required = true;
            }
        } else {
            console.log('Hiding random settings');
            settings.style.display = 'none';
            if (questionsInput) {
                questionsInput.required = false;
            }
        }
    } else {
        console.error('Elements not found:', {
            checkbox: !!checkbox,
            settings: !!settings
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    toggleRandomSettings();
    
    // Also add event listener directly to be safe
    const checkbox = document.getElementById('use_random_selection');
    if (checkbox) {
        checkbox.addEventListener('change', toggleRandomSettings);
        console.log('Event listener added to checkbox');
    }
});

// Update chapters when course changes
document.getElementById('course_id').addEventListener('change', function() {
    const courseId = this.value;
    const chapterSelect = document.getElementById('after_chapter_id');
    
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