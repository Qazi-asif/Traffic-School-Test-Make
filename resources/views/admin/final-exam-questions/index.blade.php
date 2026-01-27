@extends('layouts.app')

@section('title', 'Final Exam Questions Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-question-circle me-2"></i>Final Exam Questions Management
                        </h4>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                                <i class="fas fa-upload me-1"></i>Import Questions
                            </button>
                            <a href="{{ route('admin.final-exam-questions.create', ['course_id' => $courseId]) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i>Add New Question
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <form method="GET" action="{{ route('admin.final-exam-questions.index') }}">
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
                            <form method="GET" action="{{ route('admin.final-exam-questions.index') }}">
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
                            <form method="GET" action="{{ route('admin.final-exam-questions.index') }}">
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
                                    <th width="12%">Type</th>
                                    <th width="12%">Correct Answer</th>
                                    <th width="8%">Points</th>
                                    <th width="10%">Actions</th>
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
                                                {{ Str::limit($question->question_text, 100) }}
                                                @if(strlen($question->question_text) > 100)
                                                    <button class="btn btn-link btn-sm p-0 ms-1" 
                                                            onclick="showFullQuestion({{ $question->id }})"
                                                            title="View full question">
                                                        <i class="fas fa-expand-alt" style="font-size: 0.75rem;"></i>
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="question-options mt-1">
                                                @php
                                                    $options = json_decode($question->options, true) ?? [];
                                                @endphp
                                                @foreach($options as $key => $option)
                                                    <small class="d-block text-muted">
                                                        <strong>{{ $key }})</strong> {{ Str::limit($option, 50) }}
                                                        @if($key === $question->correct_answer)
                                                            <i class="fas fa-check text-success ms-1" title="Correct Answer"></i>
                                                        @endif
                                                    </small>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $question->question_type === 'multiple_choice' ? 'primary' : 'info' }}">
                                                {{ ucfirst(str_replace('_', ' ', $question->question_type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ $question->correct_answer }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">{{ $question->points ?? 1 }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.final-exam-questions.edit', $question->id) }}" 
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
                                                <i class="fas fa-question-circle fa-2x mb-3"></i>
                                                <h5>No Questions Found</h5>
                                                <p>No final exam questions found for this course.</p>
                                                <a href="{{ route('admin.final-exam-questions.create', ['course_id' => $courseId]) }}" 
                                                   class="btn btn-primary">
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

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.final-exam-questions.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Questions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="course_id" value="{{ $courseId }}">
                    
                    <div class="mb-3">
                        <label for="file" class="form-label">Select Text File</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".txt" required>
                        <div class="form-text">
                            Upload a .txt file with questions. Format:
                            <br><strong>Question text</strong>
                            <br>A) Option A
                            <br>B) Option B *
                            <br>C) Option C
                            <br>D) Option D
                            <br><em>* Mark correct answer with asterisk</em>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload me-1"></i>Import Questions
                    </button>
                </div>
            </form>
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

.pagination-wrapper .page-item:first-child .page-link,
.pagination-wrapper .page-item:last-child .page-link {
    border-radius: 0.375rem;
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

.question-options small {
    font-size: 0.75rem;
    line-height: 1.3;
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

/* Compact card headers */
.card-header h4 {
    font-size: 1.25rem;
    margin-bottom: 0;
}

/* Better spacing for action buttons */
.btn-group .btn {
    margin: 0;
}

/* Compact modal styling */
.modal-body {
    padding: 1rem;
}

.modal-header {
    padding: 1rem;
}

.modal-footer {
    padding: 0.75rem 1rem;
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

@section('scripts')
<script>
function deleteQuestion(id) {
    if (confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
        fetch(`/admin/final-exam-questions/${id}`, {
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

function showFullQuestion(id) {
    // Find the question row
    const row = document.querySelector(`tr:has(td:first-child:contains('${id}'))`);
    if (!row) return;
    
    // Get question data from the row
    const questionText = row.querySelector('.question-preview').textContent.trim();
    const options = Array.from(row.querySelectorAll('.question-options small')).map(el => el.textContent.trim());
    
    // Build full question content
    let content = `<div class="mb-3"><strong>Question:</strong><br>${questionText}</div>`;
    content += `<div><strong>Options:</strong><br>`;
    options.forEach(option => {
        content += `<div class="ms-3">${option}</div>`;
    });
    content += `</div>`;
    
    document.getElementById('fullQuestionContent').innerHTML = content;
    
    const modal = new bootstrap.Modal(document.getElementById('fullQuestionModal'));
    modal.show();
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
@endsection