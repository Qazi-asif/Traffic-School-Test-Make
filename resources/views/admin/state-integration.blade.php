<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>State Integration Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>State Integration & Automation</h2>
            <div>
                <a href="/admin/manage-counties" class="btn btn-info me-2">
                    <i class="fas fa-map"></i> Manage Counties & Courts
                </a>
                <button onclick="showCreateModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add State Configuration
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5>Active States</h5>
                        <h3 id="activeStates">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5>Pending Queue</h5>
                        <h3 id="pendingQueue">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Completed Today</h5>
                        <h3 id="completedToday">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5>Failed</h5>
                        <h3 id="failedSubmissions">0</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Configurations List -->
        <div class="card">
            <div class="card-header">
                <h5>State Configurations</h5>
            </div>
            <div class="card-body">
                <div id="configurations-table">
                    <p>Loading configurations...</p>
                </div>
            </div>
        </div>

        <!-- Submission Queue -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between">
                <h5>Submission Queue</h5>
                <button onclick="processQueue()" class="btn btn-sm btn-success">
                    <i class="fas fa-play"></i> Process Queue
                </button>
            </div>
            <div class="card-body">
                <div id="queue-table">
                    <p>Loading queue...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="configModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create State Configuration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="configForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">State Code</label>
                                    <input type="text" class="form-control" id="stateCode" maxlength="2" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">State Name</label>
                                    <input type="text" class="form-control" id="stateName" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Submission Method</label>
                            <select class="form-control" id="submissionMethod" required>
                                <option value="">Select Method</option>
                                <option value="api">API</option>
                                <option value="portal">Portal</option>
                                <option value="email">Email</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Certificate Template</label>
                            <input type="text" class="form-control" id="certificateTemplate" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button onclick="saveConfiguration()" class="btn btn-primary">Save Configuration</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function loadConfigurations() {
            try {
                const response = await fetch('/web/admin/state-configurations', {
                    headers: { 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const configs = await response.json();
                    displayConfigurations(configs);
                } else {
                    document.getElementById('configurations-table').innerHTML = '<p class="text-danger">Error loading configurations</p>';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('configurations-table').innerHTML = '<p class="text-danger">Error loading configurations</p>';
            }
        }

        function displayConfigurations(configs) {
            const container = document.getElementById('configurations-table');
            
            // Update active states count
            const activeCount = configs.filter(config => config.is_active).length;
            document.getElementById('activeStates').textContent = activeCount;
            
            if (configs.length === 0) {
                container.innerHTML = '<p>No configurations found. <a href="#" onclick="showCreateModal()">Create one now</a></p>';
                return;
            }

            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>State</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${configs.map(config => `
                                <tr>
                                    <td><strong>${config.state_code}</strong><br><small>${config.state_name}</small></td>
                                    <td><span class="badge bg-primary">${config.submission_method.toUpperCase()}</span></td>
                                    <td><span class="badge ${config.is_active ? 'bg-success' : 'bg-secondary'}">${config.is_active ? 'Active' : 'Inactive'}</span></td>
                                    <td>
                                        <button onclick="testConnection('${config.state_code}')" class="btn btn-sm btn-outline-info">Test</button>
                                        <button onclick="deleteConfig(${config.id})" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        function showCreateModal() {
            document.getElementById('configForm').reset();
            new bootstrap.Modal(document.getElementById('configModal')).show();
        }

        async function saveConfiguration() {
            const data = {
                state_code: document.getElementById('stateCode').value,
                state_name: document.getElementById('stateName').value,
                submission_method: document.getElementById('submissionMethod').value,
                certificate_template: document.getElementById('certificateTemplate').value,
                is_active: true
            };

            try {
                const response = await fetch('/web/admin/state-configurations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('configModal')).hide();
                    loadConfigurations();
                    alert('Configuration saved successfully!');
                } else {
                    alert('Error saving configuration');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error saving configuration');
            }
        }

        async function testConnection(stateCode) {
            try {
                const response = await fetch(`/web/admin/state-configurations/${stateCode}/test-connection`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                const result = await response.json();
                alert(result.message);
            } catch (error) {
                alert('Error testing connection');
            }
        }

        async function deleteConfig(id) {
            if (confirm('Are you sure?')) {
                try {
                    await fetch(`/web/admin/state-configurations/${id}`, { 
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        credentials: 'same-origin'
                    });
                    loadConfigurations();
                    alert('Configuration deleted');
                } catch (error) {
                    alert('Error deleting configuration');
                }
            }
        }

        async function processQueue() {
            try {
                const response = await fetch('/web/admin/submission-queue/process-pending', { 
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                const result = await response.json();
                alert(result.message);
                loadStats();
                loadQueue();
            } catch (error) {
                alert('Error processing queue');
            }
        }

        async function loadStats() {
            try {
                const response = await fetch('/web/admin/submission-queue/stats', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                const stats = await response.json();
                
                document.getElementById('pendingQueue').textContent = stats.pending || 0;
                document.getElementById('completedToday').textContent = stats.completed || 0;
                document.getElementById('failedSubmissions').textContent = stats.failed || 0;
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        async function loadQueue() {
            try {
                const response = await fetch('/web/admin/submission-queue', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    displayQueue(data.data || data);
                } else {
                    document.getElementById('queue-table').innerHTML = '<p class="text-danger">Error loading queue</p>';
                }
            } catch (error) {
                console.error('Error loading queue:', error);
                document.getElementById('queue-table').innerHTML = '<p class="text-danger">No queue items found</p>';
            }
        }

        function displayQueue(items) {
            const container = document.getElementById('queue-table');
            
            if (items.length === 0) {
                container.innerHTML = '<p>No items in submission queue</p>';
                return;
            }

            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Certificate</th>
                                <th>State</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Attempts</th>
                                <th>Next Attempt</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${items.map(item => `
                                <tr>
                                    <td>#${item.certificate_id}</td>
                                    <td>${item.state_configuration?.state_code || 'N/A'}</td>
                                    <td><span class="badge ${getPriorityBadge(item.priority)}">${item.priority.toUpperCase()}</span></td>
                                    <td><span class="badge ${getStatusBadge(item.status)}">${item.status.toUpperCase()}</span></td>
                                    <td>${item.attempts}/${item.max_attempts}</td>
                                    <td>${item.next_attempt_at ? new Date(item.next_attempt_at).toLocaleString() : 'N/A'}</td>
                                    <td>
                                        ${item.status === 'failed' ? `<button onclick="retrySubmission(${item.id})" class="btn btn-sm btn-outline-warning">Retry</button>` : ''}
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        function getPriorityBadge(priority) {
            const badges = {
                high: 'bg-danger',
                normal: 'bg-primary',
                low: 'bg-secondary'
            };
            return badges[priority] || 'bg-secondary';
        }

        function getStatusBadge(status) {
            const badges = {
                pending: 'bg-warning',
                processing: 'bg-info',
                completed: 'bg-success',
                failed: 'bg-danger',
                retry: 'bg-warning'
            };
            return badges[status] || 'bg-secondary';
        }

        async function retrySubmission(id) {
            try {
                const response = await fetch(`/web/admin/submission-queue/${id}/retry`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    alert('Submission queued for retry');
                    loadQueue();
                    loadStats();
                }
            } catch (error) {
                alert('Error retrying submission');
            }
        }

        // Load data on page load
        loadConfigurations();
        loadStats();
        loadQueue();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
