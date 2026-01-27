@extends('layouts.app')

@section('title', 'Free Response Quiz Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Free Response Quiz Management
                        </h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.free-response-quiz.create', ['course_id' => $courseId]) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i>Add New Question
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <form method="GET" action="{{ route('admin.free-response-quiz.index') }}">
                                <div class="input-group">
                                    <select name="course_id" class="form-select" onchange="this.form.submit()">
                                        <option value="" {{ !$courseId ? 'selected' : '' }}>All Courses</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ $courseId == $course->id ? 'selected' : '' }}>
                                                {{ $course->title }} ({{ $course->state_code }}) 
                                                @if(isset($course->table_type))
                                                    - {{ $course->table_type === 'florida_courses' ? 'Florida' : 'Regular' }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="search" value="{{ $search }}">
                                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form method="GET" action="{{ route('admin.free-response-quiz.index') }}">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search questions..." value="{{ $search }}">
                                    <input type="hidden" name="course_id" value="{{ $courseId }}">
                                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form method="GET" action="{{ route('admin.free-response-quiz.index') }}">
                                <div class="input-group">
                                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 per page</option>
                                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 per page</option>
                                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 per page</option>
                                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 per page</option>
                                    </select>
                                    <input type="hidden" name="course_id" value="{{ $courseId }}">
                                    <input type="hidden" name="search" value="{{ $search }}">
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                @if($courseId)
                                    <strong>Course Questions:</strong> {{ $totalQuestions }} questions for selected course
                                    <span class="ms-3"><strong>Total in System:</strong> {{ $totalAllQuestions ?? 0 }} questions across all courses</span>
                                @else
                                    <strong>All Questions:</strong> {{ $totalAllQuestions ?? 0 }} questions across all courses
                                @endif
                                <span class="ms-3"><strong>Showing:</strong> {{ $questions->count() }} questions on this page</span>
                            </div>
                        </div>
                    </div>

                    <!-- Questions Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="4%">#</th>
                                    <th width="4%">Order</th>
                                    @if(!$courseId)
                                        <th width="15%">Course</th>
                                    @endif
                                    <th width="{{ !$courseId ? '35%' : '40%' }}">Question</th>
                                    <th width="10%">Points</th>
                                    <th width="8%">Status</th>
                                    <th width="12%">Sample Answer</th>
                                    <th width="12%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($questions as $question)
                                    <tr>
                                        <td>{{ $question->id }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $question->order_index }}</span>
                                        </td>
                                        @if(!$courseId)
                                            <td>
                                                <div class="small">
                                                    <strong>{{ $question->course_title ?? 'Unknown Course' }}</strong>
                                                    <br>
                                                    <span class="text-muted">{{ $question->course_state_code ?? 'N/A' }} - {{ $question->course_type ?? 'Unknown' }}</span>
                                                </div>
                                            </td>
                                        @endif
                                        <td>
                                            <div class="question-preview">
                                                {{ Str::limit($question->question_text, 120) }}
                                                @if(strlen($question->question_text) > 120)
                                                    <button class="btn btn-link btn-sm p-0 ms-1" 
                                                            onclick="showFullQuestion({{ $question->id }})"
                                                            title="View full question">
                                                        <i class="fas fa-expand-alt" style="font-size: 0.75rem;"></i>
                                                    </button>
                                                @endif
                                            </div>
                                            @if($question->grading_rubric)
                                                <div class="mt-1">
                                                    <small class="text-info">
                                                        <i class="fas fa-clipboard-list"></i> Has grading rubric
                                                    </small>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">{{ $question->points ?? 5 }}</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-{{ $question->is_active ? 'success' : 'secondary' }}" 
                                                    onclick="toggleActive({{ $question->id }})"
                                                    title="{{ $question->is_active ? 'Active' : 'Inactive' }}">
                                                <i class="fas fa-{{ $question->is_active ? 'check' : 'times' }}"></i>
                                                {{ $question->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </td>
                                        <td>
                                            @if($question->sample_answer)
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="showSampleAnswer({{ $question->id }})"
                                                        title="View sample answer">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            @else
                                                <span class="text-muted small">No sample</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.free-response-quiz.edit', $question->id) }}" 
                                                   class="btn btn-outline-primary btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="deleteQuestion({{ $question->id }})" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ !$courseId ? '8' : '7' }}" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-edit fa-2x mb-3"></i>
                                                <h5>No Questions Found</h5>
                                                <p>No free response questions found for this course.</p>
                                                <a href="{{ route('admin.free-response-quiz.create', ['course_id' => $courseId]) }}" 
                                                   class="btn btn-info">
                                                    <i class="fas fa-plus me-1"></i>Add First Question
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($questions->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Questions pagination">
                                <div class="pagination-wrapper">
                                    {{ $questions->appends(request()->query())->links('pagination::bootstrap-4') }}
                                </div>
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Full Question Modal -->
<div class="modal fade" id="fullQuestionModal" tabindex="-1" aria-labelledby="fullQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullQuestionModalLabel">Full Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="fullQuestionContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Sample Answer Modal -->
<div class="modal fade" id="sampleAnswerModal" tabindex="-1" aria-labelledby="sampleAnswerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sampleAnswerModalLabel">Sample Answer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="sampleAnswerContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Compact pagination styling */
.pagination-wrapper .pagination {
    margin-bottom: 0;
}

.pagination-wrapper .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.25;
}

/* Compact table styling */
.table td {
    vertical-align: middle;
    padding: 0.5rem;
}

.table th {
    padding: 0.75rem 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
}

/* Compact badges */
.badge {
    font-size: 0.75rem;
}

/* Compact buttons */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

/* Question preview styling */
.question-preview {
    font-size: 0.875rem;
    line-height: 1.4;
}

/* Compact form controls */
.form-select, .form-control {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

/* Compact alert */
.alert {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
}

/* Better spacing for action buttons */
.btn-group .btn {
    margin: 0;
}

/* Fix large icons in empty state */
.fa-2x {
    font-size: 1.5em !important;
}

/* Responsive table improvements */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.75rem;
    }
    
    .btn-sm {
        padding: 0.125rem 0.25rem;
        font-size: 0.625rem;
    }
    
    .pagination-wrapper .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
function deleteQuestion(id) {
    if (confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
        fetch(`/admin/free-response-quiz/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to delete question'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete question');
        });
    }
}

function toggleActive(id) {
    fetch(`/admin/free-response-quiz/${id}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to update question status'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update question status');
    });
}

function showFullQuestion(id) {
    // Find the question row and get the full text
    const row = document.querySelector(`tr:has(td:first-child:contains('${id}'))`);
    if (!row) return;
    
    const questionText = row.querySelector('.question-preview').textContent.trim();
    
    document.getElementById('fullQuestionContent').innerHTML = `
        <div class="mb-3">
            <strong>Question:</strong><br>
            <div class="mt-2 p-3 bg-light rounded">${questionText}</div>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('fullQuestionModal'), { backdrop: false });
    modal.show();
}

function showSampleAnswer(id) {
    // This would typically fetch the sample answer via AJAX
    // For now, we'll show a placeholder
    document.getElementById('sampleAnswerContent').innerHTML = `
        <div class="mb-3">
            <strong>Sample Answer:</strong><br>
            <div class="mt-2 p-3 bg-light rounded">Loading sample answer...</div>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('sampleAnswerModal'), { backdrop: false });
    modal.show();
    
    // Fetch actual sample answer
    fetch(`/admin/free-response-quiz/${id}/sample-answer`)
        .then(response => response.json())
        .then(data => {
            if (data.sample_answer) {
                document.getElementById('sampleAnswerContent').innerHTML = `
                    <div class="mb-3">
                        <strong>Sample Answer:</strong><br>
                        <div class="mt-2 p-3 bg-light rounded">${data.sample_answer}</div>
                    </div>
                    ${data.grading_rubric ? `
                        <div class="mb-3">
                            <strong>Grading Rubric:</strong><br>
                            <div class="mt-2 p-3 bg-info bg-opacity-10 rounded">${data.grading_rubric}</div>
                        </div>
                    ` : ''}
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('sampleAnswerContent').innerHTML = `
                <div class="alert alert-danger">Failed to load sample answer</div>
            `;
        });
}

// Show success/error messages
@if(session('success'))
    setTimeout(() => {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        
        setTimeout(() => alert.remove(), 5000);
    }, 100);
@endif

@if(session('error'))
    setTimeout(() => {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show position-fixed';
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        
        setTimeout(() => alert.remove(), 5000);
    }, 100);
@endif
</script>
@endpush