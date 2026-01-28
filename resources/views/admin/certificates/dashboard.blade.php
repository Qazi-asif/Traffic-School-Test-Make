@extends('layouts.admin')

@section('title', 'Certificate Management Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-certificate"></i> Certificate Management</h1>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
                        <i class="fas fa-tasks"></i> Bulk Actions
                    </button>
                    <a href="{{ route('admin.certificates.export') }}" class="btn btn-success">
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
                            <h4>{{ number_format($stats['total_certificates']) }}</h4>
                            <p class="mb-0">Total Certificates</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-certificate fa-2x"></i>
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
                            <h4>{{ number_format($stats['sent_certificates']) }}</h4>
                            <p class="mb-0">Sent to Students</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-paper-plane fa-2x"></i>
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
                            <h4>{{ number_format($stats['pending_certificates']) }}</h4>
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
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['state_submitted']) }}</h4>
                            <p class="mb-0">State Submitted</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-upload fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- State Breakdown Chart -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Certificates by State</h5>
                </div>
                <div class="card-body">
                    <canvas id="stateChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($certificates->take(5) as $certificate)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $certificate->certificate_number }}</strong><br>
                                <small class="text-muted">{{ $certificate->student_name }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $certificate->status === 'sent' ? 'success' : 'warning' }}">
                                    {{ ucfirst($certificate->status) }}
                                </span><br>
                                <small class="text-muted">{{ $certificate->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach
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
            <form method="GET" action="{{ route('admin.certificates.dashboard') }}">
                <div class="row">
                    <div class="col-md-2">
                        <select name="state_code" class="form-select">
                            <option value="">All States</option>
                            <option value="FL" {{ request('state_code') === 'FL' ? 'selected' : '' }}>Florida</option>
                            <option value="MO" {{ request('state_code') === 'MO' ? 'selected' : '' }}>Missouri</option>
                            <option value="TX" {{ request('state_code') === 'TX' ? 'selected' : '' }}>Texas</option>
                            <option value="DE" {{ request('state_code') === 'DE' ? 'selected' : '' }}>Delaware</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="generated" {{ request('status') === 'generated' ? 'selected' : '' }}>Generated</option>
                            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From Date">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To Date">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search...">
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

    <!-- Certificates Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Certificates ({{ $certificates->total() }})</h5>
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
                            <th>Course</th>
                            <th>State</th>
                            <th>Score</th>
                            <th>Completion Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificates as $certificate)
                        <tr>
                            <td>
                                <input type="checkbox" class="certificate-checkbox" value="{{ $certificate->id }}">
                            </td>
                            <td>
                                <strong>{{ $certificate->certificate_number }}</strong>
                            </td>
                            <td>
                                {{ $certificate->student_name }}<br>
                                <small class="text-muted">{{ $certificate->enrollment->user->email ?? 'N/A' }}</small>
                            </td>
                            <td>{{ $certificate->course_title }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $certificate->state_code }}</span>
                            </td>
                            <td>{{ number_format($certificate->final_exam_score, 1) }}%</td>
                            <td>{{ $certificate->completion_date->format('M j, Y') }}</td>
                            <td>
                                @switch($certificate->status)
                                    @case('generated')
                                        <span class="badge bg-warning">Generated</span>
                                        @break
                                    @case('sent')
                                        <span class="badge bg-success">Sent</span>
                                        @break
                                    @case('verified')
                                        <span class="badge bg-info">Verified</span>
                                        @break
                                    @case('submitted')
                                        <span class="badge bg-primary">Submitted</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($certificate->status) }}</span>
                                @endswitch
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewCertificate({{ $certificate->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="emailCertificate({{ $certificate->id }})">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                    <button class="btn btn-outline-info" onclick="downloadCertificate({{ $certificate->id }})">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteCertificate({{ $certificate->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                                <h5>No certificates found</h5>
                                <p class="text-muted">No certificates match your current filters.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($certificates->hasPages())
        <div class="card-footer">
            {{ $certificates->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkActionForm">
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select name="action" class="form-select" required>
                            <option value="">Select Action</option>
                            <option value="email">Email Certificates</option>
                            <option value="regenerate">Regenerate Certificates</option>
                            <option value="delete">Delete Certificates</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Selected certificates: <span id="selectedCount">0</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeBulkAction()">Execute</button>
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
const ctx = document.getElementById('stateChart').getContext('2d');
new Chart(ctx, {
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

// Checkbox functions
function toggleAll(checkbox) {
    const checkboxes = document.querySelectorAll('.certificate-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateSelectedCount();
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.certificate-checkbox');
    checkboxes.forEach(cb => cb.checked = true);
    document.getElementById('selectAllCheckbox').checked = true;
    updateSelectedCount();
}

function selectNone() {
    const checkboxes = document.querySelectorAll('.certificate-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateSelectedCount();
}

function updateSelectedCount() {
    const selected = document.querySelectorAll('.certificate-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selected;
}

// Certificate actions
function viewCertificate(id) {
    window.open(`/admin/certificates/${id}/view`, '_blank');
}

function emailCertificate(id) {
    if (confirm('Send certificate email to student?')) {
        fetch(`/admin/certificates/${id}/email`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Certificate emailed successfully!');
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}

function downloadCertificate(id) {
    window.open(`/admin/certificates/${id}/download`, '_blank');
}

function deleteCertificate(id) {
    if (confirm('Are you sure you want to delete this certificate?')) {
        fetch(`/admin/certificates/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}

function executeBulkAction() {
    const selected = Array.from(document.querySelectorAll('.certificate-checkbox:checked')).map(cb => cb.value);
    const action = document.querySelector('[name="action"]').value;
    
    if (!action) {
        alert('Please select an action');
        return;
    }
    
    if (selected.length === 0) {
        alert('Please select certificates');
        return;
    }
    
    if (confirm(`Execute ${action} on ${selected.length} certificates?`)) {
        fetch('/admin/certificates/bulk-action', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: action,
                certificate_ids: selected
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Bulk action completed successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}

// Update selected count on checkbox change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('certificate-checkbox')) {
        updateSelectedCount();
    }
});
</script>
@endsection