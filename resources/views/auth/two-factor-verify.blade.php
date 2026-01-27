<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two-Factor Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #516425, #6b8332);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 400px;
            width: 100%;
        }
        .card-header {
            background: white;
            border-radius: 15px 15px 0 0;
            text-align: center;
            padding: 2rem 1.5rem 1rem;
        }
        .card-body {
            padding: 1.5rem;
        }
        .code-input {
            font-size: 1.5rem;
            text-align: center;
            letter-spacing: 0.5rem;
            font-family: 'Courier New', monospace;
            border: 2px solid #516425;
            border-radius: 8px;
            padding: 1rem;
        }
        .code-input:focus {
            border-color: #6b8332;
            box-shadow: 0 0 0 0.2rem rgba(81, 100, 37, 0.25);
        }
        .btn-verify {
            background: #516425;
            border-color: #516425;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        .btn-verify:hover {
            background: #3d4b1c;
            border-color: #3d4b1c;
        }
        .btn-resend {
            color: #516425;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-resend:hover {
            color: #3d4b1c;
            text-decoration: underline;
        }
        .timer {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #516425;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                        <h3 class="mb-0">Two-Factor Authentication</h3>
                        <p class="text-muted mb-0">Enter the verification code sent to your email</p>
                    </div>
                    <div class="card-body">
                        <div id="alert-container"></div>
                        
                        <form id="verifyForm">
                            <div class="mb-4">
                                <label for="code" class="form-label">Verification Code</label>
                                <input type="text" 
                                       class="form-control code-input" 
                                       id="code" 
                                       name="code" 
                                       maxlength="6" 
                                       placeholder="000000"
                                       autocomplete="off"
                                       required>
                                <div class="form-text">
                                    <i class="fas fa-clock me-1"></i>
                                    Code expires in: <span id="timer" class="timer">--:--</span>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-verify text-white" id="verifyBtn">
                                    <i class="fas fa-check me-2"></i>Verify Code
                                </button>
                                
                                <button type="button" class="btn btn-link btn-resend" id="resendBtn" onclick="resendCode()">
                                    <i class="fas fa-redo me-1"></i>Resend Code
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                Didn't receive the code? Check your spam folder or 
                                <a href="#" onclick="resendCode()" class="btn-resend">request a new one</a>
                            </small>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="/logout" class="text-muted">
                                <i class="fas fa-sign-out-alt me-1"></i>Sign out
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let timerInterval;
        let expiresAt;

        document.getElementById('verifyForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const code = document.getElementById('code').value;
            const btn = document.getElementById('verifyBtn');
            
            if (code.length !== 6) {
                showAlert('Please enter a 6-digit code', 'danger');
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';
            
            try {
                const response = await fetch('/two-factor/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ code })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showAlert('Verification successful! Redirecting...', 'success');
                    setTimeout(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = '/dashboard';
                        }
                    }, 1500);
                } else {
                    showAlert(data.error || 'Verification failed', 'danger');
                }
            } catch (error) {
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Verify Code';
            }
        });

        async function resendCode() {
            const btn = document.getElementById('resendBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';
            
            try {
                const response = await fetch('/two-factor/send', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showAlert('New verification code sent to your email', 'success');
                    startTimer(data.expires_in * 60); // Convert minutes to seconds
                } else {
                    showAlert(data.error || 'Failed to send code', 'danger');
                }
            } catch (error) {
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-redo me-1"></i>Resend Code';
            }
        }

        function showAlert(message, type) {
            const container = document.getElementById('alert-container');
            container.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }

        function startTimer(seconds) {
            clearInterval(timerInterval);
            expiresAt = Date.now() + (seconds * 1000);
            
            timerInterval = setInterval(() => {
                const remaining = Math.max(0, expiresAt - Date.now());
                const minutes = Math.floor(remaining / 60000);
                const secs = Math.floor((remaining % 60000) / 1000);
                
                document.getElementById('timer').textContent = 
                    `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                
                if (remaining <= 0) {
                    clearInterval(timerInterval);
                    showAlert('Verification code has expired. Please request a new one.', 'warning');
                }
            }, 1000);
        }

        // Auto-format code input
        document.getElementById('code').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 6);
        });

        // Load initial status
        async function loadStatus() {
            try {
                const response = await fetch('/two-factor/status');
                const data = await response.json();
                
                if (data.code_expires_at) {
                    const expiresAt = new Date(data.code_expires_at);
                    const remaining = Math.max(0, expiresAt - new Date());
                    if (remaining > 0) {
                        startTimer(Math.floor(remaining / 1000));
                    }
                }
            } catch (error) {
                console.error('Failed to load 2FA status:', error);
            }
        }

        loadStatus();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>