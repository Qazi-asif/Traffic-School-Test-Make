<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Free Response Quiz - {{ $course->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        
        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .question-card {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
        }
        
        .question-number {
            background-color: var(--accent);
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .answer-textarea {
            resize: vertical;
            min-height: 120px;
            font-family: 'Arial', sans-serif;
            line-height: 1.5;
        }
        
        .word-counter {
            font-size: 0.875rem;
            margin-top: 5px;
        }
        
        .word-counter.warning {
            color: #ffc107;
            font-weight: bold;
        }
        
        .word-counter.danger {
            color: #dc3545;
            font-weight: bold;
        }
        
        .no-copy-paste {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        .quiz-header {
            background: linear-gradient(135deg, var(--accent), var(--hover));
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .submit-section {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
            text-align: center;
        }
        
        .progress-indicator {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    
    <div class="quiz-container">
        <!-- Quiz Header -->
        <div class="quiz-header">
            <h1><i class="fas fa-edit me-2"></i>Free Response Quiz</h1>
            <h4>{{ $course->title }} ({{ $course->state_code }})</h4>
            <p class="mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Answer each question with a written response. Maximum 50 words per answer.
            </p>
        </div>

        <!-- Progress Indicator -->
        <div class="progress-indicator">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>Questions:</strong> {{ $questions->count() }} total
                </div>
                <div>
                    <strong>Answered:</strong> <span id="answered-count">{{ $existingAnswers->count() }}</span> / {{ $questions->count() }}
                </div>
                <div>
                    <div class="progress" style="width: 150px; height: 8px;">
                        <div class="progress-bar" id="progress-bar" 
                             style="width: {{ $questions->count() > 0 ? ($existingAnswers->count() / $questions->count()) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="alert alert-info">
            <h6><i class="fas fa-clipboard-list me-2"></i>Instructions:</h6>
            <ul class="mb-0">
                <li><strong>Word Limit:</strong> Each answer must be 50 words or less</li>
                <li><strong>No Copy/Paste:</strong> You must type your answers manually</li>
                <li><strong>Save Progress:</strong> Your answers are saved automatically as you type</li>
                <li><strong>Review:</strong> You can edit your answers before final submission</li>
            </ul>
        </div>

        <!-- Quiz Form -->
        <form id="quiz-form">
            @csrf
            <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}">
            
            @foreach($questions as $index => $question)
                <div class="question-card">
                    <div class="d-flex align-items-start mb-3">
                        <span class="question-number">{{ $index + 1 }}</span>
                        <div class="flex-grow-1">
                            <h5 class="mb-2">{{ $question->question_text }}</h5>
                            <div class="text-muted small">
                                <i class="fas fa-star me-1"></i>{{ $question->points }} points
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="answer_{{ $question->id }}" class="form-label">Your Answer:</label>
                        <textarea 
                            name="answers[{{ $question->id }}]" 
                            id="answer_{{ $question->id }}"
                            class="form-control answer-textarea no-copy-paste"
                            placeholder="Type your answer here... (maximum 50 words)"
                            data-question-id="{{ $question->id }}"
                            required>{{ $existingAnswers->get($question->id)->answer_text ?? '' }}</textarea>
                        <div class="word-counter" id="counter_{{ $question->id }}">
                            <span id="word-count_{{ $question->id }}">0</span> / 50 words
                        </div>
                    </div>
                </div>
            @endforeach
            
            <!-- Submit Section -->
            <div class="submit-section">
                <h5><i class="fas fa-paper-plane me-2"></i>Submit Your Answers</h5>
                <p class="text-muted">
                    Make sure all your answers are complete before submitting. 
                    You can review and edit them until you click submit.
                </p>
                
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-outline-secondary" onclick="saveProgress()">
                        <i class="fas fa-save me-1"></i>Save Progress
                    </button>
                    <button type="submit" class="btn btn-success btn-lg" id="submit-btn">
                        <i class="fas fa-check me-1"></i>Submit Final Answers
                    </button>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Your answers will be reviewed and graded by an instructor.
                    </small>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Disable copy/paste functionality
        document.addEventListener('DOMContentLoaded', function() {
            const textareas = document.querySelectorAll('.answer-textarea');
            
            textareas.forEach(textarea => {
                // Disable copy/paste
                textarea.addEventListener('paste', function(e) {
                    e.preventDefault();
                    alert('Copy and paste is not allowed. Please type your answer manually.');
                });
                
                textarea.addEventListener('copy', function(e) {
                    e.preventDefault();
                });
                
                textarea.addEventListener('cut', function(e) {
                    e.preventDefault();
                });
                
                // Disable right-click context menu
                textarea.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                });
                
                // Word counter
                textarea.addEventListener('input', function() {
                    updateWordCount(this);
                    updateProgress();
                });
                
                // Initialize word count
                updateWordCount(textarea);
            });
            
            // Initialize progress
            updateProgress();
        });
        
        function updateWordCount(textarea) {
            const questionId = textarea.dataset.questionId;
            const text = textarea.value.trim();
            const wordCount = text === '' ? 0 : text.split(/\s+/).length;
            
            const counter = document.getElementById(`counter_${questionId}`);
            const wordCountSpan = document.getElementById(`word-count_${questionId}`);
            
            wordCountSpan.textContent = wordCount;
            
            // Update styling based on word count
            counter.classList.remove('warning', 'danger');
            if (wordCount > 50) {
                counter.classList.add('danger');
                textarea.classList.add('is-invalid');
            } else if (wordCount > 40) {
                counter.classList.add('warning');
                textarea.classList.remove('is-invalid');
            } else {
                textarea.classList.remove('is-invalid');
            }
        }
        
        function updateProgress() {
            const textareas = document.querySelectorAll('.answer-textarea');
            let answeredCount = 0;
            
            textareas.forEach(textarea => {
                if (textarea.value.trim() !== '') {
                    answeredCount++;
                }
            });
            
            const totalQuestions = textareas.length;
            const percentage = totalQuestions > 0 ? (answeredCount / totalQuestions) * 100 : 0;
            
            document.getElementById('answered-count').textContent = answeredCount;
            document.getElementById('progress-bar').style.width = percentage + '%';
        }
        
        function saveProgress() {
            const formData = new FormData(document.getElementById('quiz-form'));
            
            fetch('/free-response-quiz/submit', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Progress saved successfully!', 'success');
                } else {
                    showNotification('Error: ' + data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to save progress', 'error');
            });
        }
        
        document.getElementById('quiz-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check word limits
            const textareas = document.querySelectorAll('.answer-textarea');
            let hasErrors = false;
            
            textareas.forEach(textarea => {
                const text = textarea.value.trim();
                const wordCount = text === '' ? 0 : text.split(/\s+/).length;
                
                if (wordCount > 50) {
                    hasErrors = true;
                    textarea.classList.add('is-invalid');
                }
            });
            
            if (hasErrors) {
                alert('Please ensure all answers are 50 words or less before submitting.');
                return;
            }
            
            // Check if all questions are answered
            let unanswered = 0;
            textareas.forEach(textarea => {
                if (textarea.value.trim() === '') {
                    unanswered++;
                }
            });
            
            if (unanswered > 0) {
                if (!confirm(`You have ${unanswered} unanswered question(s). Do you want to submit anyway?`)) {
                    return;
                }
            }
            
            // Submit the form
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
            
            const formData = new FormData(this);
            
            fetch('/free-response-quiz/submit', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Quiz submitted successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 2000);
                } else {
                    showNotification('Error: ' + data.error, 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-check me-1"></i>Submit Final Answers';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to submit quiz', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check me-1"></i>Submit Final Answers';
            });
        });
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
        
        // Disable keyboard shortcuts that might allow copying
        document.addEventListener('keydown', function(e) {
            // Disable Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+A
            if (e.ctrlKey && (e.key === 'c' || e.key === 'v' || e.key === 'x' || e.key === 'a')) {
                if (document.activeElement.classList.contains('answer-textarea')) {
                    e.preventDefault();
                    if (e.key === 'v') {
                        alert('Paste is not allowed. Please type your answer manually.');
                    }
                }
            }
        });
    </script>
</body>
</html>