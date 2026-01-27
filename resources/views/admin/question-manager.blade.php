@extends('layouts.app')

@section('title', 'Question Manager')

@section('content')
<style>
.modal-backdrop {
    display: none !important;
}
</style>
<div class="container-fluid py-2" style="margin-left: 10px; padding: 10px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Question Manager - Chapter {{ $chapterId }}</h2>
        <div>
            <!-- Delaware Quiz Set Selector -->
            @if($courseStateCode === 'DE')
            <div class="btn-group me-3" role="group">
                <input type="radio" class="btn-check" name="quizSet" id="quizSet1" value="1" checked onchange="switchQuizSet(1)">
                <label class="btn btn-outline-primary" for="quizSet1">Quiz Set 1</label>
                
                <input type="radio" class="btn-check" name="quizSet" id="quizSet2" value="2" onchange="switchQuizSet(2)">
                <label class="btn btn-outline-primary" for="quizSet2">Quiz Set 2</label>
            </div>
            @endif
            
            <button class="btn btn-success" onclick="exportSampleDocx()">
                <i class="fas fa-download"></i> Export Sample DOCX
            </button>
            <button class="btn btn-info" onclick="document.getElementById('importFile').click()">
                <i class="fas fa-upload"></i> Import
            </button>
            <input type="file" id="importFile" accept=".docx,.txt" style="display:none;" onchange="importFile(event)">
            <button class="btn btn-warning ms-2" onclick="document.getElementById('importFinalExam').click()">
                <i class="fas fa-upload"></i> Import Final Exam (TXT)
            </button>
            <input type="file" id="importFinalExam" accept=".txt" style="display:none;" onchange="importFinalExam(event)">
            <button class="btn btn-danger ms-2" onclick="deleteAllQuestions()">
                <i class="fas fa-trash-alt"></i> Delete All
            </button>
            <button class="btn btn-primary" onclick="showCreateModal()">
                <i class="fas fa-plus"></i> Add Question
            </button>
        </div>
    </div>

    @if($courseStateCode === 'DE')
    <div class="alert alert-info mb-3">
        <i class="fas fa-info-circle"></i> 
        <strong>Delaware Rotating Quiz System:</strong> 
        Create questions for both Quiz Set 1 and Quiz Set 2. If a student fails Quiz Set 1, they will automatically see Quiz Set 2 questions.
        Currently viewing: <span id="currentQuizSetLabel">Quiz Set 1</span>
    </div>
    @endif

    <!-- Bulk Actions -->
    <div class="row justify-content-center mb-3" id="bulk-actions" style="display: none;">
        <div class="col-md-9">
            <div class="d-flex align-items-center gap-3">
                <label>
                    <input type="checkbox" id="select-all"> Select All
                </label>
                <button class="btn btn-danger btn-sm" onclick="deleteSelected()">
                    Delete Selected (<span id="selected-count">0</span>)
                </button>
            </div>
        </div>
    </div>

    <div id="questions-list" class="row justify-content-center">
        <p>Loading questions...</p>
    </div>
</div>

