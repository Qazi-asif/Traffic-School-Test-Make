@extends('layouts.app')

@section('title', 'Missouri Form 4444 Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Missouri Form 4444 Management</h3>
                    <div>
                        <button class="btn btn-info" onclick="loadExpiringForms()">
                            <i class="fas fa-exclamation-triangle"></i> Expiring Forms
                        </button>
                        <button class="btn btn-primary" onclick="refreshForms()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="statusFilter">Status:</label>
                            <select id="statusFilter" class="form-control" onchange="filterForms()">
                                <option value="">All Statuses</option>
                                <option value="ready_for_submission">Ready for Submission</option>
                                <option value="awaiting_court_signature">Awaiting Court Signature</option>
                                <option value="submitted_to_dor">Submitted to DOR</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="methodFilter">Submission Method:</label>
                            <select id="methodFilter" class="form-control" onchange="filterForms()">
                                <option value="">All Methods</option>
                                <option value="point_reduction">Point Reduction</option>
                                <option value="court_ordered">Court Ordered</option>
                                <option value="insurance_discount">Insurance Discount</option>
                                <option value="voluntary">Voluntary</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="searchInput">Search:</label>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search by name, email, or form number" onkeyup="filterForms()">
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <div>
                                <button class="btn btn-warning btn-block" onclick="showExpiringOnly()">
                                    <i class="fas fa-clock"></i> Show Expiring (≤3 days)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Forms Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="formsTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Form Number</th>
                                    <th>Student</th>
                                    <th>Email</th>
                                    <th>Completion Date</th>
                                    <th>Submission Deadline</th>
                                    <th>Days Remaining</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="formsTableBody">
                                <!-- Forms will be loaded here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Loading indicator -->
                    <div id="loadingIndicator" class="text-center" style="display: none;">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p>Loading forms...</p>
                    </div>

                    <!-- No results message -->
                    <div id="noResultsMessage" class="text-center" style="display: none;">
                        <p class="text-muted">No forms found matching your criteria.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Form 4444</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="emailForm">
                    <div class="form-group">
                        <label for="emailAddress">Email Address:</label>
                        <input type="email" id="emailAddress" class="form-control" required>
                    </div>
                    <input type="hidden" id="emailFormId">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendEmail()">Send Email</button>
            </div>
        </div>
    </div>
</div>

<script>
let allForms = [];

document.addEventListener('DOMContentLoaded', function() {
    loadForms();
});

function loadForms() {
    showLoading(true);
    
    fetch('/api/missouri/forms/all', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        allForms = data;
        displayForms(allForms);
        showLoading(false);
    })
    .catch(error => {
        console.error('Error loading forms:', error);
        showLoading(false);
        showAlert('Error loading forms: ' + error.message, 'danger');
    });
}

