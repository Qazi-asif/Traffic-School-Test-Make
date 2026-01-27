<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Questions Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        .container-fluid {
            padding: 20px;
        }
        .card {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .table {
            color: var(--text-primary);
        }
        .table thead {
            background-color: var(--accent);
            color: white;
        }
        .table tbody tr {
            border-color: var(--border);
        }
        .table tbody tr:hover {
            background-color: var(--hover);
        }
        .form-select, .form-control {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .form-select:focus, .form-control:focus {
            background-color: var(--bg-secondary);
            border-color: var(--accent);
            color: var(--text-primary);
        }
        .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
        }
        .btn-primary:hover {
            background-color: var(--hover);
            border-color: var(--hover);
        }
        .modal-content {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }
        .modal-header {
            border-bottom-color: var(--border);
        }
        .modal-footer {
            border-top-color: var(--border);
        }
        .sortable {
            cursor: move;
        }
        .sortable:hover {
            background-color: var(--hover);
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-shield-alt"></i> Security Questions Management</h2>
                    <button class="btn btn-primary" onclick="showCreateModal()">
                        <i class="fas fa-plus"></i> Add New Question
                    </button>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Security Questions</h5>
                        <small class="text-muted">Drag and drop to reorder questions</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th width="50">Order</th>
                                        <th width="80">Key</th>
                                        <th>Question</th>
                                        <th width="100">Type</th>
                                        <th width="100">Status</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="questions-tbody">
                                    @forelse($questions as $question)
                                        <tr class="sortable" data-id="{{ $question->id }}">
                                            <td>
                                                <span class="badge bg-secondary">{{ $question->order_index }}</span>
                                                <i class="fas fa-grip-vertical ms-2 text-muted"></i>
                                            </td>
                                            <td>
                                                <code>{{ $question->question_key }}</code>
                                            </td>
                                            <td>
                                                <strong>{{ $question->question_text }}</strong>
                                                @if($question->help_text)
                                                    <br><small class="text-muted">{{ $question->help_text }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($question->answer_type) }}</span>
                                            </td>
                                            <td>
                                                @if($question->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="editQuestion({{ $question->id }})" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-{{ $question->is_active ? 'warning' : 'success' }}" 
                                                            onclick="toggleActive({{ $question->id }})" 
                                                            title="{{ $question->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="fas fa-{{ $question->is_active ? 'eye-slash' : 'eye' }}"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="deleteQuestion({{ $question->id }})" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <i class="fas fa-shield-alt fa-3x mb-3"></i>
                                                <p>No security questions found.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Question Modal -->
    <div class="modal fade" id="questionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Security Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="questionForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="questionId" name="id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="question_key" class="form-label">Question Key</label>
                                    <input type="text" class="form-control" id="question_key" name="question_key" required>
                                    <small class="form-text text-muted">Unique identifier (e.g., q1, q2, etc.)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="answer_type" class="form-label">Answer Type</label>
                                    <select class="form-select" id="answer_type" name="answer_type" required>
                                        <option value="text">Text</option>
                                        <option value="number">Number</option>
                                        <option value="date">Date</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="question_text" class="form-label">Question Text</label>
                            <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="help_text" class="form-label">Help Text (Optional)</label>
                            <input type="text" class="form-control" id="help_text" name="help_text" placeholder="e.g., (Year only, e.g., 2025)">
                            <small class="form-text text-muted">Additional guidance for users</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order_index" class="form-label">Order</label>
                                    <input type="number" class="form-control" id="order_index" name="order_index" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Question</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        let questions = @json($questions);
        let editingQuestionId = null;
        
        // Initialize sortable
        const tbody = document.getElementById('questions-tbody');
        if (tbody) {
            Sortable.create(tbody, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function(evt) {
                    updateOrder();
                }
            });
        }
        
        function showCreateModal() {
            editingQuestionId = null;
            document.getElementById('modalTitle').textContent = 'Add New Security Question';
            document.getElementById('questionForm').reset();
            document.getElementById('questionId').value = '';
            document.getElementById('is_active').checked = true;
            
            // Set next order index
            const maxOrder = Math.max(...questions.map(q => q.order_index), 0);
            document.getElementById('order_index').value = maxOrder + 1;
            
            new bootstrap.Modal(document.getElementById('questionModal')).show();
        }
        
        function editQuestion(id) {
            const question = questions.find(q => q.id === id);
            if (!question) return;
            
            editingQuestionId = id;
            document.getElementById('modalTitle').textContent = 'Edit Security Question';
            document.getElementById('questionId').value = question.id;
            document.getElementById('question_key').value = question.question_key;
            document.getElementById('question_text').value = question.question_text;
            document.getElementById('answer_type').value = question.answer_type;
            document.getElementById('help_text').value = question.help_text || '';
            document.getElementById('order_index').value = question.order_index;
            document.getElementById('is_active').checked = question.is_active;
            
            new bootstrap.Modal(document.getElementById('questionModal')).show();
        }
        
        function toggleActive(id) {
            if (confirm('Are you sure you want to change the status of this question?')) {
                fetch(`/admin/security-questions/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating question status');
                });
            }
        }
        
        function deleteQuestion(id) {
            if (confirm('Are you sure you want to delete this security question? This action cannot be undone.')) {
                fetch(`/admin/security-questions/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting question');
                });
            }
        }
        
        function updateOrder() {
            const rows = document.querySelectorAll('#questions-tbody tr.sortable');
            const questionsData = [];
            
            rows.forEach((row, index) => {
                const id = row.dataset.id;
                questionsData.push({
                    id: parseInt(id),
                    order_index: index + 1
                });
            });
            
            fetch('/admin/security-questions/reorder', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ questions: questionsData })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update order badges
                    rows.forEach((row, index) => {
                        const badge = row.querySelector('.badge');
                        badge.textContent = index + 1;
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                location.reload(); // Reload on error to reset order
            });
        }
        
        // Form submission
        document.getElementById('questionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            data.is_active = document.getElementById('is_active').checked;
            
            const url = editingQuestionId 
                ? `/admin/security-questions/${editingQuestionId}`
                : '/admin/security-questions';
            
            const method = editingQuestionId ? 'PUT' : 'POST';
            
            if (editingQuestionId) {
                data._method = 'PUT';
            }
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('questionModal')).hide();
                    location.reload();
                } else {
                    alert('Error saving question: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving question');
            });
        });
    </script>

    <x-footer />
</body>
</html>