<!-- Question Modal -->
<div class="modal fade" id="questionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="questionForm">
                    <input type="hidden" id="questionId">
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea class="form-control" id="questionText" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question Type</label>
                        <select class="form-control" id="questionType" onchange="updateOptionsFields()" required>
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="true_false">True/False</option>
                        </select>
                    </div>
                    <div id="optionsContainer">
                        <div class="mb-3">
                            <label class="form-label">Options (one per line)</label>
                            <textarea class="form-control" id="options" rows="4" placeholder="Option A&#10;Option B&#10;Option C&#10;Option D"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correct Answer</label>
                        <input type="text" class="form-control" id="correctAnswer" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Explanation (optional)</label>
                        <textarea class="form-control" id="explanation" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Points</label>
                        <input type="number" class="form-control" id="points" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Order</label>
                        <input type="number" class="form-control" id="orderIndex" value="1" min="1" required>
                    </div>
                    @if($courseStateCode === 'DE')
                    <div class="mb-3">
                        <label class="form-label">Quiz Set</label>
                        <select class="form-control" id="quizSet" required>
                            <option value="1">Quiz Set 1</option>
                            <option value="2">Quiz Set 2</option>
                        </select>
                        <small class="form-text text-muted">Select which quiz set this question belongs to</small>
                    </div>
                    @endif
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveQuestion()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="progressTitle">Processing...</h5>
            </div>
            <div class="modal-body">
                <div class="progress mb-3">
                    <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%"></div>
                </div>
                <div id="progressText">Starting...</div>
                <div id="progressDetails" class="mt-2 small text-muted"></div>
                <div id="errorLog" class="mt-3" style="display: none;">
                    <h6 class="text-danger">Errors:</h6>
                    <div class="alert alert-danger" style="max-height: 200px; overflow-y: auto;">
                        <ul id="errorList" class="mb-0"></ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="progressClose" data-bs-dismiss="modal" style="display: none;">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="successToast" class="toast" role="alert">
        <div class="toast-header bg-success text-white">
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body text-dark" id="toastMessage">
            Question saved successfully!
        </div>
    </div>
</div>

<script>
const chapterId = '{{ $chapterId }}';
const courseId = '{{ $courseId ?? 1 }}';
const courseStateCode = '{{ $courseStateCode ?? "" }}';
let editingQuestionId = null;
let currentQuizSet = 1;

async function loadQuestions() {
    try {
        let url = `/api/chapters/${chapterId}/questions`;
        
        // Add course_id parameter for final exam
        if (chapterId === 'final-exam') {
            url += `?course_id=${courseId}`;
        }
        
        // Add quiz_set parameter for Delaware courses
        if (courseStateCode === 'DE') {
            const separator = url.includes('?') ? '&' : '?';
            url += `${separator}quiz_set=${currentQuizSet}`;
        }
        
        const response = await fetch(url);
        const questions = await response.json();
        displayQuestions(questions);
    } catch (error) {
        console.error('Error loading questions:', error);
        document.getElementById('questions-list').innerHTML = '<p class="text-danger">Failed to load questions</p>';
    }
}

function switchQuizSet(quizSet) {
    currentQuizSet = quizSet;
    document.getElementById('currentQuizSetLabel').textContent = `Quiz Set ${quizSet}`;
    loadQuestions();
}

function showCreateModal() {
    editingQuestionId = null;
    document.getElementById('modalTitle').textContent = 'Add Question';
    document.getElementById('questionForm').reset();
    document.getElementById('questionId').value = '';
    
    // Set default quiz set for Delaware courses
    if (courseStateCode === 'DE') {
        document.getElementById('quizSet').value = currentQuizSet;
    }
    
    updateOptionsFields();
    new bootstrap.Modal(document.getElementById('questionModal')).show();
}

