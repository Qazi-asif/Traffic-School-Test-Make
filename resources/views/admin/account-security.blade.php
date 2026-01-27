<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Account Security</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <h2>
            <i class="fas fa-shield-alt me-2"></i>
            Account Security
        </h2>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form id="passwordForm">
                                <div class="mb-3">
                                    <label for="currentPassword" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="currentPassword" required>
                                </div>
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="newPassword" required>
                                    <div id="passwordStrength" class="mt-2"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirmPassword" required>
                                </div>
                                <button type="button" onclick="changePassword()" class="btn btn-primary">
                                    Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Security Settings</h5>
                        </div>
                        <div class="card-body" id="securitySettings">
                            <div class="mb-4">
                                <h6 class="fw-bold">Two-Factor Authentication</h6>
                                <p class="text-muted small">Add an extra layer of security to your account by requiring a verification code sent to your email.</p>
                                
                                <div id="twoFactorStatus">
                                    <p>Loading 2FA status...</p>
                                </div>
                                
                                <div id="twoFactorControls" style="display: none;">
                                    <!-- Enable 2FA Form -->
                                    <div id="enable2FA" style="display: none;">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Enable Two-Factor Authentication</strong><br>
                                            Enter your password to enable 2FA. You'll receive verification codes via email when logging in.
                                        </div>
                                        <div class="mb-3">
                                            <label for="enable2FAPassword" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="enable2FAPassword" required>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-success" onclick="enable2FA()">
                                                <i class="fas fa-shield-alt me-1"></i>Enable 2FA
                                            </button>
                                            <button type="button" class="btn btn-secondary" onclick="cancel2FAAction()">Cancel</button>
                                        </div>
                                    </div>
                                    
                                    <!-- Disable 2FA Form -->
                                    <div id="disable2FA" style="display: none;">
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Disable Two-Factor Authentication</strong><br>
                                            This will remove the extra security layer from your account. Enter your password to confirm.
                                        </div>
                                        <div class="mb-3">
                                            <label for="disable2FAPassword" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="disable2FAPassword" required>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-danger" onclick="disable2FA()">
                                                <i class="fas fa-shield-alt me-1"></i>Disable 2FA
                                            </button>
                                            <button type="button" class="btn btn-secondary" onclick="cancel2FAAction()">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div id="otherSecuritySettings">
                                <p>Loading other security settings...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Login History</h5>
                        </div>
                        <div class="card-body">
                            <div id="loginHistory">
                                <p>Loading login history...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let passwordStrengthTimer;

        async function loadSecuritySettings() {
            try {
                const response = await fetch('/web/account/security-settings', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const settings = await response.json();
                
                // Update 2FA status
                const twoFactorEnabled = settings.two_factor_enabled || false;
                document.getElementById('twoFactorStatus').innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge ${twoFactorEnabled ? 'bg-success' : 'bg-secondary'} me-2">
                                ${twoFactorEnabled ? 'Enabled' : 'Disabled'}
                            </span>
                            <span class="text-muted">
                                ${twoFactorEnabled ? 'Your account is protected with 2FA' : 'Add extra security to your account'}
                            </span>
                        </div>
                        <button class="btn btn-${twoFactorEnabled ? 'outline-danger' : 'outline-success'} btn-sm" 
                                onclick="${twoFactorEnabled ? 'showDisable2FA' : 'showEnable2FA'}()">
                            <i class="fas fa-${twoFactorEnabled ? 'times' : 'plus'} me-1"></i>
                            ${twoFactorEnabled ? 'Disable' : 'Enable'} 2FA
                        </button>
                    </div>
                `;
                
                // Show controls
                document.getElementById('twoFactorControls').style.display = 'block';
                
                // Update other settings
                document.getElementById('otherSecuritySettings').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Last Password Change</label>
                                <div class="text-muted">${formatDate(settings.last_password_change)}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Active Sessions</label>
                                <div class="text-muted">${settings.active_sessions || 1}</div>
                            </div>
                        </div>
                    </div>
                `;
                
            } catch (error) {
                console.error('Failed to load security settings:', error);
                document.getElementById('twoFactorStatus').innerHTML = '<p class="text-danger">Error loading 2FA status</p>';
                document.getElementById('otherSecuritySettings').innerHTML = '<p class="text-danger">Error loading settings</p>';
            }
        }

        function showEnable2FA() {
            document.getElementById('enable2FA').style.display = 'block';
            document.getElementById('disable2FA').style.display = 'none';
        }

        function showDisable2FA() {
            document.getElementById('disable2FA').style.display = 'block';
            document.getElementById('enable2FA').style.display = 'none';
        }

        function cancel2FAAction() {
            document.getElementById('enable2FA').style.display = 'none';
            document.getElementById('disable2FA').style.display = 'none';
            document.getElementById('enable2FAPassword').value = '';
            document.getElementById('disable2FAPassword').value = '';
        }

        async function enable2FA() {
            const password = document.getElementById('enable2FAPassword').value;
            
            if (!password) {
                alert('Please enter your password');
                return;
            }

            console.log('Attempting to enable 2FA...');
            
            // Get fresh CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('CSRF Token:', csrfToken);

            try {
                const response = await fetch('/two-factor/enable', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ password })
                });

                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.log('Error response text:', errorText);
                    throw new Error(`HTTP ${response.status}: ${errorText}`);
                }

                const result = await response.json();
                console.log('Response data:', result);

                alert('Two-factor authentication enabled successfully!');
                cancel2FAAction();
                loadSecuritySettings();
                
            } catch (error) {
                console.error('Enable 2FA error:', error);
                alert('Error enabling 2FA: ' + error.message);
            }
        }

        async function disable2FA() {
            const password = document.getElementById('disable2FAPassword').value;
            
            if (!password) {
                alert('Please enter your password');
                return;
            }

            if (!confirm('Are you sure you want to disable two-factor authentication? This will make your account less secure.')) {
                return;
            }

            try {
                const response = await fetch('/two-factor/disable', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ password })
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Two-factor authentication disabled successfully.');
                    cancel2FAAction();
                    loadSecuritySettings();
                } else {
                    alert(result.error || 'Failed to disable 2FA');
                }
            } catch (error) {
                console.error('Disable 2FA error:', error);
                alert('Error disabling 2FA');
            }
        }

        async function loadLoginHistory() {
            try {
                const response = await fetch('/web/account/login-history', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const history = await response.json();
                
                if (history.length === 0) {
                    document.getElementById('loginHistory').innerHTML = '<p>No login history found.</p>';
                    return;
                }

                const table = `
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Status</th>
                                    <th>IP Address</th>
                                    <th>User Agent</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${history.map(attempt => `
                                    <tr>
                                        <td>${formatDate(attempt.attempted_at)}</td>
                                        <td>
                                            <span class="badge ${attempt.successful ? 'bg-success' : 'bg-danger'}">
                                                ${attempt.successful ? 'Success' : 'Failed'}
                                            </span>
                                        </td>
                                        <td>${attempt.ip_address}</td>
                                        <td class="text-truncate" style="max-width: 200px;" title="${attempt.user_agent}">
                                            ${attempt.user_agent}
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
                
                document.getElementById('loginHistory').innerHTML = table;
                
            } catch (error) {
                console.error('Failed to load login history:', error);
                document.getElementById('loginHistory').innerHTML = '<p class="text-danger">Error loading login history</p>';
            }
        }

        async function changePassword() {
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                alert('New passwords do not match');
                return;
            }

            try {
                const response = await fetch('/web/account/password', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        current_password: currentPassword,
                        new_password: newPassword,
                        new_password_confirmation: confirmPassword
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Password changed successfully');
                    document.getElementById('passwordForm').reset();
                    document.getElementById('passwordStrength').innerHTML = '';
                    loadSecuritySettings();
                } else {
                    alert(result.error || 'Failed to change password');
                }
            } catch (error) {
                console.error('Password change error:', error);
                alert('Error changing password');
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('newPassword').value;
            
            if (!password) {
                document.getElementById('passwordStrength').innerHTML = '';
                return;
            }

            let score = 0;
            let feedback = [];

            // Length check
            if (password.length >= 8) score += 20;
            else feedback.push('At least 8 characters');

            // Character variety
            if (/[a-z]/.test(password)) score += 20;
            else feedback.push('Lowercase letter');

            if (/[A-Z]/.test(password)) score += 20;
            else feedback.push('Uppercase letter');

            if (/[0-9]/.test(password)) score += 20;
            else feedback.push('Number');

            if (/[^A-Za-z0-9]/.test(password)) score += 20;
            else feedback.push('Special character');

            let strengthClass, strengthText;
            if (score < 40) {
                strengthClass = 'bg-danger';
                strengthText = 'Weak';
            } else if (score < 80) {
                strengthClass = 'bg-warning';
                strengthText = 'Fair';
            } else {
                strengthClass = 'bg-success';
                strengthText = 'Strong';
            }

            const strengthHtml = `
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar ${strengthClass}" style="width: ${score}%"></div>
                </div>
                <small class="text-muted">Strength: ${strengthText}</small>
                ${feedback.length ? `<div class="small text-muted mt-1">Missing: ${feedback.join(', ')}</div>` : ''}
            `;

            document.getElementById('passwordStrength').innerHTML = strengthHtml;
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        }

        // Add event listener for password strength checking
        document.getElementById('newPassword').addEventListener('input', function() {
            clearTimeout(passwordStrengthTimer);
            passwordStrengthTimer = setTimeout(checkPasswordStrength, 300);
        });

        loadSecuritySettings();
        loadLoginHistory();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
