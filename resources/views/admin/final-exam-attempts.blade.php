@extends('layouts.app')

@section('title', 'Final Exam Attempts')

@section('content')
<style>
.modal-backdrop {
    display: none !important;
}
#pushNotificationModal {
    display: none !important;
}
.modal {
    display: none !important;
}
.modal.show {
    z-index: 999999 !important;
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    display: block !important;
}
.modal-dialog {
    z-index: 999999 !important;
    position: relative !important;
}
.modal-content {
    z-index: 999999 !important;
}
footer {
    display: none !important;
}
body {
    padding-bottom: 0 !important;
}
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Final Exam Attempts Management</h3>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" id="searchUser" class="form-control" placeholder="Search by user name or email...">
                        </div>
                        <div class="col-md-3">
                            <select id="filterStatus" class="form-control">
                                <option value="">All Status</option>
                                <option value="passed">Passed</option>
                                <option value="failed">Failed</option>
                                <option value="exhausted">Attempts Exhausted</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" onclick="loadAttempts()">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <button class="btn btn-warning ms-2" onclick="clearAllModals()" title="Clear stuck modals">
                                <i class="fas fa-times-circle"></i> Clear Modals
                            </button>
                        </div>
                    </div>

                    <!-- Results Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Course</th>
                                    <th>Attempts Used</th>
                                    <th>Max Attempts</th>
                                    <th>Best Score</th>
                                    <th>Status</th>
                                    <th>Last Attempt</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="attemptsTable">
                                <tr>
                                    <td colspan="8" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Increase Attempts Modal -->
<div class="modal fade" id="increaseAttemptsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Increase Final Exam Attempts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="userInfo" class="mb-3"></div>
                <div class="mb-3">
                    <label class="form-label">Additional Attempts</label>
                    <input type="number" id="additionalAttempts" class="form-control" min="1" max="10" value="1">
                    <small class="text-muted">Number of additional attempts to grant</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason (Optional)</label>
                    <textarea id="reason" class="form-control" rows="3" placeholder="Reason for granting additional attempts..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="grantAdditionalAttempts()">Grant Attempts</button>
            </div>
        </div>
    </div>
</div>

<!-- Attempt Details Modal -->
<div class="modal fade" id="attemptDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Attempt Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="attemptDetailsContent">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentEnrollmentId = null;

document.addEventListener('DOMContentLoaded', function() {
    // Remove all stuck backdrops
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    
    // Hide all modals
    document.querySelectorAll('.modal').forEach(modal => {
        modal.style.display = 'none';
        modal.classList.remove('show');
    });
    
    loadAttempts();
});

function showIncreaseModal(enrollmentId, userName, courseTitle) {
    currentEnrollmentId = enrollmentId;
    document.getElementById('userInfo').innerHTML = `
        <div class="alert alert-info">
            <strong>User:</strong> ${userName}<br>
            <strong>Course:</strong> ${courseTitle}
        </div>
    `;
    document.getElementById('additionalAttempts').value = 1;
    document.getElementById('reason').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('increaseAttemptsModal'));
    modal.show();
}

async function loadAttempts() {
    try {
        const search = document.getElementById('searchUser').value;
        const status = document.getElementById('filterStatus').value;
        
        const response = await fetch(`/api/admin/final-exam-attempts?search=${search}&status=${status}`);
        const data = await response.json();
        
        const attempts = Array.isArray(data) ? data : (data.data || []);
        displayAttempts(attempts);
    } catch (error) {
        console.error('Error loading attempts:', error);
        document.getElementById('attemptsTable').innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading data</td></tr>';
    }
}

