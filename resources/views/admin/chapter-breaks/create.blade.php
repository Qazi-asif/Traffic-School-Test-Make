@extends('layouts.app')

@section('title', 'Add Chapter Break - ' . $course->title)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-plus-circle me-2"></i>Add Chapter Break</h2>
                    <p class="text-muted mb-0">
                        <strong>{{ $course->title }}</strong> 
                        <span class="badge bg-secondary ms-2">{{ ucfirst(str_replace('-', ' ', $courseType)) }}</span>
                    </p>
                </div>
                <a href="{{ route('admin.chapter-breaks.index', [$courseType, $courseId]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Breaks
                </a>
            </div>

            <!-- Create Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-pause-circle me-2"></i>Break Configuration</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.chapter-breaks.store', [$courseType, $courseId]) }}" method="POST">
                        @csrf

                        <!-- Chapter Selection -->
                        <div class="mb-4">
                            <label for="after_chapter_id" class="form-label">
                                <i class="fas fa-list me-1"></i>Add Break After Chapter <span class="text-danger">*</span>
                            </label>
                            <select name="after_chapter_id" id="after_chapter_id" 
                                    class="form-select @error('after_chapter_id') is-invalid @enderror" required>
                                <option value="">Select a chapter...</option>
                                @foreach($chapters as $chapter)
                                    <option value="{{ $chapter->id }}" {{ old('after_chapter_id') == $chapter->id ? 'selected' : '' }}>
                                        Chapter {{ $chapter->order_index }}: {{ $chapter->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('after_chapter_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Students will see the break screen after completing this chapter.
                            </div>
                        </div>

                        <!-- Break Title -->
                        <div class="mb-4">
                            <label for="break_title" class="form-label">
                                <i class="fas fa-heading me-1"></i>Break Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="break_title" id="break_title" 
                                   class="form-control @error('break_title') is-invalid @enderror"
                                   value="{{ old('break_title', 'Study Break') }}" 
                                   placeholder="e.g., Study Break, Rest Time, Reflection Break" required>
                            @error('break_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Break Message -->
                        <div class="mb-4">
                            <label for="break_message" class="form-label">
                                <i class="fas fa-comment me-1"></i>Break Message
                            </label>
                            <textarea name="break_message" id="break_message" rows="3"
                                      class="form-control @error('break_message') is-invalid @enderror"
                                      placeholder="Optional message to display during the break...">{{ old('break_message') }}</textarea>
                            @error('break_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                This message will be shown to students during their break.
                            </div>
                        </div>

                        <!-- Break Duration -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-clock me-1"></i>Break Duration <span class="text-danger">*</span>
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="number" name="break_duration_hours" id="break_duration_hours"
                                               class="form-control @error('break_duration_hours') is-invalid @enderror"
                                               value="{{ old('break_duration_hours', 1) }}" 
                                               min="0" max="24" placeholder="0">
                                        <span class="input-group-text">Hours</span>
                                    </div>
                                    @error('break_duration_hours')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="number" name="break_duration_minutes" id="break_duration_minutes"
                                               class="form-control @error('break_duration_minutes') is-invalid @enderror"
                                               value="{{ old('break_duration_minutes', 0) }}" 
                                               min="0" max="59" placeholder="0">
                                        <span class="input-group-text">Minutes</span>
                                    </div>
                                    @error('break_duration_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Set the minimum break duration. Students must wait this long before continuing.
                            </div>
                        </div>

                        <!-- Break Options -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-cog me-1"></i>Break Options
                            </label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_mandatory" id="is_mandatory" 
                                       value="1" {{ old('is_mandatory', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_mandatory">
                                    <strong>Mandatory Break</strong>
                                </label>
                                <div class="form-text">
                                    <i class="fas fa-lock me-1"></i>
                                    Students cannot skip this break and must wait for the full duration.
                                </div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                       value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong>
                                </label>
                                <div class="form-text">
                                    <i class="fas fa-play me-1"></i>
                                    Enable this break immediately after creation.
                                </div>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="mb-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-eye me-1"></i>Break Preview</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div id="preview-content">
                                        <h4 id="preview-title">Study Break</h4>
                                        <p id="preview-message" class="text-muted">Take a moment to rest and reflect on what you've learned.</p>
                                        <div class="badge bg-primary fs-6" id="preview-duration">1h 0m</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.chapter-breaks.index', [$courseType, $courseId]) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Chapter Break
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
// Update preview in real-time
function updatePreview() {
    const title = document.getElementById('break_title').value || 'Study Break';
    const message = document.getElementById('break_message').value || 'Take a moment to rest and reflect on what you\'ve learned.';
    const hours = parseInt(document.getElementById('break_duration_hours').value) || 0;
    const minutes = parseInt(document.getElementById('break_duration_minutes').value) || 0;
    
    document.getElementById('preview-title').textContent = title;
    document.getElementById('preview-message').textContent = message;
    
    let durationText = '';
    if (hours > 0 && minutes > 0) {
        durationText = `${hours}h ${minutes}m`;
    } else if (hours > 0) {
        durationText = `${hours}h`;
    } else if (minutes > 0) {
        durationText = `${minutes}m`;
    } else {
        durationText = '0m';
    }
    
    document.getElementById('preview-duration').textContent = durationText;
}

// Add event listeners
document.getElementById('break_title').addEventListener('input', updatePreview);
document.getElementById('break_message').addEventListener('input', updatePreview);
document.getElementById('break_duration_hours').addEventListener('input', updatePreview);
document.getElementById('break_duration_minutes').addEventListener('input', updatePreview);

// Initialize preview
updatePreview();

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const hours = parseInt(document.getElementById('break_duration_hours').value) || 0;
    const minutes = parseInt(document.getElementById('break_duration_minutes').value) || 0;
    
    if (hours === 0 && minutes === 0) {
        e.preventDefault();
        alert('Please set a break duration of at least 1 minute.');
        return false;
    }
});
</script>
@endsection