function displayQuestions(questions) {
    const container = document.getElementById('questions-list');
    const bulkActions = document.getElementById('bulk-actions');
    
    if (questions.length === 0) {
        container.innerHTML = '<p>No questions yet. Click "Add Question" to create one.</p>';
        bulkActions.style.display = 'none';
        return;
    }
    
    bulkActions.style.display = 'block';
    
    container.innerHTML = questions.map((q, index) => {
        // Determine question source
        let sourceLabel = '';
        let sourceClass = '';
        let isLegacy = q.id.toString().startsWith('old_');
        
        if (isLegacy) {
            sourceLabel = '<span class="badge bg-warning ms-2" style="font-size: 10px;">Legacy (Old Table)</span>';
            sourceClass = 'border-warning';
        } else {
            // Check if this question is from questions table or chapter_questions table
            // We can't determine this from the frontend, so we'll show a generic label
            sourceLabel = '<span class="badge bg-success ms-2" style="font-size: 10px;">Active</span>';
            sourceClass = 'border-success';
        }
        
        return `
        <div class="col-md-9 mb-2">
            <div class="card ${sourceClass}" style="padding: 10px;">
                <div class="card-body" style="padding: 8px;">
                    <div class="d-flex justify-content-between align-items-start">
                        <input type="checkbox" class="question-checkbox me-2" value="${q.id}" onchange="updateSelectedCount()" style="margin-top: 2px;" ${isLegacy ? 'disabled title="Cannot select legacy questions"' : ''}>
                        <div style="flex: 1; min-width: 0;">
                            <h6 style="margin: 0; font-size: 14px;">${index + 1}. ${q.question_text} ${sourceLabel}</h6>
                            <small class="text-muted" style="display: block; margin-top: 4px;"><strong>Type:</strong> ${q.question_type}</small>
                            ${courseStateCode === 'DE' ? `<small class="text-muted" style="display: block;"><strong>Quiz Set:</strong> ${q.quiz_set || 1}</small>` : ''}
                            ${q.options ? (function() {
                                let optionsDisplay = '';
                                let optionsData = q.options;
                                
                                // Parse if string
                                if (typeof optionsData === 'string') {
                                    try {
                                        optionsData = JSON.parse(optionsData);
                                    } catch (e) {
                                        return '<small class="text-muted" style="display: block;"><strong>Options:</strong> ' + q.options + '</small>';
                                    }
                                }
                                
                                if (Array.isArray(optionsData)) {
                                    // Check if array contains objects with label/text
                                    if (optionsData.length > 0 && typeof optionsData[0] === 'object' && optionsData[0].label) {
                                        optionsDisplay = optionsData.map(o => `${o.label}. ${o.text}`).filter(t => t.trim() !== '. ').join(', ');
                                    } else {
                                        optionsDisplay = optionsData.join(', ');
                                    }
                                } else if (typeof optionsData === 'object') {
                                    optionsDisplay = Object.entries(optionsData).map(([k, v]) => `${k}. ${v}`).join(', ');
                                }
                                
                                return optionsDisplay ? '<small class="text-muted" style="display: block;"><strong>Options:</strong> ' + optionsDisplay + '</small>' : '';
                            })() : ''}
                            <small class="text-muted" style="display: block;"><strong>Answer:</strong> ${q.correct_answer}</small>
                            ${q.explanation ? `<small class="text-muted" style="display: block;"><strong>Exp:</strong> ${q.explanation}</small>` : ''}
                            <small class="text-muted" style="display: block;"><strong>Points:</strong> ${q.points}</small>
                        </div>
                        <div style="margin-left: 10px; white-space: nowrap;">
                            <button class="btn btn-sm btn-outline-primary" onclick="editQuestion('${q.id}')" style="padding: 4px 8px; font-size: 12px;" ${q.id.toString().startsWith('old_') ? 'disabled title="Cannot edit legacy questions"' : ''}>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteQuestion('${q.id}')" style="padding: 4px 8px; font-size: 12px;" ${isLegacy ? 'disabled title="Legacy questions cannot be deleted from this interface"' : 'title="Delete this question"'}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `
    }).join('');
}

function showCreateModal() {
    editingQuestionId = null;
    document.getElementById('modalTitle').textContent = 'Add Question';
    document.getElementById('questionForm').reset();
    document.getElementById('questionId').value = '';
    new bootstrap.Modal(document.getElementById('questionModal')).show();
}

