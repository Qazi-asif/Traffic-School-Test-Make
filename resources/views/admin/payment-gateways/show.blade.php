<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configure {{ $gateway->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('admin.payment-gateways.index') }}" class="text-decoration-none text-muted">
                    <i class="fas fa-arrow-left"></i> Back to Gateways
                </a>
                <h2 class="mt-2"><i class="fas fa-cog"></i> {{ $gateway->display_name }}</h2>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.payment-gateways.logs', $gateway) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-history"></i> View Logs
                </a>
                <a href="{{ route('admin.payment-gateways.edit', $gateway) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Settings
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Status</h6>
                        @if($gateway->is_active)
                        <h3 class="text-success mb-3"><i class="fas fa-check-circle"></i> Active</h3>
                        @else
                        <h3 class="text-secondary mb-3"><i class="fas fa-times-circle"></i> Inactive</h3>
                        @endif
                        <form action="{{ $gateway->is_active ? route('admin.payment-gateways.deactivate', $gateway) : route('admin.payment-gateways.activate', $gateway) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $gateway->is_active ? 'btn-danger' : 'btn-success' }}">
                                <i class="fas fa-{{ $gateway->is_active ? 'power-off' : 'check' }}"></i>
                                {{ $gateway->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Environment</h6>
                        @if($gateway->is_test_mode)
                        <h3 class="text-warning mb-3"><i class="fas fa-flask"></i> Test Mode</h3>
                        @else
                        <h3 class="text-primary mb-3"><i class="fas fa-globe"></i> Production</h3>
                        @endif
                        <form action="{{ route('admin.payment-gateways.toggle-mode', $gateway) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-exchange-alt"></i> Switch to {{ $gateway->is_test_mode ? 'Production' : 'Test' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Connection</h6>
                        <h3 id="connection-status" class="text-muted mb-3"><i class="fas fa-question-circle"></i> Not tested</h3>
                        <button onclick="testConnection()" id="test-btn" class="btn btn-sm btn-info">
                            <i class="fas fa-plug"></i> Test Connection
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if(!$validation['valid'])
        <div class="alert alert-warning">
            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Configuration Incomplete</h5>
            <p class="mb-0">
                Missing required settings for {{ $gateway->is_test_mode ? 'test' : 'production' }} environment:
                <strong>{{ implode(', ', $validation['missing']) }}</strong>
            </p>
            @if($gateway->code === 'authorize_net')
            <hr>
            <p class="mb-0">
                <i class="fas fa-info-circle"></i> <strong>Note:</strong> If settings are not configured here, the system will automatically use credentials from your .env file as fallback.
            </p>
            @endif
        </div>
        @endif

        @if($gateway->code === 'authorize_net' && $validation['valid'])
        <div class="alert alert-info">
            <h5 class="alert-heading"><i class="fas fa-database"></i> Using Database Configuration</h5>
            <p class="mb-0">
                This gateway is configured in the database. The system will use these credentials instead of .env values.
            </p>
        </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#test-settings">
                            <i class="fas fa-flask"></i> Test Settings
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#production-settings">
                            <i class="fas fa-globe"></i> Production Settings
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="test-settings">
                        <form action="{{ route('admin.payment-gateways.update-settings', $gateway) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="environment" value="test">
                            
                            @foreach($settingsSchema as $field)
                            <div class="mb-3">
                                <label class="form-label">
                                    {{ $field['label'] }}
                                    @if($field['required']) <span class="text-danger">*</span> @endif
                                </label>
                                @if($field['type'] === 'password')
                                <input type="password" 
                                       name="settings[{{ $field['key'] }}]" 
                                       class="form-control"
                                       placeholder="{{ $field['sensitive'] && $testSettings->firstWhere('setting_key', $field['key']) ? '••••••••' : '' }}">
                                <small class="form-text text-muted">Leave blank to keep existing value</small>
                                @elseif($field['type'] === 'select')
                                <select name="settings[{{ $field['key'] }}]" class="form-control">
                                    <option value="">Select {{ $field['label'] }}</option>
                                    @foreach($field['options'] as $value => $label)
                                    <option value="{{ $value }}" {{ ($testSettings->firstWhere('setting_key', $field['key'])?->setting_value ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @else
                                <input type="text" 
                                       name="settings[{{ $field['key'] }}]" 
                                       value="{{ $testSettings->firstWhere('setting_key', $field['key'])?->setting_value ?? '' }}"
                                       class="form-control">
                                @endif
                            </div>
                            @endforeach

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Test Settings
                            </button>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="production-settings">
                        <div class="alert alert-danger mb-3">
                            <i class="fas fa-exclamation-triangle"></i> These are PRODUCTION credentials. Changes will affect live transactions.
                        </div>

                        <form action="{{ route('admin.payment-gateways.update-settings', $gateway) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="environment" value="production">
                            
                            @foreach($settingsSchema as $field)
                            <div class="mb-3">
                                <label class="form-label">
                                    {{ $field['label'] }}
                                    @if($field['required']) <span class="text-danger">*</span> @endif
                                </label>
                                @if($field['type'] === 'password')
                                <input type="password" 
                                       name="settings[{{ $field['key'] }}]" 
                                       class="form-control"
                                       placeholder="{{ $field['sensitive'] && $productionSettings->firstWhere('setting_key', $field['key']) ? '••••••••' : '' }}">
                                <small class="form-text text-muted">Leave blank to keep existing value</small>
                                @elseif($field['type'] === 'select')
                                <select name="settings[{{ $field['key'] }}]" class="form-control">
                                    <option value="">Select {{ $field['label'] }}</option>
                                    @foreach($field['options'] as $value => $label)
                                    <option value="{{ $value }}" {{ ($productionSettings->firstWhere('setting_key', $field['key'])?->setting_value ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @else
                                <input type="text" 
                                       name="settings[{{ $field['key'] }}]" 
                                       value="{{ $productionSettings->firstWhere('setting_key', $field['key'])?->setting_value ?? '' }}"
                                       class="form-control">
                                @endif
                            </div>
                            @endforeach

                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-save"></i> Save Production Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function testConnection() {
        const btn = document.getElementById('test-btn');
        const status = document.getElementById('connection-status');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
        status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
        status.className = 'text-muted mb-3';
        
        fetch('{{ route("admin.payment-gateways.test", $gateway) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                status.innerHTML = '<i class="fas fa-check-circle"></i> Connected';
                status.className = 'text-success mb-3';
            } else {
                status.innerHTML = '<i class="fas fa-times-circle"></i> Failed';
                status.className = 'text-danger mb-3';
                alert('Connection failed: ' + data.message);
            }
        })
        .catch(error => {
            status.innerHTML = '<i class="fas fa-times-circle"></i> Error';
            status.className = 'text-danger mb-3';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plug"></i> Test Connection';
        });
    }
    </script>
</body>
</html>
