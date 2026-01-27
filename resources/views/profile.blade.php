<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        .profile-section {
            margin-bottom: 2rem;
        }
        .security-question {
            background-color: var(--bg-secondary);
            border-left: 4px solid var(--accent);
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.375rem;
            color: var(--text-primary);
        }
        .field-label {
            font-weight: 600;
            color: var(--text-secondary);
        }
        .field-value {
            color: var(--text-primary);
        }
        .card {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .card-header {
            border-bottom-color: var(--border);
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
        .form-control {
            background-color: var(--bg-primary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .form-control:focus {
            background-color: var(--bg-primary);
            border-color: var(--accent);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
        }
        .form-label {
            color: var(--text-secondary);
        }
        .alert-info {
            background-color: var(--bg-primary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .edit-mode {
            display: none;
        }
        .edit-mode.active {
            display: block;
        }
        .view-mode.editing {
            display: none;
        }
        
        /* Fix modal backdrop and z-index issues */
        .modal-backdrop {
            display: none !important;
            visibility: hidden !important;
            pointer-events: none !important;
            opacity: 0 !important;
        }
        .modal {
            z-index: 1050;
            background-color: rgba(0, 0, 0, 0.5) !important;
        }
        .modal-dialog {
            z-index: 1060;
        }
        
        /* Ensure modal content is properly positioned */
        .modal.show .modal-dialog {
            transform: none;
        }
        
        /* Fix any potential overflow issues */
        body.modal-open {
            overflow: hidden;
        }
        
        /* Custom modal styling without backdrop */
        #editProfileModal.show {
            background-color: rgba(0, 0, 0, 0.5) !important;
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-user-circle me-2"></i>User Profile</h1>
                    <button class="btn btn-primary" id="editProfileBtn">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </button>
                </div>
            </div>
        </div>

        <!-- Basic Information -->
        <div class="profile-section">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Basic Information</h5>
                </div>
                <div class="card-body" id="basic-info">
                    <p>Loading profile...</p>
                </div>
            </div>
        </div>

        <!-- License Information -->
        <div class="profile-section">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>License Information</h5>
                </div>
                <div class="card-body" id="license-info">
                    <p>Loading license information...</p>
                </div>
            </div>
        </div>

        <!-- Court & Citation Information -->
        <div class="profile-section">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-gavel me-2"></i>Court & Citation Information</h5>
                </div>
                <div class="card-body" id="court-info">
                    <p>Loading court information...</p>
                </div>
            </div>
        </div>

        <!-- Security Questions -->
        <div class="profile-section">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Questions</h5>
                </div>
                <div class="card-body" id="security-questions">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        These security questions are used for identity verification during your course.
                    </div>
                    <div id="security-questions-content">
                        <p>Loading security questions...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="profile-section">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Account Information</h5>
                </div>
                <div class="card-body" id="account-info">
                    <p>Loading account information...</p>
                </div>
            </div>
        </div>

        <!-- Edit Profile Modal -->
        <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProfileModalLabel">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="profile-form">
                            <!-- Basic Information -->
                            <h6 class="text-primary mb-3"><i class="fas fa-user me-2"></i>Basic Information</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <!-- License Information -->
                            <h6 class="text-success mb-3 mt-4"><i class="fas fa-id-card me-2"></i>License Information</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="license_state" class="form-label">License State</label>
                                    <input type="text" class="form-control" id="license_state" name="license_state">
                                </div>
                                <div class="col-md-6">
                                    <label for="license_class" class="form-label">License Class</label>
                                    <input type="text" class="form-control" id="license_class" name="license_class">
                                </div>
                            </div>

                            <!-- Court Information -->
                            <h6 class="text-warning mb-3 mt-4"><i class="fas fa-gavel me-2"></i>Court & Citation</h6>
                            <div class="mb-3">
                                <label for="court_selected" class="form-label">Court Selected</label>
                                <input type="text" class="form-control" id="court_selected" name="court_selected">
                            </div>
                            <div class="mb-3">
                                <label for="citation_number" class="form-label">Citation Number</label>
                                <input type="text" class="form-control" id="citation_number" name="citation_number">
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="due_month" class="form-label">Due Month</label>
                                    <input type="number" class="form-control" id="due_month" name="due_month" min="1" max="12">
                                </div>
                                <div class="col-md-4">
                                    <label for="due_day" class="form-label">Due Day</label>
                                    <input type="number" class="form-control" id="due_day" name="due_day" min="1" max="31">
                                </div>
                                <div class="col-md-4">
                                    <label for="due_year" class="form-label">Due Year</label>
                                    <input type="number" class="form-control" id="due_year" name="due_year" min="2020" max="2030">
                                </div>
                            </div>

                            <!-- Security Questions -->
                            <h6 class="text-danger mb-3 mt-4"><i class="fas fa-shield-alt me-2"></i>Security Questions</h6>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important:</strong> These answers are used for identity verification during your course. Make sure they match exactly what you entered during registration.
                            </div>
                            
                            <div id="edit-security-questions">
                                <div class="text-center text-muted">
                                    <i class="fas fa-spinner fa-spin"></i> Loading security questions...
                                </div>
                            </div>

                            <!-- Agreement Information -->
                            <h6 class="text-info mb-3 mt-4"><i class="fas fa-file-contract me-2"></i>Agreement Information</h6>
                            <div class="mb-3">
                                <label for="agreement_name" class="form-label">Agreement Name</label>
                                <input type="text" class="form-control" id="agreement_name" name="agreement_name">
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms_agreement" name="terms_agreement" value="1">
                                    <label class="form-check-label" for="terms_agreement">
                                        I agree to the terms and conditions
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveProfileBtn">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let currentUser = null;
        let securityQuestions = {};

        // Load security questions from database
        async function loadSecurityQuestions() {
            try {
                const response = await fetch('/api/security/all-questions');
                if (response.ok) {
                    securityQuestions = await response.json();
                }
            } catch (error) {
                console.error('Error loading security questions:', error);
                // Fallback to empty object
                securityQuestions = {};
            }
        }

        // Load and populate security questions for edit modal
        async function loadEditSecurityQuestions() {
            try {
                const response = await fetch('/api/security/all-questions');
                if (response.ok) {
                    const questionsMap = await response.json();
                    populateEditSecurityQuestions(questionsMap);
                }
            } catch (error) {
                console.error('Error loading security questions for edit:', error);
                populateEditSecurityQuestionsFallback();
            }
        }

        function populateEditSecurityQuestions(questionsMap) {
            const container = document.getElementById('edit-security-questions');
            let html = '';
            
            // Show ALL questions in edit mode (both answered and unanswered)
            Object.keys(questionsMap).forEach((key, index) => {
                // Handle both old format (string) and new format (object with question property)
                const questionText = typeof questionsMap[key] === 'string' 
                    ? questionsMap[key] 
                    : questionsMap[key]?.question || 'Question not available';
                const answerType = typeof questionsMap[key] === 'object' 
                    ? questionsMap[key]?.answer_type || 'text'
                    : getInputTypeFromQuestion(questionText);
                    
                const fieldName = key; // security_q1, security_q2, etc.
                const pattern = getPatternFromType(answerType);
                const maxLength = getMaxLengthFromType(answerType);
                const title = getTitleFromType(answerType);
                
                // Get current answer (if any)
                const currentAnswer = (currentUser && currentUser[key]) ? currentUser[key] : '';
                const hasAnswer = currentAnswer && currentAnswer.trim() !== '';
                
                html += `
                    <div class="mb-3">
                        <label for="${fieldName}" class="form-label">
                            ${index + 1}. ${questionText}
                            ${hasAnswer ? '<span class="badge bg-success ms-2">Answered</span>' : '<span class="badge bg-secondary ms-2">Not Answered</span>'}
                        </label>
                        <input type="text" 
                               class="form-control ${hasAnswer ? 'border-success' : ''}" 
                               id="${fieldName}" 
                               name="${fieldName}" 
                               pattern="${pattern}"
                               maxlength="${maxLength}"
                               title="${title}"
                               data-input-type="${answerType}"
                               value="${currentAnswer}"
                               placeholder="${hasAnswer ? 'Current answer' : 'Enter your answer'}">
                        ${hasAnswer ? `<small class="form-text text-success">Current answer: ${currentAnswer}</small>` : '<small class="form-text text-muted">This question has not been answered yet</small>'}
                    </div>
                `;
            });
            
            container.innerHTML = html;
            
            // Add input validation
            setupEditSecurityValidation();
        }

        function populateEditSecurityQuestionsFallback() {
            const container = document.getElementById('edit-security-questions');
            container.innerHTML = `
                <div class="mb-3">
                    <label for="security_q1" class="form-label">1. When does your driver's license expire? (Year only, e.g., 2025)</label>
                    <input type="text" class="form-control" id="security_q1" name="security_q1" pattern="\\d{4}" maxlength="4" title="4 digits only">
                </div>
                <div class="mb-3">
                    <label for="security_q2" class="form-label">2. What is the weight listed on your driver's license? (Numbers only, e.g., 162)</label>
                    <input type="text" class="form-control" id="security_q2" name="security_q2" pattern="\\d+" title="Numbers only">
                </div>
            `;
            setupEditSecurityValidation();
        }

        function getInputTypeFromQuestion(question) {
            // If question is already a type (from answer_type), return it
            if (typeof question === 'string' && ['year', 'number', 'zip', 'text'].includes(question)) {
                return question;
            }
            
            const lowerQuestion = question.toLowerCase();
            if (lowerQuestion.includes('year') || lowerQuestion.includes('born')) return 'year';
            if (lowerQuestion.includes('weight') || lowerQuestion.includes('age') || lowerQuestion.includes('cars') || lowerQuestion.includes('digits')) return 'number';
            if (lowerQuestion.includes('zip')) return 'zip';
            return 'text';
        }

        function getPatternFromType(inputType) {
            switch(inputType) {
                case 'year': return '\\d{4}';
                case 'number': return '\\d+';
                case 'zip': return '\\d{5}';
                case 'text': return '[a-zA-Z\\s\'-]+';
                default: return '';
            }
        }

        function getMaxLengthFromType(inputType) {
            switch(inputType) {
                case 'year': return '4';
                case 'zip': return '5';
                case 'number': return '10';
                default: return '50';
            }
        }

        function getTitleFromType(inputType) {
            switch(inputType) {
                case 'year': return '4 digits only (e.g., 2025)';
                case 'number': return 'Numbers only';
                case 'zip': return '5 digits only';
                case 'text': return 'Letters, spaces, hyphens, and apostrophes only';
                default: return '';
            }
        }

        function setupEditSecurityValidation() {
            // Add real-time validation for security question inputs
            document.querySelectorAll('#edit-security-questions input').forEach(input => {
                input.addEventListener('input', function(e) {
                    const inputType = e.target.dataset.inputType;
                    let value = e.target.value;
                    
                    switch(inputType) {
                        case 'number':
                        case 'year':
                        case 'zip':
                            // Only allow digits
                            e.target.value = value.replace(/\D/g, '');
                            break;
                        case 'text':
                            // Only allow letters, spaces, hyphens, apostrophes
                            e.target.value = value.replace(/[^a-zA-Z\s\-']/g, '');
                            break;
                    }
                });
            });
        }

        async function loadProfile() {
            // Load security questions first
            await loadSecurityQuestions();
            
            try {
                const response = await fetch('/web/user', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    if (response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }
                    throw new Error('Failed to load profile');
                }
                
                currentUser = await response.json();
                displayProfile(currentUser);
                
            } catch (error) {
                console.error('Error loading profile:', error);
                showError('Error loading profile.');
            }
        }

        function displayProfile(user) {
            // Basic Information
            document.getElementById('basic-info').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="field-label">First Name:</span> <span class="field-value">${user.first_name || 'Not provided'}</span></p>
                        <p><span class="field-label">Last Name:</span> <span class="field-value">${user.last_name || 'Not provided'}</span></p>
                        <p><span class="field-label">Email:</span> <span class="field-value">${user.email || 'Not provided'}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="field-label">Role:</span> <span class="field-value">${user.role?.name || 'Student'}</span></p>
                        <p><span class="field-label">Status:</span> <span class="field-value badge ${user.status === 'active' ? 'bg-success' : 'bg-secondary'}">${user.status || 'Unknown'}</span></p>
                        <p><span class="field-label">Member Since:</span> <span class="field-value">${user.created_at ? new Date(user.created_at).toLocaleDateString() : 'Unknown'}</span></p>
                    </div>
                </div>
            `;

            // License Information
            document.getElementById('license-info').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="field-label">License State:</span> <span class="field-value">${user.license_state || 'Not provided'}</span></p>
                        <p><span class="field-label">License Class:</span> <span class="field-value">${user.license_class || 'Not provided'}</span></p>
                    </div>
                </div>
            `;

            // Court & Citation Information
            const dueDate = (user.due_month && user.due_day && user.due_year) 
                ? `${user.due_month}/${user.due_day}/${user.due_year}` 
                : 'Not provided';

            document.getElementById('court-info').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="field-label">Court Selected:</span> <span class="field-value">${user.court_selected || 'Not provided'}</span></p>
                        <p><span class="field-label">Citation Number:</span> <span class="field-value">${user.citation_number || 'Not provided'}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="field-label">Due Date:</span> <span class="field-value">${dueDate}</span></p>
                    </div>
                </div>
            `;

            // Security Questions - only show answered ones
            let securityHtml = '';
            const answeredSecurityQuestions = {};
            
            // Filter to only answered questions
            Object.keys(securityQuestions).forEach(key => {
                if (user[key] && user[key].trim() !== '') {
                    answeredSecurityQuestions[key] = securityQuestions[key];
                }
            });
            
            if (Object.keys(answeredSecurityQuestions).length === 0) {
                securityHtml = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No security questions have been answered yet. Security questions are typically set during registration.
                    </div>
                `;
            } else {
                Object.keys(answeredSecurityQuestions).forEach((key, index) => {
                    const answer = user[key] || 'Not answered';
                    // Handle both old format (string) and new format (object with question property)
                    const questionText = typeof answeredSecurityQuestions[key] === 'string' 
                        ? answeredSecurityQuestions[key] 
                        : answeredSecurityQuestions[key]?.question || 'Question not available';
                        
                    securityHtml += `
                        <div class="security-question">
                            <strong>Question ${index + 1}:</strong> ${questionText}<br>
                            <span class="field-label">Answer:</span> <span class="field-value">${answer}</span>
                        </div>
                    `;
                });
            }

            document.getElementById('security-questions-content').innerHTML = securityHtml;

            // Account Information
            document.getElementById('account-info').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="field-label">Agreement Name:</span> <span class="field-value">${user.agreement_name || 'Not provided'}</span></p>
                        <p><span class="field-label">Terms Agreement:</span> <span class="field-value badge ${user.terms_agreement ? 'bg-success' : 'bg-danger'}">${user.terms_agreement ? 'Accepted' : 'Not Accepted'}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="field-label">Registration Completed:</span> <span class="field-value">${user.registration_completed_at ? new Date(user.registration_completed_at).toLocaleDateString() : 'Not completed'}</span></p>
                        <p><span class="field-label">Last Updated:</span> <span class="field-value">${user.updated_at ? new Date(user.updated_at).toLocaleDateString() : 'Unknown'}</span></p>
                    </div>
                </div>
            `;

            // Fill edit form
            fillEditForm(user);
        }

        function fillEditForm(user) {
            const fields = [
                'first_name', 'last_name', 'email', 'license_state', 'license_class',
                'court_selected', 'citation_number', 'due_month', 'due_day', 'due_year',
                'security_q1', 'security_q2', 'security_q3', 'security_q4', 'security_q5',
                'security_q6', 'security_q7', 'security_q8', 'security_q9', 'security_q10',
                'agreement_name'
            ];

            fields.forEach(field => {
                const element = document.getElementById(field);
                if (element && user[field] !== undefined) {
                    element.value = user[field] || '';
                }
            });

            // Handle checkbox for terms agreement
            const termsCheckbox = document.getElementById('terms_agreement');
            if (termsCheckbox) {
                termsCheckbox.checked = user.terms_agreement || false;
            }
        }

        function showError(message) {
            const errorHtml = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>${message}</div>`;
            document.getElementById('basic-info').innerHTML = errorHtml;
            document.getElementById('license-info').innerHTML = errorHtml;
            document.getElementById('court-info').innerHTML = errorHtml;
            document.getElementById('security-questions-content').innerHTML = errorHtml;
            document.getElementById('account-info').innerHTML = errorHtml;
        }

        // Event Listeners
        document.getElementById('editProfileBtn').addEventListener('click', async function() {
            try {
                // Load security questions for the edit modal
                await loadEditSecurityQuestions();
                
                // Completely disable backdrop and use manual approach
                const modalElement = document.getElementById('editProfileModal');
                
                // Create modal instance with NO backdrop
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: 'static', // Prevent clicking outside to close
                    keyboard: false     // Prevent escape key
                });
                
                // Show modal
                modal.show();
                
                // Force remove any stuck backdrops after modal shows
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => {
                        backdrop.style.display = 'none';
                        backdrop.style.visibility = 'hidden';
                        backdrop.style.pointerEvents = 'none';
                    });
                }, 100);
                
            } catch (error) {
                console.error('Error opening edit modal:', error);
                alert('Error loading edit form. Please try again.');
            }
        });

        document.getElementById('saveProfileBtn').addEventListener('click', async function() {
            const form = document.getElementById('profile-form');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Convert numeric fields
            if (data.due_month) data.due_month = parseInt(data.due_month);
            if (data.due_day) data.due_day = parseInt(data.due_day);
            if (data.due_year) data.due_year = parseInt(data.due_year);

            // Handle checkbox for terms agreement
            data.terms_agreement = document.getElementById('terms_agreement').checked;

            // Filter out empty string values to avoid validation errors
            Object.keys(data).forEach(key => {
                if (data[key] === '' || data[key] === null || data[key] === undefined) {
                    delete data[key];
                }
            });

            // Debug: Log the data being sent
            console.log('Sending profile data:', data);

            try {
                const response = await fetch('/web/user', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });
                
                const responseData = await response.json();
                
                if (!response.ok) {
                    if (response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }
                    
                    console.error('Server error response:', responseData);
                    let errorMessage = 'Failed to update profile';
                    
                    if (responseData.error) {
                        errorMessage += ': ' + responseData.error;
                    }
                    if (responseData.errors) {
                        errorMessage += ': ' + Object.values(responseData.errors).flat().join(', ');
                    }
                    
                    window.alert(errorMessage);
                    return;
                }
                
                // Close modal using custom function
                closeEditModal();
                
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>Profile updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.container').insertBefore(alert, document.querySelector('.row'));
                
                // Reload profile data
                loadProfile();
                
            } catch (error) {
                console.error('Network error updating profile:', error);
                window.alert('Network error: Failed to update profile. Please check your connection and try again.');
            }
        });

        // Input validation for security questions
        function setupInputValidation() {
            const securityInputs = [
                { id: 'security_q1', type: 'number' },
                { id: 'security_q2', type: 'number' },
                { id: 'security_q3', type: 'number' },
                { id: 'security_q4', type: 'number' },
                { id: 'security_q5', type: 'number' },
                { id: 'security_q6', type: 'number' },
                { id: 'security_q7', type: 'zip' },
                { id: 'security_q8', type: 'year' },
                { id: 'security_q9', type: 'text' },
                { id: 'security_q10', type: 'text' }
            ];

            securityInputs.forEach(input => {
                const element = document.getElementById(input.id);
                if (element) {
                    element.addEventListener('input', function(e) {
                        let value = e.target.value;
                        
                        switch(input.type) {
                            case 'number':
                            case 'year':
                            case 'zip':
                                // Only allow digits
                                e.target.value = value.replace(/\D/g, '');
                                break;
                            case 'text':
                                // Only allow letters, spaces, hyphens, apostrophes
                                e.target.value = value.replace(/[^a-zA-Z\s\-']/g, '');
                                break;
                        }
                    });
                }
            });
        }

        // Custom function to close modal without backdrop issues
        function closeEditModal() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editProfileModal'));
            if (modal) {
                modal.hide();
            }
            
            // Force cleanup any remaining backdrops
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }, 50);
        }

        // Load profile when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadProfile();
            setupInputValidation();
            
            // Add close button handlers
            const editModal = document.getElementById('editProfileModal');
            const closeButtons = editModal.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
            
            closeButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    closeEditModal();
                });
            });
            
            // Handle escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && editModal.classList.contains('show')) {
                    closeEditModal();
                }
            });
            
            // Clean up when modal is hidden
            editModal.addEventListener('hidden.bs.modal', function () {
                // Additional cleanup
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
        });
    </script>
    
    @vite(['resources/js/app.js'])
    <x-footer />
</body>
</html>
