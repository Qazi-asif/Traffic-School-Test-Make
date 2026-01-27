<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Payments - Traffic School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .payment-card {
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            background: var(--bg-card);
        }
        .payment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 50px;
        }
        .status-completed { background: #516425; color: white; }
        .status-pending { background: #ffc107; color: #000; }
        .status-failed { background: #dc3545; color: white; }
        .status-cancelled { background: #6c757d; color: white; }
        .status-refunded { background: #17a2b8; color: white; }
        
        .payment-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent);
        }
        .payment-method-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-secondary);
            color: var(--text-primary);
        }
        .stats-card {
            background: linear-gradient(135deg, var(--accent), var(--hover));
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
        }
        .filter-tabs {
            border-bottom: 2px solid var(--border);
            margin-bottom: 2rem;
        }
        .filter-tab {
            padding: 0.75rem 1.5rem;
            border: none;
            background: none;
            color: var(--text-secondary);
            font-weight: 500;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }
        .filter-tab.active {
            color: var(--accent);
            border-bottom-color: var(--accent);
        }
        .filter-tab:hover {
            color: var(--text-primary);
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .btn-retry {
            background: #516425;
            border-color: #516425;
            color: white;
        }
        .btn-retry:hover {
            background: #3d4b1c;
            border-color: #3d4b1c;
            color: white;
        }
        .course-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .course-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: var(--bg-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--accent);
        }
        .search-box {
            position: relative;
        }
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }
        .search-box input {
            padding-left: 3rem;
        }
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .payment-timeline {
            position: relative;
            padding-left: 2rem;
        }
        .payment-timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border);
        }
        .timeline-item {
            position: relative;
            margin-bottom: 1rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -0.75rem;
            top: 0.5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--accent);
        }
        .mobile-responsive {
            display: block;
        }
        @media (max-width: 768px) {
            .desktop-only { display: none !important; }
            .mobile-responsive { display: block !important; }
            .payment-card { margin-bottom: 1rem; }
            .action-buttons { justify-content: center; }
            .course-info { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="fas fa-credit-card me-2"></i>
                    My Payments
                </h2>
                <p class="text-muted mb-0">Manage your course payments and enrollments</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="exportPayments()">
                    <i class="fas fa-download me-1"></i>
                    Export
                </button>
                <button class="btn btn-primary" onclick="refreshPayments()">
                    <i class="fas fa-sync-alt me-1"></i>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4" id="stats-section">
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0" id="total-payments">0</h3>
                            <small>Total Payments</small>
                        </div>
                        <i class="fas fa-receipt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0" id="total-amount">$0.00</h3>
                            <small>Total Spent</small>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0" id="pending-count">0</h3>
                            <small>Pending Payments</small>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0" id="completed-count">0</h3>
                            <small>Completed</small>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" id="search-input" placeholder="Search by course name, payment method, or amount...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2">
                            <select class="form-select" id="status-filter">
                                <option value="">All Statuses</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="refunded">Refunded</option>
                            </select>
                            <select class="form-select" id="date-filter">
                                <option value="">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">
                All Payments <span class="badge bg-secondary ms-1" id="all-count">0</span>
            </button>
            <button class="filter-tab" data-filter="pending">
                Pending <span class="badge bg-warning ms-1" id="pending-tab-count">0</span>
            </button>
            <button class="filter-tab" data-filter="completed">
                Completed <span class="badge" style="background: #516425;" id="completed-tab-count">0</span>
            </button>
            <button class="filter-tab" data-filter="failed">
                Failed <span class="badge bg-danger ms-1" id="failed-count">0</span>
            </button>
        </div>

        <!-- Payments List -->
        <div id="payments-container">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading your payments...</p>
            </div>
        </div>

        <!-- Pagination -->
        <nav aria-label="Payments pagination" id="pagination-container" style="display: none;">
            <ul class="pagination justify-content-center">
                <!-- Pagination will be generated by JavaScript -->
            </ul>
        </nav>
    </div>

    <!-- Payment Details Modal -->
    <div class="modal fade" id="paymentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="payment-details-content">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Retry Payment Modal -->
    <div class="modal fade" id="retryPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Retry Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You will be redirected to the payment page to complete this transaction.</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Make sure to complete the payment process to avoid losing your enrollment.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-retry" id="confirm-retry-btn">
                        <i class="fas fa-credit-card me-1"></i>
                        Continue to Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let allPayments = [];
        let filteredPayments = [];
        let currentPage = 1;
        const itemsPerPage = 10;
        let currentFilter = 'all';

        // Load payments on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadPayments();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Search functionality
            document.getElementById('search-input').addEventListener('input', debounce(filterPayments, 300));
            
            // Filter dropdowns
            document.getElementById('status-filter').addEventListener('change', filterPayments);
            document.getElementById('date-filter').addEventListener('change', filterPayments);
            
            // Filter tabs
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    currentFilter = this.dataset.filter;
                    filterPayments();
                });
            });
        }

        async function loadPayments() {
            try {
                const response = await fetch('/web/my-payments', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    allPayments = await response.json();
                    updateStats();
                    filterPayments();
                } else {
                    showError('Error loading payments. Please try again.');
                }
            } catch (error) {
                console.error('Error loading payments:', error);
                showError('Error loading payments. Please check your connection.');
            }
        }

        function updateStats() {
            const stats = {
                total: allPayments.length,
                totalAmount: allPayments.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0),
                pending: allPayments.filter(p => p.status === 'pending').length,
                completed: allPayments.filter(p => p.status === 'completed').length,
                failed: allPayments.filter(p => p.status === 'failed').length
            };

            document.getElementById('total-payments').textContent = stats.total;
            document.getElementById('total-amount').textContent = '$' + stats.totalAmount.toFixed(2);
            document.getElementById('pending-count').textContent = stats.pending;
            document.getElementById('completed-count').textContent = stats.completed;

            // Update tab counts
            document.getElementById('all-count').textContent = stats.total;
            document.getElementById('pending-tab-count').textContent = stats.pending;
            document.getElementById('completed-tab-count').textContent = stats.completed;
            document.getElementById('failed-count').textContent = stats.failed;
        }

        function filterPayments() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const statusFilter = document.getElementById('status-filter').value;
            const dateFilter = document.getElementById('date-filter').value;

            filteredPayments = allPayments.filter(payment => {
                // Text search
                const matchesSearch = !searchTerm || 
                    (payment.enrollment?.course?.title || '').toLowerCase().includes(searchTerm) ||
                    (payment.payment_method || '').toLowerCase().includes(searchTerm) ||
                    (payment.amount || '').toString().includes(searchTerm);

                // Status filter
                const matchesStatus = !statusFilter || payment.status === statusFilter;

                // Tab filter
                const matchesTab = currentFilter === 'all' || payment.status === currentFilter;

                // Date filter
                let matchesDate = true;
                if (dateFilter) {
                    const paymentDate = new Date(payment.created_at);
                    const now = new Date();
                    
                    switch(dateFilter) {
                        case 'today':
                            matchesDate = paymentDate.toDateString() === now.toDateString();
                            break;
                        case 'week':
                            const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                            matchesDate = paymentDate >= weekAgo;
                            break;
                        case 'month':
                            matchesDate = paymentDate.getMonth() === now.getMonth() && 
                                         paymentDate.getFullYear() === now.getFullYear();
                            break;
                        case 'year':
                            matchesDate = paymentDate.getFullYear() === now.getFullYear();
                            break;
                    }
                }

                return matchesSearch && matchesStatus && matchesTab && matchesDate;
            });

            currentPage = 1;
            displayPayments();
        }

        function displayPayments() {
            const container = document.getElementById('payments-container');
            
            if (filteredPayments.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <h4>No payments found</h4>
                        <p>Try adjusting your search criteria or filters.</p>
                    </div>
                `;
                document.getElementById('pagination-container').style.display = 'none';
                return;
            }

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pagePayments = filteredPayments.slice(startIndex, endIndex);

            container.innerHTML = pagePayments.map(payment => createPaymentCard(payment)).join('');
            
            // Show pagination if needed
            if (filteredPayments.length > itemsPerPage) {
                displayPagination();
                document.getElementById('pagination-container').style.display = 'block';
            } else {
                document.getElementById('pagination-container').style.display = 'none';
            }
        }

        function createPaymentCard(payment) {
            const isEnrollment = payment.is_pending_enrollment;
            const course = payment.enrollment?.course;
            const statusClass = `status-${payment.status}`;
            const paymentDate = new Date(payment.created_at).toLocaleDateString();
            
            return `
                <div class="payment-card card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="course-info">
                                    <div class="course-icon">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">${course?.title || 'Course Not Found'}</h5>
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-calendar me-1"></i>
                                            ${paymentDate}
                                            ${payment.payment_method ? `• ${payment.payment_method}` : ''}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="payment-amount">$${parseFloat(payment.amount || 0).toFixed(2)}</div>
                                ${payment.discount_amount > 0 ? `
                                    <small class="text-muted">
                                        <s>$${parseFloat(payment.original_amount || 0).toFixed(2)}</s>
                                        <span class="text-success ms-1">-$${parseFloat(payment.discount_amount).toFixed(2)}</span>
                                    </small>
                                ` : ''}
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex flex-column align-items-end">
                                    <span class="status-badge ${statusClass} mb-2">
                                        ${getStatusIcon(payment.status)} ${payment.status.toUpperCase()}
                                    </span>
                                    <div class="action-buttons">
                                        ${createActionButtons(payment)}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${payment.coupon_code ? `
                            <div class="row mt-2">
                                <div class="col-12">
                                    <small class="text-success">
                                        <i class="fas fa-tag me-1"></i>
                                        Coupon applied: ${payment.coupon_code}
                                    </small>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        function getStatusIcon(status) {
            const icons = {
                completed: 'fas fa-check-circle',
                pending: 'fas fa-clock',
                failed: 'fas fa-times-circle',
                cancelled: 'fas fa-ban',
                refunded: 'fas fa-undo'
            };
            return `<i class="${icons[status] || 'fas fa-question-circle'}"></i>`;
        }

        function createActionButtons(payment) {
            let buttons = [];
            
            if (payment.status === 'pending') {
                if (payment.is_pending_enrollment) {
                    buttons.push(`
                        <button class="btn btn-retry btn-sm" onclick="retryPayment(${payment.enrollment_id})">
                            <i class="fas fa-credit-card me-1"></i>
                            Pay Now
                        </button>
                    `);
                    buttons.push(`
                        <button class="btn btn-outline-danger btn-sm" onclick="cancelPayment(${payment.enrollment_id})">
                            <i class="fas fa-times me-1"></i>
                            Cancel
                        </button>
                    `);
                } else {
                    buttons.push(`
                        <button class="btn btn-retry btn-sm" onclick="retryPayment(${payment.enrollment_id})">
                            <i class="fas fa-redo me-1"></i>
                            Retry
                        </button>
                    `);
                }
            }
            
            if (payment.status === 'completed' && payment.invoice) {
                buttons.push(`
                    <a href="/invoices/${payment.invoice.id}/download" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download me-1"></i>
                        Invoice
                    </a>
                `);
            }
            
            if (!payment.is_pending_enrollment) {
                buttons.push(`
                    <button class="btn btn-outline-info btn-sm" onclick="showPaymentDetails('${payment.id}')">
                        <i class="fas fa-eye me-1"></i>
                        Details
                    </button>
                `);
            }
            
            return buttons.join('');
        }

        async function retryPayment(enrollmentId) {
            document.getElementById('confirm-retry-btn').onclick = async function() {
                this.innerHTML = '<span class="loading-spinner"></span> Processing...';
                this.disabled = true;
                
                try {
                    const response = await fetch('/web/payments/retry', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ enrollment_id: enrollmentId })
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        window.location.href = data.redirect_url;
                    } else {
                        throw new Error(data.message || 'Failed to retry payment');
                    }
                } catch (error) {
                    showError('Error processing retry: ' + error.message);
                    this.innerHTML = '<i class="fas fa-credit-card me-1"></i> Continue to Payment';
                    this.disabled = false;
                }
            };
            
            new bootstrap.Modal(document.getElementById('retryPaymentModal')).show();
        }

        async function cancelPayment(enrollmentId) {
            if (!confirm('Are you sure you want to cancel this payment? This will remove your enrollment from the course.')) {
                return;
            }
            
            try {
                const response = await fetch('/web/payments/cancel', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ enrollment_id: enrollmentId })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showSuccess('Payment cancelled successfully');
                    loadPayments(); // Refresh the list
                } else {
                    throw new Error(data.message || 'Failed to cancel payment');
                }
            } catch (error) {
                showError('Error cancelling payment: ' + error.message);
            }
        }

        function showPaymentDetails(paymentId) {
            const payment = allPayments.find(p => p.id == paymentId);
            if (!payment) return;
            
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Payment Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Payment ID:</strong></td><td>${payment.id}</td></tr>
                            <tr><td><strong>Amount:</strong></td><td>$${parseFloat(payment.amount).toFixed(2)}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="status-badge status-${payment.status}">${payment.status}</span></td></tr>
                            <tr><td><strong>Method:</strong></td><td>${payment.payment_method || 'N/A'}</td></tr>
                            <tr><td><strong>Gateway ID:</strong></td><td>${payment.gateway_payment_id || 'N/A'}</td></tr>
                            <tr><td><strong>Date:</strong></td><td>${new Date(payment.created_at).toLocaleString()}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Course Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Course:</strong></td><td>${payment.enrollment?.course?.title || 'N/A'}</td></tr>
                            <tr><td><strong>State:</strong></td><td>${payment.enrollment?.course?.state_code || 'N/A'}</td></tr>
                            <tr><td><strong>Type:</strong></td><td>${payment.enrollment?.course?.course_type || 'N/A'}</td></tr>
                            <tr><td><strong>Duration:</strong></td><td>${payment.enrollment?.course?.duration || 'N/A'} min</td></tr>
                        </table>
                    </div>
                </div>
                
                ${payment.coupon_code ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Discount Applied</h6>
                            <div class="alert alert-success">
                                <i class="fas fa-tag me-2"></i>
                                Coupon <strong>${payment.coupon_code}</strong> saved you $${parseFloat(payment.discount_amount).toFixed(2)}
                                <br><small>Original amount: $${parseFloat(payment.original_amount).toFixed(2)}</small>
                            </div>
                        </div>
                    </div>
                ` : ''}
            `;
            
            document.getElementById('payment-details-content').innerHTML = content;
            new bootstrap.Modal(document.getElementById('paymentDetailsModal')).show();
        }

        function displayPagination() {
            const totalPages = Math.ceil(filteredPayments.length / itemsPerPage);
            const container = document.getElementById('pagination-container').querySelector('.pagination');
            
            let paginationHTML = '';
            
            // Previous button
            paginationHTML += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
                </li>
            `;
            
            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    paginationHTML += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                        </li>
                    `;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    paginationHTML += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            
            // Next button
            paginationHTML += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
                </li>
            `;
            
            container.innerHTML = paginationHTML;
        }

        function changePage(page) {
            const totalPages = Math.ceil(filteredPayments.length / itemsPerPage);
            if (page < 1 || page > totalPages) return;
            
            currentPage = page;
            displayPayments();
        }

        function refreshPayments() {
            const btn = event.target;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<span class="loading-spinner"></span> Refreshing...';
            btn.disabled = true;
            
            loadPayments().finally(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            });
        }

        function exportPayments() {
            const csvContent = "data:text/csv;charset=utf-8," + 
                "Date,Course,Amount,Status,Payment Method,Coupon Code,Discount\n" +
                filteredPayments.map(p => [
                    new Date(p.created_at).toLocaleDateString(),
                    `"${p.enrollment?.course?.title || 'N/A'}"`,
                    p.amount,
                    p.status,
                    p.payment_method || 'N/A',
                    p.coupon_code || '',
                    p.discount_amount || 0
                ].join(',')).join('\n');
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `payments_${new Date().toISOString().split('T')[0]}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function showError(message) {
            // You can implement a toast notification system here
            alert('Error: ' + message);
        }

        function showSuccess(message) {
            // You can implement a toast notification system here
            alert('Success: ' + message);
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    </script>
    
    @vite(['resources/js/app.js'])
    <x-footer />
</body>
</html>