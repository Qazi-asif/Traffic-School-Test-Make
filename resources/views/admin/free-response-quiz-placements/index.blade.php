@extends('layouts.app')

@section('title', 'Free Response Quiz Placements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>Free Response Quiz Placements
                        </h4>
                        <a href="{{ route('admin.free-response-quiz-placements.create', ['course_id' => $courseId ?? '']) }}" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i>Add Placement
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Course Selection -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="course-select" class="form-label">Select Course:</label>
                            <select id="course-select" class="form-select" onchange="filterByCourse()">
                                <option value="">All Courses</option>
                                @if(isset($courses))
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ ($courseId ?? '') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }} ({{ $course->state_code }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    @if(isset($placements) && $placements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Quiz Title</th>
                                        <th>After Chapter</th>
                                        <th>Order</th>
                                        <th>Mandatory</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($placements as $placement)
                                        <tr>
                                            <td>
                                                <strong>{{ $placement->quiz_title }}</strong>
                                                @if($placement->quiz_description)
                                                    <br><small class="text-muted">{{ Str::limit($placement->quiz_description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($placement->after_chapter_id)
                                                    @if(isset($chapters))
                                                        @php
                                                            $chapter = $chapters->firstWhere('id', $placement->after_chapter_id);
                                                        @endphp
                                                        {{ $chapter ? $chapter->title : 'Chapter #' . $placement->after_chapter_id }}
                                                    @else
                                                        Chapter #{{ $placement->after_chapter_id }}
                                                    @endif
                                                @else
                                                    <em>End of Course</em>
                                                @endif
                                            </td>
                                            <td>{{ $placement->order_index }}</td>
                                            <td>
                                                <span class="badge bg-{{ $placement->is_mandatory ? 'warning' : 'secondary' }}">
                                                    {{ $placement->is_mandatory ? 'Mandatory' : 'Optional' }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-{{ $placement->is_active ? 'success' : 'secondary' }}" 
                                                        onclick="toggleActive({{ $placement->id }})"
                                                        title="{{ $placement->is_active ? 'Active' : 'Inactive' }}">
                                                    <i class="fas fa-{{ $placement->is_active ? 'check' : 'times' }}"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.free-response-quiz-placements.edit', $placement->id) }}" 
                                                       class="btn btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.free-response-quiz-placements.destroy', $placement->id) }}" 
                                                          method="POST" class="d-inline" 
                                                          onsubmit="return confirm('Are you sure you want to delete this placement?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                            <h5>No Quiz Placements Found</h5>
                            <p class="text-muted">Create your first quiz placement to add free response quizzes between chapters.</p>
                            <a href="{{ route('admin.free-response-quiz-placements.create', ['course_id' => $courseId ?? '']) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Add First Placement
                            </a>
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
function filterByCourse() {
    const courseId = document.getElementById('course-select').value;
    const url = new URL(window.location);
    
    if (courseId) {
        url.searchParams.set('course_id', courseId);
    } else {
        url.searchParams.delete('course_id');
    }
    
    window.location.href = url.toString();
}

function toggleActive(id) {
    fetch(`/admin/free-response-quiz-placements/${id}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to update status'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status');
    });
}
</script>
@endsection