async function editQuestion(id) {
    try {
        // Handle prefixed IDs from old questions table
        if (typeof id === 'string' && id.startsWith('old_')) {
            alert('Editing questions from the old questions table is not supported. Please create a new question in the chapter_questions table instead.');
            return;
        }
        
        // Pass chapter context to help backend choose correct question
        const response = await fetch(`/api/questions/${id}?chapter_id=${chapterId}`, {
            headers: {
                'X-Chapter-Id': chapterId
            }
        });
        const question = await response.json();
        
        console.log('Edit question data:', question);
        console.log('Options data:', question.options);
        
        editingQuestionId = id;
        document.getElementById('modalTitle').textContent = 'Edit Question';
        document.getElementById('questionId').value = question.id;
        document.getElementById('questionText').value = question.question_text || '';
        document.getElementById('questionType').value = question.question_type || 'multiple_choice';
        document.getElementById('correctAnswer').value = question.correct_answer || '';
        document.getElementById('explanation').value = question.explanation || '';
        document.getElementById('points').value = question.points || 1;
        document.getElementById('orderIndex').value = question.order_index || 1;
        
        // Update options fields first to set up the UI
        updateOptionsFields();
        
        // Then set the options value after the UI is ready
        setTimeout(() => {
            let optionsText = '';
            if (question.options) {
                let optionsData = question.options;
                
                // Parse if string
                if (typeof optionsData === 'string') {
                    try {
                        optionsData = JSON.parse(optionsData);
                    } catch (e) {
                        optionsText = question.options;
                    }
                }
                
                if (Array.isArray(optionsData)) {
                    // Check if array contains objects with label/text
                    if (optionsData.length > 0 && typeof optionsData[0] === 'object' && optionsData[0].label) {
                        optionsText = optionsData.map(o => `${o.label}. ${o.text}`).filter(t => t.trim() !== '. ').join('\n');
                    } else if (optionsData.length > 0 && typeof optionsData[0] === 'object' && optionsData[0].text) {
                        optionsText = optionsData.map(o => o.text).join('\n');
                    } else {
                        optionsText = optionsData.join('\n');
                    }
                } else if (typeof optionsData === 'object') {
                    optionsText = Object.entries(optionsData).map(([k, v]) => `${k}. ${v}`).join('\n');
                }
            }
            
            const optionsField = document.getElementById('options');
            if (optionsField) {
                optionsField.value = optionsText;
                console.log('Set options field to:', optionsText);
            }
        }, 100);
        
        new bootstrap.Modal(document.getElementById('questionModal')).show();
    } catch (error) {
        console.error('Error loading question:', error);
        alert('Failed to load question');
    }
}

