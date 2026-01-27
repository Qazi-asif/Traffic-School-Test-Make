<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Control Panel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .control-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .module-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }
        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .module-enabled {
            border-left: 4px solid #28a745;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }
        .module-disabled {
            border-left: 4px solid #dc3545;
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        }
        .toggle-switch {
            position: relative;
            width: 60px;
            height: 30px;
            background: #ccc;
            border-radius: 15px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .toggle-switch.active {
            background: #28a745;
        }
        .toggle-switch::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s;
        }
        .toggle-switch.active::after {
            transform: translateX(30px);
        }
        .system-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            border-radius: 10px;
        }
        .danger-zone {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border-radius: 10px;
        }
        .header-title {
            background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="control-panel p-4">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="header-title mb-2">
                    <i class="fas fa-shield-alt"></i> System Control Panel
                </h1>
                <p class="text-muted">Advanced Module Management & System Control</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Restricted Access:</strong> This panel is for authorized personnel only.
                </div>
            </div>

            <!-- System Information -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="system-info p-4">
                        <h4><i class="fas fa-info-circle"></i> System Overview</h4>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3>{{ number_format($systemInfo['total_users']) }}</h3>
                                    <small>Total Users</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3>{{ number_format($systemInfo['active_enrollments']) }}</h3>
                                    <small>Active Enrollments</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3>${{ number_format($systemInfo['total_revenue'], 2) }}</h3>
                                    <small>Total Revenue</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3>{{ $systemInfo['last_activity'] ? $systemInfo['last_activity']->diffForHumans() : 'N/A' }}</h3>
                                    <small>Last Activity</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module Controls -->
            <div class="row">
                <div class="col-md-8">
                    <h4><i class="fas fa-cogs"></i> Module Management</h4>
                    <div class="row">
                        @foreach($moduleStatuses as $key => $module)
                        <div class="col-md-6 mb-3">
                            <div class="card module-card {{ $module['enabled'] ? 'module-enabled' : 'module-disabled' }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $module['name'] }}</h6>
                                            <small class="text-muted">{{ $key }}</small>
                                        </div>
                                        <div class="toggle-switch {{ $module['enabled'] ? 'active' : '' }}" 
                                             onclick="toggleModule('{{ $key }}', {{ $module['enabled'] ? 'false' : 'true' }})">
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge {{ $module['enabled'] ? 'bg-success' : 'bg-danger' }}">
                                            {{ $module['enabled'] ? 'ENABLED' : 'DISABLED' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Control Panel -->
                <div class="col-md-4">
                    <h4><i class="fas fa-tools"></i> System Controls</h4>
                    
                    <!-- License Management -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6><i class="fas fa-key"></i> License Management</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">License Expires</label>
                                <input type="date" class="form-control" id="licenseExpiry" 
                                       value="{{ $systemInfo['license_expires'] }}">
                            </div>
                            <button class="btn btn-primary btn-sm w-100" onclick="updateLicenseExpiry()">
                                Update License
                            </button>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6><i class="fas fa-bolt"></i> Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-success btn-sm w-100 mb-2" onclick="enableAllModules()">
                                <i class="fas fa-check-circle"></i> Enable All Modules
                            </button>
                            <button class="btn btn-info btn-sm w-100 mb-2" onclick="getSystemInfo()">
                                <i class="fas fa-info"></i> System Information
                            </button>
                            <button class="btn btn-secondary btn-sm w-100" onclick="clearCache()">
                                <i class="fas fa-broom"></i> Clear Cache
                            </button>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="danger-zone p-3">
                        <h6><i class="fas fa-exclamation-triangle"></i> Danger Zone</h6>
                        <p class="small mb-3">Emergency controls - use with caution</p>
                        <button class="btn btn-outline-light btn-sm w-100" onclick="emergencyDisable()">
                            <i class="fas fa-power-off"></i> Emergency Disable All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toast" class="toast" role="alert">
            <div class="toast-header">
                <strong class="me-auto">System Control</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toast-body"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const token = '{{ request()->get("token") }}';
        
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastBody = document.getElementById('toast-body');
            
            toastBody.textContent = message;
            toast.className = `toast bg-${type === 'success' ? 'success' : 'danger'} text-white`;
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }

        function toggleModule(module, enabled) {
            fetch(`/system-control-panel/toggle-module?token=${token}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ module, enabled })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showToast(data.error || 'Failed to toggle module', 'error');
                }
            })
            .catch(error => {
                showToast('Network error occurred', 'error');
            });
        }

        function updateLicenseExpiry() {
            const expiryDate = document.getElementById('licenseExpiry').value;
            
            fetch(`/system-control-panel/set-license-expiry?token=${token}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ expires_at: expiryDate })
            })
            .then(response => response.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
            });
        }

        function emergencyDisable() {
            if (confirm('This will disable ALL modules immediately. Are you sure?')) {
                fetch(`/system-control-panel/emergency-disable?token=${token}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    showToast(data.message, data.success ? 'success' : 'error');
                    if (data.success) {
                        setTimeout(() => location.reload(), 2000);
                    }
                });
            }
        }

        function enableAllModules() {
            const modules = @json(array_keys($moduleStatuses));
            
            Promise.all(modules.map(module => 
                fetch(`/system-control-panel/toggle-module?token=${token}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ module, enabled: true })
                })
            ))
            .then(() => {
                showToast('All modules enabled successfully');
                setTimeout(() => location.reload(), 1500);
            })
            .catch(() => {
                showToast('Failed to enable all modules', 'error');
            });
        }

        function getSystemInfo() {
            fetch(`/system-control-panel/system-info?token=${token}`)
            .then(response => response.json())
            .then(data => {
                const info = Object.entries(data).map(([key, value]) => 
                    `${key.replace(/_/g, ' ').toUpperCase()}: ${value}`
                ).join('\n');
                
                alert('SYSTEM INFORMATION:\n\n' + info);
            });
        }

        function clearCache() {
            fetch(`/system-control-panel/clear-cache?token=${token}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                showToast(data.message || 'Cache cleared successfully', data.success ? 'success' : 'error');
            })
            .catch(error => {
                showToast('Failed to clear cache', 'error');
            });
        }
    </script>
</body>
</html>