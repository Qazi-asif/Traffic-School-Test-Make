@extends('layouts.app')

@section('title', 'DICDS User Management')

@section('content')
<div class="container-fluid" style="padding: 2rem;">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">DICDS User Management</h1>
                <div class="btn-group">
                    <button class="btn btn-outline-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Search Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Status:</label>
                            <select id="statusFilter" class="form-control" onchange="loadUsers()">
                                <option value="">All Users</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search:</label>
                            <input id="searchFilter" placeholder="Search by name or email" class="form-control" oninput="loadUsers()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button onclick="loadUsers()" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h5>User Management</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTable">
                                <tr><td colspan="5">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div id="statusModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update User Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">New Status:</label>
                    <select id="newStatus" class="form-control">
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-check">
                    <input id="sendEmail" class="form-check-input" type="checkbox" checked>
                    <label class="form-check-label">Send email notification</label>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="confirmStatusUpdate()" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="passwordModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Generate a temporary password for <span id="resetUserName"></span>?</p>
                <div id="tempPasswordDisplay" class="alert alert-success" style="display: none;">
                    <strong>Temporary Password:</strong> <span id="tempPassword"></span>
                    <br><small>Please provide this to the user securely.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="confirmPasswordReset()" class="btn btn-warning">Generate Password</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Role Modal -->
<div id="roleModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update User Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Update role for <strong><span id="roleUserName"></span></strong></p>
                <div class="mb-3">
                    <label class="form-label">Select Role:</label>
                    <select id="newRole" class="form-control">
                        <option value="">Loading roles...</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="confirmRoleUpdate()" class="btn btn-primary">Update Role</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedUserId = null;
let availableRoles = [];

document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    loadRoles();
});

async function loadRoles() {
    try {
        const response = await fetch('/api/dicds/user-management/roles');
        const data = await response.json();
        availableRoles = data.data || [];
    } catch (error) {
        console.error('Error loading roles:', error);
    }
}

async function loadUsers() {
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchFilter').value;
    
    try {
        const response = await fetch(`/api/dicds/user-management/users?status=${status}&search=${search}`);
        const data = await response.json();
        
        const tbody = document.getElementById('usersTable');
        tbody.innerHTML = (data.data || []).map(user => `
            <tr>
                <td>${user.name || 'N/A'}</td>
                <td>${user.email || 'N/A'}</td>
                <td><span class="badge bg-${getStatusClass(user.status)}">${user.status || 'unknown'}</span></td>
                <td>${user.role?.name || 'No Role'}</td>
                <td>
                    <button onclick="updateUserStatus(${user.id}, '${escapeHtml(user.name || 'User')}', '${user.status || 'active'}')" class="btn btn-sm btn-outline-primary">
                        Update Status
                    </button>
                    <button onclick="resetPassword(${user.id}, '${escapeHtml(user.name || 'User')}')" class="btn btn-sm btn-outline-warning">
                        Reset Password
                    </button>
                    <button onclick="updateRole(${user.id}, '${escapeHtml(user.name || 'User')}', ${user.role?.id || 'null'})" class="btn btn-sm btn-outline-info">
                        Update Role
                    </button>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="5">No users found</td></tr>';
        
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function getStatusClass(status) {
    const classes = { active: 'success', pending: 'warning', inactive: 'secondary' };
    return classes[status] || 'secondary';
}

function updateUserStatus(userId, userName, currentStatus) {
    selectedUserId = userId;
    document.getElementById('newStatus').value = currentStatus;
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

function resetPassword(userId, userName) {
    selectedUserId = userId;
    document.getElementById('resetUserName').textContent = userName;
    document.getElementById('tempPasswordDisplay').style.display = 'none';
    new bootstrap.Modal(document.getElementById('passwordModal')).show();
}

function updateRole(userId, userName, currentRoleId) {
    selectedUserId = userId;
    document.getElementById('roleUserName').textContent = userName;
    
    const roleSelect = document.getElementById('newRole');
    roleSelect.innerHTML = availableRoles.map(role => 
        `<option value="${role.id}" ${role.id == currentRoleId ? 'selected' : ''}>${role.name}</option>`
    ).join('');
    
    new bootstrap.Modal(document.getElementById('roleModal')).show();
}

async function confirmStatusUpdate() {
    const newStatus = document.getElementById('newStatus').value;
    const sendEmail = document.getElementById('sendEmail').checked;
    
    try {
        await fetch(`/api/dicds/user-management/users/${selectedUserId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status: newStatus, send_email: sendEmail })
        });
        
        bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
        loadUsers();
        alert('User status updated successfully!');
    } catch (error) {
        console.error('Error updating status:', error);
        alert('Error updating status. Please try again.');
    }
}

async function confirmPasswordReset() {
    try {
        const response = await fetch(`/api/dicds/user-management/users/${selectedUserId}/reset-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        document.getElementById('tempPassword').textContent = data.temp_password;
        document.getElementById('tempPasswordDisplay').style.display = 'block';
    } catch (error) {
        console.error('Error resetting password:', error);
        alert('Error resetting password. Please try again.');
    }
}

async function confirmRoleUpdate() {
    const newRoleId = document.getElementById('newRole').value;
    
    try {
        await fetch(`/api/dicds/user-management/users/${selectedUserId}/role`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ role_id: newRoleId })
        });
        
        bootstrap.Modal.getInstance(document.getElementById('roleModal')).hide();
        loadUsers();
        alert('User role updated successfully!');
    } catch (error) {
        console.error('Error updating role:', error);
        alert('Error updating role. Please try again.');
    }
}
</script>
@endsection
