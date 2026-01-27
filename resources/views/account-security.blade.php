<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Account Security - Traffic School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <link href="/css/mobile-responsive.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
        }
        
        .security-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .security-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: var(--accent);
        }
        
        .security-header {
            background: linear-gradient(135deg, var(--accent), var(--hover));
            color: white;
            padding: 1.25rem;
            border-radius: 12px 12px 0 0;
            position: relative;
            overflow: hidden;
        }
        
        .security-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        .security-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .security-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        
        .security-body {
            padding: 1.25rem;
        }
        
        .security-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .status-secure { background: #28a745; }
        .status-warning { background: #ffc107; }
        .status-danger { background: #dc3545; }
        
        .security-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .password-strength {
            height: 8px;
            border-radius: 4px;
            background: var(--bg-secondary);
            overflow: hidden;
            margin: 0.5rem 0;
        }
        
        .strength-bar {
            height: 100%;
            transition: width 0.3s ease;
        }
        
        .strength-weak { background: #dc3545; width: 25%; }
        .strength-fair { background: #ffc107; width: 50%; }
        .strength-good { background: #28a745; width: 75%; }
        .strength-strong { background: #20c997; width: 100%; }
        
        .activity-log {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .activity-item {
            padding: 0.75rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-secondary);
            color: var(--text-primary);
        }
        
        .activity-content {
            flex-grow: 1;
        }
        
        .activity-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .activity-meta {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .security-header {
                padding: 1rem;
            }
            
            .security-icon {
                font-size: 1.5rem;
            }
            
            .security-title {
                font-size: 1rem;
                line-height: 1.3;
            }
            
            .security-body {
                padding: 1rem;
            }
            
            .security-actions {
                flex-direction: column;
            }
            
            .security-actions .btn {
                width: 100%;
                min-height: 48px;
                font-size: 16px;
            }
            
            .activity-item {
                padding: 1rem 0.75rem;
            }
            
            .activity-icon {
                width: 36px;
                height: 36px;
            }
            
            .activity-title {
                font-size: 0.9rem;
            }
            
            .activity-meta {
                font-size: 0.8rem;
            }
            
            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }
            
            .modal-body {
                padding: 1rem;
                max-height: 70vh;
                overflow-y: auto;
            }
            
            .modal-footer {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .modal-footer .btn {
                width: 100%;
            }
            
            .form-control {
                min-height: 48px;
                font-size: 16px;
                padding: 12px 16px;
            }
            
            .form-label {
                font-size: 16px;
                margin-bottom: 0.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .security-header {
                padding: 0.75rem;
            }
            
            .security-body {
                padding: 0.75rem;
            }
            
            .activity-item {
                padding: 0.75rem 0.5rem;
            }
            
            .activity-icon {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container-fluid main-content mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-3">
                    <div>
                        <h2 class="mb-1">
                            <i class="fas fa-shield-alt me-2"></i>
                            Account Security
                        </h2>
                        <p class="text-muted mb-0">Manage your account security settings and monitor activity</p>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <button class="btn btn-outline-primary" onclick="refreshSecurityInfo()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Overview -->
        <div class="row mb-4">
            <div class="col-lg-4 col-md-6 col-12 mb-3">
                <div class="security-card">
                    <div class="security-header text-center">
                        <div class="security-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h5 class="security-title">Password Security</h5>
                    </div>
                    <div class="security-body">
                        <div class="security-status">
                            <div class="status-indicator status-secure"></div>
                            <span>Strong Password</span>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar strength-strong"></div>
                        </div>
                        <p class="text-muted small mb-3">Last changed: Never</p>
                        <div class="security-actions">
                            <button class="btn btn-primary flex-grow-1" onclick="showChangePasswordModal()">
                                <i class="fas fa-key me-1"></i>
                                Change Password
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 col-12 mb-3">
                <div class="security-card">
                    <div class="security-header text-center">
                        <div class="security-icon">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <h5 class="security-title">Email Security</h5>
                    </div>
                    <div class="security-body">
                        <div class="security-status">
                            <div class="status-indicator status-secure"></div>
                            <span>Email Verified</span>
                        </div>
                        <p class="text-muted small mb-3" id="user-email">Loading...</p>
                        <div class="security-actions">
                            <button class="btn btn-outline-primary flex-grow-1" onclick="resendVerification()">
                                <i class="fas fa-paper-plane me-1"></i>
                                Resend Verification
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-12 mb-3">
                <div class="security-card">
                    <div class="security-header text-center">
                        <div class="security-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <h5 class="security-title">Login Activity</h5>
                    </div>
                    <div class="security-body">
                        <div class="security-status">
                            <div class="status-indicator status-secure"></div>
                            <span>No Suspicious Activity</span>
                        </div>
                        <p class="text-muted small mb-3">Last login: <span id="last-login">Loading...</span></p>
                        <div class="security-actions">
                            <button class="btn btn-outline-info flex-grow-1" onclick="showActivityLog()">
                                <i class="fas fa-list me-1"></i>
                                View Activity
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Recommendations -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-lightbulb me-2"></i>Security Recommendations</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-success">
                                        <i class="fas fa-check-circle fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Strong Password</h6>
                                        <p class="text-muted small mb-0">Your password meets security requirements</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-success">
                                        <i class="fas fa-check-circle fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Email Verified</h6>
                                        <p class="text-muted small mb-0">Your email address has been verified</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-warning">
                                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Regular Password Updates</h6>
                                        <p class="text-muted small mb-0">Consider changing your password every 90 days</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-info">
                                        <i class="fas fa-info-circle fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Monitor Activity</h6>
                                        <p class="text-muted small mb-0">Regularly check your login activity for suspicious access</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-key me-2"></i>Change Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm">
                        <div class="mb-3">
                            <label class="form-label">Current Password *</label>
                            <input type="password" class="form-control" id="currentPassword" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="newPassword" required>
                            <div class="password-strength mt-2">
                                <div class="strength-bar" id="passwordStrengthBar"></div>
                            </div>
                            <small class="text-muted">Password must be at least 8 characters long</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" id="confirmPassword" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="changePassword()">
                        <i class="fas fa-save me-1"></i>Update Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Log Modal -->
    <div class="modal fade" id="activityLogModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-history me-2"></i>Login Activity
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="activity-log" id="activityLogContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading activity log...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load user security information
        async function loadSecurityInfo() {
            try {
                const response = await fetch('/web/user', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const user = await response.json();
                    document.getElementById('user-email').textContent = user.email || 'Not provided';
                    document.getElementById('last-login').textContent = user.last_login_at 
                        ? new Date(user.last_login_at).toLocaleString() 
                        : 'Never';
                }
            } catch (error) {
                console.error('Error loading security info:', error);
            }
        }

        function showChangePasswordModal() {
            new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
        }

        function showActivityLog() {
            const modal = new bootstrap.Modal(document.getElementById('activityLogModal'));
            modal.show();
            
            // Simulate loading activity log
            setTimeout(() => {
                const content = document.getElementById('activityLogContent');
                content.innerHTML = `
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-sign-in-alt text-success"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Successful Login</div>
                            <div class="activity-meta">Today at ${new Date().toLocaleTimeString()} • Your current session</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-user-edit text-info"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Profile Updated</div>
                            <div class="activity-meta">2 days ago • Profile information changed</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-sign-in-alt text-success"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Successful Login</div>
                            <div class="activity-meta">3 days ago • Previous session</div>
                        </div>
                    </div>
                `;
            }, 1000);
        }

        function changePassword() {
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (!currentPassword || !newPassword || !confirmPassword) {
                alert('Please fill in all fields');
                return;
            }

            if (newPassword !== confirmPassword) {
                alert('New passwords do not match');
                return;
            }

            if (newPassword.length < 8) {
                alert('Password must be at least 8 characters long');
                return;
            }

            // Simulate password change
            alert('Password change functionality would be implemented here');
            bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
        }

        function resendVerification() {
            alert('Email verification functionality would be implemented here');
        }

        function refreshSecurityInfo() {
            loadSecurityInfo();
        }

        // Password strength checker
        document.addEventListener('DOMContentLoaded', function() {
            loadSecurityInfo();
            
            const newPasswordInput = document.getElementById('newPassword');
            if (newPasswordInput) {
                newPasswordInput.addEventListener('input', function() {
                    const password = this.value;
                    const strengthBar = document.getElementById('passwordStrengthBar');
                    
                    let strength = 0;
                    if (password.length >= 8) strength++;
                    if (/[A-Z]/.test(password)) strength++;
                    if (/[0-9]/.test(password)) strength++;
                    if (/[^A-Za-z0-9]/.test(password)) strength++;
                    
                    strengthBar.className = 'strength-bar';
                    switch (strength) {
                        case 1:
                            strengthBar.classList.add('strength-weak');
                            break;
                        case 2:
                            strengthBar.classList.add('strength-fair');
                            break;
                        case 3:
                            strengthBar.classList.add('strength-good');
                            break;
                        case 4:
                            strengthBar.classList.add('strength-strong');
                            break;
                    }
                });
            }
        });
    </script>
    
    @vite(['resources/js/app.js'])
    <x-footer />
</body>
</html>