function displayForms(forms) {
    const tbody = document.getElementById('formsTableBody');
    
    if (forms.length === 0) {
        tbody.innerHTML = '';
        document.getElementById('noResultsMessage').style.display = 'block';
        return;
    }
    
    document.getElementById('noResultsMessage').style.display = 'none';
    
    tbody.innerHTML = forms.map(form => {
        const daysRemaining = calculateDaysRemaining(form.submission_deadline);
        const isExpiring = daysRemaining <= 3 && daysRemaining >= 0;
        const isExpired = daysRemaining < 0;
        
        return `
            <tr class="${isExpiring ? 'table-warning' : ''} ${isExpired ? 'table-danger' : ''}">
                <td>
                    <strong>${form.form_number}</strong>
                    ${form.court_signature_required ? '<br><small class="text-info">Court Signature Required</small>' : ''}
                </td>
                <td>${form.user.first_name} ${form.user.last_name}</td>
                <td>${form.user.email}</td>
                <td>${formatDate(form.completion_date)}</td>
                <td>${formatDate(form.submission_deadline)}</td>
                <td>
                    <span class="badge ${getDaysRemainingBadgeClass(daysRemaining)}">
                        ${daysRemaining >= 0 ? daysRemaining + ' days' : 'Expired (' + Math.abs(daysRemaining) + ' days ago)'}
                    </span>
                </td>
                <td>
                    <span class="badge badge-secondary">
                        ${form.submission_method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                    </span>
                </td>
                <td>
                    <span class="badge ${getStatusBadgeClass(form.status)}">
                        ${form.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-primary" onclick="downloadForm(${form.id})" title="Download PDF">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="btn btn-info" onclick="showEmailModal(${form.id}, '${form.user.email}')" title="Email Form">
                            <i class="fas fa-envelope"></i>
                        </button>
                        ${form.status !== 'submitted_to_dor' ? `
                            <button class="btn btn-success" onclick="markAsSubmitted(${form.id})" title="Mark as Submitted">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function calculateDaysRemaining(deadline) {
    const now = new Date();
    const deadlineDate = new Date(deadline);
    const diffTime = deadlineDate - now;
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

function getDaysRemainingBadgeClass(days) {
    if (days < 0) return 'badge-danger';
    if (days <= 3) return 'badge-warning';
    if (days <= 7) return 'badge-info';
    return 'badge-success';
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'ready_for_submission': return 'badge-primary';
        case 'awaiting_court_signature': return 'badge-warning';
        case 'submitted_to_dor': return 'badge-success';
        case 'expired': return 'badge-danger';
        default: return 'badge-secondary';
    }
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString();
}

function filterForms() {
    const statusFilter = document.getElementById('statusFilter').value;
    const methodFilter = document.getElementById('methodFilter').value;
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    
    let filtered = allForms.filter(form => {
        const matchesStatus = !statusFilter || form.status === statusFilter;
        const matchesMethod = !methodFilter || form.submission_method === methodFilter;
        const matchesSearch = !searchInput || 
            form.user.first_name.toLowerCase().includes(searchInput) ||
            form.user.last_name.toLowerCase().includes(searchInput) ||
            form.user.email.toLowerCase().includes(searchInput) ||
            form.form_number.toLowerCase().includes(searchInput);
        
        return matchesStatus && matchesMethod && matchesSearch;
    });
    
    displayForms(filtered);
}

function showExpiringOnly() {
    const expiring = allForms.filter(form => {
        const daysRemaining = calculateDaysRemaining(form.submission_deadline);
        return daysRemaining <= 3 && daysRemaining >= 0;
    });
    
    displayForms(expiring);
}

function downloadForm(formId) {
    window.open(`/missouri/form4444/${formId}/download`, '_blank');
}

function showEmailModal(formId, email) {
    document.getElementById('emailFormId').value = formId;
    document.getElementById('emailAddress').value = email;
    $('#emailModal').modal('show');
}

function sendEmail() {
    const formId = document.getElementById('emailFormId').value;
    const email = document.getElementById('emailAddress').value;
    
    fetch(`/missouri/form4444/${formId}/email`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Form 4444 sent successfully!', 'success');
            $('#emailModal').modal('hide');
        } else {
            showAlert('Error sending email: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error sending email:', error);
        showAlert('Error sending email: ' + error.message, 'danger');
    });
}

function markAsSubmitted(formId) {
    if (!confirm('Mark this form as submitted to Missouri DOR?')) {
        return;
    }
    
    fetch(`/missouri/form4444/${formId}/submit-dor`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Form marked as submitted!', 'success');
            loadForms(); // Refresh the list
        } else {
            showAlert('Error updating form: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error updating form:', error);
        showAlert('Error updating form: ' + error.message, 'danger');
    });
}

function refreshForms() {
    loadForms();
}

function loadExpiringForms() {
    fetch('/api/missouri/expiring-forms', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        allForms = data;
        displayForms(allForms);
        showAlert(`Found ${data.length} expiring forms`, 'info');
    })
    .catch(error => {
        console.error('Error loading expiring forms:', error);
        showAlert('Error loading expiring forms: ' + error.message, 'danger');
    });
}

function showLoading(show) {
    document.getElementById('loadingIndicator').style.display = show ? 'block' : 'none';
}

function showAlert(message, type) {
    // Create and show bootstrap alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.row'));
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>
@endsection