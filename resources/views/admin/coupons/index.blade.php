<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupon Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        .container-fluid {
            padding: 20px;
        }
        .card {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .table {
            color: var(--text-primary);
        }
        .table thead {
            background-color: var(--accent);
            color: white;
        }
        .table tbody tr {
            border-color: var(--border);
        }
        .table tbody tr:hover {
            background-color: var(--hover);
        }
        .form-select, .form-control {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .form-select:focus, .form-control:focus {
            background-color: var(--bg-secondary);
            border-color: var(--accent);
            color: var(--text-primary);
        }
        .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
        }
        .btn-primary:hover {
            background-color: var(--hover);
            border-color: var(--hover);
        }
        .badge {
            font-size: 0.75em;
        }
        .coupon-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 1.1em;
        }
        .modal-content {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }
        .modal-header {
            border-bottom-color: var(--border);
        }
        .modal-footer {
            border-top-color: var(--border);
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-ticket-alt"></i> Coupon Management</h2>
                    <button class="btn btn-primary" onclick="showCreateModal()">
                        <i class="fas fa-plus"></i> Create Coupons
                    </button>
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

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i> All Coupons</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Usage Limit</th>
                                        <th>Times Used</th>
                                        <th>Expires</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($coupons as $coupon)
                                        <tr>
                                            <td>
                                                <span class="coupon-code">{{ $coupon->code }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst($coupon->type) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($coupon->type === 'percentage')
                                                    {{ $coupon->amount }}%
                                                @else
                                                    ${{ number_format($coupon->amount, 2) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($coupon->is_used)
                                                    <span class="badge bg-secondary">Used</span>
                                                @elseif(!$coupon->is_active)
                                                    <span class="badge bg-warning">Inactive</span>
                                                @elseif($coupon->expires_at && $coupon->expires_at->isPast())
                                                    <span class="badge bg-danger">Expired</span>
                                                @else
                                                    <span class="badge bg-success">Active</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($coupon->usage_limit)
                                                    <span class="badge bg-info">{{ $coupon->usage_limit }}</span>
                                                @else
                                                    <span class="text-muted">Unlimited</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $coupon->usage->count() }}</span>
                                            </td>
                                            <td>
                                                @if($coupon->expires_at)
                                                    {{ $coupon->expires_at->format('M j, Y') }}
                                                @else
                                                    <span class="text-muted">Never</span>
                                                @endif
                                            </td>
                                            <td>{{ $coupon->created_at->format('M j, Y') }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="editCoupon({{ $coupon->id }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="deleteCoupon({{ $coupon->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">
                                                <i class="fas fa-ticket-alt fa-3x mb-3"></i>
                                                <p>No coupons created yet.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Coupon Modal -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Coupons</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="/admin/coupons">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="code" class="form-label">Coupon Code (Optional)</label>
                            <input type="text" class="form-control" id="code" name="code" maxlength="6" placeholder="Leave blank for auto-generated">
                            <small class="form-text text-muted">Max 6 characters. Leave blank to auto-generate.</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Discount Type</label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="fixed">Fixed Amount ($)</option>
                                        <option value="percentage">Percentage (%)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount</label>
                                    <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                                    <small class="form-text text-muted" id="amount-help">Enter dollar amount or percentage</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" min="1" max="100" value="1" required>
                                    <small class="form-text text-muted">Number of coupons to create</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="usage_limit" class="form-label">Usage Limit (Optional)</label>
                                    <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1" placeholder="Unlimited">
                                    <small class="form-text text-muted">Max times this coupon can be used</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expires_at" class="form-label">Expiration Date (Optional)</label>
                                    <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Coupons</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Coupon Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Coupon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_code" class="form-label">Coupon Code</label>
                            <input type="text" class="form-control" id="edit_code" name="code" maxlength="6" required readonly>
                            <small class="form-text text-muted">Coupon code cannot be changed after creation.</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_type" class="form-label">Discount Type</label>
                                    <select class="form-select" id="edit_type" name="type" required>
                                        <option value="fixed">Fixed Amount ($)</option>
                                        <option value="percentage">Percentage (%)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_amount" class="form-label">Amount</label>
                                    <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0" required>
                                    <small class="form-text text-muted" id="edit-amount-help">Enter dollar amount or percentage</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_usage_limit" class="form-label">Usage Limit (Optional)</label>
                                    <input type="number" class="form-control" id="edit_usage_limit" name="usage_limit" min="1" placeholder="Unlimited">
                                    <small class="form-text text-muted">Max times this coupon can be used</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_used_count" class="form-label">Times Used</label>
                                    <input type="number" class="form-control" id="edit_used_count" name="used_count" readonly>
                                    <small class="form-text text-muted">Current usage count</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_expires_at" class="form-label">Expiration Date (Optional)</label>
                                    <input type="datetime-local" class="form-control" id="edit_expires_at" name="expires_at">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_is_active" class="form-label">Status</label>
                                    <select class="form-select" id="edit_is_active" name="is_active" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Changes will only affect future uses of this coupon. Past usage history will remain unchanged.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Coupon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let coupons = @json($coupons);
        
        function showCreateModal() {
            new bootstrap.Modal(document.getElementById('createModal')).show();
        }

        function editCoupon(id) {
            // Find the coupon data
            const coupon = coupons.find(c => c.id === id);
            if (!coupon) {
                alert('Coupon not found');
                return;
            }
            
            // Populate the edit form
            document.getElementById('edit_code').value = coupon.code;
            document.getElementById('edit_type').value = coupon.type;
            document.getElementById('edit_amount').value = coupon.amount;
            document.getElementById('edit_is_active').value = coupon.is_active ? '1' : '0';
            document.getElementById('edit_usage_limit').value = coupon.usage_limit || '';
            document.getElementById('edit_used_count').value = coupon.usage.length || 0;
            
            // Handle expiration date
            if (coupon.expires_at) {
                // Convert the date to the format expected by datetime-local input
                const date = new Date(coupon.expires_at);
                const localDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
                document.getElementById('edit_expires_at').value = localDate.toISOString().slice(0, 16);
            } else {
                document.getElementById('edit_expires_at').value = '';
            }
            
            // Update help text based on type
            updateEditAmountHelp();
            
            // Set up form submission
            const form = document.getElementById('editForm');
            form.onsubmit = function(e) {
                e.preventDefault();
                updateCoupon(id);
            };
            
            // Show the modal
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function updateCoupon(id) {
            const form = document.getElementById('editForm');
            const formData = new FormData(form);
            
            // Convert FormData to JSON with proper type conversion
            const data = {};
            for (let [key, value] of formData.entries()) {
                // Skip the code field since it's readonly
                if (key === 'code') continue;
                
                if (key === 'is_active') {
                    data[key] = value === '1';
                } else if (key === 'amount' || key === 'usage_limit') {
                    data[key] = parseFloat(value);
                } else {
                    data[key] = value;
                }
            }
            
            fetch(`/admin/coupons/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide modal
                    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                    
                    // Show success message and reload page
                    alert('Coupon updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating coupon: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating coupon');
            });
        }

        function deleteCoupon(id) {
            if (confirm('Are you sure you want to delete this coupon?')) {
                fetch(`/admin/coupons/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting coupon');
                });
            }
        }

        function updateAmountHelp() {
            const helpText = document.getElementById('amount-help');
            const typeSelect = document.getElementById('type');
            if (typeSelect.value === 'percentage') {
                helpText.textContent = 'Enter percentage (e.g., 10 for 10%)';
            } else {
                helpText.textContent = 'Enter dollar amount (e.g., 5.00 for $5)';
            }
        }

        function updateEditAmountHelp() {
            const helpText = document.getElementById('edit-amount-help');
            const typeSelect = document.getElementById('edit_type');
            if (typeSelect.value === 'percentage') {
                helpText.textContent = 'Enter percentage (e.g., 10 for 10%)';
            } else {
                helpText.textContent = 'Enter dollar amount (e.g., 5.00 for $5)';
            }
        }

        // Update amount help text based on type for create modal
        document.getElementById('type').addEventListener('change', updateAmountHelp);
        
        // Update amount help text based on type for edit modal
        document.getElementById('edit_type').addEventListener('change', updateEditAmountHelp);
    </script>

    <x-footer />
</body>
</html>