function updateOptionsFields() {
    const type = document.getElementById('questionType').value;
    const container = document.getElementById('optionsContainer');
    
    if (type === 'true_false') {
        container.innerHTML = '<input type="hidden" id="options" value="[&quot;True&quot;,&quot;False&quot;]">';
    } else {
        container.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Options (one per line)</label>
                <textarea class="form-control" id="options" rows="4" placeholder="Option A&#10;Option B&#10;Option C&#10;Option D"></textarea>
            </div>
        `;
        
        // Add paste event listener to auto-remove *** and set correct answer
        setTimeout(() => {
            const optionsTextarea = document.getElementById('options');
            optionsTextarea.addEventListener('paste', function(e) {
                setTimeout(() => {
                    const lines = this.value.split('\n');
                    let correctAnswer = '';
                    const cleanLines = lines.map(line => {
                        if (line.includes('***')) {
                            // Extract the option letter (A, B, C, etc.)
                            const match = line.match(/^([A-E])\./);
                            if (match) {
                                correctAnswer = match[1];
                            }
                            // Remove *** from the line
                            return line.replace(/\*\*\*/g, '').trim();
                        }
                        return line;
                    });
                    
                    this.value = cleanLines.join('\n');
                    
                    // Set the correct answer if found
                    if (correctAnswer) {
                        document.getElementById('correctAnswer').value = correctAnswer;
                    }
                }, 10);
            });
        }, 10);
    }
}

async function saveQuestion() {
    const questionType = document.getElementById('questionType').value;
    const correctAnswerInput = document.getElementById('correctAnswer').value;
    let finalCorrectAnswer = correctAnswerInput;
    
    // For multiple choice questions, convert letter to actual option text
    if (questionType === 'multiple_choice') {
        const optionsText = document.getElementById('options').value;
        const optionsArray = optionsText.split('\n').filter(o => o.trim());
        
        // Check if correct answer is a letter (A, B, C, D, E)
        if (/^[A-E]$/i.test(correctAnswerInput.trim())) {
            const letterIndex = correctAnswerInput.toUpperCase().charCodeAt(0) - 65; // A=0, B=1, C=2, etc.
            
            if (letterIndex >= 0 && letterIndex < optionsArray.length) {
                // Get the actual text of the option, removing any letter prefix
                let optionText = optionsArray[letterIndex].trim();
                
                // Remove letter prefix if it exists (e.g., "A. text" -> "text")
                optionText = optionText.replace(/^[A-E]\.\s*/i, '');
                
                finalCorrectAnswer = optionText;
                console.log(`Converted correct answer from "${correctAnswerInput}" to "${finalCorrectAnswer}"`);
            }
        }
    }
    
    const data = {
        question_text: document.getElementById('questionText').value,
        question_type: questionType,
        options: questionType === 'true_false' 
            ? JSON.stringify(['True', 'False'])
            : JSON.stringify(document.getElementById('options').value.split('\n').filter(o => o.trim())),
        correct_answer: finalCorrectAnswer, // Use the converted answer
        explanation: document.getElementById('explanation').value,
        points: parseInt(document.getElementById('points').value),
        order_index: parseInt(document.getElementById('orderIndex').value)
    };
    
    // Add quiz_set for Delaware courses
    if (courseStateCode === 'DE') {
        data.quiz_set = parseInt(document.getElementById('quizSet').value);
    }
    
    // Add course_id for final exam questions
    if (chapterId === 'final-exam') {
        data.course_id = courseId;
    }
    
    // Show progress modal for saving
    showProgressModal(editingQuestionId ? 'Updating Question' : 'Creating Question', 'Saving question...');
    updateProgress(50, 100, 'Processing...');
    
    try {
        const url = editingQuestionId 
            ? `/api/questions/${editingQuestionId}`
            : `/api/chapters/${chapterId}/questions`;
        const method = editingQuestionId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            updateProgress(100, 100, editingQuestionId ? 'Question updated successfully!' : 'Question created successfully!');
            document.getElementById('progressClose').style.display = 'block';
            
            // Close modals and reload
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('questionModal')).hide();
                bootstrap.Modal.getInstance(document.getElementById('progressModal')).hide();
                loadQuestions();
                showToast(editingQuestionId ? 'Question updated!' : 'Question created!');
            }, 1500);
        } else {
            const result = await response.json();
            updateProgress(0, 100, 'Save failed!');
            showErrors([result.message || 'Error saving question']);
            document.getElementById('progressClose').style.display = 'block';
        }
    } catch (error) {
        console.error('Error saving question:', error);
        updateProgress(0, 100, 'Save failed!');
        showErrors([error.message || 'Network error occurred']);
        document.getElementById('progressClose').style.display = 'block';
    }
}

async function deleteQuestion(id) {
    if (!confirm('Are you sure you want to delete this question? This action cannot be undone.')) return;
    
    try {
        // Handle prefixed IDs from old questions table
        if (typeof id === 'string' && id.startsWith('old_')) {
            alert('Legacy questions from the old questions table cannot be deleted through this interface.\n\nIf you need to remove legacy questions, please contact your system administrator.');
            return;
        }
        
        const response = await fetch(`/api/questions/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        if (response.ok) {
            const result = await response.json();
            loadQuestions();
            alert('Question deleted successfully!');
        } else {
            const error = await response.json();
            alert('Error deleting question: ' + (error.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error deleting question:', error);
        alert('Error deleting question: ' + error.message);
    }
}

loadQuestions();

// Add paste event listener to clean question text
document.getElementById('questionText').addEventListener('paste', function(e) {
    // Allow the paste to happen first
    setTimeout(() => {
        let text = this.value;
        
        // Remove option prefixes like A), B ), C) etc. (with or without spaces)
        text = text.replace(/^[A-Z]\s*\)\s*/gm, '');
        
        // Remove numbered prefixes like 1), 2 ), 3) etc. (with or without spaces)
        text = text.replace(/^\d+\s*\)\s*/gm, '');
        
        // Clean up extra whitespace
        text = text.trim();
        
        // Update the textarea value
        this.value = text;
        
        console.log('Cleaned pasted text:', text);
    }, 10);
});

// Add paste event listener to clean options text
document.getElementById('options').addEventListener('paste', function(e) {
    // Allow the paste to happen first
    setTimeout(() => {
        let text = this.value;
        let correctAnswerIndex = -1;
        
        // Split into lines and process each
        let lines = text.split('\n');
        let cleanedLines = [];
        
        lines.forEach((line, index) => {
            line = line.trim();
            if (!line) return;
            
            // Check if this line has *** (correct answer marker)
            if (line.includes('***')) {
                correctAnswerIndex = cleanedLines.length; // Current position in cleaned array
                line = line.replace(/\*\*\*/g, '').trim(); // Remove ***
            }
            
            // Remove option prefixes like A), B ), C) etc.
            line = line.replace(/^[A-Z]\s*\)\s*/, '');
            
            // Remove numbered prefixes like 1), 2 ), 3) etc.
            line = line.replace(/^\d+\s*\)\s*/, '');
            
            if (line.trim()) {
                cleanedLines.push(line.trim());
            }
        });
        
        // Update the textarea value
        this.value = cleanedLines.join('\n');
        
        // Auto-select correct answer if *** was found
        if (correctAnswerIndex >= 0) {
            const correctAnswerField = document.getElementById('correctAnswer');
            const answerLabels = ['A', 'B', 'C', 'D', 'E', 'F'];
            if (correctAnswerIndex < answerLabels.length) {
                correctAnswerField.value = answerLabels[correctAnswerIndex];
                console.log('Auto-selected correct answer:', answerLabels[correctAnswerIndex]);
            }
        }
        
        console.log('Cleaned pasted options:', cleanedLines);
    }, 10);
});

