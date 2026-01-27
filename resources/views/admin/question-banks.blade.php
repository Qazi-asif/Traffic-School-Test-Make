@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-question-circle me-2"></i>Question Banks</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
            <i class="fas fa-plus me-2"></i>Add Question
        </button>
    </div>

    <div class="card">
        <div class="card-header">
            <select class="form-select" id="course-filter" onchange="loadQuestions()">
                <option value="">All Courses</option>
            </select>
        </div>
        <div class="card-body">
            <div id="questions-table"></div>
        </div>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="questionForm">
                    <input type="hidden" id="question_id">
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select class="form-select" id="course_id" required>
                            <option value="">Select Course</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question</label>
                        <textarea class="form-control" id="question" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Options (JSON format)</label>
                        <textarea class="form-control" id="options" rows="4" required>["Option A", "Option B", "Option C", "Option D"]</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correct Answer</label>
                        <input type="text" class="form-control" id="correct_answer" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" id="category">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveQuestion()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
function loadCourses() {
    fetch('/api/courses', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        const filterSelect = document.getElementById('course-filter');
        const modalSelect = document.getElementById('course_id');
        
        filterSelect.innerHTML = '<option value="">All Courses</option>';
        modalSelect.innerHTML = '<option value="">Select Course</option>';
        
        if (data.length === 0) {
            filterSelect.innerHTML += '<option disabled>No courses available</option>';
            modalSelect.innerHTML += '<option disabled>No courses available</option>';
        } else {
            data.forEach(course => {
                const courseName = course.name || course.title || 'Unnamed Course';
                filterSelect.innerHTML += `<option value="${course.id}">${courseName}</option>`;
                modalSelect.innerHTML += `<option value="${course.id}">${courseName}</option>`;
            });
        }
    })
    .catch(error => console.error('Error loading courses:', error));
}

function loadQuestions() {
    const courseId = document.getElementById('course-filter').value;
    console.log('Loading questions for course:', courseId);
    
    fetch(`/api/question-banks?course_id=${courseId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => {
        console.log('Questions response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Questions data:', data);
        const container = document.getElementById('questions-table');
        if (data.length > 0) {
            let html = '<table class="table table-hover"><thead><tr><th>ID</th><th>Question</th><th>Category</th><th>Options</th><th>Actions</th></tr></thead><tbody>';
            data.forEach(q => {
                const options = typeof q.options === 'string' ? JSON.parse(q.options) : (q.options || []);
                const questionText = (q.question_text || q.question || 'No question text');
                const displayText = questionText.length > 100 ? questionText.substring(0, 100) + '...' : questionText;
                html += `<tr>
                    <td>${q.id}</td>
                    <td>${displayText}</td>
                    <td><span class="badge bg-info">${q.category || 'General'}</span></td>
                    <td>${options.length} options</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editQuestion(${q.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteQuestion(${q.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-center">No questions found</p>';
        }
    })
    .catch(() => {
        document.getElementById('questions-table').innerHTML = '<p class="text-danger">Error loading questions</p>';
    });
}

function saveQuestion() {
    const id = document.getElementById('question_id').value;
    const data = {
        course_id: document.getElementById('course_id').value,
        question_text: document.getElementById('question').value,
        options: JSON.parse(document.getElementById('options').value),
        correct_answer: document.getElementById('correct_answer').value,
        category: document.getElementById('category').value
    };
    
    const url = id ? `/api/question-banks/${id}` : '/api/question-banks';
    const method = id ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('addQuestionModal')).hide();
            document.getElementById('questionForm').reset();
            document.getElementById('question_id').value = '';
            document.querySelector('#addQuestionModal .modal-title').textContent = 'Add Question';
            loadQuestions();
        } else {
            alert('Error: ' + (result.error || 'Failed to save question'));
        }
    })
    .catch(error => {
        console.error('Save error:', error);
        alert('Failed to save question');
    });
}

function editQuestion(id) {
    fetch(`/api/question-banks/${id}`)
        .then(response => response.json())
        .then(q => {
            document.getElementById('question_id').value = q.id;
            document.getElementById('course_id').value = q.course_id;
            document.getElementById('question').value = q.question_text || q.question;
            document.getElementById('options').value = typeof q.options === 'string' ? q.options : JSON.stringify(q.options);
            document.getElementById('correct_answer').value = q.correct_answer;
            document.getElementById('category').value = q.category || '';
            document.querySelector('#addQuestionModal .modal-title').textContent = 'Edit Question';
            new bootstrap.Modal(document.getElementById('addQuestionModal')).show();
        });
}

function deleteQuestion(id) {
    if (confirm('Delete this question?')) {
        alert('Delete question: ' + id);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadCourses();
    loadQuestions();
});
</script>
@endsection
