@extends('layouts.app')

@section('title', 'Manage Enrollments')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Student Enrollments</h2>
    </div>
    
    <!-- Search and Filter Controls -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" id="search-input" class="form-control" placeholder="Search students..." onkeyup="handleSearch()">
                </div>
                <div class="col-md-3">
                    <select id="status-filter" class="form-select" onchange="handleFilter()">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="expired">Expired</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="payment-filter" class="form-select" onchange="handleFilter()">
                        <option value="">All Payments</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button onclick="loadEnrollments()" class="btn btn-outline-secondary w-100">Refresh</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="enrollments-table">
                <p>Loading enrollments...</p>
            </div>
        </div>
    </div>
</div>

<script>
    let enrollments = [];
    
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '{{ csrf_token() }}';
    }
    
    async function loadEnrollments(page = 1, search = '', status = '', paymentStatus = '') {
        try {
            let url = `/web/enrollments?page=${page}&per_page=100`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (status) url += `&status=${status}`;
            if (paymentStatus) url += `&payment_status=${paymentStatus}`;
            
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const result = await response.json();
                enrollments = result.data || result;
                displayEnrollments(result);
            } else {
                document.getElementById('enrollments-table').innerHTML = '<p class="text-danger">Error loading enrollments.</p>';
            }
        } catch (error) {
            console.error('Error loading enrollments:', error);
            document.getElementById('enrollments-table').innerHTML = '<p class="text-danger">Error loading enrollments.</p>';
        }
    }
    
    function displayEnrollments(result = null) {
        const container = document.getElementById('enrollments-table');
        
        if (!enrollments || !Array.isArray(enrollments) || enrollments.length === 0) {
            container.innerHTML = '<p>No enrollments found.</p>';
            return;
        }
        
        let paginationInfo = '';
        if (result && result.total) {
            paginationInfo = `<div class="mb-3"><small class="text-muted">Showing ${enrollments.length} of ${result.total} enrollments</small></div>`;
        }
        
        container.innerHTML = paginationInfo + `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Optional Services</th>
                            <th>Enrolled Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${enrollments.map(enrollment => `
                            <tr>
                                <td>${enrollment.id}</td>
                                <td>${enrollment.user?.first_name || ''} ${enrollment.user?.last_name || ''}<br>
                                    <small class="text-muted">${enrollment.user?.email || ''}</small></td>
                                <td>${enrollment.course?.title || 'N/A'}<br>
                                    <small class="text-muted">${enrollment.course_table || 'florida_courses'}</small></td>
                                <td><span class="badge ${getStatusBadgeClass(enrollment.status)}">${enrollment.status || 'pending'}</span></td>
                                <td><span class="badge ${getPaymentBadgeClass(enrollment.payment_status)}">${enrollment.payment_status || 'unpaid'}</span><br>
                                    <small class="text-muted">$${parseFloat(enrollment.amount_paid || 0).toFixed(2)}</small></td>
                                <td>${getOptionalServicesDisplay(enrollment)}</td>
                                <td>${enrollment.created_at ? new Date(enrollment.created_at).toLocaleDateString() : 'N/A'}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="viewEnrollment(${enrollment.id})">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }
    
    function getOptionalServicesDisplay(enrollment) {
        let optionalServices = enrollment.optional_services;
        
        // Handle different data types
        if (!optionalServices) {
            return '<small class="text-muted">None</small>';
        }
        
        // If it's a string, try to parse it as JSON
        if (typeof optionalServices === 'string') {
            try {
                optionalServices = JSON.parse(optionalServices);
            } catch (e) {
                return '<small class="text-muted">None</small>';
            }
        }
        
        // Ensure it's an array and has items
        if (!Array.isArray(optionalServices) || optionalServices.length === 0) {
            return '<small class="text-muted">None</small>';
        }
        
        const serviceNames = {
            'certverify': 'CertVerify',
            'mail_certificate': 'Mail Copy',
            'fedex_certificate': 'FedEx 2Day',
            'nextday_certificate': 'Next Day',
            'email_certificate': 'Email Copy'
        };
        
        const services = optionalServices.map(service => {
            const name = serviceNames[service.id] || service.name || service.id;
            const price = parseFloat(service.price || 0).toFixed(2);
            return `${name} ($${price})`;
        }).join('<br>');
        
        const total = parseFloat(enrollment.optional_services_total || 0).toFixed(2);
        
        return `<small>${services}</small><br><strong class="text-success">Total: $${total}</strong>`;
    }
    
    function getStatusBadgeClass(status) {
        switch(status) {
            case 'completed': return 'bg-success';
            case 'active': return 'bg-primary';
            case 'expired': return 'bg-warning';
            case 'cancelled': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }
    
    function getPaymentBadgeClass(paymentStatus) {
        switch(paymentStatus) {
            case 'paid': return 'bg-success';
            case 'pending': return 'bg-warning';
            case 'failed': return 'bg-danger';
            case 'refunded': return 'bg-info';
            default: return 'bg-secondary';
        }
    }
    
    function handleSearch() {
        const search = document.getElementById('search-input').value;
        const status = document.getElementById('status-filter').value;
        const paymentStatus = document.getElementById('payment-filter').value;
        loadEnrollments(1, search, status, paymentStatus);
    }
    
    function handleFilter() {
        const search = document.getElementById('search-input').value;
        const status = document.getElementById('status-filter').value;
        const paymentStatus = document.getElementById('payment-filter').value;
        loadEnrollments(1, search, status, paymentStatus);
    }
    
    function viewEnrollment(id) {
        window.location.href = `/admin/enrollments/${id}`;
    }
    
    // Load enrollments on page load
    loadEnrollments();
</script>
@endsection
