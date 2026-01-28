@extends('layouts.admin')

@section('title', 'State Transmission Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-paper-plane"></i> State Transmission Management</h1>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkSubmitModal">
                        <i class="fas fa-upload"></i> Bulk Submit
                    </button>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#bulkRetryModal">
                        <i class="fas fa-redo"></i> Bulk Retry
                    </button>
                    <a href="{{ route('admin.state-transmissions.export') }}" class="btn btn-success">
                        <i class="fas fa-download"></i> Export
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['total_transmissions']) }}</h4>
                            <p class="mb-0">Total Transmissions</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-paper-plane fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['successful_transmissions']) }}</h4>
                            <p class="mb-0">Successful</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['pending_transmissions']) }}</h4>
                            <p class="mb-0">Pending</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['failed_transmissions']) }}</h4>
                            <p class="mb-0">Failed</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Rate and Charts -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Success Rate</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 text-{{ $stats['success_rate'] >= 90 ? 'success' : ($stats['success_rate'] >= 70 ? 'warning' : 'danger') }}">
                        {{ $stats['success_rate'] }}%
                    </div>
                    <p class="text-muted">Overall transmission success rate</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Transmissions by State</h5>
                </div>
                <div class="card-body">
                    <canvas id="stateChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Transmissions by System</h5>
                </div>
                <div class="card-body">
                    <canvas id="systemChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Failures -->
    @if($recentFailures->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-exclamation-triangle text-warning"></i> Recent Failures</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Certificate</th>
                                    <th>Student</th>
                                    <th>State/System</th>
                                    <th>Error</th>
                                    <th>Failed At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentFailures as $failure)
                                <tr>
                                    <td>{{ $failure->certificate->certificate_number ?? 'N/A' }}</td>
                                    <td>{{ $failure->certificate->student_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $failure->state }}</span>
                                        <span class="badge bg-info">{{ $failure->system }}</span>
                                    </td>
                                    <td>
                                        <small class="text-danger">{{ Str::limit($failure->response_message, 50) }}</small>
                                    </td>
                                    <td>{{ $failure->updated_at->diffForHumans() }}</td>
                                    <td>
                                        @if($failure->canRetry())
                                            <button class="btn btn-sm btn-warning" onclick="retryTransmission({{ $failure->id }})">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.state-transmissions.show', $failure) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Connection Test Panel -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-plug"></i> Connection Tests</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary w-100" onclick="testConnection('FL')">
                                <i class="fas fa-plug"></i> Test Florida DICDS
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-success w-100" onclick="testConnection('MO')">
                                <i class="fas fa-plug"></i> Test Missouri DOR
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-warning w-100" onclick="testConnection('TX')">
                                <i class="fas fa-plug"></i> Test Texas TDLR
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-info w-100" onclick="testConnection('DE')">
                                <i class="fas fa-plug"></i> Test Delaware DMV
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.state-transmissions.dashboard') }}">
                <div class="row">
                    <div class="col-md-2">
                        <select name="state" class="form-select">
                            <option value="">All States</option>
                            <option value="FL" {{ $filters['state'] === 'FL' ? 'selected' : '' }}>Florida</option>
                            <option value="MO" {{ $filters['state'] === 'MO' ? 'selected' : '' }}>Missouri</option>
                            <option value="TX" {{ $filters['state'] === 'TX' ? 'selected' : '' }}>Texas</option>
                            <option value="DE" {{ $filters['state'] === 'DE' ? 'selected' : '' }}>Delaware</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="system" class="form-select">
                            <option value="">All Systems</option>
                            <option value="DICDS" {{ $filters['system'] === 'DICDS' ? 'selected' : '' }}>DICDS</option>
                            <option value="DOR" {{ $filters['system'] === 'DOR' ? 'selected' : '' }}>DOR</option>
                            <option value="TDLR" {{ $filters['system'] === 'TDLR' ? 'selected' : '' }}>TDLR</option>
                            <option value="DMV" {{ $filters['system'] === 'DMV' ? 'selected' : '' }}>DMV</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $filters['status'] === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="success" {{ $filters['status'] === 'success' ? 'selected' : '' }}>Success</option>
                            <option value="error" {{ $filters['status'] === 'error' ? 'selected' : '' }}>Error</option>
                            <option value="failed" {{ $filters['status'] === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}" placeholder="From Date">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}" placeholder="To Date">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Transmissions Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>State Transmissions ({{ $transmissions->total() }})</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary" onclick="selectAll()">Select All</button>
                <button class="btn btn-sm btn-outline-secondary" onclick="selectNone()">Select None</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll(this)">
                            </th>
                            <th>Certificate #</th>
                            <th>Student</th>
                            <th>State/System</th>
                            <th>Status</th>
                            <th>Response</th>
                            <th>Sent At</th>
                            <th>Retries</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transmissions as $transmission)
                        <tr>
                            <td>
                                <input type="checkbox" class="transmission-checkbox" value="{{ $transmission->id }}">
                            </td>
                            <td>
                                <strong>{{ $transmission->certificate->certificate_number ?? 'N/A' }}</strong>
                            </td>
                            <td>
                                {{ $transmission->certificate->student_name ?? 'N/A' }}<br>
                                <small class="text-muted">{{ $transmission->enrollment->user->email ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $transmission->state }}</span><br>
                                <span class="badge bg-info">{{ $transmission->system }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $transmission->status_color }}">
                                    {{ $transmission->formatted_status }}
                                </span>
                            </td>
                            <td>
                                @if($transmission->response_code)
                                    <small><strong>{{ $transmission->response_code }}</strong></small><br>
                                @endif
                                @if($transmission->response_message)
                                    <small class="text-muted">{{ Str::limit($transmission->response_message, 30) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($transmission->sent_at)
                                    {{ $transmission->sent_at->format('M j, Y H:i') }}
                                @else
                                    <span class="text-muted">Not sent</span>
                                @endif
                            </td>
                            <td>
                                @if($transmission->retry_count > 0)
                                    <span class="badge bg-warning">{{ $transmission->retry_count }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.state-transmissions.show', $transmission) }}" class="btn btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($transmission->canRetry())
                                        <button class="btn btn-outline-warning" onclick="retryTransmission({{ $transmission->id }})">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-paper-plane fa-3x text-muted mb-3"></i>
                                <h5>No transmissions found</h5>
                                <p class="text-muted">No state transmissions match your current filters.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($transmissions->hasPages())
        <div class="card-footer">
            {{ $transmissions->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Bulk Submit Modal -->
<div class="modal fade" id="bulkSubmitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Submit Certificates</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkSubmitForm">
                    <div class="mb-3">
                        <label class="form-label">State</label>
                        <select name="state_code" class="form-select" required>
                            <option value="">Select State</option>
                            <option value="FL">Florida</option>
                            <option value="MO">Missouri</option>
                            <option value="TX">Texas</option>
                            <option value="DE">Delaware</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Limit</label>
                        <input type="number" name="limit" class="form-control" value="50" min="1" max="100">
                        <div class="form-text">Maximum number of certificates to submit</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeBulkSubmit()">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Retry Modal -->
<div class="modal fade" id="bulkRetryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Retry Transmissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Selected transmissions: <span id="selectedCount">0</span>
                </div>
                <p>This will retry all selected failed transmissions. Only transmissions that can be retried will be processed.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="executeBulkRetry()">Retry Selected</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// State breakdown chart
const stateData = @json($stateBreakdown);
const stateCtx = document.getElementById('stateChart').getContext('2d');
new Chart(stateCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(stateData),
        datasets: [{
            data: Object.values(stateData),
            backgroundColor: ['#007bff', '#28a745', '#ffc107', '#17a2b8']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// System breakdown chart
const systemData = @json($systemBreakdown);
const systemCtx = document.getElementById('systemChart').getContext('2d');
new Chart(systemCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(systemData),
        datasets: [{
            data: Object.values(systemData),
            backgroundColor: ['#dc3545', '#fd7e14', '#20c997', '#6f42c1']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Checkbox functions
function toggleAll(checkbox) {
    const checkboxes = document.querySelectorAll('.transmission-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateSelectedCount();
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.transmission-checkbox');
    checkboxes.forEach(cb => cb.checked = true);
    document.getElementById('selectAllCheckbox').checked = true;
    updateSelectedCount();
}

function selectNone() {
    const checkboxes = document.querySelectorAll('.transmission-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateSelectedCount();
}

function updateSelectedCount() {
    const selected = document.querySelectorAll('.transmission-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selected;
}

// Retry transmission
function retryTransmission(id) {
    if (confirm('Retry this transmission?')) {
        fetch(`/admin/state-transmissions/${id}/retry`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Transmission retry queued successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}

// Test connection
function testConnection(stateCode) {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    button.disabled = true;
    
    fetch('/admin/state-transmissions/test-connection', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ state_code: stateCode })
    })
    .then(response => response.json())
    .then(data => {
        button.innerHTML = originalText;
        button.disabled = false;
        
        if (data.success) {
            alert(`${stateCode} connection test successful!`);
        } else {
            alert(`${stateCode} connection test failed: ` + data.error);
        }
    })
    .catch(error => {
        button.innerHTML = originalText;
        button.disabled = false;
        alert('Connection test failed: ' + error.message);
    });
}

// Bulk submit
function executeBulkSubmit() {
    const form = document.getElementById('bulkSubmitForm');
    const formData = new FormData(form);
    
    if (!formData.get('state_code')) {
        alert('Please select a state');
        return;
    }
    
    if (confirm(`Submit certificates for ${formData.get('state_code')}?`)) {
        fetch('/admin/state-transmissions/bulk-submit', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                state_code: formData.get('state_code'),
                limit: parseInt(formData.get('limit'))
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Bulk submit completed! Processed: ${data.results.total_processed}, Queued: ${data.results.successful_queued}, Failed: ${data.results.failed}`);
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}

// Bulk retry
function executeBulkRetry() {
    const selected = Array.from(document.querySelectorAll('.transmission-checkbox:checked')).map(cb => cb.value);
    
    if (selected.length === 0) {
        alert('Please select transmissions to retry');
        return;
    }
    
    if (confirm(`Retry ${selected.length} selected transmissions?`)) {
        fetch('/admin/state-transmissions/bulk-retry', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                transmission_ids: selected
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Bulk retry completed! Processed: ${data.results.total_processed}, Successful: ${data.results.successful_retries}, Failed: ${data.results.failed_retries}`);
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}

// Update selected count on checkbox change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('transmission-checkbox')) {
        updateSelectedCount();
    }
});
</script>
@endsection