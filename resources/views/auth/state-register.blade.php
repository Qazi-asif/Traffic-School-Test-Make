<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ucfirst($state) }} Traffic School - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        
        /* State-specific color schemes */
        .florida-theme { background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); }
        .missouri-theme { background: linear-gradient(135deg, #c41e3a 0%, #8b0000 100%); }
        .texas-theme { background: linear-gradient(135deg, #bf5700 0%, #333f48 100%); }
        .delaware-theme { background: linear-gradient(135deg, #006847 0%, #ffd700 100%); }
        
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
        }
        
        .state-header {
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .florida-header { background: linear-gradient(135deg, #ff6b35, #f7931e); }
        .missouri-header { background: linear-gradient(135deg, #c41e3a, #8b0000); }
        .texas-header { background: linear-gradient(135deg, #bf5700, #333f48); }
        .delaware-header { background: linear-gradient(135deg, #006847, #2d5016); }
        
        .state-logo {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .register-form {
            padding: 40px;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--state-color);
            box-shadow: 0 0 0 0.2rem rgba(var(--state-color-rgb), 0.25);
        }
        
        .btn-state {
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .florida-btn { background: linear-gradient(135deg, #ff6b35, #f7931e); }
        .missouri-btn { background: linear-gradient(135deg, #c41e3a, #8b0000); }
        .texas-btn { background: linear-gradient(135deg, #bf5700, #333f48); }
        .delaware-btn { background: linear-gradient(135deg, #006847, #2d5016); }
        
        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6c757d;
            font-size: 16px;
        }
        
        .password-wrapper input {
            padding-right: 45px;
        }
        
        .state-links {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .state-links a {
            color: var(--state-color);
            text-decoration: none;
        }
        
        .state-links a:hover {
            text-decoration: underline;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            color: var(--state-color);
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }
    </style>
</head>
<body class="{{ $state }}-theme">
    <div class="register-container">
        <div class="register-card">
            <!-- State Header -->
            <div class="state-header {{ $state }}-header">
                <div class="state-logo">
                    @switch($state)
                        @case('florida')
                            🌴
                            @break
                        @case('missouri')
                            🏛️
                            @break
                        @case('texas')
                            🤠
                            @break
                        @case('delaware')
                            🏖️
                            @break
                    @endswitch
                </div>
                <h2 class="mb-0">{{ ucfirst($state) }} Traffic School</h2>
                <p class="mb-0 opacity-75">Create Your Student Account</p>
            </div>
            
            <!-- Registration Form -->
            <div class="register-form">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('auth.register', $state) }}">
                    @csrf
                    
                    <!-- Personal Information -->
                    <div class="form-section">
                        <h5 class="section-title">Personal Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label fw-semibold">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="{{ old('first_name') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label fw-semibold">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="{{ old('last_name') }}" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="{{ old('email') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="{{ old('phone') }}" placeholder="(555) 123-4567">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Security -->
                    <div class="form-section">
                        <h5 class="section-title">Account Security</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label fw-semibold">Password *</label>
                                <div class="password-wrapper">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label fw-semibold">Confirm Password *</label>
                                <div class="password-wrapper">
                                    <input type="password" class="form-control" id="password_confirmation" 
                                           name="password_confirmation" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="password_confirmation-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- License Information -->
                    <div class="form-section">
                        <h5 class="section-title">License Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="driver_license" class="form-label fw-semibold">Driver's License Number</label>
                                <input type="text" class="form-control" id="driver_license" name="driver_license" 
                                       value="{{ old('driver_license') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label fw-semibold">License State *</label>
                                <select class="form-select" id="state" name="state" required>
                                    <option value="">Select State</option>
                                    <option value="{{ $state }}" {{ old('state') == $state ? 'selected' : '' }}>
                                        {{ ucfirst($state) }}
                                    </option>
                                    <option value="AL" {{ old('state') == 'AL' ? 'selected' : '' }}>Alabama</option>
                                    <option value="AK" {{ old('state') == 'AK' ? 'selected' : '' }}>Alaska</option>
                                    <option value="AZ" {{ old('state') == 'AZ' ? 'selected' : '' }}>Arizona</option>
                                    <option value="AR" {{ old('state') == 'AR' ? 'selected' : '' }}>Arkansas</option>
                                    <option value="CA" {{ old('state') == 'CA' ? 'selected' : '' }}>California</option>
                                    <option value="CO" {{ old('state') == 'CO' ? 'selected' : '' }}>Colorado</option>
                                    <option value="CT" {{ old('state') == 'CT' ? 'selected' : '' }}>Connecticut</option>
                                    <option value="DE" {{ old('state') == 'DE' ? 'selected' : '' }}>Delaware</option>
                                    <option value="FL" {{ old('state') == 'FL' ? 'selected' : '' }}>Florida</option>
                                    <option value="GA" {{ old('state') == 'GA' ? 'selected' : '' }}>Georgia</option>
                                    <!-- Add more states as needed -->
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="citation_number" class="form-label fw-semibold">Citation/Ticket Number</label>
                            <input type="text" class="form-control" id="citation_number" name="citation_number" 
                                   value="{{ old('citation_number') }}" placeholder="Optional">
                            <small class="text-muted">Enter if you have a traffic citation</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-state {{ $state }}-btn text-white w-100">
                        <i class="fas fa-user-plus me-2"></i>Create {{ ucfirst($state) }} Account
                    </button>
                </form>
            </div>
            
            <!-- State Links -->
            <div class="state-links">
                <div>
                    Already have an account? 
                    <a href="{{ route('auth.login.form', $state) }}">
                        <i class="fas fa-sign-in-alt me-1"></i>Login Here
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set CSS custom properties for state colors
        const stateColors = {
            florida: { color: '#ff6b35', rgb: '255, 107, 53' },
            missouri: { color: '#c41e3a', rgb: '196, 30, 58' },
            texas: { color: '#bf5700', rgb: '191, 87, 0' },
            delaware: { color: '#006847', rgb: '0, 104, 71' }
        };
        
        const state = '{{ $state }}';
        if (stateColors[state]) {
            document.documentElement.style.setProperty('--state-color', stateColors[state].color);
            document.documentElement.style.setProperty('--state-color-rgb', stateColors[state].rgb);
        }
        
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
    </script>
</body>
</html>