async function exportSampleDocx() {
    try {
        const response = await fetch(`/api/chapters/${chapterId}/questions/export-sample`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `questions_sample_chapter_${chapterId}.docx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } else {
            alert('Error exporting sample');
        }
    } catch (error) {
        console.error('Error exporting sample:', error);
        alert('Error exporting sample');
    }
}

async function importFile(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    if (fileExtension === 'docx') {
        return importDocx(event);
    } else if (fileExtension === 'txt') {
        return importTxt(event);
    } else {
        alert('Please select a .docx or .txt file');
        return;
    }
}

async function importTxt(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    console.log('Starting txt import with file:', file.name);
    console.log('Chapter ID:', chapterId);
    console.log('Course ID:', courseId);
    
    // Show progress modal
    showProgressModal('Importing Questions', 'Uploading and processing file...');
    updateProgress(0, 100, 'Uploading file...');
    
    const formData = new FormData();
    formData.append('file', file);
    
    // Add course_id for final exam
    if (chapterId === 'final-exam') {
        formData.append('course_id', courseId);
    }
    
    try {
        let url;
        if (chapterId === 'final-exam') {
            url = `/api/chapters/final-exam/questions/import-txt`;
        } else {
            url = `/api/chapters/${chapterId}/questions/import-txt`;
        }
        
        console.log('Posting to URL:', url);
        updateProgress(30, 100, 'Processing file...');
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        console.log('Response status:', response.status);
        console.log('Response OK:', response.ok);
        
        updateProgress(70, 100, 'Parsing response...');
        
        const result = await response.json();
        console.log('Import response:', result);
        
        if (response.ok && result.success) {
            console.log(`Successfully imported ${result.count} questions`);
            updateProgress(100, 100, `Successfully imported ${result.count} questions!`);
            
            // Show success details
            const details = `
                Questions imported: ${result.count}
                File: ${file.name}
                Chapter: ${chapterId}
                Course ID: ${courseId}
            `;
            document.getElementById('progressDetails').innerHTML = details.replace(/\n/g, '<br>');
            document.getElementById('progressClose').style.display = 'block';
            
            // Reload questions after a short delay
            setTimeout(() => {
                loadQuestions();
                document.getElementById('importFile').value = '';
            }, 1000);
        } else {
            updateProgress(0, 100, 'Import failed!');
            showErrors([result.message || 'Unknown error']);
            document.getElementById('progressClose').style.display = 'block';
        }
    } catch (error) {
        console.error('Error importing:', error);
        updateProgress(0, 100, 'Import failed!');
        showErrors([error.message || 'Network error occurred']);
        document.getElementById('progressClose').style.display = 'block';
    }
}

async function importDocx(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    console.log('Starting import with file:', file.name);
    
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        const response = await fetch(`/api/chapters/${chapterId}/questions/import`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const result = await response.json();
        console.log('Import response:', result);
        
        if (response.ok) {
            console.log(`Successfully imported ${result.count} questions`);
            console.log('Debug info:', result.debug);
            alert(`Successfully imported ${result.count} questions!`);
            loadQuestions();
            document.getElementById('importFile').value = '';
        } else {
            console.error('Import error:', result);
            alert('Error importing: ' + (result.message || 'Unknown error') + '\n' + (result.trace || ''));
        }
    } catch (error) {
        console.error('Error importing:', error);
        alert('Error importing file: ' + error.message);
    }
}

async function importFinalExam(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    console.log('Starting final exam import with file:', file.name);
    
    // Show progress modal
    showProgressModal('Importing Final Exam', 'Uploading and processing file...');
    updateProgress(0, 100, 'Uploading file...');
    
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        const response = await fetch('/api/final-exam/import', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const result = await response.json();
        console.log('Final exam import response:', result);
        
        if (response.ok) {
            updateProgress(100, 100, `Successfully imported ${result.count} questions!`);
            document.getElementById('progressClose').style.display = 'block';
            document.getElementById('importFinalExam').value = '';
            
            // Show detailed results
            const details = `
                Questions imported: ${result.count}
                File processed: ${file.name}
                Import completed successfully
            `;
            document.getElementById('progressDetails').innerHTML = details.replace(/\n/g, '<br>');
            
        } else {
            updateProgress(0, 100, 'Import failed!');
            showErrors([result.message || 'Unknown error']);
            document.getElementById('progressClose').style.display = 'block';
        }
    } catch (error) {
        console.error('Error importing final exam:', error);
        updateProgress(0, 100, 'Import failed!');
        showErrors([error.message]);
        document.getElementById('progressClose').style.display = 'block';
    }
}

function showToast(message) {
    document.getElementById('toastMessage').textContent = message;
    const toast = new bootstrap.Toast(document.getElementById('successToast'));
    toast.show();
}

function showProgressModal(title, message) {
    document.getElementById('progressTitle').textContent = title;
    document.getElementById('progressText').textContent = message;
    document.getElementById('progressBar').style.width = '0%';
    document.getElementById('progressDetails').innerHTML = '';
    document.getElementById('errorLog').style.display = 'none';
    document.getElementById('progressClose').style.display = 'none';
    
    const modal = new bootstrap.Modal(document.getElementById('progressModal'));
    modal.show();
}

function updateProgress(current, total, message) {
    const percentage = Math.round((current / total) * 100);
    document.getElementById('progressBar').style.width = percentage + '%';
    document.getElementById('progressBar').textContent = percentage + '%';
    document.getElementById('progressText').textContent = message;
}

function showErrors(errors) {
    const errorList = document.getElementById('errorList');
    errorList.innerHTML = '';
    
    errors.forEach(error => {
        const li = document.createElement('li');
        li.textContent = error;
        errorList.appendChild(li);
    });
    
    document.getElementById('errorLog').style.display = 'block';
}

// Bulk operations
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.question-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selected-count').textContent = count;
    
    // Update select all checkbox
    const selectAll = document.getElementById('select-all');
    const allCheckboxes = document.querySelectorAll('.question-checkbox');
    selectAll.checked = count === allCheckboxes.length && count > 0;
    selectAll.indeterminate = count > 0 && count < allCheckboxes.length;
}

document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.question-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateSelectedCount();
});

async function deleteSelected() {
    const checkboxes = document.querySelectorAll('.question-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);
    
    if (ids.length === 0) {
        alert('No questions selected');
        return;
    }
    
    if (!confirm(`Delete ${ids.length} selected questions?`)) return;
    
    // Show progress modal
    showProgressModal('Deleting Questions', `Deleting ${ids.length} questions...`);
    
    let completed = 0;
    let errors = [];
    
    try {
        for (let i = 0; i < ids.length; i++) {
            const id = ids[i];
            updateProgress(i + 1, ids.length, `Deleting question ${i + 1} of ${ids.length}...`);
            
            try {
                const response = await fetch(`/api/questions/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                
                if (response.ok) {
                    completed++;
                } else {
                    const result = await response.json();
                    errors.push(`Question ${id}: ${result.message || 'Delete failed'}`);
                }
            } catch (error) {
                errors.push(`Question ${id}: ${error.message}`);
            }
        }
        
        // Show completion
        updateProgress(ids.length, ids.length, `Completed! ${completed} deleted, ${errors.length} errors`);
        
        if (errors.length > 0) {
            showErrors(errors);
        }
        
        document.getElementById('progressClose').style.display = 'block';
        loadQuestions();
        
    } catch (error) {
        updateProgress(0, ids.length, 'Error: ' + error.message);
        document.getElementById('progressClose').style.display = 'block';
    }
}

