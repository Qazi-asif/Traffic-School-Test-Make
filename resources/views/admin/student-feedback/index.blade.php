@extends('layouts.app')

@section('title', 'Student Feedback Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-comments me-2"></i>Student Feedback Management</h2>
                    <p class="text-muted mb-0">Review student quiz performance and provide feedback before final exam</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/admin/free-response-quiz" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>Manage Free Response Quiz
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">{{ $stats['pending_feedback'] }}</h5>
                                    <p class="card-text">Pending Feedback</p>
                                </div>
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">{{ $stats['completed_feedback'] }}</h5>
                                    <p class="card-text">Total Feedback Given</p>
                                </div>
                                <i class="fas fa-comments fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">{{ $stats['approved_students'] }}</h5>
                                    <p class="card-text">Approved for Final Exam</p>
                                </div>
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">{{ $stats['needs_improvement'] }}</h5>
                                    <p class="card-text">Needs Improvement</p>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.student-feedback.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Feedback Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Students</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Feedback</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="needs_improvement" {{ request('status') === 'needs_improvement' ? 'selected' : '' }}>Needs Improvement</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="course_id" class="form-label">Course</label>
                            <select name="course_id" id="course_id" class="form-select">
                                <option value="">All Courses</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->title }} ({{ $course->state_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Student</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Search by name or email..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                                <a href="{{ route('admin.student-feedback.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Students List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Students Requiring Feedback</h5>
                </div>
                <div class="card-body">
                    @if($students->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>Progress</th>
                                        <th>Quiz Average</th>
                                        <th>Enrolled</th>
                                        <th>Feedback Status</th>
                                        <th>Final Exam</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $student)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $student->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $student->course_title }}</strong>
                                                    <br>
                                                    <span class="badge bg-secondary">{{ $student->course_state_code }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" style="width: {{ $student->progress_percentage ?? 0 }}%">
                                                        {{ round($student->progress_percentage ?? 0) }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ ($student->quiz_average ?? 0) >= 80 ? 'success' : 'warning' }} fs-6">
                                                    {{ round($student->quiz_average ?? 0) }}%
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ date('M j, Y', strtotime($student->enrolled_at)) }}</small>
                                            </td>
                                            <td>
                                                @if($student->feedback_status)
                                                    <span class="badge bg-{{ $student->feedback_status === 'approved' ? 'success' : ($student->feedback_status === 'needs_improvement' ? 'warning' : 'info') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $student->feedback_status)) }}
                                                    </span>
                                                    @if($student->feedback_given_at)
                                                        <br><small class="text-muted">{{ date('M j, g:i A', strtotime($student->feedback_given_at)) }}</small>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($student->can_take_final_exam)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-unlock me-1"></i>Allowed
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-lock me-1"></i>Blocked
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.student-feedback.show', $student->enrollment_id) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye me-1"></i>Review
                                                    </a>
                                                    @if($student->feedback_status)
                                                        <a href="{{ route('admin.student-feedback.show', $student->enrollment_id) }}" 
                                                           class="btn btn-sm btn-outline-secondary">
                                                            <i class="fas fa-edit me-1"></i>Edit
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <p class="text-muted mb-0">
                                    Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} of {{ $students->total() }} students
                                </p>
                            </div>
                            <div>
                                {{ $students->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>No Students Found</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['status', 'course_id', 'search']))
                                    No students match your current filters. Try adjusting your search criteria.
                                @else
                                    No students have completed enough coursework to require feedback yet.
                                @endif
                            </p>
                            @if(request()->hasAny(['status', 'course_id', 'search']))
                                <a href="{{ route('admin.student-feedback.index') }}" class="btn btn-primary">
                                    <i class="fas fa-times me-1"></i>Clear Filters
                                </a>
                            @endif
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
// Auto-submit form when filters change
document.getElementById('status').addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('course_id').addEventListener('change', function() {
    this.form.submit();
});

// Add search delay
let searchTimeout;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (this.value.length >= 3 || this.value.length === 0) {
            this.form.submit();
        }
    }, 500);
});
</script>
@endsection