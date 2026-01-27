<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Free Response Quiz Submissions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')
    
    <div class="container-fluid mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Free Response Quiz Submissions</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" class="mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="course_id" class="form-label">Course</label>
                                    <select name="course_id" id="course_id" class="form-select">
                                        <option value="">All Courses</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ $courseId == $course->id ? 'selected' : '' }}>
                                                {{ $course->title }} ({{ $course->state_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" name="search" id="search" class="form-control" 
                                           value="{{ $search }}" placeholder="Student name, email, or answer text">
                                </div>
                                <div class="col-md-2">
                                    <label for="per_page" class="form-label">Per Page</label>
                                    <select name="per_page" id="per_page" class="form-select">
                                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="{{ route('admin.free-response-quiz.submissions') }}" class="btn btn-secondary">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Results -->
                        @if($submissions->count() > 0)
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                Found {{ $submissions->total() }} submission(s)
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Course</th>
                                            <th>Question</th>
                                            <th>Answer</th>
                                            <th>Word Count</th>
                                            <th>Grade</th>
                                            <th>Submitted</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($submissions as $submission)
                                            <tr>
                                                <td>
                                                    <strong>{{ $submission->student_name }}</strong><br>
                                                    <small class="text-muted">{{ $submission->student_email }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ $submission->course_title ?? 'Unknown Course' }}</strong><br>
                                                    <small class="text-muted">{{ $submission->course_state ?? 'N/A' }}</small>
                                                </td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $submission->question_text }}">
                                                        {{ Str::limit($submission->question_text, 50) }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 300px;" title="{{ $submission->answer_text }}">
                                                        {{ Str::limit($submission->answer_text, 100) }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $submission->word_count > 50 ? 'bg-warning' : 'bg-success' }}">
                                                        {{ $submission->word_count }} words
                                                    </span>
                                                </td>
                                                <td>
                                                    @if(isset($submission->score))
                                                        <span class="badge bg-{{ $submission->score >= 70 ? 'success' : 'danger' }}">
                                                            {{ $submission->score }}%
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">Not Graded</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $submission->submitted_at ? \Carbon\Carbon::parse($submission->submitted_at)->format('M j, Y g:i A') : 'Not submitted' }}
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" onclick="openGradeModal({{ $submission->id }}, '{{ addslashes($submission->answer_text) }}', {{ $submission->score ?? 'null' }}, '{{ addslashes($submission->feedback ?? '') }}')">
                                                        <i class="fas fa-edit"></i> Grade
                                                    </button>
                                                </td>
                                                <td>
                                                    {{ $submission->submitted_at ? \Carbon\Carbon::parse($submission->submitted_at)->format('M j, Y g:i A') : 'Not submitted' }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $submission->status === 'submitted' ? 'success' : 'secondary' }}">
                                                        {{ ucfirst($submission->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div>
                                    Showing {{ $submissions->firstItem() }} to {{ $submissions->lastItem() }} of {{ $submissions->total() }} results
                                </div>
                                <div>
                                    {{ $submissions->appends(request()->query())->links() }}
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                No submissions found. 
                                @if($search || $courseId)
                                    Try adjusting your filters.
                                @else
                                    Students haven't submitted any free response answers yet.
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grading Modal -->
    <div class="modal fade" id="gradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Grade Answer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>Student Answer:</strong></label>
                        <div id="answerText" class="p-3 bg-light border rounded"></div>
                    </div>
                    <div class="mb-3">
                        <label for="grade" class="form-label">Grade:</label>
                        <select class="form-select" id="grade" required>
                            <option value="">Select Grade</option>
                            <option value="100">Correct</option>
                            <option value="0">Incorrect</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="feedback" class="form-label">Instructor Feedback:</label>
                        <textarea class="form-control" id="feedback" rows="3" placeholder="Optional feedback for the student"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="sample_answer" class="form-label">Sample Answer:</label>
                        <textarea class="form-control" id="sample_answer" rows="3" placeholder="Optional sample answer for this question"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitGrade()">Save Grade</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @vite(['resources/js/app.js'])
    
    <script>
        let currentAnswerId = null;
        
        function openGradeModal(answerId, answerText, currentGrade, currentFeedback) {
            currentAnswerId = answerId;
            document.getElementById('answerText').textContent = answerText;
            document.getElementById('grade').value = currentGrade || '';
            document.getElementById('feedback').value = currentFeedback || '';
            
            const modal = new bootstrap.Modal(document.getElementById('gradeModal'));
            modal.show();
        }
        
        async function submitGrade() {
            const grade = document.getElementById('grade').value;
            const feedback = document.getElementById('feedback').value;
            const sample_answer = document.getElementById('sample_answer').value;
            
            if (!grade) {
                alert('Please select a grade');
                return;
            }
            
            try {
                const response = await fetch(`/admin/free-response-quiz-submissions/${currentAnswerId}/grade`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ grade: grade, feedback: feedback, sample_answer: sample_answer })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Grade saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Failed to save grade. Please try again.');
            }
        }
    </script>
</body>
</html>
