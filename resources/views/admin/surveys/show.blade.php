<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-poll"></i> {{ $survey->name }}</h2>
            <div class="btn-group">
                <a href="{{ route('admin.surveys.edit', $survey) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('admin.survey-reports.by-survey', $survey) }}" class="btn btn-success">
                    <i class="fas fa-chart-bar"></i> View Report
                </a>
                <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Responses</h6>
                        <h2 class="mb-0">{{ $responsesCount }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Completed</h6>
                        <h2 class="mb-0">{{ $completedCount }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Completion Rate</h6>
                        <h2 class="mb-0">{{ $responsesCount > 0 ? round(($completedCount / $responsesCount) * 100, 1) : 0 }}%</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Survey Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <strong>State:</strong> {{ $survey->state_code ?? 'All States' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Course:</strong> {{ $survey->getCourseTitle() }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Status:</strong> 
                        <span class="badge {{ $survey->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $survey->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Required:</strong> {{ $survey->is_required ? 'Yes' : 'No' }}
                    </div>
                </div>
                @if($survey->description)
                    <div class="mt-3">
                        <strong>Description:</strong> {{ $survey->description }}
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Questions</h5>
                <button onclick="toggleAddQuestion()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Question
                </button>
            </div>
            <div class="card-body">
                <div id="addQuestionForm" class="d-none mb-4 p-3 border rounded bg-light">
                    <form action="{{ route('admin.surveys.questions.store', $survey) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Question Text *</label>
                            <textarea name="question_text" required class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Question Type *</label>
                                <select name="question_type" id="questionType" required class="form-select" onchange="toggleOptions()">
                                    <option value="scale_1_5">Scale (1-5)</option>
                                    <option value="scale_1_10">Scale (1-10)</option>
                                    <option value="rating">Rating</option>
                                    <option value="yes_no">Yes/No</option>
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="text">Text</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" name="display_order" value="{{ $survey->questions->count() }}" min="0" class="form-control">
                            </div>
                        </div>
                        <div id="optionsDiv" class="mb-3 d-none">
                            <label class="form-label">Options (one per line)</label>
                            <textarea name="options_text" class="form-control" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_required" value="1" checked class="form-check-input" id="isRequired">
                                <label class="form-check-label" for="isRequired">Required</label>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Question
                            </button>
                            <button type="button" onclick="toggleAddQuestion()" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <div class="list-group">
                    @forelse($survey->questions as $question)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="mb-2">
                                        <span class="badge bg-secondary">{{ $question->display_order }}</span>
                                        <span class="badge bg-primary">{{ $question->getQuestionTypeLabel() }}</span>
                                        @if($question->is_required)
                                            <span class="badge bg-danger">Required</span>
                                        @endif
                                    </div>
                                    <p class="mb-2">{{ $question->question_text }}</p>
                                    @if($question->options)
                                        <small class="text-muted">
                                            <strong>Options:</strong> {{ implode(', ', $question->options) }}
                                        </small>
                                    @endif
                                </div>
                                <form action="{{ route('admin.surveys.questions.destroy', [$survey, $question]) }}" method="POST" onsubmit="return confirm('Delete this question?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            No questions added yet
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleAddQuestion() {
            document.getElementById('addQuestionForm').classList.toggle('d-none');
        }

        function toggleOptions() {
            const type = document.getElementById('questionType').value;
            const optionsDiv = document.getElementById('optionsDiv');
            if (type === 'multiple_choice') {
                optionsDiv.classList.remove('d-none');
            } else {
                optionsDiv.classList.add('d-none');
            }
        }
    </script>
</body>
</html>
