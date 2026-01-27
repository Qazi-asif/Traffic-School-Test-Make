@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="container-fluid py-4">
    <div id="app">
        <user-list></user-list>
    </div>
    
    <!-- Fallback content -->
    <div id="fallback-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>User Management</h2>
            <button onclick="showAddForm()" class="btn btn-primary">Add User</button>
        </div>
        
        <!-- Search and Filter Controls -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" id="search-input" class="form-control" placeholder="Search users..." onkeyup="handleSearch()">
                    </div>
                    <div class="col-md-3">
                        <select id="role-filter" class="form-select" onchange="handleFilter()">
                            <option value="">All Roles</option>
                            <option value="1">Super Admin</option>
                            <option value="2">Admin</option>
                            <option value="3">Instructor</option>
                            <option value="4">Student</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="status-filter" class="form-select" onchange="handleFilter()">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button onclick="loadUsers()" class="btn btn-outline-secondary w-100">Refresh</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card h-100">
            <div class="card-body">
                <div id="users-table">
                    <p>Loading users...</p>
                </div>
            </div>
        </div>
        
        <!-- Add User Modal -->
        <div id="add-user-modal" class="modal" style="display: none; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Add New User</h5>
                        <button onclick="hideAddForm()" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="add-user-form">
                            <div class="mb-3">
                                <label>First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Role</label>
                                    <select name="role_id" class="form-select" required id="role-select">
                                        <option value="">Select Role</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button onclick="saveUser()" class="btn btn-primary">Save</button>
                            <button onclick="hideAddForm()" class="btn btn-secondary">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Edit User Modal -->
        <div id="edit-user-modal" class="modal" style="display: none; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Edit User</h5>
                        <button onclick="hideEditForm()" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="edit-user-form">
                            <input type="hidden" id="edit-user-id">
                            <div class="mb-3">
                                <label>First Name</label>
                                <input type="text" id="edit-first-name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Last Name</label>
                                <input type="text" id="edit-last-name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" id="edit-email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Role</label>
                                <select id="edit-role-select" class="form-control" required>
                                    <option value="">Select Role</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Password (leave blank to keep current)</label>
                                <input type="password" id="edit-password" class="form-control">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button onclick="updateUser()" class="btn btn-primary">Update</button>
                        <button onclick="hideEditForm()" class="btn btn-secondary">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let users = [];
        let roles = [];
        
        async function loadRoles() {
            try {
                // For now, use hardcoded roles since we don't have a roles API endpoint
                roles = [
                    {id: 1, name: 'Super Admin'},
                    {id: 2, name: 'Admin'},
                    {id: 3, name: 'Instructor'},
                    {id: 4, name: 'Student'}
                ];
                
                const roleSelect = document.getElementById('role-select');
                const editRoleSelect = document.getElementById('edit-role-select');
                const options = '<option value="">Select Role</option>' + 
                    roles.map(role => `<option value="${role.id}">${role.name}</option>`).join('');
                roleSelect.innerHTML = options;
                editRoleSelect.innerHTML = options;
            } catch (error) {
                console.error('Error loading roles:', error);
            }
        }
        
        function getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '{{ csrf_token() }}';
        }
        
        async function loadUsers(page = 1, search = '', roleId = '', status = '') {
            try {
                let url = `/web/users?page=${page}&per_page=100`;
                if (search) url += `&search=${encodeURIComponent(search)}`;
                if (roleId) url += `&role_id=${roleId}`;
                if (status) url += `&status=${status}`;
                
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const result = await response.json();
                    users = result.data || result; // Handle both {data: [...]} and [...] formats
                    displayUsers(result);
                } else {
                    document.getElementById('users-table').innerHTML = '<p class="text-danger">Error loading users.</p>';
                }
            } catch (error) {
                console.error('Error loading users:', error);
                document.getElementById('users-table').innerHTML = '<p class="text-danger">Error loading users.</p>';
            }
        }
        
        function handleSearch() {
            const search = document.getElementById('search-input').value;
            const roleId = document.getElementById('role-filter').value;
            const status = document.getElementById('status-filter').value;
            loadUsers(1, search, roleId, status);
        }
        
        function handleFilter() {
            const search = document.getElementById('search-input').value;
            const roleId = document.getElementById('role-filter').value;
            const status = document.getElementById('status-filter').value;
            loadUsers(1, search, roleId, status);
        }
        
        function displayUsers(result = null) {
            const container = document.getElementById('users-table');
            
            if (!users || !Array.isArray(users) || users.length === 0) {
                container.innerHTML = '<p>No users found.</p>';
                return;
            }
            
            let paginationInfo = '';
            if (result && result.total) {
                paginationInfo = `<div class="mb-3"><small class="text-muted">Showing ${users.length} of ${result.total} users</small></div>`;
            }
            
            container.innerHTML = paginationInfo + `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${users.map(user => `
                                <tr>
                                    <td>${user.first_name} ${user.last_name}</td>
                                    <td>${user.email}</td>
                                    <td><span class="badge bg-primary">${user.role?.name || 'N/A'}</span></td>
                                    <td><span class="badge ${user.status === 'active' ? 'bg-success' : 'bg-danger'}">${user.status}</span></td>
                                    <td>${new Date(user.created_at).toLocaleDateString()}</td>
                                    <td>
                                        <button onclick="editUser(${user.id})" class="btn btn-sm btn-outline-primary">Edit</button>
                                        <button onclick="deleteUser(${user.id})" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }
        
        function showAddForm() {
            loadRoles();
            document.getElementById('add-user-modal').style.display = 'block';
        }
        
        function hideAddForm() {
            document.getElementById('add-user-modal').style.display = 'none';
            document.getElementById('add-user-form').reset();
        }
        
        async function saveUser() {
            const form = document.getElementById('add-user-form');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('/web/users', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });
                
                if (response.ok) {
                    alert('User created successfully!');
                    hideAddForm();
                    loadUsers();
                } else {
                    const errorData = await response.json();
                    console.error('Validation errors:', errorData);
                    alert('Failed to create user: ' + (errorData.message || 'Validation failed'));
                }
            } catch (error) {
                console.error('Error creating user:', error);
                alert('Failed to create user');
            }
        }
        
        function editUser(userId) {
            const user = users.find(u => u.id === userId);
            if (!user) return;
            
            document.getElementById('edit-user-id').value = user.id;
            document.getElementById('edit-first-name').value = user.first_name || '';
            document.getElementById('edit-last-name').value = user.last_name || '';
            document.getElementById('edit-email').value = user.email || '';
            document.getElementById('edit-role-select').value = user.role_id || '';
            document.getElementById('edit-password').value = '';
            
            document.getElementById('edit-user-modal').style.display = 'block';
        }
        
        function hideEditForm() {
            document.getElementById('edit-user-modal').style.display = 'none';
        }
        
        async function updateUser() {
            const userId = document.getElementById('edit-user-id').value;
            const data = {
                first_name: document.getElementById('edit-first-name').value,
                last_name: document.getElementById('edit-last-name').value,
                email: document.getElementById('edit-email').value,
                role_id: document.getElementById('edit-role-select').value
            };
            
            const password = document.getElementById('edit-password').value;
            if (password) {
                data.password = password;
            }
            
            try {
                const response = await fetch(`/web/users/${userId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });
                
                if (response.ok) {
                    hideEditForm();
                    loadUsers();
                    alert('User updated successfully');
                } else {
                    const error = await response.json();
                    alert('Error: ' + (error.message || 'Failed to update user'));
                }
            } catch (error) {
                console.error('Error updating user:', error);
                alert('Error updating user');
            }
        }
        
        async function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                try {
                    const response = await fetch(`/web/users/${userId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (response.ok) {
                        alert('User deleted successfully!');
                        loadUsers();
                    } else {
                        alert('Failed to delete user');
                    }
                } catch (error) {
                    console.error('Error deleting user:', error);
                    alert('Failed to delete user');
                }
            }
        }
        
        // Show fallback and load users if Vue doesn't load
        setTimeout(() => {
            const vueApp = document.querySelector('#app user-list');
            if (!vueApp || vueApp.children.length === 0) {
                document.getElementById('fallback-content').style.display = 'block';
                loadRoles();
                loadUsers();
            }
        }, 1000);
    </script>
    
    @vite(['resources/js/app.js'])
</div>
@endsection
