@extends('layouts.app')

@section('title', 'DICDS Access Requests')

@section('content')
<div class="container-fluid" style="padding: 2rem;">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">DICDS Access Requests</h1>
                <div class="btn-group">
                    <button class="btn btn-outline-success" onclick="requestAccess()">
                        <i class="fas fa-plus"></i> Request Access
                    </button>
                </div>
            </div>

            <!-- Access Requests Table -->
            <div class="card">
                <div class="card-header">
                    <h5>Pending Access Requests</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Desired Application</th>
                                    <th>Desired Role</th>
                                    <th>User Group</th>
                                    <th>Status</th>
                                    <th>Requested</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="accessRequests">
                                <tr><td colspan="7">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Request Access Form -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Request Additional Access</h5>
                </div>
                <div class="card-body">
                    <form id="accessRequestForm">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Desired Application:</label>
                                <select class="form-control" id="desiredApplication">
                                    <option value="Driver School Certificates">Driver School Certificates</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Desired Role:</label>
                                <select class="form-control" id="desiredRole">
                                    <option value="">Select Role</option>
                                    <option value="DRS_Provider_Admin">DRS Provider Admin</option>
                                    <option value="DRS_Provider_User">DRS Provider User</option>
                                    <option value="DRS_School_Admin">DRS School Admin</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">User Group:</label>
                                <input type="text" class="form-control" id="userGroup" placeholder="Course Provider Name">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadAccessRequests();
    
    document.getElementById('accessRequestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitAccessRequest();
    });
});

async function loadAccessRequests() {
    try {
        const response = await fetch('/api/dicds/access-requests');
        const data = await response.json();
        
        const tbody = document.getElementById('accessRequests');
        tbody.innerHTML = data.data.map(request => `
            <tr>
                <td>${request.user?.name || 'Unknown'}</td>
                <td>${request.desired_application}</td>
                <td>${request.desired_role}</td>
                <td>${request.user_group}</td>
                <td><span class="badge bg-warning">${request.status}</span></td>
                <td>${new Date(request.created_at).toLocaleDateString()}</td>
                <td>
                    <button onclick="approveRequest(${request.id})" class="btn btn-sm btn-success">Approve</button>
                    <button onclick="denyRequest(${request.id})" class="btn btn-sm btn-danger">Deny</button>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="7">No requests found</td></tr>';
        
    } catch (error) {
        console.error('Error loading access requests:', error);
    }
}

async function submitAccessRequest() {
    const formData = {
        desired_application: document.getElementById('desiredApplication').value,
        desired_role: document.getElementById('desiredRole').value,
        user_group: document.getElementById('userGroup').value
    };
    
    try {
        const response = await fetch('/api/dicds/access-requests', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(formData)
        });
        
        if (response.ok) {
            alert('Access request submitted successfully!');
            document.getElementById('accessRequestForm').reset();
            loadAccessRequests();
        }
    } catch (error) {
        console.error('Error submitting request:', error);
    }
}

async function approveRequest(id) {
    try {
        await fetch(`/api/dicds/access-requests/${id}/approve`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status: 'approved' })
        });
        
        alert('Request approved successfully!');
        loadAccessRequests();
    } catch (error) {
        console.error('Error approving request:', error);
    }
}

async function denyRequest(id) {
    try {
        await fetch(`/api/dicds/access-requests/${id}/approve`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status: 'denied' })
        });
        
        alert('Request denied successfully!');
        loadAccessRequests();
    } catch (error) {
        console.error('Error denying request:', error);
    }
}

function requestAccess() {
    document.getElementById('userGroup').focus();
}
</script>
@endsection
