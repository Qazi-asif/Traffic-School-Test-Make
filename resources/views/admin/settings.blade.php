<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>System Settings - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
        }
        
        .settings-header {
            background: linear-gradient(135deg, var(--accent) 0%, var(--hover) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
        }
        
        .settings-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .settings-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .settings-section {
            border-bottom: 1px solid var(--border);
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .settings-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .form-control, .form-select {
            background-color: var(--bg-primary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        
        .form-control:focus, .form-select:focus {
            background-color: var(--bg-primary);
            border-color: var(--accent);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
        }
        
        .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
        }
        
        .btn-primary:hover {
            background-color: var(--hover);
            border-color: var(--hover);
        }
        
        .nav-pills .nav-link {
            background-color: transparent;
            border: 1px solid var(--border);
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--accent);
            border-color: var(--accent);
            color: white;
        }
        
        .nav-pills .nav-link:hover {
            background-color: var(--hover);
            color: white;
        }
        
        .alert {
            border: none;
            border-radius: 8px;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--accent);
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
        }
        
        .status-online {
            background-color: #28a745;
        }
        
        .status-offline {
            background-color: #dc3545;
        }
        
        .status-warning {
            background-color: #ffc107;
        }
        
        .container-main {
            padding: 0 1.5rem;
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container-fluid" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 0;">
        <!-- Settings Header -->
        <div class="settings-header">
            <div class="container-main">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2">
                            <i class="fas fa-cogs me-2"></i>
                            System Settings
                        </h2>
                        <p class="mb-0 opacity-75">Configure system preferences and manage application settings</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-light" onclick="saveAllSettings()">
                            <i class="fas fa-save me-2"></i>Save All Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-main">
            <div class="row">
                <!-- Settings Navigation -->
                <div class="col-md-3">
                    <div class="settings-card">
                        <h6 class="mb-3">Settings Categories</h6>
                        <ul class="nav nav-pills flex-column" id="settings-nav">
                            <li class="nav-item">
                                <a class="nav-link active" href="#general" data-bs-toggle="pill">
                                    <i class="fas fa-cog me-2"></i>General
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#email" data-bs-toggle="pill">
                                    <i class="fas fa-envelope me-2"></i>Email Settings
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#security" data-bs-toggle="pill">
                                    <i class="fas fa-shield-alt me-2"></i>Security
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#payment" data-bs-toggle="pill">
                                    <i class="fas fa-credit-card me-2"></i>Payment Gateway
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#notifications" data-bs-toggle="pill">
                                    <i class="fas fa-bell me-2"></i>Notifications
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#integrations" data-bs-toggle="pill">
                                    <i class="fas fa-plug me-2"></i>Integrations
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#maintenance" data-bs-toggle="pill">
                                    <i class="fas fa-tools me-2"></i>Maintenance
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- System Status -->
                    <div class="settings-card">
                        <h6 class="mb-3">System Status</h6>
                        <div class="mb-2">
                            <span class="status-indicator status-online"></span>
                            <small>Database: Online</small>
                        </div>
                        <div class="mb-2">
                            <span class="status-indicator status-online"></span>
                            <small>Email Service: Active</small>
                        </div>
                        <div class="mb-2">
                            <span class="status-indicator status-warning"></span>
                            <small>Queue: 3 pending jobs</small>
                        </div>
                        <div class="mb-2">
                            <span class="status-indicator status-online"></span>
                            <small>Storage: 78% available</small>
                        </div>
                    </div>
                </div>

                <!-- Settings Content -->
                <div class="col-md-9">
                    <div class="tab-content" id="settings-content">
                        <!-- General Settings -->
                        <div class="tab-pane fade show active" id="general">
                            <div class="settings-card">
                                <h5 class="mb-4">General Settings</h5>
                                
                                <div class="settings-section">
                                    <h6>Application Information</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Application Name</label>
                                                <input type="text" class="form-control" value="Dummies Traffic School" id="app-name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Application URL</label>
                                                <input type="url" class="form-control" value="https://dummiestrafficschool.com" id="app-url">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Application Description</label>
                                        <textarea class="form-control" rows="3" id="app-description">Multi-state online traffic school and defensive driving course platform</textarea>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h6>Timezone & Localization</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Default Timezone</label>
                                                <select class="form-select" id="timezone">
                                                    <option value="America/New_York">Eastern Time (ET)</option>
                                                    <option value="America/Chicago">Central Time (CT)</option>
                                                    <option value="America/Denver">Mountain Time (MT)</option>
                                                    <option value="America/Los_Angeles" selected>Pacific Time (PT)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Date Format</label>
                                                <select class="form-select" id="date-format">
                                                    <option value="m/d/Y" selected>MM/DD/YYYY</option>
                                                    <option value="d/m/Y">DD/MM/YYYY</option>
                                                    <option value="Y-m-d">YYYY-MM-DD</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h6>Course Settings</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Default Course Duration (minutes)</label>
                                                <input type="number" class="form-control" value="240" id="default-duration">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Default Passing Score (%)</label>
                                                <input type="number" class="form-control" value="80" min="0" max="100" id="default-passing-score">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="auto-enroll" checked>
                                            <label class="form-check-label" for="auto-enroll">
                                                Enable automatic enrollment after payment
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Email Settings -->
                        <div class="tab-pane fade" id="email">
                            <div class="settings-card">
                                <h5 class="mb-4">Email Configuration</h5>
                                
                                <div class="settings-section">
                                    <h6>SMTP Settings</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">SMTP Host</label>
                                                <input type="text" class="form-control" placeholder="smtp.gmail.com" id="smtp-host">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">SMTP Port</label>
                                                <input type="number" class="form-control" value="587" id="smtp-port">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">SMTP Username</label>
                                                <input type="email" class="form-control" placeholder="your-email@domain.com" id="smtp-username">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">SMTP Password</label>
                                                <input type="password" class="form-control" placeholder="••••••••" id="smtp-password">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="smtp-encryption" checked>
                                            <label class="form-check-label" for="smtp-encryption">
                                                Use TLS Encryption
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h6>Email Templates</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">From Name</label>
                                                <input type="text" class="form-control" value="Dummies Traffic School" id="email-from-name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">From Email</label>
                                                <input type="email" class="form-control" value="noreply@dummiestrafficschool.com" id="email-from-address">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <button class="btn btn-outline-primary me-2">
                                            <i class="fas fa-envelope me-2"></i>Test Email Configuration
                                        </button>
                                        <button class="btn btn-outline-secondary">
                                            <i class="fas fa-edit me-2"></i>Manage Email Templates
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Settings -->
                        <div class="tab-pane fade" id="security">
                            <div class="settings-card">
                                <h5 class="mb-4">Security Settings</h5>
                                
                                <div class="settings-section">
                                    <h6>Authentication</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Session Timeout (minutes)</label>
                                                <input type="number" class="form-control" value="120" id="session-timeout">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Max Login Attempts</label>
                                                <input type="number" class="form-control" value="5" id="max-login-attempts">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="two-factor" checked>
                                            <label class="form-check-label" for="two-factor">
                                                Enable Two-Factor Authentication
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="force-https" checked>
                                            <label class="form-check-label" for="force-https">
                                                Force HTTPS Connections
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h6>Password Policy</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Minimum Password Length</label>
                                                <input type="number" class="form-control" value="8" min="6" max="32" id="min-password-length">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Password Expiry (days)</label>
                                                <input type="number" class="form-control" value="90" id="password-expiry">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="require-special-chars" checked>
                                            <label class="form-check-label" for="require-special-chars">
                                                Require special characters in passwords
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Settings -->
                        <div class="tab-pane fade" id="payment">
                            <div class="settings-card">
                                <h5 class="mb-4">Payment Gateway Settings</h5>
                                
                                <div class="settings-section">
                                    <h6>Stripe Configuration</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Stripe Publishable Key</label>
                                                <input type="text" class="form-control" placeholder="pk_test_..." id="stripe-public-key">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Stripe Secret Key</label>
                                                <input type="password" class="form-control" placeholder="sk_test_..." id="stripe-secret-key">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="stripe-test-mode" checked>
                                            <label class="form-check-label" for="stripe-test-mode">
                                                Test Mode (Sandbox)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h6>Payment Options</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Default Currency</label>
                                                <select class="form-select" id="default-currency">
                                                    <option value="USD" selected>US Dollar (USD)</option>
                                                    <option value="CAD">Canadian Dollar (CAD)</option>
                                                    <option value="EUR">Euro (EUR)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Tax Rate (%)</label>
                                                <input type="number" class="form-control" value="0" step="0.01" id="tax-rate">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="enable-refunds" checked>
                                            <label class="form-check-label" for="enable-refunds">
                                                Enable automatic refunds
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications -->
                        <div class="tab-pane fade" id="notifications">
                            <div class="settings-card">
                                <h5 class="mb-4">Notification Settings</h5>
                                
                                <div class="settings-section">
                                    <h6>Email Notifications</h6>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="notify-enrollment" checked>
                                            <label class="form-check-label" for="notify-enrollment">
                                                Send enrollment confirmation emails
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="notify-completion" checked>
                                            <label class="form-check-label" for="notify-completion">
                                                Send course completion emails
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="notify-certificate" checked>
                                            <label class="form-check-label" for="notify-certificate">
                                                Send certificate delivery emails
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h6>Admin Notifications</h6>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="notify-admin-enrollment" checked>
                                            <label class="form-check-label" for="notify-admin-enrollment">
                                                Notify admins of new enrollments
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="notify-admin-errors">
                                            <label class="form-check-label" for="notify-admin-errors">
                                                Notify admins of system errors
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Integrations -->
                        <div class="tab-pane fade" id="integrations">
                            <div class="settings-card">
                                <h5 class="mb-4">State Integrations</h5>
                                
                                <div class="settings-section">
                                    <h6>Florida DICDS/FLHSMV</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">DICDS Username</label>
                                                <input type="text" class="form-control" placeholder="Your DICDS username" id="dicds-username">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">DICDS Password</label>
                                                <input type="password" class="form-control" placeholder="••••••••" id="dicds-password">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="auto-submit-florida" checked>
                                            <label class="form-check-label" for="auto-submit-florida">
                                                Automatically submit completions to Florida
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h6>California TVCC</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">TVCC API Endpoint</label>
                                                <input type="url" class="form-control" value="https://xsg.dmv.ca.gov/tvcc/tvccservice" id="tvcc-endpoint">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">TVCC User ID</label>
                                                <input type="text" class="form-control" value="Support@dummiestrafficschool.com" id="tvcc-user-id">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance -->
                        <div class="tab-pane fade" id="maintenance">
                            <div class="settings-card">
                                <h5 class="mb-4">System Maintenance</h5>
                                
                                <!-- Maintenance Mode Section -->
                                <div class="settings-section">
                                    <h6>Maintenance Mode</h6>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="maintenance-message" class="form-label">Maintenance Message</label>
                                                <textarea id="maintenance-message" class="form-control" rows="3" 
                                                    placeholder="Site is under maintenance. Please check back later."></textarea>
                                                <div class="form-text">Message displayed to users when maintenance mode is active</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex flex-column h-100 justify-content-center">
                                                <div id="maintenance-status" class="alert alert-info mb-3">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    <span id="maintenance-status-text">Checking status...</span>
                                                </div>
                                                <button id="toggle-maintenance" class="btn btn-warning w-100" onclick="toggleMaintenanceMode()">
                                                    <i class="fas fa-tools me-2"></i>
                                                    <span id="maintenance-button-text">Enable Maintenance</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="settings-section">
                                    <h6>Cache Management</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <button class="btn btn-outline-primary w-100 mb-2" onclick="clearCache('config')">
                                                <i class="fas fa-broom me-2"></i>Clear Config Cache
                                            </button>
                                        </div>
                                        <div class="col-md-4">
                                            <button class="btn btn-outline-primary w-100 mb-2" onclick="clearCache('route')">
                                                <i class="fas fa-broom me-2"></i>Clear Route Cache
                                            </button>
                                        </div>
                                        <div class="col-md-4">
                                            <button class="btn btn-outline-primary w-100 mb-2" onclick="clearCache('view')">
                                                <i class="fas fa-broom me-2"></i>Clear View Cache
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <button class="btn btn-outline-primary w-100 mb-2" onclick="clearCache('application')">
                                                <i class="fas fa-broom me-2"></i>Clear App Cache
                                            </button>
                                        </div>
                                        <div class="col-md-4">
                                            <button class="btn btn-outline-danger w-100 mb-2" onclick="clearCache('all')">
                                                <i class="fas fa-broom me-2"></i>Clear All Cache
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h6>Database Maintenance</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button class="btn btn-outline-warning w-100 mb-2" onclick="optimizeDatabase()">
                                                <i class="fas fa-database me-2"></i>Optimize Database
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <button class="btn btn-outline-info w-100 mb-2" onclick="exportDatabase()" id="export-btn">
                                                <i class="fas fa-download me-2"></i>Export Database
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Progress Tracking Section -->
                                    <div id="export-progress-section" class="mt-3" style="display: none;">
                                        <div class="card border-info">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-download me-2"></i>Database Export Progress
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span id="current-task">Initializing export...</span>
                                                        <span id="progress-percentage">0%</span>
                                                    </div>
                                                    <div class="progress" style="height: 8px;">
                                                        <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                                             role="progressbar" style="width: 0%"></div>
                                                    </div>
                                                </div>
                                                
                                                <div class="row text-center">
                                                    <div class="col-md-3">
                                                        <small class="text-muted">Tables Processed</small>
                                                        <div class="fw-bold" id="tables-processed">0 / 0</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted">Records Exported</small>
                                                        <div class="fw-bold" id="records-exported">0</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted">Speed</small>
                                                        <div class="fw-bold" id="export-speed">0 rec/sec</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted">Time Remaining</small>
                                                        <div class="fw-bold" id="time-remaining">Calculating...</div>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-3">
                                                    <button class="btn btn-outline-danger btn-sm" onclick="cancelExport()" id="cancel-btn">
                                                        <i class="fas fa-times me-1"></i>Cancel Export
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h6>System Information</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>PHP Version:</strong> <span id="php-version">{{ phpversion() }}</span></p>
                                            <p><strong>Laravel Version:</strong> <span id="laravel-version">{{ app()->version() }}</span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Server:</strong> <span id="server-software">{{ $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' }}</span></p>
                                            <p><strong>Database:</strong> <span id="database-version">MySQL</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Alert -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050">
        <div id="success-alert" class="alert alert-success alert-dismissible fade" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <span id="success-message">Settings saved successfully!</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Settings management functions
        function saveAllSettings() {
            // Collect all form data
            const settings = {
                general: {
                    app_name: document.getElementById('app-name').value,
                    app_url: document.getElementById('app-url').value,
                    app_description: document.getElementById('app-description').value,
                    timezone: document.getElementById('timezone').value,
                    date_format: document.getElementById('date-format').value,
                    default_duration: document.getElementById('default-duration').value,
                    default_passing_score: document.getElementById('default-passing-score').value,
                    auto_enroll: document.getElementById('auto-enroll').checked
                },
                email: {
                    smtp_host: document.getElementById('smtp-host').value,
                    smtp_port: document.getElementById('smtp-port').value,
                    smtp_username: document.getElementById('smtp-username').value,
                    smtp_password: document.getElementById('smtp-password').value,
                    smtp_encryption: document.getElementById('smtp-encryption').checked,
                    from_name: document.getElementById('email-from-name').value,
                    from_address: document.getElementById('email-from-address').value
                },
                security: {
                    session_timeout: document.getElementById('session-timeout').value,
                    max_login_attempts: document.getElementById('max-login-attempts').value,
                    two_factor: document.getElementById('two-factor').checked,
                    force_https: document.getElementById('force-https').checked,
                    min_password_length: document.getElementById('min-password-length').value,
                    password_expiry: document.getElementById('password-expiry').value,
                    require_special_chars: document.getElementById('require-special-chars').checked
                }
            };

            // Save settings via AJAX
            fetch('/admin/settings/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(settings)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessAlert('Settings saved successfully!');
                } else {
                    showErrorAlert('Error saving settings: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorAlert('An error occurred while saving settings.');
            });
        }

        function clearCache(type) {
            fetch(`/admin/settings/clear-cache/${type}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessAlert(`${type.charAt(0).toUpperCase() + type.slice(1)} cache cleared successfully!`);
                } else {
                    showErrorAlert('Error clearing cache: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorAlert('An error occurred while clearing cache.');
            });
        }

        function optimizeDatabase() {
            if (confirm('This will optimize the database tables. Continue?')) {
                fetch('/admin/settings/optimize-database', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessAlert('Database optimized successfully!');
                    } else {
                        showErrorAlert('Error optimizing database: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorAlert('An error occurred while optimizing database.');
                });
            }
        }

        function backupDatabase() {
            if (confirm('This will create a database backup. Continue?')) {
                window.location.href = '/admin/settings/backup-database';
                showSuccessAlert('Database backup initiated!');
            }
        }

        // Enhanced Database Export with Progress Tracking
        let exportInterval = null;
        let exportStartTime = null;
        let exportJobId = null;

        function exportDatabase() {
            if (confirm('This will export the entire database. This may take several minutes. Continue?')) {
                // Show progress section
                document.getElementById('export-progress-section').style.display = 'block';
                document.getElementById('export-btn').disabled = true;
                document.getElementById('export-btn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Exporting...';
                
                exportStartTime = Date.now();
                
                // Start the export process
                fetch('/admin/settings/export-database', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        exportJobId = data.job_id;
                        // Start polling for progress
                        exportInterval = setInterval(checkExportProgress, 1000);
                        showSuccessAlert('Database export started successfully!');
                    } else {
                        resetExportUI();
                        showErrorAlert('Error starting export: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    resetExportUI();
                    showErrorAlert('An error occurred while starting the export.');
                });
            }
        }

        function checkExportProgress() {
            if (!exportJobId) return;
            
            fetch(`/admin/settings/export-progress/${exportJobId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateProgressUI(data.progress);
                    
                    if (data.progress.status === 'completed') {
                        clearInterval(exportInterval);
                        completeExport(data.progress.download_url);
                    } else if (data.progress.status === 'failed') {
                        clearInterval(exportInterval);
                        resetExportUI();
                        showErrorAlert('Export failed: ' + data.progress.error);
                    }
                } else {
                    console.error('Error checking progress:', data.message);
                }
            })
            .catch(error => {
                console.error('Error checking progress:', error);
            });
        }

        function updateProgressUI(progress) {
            // Update progress bar
            const percentage = Math.round(progress.percentage || 0);
            document.getElementById('progress-bar').style.width = percentage + '%';
            document.getElementById('progress-percentage').textContent = percentage + '%';
            
            // Update current task
            document.getElementById('current-task').textContent = progress.current_task || 'Processing...';
            
            // Update statistics
            document.getElementById('tables-processed').textContent = 
                `${progress.tables_processed || 0} / ${progress.total_tables || 0}`;
            document.getElementById('records-exported').textContent = 
                (progress.records_exported || 0).toLocaleString();
            
            // Calculate and update speed
            const elapsedSeconds = (Date.now() - exportStartTime) / 1000;
            const speed = elapsedSeconds > 0 ? Math.round((progress.records_exported || 0) / elapsedSeconds) : 0;
            document.getElementById('export-speed').textContent = speed.toLocaleString() + ' rec/sec';
            
            // Calculate and update time remaining
            if (speed > 0 && progress.total_records && progress.records_exported) {
                const remainingRecords = progress.total_records - progress.records_exported;
                const remainingSeconds = Math.round(remainingRecords / speed);
                document.getElementById('time-remaining').textContent = formatTime(remainingSeconds);
            } else {
                document.getElementById('time-remaining').textContent = 'Calculating...';
            }
        }

        function formatTime(seconds) {
            if (seconds < 60) {
                return seconds + 's';
            } else if (seconds < 3600) {
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = seconds % 60;
                return minutes + 'm ' + remainingSeconds + 's';
            } else {
                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                return hours + 'h ' + minutes + 'm';
            }
        }

        function completeExport(downloadUrl) {
            // Update UI to show completion
            document.getElementById('current-task').textContent = 'Export completed successfully!';
            document.getElementById('progress-bar').classList.remove('progress-bar-animated');
            document.getElementById('progress-bar').classList.add('bg-success');
            
            // Reset button and hide progress after delay
            setTimeout(() => {
                resetExportUI();
                showSuccessAlert('Database export completed successfully!');
                
                // Trigger download
                if (downloadUrl) {
                    window.location.href = downloadUrl;
                }
            }, 2000);
        }

        function cancelExport() {
            if (confirm('Are you sure you want to cancel the export?')) {
                if (exportJobId) {
                    fetch(`/admin/settings/cancel-export/${exportJobId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccessAlert('Export cancelled successfully.');
                        } else {
                            showErrorAlert('Error cancelling export: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorAlert('An error occurred while cancelling the export.');
                    });
                }
                
                clearInterval(exportInterval);
                resetExportUI();
            }
        }

        function resetExportUI() {
            document.getElementById('export-progress-section').style.display = 'none';
            document.getElementById('export-btn').disabled = false;
            document.getElementById('export-btn').innerHTML = '<i class="fas fa-download me-2"></i>Export Database';
            
            // Reset progress indicators
            document.getElementById('progress-bar').style.width = '0%';
            document.getElementById('progress-bar').classList.add('progress-bar-animated');
            document.getElementById('progress-bar').classList.remove('bg-success');
            document.getElementById('progress-percentage').textContent = '0%';
            document.getElementById('current-task').textContent = 'Initializing export...';
            document.getElementById('tables-processed').textContent = '0 / 0';
            document.getElementById('records-exported').textContent = '0';
            document.getElementById('export-speed').textContent = '0 rec/sec';
            document.getElementById('time-remaining').textContent = 'Calculating...';
            
            exportJobId = null;
            exportStartTime = null;
        }

        function showSuccessAlert(message) {
            const alert = document.getElementById('success-alert');
            const messageSpan = document.getElementById('success-message');
            messageSpan.textContent = message;
            alert.classList.add('show');
            
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }

        function showErrorAlert(message) {
            // Create error alert similar to success alert
            const alert = document.getElementById('success-alert');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            const messageSpan = document.getElementById('success-message');
            messageSpan.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + message;
            
            setTimeout(() => {
                alert.classList.remove('show');
                // Reset to success styling
                setTimeout(() => {
                    alert.className = 'alert alert-success alert-dismissible fade';
                    messageSpan.innerHTML = '<i class="fas fa-check-circle me-2"></i>Settings saved successfully!';
                }, 300);
            }, 5000);
        }

        // Load existing settings on page load
        // (This is now handled in the updated DOMContentLoaded listener above)

        function loadSettings() {
            fetch('/admin/settings/load', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.settings) {
                    // Populate form fields with existing settings
                    const settings = data.settings;
                    
                    // General settings
                    if (settings.general) {
                        if (settings.general.app_name) document.getElementById('app-name').value = settings.general.app_name;
                        if (settings.general.app_url) document.getElementById('app-url').value = settings.general.app_url;
                        // ... populate other fields
                    }
                }
            })
            .catch(error => {
                console.error('Error loading settings:', error);
            });
        }

        // Maintenance Mode Functions
        let maintenanceMode = false;

        function loadMaintenanceStatus() {
            fetch('/admin/settings/maintenance/status', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    maintenanceMode = data.maintenance_mode;
                    updateMaintenanceUI(data.maintenance_mode, data.message);
                }
            })
            .catch(error => {
                console.error('Error loading maintenance status:', error);
            });
        }

        function updateMaintenanceUI(isEnabled, message = '') {
            const statusDiv = document.getElementById('maintenance-status');
            const statusText = document.getElementById('maintenance-status-text');
            const button = document.getElementById('toggle-maintenance');
            const buttonText = document.getElementById('maintenance-button-text');
            const messageInput = document.getElementById('maintenance-message');

            if (isEnabled) {
                statusDiv.className = 'alert alert-warning mb-3';
                statusText.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Maintenance Mode is ACTIVE';
                button.className = 'btn btn-success w-100';
                buttonText.textContent = 'Disable Maintenance';
                if (message) {
                    messageInput.value = message;
                }
            } else {
                statusDiv.className = 'alert alert-success mb-3';
                statusText.innerHTML = '<i class="fas fa-check-circle me-2"></i>Site is ONLINE';
                button.className = 'btn btn-warning w-100';
                buttonText.textContent = 'Enable Maintenance';
            }
        }

        function toggleMaintenanceMode() {
            const message = document.getElementById('maintenance-message').value || 'Site is under maintenance. Please check back later.';
            
            if (maintenanceMode) {
                // Disable maintenance mode
                if (confirm('Are you sure you want to disable maintenance mode? The site will be accessible to all users.')) {
                    fetch('/admin/settings/maintenance/disable', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            maintenanceMode = false;
                            updateMaintenanceUI(false);
                            showSuccessAlert('Maintenance mode disabled. Site is now online.');
                        } else {
                            showErrorAlert('Error disabling maintenance mode: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorAlert('An error occurred while disabling maintenance mode.');
                    });
                }
            } else {
                // Enable maintenance mode
                if (confirm('Are you sure you want to enable maintenance mode? This will make the site inaccessible to regular users.')) {
                    fetch('/admin/settings/maintenance/enable', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ message: message })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            maintenanceMode = true;
                            updateMaintenanceUI(true, message);
                            showSuccessAlert('Maintenance mode enabled. Site is now in maintenance.');
                            // Open admin panel in new tab
                            if (data.admin_url) {
                                window.open(data.admin_url, '_blank');
                            }
                        } else {
                            showErrorAlert('Error enabling maintenance mode: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorAlert('An error occurred while enabling maintenance mode.');
                    });
                }
            }
        }

        // Load maintenance status on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadSettings();
            loadMaintenanceStatus();
        });
    </script>

    <x-footer />
</body>
</html>