async function deleteAllQuestions() {
    if (!confirm('Are you sure you want to delete ALL questions? This action cannot be undone!')) {
        return;
    }
    
    if (!confirm('This will permanently delete all questions for this chapter/exam. Are you absolutely sure?')) {
        return;
    }
    
    // Show progress modal
    showProgressModal('Deleting All Questions', 'Fetching questions...');
    updateProgress(10, 100, 'Loading questions...');
    
    try {
        // First, get all questions to know how many we're deleting
        let url = `/api/chapters/${chapterId}/questions`;
        if (chapterId === 'final-exam') {
            url += `?course_id=${courseId}`;
        }
        
        const response = await fetch(url);
        const questions = await response.json();
        
        if (questions.length === 0) {
            updateProgress(100, 100, 'No questions to delete');
            document.getElementById('progressClose').style.display = 'block';
            return;
        }
        
        updateProgress(20, 100, `Found ${questions.length} questions. Starting deletion...`);
        
        let deleted = 0;
        let errors = [];
        
        // Delete each question
        for (let i = 0; i < questions.length; i++) {
            const question = questions[i];
            const progress = 20 + ((i + 1) / questions.length) * 70; // 20% to 90%
            
            updateProgress(progress, 100, `Deleting question ${i + 1} of ${questions.length}...`);
            
            try {
                const deleteResponse = await fetch(`/api/questions/${question.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if (deleteResponse.ok) {
                    deleted++;
                } else {
                    const result = await deleteResponse.json();
                    errors.push(`Question ${question.id}: ${result.message || 'Delete failed'}`);
                }
            } catch (error) {
                errors.push(`Question ${question.id}: ${error.message}`);
            }
            
            // Small delay to show progress
            await new Promise(resolve => setTimeout(resolve, 100));
        }
        
        // Show completion
        updateProgress(100, 100, `Completed! ${deleted} deleted, ${errors.length} errors`);
        
        if (errors.length > 0) {
            showErrors(errors);
        }
        
        // Show success details
        const details = `
            Total questions found: ${questions.length}
            Successfully deleted: ${deleted}
            Errors: ${errors.length}
            Chapter: ${chapterId}
            Course ID: ${courseId}
        `;
        document.getElementById('progressDetails').innerHTML = details.replace(/\n/g, '<br>');
        document.getElementById('progressClose').style.display = 'block';
        
        // Reload questions
        loadQuestions();
        
    } catch (error) {
        console.error('Error deleting all questions:', error);
        updateProgress(0, 100, 'Delete operation failed!');
        showErrors([error.message || 'Network error occurred']);
        document.getElementById('progressClose').style.display = 'block';
    }
}

</script>
@endsection
