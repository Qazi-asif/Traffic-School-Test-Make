<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ucfirst($state) }} Traffic School - Login</title>
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
        .florida-theme {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        }
        .missouri-theme {
            background: linear-gradient(135deg, #c41e3a 0%, #8b0000 100%);
        }
        .texas-theme {
            background: linear-gradient(135deg, #bf5700 0%, #333f48 100%);
        }
        .delaware-theme {
            background: linear-gradient(135deg, #006847 0%, #ffd700 100%);
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 450px;
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
        
        .login-form {
            padding: 40px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
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
    </style>
</head>
<body class="{{ $state }}-theme">
    <div class="login-container">
        <div class="login-card">
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
                <p class="mb-0 opacity-75">Student Portal Login</p>
            </div>
            
            <!-- Login Form -->
            <div class="login-form">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('auth.login', $state) }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="{{ old('email') }}" required autocomplete="email" autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="password" name="password" 
                                   required autocomplete="current-password">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="password-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-state {{ $state }}-btn text-white w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Login to {{ ucfirst($state) }} Portal
                    </button>
                </form>
            </div>
            
            <!-- State Links -->
            <div class="state-links">
                <div class="mb-2">
                    <a href="{{ route('auth.register.form', $state) }}">
                        <i class="fas fa-user-plus me-1"></i>Create New Account
                    </a>
                </div>
                <div class="mb-2">
                    <a href="{{ route('password.request') }}">
                        <i class="fas fa-key me-1"></i>Forgot Password?
                    </a>
                </div>
                <div>
                    <small class="text-muted">
                        Need help? Contact {{ ucfirst($state) }} Traffic School Support
                    </small>
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
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('password-eye');
            
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