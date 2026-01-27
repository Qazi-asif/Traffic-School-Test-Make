@extends('layouts.app')

@section('title', 'DICDS Certificate Orders')

@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Florida DICDS Certificate Orders</h1>
        <button class="btn btn-primary" onclick="showCreateModal()">
            <i class="fas fa-plus"></i> New Order
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="ordersTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>School</th>
                            <th>Course</th>
                            <th>Certificate Count</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ordersBody">
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Order Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    <div class="mb-3">
                        <label class="form-label">School</label>
                        <select class="form-select" id="schoolId" required>
                            <option value="">Select School...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select class="form-select" id="courseId" required>
                            <option value="">Select Course...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Certificate Count</label>
                        <input type="number" class="form-control" id="certificateCount" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit Price ($)</label>
                        <input type="number" class="form-control" id="unitPrice" step="0.01" min="0" value="5.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <input type="text" class="form-control" id="totalAmount" readonly>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitOrder()">Create Order</button>
            </div>
        </div>
    </div>
</div>

<!-- Amendment Modal -->
<div class="modal fade" id="amendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Amend Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="amendForm">
                    <input type="hidden" id="amendOrderId">
                    <div class="mb-3">
                        <label class="form-label">Current Certificate Count: <span id="currentCount"></span></label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Certificate Count</label>
                        <input type="number" class="form-control" id="amendedCount" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amendment Reason (min 10 characters)</label>
                        <textarea class="form-control" id="amendReason" rows="3" minlength="10" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitAmendment()">Submit Amendment</button>
            </div>
        </div>
    </div>
</div>

<script>
let orders = [];
let schools = [];
let courses = [];

async function loadOrders() {
    try {
        const response = await fetch('/web/dicds-orders');
        orders = await response.json();
        renderOrders();
    } catch (error) {
        console.error('Error loading orders:', error);
        document.getElementById('ordersBody').innerHTML = 
            '<tr><td colspan="7" class="text-center text-danger">Error loading orders</td></tr>';
    }
}

async function loadSchools() {
    try {
        const response = await fetch('/web/florida-schools');
        schools = await response.json();
        const select = document.getElementById('schoolId');
        select.innerHTML = '<option value="">Select School...</option>' + 
            schools.map(s => `<option value="${s.id}">${s.school_name}</option>`).join('');
    } catch (error) {
        console.error('Error loading schools:', error);
    }
}

async function loadCourses() {
    try {
        const response = await fetch('/web/florida-courses');
        courses = await response.json();
        const select = document.getElementById('courseId');
        select.innerHTML = '<option value="">Select Course...</option>' + 
            courses.map(c => `<option value="${c.id}">${c.course_name}</option>`).join('');
    } catch (error) {
        console.error('Error loading courses:', error);
    }
}

function renderOrders() {
    const tbody = document.getElementById('ordersBody');
    
    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No orders found. Create your first order!</td></tr>';
        return;
    }
    
    tbody.innerHTML = orders.map(order => `
        <tr>
            <td>#${order.id}</td>
            <td>${order.school?.school_name || 'N/A'}</td>
            <td>${order.course?.course_name || 'N/A'}</td>
            <td>${order.certificate_count}</td>
            <td>$${order.total_amount}</td>
            <td>
                <span class="badge bg-${order.status === 'active' ? 'success' : 'warning'}">
                    ${order.status}
                </span>
            </td>
            <td>
                ${order.status === 'pending' ? 
                    `<button class="btn btn-sm btn-warning" onclick="amendOrder(${order.id})">
                        <i class="fas fa-edit"></i> Amend
                    </button>` : ''}
                <button class="btn btn-sm btn-info" onclick="generateReceipt(${order.id})">
                    <i class="fas fa-receipt"></i> Receipt
                </button>
                <button class="btn btn-sm btn-success" onclick="updateApproval(${order.id})">
                    <i class="fas fa-check"></i> Approval
                </button>
            </td>
        </tr>
    `).join('');
}

function showCreateModal() {
    loadSchools();
    loadCourses();
    document.getElementById('createForm').reset();
    document.getElementById('totalAmount').value = '';
    const modal = new bootstrap.Modal(document.getElementById('createModal'));
    modal.show();
}

function calculateTotal() {
    const count = document.getElementById('certificateCount').value;
    const price = document.getElementById('unitPrice').value;
    if (count && price) {
        document.getElementById('totalAmount').value = '$' + (count * price).toFixed(2);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('certificateCount')?.addEventListener('input', calculateTotal);
    document.getElementById('unitPrice')?.addEventListener('input', calculateTotal);
});

async function submitOrder() {
    const schoolId = document.getElementById('schoolId').value;
    const courseId = document.getElementById('courseId').value;
    const count = document.getElementById('certificateCount').value;
    const price = document.getElementById('unitPrice').value;
    
    if (!schoolId || !courseId || !count || !price) {
        alert('Please fill all fields');
        return;
    }
    
    try {
        const response = await fetch('/web/dicds-orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                school_id: schoolId,
                course_id: courseId,
                certificate_count: count,
                total_amount: count * price
            })
        });
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
            loadOrders();
            alert('Order created successfully');
        } else {
            alert('Error creating order');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error creating order');
    }
}

function amendOrder(orderId) {
    const order = orders.find(o => o.id === orderId);
    if (!order) return;
    
    document.getElementById('amendOrderId').value = orderId;
    document.getElementById('currentCount').textContent = order.certificate_count;
    document.getElementById('amendedCount').value = order.certificate_count;
    
    const modal = new bootstrap.Modal(document.getElementById('amendModal'));
    modal.show();
}

async function submitAmendment() {
    const orderId = document.getElementById('amendOrderId').value;
    const amendedCount = document.getElementById('amendedCount').value;
    const reason = document.getElementById('amendReason').value;
    
    if (reason.length < 10) {
        alert('Amendment reason must be at least 10 characters');
        return;
    }
    
    try {
        const response = await fetch(`/web/dicds-orders/${orderId}/amend`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                amended_certificate_count: amendedCount,
                amendment_reason: reason
            })
        });
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('amendModal')).hide();
            loadOrders();
            alert('Order amended successfully');
        } else {
            alert('Error amending order');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error amending order');
    }
}

async function generateReceipt(orderId) {
    try {
        const response = await fetch(`/web/dicds-orders/${orderId}/generate-receipt`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (response.ok) {
            const receipt = await response.json();
            alert('Receipt generated: ' + receipt.receipt_number);
            window.print();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function updateApproval(orderId) {
    // Implement approval update
    alert('Approval update for order #' + orderId);
}

// Load orders on page load
document.addEventListener('DOMContentLoaded', loadOrders);
</script>
@endsection
