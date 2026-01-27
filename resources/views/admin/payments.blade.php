<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Management</title>
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
            <h2>Payment Management</h2>
            <button onclick="showCreatePaymentModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Payment
            </button>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>All Payments</h5>
            </div>
            <div class="card-body">
                <div id="payments-table">
                    <p>Loading payments...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Create/Edit Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalTitle">Create Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        <input type="hidden" id="paymentId">
                        <div class="mb-3">
                            <label class="form-label">Student Email</label>
                            <select class="form-control" id="userEmail" required>
                                <option value="">Select Student</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course</label>
                            <select class="form-control" id="courseId" required>
                                <option value="">Select Course</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" class="form-control" id="amount" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-control" id="paymentMethod" required>
                                <option value="credit_card">Credit Card</option>
                                <option value="paypal">PayPal</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" id="status" required>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" onclick="savePayment()" class="btn btn-primary">Save Payment</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        async function loadPayments() {
            try {
                console.log('Loading payments...');
                const response = await fetch('/web/admin/payments', {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (response.ok) {
                    const data = await response.json();
                    console.log('Payments data:', data);
                    displayPayments(data.data || data);
                } else {
                    const errorText = await response.text();
                    console.error('Failed to load payments:', response.status, errorText);
                    document.getElementById('payments-table').innerHTML = '<p class="text-danger">Error loading payments: ' + response.status + '</p>';
                }
            } catch (error) {
                console.error('Error loading payments:', error);
                document.getElementById('payments-table').innerHTML = '<p class="text-danger">Error loading payments: ' + error.message + '</p>';
            }
        }
        
        function displayPayments(payments) {
            const container = document.getElementById('payments-table');
            
            if (payments.length === 0) {
                container.innerHTML = '<p>No payments found.</p>';
                return;
            }
            
            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Amount</th>
                                <th>Gateway</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${payments.map(payment => `
                                <tr>
                                    <td>#${payment.id}</td>
                                    <td>
                                        ${payment.user?.first_name || "N/A"} ${payment.user?.last_name || ""}
                                        <br><small class="text-muted">${payment.user?.email || "N/A"}</small>
                                    </td>
                                    <td>${payment.enrollment?.course?.title || 'N/A'}</td>
                                    <td>$${payment.amount}</td>
                                    <td><span class="badge bg-info">${payment.gateway}</span></td>
                                    <td><span class="badge bg-${payment.status === 'completed' ? 'success' : payment.status === 'pending' ? 'warning' : 'danger'}">${payment.status}</span></td>
                                    <td>${new Date(payment.created_at).toLocaleDateString()}</td>
                                    <td>
                                        <button onclick="editPayment(${payment.id})" class="btn btn-sm btn-outline-primary">Edit</button>
                                        <button onclick="downloadPaymentPDF(${payment.id})" class="btn btn-sm btn-outline-success">PDF</button>
                                        <button onclick="emailPaymentReceipt(${payment.id})" class="btn btn-sm btn-outline-info">Email</button>
                                        ${payment.status === 'completed' ? `<button onclick="refundPayment(${payment.id})" class="btn btn-sm btn-outline-warning">Refund</button>` : ''}
                                        <button onclick="deletePayment(${payment.id})" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }
        
        function viewPayment(paymentId) {
            alert(`Viewing payment #${paymentId}`);
        }
        
        function refundPayment(paymentId) {
            if (confirm('Are you sure you want to refund this payment?')) {
                const amount = prompt('Enter refund amount:');
                const reason = prompt('Enter refund reason:');
                
                if (amount && reason) {
                    fetch(`/web/admin/payments/${paymentId}/refund`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ amount: parseFloat(amount), reason: reason })
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert('Refund processed successfully!');
                        loadPayments(); // Reload the payments
                    })
                    .catch(error => {
                        console.error('Error processing refund:', error);
                        alert('Error processing refund');
                    });
                }
            }
        }
        
        async function loadCourses() {
            try {
                const response = await fetch('/web/courses', {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const courses = await response.json();
                    const courseSelect = document.getElementById('courseId');
                    courseSelect.innerHTML = '<option value="">Select Course</option>';
                    courses.forEach(course => {
                        courseSelect.innerHTML += `<option value="${course.id}">${course.title}</option>`;
                    });
                }
            } catch (error) {
                console.error('Error loading courses:', error);
            }
        }

        async function loadUsers() {
            try {
                const response = await fetch('/web/users', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    const users = data.data || data; // Handle paginated response
                    const userSelect = document.getElementById('userEmail');
                    
                    // Clear existing options except the first one
                    userSelect.innerHTML = '<option value="">Select Student</option>';
                    
                    users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.email;
                        option.textContent = `${user.first_name} ${user.last_name} (${user.email})`;
                        userSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }

        function showCreatePaymentModal() {
            document.getElementById('paymentModalTitle').textContent = 'Create Payment';
            document.getElementById('paymentForm').reset();
            document.getElementById('paymentId').value = '';
            loadCourses();
            loadUsers();
            new bootstrap.Modal(document.getElementById('paymentModal')).show();
        }
        
        function editPayment(paymentId) {
            loadUsers(); // Load users first
            loadCourses(); // Load courses first
            
            fetch(`/web/admin/payments/${paymentId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(payment => {
                document.getElementById('paymentModalTitle').textContent = 'Edit Payment';
                document.getElementById('paymentId').value = payment.id;
                
                // Set the selected user email in dropdown after a small delay
                setTimeout(() => {
                    document.getElementById('userEmail').value = payment.user.email;
                }, 200);
                
                document.getElementById('amount').value = payment.amount;
                document.getElementById('paymentMethod').value = payment.payment_method;
                document.getElementById('status').value = payment.status;
                new bootstrap.Modal(document.getElementById('paymentModal')).show();
            });
        }
        
        function savePayment() {
            const paymentId = document.getElementById('paymentId').value;
            const isEdit = paymentId !== '';
            const url = isEdit ? `/web/admin/payments/${paymentId}` : '/web/admin/payments';
            const method = isEdit ? 'PUT' : 'POST';
            
            const data = {
                user_email: document.getElementById('userEmail').value,
                course_id: document.getElementById('courseId').value,
                amount: document.getElementById('amount').value,
                payment_method: document.getElementById('paymentMethod').value,
                status: document.getElementById('status').value
            };
            
            console.log('Sending payment data:', data);
            console.log('URL:', url, 'Method:', method);
            
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Error response text:', text);
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Success response data:', data);
                if (data.error) {
                    console.error('Server error:', data.error);
                    alert('Error: ' + data.error);
                } else {
                    console.log('Payment saved successfully:', data);
                    bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                    alert('Payment saved successfully!');
                    loadPayments();
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Error saving payment: ' + error.message);
            });
        }
        
        function deletePayment(paymentId) {
            if (confirm('Are you sure you want to delete this payment?')) {
                fetch(`/web/admin/payments/${paymentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert('Payment deleted successfully!');
                    loadPayments();
                })
                .catch(error => {
                    console.error('Error deleting payment:', error);
                    alert('Error deleting payment');
                });
            }
        }
        
        function downloadPaymentPDF(paymentId) {
            window.open(`/web/admin/payments/${paymentId}/pdf`, '_blank');
        }
        
        function emailPaymentReceipt(paymentId) {
            if (confirm('Send payment receipt to customer?')) {
                fetch(`/web/admin/payments/${paymentId}/email`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert('Payment receipt sent successfully!');
                })
                .catch(error => {
                    console.error('Error sending receipt:', error);
                    alert('Error sending receipt');
                });
            }
        }
        
        loadPayments();
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    @vite(['resources/js/app.js'])
</body>
</html>
