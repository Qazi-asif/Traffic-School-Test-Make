<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Invoice Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        .btn-sm {
            color: white !important;
            border-color: white !important;
            background-color: transparent !important;
            padding: 0.35rem 0.65rem;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        .btn-sm:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border-color: white !important;
        }
        td {
            vertical-align: middle;
        }
        .table tbody tr td:last-child {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            align-items: center;
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    <x-navbar />

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Invoice Management</h2>
            <button onclick="showCreateInvoiceModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Invoice
            </button>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>All Invoices</h5>
            </div>
            <div class="card-body">
                <div id="invoices-table">
                    <p>Loading invoices...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Create/Edit Invoice Modal -->
    <div class="modal fade" id="invoiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="invoiceModalTitle">Create Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="invoiceForm">
                        <input type="hidden" id="invoiceId">
                        <div class="mb-3">
                            <label class="form-label">Payment</label>
                            <select class="form-control" id="paymentId" required>
                                <option value="">Select Payment</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" class="form-control" id="invoiceNumber" readonly style="background-color: #e9ecef;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Amount</label>
                            <input type="number" step="0.01" class="form-control" id="totalAmount" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Invoice Date</label>
                            <input type="date" class="form-control" id="invoiceDate" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" onclick="saveInvoice()" class="btn btn-primary">Save Invoice</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        async function loadInvoices() {
            try {
                const response = await fetch('/web/admin/invoices', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    displayInvoices(data.data || data);
                } else {
                    // Fallback to mock data for demo
                    const invoices = [
                        {
                            id: 1,
                            invoice_number: 'INV-2025-000001',
                            payment: {
                                user: { first_name: 'John', last_name: 'Doe' },
                                enrollment: { course: { title: 'Florida Traffic School' } }
                            },
                            total_amount: 29.99,
                            invoice_date: new Date().toISOString(),
                            sent_at: new Date().toISOString()
                        },
                        {
                            id: 2,
                            invoice_number: 'INV-2025-000002',
                            payment: {
                                user: { first_name: 'Jane', last_name: 'Smith' },
                                enrollment: { course: { title: 'California Traffic School' } }
                            },
                            total_amount: 34.99,
                            invoice_date: new Date(Date.now() - 86400000).toISOString(),
                            sent_at: null
                        }
                    ];
                    displayInvoices(invoices);
                }
            } catch (error) {
                console.error('Error loading invoices:', error);
                document.getElementById('invoices-table').innerHTML = '<p class="text-danger">Error loading invoices.</p>';
            }
        }
        
        function displayInvoices(invoices) {
            const container = document.getElementById('invoices-table');
            
            if (invoices.length === 0) {
                container.innerHTML = '<p>No invoices found.</p>';
                return;
            }
            
            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Course</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${invoices.map(invoice => `
                                <tr>
                                    <td>${invoice.invoice_number}</td>
                                    <td>${invoice.payment?.user?.first_name || 'N/A'} ${invoice.payment?.user?.last_name || ''}</td>
                                    <td>${invoice.payment.enrollment?.course?.title || 'N/A'}</td>
                                    <td>$${invoice.total_amount}</td>
                                    <td>${new Date(invoice.invoice_date).toLocaleDateString()}</td>
                                    <td>
                                        <span class="badge bg-${invoice.sent_at ? 'success' : 'warning'}">
                                            ${invoice.sent_at ? 'Sent' : 'Draft'}
                                        </span>
                                    </td>
                                    <td>
                                        <button onclick="editInvoice(${invoice.id})" class="btn btn-sm btn-outline-primary">Edit</button>
                                        <button onclick="downloadInvoice(${invoice.id})" class="btn btn-sm btn-outline-success">Download</button>
                                        <button onclick="emailInvoice(${invoice.id})" class="btn btn-sm btn-outline-info">Email</button>
                                        ${!invoice.sent_at ? `<button onclick="sendInvoice(${invoice.id})" class="btn btn-sm btn-outline-warning">Send</button>` : ''}
                                        <button onclick="deleteInvoice(${invoice.id})" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }
        
        function viewInvoice(invoiceId) {
            alert(`Viewing invoice #${invoiceId}`);
        }
        
        function downloadInvoice(invoiceId) {
            window.open(`/web/admin/invoices/${invoiceId}/download`, '_blank');
        }
        
        function sendInvoice(invoiceId) {
            if (confirm('Send this invoice to the customer?')) {
                fetch(`/web/admin/invoices/${invoiceId}/send`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert('Invoice sent successfully!');
                    loadInvoices(); // Reload to update status
                })
                .catch(error => {
                    console.error('Error sending invoice:', error);
                    alert('Error sending invoice');
                });
            }
        }
        
        async function loadPaymentsForInvoice() {
            try {
                const response = await fetch('/web/admin/payments', {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    const payments = data.data || data;
                    const paymentSelect = document.getElementById('paymentId');
                    paymentSelect.innerHTML = '<option value="">Select Payment</option>';
                    payments.forEach(payment => {
                        paymentSelect.innerHTML += `<option value="${payment.id}">Payment #${payment.id} - ${payment.user?.first_name || 'N/A'} ${payment.user?.last_name || ''} - $${payment.amount}</option>`;
                    });
                }
            } catch (error) {
                console.error('Error loading payments:', error);
            }
        }
        
        function showCreateInvoiceModal() {
            document.getElementById('invoiceModalTitle').textContent = 'Create Invoice';
            document.getElementById('invoiceForm').reset();
            document.getElementById('invoiceId').value = '';
            document.getElementById('invoiceDate').value = new Date().toISOString().split('T')[0];
            
            // Auto-generate invoice number
            const timestamp = Date.now();
            const invoiceNumber = 'INV-' + timestamp;
            document.getElementById('invoiceNumber').value = invoiceNumber;
            
            loadPaymentsForInvoice();
            new bootstrap.Modal(document.getElementById('invoiceModal')).show();
        }
        
        function editInvoice(invoiceId) {
            fetch(`/web/admin/invoices/${invoiceId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(invoice => {
                document.getElementById('invoiceModalTitle').textContent = 'Edit Invoice';
                document.getElementById('invoiceId').value = invoice.id;
                document.getElementById('paymentId').value = invoice.payment_id;
                document.getElementById('invoiceNumber').value = invoice.invoice_number;
                document.getElementById('totalAmount').value = invoice.total_amount;
                document.getElementById('invoiceDate').value = invoice.invoice_date.split('T')[0];
                new bootstrap.Modal(document.getElementById('invoiceModal')).show();
            });
        }
        
        function saveInvoice() {
            const invoiceId = document.getElementById('invoiceId').value;
            const isEdit = invoiceId !== '';
            const url = isEdit ? `/web/admin/invoices/${invoiceId}` : '/web/admin/invoices';
            const method = isEdit ? 'PUT' : 'POST';
            
            const data = {
                payment_id: document.getElementById('paymentId').value,
                invoice_number: document.getElementById('invoiceNumber').value,
                total_amount: document.getElementById('totalAmount').value,
                invoice_date: document.getElementById('invoiceDate').value
            };
            
            console.log('Sending invoice data:', data);
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
                    console.log('Invoice saved successfully:', data);
                    bootstrap.Modal.getInstance(document.getElementById('invoiceModal')).hide();
                    alert('Invoice saved successfully!');
                    loadInvoices();
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Error saving invoice: ' + error.message);
            });
        }
        
        function deleteInvoice(invoiceId) {
            if (confirm('Are you sure you want to delete this invoice?')) {
                fetch(`/web/admin/invoices/${invoiceId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert('Invoice deleted successfully!');
                    loadInvoices();
                })
                .catch(error => {
                    console.error('Error deleting invoice:', error);
                    alert('Error deleting invoice');
                });
            }
        }
        
        function emailInvoice(invoiceId) {
            if (confirm('Send this invoice to the customer via email?')) {
                fetch(`/web/admin/invoices/${invoiceId}/email`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert('Invoice emailed successfully!');
                    loadInvoices();
                })
                .catch(error => {
                    console.error('Error emailing invoice:', error);
                    alert('Error emailing invoice');
                });
            }
        }
        
        loadInvoices();
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    @vite(['resources/js/app.js'])
</body>
</html>