function displayAttempts(attempts) {
    const tbody = document.getElementById('attemptsTable');
    
    if (attempts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No attempts found</td></tr>';
        return;
    }
    
    tbody.innerHTML = attempts.map(attempt => {
        const statusBadge = getStatusBadge(attempt);
        const canIncrease = attempt.attempts_used >= attempt.max_attempts && !attempt.passed;
        
        return `
            <tr>
                <td>
                    <strong>${attempt.user_name}</strong><br>
                    <small class="text-muted">${attempt.user_email}</small>
                </td>
                <td>${attempt.course_title}</td>
                <td>${attempt.attempts_used}</td>
                <td>${attempt.max_attempts}</td>
                <td>${attempt.best_score || 'N/A'}%</td>
                <td>${statusBadge}</td>
                <td>${attempt.last_attempt ? new Date(attempt.last_attempt).toLocaleDateString() : 'Never'}</td>
                <td>
                    <button class="btn btn-sm btn-info me-1" onclick="viewAttemptDetails(${attempt.enrollment_id})">
                        <i class="fas fa-eye"></i> Details
                    </button>
                    ${canIncrease ? 
                        `<button class="btn btn-sm btn-warning" onclick="showIncreaseModal(${attempt.enrollment_id}, '${attempt.user_name}', '${attempt.course_title}')">
                            <i class="fas fa-plus"></i> Add Attempts
                        </button>` : ''
                    }
                </td>
            </tr>
        `;
    }).join('');
}

function getStatusBadge(attempt) {
    if (attempt.passed) {
        return '<span class="badge bg-success">Passed</span>';
    } else if (attempt.attempts_used >= attempt.max_attempts) {
        return '<span class="badge bg-danger">Attempts Exhausted</span>';
    } else if (attempt.attempts_used > 0) {
        return '<span class="badge bg-warning">In Progress</span>';
    } else {
        return '<span class="badge bg-secondary">Not Started</span>';
    }
}

async function grantAdditionalAttempts() {
    try {
        const additionalAttempts = document.getElementById('additionalAttempts').value;
        const reason = document.getElementById('reason').value;
        
        if (!additionalAttempts || additionalAttempts < 1) {
            alert('Please enter a valid number of additional attempts');
            return;
        }
        
        const response = await fetch('/api/admin/final-exam-attempts/increase', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                enrollment_id: currentEnrollmentId,
                additional_attempts: parseInt(additionalAttempts),
                reason: reason
            })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('increaseAttemptsModal')).hide();
            alert('Additional attempts granted successfully!');
            loadAttempts();
        } else {
            alert('Error granting attempts: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error granting attempts:', error);
        alert('Error granting attempts: ' + error.message);
    }
}

async function viewAttemptDetails(enrollmentId) {
    try {
        document.getElementById('attemptDetailsContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        
        const modal = new bootstrap.Modal(document.getElementById('attemptDetailsModal'));
        modal.show();
        
        const response = await fetch(`/api/admin/final-exam-attempts/${enrollmentId}/details`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const details = await response.json();
        
        document.getElementById('attemptDetailsContent').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>User Information</h6>
                    <p><strong>Name:</strong> ${details.user?.name || 'N/A'}</p>
                    <p><strong>Email:</strong> ${details.user?.email || 'N/A'}</p>
                    <p><strong>Course:</strong> ${details.course?.title || 'N/A'}</p>
                </div>
                <div class="col-md-6">
                    <h6>Attempt Summary</h6>
                    <p><strong>Total Attempts:</strong> ${details.attempts?.length || 0}</p>
                    <p><strong>Max Allowed:</strong> ${details.max_attempts || 'N/A'}</p>
                    <p><strong>Best Score:</strong> ${details.best_score || 'N/A'}%</p>
                </div>
            </div>
            
            <h6 class="mt-4">Attempt History</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Attempt</th>
                            <th>Score</th>
                            <th>Result</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${(details.attempts || []).map((attempt, index) => `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${attempt.score || 0}%</td>
                                <td>
                                    <span class="badge bg-${attempt.passed ? 'success' : 'danger'}">
                                        ${attempt.passed ? 'Passed' : 'Failed'}
                                    </span>
                                </td>
                                <td>${attempt.created_at ? new Date(attempt.created_at).toLocaleString() : 'N/A'}</td>
                            </tr>
                        `).join('') || '<tr><td colspan="4" class="text-center">No attempts found</td></tr>'}
                    </tbody>
                </table>
            </div>
        `;
        
    } catch (error) {
        console.error('Error loading details:', error);
        document.getElementById('attemptDetailsContent').innerHTML = `
            <div class="alert alert-danger">
                <strong>Error loading details:</strong> ${error.message}
            </div>
        `;
    }
}

document.getElementById('searchUser').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        loadAttempts();
    }
});
</script>
@endsection
