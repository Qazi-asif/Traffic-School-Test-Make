<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create your Account - Step 3</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f8f9fa; 
            color: #212529;
        }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #0d6efd; font-size: 28px; margin: 0; }
        .header p { color: #6c757d; margin: 10px 0 0 0; }
        .registration-form { background: white; padding: 40px; border-radius: 0.375rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
        .info-text { color: #6c757d; margin-bottom: 20px; line-height: 1.6; }
        .warning-text { 
            color: #dc3545; 
            font-weight: bold; 
            margin: 20px 0; 
            padding: 15px; 
            background: #f8d7da; 
            border: 1px solid #f5c6cb; 
            border-radius: 0.375rem; 
        }
        .question-row { display: flex; margin-bottom: 15px; align-items: center; }
        .question-number { color: #0d6efd; font-weight: bold; width: 30px; }
        .question-text { color: #212529; flex: 1; margin-right: 20px; }
        .answer-input { 
            width: 200px; 
            padding: 8px 12px; 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .answer-input:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .button-row { display: flex; justify-content: space-between; margin-top: 30px; gap: 20px; }
        .btn { 
            padding: 12px 30px; 
            border: none; 
            border-radius: 0.375rem; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold;
            text-decoration: none; 
            display: inline-block;
            transition: background-color 0.15s ease-in-out;
            flex: 1;
            text-align: center;
        }
        .btn-continue { background: #0d6efd; color: white; }
        .btn-continue:hover { background: #0b5ed7; }
        .btn-back { background: #6c757d; color: white; }
        .btn-back:hover { background: #5c636a; }
        .validation-errors { background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 15px; border-radius: 0.375rem; margin-bottom: 20px; }
        .validation-errors ul { margin: 10px 0 0 20px; padding: 0; }
        .loading { text-align: center; padding: 40px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verification of Student Identity</h1>
            <p>Step 3 of 4 - Security questions for identity verification</p>
        </div>
        
        <form method="POST" action="{{ route('register.process', 3) }}" id="registrationForm">
            @csrf
            
            @if(session('error'))
                <div style="background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 15px; border-radius: 0.375rem; margin-bottom: 20px;">
                    <strong>Error:</strong> {{ session('error') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="validation-errors" style="background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 20px; border-radius: 0.375rem; margin-bottom: 20px;">
                    <strong style="font-size: 16px; display: block; margin-bottom: 15px;">❌ Please fix the following errors:</strong>
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li style="margin-bottom: 8px; line-height: 1.5;">{{ $error }}</li>
                        @endforeach
                    </ul>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #f5c2c7; font-size: 14px;">
                        <strong>Quick Reference:</strong>
                        <ul style="margin: 10px 0 0 20px; padding: 0;">
                            <li><strong>Q1 (License Expiration):</strong> Must be exactly 4 digits (e.g., 2025)</li>
                            <li><strong>Q2 (Weight):</strong> Numbers only, up to 10 digits (e.g., 162)</li>
                            <li><strong>Q3 (Number of Cars):</strong> Numbers only, up to 5 digits (e.g., 1)</li>
                            <li><strong>Q4 (Last 4 License Digits):</strong> Must be exactly 4 digits (e.g., 6374)</li>
                            <li><strong>Q5 (Your Age):</strong> Numbers only, up to 3 digits (e.g., 31)</li>
                            <li><strong>Q6 (Age Got License):</strong> Numbers only, up to 3 digits (e.g., 16)</li>
                            <li><strong>Q7 (Zip Code):</strong> Must be exactly 5 digits (e.g., 90210)</li>
                            <li><strong>Q8 (Birth Year):</strong> Must be exactly 4 digits (e.g., 1980)</li>
                            <li><strong>Q9 (Hair Color):</strong> Letters only (e.g., black, brown, blonde)</li>
                            <li><strong>Q10 (City):</strong> Letters only (e.g., New York, Los Angeles)</li>
                        </ul>
                    </div>
                </div>
            @endif
            
            <div class="registration-form">
                <div class="info-text">
                    We are required by the DMV/Courts to include various identification checkpoints throughout the course. These checkpoints enable us to verify the identity of the test taker. The individual who takes and completes the course must be the same person who begins the course. The information below will be used in each checkpoint. Please remember your answers to the questions below, they will be the same questions on the verification checkpoints. If you answer any question wrong, your account will be locked and you'll have to contact us for ID verification and account unlock. When registering to take this course, you are saying to the court that you are the ticket holder and have elected to traffic school.
                </div>
                
                <div class="info-text">
                    The information we are asking you below is only to verify that the person that started the course is the person that will be finishing the course. This information will not be shared with the courts or any third party.
                </div>
                
                <div class="warning-text">
                    YOU MUST ENTER THESE ANSWERS ON THE COURSE EXACTLY AS THEY APPEAR BELOW. IF ANSWER INCORRECTLY YOU WILL BE LOCKED OUT OF YOUR ACCOUNT AND BE REQUIRED TO CONTACT CUSTOMER SUPPORT. PLEASE WRITE THEM DOWN.
                </div>
                
                <div id="questions-container">
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i> Loading security questions...
                    </div>
                </div>
                
                <div class="button-row">
                    <a href="{{ route('register.step', 2) }}" class="btn btn-back">Back</a>
                    <button type="submit" class="btn btn-continue">Continue</button>
                </div>
            </div>
        </form>
    </div>
    
    <script src="/js/csrf-handler.js"></script>
    <script>
        let securityQuestions = [];
        
        // Load security questions from database
        async function loadSecurityQuestions() {
            console.log('Starting to load security questions...');
            console.log('Current URL:', window.location.href);
            console.log('Base URL:', window.location.origin);
            
            try {
                // Try multiple URL variations to handle different environments
                const possibleUrls = [
                    '/api/security/all-questions',
                    window.location.origin + '/api/security/all-questions',
                    'api/security/all-questions'
                ];
                
                let response = null;
                let workingUrl = null;
                
                for (const url of possibleUrls) {
                    console.log(`Trying URL: ${url}`);
                    try {
                        response = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        console.log(`Response for ${url}:`, {
                            status: response.status,
                            statusText: response.statusText,
                            ok: response.ok,
                            headers: Object.fromEntries([...response.headers.entries()])
                        });
                        
                        if (response.ok) {
                            workingUrl = url;
                            break;
                        }
                    } catch (fetchError) {
                        console.error(`Fetch error for ${url}:`, fetchError);
                        continue;
                    }
                }
                
                if (!response || !response.ok) {
                    console.error('All URL attempts failed, using fallback questions');
                    displayFallbackQuestions();
                    return;
                }
                
                console.log(`Successfully connected using: ${workingUrl}`);
                
                const responseText = await response.text();
                console.log('Raw response length:', responseText.length);
                console.log('Raw response preview:', responseText.substring(0, 200));
                console.log('Response content type:', response.headers.get('content-type'));
                
                // Check if response looks like HTML (error page)
                if (responseText.trim().startsWith('<!DOCTYPE') || responseText.trim().startsWith('<html')) {
                    console.error('Received HTML instead of JSON - likely an error page');
                    console.log('Full HTML response:', responseText);
                    displayFallbackQuestions();
                    return;
                }
                
                // Try to parse as JSON
                let questionsMap;
                try {
                    questionsMap = JSON.parse(responseText);
                    console.log('Successfully parsed JSON:', questionsMap);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.log('Failed to parse response as JSON, using fallback');
                    displayFallbackQuestions();
                    return;
                }
                
                // Validate the response structure
                if (!questionsMap || typeof questionsMap !== 'object') {
                    console.error('Invalid response structure:', typeof questionsMap);
                    displayFallbackQuestions();
                    return;
                }
                
                // Check if we got any questions
                const questionKeys = Object.keys(questionsMap);
                console.log('Question keys found:', questionKeys);
                
                if (questionKeys.length === 0) {
                    console.warn('No questions found in response, using fallback');
                    displayFallbackQuestions();
                    return;
                }
                
                // Convert to array format with proper field names
                securityQuestions = questionKeys.map((key, index) => {
                    // Extract the question key (e.g., 'q1' from 'security_q1')
                    const questionKey = key.replace('security_', '');
                    const questionData = questionsMap[key];
                    
                    // Handle both old format (string) and new format (object with question and answer_type)
                    if (typeof questionData === 'string') {
                        return {
                            key: key,
                            question: questionData,
                            answer_type: null,
                            fieldName: questionKey
                        };
                    } else if (typeof questionData === 'object' && questionData.question) {
                        return {
                            key: key,
                            question: questionData.question,
                            answer_type: questionData.answer_type,
                            fieldName: questionKey
                        };
                    }
                });
                
                console.log('Processed security questions:', securityQuestions);
                displayQuestions();
                
            } catch (error) {
                console.error('Unexpected error loading security questions:', error);
                console.error('Error stack:', error.stack);
                displayFallbackQuestions();
            }
        }
        
        function displayQuestions() {
            const container = document.getElementById('questions-container');
            let html = '';
            
            securityQuestions.forEach((q, index) => {
                const oldValue = getOldValue(q.fieldName);
                // Use answer_type from API if available, otherwise guess from question text
                const inputType = q.answer_type ? (q.answer_type === 'number' ? 'number' : 'text') : getInputType(q.question);
                const pattern = getPattern(inputType);
                const maxLength = getMaxLength(inputType, q.question);
                const title = getTitle(inputType);
                
                html += `
                    <div class="question-row">
                        <div class="question-number">${index + 1}.</div>
                        <div class="question-text">${q.question}</div>
                        <input type="text" 
                               name="${q.fieldName}" 
                               class="answer-input" 
                               data-type="${inputType}"
                               pattern="${pattern}"
                               maxlength="${maxLength}"
                               title="${title}"
                               value="${oldValue}"
                               required>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            setupValidation();
        }
        
        function displayFallbackQuestions() {
            // Fallback to hardcoded questions if API fails
            const container = document.getElementById('questions-container');
            container.innerHTML = `
                <div class="question-row">
                    <div class="question-number">1.</div>
                    <div class="question-text">When does your driver's license expire? (Year only, e.g., 2025)</div>
                    <input type="text" name="q1" class="answer-input" data-type="year" pattern="\\d{4}" maxlength="4" title="4 digits only" value="{{ old('q1', session('registration_step_3.q1')) }}" required>
                    @error('q1')<span class="error-text" style="color: #dc3545; font-size: 12px; display: block; margin-top: 5px;">⚠️ {{ $message }}</span>@enderror
                </div>
                <div class="question-row">
                    <div class="question-number">2.</div>
                    <div class="question-text">What is the weight listed on your driver's license? (Numbers only, e.g., 162)</div>
                    <input type="text" name="q2" class="answer-input" data-type="number" pattern="\\d+" maxlength="10" title="Numbers only" value="{{ old('q2', session('registration_step_3.q2')) }}" required>
                    @error('q2')<span class="error-text" style="color: #dc3545; font-size: 12px; display: block; margin-top: 5px;">⚠️ {{ $message }}</span>@enderror
                </div>
                <div class="question-row">
                    <div class="question-number">3.</div>
                    <div class="question-text">How many cars do you own? (Numbers only, e.g., 1)</div>
                    <input type="text" name="q3" class="answer-input" data-type="number" pattern="\\d+" maxlength="10" title="Numbers only" value="{{ old('q3', session('registration_step_3.q3')) }}" required>
                    @error('q3')<span class="error-text" style="color: #dc3545; font-size: 12px; display: block; margin-top: 5px;">⚠️ {{ $message }}</span>@enderror
                </div>
                <div class="question-row">
                    <div class="question-number">4.</div>
                    <div class="question-text">What are the last four digits of your Driver's License Number? (e.g., 6374)</div>
                    <input type="text" name="q4" class="answer-input" data-type="number" pattern="\\d{4}" maxlength="4" title="4 digits only" value="{{ old('q4', session('registration_step_3.q4')) }}" required>
                    @error('q4')<span class="error-text" style="color: #dc3545; font-size: 12px; display: block; margin-top: 5px;">⚠️ {{ $message }}</span>@enderror
                </div>
                <div class="question-row">
                    <div class="question-number">5.</div>
                    <div class="question-text">What is your age? (Numbers only, e.g., 31)</div>
                    <input type="text" name="q5" class="answer-input" data-type="number" pattern="\\d+" maxlength="3" title="Numbers only" value="{{ old('q5', session('registration_step_3.q5')) }}" required>
                    @error('q5')<span class="error-text" style="color: #dc3545; font-size: 12px; display: block; margin-top: 5px;">⚠️ {{ $message }}</span>@enderror
                </div>
                <div class="question-row">
                    <div class="question-number">6.</div>
                    <div class="question-text">How old were you when you got your Driver's License? (Numbers only, e.g., 16)</div>
                    <input type="text" name="q6" class="answer-input" data-type="number" pattern="\\d+" maxlength="3" title="Numbers only" value="{{ old('q6', session('registration_step_3.q6')) }}" required>
                    @error('q6')<span class="error-text" style="color: #dc3545; font-size: 12px; display: block; margin-top: 5px;">⚠️ {{ $message }}</span>@enderror
                </div>
                <div class="question-row">
                    <div class="question-number">7.</div>
                    <div class="question-text">What zip code do you live in? (e.g., 90210)</div>
                    <input type="text" name="q7" class="answer-input" data-type="zip" pattern="\\d{5}" maxlength="5" title="5 digits only" value="{{ old('q7', session('registration_step_3.q7')) }}" required>
                    @error('q7')<span class="error-text" style="color: #dc3545; font-size: 12px; display: block; margin-top: 5px;">⚠️ {{ $message }}</span>@enderror
                </div>
                <div class="question-row">
                    <div class="question-number">8.</div>
                    <div class="question-text">In what year were you born? (e.g., 1980)</div>
                    <input type="text" name="q8" class="answer-input" data-type="year" pattern="\\d{4}" maxlength="4" title="4 digits only" value="{{ old('q8', session('registration_step_3.q8')) }}" required>
                    @error('q8')<span class="error-text" style="color: #dc3545; font-size: 12px; display: block; margin-top: 5px;">⚠️ {{ $message }}</span>@enderror
                </div>
                <div class="question-row">
                    <div class="question-number">9.</div>
                    <div class="question-text">What color is your hair?</div>
                    <input type="text" name="q9" class="answer-input" data-type="text" pattern="[a-zA-Z\\s\\-']+" maxlength="50" title="Letters, spaces, hyphens, and apostrophes only" value="{{ old('q9', session('registration_step_3.q9')) }}" required>
                    @error('q9')<span class="error-text" style="color: #dc3545; font-size: 12px; display: block; margin-top: 5px;">⚠️ {{ $message }}</span>@enderror
                </div>
                <div class="question-row">
                    <div class="question-number">10.</div>
                    <div class="question-text">What city do you live in?</div>
                    <input type="text" name="q10" class="answer-input" data-type="text" pattern="[a-zA-Z\\s\\-']+" maxlength="50" title="Letters, spaces, hyphens, and apostrophes only" value="{{ old('q10', session('registration_step_3.q10')) }}" required>
                    @error('q10')<span class="error-text" style="color: #dc3545; font-size: 12px; display: block; margin-top: 5px;">⚠️ {{ $message }}</span>@enderror
                </div>
            `;
            setupValidation();
        }
        
        function getOldValue(fieldName) {
            // Get old values from Laravel's old() helper or session
            const oldValues = @json(old() ?: session('registration_step_3', []));
            return oldValues[fieldName] || '';
        }
        
        function getInputType(question) {
            const lowerQuestion = question.toLowerCase();
            if (lowerQuestion.includes('year') || lowerQuestion.includes('born')) return 'year';
            if (lowerQuestion.includes('weight') || lowerQuestion.includes('age') || lowerQuestion.includes('cars') || lowerQuestion.includes('digits')) return 'number';
            if (lowerQuestion.includes('zip')) return 'zip';
            return 'text';
        }
        
        function getPattern(inputType) {
            switch(inputType) {
                case 'year': return '[0-9]{4}';
                case 'number': return '[0-9]+';
                case 'zip': return '[0-9]{5}';
                case 'text': return "[a-zA-Z\\s'-]+";
                default: return '';
            }
        }
        
        function getMaxLength(inputType, question = '') {
            const lowerQuestion = question.toLowerCase();
            switch(inputType) {
                case 'year': return '4';
                case 'zip': return '5';
                case 'number': 
                    // Age fields should be max 3 digits
                    if (lowerQuestion.includes('age')) return '3';
                    // Weight and cars can be longer
                    return '10';
                default: return '50';
            }
        }
        
        function getTitle(inputType) {
            switch(inputType) {
                case 'year': return '4 digits only (e.g., 2025)';
                case 'number': return 'Numbers only';
                case 'zip': return '5 digits only';
                case 'text': return 'Letters, spaces, hyphens, and apostrophes only';
                default: return '';
            }
        }
        
        function setupValidation() {
            // Real-time validation for all answer inputs
            document.querySelectorAll('.answer-input').forEach(input => {
                input.addEventListener('input', function(e) {
                    const type = e.target.dataset.type;
                    let value = e.target.value;
                    
                    switch(type) {
                        case 'number':
                        case 'year':
                        case 'zip':
                            // Only allow digits
                            e.target.value = value.replace(/[^0-9]/g, '');
                            break;
                        case 'text':
                            // Only allow letters, spaces, hyphens, apostrophes
                            e.target.value = value.replace(/[^a-zA-Z\s'-]/g, '');
                            break;
                    }
                });
            });
            
            // Add form submission handler
            const form = document.getElementById('registrationForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('Form submitted');
                    // Allow form to submit normally
                });
            }
        }
        
        // Load questions when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, loading security questions');
            loadSecurityQuestions();
            
            // Add form submit listener
            const form = document.getElementById('registrationForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('Form submit event fired');
                    console.log('Form data:', new FormData(form));
                });
            }
        });
    </script>
</body>
</html>
