<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create your Account - Step 1</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f8f9fa; 
            color: #212529;
        }
        .container { max-width: 600px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #0d6efd; font-size: 28px; margin: 0; }
        .header p { color: #6c757d; margin: 10px 0 0 0; }
        .registration-form { background: white; padding: 40px; border-radius: 0.375rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; min-width: 0; }
        .form-group label { 
            display: block; 
            color: #212529; 
            font-weight: bold; 
            margin-bottom: 8px; 
            font-size: 14px;
        }
        .form-group input { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
            font-size: 16px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .password-wrapper {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6c757d;
            font-size: 16px;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .password-toggle:hover {
            color: #0d6efd;
        }
        .password-wrapper input {
            padding-right: 45px;
        }
        .note-section { 
            background: #fff3cd; 
            border: 1px solid #ffeaa7; 
            padding: 20px; 
            margin-top: 30px; 
            border-radius: 0.375rem; 
        }
        .note-section strong { color: #856404; }
        .note-text { color: #856404; margin-top: 10px; }
        .button-row { display: flex; justify-content: space-between; margin-top: 30px; }
        .btn { 
            padding: 12px 30px; 
            border: none; 
            border-radius: 0.375rem; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold;
            transition: background-color 0.15s ease-in-out;
        }
        .btn-next { 
            background: #0d6efd; 
            color: white; 
            width: 100%;
        }
        .btn-next:hover { background: #0b5ed7; }
        .btn-back { background: #6c757d; color: white; }
        .btn-back:hover { background: #5c636a; }
        .footer { text-align: center; margin-top: 30px; color: #6c757d; }
        .footer a { color: #0d6efd; text-decoration: none; }
        .footer a:hover { text-decoration: underline; }
        .error-message { color: #dc3545; font-size: 14px; margin-top: 5px; display: none; }
        .form-group.error input { border-color: #dc3545; }
        .validation-errors { background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 15px; border-radius: 0.375rem; margin-bottom: 20px; }
        .validation-errors ul { margin: 10px 0 0 20px; padding: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create your Account</h1>
            <p>Get started with your free account</p>
        </div>
        
        <form method="POST" action="{{ route('register.process', 1) }}">
            @csrf
            
            <!-- Store course enrollment params -->
            <input type="hidden" name="course_id" value="{{ request('course_id') }}">
            <input type="hidden" name="course_enroll" value="{{ request('course_enroll') }}">
            <input type="hidden" name="region" value="{{ request('region') }}">
            
            @if(session('error'))
                <div style="background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 15px; border-radius: 0.375rem; margin-bottom: 20px;">
                    <strong>Error:</strong> {{ session('error') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="validation-errors">
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @if(session('success'))
                <div style="background: #d1e7dd; border: 1px solid #badbcc; color: #0f5132; padding: 15px; border-radius: 0.375rem; margin-bottom: 20px;">
                    <strong>Success:</strong> {{ session('success') }}
                </div>
            @endif
            
            <div class="registration-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', session('registration_step_1.first_name')) }}" pattern="[a-zA-Z\s\-']+" title="Only letters, spaces, hyphens, and apostrophes allowed" required>
                        <div class="error-message">Only letters, spaces, hyphens, and apostrophes allowed</div>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', session('registration_step_1.last_name')) }}" pattern="[a-zA-Z\s\-']+" title="Only letters, spaces, hyphens, and apostrophes allowed" required>
                        <div class="error-message">Only letters, spaces, hyphens, and apostrophes allowed</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email/Login ID</label>
                        <input type="email" id="email" name="email" value="{{ old('email', session('registration_step_1.email')) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="email_confirmation">Re Enter Email</label>
                        <input type="email" id="email_confirmation" name="email_confirmation" value="{{ old('email_confirmation', session('registration_step_1.email_confirmation')) }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye" id="password-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Retype Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password_confirmation" name="password_confirmation" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                <i class="fas fa-eye" id="password_confirmation-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="button-row">
                    <button type="submit" class="btn btn-next">Next</button>
                </div>
            </div>
        </form>
        
        <div class="note-section">
            <strong>Password Guidelines</strong>
            <div class="note-text">
                <strong>Note: Your Password must meet the following criteria:</strong><br>
                1) at least eight characters in length<br>
                2) contain upper and lower case characters<br>
                3) contain at least one numeric character<br>
                4) contain at least one special character.<br>
                Acceptable special characters are: ! @ # $ & * ( )<br><br>
                <strong>It is recommended that your Password meet the following criteria:</strong><br>
                5) does not contain words found in a dictionary<br>
                6) should not contain names of pets, family, etc.<br>
                7) does not match a previous password
            </div>
        </div>
        
        <div class="note-section" style="background: #fff3cd; border: 1px solid #ffeaa7; margin-top: 20px;">
            <strong style="color: #0066cc; font-size: 18px;">Note: The Login ID and Password are BOTH Case Sensitive</strong>
        </div>
        
        <div class="note-section">
            <strong>Note:</strong>
            <div class="note-text">
                The student is responsible to ensure completion of the course is accepted by the entity for which you are taking the course i.e. Courthouse, DMV, Insurance Company, etc.
            </div>
        </div>
        
        <div class="footer">
            Have an account? <a href="/login">Sign In</a>
        </div>
    </div>
    
    <script src="/js/csrf-handler.js"></script>
    <script>
        // Password toggle functionality
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(fieldId + '-eye');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
        
        // Real-time validation for name fields
        document.getElementById('first_name').addEventListener('input', function(e) {
            const value = e.target.value;
            const regex = /^[a-zA-Z\s\-']*$/;
            const parent = e.target.closest('.form-group');
            const errorMsg = parent.querySelector('.error-message');
            
            if (!regex.test(value)) {
                parent.classList.add('error');
                errorMsg.style.display = 'block';
                e.target.value = value.replace(/[^a-zA-Z\s\-']/g, '');
            } else {
                parent.classList.remove('error');
                errorMsg.style.display = 'none';
            }
        });
        
        document.getElementById('last_name').addEventListener('input', function(e) {
            const value = e.target.value;
            const regex = /^[a-zA-Z\s\-']*$/;
            const parent = e.target.closest('.form-group');
            const errorMsg = parent.querySelector('.error-message');
            
            if (!regex.test(value)) {
                parent.classList.add('error');
                errorMsg.style.display = 'block';
                e.target.value = value.replace(/[^a-zA-Z\s\-']/g, '');
            } else {
                parent.classList.remove('error');
                errorMsg.style.display = 'none';
            }
        });
        
        // Password validation
        document.getElementById('password').addEventListener('input', function(e) {
            const value = e.target.value;
            const hasLower = /[a-z]/.test(value);
            const hasUpper = /[A-Z]/.test(value);
            const hasNumber = /[0-9]/.test(value);
            const hasSpecial = /[@$!%*#?&()]/.test(value);
            const isLongEnough = value.length >= 8;
            
            if (!hasLower || !hasUpper || !hasNumber || !hasSpecial || !isLongEnough) {
                e.target.style.borderColor = '#ffc107';
            } else {
                e.target.style.borderColor = '#516425';
            }
        });
    </script>
</body>
</html>
