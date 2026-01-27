@extends('layouts.app')

@section('title', 'Final Exam Grading')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-clipboard-check me-2"></i>Final Exam Grading</h2>
                    <p class="text-muted mb-0">Review and grade final exam results within 24-hour period</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
                        <i class="fas fa-tasks me-1"></i>Bulk Actions
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">{{ $stats['pending_grading'] }}</h5>
                                    <p class="card-text">Pending Grading</p>
                                </div>
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">{{ $stats['expired_grading'] }}</h5>
                                    <p class="card-text">Expired Grading</p>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">{{ $stats['completed_grading'] }}</h5>
                                    <p class="card-text">Completed Grading</p>
                                </div>
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">{{ $stats['total_results'] }}</h5>
                                    <p class="card-text">Total Results</p>
                                </div>
                                <i class="fas fa-graduation-cap fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.final-exam-grading.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Grading Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Results</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Grading</option>
                                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired Grading</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
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
                                <a href="{{ route('admin.final-exam-grading.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results List -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Final Exam Results</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">
                                Select All
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($results->count() > 0)
                        <form id="bulkForm">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" class="form-check-input" id="selectAllTable">
                                            </th>
                                            <th>Student</th>
                                            <th>Course</th>
                                            <th>Scores</th>
                                            <th>Status</th>
                                            <th>Grading Period</th>
                                            <th>Completed</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($results as $result)
                                            <tr class="{{ $result->is_grading_period_active ? 'table-warning' : '' }}">
                                                <td>
                                                    <input type="checkbox" class="form-check-input result-checkbox" 
                                                           name="result_ids[]" value="{{ $result->id }}">
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong>{{ $result->user->first_name }} {{ $result->user->last_name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $result->user->email }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong>{{ $result->course->title ?? 'Course' }}</strong>
                                                        <br>
                                                        <span class="badge bg-secondary">{{ $result->course->state_code ?? 'N/A' }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column gap-1">
                                                        <div>
                                                            <strong>Overall:</strong> 
                                                            <span class="badge bg-{{ $result->is_passing ? 'success' : 'danger' }}">
                                                                {{ number_format($result->overall_score, 1) }}% ({{ $result->grade_letter }})
                                                            </span>
                                                        </div>
                                                        <small class="text-muted">
                                                            Quiz: {{ number_format($result->quiz_average, 1) }}% | 
                                                            @if($result->free_response_score)
                                                                Free: {{ number_format($result->free_response_score, 1) }}% | 
                                                            @endif
                                                            Final: {{ number_format($result->final_exam_score, 1) }}%
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $result->status_color }}">
                                                        {{ ucfirst(str_replace('_', ' ', $result->status)) }}
                                                    </span>
                                                    @if($result->student_feedback)
                                                        <br><small class="text-info">
                                                            <i class="fas fa-star"></i> {{ $result->student_rating }}/5
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($result->grading_completed)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Completed
                                                        </span>
                                                        @if($result->graded_by)
                                                            <br><small class="text-muted">
                                                                by {{ $result->gradedBy->first_name ?? 'Admin' }}
                                                            </small>
                                                        @endif
                                                    @elseif($result->is_grading_period_active)
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock me-1"></i>{{ $result->remaining_grading_time }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>Expired
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $result->exam_completed_at->format('M j, Y') }}
                                                        <br>
                                                        {{ $result->exam_completed_at->format('g:i A') }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.final-exam-grading.show', $result->id) }}" 
                                                           class="btn btn-sm btn-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @if($result->is_grading_period_active || Auth::user()->hasRole('super-admin'))
                                                            <button type="button" class="btn btn-sm btn-success" 
                                                                    onclick="quickGrade({{ $result->id }}, 'passed')">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-warning" 
                                                                    onclick="quickGrade({{ $result->id }}, 'under_review')">
                                                                <i class="fas fa-exclamation"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <p class="text-muted mb-0">
                                    Showing {{ $results->firstItem() }} to {{ $results->lastItem() }} of {{ $results->total() }} results
                                </p>
                            </div>
                            <div>
                                {{ $results->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                            <h5>No Exam Results Found</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['status', 'course_id', 'search']))
                                    No results match your current filters.
                                @else
                                    No final exam results available for grading.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.final-exam-grading.bulk-update') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bulk_action" class="form-label">Action</label>
                        <select name="bulk_action" id="bulk_action" class="form-select" required>
                            <option value="">Select action...</option>
                            <option value="approve_all">Approve All (Pass & Generate Certificates)</option>
                            <option value="mark_review">Mark for Review</option>
                            <option value="complete_grading">Complete Grading (Keep Current Status)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bulk_notes" class="form-label">Notes (Optional)</label>
                        <textarea name="bulk_notes" id="bulk_notes" rows="3" class="form-control" 
                                  placeholder="Add notes for all selected results..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="selected-count">0</span> results selected for bulk action.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Action</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.result-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelectedCount();
});

document.getElementById('selectAllTable').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.result-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelectedCount();
});

// Update selected count
function updateSelectedCount() {
    const selected = document.querySelectorAll('.result-checkbox:checked').length;
    document.getElementById('selected-count').textContent = selected;
    
    // Update bulk form with selected IDs
    const selectedIds = Array.from(document.querySelectorAll('.result-checkbox:checked'))
        .map(cb => cb.value);
    
    // Remove existing hidden inputs
    const existingInputs = document.querySelectorAll('#bulkActionModal input[name="result_ids[]"]');
    existingInputs.forEach(input => input.remove());
    
    // Add new hidden inputs
    const form = document.querySelector('#bulkActionModal form');
    selectedIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'result_ids[]';
        input.value = id;
        form.appendChild(input);
    });
}

// Add event listeners to checkboxes
document.querySelectorAll('.result-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
});

// Quick grade function
function quickGrade(resultId, status) {
    if (!confirm(`Are you sure you want to mark this result as ${status.replace('_', ' ')}?`)) {
        return;
    }
    
    fetch(`/admin/final-exam-grading/${resultId}/quick-grade`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status: status
        })
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
        alert('Failed to update status');
    });
}

// Auto-submit form when filters change
document.getElementById('status').addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('course_id').addEventListener('change', function() {
    this.form.submit();
});
</script>
@endsection